<?php
/**
 * @package KetShop
 * @copyright Copyright (c)2012 - 2016 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// Create a shortcut for params.
$product = $displayData['product'];
$variant = $displayData['variant'];
$params = $displayData['params'];

//Test if product weight and/or dimensions must be displayed. 
$weight = $dimensions = false;
if($params->get('show_weight')
    && ($params->get('weight_location') == $product->weight_location || $params->get('weight_location') == 'both')
    && $variant->weight != 0) {
  $weight = true;
}

if($params->get('show_dimensions')
   && ($params->get('dimensions_location') == $product->dimensions_location
   || $params->get('dimensions_location') == 'both')
   && $variant->length != 0 && $variant->width != 0 && $variant->height != 0) {
  $dimensions = true;
}
?>

<?php if($weight || $dimensions) : ?>
  <table class="table table-condensed small">

  <?php if($weight) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_WEIGHT'); ?>
    </td><td>
      <?php echo UtilityHelper::floatFormat($variant->weight); ?>
      <?php echo $product->weight_unit; ?>
    </td></tr>
  <?php endif; ?>

  <?php if($dimensions) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_LENGTH'); ?>
    </td><td>
      <?php echo UtilityHelper::floatFormat($variant->length); ?>
      <?php echo $product->dimension_unit; ?>
    </td></tr>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_WIDTH'); ?>
    </td><td>
      <?php echo UtilityHelper::floatFormat($variant->width); ?>
      <?php echo $product->dimension_unit; ?>
    </td></tr>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_HEIGHT'); ?>
    </td><td>
      <?php echo UtilityHelper::floatFormat($variant->height); ?>
      <?php echo $product->dimension_unit; ?>
    </td></tr>
  <?php endif; ?>
  </table>
<?php endif; ?>

