<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


/**
 * Manufacturer table class
 */
class KetshopTableManufacturer extends JTable
{
  /**
   * Constructor
   *
   * @param object Database connector object
   */
  function __construct(&$db) 
  {
    parent::__construct('#__ketshop_manufacturer', 'id', $db);
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

    return parent::store($updateNulls);
  }
}

