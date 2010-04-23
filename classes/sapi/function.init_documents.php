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
# function init_documentlist
#	parent => parent ID value or comma separated ID values; default: <current page id>
#	name => documents
#	start => <starting from row>
#	limit => <count of rows>
#	position => <position number in the page> default: 0
#	order => <field name> asc|desc
#	where => where clause (sql)
#	on_create => "publish", default "hide"
# 
# Returns array of document objects 
# if parent is undefined, current page id is used

function smarty_function_init_documents ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	$documents = Array();
	$parent_ary = Array();

	##############
	# default values

	extract($params);
    if(!isset($parent)) { 
		$parent = $leht->id;
	} 
    if(!isset($name)) { $name="documents"; }
	# for language compatibility, replace with search string existing db field name
	$order = preg_replace('#\btitle\b#i', "pealkiri", $order);
	$order = preg_replace('#\bdate\b#i', "aeg", $order);
	
	switch ($on_create) {
		case "publish": $publish = 1; break;
		case "hide": $publish = 0; break;
		default: $publish = 0;
	}

	$parent_id = trim($parent);
	
	if ( $parent_id ) {

		##############
		# alamlist

	$alamlistSQL = new AlamlistSQL(array(
			parent => $parent_id,
			klass	=> "dokument",
			asukoht	=> $position,
			order => $order,
			where => $where
	));

	$alamlistSQL->add_select(" obj_dokument.tyyp, obj_dokument.mime_tyyp, obj_dokument.fail, obj_dokument.kirjeldus, obj_dokument.autor, obj_dokument.size, obj_dokument.download_type");
	$alamlistSQL->add_from("LEFT JOIN obj_dokument ON objekt.objekt_id=obj_dokument.objekt_id");

	$alamlist = new Alamlist(array(
		alamlistSQL => $alamlistSQL,
		start => $start,
		limit => $limit
	));

#		$alamlist->debug->print_msg();
#		$alamlist->sql->debug->print_msg();

	# if parameter "limit" is provided then "counttotal" element is needed (shows total rows)
	if(isset($limit)){
		$alamlist_count = new Alamlist(array(
			alamlistSQL => $alamlistSQL,
			on_counter => 1
		));
	}	
		
		##############
		# load variables
		$new_button = $alamlist->get_edit_buttons(array(
			tyyp_idlist	=> 7,
			publish => $publish
		));	

		while ($obj = $alamlist->next()) {
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

			$obj->type = $obj->all['tyyp'];
			$obj->mime_type = $obj->all['mime_tyyp'];
			$obj->size_formated = print_filesize($obj->all['size']);
			$obj->author = ($obj->all['author'] ? $obj->all['author'] : $obj->all['autor']);
		
			$obj->details_link = $site->self.'?id='.$obj->objekt_id;
			$obj->download_link = $site->wwwroot.'/doc.php?'.$obj->objekt_id;
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

			array_push($documents, $obj);

		}
	}
	# / loop over all parent id
	#######################

	##################
	# fix objects order, if more than 1 parent_id was given
	# because database sort is not enough for this case
	if(sizeof($parent_ary) > 1) {
		list($order_field, $order_sort) = split(" ", $order);
		# exception for dates: for array sort rename db field date:
		$order_field = str_replace("aeg", "fdate", $order_field);

		# sort objects by required field
		if (trim($order_field)) {
			$documents = casort($documents, $order_field);
		}
		# if sortorder is 'desc', then reverse array
		if (strtolower(trim($order_sort)) == 'desc') {
			$documents = array_reverse($documents);
		}
	}

	# / fix objects order, if more than 1 parent_id was given
	##################

	$count = sizeof($documents);
	$counttotal = (isset($limit) ? $alamlist_count->rows : $count);

	##############
	# assign to template variables

	$smarty->assign(array(
			$name => $documents,
			$name.'_newbutton' => $new_button,
		$name.'_counttotal' => $counttotal,
			$name.'_count' => $count
		));
	}
