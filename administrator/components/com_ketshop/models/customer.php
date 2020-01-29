<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


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
    // N.B: Do not use the getName() method to set the item id as the child class (ie:
    // Profile) in frontend would be forced to set its item id as profile.id which is not
    // appropriate.
    $pk = (!empty($pk)) ? $pk : (int)$this->getState('customer.id');

    if($item = parent::getItem($pk)) {
      $db = JFactory::getDbo();
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

      $item->addresses = $this->getAddresses($item->id);
    }

    return $item;
  }


  /**
   * Returns the billing and shipping addresses of a given user. 
   *
   * @param   integer	The id of the user (optional).
   *
   * @return  array	A list of billing and shipping address objects.
   *
   */
  public function getAddresses($pk = null)
  {
    $pk = (!empty($pk)) ? $pk : (int)$this->getState('customer.id');

    $addresses = array();
    $types = array('billing', 'shipping');

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    foreach($types as $type) {
      // Gets the last billing or shipping address set by the customer. 
      $query->clear();
      // Appends the company field to the query for shipping address.
      $company = ($type == 'shipping') ? 'a.company,' : '';

      $query->select('a.street, a.postcode, a.city, a.region_code, a.country_code, a.continent_code, a.type,'.$company.
		     'a.phone, a.additional, c.lang_var AS country_lang_var, r.lang_var AS region_lang_var')
	    ->from('#__ketshop_address AS a')
	    ->join('INNER', '#__ketshop_customer AS cu ON cu.id='.(int)$pk)
	    ->join('LEFT', '#__ketshop_country AS c ON c.alpha_2 = a.country_code')
	    ->join('LEFT', '#__ketshop_region AS r ON r.code = a.region_code')
	    ->where('a.item_id = cu.id AND a.item_type = '.$db->Quote('customer'))
	    ->where('a.type='.$db->Quote($type))
	    // Gets the latest inserted address in case of history.
	    ->order('a.created DESC')
	    ->setLimit(1);
      $db->setQuery($query);
      $address = $db->loadObject();

      if($address !== null) {
	$addresses[$type] = $address; 
      }
    }

    return $addresses;
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

