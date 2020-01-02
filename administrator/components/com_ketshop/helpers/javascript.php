<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die; 


class JavascriptHelper
{
  /**
   * Loads the field labels in order to use them with the dynamical items.
   *
   * @return  void
   */
  public static function loadFieldLabels() 
  {
    // Gets the language tag as well as the path to the language files. 
    $langTag = JFactory::getLanguage()->getTag();
    $path = JPATH_ADMINISTRATOR.'/components/com_ketshop/language';

    // Gets the ini language file matching the language tag.
    $langFile = parse_ini_file($path.'/'.$langTag.'/'.$langTag.'.com_ketshop.ini', true);
    // Loads language variables relating to Javascript.
    foreach($langFile['javascript_texts'] as $langVar => $name) {
      JText::script($langVar); 
    }
  }


  /**
   * Build and load Javascript functions which return different kind of data, generaly as a JSON array.
   *
   * @param   array	$names	An array containing the names of the functions to load.
   * @param   array	$data	Optional. A set of data.
   *
   * @return  void
   */
  public static function loadFunctions($names, $data = null)
  {
    $js = array();
    // Creates a name space in order to put functions into it.
    $js = 'var ketshop = { '."\n";

    // Includes the required functions.

    // Returns region names and codes used to build option tags.
    if(in_array('region', $names)) {
      $regions = JavascriptHelper::getRegions();
      $js .= 'getRegions: function() {'."\n";
      $js .= ' return '.$regions.';'."\n";
      $js .= '},'."\n";
    }

    // Returns country names and codes used to build option tags.
    if(in_array('country', $names)) {
      $countries = JavascriptHelper::getCountries();
      $js .= 'getCountries: function() {'."\n";
      $js .= ' return '.$countries.';'."\n";
      $js .= '},'."\n";
    }

    // Returns continent names and codes used to build option tags.
    if(in_array('continent', $names)) {
      $continents = JavascriptHelper::getContinents();
      $js .= 'getContinents: function() {'."\n";
      $js .= ' return '.$continents.';'."\n";
      $js .= '},'."\n";
    }

    // Returns the attributes used with the product.
    if(in_array('product_attributes', $names)) {
      $productAttributes = JavascriptHelper::getProductAttributes();
      $js .= 'productAttributes: '.$productAttributes.','."\n";
    }

    if(in_array('attribute_options', $names)) {
      $attributeOptions = JavascriptHelper::getAttributeOptions();
      $js .= 'attributeOptions: '.$attributeOptions.','."\n";
    }

    // Returns the id of the current user.
    if(in_array('user', $names)) {
      $user = JFactory::getUser();
      $js .= 'getUserId: function() {'."\n";
      $js .= ' return '.$user->id.';'."\n";
      $js .= '},'."\n";
    }

    // Functions used to access an item directly from an other item.
    if(in_array('shortcut', $names)) {
      $js .= 'shortcut: function(itemId, task) {'."\n";
	       //Set the id of the item to edit.
      $js .= ' var shortcutId = document.getElementById("jform_shortcut_id");'."\n";
	       //This id will be retrieved in the overrided functions of the controller
	       //(ie: checkin and cancel functions).
      $js .= ' shortcutId.value = itemId;'."\n";
      $js .= ' Joomla.submitbutton(task);'."\n";
      $js .= '},'."\n";
    }

    // Checks for getter functions.
    $getters = preg_grep('#^getter_#', $names);

    if(!empty($getters)) {

      foreach($getters as $key => $getter) {
	// Builds a getter Javascript function which return the given data.
	$chunks = explode('_', $getter);
	$functionName = 'get';

	foreach($chunks as $chunk) {
	  if($chunk != 'getter') {
	    $functionName .= ucfirst($chunk);
	  }
	}

	$js .= $functionName.': function() {'."\n";
	$js .= ' return '.$data[$key].';'."\n";
	$js .= '},'."\n";
      }
    }

    // Removes coma from the end of the string, (-2 due to the carriage return "\n").
    $js = substr($js, 0, -2); 

    $js .= '};'."\n\n";

    // Places the Javascript code into the html page header.
    $doc = JFactory::getDocument();
    $doc->addScriptDeclaration($js);
  }


  /**
   * Returns the region codes and names.
   *
   * @return array	A JSON array.  
   */
  public static function getRegions()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    // Gets all the regions from the region list.
    $query->select('r.country_code, r.code, r.lang_var')
	  ->from('#__ketshop_region AS r')
	  // Gets only regions which country they're linked with is published (to minimized
	  // the number of regions to display).
	  ->join('LEFT', '#__ketshop_country AS c ON r.country_code=c.alpha_2')
	  ->where('c.published=1');
    $db->setQuery($query);
    $results = $db->loadAssocList();

    // Builds the regions array.
    $regions = array();

    // Sets text value in the proper language.
    foreach($results as $result) {
      // Adds the country code to the region name to get an easier search.
      $regions[] = array('code' => $result['code'], 'text' => $result['country_code'].' - '.JText::_($result['lang_var']));
    }

    return json_encode($regions);
  }


  /**
   * Returns the country ids and names.
   *
   * @return array	A JSON array.  
   */
  public static function getCountries()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    // Gets all the countries from the country list.
    $query->select('alpha_2 AS code, lang_var AS text')
	  ->from('#__ketshop_country')
	  ->where('published=1');
    $db->setQuery($query);
    $countries = $db->loadAssocList();

    // Sets text value in the proper language.
    foreach($countries as $key => $country) {
      $countries[$key]['text'] = JText::_($country['text']);
    }

    return json_encode($countries);
  }


  /**
   * Returns the attributes of the current product
   *
   * @return array	A JSON array.  
   */
  public static function getProductAttributes()
  {
    $productId = JFactory::getApplication()->input->get('id', 0, 'uint');

    // Invokes the model's function.
    $model = JModelLegacy::getInstance('Product', 'KetshopModel');
    $attributes = $model->getProductAttributes($productId);

    return json_encode($attributes);
  }


  /**
   * Returns all the attribute options.
   *
   * @return array	A JSON array.  
   */
  public static function getAttributeOptions()
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    // Fetches all the attribute options.
    $query->select('ao.*, a.multiselect')
	  ->from('#__ketshop_attrib_option AS ao')
	  ->join('INNER', '#__ketshop_attribute AS a ON a.id = ao.attrib_id')
          ->order('attrib_id, ordering');
    $db->setQuery($query);
    $options = $db->loadAssocList();

    $attributeOptions = array();

    // Reshapes the result structure.
    foreach($options as $option) {
      // Gets the attribute id to which the option belongs to.
      $attribId = $option['attrib_id'];

      if(!array_key_exists($attribId, $attributeOptions)) {
	// Uses the attribute id as array key.
	$attributeOptions[$attribId]['options'] = array();
	$attributeOptions[$attribId]['multiselect'] = $option['multiselect'];
      }

      // Deletes the attribute id and multiselect fields as they're now useless. 
      unset($option['attrib_id']);
      unset($option['multiselect']);

      // Stores the option in the corresponding attribute.
      $attributeOptions[$attribId]['options'][] = $option;
    }

    return json_encode($attributeOptions);
  }


  /**
   * Returns the continent ids and names.
   *
   * @return array	A JSON array.  
   */
  public static function getContinents()
  {
    //Since continents are few in number we dont need to spend a db table for them. 
    //We simply store their data into an array.
    $continents = array();
    $continents[] = array('code'=>'AF','text'=>'COM_KETSHOP_LANG_CONTINENT_AF');
    $continents[] = array('code'=>'AN','text'=>'COM_KETSHOP_LANG_CONTINENT_AN');
    $continents[] = array('code'=>'AS','text'=>'COM_KETSHOP_LANG_CONTINENT_AS');
    $continents[] = array('code'=>'EU','text'=>'COM_KETSHOP_LANG_CONTINENT_EU');
    $continents[] = array('code'=>'OC','text'=>'COM_KETSHOP_LANG_CONTINENT_OC');
    $continents[] = array('code'=>'NA','text'=>'COM_KETSHOP_LANG_CONTINENT_NA');
    $continents[] = array('code'=>'SA','text'=>'COM_KETSHOP_LANG_CONTINENT_SA');

    // Sets text value in the proper language.
    foreach($continents as &$continent) {
      $continent['text'] = JText::_($continent['text']);
    }

    return json_encode($continents);
  }
}

