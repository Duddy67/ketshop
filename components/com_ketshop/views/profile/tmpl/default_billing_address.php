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
    <?php echo JText::_('COM_KETSHOP_BILLING_ADDRESS_TITLE'); ?>
  </legend>
  <dl class="dl-horizontal">
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_STREET_LABEL'); ?>
    </dt>
    <dd>
      <?php echo $this->escape($this->item->addresses['billing']->street); ?>
    </dd>
    <?php if(!empty($this->item->addresses['billing']->additional)) : ?>
      <dt>
	<?php echo JText::_('COM_KETSHOP_FIELD_ADDITIONAL_LABEL'); ?>
      </dt>
      <dd>
	<?php echo $this->escape($this->item->addresses['billing']->additional); ?>
      </dd>
    <?php endif; ?>
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_CITY_LABEL'); ?>
    </dt>
    <dd>
      <?php echo $this->escape($this->item->addresses['billing']->city); ?>
    </dd>
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_POSTCODE_LABEL'); ?>
    </dt>
    <dd>
      <?php echo $this->escape($this->item->addresses['billing']->postcode); ?>
    </dd>
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_REGION_LABEL'); ?>
    </dt>
    <dd>
      <?php echo JText::_('COM_KETSHOP_LANG_REGION_'.strtoupper($this->item->addresses['billing']->region_code)); ?>
    </dd>
    <dt>
      <?php echo JText::_('COM_KETSHOP_FIELD_COUNTRY_LABEL'); ?>
    </dt>
    <dd>
      <?php echo JText::_('COM_KETSHOP_LANG_COUNTRY_'.strtoupper($this->item->addresses['billing']->country_code)); ?>
    </dd>
  </dl>
</fieldset>

