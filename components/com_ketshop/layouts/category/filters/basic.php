<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// Create shortcuts.
$filterForm = $displayData['filter_form'];
$params = $displayData['params'];
$state = $displayData['state'];
?>

<?php if($params->get('filter_field') != 'hide' || $params->get('show_pagination_limit') || $params->get('filter_ordering')) : ?>
  <div class="ketshop-toolbar clearfix">
    <?php
	  // Gets the filter fields.
	  $fieldset = $filterForm->getFieldset('filter');

	  // Loops through the fields.
	  foreach($fieldset as $field) {
	    $filterName = $field->getAttribute('name');

	    if($filterName == 'filter_search' && $params->get('filter_field') != 'hide') { ?>
	      <div class="btn-group input-append span6">
	    <?php
		  // Sets the proper hint to display according to the chosen filter (ie: title or author).
		  $hint = JText::_('COM_KETSHOP_'.$params->get('filter_field').'_FILTER_LABEL');
		  $filterForm->setFieldAttribute($filterName, 'hint', $hint); 
		  // Displays only the input tag (without the div around).
		  echo $filterForm->getInput($filterName, null, $state->get('list.'.$filterName));
		  // Adds the search and clear buttons.  ?>
	      <button type="submit" onclick="ketshop.submitForm();" class="btn hasTooltip"
		      title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
		  <i class="icon-search"></i></button>

	      <button type="button" onclick="ketshop.clearSearchFilter()" class="btn hasTooltip js-stools-btn-clear"
		      title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
		  <?php echo JText::_('JSEARCH_FILTER_CLEAR');?></button>
	      </div>
    <?php	}
	    elseif(($filterName == 'filter_ordering' && $params->get('filter_ordering')) ||
		   ($filterName == 'limit' && $params->get('show_pagination_limit'))) {
	      // Sets the field value to the currently selected value.
	      $field->setValue($state->get('list.'.$filterName));
	      echo $field->renderField(array('hiddenLabel' => true, 'class' => 'span3 ketshop-filters'));
	    }
	  }
     ?>
   </div>
<?php endif; ?>

