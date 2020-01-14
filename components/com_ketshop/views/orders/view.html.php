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
  protected $order_model = null;


  function display($tpl = null)
  {
    // Initialise variables
    $this->state = $this->get('State');
    $this->items = $this->get('Items');
    $this->pagination = $this->get('Pagination');
    $this->filterForm = $this->get('FilterForm');
    $this->activeFilters = $this->get('ActiveFilters');
    $this->order_model = JModelLegacy::getInstance('Order', 'KetshopModel');

    // Adds products linked to each order.
    foreach($this->items as $i => $item) {
      $this->items[$i]->products = $this->order_model->getProducts($item);
    }

    parent::display($tpl);
  }


  protected function setDocument() 
  {
    $doc = JFactory::getDocument();
    $doc->addStyleSheet(JURI::base().'components/com_ketshop/css/ketshop.css');
  }
}
