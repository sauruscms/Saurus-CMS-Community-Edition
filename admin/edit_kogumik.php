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
?>

        <TR> 
          <TD noWrap><?=$site->sys_sona(array(sona => "Artiklite arv", tyyp=>"editor"))?>:</TD>
          <TD> 
            <input type=text name="art_arv" value="<?=$objekt->all[art_arv]>0 ? $objekt->all[art_arv]:"5"?>" class="scms_flex_input" style="width:30px" >
          </TD>
          <TD nowrap align="right" style="padding-left:15px"><?=$site->sys_sona(array(sona => "Naita kuupaev", tyyp=>"editor"))?>:</td>
          <TD width="100%" style="padding-left:0px"> 
            <input type=checkbox name="on_kp_nahtav" value="1" <?=$objekt->all[on_kp_nahtav]?"checked":""?>>
          </td></TR>

    <tr>
      <td noWrap valign="top"><?=$site->sys_sona(array(sona => "Uudiste rubriigid", tyyp=>"editor"))?>:</td>
      <td colspan="3">
<?php 
		$class_path = "../classes/";
		include_once($class_path."rubloetelu.class.php");
		$rubs = new RubLoetelu(array(
			"keel" => $keel,
			"required_perm" => "is_visible",		
		));
		$topparents = $rubs->get_loetelu();

#		$rubs->debug->print_msg();
		if ($objekt->objekt_id) {
			$sql = $site->db->prepare("SELECT objekt_id FROM objekt_objekt WHERE parent_id=?",
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$rubriigid = array();
			while ($rid=$sth->fetchsingle()) {
				$rubriigid[$rid]=1;
			}
		}
		print "<SELECT style=\"WIDTH: 99%; height: 150px\" multiple name=\"news_rubrigid[]\" size=7>";
		print $site->alias("rub_system_id");
		asort($topparents);
		foreach ($topparents as $key=>$value) {
			if ($key != $site->alias("rub_system_id")) {
?>
				<option value="<?=$key?>" <?=($rubriigid[$key] ? "selected":"")?>><?=$topparents[$key]?></option>
<?php 
			}
		} 
?>
</select>
	  </td>
    </tr>
	<input name="permanent_parent_id" type=hidden value="<?=$objekt->parent_id?>">
<?php 
}

function salvesta_objekt () {
	global $site;
	global $objekt;

	if ($objekt->objekt_id) {
		$art_arv = preg_match("/\d+/",$site->fdat[art_arv],$matches) ? $matches[0] : $site->CONF[artiklite_arv_arhiivis];

		if ($objekt->on_sisu_olemas) {
			# -------------------------------
			# Objekti uuendamine andmebaasis    
			# -------------------------------
			$sql = $site->db->prepare("update obj_rubriik set on_kp_nahtav=?, art_arv=? WHERE objekt_id=?",
				$site->fdat[on_kp_nahtav] ? 1 : 0,
				$art_arv,
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("insert into obj_rubriik (on_kp_nahtav, art_arv, objekt_id) values (?,?,?)",
				$site->fdat[on_kp_nahtav] ? 1 : 0,
				$art_arv,
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}

		# nüüd vaja valitud rubriigid salvestada

		$sql = $site->db->prepare("DELETE FROM objekt_objekt WHERE parent_id=?", $objekt->objekt_id);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		$sql = "select max(sorteering) from objekt_objekt";
		$sth = new SQL($sql);
		$sorteering = $sth->fetchsingle();
		$site->debug->msg($sth->debug->get_msgs());

		$values = array();
		foreach ($site->fdat[news_rubrigid] as $rub_id) {
			array_push($values, $site->db->prepare("(?,?,?)", $rub_id, $objekt->objekt_id, ++$sorteering));
		}; #foreach
		
		if (sizeof($values)) {
			$sql = "insert into objekt_objekt (objekt_id, parent_id, sorteering) values ".join(",", $values);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}
		
		$site->debug->msg("sisu on salvestatud, objekt_id = ".$objekt->objekt_id);
		#$site->debug->print_hash($site->fdat,1,"FDAT");	

	} else {
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}
}

