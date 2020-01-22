<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 
?>

<div class="profile<?php echo $this->pageclass_sfx; ?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
<?php endif; ?>
<?php if (JFactory::getUser()->id == $this->item->id) : ?>
	<ul class="btn-toolbar pull-right">
	  <li class="btn-group">
	    <a class="btn" href="<?php echo JRoute::_('index.php?option=com_ketshop&task=profile.edit&c_id='.(int) $this->item->id); ?>">
	  <span class="icon-user"></span>
	  <?php echo JText::_('COM_KETSHOP_EDIT_CUSTOMER_DATA'); ?>
	  </a>
	  </li>
	</ul>
<?php endif; ?>
<?php
      echo $this->loadTemplate('customer');
      echo $this->loadTemplate('billing_address');
      echo $this->loadTemplate('shipping_address');
?>
</div>

