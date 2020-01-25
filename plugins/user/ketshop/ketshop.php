<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JLoader::register('UtilityHelper', JPATH_ADMINISTRATOR.'/components/com_ketshop/helpers/utility.php');
JLoader::register('ShopHelper', JPATH_SITE.'/components/com_ketshop/helpers/shop.php');
JLoader::register('OrderTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/order.php');



class plgUserKetshop extends JPlugin
{
  use OrderTrait;

  protected $post;

  /**
   * Constructor.
   *
   * @param   object  &$subject  The object to observe
   * @param   array   $config    An optional associative array of configuration settings.
   *
   * @since   3.7.0
   */
  public function __construct(&$subject, $config)
  {
    //Loads the component language file.
    $lang = JFactory::getLanguage();
    $langTag = $lang->getTag();
    $lang->load('com_ketshop', JPATH_ROOT.'/administrator/components/com_ketshop', $langTag);
    //Get the POST data.
    $this->post = JFactory::getApplication()->input->post->getArray();

    parent::__construct($subject, $config);
  }


  public function onUserAuthorisation($user, $options)
  {
    return $user;
  }


  public function onUserAuthorisationFailure($user)
  {
  }


  public function onUserLogin($user, $options)
  {
    return true;
  }


  public function onUserLogout($credentials, $options)
  {
    return true;
  }


  /**
   * This event is triggered whenever a user is successfully logged in.
   *
   * @param   array    $options    The logged in user data.
   *
   * @return  boolean
   */
  public function onUserAfterLogin($options)
  {
    // Gets the ketshop current cookie id.
    $cookieId = ShopHelper::getCookieId();

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    // Checks whether a previous shopping order linked to the user already exists.
    $query->select('id')
	  ->from('#__ketshop_order')
          ->where('cookie_id='.$db->Quote($cookieId))
          ->where('status='.$db->Quote('shopping'))
          ->where('customer_id='.(int)$options['user']->id);
    $db->setQuery($query);
    $previousOrder = $db->loadObject();

    if($previousOrder) {
      $this->updateCurrentOrder($cookieId, $previousOrder, $options['user']);
    }

    return true;
  }


  public function onUserBeforeSave($oldUser, $isNew, $newUser)
  {
    return true;
  }


  /**
   * Saves user profile data
   *
   * @param   array    $data    entered user data
   * @param   boolean  $isNew   true if this is a new user
   * @param   boolean  $result  true if saving the user worked
   * @param   string   $error   error message
   *
   * @return  boolean
   */
  function onUserAfterSave($data, $isNew, $result, $error)
  {
    if($isNew && $result) {
      $db = JFactory::getDbo();
      // Changes the auto increment starting number in order to synchronize the 
      // user ids with the customer ids 
      $query = 'ALTER TABLE '.$db->quoteName('#__ketshop_customer').' AUTO_INCREMENT='.(int)$data['id'];
      $db->setQuery($query);
      $db->execute();

      $columns = array('firstname', 'lastname', 'phone', 'created', 'created_by', 'shipping_address');
      $values = $db->Quote($data['firstname']).','.$db->Quote($data['lastname']).','.$db->Quote($data['phone']).','.$db->Quote($data['registerDate'].','.$data['id'].','.$data['shipping_address']);

      // Add the new Joomla user's data into the ketshop_customer table
      $query = $db->getQuery(true);
      $query->clear()
	    ->insert($db->quoteName('#__ketshop_customer'))
	    ->columns($columns)
	    ->values($values);
      $db->setQuery($query);
      $db->execute();

      $customerId = (int)$db->insertid();

      UtilityHelper::insertAddress($data, 'billing', 'customer', $customerId);

      if((int)$data['shipping_address']) {
	UtilityHelper::insertAddress($data, 'shipping', 'customer', $customerId);
      }
    }
  }

  public function onUserBeforeDelete($user)
  {
  }


  public function onUserAfterDelete($user, $success, $msg)
  {
  }


  /**
   * Switches the products from the previous order to the current order (if any).
   *
   * @param   string   $cookieId	The id of the current cookie.
   * @param   object   $previousOrder	The previous user's order.
   * @param   object   $user		The logged in user.
   *
   * @return  void
   */
  private function updateCurrentOrder($cookieId, $previousOrder, $user)
  {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    // Checks whether a current "blank" order does exist (ie: an order not yet linked to any customer).
    // If it does it means that the current user has added some products in the cart before logging in.
    $query->select('id, customer_id')
	  ->from('#__ketshop_order')
          ->where('cookie_id='.$db->Quote($cookieId))
          ->where('status='.$db->Quote('shopping'))
          ->where('customer_id=0');
    $db->setQuery($query);
    $order = $db->loadObject();

    if($order) {
      // Retrieves products from the previous user's order.
      $query->clear()
	    ->select('op.prod_id, op.var_id')
	    ->from('#__ketshop_order_prod AS op')
	    ->join('INNER', '#__ketshop_order AS o ON o.id=op.order_id')
	    ->where('o.id='.(int)$previousOrder->id);
      $db->setQuery($query);
      $results = $db->loadObjectList();

      $session = JFactory::getSession();
      $coupons = $session->get('coupons', array(), 'ketshop'); 

      // Switches products (if any) from the previous order to the current order.
      foreach($results as $result) {
	$product = $this->getProduct($result->prod_id, $result->var_id, $user, $coupons);
	$this->storeProduct($product, $order);
	// Updates the cart amounts.
	$cartPriceRules = $this->getCartPriceRules($user, $coupons);
	$this->setAmounts($order, $cartPriceRules);
      }

      // Deletes all of the elements linked to the previous order (products, shipping etc...).
      $this->resetOrder($previousOrder);
      // Then deletes the previous order.
      $query->clear()
	    ->delete('#__ketshop_order')
	    ->where('id='.(int)$previousOrder->id);
      $db->setQuery($query);
      $db->execute();

      // Finally binds the updated current order to the user.
      $this->setUserId($user->id, $order);
    }
  }
}

