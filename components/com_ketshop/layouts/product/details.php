<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// Create a shortcut for params.
$product = $displayData['product'];
$variant = $displayData['variant'];
$params = $displayData['params'];
?>

<?php if($params->get('show_code') || $params->get('show_hits')  ||
	 $params->get('show_tax') || $params->get('show_categories')) : ?>

  <table class="table table-condensed small">

  <?php if($params->get('show_code') && !empty($variant->code)) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_REFERENCE'); ?>
    </td><td>
      <?php echo $this->escape($variant->code); ?>
    </td></tr>
  <?php endif; ?>

  <?php if($params->get('show_sales')) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_SALES_LABEL'); ?>
    </td><td>
      <?php echo $variant->sales; ?>
    </td></tr>
  <?php endif; ?>

  <?php if($params->get('show_hits')) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_HITS_LABEL'); ?>
    </td><td>
      <?php echo $product->hits; ?>
    </td></tr>
  <?php endif; ?>

  <?php if($params->get('show_tax')) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_TAX'); ?>
    </td><td>
      <?php if($params->get('show_tax_name')) : ?>
	<?php echo $product->tax_name; ?>
      <?php endif; ?>
      <?php echo $product->tax_rate.' %'; ?>
    </td></tr>
  <?php endif; ?>

  <?php if($params->get('show_category')) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_CATEGORY'); ?>
    </td><td>
      <?php echo '<span itemprop="genre">'.$this->escape($product->category_title).'</span>'; ?>
    </td></tr>
  <?php endif; ?>

  <?php if($params->get('show_parent_category')) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_PARENT'); ?>
    </td><td>
      <?php echo '<span itemprop="genre">'.$this->escape($product->parent_title).'</span>'; ?>
    </td></tr>
  <?php endif; ?>

  <?php if($params->get('show_creator')) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_WRITTEN_BY'); ?>
    </td><td>
      <?php echo '<span itemprop="genre">'.$this->escape($product->creator).'</span>'; ?>
    </td></tr>
  <?php endif; ?>

  <?php if($params->get('show_publish_date')) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_PUBLISHED_DATE_ON'); ?>
    </td><td>
      <?php echo '<span itemprop="genre">'.JHtml::_('date', $product->publish_up, JText::_('DATE_FORMAT_LC3')).'</span>'; ?>
    </td></tr>
  <?php endif; ?>

  <?php if($params->get('show_create_date')) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_CREATED_DATE_ON'); ?>
    </td><td>
      <?php echo '<span itemprop="genre">'.JHtml::_('date', $product->created, JText::_('DATE_FORMAT_LC3')).'</span>'; ?>
    </td></tr>
  <?php endif; ?>

  <?php if($params->get('show_modify_date')) : ?>
    <tr><td>
      <?php echo JText::_('COM_KETSHOP_PRODUCT_LAST_UPDATED'); ?>
    </td><td>
      <?php echo '<span itemprop="genre">'.JHtml::_('date', $product->modified, JText::_('DATE_FORMAT_LC3')).'</span>'; ?>
    </td></tr>
  <?php endif; ?>
  </table>
<?php endif; ?>


