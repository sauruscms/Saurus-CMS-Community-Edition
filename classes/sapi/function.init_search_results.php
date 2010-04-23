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



#########################################################################
# Function init_search_results()
# Use this function
# to customise yours search results
#
function smarty_function_init_search_results($params,&$smarty) {
	global $site, $leht, $template, $class_path;

	//translate url params
	foreach($site->fdat as $key => $value)
	{
		if(!array_key_exists($site->fdat[translate_en($key)], $site->fdat)) $site->fdat[translate_en($key)] =& $site->fdat[$key];
	}
	
	extract($params);
	if(!isset($name)) $name = 'search';
	if(!isset($query)) $query = $site->fdat['query'];

	if(!isset($sites)) $sites = $site->fdat['sites'];

	if(!empty($sites)){

		if(strtolower($sites) == "all"){
			$sql_keel = "SELECT keel_id FROM keel WHERE on_kasutusel=1";
		}else{
			$pre_search_explode=explode(",",strtolower(trim($sites)));
			foreach($pre_search_explode as $k=>$v){
				$pre_search_explode[$k]=trim($v);
			}
			$sql_keel = "SELECT keel_id FROM keel WHERE on_kasutusel=1 AND extension IN ('".implode("','",$pre_search_explode)."')";
			echo $sql;
		}

		$sth = new SQL($sql_keel);
			while($r = $sth->fetch("ASSOC")){
				$keeled[]=$r['keel_id'];
			}

		$keel = implode(",",(array)$keeled);

	}else{
		$keel = $site->keel;
	}



	if(!isset($search_type)) $search_type = $site->fdat['bool'];
	$bool_array=array("or","and","phrase");
	if(!in_array(strtolower($search_type),$bool_array)){
		$search_type = "or";
	}

	if(!isset($exclude)) $exclude = $site->fdat['exclude'];
	if(!isset($section)) $section = $site->fdat['section'];

	if(!isset($last_changed)) $last_changed = $site->fdat['time'];


	if($last_changed !=""){



		if(is_numeric($last_changed) && $last_changed>=1&&$last_changed<=6){

		}elseif(!is_numeric($last_changed)){
			$time_array=array("1 DAY","7 DAY","1 MONTH","3 MONTH","6 MONTH","1 YEAR");
			foreach($time_array as $k=>$v){
				if(strtoupper(trim($last_changed)) == $v){
					$last_changed = $k+1;
				}
			}
			if(!is_numeric($last_changed)){$last_changed="0";}
		}else{
		$last_changed = "0";
		}
	}


	if(!isset($order)) $order = $site->fdat['order'];

	if(!isset($name)) $name = 'search';

	if(!isset($classes))
	{
		foreach($site->object_classes as $class_def)
		{
			if($class_def['on_otsingus']) $classes[] = $class_def['klass'];
		}
	}
	else 
	{
		$classes = explode(',', trim($classes));
		foreach ($classes as $i => $class) $classes[$i] = translate_ee(trim($class));
	}
	
    /*
	if(!isset($buttons))
		$buttons = array('new', 'edit', 'hide', 'move', 'delete');
	else
		$buttons = split(',', $buttons);
	*/
	
	//check cache
	if(is_array($site->cash(array('klass' => 'GET_SEARCH_RESULTS', 'kood' => 'GET_SEARCH_RESULTS'))))
	{
		//read from cache
		$search = $site->cash(array('klass' => 'GET_SEARCH_RESULTS', 'kood' => 'GET_SEARCH_RESULTS'));
	}
	else 
	{
		include_once($class_path.'FulltextSearch.class.php');
		
		$do_boolean = false;
		
		foreach (explode(' ', $query) as $query_word)
		{
			if(preg_match('/\*$/', $query_word))
			{
				$do_boolean = true;
				break;
			}
		}
		
		if(strtolower($search_type) != 'or') $do_boolean = true;

		if($do_boolean || $exclude || $section || $last_changed)
		{
			//boolean search
			if($use_fulltext)
			{
				$search = new FulltextSearchBoolean($query, $exclude, $search_type, $last_changed, $order, $section, $classes, $keel);
			}
			else
			{
				$search = new AdvancedSearch($query, $exclude, $search_type, $last_changed, $order, $section, $classes, $keel);
			}
			
		}
		else 
		{
			//simple search
			$search = new FulltextSearch($query, 0, $classes, ($use_fulltext ? true : false), $keel);
		}
		if($keel!=""){
			$search->execSearch();
		}
		
		//write to cache
		$site->cash(array(klass => 'GET_SEARCH_RESULTS', 'kood' => 'GET_SEARCH_RESULTS', 'sisu' => $search));
	}
	
	$labels = array();
	$k = 0;
	
	foreach($search->getResults() as $class_name => $objects)
	{
		if(in_array($class_name, $classes))
		{
			$labels[$k]->title = $site->sys_sona(array('sona' => 'lipik '.$class_name, 'tyyp' => 'otsing'));
			$labels[$k]->name = translate_en($class_name);
			$labels[$k]->counttotal = count($objects);
			$labels[$k]->results = (isset($start) && $limit ? array_slice($objects, $start, $limit) : $objects);
			$labels[$k]->count = count($labels[$k]->results);
			
			foreach($labels[$k]->results as $i => $obj)
			{
				/* @var $labels[$k]->results[$i] Objekt */
				// copy-paste from init_object
				$labels[$k]->results[$i]->id =& $labels[$k]->results[$i]->objekt_id;
				# kui link
				if($labels[$k]->results[$i]->all['klass'] == 'link')
				{
					# load sisu, et saada vļæ½ļæ½rtused "url" ja "on_uusaken"
					$labels[$k]->results[$i]->load_sisu();
					// eeldab et HTML'is on kasutusel " mitte '
					($labels[$k]->results[$i]->all['url'] && $labels[$k]->results[$i]->all['on_uusaken'] ? $labels[$k]->results[$i]->href = $labels[$k]->results[$i]->all['url'].'" target="_blank' : $labels[$k]->results[$i]->href = $labels[$k]->results[$i]->all['url']);
				}
				# muidu:
				else { $labels[$k]->results[$i]->href = $site->self.'?id='.$labels[$k]->results[$i]->objekt_id; } 

				// if an article mark for excerpt loading
				if($labels[$k]->results[$i]->all['klass'] == 'artikkel')
				{
					$sql = "select substring(sisu_strip, if(locate('".$search->search_words[0]."', sisu_strip) < (".floor($site->CONF['search_result_excerpt_length'] / 2)."), 1, if(locate('".$search->search_words[0]."', sisu_strip) + (".floor($site->CONF['search_result_excerpt_length'] / 2).") > char_length(sisu_strip), char_length(sisu_strip) - ".$site->CONF['search_result_excerpt_length'].", locate('".$search->search_words[0]."', sisu_strip) - (".floor($site->CONF['search_result_excerpt_length'] / 2)."))), ".$site->CONF['search_result_excerpt_length'].") as excerpt from objekt where objekt_id = ".$labels[$k]->results[$i]->all['objekt_id'];
					$result = new SQL($sql);
					$labels[$k]->results[$i]->excerpt = $result->fetchsingle();
				}
				else 
				{
					$labels[$k]->results[$i]->excerpt = '';
				}
				
				$labels[$k]->results[$i]->score =& $labels[$k]->results[$i]->all['fulltext_score'];
				
				$labels[$k]->results[$i]->title =& $labels[$k]->results[$i]->pealkiri;
				$labels[$k]->results[$i]->fdate =& $labels[$k]->results[$i]->all['aeg'];
				$labels[$k]->results[$i]->author =& $labels[$k]->results[$i]->all['author'];
				$labels[$k]->results[$i]->class = translate_en($labels[$k]->results[$i]->all['klass']); # translate it to english
		
				/* maybe fields
				$labels[$k]->results[$i]->is_selected = $leht->parents->on_parent($labels[$k]->results[$i]->objekt_id);
				$labels[$k]->results[$i]->buttons = $labels[$k]->results[$i]->get_edit_buttons(array(
					'nupud' => $buttons,
					//'tyyp_idlist' => $tyyp_idlist,//???
				));
				$labels[$k]->results[$i]->created_user_id =& $labels[$k]->results[$i]->all['created_user_id'];
				$labels[$k]->results[$i]->created_user_name =& $labels[$k]->results[$i]->all['created_user_name'];
				$labels[$k]->results[$i]->changed_user_id =& $labels[$k]->results[$i]->all['changed_user_id'];
				$labels[$k]->results[$i]->changed_user_name =& $labels[$k]->results[$i]->all['changed_user_name'];
				$labels[$k]->results[$i]->created_time =& $site->db->MySQL_ee($labels[$k]->results[$i]->all['created_time']);
				$labels[$k]->results[$i]->fcreated_time =& $labels[$k]->results[$i]->all['created_time'];
				$labels[$k]->results[$i]->changed_time =& $site->db->MySQL_ee($labels[$k]->results[$i]->all['changed_time']);
				$labels[$k]->results[$i]->fchanged_time =& $labels[$k]->results[$i]->all['changed_time'];
				$labels[$k]->results[$i]->last_commented_time =& $site->db->MySQL_ee($labels[$k]->results[$i]->all['last_commented_time']);;
				$labels[$k]->results[$i]->comment_count =& $labels[$k]->results[$i]->all['comment_count'];
				*/
			}
			
			//$labels[] = $label;
			$k++;
		}
	}

	$smarty->assign(array(
		$name => $labels,
		$name.'_counttotal' => $search->search_count,
	));
}
