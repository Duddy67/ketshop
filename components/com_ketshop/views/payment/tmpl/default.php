<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// no direct access
defined('_JEXEC') or die;
?>

<form action="index.php?option=com_ketshop&task=payment.pay" method="post" id="ketshop_payment">
  <div>
   <h3><?php echo $this->payment_mode->name; ?></h3>
    <?php if(!empty($this->payment_mode->information)) : ?>
      <div><?php echo $this->payment_mode->information; ?></div>
    <?php endif; ?>

    <span class="btn">
    <a href="<?php echo JRoute::_('index.php?option=com_ketshop&view=checkout', false); ?>" class="btn-link ketshop-btn">
	<?php echo JText::_('COM_KETSHOP_CANCEL'); ?> <span class="icon-remove"></span></a>
    </span>

    <span class="btn">
      <a href="#" class="btn-link ketshop-btn" onclick="document.getElementById('ketshop_payment').submit();">
	<?php echo JText::_('COM_KETSHOP_PAY_NOW'); ?> <span class="icon-shop-credit-card"></span></a>
    </span>

  <input type="hidden" name="plugin_element" value="<?php echo $this->payment_mode->plugin_element; ?>" />
  </div>
</form>
