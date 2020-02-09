<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');


/**
 * This controller is called by the bank gateways during the transaction on the bank
 * website.
 * Since the controller is called remotely from the bank website there is no way to know
 * the id of the current customer. Thus, the id of the current order has to be retrieve 
 * through the GET variable or through the data sent by the bank gateway. 
 *
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

    // Sets the order model.
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

    $event = 'onKetshopPayment'.ucfirst($paymentMode).ucfirst($suffix);
    JPluginHelper::importPlugin('ketshoppayment');
    $dispatcher = JDispatcher::getInstance();

    $results = $dispatcher->trigger($event);

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
    $status = $this->input->get->get('status', '', 'string');

    JFactory::getApplication()->enqueueMessage(JText::_('COM_KETSHOP_ORDERING_CONFIRMATION_'.strtoupper($result)), 'message');

    $order = $this->order_model->getCompleteOrder($orderId);
    $this->order_model->finalizeOrder($status, $order);
    $this->order_model->sendOrderConfirmation($order);

    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=order&o_id='.(int)$order->id, false));
  }
}

