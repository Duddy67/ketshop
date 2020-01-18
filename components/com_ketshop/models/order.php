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
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @return  void
   *
   * @since   1.6
   */
  protected function populateState()
  {
    $app = JFactory::getApplication();
    // Load state from the request.
    $pk = $app->input->getInt('o_id');
    $this->setState('order.id', $pk);

    // Load the global parameters of the component.
    $params = $app->getParams();
    $this->setState('params', $params);
  }


  /**
   * Gets the current order. Creates one if it doesn't exist.
   *
   * @return  object	The current order.
   */
  public function getCurrentOrder()
  {
    // Gets the ketshop current cookie id.
    $cookieId = ShopHelper::getCookieId();
    $user = JFactory::getUser();

    $db = $this->getDbo();
    $query = $db->getQuery(true);
    // Get the required product data.
    $query->select('*')
	  ->from('#__ketshop_order')
          ->where('cookie_id='.$db->Quote($cookieId))
          ->where('status='.$db->Quote('shopping'));

    // The current user has already logged in.
    if($user->id) {
      // The user may have products in his cart from a previous and abandoned checkout.
      $query->where('(customer_id='.(int)$user->id.' OR customer_id=0)');
    }

    $db->setQuery($query);
    $currentOrder = $db->loadObject();

    if($currentOrder === null) {
      if($this->createOrder($cookieId)) {
	$this->setOrderNumber($cookieId);
	// Now that a brand new order is created the function can be called again.
	return $this->getCurrentOrder();
      }
    }

    return $currentOrder;
  }


  /**
   * Updates some values of the current order.
   *
   * @param   string   $status		The new order status.
   * @param   object   $order		The order to update.
   *
   * @return  void
   */
  public function finalizeOrder($status, $order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $fields = array('status='.$db->Quote($status), 'published=1');

    $query->update('#__ketshop_order')
	  ->set($fields)
	  ->where('id='.(int)$order->id);
    $db->setQuery($query);
    $db->execute();
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
    // Get the id of the first registered super user.
    $query->select('MIN(user_id)')
	  ->from('#__user_usergroup_map')
          ->where('group_id=8');
    $db->setQuery($query);
    $superUserId = $db->loadResult();

    $item = array('cookie_id' => $cookieId, 'customer_id' => $user->get('id'), 'name' => 'xxxxxxxx', 'status' => 'shopping',
		  'tax_method' => $settings->tax_method, 'currency_code' => $settings->currency_code,
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


  /**
   * Creates a uniq number for the current order.
   *
   * @param   string  $cookieId		The id of the current ketshop cookie.
   *
   * @return  void
   */
  private function setOrderNumber($cookieId)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('id, name, created')
	  ->from('#__ketshop_order')
	  ->where('cookie_id='.$db->Quote($cookieId))
	  ->where('status='.$db->Quote('shopping'))
	  ->where('name='.$db->Quote('xxxxxxxx'));
    $db->setQuery($query);
    $order = $db->loadObject();

    if($order !== null) {
      // Collects some parts of the creating date time.
      preg_match('#^[0-9]{2}([0-9]{2})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):[0-9]{2}$#', $order->created, $matches);
      // Concatenates a uniq order number.
      $orderNumber = $matches[2].$matches[3].$matches[1].$matches[4].$matches[5].'-'.$order->id;

      $query->clear()
	    ->update('#__ketshop_order')
	    ->set('name='.$db->Quote($orderNumber))
	    ->where('id='.(int)$order->id);
      $db->setQuery($query);
      $db->execute();
    }
  }
}

