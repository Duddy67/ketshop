<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// Create shortcuts.
$product = $displayData['product'];
$image = $displayData['image'];
$params = $displayData['params'];
//$params = $displayData['params'];
//echo '<pre>';
//var_dump($item->images);
//echo '</pre>';
?>

<?php if(!empty($image->src)) { // Display the image of the product.
	$size = ShopHelper::getThumbnailSize($image->width, $image->height, $image->img_reduction_rate);
	$imgSrc = $image->src;
	$imgAlt = $image->alt;
      }
      // Display a default image.
      else { 
	$size = ShopHelper::getThumbnailSize(200, 200, $image->img_reduction_rate); 
	$imgSrc = 'media/com_ketshop/images/missing-picture.jpg';
	$imgAlt = JText::_('COM_KETSHOP_IMAGE_UNAVAILABLE');
      }
  ?>

  <div class="variant-image">
    <?php if($params->get('linked_image') && $params->get('access-view')) : // Create the image link.
	    $link = JRoute::_(KetshopHelperRoute::getProductRoute($product->slug, $product->catid, $product->language));
	?>
      <a href="<?php echo $link; ?>">
    <?php endif; ?>
      <img class="image" src="<?php echo $imgSrc; ?>" width="<?php echo (int)$size['width']; ?>"
	   height="<?php echo (int)$size['height']; ?>" alt="<?php echo $this->escape($imgAlt); ?>" />

    <?php if($params->get('linked_image') && $params->get('access-view')) : // Close the image link. ?>
      </a>
    <?php endif; ?>
  </div>

