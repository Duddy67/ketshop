<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class KetshopModelOrders extends JModelList
{
  /**
   * Constructor.
   *
   * @param   array  $config  An optional associative array of configuration settings.
   *
   * @see     \JModelLegacy
   * @since   1.6
   */
  public function __construct($config = array())
  {
    if(empty($config['filter_fields'])) {
      $config['filter_fields'] = array(
	      'id', 'o.id',
	      'name', 'o.name',
	      'created', 'o.created',
	      'created_by', 'o.created_by',
	      'published', 'o.published',
	      'creator', 'user_id',
	      'order_nb', 'order_status', 
	      'payment_status', 'shipping_status',
	      'c.lastname', 'c.customer_number'
      );
    }

    parent::__construct($config);
  }


  /**
   * Method to auto-populate the model state.
   *
   * This method should only be called once per instantiation and is designed
   * to be called on the first call to the getState() method unless the model
   * configuration flag to ignore the request is set.
   *
   * Book. Calling getState in this method will result in recursion.
   *
   * @param   string  $ordering   An optional ordering field.
   * @param   string  $direction  An optional direction (asc|desc).
   *
   * @return  void
   *
   * @since   1.6
   */
  protected function populateState($ordering = null, $direction = null)
  {
    // Initialise variables.
    $app = JFactory::getApplication();
    $session = JFactory::getSession();

    // Adjust the context to support modal layouts.
    if($layout = JFactory::getApplication()->input->get('layout')) {
      $this->context .= '.'.$layout;
    }

    // Get the state values set by the user.
    $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
    $this->setState('filter.search', $search);

    $userId = $app->getUserStateFromRequest($this->context.'.filter.user_id', 'filter_user_id');
    $this->setState('filter.user_id', $userId);

    $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
    $this->setState('filter.published', $published);

    $orderStatus = $app->getUserStateFromRequest($this->context.'.filter.order_status', 'filter_order_status');
    $this->setState('filter.order_status', $orderStatus);

    $paymentStatus = $app->getUserStateFromRequest($this->context.'.filter.payment_status', 'filter_payment_status');
    $this->setState('filter.payment_status', $paymentStatus);

    $shippingStatus = $app->getUserStateFromRequest($this->context.'.filter.shipping_status', 'filter_shipping_status');
    $this->setState('filter.shipping_status', $shippingStatus);

    // List state information.
    parent::populateState('o.name', 'asc');
  }


  /**
   * Method to get a store id based on the model configuration state.
   *
   * This is necessary because the model is used by the component and
   * different modules that might need different sets of data or different
   * ordering requirements.
   *
   * @param   string  $id  An identifier string to generate the store id.
   *
   * @return  string  A store id.
   *
   * @since   1.6
   */
  protected function getStoreId($id = '')
  {
    // Compile the store id.
    $id .= ':'.$this->getState('filter.search');
    $id .= ':'.$this->getState('filter.published');
    $id .= ':'.$this->getState('filter.user_id');

    return parent::getStoreId($id);
  }


  /**
   * Method to get a \JDatabaseQuery object for retrieving the data set from a database.
   *
   * @return  \JDatabaseQuery  A \JDatabaseQuery object to retrieve the data set.
   *
   * @since   1.6
   */
  protected function getListQuery()
  {
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    // Select the required fields from the table.
    $query->select($this->getState('list.select', 'o.id, o.name AS order_nb, o.created, o.published, o.created_by, o.checked_out,'.
				   'o.checked_out_time, o.status AS order_status, IFNULL(t.status, "no_payment") AS payment_status,'.
				   'IFNULL(s.status, "no_shipping") AS shipping_status, o.customer_id'));

    $query->from('#__ketshop_order AS o');

    // Join over shipping and transaction tables.
    $query->join('LEFT', '#__ketshop_order_shipping AS s ON o.id=s.order_id')
	  ->join('LEFT', '#__ketshop_order_transaction AS t ON o.id=t.order_id');

    // Get the creator name.
    $query->select('u.name AS creator, u.username');
    $query->join('LEFT', '#__users AS u ON u.id = o.created_by');

    // Get the customer name and number.
    $query->select('c.firstname, c.lastname, c.customer_number');
    $query->join('LEFT', '#__ketshop_customer AS c ON c.id = o.customer_id');

    // Filter by title search.
    $search = $this->getState('filter.search');
    if(!empty($search)) {
      if(stripos($search, 'id:') === 0) {
	$query->where('o.name= '.(int) substr($search, 3));
      }
      else {
	$search = $db->Quote('%'.$db->escape($search, true).'%');
	$query->where('(c.lastname LIKE '.$search.')');
      }
    }

    // Filter by publication state.
    $published = $this->getState('filter.published');
    if(is_numeric($published)) {
      $query->where('o.published='.(int)$published);
    }
    elseif($published === '') {
      $query->where('(o.published IN (0, 1))');
    }

    // Join over the users for the checked out user.
    $query->select('uc.name AS editor');
    $query->join('LEFT', '#__users AS uc ON uc.id=o.checked_out');

    // Filter by creator.
    $userId = $this->getState('filter.user_id');
    if(is_numeric($userId)) {
      $type = $this->getState('filter.user_id.include', true) ? '= ' : '<>';
      $query->where('o.created_by'.$type.(int) $userId);
    }

    // Filter by order status.
    $orderStatus = $this->getState('filter.order_status');
    if(!empty($orderStatus)) {
      $query->where('o.status='.$db->Quote($orderStatus));
    }

    //Filter by payment status.
    $paymentStatus = $this->getState('filter.payment_status');
    if(!empty($paymentStatus)) {
      $query->where('t.status='.$db->Quote($paymentStatus));
    }

    //Filter by shipping status.
    $shippingStatus = $this->getState('filter.shipping_status');
    if(!empty($shippingStatus)) {
      $query->where('s.status='.$db->Quote($shippingStatus));
    }

    // Add the list to the sort.
    $orderCol = $this->state->get('list.ordering', 'o.name');
    $orderDirn = $this->state->get('list.direction'); //asc or desc

    $query->order($db->escape($orderCol.' '.$orderDirn));

    return $query;
  }
}


