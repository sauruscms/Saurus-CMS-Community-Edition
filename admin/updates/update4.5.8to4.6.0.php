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

$class_path = 'classes/';

include_once($class_path.'port.inc.php');

$site = new Site(array(
	'on_debug' => 0,
));

/*---------------------------	Code Begin	------------------------------------------*/


// filemanager migration

// convert full paths into relatives
$sql = "update obj_file set relative_path = replace(fullpath, '".$site->absolute_path."', '/') where locate('".$site->absolute_path."', fullpath) = 1";
new SQL($sql);

$sql = "update obj_folder set relative_path = replace(fullpath, '".$site->absolute_path."', '/') where locate('".$site->absolute_path."', fullpath) = 1";
new SQL($sql);

// delete junk files and folders: those who dont have the relative_path by now
new SQL("delete from objekt where objekt_id in (select objekt_id from obj_file where relative_path is NULL)");
new SQL("delete from objekt_objekt where objekt_id in (select objekt_id from obj_file where relative_path is NULL)");
new SQL("delete from obj_file where relative_path is NULL");

new SQL("delete from objekt where objekt_id in (select objekt_id from obj_folder where relative_path is NULL)");
new SQL("delete from objekt_objekt where objekt_id in (select objekt_id from obj_folder where relative_path is NULL)");
new SQL("delete from obj_folder where relative_path is NULL");

// make sure public folder permissions are 11111
include_once($class_path."alampuu.class.php");
$otsingu_juur = $site->alias('public');
#printr($otsingu_juur);
$puu = new Alampuu(array(
	'parent_id' => $otsingu_juur,
	'tyyp_idlist' => '22'  # folder
));
#printr($puu->objektid);

##################
# sql

######### loop
foreach($puu->objektid as $folder_id){

	## create folder object
	$objekt = new Objekt(array(
		'objekt_id' => $folder_id,
		'no_cache' => 1,
		'skip_sanity_check' => 1,
	));
	$objekt->load_sisu();

	### get this folder object permission mask directly from database
	$sql2 = $site->db->prepare("SELECT * FROM permissions  WHERE group_id=? AND source_id = ?", 1, $folder_id);
	$sth2 = new SQL($sql2);
	$tmp = $sth2->fetch();
	$perm_mask = $tmp['C'].$tmp['R'].$tmp['U'].$tmp['P'].$tmp['D'];

#		echo "<tr bgcolor=\"FFFFFF\"><td>"; 	
#printr($perm_mask);
#	echo "</td></tr>"; 	
#printr($objekt->all['pealkiri']. ' => '.$perm_mask.' (ID: '.$folder_id.')');

	if($perm_mask != '11111') { # wrong perm mask

		############ 1. DELETE ALL OLD PERMISSIONS for object
		$sql = $site->db->prepare("DELETE FROM permissions WHERE type=? AND source_id=?", 	
			'OBJ', 
			$folder_id
		);
		$sth = new SQL($sql);
				############ 2. INSERT NEW PERMISSIONS for object
				$sql2 = $site->db->prepare("INSERT INTO permissions (type,source_id,role_id,group_id,user_id,C,R,U,P,D) VALUES (?,?,?,?,?,?,?,?,?,?)", 	
					'OBJ', 
					$folder_id, 
					0,
					1, # everybody
					0,
					1, # C
					1, # R
					1, # U
					1, # P
					1 # D
				);
				$sth2 = new SQL($sql2);

	} # f wrong perm mask

} # loop folders

/*---------------------------	Code End	------------------------------------------*/

if ($site->on_debug){

	$site->debug->msg('SQL päringute arv = '.$site->db->sql_count.'; aeg = '.$site->db->sql_aeg);
	$site->debug->msg('TÖÖAEG = '.$site->timer->get_aeg());
#	$site->debug->print_msg();

}
