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
# function init_files
#	parent => folder ID value or comma separated ID values; default: public/ folder ID
#	parent_dir => folder path
#	name => default: "files"
#	buttons => default: "new,edit,hide,move,delete"
#	start => <starting from row>
#	limit => <count of rows>
#	order => <field name> asc|desc
#	profile => <profile name(s)>
#	where => where clause (sql)
#	select => additional select clause
#	icons => relative path to the icons folder (eg "extensions/saurus4/images/file_icons/")
#	on_create => default: "publish"
# 
# Returns array of file objects 
# if parent is undefined, public/ folder is used

function smarty_function_init_files ($params, &$smarty) {
	global $site, $leht, $template, $class_path;

	$content_template = &$leht->content_template;

	$files = Array();

	##############
	# default values
	
	extract($params);
	
	$folder = false;
    
	if(isset($parent)) { 
		$sql = $site->db->prepare("SELECT objekt_id, relative_path FROM obj_folder WHERE objekt_id=?", $parent );
		$sth = new SQL($sql);
		$folder = $sth->fetch();		
	} 
	elseif(isset($parent_dir)) { # get parent folder info
		
		$parent_dir = preg_replace('#^/#', '', $parent_dir);
		$parent_dir = preg_replace('#/$#', '', $parent_dir);
		
		//parent dir must start with "public" or "shared"
		if(strpos($parent_dir, 'public') === 0 || strpos($parent_dir, 'shared') === 0)
		{
			$sql = $site->db->prepare("SELECT objekt_id, relative_path FROM obj_folder WHERE relative_path = ?", '/'.$parent_dir);
			$sth = new SQL($sql);
			$folder = $sth->fetch();
			$parent = $folder['objekt_id'];
		}
	}
	
	if(!$folder)
	{
		# default parent for file (folder "public/"): get folder ID of "public/"
		$sql = $site->db->prepare("SELECT objekt_id, relative_path FROM obj_folder WHERE relative_path = ? LIMIT 1",
			$site->CONF['file_path']
		);
		$sth = new SQL($sql);
		$folder = $sth->fetch();
		$parent = $folder['objekt_id'];
	}
	
	if(!isset($name)) { $name="files"; }
	
	switch ($on_create) {
		case 'publish': $publish = 1; break;
		case 'hide': $publish = 0; break;
		default: $publish = 1;
	}

	#  kui pole profile parameetrit, siis kasuta default profiili
	if(!$profile) { 
		$default_profile_def = $site->get_profile(array(
			id => $site->get_default_profile_id(array(source_table => 'obj_file'))
		));
		# get profile name
		$profile = $default_profile_def['name'];
		unset($default_profile_def);
	}

	###############
	# action-buttons
	# by default show all 

	if(!isset($buttons)) { 
		$buttons = array('new', 'edit', 'delete');
	} else {
		$buttons = split(',',$buttons);
	}

	###############
	# order, parent

	# for language compatibility, replace with search string existing db field name
	$order = preg_replace('#\btitle\b#i', "pealkiri", $order);
	$order = preg_replace('#\bdate\b#i', "aeg", $order);
	
	$where = preg_replace('#\btitle\b#i', "pealkiri", $where);
	$where = preg_replace('#\bdate\b#i', "aeg", $where);

	######## where: profile, replace technical name with field name

	if(trim($where)!='') {
		$where = " (".$where.") ";
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
		# sanity check: kui ei leitud sellise nimega profiili, anda toimetajale veateade ja vļæ½ljuda:
		if(!$profile_def[profile_id]) {
			if($site->admin) {
				print "<font color=red><b>Profile '".$profile_name."' not found!</b></font>";
			}
			return;
		}
		$profile_ids[] = $profile_def[profile_id];
		$profile_arr[$profile_def[profile_id]] = $profile_def;		
	} 

	############# parent
	$parent_id = trim($parent);   #Bug #2803: Tagil {init_files} ei saanud ette anda mitud parent ID v��rtust

	if ( $parent_id ) {

		##############
		# create SQL
		$alamlistSQL = new AlamlistSQL(array(
			parent => $parent_id,
			klass	=> "file",
			order => $order,
		));

		$alamlistSQL->add_select("obj_file.profile_id, obj_file.relative_path, obj_file.filename, obj_file.mimetype, obj_file.size");
		if(sizeof($profile_ids)>0 ) {
			$alamlistSQL->add_select("obj_file.*");
		}
		if(isset($select)){ $alamlistSQL->add_select($select); }
		if (sizeof($select_sql) > 0) { $alamlistSQL->add_select(join(", ", $select_sql)); }

		$alamlistSQL->add_from("LEFT JOIN obj_file ON objekt.objekt_id=obj_file.objekt_id");		

		if ($where) { $alamlistSQL->add_where($where); }

		$alamlist = new Alamlist(array(
			alamlistSQL => $alamlistSQL,
			start => $start,
			limit => $limit
		));
		$alamlist->debug->print_msg();

		$alamlist_count = new Alamlist(array(
			alamlistSQL => $alamlistSQL,
			on_counter => 1
		));

		##############
		# load variables
		$new_button = $alamlist->get_edit_buttons(array(
			tyyp_idlist => "21",
			profile_id => join(",",$profile_ids), # new nupule anda edasi kļæ½ik profiili ID-d
			publish => $publish
		));

		while ($obj = $alamlist->next()) {
			$obj->buttons = $obj->get_edit_buttons(array(
				tyyp_idlist => "21",
				profile_id => join(",",$profile_ids),
				nupud => $buttons,
				publish => $publish
			));
			$obj->id = $obj->objekt_id;
			$obj->parent = $obj->parent_id;
			$obj->folder_fullpath = $site->absolute_path.$folder['relative_path'];
			$obj->href = $site->CONF['wwwroot'].'/file.php?'.$obj->objekt_id; # Bug #2317
			$obj->title = $obj->all['pealkiri'];

			$obj->date = $site->db->MySQL_ee_short($obj->all['aeg']);
			$obj->datetime = $site->db->MySQL_ee($obj->all['aeg']);
			
			$obj->fdate = substr($obj->all['aeg'], 0, strpos($obj->all['aeg'], ' '));
			$obj->fdatetime = $obj->all['aeg'];

			$pathinfo = pathinfo($site->absolute_path.$obj->all['relative_path']);
			$obj->fullpath = $site->absolute_path.$obj->all['relative_path'];
			$obj->filename = $obj->all['filename'];
			$obj->mimetype = $obj->all['mimetype'];
			# size is set later: after profiles
			$obj->profile_id = $obj->all['profile_id'];
			$obj->extension = strtolower($pathinfo["extension"]);
			
			if ($icons)
			{
				if (!preg_match("/\/$/",$icons)) {$icons .= '/'; }
				
				if(file_exists($site->absolute_path.$icons.$obj->extension.'.gif'))
				{
					$obj->icon = $site->CONF['wwwroot'].'/'.$icons.$obj->extension.'.gif';
				}
				elseif(file_exists($site->absolute_path.$icons.'unknown.gif'))
				{
					$obj->icon = $site->CONF['wwwroot'].'/'.$icons.'unknown.gif';
				}
			}
		
			$obj->class = translate_en($obj->all[klass]); # translate it to english
			
			$obj->created_user_id = $obj->all['created_user_id'];
			$obj->created_user_name = $obj->all['created_user_name'];
			$obj->changed_user_id = $obj->all['changed_user_id'];
			$obj->changed_user_name = $obj->all['changed_user_name'];
			$obj->created_time = $site->db->MySQL_ee($obj->all['created_time']);
			$obj->fcreated_time = $obj->all['created_time'];
			$obj->changed_time = $site->db->MySQL_ee($obj->all['changed_time']);
			$obj->fchanged_time = $obj->all['changed_time'];
			$obj->last_commented_time = $site->db->MySQL_ee($obj->all['last_commented_time']);
			$obj->comment_count = $obj->all['comment_count'];


			########## KUI PROFIIL on parameetrina kaasas JA failil on Mļæ½ļæ½RATUD mļæ½ni PROFIIL, siis korja andmed "->" omadustena kokku
			if(sizeof($profile_ids)>0 ) {
			###### load object. #### NB! actually should be: profile_id is in "objekt" tabel. then we don't have to entire object
#			$obj->load_sisu();

			###### loop over profiles
			foreach($profile_ids as $profile_id){
				#printr($obj->objekt_id.' PROFILE_ID: '.$obj->all['profile_id']);

				include_once($class_path.'profile.class.php');
				
				$obj_profile = new Profile(array("id"=>$obj->all['profile_id']));
				
				#### 1. set profile fields as object attributes
				$obj_profile->set_obj_general_fields(array(
					"obj" => &$obj,
					"get_object_fields" => $get_object_fields
				));
				###################
				# get selectlist values - 1 extra sql per function; sql is fast
				if(is_array($obj_profile->selectlist)) {
					$obj_profile->selectlist = array_unique($obj_profile->selectlist);
					#printr($obj_profile->selectlist);
				}
				# go on if object values needs changing:
				if( sizeof($obj_profile->selectlist)>0 ) {
					#### 2. save array "->asset_names"  human readable NAME-s:
					$obj_profile->get_asset_names(array(
						"selectlist" => $obj_profile->selectlist
					));

					#printr($obj_profile->asset_names);			
					#printr($obj_profile->change_fields);

					### 3. save object rest of attributes
					#print "<br>muuta ID: ".$obj->id;	
					$obj_profile->set_obj_selectlist_fields(array(
						"obj" => &$obj,
						"change_fields" => $obj_profile->change_fields
					));					
				} # if any selectvalue exist & need to change 
				# / get selectlist values
				###################

			} ###### / loop over profiles
			} ####### / profile is set


			$obj->size = print_filesize($obj->all['size']);


			array_push($files, $obj);

		} ###### / loop over objects
	}

	$count = sizeof($files);
	$counttotal = (isset($limit) ? $alamlist_count->rows : $count);

	##############
	# assign to template variables
	$smarty->assign(array(
		$name => $files,
		$name.'_newbutton' => $new_button,
		$name.'_counttotal' => $counttotal,
		$name.'_count' => $count
	));
}
