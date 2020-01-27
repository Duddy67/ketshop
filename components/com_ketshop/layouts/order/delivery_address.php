<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// An array of shipping object or a single shipping object.
$address = $displayData['delivery_address'];
$customer = $displayData['customer'];
$settings = $displayData['settings'];
?>
<h4><?php echo JText::_('COM_KETSHOP_DELIVERY_ADDRESS_TITLE'); ?></h4>
<table class="table table-condensed">
  <?php if(isset($address->company) && !empty($address->company)) : ?>
    <tr><td><?php echo $address->company; ?></td></tr>
  <?php else : ?>
    <tr><td><?php echo $customer->firstname.' '.$customer->lastname; ?></td></tr>
  <?php endif; ?>
  <tr><td><?php echo $address->street; ?></td></tr>
  <?php if(!empty($address->additional)) : ?>
    <tr><td><?php echo $address->additional; ?></td></tr>
  <?php endif; ?>
  <tr><td><?php echo $address->postcode; ?></td></tr>
  <tr><td><?php echo $address->city; ?></td></tr>
  <tr><td><?php echo $address->country_code; ?></td></tr>
</table>

