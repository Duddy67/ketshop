<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 

JLoader::register('PriceruleTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/pricerule.php');
JLoader::register('ProductTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/product.php');


class KetshopModelProduct extends JModelItem
{
  use ProductTrait, PriceruleTrait;

  protected $_context = 'com_ketshop.product';

  /**
   * Method to auto-populate the model state.
   *
   * Product. Calling getState in this method will result in recursion.
   *
   * @since   1.6
   *
   * @return void
   */
  protected function populateState()
  {
    $app = JFactory::getApplication('site');

    // Load state from the request.
    $pk = $app->input->getInt('id');
    $this->setState('product.id', $pk);

    // Load the global parameters of the component.
    $params = $app->getParams();
    $this->setState('params', $params);

    $this->setState('filter.language', JLanguageMultilang::isEnabled());

    $snitch = ShopHelper::getSnitch();
    $catid = 0;
    // The user is coming from a category or he added the product to the cart.
    if(preg_match('#^category\.([0-9]+)$#', $snitch->from, $matches) || 
       preg_match('#^product\.'.$pk.'\.([0-9]+)$#', $snitch->from, $matches)) {
      // Retrieves the category id.
      $catid = $matches[1];
    }

    $this->setState('product.from_cat_id', $catid);
    $this->setState('product.category_pagination', $snitch->limit_start);

    // Updates the snitch.
    $snitch->from = 'product.'.$pk.'.'.$catid;
    ShopHelper::setSnitch($snitch);
  }


  // Returns a Table object, always creating it.
  public function getTable($type = 'Product', $prefix = 'KetshopTable', $config = array()) 
  {
    return JTable::getInstance($type, $prefix, $config);
  }


  /**
   * Method to get a single record.
   *
   * @param   integer  $pk  The id of the primary key.
   *
   * @return  mixed    Object on success, false on failure.
   *
   * @since   12.2
   */
  public function getItem($pk = null)
  {
    $pk = (!empty($pk)) ? $pk : (int)$this->getState('product.id');
    $user = JFactory::getUser();

    if($this->_item === null) {
      $this->_item = array();
    }

    if(!isset($this->_item[$pk])) {
      $db = $this->getDbo();
      $query = $db->getQuery(true);
      $query->select($this->getState('list.select', 'p.id,p.name,p.alias,p.intro_text,p.full_text,p.catid,p.published,p.shippable,'.
				     'pv.name AS var_name,pv.base_price,pv.price_with_tax,pv.code,pv.stock,pv.sales,'.
				     'pv.availability_delay,pv.weight,pv.length,pv.width,pv.height,pv.stock_subtract,'.
				     'pv.allow_order,pv.min_quantity,pv.max_quantity,pv.min_stock_threshold,pv.var_id,'.
				     'pv.max_stock_threshold,m.name AS manufacturer,t.name AS tax_name, t.rate AS tax_rate,'. 
				     'i.src AS img_src, i.width AS img_width, i.height AS img_height, i.alt AS img_alt,'.
				     'p.checked_out,p.checked_out_time,p.created,p.created_by,p.access,p.params,p.metadata,'.
				     'p.metakey,p.metadesc,p.hits,p.publish_up,p.publish_down,p.language,p.modified,p.modified_by,'.
				     'p.dimension_unit,p.weight_unit,pv.weight,p.img_reduction_rate,'.
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
	    ->where('p.id='.$pk);

      // Join on category table.
      $query->select('ca.title AS category_title, ca.alias AS category_alias, ca.access AS category_access')
	    ->join('LEFT', '#__categories AS ca on ca.id = p.catid');

      // Join on user table.
      $query->select('us.name AS author')
	    ->join('LEFT', '#__users AS us on us.id = p.created_by');

      // Join over the categories to get parent category titles
      $query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
	    ->join('LEFT', '#__categories as parent ON parent.id = ca.parent_id');

      // Filter by language
      if($this->getState('filter.language')) {
	$query->where('p.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
      }

      if((!$user->authorise('core.edit.state', 'com_ketshop')) && (!$user->authorise('core.edit', 'com_ketshop'))) {
	// Filter by start and end dates.
	$nullDate = $db->quote($db->getNullDate());
	$nowDate = $db->quote(JFactory::getDate()->toSql());
	$query->where('(p.publish_up = '.$nullDate.' OR p.publish_up <= '.$nowDate.')')
	      ->where('(p.publish_down = '.$nullDate.' OR p.publish_down >= '.$nowDate.')');
      }

      $db->setQuery($query);
      $data = $db->loadObject();

      if(is_null($data)) {
	JFactory::getApplication()->enqueueMessage(JText::_('COM_KETSHOP_ERROR_PRODUCT_NOT_FOUND'), 'error');
	return false;
      }

      // Convert parameter fields to objects.
      $registry = new JRegistry;
      $registry->loadString($data->params);

      $data->params = clone $this->getState('params');
      $data->params->merge($registry);

      // Technically guest could edit an article, but lets not check that to improve performance a little.
      if(!$user->get('guest')) {
	$userId = $user->get('id');
	$asset = 'com_ketshop.product.'.$data->id;

	// Check general edit permission first.
	if($user->authorise('core.edit', $asset)) {
	  $data->params->set('access-edit', true);
	}

	// Now check if edit.own is available.
	elseif(!empty($userId) && $user->authorise('core.edit.own', $asset)) {
	  // Check for a valid user and that they are the owner.
	  if($userId == $data->created_by) {
	    $data->params->set('access-edit', true);
	  }
	}
      }

      $data->categories = $this->getCategories();
      $data->cat_ids = $this->getCategoryIds($data->id);

      // Fetches all the variants of the product.
      $data->variants = $this->getProductVariants();

      foreach($data->variants as $key => $variant) {
	// Gets the variant attributes.
	$data->variants[$key]->attributes = $this->getAttributeData($data->id, $variant->var_id);
      }

      $data->nb_variants = count($data->variants);
      $data->variants = $this->setVariantImages($data->variants);

      // Gets the product attributes.
      $data->attributes = $this->getAttributeData($data->id, $data->var_id);

      // Get the tags
      $data->tags = new JHelperTags;
      $data->tags->getItemTags('com_ketshop.product', $data->id);

      // Sets the snitch data which will be useful in some product layouts.
      $data->from_cat_id = $this->getState('product.from_cat_id');
      $data->limit_start = $this->getState('product.category_pagination');

      $this->_item[$pk] = $data;
    }

    return $this->_item[$pk];
  }


  /**
   * Returns the variants of a given product.
   *
   * @param   integer  $pk	The id of the primary key.
   *
   * @return  array	        A list of variant objects.
   */
  public function getProductVariants($pk = null)
  {
    $pk = (!empty($pk)) ? $pk : (int)$this->getState('product.id');

    $db = $this->getDbo();
    $query = $db->getQuery(true);

    $query->select('pv.*, p.shippable, p.dimension_unit, p.weight_unit, pv.weight,'.
                   'p.img_reduction_rate, p.nb_variants, t.rate AS tax_rate')
	  ->from('#__ketshop_product_variant AS pv')
	  ->join('INNER', '#__ketshop_product AS p ON p.id=pv.prod_id')
	  ->join('LEFT', '#__ketshop_tax AS t ON t.id=p.tax_id')
	  ->where('prod_id='.(int)$pk)
	  ->where('pv.published=1')
	  ->order('pv.ordering');
    $db->setQuery($query);

    return $db->loadObjectList();
  }


  /**
   * Returns the categories bound to a given item.
   *
   * @param   integer  $pk  The id of the primary key.
   *
   * @return  array	        A list of category objects.
   */
  public function getCategories($pk = null)
  {
    $pk = (!empty($pk)) ? $pk : (int)$this->getState('product.id');

    $db = $this->getDbo();
    $query = $db->getQuery(true);
    $query->select('id, title, alias, language')
	  ->from('#__ketshop_product_cat_map')
	  ->join('INNER', '#__categories ON id=cat_id')
	  ->where('product_id='.(int)$pk);
    $db->setQuery($query);

    return $db->loadObjectList();
  }


  /**
   * Links the images of a given product to its corresponding variants according their
   * code.
   *
   * @param   array    $variants  A list of variant objects.
   * @param   integer  $pk        The id of the primary key (the product id).
   *
   * @return  array	          The list of variants with their respective images
   */
  public function setVariantImages($variants, $pk = null)
  {
    $pk = (!empty($pk)) ? $pk : (int)$this->getState('product.id');

    $db = $this->getDbo();
    $query = $db->getQuery(true);
    $query->select('i.*, p.img_reduction_rate')
	  ->from('#__ketshop_prod_image AS i')
	  ->join('INNER', '#__ketshop_product AS p ON p.id=i.prod_id')
	  ->where('i.prod_id = '.(int)$pk)
	  ->order('i.ordering');
    $db->setQuery($query);
    $images = $db->loadObjectList();

    foreach($variants as $key => $variant) {
      $variants[$key]->images = array();

      foreach($images as $image) {
	// The images with empty code are linked to the first variant. 
	if($key == 0 && empty($image->variant_code)) {
	  $variants[$key]->images[] = $image;
	  continue;
	}

	// Linked the image to the variant when the code matches.
	if($image->variant_code == $variant->code) {
	  $variants[$key]->images[] = $image;
	}
      }
    }

    return $variants;
  }


  /**
   * Increment the hit counter for the product.
   *
   * @param   integer  $pk  Optional primary key of the product to increment.
   *
   * @return  boolean  True if successful; false otherwise and internal error set.
   */
  public function hit($pk = 0)
  {
    $input = JFactory::getApplication()->input;
    $hitcount = $input->getInt('hitcount', 1);

    if($hitcount) {
      $pk = (!empty($pk)) ? $pk : (int) $this->getState('product.id');

      $table = JTable::getInstance('Product', 'KetshopTable');
      $table->load($pk);
      $table->hit($pk);
    }

    return true;
  }


  /**
   * Sets the final prices of the variants contained in a given product according
   * to the catalog price rule.
   *
   * @param   object	$product    The product object.
   *
   * @return  object	    	    The product object with the final prices and the price
   *                                rules (if any).
   */
  public function setProductPrices($product)
  {
    $user = JFactory::getUser();
    // Gets the coupon session array.
    $session = JFactory::getSession();
    $coupons = $session->get('coupons', array(), 'ketshop'); 

    // Gets the price rules linked to the product and its variants.
    $priceRules = $this->getCatalogPriceRules($product, $user, $coupons);

    $show = 0;
    foreach($priceRules as $key => $priceRule) {
      // The highest rule on the stack defines the show rule flag for all the 
      // following price rules. 
      if($key == 0 && $priceRule->show_rule) {
	$show = 1;
      }

      $priceRules[$key]->show_rule = $show;
    }

    // Sets the price rules for each variant.
    foreach($product->variants as $key => $variant) {
      $product->variants[$key]->price_rules = array();

      // Loops through the price rules.
      foreach($priceRules as $priceRule) {
	if($priceRule->target_type != 'product' ||
	    // All variants are impacted by the price rule or the variant id matches the price rule.
	   ($priceRule->target_type == 'product' && ((int)$priceRule->all_variants || in_array($variant->var_id, $priceRule->var_ids)))) {
	  // Stores the price rule.
	  $product->variants[$key]->price_rules[] = $priceRule;
	}
      }

      // Sets the default final prices.
      $product->variants[$key]->final_base_price = $variant->base_price;
      $product->variants[$key]->final_price_with_tax = $variant->price_with_tax;
      // Sets some extra data.
      $product->variants[$key]->tax_rate = $product->tax_rate;

      // Gets the catalog price of the variant.
      $product->variants[$key] = $this->getCatalogPrice($variant);
    }

    return $product;
  }


  /**
   * Sets the stock values of the variants contained in a given product.
   *
   * @param   object	$product    The product object.
   *
   * @return  object	    	    The product object with the stock states.
   */
  public function setProductStocks($product)
  {
    foreach($product->variants as $key => $variant) {
      // Gets the stock state.
      if($variant->stock_subtract && $product->shippable) {
	$product->variants[$key]->stock_state = $this->getStockState($variant);
      }
      // If a product is not subtracted from stock or is not shippable, we assume that stock is always full.
      else { 
	$product->variants[$key]->stock_state = 'maximum';
      }
    }

    return $product;
  }


  /**
   * Returns the stock state of a given variant according to some values.
   *
   * @param   object	$variant    The variant object.
   *
   * @return  string		    The variant stock state.
   */
  private function getStockState($variant)
  {
    if($variant->stock == 0) {
      return 'minimum';
    }
    elseif($variant->stock <= $variant->min_stock_threshold && !$variant->allow_order) {
      return 'minimum';
    }
    elseif($variant->stock >= $variant->max_stock_threshold) {
      return 'maximum';
    }
    else {
      return 'middle';
    }
  }
}

