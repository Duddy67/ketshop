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
    <?php echo JText::_('COM_KETSHOP_SHIPPING_ADDRESS_TITLE'); ?>
  </legend>
  <?php if($this->item->shipping_address) : ?>
    <dl class="dl-horizontal">
      <dt>
	<?php echo JText::_('COM_KETSHOP_FIELD_COMPANY_LABEL'); ?>
      </dt>
      <dd>
      <?php
	    if(!empty($this->item->addresses['shipping']->company)) {
	      echo $this->escape($this->item->addresses['shipping']->company);
	    }
	    else {
	      echo $this->escape($this->item->firstname).' '.$this->escape($this->item->lastname);
	    }
      ?>
      </dd>
      <dt>
	<?php echo JText::_('COM_KETSHOP_FIELD_STREET_LABEL'); ?>
      </dt>
      <dd>
	<?php echo $this->escape($this->item->addresses['shipping']->street); ?>
      </dd>
      <?php if(!empty($this->item->addresses['shipping']->additional)) : ?>
	<dt>
	  <?php echo JText::_('COM_KETSHOP_FIELD_ADDITIONAL_LABEL'); ?>
	</dt>
	<dd>
	  <?php echo $this->escape($this->item->addresses['shipping']->additional); ?>
	</dd>
      <?php endif; ?>
      <dt>
	<?php echo JText::_('COM_KETSHOP_FIELD_CITY_LABEL'); ?>
      </dt>
      <dd>
	<?php echo $this->escape($this->item->addresses['shipping']->city); ?>
      </dd>
      <dt>
	<?php echo JText::_('COM_KETSHOP_FIELD_POSTCODE_LABEL'); ?>
      </dt>
      <dd>
	<?php echo $this->escape($this->item->addresses['shipping']->postcode); ?>
      </dd>
      <dt>
	<?php echo JText::_('COM_KETSHOP_FIELD_REGION_LABEL'); ?>
      </dt>
      <dd>
	<?php echo JText::_('COM_KETSHOP_LANG_REGION_'.strtoupper($this->item->addresses['shipping']->region_code)); ?>
      </dd>
      <dt>
	<?php echo JText::_('COM_KETSHOP_FIELD_COUNTRY_LABEL'); ?>
      </dt>
      <dd>
	<?php echo JText::_('COM_KETSHOP_LANG_COUNTRY_'.strtoupper($this->item->addresses['shipping']->country_code)); ?>
      </dd>
    </dl>
  <?php else : ?>
    <div class="alert">
	<?php echo JText::_('COM_KETSHOP_NO_SHIPPING_ADDRESS_PROVIDED'); ?>
    </div>
  <?php endif; ?>
</fieldset>
