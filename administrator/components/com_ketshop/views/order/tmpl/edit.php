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
  if(task == 'order.cancel' || document.formvalidator.isValid(document.getElementById('order-form'))) {
    Joomla.submitform(task, document.getElementById('order-form'));
  }
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_ketshop&view=order&layout=edit&id='.(int) $this->item->id); ?>" 
 method="post" name="adminForm" id="order-form" enctype="multipart/form-data" class="form-validate">

  <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

  <div class="form-horizontal">

    <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_KETSHOP_TAB_DETAILS')); ?>

      <div class="row-fluid">
	<div class="span2">
	  <div class="form-vertical">
	    <?php
		  echo $this->form->renderField('status');
		  $this->form->setValue('shipping_status', null, $this->item->shipping->status);
		  echo $this->form->renderField('shipping_status');
		  $this->form->setValue('firstname', null, $this->item->firstname);
		  echo $this->form->renderField('firstname');
		  $this->form->setValue('lastname', null, $this->item->lastname);
		  echo $this->form->renderField('lastname');
		  $this->form->setValue('customer_number', null, $this->item->customer_number);
		  echo $this->form->renderField('customer_number');
		  echo JLayoutHelper::render('order.addresses', array('order' => $this->item, 'settings' => $this->shop_settings), $this->layout_path); 
	      ?>
	  </div>
	</div>
	<div class="span10">
	  <?php echo $this->loadTemplate('details'); ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

     <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'transaction', JText::_('COM_KETSHOP_FIELDSET_TRANSACTION_DETAIL', true)); ?>
      <?php 
	    // N.B: The first transaction row is the newest.
            $this->form->setValue('payment_status', null, $this->item->transactions[0]->status);
	    echo $this->form->renderField('payment_status');
      ?>
	<table class="table product-row end-table">
	  <thead>
	    <th class="center" width="25%"><?php echo JText::_('COM_KETSHOP_HEADING_PAYMENT_MODE'); ?></th>
	    <th class="center" width="15%"><?php echo JText::_('COM_KETSHOP_HEADING_AMOUNT'); ?></th>
	    <th class="center" width="15%"><?php echo JText::_('COM_KETSHOP_HEADING_RESULT'); ?></th>
	    <th class="center" width="25%"><?php echo JText::_('COM_KETSHOP_HEADING_DETAIL'); ?></th>
	    <th class="center" width="15%"><?php echo JText::_('JDATE'); ?></th>
          </thead>
          <?php foreach($this->item->transactions as $transaction) : ?>
	  <tr>
            <td class="center"><?php echo $transaction->payment_mode; ?></td>
            <td class="center"><?php echo UtilityHelper::floatFormat($transaction->amount).' '.UtilityHelper::getCurrency($this->item->currency_code); ?></td>
            <td class="center"><?php echo $transaction->result; ?></td>
            <td class="center"><?php echo $transaction->detail; ?></td>
            <td class="center"><?php echo JHtml::_('date', $transaction->created, JText::_('DATE_FORMAT_LC6')); ?></td>
          </tr>
	  <?php endforeach; ?>
        </table>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
      <div class="row-fluid form-horizontal-desktop">
	<div class="span6">
	  <?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
	</div>
	<div class="span6">
	  <?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

  </div>

  <input type="hidden" name="task" value="" />
  <?php echo JHtml::_('form.token'); ?>
</form>

