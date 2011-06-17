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

// search for sys_alias objekts that should have a parent_is 0 but are not in the object tree
$parentless = array();

$sql = "select objekt.objekt_id, parent_id from objekt left join objekt_objekt using(objekt_id) where sys_alias in ('home','system','gallup_arhiiv','trash','public','shared','resource');";
$result = new SQL($sql);

while($row = $result->fetch('ASSOC'))
{
	if($row['parent_id'] === null)
	{
		$parentless[] = $row['objekt_id'];
	}
}

//insert them into objekt_objekt tree
foreach($parentless as $objekt_id)
{
	$sql = "insert into objekt_objekt (objekt_id, parent_id, sorteering) values ('".$objekt_id."', 0, 1);";
	new SQL($sql);
}

/*---------------------------	Code End	------------------------------------------*/

if ($site->on_debug){

	$site->debug->msg('SQL päringute arv = '.$site->db->sql_count.'; aeg = '.$site->db->sql_aeg);
	$site->debug->msg('TÖÖAEG = '.$site->timer->get_aeg());
#	$site->debug->print_msg();

}
?>