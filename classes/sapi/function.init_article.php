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
# function init_article
#	id => default: <current page id>
#	name => article
#	buttons => default: "new,edit,hide,move,delete"
#	on_create => "publish", default "hide"
#	system_alias (formerly: system_message) => alias of system article
# Returns 1 article object
# if id is undefined, current page id is used

function smarty_function_init_article ($params,&$smarty) {
	global $site, $leht, $template, $class_path;

	$content_template = &$leht->content_template;

	##############
	# default values
	
	extract($params);

	if(!isset($id)) {
		$id = $leht->id;
	}
	
	if($system_message || $system_alias)
	{
		$system_message = ($system_alias ? $system_alias : $system_message);
		$id = $site->alias(array(
			'key' => translate_ee($system_message),
			'keel' => $site->keel,
		));
	}
	
	if(!isset($name)) { $name="article"; }
	
	// on_create statements:
	$on_create = explode(',', $on_create);
	// default on_create statements:
	$publish = 0;
	$allow_comments = $site->CONF['default_comments'];
	
	// cycle statements
	foreach($on_create as $on_create_statement)
	{
		$on_create_statement = trim($on_create_statement);
		
		switch ($on_create_statement)
		{
			case 'publish': $publish = 1; break;
			case 'hide': $publish = 0; break;
			case 'allow_comments': $allow_comments = 1; break;
		}
	}

	# if parameter "get_object_fields" is given (may be comma sep.list), then split it to array
	if(isset($get_object_fields)){
		$get_object_fields_arr = split(",",$get_object_fields);
		$i=0;foreach($get_object_fields_arr as $tmp){ $get_object_fields_arr[$i] = trim($tmp); $i++; }
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

	// system alias given but no such article, can be created under system section
	if(!$id)
	{
		$parent_id = $site->alias('system');
		
		$alamlist = new Alamlist (array(
			'parent'	=> $parent_id,
			'klass'	=> 'artikkel',
			'asukoht'	=> $position,
			'start' => 0,
			'limit' => 1,
		));
		
		$new_button = $alamlist->get_edit_buttons(array(
			'tyyp_idlist' => 2,
			'publish' => $publish,
			'allow_comments' => $allow_comments,
			'sys_alias' => $system_message,
		));
		
		$smarty->assign($name.'_newbutton', $new_button);
		return;
	}
	
	##############
	# luua objekt

	$objSettings = array();
	$objSettings['objekt_id'] = $id;
	$obj = new Objekt($objSettings);
	
	$allObjParents = $obj->get_obj_all_parents($objSettings['objekt_id']);
	
	if(in_array($leht->parents->list[0]->parent_id, $allObjParents)) {
		$objSettings['parent_id'] = $leht->parents->list[0]->parent_id;
		$obj = new Objekt($objSettings);
	}

	##############
	# minna edasi vaid siis kui tegemist on artikliga

	if (!$obj->all[klass]=="artikkel") {
		# error pealkirja or smth
		# assign
		# exit;
	}

	##############
	# load variables

	#PREVIOUS ARTICLE

	$alamlistSQL = new AlamlistSQL(array(
		parent => $obj->parent_id,
		klass	=> "artikkel",
		asukoht	=> 0,
		order => "objekt_objekt.sorteering ASC"
	));
	
	$alamlistSQL->add_where("sorteering>'".$obj->all['sorteering']."'");
	
	$alamlist = new Alamlist(array(
		alamlistSQL => $alamlistSQL,
		start => 0,
		limit => 1,

	));
	#NEXT ARTICLE

	$alamlistSQL2 = new AlamlistSQL(array(
		parent => $obj->parent_id,
		klass	=> "artikkel",
		asukoht	=> 0,
	));
	
	$alamlistSQL2->add_where("sorteering<'".$obj->all['sorteering']."'");
	
	$alamlist2 = new Alamlist(array(
		alamlistSQL => $alamlistSQL2,
		start => 0,
		limit => 1,
	));

	$prev_art = $alamlist->next();
	$next_art = $alamlist2->next();

	$obj->id = $obj->objekt_id;
	$obj->get_object_href();
	$obj->is_selected = $leht->parents->on_parent($obj->objekt_id);
			
	$obj->title = $obj->pealkiri;
	
	$obj->date = $site->db->MySQL_ee_short($obj->all['aeg']);
	$obj->datetime = $site->db->MySQL_ee($obj->all['aeg']);
	
	$obj->fdate = substr($obj->all['aeg'], 0, strpos($obj->all['aeg'], ' '));
	$obj->fdatetime = $obj->all['aeg'];

	$obj->show_headline = $obj->all['on_pealkiri'];
	$obj->details_link = $site->self.'?id='.$obj->objekt_id;
	$obj->details_title = $site->sys_sona(array(sona => "loe edasi", tyyp=>"kujundus"));

	$obj->printgif = '<a href="'.$obj->href.'&op=print" onClick="avaprintaken(this.href, 600, 400, \'print\'); return false;" target=_blank><img src="'.$site->img_path.'/print_it.gif" border=0 width=19 height=18></a>';
	$obj->printlink = $site->self.'?id='.$obj->objekt_id.'&op=print';

	# added 08.11.2002:
	$obj->comment_link = $site->self.'?id='.$obj->objekt_id.'#comm';
	$obj->comment_title = $site->sys_sona(array(sona => "Kommentaarid", tyyp=>"kujundus"));
	$obj->add_comment_link = $site->self.'?id='.$obj->objekt_id.'#cbox';
	$obj->add_comment_title = $site->sys_sona(array(sona => "Add", tyyp=>"kujundus"));
	# existing already by default: $obj->comment_count
	$obj->forum_allowed = $obj->all[on_foorum];
	$obj->last_commented_time = $site->db->MySQL_ee($obj->all['last_commented_time']);;
	$obj->comment_count = $obj->all['comment_count'];

	# added 21.01.2003:
	$obj->author = $obj->all[author];
	$obj->class = translate_en($obj->all[klass]); # translate it to english
	
	$obj->next_id = $next_art->objekt_id;
	$obj->prev_id = $prev_art->objekt_id;

	$obj->hit_count = $obj->all['count'];

	##############
	# load sisu
	$obj->load_sisu();
	if(0 && $context_start) {
		$obj->lead = $context_start.$obj->lyhi->get_text().'</editor:context>';
		$obj->body = $context_start.$obj->sisu->get_text().'</editor:context>';
	} else {
		$obj->lead = $obj->lyhi->get_text();
		$obj->body = $obj->sisu->get_text();
	}

	if (!$site->in_editor && $site->CONF['use_aliases'] && $site->CONF['replace_links_with_alias']) {
		$hostUrl =  (empty($_SERVER['HTTPS']) ? 'http://' :
'https://') . $_SERVER['SERVER_NAME'] . $site->wwwroot . '/';

		//body urls enclosed with "
		preg_match_all('{<a[^>]+href="((' . str_replace('.', '\.', $hostUrl) . '[^>]*|/[^>]*|index.php|)\?([^>]*id=([0-9]+)[^>0-9]*))"[^>]*>.+</a>}Ui', $obj->body, $searchResults, PREG_SET_ORDER);
		
		//body urls enclosed with '
		preg_match_all("{<a[^>]+href='((" . str_replace('.', '\.', $hostUrl) . "[^>]*|/[^>]*|index.php|)\?([^>]*id=([0-9]+)[^>0-9]*))'[^>]*>.+</a>}Ui", $obj->body, $searchResults2, PREG_SET_ORDER);
		$searchResults = array_merge($searchResults, $searchResults2);

		//non-enclosed body urls
		preg_match_all('{<a[^>]+href=((' . str_replace('.', '\.', $hostUrl) . '[^>]*|/[^>]*|index.php|)\?([^>]*id=([0-9]+)[^>\s0-9]*))(\s+[^>]*|)>.+</a>}Ui', $obj->body, $searchResults2, PREG_SET_ORDER);
		$searchResults = array_merge($searchResults, $searchResults2);

		//lead urls enclosed with "
		preg_match_all('{<a[^>]+href="((' . str_replace('.', '\.', $hostUrl) . '[^>]*|/[^>]*|index.php|)\?([^>]*id=([0-9]+)[^>0-9]*))"[^>]*>.+</a>}Ui', $obj->lead, $searchResults2, PREG_SET_ORDER);
		$searchResults = array_merge($searchResults, $searchResults2);

		//lead urls enclosed with '
		preg_match_all("{<a[^>]+href='((" . str_replace('.', '\.', $hostUrl) . "[^>]*|/[^>]*|index.php|)\?([^>]*id=([0-9]+)[^>0-9]*))'[^>]*>.+</a>}Ui", $obj->lead, $searchResults2, PREG_SET_ORDER);
		$searchResults = array_merge($searchResults, $searchResults2);

		//non-enclosed lead urls
		preg_match_all('{<a[^>]+href=((' . str_replace('.', '\.', $hostUrl) . '[^>]*|/[^>]*|index.php|)\?([^>]*id=([0-9]+)[^>\s0-9]*))(\s+[^>]*|)>.+</a>}Ui', $obj->lead, $searchResults2, PREG_SET_ORDER);
		$searchResults = array_merge($searchResults, $searchResults2);

		foreach ($searchResults as $key=>$value) {
			//create an object with the id found in url
			$linkObj = new Objekt(array(
				objekt_id => $value[4],
			));

			$variables = array();
			$separator = (strpos($value[3], '&amp;') !== false ? '&amp;' : '&');
			foreach (explode($separator, $value[3]) as $param) {
				$paramArray = explode('=', $param);
				if ($paramArray[0] != 'id') {
					$variables[] = $param;
				}
			}
			
			if (count($variables) > 0) {
				$param = '?' . implode('&amp;', $variables);
			} else {
				$param = '';
			}

			$replaceValue = str_replace($value[1], (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['SERVER_NAME'] . $linkObj->get_object_href() . $param, $value[0]);

			$obj->lead = str_replace($value[0], $replaceValue, $obj->lead);
			$obj->body = str_replace($value[0], $replaceValue, $obj->body);
		}
	}

	#############
	# buttons (must be after load_sisu(), Bug #1963)
	$obj->buttons = $obj->get_edit_buttons(array(
		tyyp_idlist => $obj->all['tyyp_id'],
		nupud => $buttons,
		ttyyp_id => $ttyyp_id,
		profile_id => $obj->all['profile_id'],
		publish => $publish,
		'allow_comments' => $allow_comments,
	));

	########## KUI artiklil on Mļæ½ļæ½RATUD mļæ½ni PROFIIL, siis korja andmed "->" omadustena kokku
	if($obj->all['profile_id']) {
		#printr($obj->objekt_id.' PROFILE_ID: '.$obj->all['profile_id']);

		include_once($class_path.'profile.class.php');
		
		$obj_profile = new Profile(array("id"=>$obj->all['profile_id']));
		
		#### 1. set profile fields as object attributes
		$obj_profile->set_obj_general_fields(array(
			"obj" => &$obj,
			"get_object_fields" => $get_object_fields
		));	
		###################
		# get selectlist values - 1 extra sql per function; sql is fast
		if(is_array($obj_profile->selectlist)) {
			$obj_profile->selectlist = array_unique($obj_profile->selectlist);
			#printr($obj_profile->selectlist);
		}
		# go on if object values needs changing:
		if( sizeof($obj_profile->selectlist)>0 ) {
			#### 2. save array "->asset_names"  human readable NAME-s:
			$obj_profile->get_asset_names(array(
				"selectlist" => $obj_profile->selectlist
			));

			#printr($obj_profile->asset_names);			
			#printr($obj_profile->change_fields);

			### 3. save object rest of attributes
			#print "<br>muuta ID: ".$obj->id;	
			$obj_profile->set_obj_selectlist_fields(array(
				"obj" => &$obj,
				"change_fields" => $obj_profile->change_fields
			));					
		} # if any selectvalue exist & need to change 
		# / get selectlist values
		###################

	} ####### / profile is set


	$obj->created_user_id = $obj->all['created_user_id'];
	$obj->created_user_name = $obj->all['created_user_name'];
	$obj->changed_user_id = $obj->all['changed_user_id'];
	$obj->changed_user_name = $obj->all['changed_user_name'];
	$obj->created_time = $site->db->MySQL_ee($obj->all['created_time']);
	$obj->fcreated_time = $obj->all['created_time'];
	$obj->changed_time = $site->db->MySQL_ee($obj->all['changed_time']);
	$obj->fchanged_time = $obj->all['changed_time'];

	##############
	# assign to template variables

	$smarty->assign($name,$obj);
	//return $obj; # bug #1921 # for {init_object} tag
}
