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


function smarty_function_init_file($params, &$smarty)
{
	global $site, $leht, $class_path;
	
	extract($params);
    
	$id = (int)$id;
	if(!$id) $id = $leht->id;	
	
    if(!isset($name)) $name = 'file';
	
    if(!isset($buttons))
		$buttons = array('new', 'edit', 'hide', 'move', 'delete');
	else
		$buttons = split(',',$buttons);
	
	$obj = new Objekt(array(
		'objekt_id' => $id,
	));
	$obj->load_sisu();
	
	$obj->id = $obj->objekt_id;
	$obj->parent = $obj->parent_id;
	$obj->title = $obj->pealkiri;
	$obj->class = translate_en($obj->all['klass']);
	
	$obj->date = $site->db->MySQL_ee_short($obj->all['aeg']);
	$obj->datetime = $site->db->MySQL_ee($obj->all['aeg']);
	
	$obj->fdate = substr($obj->all['aeg'], 0, strpos($obj->all['aeg'], ' '));
	$obj->fdatetime = $obj->all['aeg'];
	
	$obj->created_user_id = $obj->all['created_user_id'];
	$obj->created_user_name = $obj->all['created_user_name'];
	$obj->changed_user_id = $obj->all['changed_user_id'];
	$obj->changed_user_name = $obj->all['changed_user_name'];
	$obj->created_time = $site->db->MySQL_ee($obj->all['created_time']);
	$obj->fcreated_time = $obj->all['created_time'];
	$obj->changed_time = $site->db->MySQL_ee($obj->all['changed_time']);
	$obj->fchanged_time = $obj->all['changed_time'];
	$obj->last_commented_time = $site->db->MySQL_ee($obj->all['last_commented_time']);;
	$obj->comment_count = $obj->all['comment_count'];
	$obj->href = $site->CONF['wwwroot'].'/file.php?'.$obj->objekt_id;
	$obj->fullpath = preg_replace('#/$#', '', $site->absolute_path).$obj->all['relative_path'];
	unset($obj->all['fullpath']);
	$obj->filename = $obj->all['filename'];
	$obj->mimetype = $obj->all['mimetype'];
	$obj->profile_id = $obj->all['profile_id'];
	$obj->url = $site->CONF['wwwroot'].$obj->all['relative_path'];
	$obj->size = print_filesize($obj->all['size']);

	$pathinfo = pathinfo($obj->fullpath);
	$obj->extension = strtolower($pathinfo['extension']);
	
	// for images give gallery thumbs and images
	if(strpos($obj->all['mimetype'], 'image/') === 0)
	{
		$folder = preg_replace('#/$#', '', $site->absolute_path).str_replace($obj->all['filename'], '', $obj->all['relative_path']);
		$folder_url = $site->CONF['wwwroot'].$folder;
		
		//thumbs
		if(file_exists($folder.'.gallery_thumbnails/'.$obj->all['filename']) && $thumb_info = @getimagesize($folder.'.gallery_thumbnails/'.$obj->all['filename']))
		{
			$obj->thumb_path = $folder_url.'.gallery_thumbnails/'.$obj->all['filename'];
			$obj->thumb_width = $thumb_info[0];
			$obj->thumb_height = $thumb_info[1];
		}
		
		//image
		if(file_exists($folder.'.gallery_pictures/'.$obj->all['filename']) && $image_info = @getimagesize($folder.'.gallery_pictures/'.$obj->all['filename']))
		{
			$obj->image_path = $folder_url.'.gallery_pictures/'.$obj->all['filename'];
			$obj->image_width = $image_info[0];
			$obj->image_height = $image_info[1];
		}
		
		//actual image
		if(file_exists($site->absolute_path.$obj->all['relative_path']) && $actual_image_info = @getimagesize($site->absolute_path.$obj->all['relative_path']))
		{
			$obj->actual_image_path = $obj->url;
			$obj->actual_image_width = $actual_image_info[0];
			$obj->actual_image_height = $actual_image_info[1];
		}
	}
	
	if ($icons)
	{
		if (!preg_match("/\/$/",$icons)) $icons .= '/';
		
		if(file_exists($site->absolute_path.$icons.$obj->extension.'.gif'))
		{
			$obj->icon = $site->CONF['wwwroot'].'/'.$icons.$obj->extension.'.gif';
		}
		elseif(file_exists($site->absolute_path.$icons.'unknown.gif'))
		{
			$obj->icon = $site->CONF['wwwroot'].'/'.$icons.'unknown.gif';
		}
	}
	
	if(!$profile) { 
		$default_profile_def = $site->get_profile(array(
			'id' => $site->get_default_profile_id(array(source_table => 'obj_file'))
		));
		# get profile name
		$profile = $default_profile_def['name'];
		unset($default_profile_def);
	}

		##############
	# put all profile names into arr
	if ($profile) {
		$profile_names = split(",",$profile);
	} else {
		$profile_names = array();
		$profile_ids = array();
	}

	# get all profile data from cash
	foreach($profile_names as $profile_name) {
		# profile name is case insensitive
		$profile_name = strtolower($profile_name);

		$profile_def = $site->get_profile(array(name=>$profile_name));
		# sanity check: kui ei leitud sellise nimega profiili, anda toimetajale veateade
		if(!$profile_def['profile_id']) {
			if($site->admin) {
				print "<font color=red><b>Profile '".$profile_name."' not found!</b></font>";
			}
			return;
		}
		$profile_ids[] = $profile_def['profile_id'];
		$profile_arr[$profile_def['profile_id']] = $profile_def;		
	} 

	$obj->buttons = $obj->get_edit_buttons(array(
		'nupud' => $buttons,
		'tyyp_idlist' => 21,
		'publish' => $publish,
		'profile_id' => join(',', $profile_ids),
	));

	$profile_def = $site->get_profile(array('id' => $obj->all['profile_id']));
	if($profile_def[profile_id])
	{
		include_once($class_path.'profile.class.php');
		
		$obj_profile = new Profile(array('id' => $obj->all['profile_id']));

		#### 1. set profile fields as object attributes
		$obj_profile->set_obj_general_fields(array(
			'obj' => &$obj,
			'get_object_fields' => $get_object_fields
		));
		###################
		# get selectlist values - 1 extra sql per function; sql is fast
		if(is_array($obj_profile->selectlist))
		{
			$obj_profile->selectlist = array_unique($obj_profile->selectlist);
			#printr($obj_profile->selectlist);
		}
		# go on if object values needs changing:
		if(sizeof($obj_profile->selectlist) > 0)
		{
			#### 2. save array "->asset_names"  human readable NAME-s:
			$obj_profile->get_asset_names(array(
				'selectlist' => $obj_profile->selectlist
			));

			### 3. save object rest of attributes
			$obj_profile->set_obj_selectlist_fields(array(
				'obj' => &$obj,
				'change_fields' => $obj_profile->change_fields
			));					
		} # if any selectvalue exist & need to change 
		# / get selectlist values
		###################
	}
	
	foreach($obj->all as $fieldname => $value)
	{
		$obj->$fieldname = $value;
	}

	$smarty->assign($name, $obj);
}
