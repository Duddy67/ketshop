<?php
/**
 * @package KetShop
 * @copyright Copyright (c)2012 - 2016 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// Create some shortcuts.
$variant = $displayData['variant'];
$params = $displayData['params'];
$settings = $displayData['shop_settings'];

$showPrice2 = true;
if(($settings->price_display == 'incl_tax' && !$params->get('show_price_excl_tax')) || 
   ($settings->price_display == 'excl_tax' && !$params->get('show_price_incl_tax'))) {
  $showPrice2 = false;
}
?>


<?php if($params->get('show_price')) : ?>
  <div class="price-container">
  <?php if(!empty($variant->price_rules)) : // Display price rules. 
	  if($params->get('show_rule_name')) : ?>
	    <span class="space-1"></span>
	    <div class="price-rules">
	    <?php foreach($variant->price_rules as $priceRule) : ?>
		<span class="rule-name"><?php echo $priceRule->name; ?></span>
		<span class="label label-warning">
		  <?php echo UtilityHelper::formatPriceRule($priceRule->operation, $priceRule->value); ?>
		</span>
		<span class="space-1"></span>
		<?php if(!empty($priceRule->description)) : ?>
		  <div class="rule-description"><?php echo $priceRule->description; ?></div>
		<?php endif; ?>
	    <?php endforeach; ?>
	    </div>
	    <span class="space-1 clear-both"></span>
	<?php endif; ?>
<?php
	$strikedPrice1 = ($settings->price_display == 'incl_tax') ? $variant->price_with_tax : $variant->base_price;
	$strikedPrice2 = ($settings->price_display == 'incl_tax') ? $variant->base_price : $variant->price_with_tax;
	$class = 'striked-price';

	// cart or order view.
	if($params->get('product_price') !== null) {
	  $class = 'striked-price-column';

	  if($params->get('product_price') == 'by_quantity') {
	    $strikedPrice1 = $strikedPrice1 * $variant->quantity;
	    $strikedPrice2 = $strikedPrice2 * $variant->quantity;
	  }
	}
?>
    <div class="prices">
    <span class="<?php echo $class; ?>">
	<?php echo UtilityHelper::floatFormat($strikedPrice1); ?>
	<?php echo $settings->currency; ?>
      </span>
      <span class="space-1"></span>

      <?php if($showPrice2) : ?>
	<span class="striked-price small">
	  <?php echo UtilityHelper::floatFormat($strikedPrice2); ?>
	  <?php echo $settings->currency; ?>
	</span>
      <?php endif; ?>
    </div>
  <?php else : ?>
    <span class="space-1"></span>
  <?php endif;

	$price1 = ($settings->price_display == 'incl_tax') ? $variant->final_price_with_tax : $variant->final_base_price;
	$label1 = ($settings->price_display == 'incl_tax') ? 'COM_KETSHOP_FIELD_INCL_TAX_LABEL' : 'COM_KETSHOP_FIELD_EXCL_TAX_LABEL';
	$price2 = ($settings->price_display == 'incl_tax') ? $variant->final_base_price : $variant->final_price_with_tax;
	$label2 = ($settings->price_display == 'incl_tax') ? 'COM_KETSHOP_FIELD_EXCL_TAX_LABEL' : 'COM_KETSHOP_FIELD_INCL_TAX_LABEL';
	$class = 'price';
	$label = 'label label-default';

	// cart or order view.
	if($params->get('product_price')) {
	  $class = 'price-column';
	  $label = 'tax-method';

	  if($params->get('product_price') == 'by_quantity') {
	    $price1 = $price1 * $variant->quantity;
	    $price2 = $price2 * $variant->quantity;
	  }
	}
?>
    <div class="prices">
      <span class="<?php echo $class; ?>"><?php echo UtilityHelper::floatFormat($price1); ?>
      <?php echo $settings->currency; ?></span>
      <span class="<?php echo $label; ?>"><?php echo JText::_($label1); ?></span>
      <span class="space-1"></span>

      <?php if($showPrice2) : ?>
	<span class="<?php echo $class; ?> small"><?php echo UtilityHelper::floatFormat($price2); ?>
	<?php echo $settings->currency; ?></span>
	<span class="<?php echo $label; ?> small"><?php echo JText::_($label2); ?></span>
      <?php endif; ?>
    </div>
  </div>

<?php endif; ?>
