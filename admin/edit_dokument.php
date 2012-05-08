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

	$aeg = $objekt->all[aeg] ? $site->db->MySQL_ee($objekt->all[aeg]) : $site->eesti_aeg();
	$profile_id = $objekt->objekt_id ? $objekt->all[profile_id] : $site->fdat[profile_id];
	# kui ikka veel profiil poel teada, kasuta defautl profiili
	if(!$profile_id) {
		# POOLELI - mis on tegelikult dokumendi profiili default ID?
		$profile_id = 55;
	}

?>
	<script>
	function setPealkiri (strPealkiri)
	{
		if (document.frmEdit.pealkiri.value=='')
		{
			// \ to /
			strPealkiri = strPealkiri.replace(/\\/g, '/');
			// strip out file extension
			strPealkiri = strPealkiri.replace(/\.[^\.]*$/g, '');
			// strip out file path
			strPealkiri = strPealkiri.replace(/^(.*)\//g, '');

			document.frmEdit.pealkiri.value = strPealkiri;
		}
	}
	</script>
	 <?########### fail ?>
		<tr>
		  <td nowrap><?=$site->sys_sona(array(sona => "filename", tyyp=>"editor"))?>:</td>
		  <td><input type="file" name=file onChange="setPealkiri(file.value)" class="scms_flex_input" style="width:100%"></td>
		</tr>
	 <?########### kirjeldus ?>
		<tr>
		  <td nowrap><?=$site->sys_sona(array(sona => "Kirjeldus", tyyp=>"editor"))?>:</td>
		  <td><textarea name="kirjeldus" rows=5  style="width:100%"><?=htmlspecialchars(stripslashes($objekt->all[kirjeldus]))?></textarea></td> 
		</tr>
	 <?########### autor ?>

	<?
	$sql = "select distinct autor from obj_dokument where autor not like '' order by autor";
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
	?>
		<tr>
		  <td nowrap><?=$site->sys_sona(array(sona => "Autor", tyyp=>"editor"))?>:</td>
		  <td><input type="text" class="scms_flex_input" style="width:80px" name=autor value="<?=htmlspecialchars($objekt->all[autor])?>">
		  <select name=autor_select onChange="frmEdit.autor.value = frmEdit.autor_select[frmEdit.autor_select.selectedIndex].value">
			<option>vali</option>
<?
	while ($autor=$sth->fetchsingle()) {
			print "<option value=\"$autor\">$autor</option>";
	}
?>
		  </select>
		  </td>
		</tr>

	 <?########### aeg ?>
		<tr>
		  <td nowrap><?=$site->sys_sona(array(sona => "Aeg", tyyp=>"editor"))?>:</td>
		  <td><input type="text" class="scms_flex_input" style="width:80px"  name=aeg value="<?=htmlspecialchars($aeg)?>"></td>
		</tr>
<?
	####################
	# Additional info: attributes list

		# get profile
		$profile_def = $site->get_profile(array("id"=>$profile_id)); 
		$profile_fields = unserialize($profile_def['data']);	# profile_fields is now array of ALL fields, indexes are fieldnames
		### unset default fields:
	#	unset($profile_fields['fail']);
	#	unset($profile_fields['autor']);
	#	unset($profile_fields['kirjeldus']);
		#printr($profile_fields);

		###################
		# print profile fields rows
		print_profile_fields(array(
			'profile_fields' => $profile_fields,
			'field_values' => $objekt->all,
			'fields_width' => '300px',
		));
}

function salvesta_objekt () {
	global $site;
	
	# sets the maximum amount of memory in CONF["php_memory_limit"] Mbytes 
	# that a script is allowed to allocate
	# if general value is smaller
	if ( intval(ini_get('memory_limit')) < intval($site->CONF["php_memory_limit"]) ) {
		ini_set ( "memory_limit", $site->CONF["php_memory_limit"]."M" );
	}

	$args = func_num_args()>0 ? func_get_arg(0) : "";

	if (!$args["file"]) {
		global $_FILES;
	}
	
	#############################################
	# download_type - default: 0
	# kui download_type=1, 
	# siis salvestame uploaditud file k�vaketas,
	# mitte baasi
	$download_type = $args['download_type'] ? 1 : ((!empty($site->CONF['documents_in_filesystem']) && !empty($site->CONF['documents_directory']) && file_exists(str_replace('//', '/', $site->absolute_path.$site->CONF['documents_directory']))) ? 1 : 0);

	if ($args["objekt"]) {
		$objekt = $args["objekt"];
		$obj_sisu = $objekt->get_sisu();
		$site->fdat[kirjeldus] = $obj_sisu[kirjeldus];
	} else {
		global $objekt;
	}
	
	if ($objekt->objekt_id) {
		$file = $args["file"] ? $args["file"] : $_FILES["file"];
		$file_size = $file['size'];
		$file_name = $file['name'];
		$site->debug->print_hash($file,1,"Files");

		if ($file[size]==0 && $file_name){
		?>
			<SCRIPT LANGUAGE="JavaScript">
			<!--
				alert('<?=$site->sys_sona(array(sona => "big_file", tyyp=>"editor"))?>');
			//-->
			</SCRIPT>
		<?
		}

		if (file_exists($file[tmp_name])) {
			$fd = fopen($file[tmp_name], "rb"); # Bug #2154
			$data_big = fread ($fd, filesize($file[tmp_name]));
			fclose ($fd);
		}
		# -------------------------------
		# Objekti uuendamine andmebaasis
		# -------------------------------
		if($file_name){
			$ft_tmp=explode(".",$file_name);
			$fail_tyyp = (sizeof($ft_tmp) == 1) ? '' : $ft_tmp[sizeof($ft_tmp)-1];  # Bug #2509
		}

		/*-------------------------------
			Suurte failide uploadimine
		--------------------------------*/
		#$limit = 1000000; // vale segmenti suurus!
		$limit = 522000; #segmenti suurus 524288-2288 = 522000

		if ($file_size >= $limit && !$download_type) {

			#kustutame �ra baasist vana fail
			$sql = $site->db->prepare("
				DELETE 
				FROM document_parts 
				WHERE objekt_id = ?",
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			$data_start = $limit;
			$init_data = substr($data_big,0,$limit);
			while (substr($init_data,-1) == "\\")
			{
				$init_data=substr($init_data,0,-1);
				$data_start--;
			}

			if ($objekt->on_sisu_olemas) {
				if ($file_size) {
					$sql = $site->db->prepare("update obj_dokument set fail=?, size=?, kirjeldus=?, autor=?, tyyp=?, mime_tyyp=?, sisu_blob=?, repl_last_modified=? WHERE objekt_id=?",
						$file_name,
						$file_size,
						addslashes($site->fdat[kirjeldus]),
						$site->fdat[autor],
						$fail_tyyp,
						$file[type],
						$init_data,
						$file[modified],
						$objekt->objekt_id
					);

				} else {
					$sql = $site->db->prepare("update obj_dokument set kirjeldus=?, autor=? WHERE objekt_id=?",
						addslashes($site->fdat[kirjeldus]),
						$site->fdat[autor],
						$objekt->objekt_id
					);
				}
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());

			} else {
				$sql = $site->db->prepare("
					insert into obj_dokument (objekt_id, size, fail, kirjeldus, autor, tyyp, mime_tyyp, sisu_blob, repl_last_modified) values (?, ?, ?, ?, ?, ?, ?, ?, ?)",
					$objekt->objekt_id,
					$file_size,
					$file_name,
					addslashes($site->fdat[kirjeldus]),
					$site->fdat[autor],
					$fail_tyyp,
					$file[type],
					$init_data,
					$file[modified]
				);
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());
			}

			unset($init_data);

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
					$objekt->objekt_id,
					$rest_data
				);
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());

				$data_left-=$chunk;
				$data_start+=$chunk;
			}
		} else {
			if ($objekt->on_sisu_olemas) {
				if ($file_size) {
					$sql = $site->db->prepare("update obj_dokument set fail=?, size=?, kirjeldus=?, autor=?, tyyp=?, mime_tyyp=? WHERE objekt_id=?",
						$file_name,
						$file_size,
						addslashes($site->fdat[kirjeldus]),
						$site->fdat[autor],
						$fail_tyyp,
						$file[type],
						$objekt->objekt_id
					);
				} else {
					$sql = $site->db->prepare("update obj_dokument set kirjeldus=?, autor=? WHERE objekt_id=?",
						addslashes($site->fdat[kirjeldus]),
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

				$sql = $site->db->prepare("insert into obj_dokument (objekt_id, size, fail, kirjeldus, autor, tyyp, mime_tyyp, download_type) values (?, ?, ?, ?, ?, ?, ?, ?)",
					$objekt->objekt_id,
					$file_size,
					$file_name,
					addslashes($site->fdat[kirjeldus]),
					$site->fdat[autor],
					$fail_tyyp,
					$file[type],
					$download_type
				);
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());
			}
			if ($file_size) {
				#####################################################
				# kui download_type=1, 
				# siis salvestame uploaditud fail kataloogisse, 
				# mis on m��ratud konfis CONF['documents_directory']
				if ($download_type) {
					if (!empty($site->CONF['documents_in_filesystem']) && !empty($site->CONF['documents_directory']) && file_exists(str_replace('//', '/', $site->absolute_path.$site->CONF['documents_directory'])))
					{
						$doc_name = md5($objekt->objekt_id);
						$dir_path = str_replace('//','/',$site->absolute_path.$site->CONF['documents_directory'].'/'.$doc_name[0]);
						if (!file_exists($dir_path)) { mkdir($dir_path); chmod($dir_path, 0775); }
						$doc_full_path = $dir_path.'/'.$doc_name;
					}
					else
					{
					$doc_full_path = $site->absolute_path.$site->CONF["documents_directory"]."/".$file_name;
					
					######################################
					# Kontrollime, kas fail juba olemas?
					# Kui jah, siis anname uus nimi
					if (file_exists($doc_full_path)) {
						$i = 1;
						while (file_exists($doc_full_path) && $i<=5) {
							$file_name = "[".$i."]".$file['name'];
							$doc_full_path = $site->absolute_path.$site->CONF["documents_directory"]."/".$file_name;
							$i++;
						}
						$sql = $site->db->prepare("
							update obj_dokument set fail=? WHERE objekt_id=?",
							$file_name,
							$objekt->objekt_id
						);
						$sth = new SQL($sql);
						$site->debug->msg($sth->debug->get_msgs());
					}
					}

					$doc_uploaded = @move_uploaded_file($file['tmp_name'], $doc_full_path);
					if (!$doc_uploaded) {
						?>
							<SCRIPT LANGUAGE="JavaScript">
							<!--
								alert('<?=$site->sys_sona(array(sona => "ERROR: File upload error", tyyp=>"admin"))?>');
							//-->
							</SCRIPT>
						<?
						$site->debug->msg("Warning! Can't move uploaded file: <b>'".$file['name']."'</b> into document directory: <b>'".$site->absolute_path.$site->CONF["documents_directory"]."/'</b>. Permission denied.");
					}
				} else {
					$sql = $site->db->prepare("
						update obj_dokument set sisu_blob=?, repl_last_modified=? WHERE objekt_id=?",
						$data_big,
						$file[modified],
						$objekt->objekt_id
					);
					$sth = new SQL($sql);
					$site->debug->msg($sth->debug->get_msgs());
				}
			}
		}

		$site->debug->msg("sisu on salvestatud, objekt_id = ".$objekt->objekt_id);
		#$site->debug->print_hash($site->fdat,1,"FDAT");	

	} else {
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}

}
