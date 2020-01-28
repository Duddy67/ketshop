<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 

JLoader::register('OrderTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/order.php');
JLoader::register('ShopHelper', JPATH_SITE.'/components/com_ketshop/helpers/shop.php');


class KetshopModelOrder extends JModelAdmin
{
  use OrderTrait;

  // Prefix used with the controller messages.
  protected $text_prefix = 'COM_KETSHOP';


  /**
   * Returns a Table object, always creating it.
   *
   * @param   string  $type    The table type to instantiate
   * @param   string  $prefix  A prefix for the table class name. Optional.
   * @param   array   $config  Configuration array for model. Optional.
   *
   * @return  JTable    A database object
   */
  public function getTable($type = 'Order', $prefix = 'KetshopTable', $config = array()) 
  {
    return JTable::getInstance($type, $prefix, $config);
  }


  /**
   * Method to get the record form.
   *
   * @param   array    $data      Data for the form.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  JForm|boolean  A JForm object on success, false on failure
   *
   * @since   1.6
   */
  public function getForm($data = array(), $loadData = true) 
  {
    $form = $this->loadForm('com_ketshop.order', 'order', array('control' => 'jform', 'load_data' => $loadData));

    if(empty($form)) {
      return false;
    }

    return $form;
  }


  /**
   * Method to get the data that should be injected in the form.
   *
   * @return  mixed  The data for the form.
   *
   * @since   1.6
   */
  protected function loadFormData() 
  {
    // Check the session for previously entered form data.
    $data = JFactory::getApplication()->getUserState('com_ketshop.edit.order.data', array());

    if(empty($data)) {
      $data = $this->getItem();
    }

    return $data;
  }


  /**
   * Method to get a single record.
   *
   * @param   integer  $pk  The id of the primary key.
   *
   * @return  \JObject|boolean  Object on success, false on failure.
   *
   * @since   1.6
   */
  public function getItem($pk = null)
  {
    if($item = parent::getItem($pk)) {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);

      $query->select('lastname, firstname, customer_number')
	    ->from('#__ketshop_customer')
	    ->where('id='.(int)$item->customer_id);
      $db->setQuery($query);
      $results = $db->loadObject();

      $item->firstname = $results->firstname;
      $item->lastname = $results->lastname;
      $item->customer_number = $results->customer_number;
      $item->transactions = $this->getTransactions($item);
      $item->shipping = $this->getShipping($item);
      $item->addresses = ShopHelper::getCustomerAddresses($item->customer_id);

      //$item = $this->getCompleteOrder($item);
      //var_dump($transaction);
    }

    return $item;
  }
}

