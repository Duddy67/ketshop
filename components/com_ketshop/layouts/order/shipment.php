<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// An array of shipping object or a single shipping object.
$shippings = $displayData['shippings'];
$settings = $displayData['settings'];

$isPicker = (is_array($shippings)) ? true : false;

if(!$isPicker) {
  // Puts the shipping object into an array.
  $shippings = array($shippings);
}

$colspan = ($settings->can_edit) ? 6 : 5;
?>

<tr class="shipping-row-bgr font-bold"><td colspan="<?php echo $colspan; ?>">
 <?php echo JText::_('COM_KETSHOP_SHIPPING_LABEL'); ?>
</td></tr>
<tr><td colspan="<?php echo $colspan; ?>">

<table class="table product-row">
<?php foreach($shippings as $i => $shipping) :
	// The first radio button is checked by default. 
	$checked = ($i == 0) ? 'checked' : ''; 
  ?>
  <tr>
    <?php if($isPicker) : ?>
      <td><input type="radio" name="shipping" <?php echo $checked; ?> value="<?php echo $shipping->id; ?>"></td>
    <?php endif; ?>
    <td>
      <span class="product-name"><?php echo $shipping->name; ?></span>
      <?php if($shipping->delivery_type == 'at_delivery_point') : ?>
	<span class="btn address-btn">
	    <?php echo JText::_('COM_KETSHOP_ADDRESS'); ?> <span class="icon-shop-location"></span>
	</span>
      <?php endif; ?>
    </td>
    <?php if(!empty($shipping->price_rules)) : ?>
      <td>
	<span class="striked-price small">
	  <?php echo UtilityHelper::floatFormat($shipping->shipping_cost).' '.$settings->currency; ?>
	</span>
      </td>
    <?php endif; ?>
    <td>
      <span class="price-column">
	<?php echo UtilityHelper::floatFormat($shipping->final_shipping_cost).' '.$settings->currency; ?>
      </span>
      <input type="hidden" name="shipping_cost" id="shipping-cost-<?php echo $shipping->id; ?>" value="<?php echo UtilityHelper::floatFormat($shipping->final_shipping_cost); ?>" />
    </td>
  </tr>
<?php endforeach; ?>
<?php if(!empty($shipping->price_rules)) :
	$colspan = ($isPicker) ? 4 : 3;
  ?>
    <tr><td colspan="<?php echo $colspan; ?>">
	<?php foreach($shipping->price_rules as $i => $priceRule) : 
	        $br = ($i > 0) ? '<br />' : '';
                echo $br;
	   ?>
           <span class="rule-name small"><?php echo $priceRule->name; ?></span>
	   <span class="tax-method"><?php echo UtilityHelper::formatPriceRule($priceRule->operation, $priceRule->value); ?></span>
	<?php endforeach; ?>
    </td></tr>
<?php endif; ?>
</table>
</td></tr>

