<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die; 

JLoader::register('PriceruleTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/pricerule.php');
JLoader::register('ProductTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/product.php');


trait OrderTrait
{
  use PriceruleTrait, ProductTrait;

  private $total_qty = null;



  /**
   * Returns an order from a given order id.
   *
   * @param   integer  $orderId		An order id.
   *
   * @return  object	 		An order object.
   */
  public function getOrder($orderId)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('*')
	  ->from('#__ketshop_order')
	  ->where('id='.(int)$orderId);
    $db->setQuery($query);

    return $db->loadObject();
  }


  /**
   * Stores the global and detailed amounts in the corresponding tables.
   *
   * @param   object   $order		An order object
   * @param   array    $cartPriceRules  An array of price rule objects.
   *
   * @return  void
   */
  public function setAmounts($order, $cartPriceRules)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('*')
	  ->from('#__ketshop_order_prod')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $products = $db->loadObjectList();

    if(empty($products)) {
      $this->resetOrder($order);

      return;
    }

    $detailed = array();
    $globalAmounts = new stdClass();
    $globalAmounts->excl_tax = $globalAmounts->incl_tax = $globalAmounts->final_excl_tax = $globalAmounts->final_incl_tax = 0;

    // Loops through the ordered products.
    foreach($products as $product) {
      // Sets the global amounts.
      $globalAmounts->excl_tax += $product->base_price * $product->quantity;
      $globalAmounts->incl_tax += $product->price_with_tax * $product->quantity;
      $globalAmounts->final_excl_tax += $product->final_base_price * $product->quantity;
      $globalAmounts->final_incl_tax += $product->final_price_with_tax * $product->quantity;
      // Updates the total quantity.
      $this->total_qty += $product->quantity;

      // Sets the detailed amounts (one amount per tax).
      if(array_key_exists($product->tax_rate, $detailed)) {
	$detailed[$product->tax_rate]->excl_tax += $product->base_price * $product->quantity;
	$detailed[$product->tax_rate]->incl_tax += $product->price_with_tax * $product->quantity;
	$detailed[$product->tax_rate]->final_excl_tax += $product->final_base_price * $product->quantity;
	$detailed[$product->tax_rate]->final_incl_tax += $product->final_price_with_tax * $product->quantity;
      }
      else {
	$amounts = new stdClass();
	$amounts->excl_tax = $product->base_price * $product->quantity;
	$amounts->incl_tax = $product->price_with_tax * $product->quantity;
	$amounts->final_excl_tax = $product->final_base_price * $product->quantity;
	$amounts->final_incl_tax = $product->final_price_with_tax * $product->quantity;
        // The array is indexed with the tax rate.
	$detailed[(string)$product->tax_rate] = $amounts;
      }
    }

    $this->checkCartPriceRules($cartPriceRules, $globalAmounts, $products);
    // The global tax rate is computed against the difference between the final global
    // amounts excl tax and incl tax.   
    $globalTaxRate = UtilityHelper::getTaxRateFromPrices($globalAmounts->final_excl_tax, $globalAmounts->final_incl_tax);

    // Loops through the price rules.
    foreach($cartPriceRules as $priceRule) {
      if($priceRule->target_type == 'cart_amount') {
	// Applies the price rule according its application level.
	if($priceRule->application_level == 'before_tax') {
	  $globalAmounts->final_excl_tax = $this->applyRule($priceRule, $globalAmounts->final_excl_tax);
	  $globalAmounts->final_incl_tax = UtilityHelper::getPriceWithTax($globalAmounts->final_excl_tax, $globalTaxRate);
	}
	// after tax
	else {
	  $globalAmounts->final_incl_tax = $this->applyRule($priceRule, $globalAmounts->final_incl_tax);
	  $globalAmounts->final_excl_tax = UtilityHelper::getPriceWithoutTax($globalAmounts->final_incl_tax, $globalTaxRate);
	}
      }
      // shipping_cost
      else {
	// TODO
      }
    }

    $this->storeAmounts($order, $globalAmounts, $detailed);
    $this->storeCartPriceRules($order, $cartPriceRules);
  }


  /**
   * Tests the cart price rules against the global amounts and the products. 
   * If the current order/cart state doesn't match a price rule, it is removed from the
   * list.  
   *
   * @param   array    $priceRules	An array of price rule objects.
   * @param   array    $globalAmounts	An array of global amounts.
   * @param   array    $products	An array of product objects.
   *
   * @return  void
   */
  public function checkCartPriceRules(&$priceRules, $globalAmounts, $products)
  {
    // Loops through the price rules.
    foreach($priceRules as $key => $priceRule) {
      $isTrue = false;

      // Starts with the trivial cases.
      if($priceRule->condition_type == 'total_prod_qty' || $priceRule->condition_type == 'total_prod_amount') { 
	// The total number of products matches the price rule.
        if($priceRule->condition_type == 'total_prod_qty' &&
	   UtilityHelper::isTrue($this->total_qty, $priceRule->comparison_opr, $priceRule->condition_qty)) {
	  $isTrue = true;
	}

	// The total amount of products matches the price rule.
	if($priceRule->condition_type == 'total_prod_amount') {
	  // Compares amount according to the application level.
	  if($priceRule->application_level == 'before_tax') {
	    $isTrue = UtilityHelper::isTrue($globalAmounts->final_excl_tax, $priceRule->comparison_opr, $priceRule->condition_amount); 
	  }
	  // after tax
	  else {
	    $isTrue = UtilityHelper::isTrue($globalAmounts->final_incl_tax, $priceRule->comparison_opr, $priceRule->condition_amount); 
	  }
	}
      }
      //  A certain quantity of a specific product.
      elseif($priceRule->condition_type == 'product_qty') {
        // Loops through the required conditions.
        foreach($priceRule->conditions as $condition) {
	  $isTrue = false;
	  // Loops through the products in the cart.
	  foreach($products as $product) {
	    // The product matches the condition.
	    if($condition->item_id == $product->prod_id &&
	       ($priceRule->all_variants || in_array($product->var_id, $priceRule->var_ids)) && 
	       UtilityHelper::isTrue($product->quantity, $condition->operator, $condition->item_qty)) {

	      $isTrue = true;

	      if($priceRule->logical_opr == 'OR') {
		// No need to go further. Gets out of the 2 foreach loops.
		break 2;
	      }
	    }
	  }

	  // With the AND operator all of the conditions must be true.
	  if($priceRule->logical_opr == 'AND' && !$isTrue) {
	    // Stops the checking.
	    break;
	  }
	}
      }
      //  A certain quantity of products from a specific category.
      elseif($priceRule->condition_type == 'product_cat_qty') {
        // Loops through the required conditions.
	foreach($priceRule->conditions as $condition) {
	  $nbProducts = 0;
	  $isTrue = false;
	  // Loops through the products in the cart.
	  foreach($products as $product) {
	    $catids = explode(',', $product->cat_ids);

	    // The product is part of the category targeted by the price rule.
	    if(in_array($condition->item_id, $catids)) {
	      // Appends the product quantity to the total number of products.
	      $nbProducts += $product->quantity;
	    }
	  }

	  // The number of products matches the condition.
	  if(UtilityHelper::isTrue($nbProducts, $condition->operator, $condition->item_qty)) {
	    $isTrue = true;

	    if($priceRule->logical_opr == 'OR') {
	      // Don't go further.
	      break;
	    }
	  }

	  // With the AND operator all of the conditions must be true.
	  if($priceRule->logical_opr == 'AND' && !$isTrue) {
	    // Don't go further.
	    break;
	  }
	}
      }
      //  A certain amount of products from a specific category.
      elseif($priceRule->condition_type == 'product_cat_amount') {
        // Loops through the required conditions.
	foreach($priceRule->conditions as $condition) {
	  $productAmount = 0;
	  $isTrue = false;
	  // Loops through the products in the cart.
	  foreach($products as $product) {
	    $catids = explode(',', $product->cat_ids);

	    // The product is part of the category targeted by the price rule.
	    if(in_array($condition->item_id, $catids)) {
	      // Appends the product price to the total amount of products according to
	      // the application level.
	      if($priceRule->application_level == 'before_tax') {
		$productAmount += $product->final_base_price;
	      }
	      // after tax
	      else {
		$productAmount += $product->final_price_with_tax;
	      }
	    }
	  }

	  // The amount of products matches the condition.
	  if(UtilityHelper::isTrue($productAmount, $condition->operator, $condition->item_amount)) {
	    $isTrue = true;

	    if($priceRule->logical_opr == 'OR') {
	      // Don't go further.
	      break;
	    }
	  }

	  // With the AND operator all of the conditions must be true.
	  if($priceRule->logical_opr == 'AND' && !$isTrue) {
	    // Don't go further.
	    break;
	  }
	}
      }

      if(!$isTrue) {
	// Removes the price rule if it doesn't match.
	unset($priceRules[$key]);
      }
    }
  }


  /**
   * Gets, sets then returns a product aimed to be added in an order.
   *
   * @param   integer  $productId	The id of the product.
   * @param   integer  $variantId	The id of the product variant.
   * @param   object   $user		A user object.
   * @param   array    $coupons		A coupon array (optional).
   *
   * @return  object			The product object.
   */
  public function getProduct($productId, $variantId, $user, $coupons = array())
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    // Gets the required product data.
    $query->select('p.name,p.id,p.catid,p.type,t.rate AS tax_rate,p.alias,p.nb_variants,p.shippable,'.
		   'p.weight_unit,p.dimension_unit,pv.var_id,pv.code,pv.base_price,pv.price_with_tax,pv.stock,pv.availability_delay,'.
		   'pv.name AS var_name,pv.weight,pv.length, pv.width, pv.height, pv.min_quantity, pv.max_quantity')
	  ->from('#__ketshop_product AS p')
	  ->join('INNER', '#__ketshop_product_variant AS pv ON pv.prod_id='.(int)$productId.' AND pv.var_id='.(int)$variantId)
          ->join('LEFT', '#__ketshop_tax AS t ON t.id = p.tax_id')
          ->where('p.id='.(int)$productId);
    $db->setQuery($query);
    $product = $db->loadObject();

    // Sets some product parameters.
    $product->cat_ids = $this->getCategoryIds($product->id);
    $product->price_rules = $this->getCatalogPriceRules($product, $user, $coupons);
    $product = $this->getCatalogPrice($product);
    $product->final_price_with_tax = UtilityHelper::getPriceWithTax($product->final_base_price, $product->tax_rate);

    return $product;
  }


  /**
   * Gets and returns all the products contained in a given order.
   *
   * @return array	An array of product objects.
   */
  public function getProducts($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    // 
    $query->select('*')
	  ->from('#__ketshop_order_prod')
          ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $products = $db->loadObjectList();

    $query->clear();
    $query->select('*')
	  ->from('#__ketshop_order_prule')
          ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $priceRules = $db->loadObjectList();

    foreach($products as $key => $product) {
      // Sets the price rules for each product.
      $products[$key]->price_rules = array();

      foreach($priceRules as $priceRule) {
	if($priceRule->prod_id == $product->prod_id && $priceRule->var_id == $product->var_id) {
	  $products[$key]->price_rules[] = $priceRule;
	}
      }
    }

    return $products;
  }


  /**
   * Gets and returns the detailed amounts of a given order.
   *
   * @return array	An array of detailed amount objects.
   */
  public function getDetailedAmounts($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    // 
    $query->select('*')
	  ->from('#__ketshop_order_detailed_amount')
          ->where('order_id='.(int)$order->id);
    $db->setQuery($query);

    return $db->loadObjectList();
  }


  /**
   * Gets and returns the amounts of a given order.
   *
   * @return object	An amount object.
   */
  public function getAmounts($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    // 
    $query->select('amount_excl_tax AS excl_tax, amount_incl_tax AS incl_tax,'.
                   'final_amount_excl_tax AS final_excl_tax, final_amount_incl_tax final_incl_tax')
	  ->from('#__ketshop_order')
          ->where('id='.(int)$order->id);
    $db->setQuery($query);

    return $db->loadObject();
  }


  /**
   * Stores a given product in a given order.
   *
   * @param   object  $product		A product object
   * @param   object  $order		An order object
   *
   * @return  void
   */
  public function storeProduct($product, $order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    // Ensures first that the product is not already in the order.
    $query->select('COUNT(*)')
	->from('#__ketshop_order_prod')
	->where('order_id='.(int)$order->id)
	->where('prod_id='.(int)$product->id)
	->where('var_id='.(int)$product->var_id);
    $db->setQuery($query);

    if($db->loadResult() == 0) {
      $columns = array('order_id', 'prod_id', 'var_id', 'name', 'variant_name', 'alias', 'code', 
		       'base_price', 'price_with_tax', 'final_base_price', 'final_price_with_tax',
		       'cart_rules_impact', 'quantity', 'min_quantity', 'max_quantity', 'tax_rate', 'catid', 'cat_ids');

      $values = array($order->id, $product->id, $product->var_id, $db->Quote($product->name), $db->Quote($product->var_name),
		      $db->Quote($product->alias), $db->Quote($product->code), $product->base_price, $product->price_with_tax,
		      $product->final_base_price, $product->final_price_with_tax, 0, 1, $product->min_quantity, 
		      $product->max_quantity, $product->tax_rate, $product->catid, $db->Quote(implode(',', $product->cat_ids)));

      $query->clear()
	    ->insert('#__ketshop_order_prod')
	    ->columns($columns)
	    ->values(implode(',', $values));
      $db->setQuery($query);
      $db->execute();

      // Product price rules (if any) have to be stored as well.
      $this->storeProductPriceRules($product, $order);
    }
  }


  /**
   * Returns the number of products in a given order.
   *
   * @param   object  $order	An order object.
   *
   * @return  integer		The number of products.
   */
  public function getNumberOfProducts($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('COUNT(*)')
	  ->from('#__ketshop_order_prod')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);

    return $db->loadResult();
  }


  /**
   * Returns the number of shippable products in a given order.
   *
   * @param   object  $order	An order object.
   *
   * @return  integer		The number of shippable products.
   */
  public function getNumberOfShippableProducts($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('COUNT(*)')
	  ->from('#__ketshop_order_prod AS op')
	  ->join('INNER', '#__ketshop_product AS p ON p.id=op.prod_id')
	  ->where('op.order_id='.(int)$order->id)
	  ->where('p.shippable=1');
    $db->setQuery($query);

    return $db->loadResult();
  }


  /**
   * Returns the cart price rules targeting the cart amount.
   *
   * @param   object  $order	An order object.
   *
   * @return  array		A list of price rule objects.
   */
  public function getCartAmountPriceRules($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('name, operation, behavior, value, application_level')
	  ->from('#__ketshop_order_prule')
	  ->where('order_id='.(int)$order->id)
	  ->where('type='.$db->Quote('cart'))
	  ->where('target_type='.$db->Quote('cart_amount'))
          ->order('ordering');
    $db->setQuery($query);

    return $db->loadObjectList();
  }


  /**
   * Returns the cart price rules targeting the shipping cost.
   *
   * @param   object  $order	An order object.
   *
   * @return  array		A list of price rule objects.
   */
  public function getShippingPriceRules($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('name, operation, behavior, value, application_level')
	  ->from('#__ketshop_order_prule')
	  ->where('order_id='.(int)$order->id)
	  ->where('type='.$db->Quote('cart'))
	  ->where('target_type='.$db->Quote('shipping_cost'))
          ->order('ordering');
    $db->setQuery($query);

    return $db->loadObjectList();
  }


  /**
   * Returns the weight and dimension of each shippable product in a given order.
   *
   * @param   object  $order	An order object.
   *
   * @return  array		A list of weight and dimension product.
   */
  public function getWeightsAndDimensions($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('pv.weight, pv.length, pv.width, pv.height, p.weight_unit, p.dimension_unit')
	  ->from('#__ketshop_order_prod AS op')
	  ->join('INNER', '#__ketshop_product_variant AS pv ON pv.prod_id=op.prod_id AND pv.var_id=op.var_id')
	  ->join('INNER', '#__ketshop_product AS p ON p.id=op.prod_id')
	  ->where('op.order_id='.(int)$order->id)
	  ->where('p.shippable=1');
    $db->setQuery($query);

    return $db->loadObjectList();
  }


  /**
   * Stores the price rules of a given product in a given order.
   *
   * @param   object  $product		A product object
   * @param   object  $order		An order object
   *
   * @return  void
   */
  private function storeProductPriceRules($product, $order)
  {
    if(!empty($product->price_rules)) {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      $values = array();

      foreach($product->price_rules as $priceRule) {
	$values[] = $order->id.','.$priceRule->id.','.$product->id.','.$product->var_id.','.$db->Quote($priceRule->name).','.
		    $db->Quote($priceRule->type).','.$db->Quote($priceRule->target_type).','.$db->Quote($priceRule->operation).','.
		    $db->Quote($priceRule->behavior).','.$priceRule->value.','.$db->Quote($priceRule->application_level).','.
		    $priceRule->ordering.','.$priceRule->show_rule;
      }

      $columns = array('order_id', 'prule_id', 'prod_id', 'var_id', 'name', 'type', 'target_type',
		       'operation', 'behavior', 'value', 'application_level', 'ordering', 'show_rule');

      $query->insert('#__ketshop_order_prule')
	    ->columns($columns)
	    ->values($values);
      $db->setQuery($query);
      $db->execute();
    }
  }


  /**
   * Stores the cart price rules which conditions are matching the current cart state.
   *
   * @param   array   $priceRules	A list of price rule objects.
   * @param   object  $order		An order object
   *
   * @return  void
   */
  private function storeCartPriceRules($order, $priceRules)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    // First removes the possible old cart price rules.
    $query->delete('#__ketshop_order_prule')
	  ->where('order_id='.(int)$order->id)
	  ->where('type='.$db->Quote('cart'));
    $db->setQuery($query);
    $db->execute();

    $values = array();

    foreach($priceRules as $priceRule) {
      $values[] = $order->id.','.$priceRule->id.','.$db->Quote($priceRule->name).','.
		  $db->Quote($priceRule->type).','.$db->Quote($priceRule->target_type).','.$db->Quote($priceRule->operation).','.
		  $db->Quote($priceRule->behavior).','.$priceRule->value.','.$db->Quote($priceRule->application_level).','.
		  $priceRule->ordering.','.$priceRule->show_rule;
    }

    if(!empty($values)) {
      $columns = array('order_id', 'prule_id', 'name', 'type', 'target_type', 'operation',
		       'behavior', 'value', 'application_level', 'ordering', 'show_rule');

      $query->clear();
      $query->insert('#__ketshop_order_prule')
	    ->columns($columns)
	    ->values($values);
      $db->setQuery($query);
      $db->execute();
    }
  }


  /**
   * Stores the global and detailed amounts in the corresponding tables.
   *
   * @param   object   $order		An order object
   * @param   array    $globalAmounts	An array of global amounts.
   * @param   array    $detailedAmounts	An array of detailed amounts.
   *
   * @return  void
   */
  private function storeAmounts($order, $globalAmounts, $detailedAmounts)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $fields = array('amount_excl_tax='.$globalAmounts->excl_tax, 
		    'amount_incl_tax='.$globalAmounts->incl_tax, 
		    'final_amount_excl_tax='.$globalAmounts->final_excl_tax, 
		    'final_amount_incl_tax='.$globalAmounts->final_incl_tax);

    $query->update('#__ketshop_order')
	  ->set($fields)
	  ->where('id='.(int)$order->id);
    $db->setQuery($query);
    $db->execute();

    $query->clear();
    $query->delete('#__ketshop_order_detailed_amount')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $db->execute();

    $values = array();

    foreach($detailedAmounts as $taxRate => $amount) {
      $values[] = $order->id.','.$amount->excl_tax.','.$amount->incl_tax.','.$amount->final_excl_tax.','.$amount->final_incl_tax.','.$taxRate;
    }

    $columns = array('order_id', 'excl_tax', 'incl_tax', 'final_excl_tax', 'final_incl_tax', 'tax_rate');

    $query->clear();
    $query->insert('#__ketshop_order_detailed_amount')
	  ->columns($columns)
	  ->values($values);
    $db->setQuery($query);
    $db->execute();
  }


  /**
   * Removes all of the products, price rules and detailed amounts from a given order
   * then reset its amounts.
   *
   * @param   object   $order		The order to reset.
   *
   * @return  void
   */
  public function resetOrder($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->delete('#__ketshop_order_prod')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $db->execute();

    $query->clear();
    $query->delete('#__ketshop_order_prule')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $db->execute();

    $query->clear();
    $query->delete('#__ketshop_order_detailed_amount')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $db->execute();

    $query->clear();
    $fields = array('amount_excl_tax=0', 'amount_incl_tax=0', 
		    'final_amount_excl_tax=0', 'final_amount_incl_tax=0');

    $query->update('#__ketshop_order')
	  ->set($fields)
	  ->where('id='.(int)$order->id);
    $db->setQuery($query);
    $db->execute();
  }


  /**
   * Removes a product from a given order.
   *
   * @param   integer  $productId	The product id.
   * @param   integer  $variantId	The product variant id.
   * @param   object   $order		The order to remove the product from.
   *
   * @return  void
   */
  public function removeProduct($productId, $variantId, $order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->delete('#__ketshop_order_prod')
	  ->where('order_id='.(int)$order->id)
	  ->where('prod_id='.(int)$productId)
	  ->where('var_id='.(int)$variantId);
    $db->setQuery($query);
    $db->execute();

    $query->clear();
    // Removes price rules linked to the removed product (if any).
    $query->delete('#__ketshop_order_prule')
	  ->where('order_id='.(int)$order->id)
	  ->where('prod_id='.(int)$productId)
	  ->where('var_id='.(int)$variantId);
    $db->setQuery($query);
    $db->execute();
  }


  /**
   * Updates the quantity of the products of a given order.
   *
   * @param   array    $products	An array of product objects.
   * @param   object   $order		The order to update.
   *
   * @return  void
   */
  public function updateProductQuantities($products, $order)
  {
    $products = $this->checkProductQuantities($products, $order);

    $when = '';
    foreach($products as $product) {
      $when .= ' WHEN prod_id='.$product->prod_id.' AND var_id='.$product->var_id.' THEN '.$product->quantity;
    }

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->update('#__ketshop_order_prod')
	  ->set('quantity = CASE '.$when.' ELSE quantity END')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $db->execute();
  }


  /**
   * Links the current user to the current order.
   *
   * @param   int      $userId		The id of the current user.
   * @param   object   $order		The current order.
   *
   * @return  void
   */
  public function setUserId($userId, $order)
  {
    if($order->user_id == 0) {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);

      $query->update('#__ketshop_order')
	    ->set('user_id='.(int)$userId)
	    ->where('id='.(int)$order->id);
      $db->setQuery($query);
      $db->execute();
    }
  }


  /**
   * Returns the transaction data from a given order.
   *
   * @param   object   $order	An order object.
   *
   * @return  object	 	The transaction data.
   */
  public function getTransaction($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('*')
	  ->from('#__ketshop_order_transaction')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);

    return $db->loadObject();
  }


  /**
   * Returns the shipping data from a given order.
   *
   * @param   object   $order	An order object.
   *
   * @return  object	 	The shipping data.
   */
  public function getShipping($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('*')
	  ->from('#__ketshop_order_shipping')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);

    return $db->loadObject();
  }


  /**
   * Sets the shipping for a given order.
   *
   * @param   object   $shipping	A shipping object.
   * @param   object   $order		The order for which to set shipping.
   *
   * @return  void
   */
  public function setShipping($shipping, $order)
  {
    $this->deleteShipping($order);

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $columns = array('order_id', 'shipping_id', 'name', 'delivery_type', 'status', 'shipping_cost', 'final_shipping_cost');
    $values = array($order->id, $shipping->id, $db->Quote($shipping->name), $db->Quote($shipping->delivery_type),
		    $db->Quote($shipping->status), $shipping->shipping_cost, $shipping->final_shipping_cost);

    $query->insert('#__ketshop_order_shipping')
	  ->columns($columns)
	  ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();
  }


  /**
   * Deletes the shipping from a given order.
   *
   * @param   object   $order		The order for which to delete shipping from.
   *
   * @return  void
   */
  public function deleteShipping($order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->delete('#__ketshop_order_shipping')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $db->execute();
  }


  /**
   * Checks the new product quantities to update. Modifies the quantity values if necessary.
   *
   * @param   array    $products	An array of product objects.
   * @param   object   $order		The order to update.
   *
   * @return  array			The given array of product objects.
   */
  private function checkProductQuantities($products, $order)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    // Gets both minimum and maximum quantity allowed to check for each product.
    $query->select('prod_id, var_id, min_quantity, max_quantity')
	  ->from('#__ketshop_order_prod')
	  ->where('order_id='.(int)$order->id);
    $db->setQuery($query);
    $checkings = $db->loadObjectList();

    foreach($checkings as $checking) {
      foreach($products as $key => $product) {
	if($product->prod_id == $checking->prod_id && $product->var_id == $checking->var_id && 
	   $product->quantity > $checking->max_quantity) {
	  $products[$key]->quantity = $checking->max_quantity;
	}
	elseif($product->prod_id == $checking->prod_id && $product->var_id == $checking->var_id && 
	       $product->quantity < $checking->min_quantity) {
	  $products[$key]->quantity = $checking->min_quantity;
	}
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
      }
    }

    return $products;
  }
}

