<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */


defined('_JEXEC') or die; //No direct access to this file.



class UtilityHelper
{
  /**
   * Applies a given tax rate on a given price then returns the result. 
   *
   * @param   decimal  $price           The price to which to apply the tax rate.
   * @param   decimal  $taxRate		The tax rate to apply.
   *
   * @return  decimal			The price with tax.
   */
  public static function getPriceWithTax($price, $taxRate)
  {
    // Division by zero is not allowed.
    if($price == 0 || $taxRate == 0) {
      return $price;
    }

    $taxValue = $price * ($taxRate / 100);
    $priceWithTaxes = $price + $taxValue;

    return $priceWithTaxes;
  }


  /**
   * Extract a given tax rate from a given price then returns the result.
   *
   * In order to achieve this here's the used formula:
   * Example 1: For a given tax rate of 19.6 % and a price with taxes of 15 €
   *            Price without tax: 15/1.196 = 12.54 €
   *
   * Example 2: For a given tax rate of 5.5 % and a price with taxes of 15 €
   *            Price without tax: 15/1.055 = 14.22 €
   *
   * @param   decimal  $price           The price to which to extract the tax rate.
   * @param   decimal  $taxRate		The tax rate to extract.
   *
   * @return  decimal			The price without tax.
   */
  public static function getPriceWithoutTax($price, $taxRate)
  {
    // Division by zero is not allowed.
    if($price == 0 || $taxRate == 0) {
      return $price;
    }

    // N.B: With strpos function the string positions start at 0, and not 1.
    $dotPosition = strpos($taxRate, '.');

    // The given tax value has no dot.
    if($dotPosition === false) {
      // Computes the dot position from the number of figures.
      $dotPosition = strlen($taxRate) - 1;
    }

    $dotlessNb = preg_replace('#\.#', '', $taxRate);

    if($dotPosition == 1) {
      $divisor = '1.0'.$dotlessNb;
    }
    // 0
    else { 
      $divisor = '1.'.$dotlessNb;
    }

    return $price / $divisor;
  }


  /**
   * Computes a tax rate based on the difference between the price with tax and the price
   * without tax. 
   *
   * @param   decimal  $priceExclTax		The price without tax.
   * @param   decimal  $priceInclTax		The price with tax.
   *
   * @return  decimal				The tax rate.
   */
  public static function getTaxRateFromPrices($priceExclTax, $priceInclTax)
  {
    return (($priceInclTax * 100) / $priceExclTax) - 100;
  }


  public static function roundNumber($float, $roundingRule = 'down', $digitPrecision = 2)
  {
    //In case variable passed in argument is undefined.
    if($float == '') {
      return 0;
    }

    switch($roundingRule) {
      case 'up':
	return round($float, $digitPrecision, PHP_ROUND_HALF_UP);

      case 'down':
	return round($float, $digitPrecision, PHP_ROUND_HALF_DOWN);

     default: //Unknown value.
	return $float;
    }
  }


  /**
   * Sanitized a given number and applies a float number pattern defined by the given digits.
   *
   * @param   mixed    $number          The number to format.
   * @param   integer  $digits          The number of digits to format the number with.
   *
   * @return  string                    The float formated number. 
   */
  public static function floatFormat($number, $digits = 2)
  {
    // Removes possible spaces.
    $number = preg_replace('#\s#', '', $number);

    // Checks for empty value.
    if($number == '') {
      $number = 0;
    }

    // Replaces possible comma by a point.
    $number = preg_replace('#,#', '.', $number);

    // Retrieves the part of the number matching the global pattern, (ie: possible dot, possible digits etc..).
    if(preg_match('#^-?[0-9]+\.?[0-9]*#', $number, $matches) === 1) {
      $number = $matches[0]; 
    }
    else {
      $number = 0;
    }

    // Ensures the digit value is correct.
    if($digits < 1 || !is_int($digits)) {
      $digits = 2;
    }

    if(preg_match('#^-?[0-9]+\.[0-9]{'.$digits.'}#', $number, $matches)) {
      // Returns the part of the number matching the final pattern.
      return $matches[0]; 
    }
    // In case the float number is truncated (eg: 18.5 or 18).
    else {
      $dot = $padding = '';
      // Dot is added if there's only the left part of the float. 
      if(!preg_match('#\.#', $number)) {
	$missingDigits = $digits;
	$dot = '.';
      }

      // Computes how many digits are missing.
      if(preg_match('#^-?[0-9]+\.([0-9]*)#', $number, $matches)) {
	$missingDigits =  $digits - strlen($matches[1]);
      }

      // Replaces missing digits with zeros. 
      for($i = 0; $i < $missingDigits; $i++) {
	$padding .= '0';
      }

      $formatedNumber = $number.$dot.$padding;
    }

    return $formatedNumber;
  }


  /**
   * Checks if all the plugins currently used by KetShop are still installed and/or enabled.
   *
   * @param   string  $type	The plugin type to check.
   *
   * @return  Array		The names of the missing KetShop plugins (if any). 
   */
  public static function getMissingPlugins($type)
  {
    // Checks for types.
    if($type != 'payment' && $type != 'shipment') {
      return array();
    }

    $table = 'payment_mode';
    $folder = 'ketshoppayment';

    if($type == 'shipment') {
      $table = 'shipping';
      $folder = 'ketshopshipment';
    }

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    // Gets all of the plugins which are currently used by the given item type.
    $query->select('plugin_element')
	  ->from('#__ketshop_'.$table);

    if($type == 'payment') {
      // Since offline payment plugin can have several payment modes, we must remove
      // duplicate values before running array test.
      $query->group('plugin_element');
    }

    $db->setQuery($query);
    $usedPlugins = $db->loadColumn();

    // Get all the enabled plugins.
    $query->clear();
    $query->select('element')
	  ->from('#__extensions')
	  ->where('type="plugin" AND folder='.$db->Quote($folder).' AND enabled=1');
    $db->setQuery($query);
    $plugins = $db->loadColumn();

    // Runs the array test.
    $missingPlugins = array();

    foreach($usedPlugins as $usedPlugin) {
      // Stores the missing plugins.
      if(!in_array($usedPlugin, $plugins)) {
	$missingPlugins[] = $usedPlugin;
      }
    }

    return $missingPlugins;
  }


  /**
   * Gathers the currency, country and global data set for all the shop.
   *
   * @param   integer  $userId	The id of the current user.
   *
   * @return  object		The shop settings.
   */
  public static function getShopSettings($userId = null)
  {
    $config = JComponentHelper::getParams('com_ketshop');
    $langTag = JFactory::getLanguage()->getTag();
    $suffix = preg_replace('#\-#', '_', $langTag);

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    // Gets the currency and country data according to their global setting.
    $query->select('cu.alpha AS currency_code,cu.symbol AS currency_symbol,co.name_'.$suffix.' AS country_name,'.
		   'co.alpha_2 AS country_alpha_2,co.alpha_3 AS country_alpha_3,'.
		   'co.lang_var AS country_lang_var') 
	  ->from('#__ketshop_currency AS cu')
	  ->join('INNER', '#__ketshop_country AS co ON co.alpha_2='.$db->quote($config->get('country_code')))
	  ->where('cu.alpha='.$db->quote($config->get('currency_code')));
    $db->setQuery($query);
    $settings = $db->loadObject();

    $attribs = array('shop_name','vendor_name','site_url',
		     'redirect_url_1','rounding_rule','digits_precision','currency_display',
		     'excl_tax_groups', 'default_tax_method', 'gts_article_ids');

    // Adds the global shop settings.
    foreach($attribs as $attrib) {
      $settings->$attrib = $config->get($attrib);
    }

    $settings->tax_method = $settings->default_tax_method;

    if($settings->excl_tax_groups !== null && $userId !== null && $settings->tax_method == 'incl_tax') {
      // Gets user group ids to which the user belongs to. 
      $userGroups = JAccess::getGroupsByUser($userId);
      // Checks if the current user is part of a exclude tax group.
      foreach($userGroups as $group) {
	if(in_array($group, $settings->excl_tax_groups)) {
	  $settings->tax_method = 'excl_tax';
	  break;
	}
      }
    }

    // Creates a currency attribute which contains currency in the correct display.
    $settings->currency = $settings->currency_code;
    if($settings->currency_display == 'symbol') {
      $settings->currency = $settings->currency_symbol;
    }

    return $settings;
  }


  /**
   * Returns the root url without path (if any). 
   *
   * @return  string	The root url.
   */
  public static function getRootUrl()
  {
    // Gets the root url.
    $rootUrl = JUri::root();
    // Gets the path only (if any).
    $length = strlen(JUri::root(true));

    if($length) {
      // Turns the length in negative value.
      $length = $length - ($length * 2);
      $rootUrl = substr(JUri::root(), 0, $length);
    }

    // Removes possible slash from the end of the url.
    $rootUrl = preg_replace('#(\/)$#', '', $rootUrl);

    return $rootUrl;
  }


  /**
   * Returns whether a given comparison between 2 decimal (or integer) values is true or not. 
   *
   * @param   decimal $leftValue	The left value to compare (can aso be an integer).
   * @param   string  $operator		The operator to use to compare values. 
   * @param   decimal $rightValue	The right value to compare (can aso be an integer).
   *
   * @return  boolean          		The comparison result.
   */
  public static function isTrue($leftValue, $operator, $rightValue)
  {
    switch($operator) {
      case 'lt': // Lower Than
	return ($leftValue < $rightValue) ? true : false;

      case 'gt': // Greater Than
	return ($leftValue > $rightValue) ? true : false;

      case 'ltoet': // Lower Than Or Equal To
	return ($leftValue <= $rightValue) ? true : false;

      case 'gtoet': // Greater Than Or Equal To
	return ($leftValue >= $rightValue) ? true : false;

      case 'et': // Equal To
	return ($leftValue == $rightValue) ? true : false;
    }
  }


  /**
   * Adapts the GROUP_CONCAT MySQL function in case another database type is used (ie: PostgreSQL,
   * SQLServer, Microsoft Server).
   *
   * @param   string  $expression	The expression to evaluate.
   * @param   string  $order		The ORDER BY clause (default: none).
   * @param   string  $separator	The separator to use (default: comma).
   * @param   boolean $distinct		If set to true, appends the DISTINCT clause (MySQL only).
   *
   * @return  string                    The passed arguments set with the corresponding function.
   */
  public static function groupConcatAdapter($expression, $order = '', $separator = ',', $distinct = false)
  {
    $dbType = JFactory::getApplication()->get('dbtype');

    // PostgreSQL >= 9.0
    if($dbType == 'postgresql') {
      return ' STRING_AGG('.$expression.', \''.$separator.'\' '.$order.') ';
    }
    // SQLServer, Microsoft Server
    elseif($dbType == 'sqlsrv' || $dbType == 'mssql') {
      return ' STRING_AGG('.$expression.', \',\') '.((!empty($order)) ? 'WITHIN GROUP('.$order.') ' : '');
    }
    // MySQL, MariaDB
    else { // mysql, mysqli, pdomysql
      return ' GROUP_CONCAT('.(($distinct) ? 'DISTINCT ' : '').$expression.' '.$order.' SEPARATOR \''.$separator.'\') ';
    }
  }
  

  /**
   * Inserts a given address along with its type, item type and item id. 
   * N.B: If a country code is given, the corresponding continent code is automaticaly
   *      added to the query.
   *
   * @param   array   $data		An associative array of address attributes (ie: [name] => value). 
   * @param   string  $type		The address type (eg: billing, shipping...).
   * @param   string  $itemType 	The type of the item linked to the address (eg: customer, vendor...).
   * @param   integer $itemId		The id of the item linked to the address.
   *
   * @return  void
   */
  public static function insertAddress($data, $type, $itemType, $itemId)
  {
    // Gets the current date and time (UTC).
    $now = JFactory::getDate()->toSql();
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $columns = array('type', 'item_type', 'item_id', 'created');
    $values = $db->Quote($type).','.$db->Quote($itemType).','.$itemId.','.$db->Quote($now).',';
    $countryCode = '';

    foreach($data as $key => $value) {
      // Retrieves the attribute name without its type.
      if(preg_match('#^([a-z0-9_]+)_'.$type.'$#', $key, $matches)) {
	$columns[] = $matches[1];
	$value = trim($value);
	$values .= $db->Quote($value).',';

	if($matches[1] == 'country_code' && !empty($value)) {
	  $countryCode = $value;
	}
      }
    }

    // Removes comma from the end of the string.
    $values = substr($values, 0, -1);

    if(!empty($countryCode)) {
      // Gets and adds the corresponding continent code.
      $query->select('continent_code')
	    ->from('#__ketshop_country')
	    ->where('alpha_2='.$db->Quote($countryCode));
      $db->setQuery($query);
      $values .= ','.$db->Quote($db->loadResult());

      $columns[] = 'continent_code';
    }

    $query->clear();
    $query->insert('#__ketshop_address')
	  ->columns($columns)
	  ->values($values);
    $db->setQuery($query);
    $db->execute();
  }


  /**
   * Updates a given address along with its type, item type and item id. 
   * N.B: If a country code is given, the corresponding continent code is automaticaly updated.
   *
   * @param   array   $data		An associative array of address attributes (ie: [name] => value). 
   * @param   string  $type		The address type (eg: billing, shipping...).
   * @param   string  $itemType 	The type of the item linked to the address (eg: customer, vendor...).
   * @param   integer $itemId		The id of the item linked to the address.
   *
   * @return  void
   */
  public static function updateAddress($data, $type, $itemType, $itemId)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $fields = array('type='.$db->Quote($type), 'item_type='.$db->Quote($itemType), 'item_id='.(int)$itemId);

    foreach($data as $key => $value) {
      // Retrieves the attribute name without its type.
      if(preg_match('#^([a-z0-9_]+)_'.$type.'$#', $key, $matches)) {
	$value = trim($value);
	$fields[] = $matches[1].'='.$db->Quote($value);

	if($matches[1] == 'country_code' && !empty($value)) {
	  $countryCode = $value;
	}
      }
    }

    if(!empty($countryCode)) {
      // Gets and adds the corresponding continent code to the updating.
      $query->select('continent_code')
	    ->from('#__ketshop_country')
	    ->where('alpha_2='.$db->Quote($countryCode));
      $db->setQuery($query);
      $fields[] = 'continent_code='.$db->Quote($db->loadResult());
    }

    $query->clear();
    $query->update('#__ketshop_address')
	  ->set($fields)
	  ->where('type='.$db->Quote($type))
	  ->where('item_type='.$db->Quote($itemType))
	  ->where('item_id='.(int)$itemId)
	  // Updates only the newest row to preserve history (if any).
	  ->order('created DESC')
	  ->setLimit(1);
    $db->setQuery($query);
    $db->execute();
  }


  /**
   * Returns an address according to the given type, item type and item id.
   *
   * @param   string  $type		The address type (eg: billing, shipping...).
   * @param   string  $itemType 	The type of the item linked to the address (eg: customer, vendor...).
   * @param   integer $itemId		The id of the item linked to the address.
   *
   * @return  object			An address object.
   */
  public static function getAddress($type, $itemType, $itemId)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $company = '';
    if($type == 'shipping') {
      // Appends the company field to the query.
      $company = ',company';
    }

    $query->select('street, additional, city, postcode, region_code, country_code, continent_code, phone'.$company)
	  ->from('#__ketshop_address')
	  ->where('type='.$db->Quote($type))
	  ->where('item_type='.$db->Quote($itemType))
	  ->where('item_id='.(int)$itemId)
	  ->order('created DESC')
	  ->setLimit(1);
    $db->setQuery($query);

    return $db->loadObject();
  }


  public static function formatPriceRule($operation, $value, $currencyId = 0)
  {
    $value = UtilityHelper::floatFormat($value);
    $currency = UtilityHelper::getCurrency($currencyId);

    switch($operation) {
      case 'add_percentage': 
	$label = '+ '.$value.'%';
	break;

      case 'subtract_percentage': 
	$label = '- '.$value.'%';
	break;

      case 'add_value': 
	$label = '+ '.$value.' '.$currency;
	break;

      case 'subtract_value': 
	$label = '- '.$value.' '.$currency;
	break;

      case 'fixed_price': 
	$label = $value.' '.$currency;
	break;
    }

    return $label;
  }


  //Return the reference language parameter considered as the shop default
  //language.
  public static function getLanguage($tag = false) 
  {
    //Get the reference language set in config.
    $params = JComponentHelper::getParams('com_ketshop');
    $langTag = $params->get('reference_language');

    //Get the xml file path then parse it to get the language name.
    $file = JPATH_BASE.'/language/'.$langTag.'/'.$langTag.'.xml';
    $info = JApplicationHelper::parseXMLLangMetaFile($file);
    $langName = $info['name'];

    if($tag) {
      return $langTag;
    }

    //In case the xml parse has failed we display the language code.
    if(empty($langName)) {
      return $langTag;
    }
    else {
      return $langName;
    }
  }


  //Return the requested currency or the currency set by default for 
  //the shop if the id argument is not defined.
  public static function getCurrency($currencyCode = 0) 
  {
    $config = JComponentHelper::getParams('com_ketshop');

    if(!$currencyCode) { 
      // Fetches the required currency.
      $currencyCode = $config->get('currency_code');
    }

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('alpha,symbol')
	  ->from('#__ketshop_currency')
	  ->where('alpha='.$db->quote($currencyCode));
    $db->setQuery($query);
    $currency = $db->loadObject();

    // Returns currency in the correct display.
    if($config->get('currency_display') == 'symbol') {
      return $currency->symbol;
    }

    return $currency->alpha;
  }


  //Compare 2 strings by taking account the encoding.
  public static function mbStrcasecmp($str1, $str2, $encoding = null)
  {
    if(is_null($encoding)) {
      $encoding = mb_internal_encoding();
    }

    //Take advantage of a multibyte string function to use encoding.
    return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
  }


  //Create and return a INSERT or UPDATE query according to the given arguments.
  //The choice of the query to use allows to manage an address history.
  public static function getAddressQuery($data, $type, $itemType, $itemId)
  {
    //A suffix might be used.
    $suffix = '';

    //A suffix is needed when we deal with a customer address.
    if($itemType == 'customer') {
      //Create the proper suffix according to the type.
      $suffix = '_sh';
      if($type == 'billing') {
	$suffix = '_bi';
      }
    }

    //Remove possible spaces.
    foreach($data as $key => $value) {
      //Replace all contiguous space characters with one space character.
      $value = preg_replace('#\s{2,}#', ' ', $value);
      //Remove space characters before and after the string.
      $data[$key] = trim($value);
    }

    //Get the last address set by the customer. 
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('id, street, city, postcode, region_code, country_code')
	  ->from('#__ketshop_address')
	  ->where('type='.$db->Quote($type).' AND item_type='.$db->Quote($itemType).' AND item_id='.$itemId)
	  ->order('created DESC')
	  ->setLimit(1);
    $db->setQuery($query);
    $address = $db->loadAssoc();

    //Get the database encoding.
    //TODO: Figure out how to set this query with the JDatabaseQuery class.
    $db->setQuery('SHOW VARIABLES LIKE "character_set_database"');
    $result = $db->loadObject();
    $encoding = $result->Value;

    //Get the continent code for the shipping address according to the chosen country.
    if(!empty($data['country_code'.$suffix])) {
      $query->clear();
      $query->select('continent_code')
	    ->from('#__ketshop_country')
	    ->where('alpha_2='.$db->Quote($data['country_code'.$suffix]));
      $db->setQuery($query);
      $continentCode = $db->loadResult();
    }

    //Run the test.

    //One or more address rows have been previouly stored.
    if(!is_null($address)) {
      //If street, city, postcode region and country fields are equal to
      //their equivalent in database, we assume that the customer has still the same address.
      //So we just update data. 
      if(!UtilityHelper::mbStrcasecmp($address['street'], $data['street'.$suffix], $encoding) &&
	 !UtilityHelper::mbStrcasecmp($address['city'], $data['city'.$suffix], $encoding) &&
	 !UtilityHelper::mbStrcasecmp($address['postcode'], $data['postcode'.$suffix], $encoding) && 
	 $address['region_code'] === $data['region_code'.$suffix] && 
	 $address['country_code'] === $data['country_code'.$suffix])
      {
	$fields = array('street='.$db->Quote($data['street'.$suffix]),
			'city='.$db->Quote($data['city'.$suffix]),
			'postcode='.$db->Quote($data['postcode'.$suffix]),
			'region_code='.$db->Quote($data['region_code'.$suffix]),
			'country_code='.$db->Quote($data['country_code'.$suffix]),
			'continent_code='.$db->Quote($continentCode),
			'note='.$db->Quote($data['note'.$suffix]),
			'phone='.$db->Quote($data['phone'.$suffix]));

	$query->clear();
	$query->update('#__ketshop_address')
	      ->set($fields)
	      ->where('id='.(int)$address['id']);

	return $query;
      }
    }

    //In all other cases a new address row must be inserted.

    //Gets the current date and time (UTC).
    //A date stamp allows to keep an address history.
    $now = JFactory::getDate()->toSql();

    $columns = array('item_id','street','city','region_code','postcode',
		     'phone','country_code','continent_code','type',
		     'item_type','created','note');
    $query->clear();
    $query->insert('#__ketshop_address')
	  ->columns($columns)
	  ->values($itemId.','.$db->Quote($data['street'.$suffix]).','.$db->Quote($data['city'.$suffix]).','.
		   $db->Quote($data['region_code'.$suffix]).','.$db->Quote($data['postcode'.$suffix]).','.
		   $db->Quote($data['phone'.$suffix]).','.$db->Quote($data['country_code'.$suffix]).','.$db->Quote($continentCode).','.
		   $db->Quote($type).','.$db->Quote($itemType).','.$db->Quote($now).','.$db->Quote($data['note'.$suffix]));

    return $query;
  }
}

