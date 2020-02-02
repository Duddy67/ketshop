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
	  ->where('published=1');
    $db->setQuery($query);

    return $db->loadObject();
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


  public function onKetshopPaymentPaypalNotify($order, $settings)
  {
    // CONFIG: Enable debug mode. This means we'll log requests into 'ipn.log' in the same directory.
    // Especially useful if you encounter network errors or other intermittent problems with IPN (validation).
    // Set this to 0 once you go live or don't require logging.
    define("DEBUG", 1);
    // Set to 0 once you're ready to go live
    define("USE_SANDBOX", 1);
    define("LOG_FILE", "ipn.log");

    // Read POST data
    // reading posted data directly from $_POST causes serialization
    // issues with array data in POST. Reading raw POST data from input stream instead.
    $raw_post_data = file_get_contents('php://input');
    $raw_post_array = explode('&', $raw_post_data);
    $myPost = array();

    foreach($raw_post_array as $keyval) {
      $keyval = explode ('=', $keyval);

      if(count($keyval) == 2) {
	$myPost[$keyval[0]] = urldecode($keyval[1]);
      }
    }

    // read the post from PayPal system and add 'cmd'
    $req = 'cmd=_notify-validate';

    if(function_exists('get_magic_quotes_gpc')) {
      $get_magic_quotes_exists = true;
    }

    foreach($myPost as $key => $value) {
      if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
	$value = urlencode(stripslashes($value));
      }
      else {
	$value = urlencode($value);
      }

      $req .= "&$key=$value";
    }

    // Post IPN data back to PayPal to validate the IPN data is genuine
    // Without this step anyone can fake IPN data
    if(USE_SANDBOX == true) {
      $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
    }
    else {
      $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
    }

    $ch = curl_init($paypal_url);

    if($ch == false) {
      return false;
    }

    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

    if(DEBUG == true) {
      curl_setopt($ch, CURLOPT_HEADER, 1);
      curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    }

    // CONFIG: Optional proxy configuration
    //curl_setopt($ch, CURLOPT_PROXY, $proxy);
    //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
    // Set TCP timeout to 30 seconds
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

    // CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
    // of the certificate as shown below. Ensure the file is readable by the webserver.
    // This is mandatory for some environments.
    //$cert = __DIR__ . "./cacert.pem";
    //curl_setopt($ch, CURLOPT_CAINFO, $cert);
    $res = curl_exec($ch);

    if(curl_errno($ch) != 0) { // cURL error
      if(DEBUG == true) {
	error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
      }

      curl_close($ch);

      exit;
    }
    else {
      // Log the entire HTTP response if debug is switched on.
      if(DEBUG == true) {
	error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
	error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
      }

      curl_close($ch);
    }

    // Inspect IPN validation result and act accordingly
    // Split response headers and payload, a better way for strcmp
    $tokens = explode("\r\n\r\n", trim($res));
    $res = trim(end($tokens));

    if(strcmp ($res, "VERIFIED") == 0) {
      // assign posted variables to local variables
      $item_name = $_POST['item_name'];
      $item_number = $_POST['item_number'];
      $payment_status = $_POST['payment_status'];
      $payment_amount = $_POST['mc_gross'];
      $payment_currency = $_POST['mc_currency'];
      $txn_id = $_POST['txn_id'];
      $receiver_email = $_POST['receiver_email'];
      $payer_email = $_POST['payer_email'];

      include("DBController.php");
      $db = new DBController();

      // check whether the payment_status is Completed
      $isPaymentCompleted = false;

      if($payment_status == "Completed") {
	$isPaymentCompleted = true;
      }

      // check that txn_id has not been previously processed
      $isUniqueTxnId = false;
      $param_type="s";
      $param_value_array = array($txn_id);

      $result = $db->runQuery("SELECT * FROM payment WHERE txn_id = ?",$param_type,$param_value_array);

      if(empty($result)) {
	$isUniqueTxnId = true;
      }

      // check that receiver_email is your PayPal email
      // check that payment_amount/payment_currency are correct
      if($isPaymentCompleted) {
	$param_type = "sssdss";
	$param_value_array = array($item_number, $item_name, $payment_status, $payment_amount, $payment_currency, $txn_id);
	$payment_id = $db->insert("INSERT INTO payment(item_number, item_name, payment_status, payment_amount, payment_currency, txn_id) VALUES(?, ?, ?, ?, ?, ?)", $param_type, $param_value_array);
      }

      // process payment and mark item as paid.

      if(DEBUG == true) {
	error_log(date('[Y-m-d H:i e] '). "Verified IPN: $req ". PHP_EOL, 3, LOG_FILE);
      }
    }
    else if(strcmp ($res, "INVALID") == 0) {
      // log for manual investigation
      // Add business logic here which deals with invalid IPN messages
      if(DEBUG == true) {
	error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
      }
    }
  }


  public function onKetshopPaymentPaypalCancel($context, &$data, &$params, $limitstart)
  {
  }


  //Retrieve the Paypal result parameters then turn it into an array for more convenience.
  private function buildPaypalParamsArray($paypalResult)
  {
    //Create an array of parameters.
    $parametersList = explode("&",$paypalResult);

    //Separate name and value of each parameter.
    foreach($parametersList as $paypalParam) {
      list($name, $value) = explode("=", $paypalParam);
      $paypalParamArray[$name]=urldecode($value); //Create final array.
    }

    return $paypalParamArray; 
  }


  private function renderPaymentForm($data)
  {
    $html = '<form action="'.$data->api_url.'" method="post">';
    $html .= '<input type="hidden" name="business" value="'.$data->paypal_id.'">';
    $html .= '<input type="hidden" name="cmd" value="_xclick">';
    $html .= '<input type="hidden" name="notify_url" value="">';

    foreach($data->products as $i => $product) {
      $name = (!empty($product->variant_name)) ? $product->name.' '.$product->variant_name : $product->name;

      $html .= '<input type="hidden" name="item_name'.$i.'" value="'.$name.'">';
      $html .= '<input type="hidden" name="item_number'.$i.'" value="'.$product->code.'">';
      $html .= '<input type="hidden" name="amount'.$i.'" value="'.$product->final_price_with_tax.'">';
      $html .= '<input type="hidden" name="currency_code'.$i.'" value="'.$data->currency_code.'">';
    }

    $html .= '<input type="hidden" name="return" value="'.$data->return_url.'">';
    $html .= '<input type="hidden" name="cancel_return" value="'.$data->cancel_url.'">';
    $html .= '<span class="btn">';
    $html .= '<a href="'.JRoute::_('index.php?option=com_ketshop&view=checkout', false).'" class="btn-link ketshop-btn">';
    $html .= JText::_('COM_KETSHOP_CANCEL').' <span class="icon-remove"></span></a></span>';
    $html .= '<input type="image" name="submit" border="0" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif">';
    $html .= '</form>';

    return $html;
  }
}

