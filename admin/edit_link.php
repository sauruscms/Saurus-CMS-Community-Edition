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
	$on_intra = $site->CONF[editor_intra_link];

	// setup file select
	$_SESSION['scms_filemanager_settings']['scms_select_file_link'] = array(
		'select_mode' => 1, // 1 - select single file
		'action_text' => $site->sys_sona(array('sona' => 'fm_choose_file_for_link', 'tyyp' => 'editor')),
		'action_trigger' => $site->sys_sona(array('sona' => 'fm_choose_file_for_link', 'tyyp' => 'editor')),
		'callback' => 'window.opener.setFile',
	);
?>
	<tr>
      <td nowrap><?=$site->sys_sona(array(sona => "URL", tyyp=>"editor"))?>:</td>
      <td nowrap width="100%">
		<? if($on_intra ) { ?>
		<div style="visibility:hidden; position: absolute">
		<INPUT TYPE="file" name="url_browse">
		</div>
		<SCRIPT LANGUAGE="JavaScript">
		<!--
			function do_regexp_swap(obj) {
			  function replace(string,text,by) {

				var strLength = string.length, txtLength = text.length;
				if ((strLength == 0) || (txtLength == 0)) return string;

				var i = string.indexOf(text);
				if ((!i) && (text != string.substring(0,txtLength))) return string;
				if (i == -1) return string;

				var newstr = string.substring(0,i) + by;

				if (i+txtLength < strLength)
					newstr += replace(string.substring(i+txtLength,strLength),text,by);

				return newstr;
			  }

			  var my_url;

			  my_url = obj.value;

			  if (my_url.substring(1,3)==':\\') {	
				  my_url = 'file:///'+my_url;
				  my_url = replace(my_url,'\\','/');
			  }
				
			  return my_url;
			}
		//-->
		</SCRIPT>
		<input name="url" style="width:300px" class="scms_flex_input"  value="<?=$objekt->all[url]?$objekt->all[url]:"http://"?>"><input type="button" value="<?=$site->sys_sona(array(sona => "otsi", tyyp=>"editor"))?>" name="browse" class="btn2" onclick="url_browse.click();url.value=do_regexp_swap(url_browse);" >
		<? } else { ?>

		<? // 28.02.2011 Mati: added option to choose local files into link editor using filemanager ?>
		<SCRIPT LANGUAGE="JavaScript">
		<!--
			function chooseFile()
			{
				filemanager_window = openpopup('filemanager.php?setup=scms_select_file_link', 'filemanager', 980, 600);
			}
			function setFile(data)
			{
				filemanager_window.close();
				$('input#url').attr('value', data.files[0].folder.replace(/^\//, '') + '/' + data.files[0].filename);
			}
		//-->
		</SCRIPT>
		<table cellpadding="0" cellspacing="0" border="0" class="cf_container">
			<tr>
				<th style="padding:0px;"><input id="url" name="url" class="scms_flex_input" style="border:0px;" value="<?=$objekt->all[url]?$objekt->all[url]:"http://"?>"></th>
				<td><a href="javascript:chooseFile();">..</a></td>
			</tr>
		</table>
		<? } ?>
		</td>
    </tr>
    <tr>
      <td></td>
      <td nowrap><input type=checkbox name="on_uusaken"  id="on_uusaken" value=1 <?=$objekt->all[on_uusaken]?"checked":""?>> <label for="on_uusaken"><?=$site->sys_sona(array(sona => "Open in new window", tyyp=>"editor"))?></label></td>
    </tr>

	<input name="permanent_parent_id" type=hidden value="<?=$objekt->parent_id?>">
<?
}

function salvesta_objekt () {
	global $site;
	global $objekt;

	if ($objekt->objekt_id) {

/*
		$sql = $site->db->prepare("select objekt_id from obj_link WHERE objekt_id=?",
			$objekt->objekt_id
		);
		$sth = new SQL($sql);
		$on_olemas = $sth->rows;
		if (preg_match('/\.(.*?)$/',$file[name],$matches)) {
			$fail_tyyp = $matches[1];
		}
*/
		if ($objekt->on_sisu_olemas) {
			# -------------------------------
			# Objekti uuendamine andmebaasis    
			# -------------------------------
			$sql = $site->db->prepare("update obj_link set url=?,on_uusaken=? WHERE objekt_id=?",
				$site->fdat[url],
				$site->fdat[on_uusaken] ? 1 : 0,
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("insert into obj_link (url,on_uusaken,objekt_id) values (?,?,?)",
				$site->fdat[url],
				$site->fdat[on_uusaken] ? 1 : 0,
				$objekt->objekt_id
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
