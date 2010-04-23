<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty mysql_date_to_RFC822 modifier plugin
 *
 * Type:     modifier<br>
 * Name:     mysql_date_to_RFC822<br>
 * Date:     01.11.2006
 * Purpose:  covnert MySQL datetime to RFC 822 compliant date
 *
 * @author Saurus <saurus@saurus.info>
 */
function smarty_modifier_mysql_date_to_rfc822($mysql_datetime)
{
	return date('r', strtotime($mysql_datetime));
}

?>