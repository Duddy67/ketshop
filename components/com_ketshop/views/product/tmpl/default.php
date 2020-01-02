<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access'); 

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');


// Create some shortcuts.
$params = $this->item->params;
$item = $this->item;

// Sets the canonical url of the item.

// Gets the current protocol and domain name without path (if any).
$domain = preg_replace('#'.JUri::root(true).'/$#', '', JUri::root());
// Uses the main category to build the canonical url. 
$link = $domain.JRoute::_(KetshopHelperRoute::getProductRoute($this->item->slug, $this->item->catid, $this->item->language));
$canUrl = '<link href="'.$link.'" rel="canonical" />';
// Inserts the canonical link in HTML head.
$document = JFactory::getDocument();
$document->addCustomTag($canUrl);
?>

<div class="item-page<?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Product">
  <?php if($item->params->get('show_page_heading')) : ?>
    <div class="page-header">
      <h1><?php echo $this->escape($params->get('page_heading')); ?></h1>
    </div>
  <?php endif; ?>

  <?php echo JLayoutHelper::render('product.title', array('item' => $item, 'now_date' => $this->nowDate)); 
	echo JLayoutHelper::render('product.icons', array('item' => $item, 'user' => $this->user, 'uri' => $this->uri));
  ?>

  <?php if($params->get('show_image')) {
          // Set the default class for the very first image div.
	  $default = ' default-images';

	  foreach($item->variants as $variant) {
	    echo '<div class="variant-images'.$default.'" id="variant-images-'.$item->id.'-'.$variant->var_id.'">';
	    foreach($variant->images as $image) {
	      echo JLayoutHelper::render('product.image', array('product' => $item, 'image' => $image, 'params' => $params));
	      // Reset the default class after the very first image div.
	      $default = '';
	    }

	    echo '</div>';
	  }
	}
   ?>

  <?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
		       || $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category')
		       || $params->get('show_creator') ); ?>

  <?php echo JLayoutHelper::render('product.categories', array('item' => $this->item)); ?>

  <?php if($item->params->get('show_intro')) : ?>
    <?php echo $item->intro_text; ?>
  <?php endif; ?>

  <?php if(!empty($item->full_text)) : ?>
    <?php echo $item->full_text; ?>
  <?php endif; ?>

  <?php if($item->nb_variants > 1) :  // Shows the variant picker.
	  echo JLayoutHelper::render('product.variants', array('product' => $item, 'variants' => $item->variants, 'params' => $params));
        endif; ?>

<?php
	// Set the default class for the very first variant div.
	$default = ' default-variant';

	foreach($item->variants as $variant) :
	  echo '<div class="product-variant'.$default.'" id="product-variant-'.$item->id.'-'.$variant->var_id.'">';
	    echo JLayoutHelper::render('product.availability', array('product' => $item, 'variant' => $variant, 'params' => $params, 'view' => 'product')); 
	    echo JLayoutHelper::render('product.price', array('variant' => $variant, 'params' => $params, 'shop_settings' => $this->shop_settings)); 
	    echo JLayoutHelper::render('product.tabs', array('product' => $item, 'variant' => $variant, 'params' => $params));
	  echo '</div>';
	  // Reset the default class after the very first variant div.
	  $default = '';
	endforeach; ?>

  <?php if($params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
	  <?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); 
		echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
  <?php endif; ?>
</div>

<?php
// Load jQuery library before our script.
JHtml::_('jquery.framework');
// Loads the jQuery scripts.
$doc = JFactory::getDocument();
$doc->addScript(JURI::root().'components/com_ketshop/js/variants.js');

