<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
?>


<ol class="nav nav-tabs nav-stacked">
<?php foreach ($this->link_items as &$item) : ?>
	<li>
	  <a href="<?php echo JRoute::_(KetshopHelperRoute::getProductRoute($item->slug, $this->state->get('category.id'), $item->language)); ?>">
		      <?php echo $item->name; ?></a>
	</li>
<?php endforeach; ?>
</ol>

