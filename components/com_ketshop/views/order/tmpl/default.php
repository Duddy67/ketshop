<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// no direct access
defined('_JEXEC') or die;

    //echo '<pre>';
    //var_dump($this->shippings);
    //echo '</pre>';
?>

<div class="blog purchase">


  <form action="index.php?option=com_ketshop&task=checkout.updateCart" method="post" id="ketshop_checkout">
    <table class="table product-row end-table">

    <?php echo JLayoutHelper::render('order.product_header', $this->shop_settings); ?>
    <?php echo JLayoutHelper::render('order.product_rows', array('products' => $this->products, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>
    <?php echo JLayoutHelper::render('order.amounts', array('amounts' => $this->amounts, 'detailed_amounts' => $this->detailed_amounts, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>
    <?php echo JLayoutHelper::render('order.total_amount', array('amounts' => $this->amounts, 'settings' => $this->shop_settings)); ?>

    <?php //echo JLayoutHelper::render('order.shipment', array('shippings' => $this->shippings, 'settings' => $this->shop_settings)); ?>
    <?php //echo JLayoutHelper::render('order.payment', array('payment_modes' => $this->payment_modes, 'settings' => $this->shop_settings)); ?>
    </table>

  </form>

</div>

