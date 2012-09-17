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
# function init_section
#	level => default: current level
#	parent => parent ID value or comma separated ID values; default: <current page id>
#	name => default: "section"
#	buttons => default: "new,edit,hide,move,delete"
#	position => <position number in the page> default: 9
#	order => <field name> asc|desc
#	start => <starting from row>
#	limit => <count of rows>
#	classes => default: "section" (additional allowed class is "link")
#	on_create => "publish", default "hide"
#
# Returns array of section objects
# if section level is undefined, current level is used

function smarty_function_init_section ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	$section = Array();

	##############
	# default values

	extract($params);
	if(!isset($level) && !isset($parent)) {
		$parent_id = $leht->id;
	}
	elseif(isset($level) && !isset($parent)) {
		$level = 0-$level; /* put '-' at the beginning */
		$tmp = $leht->parents->get($level);
		$parent_id = $tmp->objekt_id;
	}
	elseif(isset($parent)) {
		$parent_id = $parent;
	}
	if(!isset($name)) { $name = "section"; }
	switch ($on_create) {
		case "publish": $publish = 1; break;
		case "hide": $publish = 0; break;
		default: $publish = 0;
	}

	if(!$parent_id)
	{
		$smarty->assign(array(
			$name => $section,
			$name.'_newbutton' => '',
				$name.'_counttotal' => 0,
				$name.'_rows' => 0, # alias for backward compability
				$name.'_count' => 0,
		));
		return;
	}

	###############
	# action-buttons
	# by default show all

	if(!isset($buttons)) {
		$buttons=array("new", "edit", "hide", "move", "delete");
	} else {
		$buttons = split(",",$buttons);
	}
	if(!isset($classes)) { $classes = "section"; }
	# for language compatibility, replace with search string existing db field name
	$order = preg_replace('#\btitle\b#i', "pealkiri", $order);
	$order = preg_replace('#\bdate\b#i', "aeg", $order);

	################
	# position
	# default values for position
	if(!isset($position))
	{
		$position = 0;
	}

	##############
	# alamlist

	# change class values for language compability:

	$tyyp_idlist = $classes;

	$classes = str_replace("section", translate_ee("section"), $classes); # tyyp_id = 1
	$classes = str_replace("link", translate_ee("link"), $classes); # tyyp_id = 3
	# strip out all spaces
	$classes = preg_replace("/(\s)*/","",$classes);
	# for buttons:

	$tyyp_idlist = str_replace("section", "1", $tyyp_idlist); # tyyp_id = 1
	$tyyp_idlist = str_replace("link", "3", $tyyp_idlist); # tyyp_id = 3

	$alamlist = new Alamlist (array(
		parent	=> $parent_id,
		klass	=> ($classes?$classes:"rubriik"),
		asukoht	=> $position,
		order => $order,
		start => $start,
		limit => $limit,
	));

	#$alamlist->debug->print_msg();
	# if parameter "limit" is provided then "counttotal" element is needed (shows total rows)
	if(isset($limit)){
		$alamlist_count = new Alamlist(array(
			parent	=> $parent_id,
			klass	=> ($classes?$classes:"rubriik"),
			asukoht	=> $position,
			on_counter => 1
		));
	}

	##############
	# load variables

	$new_button = $alamlist->get_edit_buttons(array(
		tyyp_idlist => ($tyyp_idlist ? $tyyp_idlist: "1"),
		publish => $publish
	));
	while ($obj = $alamlist->next()) {
		################
		# object parameters

		$obj->id = $obj->objekt_id;
		# kui rubriik:
		if($obj->all[klass] == "rubriik") {
			$obj->get_object_href();
		}
		# kui link
		elseif($obj->all[klass] == 'link') {
			# load sisu, et saada vļæ½ļæ½rtused "url" ja "on_uusaken"
			$obj->load_sisu();

			$objektUrl = $obj->all['url'];
			// replace index.php?id=xxx or ?id=xxx style local url with its alias
			if (!$site->in_editor && $site->CONF['use_aliases'] && $site->CONF['replace_links_with_alias']) {
				$objektUrl = convert_local_link_to_alias($objektUrl);
			}

			/* eeldab et HTML'is on kasutusel " mitte ' */
			($objektUrl && $obj->all['on_uusaken'] ? $obj->href = $objektUrl.'" target="_blank' : $obj->href = $objektUrl);
		}
		$obj->is_selected = $leht->parents->on_parent($obj->objekt_id);
		$obj->title .= $obj->pealkiri;
		$obj->buttons = $obj->get_edit_buttons(array(
			nupud => $buttons,
			tyyp_idlist	=> ($tyyp_idlist ? $tyyp_idlist: "1"),
			publish => $publish
		));
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

		###############
		# push array

		# kui objektil featuur "Peida menļæ½ļæ½s" sisselļæ½litatud (NB! erinev tingimus kui avaldatus)
		# ja pole admin siis mitte lisada objekti massiivi
		if (!$obj->all[is_hided_in_menu] || $site->in_editor){
			array_push($section, $obj);
		}
	}

	$count = sizeof($section);
	$counttotal = (isset($limit) ? $alamlist_count->rows : $count);

	##############
	# assign to template variables

	## This is how we __should__ have assigned the
	## variables !!
	##
	$smarty->assign(array(
		$name => $section,
		$name.'_newbutton' => $new_button,
			$name.'_counttotal' => $counttotal,
			$name.'_rows' => $counttotal, # alias for backward compability
			$name.'_count' => $count
		));

}
