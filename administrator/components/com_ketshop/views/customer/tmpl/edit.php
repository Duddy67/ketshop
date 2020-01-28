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
//var_dump($this->address_form);
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
		  echo $this->form->renderField('customer_number');
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
      <div class="row-fluid">
	<div class="span5">
	<h3><?php echo JText::_('COM_KETSHOP_FIELD_BILLING_ADDRESS_TITLE'); ?></h3>
        <br />
	  <?php 
		$fieldset = $this->form->getFieldset('billing_address');

		foreach($fieldset as $field) {
		  if($field->name != 'jform[new_billing_address]') {
		    $name = preg_replace('#^jform\[([a-zA-Z0-9_]+)_billing\]$#', '$1', $field->name);
		    $field->setValue($this->item->addresses['billing']->$name);
		  }

		  echo $field->renderField();
		}
            ?>
          <span class="item-space">&nbsp;</span>
	  <span class="btn btn-success" id="new-billing-address">
	      <?php echo JText::_('COM_KETSHOP_NEW_ADDRESS'); ?> <span class="icon-shop-home"></span></a>
	  </span>
	</div>
	<div class="span5">
	  <?php 
		$fieldset = $this->form->getFieldset('shipping_address');

		echo $fieldset['jform_shipping_address']->renderField();

		echo '<div id="shipping_div">';
		echo '<h3>'.JText::_('COM_KETSHOP_FIELD_SHIPPING_ADDRESS_TITLE').'</h3>';

		foreach($fieldset as $field) {
		  if($field->name == 'jform[shipping_address]') {
		    continue;
		  }

		  if(isset($this->item->addresses['shipping']) && $field->name != 'jform[new_shipping_address]') {
		    $name = preg_replace('#^jform\[([a-zA-Z0-9_]+)_shipping\]$#', '$1', $field->name);
		    $field->setValue($this->item->addresses['shipping']->$name);
		  }

		  echo $field->renderField();
		}
	   ?>
          <span class="item-space">&nbsp;</span>
	  <span class="btn btn-success" id="new-shipping-address">
	      <?php echo JText::_('COM_KETSHOP_NEW_ADDRESS'); ?> <span class="icon-shop-home"></span></a>
	  </span>
	  </div>
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

<?php
$doc = JFactory::getDocument();
$doc->addScript(JURI::root().'components/com_ketshop/js/shipping_address.js');

