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

// add fulltext_keywords to default article profile
$sql = "select profile_id, data from object_profiles where source_table = 'obj_artikkel' and is_predefined = 1";
$result = new SQL($sql);
$row = $result->fetch('ASSOC');

$profile = unserialize($row['data']);

$profile['fulltext_keywords'] = array(
    'name' => 'fulltext_keywords',
    'type' => 'TEXT',
    'source_object' => '',
    'default_value' => '',
    'db_type' => 'text',
    'is_required' => 0,
    'is_active' => 0,
    'is_predefined' => 1,
    'is_general' => 1,
);

$sql = "update object_profiles set data = '".serialize($profile)."' where profile_id = ".$row['profile_id'];
new SQL($sql);
// / add fulltext_keywords to default article profile

/*---------------------------	Code End	------------------------------------------*/

if ($site->on_debug){

	$site->debug->msg('SQL päringute arv = '.$site->db->sql_count.'; aeg = '.$site->db->sql_aeg);
	$site->debug->msg('TÖÖAEG = '.$site->timer->get_aeg());
#	$site->debug->print_msg();

}
?>