<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class KetshopModelPaymentmodes extends JModelList
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
	      'id', 'pm.id',
	      'name', 'pm.name',
	      'plugin_element', 'pm.plugin_element',
	      'plugin_name',
	      'created', 'pm.created',
	      'created_by', 'pm.created_by',
	      'published', 'pm.published',
	      'creator', 'user_id',
	      'ordering', 'pm.ordering',
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

    // List state information.
    parent::populateState('pm.name', 'asc');
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
    $query->select($this->getState('list.select', 'pm.id, pm.name, pm.created, pm.published,pm.plugin_element,'.
				   'pm.ordering, pm.created_by, pm.checked_out, pm.checked_out_time'));

    $query->from('#__ketshop_payment_mode AS pm');

    // Gets the creator name.
    $query->select('u.name AS creator');
    $query->join('LEFT', '#__users AS u ON u.id = pm.created_by');

    // Gets the plugin name.
    $query->select('e.name AS plugin_name');
    $query->join('LEFT', '#__extensions AS e ON e.element=pm.plugin_element AND e.folder="ketshoppayment"');

    // Filter by title search.
    $search = $this->getState('filter.search');
    if(!empty($search)) {
      if(stripos($search, 'id:') === 0) {
	$query->where('pm.id = '.(int) substr($search, 3));
      }
      else {
	$search = $db->Quote('%'.$db->escape($search, true).'%');
	$query->where('(pm.name LIKE '.$search.')');
      }
    }

    // Filter by publication state.
    $published = $this->getState('filter.published');
    if(is_numeric($published)) {
      $query->where('pm.published='.(int)$published);
    }
    elseif($published === '') {
      $query->where('(pm.published IN (0, 1))');
    }

    // Join over the users for the checked out user.
    $query->select('uc.name AS editor');
    $query->join('LEFT', '#__users AS uc ON uc.id=pm.checked_out');

    // Filter by creator.
    $userId = $this->getState('filter.user_id');
    if(is_numeric($userId)) {
      $type = $this->getState('filter.user_id.include', true) ? '= ' : '<>';
      $query->where('pm.created_by'.$type.(int) $userId);
    }

    // Add the list to the sort.
    $orderCol = $this->state->get('list.ordering', 'pm.name');
    $orderDirn = $this->state->get('list.direction'); //asc or desc

    $query->order($db->escape($orderCol.' '.$orderDirn));

    return $query;
  }
}


