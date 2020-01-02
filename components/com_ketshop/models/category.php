<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JLoader::register('ProductTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/product.php');


/**
 * KetShop Component Model
 *
 * @package     Joomla.Site
 * @subpackage  com_ketshop
 */
class KetshopModelCategory extends JModelList
{
  use ProductTrait;
  
  /**
   * Category items data
   *
   * @var array
   */
  protected $_item = null;

  protected $_products = null;

  protected $_siblings = null;

  protected $_children = null;

  protected $_parent = null;

  /**
   * Model context string.
   *
   * @var		string
   */
  protected $_context = 'com_ketshop.category';

  /**
   * The category that applies.
   *
   * @access    protected
   * @var        object
   */
  protected $_category = null;

  /**
   * The list of other product categories.
   *
   * @access    protected
   * @var        array
   */
  protected $_categories = null;

  /**
   * The POST variables get from the form. 
   *
   * @access    protected
   * @var        array
   */
  protected $post = null;

  /**
   * The snitch object.
   *
   * @access    protected
   * @var        object
   */
  protected $snitch = null;

  /**
   * Flag to determine whether filters have to be cleared.
   *
   * @access    protected
   * @var        boolean
   */
  protected $filter_reset = true;


  /**
   * Method to get a list of items.
   *
   * @return  mixed  An array of objects on success, false on failure.
   */

  /**
   * Constructor.
   *
   * @param   array  An optional associative array of configuration settings.
   * @see     JController
   * @since   1.6
   */
  public function __construct($config = array())
  {
    if(empty($config['filter_fields'])) {
      $config['filter_fields'] = array(
	      'id', 'p.id',
	      'name', 'p.name',
	      'creator', 'p.creator',
	      'created', 'p.created',
	      'catid', 'p.catid', 'cm.cat_id', 'category_title',
	      'modified', 'p.modified',
	      'published', 'p.published',
	      'ordering', 'cm.ordering',
	      'publish_up', 'p.publish_up',
	      'publish_down', 'p.publish_down'
      );
    }

    parent::__construct($config);
  }


  /**
   * Method to auto-populate the model state.
   *
   * Product. Calling getState in this method will result in recursion.
   *
   * @since   1.6
   */
  protected function populateState($ordering = null, $direction = null)
  {
    $app = JFactory::getApplication('site');
    $this->post = $app->input->post->getArray();

    // Get and set the current category id.
    $pk = $app->input->getInt('id');
    $this->setState('category.id', $pk);

    // getParams function return global parameters overrided by the menu parameters (if any).
    // N.B: Some specific parameters of this menu are not returned.
    $params = $app->getParams();

    $menuParams = new JRegistry;

    // Get the menu with its specific parameters.
    if($menu = $app->getMenu()->getActive()) {
      $menuParams->loadString($menu->params);
    }

    // Merge Global and Menu Item params into a new object.
    $mergedParams = clone $menuParams;
    $mergedParams->merge($params);

    // Load the parameters in the session.
    $this->setState('params', $mergedParams);

    // process show_noauth parameter

    // The user is not allowed to see the registered products unless he has the proper view permissions.
    if(!$params->get('show_noauth')) {
      // Set the access filter to true. This way the SQL query checks against the user
      // view permissions and fetchs only the products this user is allowed to see.
      $this->setState('filter.access', true);
    }
    // The user is allowed to see any of the registred products (ie: intro_text as a teaser). 
    else {
      // The user is allowed to see all the products or some of them.
      // All of the products are returned and it's up to thelayout to 
      // deal with the access (ie: redirect the user to login form when Read more
      // button is clicked).
      $this->setState('filter.access', false);
    }

    // Set limit for query. If list, use parameter. If blog, add blog parameters for limit.
    // Important: The pagination limit box must be hidden to use the limit value based upon the layout.
    if(!$params->get('show_pagination_limit') && (($app->input->get('layout') === 'blog') || $params->get('layout_type') === 'blog')) {
      $limit = $params->get('num_leading_products') + $params->get('num_intro_products') + $params->get('num_links');
    }
    // list layout or blog layout with the pagination limit box shown.
    else { 
      // Get the number of songs to display per page.
      $limit = $params->get('display_num', 10);

      if($params->get('show_pagination_limit')) {
	// Gets the limit value from the pagination limit box.
	$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $limit, 'uint');
      }
    }

    $this->setState('list.limit', $limit);

    // Get the limitstart variable (used for the pagination) from the form variable.
    $limitstart = $app->input->get('limitstart', 0, 'uint');
    $this->setState('list.start', $limitstart);

    // Checks the snitch to set the state of the filters.
    $this->snitch = ShopHelper::getSnitch();
    // The page is reloaded or the user has previously entered a product page then he's
    // now coming back. 
    // N.B: the filter_reset flag is set to true by default.
    if($this->snitch->from == 'category.'.$pk || preg_match('#^product\.[0-9]+\.'.$pk.'#', $this->snitch->from)) {
      // Keeps the filter selecting.
      $this->filter_reset = false;
    }

    $filters = array('filter_search', 'filter_ordering', 'filter_manufacturer');
    foreach($filters as $filter) {
      $this->setFilterState($filter, $pk);
    }

    // Sets the values of the attribute filters (if any).
    if($params->get('attribute_filter_ids') !== null) {
      $attribIds = $params->get('attribute_filter_ids');

      $forceDelete = false;
      if(isset($this->post['filter_cleared']) && $this->post['filter_cleared'] == 'filter_attributes') {
	// All of the attribute filters have to be cleared.
	$forceDelete = true;
      }

      foreach($attribIds as $attribId) {
	$this->setFilterState('filter_attribute_'.$attribId, $pk, $forceDelete);
      }
    }

    // Updates the snitch.
    $this->snitch->from = 'category.'.$pk;
    $this->snitch->limit_start = $limitstart;
    ShopHelper::setSnitch($this->snitch);

    $user = JFactory::getUser();
    $asset = 'com_ketshop';

    if($pk) {
      $asset .= '.category.'.$pk;
    }

    // Check against the category permissions.
    if((!$user->authorise('core.edit.state', $asset)) && (!$user->authorise('core.edit', $asset))) {
      // limit to published for people who can't edit or edit.state.
      $this->setState('filter.published', 1);

      // Filter by start and end dates.
      $this->setState('filter.publish_date', true);
    }
    else {
      // User can access published, unpublished and archived products.
      $this->setState('filter.published', array(0, 1, 2));
    }

    $this->setState('filter.language', JLanguageMultilang::isEnabled());
  }


  /**
   * Method to get a list of items.
   *
   * @return  mixed  An array of objects on success, false on failure.
   */
  public function getItems()
  {
    // Invoke the parent getItems method (using the getListQuery method) to get the main list
    $items = parent::getItems();
    $input = JFactory::getApplication()->input;

    // Get some user data.
    $user = JFactory::getUser();
    $userId = $user->get('id');
    $guest = $user->get('guest');
    $groups = $user->getAuthorisedViewLevels();
    // Get the global params
    $globalParams = JComponentHelper::getParams('com_ketshop', true);

    // Convert the params field into an object, saving original in _params
    foreach($items as $item) {
      // Get the product parameters only.
      $productParams = new JRegistry;
      $productParams->loadString($item->params);
      // Set the params attribute, ie: the merged global and menu parameters set
      // in the populateState function.
      $item->params = clone $this->getState('params');

      // For Blog layout, product params override menu item params only if menu param='use_product'.
      // Otherwise, menu item params control the layout.
      // If menu item is 'use_product' and there is no product param, use global.
      if($input->getString('layout') == 'blog' || $this->getState('params')->get('layout_type') == 'blog') {
	// Create an array of just the params set to 'use_product'
	$menuParamsArray = $this->getState('params')->toArray();
	$productArray = array();

	foreach($menuParamsArray as $key => $value) {
	  if($value === 'use_product') {
	    // If the product has a value, use it
	    if($productParams->get($key) != '') {
	      // Get the value from the product
	      $productArray[$key] = $productParams->get($key);
	    }
	    else {
	      // Otherwise, use the global value
	      $productArray[$key] = $globalParams->get($key);
	    }
	  }
	}

	// Merge the selected product params
	if(count($productArray) > 0) {
	  $productParams = new JRegistry;
	  $productParams->loadArray($productArray);
	  $item->params->merge($productParams);
	}
      }
      else { // Default layout (list).
	// Merge all of the product params.
	// N.B: Product params (if they are defined) override global/menu params.
	$item->params->merge($productParams);
      }

      // Compute the asset access permissions.
      // Technically guest could edit a product, but lets not check that to improve performance a little.
      if(!$guest) {
	$asset = 'com_ketshop.product.'.$item->id;

	// Check general edit permission first.
	if($user->authorise('core.edit', $asset)) {
	  $item->params->set('access-edit', true);
	}
	// Now check if edit.own is available.
	elseif(!empty($userId) && $user->authorise('core.edit.own', $asset)) {
	  // Check for a valid user and that they are the owner.
	  if($userId == $item->created_by) {
	    $item->params->set('access-edit', true);
	  }
	}
      }

      $access = $this->getState('filter.access');
      // Set the access view parameter.
      if($access) {
	// If the access filter has been set, we already have only the products this user can view.
	$item->params->set('access-view', true);
      }
      else { // If no access filter is set, the layout takes some responsibility for display of limited information.
	if($item->catid == 0 || $item->category_access === null) {
	  // In case the product is not linked to a category, we just check permissions against the product access.
	  $item->params->set('access-view', in_array($item->access, $groups));
	}
	else { // Check the user permissions against the product access as well as the category access.
	  $item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
	}
      }

      // Set the type of date to display, (default layout only).
      if($this->getState('params')->get('layout_type') != 'blog'
	  && $this->getState('params')->get('list_show_date')
	  && $this->getState('params')->get('order_date')) {
	switch($this->getState('params')->get('order_date')) {
	  case 'modified':
		  $item->displayDate = $item->modified;
		  break;

	  case 'published':
		  $item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
		  break;

	  default: // created
		  $item->displayDate = $item->created;
	}
      }

      // Get the tags
      $item->tags = new JHelperTags;
      $item->tags->getItemTags('com_ketshop.product', $item->id);

      // Fetches all the variants of the product.
      $model = JModelLegacy::getInstance('Product', 'KetshopModel');
      $item->variants = $model->getProductVariants($item->id);
      $item->nb_variants = count($item->variants);

      foreach($item->variants as $key => $variant) {
	// Gets the variant attributes.
	$item->variants[$key]->attributes = $this->getAttributeData($item->id, $variant->var_id);
	$item->variants = $model->setVariantImages($item->variants, $item->id);
      }

      $item->categories = $model->getCategories($item->id);
      $item->cat_ids = $model->getCategoryIds($item->id);
    }

    return $items;
  }



  /**
   * Method to build an SQL query to load the list data (product items).
   *
   * @return  string    An SQL query
   * @since   1.6
   */
  protected function getListQuery()
  {
    $user = JFactory::getUser();
    $groups = implode(',', $user->getAuthorisedViewLevels());

    // Create a new query object.
    $db = $this->getDbo();
    $query = $db->getQuery(true);

    // Select required fields from the categories.
    $query->select($this->getState('list.select', 'p.id,p.name,p.alias,p.intro_text,p.full_text,p.catid,p.published,p.shippable,'.
				   'pv.name AS var_name,pv.price_with_tax,pv.code,pv.stock,pv.sales,'.
				   'm.name AS manufacturer,t.name AS tax_name, t.rate AS tax_rate,'. 
				   'i.src AS img_src, i.width AS img_width, i.height AS img_height, i.alt AS img_alt,'.
	                           'p.checked_out,p.checked_out_time,p.created,p.created_by,p.access,p.params,p.metadata,'.
				   'p.metakey,p.metadesc,p.hits,p.publish_up,p.publish_down,p.language,p.modified,p.modified_by,'.
				   'p.dimension_unit,p.weight_unit,p.img_reduction_rate,'.
				   'IF(p.new_until > NOW(),1,0) AS is_new'))
	  ->from($db->quoteName('#__ketshop_product').' AS p')
	  // Join over the mapping table to get the product ids.
	  ->join('INNER', '#__ketshop_product_cat_map AS cm on cm.product_id = p.id')
	  // Gets only the basic variant of the product (ie: the first in the list).
	  ->join('INNER', '#__ketshop_product_variant AS pv ON pv.prod_id=p.id AND pv.ordering=1')
	  // Join over the manufacturer.
	  ->join('LEFT', '#__ketshop_manufacturer AS m ON m.id=p.manufact_id')
	  // Join over the tax.
	  ->join('LEFT', '#__ketshop_tax AS t ON t.id=p.tax_id')
	  // Gets the main image of the product (ie: the first in the list).
	  ->join('LEFT', '#__ketshop_prod_image AS i ON i.prod_id = p.id AND i.ordering=1')
	  // Display products linked to the current category.
	  ->where('cm.cat_id='.(int)$this->getState('category.id'));

    // Join on category table to get the main item category.
    $query->select('ca.title AS category_title, ca.alias AS category_alias, ca.access AS category_access')
	  ->join('LEFT', '#__categories AS ca on ca.id = p.catid');

    // Join over the categories to get parent category titles
    $query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
	  ->join('LEFT', '#__categories as parent ON parent.id = ca.parent_id');

    // Join over the creators.
    $query->select('us.name AS creator')
	  ->join('LEFT', '#__users AS us ON us.id = p.created_by');

    // Join over the asset groups.
    $query->select('al.title AS access_level');
    $query->join('LEFT', '#__viewlevels AS al ON al.id = p.access');

    // Filter by access level.
    if($access = $this->getState('filter.access')) {
      $query->where('p.access IN ('.$groups.')')
	    ->where('ca.access IN ('.$groups.')');
    }

    // Filter by state
    $published = $this->getState('filter.published');
    if(is_numeric($published)) {
      // User is only allowed to see published products.
      $query->where('p.published='.(int)$published);
    }
    elseif(is_array($published)) {
      // User is allowed to see products with different states.
      JArrayHelper::toInteger($published);
      $published = implode(',', $published);
      $query->where('p.published IN ('.$published.')');
    }

    // Do not show expired products to users who can't edit or edit.state.
    if($this->getState('filter.publish_date')) {
      // Filter by start and end dates.
      $nullDate = $db->quote($db->getNullDate());
      $nowDate = $db->quote(JFactory::getDate()->toSql());

      $query->where('(p.publish_up = '.$nullDate.' OR p.publish_up <= '.$nowDate.')')
	    ->where('(p.publish_down = '.$nullDate.' OR p.publish_down >= '.$nowDate.')');
    }

    // Filter by language
    if($this->getState('filter.language')) {
      $query->where('p.language IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
    }

    // Filter by search in title
    $filterSearch = $this->getState('list.filter_search');
    // Get the field to search by.
    $field = $this->getState('params')->get('filter_field');
    if(!empty($filterSearch)) {
      $filterSearch = $db->quote('%'.$db->escape($filterSearch, true).'%');
      $query->where('(p.'.$field.' LIKE '.$filterSearch.')');
    }

    // Filters by variant attributes.
    if($this->getState('params')->get('attribute_filter_ids') !== null) {
      $attribIds = $this->getState('params')->get('attribute_filter_ids');

      // Builds a join query for each attribute.
      foreach($attribIds as $key => $attribId) {
	$optionValues = $this->getState('list.filter_attribute_'.$attribId);

	if(!empty($optionValues)) {
	  $option = '(';
	  // Creates a LIKE clause for each option value.
	  foreach($optionValues as $value) {
	    $option .= 'pva'.$key.'.option_value LIKE '.$db->Quote('%'.$value.'%').' OR ';
	  }

	  // Removes the last OR from the string.
	  $option = substr($option, 0, -4);
	  $option .= ')';

	  $query->join('INNER', '(SELECT * FROM #__ketshop_var_attrib) AS pva'.$key.' ON pva'.$key.'.prod_id=p.id '.
	                        'AND pva'.$key.'.attrib_id='.$attribId.' AND '.$option);
	  // Prevents duplicates when several variants of the same product match the
	  // values.
	  $query->group('p.id');
	}
      }
    }

    // Filters by manufacturers.
    $filterManufacturer = $this->getState('list.filter_manufacturer');
    if(!empty($filterManufacturer)) {
      $options = '(';
      foreach($filterManufacturer as $option) {
	$options .= 'p.manufact_id='.$option.' OR ';
      }

      // Removes the last OR from the string.
      $options = substr($options, 0, -4);
      $options .= ')';
      $query->where($options);
    }

    // Get the products ordering by default set in the menu options. (N.B: sec stands for secondary). 
    $productOrderBy = $this->getState('params')->get('orderby_sec', 'rdate');
    // If products are sorted by date (ie: date, rdate), order_date defines
    // which type of date should be used (ie: created, modified or publish_up).
    $productOrderDate = $this->getState('params')->get('order_date');
    // Get the field to use in the ORDER BY clause according to the orderby_sec option.
    $orderBy = KetshopHelperQuery::orderbySecondary($productOrderBy, $productOrderDate);

    // Filter by order (eg: the select list set by the end user).
    $filterOrdering = $this->getState('list.filter_ordering');
    // If the end user has define an order, we override the ordering by default.
    if(!empty($filterOrdering)) {
      $orderBy = KetshopHelperQuery::orderbySecondary($filterOrdering, $productOrderDate);
    }

    $query->order($orderBy);

    return $query;
  }


  /**
   * Method to get category data for the current category
   *
   * @param   integer  An optional ID
   *
   * @return  object
   * @since   1.5
   */
  public function getCategory()
  {
    if(!is_object($this->_item)) {
      $app = JFactory::getApplication();
      $menu = $app->getMenu();
      $active = $menu->getActive();
      $params = new JRegistry;

      if($active) {
	$params->loadString($active->params);
      }

      $options = array();
      $options['countItems'] = $params->get('show_cat_num_products_cat', 1) || $params->get('show_empty_categories', 0);
      $categories = JCategories::getInstance('Ketshop', $options);
      $this->_item = $categories->get($this->getState('category.id', 'root'));

      // Compute selected asset permissions.
      if(is_object($this->_item)) {
	$user = JFactory::getUser();
	$asset = 'com_ketshop.category.'.$this->_item->id;

	// Check general create permission.
	if($user->authorise('core.create', $asset)) {
	  $this->_item->getParams()->set('access-create', true);
	}

	$this->_children = $this->_item->getChildren();
	$this->_parent = false;

	if($this->_item->getParent()) {
	  $this->_parent = $this->_item->getParent();
	}

	$this->_rightsibling = $this->_item->getSibling();
	$this->_leftsibling = $this->_item->getSibling(false);
      }
      else {
	$this->_children = false;
	$this->_parent = false;
      }
    }

    // Get the tags
    $this->_item->tags = new JHelperTags;
    $this->_item->tags->getItemTags('com_ketshop.category', $this->_item->id);

    return $this->_item;
  }

  /**
   * Get the parent category
   *
   * @param   integer  An optional category id. If not supplied, the model state 'category.id' will be used.
   *
   * @return  mixed  An array of categories or false if an error occurs.
   */
  public function getParent()
  {
    if(!is_object($this->_item)) {
      $this->getCategory();
    }

    return $this->_parent;
  }

  /**
   * Get the sibling (adjacent) categories.
   *
   * @return  mixed  An array of categories or false if an error occurs.
   */
  function &getLeftSibling()
  {
    if(!is_object($this->_item)) {
      $this->getCategory();
    }

    return $this->_leftsibling;
  }

  function &getRightSibling()
  {
    if(!is_object($this->_item)) {
      $this->getCategory();
    }

    return $this->_rightsibling;
  }

  /**
   * Get the child categories.
   *
   * @param   integer  An optional category id. If not supplied, the model state 'category.id' will be used.
   *
   * @return  mixed  An array of categories or false if an error occurs.
   * @since   1.6
   */
  function &getChildren()
  {
    if(!is_object($this->_item)) {
      $this->getCategory();
    }

    // Order subcategories
    if(count($this->_children)) {
      $params = $this->getState()->get('params');

      if($params->get('orderby_pri') == 'alpha' || $params->get('orderby_pri') == 'ralpha') {
	jimport('joomla.utilities.arrayhelper');
	JArrayHelper::sortObjects($this->_children, 'title', ($params->get('orderby_pri') == 'alpha') ? 1 : -1);
      }
    }

    return $this->_children;
  }

  /**
   * Increment the hit counter for the category.
   *
   * @param   int  $pk  Optional primary key of the category to increment.
   *
   * @return  boolean True if successful; false otherwise and internal error set.
   *
   * @since   3.2
   */
  public function hit($pk = 0)
  {
    $input = JFactory::getApplication()->input;
    $hitcount = $input->getInt('hitcount', 1);

    if($hitcount) {
      $pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

      $table = JTable::getInstance('Category', 'JTable');
      $table->load($pk);
      $table->hit($pk);
    }

    return true;
  }


  /**
   * Returns product name suggestions for a given search request.
   *
   * @param   int  $pk  	Optional primary key of the current category.
   * @param   string $search 	The request search to get the matching title suggestions.
   *
   * @return  mixed		An array of suggestion results.
   *
   */
  public function getAutocompleteSuggestions($pk = null, $search)
  {
    $pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');
    $results = array();

    $db = $this->getDbo();
    $query = $db->getQuery(true);
    $query->select('name AS value, id AS data')
	  ->from('#__ketshop_product')
	  ->join('INNER', '#__ketshop_product_cat_map ON product_id=id')
	  ->where('cat_id='.(int)$pk)
	  ->where('published=1')
	  ->where('name LIKE '.$db->Quote($search.'%'))
	  ->order('name DESC');
    $db->setQuery($query);
    // Requested to get the JQuery autocomplete working properly.
    $results['suggestions'] = $db->loadAssocList();

    return $results;
  }


  /**
   * Returns the manufacturer id and name of each product belonging to the current
   * category.
   *
   * @param   int  $pk  	Optional primary key of the current category.
   *
   * @return  mixed		An array of options.
   *
   */
  public function getManufacturerOptions($pk = null)
  {
    $pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

    $db = $this->getDbo();
    $query = $db->getQuery(true);
    $query->select('m.id AS value, m.name AS text')
	  ->from('#__ketshop_product AS p')
	  ->join('INNER', '#__ketshop_product_cat_map ON product_id=id')
	  ->join('INNER', '#__ketshop_manufacturer AS m ON m.id=p.manufact_id')
	  ->where('cat_id='.(int)$pk)
	  ->where('p.published=1')
	  ->group('m.id')
	  ->order('m.name DESC');
    $db->setQuery($query);

    return  $db->loadObjectList();
  }


  /**
   * Sets the state of a given filter according to both the snitch and POST data. 
   *
   * @param   string  $filterName  	The name of the filter.
   * @param   int     $pk  	        The primary key of the current category.
   * @param   boolean $forceDelete  	If set to true the filter will be cleared regardless of its state.
   *
   * @return  void
   *
   */
  private function setFilterState($filterName, $pk, $forceDelete = false)
  {
    // The page is loaded for the first time or the filter has just been cleared.
    if(($this->filter_reset || $forceDelete || 
       (!$this->filter_reset && isset($this->post['filter_cleared']) && $this->post['filter_cleared'] == $filterName)) && !in_array($filterName, $this->snitch->empty_filters)) {
       // Appends the filter to the empty filter list.
       // N.B: The empty filter list is used to deal with the tricky behavior of the multiselection.
       //      See the getUserStateFromRequest parent function for more details. 
      $this->snitch->empty_filters[] = $filterName;
    }
    // The page is reloaded and one or more options of the filter have been selected. 
    elseif($this->snitch->from == 'category.'.$pk && isset($this->post[$filterName])) {
      // Removes the filter possibly stored in the empty filter list.
      if(($key = array_search($filterName, $this->snitch->empty_filters)) !== false) {
	unset($this->snitch->empty_filters[$key]);
      }
    }

    if($this->filter_reset || in_array($filterName, $this->snitch->empty_filters)) {
      // Empties the Joomla session variable.
      $this->setState('list.'.$filterName, null);
    }
    else {
      // Appends the current filter state to the Joomla session variable. 
      $this->setState('list.'.$filterName, $this->getUserStateFromRequest($this->context.'.list.'.$filterName, $filterName));
    }
  }
}

