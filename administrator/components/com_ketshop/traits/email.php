<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die; 

JLoader::register('OrderTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/order.php');


trait EmailTrait
{
  use OrderTrait;


  public function sendEmail($user, $message, $html = false)
  {
    $users = array($user);

    $this->sendEmails($users, $message, $html);
  }


  public function sendEmails($users, $message, $html = false)
  {
  }


  public function setOrderConfirmationEmail($customer, $order)
  {
    $message = array('subject' => '', 'body' => '');
    $params = JComponentHelper::getParams('com_ketshop');

    $message['subject'] = JText::sprintf('COM_KETSHOP_EMAIL_SUBJECT_ORDER_CONFIRMATION', $params->shop_name, $customer->customer_number);
  }


  public function setSubjectEmail($type, $user = null, $data = null)
  {
  }


  public function setBodyEmail($type, $user = null, $data = null)
  {
  }
}
