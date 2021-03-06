<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access'); 

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');


$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
// Check only against component permission as order items have no categories.
$canOrder = $user->authorise('core.edit.state', 'com_ketshop');

// Build a status array.
$status = array();
$status['completed'] = 'COM_KETSHOP_OPTION_COMPLETED_STATUS';
$status['pending'] = 'COM_KETSHOP_OPTION_PENDING_STATUS';
$status['pending_payment'] = 'COM_KETSHOP_OPTION_PENDING_PAYMENT_STATUS';
$status['payment_accepted'] = 'COM_KETSHOP_OPTION_PAYMENT_ACCEPTED_STATUS';
$status['other'] = 'COM_KETSHOP_OPTION_OTHER_STATUS';
$status['cancelled'] = 'COM_KETSHOP_OPTION_CANCELLED_STATUS';
$status['refunded'] = 'COM_KETSHOP_OPTION_REFUNDED_STATUS';
$status['error'] = 'COM_KETSHOP_OPTION_ERROR_STATUS';
$status['payment_error'] = 'COM_KETSHOP_OPTION_PAYMENT_ERROR_STATUS';
$status['in_transit'] = 'COM_KETSHOP_OPTION_IN_TRANSIT_STATUS';
$status['delivered'] = 'COM_KETSHOP_OPTION_DELIVERED_STATUS';
$status['no_shipping'] = 'COM_KETSHOP_OPTION_NO_SHIPPING_STATUS';
$status['no_payment'] = 'COM_KETSHOP_OPTION_NO_PAYMENT_STATUS';
$status['unfinished'] = 'COM_KETSHOP_OPTION_UNFINISHED_STATUS';
$status['undefined'] = 'COM_KETSHOP_OPTION_UNDEFINED_STATUS';
$status['shopping'] = 'COM_KETSHOP_OPTION_SHOPPING_STATUS';
?>


<form action="<?php echo JRoute::_('index.php?option=com_ketshop&view=orders');?>" method="post" name="adminForm" id="adminForm">

<?php if (!empty( $this->sidebar)) : ?>
  <div id="j-sidebar-container" class="span2">
	  <?php echo $this->sidebar; ?>
  </div>
  <div id="j-main-container" class="span10">
<?php else : ?>
  <div id="j-main-container">
<?php endif;?>

<?php
// Search tools bar 
echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
?>

  <div class="clr"> </div>
  <?php if (empty($this->items)) : ?>
	<div class="alert alert-no-items">
		<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
  <?php else : ?>
    <table class="table table-striped" id="orderList">
      <thead>
      <tr>
	<th width="1%" class="hidden-phone">
	  <?php echo JHtml::_('grid.checkall'); ?>
	</th>
	<th width="1%" style="min-width:55px" class="nowrap center">
	  <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'o.published', $listDirn, $listOrder); ?>
	</th>
	<th width="15%">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_ORDER_NUMBER', 'order_nb', $listDirn, $listOrder); ?>
	</th>
	<th width="10%" class="nowrap hidden-phone">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_CUSTOMER', 'c.lastname', $listDirn, $listOrder); ?>
	</th>
	<th width="10%" class="nowrap hidden-phone">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_CUSTOMER_NUMBER', 'c.customer_number', $listDirn, $listOrder); ?>
	</th>
	<th width="10%">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_ORDER_STATUS', 'order_status', $listDirn, $listOrder); ?>
	</th>
	<th width="15%">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_PAYMENT_STATUS', 'payment_status', $listDirn, $listOrder); ?>
	</th>
	<th width="15%">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_SHIPPING_STATUS', 'shipping_status', $listDirn, $listOrder); ?>
	</th>
	<th width="20%">
	  <?php echo JText::_('COM_KETSHOP_HEADING_PRODUCTS'); ?>
	</th>
	<th width="5%" class="nowrap hidden-phone">
	  <?php echo JHtml::_('searchtools.sort', 'JDATE', 'o.created', $listDirn, $listOrder); ?>
	</th>
	<th width="1%" class="nowrap hidden-phone">
	  <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'o.id', $listDirn, $listOrder); ?>
	</th>
      </tr>
      </thead>

      <tbody>
      <?php foreach ($this->items as $i => $item) :

      $canEdit = $user->authorise('core.edit','com_ketshop.order.'.$item->id);
      $canEditOwn = $user->authorise('core.edit.own', 'com_ketshop.order.'.$item->id) && $item->created_by == $userId;
      $canCheckin = $user->authorise('core.manage','com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
      $canChange = ($user->authorise('core.edit.state','com_ketshop.order.'.$item->id) && $canCheckin) || $canEditOwn; 
      ?>

      <tr class="row<?php echo $i % 2; ?>">
	  <td class="center hidden-phone">
	    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
	  </td>
	  <td class="center">
	    <div class="btn-group">
	      <?php echo JHtml::_('jgrid.published', $item->published, $i, 'orders.', $canChange, 'cb'); ?>
	      <?php
	      // Create dropdown items
	      $action = $archived ? 'unarchive' : 'archive';
	      JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'orders');

	      $action = $trashed ? 'untrash' : 'trash';
	      JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'orders');

	      // Render dropdown list
	      echo JHtml::_('actionsdropdown.render', $this->escape($item->order_nb));
	      ?>
	    </div>
	  </td>
	  <td class="has-context">
	    <div class="pull-left">
	      <?php if ($item->checked_out) : ?>
		  <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'orders.', $canCheckin); ?>
	      <?php endif; ?>
	      <?php if($canEdit || $canEditOwn) : ?>
		<a href="<?php echo JRoute::_('index.php?option=com_ketshop&task=order.edit&id='.$item->id);?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
			<?php echo $this->escape($item->order_nb); ?></a>
	      <?php else : ?>
		<?php echo $this->escape($item->order_nb); ?>
	      <?php endif; ?>
	    </div>
	  </td>
	  <td class="hidden-phone">
	    <?php if ($item->customer_id) : ?>
	      <?php echo $this->escape($item->lastname); ?>
	      <div class="small">
		<?php echo $this->escape($item->firstname); ?>
	      </div>
	    <?php else : ?>
	      <?php echo JText::_('COM_KETSHOP_VISITOR_CUSTOMER'); ?>
	    <?php endif; ?>
	  </td>
	  <td class="hidden-phone">
	    <?php echo $this->escape($item->customer_number); ?>
	  </td>
	  <td class="hidden-phone">
	    <?php echo JText::_($status[$item->order_status]); ?>
	  </td>
	  <td class="hidden-phone">
	    <?php echo JText::_($status[$item->payment_status]); ?>
	  </td>
	  <td class="hidden-phone">
	    <?php echo JText::_($status[$item->shipping_status]); ?>
	  </td>
	  <td class="hidden-phone small">
	  <?php 
		 $maxProducts = 3;
		 foreach($item->products as $i => $product) {
		   echo $product->name;

		   if($i + 1 == $maxProducts) {
		     echo ' ...';
		     break;
		   }
		   else {
		     echo '<br />';
		   }
		 }
	   ?>
	  </td>
	  <td class="nowrap small hidden-phone">
	    <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
	  </td>
	  <td>
	    <?php echo $item->id; ?>
	  </td></tr>

      <?php endforeach; ?>
      <tr>
	  <td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
      </tr>
      </tbody>
    </table>
  <?php endif; ?>

<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="option" value="com_ketshop" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>

