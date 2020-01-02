<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
?>

<div class="blog purchase">
<!--<h1 class="icon-shop-cart" style="margin-left:10px;font-size:24px;"></h1>-->

<?php if(!empty($this->products)) : // Makes sure there is something within the cart. ?>

  <form action="index.php?option=com_ketshop&task=cart.updateCart" method="post" id="ketshop_cart">
    <table class="table product-row end-table">

    <?php echo JLayoutHelper::render('order.product_header', $this->shop_settings); ?>
    <?php echo JLayoutHelper::render('order.product_rows', array('products' => $this->products, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>
    <?php echo JLayoutHelper::render('order.amounts', array('amounts' => $this->amounts, 'detailed_amounts' => $this->detailed_amounts, 'settings' => $this->shop_settings, 'params' => $this->params)); ?>

    </table>

    <span class="btn">
      <a href="#" class="btn-link ketshop-btn" onclick="document.getElementById('ketshop_cart').submit();">
	<?php echo JText::_('COM_KETSHOP_UPDATE_CART'); ?> <span class="icon-shop-loop2"></span></a>
    </span>

    <span class="btn">
      <a href="index.php?option=com_ketshop&task=cart.emptyCart" class="btn-link ketshop-btn" id="empty-cart">
	<?php echo JText::_('COM_KETSHOP_EMPTY_CART'); ?> <span class="icon-shop-bin"></span></a>
    </span>

    <span class="btn">
    <a href="<?php echo JRoute::_('index.php?option=com_ketshop&view=connection', false); ?>" class="btn-link ketshop-btn">
	<?php echo JText::_('COM_KETSHOP_ORDER_NOW'); ?> <span class="icon-shop-file-text2"></span></a>
    </span>
  </form>

<?php else : // Cart is empty. ?>
  <div class="alert alert-no-items">
    <?php echo JText::_('COM_KETSHOP_CART_EMPTY'); ?>
  </div>
<?php endif; ?>
</div>

<?php
// Load jQuery library before our script.
JHtml::_('jquery.framework');
// Loads the jQuery scripts.
$doc = JFactory::getDocument();
$doc->addScript(JURI::root().'components/com_ketshop/js/cart.js');

