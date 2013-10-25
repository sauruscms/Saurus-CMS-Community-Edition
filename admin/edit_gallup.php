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
?>
	<?php ######### answers ?>
    <tr valign=top>
      <td nowrap>

	  <?=$site->sys_sona(array(sona => "Vastused", tyyp=>"editor"))?>:</td>
	<td width="100%">
<?php 
	$sql = $site->db->prepare("select * from gallup_vastus where objekt_id=?", $objekt->objekt_id);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	while ($vastus = $sth->fetch()) {
		print "<input class=scms_flex_input name=\"vastus[$vastus[gv_id]]\" value=\"$vastus[vastus]\"><br>";
	}

	## rest of empty rows
	for ($i=$sth->rows; $i<5; ++$i) {
		print "<input class=scms_flex_input name=\"uusvastus[$i]\"><br>";
	}
	if ($sth->rows>=5) {
		print "<input class=scms_flex_input name=\"uusvastus[0]\"><br>";
	}
?>
	  </td>
    </tr>
	<?php ######### on_avatud ?>
	<tr>
	<td><input <?= ($site->fdat['avatud_disabled'] == 1 ? " disabled " : "") ?>type=checkbox name="on_avatud" id="on_avatud" value="1" <?=((!$objekt->objekt_id && $site->fdat['default_avatud_off'] != 1) || ($objekt->all[on_avatud]))?"checked":""?>> 
      <td nowrap><label for="on_avatud"><?=$site->sys_sona(array(sona => "Avatud", tyyp=>"editor"))?></label></td>
	  </td>
    </tr>
	<?php ######### is_anonymous ?>
	<tr>
	<td><input type=checkbox name="is_anonymous" id="is_anonymous" value="1" <?=(!$objekt->objekt_id || $objekt->all[is_anonymous])?"checked":""?>> 
      <td nowrap><label for="is_anonymous"><?=$site->sys_sona(array(sona => "Anonymous", tyyp=>"editor"))?></label></td>
	  </td>
    </tr>
	<?php ######### expires ?>
	<tr>
      <td nowrap><?=$site->sys_sona(array(sona => "expires", tyyp=>"editor"))?>:</td>
	  <td width="100%">
			<table border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td style="padding:0px"> 
                  <input name="expires" id="expires" type="text" class="scms_flex_input" style="width:80px" value="<?=($objekt->all['expires']?$site->db->MySQL_ee($objekt->all['expires']):'') ?>">
                </td>
                <td style="padding:0px"><a href="#" onclick="init_datepicker('expires');"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" hspace="4" border="0" alt="Calendar"></a></td>
              </tr>
            </table>

	  </td>
	</tr>



<?php 
}
###############################
function salvesta_objekt () {
	global $site;
	global $objekt;

	if ($objekt->objekt_id) {

		if ($objekt->on_sisu_olemas) {
			# -------------------------------
			# Objekti uuendamine andmebaasis    
			# -------------------------------
			$sql = $site->db->prepare("UPDATE obj_gallup SET on_avatud=?, is_anonymous=?,expires=? WHERE objekt_id=?",
				$site->fdat[on_avatud] ? 1 : 0,
				$site->fdat[is_anonymous] ? 1 : 0,
				$site->db->ee_MySQL($site->fdat['expires']),
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			#print $sql;
			$site->debug->msg($sth->debug->get_msgs());
		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("INSERT INTO obj_gallup (on_avatud, objekt_id, orig_parent_id, is_anonymous,expires) VALUES (?,?,?,?,?)",
				$site->fdat[on_avatud] ? 1 : 0,
				$objekt->objekt_id,
				$site->fdat[parent_id],
				$site->fdat[is_anonymous] ? 1 : 0,
				$site->db->ee_MySQL($site->fdat['expires'])
			);
			$sth = new SQL($sql);
			#print $sql;
			$site->debug->msg($sth->debug->get_msgs());
		}
		$vids = array();
		
		if (is_array($site->fdat[vastus])) {
			$site->debug->msg("Muutuvad vastused: ",join(",",$site->fdat[vastus]));			
			# nüüd vaja vastused salvestada

			foreach ($site->fdat[vastus] as $vid=>$vastus) {
				if ($vastus) {
					$sql = $site->db->prepare("UPDATE gallup_vastus SET vastus=? WHERE gv_id=?",
						$vastus,$vid
					);
					$sth = new SQL($sql);
					$site->debug->msg($sth->debug->get_msgs());
					array_push ($vids,$vid);
				}
			}
		}

		$site->debug->msg("Uued vastused: ",join(",",$site->fdat[uusvastus]));
		foreach ($site->fdat[uusvastus] as $vid=>$vastus) {
			if ($vastus) {
				$sql = $site->db->prepare("INSERT INTO gallup_vastus (objekt_id, vastus) VALUES (?, ?)", 
					$objekt->objekt_id, $vastus
				);
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());	
				array_push ($vids,$sth->insert_id);
			}
		}
		$site->debug->msg("Updated vastused: ",join(",",$vids));

		$sql = $site->db->prepare("DELETE FROM gallup_vastus WHERE objekt_id=? AND NOT gv_id IN('".join("','", $vids)."')", 
			$objekt->objekt_id
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		$site->debug->msg("sisu on salvestatud, objekt_id = ".$objekt->objekt_id);

	} else {
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}
}
