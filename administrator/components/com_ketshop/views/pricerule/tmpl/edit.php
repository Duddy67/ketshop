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
JHtml::_('behavior.modal');
?>

<script type="text/javascript">
Joomla.submitbutton = function(task)
{
  if(task == 'pricerule.cancel' || document.formvalidator.isValid(document.getElementById('pricerule-form'))) {
    Joomla.submitform(task, document.getElementById('pricerule-form'));
  }
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_ketshop&view=pricerule&layout=edit&id='.(int) $this->item->id); ?>" 
 method="post" name="adminForm" id="pricerule-form" enctype="multipart/form-data" class="form-validate">

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
		    $this->form->setFieldAttribute('type', 'type', 'hidden');

		    // Sets and displays the price rule type value for information.
		    $this->form->setValue('type_info', null,JText::_('COM_KETSHOP_OPTION_'.strtoupper($this->item->type)));

		    echo $this->form->renderField('type_info');
		  }

		  echo $this->form->renderField('type');
		  echo $this->form->renderField('operation');

		  if($this->item->id) {
		    $this->form->setValue('value', null, UtilityHelper::floatFormat($this->item->value));
		  }

		  echo $this->form->renderField('value');
		  echo $this->form->renderField('behavior');
		  echo $this->form->renderField('application_level');
		  echo $this->form->renderField('show_rule');
		  echo $this->form->renderField('description');
	      ?>
	  </div>
	</div>
	<div class="span3">
	  <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
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

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'pricerule-condition', JText::_('COM_KETSHOP_FIELDSET_CONDITION', true)); ?>
      <div class="row-fluid form-horizontal-desktop">
	<div class="span12" id="condition">
	  <?php
		echo $this->form->renderField('condition_type');
		$this->form->setValue('all_variants_condition', null, $this->item->all_variants);
		echo $this->form->renderField('all_variants_condition');
		echo $this->form->renderField('logical_opr');
		echo $this->form->renderField('comparison_opr');
		echo $this->form->renderField('condition_qty');

		if($this->item->id) {
		  $this->form->setValue('condition_amount', null, UtilityHelper::floatFormat($this->item->condition_amount));
		}

		echo $this->form->renderField('condition_amount');
	  ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'pricerule-target', JText::_('COM_KETSHOP_FIELDSET_ON_WHAT', true)); ?>
      <div class="row-fluid form-horizontal-desktop">
	<div class="span12" id="target">
	  <?php echo $this->form->renderField('target_type');
		$this->form->setValue('all_variants_target', null, $this->item->all_variants);
		echo $this->form->renderField('all_variants_target');
	      ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>


      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'pricerule-recipient', JText::_('COM_KETSHOP_FIELDSET_FOR_WHOM', true)); ?>
      <div class="row-fluid form-horizontal-desktop">
	<div class="span12" id="recipient">
	  <?php echo $this->form->renderField('recipient_type'); ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

  </div>

  <input type="hidden" name="task" value="" />
  <?php echo $this->form->renderField('all_variants'); ?>
  <?php echo JHtml::_('form.token', array('id' => 'token')); ?>
  <input type="hidden" name="root_location" id="root-location" value="<?php echo JUri::root(); ?>" />
</form>

<?php
// Loads the required scripts.
$doc = JFactory::getDocument();
$doc->addScript(JURI::base().'components/com_ketshop/js/omkod-ajax.js');
$doc->addScript(JURI::base().'components/com_ketshop/js/omkod-dynamic-item.js');
$doc->addScript(JURI::base().'components/com_ketshop/js/pricerule.js');

