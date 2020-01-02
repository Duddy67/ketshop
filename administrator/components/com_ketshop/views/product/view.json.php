<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access'); 
 

/**
 * JSON Product View class. Mainly used for Ajax request. 
 */
class KetshopViewProduct extends JViewLegacy
{
  public function display($tpl = null)
  {
    $jinput = JFactory::getApplication()->input;
    // Collects the required variables.
    $context = $jinput->get('context', '', 'string');
    $productId = $jinput->get('product_id', 0, 'uint');
    $isAdmin = $jinput->get('is_admin', 0, 'uint');
    $model = $this->getModel();
    $results = array();

    $results['attribute'] = $model->getProductAttributes($productId);
    $results['image'] = $model->getImageData($productId, $isAdmin);
    $results['variant'] = $model->getVariantData($productId);
    $results['bundle'] = $model->getBundleProducts($productId);

    echo new JResponseJson($results);
  }
}



