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

	$aeg = $objekt->all[aeg] ? $site->db->MySQL_ee($objekt->all[aeg]) : $site->eesti_aeg();
?>
 

	<tr>
    <td nowrap valign="top"><?=$site->sys_sona(array(sona => "Autor", tyyp=>"editor"))?>:</td>
	<td><input type=text class="scms_flex_input" name="nimi" value="<?=$objekt->all[nimi]?>"> <? if ($objekt->objekt_id) { ?><font class="txt">IP: <?=$objekt->all[ip]?></font><? } ?>
	<!--	
	<textarea name="pastearea" rows="5" cols="30" style="width:1;height:1;visibility:hidden;"></textarea>
		<input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor"))?>"  name="submit2" onclick="body.style.cursor = 'wait';if(typeof url_browse == 'object'){url_browse.removeNode()};<?=($on_nupurida && !$on_textarea) ? "savedoc('$tyyp[klass]');":"frmEdit.submit();" ?>">
	-->
	  </td>
    </tr>  

	<tr>
      <td nowrap><?=$site->sys_sona(array(sona => "E-mail", tyyp=>"editor"))?>:</td>
		<td><input type=text name="email" class="scms_flex_input" value="<?=$objekt->all[email]?>"> 
	  </td>
    </tr>  


    <tr>
      <td nowrap valign="top"><?=$site->sys_sona(array(sona => "Kommentaar", tyyp=>"editor"))?>:</td>
      <td>
	<textarea name="text" style="width:100%; height:100px" class=tarea><?=$objekt->all[text] ?></textarea>
	  </td>
    </tr>

	<tr>
	<td nowrap>&nbsp;</td>
      <td nowrap><input type=checkbox name="on_peida_email" id="on_peida_email" value="1" <?=$objekt->all[on_peida_email]?"checked":""?>> <label for="on_peida_email"><?=$site->sys_sona(array(sona => "Peida meiliaadress", tyyp=>"editor"))?></label>
	  </td>
    </tr>  

	<tr>
	  <td nowrap>&nbsp;</td>
      <td nowrap><input type=checkbox name="on_saada_email" id="on_saada_email" value="1" <?=$objekt->all[on_saada_email]?"checked":""?>> <label for="on_saada_email"><?=$site->sys_sona(array(sona => "Saada meilile", tyyp=>"editor"))?></label>
	  </td>
    </tr>  
	<tr>
		<td nowrap><?=$site->sys_sona(array(sona => "Aeg", tyyp=>"editor"))?>:</td>
		<td>
            <table border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td style="padding:0px"> 
				  <input type="text" class="scms_flex_input" style="width: 80px; text-align: right;" id="aeg"  name="aeg" value="<?=$aeg?>">
	              </td>
                <td style="padding:0px">
				  <a href="#" onclick="init_datepicker('aeg');"><img src="<?=$site->CONF[wwwroot].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" hspace="4" border="0" alt="Calendar"></a>
				</td>
			</tr>
			</table>
	  </td>
    </tr>


	<input name="permanent_parent_id" type=hidden value="<?=$objekt->parent_id?>">
<?
}

##############################################################################

function salvesta_objekt () {
	global $site;
	global $objekt;

	if ($objekt->objekt_id) {
		if ($objekt->on_sisu_olemas) {
			# -------------------------------
			# Objekti uuendamine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("UPDATE obj_kommentaar SET nimi=?, email=?, on_saada_email=?, on_peida_email=?, text=? WHERE objekt_id=?",
				$site->fdat[nimi],
				$site->fdat[email],
				$site->fdat[on_saada_email] ? 1 : 0,
				$site->fdat[on_peida_email] ? 1 : 0,				
				$site->fdat[text],
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());


		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("INSERT INTO obj_kommentaar (objekt_id, nimi, email, on_saada_email, on_peida_email, ip, text) values (?, ?, ?, ?, ?, ?, ?)",
				$objekt->objekt_id,
				$site->fdat[nimi],
				$site->fdat[email],
				$site->fdat[on_saada_email] ? 1 : 0,
				$site->fdat[on_peida_email] ? 1 : 0,
				getenv ("REMOTE_ADDR"),
				$site->fdat[text]
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}


		$site->debug->msg("sisu on salvestatud, objekt_id = ".$objekt->objekt_id);
		#$site->debug->print_hash($site->fdat,1,"FDAT");	

	} else {
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}
}
