<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class KetshopModelPricerule extends JModelAdmin
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
  public function getTable($type = 'Pricerule', $prefix = 'KetshopTable', $config = array()) 
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
    $form = $this->loadForm('com_ketshop.pricerule', 'pricerule', array('control' => 'jform', 'load_data' => $loadData));

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
    $data = JFactory::getApplication()->getUserState('com_ketshop.edit.pricerule.data', array());

    if(empty($data)) {
      $data = $this->getItem();
    }

    return $data;
  }


  /**
   * Prepare and sanitise the table data prior to saving.
   *
   * @param   JTable  $table  A JTable object.
   *
   * @return  void
   *
   * @since   1.6
   */
  protected function prepareTable($table)
  {
    // Set the publish date to now
    if($table->published == 1 && (int)$table->publish_up == 0) {
      $table->publish_up = JFactory::getDate()->toSql();
    }

    if($table->published == 1 && intval($table->publish_down) == 0) {
      $table->publish_down = $this->getDbo()->getNullDate();
    }
  }


  /**
   * Returns some recipient data for a given price rule.
   *
   * @param   integer   $pk		The price rule id.
   * @param   string    $recipientType	The type of the recipient.
   *
   * @return  array			The recipient data.
   */
  public function getRecipientData($pk = null, $recipientType)
  {
    $pk = (!empty($pk)) ? $pk : (int)$this->getState($this->getName().'.id');

    $db = $this->getDbo();
    $query = $db->getQuery(true);

    // Sets attribute and table names according to the recipient type.
    $name = 'name AS item_name';
    $table = '#__users';

    if($recipientType == 'customer_group') {
      $name = 'title AS item_name';
      $table = '#__usergroups';
    }

    $query->select('item_id,'.$name)
	  ->from('#__ketshop_prule_recipient')
	  ->join('INNER', $table.' ON id=item_id')
	  ->where('prule_id='.(int)$pk)
          ->order('item_name');
    $db->setQuery($query);

    return $db->loadAssocList();
  }


  /**
   * Returns some target data for a given price rule.
   *
   * @param   integer   $pk		The price rule id.
   * @param   string    $targetType	The type of the target.
   *
   * @return  array			The target data.
   */
  public function getTargetData($pk = null, $targetType)
  {
    $pk = (!empty($pk)) ? $pk : (int)$this->getState($this->getName().'.id');

    $db = $this->getDbo();
    $query = $db->getQuery(true);

    // Sets attribute and table names according to the target type.
    $name = 'CONCAT_WS(" ", p.name, pv.name) AS item_name';
    $table = '#__ketshop_product AS p';

    if($targetType == 'product_cat') {
      $name = 'c.title AS item_name';
      $table = '#__categories AS c';
    }

    $query->select('pt.item_id, pt.var_id, '.$name)
	  ->from('#__ketshop_prule_target AS pt')
	  ->join('INNER', $table.' ON id=pt.item_id');

    if($targetType == 'product') {
      // Gets the variant name from the variant table.
      $query->join('INNER', '#__ketshop_product_variant AS pv ON pv.prod_id=pt.item_id AND pv.var_id=pt.var_id');
    }

    $query->where('pt.prule_id='.(int)$pk)
          ->order('item_name');
    $db->setQuery($query);

    return $db->loadAssocList();
  }


  /**
   * Returns some condition data for a given price rule.
   *
   * @param   integer   $pk		The price rule id.
   * @param   string    $conditionType	The type of the condition.
   *
   * @return  array			The condition data.
   */
  public function getConditionData($pk = null, $conditionType)
  {
    $pk = (!empty($pk)) ? $pk : (int)$this->getState($this->getName().'.id');
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    // Builds the SQL query according to the condition type.
    $join = '';

    if($conditionType == 'product_cat_amount') {
      $select = 'pc.item_id, c.title AS item_name, pc.operator, TRUNCATE(pc.item_amount, 2) AS item_amount';
      $join = '#__categories AS c ON c.id=pc.item_id';
    }
    elseif($conditionType == 'product_cat_qty') {
      $select = 'pc.item_id, c.title AS item_name, pc.operator, pc.item_qty';
      $join = '#__categories AS c ON c.id=pc.item_id';
    }
    else {
      $select = 'pc.item_id, pc.var_id, p.name AS item_name, pc.operator, pc.item_qty';
      $join = '#__ketshop_product AS p ON p.id=pc.item_id';
    }

    $query->select($select)
	  ->from('#__ketshop_prule_condition AS pc');

    if(!empty($join)) {
      $query->join('INNER', $join);
    }

    if($conditionType == 'product_qty') {
      // Gets the variant name from the variant table then concatenes it.
      $query->select('CONCAT_WS(" ", p.name, pv.name) AS item_name')
	    ->join('INNER', '#__ketshop_product_variant AS pv ON pv.prod_id=pc.item_id AND pv.var_id=pc.var_id');
    }

    $query->where('pc.prule_id='.(int)$pk)
          ->order('item_name');
    $db->setQuery($query);

    return $db->loadObjectList();
  }
}

