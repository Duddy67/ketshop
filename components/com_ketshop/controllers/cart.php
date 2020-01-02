<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JLoader::register('PriceruleTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/pricerule.php');


/**
 * @package     KetShop
 * @subpackage  com_ketshop
 */
class KetshopControllerCart extends JControllerForm
{
  use PriceruleTrait;


  /**
   * The Order model.
   *
   * @var    object
   */
  protected $model = null;

  /**
   * The current order.
   *
   * @var    object
   */
  protected $order = null;

  /**
   * The coupon array.
   *
   * @var    array
   */
  protected $coupons = null;

  /**
   * The current user.
   *
   * @var    object
   */
  protected $user = null;


  /**
   * Constructor.
   *
   * @param   array  $config  An optional associative array of configuration settings.
   *
   * @see     \JControllerLegacy
   * @since   1.6
   * @throws  \Exception
   */
  public function __construct($config = array())
  {
    parent::__construct($config);

    // Sets some common variables.
    $this->model = $this->getModel('Order', 'KetshopModel');
    $this->order = $this->model->getCurrentOrder();
    $this->user = JFactory::getUser();
    $session = JFactory::getSession();
    $this->coupons = $session->get('coupons', array(), 'ketshop'); 
  }


  /**
   * Adds a given product to the cart.
   *
   * @return  void
   */
  public function addToCart()
  {
    // Gets the product ids from GET.
    $prodId = $this->input->get('prod_id', 0, 'uint');
    $varId = $this->input->get('var_id', 0, 'uint');

    // Checks for ids.
    if(!(int)$prodId || !(int)$varId) {
      return;
    }

    // Adds the product.
    $product = $this->model->getProduct($prodId, $varId, $this->user, $this->coupons);
    $this->model->storeProduct($product, $this->order);
    // Updates the cart amounts.
    $cartPriceRules = $this->getCartPriceRules($this->user, $this->coupons);
    $this->model->setAmounts($this->order, $cartPriceRules);

    // Redirects the user to the reffering page.

    $snitch = ShopHelper::getSnitch();
    $url = 'index.php';

    // Checks just in case meanwhile cookies have been deleted. 
    if(preg_match('#^([a-z]+)\.#', $snitch->from, $matches)) {
      if($matches[1] == 'product') {
	$url = KetshopHelperRoute::getProductRoute($product->id.':'.$product->alias, $product->catid);
      }
      // category
      else {
	preg_match('#^[a-z]+\.([0-9]+)$#', $snitch->from, $matches);
	$alias = '';

	foreach($product->categories as $category) {
	  if($category->id == $matches[1]) {
	    $alias = $category->alias;
	  }
	}

	$url = KetshopHelperRoute::getCategoryRoute($matches[1].':'.$alias);

	if($snitch->limit_start) {
	  $url = $url.'&limitstart='.$snitch->limit_start;
	}
      }
    }

    $app = JFactory::getApplication();
    $app->enqueueMessage(JText::sprintf('COM_KETSHOP_PRODUCT_ADDED_TO_CART', $product->name.' '.$product->var_name));
    $this->setRedirect(JRoute::_($url, false));
  }


  /**
   * Updates the cart.
   *
   * @return  void
   */
  public function updateCart()
  {
    $form = $this->input->post->getArray();

    $products = array();
    foreach($form as $key => $quantity) {

      if(!ctype_digit($quantity) || $quantity == 0) {
	$quantity = 1;
      }

      $product = new stdClass();

      preg_match('#quantity_([0-9]*)_([0-9]*)$#', $key, $matches);

      $product->prod_id = (int)$matches[1];
      $product->var_id = (int)$matches[2];
      $product->quantity = (int)$quantity;

      $products[] = $product;
    }

    $this->model->updateProductQuantities($products, $this->order);
    // Updates the cart amounts.
    $cartPriceRules = $this->getCartPriceRules($this->user, $this->coupons);
    $this->model->setAmounts($this->order, $cartPriceRules);

    $app = JFactory::getApplication();
    $app->enqueueMessage(JText::sprintf('COM_KETSHOP_CART_UPDATED'));
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=cart', false));
  }


  /**
   * Removes all of the products from the cart.
   *
   * @return  void
   */
  public function emptyCart()
  {
    $this->model->resetOrder($this->order);

    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=cart', false));
  }


  /**
   * Removes a given product from the cart.
   *
   * @return  void
   */
  public function removeFromCart()
  {
    // Gets the product ids from GET.
    $prodId = $this->input->get('prod_id', 0, 'uint');
    $varId = $this->input->get('var_id', 0, 'uint');

    $this->model->removeProduct($prodId, $varId, $this->order);
    // Updates the cart amounts.
    $cartPriceRules = $this->getCartPriceRules($this->user, $this->coupons);
    $this->model->setAmounts($this->order, $cartPriceRules);

    $app = JFactory::getApplication();
    $app->enqueueMessage(JText::sprintf('COM_KETSHOP_PRODUCT_REMOVED_FROM_CART'));
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=cart', false));
  }


  /**
   * Redirects the user to the appropriate step.
   *
   * @return  void
   */
  public function order()
  {
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=connection', false));
    return false;
  }
}

