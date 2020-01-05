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
class KetshopControllerConnection extends JControllerForm
{
  /**
   * Method to log in a user.
   *
   * @param   array  $credentials	The user's credentials (optional).
   *
   * @return  void
   *
   * @since   1.6
   */
  public function login($credentials = array())
  {
    // Check for request forgeries.
    $this->checkToken('post');

    $app = JFactory::getApplication();

    // Get the log in credentials.
    if(empty($credentials)) {
      $input = $app->input->getInputForRequestMethod();
      $credentials['username'] = $input->get('username', '', 'USERNAME');
      $credentials['password'] = $input->get('password', '', 'RAW');
    }

    // Perform the log in.
    if(true !== $app->login($credentials)) {
      // Login failed !
      $app->enqueueMessage(JText::_('COM_KETSHOP_LOGIN_AUTHENTICATE'), 'warning');
      $app->redirect(JRoute::_('index.php?option=com_ketshop&view=connection', false));
    }

    // Success
    $app->redirect(JRoute::_('index.php?option=com_ketshop&view=checkout', false));
  }


  /**
   * Method to register a user.
   *
   * @return  boolean  True on success, false on failure.
   *
   * @since   1.6
   */
  public function registration()
  {
    // Check for request forgeries.
    $this->checkToken();

    $app = JFactory::getApplication();
    $model = $this->getModel('Connection', 'KetshopModel');

    // Get the user data.
    $requestData = $this->input->post->get('jform', array(), 'array');
    // Validate the posted data.
    $form = $model->getForm();

    if(!$form) {
      JError::raiseError(500, $model->getError());
      return false;
    }

    $data = $model->validate($form, $requestData);

    // Check for validation errors.
    if($data === false) {
      // Get the validation messages.
      $errors = $model->getErrors();

      // Push up to three validation messages out to the user.
      for($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
	if($errors[$i] instanceof Exception) {
	  $app->enqueueMessage($errors[$i]->getMessage(), 'error');
	}
	else {
	  $app->enqueueMessage($errors[$i], 'error');
	}
      }

      // Save the data in the session.
      $app->setUserState('com_ketshop.registration.data', $requestData);

      // Redirect back to the registration screen.
      $app->redirect(JRoute::_('index.php?option=com_ketshop&view=connection', false));

      return false;
    }

    // Attempt to save the data.
    $return = $model->register($data);

    // Check for errors.
    if($return === false) {
      // Save the data in the session.
      $app->setUserState('com_ketshop.registration.data', $data);

      // Redirect back to the edit screen.
      $this->setMessage($model->getError(), 'error');
      $app->redirect(JRoute::_('index.php?option=com_ketshop&view=connection', false));

      return false;
    }

    // Flush the data from the session.
    $app->setUserState('com_ketshop.registration.data', null);

    // Log the new user in.
    $credentials = array();
    $credentials['username'] = $data['username'];
    $credentials['password'] = $data['password1'];

    $this->login($credentials);
  }
}

