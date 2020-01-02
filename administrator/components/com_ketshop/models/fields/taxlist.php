<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');


/*
 * Script which build the select html tag containing the tax rates previously defined.
 *
 */
class JFormFieldTaxList extends JFormFieldList
{
  protected $type = 'taxlist';


  protected function getOptions()
  {
    $options = array();
      
    // Gets the tax rates.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('id, rate, name')
	  ->from('#__ketshop_tax')
	  ->where('published=1')
	  ->order('ordering');
    $db->setQuery($query);
    $taxes = $db->loadObjectList();

    // Gets all the view level of the user.
    $user = JFactory::getUser();

    // Builds the first option.
    $options[] = JHtml::_('select.option', '', JText::_('COM_KETSHOP_OPTION_SELECT'));

    // Builds the select options.
    foreach($taxes as $tax) {
      $options[] = JHtml::_('select.option', $tax->id, JText::_($tax->name.' - '.$tax->rate.' %'));
    }

    // Merges any additional options in the XML definition.
    $options = array_merge(parent::getOptions(), $options);

    return $options;
  }
}

