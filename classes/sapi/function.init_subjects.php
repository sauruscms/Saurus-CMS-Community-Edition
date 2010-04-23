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
# function init_subjects
#	parent => parent ID value or comma separated ID values; default: <current page id>
#	name => default: "subject"
#	subjectdetail_tpl
#	on_create => "publish", default "hide"
#	start => <starting from row>
#	limit => <count of rows>
# 
# Returns array of subject objects 
# if subject parent is undefined, current page id is used

function smarty_function_init_subjects ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	$subjects = Array();

	##############
	# default values

	extract($params);
    if(!isset($parent)) { 
		$parent_id = $leht->id;	
	} 
	else {
		$parent_id = $parent;
	}
    if(!isset($name)) { $name = "subject"; }
	switch ($on_create) {
		case "publish": $publish = 1; break;
		case "hide": $publish = 0; break;
		default: $publish = 0;
	}

	##################
	# find template id by parameter subjectdetail_tpl (= template name)
	$sth = new SQL("SELECT ttyyp_id FROM templ_tyyp WHERE nimi = '".$subjectdetail_tpl."' AND ttyyp_id >= '1000' LIMIT 1");
	$subjectdetail_tpl_id = $sth->fetchsingle();
	# if dynamical template not found, use fixed template 1
	if(!$subjectdetail_tpl_id) {
		$subjectdetail_tpl_id = 1;  # default, templ1.php
	}

	##############
	# alamlist

	$alamlist = new Alamlist (array(
		parent	=> $parent_id,
		klass	=> "teema",
		start => $start,
		limit => $limit
	));

	##############
	# load variables

	$new_button = $alamlist->get_edit_buttons(array(
		tyyp_idlist	=> 15,
		publish => $publish
	));	

	while ($obj = $alamlist->next()) {
		$obj->id = $obj->objekt_id;
		$obj->detail_href = $site->self.'?'.(isset($content_template)? 'c_tpl':'tpl').'='.$subjectdetail_tpl_id.'&id='.$obj->objekt_id;

		$obj->title = $obj->pealkiri;
		$obj->buttons = $obj->get_edit_buttons(array(
			tyyp_idlist	=> 15,
			publish => $publish
		));	

		$alamlist_count = new Alamlist(array(
			parent => $obj->objekt_id,
			klass	=> "kommentaar",
			asukoht	=> 0,
			on_counter => 1
		));
		$obj->message_count = $alamlist_count->rows;

		$obj->started = $site->db->MySQL_ee_short($obj->all[aeg]);

		$alamlist2 = new Alamlist(array(
			parent => $obj->objekt_id,
			klass	=> "kommentaar",
			asukoht	=> 0,
			start => 0,
			limit => 1
		));
		$last = $alamlist2->next();

		$obj->last_message = $last?$site->db->MySQL_ee_short($last->all[aeg]):"&nbsp;";

		$obj->last_modified = date('Y', $obj->all['last_modified']) > 1970? date('d.m.Y H:i', $obj->all['last_modified']) : ''; ## crap data
		$obj->flast_modified = $obj->all['last_modified'];
		$obj->class = translate_en($obj->all[klass]); # translate it to english

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


		array_push($subjects, $obj);
	}

	$count = sizeof($subjects);

	##############
	# assign to template variables

	$smarty->assign(array(
			$name => $subjects,
			$name.'_newbutton' => $new_button,
			$name.'_count' => $count
		));
	}
