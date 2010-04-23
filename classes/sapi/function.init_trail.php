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
# function init_trail
#	name => default: "trail"
#
# returns array of all parents in object hierarchy up to language section
function smarty_function_init_trail ($params, &$smarty) {
	global $site, $leht;

	##############
	# default values
	extract($params);
	if(!isset($name)) { $name = 'trail'; }

	$trail_objs = array_reverse($leht->parents->list);
	$trail = array();


	foreach ($trail_objs as $i => $item)
	{
		/* skip  the first */
		if($i)
		{
			$obj = $trail_objs[$i]; // $item
			$obj->id = $item->objekt_id;
			$obj->get_object_href();
			$obj->title = $item->pealkiri;
			$obj->hide_in_menu = $item->all['is_hided_in_menu'] ? 1 : 0;

			$trail[] = $obj;
		}
	}

	$smarty->assign($name, $trail);

	return;
}
