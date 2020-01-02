<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 

JLoader::register('ProductTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/product.php');
use Joomla\Utilities\ArrayHelper;


class KetshopModelProducts extends JModelList
{
  use ProductTrait;

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
      $config['filter_fields'] = array('id', 'p.id',
				       'name', 'p.name', 
				       'alias', 'p.alias',
				       'created', 'p.created', 
				       'created_by', 'p.created_by',
				       'published', 'p.published', 
			               'access', 'p.access', 'access_level',
				       'creator', 'user_id',
				       'ordering', 'cm.ordering',
				       'type', 'p.type', 'product_type',
				       'stock', 'pv.stock',
				       'language', 'p.language',
				       'hits', 'p.hits',
				       'catid', 'p.catid', 'category_id',
				       'tag'
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
   * Product. Calling getState in this method will result in recursion.
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

    $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
    $this->setState('filter.published', $published);

    $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language');
    $this->setState('filter.language', $language);

    $productType = $this->getUserStateFromRequest($this->context.'.filter.product_type', 'filter_product_type');
    $this->setState('filter.product_type', $productType);

    // Used with the multiple list selections.
    $formSubmited = $app->input->post->get('form_submited');

    $categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id');
    $userId = $this->getUserStateFromRequest($this->context.'.filter.user_id', 'filter_user_id');
    $tag = $this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag');
    $access = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access');

    if($formSubmited) {
      // Gets the current value of the fields.
      $categoryId = $app->input->post->get('category_id');
      $this->setState('filter.category_id', $categoryId);

      $userId = $app->input->post->get('user_id');
      $this->setState('filter.user_id', $userId);

      $tag = $app->input->post->get('tag');
      $this->setState('filter.tag', $tag);

      $access = $app->input->post->get('access');
      $this->setState('filter.access', $access);
    }

    // List state information.
    parent::populateState('p.name', 'asc');

    // Force a language
    $forcedLanguage = $app->input->get('forcedLanguage');

    if(!empty($forcedLanguage)) {
      $this->setState('filter.language', $forcedLanguage);
      $this->setState('filter.forcedLanguage', $forcedLanguage);
    }
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
    $id .= ':'.serialize($this->getState('filter.access'));
    $id .= ':'.$this->getState('filter.published');
    $id .= ':'.serialize($this->getState('filter.user_id'));
    $id .= ':'.serialize($this->getState('filter.category_id'));
    $id .= ':'.serialize($this->getState('filter.tag'));
    $id .= ':'.$this->getState('filter.language');
    $id .= ':'.$this->getState('filter.product_type');

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
    //Create a new JDatabaseQuery object.
    $db = $this->getDbo();
    $query = $db->getQuery(true);
    $user = JFactory::getUser();
    // Variable sent from price rule or bundle dynamic items in order to display only
    // the selected product type in the product modal window (ie: normal or bundle).
    $dynamicItemType = JFactory::getApplication()->input->get->get('dynamic_item_type', '', 'string');
    $layout = JFactory::getApplication()->input->get->get('layout', '', 'string');
    $and = '';

    // Select the required fields from the table.
    $query->select($this->getState('list.select', 'p.id,p.name,p.alias,p.created,p.published,p.catid,p.hits,p.access,'.
				   'cm.ordering,p.created_by,p.checked_out,p.checked_out_time,p.language,'.
				   'pv.var_id,pv.base_price,pv.price_with_tax,p.type,pv.stock,pv.name AS variant_name,'.
				   'pv.ordering AS var_ordering,pv.stock_subtract'))
	  ->from('#__ketshop_product AS p');

    if($layout != 'modal') {
      // Gets only the basic variant of the product.
      $and = 'AND pv.ordering=1';
    }

    $query->join('LEFT', '#__ketshop_product_variant AS pv ON pv.prod_id=p.id '.$and);

    // Get the creator name.
    $query->select('us.name AS creator')
	  ->join('LEFT', '#__users AS us ON us.id = p.created_by');

    // Join over the users for the checked out user.
    $query->select('uc.name AS editor')
	  ->join('LEFT', '#__users AS uc ON uc.id=p.checked_out');

    // Join over the categories.
    $query->select('ca.title AS category_title')
	  ->join('LEFT', '#__categories AS ca ON ca.id = p.catid');

    // Join over the language
    $query->select('lg.title AS language_title')
	  ->join('LEFT', $db->quoteName('#__languages').' AS lg ON lg.lang_code = p.language');

    // Join over the asset groups.
    $query->select('al.title AS access_level')
	  ->join('LEFT', '#__viewlevels AS al ON al.id = p.access');

    // Filter by category.
    $categoryId = $this->getState('filter.category_id');
    if(is_numeric($categoryId)) {
      // Gets the products from the category mapping table.
      $query->join('INNER', '#__ketshop_product_cat_map AS cm ON cm.product_id=p.id AND cm.cat_id='.(int)$categoryId);
    }
    elseif(is_array($categoryId)) {
      $categoryId = ArrayHelper::toInteger($categoryId);
      $categoryId = implode(',', $categoryId);
      // Gets the products from the category mapping table.
      $query->join('INNER', '#__ketshop_product_cat_map AS cm ON cm.product_id=p.id AND cm.cat_id IN('.$categoryId.')');
    }
    else {
      // Gets the ordering value from the category mapping table.
      $query->join('LEFT', '#__ketshop_product_cat_map AS cm ON cm.product_id=p.id AND cm.cat_id=p.catid');
    }

    // Filter by title search.
    $search = $this->getState('filter.search');
    if(!empty($search)) {
      if(stripos($search, 'id:') === 0) {
	$query->where('p.id = '.(int) substr($search, 3));
      }
      else {
	$search = $db->Quote('%'.$db->escape($search, true).'%');
	$query->where('(p.name LIKE '.$search.')');
      }
    }

    // Filter by access level.
    $access = $this->getState('filter.access');

    if(is_numeric($access)) {
      $query->where('p.access='.(int) $access);
    }
    elseif (is_array($access)) {
      $access = ArrayHelper::toInteger($access);
      $access = implode(',', $access);
      $query->where('p.access IN ('.$access.')');
    }

    // Filter by access level on categories.
    if(!$user->authorise('core.admin')) {
      $groups = implode(',', $user->getAuthorisedViewLevels());
      $query->where('p.access IN ('.$groups.')');
      $query->where('ca.access IN ('.$groups.')');
    }

    // Filter by publication state.
    $published = $this->getState('filter.published');
    if(is_numeric($published)) {
      $query->where('p.published='.(int)$published);
    }
    elseif($published === '') {
      $query->where('(p.published IN (0, 1))');
    }

    // Filter by creator.
    $userId = $this->getState('filter.user_id');

    if(is_numeric($userId)) {
      $type = $this->getState('filter.user_id.include', true) ? '= ' : '<>';
      $query->where('p.created_by'.$type.(int) $userId);
    }
    elseif(is_array($userId)) {
      $userId = ArrayHelper::toInteger($userId);
      $userId = implode(',', $userId);
      $query->where('p.created_by IN ('.$userId.')');
    }

    // Filter by language.
    if($language = $this->getState('filter.language')) {
      $query->where('p.language = '.$db->quote($language));
    }

    // Filter by product type.
    if($productType = $this->getState('filter.product_type')) {
      $query->where('p.type= '.$db->quote($productType));
    }

    // Filter by a single or group of tags.
    $hasTag = false;
    $tagId = $this->getState('filter.tag');

    if(is_numeric($tagId)) {
      $hasTag = true;
      $query->where($db->quoteName('tagmap.tag_id').' = '.(int)$tagId);
    }
    elseif(is_array($tagId)) {
      $tagId = ArrayHelper::toInteger($tagId);
      $tagId = implode(',', $tagId);

      if(!empty($tagId)) {
	$hasTag = true;
	$query->where($db->quoteName('tagmap.tag_id').' IN ('.$tagId.')');
      }
    }

    if($hasTag) {
      $query->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap').
		   ' ON '.$db->quoteName('tagmap.content_item_id').' = '.$db->quoteName('p.id').
		   ' AND '.$db->quoteName('tagmap.type_alias').' = '.$db->quote('com_noteproduct.note'));
    }

    // Add the list to the sort.
    $orderCol = $this->state->get('list.ordering', 'p.name');
    $orderDirn = $this->state->get('list.direction'); // asc or desc

    $query->order($db->escape($orderCol.' '.$orderDirn));

    if($dynamicItemType) {
      // Displays only the required product type (ie: normal or bundle).
      $query->where('p.type='.$db->Quote(JFactory::getApplication()->input->get->get('product_type', '', 'string')));
      // Displays the product variants in order.
      $query->order('p.id, pv.ordering');
      // Shows only the published products.
      $query->where('p.published=1');
      // To prevent duplicates when filtering with multiple selection lists.
      $query->group('pv.var_id, p.id');
    }
    else {
      // To prevent duplicates when filtering with multiple selection lists.
      $query->group('p.id');
    }

    return $query;
  }
}

