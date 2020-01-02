<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');


/**
 * HTML View class for the KetShop component.
 */
class KetshopViewProduct extends JViewLegacy
{
  protected $state;
  protected $item;
  protected $nowDate;
  protected $user;
  protected $uri;
  public $shop_settings;


  /**
   * Execute and display a template script.
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  mixed  A string if successful, otherwise an Error object.
   *
   * @see     \JViewLegacy::loadTemplate()
   * @since   3.0
   */
  public function display($tpl = null)
  {
    // Initialise variables
    $this->state = $this->get('State');
    $this->item = $this->get('Item');
    $this->item->images = $this->get('Images');
    $this->user = JFactory::getUser();
    // Gets the global settings of the shop.
    $this->shop_settings = UtilityHelper::getShopSettings($this->user->id);
    $this->shop_settings->price_display = $this->shop_settings->tax_method;

    // Check for errors.
    if(count($errors = $this->get('Errors'))) {
      throw new Exception(implode("\n", $errors), 500);
    }

    $this->item->slug = $this->item->alias ? ($this->item->id.':'.$this->item->alias) : $this->item->id;
    // Compute the category slug.
    $this->item->catslug = $this->item->category_alias ? ($this->item->catid.':'.$this->item->category_alias) : $this->item->catid;
    // Get the possible extra class name.
    $this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx'));

    // Get the current url, (needed in the product edit layout).
    $this->uri = JUri::getInstance();

    // Increment the hits for this product.
    $model = $this->getModel();
    $model->hit();

    $this->item = $model->setProductPrices($this->item);
    $this->item = $model->setProductStocks($this->item);

    // Needed for the product properties layouts.
    $this->item->attributes_location = $this->item->weight_location = $this->item->dimensions_location = 'summary';

    $this->nowDate = JFactory::getDate()->toSql();

    $this->setDocument();

    parent::display($tpl);
  }


  /**
   * Includes possible css and Javascript files.
   *
   * @return  void
   */
  protected function setDocument() 
  {
    $doc = JFactory::getDocument();
    $doc->addStyleSheet(JURI::base().'components/com_ketshop/css/ketshop.css');
  }
}

