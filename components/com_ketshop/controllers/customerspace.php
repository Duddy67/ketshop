<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');


/**
 * @package     KetShop
 * @subpackage  com_ketshop
 */
class KetshopControllerCustomerspace extends JControllerForm
{
  /**
   * Method to check out a user for editing and redirect to the edit form.
   *
   * @return  boolean
   *
   * @since   1.6
   */
  public function edit()
  {

    // Redirect to the edit screen.
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=customerspace&layout=edit', false));

    return true;
  }


  /**
   * Method to save a user's profile data.
   *
   * @return  void
   *
   * @since   1.6
   */
  public function save()
  {
  }
}

