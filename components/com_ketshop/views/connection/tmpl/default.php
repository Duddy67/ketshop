<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');
?>

<div class="login connection">
  <form action="<?php echo JRoute::_('index.php?option=com_ketshop&task=connection.login'); ?>" method="post" class="form-validate form-horizontal well">
	  <fieldset>
	  <legend><?php echo JText::_($this->login->getFieldsets()['credentials']->label); ?></legend>
		  <?php echo $this->login->renderFieldset('credentials'); ?>
		  <div class="control-group">
			  <div class="controls">
				  <button type="submit" class="btn btn-primary">
					  <?php echo JText::_('JLOGIN'); ?>
				  </button>
			  </div>
		  </div>
		  <?php echo JHtml::_('form.token'); ?>
	  </fieldset>
  </form>
</div>

<div class="registration connection">
	<form name="registration" id="member-registration" action="<?php echo JRoute::_('index.php?option=com_ketshop&task=connection.registration'); ?>" method="post" class="form-validate form-horizontal well" enctype="multipart/form-data">
		<?php // Iterate through the form fieldsets and display each one. ?>
		<?php foreach ($this->registration->getFieldsets() as $fieldset) : ?>
		  <?php $fields = $this->registration->getFieldset($fieldset->name); ?>
		  <?php if (count($fields)) : ?>
			<fieldset>
			<?php // If the fieldset has a label set, display it as the legend. ?>
			<?php if (isset($fieldset->label)) : ?>
				<legend><?php echo JText::_($fieldset->label); ?></legend>
			<?php endif; ?>
			<?php // Renders the fields. ?>
			<?php foreach ($fields as $field) : 
				echo $field->renderField(); 
                                
                                if($field->name == 'jform[shipping_address]') {
				  //
				  echo '<div id="shipping_div">';
				}

			      endforeach; ?>
			</fieldset>
		  <?php endif; ?>
		<?php endforeach; ?>

		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn btn-primary validate">
					<?php echo JText::_('JREGISTER'); ?>
				</button>
				<a class="btn" href="<?php echo JRoute::_(''); ?>" title="<?php echo JText::_('JCANCEL'); ?>">
					<?php echo JText::_('JCANCEL'); ?>
				</a>
				<input type="hidden" name="option" value="com_ketshop" />
				<input type="hidden" name="task" value="connection.registration" />
			</div>
		</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>

<?php
$doc = JFactory::getDocument();
$doc->addScript(JURI::root().'components/com_ketshop/js/setregions.js');
$doc->addScript(JURI::root().'components/com_ketshop/js/shipping_address.js');

