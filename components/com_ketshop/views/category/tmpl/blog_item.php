<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.framework');


// Create shortcut for params.
$params = $this->item->params;
$variant = $this->item->variants[0];
?>

<div class="product-item">
  <?php echo JLayoutHelper::render('product.title', array('item' => $this->item, 'current_cat_id' => $this->state->get('category.id'), 'now_date' => $this->now_date)); 
	echo JLayoutHelper::render('product.icons', array('item' => $this->item, 'user' => $this->user, 'uri' => $this->uri)); ?>

  <?php if($params->get('show_image')) {
          // Set the default class for the very first image div.
	  $default = ' default-images';

	  foreach($this->item->variants as $variant) {
	    echo '<div class="variant-images'.$default.'" id="variant-images-'.$this->item->id.'-'.$variant->var_id.'">';
	    foreach($variant->images as $image) {
	      echo JLayoutHelper::render('product.image', array('product' => $this->item, 'image' => $image, 'params' => $params));
	      // Only use the very first image.
	      break;
	    }
	    // Reset the default class after the very first image div.
	    $default = '';
	    echo '</div>';
	  }
	}
   ?>

  <?php if($params->get('show_categories', 1)) : 
	  echo JLayoutHelper::render('product.categories', array('item' => $this->item, 'current_cat_id' => $this->state->get('category.id'))); 
       endif; ?>

  <?php echo $this->item->intro_text; ?>

  <?php if($params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
	  <?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); 
		echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
  <?php endif; ?>

  <?php if($params->get('show_readmore') && !empty($this->item->full_text)) :
	  if($params->get('access-view')) :
	    $link = JRoute::_(KetshopHelperRoute::getProductRoute($this->item->slug, $this->state->get('category.id'), $this->item->language));
	  else : // Redirect the user to the login page.
	    $menu = JFactory::getApplication()->getMenu();
	    $active = $menu->getActive();
	    $itemId = $active->id;
	    $link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid='.$itemId, false));
	    $link->setVar('return', base64_encode(JRoute::_(KetshopHelperRoute::getProductRoute($this->item->slug, $this->state->get('category.id'), $this->item->language), false)));
	  endif; 

	  echo JLayoutHelper::render('product.product_page', array('item' => $this->item, 'params' => $params, 'link' => $link)); 
     endif; ?>

  <?php if($this->item->nb_variants > 1) : // Shows the variant picker.
	  echo JLayoutHelper::render('product.variants', array('product' => $this->item, 'variants' => $this->item->variants, 'params' => $params));
        endif; ?>

<?php 
          // Set the default class for the very first variant div.
	  $default = ' default-variant';

	  foreach($this->item->variants as $variant) :
	    echo '<div class="product-variant'.$default.'" id="product-variant-'.$this->item->id.'-'.$variant->var_id.'">';
	      echo JLayoutHelper::render('product.availability', array('product' => $this->item, 'variant' => $variant, 'params' => $params, 'view' => 'product')); 
	      echo JLayoutHelper::render('product.price', array('variant' => $variant, 'params' => $params, 'shop_settings' => $this->shop_settings)); 
	      echo JLayoutHelper::render('product.tabs', array('product' => $this->item, 'variant' => $variant, 'params' => $params));
	    echo '</div>';
	    // Reset the default class after the very first variant div.
	    $default = '';
	  endforeach; ?>
</div>

