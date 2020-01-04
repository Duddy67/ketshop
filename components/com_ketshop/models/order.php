<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 

JLoader::register('OrderTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/order.php');


class KetshopModelOrder extends JModelItem
{
  use OrderTrait;


  /**
   * Gets the current order. Creates one if it doesn't exist.
   *
   * @return  object	The current order.
   */
  public function getCurrentOrder()
  {
    // Gets the ketshop current cookie id.
    $cookieId = ShopHelper::getCookieId();

    $db = $this->getDbo();
    $query = $db->getQuery(true);
    // Get the required product data.
    $query->select('*')
	  ->from('#__ketshop_order')
          ->where('cookie_id='.$db->Quote($cookieId))
          ->where('order_status='.$db->Quote('shopping'));
    $db->setQuery($query);
    $currentOrder = $db->loadObject();

    if($currentOrder === null) {
      if($this->createOrder($cookieId)) {
	// Now that a brand new order is created the function can be called again.
	return $this->getCurrentOrder();
      }
    }

    return $currentOrder;
  }


  /**
   * Creates a new order from the current cookie id.
   *
   * @param   string  $cookieId		The id of the current ketshop cookie.
   *
   * @return  object			The current order.
   */
  private function createOrder($cookieId)
  {
    // Gets the current date and time (UTC).
    $now = JFactory::getDate()->toSql();
    $user = JFactory::getUser();
    $settings = UtilityHelper::getShopSettings($user->id);

    $db = $this->getDbo();
    $query = $db->getQuery(true);
    // Get the id of the oldest registered super user.
    $query->select('MIN(user_id)')
	  ->from('#__user_usergroup_map')
          ->where('group_id=8');
    $db->setQuery($query);
    $superUserId = $db->loadResult();

    $item = array('cookie_id' => $cookieId, 'user_id' => $user->get('id'), 'name' => 'xxxxxxxx', 'order_status' => 'shopping',
		  'payment_status' => 'pending', 'tax_method' => $settings->tax_method, 'currency_code' => $settings->currency_code,
		  'rounding_rule' => $settings->rounding_rule, 'digits_precision' => $settings->digits_precision,
		  'published' => 0, 'created' => $now, 'created_by' => $superUserId);

    $table = JTable::getInstance('Order', 'KetshopTable', array());

    // Bind the data.
    if(!$table->bind($item)) {
      JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_KETSHOP_SYSTEM_ERROR', $table->getError()), 'error');
      return false;
    }

    // Check the data.
    if(!$table->check()) {
      JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_KETSHOP_SYSTEM_ERROR', $table->getError()), 'error');
      return false;
    }

    // Store the data.
    if(!$table->store()) {
      JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_KETSHOP_SYSTEM_ERROR', $table->getError()), 'error');
      return false;
    }

    return true;
  }
}

