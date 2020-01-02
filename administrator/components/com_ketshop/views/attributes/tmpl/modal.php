<?php
/**
 * @package PopSy
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined( '_JEXEC' ) or die; // No direct access

JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.tooltip');
//JHtml::_('script','system/multiselect.js',false,true);


$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$jinput = JFactory::getApplication()->input;

// Gets variable from url query.
$idNb = $jinput->get->get('id_nb', 0, 'int');
$dynamicItemType = $jinput->get->get('dynamic_item_type', '', 'string');

// N.B: This modal window is called from both product and translation edit views.
// Translation doesn't provide idNb GET variable and just needs id and name
// attribute. So we use 2 functions to return attribute data accordingly.

// Sets the Javascript function to call.
if(!$idNb) {
  $function = JFactory::getApplication()->input->get('function', 'selectItem');
}
else {
  $function = JFactory::getApplication()->input->get('function', 'selectAttribute');
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_ketshop&view=attributes&layout=modal&tmpl=component&function='.$function);?>" method="post" name="adminForm" id="adminForm">

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
		  <?php echo JHtml::_('grid.sort', 'COM_KETSHOP_HEADING_NAME', 't.name', $listDirn, $listOrder); ?>
	  </th>
	  <th width="15%">
		  <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'user', $listDirn, $listOrder); ?>
	  </th>
	  <th width="15%" class="center nowrap">
		  <?php echo JHtml::_('grid.sort', 'JDATE', 't.created', $listDirn, $listOrder); ?>
	  </th>
	  <th width="1%" class="center nowrap">
		  <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 't.id', $listDirn, $listOrder); ?>
	  </th>
	</tr>
      </thead>
      <tfoot>
	<tr>
	  <td colspan="5">
	    <?php echo $this->pagination->getListFooter(); ?>
	  </td>
	</tr>
      </tfoot>

      <tbody>
      <?php foreach ($this->items as $i => $item) : ?>
      <tr class="row<?php echo $i % 2; ?>">
	      <td>
	      <?php if(!$idNb) : //Provide only id and name of the product. ?>
		<a class="pointer" style="color:#025a8d;" onclick="if(window.parent) window.parent.<?php echo $this->escape($function);?>(<?php echo $item->id; ?>, '<?php echo $this->escape(addslashes($item->name)); ?>');" >
		    <?php echo $this->escape($item->name); ?></a>
	      <?php else : //Invoke selectAttribute function. ?>
      <a class="pointer" style="color:#025a8d;" onclick="if(window.parent) window.parent.<?php echo $this->escape($function);?>(<?php echo $item->id; ?>, '<?php echo $this->escape(addslashes($item->name)); ?>',<?php echo $idNb; ?>,'<?php echo $dynamicItemType; ?>');" >
		    <?php echo $this->escape($item->name); ?></a>
	      <?php endif; ?>
	      </td>
	      <td>
		<?php echo $this->escape($item->creator); ?>
	      </td>
	      <td>
		<?php echo JHTML::_('date',$item->created, JText::_('COM_KETSHOP_DATE_FORMAT')); ?>
	      </td>
	      <td class="center">
		<?php echo (int) $item->id; ?>
	      </td></tr>

      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
  <?php echo JHtml::_('form.token'); ?>
</form>

