<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die;



class KetshopViewPayment extends JViewLegacy
{
  protected $shop_settings = null;
  protected $payment_mode = null;
  protected $payment_form = null;


  function display($tpl = null)
  {
    // Initialise variables
    $user = JFactory::getUser();
    $this->payment_mode = $this->get('PaymentMode');
    $this->payment_form = $this->get('PaymentForm');
    $this->shop_settings = UtilityHelper::getShopSettings($user->id);

    JavascriptHelper::loadFieldLabels();

    $this->setDocument();

    parent::display($tpl);
  }


  protected function setDocument() 
  {
    $doc = JFactory::getDocument();
    $doc->addStyleSheet(JURI::base().'components/com_ketshop/css/ketshop.css');
  }
}
