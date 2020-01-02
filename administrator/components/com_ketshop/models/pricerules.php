<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class KetshopModelPricerules extends JModelList
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
	      'id', 'pr.id',
	      'name', 'pr.name',
	      'created', 'pr.created',
	      'created_by', 'pr.created_by',
	      'published', 'pr.published',
	      'creator', 'user_id',
	      'value', 'pr.value',
	      'behavior', 'pr.behavior',
	      'type', 'pr.type', 'prule_type',
	      'ordering', 'pr.ordering',
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

    $pruleType = $this->getUserStateFromRequest($this->context.'.filter.prule_type', 'filter_prule_type');
    $this->setState('filter.prule_type', $pruleType);

    $behavior = $this->getUserStateFromRequest($this->context.'.filter.behavior', 'filter_behavior');
    $this->setState('filter.behavior', $behavior);

    // List state information.
    parent::populateState('pr.name', 'asc');
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
    $id .= ':'.$this->getState('filter.prule_type');
    $id .= ':'.$this->getState('filter.behavior');

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
    $query->select($this->getState('list.select', 'pr.id, pr.name, pr.created, pr.published,'.
                                   'pr.operation, pr.type, pr.recipient_type, pr.target_type, pr.behavior, pr.value,'.
				   'pr.ordering, pr.created_by, pr.checked_out, pr.checked_out_time'));

    $query->from('#__ketshop_price_rule AS pr');

    // Get the creator name.
    $query->select('u.name AS creator');
    $query->join('LEFT', '#__users AS u ON u.id = pr.created_by');


    // Filter by title search.
    $search = $this->getState('filter.search');
    if(!empty($search)) {
      if(stripos($search, 'id:') === 0) {
	$query->where('pr.id = '.(int) substr($search, 3));
      }
      else {
	$search = $db->Quote('%'.$db->escape($search, true).'%');
	$query->where('(pr.name LIKE '.$search.')');
      }
    }

    // Filter by publication state.
    $published = $this->getState('filter.published');
    if(is_numeric($published)) {
      $query->where('pr.published='.(int)$published);
    }
    elseif($published === '') {
      $query->where('(pr.published IN (0, 1))');
    }

    // Join over the users for the checked out user.
    $query->select('uc.name AS editor');
    $query->join('LEFT', '#__users AS uc ON uc.id=pr.checked_out');

    // Filter by creator.
    $userId = $this->getState('filter.user_id');
    if(is_numeric($userId)) {
      $type = $this->getState('filter.user_id.include', true) ? '= ' : '<>';
      $query->where('pr.created_by'.$type.(int) $userId);
    }

    // Filter by price rule type.
    if($pruleType = $this->getState('filter.prule_type')) {
      $query->where('pr.type = '.$db->quote($pruleType));
    }

    // Filter by price rule behavior.
    if($behavior = $this->getState('filter.behavior')) {
      $query->where('pr.behavior = '.$db->quote($behavior));
    }

    // Gets the possible option sent by a link to a modal window.
    $modalOption = JFactory::getApplication()->input->get->get('modal_option', '', 'string');

    if($modalOption == 'coupon_only') {
      // Displays price rules with coupon behavior only.
      $query->where('(pr.behavior="CPN_XOR" OR pr.behavior="CPN_AND")');
    }

    // Add the list to the sort.
    $orderCol = $this->state->get('list.ordering', 'pr.name');
    $orderDirn = $this->state->get('list.direction'); //asc or desc

    $query->order($db->escape($orderCol.' '.$orderDirn));

    return $query;
  }


  /**
   * Method to get an array of data items.
   *
   * @return  mixed  An array of data items on success, false on failure.
   *
   * @since   11.1
   */
  public function getItems()
  {
    // Get a storage key.
    $store = $this->getStoreId();

    // Try to load the data from internal storage.
    if(isset($this->cache[$store])) {
      return $this->cache[$store];
    }

    // Load the list items.
    $query = $this->_getListQuery();
    $items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));

    // Check for a database error.
    if($this->_db->getErrorNum()) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    // Places for each item the name of the linked recipients according to
    // their type (customer, customer_group) into an array.
    foreach($items as $item) {
      $query->clear();

      if($item->recipient_type == 'customer') {
	$query->select('name')
	      ->from('#__users')
	      ->join('LEFT', '#__ketshop_prule_recipient ON id = item_id')
	      ->where('prule_id='.$item->id);
      }
      // customer_group
      else {
	$query->select('title')
	      ->from('#__usergroups')
	      ->join('LEFT', '#__ketshop_prule_recipient ON id = item_id')
	      ->where('prule_id='.$item->id);
      }

      $db->setQuery($query);
      $recipients = $db->loadColumn();
      $item->recipients = $recipients;

      // Same than above but for products.
      $query->clear();

      if($item->target_type == 'product' || $item->target_type == 'bundle') {
	$query->select('name')
	      ->from('#__ketshop_product')
	      ->join('LEFT', '#__ketshop_prule_target ON id = item_id')
	      ->where('prule_id='.$item->id);
      }
      // product_cat
      else {
	$query->select('title')
	      ->from('#__categories')
	      ->join('LEFT', '#__ketshop_prule_target ON id = item_id')
	      ->where('prule_id='.$item->id);
      }

      $db->setQuery($query);
      $targets = $db->loadColumn();
      $item->targets = $targets;
    }

    // Add the items to the internal cache.
    $this->cache[$store] = $items;
    return $this->cache[$store];
  }
}

