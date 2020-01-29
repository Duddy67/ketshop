<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 

JLoader::register('KetshopHelperRoute', JPATH_SITE.'/components/com_ketshop/helpers/route.php');


class KetshopViewOrder extends JViewLegacy
{
  protected $item;
  protected $form;
  protected $state;
  protected $params = null;
  protected $shop_settings = null;
  protected $layout_path = null;


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
    $this->item = $this->get('Item');
    $this->form = $this->get('Form');
    $this->state = $this->get('State');
    $this->shop_settings = UtilityHelper::getShopSettings($this->item->customer_id);
    $this->params = $this->state->get('params');

    // Check for errors.
    if(count($errors = $this->get('Errors'))) {
      throw new Exception(implode("\n", $errors), 500);
    }

    // Sets the editing status.
    $this->shop_settings->can_edit = false;
    $this->shop_settings->view_name = 'order';
    $this->shop_settings->price_display = $this->shop_settings->tax_method;

    $this->layout_path = JPATH_SITE.'/components/com_ketshop/layouts/';

    // Ensures prices are displayed.
    $this->params->set('show_price', 1);
    // Don't display price rule names in the price columns.
    $this->params->set('show_rule_name', 0);
    // Flag used to display prices either by unit or quantity.
    $this->params->def('product_price', '');
    $this->params->def('display_type', 'cart');

    // Display the toolbar.
    $this->addToolBar();
    $this->setDocument();

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
    // Make main menu inactive.
    JFactory::getApplication()->input->set('hidemainmenu', true);

    $user = JFactory::getUser();
    $userId = $user->get('id');

    // Get the allowed actions list
    $canDo = KetshopHelper::getActions($this->state->get('filter.category_id'));
    $isNew = $this->item->id == 0;
    $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

    // Display the view title (according to the user action) and the icon.
    JToolBarHelper::title($isNew ? JText::_('COM_KETSHOP_NEW_ORDER') : JText::_('COM_KETSHOP_EDIT_ORDER'), 'pencil-2');

    if($isNew) {
      // Check the "create" permission for the new records.
      if($canDo->get('core.create')) {
	JToolBarHelper::apply('order.apply', 'JTOOLBAR_APPLY');
	JToolBarHelper::save('order.save', 'JTOOLBAR_SAVE');
	JToolBarHelper::custom('order.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
      }
    }
    else {
      // Can't save the record if it's checked out.
      if(!$checkedOut) {
	// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
	if($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId)) {
	  // We can save the new record
	  JToolBarHelper::apply('order.apply', 'JTOOLBAR_APPLY');
	  JToolBarHelper::save('order.save', 'JTOOLBAR_SAVE');
	}
      }

      // If checked out, we can still save
      if($canDo->get('core.create')) {
	//JToolBarHelper::save2copy('order.save2copy');
      }
    }

    JToolBarHelper::cancel('order.cancel', 'JTOOLBAR_CANCEL');
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
    $doc->addStyleSheet(JURI::root().'components/com_ketshop/css/ketshop.css');
  }
}

