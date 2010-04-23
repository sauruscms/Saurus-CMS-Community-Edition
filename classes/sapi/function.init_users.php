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
# function init_users
#	name => default: users
#	parent => group ID (optional)


# ei t��ta:
#	profile => <profile NAME(S)>
# ));
function smarty_function_init_users ($params,&$smarty) {
	global $site, $leht, $template, $class_path;

	$content_template = &$leht->content_template;

	########## INCLUDES
	include_once($class_path."adminpage.inc.php");
	include_once($class_path."user_html.inc.php");

	$users = array();
	
	##############
	# default values
	extract($params);	
	if(!isset($name)) { $name="users"; }
	if(!isset($parent)) { 
# 
	}
	$parent_id = trim($parent);
	#  kui pole profile parameetrit, siis  kasuta default profiili
/*
	if(!$profile) { 
		$default_profile_def = $site->get_profile(array(
			id => $site->get_default_profile_id(array(source_table => 'users'))
		));
		# get profile name
		$profile = $default_profile_def['name'];
		unset($default_profile_def);
	}

	##############
	# put all fields filter into arr
	$field_names = split(",",$fields);

	##############
	# put all profile names into arr
	$profile_names = split(",",$profile);

	##############
	# get all profile data from cash
	foreach($profile_names as $profile_name) {
		# profile name is case insensitive
		$profile_name = strtolower($profile_name);
		$profile_def = $site->get_profile(array(name=>$profile_name));
		# sanity check: kui ei leitud sellise nimega profiili, anda toimetajale veateade ja väljuda:
#echo printr($profile_def);
		if(!$profile_def[profile_id]) {
			if($site->in_editor) {
				print "<font color=red><b>Profile '".$profile_name."' not found!</b></font>";
			}
#			exit;
		}
		$profile_ids[] = $profile_def[profile_id];
		$profile_arr[$profile_def[profile_id]] = $profile_def;
		
	} 
#echo printr($profile_ids);
*/
	
		##############
		# create SQL - get users
	 	$sql = $site->db->prepare("SELECT users.* FROM users ");
		if($parent_id){
			$sql .= $site->db->prepare(" WHERE group_id=?",$parent_id );
		}
		$sth = new SQL($sql);

		while ($user = $sth->fetch()) { 

			$obj = new User(array()); #Bug #2832
			
			#$obj->buttons
			#$obj->profile = $profile_arr[$obj->all[profile_id]][name];
			
			$obj->id = $user['user_id'];
			$obj->user_id = $user['user_id'];
			$obj->firstname = $user['firstname'];
			$obj->lastname = $user['lastname'];
			$obj->username = $user['username'];
			$obj->email = $user['email'];
			$obj->all = $user;

			$users[] = $obj;
		}

	##############
	# assign to template variables

	$smarty->assign(array(
		$name => $users,
#		$name.'_newbutton' => $new_button,
		$name.'_rows' => count($users),
#		$name.'_count' => $alamlist->rows
	));
}
