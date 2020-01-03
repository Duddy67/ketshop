<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

$products = $displayData['products'];
$settings = $displayData['settings'];
$params = $displayData['params'];
?>

<?php foreach($products as $key => $product) : // Loops throught the products. 

        $url = JRoute::_(KetshopHelperRoute::getProductRoute($product->prod_id.':'.$product->alias, $product->catid));
	// Computes the class name according to $key value (ie: odd or even number).
	$class = ($key % 2) ? 'odd' : 'even';
?>
      <tr class="<?php echo $class; ?>">
      <td>
	<div class="product-name">
          <a href="<?php echo $url; ?>" class="font-bold" target="_blank"><?php echo $product->name.' '.$product->variant_name; ?></a>
        </div>

	<div class="price-rules">
	  <?php foreach($product->price_rules as $priceRule) : ?>
		<span class="rule-name small"><?php echo $priceRule->name; ?></span>
		<span class="tax-method">
		  <?php echo UtilityHelper::formatPriceRule($priceRule->operation, $priceRule->value); ?>
		</span>
		<span class="space-0"></span>
	  <?php endforeach; ?>
	</div>
      </td><td class="cart-prices">
	<?php
	      $params->set('product_price', 'by_unit');
	      echo JLayoutHelper::render('product.price', array('variant' => $product, 'params' => $params, 'shop_settings' => $settings)); ?>
      </td><td class="quantity-column">
       <?php if(isset($settings->can_edit) && $settings->can_edit) : ?>
	   <input class="quantity" type="text" name="quantity_<?php echo $product->prod_id; ?>_<?php echo $product->var_id; ?>"
		  id="quantity_product_<?php echo $product->prod_id; ?>_<?php echo $product->var_id; ?>"
		  value="<?php echo $product->quantity; ?>" />
       <?php else : ?>
	  <span class="muted"><?php echo $product->quantity; ?></span>
       <?php endif; ?>
      </td><td class="cart-prices">
	<?php
	      $params->set('product_price', 'by_quantity');
	      echo JLayoutHelper::render('product.price', array('variant' => $product, 'params' => $params, 'shop_settings' => $settings)); ?>
      </td><td class="small center">
        <?php echo $product->tax_rate.' %'; ?>
      </td>
       <?php if($settings->can_edit) : ?>
	 <td>
	    <a class="btn" id="remove-product-<?php echo $product->prod_id; ?>-<?php echo $product->var_id; ?>" href="<?php echo 'index.php?option=com_ketshop&task='.$settings->view_name.'.removeFromCart&prod_id='.$product->prod_id.'&var_id='.$product->var_id; ?>"><span class="icon-shop-bin"></span></a> 
	 </td>
       <?php endif; ?>
      </tr>
<?php endforeach; ?>

