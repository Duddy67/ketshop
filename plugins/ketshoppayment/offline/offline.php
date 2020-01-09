<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JLoader::register('ShopHelper', JPATH_SITE.'/components/com_ketshop/helpers/shop.php');


class plgKetshoppaymentOffline extends JPlugin
{

  /**
   * Collects and returns all the payment mode objects linked to the offline plugin.
   *
   * @return  array	A list of payment mode objects.
   */
  public function onKetshopPayment()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('id, name, plugin_element, description')
	  ->from('#__ketshop_payment_mode')
	  ->where('plugin_element='.$db->Quote('offline'))
	  ->where('published=1')
	  ->order('ordering');
    $db->setQuery($query);

    return $db->loadObjectList();
  }


  public function onKetshopPaymentOffline ($order, $settings)
  {
    $html = '<form action="'.JRoute::_('index.php?option=com_ketshop&task=payment.trigger&suffix=transaction&payment_mode=offline', false).'" method="post" id="ketshop_offline_payment">';
    $html .= '<span class="btn">';
    $html .= '<a href="'.JRoute::_('index.php?option=com_ketshop&view=checkout', false).'" class="btn-link ketshop-btn">';
    $html .= JText::_('COM_KETSHOP_CANCEL').' <span class="icon-remove"></span></a></span>';
    $html .= '<span class="btn" onclick="document.getElementById(\'ketshop_offline_payment\').submit();">';
    $html .= JText::_('COM_KETSHOP_PAY_NOW').' <span class="icon-shop-credit-card"></span></a></span>';
    $html .= '</form>';

    return $html;
  }


  public function onKetshopPaymentOfflineTransaction($order, $settings)
  {
    // NB: Payment results can only be ok with offline payment method since there's
    //     no web procedure to pass through.

    //ShopHelper::createTransaction($amounts, $utility, $settings); 

    //Redirect the customer to the ending step.
    return JRoute::_('index.php?option=com_ketshop&task=payment.success', false);
  }


  public function onKetshopPaymentOfflineCancel($amounts, $cart, $settings, $utility)
  {
    //Some code here if needed.
    //...
    return true;
  }
}
