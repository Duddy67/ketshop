<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

$amounts = $displayData['amounts'];
$settings = $displayData['settings'];
?>

<tr class="amount-row-bgr font-bold"><td colspan="6">
 <?php echo JText::_('COM_KETSHOP_TOTAL_AMOUNT_LABEL'); ?>
</td></tr>
<tr><td colspan="6" class="price-column lead">
  <div class="text-right"><span id="total-amount"><?php echo UtilityHelper::floatFormat($amounts->total_amount); ?></span> <?php echo $settings->currency; ?> <span class="tax-method"><?php echo JText::_('COM_KETSHOP_FIELD_INCL_TAX_LABEL'); ?></span></div>
<input type="hidden" name="original_amount" id="original-amount" value="<?php echo UtilityHelper::floatFormat($amounts->total_amount); ?>" />
</td></tr>
