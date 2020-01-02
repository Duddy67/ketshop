<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class KetshopHelper
{
  /**
   * Creates the tabs bar ($viewName = name of the active view).
   *
   * @param   string  $viewName  The name of the view to display.
   *
   * @return  void
   *
   */
  public static function addSubmenu($viewName)
  {
    $submenus = array('products', 'attributes', 'taxes', 'currencies', 'countries', 'price_rules', 'coupons', 'orders', 
                      'customers', 'payment_modes', 'shippings', 'translations', 'manufacturers');

    foreach($submenus as $submenu) {
      JHtmlSidebar::addEntry(JText::_('COM_KETSHOP_SUBMENU_'.strtoupper($submenu)),
			     'index.php?option=com_ketshop&view='.str_replace('_', '', $submenu),
			     $viewName == str_replace('_', '', $submenu));
    }

    JHtmlSidebar::addEntry(JText::_('COM_KETSHOP_SUBMENU_CATEGORIES'),
		           'index.php?option=com_categories&extension=com_ketshop', $viewName == 'categories');

    if($viewName == 'categories') {
      $document = JFactory::getDocument();
      $document->setTitle(JText::_('COM_KETSHOP_ADMINISTRATION_CATEGORIES'));
    }
  }


  /**
   * Gets the list of the allowed actions for the user.
   *
   * @param   array    $catIds    The category ids to check against.
   *
   * @return  JObject             The allowed actions for the current user.
   *
   */
  public static function getActions($catIds = array())
  {
    $user = JFactory::getUser();
    $result = new JObject;

    $actions = array('core.admin', 'core.manage', 'core.create', 'core.edit',
		     'core.edit.own', 'core.edit.state', 'core.delete');

    // Gets from the core the user's permission for each action.
    foreach($actions as $action) {
      // Checks permissions against the component. 
      if(empty($catIds)) { 
	$result->set($action, $user->authorise($action, 'com_ketshop'));
      }
      else {
	// Checks permissions against the component categories.
	foreach($catIds as $catId) {
	  if($user->authorise($action, 'com_ketshop.category.'.$catId)) {
	    $result->set($action, $user->authorise($action, 'com_ketshop.category.'.$catId));
	    break;
	  }

	  $result->set($action, $user->authorise($action, 'com_ketshop.category.'.$catId));
	}
      }
    }

    return $result;
  }


  /**
   * Builds the user list for the filter.
   *
   * @param   string   $itemName    The name of the item to check the users against.
   *
   * @return  object                The list of the users.
   *
   */
  public static function getUsers($itemName)
  {
    // Create a new query object.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('u.id AS value, u.name AS text');
    $query->from('#__users AS u');
    // Gets only the names of users who have created items (ie: creators).
    $query->join('INNER', '#__ketshop_'.$itemName.' AS i ON i.created_by = u.id');
    $query->group('u.id');
    $query->order('u.name');

    // Setup the query
    $db->setQuery($query);

    // Returns the result
    return $db->loadObjectList();
  }


  /**
   * Checks that the given category is not used as main category by one or more products.
   *
   * @param   integer    $pk	The category id.
   *
   * @return  boolean		True if the category is not used as main category, false otherwise.
   */
  public static function checkMainCategory($pk)
  {
    return self::checkMainCategories(array($pk));
  }


  /**
   * Checks that the given categories are not used as main category by one or more products.
   *
   * @param   array    $pks		An array of category IDs.
   *
   * @return  boolean			True if the categories are not used as main category, false otherwise.
   */
  public static function checkMainCategories($pks)
  {
    $ids = array();
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    foreach($pks as $pk) {
      // Finds node and all children keys
      $query->clear();
      $query->select('c.id')
	    ->from('#__categories AS node')
	    ->leftJoin('#__categories AS c ON node.lft <= c.lft AND c.rgt <= node.rgt')
	    ->where('node.id = '.(int)$pk);
      $db->setQuery($query);
      $results = $db->loadColumn();

      $ids = array_unique(array_merge($ids,$results), SORT_REGULAR);
    }

    // Checks that no product item is using one of the categories as main category.
    $query->clear();
    $query->select('COUNT(*)')
	  ->from('#__ketshop_product')
	  ->where('catid IN('.implode(',', $ids).')');
    $db->setQuery($query);

    if($db->loadResult()) {
      JFactory::getApplication()->enqueueMessage(JText::_('COM_KETSHOP_WARNING_CATEGORY_USED_AS_MAIN_CATEGORY'), 'warning');
      return false;
    }

    return true;
  }


  /**
   * Function that converts categories paths into paths of names
   * N.B: Adapted from the function used with tags. libraries/src/Helper/TagsHelper.php
   *
   * @param   array  $categories  Array of categories
   *
   * @return  array
   *
   * @since   3.1
   */
  public static function convertPathsToNames($categories)
  {
    // We will replace path aliases with tag names
    if ($categories)
    {
      // Create an array with all the aliases of the results
      $aliases = array();

      foreach ($categories as $category)
      {
	if (!empty($category->path))
	{
	  if ($pathParts = explode('/', $category->path))
	  {
	    $aliases = array_merge($aliases, $pathParts);
	  }
	}
      }

      // Get the aliases titles in one single query and map the results
      if ($aliases)
      {
	// Remove duplicates
	$aliases = array_unique($aliases);

	$db = JFactory::getDbo();

	$query = $db->getQuery(true)
		->select('alias, title')
		->from('#__categories')
		->where('extension="com_ketshop"')
		->where('alias IN (' . implode(',', array_map(array($db, 'quote'), $aliases)) . ')');
	$db->setQuery($query);

	try
	{
	  $aliasesMapper = $db->loadAssocList('alias');
	}
	catch (RuntimeException $e)
	{
	  return false;
	}

	// Rebuild the items path
	if ($aliasesMapper)
	{
	  foreach ($categories as $category)
	  {
	    $namesPath = array();

	    if (!empty($category->path))
	    {
	      if ($pathParts = explode('/', $category->path))
	      {
		foreach ($pathParts as $i => $alias)
		{
		  if (isset($aliasesMapper[$alias]))
		  {
		    $namesPath[] = $aliasesMapper[$alias]['title'];
		  }
		  else
		  {
		    $namesPath[] = $alias;
		  }

		  // Unpublished categories are put into square bracket.
		  if($category->published == 0 && ($i + 1) == $category->level) {
		    $namesPath[$i] = '['.$namesPath[$i].']';
		  }
		}

		$category->text = implode('/', $namesPath);
	      }
	    }
	  }
	}
      }
    }

    return $categories;
  }


  /**
   * Updates a mapping table according to the variables passed as arguments.
   *
   * @param string  $table   The name of the table to update (eg: #__table_name).
   * @param array   $columns Array of table's column. Important: Primary key name must be set as the first array's element.
   * @param array   $data    Array of JObject containing the column values, (values order must match the column order).
   * @param integer $pkId    The common id which hold the data rows together.
   *
   * @return void
   */
  public static function updateMappingTable($table, $columns, $data, $pkId)
  {
    // Ensures first we have a valid primary key.
    if(isset($columns[0]) && !empty($columns[0])) {
      $pk = $columns[0];
    }
    else {
      return;
    }

    // Creates a new query object.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    // Deletes all the previous items linked to the primary key.
    $query->delete($db->quoteName($table));
    $query->where($pk.'='.(int)$pkId);
    $db->setQuery($query);
    $db->execute();

    // If no item has been defined no need to go further.
    if(count($data)) {
      // List of the numerical fields for which no quotes must be used.
      $unquoted = array('id','prod_id','var_id','attrib_id','filter_id','shipping_id',
	                'cost','bundle_id','quantity','ordering','published','ordering');

      // Builds the VALUES clause of the INSERT MySQL query.
      $values = array();

      foreach($data as $itemValues) {
	$row = '';
	foreach($itemValues as $key => $value) {
	  if(in_array($key, $unquoted)) {
	    // Don't quote the numerical values.
	    $row .= $value.',';
	  }
	  else {
	    $row .= $db->Quote($value).',';
	  }
	}

	// Removes comma from the end of the string.
	$row = substr($row, 0, -1);
	// Inserts a new row in the "values" clause.
	$values[] = $row;
      }

      // Inserts a new row for each item linked to the primary id(s).
      $query->clear();
      $query->insert($db->quoteName($table));
      $query->columns($columns);
      $query->values($values);
      $db->setQuery($query);
      $db->execute();
    }

    return;
  }
}

