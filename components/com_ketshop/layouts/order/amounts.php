<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

$detailedAmounts = $displayData['detailed_amounts'];
$amounts = $displayData['amounts'];
$settings = $displayData['settings'];

$cartPriceRules = (!empty($amounts->price_rules)) ? true : false;

$colspan = ($settings->can_edit) ? 6 : 5;
?>

<tr class="amount-row-bgr font-bold"><td colspan="<?php echo $colspan; ?>">
 <?php echo JText::_('COM_KETSHOP_DETAILED_AMOUNTS_LABEL'); ?>
</td></tr>
<tr><td colspan="<?php echo $colspan; ?>">

<table class="table-condensed amounts">
<?php foreach($detailedAmounts as $detailedAmount) : ?>
  <tr>
  <?php if($detailedAmount->incl_tax != $detailedAmount->final_incl_tax) : ?>
    <td class="striked-price-column"><?php echo UtilityHelper::floatFormat($detailedAmount->incl_tax).' '.$settings->currency; ?></td>
  <?php else : ?>
    <td></td>
  <?php endif; ?>

  <td class="price-column">
    <?php echo UtilityHelper::floatFormat($detailedAmount->final_incl_tax).' '.$settings->currency; ?>
    <span class="tax-method"><?php echo JText::_('COM_KETSHOP_FIELD_INCL_TAX_LABEL'); ?></span></td>
  <td class="small"><?php echo $detailedAmount->tax_rate.' %'; ?></td></tr>
        
<?php endforeach; ?>
</table>
</td></tr>
<tr class="amount-row-bgr font-bold"><td colspan="<?php echo $colspan; ?>">
 <?php echo JText::_('COM_KETSHOP_AMOUNTS_LABEL'); ?>
</td></tr>
<tr><td colspan="<?php echo $colspan; ?>">

<table class="table-condensed amounts">
  <tr>
    <?php if($cartPriceRules) : // Displays price rule names. ?>
      <td rowspan="2">
	<?php foreach($amounts->price_rules as $i => $priceRule) : 
	        $br = ($i > 0) ? '<br />' : '';
                echo $br;
	   ?>
           <span class="rule-name small"><?php echo $priceRule->name; ?></span>
	   <span class="tax-method"><?php echo UtilityHelper::formatPriceRule($priceRule->operation, $priceRule->value); ?></span>
	<?php endforeach; ?>
      </td>
    <?php endif; ?>
  <?php if($cartPriceRules) : ?>
    <td class="striked-price-column">
     <?php echo UtilityHelper::floatFormat($amounts->excl_tax).' '.$settings->currency; ?>
    </td>
  <?php else : ?>
    <td></td>
  <?php endif; ?>

    <td class="price-column">
      <?php echo UtilityHelper::floatFormat($amounts->final_excl_tax).' '.$settings->currency; ?>
      <span class="tax-method"><?php echo JText::_('COM_KETSHOP_FIELD_EXCL_TAX_LABEL'); ?></span>
    </td>
  </tr>
  <tr>
  <?php if($amounts->incl_tax != $amounts->final_incl_tax) : ?>
    <td class="striked-price-column"><?php echo UtilityHelper::floatFormat($amounts->incl_tax).' '.$settings->currency; ?></td>
  <?php else : ?>
    <td></td>
  <?php endif; ?>

    <td class="price-column">
      <?php echo UtilityHelper::floatFormat($amounts->final_incl_tax).' '.$settings->currency; ?>
      <span class="tax-method"><?php echo JText::_('COM_KETSHOP_FIELD_INCL_TAX_LABEL'); ?></span>
    </td>
  </tr>
</table>
</td></tr>

