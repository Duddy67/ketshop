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
    <?php echo JLayoutHelper::render('order.product_rows', array('products' => $this->products, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>
    <?php echo JLayoutHelper::render('order.amounts', array('amounts' => $this->amounts, 'detailed_amounts' => $this->detailed_amounts, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>
    <?php echo JLayoutHelper::render('order.total_amount', array('amounts' => $this->amounts, 'settings' => $this->shop_settings)); ?>

    <tr><td colspan="6">
      <span class="btn">
	<a href="#" class="btn-link ketshop-btn" onclick="document.getElementById('ketshop_checkout').submit();">
	  <?php echo JText::_('COM_KETSHOP_UPDATE_CART'); ?> <span class="icon-shop-loop2"></span></a>
      </span>
   </td/></tr>

    <?php echo JLayoutHelper::render('order.shipment', array('shippings' => $this->shippings, 'settings' => $this->shop_settings)); ?>
    <?php echo JLayoutHelper::render('order.payment', array('payment_modes' => $this->payment_modes, 'settings' => $this->shop_settings)); ?>
    </table>

    <span class="btn">
    <a id="proceed" href="<?php echo JRoute::_('index.php?option=com_ketshop&task=checkout.proceed&payment_id=0&shipping_id=0', false); ?>" class="btn-link ketshop-btn">
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
$doc->addScript(JURI::root().'components/com_ketshop/js/shipment.js');
$doc->addScript(JURI::root().'components/com_ketshop/js/cart.js');

