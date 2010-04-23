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
# function init_object
#	id => default: <current page id>
#	name => default: "object"
#	buttons => default: "new,edit,hide,move,delete"
# 
# Returns general object, loads entire content

function smarty_function_init_object ($params,&$smarty) {
	global $site, $leht, $template, $class_path;

	$content_template = &$leht->content_template;
	
	##############
	# default values

	extract($params);
    if(!isset($id)) { 
		$id = $leht->id;	
	} 
    if(!isset($name)) { $name = "object"; }

	###############
	# action-buttons
	# by default show all 

    if(!isset($buttons)) { 
		$buttons=array("new", "edit", "hide", "move", "delete");
	} else {
		$buttons = split(",",$buttons);
	}

	##############
	# luua objekt & load sisu
	$obj = new Objekt(array(
		objekt_id => $id,
	));
	$obj->load_sisu();

	################
	# object GENERAL parameters

	$obj->id = $obj->objekt_id;
	$obj->class = translate_en($obj->all[klass]); # translate it to english

	# kui link
	if($obj->all[klass] == "link") {

		$objektUrl = $obj->all['url'];

		// replace index.php?id=xxx or ?id=xxx style local url with its alias
		if (!$site->in_editor && $site->CONF['use_aliases'] && $site->CONF['replace_links_with_alias']) {
			$objektUrl = convert_local_link_to_alias($objektUrl);
		}
		
		/* eeldab et HTML'is on kasutusel " mitte ' */
		($objektUrl && $obj->all['on_uusaken'] ? $obj->href = $objektUrl.'" target="_blank' : $obj->href = $objektUrl);
	}
	# muidu:
	else
	{
		$obj->get_object_href();
	} 

	$obj->title = $obj->pealkiri;
	$obj->buttons = $obj->get_edit_buttons(array(
		nupud => $buttons,
		tyyp_idlist	=> $obj->all['tyyp_id'],
		publish => $publish
	));
	$obj->fdate = $obj->all[aeg];
	$obj->last_modified = date('Y', $obj->all['last_modified']) > 1970? date('d.m.Y H:i', $obj->all['last_modified']) : ''; ## crap data
	$obj->flast_modified = $obj->all['last_modified'];

	$obj->details_link = $obj->href;
	$obj->details_title = $site->sys_sona(array(sona => "loe edasi", tyyp=>"kujundus"));

	$obj->printgif = '<a href="'.$obj->href.'&op=print" onClick="avaprintaken(this.href, 600, 400, \'print\'); return false;" target=_blank><img src="'.$site->img_path.'/print_it.gif" border=0 width=19 height=18></a>';
	$obj->printlink = $site->self.'?id='.$obj->objekt_id.'&op=print';

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

	################
	# ALL values, set as attributes
	foreach($obj->all as $fieldname=>$value){
		$obj->{$fieldname} = $value;
	}
	###############
	# profile values, set as attributes
	$profile_def = $site->get_profile(array(id=>$obj->all['profile_id']));
	if($profile_def[profile_id]) {

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
	}
	
	################
	# object CLASS specific parameters

	########## ARTICLE
	if($obj->class=='article'){
		//$obj = init_article(array("id"=>$obj->id), &$smarty);
		if(!function_exists('smarty_function_init_article')){
			require_once $smarty->_get_plugin_filepath('function', 'init_article');
		}
		smarty_function_init_article(array("id"=>$obj->id, 'name' => $name), &$smarty);
		return;
	}
	########## DOCUMENT
	elseif($obj->class=='document'){
		if(!function_exists('smarty_function_init_document')){
			require_once $smarty->_get_plugin_filepath('function', 'init_document');
		}
		$obj = smarty_function_init_document(array("id"=>$obj->id, 'name' => $name), &$smarty);
		return;
	}
	########## IMAGE
	elseif($obj->class=='image'){
		if(!function_exists('smarty_function_init_picture')){
			require_once $smarty->_get_plugin_filepath('function', 'init_picture');
		}
		$obj = smarty_function_init_picture(array("id"=>$obj->id, 'name' => $name), &$smarty);
		return;
	}
	########## SECTION
	elseif($obj->class=='section'){
		$obj->show_toolicons = $obj->all['on_printlink'];
		$obj->is_mailinglist = $obj->all['on_meilinglist'];
		$obj->show_subarticles = $obj->all['on_alamartiklid'];
		$obj->hide_in_menu = $obj->all['on_peida_vmenyy'];
		$obj->show_date = $obj->all['on_kp_nahtav'];
	}
	########## POLL
	elseif($obj->class=='poll'){
		$obj->is_open = $obj->all['on_avatud'];

		$obj->expires = $obj->all['expires'] ? $site->db->MySQL_ee($obj->all['expires']): '';
		$obj->fexpires = $obj->all['expires'] ? $obj->all['expires'] : '';
		$obj->is_expired = $obj->all['expires'] && (strtotime($obj->all['expires']) > 0 &&  strtotime($obj->all['expires']) < time() ) ? 1 : 0;
#printr(strtotime($obj->all['expires']));
		######### CHECK voting
		# 1) IP-based gallup
		if ($site->CONF[gallup_ip_check]==1){
			$sql = $site->db->prepare("SELECT COUNT(gi_id) FROM gallup_ip WHERE objekt_id=? AND ip LIKE ?",$obj->id, $_SERVER["REMOTE_ADDR"] );
			$sth = new SQL($sql);
			$count = $sth->fetchsingle();
		} 
		# 2) cookie based gallup
		else if ($site->CONF[gallup_ip_check]==2 && $site->cookie["gallup"][$obj->id]==1){
			$count = 1;
		} 
		# 3) user based gallup (only logged in users)
		else if ($site->CONF[gallup_ip_check]==3){
			$sql = $site->db->prepare("SELECT COUNT(gi_id) FROM gallup_ip WHERE objekt_id=? AND user_id=?",$obj->id,$site->user->user_id);
			$sth = new SQL($sql);
			# count=1: not logged in users are not allowed to vote:
			$count = $site->user->user_id ? $sth->fetchsingle() : 1;
		} else { 
			$count = 0;
		}
		######### / CHECK voting

		### is_voted: if user is voted this poll or not, 1/0
		$obj->is_voted = $count; # not voted
		
		### answers
		$sql = $site->db->prepare("SELECT * FROM gallup_vastus WHERE objekt_id=?", $obj->id);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$obj->answers = array();
		$obj->answers_count = 0;
		while ($vastus = $sth->fetch()) {
			unset($tmp);
			$tmp->id = $vastus[gv_id];
			$tmp->answer = $vastus[vastus];
			$tmp->title = $vastus[vastus];
			$tmp->count = $vastus[count];

			$obj->answers[$vastus[gv_id]] = $tmp;

			$obj->answers_count += $vastus[count];
		}
		### / answers

		### voters (if not anonymous poll)
		if(!$obj->is_anonymous){

			$sql = $site->db->prepare("SELECT gallup_ip.*, users.firstname, users.lastname
				FROM gallup_ip
					LEFT JOIN users ON users.user_id = gallup_ip.user_id
				WHERE objekt_id=?",
			$obj->id);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			$obj->voters = array();
			while ($vastus = $sth->fetch()) {
				unset($tmp);
				$tmp->id = $vastus[gi_id];
				$tmp->answer_id = $vastus[gv_id];
				$tmp->ip = $vastus[ip];
				$tmp->user_id = $vastus[user_id];
				$tmp->user_firstname = $vastus[firstname];
				$tmp->user_lastname = $vastus[lastname];
				$tmp->time = $site->db->MySQL_ee($vastus[vote_time]);
				$tmp->ftime = $vastus[vote_time];

				$obj->voters[$vastus[gi_id]] = $tmp;
			}
		} # if not anonymous poll


		### / voters
	} 
	########## / POLL
	########## ALBUM
	elseif($obj->class=='album') {
        // add album config atributes
		
		$conf = new CONFIG($obj->all['ttyyp_params']);

        $obj->description=$conf->get('desc');
        $obj->thumbnail_size = $conf->get('tn_size'); # in pixels
        $obj->image_size = $conf->get('pic_size'); # in pixels
        $obj->folder_id = $conf->get('folder_id'); # source folder ID
        $obj->folder_path = $conf->get('path'); # source folder path, eg "public/images"
	}
	########## / ALBUM
	
	##############
	# assign to template variables
	$smarty->assign($name,$obj);

}
# / function init_object
#################################
