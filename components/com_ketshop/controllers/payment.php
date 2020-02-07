<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// N.B: Contains OrderTrait.
JLoader::register('ShippingTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/shipping.php');


/**
 * @package     KetShop
 * @subpackage  com_ketshop
 */
class KetshopControllerPayment extends JControllerForm
{
  /**
   * The order model.
   *
   * @var    object
   */
  protected $order_model = null;


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
  }


  /**
   * Triggers a given plugin function then redirects according to the url returned by the plugin. 
   *
   * @return  void
   */
  public function trigger()
  {
    $suffix = $this->input->get->get('suffix', '', 'string');
    $paymentMode = $this->input->get->get('payment_mode', '', 'string');
    $orderId = $this->input->get->get('order_id', 0, 'int');
    // Optional parameters.
    $extraData = $this->input->get->get('extra_data', null, 'string');

    $order = $this->order_model->getCompleteOrder($orderId);
    $settings = UtilityHelper::getShopSettings($order->customer_id);

    $event = 'onKetshopPayment'.ucfirst($paymentMode).ucfirst($suffix);
    JPluginHelper::importPlugin('ketshoppayment');
    $dispatcher = JDispatcher::getInstance();
    $results = $dispatcher->trigger($event, array($order, $settings));

    if(!empty($results) && $results[0] !== null) {
      $this->setRedirect($results[0], false);
    }
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
    $paymentMode = $this->input->get('payment_mode', '', 'string');
    $orderId = $this->input->get->get('order_id', 0, 'int');
    // Sets the order status.
    $status = ($result == 'success') ? 'pending' : $result;
    JFactory::getApplication()->enqueueMessage(JText::_('COM_KETSHOP_ORDERING_CONFIRMATION_'.strtoupper($result)), 'message');

    $order = $this->order_model->getCompleteOrder($orderId);
    $this->order_model->finalizeOrder($status, $order);
    $this->order_model->sendOrderConfirmation($order);

    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=order&o_id='.(int)$order->id, false));
  }
}

