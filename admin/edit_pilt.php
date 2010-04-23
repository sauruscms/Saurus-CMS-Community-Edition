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

 
$timer = new Timer();
$class_path = "../classes/";

include_once $class_path."picture.inc.php";

function new_objekt () {
	global $site;
	global $objekt;
	global $keel;
?>

	<script>
	function setPealkiri (nr) {
		var algus=0;
		var saved_algus=0;

		filefield = eval('frmEdit.file'+nr+'.value');
		pealkiri_value  = eval('frmEdit.pealkiri'+nr+'.value');


		if (pealkiri_value=='') {
			while (algus!=-1) {
				saved_algus=algus;
				algus=filefield.indexOf('\\',algus+1);
			}
			algus=saved_algus;
			while (algus!=-1) {
				saved_lopp=algus;
				algus=filefield.indexOf('\.',algus+1);
			}

			eval('frmEdit.pealkiri'+nr+'.value = filefield.substr(saved_algus+1,saved_lopp-saved_algus-1)');
		}
	}
	</script>



<input type="hidden" name="salvesta" value="1">
<?
	/*-------------------------------
	# Kui album on kommenteeritav siis
	# lisada vaikimisi piltidele
	# on_foorum=1
	--------------------------------*/
	$sql = $site->db->prepare("
		SELECT on_foorum FROM objekt WHERE objekt_id=?
		",
		$site->fdat['parent_id']
	);
	$sth = new SQL ($sql);
	$on_foorum = $sth->fetchsingle();
	if ( $on_foorum ) {
?>
	<input type="hidden" name="on_foorum" value="1">
<? } ?>
<table width="100%"  border="0" cellspacing="3" cellpadding="0">
<tr>
    <td><?=$site->sys_sona(array(sona => "filename", tyyp=>"editor"))?>:</td>
    <td width="100%"><input type="file" name="file1" onChange="setPealkiri(1)" class="scms_flex_input"></td>
</tr>
<tr>
    <td><?=$site->sys_sona(array(sona => "Kirjeldus", tyyp=>"editor"))?>:</td>
    <td width="100%" class="scms_row_btm"><input type="text" name="pealkiri1" class="scms_flex_input"></td>    
</tr>
<tr>
    <td><?=$site->sys_sona(array(sona => "filename", tyyp=>"editor"))?>:</td>
    <td width="100%"><input type="file" name="file2" onChange="setPealkiri(2)" class="scms_flex_input"></td>
</tr>
<tr>
    <td><?=$site->sys_sona(array(sona => "Kirjeldus", tyyp=>"editor"))?>:</td>
    <td width="100%" class="scms_row_btm"><input type="text" name="pealkiri2" class="scms_flex_input"></td>    
</tr>
<tr>
    <td><?=$site->sys_sona(array(sona => "filename", tyyp=>"editor"))?>:</td>
    <td width="100%"><input type="file" name="file3" onChange="setPealkiri(3)" class="scms_flex_input"></td>
</tr>
<tr>
    <td><?=$site->sys_sona(array(sona => "Kirjeldus", tyyp=>"editor"))?>:</td>
    <td width="100%" class="scms_row_btm"><input type="text" name="pealkiri3" class="scms_flex_input"></td>    
</tr>
<tr>
    <td><?=$site->sys_sona(array(sona => "filename", tyyp=>"editor"))?>:</td>
    <td width="100%"><input type="file" name="file4" onChange="setPealkiri(4)" class="scms_flex_input"></td>
</tr>
<tr>
    <td><?=$site->sys_sona(array(sona => "Kirjeldus", tyyp=>"editor"))?>:</td>
    <td width="100%" class="scms_row_btm"><input type="text" name="pealkiri4" class="scms_flex_input"></td>    
</tr>
<tr>
    <td><?=$site->sys_sona(array(sona => "filename", tyyp=>"editor"))?>:</td>
    <td width="100%"><input type="file" name="file5" onChange="setPealkiri(5)" class="scms_flex_input"></td>
</tr>
<tr>
    <td><?=$site->sys_sona(array(sona => "Kirjeldus", tyyp=>"editor"))?>:</td>
    <td width="100%" class="scms_row_btm"><input type="text" name="pealkiri" class="scms_flex_input"></td>    
</tr>
</table>
	<?#################### BUTTONS ###########?>
  </TR>
  <tr> 
    <td align="right" valign="top" class="scms_dialog_area_bottom"> 
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='saveclose'; body.style.cursor = 'wait';if(typeof url_browse == 'object'){url_browse.removeNode()}; frmEdit.submit();">

	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
  </TBODY> 
</TABLE>

<?

}

function edit_objekt () {
	global $site;
	global $objekt;
	global $keel;

	$aeg = $objekt->all[aeg] ? $site->db->MySQL_ee($objekt->all[aeg]) : $site->eesti_aeg();
?>
	<script>
	function setPealkiri (strPealkiri) {
		var algus=0;
		var saved_algus=0;

		if (frmEdit.pealkiri.value=='') {
			while (algus!=-1) {
				saved_algus=algus;
				algus=strPealkiri.indexOf('\\',algus+1);
			}
			algus=saved_algus;
			while (algus!=-1) {
				saved_lopp=algus;
				algus=strPealkiri.indexOf('\.',algus+1);
			}

			frmEdit.pealkiri.value = strPealkiri.substr(saved_algus+1,saved_lopp-saved_algus-1);
		}
	}
	</script>
	 
		<tr>
		  <td>&nbsp;</td>
		</tr>
		<tr>
		  <td class="txt" nowrap valign="top" align=right><?=$site->sys_sona(array(sona => "filename", tyyp=>"editor"))?>:</td>
		  <td width="100%"><input type="file" name=file class="scms_flex_input"  onChange="setPealkiri(file.value)"></td>
		</tr>
		<tr>
		  <td class="txt" nowrap valign="top" align=right><?=$site->sys_sona(array(sona => "Autor", tyyp=>"editor"))?>:</td>
		  <td width="100%"><input type="text" class="scms_flex_input" name=autor value="<?=htmlspecialchars($objekt->all[autor])?>">
		  <select class="width:30%" name=autor_select onChange="frmEdit.autor.value = frmEdit.autor_select[frmEdit.autor_select.selectedIndex].value">
			<option>vali</option>
		<tr>
		  <td class="txt" nowrap valign="top" align=right><?=$site->sys_sona(array(sona => "Aeg", tyyp=>"editor"))?>:</td>
		  <td><input type="text" name=aeg class="scms_flex_input" style="width:100px" value="<?=htmlspecialchars($aeg)?>"></td>
		</tr>
<?

}


function salvesta_objekt () {
	global $site;
	global $objekt;


		$file = $_FILES["file"];
		
		$site->debug->print_hash($file,1,"Files");
/*
		$image = ImageCreateFromJPEG($file[tmp_name]);
		$width = ImageSX();
		$height = ImageSY();
*/
#		$site->debug("width = $width, height = $height");

	if ($objekt->objekt_id) {
		$file = $_FILES["file"];

		
		$site->debug->msg("File size: ".$file[size]);
		$site->debug->print_hash($file,1,"Files");

		# -------------------------------
		# Objekti uuendamine andmebaasis    
		# -------------------------------

		if ($objekt->on_sisu_olemas) {
			if ($file[size]) {
				$sql = $site->db->prepare("update obj_pilt set fail=?, size=?, kirjeldus=?, autor=?, tyyp=?, mime_tyyp=? WHERE objekt_id=?",
					$file[name],
					$file[size],
					$site->fdat[kirjeldus],
					$site->fdat[autor],
					$fail_tyyp,
					$file[type],
#					$file[tmp_name],vaike_blob=load_file(?), sisu_blob=load_file(?)
#					$file[tmp_name],
					$objekt->objekt_id
				);
			} else {
				$sql = $site->db->prepare("update obj_pilt set kirjeldus=?, autor=? WHERE objekt_id=?",
					$site->fdat[kirjeldus],
					$site->fdat[autor],
					$objekt->objekt_id
				);
			}
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("insert into obj_pilt (objekt_id, size, fail, kirjeldus, autor, tyyp, mime_tyyp) values (?, ?, ?, ?, ?, ?, ?)",
				$objekt->objekt_id,
				$file[size],
				$file[name],
				$site->fdat[kirjeldus],
				$site->fdat[autor],
				$fail_tyyp,
				$file[type]
				#$file[tmp_name], , sisu_blob, vaike_blob
				#$file[tmp_name] , load_file(?), load_file(?)				
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}

		if (file_exists($file[tmp_name])) {
			
			foreach ($_FILES as $key => $data) {
				if ($data[size]>0) {

					/*--------------------------------
						piltide konverteerimine
					---------------------------------*/
					$resized = picture_resize( $key, $data );
					$cs_image = $resized["image"];
					$cs_thumb = $resized["thumb"];
					$cs_orig = $resized["orig"];

					$sql = $site->db->prepare("
						update obj_pilt set sisu_blob=?,vaike_blob=? WHERE objekt_id=?",
						$cs_image,
						$cs_thumb,
						$objekt->objekt_id
					);
					$sth = new SQL($sql);
					$site->debug->msg($sth->debug->get_msgs());
				}
			}

			/*----------------------------
				originaalfaili UPDATE
			-----------------------------*/
			if($cs_orig) {
				original_file_save( $objekt->objekt_id, $cs_orig );
			}
		}
		$site->debug->msg("sisu on salvestatud, objekt_id = ".$objekt->objekt_id);

	} else {
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}

}
function original_file_save( $id, $data_big ) {

	global $site;

	/*------------------------------
	Originaalfaili salvestamine
	-------------------------------*/
	if ( $site->CONF[original_picture_saved] == 1 && $data_big ) {

		#$limit = 1000000; // vale segmenti suurus!
		$limit = 522000; #segmenti suurus 524288-2288 = 522000

		$file_size = strlen($data_big);

		$site->debug->msg("Saving original file: size = ".$file_size);

		#kustutame �ra baasist vana fail
		$sql = $site->db->prepare("
			DELETE 
			FROM document_parts 
			WHERE objekt_id = ?",
			$id
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		if ($file_size >= $limit) {
			/*---------------------------------------------------------------
			kui Faili suurus on �le lubatud piiri, siis jagame teda segmentina
			----------------------------------------------------------------*/

			$data_start = 0;

			$data_left=$file_size-$data_start;
			while ($data_left) {
				if ($data_left>$limit) {
					$chunk=$limit;
				} else {
					$chunk=$data_left;
				}
				$rest_data=substr($data_big,$data_start,$chunk);
				if ($chunk!=$data_left) {
					while (substr($rest_data,-1)=="\\") {
						$rest_data=substr($rest_data,0,-1);
						$chunk--;
					}
				}
				$sql = $site->db->prepare("
					insert into document_parts set objekt_id= ?, content= ?",
					$id,
					$rest_data
				);
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());

				$data_left-=$chunk;
				$data_start+=$chunk;
			}
		} else {
			$sql = $site->db->prepare("
				insert into document_parts set objekt_id= ?, content= ?",
				$id,
				$data_big
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}
	}
	/*------------------------------
	Originaalfaili salvestamise l�pp
	-------------------------------*/
}

function save_objekts () {
	global $site;
	global $objekt;
	global $keel;
	global $timer;
	
	# sort array in reverse order
	$_FILES = array_reverse($_FILES);

	$args = func_get_arg(0);


#print "<br>time in save_objekts begin:".$timer->get_aeg()." <br>";
	$thumb_width = $site->CONF[thumb_width];
	$image_width = $site->CONF[image_width];

	#############################
	# loop over uploaded pictures

	foreach ($_FILES as $key => $data) {
		if ($data[size]>0) {
			
			$pealkiri = $site->fdat[str_replace("file","pealkiri",$key)];
			if (!$pealkiri) {
				$pealkiri = $data[name];
			}
			$site->debug->msg("Image= $key: $pealkiri");
			
			/*------------------------------
			Pildi konverteerimine
			-------------------------------*/
			$resized = picture_resize( $key, $data );
			$cs_image = $resized["image"];
			$cs_thumb = $resized["thumb"];
			$cs_orig = $resized["orig"];

			#print "<br>time after picter resize:".$timer->get_aeg()." <br>";

			// insert into objekt:
			$sql = $site->db->prepare("insert into objekt (pealkiri, tyyp_id, on_avaldatud, keel, kesk, pealkiri_strip, on_foorum, aeg, check_in, created_user_id, created_user_name) values (?, ?, 1, ?, ?, ?, ?, ".$site->db->unix2db_datetime(time()).", ?, ?, ?)",
				$pealkiri,
				$args[tyyp_id],
				$args[keel],
				$site->fdat[kesk],
				strip_tags($pealkiri),
				($site->fdat['on_foorum'] ? $site->fdat['on_foorum'] : $site->CONF['default_comments']),
				0,
				$site->user->id,
				$site->user->name
			);
			$sth = new SQL ($sql);
			$site->debug->msg($sth->debug->get_msgs());
			
			$id = $sth->insert_id;
			
			// insert into objekt_objekt:	
			$sql = "select max(sorteering) from objekt_objekt";
			$sth = new SQL ($sql);
			$site->debug->msg($sth->debug->get_msgs());
			$sorteering=$sth->fetchsingle();

			$sql = $site->db->prepare("insert into objekt_objekt (objekt_id, parent_id, sorteering) values (?,?,?)",
				$id,
				$site->fdat[parent_id],
				$sorteering+1
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			
			
			// insert into obj_pilt:	
			$sql = $site->db->prepare("insert into obj_pilt (objekt_id, size, fail, tyyp, mime_tyyp) values (?, ?, ?, ?, ?)",
				$id,
				$data[size],
				$data[name],
				$fail_tyyp,
				$data[type]
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

#print "<br>time after insert  to db:".$timer->get_aeg()." <br>";

			if ($cs_thumb || $cs_image) {
				$site->debug->msg("Updateing...");
				$c_thumb = $cs_thumb;
				$c_image = $cs_image;
				
				$sql = $site->db->prepare("
					update obj_pilt set sisu_blob=?, vaike_blob=? WHERE objekt_id=?",
					$c_image,
					$c_thumb,
					$id
				);
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());

				$file_size = $data[size];
			}
			/*----------------------------
				originaalfaili UPDATE
			-----------------------------*/
			if($cs_orig) {
				original_file_save( $id, $cs_orig );
			}

		}
	}

	# / loop over uploaded pictures
	#############################


#print "time end: ".$timer->get_aeg()."<br>";

	if ($site->on_debug != 1) {

		$opener_location = $site->fdat[opener_location];
		
	?>
<script type="text/javascript">
	window.opener.location.href=<?=($opener_location && $objekt->all[klass]=="rubriik" && !$objekt->all[on_kast] ? "'$opener_location'" : "window.opener.location.href") ?>;
	window.close();
</script>
	<?
		}	
exit;

}
