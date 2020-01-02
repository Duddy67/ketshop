<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access'); 


/*
 * In order to use the Joomla modal media selector with dynamycal items, we need 
 * to modify the JFormFieldMedia javascript to fit our purpose.
 * The original file is locate here:
 * libraries/cms/form/fields/media.php
 *
 * The upload mechanism (selection button etc...) is not defined here since 
 * it is dynamicaly created in Javascript (see: js/image.js).
 * N.B: The asset identification has also been removed since we don't use it. 
 *
 * IMPORTANT: If a native Joomla media field is used in the same view the jInsertFieldValue function
 *            will be overrided and unexpected behaviors might occur.
*/
class JFormFieldMediashop extends JFormField
{
  /**
   * The form field type.
   *
   * @var    string
   * @since  11.1
   */
  protected $type = 'Mediashop';

  /**
   * The initialised state of the document object.
   *
   * @var    boolean
   * @since  11.1
   */
  protected static $initialised = false;


  protected function getInput()
  {
    if(!self::$initialised) {
      // Load the modal behavior script.
      JHtml::_('behavior.modal');

      // Build the script.
      $script = array();
      $script[] = 'function jInsertFieldValue(value, id) {';
      // Builds the image url.
      // On front-end we must set src with the absolute path or SEF will add a wrong url path.  
      $script[] = '  url="'.JURI::root().'"+value;';

      if(JFactory::getApplication()->isAdmin()) {
	// Appends "../" to the path as we are in the administrator area.
	$script[] = '  url="../"+value;';
      }

      // Gets the image attributes.
      $script[] = '  var newImg = new Image();';
      $script[] = '  newImg.src = url;';
      $script[] = '  var height = newImg.height;';
      $script[] = '  var width = newImg.width;';

      // Separates the itemType from the id number which are concatenated in the id
      // parameter (itemType-idNb).
      $script[] = '  var result = /^([a-z]+)\-([0-9]+)$/.exec(id);';
      $script[] = '  var itemType = result[1];';
      $script[] = '  var idNb = result[2];';

      // Sets the hidden fields.
      $script[] = '  document.getElementById(itemType+"-image-src-"+idNb).value=url;';
      $script[] = '  document.getElementById(itemType+"-image-width-"+idNb).value=width;';
      $script[] = '  document.getElementById(itemType+"-image-height-"+idNb).value=height;';

      // Sets the dummy path field.
      $script[] = '  document.getElementById(itemType+"-image-path-"+idNb).value=value;';

      $script[] = '}';

      // Add the script to the document head.
      JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

      self::$initialised = true;
    }

    return;
  }
}
