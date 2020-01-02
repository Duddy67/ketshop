<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');


// Prevent params layout (layouts/joomla/edit/params.php) to display twice some fieldsets.
$this->ignore_fieldsets = array('details', 'permissions', 'jmetadata');
$canDo = KetshopHelper::getActions($this->state->get('filter.category_id'));

// Sets the tax method information.
if($this->config->get('tax_method') == 'incl_tax') {
  $taxMethod = JText::_('COM_KETSHOP_SPAN_INCLUDING_TAXES');
  $taxMethodTitle = JText::_('COM_KETSHOP_SPAN_INCLUDING_TAXES_TITLE');
}
else {
  $taxMethod = JText::_('COM_KETSHOP_SPAN_EXCLUDING_TAXES');
  $taxMethodTitle = JText::_('COM_KETSHOP_SPAN_EXCLUDING_TAXES_TITLE');
}

$productType = $this->form->getValue('type');

if(!$this->item->id) {
  // New item. Gets the type of the product from the current url query.
  $productType = JFactory::getApplication()->input->get('type', '', 'string');
  // Sets the type of the product in the form.
  $this->form->setValue('type', null, $productType);
}
?>

<script type="text/javascript">
Joomla.submitbutton = function(task)
{
  if(task == 'product.cancel' || document.formvalidator.isValid(document.getElementById('product-form'))) {
    Joomla.submitform(task, document.getElementById('product-form'));
  }
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_ketshop&view=product&layout=edit&id='.(int) $this->item->id); ?>" 
 method="post" name="adminForm" id="product-form" enctype="multipart/form-data" class="form-validate">

  <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

  <div class="form-horizontal">

    <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_KETSHOP_TAB_DETAILS')); ?>

      <div class="row-fluid">
	<div class="span9">
	    <div class="form-vertical">
	      <?php
		    echo $this->form->renderField('manufact_id');
		    echo $this->form->renderField('producttext');
		    // Hidden field.
		    echo $this->form->getInput('type');
	      ?>
	    </div>
	</div>
	<div class="span3 form-vertical">
	  <?php echo JLayoutHelper::render('edit.global', $this); ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php $typeName = ($productType == 'normal') ? 'PRODUCT_VARIANTS' : 'BUNDLE_PARAMETERS';
	    echo JHtml::_('bootstrap.addTab', 'myTab', 'product-variant', JText::_('COM_KETSHOP_SUBMENU_'.$typeName, true)); ?>
	<div class="row-fluid form-vertical">
	  <div class="span2">
	    <?php echo $this->form->renderField('weight_unit'); ?>
	  </div>
	  <div class="span2">
	    <?php echo $this->form->renderField('dimension_unit'); ?>
	  </div>
	  <div class="span2">
	    <?php echo $this->form->renderField('shippable'); ?>
	  </div>
	  <div class="span2">
	    <?php echo $this->form->renderField('tax_id'); ?>
	  </div>
	  <div class="span2">
	    <?php
		  $this->form->setValue('currency_info', null, UtilityHelper::getCurrency());
		  echo $this->form->renderField('currency_info');
	    ?>
	  </div>

	  <div class="form-vertical">
	    <div class="span12" id="variant">
	    </div>
	  </div>

	</div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php if($productType == 'bundle') : ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'product-bundle', JText::_('COM_KETSHOP_SUBMENU_BUNDLE_PRODUCTS', true)); ?>
	  <div class="row-fluid form-horizontal-desktop">
	    <div class="span12" id="bundle">
	    </div>
	  </div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
      <?php endif; ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'product-attribute', JText::_('COM_KETSHOP_SUBMENU_ATTRIBUTES', true)); ?>
	<div class="row-fluid">
	  <div class="span12 form-vertical" id="attribute">
	  </div>
	</div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'product-image', JText::_('COM_KETSHOP_SUBMENU_IMAGES', true)); ?>
	<div class="row-fluid form-horizontal-desktop">
	  <div class="span12" id="image">
	    <?php echo $this->form->getInput('imageurl'); //Must be loaded to call the overrided media file.
		  echo $this->form->renderField('img_reduction_rate');
	    ?>
	  </div>
	</div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
      <div class="row-fluid form-horizontal-desktop">
	<div class="span6">
	  <?php
		echo $this->form->renderField('new_until');
		echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
	</div>
	<div class="span6">
	  <?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
	</div>
      </div>
      <?php echo JHtml::_('bootstrap.endTab'); ?>

      <?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

      <?php if($canDo->get('core.admin')) : ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_KETSHOP_TAB_PERMISSIONS', true)); ?>
		<?php echo $this->form->getInput('rules'); ?>
		<?php echo $this->form->getInput('asset_id'); ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
      <?php endif; ?>
  </div>

  <input type="hidden" name="unallowed_cats" value="<?php echo json_encode($this->item->unallowed_cats); ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="root_location" id="root-location" value="<?php echo JUri::root(); ?>" />
  <input type="hidden" id="is-admin" name="is_admin" value="1" />
  <input type="hidden" id="product-type" name="product_type" value="<?php echo $productType; ?>" />
  <?php echo JHtml::_('form.token', array('id' => 'token')); ?>
</form>

<?php
// Loads the required scripts.
$doc = JFactory::getDocument();
$doc->addScript(JURI::base().'components/com_ketshop/js/omkod-ajax.js');
$doc->addScript(JURI::base().'components/com_ketshop/js/omkod-dynamic-item.js');
$doc->addScript(JURI::base().'components/com_ketshop/js/product.js');

