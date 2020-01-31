<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

//JLoader::register('PriceruleTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/pricerule.php');
JLoader::register('ShippingTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/shipping.php');


/**
 * @package     KetShop
 * @subpackage  com_ketshop
 */
class KetshopControllerPayment extends JControllerForm
{
  use ShippingTrait;


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
  }


  /**
   * Stores the shipping data in the current order then redirect to the payment view.
   *
   * @return  void
   */
  public function proceed()
  {
    // Gets the needed ids from GET.
    $shippingId = $this->input->get('shipping_id', 0, 'uint');
    $paymentId = $this->input->get('payment_id', 0, 'uint');

    $shippings = $this->getShippingsFromPlugins($this->order);

    // Searches for the shipping selected by the customer.
    foreach($shippings as $shipping) {
      if($shipping->id == $shippingId) {
	$shipping->status = 'pending';
	$this->order_model->setShipping($shipping, $this->order);
	break;
      }
    }

    // Redirects to the payment view in order to displays the payment form.
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=payment&payment_id='.(int)$paymentId, false));
  }


  /**
   * Triggers a given plugin then redirects according to the url returned by the plugin. 
   *
   * @return  void
   */
  public function trigger()
  {
    $suffix = $this->input->get('suffix', '', 'string');
    $paymentMode = $this->input->get('payment_mode', '', 'string');
    $settings = UtilityHelper::getShopSettings($this->user->get('id'));

    $event = 'onKetshopPayment'.ucfirst($paymentMode).ucfirst($suffix);
    JPluginHelper::importPlugin('ketshoppayment');
    $dispatcher = JDispatcher::getInstance();

    $results = $dispatcher->trigger($event, array($this->order, $settings));

    $this->setRedirect($results[0], false);
  }


  /**
   * Ends the payment process and redirects to the order page.
   *
   * @return  void
   */
  public function end()
  {
    // Gets the result sent from the payment plugin.
    $result = $this->input->get('result', '', 'string');
    // Sets the order status.
    $status = ($result == 'success') ? 'pending' : $result;
    JFactory::getApplication()->enqueueMessage(JText::_('COM_KETSHOP_ORDERING_CONFIRMATION_'.strtoupper($result)), 'message');

    $this->order_model->finalizeOrder($status, $this->order);
    $this->order_model->sendOrderConfirmation($this->order);

    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=order&o_id='.(int)$this->order->id, false));
  }
}

