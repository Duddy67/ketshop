<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access'); 


/**
 * 
 *
 */

trait PriceruleTrait
{
  /**
   * Checks then applies catalog price rules (if any) to a given product.
   *
   * @param   object	$product	The product data. 
   *
   * @return  object                    The given product.
   */
  public function getCatalogPrice($product)
  {
    // Initializes the final prices.
    $product->final_base_price = $product->base_price;
    $product->final_price_with_tax = $product->price_with_tax;

    // Loops through the price rules and apply them.
    foreach($product->price_rules as $key => $priceRule) {
      // Applies the price rule according its application level.
      if($priceRule->application_level == 'before_tax') {
	$product->final_base_price = $this->applyRule($priceRule, $product->final_base_price);
	$product->final_price_with_tax = UtilityHelper::getPriceWithTax($product->final_base_price, $product->tax_rate);
      }
      // after_tax
      else {
	$product->final_price_with_tax = $this->applyRule($priceRule, $product->final_price_with_tax);
	$product->final_base_price = UtilityHelper::getPriceWithoutTax($product->final_price_with_tax, $product->tax_rate);
      }
    }

    return $product;
  }


  /**
   * Fetches all the catalog price rules bound to a given product.
   *
   * @param   object	$product	The product data. 
   * @param   object	$user		The user data. 
   * @param   array	$coupons	(Optional) The coupon session array.
   *
   * @return  array	The catalog price rules bound to the given product.
   */
  public function getCatalogPriceRules($product, $user, $coupons = array())
  {
    // Gets current date and time (UTC).
    $now = JFactory::getDate()->toSql();

    // Gets user group ids to which the user belongs to. 
    $userGroups = JAccess::getGroupsByUser($user->id);
    $groups = implode(',', $userGroups);

    // TODO: Set up a efficient translation trait.
    $translatedFields = 'pr.name,pr.description,';
    $leftJoinTranslation = '';

    // Checks for possible coupon price rule.
    $couponQuery = $this->setCouponQuery($coupons);

    // Gets all the rules concerning the product (or its category) and the
    // current user (or the group he's in).
    // The list of result is ordered to determine their level.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('pr.id, pr.type, pr.operation, pr.value, pr.behavior, pr.ordering, pr.show_rule,pr.all_variants,'. 
		   'pr.application_level, pr.target_type, pr.recipient_type, pr.ordering,'.$translatedFields.
		   UtilityHelper::groupConcatAdapter('prt.var_id').' AS var_ids')
	  ->from('#__ketshop_price_rule AS pr')
	  ->join('LEFT', '#__ketshop_prule_recipient AS prr ON (pr.recipient_type="customer" '.
			 'AND prr.item_id='.$user->id.') OR (pr.recipient_type="customer_group" '.
			 'AND prr.item_id IN ('.$groups.')) ')
	  ->join('LEFT', '#__ketshop_prule_target AS prt ON ((pr.target_type="product" OR pr.target_type="bundle") '.
			 'AND prt.item_id='.(int)$product->id.') '.
			 'OR (pr.target_type="product_cat" AND prt.item_id IN('.implode(',', $product->cat_ids).'))')
	  ->join('LEFT', '#__ketshop_coupon AS cp ON cp.prule_id=pr.id');

    // Checks for translation.
    if(!empty($leftJoinTranslation)) {
      $query->join('LEFT', $leftJoinTranslation);
    }

    $query->where('pr.id = prt.prule_id AND pr.id = prr.prule_id AND pr.published = 1 AND pr.type = "catalog"')
	  ->where($couponQuery)
	  // Checks against publication dates (start and stop).
	  ->where('('.$db->quote($now).' < pr.publish_down OR pr.publish_down = "0000-00-00 00:00:00")')
	  ->where('('.$db->quote($now).' > pr.publish_up OR pr.publish_up = "0000-00-00 00:00:00")')
	  ->group('pr.id')
	  ->order('ordering');
    $db->setQuery($query);
    $catalogPriceRules = $db->loadObjectList();

    // Checks for errors.
    if($db->getErrorNum()) {
      // TODO: To be connected to a log system.
      ////ShopHelper::logEvent($codeLocation, 'sql_error', 1, $db->getErrorNum(), $db->getErrorMsg());
      return false;
    }

    foreach($catalogPriceRules as $key => $priceRule) {
      // Converts the variant ids field to an array (used with product target price rules).
      $catalogPriceRules[$key]->var_ids = explode(',', $priceRule->var_ids);
    }

    $this->checkExclusivePriceRule($catalogPriceRules);

    return $catalogPriceRules;
  }


  /**
   * Fetches all the price rules which conditions are matching the cart state. 
   *
   * @param   object	$user		The user data. 
   * @param   array	$coupons	(Optional) The coupon session array.
   *
   * @return  array	A list of price rule objects.
   */
  public function getCartPriceRules($user, $coupons = array())
  {
    // Gets current date and time (UTC).
    $now = JFactory::getDate()->toSql();

    // Gets user group ids to which the user belongs to. 
    $userGroups = JAccess::getGroupsByUser($user->id);
    $groups = implode(',', $userGroups);

    // TODO: Set up a efficient translation trait.
    $translatedFields = 'pr.name,pr.description';
    $leftJoinTranslation = '';

    // Checks for possible coupon price rule.
    $couponQuery = $this->setCouponQuery($coupons);

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('pr.id, pr.type, pr.operation, pr.value, pr.behavior, pr.ordering, pr.show_rule,pr.all_variants,'. 
                   'pr.condition_type, pr.logical_opr, pr.comparison_opr, pr.condition_amount, pr.condition_qty,'.
		   'pr.application_level, pr.target_type, pr.recipient_type, pr.ordering,'.$translatedFields)
	  ->from('#__ketshop_price_rule AS pr')
	  ->join('LEFT', '#__ketshop_prule_recipient AS prr ON (pr.recipient_type="customer" '.
			 'AND prr.item_id='.$user->id.') OR (pr.recipient_type="customer_group" '.
			 'AND prr.item_id IN ('.$groups.')) ')
	  ->join('LEFT', '#__ketshop_coupon AS cp ON cp.prule_id=pr.id');

    // Checks for translation.
    if(!empty($leftJoinTranslation)) {
      $query->join('LEFT', $leftJoinTranslation);
    }

    $query->where('pr.id = prr.prule_id AND pr.published = 1 AND pr.type = "cart"')
	  ->where($couponQuery)
	  // Checks against publication dates (start and stop).
	  ->where('('.$db->quote($now).' < pr.publish_down OR pr.publish_down = "0000-00-00 00:00:00")')
	  ->where('('.$db->quote($now).' > pr.publish_up OR pr.publish_up = "0000-00-00 00:00:00")')
	  ->group('pr.id')
	  ->order('ordering');
    $db->setQuery($query);
    $cartPriceRules = $db->loadObjectList();

    $this->checkExclusivePriceRule($cartPriceRules);
    $this->getPriceRuleConditions($cartPriceRules);

    return $cartPriceRules;
  }


  /**
   * Checks then applies cart price rules (if any) to a given shipping.
   *
   * TODO: For now taxes are not taken in account. They should be in the futur. 
   *
   * @param   object	$shipping	The shipping data. 
   *
   * @return  object                    The given shipping.
   */
  public function getShippingCost($shipping)
  {
    $shipping->final_shipping_cost = $shipping->shipping_cost;

    // Loops through the price rules and apply them.
    foreach($shipping->price_rules as $key => $priceRule) {
      // Applies the price rule according its application level.
      $shipping->final_shipping_cost = $this->applyRule($priceRule, $shipping->final_shipping_cost);
    }

    return $shipping;
  }


  /**
   * Builds the query to take coupons (if any) into account. 
   *
   * @param   array	$coupon      The coupon session array.
   *
   * @return  string		     The coupon query.
   */
  private static function setCouponQuery($coupons)
  {
    // By default the coupon price rules are ruled out.
    $couponQuery = '(pr.behavior!="CPN_AND" AND pr.behavior!="CPN_XOR")';

    // Check the coupon session array.
    if(!empty($coupons)) {
      $couponQuery = '';
      // Concatenate the coupon codes whith OR operators. 
      foreach($coupons as $code) {
	$couponQuery .= 'cp.code="'.$code.'" OR '; 
      }

      // Remove the OR condition (include spaces) from the end of the string.
      $couponQuery = substr($couponQuery, 0, -4);
      // Search for both coupon and regular price rules.
      $couponQuery = '(('.$couponQuery.') OR (pr.behavior!="CPN_AND" AND pr.behavior!="CPN_XOR"))';
    }

    return $couponQuery;
  }


  /**
   * Checks for a possible exclusive rule. 
   *
   * @param   array	&$priceRules     The price rule object array to check.
   *
   * @return  void
   */
  private function checkExclusivePriceRule(&$priceRules)
  {
    $delete = false;

    foreach($priceRules as $key => $priceRule) {
      // An exclusive rule has been found. 
      if($delete) {
	// Deletes the rest of the price rule array.
	unset($priceRules[$key]);
	continue;
      }

      // In case of exclusive rule, the rest of the price rule array has to be deleted.
      if($priceRule->behavior == 'XOR') {
	$delete = true;
      }
    }
  }


  private function getPriceRuleConditions(&$priceRules)
  {
    $ids = array();
    foreach($priceRules as $priceRule) {
      $ids[] = $priceRule->id;
    }

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('prule_id, item_id, operator, item_amount, item_qty, '.UtilityHelper::groupConcatAdapter('var_id').' AS var_ids')
	  ->from('#__ketshop_prule_condition')
	  ->where('prule_id IN('.implode(',', $ids).')')
	  ->group('prule_id, item_id')
	  ->order('prule_id');
    $db->setQuery($query);
    $conditions = $db->loadObjectList();

    foreach($priceRules as $key => $priceRule) {
      $priceRules[$key]->conditions = array();

      foreach($conditions as $condition) {
	if($condition->prule_id == $priceRule->id) {
	  $condition->var_ids = explode(',', $condition->var_ids);
	  $priceRules[$key]->conditions[] = $condition;
	}
      }
    }
  }


  /**
   * Apply a given price rule on a given value according to the operation type.
   *
   * @param   object	$priceRule     A price rule object.
   * @param   decimal	$value         The value to apply the price rule on.
   *
   * @return  decimal                  The given value modified by the price rule.
   */
  private function applyRule($priceRule, $value)
  {
    switch($priceRule->operation) {
      case 'add_percentage': 
	$result = $value + ($value * ($priceRule->value / 100));
	break;

      case 'subtract_percentage': 
	$result = $value - ($value * ($priceRule->value / 100));
	break;

      case 'add_value': 
	$result = $value + $priceRule->value; 
	break;

      case 'subtract_value': 
	$result = $value - $priceRule->value; 
	break;

      case 'fixed_price': 
	$result = $priceRule->value; 
	break;
    }

    return $result;
  }
}

