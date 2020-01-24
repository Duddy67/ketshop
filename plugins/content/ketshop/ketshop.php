<?php
/**
 * @package KetShop
 * @copyright Copyright (c)2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 

JLoader::register('KetshopHelper', JPATH_ADMINISTRATOR.'/components/com_ketshop/helpers/ketshop.php');


class plgContentKetshop extends JPlugin
{
  protected $post;


  /**
   * Constructor.
   *
   * @param   object  &$subject  The object to observe
   * @param   array   $config    An optional associative array of configuration settings.
   *
   * @since   3.7.0
   */
  public function __construct(&$subject, $config)
  {
    // Loads the component language.
    $lang = JFactory::getLanguage();
    $langTag = $lang->getTag();
    $lang->load('com_ketshop', JPATH_ROOT.'/administrator/components/com_ketshop', $langTag);
    // Gets the POST data.
    $this->post = JFactory::getApplication()->input->post->getArray();

    parent::__construct($subject, $config);
  }


  /**
   * Method called before the content is saved.
   *
   * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
   * @param   object  $data     A JTableContent object.
   * @param   bool    $isNew    If the content is just about to be created.
   *
   * @return  boolean
   *
   * @since   2.5
   */
  public function onContentBeforeSave($context, $data, $isNew)
  {
    return true;
  }


  /**
   * Method called before the content is deleted.
   *
   * @param   string  $context  The context for the content passed to the plugin.
   * @param   object  $data     The data relating to the content that was deleted.
   *
   * @return  boolean
   *
   * @since   1.6
   */
  public function onContentBeforeDelete($context, $data)
  {
    if($context == 'com_categories.category') {
      // Ensures that the deleted category is not used as main category by one or more products.
      if(!KetshopHelper::checkMainCategory($data->id)) {
	return false;
      }
    }

    return true;
  }


  /**
   * Content is passed by reference, but after the save, so no changes will be saved.
   * Method is called right after the content is saved
   *
   * @param   string   $context  The context of the content passed to the plugin (added in 1.6)
   * @param   object   $data     A JTableContent object
   * @param   boolean  $isNew    If the content is just about to be created
   *
   * @return  void
   *
   * @since   1.6
   */
  public function onContentAfterSave($context, $data, $isNew)
  {
    // Filter the sent event.
    
    // PRODUCT
    if($context == 'com_ketshop.product' || $context == 'com_ketshop.form') { 
      // Check for product order.
      $this->setOrderByCategory($context, $data, $isNew);

      $this->setProduct($data, $isNew);
    }
    // ATTRIBUTE
    elseif($context == 'com_ketshop.attribute') {
      $this->setAttribute($data, $isNew);
    }
    // PRICE RULE
    elseif($context == 'com_ketshop.pricerule') {
      $this->setPriceRule($data, $isNew);
    }
    elseif($context == 'com_ketshop.shipping') {
      $this->setShipping($data, $isNew);
    }
    elseif($context == 'com_ketshop.customer' || $context == 'com_ketshop.profile') {
      $this->setCustomer($data, $isNew);
    }
  }


  /**
   * Content is passed by reference, but after the deletion.
   *
   * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
   * @param   object  $data     A JTableContent object.
   *
   * @return  void
   *
   * @since   2.5
   */
  public function onContentAfterDelete($context, $data)
  {
    // Filter the sent event.
    if($context == 'com_ketshop.product') {
      // Create a new query object.
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      // Delete all the rows linked to the item id. 
      $query->delete('#__ketshop_product_cat_map')
	    ->where('product_id='.(int)$data->id);
      $db->setQuery($query);
      $db->execute();
    }
    elseif($context == 'com_ketshop.order') {
    }
    elseif($context == 'com_categories.category') {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      // Delete all the rows linked to the item id. 
      $query->delete('#__ketshop_product_cat_map')
	    ->where('cat_id='.(int)$data->id);
      $db->setQuery($query);
      $db->execute();
    }
  }


  /**
   * This is an event that is called after content has its state change (e.g. Published to Unpublished).
   *
   * @param   string   $context  The context for the content passed to the plugin.
   * @param   array    $pks      A list of primary key ids of the content that has changed state.
   * @param   integer  $value    The value of the state that the content has been changed to.
   *
   * @return  boolean
   *
   * @since   3.1
   */
  public function onContentChangeState($context, $pks, $value)
  {
    return true;
  }


  /**
   * Creates (or updates) a row whenever a product is categorised.
   * The product/category mapping allows to order the products against a given category. 
   *
   * @param   string   $context  The context of the content passed to the plugin (added in 1.6)
   * @param   object   $data     A JTableContent object
   * @param   boolean  $isNew    If the content is just about to be created
   *
   * @return  void
   *
   */
  private function setOrderByCategory($context, $data, $isNew)
  {
    // Retrieves the category array, (N.B: It is not part of the table/data attributes).
    $catIds = $this->post['jform']['catids'];
    // Gets the possible unallowed categories.
    $unallowedCats = json_decode($this->post['unallowed_cats']);

    // Creates a new query object.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    // Gets the old categories linked to the item.
    $query->select('m.product_id, m.cat_id, m.ordering')
	  ->from('#__ketshop_product_cat_map AS m')
	  // Inner join in case meanwhile a category has been deleted.
	  ->join('INNER', '#__categories AS c ON c.id=m.cat_id')
	  ->where('m.product_id='.(int)$data->id);
    $db->setQuery($query);
    $categories = $db->loadObjectList();

    // The user has not access to all of the categories.
    if(!empty($unallowedCats)) {
      // Loops through the old categories.
      foreach($categories as $category) {
	// Preserves the categories the user has no access to.
	if(in_array($category->cat_id, $unallowedCats)) {
	  $catIds[] = $category->cat_id;
	}
      }
    }

    $values = array();

    foreach($catIds as $catId) {
      $newCat = true; 

      // In order to preserve the ordering of the old categories checks if 
      // they match those newly selected.
      foreach($categories as $category) {
	if($category->cat_id == $catId) {
	  $values[] = $category->product_id.','.$category->cat_id.','.$category->ordering;
	  $newCat = false; 
	  break;
	}
      }

      if($newCat) {
	$values[] = $data->id.','.$catId.',0';
      }
    }

    // Deletes all the rows matching the item id.
    $query->clear();
    $query->delete('#__ketshop_product_cat_map')
	  ->where('product_id='.(int)$data->id);
    $db->setQuery($query);
    $db->execute();

    $columns = array('product_id', 'cat_id', 'ordering');

    // Inserts a new row for each category linked to the item.
    $query->clear();
    $query->insert('#__ketshop_product_cat_map')
	  ->columns($columns)
	  ->values($values);
    $db->setQuery($query);
    $db->execute();
  }


  /**
   * Performs extra treatments on a product object.
   *
   * @param   object   $data     A JTableContent object
   * @param   boolean  $isNew    If the content is just about to be created
   *
   * @return  void
   */
  private function setProduct($data, $isNew)
  {
    $model = JModelLegacy::getInstance('Product', 'KetshopModel');

    // Retrieves all the new set attributes from the POST array then save them as
    // objects and put them into an array.
    $attributes = $attributeIds = array();

    foreach($this->post as $key => $val) {
      if(preg_match('#^attribute_attribute_id_([0-9]+)$#', $key, $matches)) {
	$attribNb = $matches[1];
	$attribId = $this->post['attribute_attribute_id_'.$attribNb];

	// Prevents duplicates.
	if(in_array($attribId, $attributeIds)) {
	  continue;
	}

	$attributeIds[] = $attribId;

	$attribute = new JObject;
	$attribute->prod_id = $data->id;
	$attribute->attrib_id = $attribId;
	$attributes[] = $attribute;
      }
    }

    // Sets fields.
    $columns = array('prod_id','attrib_id');
    KetshopHelper::updateMappingTable('#__ketshop_prod_attrib', $columns, $attributes, $data->id);

    // At last ends with images.

    $images = array();

    foreach($this->post as $key => $val) {
      if(preg_match('#^image_image_src_([0-9]+)$#', $key, $matches)) {
	$imageNb = $matches[1];

	if(JFactory::getApplication()->isAdmin()) {
	  // Removes "../" from src path in case images come from the administrator area.
	  $src = preg_replace('#^\.\.\/#', '', $this->post['image_image_src_'.$imageNb]);
	}
	// We're on front-end. Remove the domain url.
	else {
	  $src = preg_replace('#^'.JURI::root().'#', '', $this->post['image_image_src_'.$imageNb]);
	}

	$width = $this->post['image_image_width_'.$imageNb];
	$height = $this->post['image_image_height_'.$imageNb];
	$ordering = $this->post['image_ordering_'.$imageNb];
	$alt = trim($this->post['image_image_alt_'.$imageNb]);
	$variantCode = trim($this->post['image_image_variant_code_'.$imageNb]);

	if(!empty($src)) {
	  $image = new JObject;
	  $image->prod_id = $data->id;
	  $image->src = $src;
	  $image->width = $width;
	  $image->height = $height;
	  $image->ordering = $ordering;
	  $image->alt = $alt;
	  $image->variant_code = $variantCode;
	  $images[] = $image;
	}
      }
    }

    // Sets fields.
    $columns = array('prod_id','src','width','height','ordering','alt', 'variant_code');
    KetshopHelper::updateMappingTable('#__ketshop_prod_image', $columns, $images, $data->id);

    // Checks for product variants.
    // N.B: Only existing products can set variants.
    $model->setProductVariants($data->id, $this->post);
  }


  /**
   * Performs extra treatments on a attribute object.
   *
   * @param   object   $data     A JTableContent object
   * @param   boolean  $isNew    If the content is just about to be created
   *
   * @return  void
   */
  private function setAttribute($data, $isNew)
  {
    $options = array();

    foreach($this->post as $key => $groupId) {
      if(preg_match('#^option_value_([0-9]+)$#', $key, $matches)) {
	$optionNb = $matches[1];

	$value = trim($this->post['option_value_'.$optionNb]);
	$text = trim($this->post['option_text_'.$optionNb]);

	// Checks for empty values.
	if($value === '' || $text === '') {
	  continue;
	}

	// Removes any duplicate whitespace, and ensure all characters are alphanumeric
	$value = preg_replace('/(\s|[^A-Za-z0-9\-_])+/', '-', $value);

	$published = 0;

	// Checkbox variable is not passed through POST when unchecked.
	if(isset($this->post['option_published_'.$optionNb])) {
	  $published = 1;
	}

	$ordering = $this->post['option_ordering_'.$optionNb];

	$option = new JObject;
	$option->attrib_id = $data->id;
	$option->option_value = $value;
	$option->option_text = $text;
	$option->published = $published;
	$option->ordering = $ordering;
	$options[] = $option;
      }
    }

    // Sets fields.
    $columns = array('attrib_id', 'option_value', 'option_text', 'published', 'ordering');
    KetshopHelper::updateMappingTable('#__ketshop_attrib_option', $columns, $options, $data->id);
  }


  /**
   * Performs extra treatments on a price rule object.
   *
   * @param   object   $data     A JTableContent object
   * @param   boolean  $isNew    If the content is just about to be created
   *
   * @return  void
   */
  private function setPriceRule($data, $isNew)
  {
    $ruleType = $data->type;
    $targetType = $data->target_type;       // product, bundle, product group (ie: category).
    $recipientType = $data->recipient_type; // customer,customer group
    $conditionType = $data->condition_type; // product, bundle, category, product amount, product quantity.
    $conditions = $targets = $duplicates = array();

    // N.B: The 'total_prod' conditions are treated directly in the price rule table as
    //      they refer to the content of the cart, thereby no item is required.

    if($ruleType == 'cart' && $conditionType != 'total_prod_amount' && $conditionType != 'total_prod_qty') {
      // Retrieves all the new conditions from the POST array.
      foreach($this->post as $key => $val) {
	//
	if(preg_match('#^condition_item_id_([0-9]+)$#', $key, $matches)) {
	  $conditionNb = $matches[1];
	  $itemId = (int)$this->post['condition_item_id_'.$conditionNb];

	  // Prevents duplicate or empty target id from being stored.
	  if($itemId && !in_array($itemId.'_'.$varId, $duplicates)) {
	    $condition = new JObject;
	    $condition->prule_id = $data->id;
	    $condition->item_id = $itemId;
	    // N.B: Condition types other than product_qty don't use the var_id value.
	    $condition->var_id = ($conditionType == 'product_qty') ? $this->post['condition_var_id_'.$conditionNb] : 0;
	    $condition->operator = $this->post['condition_comparison_opr_'.$conditionNb];
	    $condition->item_amount = 0;
	    $condition->item_qty = 0;


	    if($conditionType == 'product_cat_amount' || $conditionType == 'total_prod_amount') {
	      $condition->item_amount = $this->post['condition_item_amount_'.$conditionNb];
	    }
	    else {
	      $condition->item_qty = $this->post['condition_item_qty_'.$conditionNb];
	    }

	    // When the all_variant flag is on, all the product variants are impacted.
	    if($conditionType == 'product_qty' && (int)$data->all_variants) {
	      // Just select the basic variant. No need to add the extra variants.
	      $condition->var_id = 1;

	      // Ensures that this product modification has not been already applied (in
	      // case several variants of this product were selected).  
	      if(in_array($condition->item_id.'_'.$condition->var_id, $duplicates)) {
		continue;
	      }
	    }

	    $conditions[] = $condition;

	    // Saves the item and var ids as a pair separated by an underscore.
	    $duplicates[] = $condition->item_id.'_'.$condition->var_id;
	  }
	}
      }
    }

    // N.B: There is no item dynamicaly added in target when cart rule is selected.
    //      So there's no need to store anything into database.

    if($ruleType == 'catalog') {
      foreach($this->post as $key => $val) {
	if(preg_match('#^target_item_id_([0-9]+)$#', $key, $matches)) {
	  $targetNb = $matches[1];
	  $itemId = (int)$this->post['target_item_id_'.$targetNb];
	  $varId = (int)$this->post['target_var_id_'.$targetNb];

	  // Prevents duplicate or empty target id from being stored.
	  if($itemId && !in_array($itemId.'_'.$varId, $duplicates)) {
	    $target = new stdClass();
	    $target->prule_id = $data->id;
	    $target->item_id = $itemId;
	    $target->var_id = $varId;

	    // When the all_variant flag is on, all the product variants are impacted.
	    if($data->target_type == 'product' && (int)$data->all_variants) {
	      // Just select the basic variant. No need to add the extra variants.
	      $target->var_id = 1;

	      // Ensures that this product modification has not been already applied (in
	      // case several variants of this product were selected).  
	      if(in_array($target->item_id.'_'.$target->var_id, $duplicates)) {
		continue;
	      }
	    }

	    // Stores the target.
	    $targets[] = $target;

	    // Saves the item and var ids as a pair separated by an underscore.
	    $duplicates[] = $target->item_id.'_'.$target->var_id;
	  }
	}
      }
    }

    $recipients = $recipientIds = array();

    // Retrieves all the new recipients from the POST array.
    foreach($this->post as $key => $val) {
      if(preg_match('#^recipient_item_id_([0-9]+)$#', $key, $matches)) {
	$recipientNb = $matches[1];
	$recipientId = (int)$this->post['recipient_item_id_'.$recipientNb];

	// Prevents duplicate or empty target id.
	if($recipientId && !in_array($recipientId, $recipientIds)) {
	  $recipient = new JObject;
	  $recipient->prule_id = $data->id;
	  $recipient->item_id = $recipientId;
	  $recipients[] = $recipient;
	  //
	  $recipientIds[] = $recipientId;
	}
      }
    }

    $columns = array('prule_id', 'item_id', 'var_id', 'operator', 'item_amount', 'item_qty');
    KetshopHelper::updateMappingTable('#__ketshop_prule_condition', $columns, $conditions, $data->id);

    $columns = array('prule_id', 'item_id', 'var_id');
    KetshopHelper::updateMappingTable('#__ketshop_prule_target', $columns, $targets, $data->id);

    $columns = array('prule_id', 'item_id');
    KetshopHelper::updateMappingTable('#__ketshop_prule_recipient', $columns, $recipients, $data->id);
  }


  /**
   * Performs extra treatments on a shipping object.
   *
   * @param   object   $data     A JTableContent object
   * @param   boolean  $isNew    If the content is just about to be created
   *
   * @return  void
   */
  private function setShipping($data, $isNew)
  {
    // Retrieves all the new set postcodes, cities, regions, countries,
    // or continents (if any) from the POST array.
    $postcodes = array();
    $cities = array();
    $regions = array();
    $countries = array();
    $continents = array();

    foreach($this->post as $key => $val) {
      if(preg_match('#^postcode_from_([0-9]+)$#', $key, $matches)) {
	$postcodeNb = $matches[1];

	$postcode = new JObject;
	$postcode->shipping_id = $data->id;
	$postcode->from = trim($this->post['postcode_from_'.$postcodeNb]);
	$postcode->to = trim($this->post['postcode_to_'.$postcodeNb]);
	$postcode->cost = trim($this->post['postcode_cost_'.$postcodeNb]);
	$postcodes[] = $postcode; 
      }

      if(preg_match('#^city_name_([0-9]+)$#', $key, $matches)) {
	$cityNb = $matches[1];

	$city = new JObject;
	$city->shipping_id = $data->id;
	$city->name = trim($this->post['city_name_'.$cityNb]);
	$city->cost = trim($this->post['city_cost_'.$cityNb]);
	$cities[] = $city; 
      }

      if(preg_match('#^region_code_([0-9]+)$#', $key, $matches)) {
	$regionNb = $matches[1];

	$region = new JObject;
	$region->shipping_id = $data->id;
	$region->code = trim($this->post['region_code_'.$regionNb]);
	$region->cost = trim($this->post['region_cost_'.$regionNb]);
	$regions[] = $region; 
      }

      if(preg_match('#^country_code_([0-9]+)$#', $key, $matches)) {
	$countryNb = $matches[1];

	$country = new JObject;
	$country->shipping_id = $data->id;
	$country->code = trim($this->post['country_code_'.$countryNb]);
	$country->cost = trim($this->post['country_cost_'.$countryNb]);
	$countries[] = $country; 
      }

      if(preg_match('#^continent_code_([0-9]+)$#', $key, $matches)) {
	$continentNb = $matches[1];

	$continent = new JObject;
	$continent->shipping_id = $data->id;
	$continent->code = trim($this->post['continent_code_'.$continentNb]);
	$continent->cost = trim($this->post['continent_cost_'.$continentNb]);
	$continents[] = $continent; 
      }
    }

    // Stores items according to the delivery type chosen by the user.
    if($data->delivery_type == 'at_destination') {
      $db = JFactory::getDbo();
      // N.B: The "from" and "to" fields MUST be "backticked" as they are
      // reserved SQL words.
      $columns = array('shipping_id', $db->quoteName('from'), $db->quoteName('to'), 'cost');
      KetshopHelper::updateMappingTable('#__ketshop_ship_postcode', $columns, $postcodes, $data->id);

      $columns = array('shipping_id', 'name', 'cost');
      KetshopHelper::updateMappingTable('#__ketshop_ship_city', $columns, $cities, $data->id);

      $columns = array('shipping_id', 'code', 'cost');
      KetshopHelper::updateMappingTable('#__ketshop_ship_region', $columns, $regions, $data->id);
      KetshopHelper::updateMappingTable('#__ketshop_ship_country', $columns, $countries, $data->id);
      KetshopHelper::updateMappingTable('#__ketshop_ship_continent', $columns, $continents, $data->id);
    }
    // at_delivery_point
    else {
      // Retrieves the jform to get the needed extra fields.
      $jform = $this->post['jform'];

      // Stores the address data.
      $address = array('street_shipping' => trim($jform['street']),
		       'city_shipping' => trim($jform['city']),
		       'region_code_shipping' => $jform['region_code'],
		       'postcode_shipping' => trim($jform['postcode']),
		       'country_code_shipping' => $jform['country_code'],
                       'phone_shipping' => trim($jform['phone']));

      if($isNew) {
	UtilityHelper::insertAddress($address, 'shipping', 'delivery_point', $data->id);
      }
      else {
	UtilityHelper::updateAddress($address, 'shipping', 'delivery_point', $data->id);
      }
    }
  }


  /**
   * Performs extra treatments on a customer object.
   *
   * @param   object   $data     A JTableContent object
   * @param   boolean  $isNew    If the content is just about to be created
   *
   * @return  void
   */
  private function setCustomer($data, $isNew)
  {
    // Gets the jform from POST to retrieve the address fields.
    $jform = $this->post['jform'];

    if((int)$jform['new_billing_address']) {
      UtilityHelper::insertAddress($jform, 'billing', 'customer', $data->id);
    }
    else {
      UtilityHelper::updateAddress($jform, 'billing', 'customer', $data->id);
    }

    if((int)$data->shipping_address) {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      // Checks first how many shipping addresses this customer has already.
      $query->select('COUNT(*)')
	    ->from('#__ketshop_address')
	    ->where('item_id='.(int)$data->id)
	    ->where('item_type="customer"')
	    ->where('type="shipping"');
      $db->setQuery($query);
      $shippingAddresses = $db->loadResult();

      if((int)$jform['new_shipping_address'] || $shippingAddresses == 0) {
	UtilityHelper::insertAddress($jform, 'shipping', 'customer', $data->id);
      }
      else {
	UtilityHelper::updateAddress($jform, 'shipping', 'customer', $data->id);
      }
    }
  }
}

