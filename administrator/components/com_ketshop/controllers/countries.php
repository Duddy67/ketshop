<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class KetshopControllerCountries extends JControllerAdmin
{
  /**
   * Proxy for getModel.
   * @since 1.6
  */
  public function getModel($name = 'Country', $prefix = 'KetshopModel', $config = array('ignore_request' => true))
  {
    $model = parent::getModel($name, $prefix, $config);
    return $model;
  }


  /**
   * Calls the updateLanguages method from the model.
   *
   * @return  boolean	True if successful, false otherwise.
   */
  public function updateLanguages()
  {
    // Check for request forgeries
    JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

    $model = $this->getModel('Countries');
    $model->updateLanguages('country');

    $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));

    return true;
  }
}



