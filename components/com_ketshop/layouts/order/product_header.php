<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');
?>

    <thead>
    <th width="30%"><?php echo JText::_('COM_KETSHOP_HEADING_PRODUCT'); ?></th>
    <th class="center" width="25%"><?php echo JText::_('COM_KETSHOP_HEADING_UNIT_PRICE'); ?></th>
    <th class="quantity-col center" width="5%"><?php echo JText::_('COM_KETSHOP_HEADING_QUANTITY'); ?></th>
    <th class="center" width="25%"><?php echo JText::_('COM_KETSHOP_HEADING_PRICE'); ?></th>
    <th class="center" width="15%"><?php echo JText::_('COM_KETSHOP_HEADING_TAX_RATE'); ?></th>
    <?php if(isset($displayData->can_edit) && $displayData->can_edit) : ?>
      <th width="5%"></th>
    <?php endif; ?>
    </thead>

