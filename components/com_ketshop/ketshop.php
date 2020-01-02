<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2017 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access.
defined('_JEXEC') or die('Restricted access'); 

// Registers the component helper files. They will be loaded automatically later as soon
// as an helper class is instantiate.
JLoader::register('KetshopHelperRoute', JPATH_SITE.'/components/com_ketshop/helpers/route.php');
JLoader::register('KetshopHelperQuery', JPATH_SITE.'/components/com_ketshop/helpers/query.php');
JLoader::register('ShopHelper', JPATH_SITE.'/components/com_ketshop/helpers/shop.php');
JLoader::register('UtilityHelper', JPATH_ADMINISTRATOR.'/components/com_ketshop/helpers/utility.php');
JLoader::register('JavascriptHelper', JPATH_ADMINISTRATOR.'/components/com_ketshop/helpers/javascript.php');


$controller = JControllerLegacy::getInstance('Ketshop');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();


