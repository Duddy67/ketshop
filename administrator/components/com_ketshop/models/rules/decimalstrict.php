<?php
/**
 * @package KetShop
 * @copyright Copyright (c) 2018 - 2019 Lucas Sanner
 * @license GNU General Public License version 3, or later
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access'); 


class JFormRuleDecimalstrict extends JFormRule
{
  protected $regex = '^[0-9]{1,}\.[0-9]+$';
}

