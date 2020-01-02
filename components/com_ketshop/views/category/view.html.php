<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JLoader::register('ProductTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/product.php');
//JLoader::register('PriceruleTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/pricerule.php');

use Joomla\Registry\Registry;


/**
 * HTML View class for the KetShop component.
 */
class KetshopViewCategory extends JViewCategory
{
  use ProductTrait;

  /**
   * Execute and display a template script.
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  mixed  A string if successful, otherwise a Error object.
   */

  /**
   * @var    array  Array of leading items for blog display
   * @since  3.2
   */
  protected $lead_items = array();

  /**
   * @var    array  Array of intro (multicolumn display) items for blog display
   * @since  3.2
   */
  protected $intro_items = array();

  /**
   * @var    array  Array of links in blog display
   * @since  3.2
   */
  protected $link_items = array();

  /**
   * @var    integer  Number of columns in a multi column display
   * @since  3.2
   */
  protected $columns = 1;

  /**
   * @var    string  The name of the extension for the category
   * @since  3.2
   */
  protected $extension = 'com_ketshop';

  /**
   * @var    string  The current date.
   */
  protected $now_date = null;

  /**
   * @var    object  The current user.
   */
  protected $user = null;

  /**
   * @var    object  A JUri object.
   */
  protected $uri = null;

  /**
   * @var    object  The current settings of the shop.
   */
  public $shop_settings;

  /**
   * @var    array   A list of attribute structures.
   */
  public $attribute_filters = array();

  /**
   * @var    array   A list manufacturer options.
   */
  public $filter_manufacturer = null;


  /**
   * Execute and display a template script.
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  mixed  A string if successful, otherwise an Error object.
   *
   * @since   3.2
   */
  public function display($tpl = null)
  {
    // Call parent method with common display elements (state, items etc...) used in
    // category list displays.
    parent::commonCategoryDisplay();

    // Prepare the data
    // Get the metrics for the structural page layout.
    $params = $this->params;
    $numLeading = $params->def('num_leading_products', 1);
    $numIntro   = $params->def('num_intro_products', 4);
    $numLinks   = $params->def('num_links', 4);

    // Gets the user object and the current url, (needed in the product edit layout).
    $this->user = JFactory::getUser();
    $this->uri = JUri::getInstance();
    $this->pagination = $this->get('Pagination');
    $model = JModelLegacy::getInstance('Product', 'KetshopModel');
    $this->filter_manufacturer = $this->get('ManufacturerOptions');

    // Gets the global settings of the shop.
    $this->shop_settings = UtilityHelper::getShopSettings($this->user->id);
    $this->shop_settings->price_display = $this->shop_settings->tax_method;

    // Prepare the data.
    // Compute the product slugs.
    foreach($this->items as $item) {
      $item->slug = $item->alias ? ($item->id.':'.$item->alias) : $item->id;
      $item->catslug = $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
      $item->parent_slug = ($item->parent_alias) ? ($item->parent_id.':'.$item->parent_alias) : $item->parent_id;
      // No link for ROOT category
      if($item->parent_alias == 'root') {
	$item->parent_slug = null;
      }

      $item = $model->setProductPrices($item);
      $item = $model->setProductStocks($item);

      // Needed for the product properties layouts.
      $item->attributes_location = $item->weight_location = $item->dimensions_location = 'summary';
    }

    // Check for layout override only if this is not the active menu item
    // If it is the active menu item, then the view and category id will match
    $app = JFactory::getApplication();
    $active = $app->getMenu()->getActive();

    // The category has no itemId and thus is not linked to any menu item. 
    if((!$active) || ((strpos($active->link, 'view=category') === false) ||
		      (strpos($active->link, '&id='.(string)$this->category->id) === false))) {
      // Get the layout from the merged category params
      if($layout = $this->category->params->get('category_layout')) {
	$this->setLayout($layout);
      }
    }
    // At this point, we are in a menu item, so we don't override the layout
    elseif(isset($active->query['layout'])) {
      // We need to set the layout from the query in case this is an alternative menu item (with an alternative layout)
      $this->setLayout($active->query['layout']);
    }
    // N.B: In case the layout parameter is not found within the query, the default layout
    // will be set.

    // For blog layouts, preprocess the breakdown of leading, intro and linked articles.
    // This makes it much easier for the designer to just interrogate the arrays.
    if(($params->get('layout_type') == 'blog') || ($this->getLayout() == 'blog')) {

      // Computes the number of extra items (if any).
      $numExtra = 0;
      if(count($this->items) > ($numLeading + $numIntro + $numLinks)) {
	$numExtra = count($this->items) - ($numLeading + $numIntro + $numLinks);
      }

      foreach($this->items as $i => $item) {
	if($i < $numLeading) {
	  $this->lead_items[] = $item;
	}
	// Adds the possible extra items to the intro items.
	elseif($i >= $numLeading && $i < $numLeading + $numIntro + $numExtra) {
	  $this->intro_items[] = $item;
	}
	elseif($i < $numLeading + $numIntro + $numLinks + $numExtra) {
	  $this->link_items[] = $item;
	}
	else {
	  continue;
	}
      }

      $this->columns = max(1, $params->def('num_columns', 1));

      $order = $params->def('multi_column_order', 1);

      if($order == 0 && $this->columns > 1) {
	// Call order down helper
	$this->intro_items = KetshopHelperQuery::orderDownColumns($this->intro_items, $this->columns);
      }
    }

    // Sets the name of the active layout in params, (needed for the filter ordering layout).
    $params->set('active_layout', $this->getLayout());
    // Sets the filter_ordering parameter for the layout.
    $this->filter_ordering = $this->state->get('list.filter_ordering');

    if($this->params->get('attribute_filter_ids') !== null) {
      // Gets the attribute structure for each filters.
      foreach($this->params->get('attribute_filter_ids') as $attributeId) {
	$this->attribute_filters[] = $this->getAttribute($attributeId);
      }
    }

    $this->now_date = JFactory::getDate()->toSql();
    // Creates a new JForm object
    $this->filter_form = new JForm('FilterForm');
    $this->filter_form->loadFile(JPATH_SITE.'/components/com_ketshop/models/forms/filter_category.xml');

    $this->prepareDocument();
    $this->setDocument();

    return parent::display($tpl);
  }


  /**
   * Method to prepares the document
   *
   * @return  void
   *
   * @since   3.2
   */
  protected function prepareDocument()
  {
    $app = JFactory::getApplication();
    // Because the application sets a default page title,
    // we need to get it from the menu item itself
    $menus = $app->getMenu();
    $menu = $menus->getActive();

    if($menu) {
      $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
    }

    $title = $this->params->get('page_title', '');

    // Check for empty title and add site name if param is set
    if(empty($title)) {
      $title = $app->get('sitename');
    }
    elseif($app->get('sitename_pagetitles', 0) == 1) {
      $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
    }
    elseif($app->get('sitename_pagetitles', 0) == 2) {
      $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
    }

    // If no title is find, set it to the category title. 
    if(empty($title)) {
      $title = $this->category->title;
    }

    $this->document->setTitle($title);

    if($this->category->metadesc) {
      $this->document->setDescription($this->category->metadesc);
    }
    elseif($this->params->get('menu-meta_description')) {
      $this->document->setDescription($this->params->get('menu-meta_description'));
    }

    if($this->category->metakey) {
      $this->document->setMetadata('keywords', $this->category->metakey);
    }
    elseif($this->params->get('menu-meta_keywords')) {
      $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
    }

    if($this->params->get('robots')) {
      $this->document->setMetadata('robots', $this->params->get('robots'));
    }

    if(!is_object($this->category->metadata)) {
      $this->category->metadata = new Registry($this->category->metadata);
    }

    if(($app->get('MetaAuthor') == '1') && $this->category->get('creator', '')) {
      $this->document->setMetaData('creator', $this->category->get('creator', ''));
    }

    $mdata = $this->category->metadata->toArray();

    foreach($mdata as $k => $v) {
      if($v) {
	$this->document->setMetadata($k, $v);
      }
    }

    return;
  }


  /**
   * Includes possible css and Javascript files.
   *
   * @return  void
   */
  protected function setDocument() 
  {
    $doc = JFactory::getDocument();
    $doc->addStyleSheet(JURI::base().'components/com_ketshop/css/ketshop.css');
  }
}
