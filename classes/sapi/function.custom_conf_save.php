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
# function custom_conf_save
#	id => default: <current page id>
#	param1 => value1
#	param2 => value2
#	...
#
# Saves configuration for given object, data in field objekt.ttyyp_params
function smarty_function_custom_conf_save ($params,&$smarty) {
	global $site, $leht;

	##############
	# default values
	extract($params);
	if(!isset($id)){ $id = $leht->id;}

	if(!$id) return;

	$objekt = new Objekt(array(
		objekt_id => $id,
		no_cache => 1
	));
	
	if($objekt->permission['U'])
	{
		$conf = new CONFIG($objekt->all['ttyyp_params']);
		foreach($params as $param=>$value){
			if($param == 'id') {continue;}
			$conf->put($param, $value);	
		}
	
		$sql = $site->db->prepare("
			UPDATE objekt SET ttyyp_params = ?
			WHERE objekt_id = ?",
			$conf->Export(),
			$id
		);
		$sth = new SQL($sql);
	}
	else 
	{
		new Log(array(
			'action' => 'update',
			'type' => 'WARNING',
			'objekt_id' => $objekt->objekt_id,
			'message' => sprintf("Access denied: attempt to edit %s '%s' (ID = %s)" , ucfirst(translate_en($objekt->all['klass'])), $objekt->pealkiri(), $objekt->objekt_id),
		));
	}
	
	return;
}

