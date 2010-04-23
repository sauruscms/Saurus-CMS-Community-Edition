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
# function init_favorites
#	name => default: "favorites"
#	classes => <classes to display>, default: all
#	order => <field name> asc|desc
# 
# Returns array of objects, which are the user's favorites
# -----------------------------------------------------------------------
# RETURNS
# <name>_count' - number of rows,
# <name>->id' - object id,
# <name>->href' - link to object,
# <name>->title' - Objekt title,
# <name>->icon' - SRC for icon to display,

function smarty_function_init_favorites ($params,&$smarty) {
	global $site, $leht, $template, $class_path;

	$content_template = &$leht->content_template;
	
	$objects_arr = Array();

	##############
	# default values

	extract($params);
    if(!isset($name)) { $name = "favorites"; }
	

	##################
	# classes
	$tyyp_idlist = null;
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
	# Get data

	$raw_favorites = $site->user->get_favorites(array(
			tyyp_id => $tyyp_idlist,
			order => $order
		));

	$favorites = array();
	$i = 0;
	if(is_array($raw_favorites)) 
	foreach($raw_favorites as $raw_fav) {
		$favorites[$i]->all = $raw_fav;
		$favorites[$i]->id = $raw_fav['objekt_id_r'];
		$favorites[$i]->title = $raw_fav['pealkiri'];
		$favorites[$i]->href = $site->self.'?id='.$raw_fav['objekt_id_r'];
		if(0 && $raw_fav['tyyp_id'] == 21) {
		/* Special case */
			/* I'm not sure this has worked before
			if (!function_exists ("objManagement")) {
				include_once($class_path."objectmanager.class.php");
			}
			$manager = new objManagement();
			$favorites[$i]->icon = $manager->getThumbnail(array(
						fullpath => $raw_fav['fullpath'],
						size => '16x16',
						show_icons => 1
						));
			unset($manager);
			*/
		} else if($raw_fav['tyyp_id'] == 22) {
		/* Special case 2 */
			$favorites[$i]->icon = $leht->site->CONF['wwwroot'].$leht->site->CONF['styles_path'].'/gfx/icons/16x16/mime/folder_open.png';
		} else {
			$favorites[$i]->icon = $leht->site->CONF['wwwroot'].$leht->site->CONF['styles_path']."/gfx/icons/16x16/mime/knode.png";
		}
		$i++;
	}
	unset($raw_favorites);

	
	##############
	# assign to template variables

	$smarty->assign(array(
		$name => $favorites,
		$name.'_count' => $i
	));
}
