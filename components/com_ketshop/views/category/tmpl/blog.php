<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('formbehavior.chosen', 'select');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
?>
<script type="text/javascript">
var ketshop = {
  clearSearchFilter: function() {
    document.getElementById('filter_search').value = '';
    ketshop.submitForm();
  },

  clearManufacturerFilter: function() {
    var manufacturers = document.getElementById('filter_manufacturer');
    for(var i = 0; i < manufacturers.options.length; i++) {
      manufacturers.options[i].selected = false;
    }

    document.getElementById('filter-cleared').value = 'filter_manufacturer';
    ketshop.submitForm();
  },

  clearAttributeFilters: function() {
    var filters = document.querySelectorAll('select[id^="filter_attribute_"]');
    for(var i = 0; i < filters.length; i++) {
      // Checks for multiselect.
      if(filters[i].multiple === true) {
	// Unselects all the options.
	for(var j = 0; j < filters[i].options.length; j++) {
	  filters[i].options[j].selected = false;
	}
      }
      else { //Single select.
	document.getElementById(filters[i].id).value = '';
      }
    }

    document.getElementById('filter-cleared').value = 'filter_attributes';
    ketshop.submitForm();
  },

  submitForm: function() {
    var action = document.getElementById('siteForm').action;
    // Set an anchor on the form.
    document.getElementById('siteForm').action = action+'#siteForm';
    document.getElementById('siteForm').submit();
  }
};
</script>

<div class="blog<?php echo $this->pageclass_sfx;?>">
  <?php if($this->params->get('show_page_heading')) : ?>
	  <h1>
	    <?php echo $this->escape($this->params->get('page_heading')); ?>
	  </h1>
  <?php endif; ?>
  <?php if($this->params->get('show_category_title', 1)) : ?>
	  <h2 class="category-title">
	      <?php echo JHtml::_('content.prepare', $this->category->title, '', $this->category->extension.'.category.title'); ?>
	  </h2>
  <?php endif; ?>
  <?php if($this->params->get('show_cat_tags', 1)) : ?>
	  <?php echo JLayoutHelper::render('joomla.content.tags', $this->category->tags->itemTags); ?>
  <?php endif; ?>
  <?php if($this->params->get('show_description') || $this->params->def('show_description_image')) : ?>
	  <div class="category-desc">
		  <?php if($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
			  <img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
		  <?php endif; ?>
		  <?php if($this->params->get('show_description') && $this->category->description) : ?>
			  <?php echo JHtml::_('content.prepare', $this->category->description, '', $this->category->extension.'.category'); ?>
		  <?php endif; ?>
		  <div class="clr"></div>
	  </div>
  <?php endif; ?>

  <form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="siteForm" id="siteForm">

    <?php echo JLayoutHelper::render('category.filters.basic', array('filter_form' => $this->filter_form, 'state' => $this->state, 'params' => $this->params)); ?>

    <?php echo JLayoutHelper::render('category.filters.manufacturer', array('filter_manufacturer' => $this->filter_manufacturer, 'state' => $this->state)); ?>

    <?php echo JLayoutHelper::render('category.filters.attribute', array('filter_attribute' => $this->attribute_filters, 'state' => $this->state)); ?>

    <?php if(empty($this->lead_items) && empty($this->link_items) && empty($this->intro_items)) : ?>
      <?php if($this->params->get('show_no_products')) : ?>
	      <p><?php echo JText::_('COM_KETSHOP_NO_PRODUCTS'); ?></p>
      <?php endif; ?>
    <?php endif; ?>

    <?php $leadingcount = 0; ?>
    <?php if(!empty($this->lead_items)) : ?>
	    <div class="items-leading clearfix">
	  <?php foreach($this->lead_items as &$item) : ?>
		  <div class="leading-<?php echo $leadingcount; ?><?php echo $item->published == 0 ? ' system-unpublished' : null; ?>"
			  itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
			  <?php
			  $this->item = & $item;
			  echo $this->loadTemplate('item');
			  ?>
		  </div>
		  <?php $leadingcount++; ?>
	  <?php endforeach; ?>
	    </div><!-- end items-leading -->
    <?php endif; ?>

    <?php
    $introcount = (count($this->intro_items));
    $counter = 0;
    ?>

    <?php if(!empty($this->intro_items)) : ?>
      <?php foreach($this->intro_items as $key => &$item) : ?>
	  <?php $rowcount = ((int) $key % (int) $this->columns) + 1; ?>
	  <?php if($rowcount == 1) : ?>
		  <?php $row = $counter / $this->columns; ?>
		  <div class="items-row cols-<?php echo (int) $this->columns; ?> <?php echo 'row-'.$row; ?> row-fluid clearfix">
	  <?php endif; ?>
	  <div class="span<?php echo round((12 / $this->columns)); ?>">
		  <div class="item column-<?php echo $rowcount; ?><?php echo $item->published == 0 ? ' system-unpublished' : null; ?>"
		      itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
		      <?php
		      $this->item = & $item;
		      echo $this->loadTemplate('item');
		      ?>
		  </div>
		  <!-- end item -->
		  <?php $counter++; ?>
	  </div><!-- end span -->
	  <?php if(($rowcount == $this->columns) or ($counter == $introcount)) : ?>
		  </div><!-- end row -->
	  <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if(!empty($this->link_items)) : ?>
	    <div class="items-more">
	      <?php echo $this->loadTemplate('links'); ?>
	    </div>
    <?php endif; ?>

    <?php if(($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
    <div class="pagination">
	    <?php echo $this->pagination->getListFooter(); ?>

	    <?php if ($this->params->def('show_pagination_results', 1) || $this->params->def('show_pagination_pages', 1)) : ?>
	      <div class="ketshop-results">
		  <?php if ($this->params->def('show_pagination_results', 1)) : ?>
		      <p class="counter pull-left small">
			<?php echo $this->pagination->getResultsCounter(); ?>
		      </p>
		  <?php endif; ?>
		  <?php if ($this->params->def('show_pagination_pages', 1)) : ?>
		      <p class="counter pull-right small">
			<?php echo $this->pagination->getPagesCounter(); ?>
		      </p>
		  <?php endif; ?>
	      </div>
	    <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if($this->get('children') && $this->maxLevel != 0) : ?>
	    <div class="cat-children">
	      <h3><?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
	      <?php echo $this->loadTemplate('children'); ?>
	    </div>
    <?php endif; ?>

    <input type="hidden" name="filter_cleared" id="filter-cleared" value="" />
    <input type="hidden" name="limitstart" value="" />
    <input type="hidden" id="token" name="<?php echo JSession::getFormToken(); ?>" value="1" />
    <input type="hidden" id="cat-id" name="cat_id" value="<?php echo $this->category->id; ?>" />
    <input type="hidden" name="task" value="" />
  </form>
</div><!-- blog -->

<?php

$doc = JFactory::getDocument();
$doc->addScript(JURI::base().'components/com_ketshop/js/variants.js');
$doc->addScript(JURI::base().'components/com_ketshop/js/filters.js');

if($this->params->get('filter_field') == 'name') {
  // Loads the JQuery autocomplete file.
  JHtml::_('script', 'media/jui/js/jquery.autocomplete.min.js');
  // Loads our js script.
  $doc->addScript(JURI::base().'components/com_ketshop/js/autocomplete.js');
}

