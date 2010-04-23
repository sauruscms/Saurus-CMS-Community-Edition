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

global $class_path;

$class_path = '../classes/';

include($class_path.'port.inc.php');
include($class_path.'adminpage.inc.php');

$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1 : 1),
	'on_admin_keel' => 1,
));

$site->user->adminpermissions = $site->user->load_adminpermissions();			

//if (!$site->user->allowed_adminpage()) exit;

// control set
$swk_setup = (string)$_GET['swk_setup'];
if(!$swk_setup) $swk_setup = 'swk_setup';

$parent_id = (int)$_GET['parent_id'];
$language_id = (int)$_GET['lang'];

// object classes
$classes = (array)$_SESSION[$swk_setup]['classes'];
if(empty($classes)) $classes = array('rubriik', 'artikkel',);

// fields to pull from db
$fields = $_SESSION[$swk_setup]['db_fields'];
if(empty($fields)) $fields = array('select_checkbox', 'objekt_id', 'pealkiri', 'tyyp_id', );

if(isset($parent_id))
{
	$objects_sql = new AlamlistSQL (array(
		'parent' => $parent_id,
		'klass' => implode(',', $classes),
		'where' => 'keel = '.$language_id,
	));
	
	$objects_list = new Alamlist (array(
		'alamlistSQL' => $objects_sql,
	));
	
	$objects = array();
	while($object = $objects_list->next())
	{
		// translations
		if($object->all['klass']) $object->all['klass'] = strtolower($site->sys_sona(array('sona' => 'tyyp_'.$object->all['klass'], 'tyyp' => 'System')));
		
		//convert dates
		if($object->all['aeg']) $object->all['aeg'] = $site->db->MySQL_ee($object->all['aeg']);

		$objects[] = $object->all;
	}
	//printr($objects);
	if(count($objects))
	{
		//this header makes prototype.js eval() the response
		header('Content-type: text/javascript');
		//produce javascript 
		//reset loaded array
		echo 'loaded = new Array();';
		foreach($objects as $object)
		{
			$properties = array();
			foreach ($object as $name => $value)
			{
				if($name && in_array($name, $fields)) $properties[] = $name.':new '.$name.'(\''.str_replace("'", "\'", $value).'\')';
			}
			$properties[] = 'select_checkbox:new select_checkbox(0)';
			echo 'loaded.push({'.implode(',', $properties).'});';
		}
		// /produce javascript 
	}
}
exit;