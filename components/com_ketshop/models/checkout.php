<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 

JLoader::register('PriceruleTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/pricerule.php');


class KetshopModelCheckout extends JModelItem
{
  use PriceruleTrait;

  protected $order_model = null;
  protected $order = null;


  /**
   * Constructor
   *
   * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
   *
   * @since   3.0
   * @throws  \Exception
   */
  public function __construct($config = array())
  {
    $this->order_model = JModelLegacy::getInstance('Order', 'KetshopModel');
    $this->order = $this->order_model->getCurrentOrder();

    parent::__construct($config);
  }


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


  /**
   * Collects the available payment modes through the ketshop payment plugins. 
   *
   * @return array	An array of payment objects.
   */
  public function getPaymentModes()
  {
    JPluginHelper::importPlugin('ketshoppayment');
    $dispatcher = JDispatcher::getInstance();

    // Trigger the event then retrieves the shippings from all the ketshop payment plugins.
    $results = $dispatcher->trigger('onKetshopPayment');

    $paymentModes = array();

    // Loops through the results returned by the plugins.
    foreach($results as $result) {
      $paymentModes[] = $result;
    }

    return $paymentModes;
  }
}

