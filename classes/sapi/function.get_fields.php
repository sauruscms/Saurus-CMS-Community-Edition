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
# function get_fields
#	table => <table name>,
#	name => default: fields <template variable name, where assign to>
# ));
# 
# return fields array of given table
function smarty_function_get_fields ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	extract($params);
	if(!isset($name)) { $name="fields"; }
	if(!isset($table)) { 
		if($site->admin) {
			print "<font color=red><b>Table parameter is required!</b></font>";
		}
		exit;
	}

	$fields = array();
	$fields = split(",", $site->db->get_fields(array(tabel => $table))	);

	##############
	# assign to template variables
	$smarty->assign(array(
			$name => $fields,
		));

}
