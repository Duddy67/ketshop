<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2016 - 2017 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die; //No direct access to this file.


class ShopHelper
{
  /**
   * Gets and returns the snitch session variable.
   * Creates it if it doesn't exist.
   *
   * @return  object            The snitch object.
   *
   */
  public static function getSnitch()
  {
    $session = JFactory::getSession();
    $snitch = $session->get('ketshop.snitch', null);

    if($snitch === null) {
      $snitch = new stdClass();
      $snitch->from = '';
      $snitch->empty_filters = array();
      $snitch->limit_start = 0;
    }

    return $snitch;
  }


  /**
   * Stores the given snitch object in the session.
   *
   * @param   object       The snitch object.
   *
   * @return  boolean      False whether the given parameter is not an object. True otherwise.
   *
   */
  public static function setSnitch($snitch)
  {
    if(!is_object($snitch)) {
      JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_KETSHOP_ERROR_WRONG_PARAMETER_TYPE', 'object', gettype($snitch)), 'error');
      return false;
    }

    $session = JFactory::getSession();
    $session->set('ketshop.snitch', $snitch);

    return true;
  }


  /**
   * Gets the current cookie id. Creates it if it doesn't exist.
   *
   * @return  string	the cookie id.
   *
   */
  public static function getCookieId()
  {
    // Gets input cookie object
    $inputCookie  = JFactory::getApplication()->input->cookie;

    // Gets cookie data
    $id = $inputCookie->get('ketshop', null);

    if($id === null) {
      $id = uniqid();
      // Creates a one week long cookie.
      $inputCookie->set('ketshop', $id, time() + (7 * 24 * 3600));
    }
    else {
      // TODO: https://mantis.codalia.fr/view.php?id=4
      // Extends by 1 hour the current cookie duration in case it is about to reach
      // expiration date.
      // preg_match('#-([0-9:-]+)$#', $id, $matches);
      // $date = $matches[1];
      // preg_replace('#^[0-9]{4}-[0-9]{2}-[0-9]{2}(-)#', ' ', $date);
      // https://stackoverflow.com/questions/3290424/set-a-cookie-to-never-expire
      //$inputCookie->set('ketshop', $id, 2147483647);
      //$inputCookie->set('ketshop', $id, time() + (7 * 24 * 3600) + 3600);
    }

    return $id;
  }


  /**
   * Returns width and height of an image according to its reduction rate.
   *
   * @param   integer		The image width.
   * @param   integer		The image height.
   * @param   integer		The reduction rate.
   *
   * @return  array		The new width and size of the image. 
   *
   */
  public static function getThumbnailSize($width, $height, $reductionRate)
  {
    $size = array();

    if($reductionRate == 0) {
      // Just returns the original values.
      $size['width'] = $width;
      $size['height'] = $height;
    }
    // Computes the new image size.
    else { 
      $widthReduction = ($width / 100) * $reductionRate;
      $size['width'] = $width - $widthReduction;

      $heightReduction = ($height / 100) * $reductionRate;
      $size['height'] = $height - $heightReduction;
    }

    return $size;
  }
}


