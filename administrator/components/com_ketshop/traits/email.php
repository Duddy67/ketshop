<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die; 


trait EmailTrait
{
  /**
   * Sends an email to a given user.
   *
   * @param objects $user       A user object.
   * @param array   $message    The subject and body of the message.
   * @param boolean $html       Flag which forces the email to be sent in html format.
   *
   * @return boolean     True on success, false otherwise.
   */
  public function sendEmail($user, $message, $html = false)
  {
    $users = array($user);
    $this->sendEmails($users, $message, $html);
  }


  /**
   * Sends an email to the given users.
   *
   * @param array   $users      A list of user objects.
   * @param array   $message    The subject and body of the message.
   * @param boolean $html       Flag which forces the email to be sent in html format.
   *
   * @return boolean     True on success, false otherwise.
   */
  public function sendEmails($users, $message, $html = false)
  {
    // A reference to the global mail object (JMail) is fetched through the JFactory object.
    // This is the object creating our mail.
    $mailer = JFactory::getMailer();

    $config = JFactory::getConfig();
    $sender = array($config->get('mailfrom'),
		    $config->get('fromname'));

    $mailer->setSender($sender);
    $recipients = array();

    foreach($users as $user) {
      $recipients[] = $user->email;
    }

    $mailer->addRecipient($recipients);

    // Set the subject and body of the email.
    $body = $message['body'];
    $mailer->setSubject($message['subject']);

    if($html) {
      // We want the body message in HTML.
      $mailer->isHTML(true);
      $mailer->Encoding = 'base64';
    }

    $mailer->setBody($body);

    $send = $mailer->Send();

    // Check for error.
    if($send !== true) {
      JFactory::getApplication()->enqueueMessage(JText::_('COM_KETSHOP_CONFIRMATION_EMAIL_FAILED'), 'warning');
      return false;
    }
    else {
      JFactory::getApplication()->enqueueMessage(JText::_('COM_KETSHOP_CONFIRMATION_EMAIL_SUCCESS'));
    }

    return true;
  }


  /**
   * Builds an order confirmation message out of the given customer and order objects.
   *
   * @param objects $customer	A customer object.
   * @param objects $order	A complete order object.
   *
   * @return array		The subject and body parts of the email message.
   */
  public function setOrderConfirmationEmail($customer, $order)
  {
    $message = array('subject' => '', 'body' => '');
    $params = JComponentHelper::getParams('com_ketshop');

    $message['subject'] = JText::sprintf('COM_KETSHOP_EMAIL_SUBJECT_ORDER_CONFIRMATION', $params->get('shop_name'), $customer->customer_number);
    $message['body'] = JText::sprintf('COM_KETSHOP_EMAIL_BODY_ORDER_GREETING', $customer->firstname, $customer->lastname, $params->get('shop_name'));
    $message['body'] .= JText::_('COM_KETSHOP_EMAIL_BODY_ORDER_HEADER');

    // Formats some data.
    $shippingMode = (isset($order->shipping)) ? $order->shipping->name : JText::_('COM_KETSHOP_NO_SHIPPING');

    if(isset($order->shipping) && $order->shipping->delivery_type == 'at_delivery_point') {
      $shippingMode = JText::_('COM_KETSHOP_AT_DELIVERY_POINT').': '.$shippingMode;
    }

    $paymentMode = $order->transactions[0]->payment_name;
    $orderDate = JHtml::_('date', $order->created, JText::_('DATE_FORMAT_LC4')); 

    $message['body'] .= JText::sprintf('COM_KETSHOP_EMAIL_BODY_ORDER_DETAIL', $customer->customer_number, $order->name, UtilityHelper::floatFormat($order->amounts->total_amount).' '.$order->currency_code, $paymentMode, $shippingMode, $orderDate);

    $address = $order->addresses['billing'];
    $street = $address->street;
    if(!empty($address->additional)) {
      $street = $address->street."\n".$address->additional;
    }

    $message['body'] .= JText::sprintf('COM_KETSHOP_EMAIL_BODY_ORDER_BILLING_ADDRESS', $customer->firstname, $customer->lastname, $street, $address->postcode, $address->city, $address->country_code);

    if($order->shippable) {
      if(isset($order->addresses['shipping'])) {
	$address = $order->addresses['shipping'];
	$street = $address->street;

	if(empty($address->company)) {
	  $address->company = $customer->firstname.' '.$customer->lastname;
	}

	if(!empty($address->additional)) {
	  $street = $address->street."\n".$address->additional;
	}
      }
      else {
	$address->company = $customer->firstname.' '.$customer->lastname;
      }

      $message['body'] .= JText::sprintf('COM_KETSHOP_EMAIL_BODY_ORDER_SHIPPING_ADDRESS', $address->company, $street, $address->postcode, $address->city, $address->country_code);
    }
    $message['body'] .= JText::_('COM_KETSHOP_EMAIL_BODY_ORDER_PRODUCTS_HEADER');

    foreach($order->products as $product) {
      if(!empty($product->variant_name)) {
	$product->name = $product->name.' '.$product->variant_name;
      }

      $price = $product->final_price_with_tax * $product->quantity;
      $unitPrice = $product->final_price_with_tax;

      $message['body'] .= JText::sprintf('COM_KETSHOP_EMAIL_BODY_ORDER_PRODUCT_ROW', $product->quantity, $product->code, $product->name, UtilityHelper::floatFormat($price).' '.$order->currency_code, UtilityHelper::floatFormat($unitPrice).' '.$order->currency_code);
    }

    $message['body'] .= JText::_('COM_KETSHOP_EMAIL_BODY_LEGAL_INFORMATION');
    $message['body'] .= JText::sprintf('COM_KETSHOP_EMAIL_BODY_SHOP_GOODBYE', $params->get('shop_name'));
    $message['body'] .= JText::sprintf('COM_KETSHOP_EMAIL_BODY_SHOP_INFORMATION', $params->get('shop_name'));

    return $message;
  }
}
