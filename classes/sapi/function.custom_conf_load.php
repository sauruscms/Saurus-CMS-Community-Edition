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
# function custom_conf_load
#	name => default: "custom_conf"
#	id => default: <current page id>
#
# Loads configuration for given object, data from field objekt.ttyyp_params
function smarty_function_custom_conf_load ($params,&$smarty) {
	global $site, $leht;

	##############
	# default values
	extract($params);
	if(!isset($name)){ $name = "custom_conf";}
	if(!isset($id)){ $id = $leht->id;}

	if(!$id) return;

	$objekt = new Objekt(array(
		objekt_id => $id,
		no_cache => 1
	));
	$conf = new CONFIG($objekt->all['ttyyp_params']);

	$smarty->assign($name, $conf->CONF);
	return;
}
