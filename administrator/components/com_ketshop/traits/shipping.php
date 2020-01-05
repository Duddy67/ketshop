<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die; 

JLoader::register('OrderTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/order.php');
JLoader::register('ShopHelper', JPATH_SITE.'/components/com_ketshop/helpers/shop.php');


trait ShippingTrait
{
  use OrderTrait;


  /**
   * Returns the available shippings according to the shipping plugin and the min/max number of
   * products allowed.
   *
   * @param   string    $pluginName	The name of the plugin that manages the shipping. 
   * @param   integer   $nbProducts	The number of products contained in the order.
   *
   * @return  array			A list of shipping objects.	
   */
  public function getShippings($pluginName, $nbProducts)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('s.id, s.name, s.weight_type, s.weight_unit, s.volumetric_ratio, s.min_weight,'.
                   's.max_weight, s.min_delivery_delay, s.delivpnt_cost, s.delivery_type,'.
		   // Uses the GROUP_CONCAT clause to get all of the joining column values in one go.
		   UtilityHelper::groupConcatAdapter('p.from,"**",p.to,"**",p.cost', '', ',', true).' AS postcodes,'.
		   UtilityHelper::groupConcatAdapter('ci.name,"**",ci.cost', '', ',', true).' AS cities,'.
		   UtilityHelper::groupConcatAdapter('r.code,"**",r.cost', '', ',', true).' AS regions,'.
		   UtilityHelper::groupConcatAdapter('co.code,"**",co.cost', '', ',', true).' AS countries,'.
		   UtilityHelper::groupConcatAdapter('ct.code,"**",ct.cost', '', ',', true).' AS continents')
	  ->from('#__ketshop_shipping AS s')
	  ->join('LEFT', '#__ketshop_ship_postcode AS p ON p.shipping_id=s.id')
	  ->join('LEFT', '#__ketshop_ship_city AS ci ON ci.shipping_id=s.id')
	  ->join('LEFT', '#__ketshop_ship_region AS r ON r.shipping_id=s.id')
	  ->join('LEFT', '#__ketshop_ship_country AS co ON co.shipping_id=s.id')
	  ->join('LEFT', '#__ketshop_ship_continent AS ct ON ct.shipping_id=s.id')
	  ->where('s.plugin_element='.$db->Quote($pluginName))
	  ->where('s.min_product <= '.(int)$nbProducts.' AND s.max_product >= '.(int)$nbProducts)
	  ->where('s.published=1')
	  ->group('s.id');
    $db->setQuery($query);
    $shippings = $db->loadObjectList();

    $itemTypes = array('postcodes' => array('from', 'to', 'cost'),
		       'cities' => array('name', 'cost'),
		       'regions' => array('code', 'cost'),
		       'countries' => array('code', 'cost'),
		       'continents' => array('code', 'cost'));

    foreach($shippings as $i => $shipping) {
      if($shipping->delivery_type == 'at_destination') {
	// Loops through the item types and creates the corresponding items according to
	// the string parts.
	foreach($itemTypes as $type => $attributes) {
          // First checks for empty values.
	  if(empty($shipping->$type)) {
	    $shippings[$i]->$type = array();
	    continue;
	  }

	  // Gets the parts separated by a comma to get the items.
	  $items = explode(',', $shipping->$type);

	  foreach($items as $j => $value) {
	    // Gets the parts separated by 2 stars to get the attribute values.
	    $parts = explode('**', $value);
	    // Creates a new item.
	    $item = new stdClass();

	    // Assigns the proper attribute and its corresponding value to the item.
	    foreach($attributes as $k => $attribute) {
	      $item->$attribute = $parts[$k];
	    }

	    $items[$j] = $item;
	  }

	  // Replaces the old string value by the newly created item set. 
	  $shippings[$i]->$type = $items;
	}
      }
      // at delivery point
      else {
	$shippings[$i]->address = UtilityHelper::getAddress('shipping', 'delivery_point', $shipping->id);
      }
    }

    return $shippings;
  }


  public function getShippingsFromPlugins($order)
  {
    // Gets the customer's delivery address.
    $addresses = ShopHelper::getCustomerAddresses($order->user_id);
    $deliveryAddress = (isset($addresses['shipping'])) ? $addresses['shipping'] : $addresses['billing'];

    $nbProducts = $this->getNumberOfShippableProducts($order);
    $weightsDimensions = $this->getWeightsAndDimensions($order);

    JPluginHelper::importPlugin('ketshopshipment');
    $dispatcher = JDispatcher::getInstance();

    // Trigger the event then retrieves the shippings from all the ketshop shipment plugins.
    $results = $dispatcher->trigger('onKetshopShipping', array($deliveryAddress, $nbProducts, $weightsDimensions));

    $priceRules = $this->getShippingPriceRules($order);

    $shippings = array();

    // Loops through the results returned by the plugins.
    foreach($results as $result) {
      foreach($result as $shipping) {
	// Sets both shipping price rules and cost.
	$shipping->price_rules = $priceRules;
	$shippings[] = $this->getShippingCost($shipping);
      }
    }

    return $shippings;
  }


  /**
   * Converts a given value from a weight unit to another weight unit.
   *
   * @param   mixed     $value		The value to convert.
   * @param   string    $unit		The actual weight unit of the given value.
   * @param   string    $unitOutput	The weight unit to convert the given value to.
   *
   * @return  mixed			The converted value.
   */
  public function weightConverter($value, $unit, $unitOutput)
  {
    // Checks parameters before starting the converting.
    if($unit === $unitOutput || $value === 0) {
      return $value;
    }

    $result = 0;

    switch($unitOutput) {
      case 'mg' :
	if($unit === 'g') {
	  $result = $value * 1000;
	}

	if($unit === 'kg') {
	  $result = $value * 1000000;
	}

	if($unit === 'lb') {
	  $result = $value * 453592.370000;
	}

	if($unit === 'oz') {
	  $result = $value * 28349;
	}

	break;

      case 'g' :
	if($unit === 'mg') {
	  $result = $value / 1000;
	}

	if($unit === 'kg') {
	  $result = $value * 1000;
	}

	if($unit === 'lb') {
	  $result = $value * 453.592370;
	}

	if($unit === 'oz') {
	  $result = $value * 28.349000;
	}

	break;

      case 'kg' :
	if($unit === 'mg') {
	  $result = $value / 1000000;
	}

	if($unit === 'g') {
	  $result = $value / 1000;
	}

	if($unit === 'lb') {
	  // (0.45359237) Not enought float numbers. Rounded.
	  $result = $value * 0.453592;  
	}

	if($unit === 'oz') {
	  $result = $value * 0.028349;
	}

	break;

      case 'lb' :
	if($unit === 'mg') {
	  $result = $value * 0.000002;
	}

	if($unit === 'g') {
	  $result = $value * 0.002204;
	}

	if($unit === 'kg') {
	  $result = $value * 2.204622;
	}

	if($unit === 'oz') {
	  $result = $value * 0.624988;
	}

	break;

      case 'oz' :
	if($unit === 'mg') {
	  $result = $value * 0.000035;
	}

	if($unit === 'g') {
	  $result = $value * 0.035274;
	}

	if($unit === 'kg') {
	  $result = $value * 35.274612;
	}

	if($unit === 'lb') {
	  $result = $value * 16.000295;
	}

	break;
    }

    return $result;
  }


  /**
   * Converts a given value from a dimension unit to another dimension unit.
   * TODO: Add code to convert to Ounce, Pound etc....
   *
   * @param   mixed     $value		The value to convert.
   * @param   string    $unit		The actual dimension unit of the given value.
   * @param   string    $unitOutput	The dimension unit to convert the given value to.
   *
   * @return  mixed			The converted value.
   */
  public function dimensionConverter($value, $unit, $unitOutput)
  {
    // Checks parameters before starting the converting.
    if($unit == $unitOutput || $value == 0) {
      return $value;
    }

    $result = 0;

    switch($unitOutput) {
      case 'mm' :
	if($unit === 'cm') {
	  $result = $value * 10;
	}

	if($unit === 'm') {
	  $result = $value * 1000;
	}

	break;

      case 'cm' :
	if($unit === 'mm') {
	  $result = $value / 10;
	}

	if($unit === 'm') {
	  $result = $value * 100;
	}

	break;

      case 'm' :
	if($unit === 'mm') {
	  $result = $value / 1000;
	}

	if($unit === 'cm') {
	  $result = $value / 100;
	}

	break;
    }

    return $result;
  }


  /**
   * Computes and returns the volumetric weight of a product.
   *
   * @param   decimal   $length		The length of the product.
   * @param   decimal   $width		The width of the product. 
   * @param   decimal   $height		The height of the product. 
   * @param   string    $unit		The dimension unit.
   * @param   decimal   $ratio		The volumetric ratio.
   *
   * @return  decimal			The volumetric weight of the product.
   */
  public function getVolumetricWeight($length, $width, $height, $unit, $ratio)
  {
    // Required the dimensions in centimeters.
    if($unit !== 'cm') {
      $length = $this->dimensionConverter($length, $unit, 'cm'); 
      $width = $this->dimensionConverter($width, $unit, 'cm'); 
      $height = $this->dimensionConverter($height, $unit, 'cm'); 
    }

    // Computes the volume (in cm3).
    $volume = $length * $width * $height;

    // Returns the volumetric weight.
    return $volume / $ratio;
  }
}

