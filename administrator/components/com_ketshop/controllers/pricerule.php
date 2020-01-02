<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class KetshopControllerPricerule extends JControllerForm
{
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
    $data = $this->input->post->get('jform', array(), 'array');

    // Resets the unwanted field from jform according to the rule type selected.
    // Sets also the show_rule value according to the chosen options.
    if($data['type'] == 'catalog') {
      $data['condition_type'] = '';
      $data['logical_opr'] = '';
      $data['comparison_opr'] = '';
      $data['condition_qty'] = 0;
      $data['condition_amount'] = 0;
      $data['all_variants'] = $data['all_variants_target'];
    }
    // cart
    else {
      // Cart rules with cart amount target cannot be hidden.
      if($data['target_type'] == 'cart_amount') {
	$data['show_rule'] = 1;
      }

      // Those conditions are unique so there is no need to use a logical operator.
      if($data['condition_type'] == 'total_prod_amount' || $data['condition_type'] == 'total_prod_qty') {
	$data['logical_opr'] = '';
      }
      else {
	// Used only with the "total_prod" conditions.
	$data['comparison_opr'] = '';
	$data['condition_qty'] = 0;
	$data['condition_amount'] = 0;
      }

      $data['all_variants'] = $data['all_variants_condition'];
    }

    // Updates the jform data array
    $this->input->post->set('jform', $data);

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
	  ->from('#__ketshop_price_rule')
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

