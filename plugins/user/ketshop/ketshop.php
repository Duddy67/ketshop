<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JLoader::register('UtilityHelper', JPATH_ADMINISTRATOR.'/components/com_ketshop/helpers/utility.php');


class plgUserKetshop extends JPlugin
{
  protected $post;

  /**
   * Constructor.
   *
   * @param   object  &$subject  The object to observe
   * @param   array   $config    An optional associative array of configuration settings.
   *
   * @since   3.7.0
   */
  public function __construct(&$subject, $config)
  {
    //Loads the component language file.
    $lang = JFactory::getLanguage();
    $langTag = $lang->getTag();
    $lang->load('com_ketshop', JPATH_ROOT.'/administrator/components/com_ketshop', $langTag);
    //Get the POST data.
    $this->post = JFactory::getApplication()->input->post->getArray();

    parent::__construct($subject, $config);
  }


  public function onUserAuthorisation($user, $options)
  {
    return $user;
  }


  public function onUserAuthorisationFailure($user)
  {
  }


  public function onUserLogin($user, $options)
  {
    return true;
  }


  public function onUserLogout($credentials, $options)
  {
    return true;
  }


  public function onUserAfterLogin($options)
  {
    return true;
  }


  public function onUserBeforeSave($oldUser, $isNew, $newUser)
  {
    return true;
  }


  /**
   * Saves user profile data
   *
   * @param   array    $data    entered user data
   * @param   boolean  $isNew   true if this is a new user
   * @param   boolean  $result  true if saving the user worked
   * @param   string   $error   error message
   *
   * @return  boolean
   */
  function onUserAfterSave($data, $isNew, $result, $error)
  {
    if($isNew && $result) {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);

      $columns = array('user_id', 'firstname', 'lastname', 'phone');
      $values = (int)$data['id'].','.$db->Quote($data['firstname']).','.$db->Quote($data['lastname']).','.$db->Quote($data['phone']);

      // Add the new Joomla user into the ketshop_customer table
      $query->insert($db->quoteName('#__ketshop_customer'))
	    ->columns($columns)
	    ->values($values);
      $db->setQuery($query);
      $db->execute();

      $customerId = (int)$db->insertid();

      UtilityHelper::insertAddress($data, 'billing', 'customer', $customerId);

      if((int)$data['shipping_address']) {
	UtilityHelper::insertAddress($data, 'shipping', 'customer', $customerId);
      }
    }
  }

  public function onUserBeforeDelete($user)
  {
  }


  public function onUserAfterDelete($user, $success, $msg)
  {
  }
}

