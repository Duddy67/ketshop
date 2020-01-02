<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');


/**
 * KetShop Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_ketshop
 * @since       1.5
 */
abstract class KetshopHelperRoute
{
  /**
   * Get the product route.
   *
   * @param   integer  $id        The route of the product item.
   * @param   integer  $catid     The category ID.
   * @param   integer  $language  The language code.
   *
   * @return  string  The article route.
   *
   * @since   1.5
   */
  public static function getProductRoute($id, $catid = 0, $language = 0)
  {
    // Create the link
    $link = 'index.php?option=com_ketshop&view=product&id='.$id;

    if((int) $catid > 1) {
      $link .= '&catid='.$catid;
    }

    if($language && $language !== '*' && JLanguageMultilang::isEnabled()) {
      $link .= '&lang='.$language;
    }

    return $link;
  }


  /**
   * Get the category route.
   *
   * @param   integer  $catid     The category ID.
   * @param   integer  $language  The language code.
   *
   * @return  string  The product route.
   *
   * @since   1.5
   */
  public static function getCategoryRoute($catid, $language = 0)
  {
    if($catid instanceof JCategoryNode) {
      $id = $catid->id;
    }
    else {
      $id = (int) $catid;
    }

    if($id < 1) {
      $link = '';
    }
    else {
      $link = 'index.php?option=com_ketshop&view=category&id='.$id;

      if($language && $language !== '*' && JLanguageMultilang::isEnabled()) {
	$link .= '&lang='.$language;
      }
    }

    return $link;
  }


  /**
   * Get the form route.
   *
   * @param   integer  $id  The form ID.
   *
   * @return  string  The product route.
   *
   * @since   1.5
   */
  public static function getFormRoute($id)
  {
    return 'index.php?option=com_ketshop&task=product.edit&n_id='.(int)$id;
  }
}
