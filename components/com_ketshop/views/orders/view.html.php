<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */


// No direct access
defined('_JEXEC') or die;


class KetshopViewOrders extends JViewLegacy
{
  protected $state = null;
  protected $items = null;


  function display($tpl = null)
  {
    // Initialise variables
    $this->state = $this->get('State');
    $this->items = $this->get('Items');
    $this->pagination = $this->get('Pagination');
    $this->filterForm = $this->get('FilterForm');
    $this->activeFilters = $this->get('ActiveFilters');

echo JRoute::_('index.php?option=com_ketshop&view=order&o_id=1', false);
    parent::display($tpl);
  }


  protected function setDocument() 
  {
    $doc = JFactory::getDocument();
    $doc->addStyleSheet(JURI::base().'components/com_ketshop/css/ketshop.css');
  }
}
