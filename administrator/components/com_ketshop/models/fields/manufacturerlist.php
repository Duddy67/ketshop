<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');


/*
 * Script which build the select html tag containing the manufacturer names previously defined.
 *
 */
class JFormFieldManufacturerList extends JFormFieldList
{
  protected $type = 'manufacturerlist';


  protected function getOptions()
  {
    $options = array();
      
    // Gets the manufacturer rates.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('id,name')
	  ->from('#__ketshop_manufacturer')
	  ->where('published=1')
	  ->order('name');
    $db->setQuery($query);
    $manufacturers = $db->loadObjectList();

    // Gets all the view level of the user.
    $user = JFactory::getUser();

    // Builds the first option.
    $options[] = JHtml::_('select.option', '', JText::_('COM_KETSHOP_OPTION_SELECT'));

    // Builds the select options.
    foreach($manufacturers as $manufacturer) {
      $options[] = JHtml::_('select.option', $manufacturer->id, $manufacturer->name);
    }

    // Merges any additional options in the XML definition.
    $options = array_merge(parent::getOptions(), $options);

    return $options;
  }
}

