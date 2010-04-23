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
# function init_articlelist
#	parent => ID value or comma separated ID values; default: <current page id>
#	name => articlelist
#	buttons => default: "new,edit,hide,move,delete"
#	start => <starting from row>
#	limit => <count of rows>
#	position => <position number in the page> default: 0
#	order => <field name> asc|desc
#	classes => default: "article" (additional allowed class is "link")
#	on_create => "publish", default "hide"
#   start_date => <starting from date> format dd.mm.yyyy
#   end_date => <starting from date> format dd.mm.yyyy
#	profile => <profile name(s)>
#	where => where clause (sql)
#DEPRECATE IT!:	get_object_fields => "any_profile_field"
#	select => additional select clause (eg "CONCAT(firstname,' ',lastname) AS fullname")
#   rows_on_page => <how many rows show on one page>
#
# Returns array of article objects
# if parent is undefined, current page id is used

function smarty_function_init_articles ($params, &$smarty) {
	global $site, $leht, $template, $class_path;

	$content_template = &$leht->content_template;

	$articles = Array();

	##############
	# default values

	extract($params);
    if(!isset($parent)) {
		$parent = $leht->id;
	}
	if(!isset($name)) { $name="articlelist"; }
	# from dd.mm.yyyy to yyyy-mm-dd
	if(isset($start_date)) { $start_date = $site->db->ee_MySQL($start_date); }
	if(isset($end_date)) { $end_date = $site->db->ee_MySQL($end_date); }

	// on_create statements:
	$on_create = explode(',', $on_create);
	// default on_create statements:
	$publish = 0;
	$allow_comments = $site->CONF['default_comments'];

	// cycle statements
	foreach($on_create as $on_create_statement)
	{
		$on_create_statement = trim($on_create_statement);

		switch ($on_create_statement)
		{
			case 'publish': $publish = 1; break;
			case 'hide': $publish = 0; break;
			case 'allow_comments': $allow_comments = 1; break;
		}
	}

	###############
	# action-buttons
	# by default show all

	if(!isset($buttons)) {
		$buttons=array("new", "edit", "hide", "move", "delete");
	} else {
		$buttons = split(",",$buttons);
	}

	###############
	# classes, order, parent

	if(!isset($classes)) { $classes = "article"; }
	# for language compatibility, replace with search string existing db field name
	$order = preg_replace('#\btitle\b#i', "pealkiri", $order);
	$order = preg_replace('#\bdate\b#i', "aeg", $order);

	$where = preg_replace('#\btitle\b#i', "pealkiri", $where);
	$where = preg_replace('#\bdate\b#i', "aeg", $where);

	##############
	# where & start_date, end_date
	if(isset($start_date) && isset($end_date)) {
		$where_add = $site->db->prepare(" objekt.aeg BETWEEN ? AND ? ",
			$start_date,
			$end_date
		);
	}
	elseif(isset($start_date) && !isset($end_date)) {
		$where_add = " objekt.aeg >= '".$start_date."' ";
	}
	elseif(!isset($start_date) && isset($end_date)) {
		$where_add = " objekt.aeg <= '".$end_date."' ";
	}
	######## add it to parameter "where"
	if(trim($where_add)!='') {
		$where = (trim($where)!='' ? $where." AND " : "")." (".$where_add.") ";
	}
	######## where: profile, replace technical name with field name


	# Bug #1583: {init_articles} parameter "where" ei tļæ½ļæ½ta ļæ½ieti
	if(trim($where)!='') {
		$where = " (".$where.") ";
	}
	# if parameter "get_object_fields" is given (may be comma sep.list), then split it to array
	if(isset($get_object_fields)){
		$get_object_fields_arr = split(",",$get_object_fields);
		$i=0;foreach($get_object_fields_arr as $tmp){ $get_object_fields_arr[$i] = trim($tmp); $i++; }
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

		$profile_def = $site->get_profile(array('name'=>$profile_name));
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
	$parent_id = trim($parent);

	if ( $parent_id ) {

		##############
		# alamlist
		# change class values for language compability:

		$tyyp_idlist = $classes;

		$classes = str_replace("article", "artikkel", $classes); # tyyp_id = 2
		$classes = str_replace("link", "link", $classes); # tyyp_id = 3
		# strip out all spaces
		$classes = preg_replace("/(\s)*/","",$classes);
		# for buttons:

		$tyyp_idlist = str_replace("article", "2", $tyyp_idlist); # tyyp_id = 2
		$tyyp_idlist = str_replace("artikkel", "2", $tyyp_idlist); # tyyp_id = 2
		$tyyp_idlist = str_replace("link", "3", $tyyp_idlist); # tyyp_id = 3
		if(sizeof($profile_ids)>0){
		if($where){$where .=" AND obj_artikkel.profile_id in ('".implode("','",$profile_ids)."') ";}else{$where=" obj_artikkel.profile_id in (".implode(",",$profile_ids).") ";}
		}

		##############
		# create SQL

		$alamlistSQL = new AlamlistSQL(array(
				parent => $parent_id,
				klass	=> ($classes?$classes:"artikkel"),
				asukoht	=> $position,
				order => $order,
				where => $where
		));
		#### if profile set => make extra JOIN with content table "obj_artikkel"
		if(sizeof($profile_ids)>0 ) {
			$alamlistSQL->add_select("obj_artikkel.profile_id");
			$alamlistSQL->add_from("LEFT JOIN obj_artikkel ON objekt.objekt_id=obj_artikkel.objekt_id");
		}
		if(isset($select)){
			$alamlistSQL->add_select($select);
		}
		#$alamlistSQL->debug->print_msg();

		###### pages: if paging needed (GET/POST variable "page" or parameter "rows_on_page" should exist ):
		if(isset($site->fdat['page']) || isset($rows_on_page)) {

			if(!$site->fdat['page']) { $tmp_page = 0; }
			else {$tmp_page = intval($site->fdat['page']) - 1; }
			if($tmp_page < 0){ $tmp_page = 0; }

		}
		$alamlist = new Alamlist(array(
			alamlistSQL => $alamlistSQL,
			start => (isset($start)? $start : $tmp_page*$rows_on_page),
			limit => (isset($limit)? $limit : $rows_on_page)
		));
		$alamlist->debug->print_msg();

		# if parameter "limit" is provided then "counttotal" element is needed (shows total rows)
		if(isset($limit) || isset($rows_on_page) ){ # 1 SQL
			$alamlist_count = new Alamlist(array(
				alamlistSQL => $alamlistSQL,
				on_counter => 1
			));
			#$alamlist_count->debug->print_msg();
#			$alamlist_count->sql->debug->print_msg();
		}

		##############
		# load variables
		$new_button = $alamlist->get_edit_buttons(array(
			tyyp_idlist => ($tyyp_idlist ? $tyyp_idlist: "2"),
			profile_id => join(",",$profile_ids), # new nupule anda edasi kļæ½ik profiili ID-d
			publish => $publish,
			'allow_comments' => $allow_comments,
		));

		while ($obj = $alamlist->next()) {
			$obj->buttons = $obj->get_edit_buttons(array(
				tyyp_idlist => ($tyyp_idlist ? $tyyp_idlist: "2"),
				profile_id => join(",",$profile_ids),
				nupud => $buttons,
				asukoht => $position,
				publish => $publish,
				'allow_comments' => $allow_comments,
			));
			$obj->id = $obj->objekt_id;
			if($obj->all[klass] == "artikkel") {
				$obj->get_object_href();
				//$obj->href = $site->self.'?id='.$obj->objekt_id;
			}
			# kui link
			elseif($obj->all[klass] == "link") {
				# load sisu, et saada vļæ½ļæ½rtused "url" ja "on_uusaken"
				$obj->load_sisu();
				$obj->href = $obj->all[url].'" target="'.($obj->all[on_uusaken] ? "_blank" : "_self");
			}
			$obj->is_selected = $leht->parents->on_parent($obj->objekt_id);

			$obj->title .= $obj->pealkiri;

			$obj->date = $site->db->MySQL_ee_short($obj->all['aeg']);
			$obj->datetime = $site->db->MySQL_ee($obj->all['aeg']);

			$obj->fdate = substr($obj->all['aeg'], 0, strpos($obj->all['aeg'], ' '));
			$obj->fdatetime = $obj->all['aeg'];

			$obj->last_modified = date('Y', $obj->all['last_modified']) > 1970? date('d.m.Y H:i', $obj->all['last_modified']) : ''; ## crap data
			$obj->flast_modified = $obj->all['last_modified'];

			$obj->details_link = $site->self.'?id='.$obj->objekt_id;
			$obj->details_title = $site->sys_sona(array(sona => "loe edasi", tyyp=>"kujundus"));

			$obj->printgif = '<a href="'.$obj->href.'&op=print" onClick="avaprintaken(this.href, 600, 400, \'print\'); return false;" target=_blank><img src="'.$site->img_path.'/print_it.gif" border=0 width=19 height=18></a>';
			$obj->printlink = $site->self.'?id='.$obj->objekt_id.'&op=print';

			# added 08.11.2002:
			$obj->comment_link = $site->self.'?id='.$obj->objekt_id.'#comm'; // not compatible with custom templates
			$obj->comment_title = $site->sys_sona(array(sona => "Kommentaarid", tyyp=>"kujundus"));
			$obj->add_comment_link = $site->self.'?id='.$obj->objekt_id.'#cbox'; // not compatible with custom templates
			$obj->add_comment_title = $site->sys_sona(array(sona => "Add", tyyp=>"kujundus"));
			# added 21.01.2003:
			$obj->author = $obj->all[author];

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
			$obj->forum_allowed = $obj->all['on_foorum'];
			$obj->show_headline = $obj->all['on_pealkiri'];


			########## KUI PROFIIL on parmeetrina kaasas JA artiklil on Mļæ½ļæ½RATUD mļæ½ni PROFIIL, siis korja andmed "->" omadustena kokku
			if(sizeof($profile_ids)>0 ) {

			$obj->load_sisu();

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
			
			array_push($articles, $obj);

		} ###### / loop over objects
	}

	$archive_link = $site->self.'?id='.$parent_id.'&op=arhiiv';
	$archive_title = $site->sys_sona(array(sona => "Arhiiv", tyyp=>"kujundus"));
	$count = sizeof($articles);
	$counttotal = (is_object($alamlist_count) ? $alamlist_count->rows : $count);

	##############
	# assign to template variables

	## This is how we __should__ have assigned the
	## variables !!
	##
	$smarty->assign(array(
		$name => $articles,
		$name.'_newbutton' => $new_button,
		$name.'_archive_link' => $archive_link,
		$name.'_archive_title' => $archive_title,
			$name.'_counttotal' => $counttotal,
			$name.'_count' => $count
		));
}
