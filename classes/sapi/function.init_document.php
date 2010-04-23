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
# function init_document
#	id => default: <current page id>
#	name => document
#	buttons => default: "new,edit,hide,move,delete"
#	on_create => "publish", default "hide"
#
# Returns 1 document object
# if id is undefined, current page id is used

function smarty_function_init_document ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	##############
	# default values

	extract($params);
	if(!isset($id)) { 
		$id = $leht->id;
	} 
	if(!isset($name)) { $name="document"; }
	switch ($on_create) {
		case "publish": $publish = 1; break;
		case "hide": $publish = 0; break;
		default: $publish = 0;
	}

	###############
	# action-buttons
	# by default show all 

	if(!isset($buttons)) { 
		$buttons=array("new", "edit", "hide", "move", "delete");
	} else {
		$buttons = split(",",$buttons);
	}
	if(!isset($ttyyp_id)) { $ttyyp_id=0; }

	##############
	# luua objekt
	$obj = new Objekt(array(
		objekt_id => $id,
	));

	
	##############
	# load variables
	$obj->load_sisu();

	$obj->buttons = $obj->get_edit_buttons(array(
		tyyp_idlist	=> 7,
		asukoht	=> $position,
		publish => $publish
	));
	$obj->id = $obj->objekt_id;
	$obj->href = $site->self.'?id='.$obj->objekt_id;
	$obj->is_selected = $leht->parents->on_parent($obj->objekt_id);
	$obj->title = $obj->pealkiri;

	$obj->date = $site->db->MySQL_ee_short($obj->all['aeg']);
	$obj->datetime = $site->db->MySQL_ee($obj->all['aeg']);
	
	$obj->fdate = substr($obj->all['aeg'], 0, strpos($obj->all['aeg'], ' '));
	$obj->fdatetime = $obj->all['aeg'];
	
	$obj->last_modified = date('Y', $obj->all['last_modified']) > 1970? date('d.m.Y H:i', $obj->all['last_modified']) : ''; ## crap data
	$obj->flast_modified = $obj->all['last_modified'];

	$obj->file = $obj->filename = $obj->all['fail'];
	$obj->description = $obj->all['kirjeldus'];
	$obj->size = $obj->all['size'];
	$obj->size_formated = print_filesize($obj->all['size']);
	$obj->author = ($obj->all['author'] ? $obj->all['author'] : $obj->all['autor']);

	$obj->details_link = $site->self.'?id='.$obj->objekt_id;
	$obj->download_link = 'doc.php?'.$obj->objekt_id;
	$obj->class = translate_en($obj->all[klass]); # translate it to english
	$obj->hit_count = $obj->all['count'];

	# added 15.12.2004:
	$obj->comment_link = $site->self.'?id='.$obj->objekt_id.'#comm';
	$obj->comment_title = $site->sys_sona(array(sona => "Kommentaarid", tyyp=>"kujundus"));
	$obj->add_comment_link = $site->self.'?id='.$obj->objekt_id.'#cbox';
	$obj->add_comment_title = $site->sys_sona(array(sona => "Lisa kommentaar", tyyp=>"kujundus"));
#no UI for this:	$obj->forum_allowed = $obj->all[on_foorum];

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

	$smarty->assign($name,$obj);

	//return $obj; # bug #1921 for {init_object} tag
}

