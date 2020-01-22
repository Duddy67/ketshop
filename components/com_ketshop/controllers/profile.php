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
class KetshopControllerProfile extends JControllerForm
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
    $app = JFactory::getApplication();
    $user = JFactory::getUser();
    $loginUserId = (int)$user->get('id');

    // Get the previous user id (if any) and the current user id.
    $previousId = (int)$app->getUserState('com_ketshop.edit.customer.id');
    $userId = $this->input->getInt('c_id');

    // Check if the user is trying to edit another users profile.
    if($userId != $loginUserId) {
      $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
      $app->setHeader('status', 403, true);

      return false;
    }

    $cookieLogin = $user->get('cookieLogin');

    // Check if the user logged in with a cookie
    if(!empty($cookieLogin)) {
      // If so, the user must login to edit the password and other data.
      $app->enqueueMessage(JText::_('JGLOBAL_REMEMBER_MUST_LOGIN'), 'message');
      $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=connection', false));

      return false;
    }

    // Set the user id for the user to edit in the session.
    $app->setUserState('com_ketshop.edit.customer.id', $userId);

    // Get the model.
    $model = $this->getModel('Profile', 'KetshopModel');

    // Check out the user.
    if($userId) {
      $model->checkout($userId);
    }

    // Check in the previous user.
    if($previousId) {
      //$model->checkin($previousId);
    }

    // Redirect to the edit screen.
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=profile&layout=edit', false));

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

