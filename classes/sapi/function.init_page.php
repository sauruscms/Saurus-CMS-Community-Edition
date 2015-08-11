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
# function init_page
#
# global variables assignments - 
# assigns all variables necessary for all templates (title, encoding, etc)
# will be called automatically with every template loading

function smarty_function_init_page(&$smarty, $params)
{
	global $site, $leht, $template, $class_path;
	include_once($class_path.'extension.class.php');

	$content_template = $leht->content_template;

	# load all possible data for current object
#	load_current_obj_data();

	## This is how we __should__ have assigned the 
	## variables !!
	##

	$title = ($leht->objekt->all['meta_title'] ? $leht->objekt->all['meta_title'] : $leht->meta[title]);
	$keywords = ($leht->objekt->all['meta_keywords'] ? $leht->objekt->all['meta_keywords'] : $leht->meta[keywords]);
	$description = ($leht->objekt->all['meta_description'] ? $leht->objekt->all['meta_description'] : $leht->meta[description]);
	$c_tpl = (is_object($leht->content_template) ? $leht->content_template->ttyyp_id : $site->fdat['c_tpl']);

		$curr_objekt = new Objekt(array(
			objekt_id => $site->alias(array(
				"key" => "rub_home_id",
				"keel" => $site->keel
			))
		));
	
	$conf = new CONFIG($curr_objekt->all["ttyyp_params"]);

	foreach ($conf->CONF as $k=>$v){
		if($k!="page_end_html"){
			if($k == "site_name"){
				$site_name=$v;
			}
			if($k == "slogan"){
				$site_slogan=$v;
			}
		}
	}

	$sql = $site->db->prepare("SELECT locale, nimi FROM keel WHERE on_kasutusel=1 AND keel_id=?", $site->glossary_id);
	$sth = new SQL($sql);
	$glossary = $sth->fetch("ASSOC");

	// registered user should not be able to use {$in_editor} and {$admin} tags, only those with update permission
	if($site->in_editor && $leht->objekt->permission['U']) $in_editor = 1;
	else $in_editor = 0;
	
	// create template object to be passed as global variable
	$template_obj = new stdClass();
	$template_obj->all = $template->all;
	$template_obj->title = $template->all['nimi'];
	$template_obj->name = $template->all['nimi'];
	$template_obj->id = $template->all['ttyyp_id'];
	$template_obj->op = $template->all['op'];
	$template_obj->extension = $template->all['extension'];
	
	$smarty->assign(array(
		'id' => $leht->id,
		'title' => $title,
		'meta_title' => $title,
		'meta_keywords' => $keywords,
		'meta_description' => $description,
		'encoding' => $leht->site->encoding,
		'img_path' => $leht->site->CONF[wwwroot].$leht->site->CONF[img_path],
		'adm_img_path' => $leht->site->CONF[wwwroot].$leht->site->CONF[adm_img_path],
		'custom_img_path' => $leht->site->CONF[wwwroot].$leht->site->CONF[custom_img_path],
		'styles_path' => $leht->site->CONF[wwwroot].$leht->site->CONF['styles_path'],
		'js_path' => $leht->site->CONF[wwwroot].$leht->site->CONF[js_path],
		'file_path' => $leht->site->CONF[wwwroot].$leht->site->CONF[file_path],
		'self' => $site->self,
		'url' => $site->URI,
		'protocol' => $leht->site->CONF[protocol],
		'wwwroot' => $leht->site->CONF[wwwroot],
		'hostname' => $leht->site->CONF[hostname],
		'current_level' => $leht->level,
		'op' => $site->fdat[op],
		'tpl' => (is_object($template) ? $template->ttyyp_id : $site->fdat['tpl'] ),
		'c_tpl' => $c_tpl,
		'ext_id' => $site->fdat[ext_id], # DEPRECATED
		'admin' => $in_editor, # DEPRECATED
		'in_editor' => $in_editor,
		'user' => ($site->user? 1 : 0),
		'userdata' => $site->user,
		'current_obj' => $leht->objekt,
		'lang' => $site->extension, # DEPRECATED
		'site_extension' => $site->extension,
		'locale' => $glossary["locale"],
		'glossary' => $glossary["nimi"],
		'current_class' => translate_en($leht->objekt->all['klass']),
		'content_tpl' => $leht->content_template->all[nimi],
		'page_tpl' => $template->all[nimi],
		'prod_id' => eregi_replace("[^0-9]","",$site->fdat[prod_id]),
		'form_error' => $site->fdat['form_error'],
		'form_data' => $site->fdat['form_data'],
		'conf' => $site->CONF,
		'template' => $template_obj,
		'site_name' => $site_name,
		'site_slogan' => $site_slogan,
	));

	##Registreeri prefilter
	if(!empty($template->all[smarty_prefilter])) {
		$smarty->register_prefilter('smarty_prefilter');
	} else if(!empty($content_template->all[smarty_prefilter])) {
		$smarty->register_prefilter('smarty_prefilter');
	}

	##Registreeri postfilter
	if(!empty($template->all[smarty_postfilter])) {
		$smarty->register_postfilter('smarty_postfilter');
	} else if(!empty($content_template->all[smarty_postfilter])) {
		$smarty->register_postfilter('smarty_postfilter');
	}

	#####################################
	# load all EXTENSION CONFIGS as SAPI variable $EXTENSION_NAME, and load filters.
	$pre_filters_for_page_templates = ($params['on_page_templ'] ? array('page_end_html', 'editor_toolbar', 'context_menu_init',) : array());
	$autoload_filters = array('pre' => $pre_filters_for_page_templates, 'output' => array(), 'post' => array(),);
	
	foreach (get_extensions() as $extension)
	{

		$EXTENSION =& load_extension_config($extension);
		$smarty->assign($extension['name'], $EXTENSION); # assign to SAPI variable $extension_name
		$smarty->assign(strtoupper($extension['name']), $EXTENSION); # assign to SAPI variable $EXTENSION_NAME
		// post, pre, outputfilter loading and registering
		if(is_array($EXTENSION['smarty_filters']))
		{
			foreach($EXTENSION['smarty_filters'] as $template_id => $filters)
			{
				if($template_id == 'all' || ($params['on_page_templ'] && $template_id == $template->ttyyp_id) || (!$params['on_page_templ'] && $template_id == $content_template->ttyyp_id))
				{
					foreach(array_keys($autoload_filters) as $filter_type)
					{
						if(is_array($filters[$filter_type])) $autoload_filters[$filter_type] = array_unique(array_merge($autoload_filters[$filter_type], $filters[$filter_type]));
					}
				}
			}
		}
		// /post, pre, outputfilter loading and registering
	}
	$smarty->autoload_filters = $autoload_filters;

#	$smarty->autoload_filters['pre'][] = 'foobar';
	//printr($smarty->autoload_filters);
	# / load all EXTENSION CONFIGS as SAPI variable $EXTENSION_NAME, and load filters.
	#####################################
}
