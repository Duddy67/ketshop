<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die;
?>

<div>
 <h3><?php echo $this->payment_mode->name; ?></h3>
  <?php if(!empty($this->payment_mode->information)) : ?>
    <div><?php echo $this->payment_mode->information; ?></div>
  <?php endif; ?>

  <?php echo $this->payment_form; ?>
</div>
