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
 * Date:     01.04.2006
 * Purpose:  duplicate PHP's array_push function
 *
 * @author Saurus <saurus@saurus.info>
 */
function smarty_modifier_array_push(&$array, $value)
{
	if(!is_array($array) && empty($array))
	{
		$array = array();
	}
	array_push($array, $value);
}
?>
