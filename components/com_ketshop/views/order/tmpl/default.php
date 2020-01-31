<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// no direct access
defined('_JEXEC') or die;
?>

<div class="blog">

<span style="float:right;" class="btn" onclick="document.getElementById('ketshop_order').submit();">
    <?php echo JText::_('COM_KETSHOP_BACK_BUTTON'); ?> <span class="icon-shop-spinner11"></span></a>
</span>

<form action="<?php echo JRoute::_('index.php?option=com_ketshop&view=orders', false); ?>" method="post" id="ketshop_order">
    <div class="order-status">
	<span><?php echo JText::_('COM_KETSHOP_FIELD_ORDER_STATUS_LABEL'); ?>:</span>
	<span><?php echo JText::_('COM_KETSHOP_OPTION_'.$this->order->status.'_STATUS'); ?></span>
    </div>
    <table class="table product-row end-table">
    <?php echo JLayoutHelper::render('order.product_header', $this->shop_settings); ?>
    <?php echo JLayoutHelper::render('order.product_rows', array('products' => $this->order->products, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>
    <?php echo JLayoutHelper::render('order.amounts', array('amounts' => $this->order->amounts, 'detailed_amounts' => $this->order->detailed_amounts, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>

    <?php 
      if($this->order->shippable) {
	echo JLayoutHelper::render('order.shipment', array('shippings' => $this->order->shipping, 'settings' => $this->shop_settings)); 
      }
    ?>

    <?php echo JLayoutHelper::render('order.total_amount', array('amounts' => $this->order->amounts, 'settings' => $this->shop_settings)); ?>
    <?php //echo JLayoutHelper::render('order.payment', array('payment_modes' => $this->payment_modes, 'settings' => $this->shop_settings)); ?>
    </table>

    <?php echo JLayoutHelper::render('order.addresses', array('order' => $this->order, 'settings' => $this->shop_settings)); ?>

    <span class="btn" onclick="document.getElementById('ketshop_order').submit();">
	<?php echo JText::_('COM_KETSHOP_BACK_BUTTON'); ?> <span class="icon-shop-spinner11"></span></a>
    </span>
  </form>
</div>

