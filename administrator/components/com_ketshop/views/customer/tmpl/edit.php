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
  if(task == 'customer.cancel' || document.formvalidator.isValid(document.getElementById('customer-form'))) {
    Joomla.submitform(task, document.getElementById('customer-form'));
  }
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_ketshop&view=customer&layout=edit&id='.(int) $this->item->id); ?>" 
 method="post" name="adminForm" id="customer-form" enctype="multipart/form-data" class="form-validate">

  <?php echo JLayoutHelper::render('edit.title_alias', $this); ?>

  <div class="form-horizontal">

    <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_KETSHOP_TAB_DETAILS')); ?>

      <div class="row-fluid">
	<div class="span4">
	  <div class="form-vertical">
	    <?php
		  echo $this->form->renderField('firstname');
		  echo $this->form->renderField('phone');
		  echo $this->form->renderField('username');
		  echo $this->form->renderField('email');
		  echo $this->form->renderField('lastvisitDate');
	      ?>
	  </div>
	</div>
	<div class="span3">
	  <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'orders', JText::_('COM_KETSHOP_FIELDSET_ORDERS')); ?>
      <div class="row-fluid form-horizontal-desktop">
	<div class="span6">
	  <?php //echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'addresses', JText::_('COM_KETSHOP_FIELDSET_ADDRESSES')); ?>
      <div class="row-fluid form-horizontal-desktop">
	<div class="span6">
	  <?php //echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
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
  <?php echo JHtml::_('form.token'); ?>
</form>

