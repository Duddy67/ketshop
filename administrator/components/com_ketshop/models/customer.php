<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 

JLoader::register('ShopHelper', JPATH_SITE.'/components/com_ketshop/helpers/shop.php');


class KetshopModelCustomer extends JModelAdmin
{
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
  public function getTable($type = 'Customer', $prefix = 'KetshopTable', $config = array()) 
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
    $form = $this->loadForm('com_ketshop.customer', 'customer', array('control' => 'jform', 'load_data' => $loadData));

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
    $data = JFactory::getApplication()->getUserState('com_ketshop.edit.customer.data', array());

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
   * @return  mixed  Object on success, false on failure.
   */
  public function getItem($pk = null)
  {
    $pk = (!empty($pk)) ? $pk : (int)$this->getState($this->getName().'.id');

    if($item = parent::getItem($pk)) {
      $db = $this->getDbo();
      $query = $db->getQuery(true);

      // Collects some Joomla user's extra data.
      $query->select('username, email, lastvisitDate')
	    ->from('#__users')
	    ->where('id='.(int)$pk);
      $db->setQuery($query);
      $user = $db->loadObject();

      $item->username = $user->username;
      $item->email = $user->email;
      $item->lastvisitDate = $user->lastvisitDate;

      $item->addresses = ShopHelper::getCustomerAddresses($item->id);
    }

    return $item;
  }


  /**
   * Method to validate the form data.
   *
   * @param   \JForm  $form   The form to validate against.
   * @param   array   $data   The data to validate.
   * @param   string  $group  The name of the field group to validate.
   *
   * @return  array|boolean  Array of filtered data if valid, false otherwise.
   *
   * @see     \JFormRule
   * @see     \JFilterInput
   * @since   1.6
   */
  public function validate($form, $data, $group = null)
  {
    // The shipping address is not required.
    if(!(int)$data['shipping_address']) {
      $mandatoryFields = array('street_shipping', 'city_shipping', 'postcode_shipping',
			       'region_code_shipping', 'country_code_shipping');

      foreach($mandatoryFields as $fieldName) {
	// Makes the field non-binding to prevent the form to get stuck with warning
	// messages.
	$form->setFieldAttribute($fieldName, 'required', 'false');
      }
    }

    return parent::validate($form, $data, $group);
  }


  public function getPendingOrders($pk = null)
  {
    //$pk = (!empty($pk)) ? $pk : (int)$this->getState($this->getName().'.id');
  }
}

