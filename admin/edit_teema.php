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

	$conf = new CONFIG($objekt->all[ttyyp_params]);
	$email = $conf->get("email");
?>
	<tr> 
      <td nowrap><?=$site->sys_sona(array(sona => "Email", tyyp=>"kasutaja"))?>:</td>
      <td width="100%"><input name="email" class="scms_flex_input" value="<?=$email?>"></td>
	</tr>
<?
}

function save_tyyp_params (){

	$args = func_get_arg(0);
	$objekt = $args["objekt"];

	$conf = new CONFIG($objekt->all[ttyyp_params]);
	$conf->put("email", $conf->site->fdat['email']);
	if ($conf->site->fdat['email']) {
		$conf->put("on_saada_email", 1);
	} else {
		$conf->put("on_saada_email", 0);
	}
	return $conf->Export();
}

function salvesta_objekt () {
	global $site;
	global $objekt;

	if ($objekt->objekt_id) {

		if ($objekt->on_sisu_olemas) {
			# -------------------------------
			# Objekti uuendamine andmebaasis    
			# -------------------------------
			$sql = $site->db->prepare("update obj_rubriik set on_peida_vmenyy=?, on_printlink=?, on_meilinglist=? WHERE objekt_id=?",
				$objekt->all[on_peida_vmenyy],
				$site->fdat[on_printlink] ? 1 : 0,
				$site->fdat[on_meilinglist] ? 1 : 0,
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("insert into obj_rubriik (objekt_id,on_peida_vmenyy, on_printlink, on_meilinglist) values (?,?,?,?)",
				$objekt->objekt_id,
				$objekt->all[on_peida_vmenyy],
				$site->fdat[on_printlink] ? 1 : 0,
				$site->fdat[on_meilinglist] ? 1 : 0
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			// Here we make objekt_id like current id (in main window)
?>
			<script language=javascript>
			<!--		
				variableFromEditRubriik_id='<?=$objekt->objekt_id ?>';
			//-->
			</script>
<?		
		}

		$site->debug->msg("sisu on salvestatud, objekt_id = ".$objekt->objekt_id);
		#$site->debug->print_hash($site->fdat,1,"FDAT");	

	} else {
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}
}
