<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2020 - 2020 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JLoader::register('OrderTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/order.php');


class plgKetshoppaymentPaypal extends JPlugin
{
  use OrderTrait;


  /**
   * Collects and returns all the payment mode objects linked to the Paypal plugin.
   *
   * @return  array	A list of payment mode objects.
   */
  public function onKetshopPayment()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('id, name, plugin_element, description')
	  ->from('#__ketshop_payment_mode')
	  ->where('plugin_element='.$db->Quote('paypal'))
	  ->where('published=1')
	  ->order('ordering');
    $db->setQuery($query);

    return $db->loadObjectList();
  }


  public function onKetshopPaymentPaypal($order, $settings)
  {
    $data = new stdClass();
    $data->api_url = $this->params->get('api_url');
    $data->paypal_id = $this->params->get('paypal_id');
    $data->return_url = $this->params->get('return_url');
    $data->cancel_url = $this->params->get('cancel_return');
    $data->products = $this->getProducts($order);
    $data->currency_code = $settings->currency_code;


    return $this->renderPaymentForm($data);
  }


  public function onKetshopPaymentResponse($context, &$data, &$params, $limitstart)
  {
  }


  public function onKetshopPaymentCancel($context, &$data, &$params, $limitstart)
  {
  }


  private function renderPaymentForm($data)
  {
    $html = '';
    $html .= '<form action="'.$data->api_url.'" method="post">';
    $html .= '<input type="hidden" name="business" value="'.$data->paypal_id.'">';
    $html .= '<input type="hidden" name="cmd" value="_xclick">';

    foreach($data->products as $product) {
      $name = (!empty($product->variant_name)) ? $product->name.' '.$product->variant_name : $product->name;

      $html .= '<input type="hidden" name="item_name" value="'.$name.'">';
      $html .= '<input type="hidden" name="item_number" value="'.$product->code.'">';
      $html .= '<input type="hidden" name="amount" value="'.$product->final_price_with_tax.'">';
      $html .= '<input type="hidden" name="currency_code" value="'.$data->currency_code.'">';
    }

    $html .= '<input type="hidden" name="return" value="'.$data->return_url.'">';
    $html .= '<input type="hidden" name="cancel_return" value="'.$data->cancel_url.'">';
    $html .= '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif">';
    $html .= '</form>';

    return $html;
  }
}

