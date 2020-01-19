<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2019 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

defined('JPATH_BASE') or die;

$form = $displayData->getForm();
$attributeNames = array('title', 'name', 'lastname');

foreach($attributeNames as $attributeName) {
  if($form->getField($attributeName)) {
    $title = $attributeName;
    break;
  }
}

?>
<div class="form-inline form-inline-header">
	<?php
	echo $title ? $form->renderField($title) : '';
	echo $form->renderField('alias');
	?>
</div>
