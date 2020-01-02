<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('JPATH_BASE') or die('Restricted access');

$categories = $displayData['item']->categories;
$item = $displayData['item'];
?>

<ul class="categories inline">
<?php foreach($categories as $category) : ?> 
  <li class="category-<?php echo $category->id; ?> category-list0" itemprop="keywords">
    <?php // No need link for the current category (used in category view). 
	  if(isset($displayData['current_cat_id']) && $displayData['current_cat_id'] == $category->id) : ?> 
      <span class="label label-default"><?php echo $this->escape($category->title); ?></span>
  <?php else : // product view.
          $labelType = 'success';
	  if($category->id == $item->parent_id) {
	    // Sets the parent category to a different color.
	    $labelType = 'warning';
	  }

	  $url = JRoute::_(KetshopHelperRoute::getCategoryRoute($category->id.':'.$category->alias, $category->language));

	  // In product view some category data is retrieved. 
	  if(isset($item->limit_start) && $item->limit_start > 0 && $category->id == $item->from_cat_id) {
	    // Sets the pagination in case the user clicks to go back to the category he comes from.
	    $limitStart = '&limitstart='.$item->limit_start;
	    $url = JRoute::_(KetshopHelperRoute::getCategoryRoute($category->id.':'.$category->alias, $category->language).$limitStart);
	  }
    ?> 
      <a href="<?php echo $url;?>" class="label label-<?php echo $labelType; ?>"><?php echo $this->escape($category->title); ?></a>
  <?php endif; ?> 
  </li>
<?php endforeach; ?>
</ul>

