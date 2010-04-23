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
# FUNCTION init_mailinglist
#	name => mailinglist
#   name_separator => default: ' &gt; ' (>)
# 
# Returns array of section objects 
# which are included in mailinglist

function smarty_function_init_mailinglist ($params,&$smarty) {
	global $site, $leht, $class_path;

	##############
	# default values

	extract($params);
	if(!isset($name)) { $name="mailinglist"; }
	if(!isset($name_separator)) { $name_separator=" &gt; "; }

	##################
	# get user mailinglists
	$subscribed_mailinglist = array();
	if ($site->user) {
		$subscribed_mailinglist = $site->user->get_mailinglist();
	}

	##################
	# get ALL available mailinglist sections 
	$sql = $site->db->prepare("SELECT objekt.objekt_id FROM obj_rubriik,objekt WHERE obj_rubriik.objekt_id=objekt.objekt_id AND objekt.on_avaldatud='1' AND obj_rubriik.on_meilinglist = '1' AND objekt.keel=?",$site->keel);
	$sth = new SQL($sql);

	# put all section IDs into array
	while ($obj = $sth->fetch()) {
		$all_mailinglist[] = $obj[objekt_id];
	}
	# if sections found
	if(sizeof($all_mailinglist)>0){

		##################
		# get all sections (privilege check is already done in rubloetelu class)

		#$timer = new Timer();
		include_once($class_path."rubloetelu.class.php");
		$rubs = new RubLoetelu(array(
			keel => $site->keel, 
			exclude_id => $site->alias("rub_home_id"),
		));
		$topparents = $rubs->get_loetelu();
		asort($topparents);
		#print "TIME:".$timer->get_aeg();
		#$rubs->debug->print_msg();

		##################
		# loop over all sections
		foreach ($topparents as $obj_id=>$obj_name) {
			# if section is not HOME AND is included in mailinglist then print row
			if ($obj_id != $site->alias("rub_home_id") && in_array($obj_id, $all_mailinglist)){
				$obj_name = str_replace("->",$name_separator,$obj_name);

				$obj = new stdClass();
				$obj->id = $obj_id;
				$obj->title = $obj_name;
				# set user subsciption info 
				if(in_array($obj_id,$subscribed_mailinglist)) {
					$obj->user_subscribed = 1;
				}
				else {
					$obj->user_subscribed = 0;
				}
				$mailinglists[] = $obj;
			} 
		} # foreach
	} # kui leidub meililiste

	$count = sizeof($all_mailinglist);

	##############
	# assign to template variables
	$smarty->assign(array(
			$name => $mailinglists,
			$name.'_count' => $count
		));

}
# / FUNCTION init_mailinglist
#################################
