<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 
 

class KetshopControllerProduct extends JControllerForm
{
  /**
   * Sets the URL type variable when the "Normal" button is clicked. 
   *
   * @return  void
   */
  public function normal()
  {
    $this->input->set('type', 'normal');

    $this->add();
  }


  /**
   * Sets the URL type variable when the "Bundle" button is clicked. 
   *
   * @return  void
   */
  public function bundle()
  {
    $this->input->set('type', 'bundle');

    $this->add();
  }


  /**
   * Gets the URL arguments to append to an item redirect.
   *
   * @param   integer  $recordId  The primary key id for the item.
   * @param   string   $urlVar    The name of the URL variable for the id.
   *
   * @return  string  The arguments to append to the redirect URL.
   *
   * @since   1.6
   */
  protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
  {
    $append = parent::getRedirectToItemAppend($recordId, $urlVar);

    // Appends the type variable in case the "Normal" or "Bundle" button has been clicked.
    if($type = $this->input->get('type', '', 'string')) {
      $append .= '&type='.$type;
    }

    return $append;
  }


  /**
   * Method to save a record.
   *
   * @param   string  $key     The name of the primary key of the URL variable.
   * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
   *
   * @return  boolean  True if successful, false otherwise.
   *
   * @since   1.6
   */
  public function save($key = null, $urlVar = null)
  {
    // Gets the jform data.
    //$data = $this->input->post->get('jform', array(), 'array');

    // Gets the current date and time (UTC).
    //$now = JFactory::getDate()->toSql();

    // Saves the modified jform data array 
    //$this->input->post->set('jform', $data);

    // Hands over to the parent function.
    return parent::save($key, $urlVar);
  }


  /**
   * Method to check if you can edit an existing record.
   *
   * Extended classes can override this if necessary.
   *
   * @param   array   $data  An array of input data.
   * @param   string  $key   The name of the key for the primary key; default is id.
   *
   * @return  boolean
   *
   * @since   1.6
   */
  protected function allowEdit($data = array(), $key = 'id')
  {
    $itemId = $data['id'];
    $user = JFactory::getUser();

    // Get the item owner id.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('created_by')
	  ->from('#__ketshop_product')
	  ->where('id='.(int)$itemId);
    $db->setQuery($query);
    $createdBy = $db->loadResult();

    $canEdit = $user->authorise('core.edit', 'com_ketshop');
    $canEditOwn = $user->authorise('core.edit.own', 'com_ketshop') && $createdBy == $user->id;

    // Allow edition. 
    if($canEdit || $canEditOwn) {
      return 1;
    }

    // Hand over to the parent function.
    return parent::allowEdit($data, $key);
  }
}

