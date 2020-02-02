<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2020 - 2020 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JLoader::register('UtilityHelper', JPATH_ADMINISTRATOR.'/components/com_ketshop/helpers/utility.php');


class plgKetshoppaymentBanktransfer extends JPlugin
{

  /**
   * Collects and returns the payment mode object linked to the plugin.
   *
   * @return  array	A list of payment mode objects.
   */
  public function onKetshopPayment()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('id, name, plugin_element, description')
	  ->from('#__ketshop_payment_mode')
	  ->where('plugin_element='.$db->Quote('banktransfer'))
	  ->where('published=1');
    $db->setQuery($query);

    return $db->loadObject();
  }


  /**
   * Builds a payment form allowing the user to pay through a bank transfer or to cancel
   * payment.
   *
   * @param   object   $order		The current order object.
   * @param   object   $settings	The shop settings.
   *
   * @return  string			A payment form.
   */
  public function onKetshopPaymentBanktransfer($order, $settings)
  {
    $html = '<form action="'.JRoute::_('index.php?option=com_ketshop&task=payment.trigger&suffix=transaction&payment_mode=banktransfer', false).'" method="post" id="ketshop_cheque_payment">';
    $html .= '<span class="btn">';
    $html .= '<a href="'.JRoute::_('index.php?option=com_ketshop&view=checkout', false).'" class="btn-link ketshop-btn">';
    $html .= JText::_('COM_KETSHOP_CANCEL').' <span class="icon-remove"></span></a></span>';
    $html .= '<span class="btn" onclick="document.getElementById(\'ketshop_cheque_payment\').submit();">';
    $html .= JText::_('COM_KETSHOP_PAY_NOW').' <span class="icon-shop-credit-card"></span></a></span>';
    $html .= '</form>';

    return $html;
  }


  /**
   * Sets and creates the transaction for a given order.
   *
   * @param   object   $order		The current order object.
   * @param   object   $settings	The shop settings.
   *
   * @return  string			A return url.
   */
  public function onKetshopPaymentBanktransferTransaction($order, $settings)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('final_shipping_cost')
	  ->from('#__ketshop_order_shipping')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $shippingCost = $db->loadResult();

    $totalAmount = $order->final_amount_incl_tax + $shippingCost;
    $transactionId = uniqid();
    // Gets the current date and time (UTC).
    $now = JFactory::getDate()->toSql();


    // Creates the transaction.
    // N.B: Payment results can only be ok with offline payment methods since there's
    //      no web procedure to pass through.

    $columns = array('order_id', 'payment_mode', 'status', 'amount', 'result', 'transaction_id', 'created');
    $values = array($order->id, $db->Quote('banktransfer'), $db->Quote('pending'), $totalAmount, $db->Quote('success'),
		    $db->Quote($transactionId), $db->Quote($now));

    $query->clear()
	  ->insert('#__ketshop_order_transaction')
	  ->columns($columns)
	  ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();

    // Tells the payment controller that the transaction is done. 
    $url = UtilityHelper::getRootUrl().JRoute::_('index.php?option=com_ketshop&task=payment.end&result=success', false);

    return $url;
  }
}
