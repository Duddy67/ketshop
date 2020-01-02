<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class KetshopModelShippings extends JModelList
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
	      'id', 's.id',
	      'name', 's.name',
	      'created', 's.created',
	      'created_by', 's.created_by',
	      'published', 's.published',
	      'creator', 'user_id',
	      'ordering', 's.ordering',
	      'delivery_type', 's.delivery_type',
	      'min_weight', 's.min_weight',
	      'max_weight', 's.max_weight',
	      'min_product', 's.min_product',
	      'max_product', 's.max_product'
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

    $deliveryType = $app->getUserStateFromRequest($this->context.'.filter.delivery_type', 'filter_delivery_type');
    $this->setState('filter.delivery_type', $deliveryType);

    $pluginElement = $app->getUserStateFromRequest($this->context.'.filter.plugin_element', 'filter_plugin_element');
    $this->setState('filter.plugin_element', $pluginElement);

    // List state information.
    parent::populateState('s.name', 'asc');
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
    $id .= ':'.$this->getState('filter.delivery_type');
    $id .= ':'.$this->getState('filter.plugin_element');

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
    $query->select($this->getState('list.select', 's.id, s.name, s.created, s.published, s.weight_type, s.weight_unit,'.
				   's.min_product, s.max_product, s.min_weight, s.max_weight, s.delivery_type,'.
				   's.plugin_element, s.ordering, s.created_by, s.checked_out, s.checked_out_time'));

    $query->from('#__ketshop_shipping AS s');

    // Get the creator name.
    $query->select('u.name AS creator');
    $query->join('LEFT', '#__users AS u ON u.id = s.created_by');


    // Filter by title search.
    $search = $this->getState('filter.search');
    if(!empty($search)) {
      if(stripos($search, 'id:') === 0) {
	$query->where('s.id = '.(int) substr($search, 3));
      }
      else {
	$search = $db->Quote('%'.$db->escape($search, true).'%');
	$query->where('(s.name LIKE '.$search.')');
      }
    }

    // Filter by delivery type.
    $deliveryType = $this->getState('filter.delivery_type');
    if(!empty($deliveryType)) {
      $query->where('s.delivery_type='.$db->Quote($deliveryType));
    }

    // Filter by plugin.
    $pluginElement = $this->getState('filter.plugin_element');
    if(!empty($pluginElement)) {
      $query->where('s.plugin_element='.$db->Quote($pluginElement));
    }

    // Filter by publication state.
    $published = $this->getState('filter.published');
    if(is_numeric($published)) {
      $query->where('s.published='.(int)$published);
    }
    elseif($published === '') {
      $query->where('(s.published IN (0, 1))');
    }

    // Join over the users for the checked out user.
    $query->select('uc.name AS editor');
    $query->join('LEFT', '#__users AS uc ON uc.id=s.checked_out');

    // Filter by creator.
    $userId = $this->getState('filter.user_id');
    if(is_numeric($userId)) {
      $type = $this->getState('filter.user_id.include', true) ? '= ' : '<>';
      $query->where('s.created_by'.$type.(int) $userId);
    }

    // Add the list to the sort.
    $orderCol = $this->state->get('list.ordering', 's.name');
    $orderDirn = $this->state->get('list.direction'); //asc or desc

    $query->order($db->escape($orderCol.' '.$orderDirn));

    return $query;
  }
}


