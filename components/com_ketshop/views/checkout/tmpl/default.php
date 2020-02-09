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

<?php if(!empty($this->shippings)) : ?>

  <form action="index.php?option=com_ketshop&task=checkout.updateCart" method="post" id="ketshop_checkout">
    <table class="table product-row end-table">

    <?php echo JLayoutHelper::render('order.product_header', $this->shop_settings); ?>
    <?php echo JLayoutHelper::render('order.product_rows', array('products' => $this->order->products, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>
    <?php echo JLayoutHelper::render('order.amounts', array('amounts' => $this->order->amounts, 'detailed_amounts' => $this->order->detailed_amounts, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>
    <?php echo JLayoutHelper::render('order.total_amount', array('amounts' => $this->order->amounts, 'settings' => $this->shop_settings)); ?>

    <tr><td colspan="6">
      <span class="btn" onclick="document.getElementById('ketshop_checkout').submit();">
	  <?php echo JText::_('COM_KETSHOP_UPDATE_CART'); ?> <span class="icon-shop-loop2"></span></a>
      </span>
   </td/></tr>

   <?php
         if($this->order->shippable) {
	   echo JLayoutHelper::render('order.shipment', array('shippings' => $this->shippings, 'settings' => $this->shop_settings)); 
	 }

	 echo JLayoutHelper::render('order.payment', array('payment_modes' => $this->payment_modes, 'settings' => $this->shop_settings));
    ?>
    </table>

    <?php echo JLayoutHelper::render('order.addresses', array('order' => $this->order, 'settings' => $this->shop_settings)); ?>

    <span class="btn">
    <a id="pay" href="<?php echo JRoute::_('index.php?option=com_ketshop&task=checkout.pay&payment_id=0&shipping_id=0', false); ?>" class="btn-link ketshop-btn">
	<?php echo JText::_('COM_KETSHOP_PAY_NOW'); ?> <span class="icon-shop-credit-card"></span></a>
    </span>
  </form>

<?php else : ?>
<?php endif; ?>
</div>

<?php
// Load jQuery library before our script.
JHtml::_('jquery.framework');
// Loads the jQuery scripts.
$doc = JFactory::getDocument();
$doc->addScript(JURI::root().'components/com_ketshop/js/checkout.js');
$doc->addScript(JURI::root().'components/com_ketshop/js/cart.js');

