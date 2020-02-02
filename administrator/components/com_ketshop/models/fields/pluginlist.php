<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');


class JFormFieldPluginList extends JFormFieldList
{
  protected $type = 'pluginlist';

  protected function getOptions()
  {
    // Gets the plugin type according to the calling form.
    $pluginType = 'payment';

    if($this->form->getName() === 'com_ketshop.shipping' || $this->form->getName() === 'com_ketshop.shippings.filter') {
      $pluginType = 'shipment';
    }

    $options = array();
      
    //Get the payment plugins.
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('name, element, folder')
	  ->from('#__extensions')
	  ->where('type="plugin" AND folder="ketshop'.$pluginType.'" AND enabled=1')
	  ->order('ordering');
    $db->setQuery($query);
    $plugins = $db->loadObjectList();

    // Get all the view level of the user.
    $user = JFactory::getUser();
    UtilityHelper::translatePlugin($plugins);

    // Build the first option.
    $options[] = JHtml::_('select.option', '', JText::_('COM_KETSHOP_OPTION_SELECT_PLUGIN'));

    // Build the select options.
    foreach($plugins as $plugin) {
      $options[] = JHtml::_('select.option', $plugin->element, $plugin->name);
    }

    // Merge any additional options in the XML definition.
    $options = array_merge(parent::getOptions(), $options);

    return $options;
  }
}

