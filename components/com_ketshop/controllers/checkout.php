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
class KetshopControllerShipment extends JControllerForm
{
  use PriceruleTrait;


  /**
   * The order model.
   *
   * @var    object
   */
  protected $order_model = null;

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
    $this->order_model = $this->getModel('Order', 'KetshopModel');
    $this->order = $this->order_model->getCurrentOrder();
    $this->user = JFactory::getUser();
    $session = JFactory::getSession();
    $this->coupons = $session->get('coupons', array(), 'ketshop'); 
  }


  /**
   * 
   *
   * @return  void
   */
  public function payment()
  {
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

    $this->order_model->updateProductQuantities($products, $this->order);
    // Updates the cart amounts.
    $cartPriceRules = $this->getCartPriceRules($this->user, $this->coupons);
    $this->order_model->setAmounts($this->order, $cartPriceRules);

    $app = JFactory::getApplication();
    $app->enqueueMessage(JText::sprintf('COM_KETSHOP_CART_UPDATED'));
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=shipment', false));
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
echo 'shipment';
    /*$this->order_model->removeProduct($prodId, $varId, $this->order);
    // Updates the cart amounts.
    $cartPriceRules = $this->getCartPriceRules($this->user, $this->coupons);
    $this->order_model->setAmounts($this->order, $cartPriceRules);

    $app = JFactory::getApplication();
    $app->enqueueMessage(JText::sprintf('COM_KETSHOP_PRODUCT_REMOVED_FROM_CART'));
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=shipment', false));*/
  }
}

