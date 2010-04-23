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
# function init_message
#	id => default: <current page id>
#	name => message
#	on_create => "publish", default "hide"
# 
# Returns 1 message object
# if id is undefined, current page id is used

function smarty_function_init_message ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	##############
	# default values

	extract($params);
    if(!isset($id)) { 
		$id = $leht->id;
	} 
    if(!isset($name)) { $name="message"; }
	switch ($on_create) {
		case "publish": $publish = 1; break;
		case "hide": $publish = 0; break;
		default: $publish = 0;
	}

	##############
	# luua objekt
	$obj = new Objekt(array(
		objekt_id => $id,
	));

	##############
	# minna edasi vaid siis kui tegemist on kommentaariga/kirjaga

	if (!$obj->all[klass]=="kommentaar") {
		# error pealkirja or smth
		# assign
		# exit;
	}

	##############
	# load variables

	$obj->buttons = $obj->get_edit_buttons(array(
		tyyp_idlist => 14,
		publish => $publish
	));

	$obj->id = $obj->objekt_id;
	$obj->parent_href = $site->self.'?id='.$obj->parent_id;
	$obj->title = $obj->pealkiri();

	$obj->load_sisu();
	$obj->body = nl2br(htmlspecialchars($obj->all[text]));

	$obj->author = $obj->all[nimi];
	$obj->author_email = $obj->all[email];
	$obj->hide_email = $obj->all[on_peida_email];

	$obj->started = $site->db->MySQL_ee_short($obj->all[aeg]);
	$obj->date = $obj->started; # alternative name
	
	$obj->datetime = $site->db->MySQL_ee($obj->all['aeg']);
	
	$obj->fdate = substr($obj->all['aeg'], 0, strpos($obj->all['aeg'], ' '));
	$obj->fdatetime = $obj->all['aeg'];

	$obj->last_modified = date('Y', $obj->all['last_modified']) > 1970? date('d.m.Y H:i', $obj->all['last_modified']) : ''; ## crap data
	$obj->flast_modified = $obj->all['last_modified'];
	$obj->class = translate_en($obj->all[klass]); # translate it to english

	# parent subject
	$obj->parent_subject = $leht->parents->get(1);
	$obj->parent_subject_id = $obj->parent_subject->objekt_id;
	$obj->parent_subject_title = $obj->parent_subject->pealkiri;

	# parent section
	$obj->parent_section = $leht->parents->get(2);
	$obj->parent_section_id = $obj->parent_section->objekt_id;
	$obj->parent_section_title = $obj->parent_section->pealkiri;
#	$obj->parent_section_href = $obj->parent_section->objekt_id;
	$obj->hit_count = $obj->all['count'];

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

	##############
	# assign to template variables

	$smarty->assign($name,$obj);

	}

