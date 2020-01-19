<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access'); 

JLoader::register('JHtmlUsers', JPATH_ADMINISTRATOR.'/components/com_users/helpers/html/users.php');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');


$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
// Check only against component permission as customer items have no categories.
$canOrder = $user->authorise('core.edit.state', 'com_ketshop');
?>


<form action="<?php echo JRoute::_('index.php?option=com_ketshop&view=customers');?>" method="post" name="adminForm" id="adminForm">

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
    <table class="table table-striped" id="customerList">
      <thead>
      <tr>
	<th width="1%" class="hidden-phone">
	  <?php echo JHtml::_('grid.checkall'); ?>
	</th>
	<th width="1%" style="min-width:55px" class="nowrap center">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_ENABLED', 'u.block', $listDirn, $listOrder); ?>
	</th>
	<th>
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_LASTNAME', 'c.lastname', $listDirn, $listOrder); ?>
	</th>
	<th>
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_FIRSTNAME', 'c.firstname', $listDirn, $listOrder); ?>
	</th>
	<th width="10%">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_USERNAME', 'u.username', $listDirn, $listOrder); ?>
	</th>
	<th width="20%" class="center">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_PENDING_ORDERS', 'u.lastvisitDate', $listDirn, $listOrder); ?>
	</th>
	<th width="5%" class="nowrap hidden-phone">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_LAST_VISIT_DATE', 'u.lastvisitDate', $listDirn, $listOrder); ?>
	</th>
	<th width="5%" class="nowrap hidden-phone">
	  <?php echo JHtml::_('searchtools.sort', 'COM_KETSHOP_HEADING_REGISTRATION_DATE', 'c.created', $listDirn, $listOrder); ?>
	</th>
	<th width="1%" class="nowrap hidden-phone">
	  <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
	</th>
      </tr>
      </thead>

      <tbody>
      <?php foreach ($this->items as $i => $item) :

      $canEdit = $user->authorise('core.edit','com_ketshop.customer.'.$item->id);
      $canEditOwn = $user->authorise('core.edit.own', 'com_ketshop.customer.'.$item->id) && $item->id == $userId;
      $canCheckin = $user->authorise('core.manage','com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
      $canChange = ($user->authorise('core.edit.state','com_ketshop.customer.'.$item->id) && $canCheckin) || $canEditOwn; 
      ?>

      <tr class="row<?php echo $i % 2; ?>">
	  <td class="center hidden-phone">
	    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
	  </td>
	  <td class="center">
	    <div class="btn-group">
	      <?php
		    $self = $user->id == $item->id;
		    echo JHtml::_('jgrid.state', JHtmlUsers::blockStates($self), $item->block, $i, 'customers.', !$self); ?>
	    </div>
	  </td>
	  <td class="has-context">
	    <div class="pull-left">
	      <?php if ($item->checked_out) : ?>
		  <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'customers.', $canCheckin); ?>
	      <?php endif; ?>
	      <?php if($canEdit || $canEditOwn) : ?>
		<a href="<?php echo JRoute::_('index.php?option=com_ketshop&task=customer.edit&id='.$item->id);?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
			<?php echo $this->escape($item->lastname); ?></a>
	      <?php else : ?>
		<?php echo $this->escape($item->lastname); ?>
	      <?php endif; ?>
	    </div>
	  </td>
	  <td class="hidden-phone">
	    <?php echo $this->escape($item->firstname); ?>
	  </td>
	  <td class="hidden-phone">
	    <?php echo $this->escape($item->username); ?>
	  </td>
	  <td class="nowrap small hidden-phone center">
	    <?php echo $item->pending_orders; ?>
	  </td>
	  <td class="nowrap small hidden-phone">
	    <?php echo JHtml::_('date', $item->lastvisitDate, JText::_('DATE_FORMAT_LC6')); ?>
	  </td>
	  <td class="nowrap small hidden-phone">
	    <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC6')); ?>
	  </td>
	  <td>
	    <?php echo $item->id; ?>
	  </td></tr>

      <?php endforeach; ?>
      <tr>
	  <td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
      </tr>
      </tbody>
    </table>
  <?php endif; ?>

<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="option" value="com_ketshop" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>

