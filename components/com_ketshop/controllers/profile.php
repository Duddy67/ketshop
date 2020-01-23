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

    // Get the current user id.
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
    $app->setUserState('com_ketshop.edit.profile.id', $userId);

    // Get the model.
    $model = $this->getModel('Profile', 'KetshopModel');

    // Check out the user.
    if($userId) {
      $model->checkout($userId);
    }

    // Redirect to the edit screen.
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=profile&layout=edit', false));

    return true;
  }


  /**
   * Method to cancel an edit.
   *
   * @param   string  $key  The name of the primary key of the URL variable.
   *
   * @return  boolean  True if access level checks pass, false otherwise.
   *
   * @since   1.6
   */
  public function cancel($key = null)
  {
    // Get the model.
    $model = $this->getModel('Profile', 'KetshopModel');

    // Check in the profile.
    $model->checkin((int)JFactory::getUser()->get('id'));

    // Redirect to the profile screen.
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=profile', false));

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
    // Check for request forgeries.
    $this->checkToken();

    $app = JFactory::getApplication();
    $model = $this->getModel('Profile', 'KetshopModel');
    $user = JFactory::getUser();
    $userId = (int)$user->get('id');

    // Get the user data.
    $requestData = $app->input->post->get('jform', array(), 'array');

    // Force the ID to this user.
    $requestData['id'] = $userId;

    // Validate the posted data.
    $form = $model->getForm();

    if(!$form) {
      JError::raiseError(500, $model->getError());
      return false;
    }

    // Validate the posted data.
    $data = $model->validate($form, $requestData);

    // Check for errors.
    if($data === false) {
      // Get the validation messages.
      $errors = $model->getErrors();

      // Push up to three validation messages out to the user.
      for($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
	if($errors[$i] instanceof Exception) {
	  $app->enqueueMessage($errors[$i]->getMessage(), 'warning');
	}
	else {
	  $app->enqueueMessage($errors[$i], 'warning');
	}
      }

      // Unset the passwords.
      unset($requestData['password1'], $requestData['password2']);

      // Save the data in the session.
      $app->setUserState('com_ketshop.edit.profile.data', $requestData);

      // Redirect back to the edit screen.
      $userId = (int) $app->getUserState('com_ketshop.edit.profile.id');
      $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=profile&layout=edit&c_id='.$userId, false));

      return false;
    }

    // Attempt to save the data.
    $return = $model->save($data);

    // Check for errors.
    if($return === false) {
      // Save the data in the session.
      $app->setUserState('com_ketshop.edit.profile.data', $data);

      // Redirect back to the edit screen.
      $userId = (int) $app->getUserState('com_ketshop.edit.profile.id');
      $this->setMessage(JText::sprintf('COM_KETSHOP_PROFILE_SAVE_FAILED', $model->getError()), 'warning');
      $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=profile&layout=edit&c_id='.$userId, false));

      return false;
    }

    // Check in the profile.
    $model->checkin($userId);

    $this->setMessage(JText::_('COM_KETSHOP_PROFILE_SAVE_SUCCESS'));

    // Flush the data from the session.
    $app->setUserState('com_ketshop.edit.profile.data', null);

    // Redirect back to the profile screen.
    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=profile', false));

    return true;
  }
}

