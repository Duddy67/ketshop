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
?>

<tr class="shipping-row-bgr font-bold"><td colspan="5">
 <?php echo JText::_('COM_KETSHOP_SHIPPING_LABEL'); ?>
</td></tr>
<tr><td colspan="5">

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
</table>
</td></tr>

