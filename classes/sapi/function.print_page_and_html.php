<?php
/**
 * This source file is is part of Saurus CMS content management software.
 * It is licensed under MPL 1.1 (http://www.opensource.org/licenses/mozilla1.1.php).
 * Copyright (C) 2000-2010 Saurused Ltd (http://www.saurus.info/).
 * Redistribution of this file must retain the above copyright notice.
 * 
 * Please note that the original authors never thought this would turn out
 * such a great piece of software when the work started using Perl in year 2000.
 * Due to organic growth, you may find parts of the software being
 * a bit (well maybe more than a bit) old fashioned and here's where you can help.
 * Good luck and keep your open source minds open!
 * 
 * @package		SaurusCMS
 * @copyright	2000-2010 Saurused Ltd (http://www.saurus.info/)
 * @license		Mozilla Public License 1.1 (http://www.opensource.org/licenses/mozilla1.1.php)
 * 
 */



//This function will find the correct language specific page-end HTML value and echoes it.

function smarty_function_print_page_and_html($params, &$smarty)
{
	global $site;

		$curr_objekt = new Objekt(array(
			objekt_id => $site->alias(array(
				"key" => "rub_home_id",
				"keel" => $site->keel
			))
		));

		$conf = new CONFIG($curr_objekt->all["ttyyp_params"]);
			foreach ($conf->CONF as $k=>$v){
				if($k=="page_end_html"){
					echo str_replace("XXYYZZ","\n",$v);
				}
			}
}
