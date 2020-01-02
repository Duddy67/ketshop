<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class KetshopModelConnection extends JModelForm
{
  /**
   * @var    object  The user registration data.
   * @since  1.6
   */
  protected $data = null;



  /**
   * Method to get the registration form.
   *
   * The base form is loaded from XML and then an event is fired
   * for users plugins to extend the form with extra fields.
   *
   * @param   array    $data      An optional array of data for the form to interogate.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  JForm  A JForm object on success, false on failure
   *
   * @since   1.6
   */
  public function getForm($data = array(), $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm('com_ketshop.registration', 'registration', array('control' => 'jform', 'load_data' => $loadData));

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
    $data = $this->getData();
    $this->preprocessData('com_ketshop.registration', $data);

    return $data;
  }


  /**
   * Method to get the registration form data.
   *
   * The base form data is loaded and then an event is fired
   * for users plugins to extend the data.
   *
   * @return  mixed  Data object on success, false on failure.
   *
   * @since   1.6
   */
  public function getData()
  {
    if ($this->data === null)
    {
      $this->data = new stdClass;
      $app = JFactory::getApplication();

      // Override the base user data with any data in the session.
      $temp = (array) $app->getUserState('com_ketshop.registration.data', array());

      // Don't load the data in this getForm call, or we'll call ourself
      $form = $this->getForm(array(), false);

      foreach($temp as $k => $v) {
	// Here we could have a grouped field, let's check it
	if(is_array($v)) {
	  $this->data->$k = new stdClass;

	  foreach($v as $key => $val) {
	    if($form->getField($key, $k) !== false) {
	      $this->data->$k->$key = $val;
	    }
	  }
	}
	// Only merge the field if it exists in the form.
	elseif($form->getField($k) !== false) {
	  $this->data->$k = $v;
	}
      }

      // Get the groups the user should be added to after registration.
      $this->data->groups = array();

      // Get the default new user group, guest or public group if not specified.
      $params = JComponentHelper::getParams('com_users');
      $system = $params->get('new_usertype', $params->get('guest_usergroup', 1));

      $this->data->groups[] = $system;

      // Unset the passwords.
      unset($this->data->password1, $this->data->password2);

      // Get the dispatcher and load the users plugins.
      $dispatcher = JEventDispatcher::getInstance();
      JPluginHelper::importPlugin('user');

      // Trigger the data preparation event.
      $results = $dispatcher->trigger('onContentPrepareData', array('com_ketshop.registration', $this->data));

      // Check for errors encountered while preparing the data.
      if(count($results) && in_array(false, $results, true)) {
	$this->setError($dispatcher->getError());
	$this->data = false;
      }
    }

    return $this->data;
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


  /**
   * Method to save the form data.
   *
   * @param   array  $temp  The form data.
   *
   * @return  mixed  The user id on success, false on failure.
   *
   * @since   1.6
   */
  public function register($temp)
  {
    // Initialise the table with JUser.
    $user = new JUser;
    $data = (array) $this->getData();

    // Merge in the registration data.
    foreach($temp as $k => $v) {
      $data[$k] = $v;
    }

    // Prepare the data for the user object.
    $data['email'] = JStringPunycode::emailToPunycode($data['email1']);
    $data['password'] = $data['password1'];
    $data['name'] = $data['firstname'].' '.$data['lastname'];
    $data['activation'] = $data['block'] = 0;

    // Bind the data.
    if(!$user->bind($data)) {
      $this->setError(JText::sprintf('COM_KETSHOP_REGISTRATION_BIND_FAILED', $user->getError()));
      return false;
    }

    // Load the users plugin group.
    JPluginHelper::importPlugin('user');

    // Store the data.
    if(!$user->save()) {
      $this->setError(JText::sprintf('COM_KETSHOP_REGISTRATION_SAVE_FAILED', $user->getError()));
      return false;
    }

    // N.B: The linking with the ketshop customer as well as his addresses is performed by the
    //      ketshop user plugin in the onUserAfterSave section.

    $config = JFactory::getConfig();
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    // Compile the notification mail values.
    $data = $user->getProperties();
    $data['fromname'] = $config->get('fromname');
    $data['mailfrom'] = $config->get('mailfrom');
    $data['sitename'] = $config->get('sitename');
    $data['siteurl'] = JUri::root();

    $emailSubject = JText::sprintf('COM_USERS_EMAIL_ACCOUNT_DETAILS', $data['name'], $data['sitename']);
    $emailBody = JText::sprintf('COM_USERS_EMAIL_REGISTERED_BODY_NOPW', $data['name'], $data['sitename'], $data['siteurl']);

    // Send the registration email.
    $return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

    // Check for an error.
    if($return !== true) {
      $this->setError(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));
      return false;
    }

    return $user->id;
  }
}

