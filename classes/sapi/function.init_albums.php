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
# function init_albumlist
#	"name" => default: albumlist
#	"parent" => ID value or comma separated ID values;
#	"start" => <starting from row>
#	"limit" => <count of rows>
#	"order" => <field name> asc|desc
#	"position" => <position number in the page> default: 0
#	"where" =>
#	on_create => "publish", default "hide"
#	thumbnail_type => [first|random], default first
# ));
function smarty_function_init_albums ($params,&$smarty) {
	global $site, $leht, $template, $class_path;

	$content_template = &$leht->content_template;

	$albumlist = array();

	##############
	# default values
	extract($params);
	if(!isset($name)) { $name='albumlist'; }
	if(!isset($thumbnail_type)) { $thumbnail_type='first'; }
	if(!isset($parent)) {
		$parent = $leht->id;
	}
	switch ($on_create) {
		case "publish": $publish = 1; break;
		case "hide": $publish = 0; break;
		default: $publish = 0;
	}
	# for language compatibility, replace with search string existing db field name
	$order = preg_replace('#\btitle\b#i', "pealkiri", $order);
	$order = preg_replace('#\bdate\b#i', "aeg", $order);

	$parent_id = trim($parent);

	if ( $parent_id ) {
		$alamlist = new Alamlist(array(
			parent => $parent_id,
			klass => "album",
			start => $start,
			limit => $limit,
			asukoht	=> $position,
			order => $order,
			where => $where
		));
		# if parameter "limit" is provided then "counttotal" element is needed (shows total rows)
		if(isset($limit)){
			$alamlist_count = new Alamlist(array(
				parent	=> $parent_id,
				klass => "album",
				asukoht	=> $position,
				on_counter => 1
			));
		}

		##############
		# load variables
		$new_button = $alamlist->get_edit_buttons(array(
			tyyp_idlist => "16",
			publish => $publish
		));

		while ($obj = $alamlist->next()) {
			$obj->buttons = $obj->get_edit_buttons(array(
				tyyp_idlist => "16",
				publish => $publish
			));
			$obj->id = &$obj->objekt_id;
			$obj->get_object_href();
			//$obj->href = $site->self.'?id='.$obj->objekt_id;
			$obj->class = translate_en($obj->all[klass]); # translate it to english
			$obj->is_selected = $leht->parents->on_parent($obj->objekt_id);
			$obj->title = $obj->pealkiri;
			
			$obj->date = $site->db->MySQL_ee_short($obj->all['aeg']);
			$obj->datetime = $site->db->MySQL_ee($obj->all['aeg']);
			
			$obj->fdate = substr($obj->all['aeg'], 0, strpos($obj->all['aeg'], ' '));
			$obj->fdatetime = $obj->all['aeg'];

			$obj->last_modified = date('Y', $obj->all['last_modified']) > 1970? date('d.m.Y H:i', $obj->all['last_modified']) : ''; ## crap data
			$obj->flast_modified = $obj->all['last_modified'];

			$obj->details_link = $site->self.'?id='.$obj->objekt_id;
			$obj->details_title = $site->sys_sona(array(sona => "loe edasi", tyyp=>"kujundus"));

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

			### custom conf parameters
            $conf = new CONFIG($obj->all['ttyyp_params']);
            $obj->description=$conf->get('desc');
            if($conf->get('path'))
            {
            	include_once($class_path.'picture.inc.php');
				
				# full relative path to the first/random thumbnail
				$obj->thumbnail=$site->CONF['wwwroot'].'/'.get_images($site->absolute_path.$conf->get('path'),$conf->get('path'),$thumbnail_type);
            }
			#printr($obj->all['ttyyp_params']);
            $obj->thumbnail_size = $conf->get('tn_size'); # in pixels
            $obj->image_size = $conf->get('pic_size'); # in pixels
            $obj->folder_id = $conf->get('folder_id'); # source folder ID
            $obj->folder_path = $conf->get('path'); # source folder path, eg "public/images"

			### / custom conf parameters

			array_push($albumlist, $obj);
		}
	}
	##############
	# assign to template variables
	$count = $alamlist->rows;
	$counttotal = (isset($limit) ? $alamlist_count->rows : $count);

	$smarty->assign(array(
		$name => $albumlist,
		$name.'_newbutton' => $new_button,
		$name.'_counttotal' => $counttotal,
		$name.'_rows' => $counttotal, # alias (NB! was = $alamlist->rows in ver <=3.3.6)
		$name.'_count' => $count
	));

}
