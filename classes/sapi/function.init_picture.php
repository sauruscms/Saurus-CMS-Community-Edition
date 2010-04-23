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
# function init_picture
#	"name" => default: picture
#	"id" => id
#	on_create => "publish", default "hide"
#	buttons => default: "new,edit,hide,move,delete"
# ));
function smarty_function_init_picture ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	##############
	# default values
	extract($params);
	if(!isset($name)) { $name="picture"; }
	if(!isset($id)) { 
		$id = $leht->id;
	}
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

	$picture = new Objekt(array(
		objekt_id => $id,
	));
	
	# we can have 2 object classes here: "pilt" and "file"
	# save class of the requested object for later
	$picture_class = $picture->all['klass'];

	##############
	# load variables
	$picture->buttons = $picture->get_edit_buttons(array(
		tyyp_idlist => $picture->all['tyyp_id'],
		nupud => $buttons,
		publish => $publish
	));

	$picture->id = $picture->objekt_id;
	$picture->title = $picture->pealkiri;
	$picture->album_href = $picture->parent_id;

	$parent=$leht->parents->get(1);
	
	$alamlist_count = new Alamlist(array(
			parent => $picture->parent_id,
			klass	=> $picture_class,
			asukoht	=> 0,
			on_counter => 1
	));
		
	#PREVIOUS PICTURE

	$alamlistSQL = new AlamlistSQL(array(
		parent => $picture->parent_id,
		klass	=> $picture_class,
		asukoht	=> 0,
		order => "objekt_objekt.sorteering ASC"
	));
	
	$alamlistSQL->add_where("sorteering>'".$picture->all['sorteering']."'");
	
	$alamlist = new Alamlist(array(
		alamlistSQL => $alamlistSQL,
		start => 0,
		limit => 1,
	));
	#NEXT PICTURE

	$alamlistSQL2 = new AlamlistSQL(array(
		parent => $picture->parent_id,
		klass	=> $picture_class,
		asukoht	=> 0,
	));
	
	$alamlistSQL2->add_where("sorteering<'".$picture->all['sorteering']."'");
	
	$alamlist2 = new Alamlist(array(
		alamlistSQL => $alamlistSQL2,
		start => 0,
		limit => 1,
	));

	$prev_img = $alamlist->next();
	$next_img = $alamlist2->next();

	## 1. img from filesystem (Bug #2316)
	if($picture_class == 'file'){

		$sql = $site->db->prepare("SELECT * FROM obj_file WHERE objekt_id = ?", $picture->objekt_id);
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$result = $sth->fetch();
		
		$result['fullpath'] = preg_replace('#/$#', '', $site->absolute_path).$result['relative_path'];

		//Find out if is picture or not 
		if(preg_match("/(jpeg|png|gif)/",$result['mimetype'])) {
			/* is img */
			if (function_exists ("getimagesize")) {
				list($i_width, $i_height, $i_type, $i_attr) = getimagesize($result['fullpath']);
			} else {
				$i_width = 720; 
				$i_height = 470;
			}

			$picture->image_width = $i_width;
			$picture->image_height = $i_height;


			//Find out if we are in secure or public dir
			if(false !== strpos($result['fullpath'],$site->CONF['secure_file_path'])) {
			/* SECURE */
				$root_dir = $site->CONF['secure_file_path'];
			} else {
			/* PUBLIC */
				$root_dir = $site->CONF['file_path'];
			}

			if(preg_match("/^.*(".str_replace('/','\/',$root_dir).".*)$/",$result['fullpath'],$regs)) {
				$root_dir = preg_replace('/\/[^\/]+$/i','',$regs[1]);
			} else {
				$root_dir = '..'.$root_dir;
			}

			$filepath =  $site->CONF['wwwroot'].$root_dir.'/'.$result['filename'];

			$source = "<img src=\"".$filepath."\" border=\"0\" />";

		} # if img type
	}
	## 2. img form database
	elseif($picture_class == 'pilt') {
		$source = "<img src=\"".$site->CONF['wwwroot'].($site->admin ? "/editor":"")."/image.php?".$picture->objekt_id."\" border=\"0\" />";
		$thumbnail ="<img src=\"".$site->CONF['wwwroot'].($site->admin ? "/editor":"")."/image.php?".$picture->objekt_id."t\" border=\"0\" alt =\"".$picture->all['pealkiri']."\" />";
	}


	$picture->hit_count = $picture->all['count'];

	$picture->created_user_id = $picture->all['created_user_id'];
	$picture->created_user_name = $picture->all['created_user_name'];
	$picture->changed_user_id = $picture->all['changed_user_id'];
	$picture->changed_user_name = $picture->all['changed_user_name'];
	$picture->created_time = $site->db->MySQL_ee($picture->all['created_time']);
	$picture->fcreated_time = $picture->all['created_time'];
	$picture->changed_time = $site->db->MySQL_ee($picture->all['changed_time']);
	$picture->fchanged_time = $picture->all['changed_time'];
	$picture->last_commented_time = $site->db->MySQL_ee($picture->all['last_commented_time']);;
	$picture->comment_count = $picture->all['comment_count'];

	$picture->show_headline = $picture->all['on_pealkiri'];

	##############
	# assign to template variables
	
	$smarty->assign(array(
			$name => $picture,
			$name.'_source' => $source,
			$name.'_thumbnail' => $thumbnail,
			$name.'_next' => $prev_img->objekt_id,
			$name.'_previous' => $next_img->objekt_id
		));
	$smarty->assign($name,$picture);

	//return $picture; Bug #1921 # for {init_object} tag
}
