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

 
function edit_objekt () {
	global $site;
	global $objekt;
	global $keel;
	global $class_path;

	include_once($class_path."adminpage.inc.php");

	$profile_id = $objekt->objekt_id ? $objekt->all[profile_id] : $site->fdat[profile_id];
?>
	<input type="hidden" name="profile_id" value="<?=$profile_id?>">
<?php 
	####################
	# Additional info: attributes list

	# get profile
	$profile_def = $site->get_profile(array("id"=>$profile_id)); 
	$profile_fields = unserialize($profile_def['data']);	# profile_fields is now array of ALL fields, indexes are fieldnames
	
	if($profile_fields['pealkiri']['is_general']) unset($profile_fields['pealkiri']);

	###################
	# print profile fields rows
	print_profile_fields(array(
		'profile_fields' => $profile_fields,
		'field_values' => $objekt->all,
		'fields_width' => '300px',
	));

} # function

####################################
# FUNCTION salvesta_objekt

function salvesta_objekt () {
	global $site;
	global $objekt;

	global $class_path;
	
	if ($objekt->objekt_id) {

		if ($objekt->on_sisu_olemas) {
			# -------------------------------
			# Objekti uuendamine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("UPDATE obj_asset SET profile_id=? WHERE objekt_id=?",
				$site->fdat['profile_id'],
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("INSERT INTO obj_asset (objekt_id,profile_id) VALUES (?,?)",
				$objekt->objekt_id,
				$site->fdat['profile_id']
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}

		#########################
		# debug info
		$site->debug->msg("sisu on salvestatud, objekt_id = ".$objekt->objekt_id);
		#$site->debug->print_hash($site->fdat,1,"FDAT");	

	} else {
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}
}
# / FUNCTION salvesta_objekt
####################################
