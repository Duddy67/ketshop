<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');


/**
 * Routing class of com_ketshop
 *
 * @since  3.3
 */
class KetshopRouter extends JComponentRouterView
{
  protected $noIDs = false;


  /**
   * ProductProduct Component router constructor
   *
   * @param   JApplicationCms  $app   The application object
   * @param   JMenu            $menu  The menu object to work with
   */
  public function __construct($app = null, $menu = null)
  {
    $params = JComponentHelper::getParams('com_ketshop');
    $this->noIDs = (bool) $params->get('sef_ids');
    $categories = new JComponentRouterViewconfiguration('categories');
    $categories->setKey('id');
    $this->registerView($categories);
    $category = new JComponentRouterViewconfiguration('category');
    $category->setKey('id')->setParent($categories, 'catid')->setNestable()->addLayout('blog');
    $this->registerView($category);
    $article = new JComponentRouterViewconfiguration('product');
    $article->setKey('id')->setParent($category, 'catid');
    $this->registerView($article);
    $form = new JComponentRouterViewconfiguration('form');
    $form->setKey('n_id');
    $this->registerView($form);
    $cart = new JComponentRouterViewconfiguration('cart');
    $this->registerView($cart);
    $connection = new JComponentRouterViewconfiguration('connection');
    $this->registerView($connection);
    $checkout = new JComponentRouterViewconfiguration('checkout');
    $this->registerView($checkout);
    $payment = new JComponentRouterViewconfiguration('payment');
    $this->registerView($payment);
    $order = new JComponentRouterViewconfiguration('order');
    $this->registerView($order);

    parent::__construct($app, $menu);

    $this->attachRule(new JComponentRouterRulesMenu($this));

    if($params->get('sef_advanced', 0)) {
      $this->attachRule(new JComponentRouterRulesStandard($this));
      $this->attachRule(new JComponentRouterRulesNomenu($this));
    }
    else {
      JLoader::register('KetshopRouterRulesLegacy', __DIR__.'/helpers/legacyrouter.php');
      $this->attachRule(new KetshopRouterRulesLegacy($this));
    }
  }


  /**
   * Method to get the segment(s) for a category
   *
   * @param   string  $id     ID of the category to retrieve the segments for
   * @param   array   $query  The request that is built right now
   *
   * @return  array|string  The segments of this item
   */
  public function getCategorySegment($id, $query)
  {
    $category = JCategories::getInstance($this->getName())->get($id);

    if($category) {
      $path = array_reverse($category->getPath(), true);
      $path[0] = '1:root';

      if($this->noIDs) {
	foreach($path as &$segment) {
	  list($id, $segment) = explode(':', $segment, 2);
	}
      }

      return $path;
    }

    return array();
  }


  /**
   * Method to get the segment(s) for a category
   *
   * @param   string  $id     ID of the category to retrieve the segments for
   * @param   array   $query  The request that is built right now
   *
   * @return  array|string  The segments of this item
   */
  public function getCategoriesSegment($id, $query)
  {
    return $this->getCategorySegment($id, $query);
  }


  /**
   * Method to get the segment(s) for a product 
   *
   * @param   string  $id     ID of the product to retrieve the segments for
   * @param   array   $query  The request that is built right now
   *
   * @return  array|string  The segments of this item
   */
  public function getProductSegment($id, $query)
  {
    if(!strpos($id, ':')) {
      $db = JFactory::getDbo();
      $dbquery = $db->getQuery(true);
      $dbquery->select($dbquery->qn('alias'))
	      ->from($dbquery->qn('#__ketshop_product'))
	      ->where('id='.$dbquery->q((int) $id));
      $db->setQuery($dbquery);

      $id .= ':'.$db->loadResult();
    }

    if($this->noIDs) {
      list($void, $segment) = explode(':', $id, 2);

      return array($void => $segment);
    }

    return array((int) $id => $id);
  }


  /**
   * Method to get the segment(s) for a form
   *
   * @param   string  $id     ID of the product form to retrieve the segments for
   * @param   array   $query  The request that is built right now
   *
   * @return  array|string  The segments of this item
   *
   * @since   3.7.3
   */
  public function getFormSegment($id, $query)
  {
    return $this->getProductSegment($id, $query);
  }


  /**
   * Method to get the id for a category
   *
   * @param   string  $segment  Segment to retrieve the ID for
   * @param   array   $query    The request that is parsed right now
   *
   * @return  mixed   The id of this item or false
   */
  public function getCategoryId($segment, $query)
  {
    if(isset($query['id'])) {
      $category = JCategories::getInstance($this->getName(), array('access' => false))->get($query['id']);

      if($category) {
	foreach($category->getChildren() as $child) {
	  if($this->noIDs) {
	    if($child->alias == $segment) {
	      return $child->id;
	    }
	  }
	  else {
	    if($child->id == (int) $segment) {
	      return $child->id;
	    }
	  }
	}
      }
    }

    return false;
  }


  /**
   * Method to get the segment(s) for a category
   *
   * @param   string  $segment  Segment to retrieve the ID for
   * @param   array   $query    The request that is parsed right now
   *
   * @return  mixed   The id of this item or false
   */
  public function getCategoriesId($segment, $query)
  {
    return $this->getCategoryId($segment, $query);
  }


  /**
   * Method to get the segment(s) for a product
   *
   * @param   string  $segment  Segment of the product to retrieve the ID for
   * @param   array   $query    The request that is parsed right now
   *
   * @return  mixed   The id of this item or false
   */
  public function getProductId($segment, $query)
  {
    if($this->noIDs) {
      $db = JFactory::getDbo();
      $dbquery = $db->getQuery(true);
      $dbquery->select($dbquery->qn('id'))
	      ->from($dbquery->qn('#__ketshop_product'))
	      ->join('INNER', $dbquery->qn('#__ketshop_product_cat_map').' ON product_id=id')
	      ->where('alias='.$dbquery->q($segment))
	      ->where('cat_id='.$dbquery->q($query['id']));
      $db->setQuery($dbquery);

      return (int) $db->loadResult();
    }

    return (int) $segment;
  }
}


/**
 * Product router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function KetshopBuildRoute(&$query)
{
  $app = JFactory::getApplication();
  $router = new KetshopRouter($app, $app->getMenu());

  return $router->build($query);
}


/**
 * Product router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function KetshopParseRoute($segments)
{
  $app = JFactory::getApplication();
  $router = new KetshopRouter($app, $app->getMenu());

  return $router->parse($segments);
}

