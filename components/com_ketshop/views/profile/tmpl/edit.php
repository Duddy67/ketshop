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
  if(task == 'profile.cancel' || document.formvalidator.isValid(document.getElementById('profile-form'))) {
    Joomla.submitform(task, document.getElementById('profile-form'));
  }
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_ketshop&task=profile.save'.(int) $this->item->id); ?>" 
 method="post" name="adminForm" id="profile-form" enctype="multipart/form-data" class="form-validate">

  <div class="form-horizontal">

    <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_KETSHOP_TAB_DETAILS')); ?>

      <div class="row-fluid">
	<div class="form-vertical span5">
	  <?php
		echo $this->form->renderField('lastname');
		echo $this->form->renderField('firstname');
		echo $this->form->renderField('phone');
		echo $this->form->renderField('password1');
		echo $this->form->renderField('password2');
	   ?>
	</div>
	<div class="form-vertical span5">
	  <?php
		echo $this->form->renderField('username');
		echo $this->form->renderField('email');
		echo $this->form->renderField('lastvisitDate');
		echo $this->form->renderField('created');
		echo $this->form->renderField('modified');
	    ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'addresses', JText::_('COM_KETSHOP_FIELDSET_ADDRESSES')); ?>
      <div class="row-fluid">
	<div class="form-vertical span5">
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
	<div class="form-vertical span5">
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
  </div>

  <div class="control-group">
    <div class="controls">
      <button type="submit" class="btn btn-primary validate">
	      <?php echo JText::_('JSUBMIT'); ?>
      </button>
      <a class="btn" href="<?php echo JRoute::_('index.php?option=com_ketshop&view=profile'); ?>" title="<?php echo JText::_('JCANCEL'); ?>">
	      <?php echo JText::_('JCANCEL'); ?>
      </a>
      <input type="hidden" name="option" value="com_ketshop" />
      <input type="hidden" name="task" value="profile.save" />
    </div>
  </div>

  <?php echo JHtml::_('form.token'); ?>
</form>

<?php
$doc = JFactory::getDocument();
$doc->addScript(JURI::root().'components/com_ketshop/js/shipping_address.js');

