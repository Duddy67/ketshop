<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 
?>

<fieldset id="users-profile-core">
  <legend>
    <?php echo JText::_('COM_KETSHOP_CUSTOMER_LEGEND'); ?>
  </legend>
  <dl class="dl-horizontal">
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_LASTNAME_LABEL'); ?>
    </dt>
    <dd>
      <?php echo $this->escape($this->item->lastname); ?>
    </dd>
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_FIRSTNAME_LABEL'); ?>
    </dt>
    <dd>
      <?php echo $this->escape($this->item->firstname); ?>
    </dd>
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_PHONE_LABEL'); ?>
    </dt>
    <dd>
      <?php echo $this->escape($this->item->phone); ?>
    </dd>
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_USERNAME_LABEL'); ?>
    </dt>
    <dd>
      <?php echo $this->escape($this->item->username); ?>
    </dd>
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_EMAIL_LABEL'); ?>
    </dt>
    <dd>
      <?php echo $this->escape($this->item->email); ?>
    </dd>
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_REGISTERDATE_LABEL'); ?>
    </dt>
    <dd>
      <?php echo JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC1')); ?>
    </dd>
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_LASTVISIT_LABEL'); ?>
    </dt>
    <?php if ($this->item->lastvisitDate != $this->db->getNullDate()) : ?>
	    <dd>
	      <?php echo JHtml::_('date', $this->item->lastvisitDate, JText::_('DATE_FORMAT_LC1')); ?>
	    </dd>
    <?php else : ?>
	    <dd>
	      <?php echo JText::_('COM_KETSHOP_CUSTOMER_SPACE_NEVER_VISITED'); ?>
	    </dd>
    <?php endif; ?>
  </dl>
</fieldset>

