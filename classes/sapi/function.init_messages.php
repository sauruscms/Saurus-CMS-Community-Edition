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
# function init_messages
#	parent => parent ID value or comma separated ID values; default: <current page id>
#	name => default: "messages"
#	order => <field name> asc|desc
#	messagedetail_tpl 
#	start => <starting from row>
#	limit => <count of rows>
#	on_create => "publish", default "hide"
#   start_date => <starting from date> format dd.mm.yyyy
#   end_date => <starting from date> format dd.mm.yyyy
#	on_create => "publish", default "hide"
#   rows_on_page => <how many rows show on one page> 
#	where => where clause (sql)
# 
# Returns array of  objects 
# if subject parent is undefined, current page id is used

function smarty_function_init_messages ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	$messages = Array();

	##############
	# default values

	extract($params);
	if(!isset($parent)) { 
		$parent_id = $leht->id;	
	} 
	else {
		$parent_id = $parent;
	}
	if(!isset($name)) { $name = "messages"; }
	if(!isset($order)) { 
		$order = "aeg DESC, objekt_id DESC"; 
		$default_order=1;
	} else {
		$order = preg_replace('#\btitle\b#i', "pealkiri", $order);
		$order = preg_replace('#\bdate\b#i', "aeg", $order);
	}
	switch ($on_create) {
		case "publish": $publish = 1; break;
		case "hide": $publish = 0; break;
		default: $publish = 0;
	}
	# from dd.mm.yyyy to yyyy-mm-dd 
	if($start_date) { $start_date = $site->db->ee_MySQL($start_date); }
	if($end_date) { $end_date = $site->db->ee_MySQL($end_date); }
	##############
	# where & start_date, end_date
	if($start_date && $end_date) {
		$where_add = " objekt.aeg BETWEEN '".$start_date."' AND '".$end_date."' ";
	}
	elseif($start_date && !$end_date) {
		$where_add = " objekt.aeg >= '".$start_date."' ";
	}
	elseif(!$start_date && $end_date) {
		$where_add = " objekt.aeg <= '".$end_date."' ";
	}
	######## add it to parameter "where"
	if(trim($where_add)!='') {
		$where = (trim($where)!='' ? $where." AND " : "")." (".$where_add.") ";
	}
	if(trim($where)!='') {
		$where = " (".$where.") ";
	}

	##################
	# find template id by parameter messagedetail_tpl (= template name)
	$sth = new SQL("SELECT ttyyp_id FROM templ_tyyp WHERE nimi = '".$messagedetail_tpl."' AND ttyyp_id >= '1000' LIMIT 1");
	$messagedetail_tpl_id = $sth->fetchsingle();

	# if dynamical template not found, use fixed template 1
	if(!$messagedetail_tpl_id) {
		$messagedetail_tpl_id = 1; # default, templ1.php
	}

	##############
	# alamlist counter
	# kirjade arv teemas

	$alamlist_count = new Alamlist(array(
		parent => $parent_id,
		klass	=> "kommentaar",
		asukoht	=> 0,
		where => $where,
		on_counter => 1
	));

		###### pages: if paging needed (GET/POST variable "page" or parameter "rows_on_page" should exist ):
		if(isset($site->fdat['page']) || isset($rows_on_page)) {

			if(!$site->fdat['page']) { $tmp_page = 0; }
			else {$tmp_page = intval($site->fdat['page']) - 1; }
			if($tmp_page < 0){ $tmp_page = 0; }

			$rows_on_page = isset($rows_on_page) ? $rows_on_page : $site->CONF['komment_arv_lehel'];
		}

	##############
	# alamlist

	$alamlist = new Alamlist(array(
		parent => $parent_id,
		klass	=> "kommentaar",
		asukoht	=> 0,
		on_alampuu_kontroll => 14,
		start => (isset($start)? $start : $tmp_page*$rows_on_page),
		limit => (isset($limit)? $limit : $rows_on_page),
		order => $order,
		from => $from,
		where => $where,
		select_strip_fields => ($where ? 1 : 0)
	));

	##############
	# load variables

	$new_button = $alamlist->get_edit_buttons(array(
		tyyp_idlist	=> 14,
		publish => $publish
	));	

	while ($obj = $alamlist->next()) {
		$obj->id = $obj->objekt_id;
		$obj->detail_href = $site->self.'?'.(isset($content_template)? 'c_tpl':'tpl').'='.$messagedetail_tpl_id.'&id='.$obj->objekt_id;

		$obj->parent_href = $site->self.'?id='.$obj->parent_id;
		$obj->title = $obj->pealkiri();

		$obj->load_sisu();
		$obj->body = nl2br(htmlspecialchars($obj->all[text]));

		$obj->author = $obj->all[nimi];
		$obj->author_email = $obj->all[email];
		$obj->hide_email = $obj->all[on_peida_email];

		$obj->buttons = $obj->get_edit_buttons(array(
			tyyp_idlist	=> 14,
			publish => $publish
		));	

		$obj->last_modified = date('Y', $obj->all['last_modified']) > 1970? date('d.m.Y H:i', $obj->all['last_modified']) : ''; ## crap data
		$obj->flast_modified = $obj->all['last_modified'];

		### CHECK & TEST: selle pļæ½ringu vļæ½ib siit maha vļæ½tta alates featuurist "objekt.comment_count"
		# praegu ei vļæ½ta, sest pole aega testida (merle, 8 juuli 2005)
		$alamlist_count2 = new Alamlist(array(
			parent => $obj->objekt_id,
			klass	=> "kommentaar",
			asukoht	=> 0,
			on_counter => 1
		));
		$obj->message_replies = $alamlist_count2->rows;
		$obj->message_count = $alamlist_count->rows;

		$obj->last_commented_time = $site->db->MySQL_ee($obj->all['last_commented_time']);;
		$obj->comment_count = $obj->all['comment_count'];


		########################
		# Generate delete link
		# Only site users who wrote comment will see delete link, rules are following:
		# 1) delete comments to comments that are LAST in conversation
		# 2) delete comments in topics, that have no answers
		
		if ($site->user->user_id == $obj->all['kasutaja_id'] && $alamlist_count2->rows == 0 && ($leht->objekt->all['klass'] == "teema" || ($leht->objekt->all['klass'] != "teema" && $alamlist->index == 0 && $default_order))){
			$obj->delete = "<a href=\"javascript:avapopup('com_del.php?id=".$obj->objekt_id."','delete','413','108');\">".$site->sys_sona(array("sona" => "Kustuta", "tyyp"=>"Editor"))."</a>";
		}

		#######################

		$obj->started = $site->db->MySQL_ee_short($obj->all[aeg]);
		$obj->date = $obj->started; # alternative name

		$obj->datetime = $site->db->MySQL_ee($obj->all['aeg']);
		
		$obj->fdate = substr($obj->all['aeg'], 0, strpos($obj->all['aeg'], ' '));
		$obj->fdatetime = $obj->all['aeg'];
		
		$obj->class = translate_en($obj->all[klass]); # translate it to english

		$alamlist2 = new Alamlist(array(
			parent => $obj->objekt_id,
			klass	=> "kommentaar",
			asukoht	=> 0,
			start => 0,
			limit => 1
		));
		$last = $alamlist2->next();
		# viimane vastus kirjale
		$obj->last_message = $last?$site->db->MySQL_ee_short($last->all[aeg]):"&nbsp;";

		$obj->created_user_id = $obj->all['created_user_id'];
		$obj->created_user_name = $obj->all['created_user_name'];
		$obj->changed_user_id = $obj->all['changed_user_id'];
		$obj->changed_user_name = $obj->all['changed_user_name'];
		$obj->created_time = $site->db->MySQL_ee($obj->all['created_time']);
		$obj->fcreated_time = $obj->all['created_time'];
		$obj->changed_time = $site->db->MySQL_ee($obj->all['changed_time']);
		$obj->fchanged_time = $obj->all['changed_time'];


		array_push($messages, $obj);
	}

	$count = sizeof($messages);
	$counttotal = $alamlist_count->rows;

	##############
	# assign to template variables

	$smarty->assign(array(
			$name => $messages,
			$name.'_newbutton' => $new_button,
			$name.'_counttotal' => $counttotal,
			$name.'_count' => $count
		));
	}
