<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');


/**
 * HTML View class for the KetShop component.
 */
class KetshopViewConnection extends JViewLegacy
{
  protected $state;
  protected $now_date;
  protected $user;
  protected $registration;
  protected $login;
  protected $uri;
  public $shop_settings;


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
    $this->user = JFactory::getUser();

    // Redirect registered users to the shipment page.
    if(!$this->user->guest) {
      $app = JFactory::getApplication();
      $app->redirect(JRoute::_('index.php?option=com_ketshop&view=shipment', false));
    }

    // Initialise variables
    //$this->state = $this->get('State');
    $this->registration = $this->get('Form');
    // Gets the global settings of the shop.
    $this->shop_settings = UtilityHelper::getShopSettings($this->user->get('id'));

    // Check for errors.
    /*if(count($errors = $this->get('Errors'))) {
      throw new Exception(implode("\n", $errors), 500);
    }*/

    // Get the user object and the current url, (needed in the product edit layout).
    $this->uri = JUri::getInstance();

    $this->now_date = JFactory::getDate()->toSql();

    // Loads the required forms.
    //$this->registration = new JForm('Registration');
    //$this->registration->loadFile(JPATH_SITE.'/components/com_ketshop/models/forms/registration.xml');

    $this->login = new JForm('Login');
    $this->login->loadFile(JPATH_SITE.'/components/com_ketshop/models/forms/login.xml');

    JavascriptHelper::loadFunctions(array('region'));

    $this->setDocument();

    parent::display($tpl);
  }


  /**
   * Includes possible css and Javascript files.
   *
   * @return  void
   */
  protected function setDocument() 
  {
    $doc = JFactory::getDocument();
    $doc->addStyleSheet(JURI::base().'components/com_ketshop/css/ketshop.css');
  }
}

