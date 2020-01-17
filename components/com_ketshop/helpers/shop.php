<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die; //No direct access to this file.


class ShopHelper
{
  /**
   * Gets and returns the snitch session variable.
   * Creates it if it doesn't exist.
   *
   * @return  object            The snitch object.
   *
   */
  public static function getSnitch()
  {
    $session = JFactory::getSession();
    $snitch = $session->get('ketshop.snitch', null);

    if($snitch === null) {
      $snitch = new stdClass();
      $snitch->from = '';
      $snitch->empty_filters = array();
      $snitch->limit_start = 0;
    }

    return $snitch;
  }


  /**
   * Stores the given snitch object in the session.
   *
   * @param   object       The snitch object.
   *
   * @return  boolean      False whether the given parameter is not an object. True otherwise.
   *
   */
  public static function setSnitch($snitch)
  {
    if(!is_object($snitch)) {
      JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_KETSHOP_ERROR_WRONG_PARAMETER_TYPE', 'object', gettype($snitch)), 'error');
      return false;
    }

    $session = JFactory::getSession();
    $session->set('ketshop.snitch', $snitch);

    return true;
  }


  /**
   * Gets the current cookie id. Creates it if it doesn't exist.
   *
   * @return  string	the cookie id.
   *
   */
  public static function getCookieId()
  {
    // Gets input cookie object
    $inputCookie  = JFactory::getApplication()->input->cookie;

    // Gets cookie data
    $id = $inputCookie->get('ketshop', null);

    if($id === null) {
      $id = uniqid();
      // Creates a one week long cookie.
      $inputCookie->set('ketshop', $id, time() + (7 * 24 * 3600));
    }
    else {
      // TODO: https://mantis.codalia.fr/view.php?id=4
      // Extends by 1 hour the current cookie duration in case it is about to reach
      // expiration date.
      // preg_match('#-([0-9:-]+)$#', $id, $matches);
      // $date = $matches[1];
      // preg_replace('#^[0-9]{4}-[0-9]{2}-[0-9]{2}(-)#', ' ', $date);
      // https://stackoverflow.com/questions/3290424/set-a-cookie-to-never-expire
      //$inputCookie->set('ketshop', $id, 2147483647);
      //$inputCookie->set('ketshop', $id, time() + (7 * 24 * 3600) + 3600);
    }

    return $id;
  }


  /**
   * Returns the customer data of a given user. 
   *
   * @param   integer	The id of the user (optional).
   *
   * @return  object	A customer object.
   *
   */
  public static function getCustomer($userId = null)
  {
    $userId = ($userId !== null) ? $userId : JFactory::getUser()->get('id');

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select('c.user_id, c.firstname, c.lastname, c.phone, u.username, u.email, u.registerDate, u.lastvisitDate')
	  ->from('#__ketshop_customer AS c')
	  ->join('INNER', '#__users AS u ON u.id=c.user_id')
	  ->where('c.user_id='.(int)$userId);
    $db->setQuery($query);
    $customer = $db->loadObject();

    $customer->addresses = self::getCustomerAddresses($customer->user_id);

    return $customer;
  }


  /**
   * Returns the billing and shipping addresses of a given user. 
   * If no user id is passed, gets the current user's addresses.
   *
   * @param   integer	The id of the user (optional).
   *
   * @return  array	A list of billing and shipping address objects.
   *
   */
  public static function getCustomerAddresses($userId = null)
  {
    $userId = ($userId !== null) ? $userId : JFactory::getUser()->get('id');

    $addresses = array();
    $types = array('billing', 'shipping');

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    foreach($types as $type) {
      // Gets the last billing or shipping address set by the customer. 
      $query->clear();
      $query->select('a.street, a.postcode, a.city, a.region_code, a.country_code, a.continent_code, a.type,'.
		     'a.phone, a.additional, c.lang_var AS country_lang_var, r.lang_var AS region_lang_var')
	    ->from('#__ketshop_address AS a')
	    ->join('INNER', '#__ketshop_customer AS cu ON cu.user_id='.(int)$userId)
	    ->join('LEFT', '#__ketshop_country AS c ON c.alpha_2 = a.country_code')
	    ->join('LEFT', '#__ketshop_region AS r ON r.code = a.region_code')
	    ->where('a.item_id = cu.id AND a.item_type = '.$db->Quote('customer'))
	    ->where('a.type='.$db->Quote($type))
	    // Gets the latest inserted address in case of history.
	    ->order('a.created DESC')
	    ->setLimit(1);
      $db->setQuery($query);
      $address = $db->loadObject();

      if($address !== null) {
	$addresses[$type] = $address; 
      }
    }

    return $addresses;
  }


  //Return width and height of an image according to its reduction rate.
  public static function getThumbnailSize($width, $height, $reductionRate)
  {
    $size = array();

    if($reductionRate == 0) {
      //Just return the original values.
      $size['width'] = $width;
      $size['height'] = $height;
    }
    else { //Compute the new image size.
      $widthReduction = ($width / 100) * $reductionRate;
      $size['width'] = $width - $widthReduction;

      $heightReduction = ($height / 100) * $reductionRate;
      $size['height'] = $height - $heightReduction;
    }

    return $size;
  }


  //Check if cart is shippable or not.
  public static function isShippable()
  {
    //Get the cart.
    $session = JFactory::getSession();
    $cart = $session->get('cart', array(), 'ketshop'); 

    $shippable = 0; //Flag

    foreach($cart as $product) {
      //As soon as one product of the cart is shippable, we set flag to true and
      //leave the loop.
      if($product['shippable']) {
	$shippable = 1;
        break;	
      }
    }

    return $shippable;
  }


  //Return the total quantity of the products which are in the cart.
  public static function getTotalQuantity($onlyShippable = true, $sessionGroup = 'ketshop')
  {
    //Get the cart.
    $session = JFactory::getSession();
    $cart = $session->get('cart', array(), $sessionGroup); 

    $totalQuantity = 0;
    foreach($cart as $product) {
      //Only shippable products are taking into account.
      if($onlyShippable && $product['shippable']) {
	$totalQuantity += (int)$product['quantity'];
      }
      //All of the products are taking into account.
      elseif(!$onlyShippable) {
	$totalQuantity += (int)$product['quantity'];
      }
    }

    return $totalQuantity;
  }


  //Return the total delay (if any) of the products which are in the cart.
  public static function getTotalDelay()
  {
    //Get the cart.
    $session = JFactory::getSession();
    $cart = $session->get('cart', array(), 'ketshop'); 

    $totalDelay = 0;
    foreach($cart as $product) {
      //Only shippable products are taking into account.
      if($product['shippable']) {
	$totalDelay += (int)$product['availability_delay'];
      }
    }

    return $totalDelay;
  }


  public static function getLocation()
  {
    //Remove previously set variables (if any) from the url query.
    $location = self::getUrlQuery(array('limitstart','start','filter_order','language'));

    $jinput = JFactory::getApplication()->input;

    //limitstart and filter_order need to be updated in category view.
    if($jinput->get('view', '', 'string') == 'category') {
      //Get the current values from POST or GET.
      $userStates = array('limitstart' => $jinput->get('limitstart', 0, 'int'),
			  'filter_order' => $jinput->get('filter_order', ''));

      $location = $location.'&limitstart='.$userStates['limitstart'];

      if(!empty($userStates['filter_order'])) {
	$location = $location.'&filter_order='.$userStates['filter_order'];
      }
    }

    return $location;
  }


  //Retrieve the current url query and 
  //remove possible unwanted variables from it.
  public static function getUrlQuery($unwanted = array())
  {
    //Get the current GET query as an associative array.
    $GETQuery = JFactory::getApplication()->input->getArray();
    //Variable to store the url query as a string.
    $urlQuery = '';

    foreach($GETQuery as $key => $value) {
      if(!in_array($key, $unwanted)) {

	if(is_array($value)) {
	  //
	  foreach($value as $val) {
	    $urlQuery .= $key.'[]='.$val.'&';
	  }
	}
	else { //string value
	  $urlQuery .= $key.'='.$value.'&';
	}
      }
    }

    //Remove & from the end of the string.
    $urlQuery = substr($urlQuery, 0, -1);

    return $urlQuery;
  }


  //Check if a translation is needed or return the required language tag 
  //if langTag parameter is true.
  public static function switchLanguage($langTag = false)
  {
    $lg = JFactory::getLanguage();

    if($langTag) {
      return $lg->get('tag');
    }

    if($lg->get('tag') === UtilityHelper::getLanguage(true)) {
      return false;
    }
    
    return true;
  }


  //If the system languagefilter plugin is enabled we assume that the site is
  //multilingual.
  public static function isSiteMultilingual()
  {
    if(JPluginHelper::isEnabled('system', 'languagefilter')) {
      return true;
    }

    return false;
  }


  //Return the SQL query parts needed for the translation of a given item.
  public static function getTranslation($itemType, $joinField, $joinPrefix, $itemPrefix, $aliasName = 'name')
  {
    //Build the translation SQL prefix by adding "tr" to the item prefix.
    $prefix = $itemPrefix.'tr';

    //Get the required language.
    $language = self::switchLanguage(true);

    //Just used for the Quote function.
    $db = JFactory::getDbo();

    //Check for an item translation in the required language. 
    //If a translation exists (ie: a name translation has been defined) we use the translated 
    //fields, otherwise we use the untranslated fields (ie: fields write in the backend language).

    //Build the SQL conditions according to the item type.

    //name field is used in all cases.
    $translatedFields ='IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.name,'.$itemPrefix.'.name) AS '.$aliasName;

    if($itemType == 'product') {
      $translatedFields .= ',IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.alias,'.$itemPrefix.'.alias) AS alias';
      $translatedFields .= ',IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.description,'.$itemPrefix.'.intro_text) AS intro_text';

      //Full_text field as well as all of the meta data are only displayed in product view.
      if(JFactory::getApplication()->input->get->get('view', '', 'string') == 'product') {
	$translatedFields .= ',IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.full_description,'.$itemPrefix.'.full_text) AS full_text';
	$translatedFields .= ',IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.metakey,'.$itemPrefix.'.metakey) AS metakey';
	$translatedFields .= ',IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.metadesc,'.$itemPrefix.'.metadesc) AS metadesc';
	$translatedFields .= ',IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.metadata,'.$itemPrefix.'.metadata) AS metadata';
	$translatedFields .= ',IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.xreference,'.$itemPrefix.'.xreference) AS xreference';
      }
    }
    elseif($itemType == 'shipping' || $itemType == 'shipper' || $itemType == 'price_rule') {
       $translatedFields .= ',IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.description,'.$itemPrefix.'.description) AS description';
    }
    elseif($itemType == 'payment_mode') {
       $translatedFields .= ',IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.description,'.$itemPrefix.'.description) AS description';
       $translatedFields .= ',IF('.$prefix.'.name IS NOT NULL,'.$prefix.'.information,'.$itemPrefix.'.information) AS information';
    }

    //Note: tax and attribute items use only the name field.

    //Build the left join clause.
    //Note: The clause itself (ie: LEFT OUTER JOIN) must be added (or not) at
    //the location where this function is called.
    $leftJoin = '#__ketshop_translation AS '.$prefix.' ON '.$prefix.'.item_id='.$joinPrefix.'.'.$joinField.
			   ' AND '.$prefix.'.published=1 AND '.$prefix.'.item_type='.$db->Quote($itemType).
			   ' AND '.$prefix.'.language='.$db->Quote($language).' ';

    //
    $translation = new JObject;
    $translation->translated_fields = $translatedFields;
    $translation->left_join = $leftJoin;

    return $translation;
  }


  //Build Javascript utility functions:
  //getMessage function display a given message through a confirm box. If an extra
  //argument is passed (a button id) then hideButton function is invoked before
  //returning true. 
  public static function javascriptUtilities()
  {
    $emptyCart = JText::_('COM_KETSHOP_MESSAGE_EMPTY_CART');
    $cancelCart = JText::_('COM_KETSHOP_MESSAGE_CANCEL_CART');
    $db = JFactory::getDbo(); //For the Quote function.

    $js = 'function getMessage(msgType) {'."\n";
    $js .= '    var message = "";'."\n";
    $js .= '  switch(msgType) {'."\n";
    $js .= '      case "empty_cart":'."\n";
    $js .= '        message = '.$db->Quote($emptyCart).';'."\n";
    $js .= '        break;'."\n";
    $js .= '      case "cancel_cart":'."\n";
    $js .= '        message = '.$db->Quote($cancelCart).';'."\n";
    $js .= '        break;'."\n";
    $js .= '    }'."\n";
    $js .= '    if(confirm(message))'."\n";
    $js .= '    {'."\n";
    $js .= '      if(arguments[1])'."\n";
    $js .= '        hideButton(arguments[1]);'."\n";
    $js .= '      return true;'."\n";
    $js .= '    }'."\n";
    $js .= '    else'."\n";
    $js .= '      return false;'."\n";
    $js .= '}'."\n\n";
    $js .= ''."\n\n";
    $js .= 'function hideButton(buttonId) {'."\n";
    $js .= '    var elements = document.getElementsByClassName(buttonId);'."\n";
    $js .= '    for(var i = 0; i < elements.length; i++) {'."\n";
    $js .= '      elements[i].style.visibility="hidden";'."\n";
    $js .= '    }'."\n";
    //$js .= '    document.getElementById(buttonId).style.visibility="hidden";'."\n";
    $js .= '    var messagePanel = getMessagePanel("waiting-message",'.$db->Quote(JText::_('COM_KETSHOP_MESSAGE_WAITING_MESSAGE')).');'."\n";
    $js .= '    parentTag = document.getElementById(buttonId+"-message").parentNode;'."\n";
    $js .= '    parentTag.insertBefore(messagePanel, document.getElementById(buttonId+"-message"))'."\n";
    $js .= '    return;'."\n";
    $js .= '}'."\n\n";

    //Place the Javascript function into the html page header.
    $doc = JFactory::getDocument();
    $doc->addScriptDeclaration($js);

    return;
  }
}


