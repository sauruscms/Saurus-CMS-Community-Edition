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


#################################
# function check_id
# id => objekt_id
# ------------------------------
# tagastab objekt_id 
# replikeeritud saidil

function smarty_function_check_id($params){
	global $site, $template;
	
	#########################
	# get all assigned template vars
	$tpl_vars = $template->smarty->get_template_vars();

	##############
	# default values
	extract($params);
	if( !isset($id) || !$tpl_vars["sitename"] ) {
		print "";
	} else {
		#funktsioon rep_id($vana_id, $sitename) asub failis `custom.inc.php`
		print rep_id($id, $tpl_vars["sitename"]);
	}
}
