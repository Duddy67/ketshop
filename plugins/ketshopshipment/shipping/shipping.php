<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JLoader::register('ShippingTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/shipping.php');


class plgKetshopshipmentShipping extends JPlugin
{
  use ShippingTrait;


  //Grab the event triggered by the shipment model.
  public function onKetshopShipping($deliveryAddress, $nbProducts, $weightsDimensions)
  {
    $shippings = $this->getShippings('shipping', $nbProducts);
    $shippings = $this->checkWeight($shippings, $weightsDimensions);
//var_dump($shippings);
    foreach($shippings as $key => $shipping) {
      // 
      if($shipping->delivery_type == 'at_destination') {
	// Searches postcodes. 
	foreach($shipping->postcodes as $postcode) {
	  if($deliveryAddress->postcode >= $postcode->from && $deliveryAddress->postcode <= $postcode->to) {
	    $shippings[$key]->shipping_cost = $postcode->cost;
	  }
	}

	// In case a cost is found, moves on to the next shipping.
	if(isset($shippings[$key]->shipping_cost)) { continue; }

	// Searches cities. 
	foreach($shipping->cities as $city) {
	  if($deliveryAddress->city == $city->name) {
	    $shippings[$key]->shipping_cost = $city->cost;
	  }
	}

	if(isset($shippings[$key]->shipping_cost)) { continue; }

	// Searches regions. 
	foreach($shipping->regions as $region) {
	  if($deliveryAddress->region_code == $region->code) {
	    $shippings[$key]->shipping_cost = $region->cost;
	  }
	}

	if(isset($shippings[$key]->shipping_cost)) { continue; }

	// Searches countries. 
	foreach($shipping->countries as $country) {
	  if($deliveryAddress->country_code == $country->code) {
	    $shippings[$key]->shipping_cost = $country->cost;
	  }
	}

	if(isset($shippings[$key]->shipping_cost)) { continue; }

	// Searches continents. 
	foreach($shipping->continents as $continent) {
	  if($deliveryAddress->continent_code == $continent->code) {
	    $shippings[$key]->shipping_cost = $continent->cost;
	  }
	}

	// In case no cost has been found, the shipping is removed from the list.
	if(!isset($shippings[$key]->shipping_cost)) {
	  unset($shippings[$key]);
	}
      }
      // at delivery point.
      else {
	$shippings[$key]->shipping_cost = $shipping->delivpnt_cost;
      }
    }

    return $shippings;
  }


  public function checkWeight($shippings, $weightsDimensions)
  {
    foreach($shippings as $key => $shipping) {
      $totalWeight = 0;
      if($shipping->weight_type == 'normal') {
	foreach($weightsDimensions as $product) {
	  $totalWeight = $totalWeight + $this->weightConverter($product->weight, $product->weight_unit, $shipping->weight_unit);
	}
      }
      // volumetric
      else {
	foreach($weightsDimensions as $product) {
	  $totalWeight = $totalWeight + $this->getVolumetricWeight($product->length, $product->width, $product->height, $product->dimension_unit, $shipping->volumetric_ratio);
	}
      }

      if($totalWeight < $shipping->min_weight || $totalWeight > $shipping->max_weight) {
	unset($shippings[$key]);
	continue;
      }
    }

    return $shippings;
  }
}
