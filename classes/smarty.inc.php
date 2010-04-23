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


###############################
# Saurus API layer: general
#
###############################

################
# function for sorting array of objects by certain object property
# call: $array = casort($array, "property");
function casort($arr, $var) {
  $tarr = array();
  $rarr = array();
  for($i = 0; $i < count($arr); $i++) {
     $element = $arr[$i];
     $tarr[] = strtolower($element->{$var});
  }
  
  reset($tarr);
  asort($tarr);
  $karr = array_keys($tarr);
  for($i = 0; $i < count($tarr); $i++) {
     $rarr[] = $arr[intval($karr[$i])];
  }
  
  return $rarr;
}
# / function casort
###################


#######################################
# Smarty pre- & post-filter
			
function smarty_prefilter($tpl_source,&$smarty) {
	global $site, $leht, $template;
	$content_template = $leht->content_template;

	if(!empty($template->all[smarty_prefilter])) {
		$blabla = eval($template->all[smarty_prefilter]);
		return $blabla;
	} else if(!empty($content_template->all[smarty_prefilter])) {
		$blabla = eval($content_template->all[smarty_prefilter]);
		return $blabla;
	}
}

function smarty_postfilter($tpl_source,&$smarty) {
	global $site, $leht, $template;
	$content_template = $leht->content_template;

	if(!empty($template->all[smarty_postfilter])) {
		$blabla = eval($template->all[smarty_postfilter]);
		return $blabla;
	} else if(!empty($content_template->all[smarty_postfilter])) {
		$blabla = eval($content_template->all[smarty_postfilter]);
		return $blabla;
	}
}

# /
#######################################

function insert_header() {
    // this function expects $content argument
    extract(func_get_arg(0));
    if(empty($content))
        return;
    header($content);
    return;
}

################################
# function load_current_obj_data
#
# load all possible data for current object
# set all data from table 'objekt' and 'obj_*' as object properties (in english)
function load_current_obj_data () {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	# 1. rubriik
	if($leht->objekt->all['klass']=='rubriik'){
		$leht->objekt->show_toolsicons = $leht->objekt->all['on_printlink'];
		$leht->objekt->is_mailinglist = $leht->objekt->all['on_meilinglist'];
		$leht->objekt->show_subarticles = $leht->objekt->all['on_alamartiklid'];
		$leht->objekt->hide_menu = $leht->objekt->all['on_peida_vmenyy'];
	}
	# 2. uudistekogu
	if($leht->objekt->all['klass']=='kogumik'){
		$leht->objekt->show_date = $leht->objekt->all['on_kp_nahtav'];
	}
	# 3. artikkel
	if($leht->objekt->all['klass']=='artikkel'){
	}

	# K�ik objektid:
	$leht->objekt->buttons = $leht->objekt->get_edit_buttons(array(
		tyyp_idlist	=> $leht->objekt->all[tyyp_id]
	));	
	$leht->objekt->id = $leht->objekt->objekt_id;
	$leht->objekt->title = $leht->objekt->pealkiri;
	
	$leht->objekt->publish_start = $site->db->MySQL_ee($leht->objekt->all['avaldamisaeg_algus']);
	$leht->objekt->fpublish_start = $leht->objekt->all['avaldamisaeg_algus'];

	$leht->objekt->publish_end = $site->db->MySQL_ee($leht->objekt->all['avaldamisaeg_lopp']);
	$leht->objekt->fpublish_end = $leht->objekt->all['avaldamisaeg_lopp'];


#echo printr($leht->objekt->all);

}

#################################
# function function
# 
# Encapsulates "function" into smarty

function sm_function ($tag_arg, &$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	require_once SMARTY_DIR.$smarty->compiler_class . '.class.php';
	
	/*
	 * Get attrs
	*/
	$smarty_compiler = new $smarty->compiler_class;
	$attrs = $smarty_compiler->_parse_attrs($tag_arg);
	
	/*
	 * Assign vars
	*/
	$name = $smarty_compiler->_dequote($attrs['name']);
	unset($smarty_compiler);

	$output = "\n".'$this->register_function("'.$name.'", "'.$name.'");'."\n";
	$output .=  'function '.$name.'($args, &$smarty) {'."\n";
	$output .= '	$this = &$smarty;'."\n";
	$output .= '	if(is_Array($args)) foreach($args as $akey => $avalue) {'."\n";
	$output .= '		if($akey!=\'name\') $this->assign($akey,&$args[$akey]);'."\n";
	$output .= '	}'."\n\n";

	return $output;
}
