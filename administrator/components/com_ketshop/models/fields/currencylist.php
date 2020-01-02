<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');


/*
 * Script which build the select html tag containing the currency names and ids.
 *
 */
class JFormFieldCurrencyList extends JFormFieldList
{
  protected $type = 'currencylist';


  protected function getOptions()
  {
    $options = array();
      
    // Gets the currency names.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('alpha,name, lang_var')
	  ->from('#__ketshop_currency')
	  ->where('published=1')
	  ->order('alpha');
    $db->setQuery($query);
    $currencies = $db->loadObjectList();

    // Gets all the view level of the user.
    $user = JFactory::getUser();

    // Builds the first option.
    $options[] = JHtml::_('select.option', '', JText::_('COM_KETSHOP_OPTION_SELECT'));

    // Builds the select options.
    foreach($currencies as $currency) {
      // If language variable is not defined, the current name is displayed. 
      $options[] = JHtml::_('select.option', $currency->alpha,
			    (empty($currency->lang_var)) ? JText::_($currency->name) : JText::_($currency->lang_var));
    }

    // Merges any additional options in the XML definition.
    $options = array_merge(parent::getOptions(), $options);

    return $options;
  }
}


