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


###############################
# Saurus API layer: Assets
# 
###############################
#################################
# function init_assets
#	name => default: assets
#	profile => <profile name(s)>
#	parent => parent ID value or comma separated ID values; 
#	position => position number
#	get_object_fields => field names, filter
#	[fields => field names, filter NB! deprecated, use "get_object_fields" instead]
#	where => where clause
#	contains => search string, fulltext search as %string%
#	order => <field name> asc|desc
#	start => <starting from row>
#	limit => <count of rows>
#   id => <asset ID> , parent ID will be ignored
#	select => additional select clause (eg "CONCAT(firstname,' ',lastname) AS fullname")
#	on_create => "publish", default "hide"
# ));
function smarty_function_init_assets ($params,&$smarty) {
	global $site, $leht, $template, $class_path;

	$content_template = &$leht->content_template;


	include_once($class_path.'profile.class.php');

	$assets = array();

	##############
	# default values
	extract($params);
	if(!isset($name)) { $name="assets"; }
	if(!isset($parent)) { 
		$parent = $leht->id;
	}
	$parent_id = trim($parent);
	switch ($on_create) {
		case "publish": $publish = 1; break;
		case "hide": $publish = 0; break;
		default: $publish = 0;
	}

	# NB! kui t��p on asset, siis PEAB alati kaasas olema ka profiili ID 
	# (muidu ei oma custom asset m�tet);
	# kui pole profile parameetrit, anda toimetajale veateade ja v�ljuda:
	if(!$profile) { 
		if($site->admin) {
			print "<font color=red><b>Profile parameter is required!</b></font>";
		}
		exit;
	}

	# for language compatibility, replace order with existing db field name
	$order = preg_replace('#\btitle\b#i', "pealkiri", $order);
	$order = preg_replace('#\bdate\b#i', "aeg", $order);

	##############
	## deprecated parameter "fields"
	if(isset($fields) ){ $get_object_fields .= ",".$fields; }
	# put all fields filter into arr
	$get_object_fields_arr = split(",",$get_object_fields);
	$i=0;foreach($get_object_fields_arr as $tmp){ $get_object_fields_arr[$i] = trim($tmp); $i++; }	

	##############
	# put all profile names into arr
	$profile_names = split(",",$profile);

	##############
	# get all profile data from cash
	foreach($profile_names as $profile_name) {
		# profile name is case insensitive
		$profile_name = strtolower($profile_name);

		$profile_def = $site->get_profile(array(name=>$profile_name));
		# sanity check: kui ei leitud sellise nimega profiili, anda toimetajale veateade ja v�ljuda:
		if(!$profile_def[profile_id]) {
			if($site->admin) {
				print "<font color=red><b>Profile '".$profile_name."' not found!</b></font>";
			}
			exit;
		}
		$profile_ids[] = $profile_def[profile_id];
		$profile_arr[$profile_def[profile_id]] = $profile_def;
		
	} 

	if ( ($id || $parent_id)  && sizeof($profile_ids)>0 ) {

	# one object
	if($id){
		##############
		# luua objekt
		$obj = new Objekt(array(
			objekt_id => $id,
		));
		$obj->load_sisu();
		$obj->hit_count = $obj->all['count'];
		$alamlist = new ObjektArray();
		$alamlist->add($obj);

	}
	# if list
	elseif($parent_id) {
		# loop over profile ID-s
		foreach($profile_ids as $profile_id){
			$where_sql[] = "obj_asset.profile_id = '".$profile_id."'";
			$profile_def = unserialize($profile_arr[$profile_id]['data']);
			if (!is_array($profile_def)) { $profile_def = array(); }
			# loop over one profile fields
			foreach($profile_def as $key => $data) {
				if(!$get_object_fields || (sizeof($get_object_fields_arr)>0 && in_array($data[name],$get_object_fields_arr)) ) {
					$select_sql[] = ($data['is_predefined'] ? 'objekt' : 'obj_asset').".".$key;
					if ($contains) {
						$contains_sql[] = ($where ? " AND " : "").$key.$site->db->prepare(" LIKE ?", '%'.$contains.'%');
					}
				}
				if ($where) { # replace technical name with field name
					$where = str_replace($data['name'],$key, $where);
				}
				if ($order) {
					$order = str_replace($data['name'],$key, $order);
				}
				if ($select) {
					$select = str_replace($data['name'],$key, $select);
			}
			} # / loop over one profile fields
		} # / loop over profile ID-s
		
		##############
		# create SQL
		$alamlistSQL = new AlamlistSQL(array(
			parent => $parent_id,
			klass	=> "asset",
			order => $order,
			asukoht	=> $position
		));

		$alamlistSQL->add_select("obj_asset.profile_id");
		
		if(isset($select)){
			$alamlistSQL->add_select($select);
		}
		if (sizeof($select_sql) > 0) {
			$alamlistSQL->add_select(join(", ", $select_sql));
		}
		$alamlistSQL->add_from("LEFT JOIN obj_asset ON objekt.objekt_id=obj_asset.objekt_id");
		
		$alamlistSQL->add_where("(".join(" OR ", $where_sql).")");
		if ($where) {
			$alamlistSQL->add_where($where);
		}
		if (sizeof($contains_sql) > 0) {
			$alamlistSQL->add_where(join(" OR ", $contains_sql));
		}

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
			tyyp_idlist	=> "20",
			profile_id => join(",",$profile_ids), # new nupule anda edasi k�ik profiili ID-d
			asukoht	=> $position,
			publish => $publish
		));	
		} # id or list
		$all_change_fields = array(); 
		$all_selectlist = array(); # array of ID-s which need additional steps to convert ID-s to human readable NAME-s (assets or users/groups)
		
		if(!isset($buttons)) {
			$buttons = array('new', 'edit', 'hide', 'move', 'delete');
		} else {
			$buttons = split(',',$buttons);
		}

		while ($obj = $alamlist->next()) {
			$obj->id = &$obj->objekt_id;
			$obj->class = $obj->all[klass]; # translate it to english
			$obj->buttons = $obj->get_edit_buttons(array(
				'nupud' => $buttons,
				tyyp_idlist => "20", 
				profile_id => join(",",$profile_ids),
				publish => $publish
			));
			
			$obj->date = $site->db->MySQL_ee_short($obj->all['aeg']);
			$obj->datetime = $site->db->MySQL_ee($obj->all['aeg']);
			
			$obj->fdate = substr($obj->all['aeg'], 0, strpos($obj->all['aeg'], ' '));
			$obj->fdatetime = $obj->all['aeg'];

			$obj->last_modified = date('d.m.Y H:i', $obj->all['last_modified']);
			$obj->flast_modified = $obj->all['last_modified'];
		
			$obj->details_link = $site->self.'?id='.$obj->objekt_id;
			$obj->details_title = $site->sys_sona(array(sona => "loe edasi", tyyp=>"kujundus"));


			$obj->title = $obj->pealkiri;
						
			$obj_profile = new Profile(array("id"=>$obj->all['profile_id']));
			// bug #2455
			if(is_array($obj_profile->data)) foreach($obj_profile->data as $profile_key => $profile_data)
			{
				if($profile_key != $profile_data['name'] && !isset($obj->all[$profile_data['name']]))
				{
					$obj->all[$profile_data['name']] =& $obj->all[$profile_key];
				}
			}
			// / bug #2455

			$obj->profile = $obj_profile->name; # name

			#### 1. set profile fields as object attributes
			$obj_profile->set_obj_general_fields(array(
				"obj" => &$obj,
				"get_object_fields" => $get_object_fields
			));

			## gather all selectlist values into one array:
			if(sizeof($obj_profile->selectlist)>0){
				$all_selectlist = array_merge($obj_profile->selectlist,$all_selectlist);
			}
			
			## gather all need_change_obj values into one array:
			# that means object attributes has to be cahnged later. remeMber fields for each obj.
			if(sizeof($obj_profile->change_fields)>0){
				$all_change_fields[$obj->id] = $obj_profile->change_fields;
			}
			#printr($obj_profile->change_fields);

			$obj->created_user_id = $obj->all['created_user_id'];
			$obj->created_user_name = $obj->all['created_user_name'];
			$obj->changed_user_id = $obj->all['changed_user_id'];
			$obj->changed_user_name = $obj->all['changed_user_name'];
			$obj->created_time = $site->db->MySQL_ee($obj->all['created_time']);
			$obj->fcreated_time = $obj->all['created_time'];
			$obj->changed_time = $site->db->MySQL_ee($obj->all['changed_time']);
			$obj->fchanged_time = $obj->all['changed_time'];

			### push
			array_push($assets, $obj);
		}
		//printr($all_selectlist);

		###################
		# get selectlist values - 1 (or 2, if system tables involved) extra sql per function; sql is fast
		if( sizeof($all_selectlist)>0 ) {
			# 2. save array "->asset_names"  human readable NAME-s:
			$obj_profile->get_asset_names(array(
				"selectlist" => $all_selectlist
			));

			#printr($obj_profile->asset_names);
			#printr($all_change_fields);

			###############
			# assign names to attributes
			#echo printr($asset_names);
			###############
			# loop over asset objects and changes attributes values correct
			$i = 0;
			foreach($assets as $tmp) {
				# pointer to array element:
				$obj = &$assets[$i];
				# go on if object values needs changing:
				if( in_array($obj->id, array_keys($all_change_fields)) ) {
					#print "<br>muuta ID: ".$obj->id;	
					### 3. save object rest of attributes
					$obj_profile->set_obj_selectlist_fields(array(
						"obj" => &$obj,
						"change_fields" => $all_change_fields[$obj->id]
					));
				} # if need to change 
				$i++;
			}
		} # if any selectvalue is to get
		# / get selectlist values
		###################
		
	} # if parameters are OK

	$count = $alamlist->rows;
	$counttotal = (isset($limit) ? $alamlist_count->rows : $count);

	##############
	# assign to template variables

	$smarty->assign(array(
		$name => $assets,
		$name.'_newbutton' => $new_button,
		$name.'_counttotal' => $counttotal,
		$name.'_rows' => $counttotal, # alias, for backward compability
		$name.'_count' => $count
	));
}
