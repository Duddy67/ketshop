<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

//JLoader::register('PriceruleTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/pricerule.php');
JLoader::register('ShippingTrait', JPATH_ADMINISTRATOR.'/components/com_ketshop/traits/shipping.php');


/**
 * @package     KetShop
 * @subpackage  com_ketshop
 */
class KetshopControllerPayment extends JControllerForm
{
  use ShippingTrait;


  /**
   * The order model.
   *
   * @var    object
   */
  protected $order_model = null;

  /**
   * The current order.
   *
   * @var    object
   */
  protected $order = null;

  /**
   * The current user.
   *
   * @var    object
   */
  protected $user = null;


  /**
   * Constructor.
   *
   * @param   array  $config  An optional associative array of configuration settings.
   *
   * @see     \JControllerLegacy
   * @since   1.6
   * @throws  \Exception
   */
  public function __construct($config = array())
  {
    parent::__construct($config);

    // Sets some common variables.
    $this->order_model = $this->getModel('Order', 'KetshopModel');
    $this->order = $this->order_model->getCurrentOrder();
    $this->user = JFactory::getUser();
  }


  /**
   * 
   *
   * @return  void
   */
  public function proceed()
  {
    // Gets the ids from GET.
    echo $shippingId = $this->input->get('shipping_id', 0, 'uint');
    echo $paymentId = $this->input->get('payment_id', 0, 'uint');
    echo 'payment';

    $shippings = $this->getShippingsFromPlugins($this->order);

    foreach($shippings as $shipping) {
      if($shipping->id == $shippingId) {
	$this->order_model->setShipping($shipping, $this->order);
	break;
      }
    }

    $this->setRedirect(JRoute::_('index.php?option=com_ketshop&view=payment&payment_id='.(int)$paymentId, false));
  }


  public function action()
  {
    echo 'action';
    echo $action = $this->input->get('action', '', 'string');
    echo $paymentMode = $this->input->get('payment_mode', '', 'string');
    return;
  }


  public function response()
  {
    echo 'response';
    return;
  }
}

