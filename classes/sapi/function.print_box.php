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
# function print_box
#	position => <position number in the page> default: 8 
#	(nr => deprecated, same as 'position', preserved for backward compability)
#	name => <name of variable to loop>, default:box
#	is_custom => <is cusomized box>
#	parent => <objektide parent>
#	url => <objektide url uudistekogu jaoks. Lisatakse linkide lļæ½ppu>
# 
# prints box (or boxes if many) with given position number,
# number is for separating all boxes in page.
#
# Default position numbers which are used in fixed templates are:
#
# - 0: default nr for all content objects in 1.column
# - 2: upper side in menu column (default position is left-upper corner)
# - 4: upper side in menu column (default position is left-down corner)
# - 5: default nr for 1.level sections
# - 6: default nr for all content objects in 2.column
# - 8: right side in content area (default position is right-upper corner)
# - 9: default nr for all sections, except 1.level
#
# NB! It is suggested not to use "0", "2", "5", "9" for box objects (kindel?)

function smarty_function_print_box ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;

	$active_template = &$template;
	if($content_template->smarty) {
		$active_template = &$content_template;
	}

	##############
	# default values

	extract($params);
    if(!isset($nr) && !isset($position)) { 
		$position = 8;
	} 
    if(!isset($name)) { $name="box"; }
	if(!isset($is_custom)) { $is_custom=0; }

	##############
	# print boxes

	$custom_objs = print_kastid(array(
		template => &$active_template,
		asukoht => ($position?$position:$nr),
		is_custom =>($is_custom?"1":"0"),
		parent_id =>$parent,
		on_td => 0,
		url => $url
	));

	##############
	# assign to template variables

	$smarty->assign($name,$custom_objs);

}
