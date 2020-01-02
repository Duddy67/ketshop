<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// Create some shortcuts.
$product = $displayData['product'];
$variant = $displayData['variant'];
$params = $displayData['params'];
$view = $displayData['view'];
?>

<?php if($params->get('show_stock_state')) : ?>
  <span class="label label-default">
    <?php echo JText::_('COM_KETSHOP_STOCK_STATE_'.strtoupper($variant->stock_state)); ?>
  </span>
  <img src="<?php echo JURI::base().'media/com_ketshop/images/stock_state_'.$variant->stock_state.'.gif'; ?>"
       class="stock-icon" width="13" height="20"
       alt="<?php echo $this->escape(JText::_('COM_KETSHOP_STOCK_STATE_'.strtoupper($variant->stock_state))); ?>" />

  <span class="space-2"></span>
<?php endif; ?>

<?php if(!$variant->stock_subtract || ($variant->stock_subtract && $variant->stock)) : ?>
  <a id="product-<?php echo $product->id; ?>"
     href="<?php echo JURI::base().'index.php?option=com_ketshop&task=cart.addToCart&prod_id='.$product->id.'&slug='.$product->slug.'&catid='.$product->catid.'&var_id='.$variant->var_id; ?>">
    <span class="label btn-success">
    <?php echo JText::_('COM_KETSHOP_ADD_TO_CART'); ?>
    </span>
  </a>
  <a id="cart-product-<?php echo $product->id; ?>"
     href="<?php echo JURI::base().'index.php?option=com_ketshop&task=cart.addToCart&prod_id='.$product->id.'&slug='.$product->slug.'&catid='.$product->catid.'&var_id='.$variant->var_id; ?>">
    <img src="<?php echo JURI::base().'media/com_ketshop/images/cart_add.png'; ?>"
	 class="cart-icon" width="24" height="24"
	 alt="<?php echo $this->escape(JText::_('COM_KETSHOP_ADD_TO_CART')); ?>" /></a>
<?php else : ?>
  <span class="label btn-danger">
  <?php echo JText::_('COM_KETSHOP_UNAVAILABLE_PRODUCT'); ?>
  </span>
    <img src="<?php echo JURI::base().'media/com_ketshop/images/unavailable.png'; ?>"
	 class="cart-icon" width="24" height="24"
	 alt="<?php echo $this->escape(JText::_('COM_KETSHOP_UNAVAILABLE_PRODUCT')); ?>" />
<?php endif; ?>


