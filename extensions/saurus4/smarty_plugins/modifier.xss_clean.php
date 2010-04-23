<?php


/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty array_push modifier plugin
 *
 * Type:     modifier<br>
 * Name:     <br>
 * Date:     01.11.2006
 * Purpose:  clean string from xss attack vectors
 *
 * @author Saurus <saurus@saurus.info>
 */
function smarty_modifier_xss_clean($string)
{
	global $class_path;
	
	include_once($class_path.'custom.inc.php');
	
	return xss_clean($string);
}

?>