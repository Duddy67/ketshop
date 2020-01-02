<?php
/**
 * @package KetShop
 * @copyright Copyright (c)2016 - 2018 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

$product = $displayData['product'];
$variants = $displayData['variants'];
$params = $displayData['params'];
?>

<h4 class="product-variants-title"><?php echo JText::_('COM_KETSHOP_PRODUCT_VARIANTS_TITLE'); ?></h4>
<div class="variant-picker">
  <?php foreach($variants as $variant) : ?>
    <div class="variant-selector" id="variant-selector-<?php echo $product->id; ?>-<?php echo $variant->var_id; ?>">
      <a href="#" onclick="return false;" id="variant-picker-<?php echo $product->id; ?>-<?php echo $variant->var_id; ?>"><?php echo $variant->name; ?></a>
    </div>
  <?php endforeach; ?>
</div>
