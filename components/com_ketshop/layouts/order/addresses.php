<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

$order = $displayData['order'];
$settings = $displayData['settings'];

// Sets the billing and shipping addresses
$addresses = array('billing' => $order->addresses['billing']);
$address = (isset($order->addresses['shipping'])) ? $order->addresses['shipping'] : $order->addresses['billing'];
$addresses['shipping'] = $address;
?>

<?php foreach($addresses as $key => $address) : ?>
  <h4><?php echo JText::_('COM_KETSHOP_'.strtoupper($key).'_ADDRESS_TITLE'); ?></h4>
  <table class="table table-condensed">
    <?php if(isset($address->company) && !empty($address->company)) : ?>
      <tr><td><?php echo $address->company; ?></td></tr>
    <?php else : ?>
      <tr><td><?php echo $order->firstname.' '.$order->lastname; ?></td></tr>
    <?php endif; ?>
    <tr><td><?php echo $address->street; ?></td></tr>
    <?php if(!empty($address->additional)) : ?>
      <tr><td><?php echo $address->additional; ?></td></tr>
    <?php endif; ?>
    <tr><td><?php echo $address->postcode; ?></td></tr>
    <tr><td><?php echo $address->city; ?></td></tr>
    <tr><td><?php echo $address->country_code; ?></td></tr>
  </table>
<?php endforeach; ?>

