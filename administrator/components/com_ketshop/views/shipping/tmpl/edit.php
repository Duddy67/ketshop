<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
?>

<script type="text/javascript">
Joomla.submitbutton = function(task)
{
  if(task == 'shipping.cancel' || document.formvalidator.isValid(document.getElementById('shipping-form'))) {
    Joomla.submitform(task, document.getElementById('shipping-form'));
  }
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_ketshop&view=shipping&layout=edit&id='.(int) $this->item->id); ?>" 
 method="post" name="adminForm" id="shipping-form" enctype="multipart/form-data" class="form-validate">

  <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

  <div class="form-horizontal">

    <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_KETSHOP_TAB_DETAILS')); ?>

      <div class="row-fluid">
	<div class="span4">
	  <div class="form-vertical">
	    <?php
		  // Existing item.
		  if($this->item->id) {
		    // Turns the original select element into a hidden field as the user is no longer allowed to change the item type.
		    $this->form->setFieldAttribute('delivery_type', 'type', 'hidden');
		    // Sets and displays the delivery type value for information.
		    $this->form->setValue('delivery_type_info', null,JText::_('COM_KETSHOP_OPTION_'.strtoupper($this->item->delivery_type)));
		    echo $this->form->renderField('delivery_type_info');
		  }

		  echo $this->form->renderField('delivery_type');
		  echo $this->form->renderField('delivpnt_cost');
		  echo $this->form->renderField('weight_type');
		  echo $this->form->renderField('weight_unit');
		  echo $this->form->renderField('volumetric_ratio');
		  echo $this->form->renderField('min_weight');
		  echo $this->form->renderField('max_weight');
		  echo $this->form->renderField('min_product');
		  echo $this->form->renderField('max_product');
		  echo $this->form->renderField('min_delivery_delay');
	      ?>
	  </div>
	</div>

	<div id="address" class="span4 form-vertical">
	  <?php
		echo $this->form->renderField('street');
		echo $this->form->renderField('city');
		echo $this->form->renderField('postcode');
		echo $this->form->renderField('region_code');
		echo $this->form->renderField('country_code');
		echo $this->form->renderField('phone');
	    ?>
	</div>

	<div class="span4 form-vertical">
	  <?php
		echo JLayoutHelper::render('joomla.edit.global', $this);
		echo $this->form->renderField('plugin_element');
		echo $this->form->renderField('description');
		$this->form->setValue('currency_info', null, UtilityHelper::getCurrency());
		echo $this->form->renderField('currency_info');
	    ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'postcode-tab', JText::_('COM_KETSHOP_FIELDSET_POSTCODES', true)); ?>
	<div class="row-fluid form-horizontal-desktop">
	  <div class="span12" id="postcode">
	  </div>
	</div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'city-tab', JText::_('COM_KETSHOP_FIELDSET_CITIES', true)); ?>
	<div class="row-fluid form-horizontal-desktop">
	  <div class="span12" id="city">
	  </div>
	</div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'region-tab', JText::_('COM_KETSHOP_FIELDSET_REGIONS', true)); ?>
	<div class="row-fluid form-horizontal-desktop">
	  <div class="span12" id="region">
	  </div>
	</div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'country-tab', JText::_('COM_KETSHOP_FIELDSET_COUNTRIES', true)); ?>
	<div class="row-fluid form-horizontal-desktop">
	  <div class="span12" id="country">
	  </div>
	</div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'continent-tab', JText::_('COM_KETSHOP_FIELDSET_CONTINENTS', true)); ?>
	<div class="row-fluid form-horizontal-desktop">
	  <div class="span12" id="continent">
	  </div>
	</div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
	<div class="row-fluid form-horizontal-desktop">
	  <div class="span6">
	    <?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
	  </div>
	  <div class="span6">
	    <?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
	  </div>
	</div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>
  </div>

  <input type="hidden" name="task" value="" />
  <?php echo JHtml::_('form.token', array('id' => 'token')); ?>
  <input type="hidden" name="root_location" id="root-location" value="<?php echo JUri::root(); ?>" />
</form>

<?php
// Loads the required scripts.
$doc = JFactory::getDocument();
$doc->addScript(JURI::base().'components/com_ketshop/js/omkod-ajax.js');
$doc->addScript(JURI::base().'components/com_ketshop/js/omkod-dynamic-item.js');
$doc->addScript(JURI::base().'components/com_ketshop/js/shipping.js');


