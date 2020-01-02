<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.framework', true);
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', '.multipleCategories', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_CATEGORY')));
JHtml::_('formbehavior.chosen', '.multipleUsers',null, array('placeholder_text_multiple' => JText::_('COM_KETSHOP_SELECT_CREATOR')));
JHtml::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')));
JHtml::_('formbehavior.chosen', '.multipleAccessLevels', null, array('placeholder_text_multiple' => JText::_('JOPTION_SELECT_ACCESS')));
JHtml::_('formbehavior.chosen', 'select');
require_once JPATH_ROOT.'/components/com_ketshop/helpers/route.php';

$app = JFactory::getApplication();

if($app->isSite()) {
  JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

$jinput = JFactory::getApplication()->input;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$idNb = $jinput->get->get('id_nb', 0, 'int');
$function = $jinput->get('function', 'selectItem', 'string');
$dynamicItemType = $jinput->get->get('dynamic_item_type', '', 'string');
$productType = $jinput->get->get('product_type', '', 'string');

// Builds the needed query variable.
if(!empty($productType)) {
  $productType = '&product_type='.$productType;
}

$currency = UtilityHelper::getCurrency();
?>

<form action="<?php echo JRoute::_('index.php?option=com_ketshop&view=products&layout=modal&tmpl=component&function='.$function.'&id_nb='.$idNb.'&dynamic_item_type='.$dynamicItemType.$productType.'&'.JSession::getFormToken().'=1');?>" method="post" name="adminForm" id="adminForm" class="form-inline">

  <?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>


  <?php if (empty($this->items)) : ?>
	<div class="alert alert-no-items">
	  <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
  <?php else : ?>
    <table class="table table-striped table-condensed">
      <thead>
	<tr>
	  <th class="title">
		  <?php echo JHtml::_('grid.sort', 'COM_KETSHOP_FIELD_NAME_LABEL', 'p.name', $listDirn, $listOrder); ?>
	  </th>
	  <th width="10%">
	    <?php echo JText::_('COM_KETSHOP_HEADING_BASE_PRICE'); ?>
	  </th>
	  <th width="10%">
	    <?php echo JText::_('COM_KETSHOP_HEADING_PRICE_WITH_TAX'); ?>
	  </th>
	  <th width="10%" class="nowrap hidden-phone">
	    <?php echo JHtml::_('grid.sort', 'COM_KETSHOP_HEADING_STOCK', 'p.stock', $listDirn, $listOrder); ?>
	  </th>
	  <th width="15%" class="nowrap hidden-phone">
		  <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
	  </th>
	  <th width="15%" class="nowrap hidden-phone">
		  <?php echo JHtml::_('grid.sort', 'JDATE', 'p.created', $listDirn, $listOrder); ?>
	  </th>
	  <th width="1%" class="nowrap hidden-phone">
		  <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'p.id', $listDirn, $listOrder); ?>
	  </th>
	</tr>
      </thead>
      <tfoot>
	<tr>
	  <td colspan="7">
	    <?php echo $this->pagination->getListFooter(); ?>
	  </td>
	</tr>
      </tfoot>
      <tbody>
      <?php foreach($this->items as $i => $item) : ?>
	      <?php if($item->language && JLanguageMultilang::isEnabled()) {
		      $tag = strlen($item->language);
		      if($tag == 5) {
			$lang = substr($item->language, 0, 2);
		      }
		      elseif($tag == 6) {
			$lang = substr($item->language, 0, 3);
		      }
		      else {
			$lang = "";
		      }
	      }
	      elseif(!JLanguageMultilang::isEnabled()) {
		$lang = "";
	      }
	      ?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="has-context">
		  <div class="pull-left">
		   <?php
			 $itemName = $item->name;

			 if(!empty($item->variant_name)) {
			   $itemName = $item->name.' - '.$item->variant_name;
			 }

			 $basicVariant = false;
			 $alinea = '<span class="alinea"></span>';

			 // It's the product with its basic variant.
			 if($i == 0 || $this->items[$i]->id != $this->items[$i - 1]->id) {
			   $basicVariant = true;
			   $alinea = '';
			 }

			 // Shifts the product name to the right.
			 echo $alinea;
		   ?>
		    <a href="javascript:void(0)" onclick="if(window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($itemName)); ?>', '<?php echo $this->escape($idNb); ?>', '<?php echo $this->escape($dynamicItemType); ?>','<?php echo $item->var_id; ?>');">
			<?php echo $this->escape($item->name); ?>
		  <?php if(!empty($item->variant_name)) : //. ?>
		    <span class="small"><?php echo ' - '.$this->escape($item->variant_name); ?></span>
		  <?php endif; ?></a>

		  <?php if($basicVariant) : ?>
			<div class="small">
			  <?php echo JText::_('JCATEGORY') . ": ".$this->escape($item->category_title); ?>
			</div>
		  <?php endif; ?>
		  </div>
		</td>
		<td>
		  <?php echo UtilityHelper::floatFormat($item->base_price).' '.$currency; ?>
		</td>
		<td>
		  <?php echo UtilityHelper::floatFormat($item->price_with_tax).' '.$currency; ?>
		</td>
		<td class="hidden-phone">
		  <?php echo ((int)$item->stock_subtract) ? $item->stock : '∞'; ?>
		</td>
		<td  class="small hidden-phone">
		  <?php echo $this->escape($item->access_level); ?>
		</td>
		<td  class="small hidden-phone">
		  <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
		</td>
		<td class="center">
		  <?php echo (int) $item->id; ?>
		</td>
	</tr>
      <?php endforeach; ?>
	</tbody>
      </table>

    <div>
      <input type="hidden" name="task" value="" />
      <input type="hidden" name="boxchecked" value="0" />
      <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
      <?php echo JHtml::_('form.token'); ?>
    </div>
  <?php endif; ?>
</form>
