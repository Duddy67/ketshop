<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

$manufacturers = $displayData['filter_manufacturer'];
$state = $displayData['state'];
?>

<?php if($manufacturers !== null) : ?>
   <h3><?php echo JText::_('COM_KETSHOP_MANUFACTURER_FILTER');?></h3>
  <?php  
	// The drop down lists are multiple type by default.
        // N.B: The onchange attribute is managed in the filters.js file. 
	echo '<select name="filter_manufacturer[]" id="filter_manufacturer" class="manufacturer-select" multiple>';

        // Retrieves the selected options from the Joomla variable session.
	$selectedOptions = $state->get('list.filter_manufacturer');

	foreach($manufacturers as $option) {
	  $selected = '';
	  if(in_array($option->value, $selectedOptions)) {
	    $selected = 'selected="selected"';
	  }

	  echo '<option value="'.$option->value.'" '.$selected.'>'.$option->text.'</option>';
	}

	echo '</select>';
   ?>

    <button type="button" onclick="ketshop.clearManufacturerFilter()" class="btn hasTooltip js-stools-btn-clear manufacturer-btn-clear"
	    title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
	<?php echo JText::_('JSEARCH_FILTER_CLEAR');?></button>
<?php endif; ?>

