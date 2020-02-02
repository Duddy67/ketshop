<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// An array of shipping object or a single shipping object.
$paymentModes = $displayData['payment_modes'];
$settings = $displayData['settings'];

$isPicker = (is_array($paymentModes)) ? true : false;

if(!$isPicker) {
  // Puts the shipping object into an array.
  $paymentModes = array($paymentModes);
}

$colspan = ($settings->can_edit) ? 6 : 5;
?>

<tr class="shipping-row-bgr font-bold"><td colspan="<?php echo $colspan; ?>">
 <?php echo JText::_('COM_KETSHOP_PAYMENT_MODE_LABEL'); ?>
</td></tr>
<tr><td colspan="<?php echo $colspan; ?>">

  <table class="table product-row no-bottom">
    <?php foreach($paymentModes as $i => $paymentMode) :
	    // The first radio button is checked by default. 
	    $checked = ($i == 0) ? 'checked' : ''; 
      ?>
      <tr>
	<?php if($isPicker) : ?>
	  <td><input type="radio" name="payment_mode" <?php echo $checked; ?> value="<?php echo $paymentMode->id; ?>"></td>
	<?php endif; ?>
	<td>
	  <span class="product-name"><?php echo $paymentMode->name; ?></span>
	</td>
	<td>
	  <?php echo $paymentMode->description; ?>
	</td>
      </tr>
    <?php endforeach; ?>
  </table>
</td></tr>

