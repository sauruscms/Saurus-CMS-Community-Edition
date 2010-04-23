<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty parse emoticons modifier plugin
 *
 * Type:     modifier<br>
 * Name:     parse_emoticons<br>
 * Purpose:  replace common emoticons with their respective graphical images
 *
 * @author	Saurus <saurus@saurus.info>
 * @param string
 * @return string
 */
function smarty_modifier_parse_emoticons($string)
{
	return do_smileys($string);
}

?>
