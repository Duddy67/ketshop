<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


/**
 * Paymentmode table class
 */
class KetshopTablePaymentmode extends JTable
{
  /**
   * Constructor
   *
   * @param object Database connector object
   */
  function __construct(&$db) 
  {
    parent::__construct('#__ketshop_payment_mode', 'id', $db);
  }


  /** 
   * Overrides JTable::store to set modified data and user id.
   *
   * @param   boolean  $updateNulls  True to update fields even if they are null.
   *
   * @return  boolean  True on success.
   *
   * @since   11.1
   */
  public function store($updateNulls = false)
  {
    // Gets the current date and time (UTC).
    $now = JFactory::getDate()->toSql();
    $user = JFactory::getUser();

    // Existing item
    if($this->id) { 
      $this->modified = $now;
      $this->modified_by = $user->get('id');
    }   
    // New items.
    else {
      // An item created and created_by field can be set by the user,
      // so we don't touch either of these if they are set.
      if(!(int)$this->created) {
        $this->created = $now;
      }   

      if(empty($this->created_by)) {
        $this->created_by = $user->get('id');
      }   
    }   

    // Verify that the chosen plugin is not already used by another payment mode. 
    $table = JTable::getInstance('Paymentmode', 'KetshopTable', array('dbo', $this->getDbo()));

    if($table->load(array('plugin_element' => $this->plugin_element)) && ($table->id != $this->id || $this->id == 0)) {
      $this->setError(JText::_('COM_KETSHOP_ERROR_PLUGIN_ALREADY_USED'));
      return false;
    }

    return parent::store($updateNulls);
  }
}

