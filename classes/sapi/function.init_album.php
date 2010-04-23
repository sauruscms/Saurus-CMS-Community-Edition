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
# function init_album
#	"name" => default: album,
#	"id" => id,
#	start => <starting from row>,
#	limit => <count of rows>
#	on_create => "publish", default "hide"
# ));
function smarty_function_init_album ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;
	$albums = array();

	##############
	# default values
	extract($params);
	if(!isset($name)) { $name="album"; }
	switch ($on_create) {
		case "publish": $publish = 1; break;
		case "hide": $publish = 0; break;
		default: $publish = 0;
	}

	$album = new Objekt(array(
		objekt_id => $id,
	));

	$conf = new CONFIG($album->all['ttyyp_params']);
	$col = $conf->get("cols")>0 ? $conf->get("cols") : 3;
	$row = $conf->get("rows")>0 ? $conf->get("rows") : 3;

	$args['num'] = $args['col'];

	$alamlist_count = new Alamlist(array(
		parent => $album->objekt_id,
		klass	=> "pilt",
		asukoht	=> 0,
		on_counter => 1
	));
	
	$alamlist = new Alamlist(array(
		parent => $album->objekt_id,
		klass	=> "pilt",
		asukoht	=> 0,
		start => $start,
		limit => $limit,
	));
	
	$new_button = $alamlist->get_edit_buttons(array(
		tyyp_idlist => "12", 
		publish => $publish
	));
	$edit_button = $album->get_edit_buttons(array(
		tyyp_idlist => "16", 
		publish => $publish
	));
	$title = &$album->pealkiri;

	while ($obj = $alamlist->next()) {
		$obj->load_sisu();
		$obj->buttons = $obj->get_edit_buttons(array(
			tyyp_idlist => "12", 
			publish => $publish
		));
		$obj->get_object_href();
		//$obj->href = $site->self.'?id='.$obj->objekt_id;
		$obj->title = $obj->pealkiri;

		$obj->date = $site->db->MySQL_ee_short($obj->all['aeg']);
		$obj->datetime = $site->db->MySQL_ee($obj->all['aeg']);
		
		$obj->fdate = substr($obj->all['aeg'], 0, strpos($obj->all['aeg'], ' '));
		$obj->fdatetime = $obj->all['aeg'];
		
		$obj->id = $obj->objekt_id;
		$obj->class = translate_en($obj->all[klass]); # translate it to english
		$obj->thumbnail = "<a href=\"".$site->self."?id=".$obj->objekt_id."\"><img src=\"".$site->CONF['wwwroot'].($site->admin ? "/editor":"")."/image.php?".$obj->objekt_id."t\" border=\"0\"></a>";

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

		$obj->forum_allowed = $obj->all['on_foorum'];

		#####push
		array_push($albums, $obj);
	}
	
	##############
	# assign to template variables

	$smarty->assign(array(
		$name => $albums,
		$name.'_newbutton' => $new_button,
		$name.'_editbutton' => $edit_button,
		$name.'_title' => $title,
		$name.'_col' => $col,
		$name.'_row' => $row,
		$name.'_count' => $alamlist_count->rows
	));

}
