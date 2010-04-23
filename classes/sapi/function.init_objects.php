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
# function init_objects
#	parent => default: <current page id>
#	name => parent ID value or comma separated ID values; default: "objects"
#	buttons => default: "new,edit,hide,move,delete"
#	position => <position number in the page> default: 0
#	order => <field name> asc|desc
#	start => <starting from row>
#	limit => <count of rows>
#	classes => default: <all>
#	on_create => "publish", default "hide"
#	select => additional table fields to return using SELECT SQL syntax.
#	group => additional filtering criteria using WHERE SQL syntax.
#	where => additional table fields to use for grouping using GROUP BY SQL syntax
# 
# Returns array of general objects, by default all classes are returned

function smarty_function_init_objects ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;
	
	$objects_arr = Array();

	##############
	# default values

	extract($params);
    if(!isset($parent_system_alias) && !isset($parent)) { 
		$parent_id = $leht->id;	
	} 
	elseif(isset($parent_system_alias)) {
		//$parent_id = $site->alias(array('key' => $parent_system_alias));
		$parent_id = $site->alias(array(
			'key' => $parent_system_alias,
			'keel' => $site->keel,
		));
	}
	elseif(isset($parent)) {
		$parent_id = $parent;
	}
    
	// if parent_id not found
	if(!$parent_id)
	{
		$parent_id = $leht->id;	
	}
	
	if(!isset($name)) { $name = "objects"; }
	$classes = trim($classes);
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
	# for language compatibility, replace with search string existing db field name
	$order = preg_replace('#\btitle\b#i', "pealkiri", $order);
	$order = preg_replace('#\bdate\b#i', "aeg", $order);

	##################
	# classes
	if($classes) {
		######### translate classes: change class values for language compability
		$transl_class_arr = array();
		foreach(split(",",$classes) as $class) {
			if(trim($class) != '') {
				$transl_class_arr[] = translate_ee($class); # translate it to estonian
			}
		}
#		echo printr($transl_class_arr);
		$classes = join(",",$transl_class_arr);

		######## gather tyyp ID values => to array
		$tyyp_id_arr = array();
		$sql = "SELECT tyyp_id, klass FROM tyyp";
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		while($tmp = $sth->fetch()){
			# if ID found in classes array, then add it:
			if(in_array($tmp['klass'], $transl_class_arr)) {
				$tyyp_id_arr[] = $tmp['tyyp_id'];
			}
		}
#		echo printr($tyyp_id_arr);
		# tyyp_idlist ID numeric values for buttons:
		$tyyp_idlist = join(",",$tyyp_id_arr);
	
	} # if classes parameter provided
	# / classes
	##################



	##############
	# alamlist

	$alamlistSQL = new AlamlistSQL (array(
		parent	=> $parent_id,
		klass	=> $classes,
		asukoht	=> $position,
		order => $order,
	));
	
	if($select) $alamlistSQL->add_select($select);
	
	if($where) $alamlistSQL->add_where($where);
	
	if($group) $alamlistSQL->add_group($site->db->prepare('group by '.$group));
	
	$alamlist = new Alamlist (array(
		'alamlistSQL' => $alamlistSQL,
		start => $start,
		limit => $limit,
	));

	$alamlist->debug->print_msg();

	# if parameter "limit" is provided then "counttotal" element is needed (shows total rows)
	if(isset($limit)){
		$alamlist_count = new Alamlist(array(
			parent	=> $parent_id,
			klass	=> $classes,
			asukoht	=> $position,
			on_counter => 1
		));
	}
	
	##############
	# load variables

	$new_button = $alamlist->get_edit_buttons(array(
		tyyp_idlist	=> $tyyp_idlist,
		publish => $publish
	));	
	while ($obj = $alamlist->next()) {
		################
		# object parameters

		$obj->id = $obj->objekt_id;
		# kui link
		if($obj->all[klass] == "link") {
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
		# muidu:
		else
		{
			$obj->get_object_href();
		} 

		$obj->is_selected = $leht->parents->on_parent($obj->objekt_id);
		$obj->title = $obj->pealkiri;
		$obj->buttons = $obj->get_edit_buttons(array(
			nupud => $buttons,
			tyyp_idlist	=> $tyyp_idlist,
			publish => $publish
		));
		$obj->fdate = $obj->all[aeg];
		$obj->last_modified = date('Y', $obj->all['last_modified']) > 1970? date('d.m.Y H:i', $obj->all['last_modified']) : ''; ## crap data
		$obj->flast_modified = $obj->all['last_modified'];
		$obj->author = $obj->all[author];
		$obj->class = translate_en($obj->all[klass]); # translate it to english

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


		###############
		# push array, in case we don't have "peida menuus" turned on (section objects)

		if (!$obj->all[is_hided_in_menu] || $site->in_editor){
			array_push($objects_arr, $obj);
		}

	}

	$count = sizeof($objects_arr);
	$counttotal = (isset($limit) ? $alamlist_count->rows : $count);

	##############
	# assign to template variables

	$smarty->assign(array(
		$name => $objects_arr,
		$name.'_newbutton' => $new_button,
		$name.'_counttotal' => $counttotal,
		$name.'_count' => $count
	));
}
