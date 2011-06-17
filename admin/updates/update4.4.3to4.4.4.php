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
 * @package 	SaurusCMS
 * @copyright 	2000-2010 Saurused Ltd (http://www.saurus.info/)
 * @license		Mozilla Public License 1.1 (http://www.opensource.org/licenses/mozilla1.1.php)
 * 
 */

global $site;

global $class_path;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == 'editor' ? '../classes/' : './classes/';

include_once($class_path.'port.inc.php');

$site = new Site(array(
	'on_debug' => 0,
));


/*---------------------------	Code Begin	------------------------------------------*/

// change sections with position 9 to 0
$objects = array();
$sql = 'select objekt_id from objekt where kesk = 9 and (tyyp_id = 1 or tyyp_id = 3)';
$result = new SQL($sql);
while($objekt_id = $result->fetchsingle())
{
	$objects[] = $objekt_id;
}

if(sizeof($objects))
{
	new SQL('update objekt set kesk = 0 where kesk = 9 and (tyyp_id = 1 or tyyp_id = 3)');
	
	new Log(array(
		'component' => 'Install',
	    'type' => 'NOTICE',
	    'action' => 'update',
	    'message' => 'Following object positions have been changed from 9 to 0 during version update: '.implode(', ', $objects),
	    'user_id' => 0,
    ));
}

// change sections with position 5 to 0
$objects = array();
$sql = 'select objekt_id from objekt where kesk = 5 and (tyyp_id = 1 or tyyp_id = 3)';
$result = new SQL($sql);
while($objekt_id = $result->fetchsingle())
{
	$objects[] = $objekt_id;
}

if(sizeof($objects))
{
	new SQL('update objekt set kesk = 0 where kesk = 5 and (tyyp_id = 1 or tyyp_id = 3)');
	
	new Log(array(
		'component' => 'Install',
	    'type' => 'NOTICE',
	    'action' => 'update',
	    'message' => 'Following object positions have been changed from 5 to 0 during version update: '.implode(', ', $objects),
	    'user_id' => 0,
    ));
}

/*---------------------------	Code End	------------------------------------------*/

if ($site->on_debug){

	$site->debug->msg('SQL päringute arv = '.$site->db->sql_count.'; aeg = '.$site->db->sql_aeg);
	$site->debug->msg('TÖÖAEG = '.$site->timer->get_aeg());
#	$site->debug->print_msg();

}
?>