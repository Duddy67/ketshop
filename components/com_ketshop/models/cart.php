<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 



class KetshopModelCart extends JModelItem
{
  /**
   * Method to auto-populate the model state.
   *
   * N.B: Calling getState in this method will result in recursion.
   *
   * @since   1.6
   *
   * @return void
   */
  protected function populateState()
  {
    $app = JFactory::getApplication('site');

    // Load the global parameters of the component.
    $params = $app->getParams();
    $this->setState('params', $params);

    $this->setState('filter.language', JLanguageMultilang::isEnabled());
  }
}

