<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class KetshopViewPaymentmodes extends JViewLegacy
{
  protected $items;
  protected $state;
  protected $pagination;
  protected $missingPlugins;


  /**
   * Execute and display a template script.
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  mixed  A string if successful, otherwise an Error object.
   *
   * @see     \JViewLegacy::loadTemplate()
   * @since   3.0
   */
  public function display($tpl = null)
  {
    $this->items = $this->get('Items');
    $this->state = $this->get('State');
    $this->pagination = $this->get('Pagination');
    $this->filterForm = $this->get('FilterForm');
    $this->activeFilters = $this->get('ActiveFilters');
    $this->missingPlugins = UtilityHelper::getMissingPlugins('payment');

    // Check for errors.
    if(count($errors = $this->get('Errors'))) {
      throw new Exception(implode("\n", $errors), 500);
    }

    // If one or more plugins are missing we display a warning message.
    if(count($this->missingPlugins)) {
      // Gets the name of the missing plugin(s).
      foreach($this->missingPlugins as $missingPlugin) {
	$pluginNames .= $missingPlugin.', ';
      }

      // Removes the comma from the end of the string.
      $pluginNames = substr($pluginNames, 0,-2);
      // Displays warning message.
      JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_KETSHOP_MISSING_PLUGINS_WARNING', $pluginNames), 'notice');
    }

    // Display the tool bar.
    $this->addToolBar();

    $this->setDocument();
    $this->sidebar = JHtmlSidebar::render();

    // Display the template.
    parent::display($tpl);
  }


  /**
   * Add the page title and toolbar.
   *
   * @return  void
   *
   * @since   1.6
   */
  protected function addToolBar() 
  {
    // Display the view title and the icon.
    JToolBarHelper::title(JText::_('COM_KETSHOP_PAYMENT_MODES_TITLE'), 'shop-credit-card');

    // Get the allowed actions list
    $canDo = KetshopHelper::getActions();

    // N.B: We check the user permissions only against the component since 
    //      the paymentmode items have no categories.
    if($canDo->get('core.create')) {
      JToolBarHelper::addNew('paymentmode.add', 'JTOOLBAR_NEW');
    }

    if($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
      JToolBarHelper::editList('paymentmode.edit', 'JTOOLBAR_EDIT');
    }

    if($canDo->get('core.edit.state')) {
      JToolBarHelper::divider();
      JToolBarHelper::custom('paymentmodes.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
      JToolBarHelper::custom('paymentmodes.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
      JToolBarHelper::divider();
      JToolBarHelper::archiveList('paymentmodes.archive','JTOOLBAR_ARCHIVE');
      JToolBarHelper::custom('paymentmodes.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
      JToolBarHelper::trash('paymentmodes.trash','JTOOLBAR_TRASH');
    }

    if($canDo->get('core.delete')) {
      JToolBarHelper::divider();
      JToolBarHelper::deleteList('', 'paymentmodes.delete', 'JTOOLBAR_DELETE');
    }

    if($canDo->get('core.admin')) {
      JToolBarHelper::divider();
      JToolBarHelper::preferences('com_ketshop', 550);
    }
  }


  /**
   * Includes possible css and Javascript files.
   *
   * @return  void
   */
  protected function setDocument() 
  {
    $doc = JFactory::getDocument();
    $doc->addStyleSheet(JURI::base().'components/com_ketshop/ketshop.css');
  }
}

