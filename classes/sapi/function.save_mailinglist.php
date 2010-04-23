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



function smarty_function_save_mailinglist($params, &$smarty)
{
	global $site;
	
	extract($params);
	
	$user_id = (int)$user;
	
	if(!$user_id) $user_id = $site->user->user_id;
	
	###################
	# salvestame mailinglistide valik
	# muide, kui kasutaja e-mail salvestati tühjana, siis ei lisata ka ühtegi meili-listi ja kustutatakse vanadki
	if ($user_id) {

		// get user data
		$sql = $site->db->prepare('select * from users where user_id = ?', $user_id);
		$result = new SQL($sql);
		$user_data = $result->fetch('ASSOC');
		
		###################
		# salvestame mailinglistide valik
		# ja lisada uued mailinglistid

		###################
		# kustutada vanad mailinglistid

		$sql = $site->db->prepare("SELECT user_mailinglist.objekt_id FROM user_mailinglist LEFT JOIN objekt ON user_mailinglist.objekt_id = objekt.objekt_id WHERE user_mailinglist.user_id = ? AND objekt.keel=?", 
			$user_id, 
			$site->keel
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$del_obj = array();
		while($del_obj_id = $sth->fetchsingle()) {
			$del_obj[] = $del_obj_id;
		}
		$sql = $site->db->prepare("DELETE FROM user_mailinglist WHERE user_id=? AND FIND_IN_SET(objekt_id,?)", 
			$user_id,
			join(",",$del_obj)
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		# ja lisada uued (NB! aga ainult siis kui kasutaja e-mail ei ole tühi)
		if (trim($site->user->all['email'] != '') && is_array($fields))
		{
			# rubrigide ID kontroll
			# kas on seal meilinglist, kas on rubriik avaldatud

			$sql = $site->db->prepare(
				"SELECT obj_rubriik.objekt_id FROM obj_rubriik,objekt WHERE obj_rubriik.objekt_id=objekt.objekt_id AND objekt.on_avaldatud='1' AND obj_rubriik.on_meilinglist = '1' AND find_in_set(obj_rubriik.objekt_id, ?)", join(",", $fields)
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			$values = array();

			while ($objekt_id = $sth->fetchsingle()) {
				$values[] = $site->db->prepare("(?,?)", $user_id, $objekt_id);
			}

			if (sizeof($values)) {
				$sql = "INSERT INTO user_mailinglist (user_id, objekt_id) VALUES ".join(",",$values);
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());
			}

		} # if mõni meilinglist oli chekitud

	} # kui kasutaja lisati edukalt
	#  / salvestame mailinglistide valik
	###################
}
