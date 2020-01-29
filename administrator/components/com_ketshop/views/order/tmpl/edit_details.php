<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 
?>

<table class="table product-row end-table">
  <?php echo JLayoutHelper::render('order.product_header', $this->shop_settings, $this->layout_path); ?>
  <?php echo JLayoutHelper::render('order.product_rows', array('products' => $this->item->products, 'settings' => $this->shop_settings, 'params' => $this->params), $this->layout_path); ?>
  <?php echo JLayoutHelper::render('order.amounts', array('amounts' => $this->item->amounts, 'detailed_amounts' => $this->item->detailed_amounts, 'settings' => $this->shop_settings, 'params' => $this->params), $this->layout_path); ?>
  <?php echo JLayoutHelper::render('order.shipment', array('shippings' => $this->item->shipping, 'settings' => $this->shop_settings), $this->layout_path); ?>
  <?php echo JLayoutHelper::render('order.total_amount', array('amounts' => $this->item->amounts, 'settings' => $this->shop_settings), $this->layout_path); ?>
</table>

