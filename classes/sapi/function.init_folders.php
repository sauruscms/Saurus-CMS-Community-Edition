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
# function init_folders
#	name => default: "folders"
#	parent => top folder ID
#	parent_dir => top folder name, eg "public"
#
function smarty_function_init_folders ($params, &$smarty) {
	global $site, $leht, $class_path;
	
	include_once($class_path.'adminpage.inc.php');

	##############
	# default values
	extract($params);
	if(!isset($name)) { $name = 'folders'; }
    
	if(!isset($parent) && !isset($parent_dir))
	{ 
		# default parent for file (folder "public/"): get folder ID of "public/"
		$sql = $site->db->prepare("SELECT objekt_id, relative_path FROM obj_folder WHERE relative_path = ? LIMIT 1",
			$site->CONF['file_path']
		);
		$sth = new SQL($sql);
		$folder = $sth->fetch();
		$parent_dir = $folder['relative_path'];
		$parent = $folder['objekt_id'];
	} 
	elseif(isset($parent))
	{
		# get parent folder info
		$parent = (int)$parent;
		
		if($parent)
		{
			$sql = $site->db->prepare("SELECT objekt_id, relative_path FROM obj_folder WHERE objekt_id = ?", $parent );
			$sth = new SQL($sql);
		}
		else return;
	}
	elseif(isset($parent_dir))
	{
		$parent_dir = preg_replace('#^/#', '', $parent_dir);
		$parent_dir = preg_replace('#/$#', '', $parent_dir);
		
		$sql = $site->db->prepare("SELECT objekt_id, relative_path FROM obj_folder WHERE relative_path = ? LIMIT 1",
			'/'.$parent_dir
		);
		$sth = new SQL($sql);
		
		if($sth->rows == 1)
		{
			$folder = $sth->fetch();
			$parent_dir = $folder['relative_path'];
			$parent = $folder['objekt_id'];
		}
	}
	else 
	{
		return;
	}
	
    
	$folders = array();
	
	$folders_from_fm = get_subfolders($parent);
	
	foreach($folders_from_fm as $folder)
	{
        $obj = new stdClass(); # Bug #2318

		$obj->title = $folder['title'];
		$obj->path = $folder['relative_path'];
        $obj->id = $folder['objekt_id']; # current folder ID
        $obj->parent_id = $folder['parent_id']; ## parent folder ID??
        $obj->fullpath = $site->absolute_path.$folder['relative_path']; # absolute path of the folder
        $obj->file_count = $folder['file_count'];
        $folders[] = $obj;
    }	
	$smarty->assign($name, $folders);

	return;
}
