<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

$attributes = $displayData['filter_attribute'];
$state = $displayData['state'];
?>

<?php if($attributes !== null) : ?>
  <div class="ketshop-toolbar clearfix attribute-filters">
   <h3><?php echo JText::_('COM_KETSHOP_ATTRIBUTE_FILTERS');?></h3>
  <?php
	// Generates the attribute drop down lists.
	foreach($attributes as $attribute) {
	  echo '<div class="attribute-filter">';
	  echo '<p>'.$attribute['name'].'</p>';

	  // Builds a dynamic name for each attribute.
	  $name = 'filter_attribute_'.$attribute['id'].'[]';
	  // Retrieves the selected options from the Joomla variable session.
	  $selectedOptions = $state->get('list.filter_attribute_'.$attribute['id']);

	  // The drop down lists are multiple type by default.
	  // N.B: The onchange attribute is managed in the filters.js file. 
	  echo '<select name="'.$name.'" id="filter_attribute_'.$attribute['id'].'" class="attribute-select" multiple>';

	  foreach($attribute['options'] as $option) {
	    $selected = '';
	    if(in_array($option['option_value'], $selectedOptions)) {
	      $selected = 'selected="selected"';
	    }

	    echo '<option value="'.$option['option_value'].'" '.$selected.'>'.$option['option_text'].'</option>';
	  }

	  echo '</select></div>';
	}
   ?>

    <button type="button" onclick="ketshop.clearAttributeFilters()" class="btn hasTooltip js-stools-btn-clear attribute-btn-clear"
	    title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
	<?php echo JText::_('JSEARCH_FILTER_CLEAR');?></button>
  </div>
<?php endif; ?>

