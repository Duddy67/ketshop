<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */


// No direct access
defined('_JEXEC') or die;

JLoader::register('ShippingTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/shipping.php');


class KetshopViewCheckout extends JViewLegacy
{
  use ShippingTrait;


  protected $state = null;
  protected $order_model = null;
  protected $order = null;
  protected $params = null;
  protected $products = null;
  protected $amounts = null;
  protected $detailed_amounts = null;
  protected $shippings = null;
  protected $payment_modes = null;
  protected $shop_settings = null;


  /**
   * Constructor
   *
   * @param   array  $config  A named configuration array for object construction.
   *                          name: the name (optional) of the view (defaults to the view class name suffix).
   *                          charset: the character set to use for display
   *                          escape: the name (optional) of the function to use for escaping strings
   *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)
   *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name
   *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)
   *                          layout: the layout (optional) to use to display the view
   *
   * @since   3.0
   */
  public function __construct($config = array())
  {
    parent::__construct($config);

    $this->order_model = JModelLegacy::getInstance('Order', 'KetshopModel');
    $this->order = $this->order_model->getCurrentOrder();
  }


  function display($tpl = null)
  {
    // Binds the order to the current user.
    $user = JFactory::getUser();
    $this->order_model->setUserId($user->id, $this->order);

    // Initialise variables
    $this->state = $this->get('State');
    $this->products = $this->order_model->getProducts($this->order);
    $this->amounts = $this->order_model->getAmounts($this->order);
    $this->amounts->price_rules = $this->order_model->getCartAmountPriceRules($this->order);
    $this->detailed_amounts = $this->order_model->getDetailedAmounts($this->order);
    $this->shippings = $this->getShippingsFromPlugins($this->order);
    $this->payment_modes = $this->get('PaymentModes');
    $this->shop_settings = UtilityHelper::getShopSettings($user->id);
    // Sets the editing status.
    $this->shop_settings->can_edit = true;
    $this->shop_settings->view_name = 'checkout';
    $this->shop_settings->price_display = $this->shop_settings->tax_method;

    // Check for errors.
    if(count($errors = $this->get('Errors'))) {
      JError::raiseWarning(500, implode("\n", $errors));
      return false;
    }

    // Redirect registered users to the cart page.
    if(empty($this->products)) {
      $app = JFactory::getApplication();
      $app->redirect(JRoute::_('index.php?option=com_ketshop&view=cart', false));
    }

    $this->params = $this->state->get('params');
    // Ensures prices are displayed.
    $this->params->set('show_price', 1);
    // Don't display price rule names in the price columns.
    $this->params->set('show_rule_name', 0);
    // Flag used to display prices either by unit or quantity.
    $this->params->def('product_price', '');
    $this->params->def('display_type', 'cart');

    JavascriptHelper::loadFieldLabels();

    $this->setDocument();

    parent::display($tpl);
  }


  protected function setDocument() 
  {
    $doc = JFactory::getDocument();
    $doc->addStyleSheet(JURI::base().'components/com_ketshop/css/ketshop.css');
  }
}
