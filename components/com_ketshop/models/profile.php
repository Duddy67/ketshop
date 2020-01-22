<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.'/components/com_ketshop/models/customer.php';


// Inherit the backend version.
class KetshopModelProfile extends KetshopModelCustomer
{
  /**
   * Method to auto-populate the model state.
   *
   * N.B: Calling getState in this method will result in recursion.
   *
   * @since   1.6
   *
   * @return void
   */
  protected function populateState()
  {
    // Get the application object.
    $params = JFactory::getApplication()->getParams('com_ketshop');

    // Get the customer id.
    $customerId = JFactory::getApplication()->getUserState('com_ketshop.edit.customer.id');
    $customerId = !empty($customerId) ? $customerId : (int) JFactory::getUser()->get('id');

    // Set the customer id.
    $this->setState('customer.id', $customerId);

    // Load the parameters.
    $this->setState('params', $params);
  }


  /**
   * Method to check in (unlock) a customer.
   *
   * @param   integer  $customerId  The id of the row to check in.
   *
   * @return  boolean  True on success, false on failure.
   *
   * @since   1.6
   */
  public function checkin($customerId = null)
  {
    // Get the customer id.
    $customerId = (!empty($customerId)) ? $customerId : (int) $this->getState('customer.id');

    if($customerId) {
      // Initialise the table with JUser.
      //$table = JTable::getInstance('User');
      $table = JTable::getInstance('Customer', 'KetshopTable');

      // Attempt to check the row in.
      if(!$table->checkin($customerId)) {
	$this->setError($table->getError());

	return false;
      }
    }

    return true;
  }


  /**
   * Method to check out (lock) a customer for editing.
   *
   * @param   integer  $customerId  The id of the row to check out.
   *
   * @return  boolean  True on success, false on failure.
   *
   * @since   1.6
   */
  public function checkout($customerId = null)
  {
    // Get the customer id.
    $customerId = (!empty($customerId)) ? $customerId : (int) $this->getState('customer.id');

    if($customerId) {
      // Initialise the table with JUser.
      //$table = JTable::getInstance('User');
      $table = JTable::getInstance('Customer', 'KetshopTable');

      // Get the current user object.
      $user = JFactory::getUser();

      // Attempt to check the row out.
      if(!$table->checkout($user->get('id'), $customerId)) {
	$this->setError($table->getError());

	return false;
      }
    }

    return true;
  }
}

