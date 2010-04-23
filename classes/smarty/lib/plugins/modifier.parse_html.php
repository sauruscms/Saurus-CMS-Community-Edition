<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty parse html modifier plugin
 *
 * Type:     modifier<br>
 * Name:     parse_html<br>
 * Purpose:  replace links and mail addresses with html links
 *
 * @author	Saurus <saurus@saurus.info>
 * @param string
 * @return string
 */
function smarty_modifier_parse_html($string)
{
	return add_html_tags($string);
}

?>
