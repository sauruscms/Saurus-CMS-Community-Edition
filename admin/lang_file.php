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


/**
 * Saurus CMS language file POPUP, opened from page "Languages > Glossary"
 * 
 */

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");

$site = new Site(array(
	on_debug=> ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));
if (!$site->user->allowed_adminpage()) {
	exit;
}

######################
# leida valitud keele p�hjal �ige lehe encoding,
# admin-osa keel j��b samaks

$keel_id = (int)(isset($site->fdat[flt_keel]) ? $site->fdat[flt_keel] : $site->fdat[keel_id]);
if (!strlen($keel_id)) { $keel_id = $site->glossary_id; }

$sql = "SELECT * FROM keel where keel_id = ?";
$sql = $site->db->prepare($sql,$keel_id);
$sth = new SQL($sql);
$site->debug->msg($sth->debug->get_msgs());	
$tmp = $sth->fetch();
$page_encoding = $tmp['encoding'];
$page_lang_name = $tmp['nimi'];

##################
# default op is "import"
$site->fdat['op'] = $site->fdat['op'] ? $site->fdat['op'] : 'import';




######################
# OP: export
# Language-file export. 
if ($site->fdat['op'] == 'export' && $site->fdat['op2'] == 'salvesta'){

	header("Content-Disposition: attachment; filename=\"language".$keel_id.".csv\"");
	header("Content-Type: plain/text");
	header("cache-control: nocache");
	echo export2file();
	exit;

}
# / OP: export
######################



###############################
# START HTML


######### get adminpage name
$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
$parent_pagename = $adminpage_names['parent_pagename'];
$pagename = $adminpage_names['pagename'];

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->admin->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$page_encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/scms_general.css">
	<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>');
//-->
</SCRIPT>
</head>

<body>
<?
#################
# IMPORT FORM

if($site->fdat['op'] == 'import' || $site->fdat['op'] == 'import_cvs') { ?>

<FORM method=post action="<?=$site->self ?>" enctype="multipart/form-data">
<?php create_form_token('import-glossary'); ?>

<!-- Popup table -->
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<tr> 
	<TD valign="top" width="100%" class="scms_table" height="100%" style="background: #fff;">

	<!-- Scrollable area -->
	<div id=listing class="scms_middle_div">

	<!-- Adding-more-space table -->
	<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_table">
	<tr>
	<td>
	<br />
		<!-- Content table with border -->
		  <table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">
			<tr> 
			  <td colspan="2"> 
				<div style="position:relative"> 
				  <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "Glossary import", tyyp=>"admin"));?><?php echo (is_numeric($site->fdat['flt_keel']) ? ' : '.$page_lang_name : ''); ?></div>
				</div>
			  </td>
			</tr>
			<tr> 
			  <td colspan=2 class="scms_table">
	<?
	########################
	# STEP 2: SAVE IMPORT
	if($site->fdat['op2'] == 'salvesta') {
		verify_form_token();
		import2glossary(); 
	}
	########################
	# STEP 1: FORM
	else { ?>

		<?
		########################
		# import_type
		?>
		<table width="100%" border=0 cellspacing=0 cellpadding=0>

		<tr>
		<td width="20px"><input type=radio name="import_type" id="import_type1" value="download" checked></td>
		<td colspan="2"><label for="import_type1"><?=$site->sys_sona(array(sona => "Nupp: Request downloading from supplier", tyyp=>"admin"));?></label></td>
		</tr>

		<tr>
		<td><input type=radio name="import_type" id="import_type2" value="upload"></td>
		<td nowrap><label for="import_type2"><?=$site->sys_sona(array(sona => "Upload new file", tyyp=>"admin"));?></label></td>
		<td width="100%">	
		<input type=file name=file class="scms_flex_input" onclick="document.getElementById('import_type2').checked=true;"></td>
		</tr>

		<tr><td colspan="3">&nbsp;</td></tr>


		<tr>
		<td><input type=checkbox name="overwrite_user_translations" id="overwrite_user_translations" value="1"></td>
		<td colspan="2"><label for="overwrite_user_translations"><?=$site->sys_sona(array(sona => "Overwrite custom translations", tyyp=>"admin"));?></label></td>
		</tr>

		<tr>
		<td><input type=checkbox name="delete_old_data" id="delete_old_data" value="1" checked></td>
		<td colspan="2"><label for="delete_old_data"><?=$site->sys_sona(array(sona => "Delete old data", tyyp=>"admin"));?></label></td>
		</tr>
		
		</table>

	<?} # form?>


				</td>
			</tr>
		  </table>
		<!-- / Content table with border -->
	</td>
	</tr>
	</table>
	<!-- / Adding-more-space table -->
	
	</div>
	<!-- / Scrollable area -->
</td>
</tr>
<?#################### BUTTONS ###########?>
<tr> 
	<td align="right" valign="top" class="scms_dialog_area_bottom"> 
		<input type=hidden name=op value="import">
		<input type=hidden name=op2 value="salvesta">
		<input type=hidden name=keel_id value="<?=$keel_id ?>">
	<?if(!$site->fdat['op2']) {?>
		<input type="submit" value=" <?=$site->sys_sona(array(sona => "salvesta", tyyp=>"editor"))?> ">
	<?}?>
	<input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.opener.location.href = window.opener.location.href; window.close();">
	</td>
</tr>
</table>
<!-- / Popup table -->

</FORM>





			

	# / DATA TABLE
	################
	?>    

</td>
</tr>
</table>

<?
}
# / IMPORT FORM
################


#################
# EXPORT FORM

elseif($site->fdat['op'] == 'export') { ?>


<!-- Popup table -->
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<tr> 
	<TD valign="top" width="100%" class="scms_table" height="100%" style="background: #fff;">

	<!-- Scrollable area -->
	<div id=listing class="scms_middle_div">

	<!-- Adding-more-space table -->
	<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_table">
	<tr>
	<td>
	<br />
		<!-- Content table with border -->
		  <table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">
			<tr> 
			  <td colspan="2"> 
				<div style="position:relative"> 
				  <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "Glossary export", tyyp=>"admin"));?>: <?=$page_lang_name?></div>
				</div>
			  </td>
			</tr>
			<tr> 
			  <td colspan=2 class="scms_table">
	<?
	########################
	# STEP 1: FORM
	?>
	<FORM method=post action="<?=$site->self ?>" target=_new>

		<table width="100%" border="0" cellspacing="0" cellpadding="0"  class="scms_table">

		<tr>
		<td><?=$site->sys_sona(array(sona => "type", tyyp=>"admin"))?>: </td>

		<td width="100%">
			  <select name="sst_id" class="scms_flex_input">
				  <option value="default">--- <?=$site->sys_sona(array(sona => "default", tyyp=>"admin"))?> ---
				  <option value="">--- <?=$site->sys_sona(array(sona => "koik", tyyp=>"editor"))?> ---
		<?			
					$sql = $site->db->prepare("SELECT sys_sona_tyyp.sst_id, sys_sona_tyyp.nimi 
						FROM sys_sona_tyyp
						ORDER BY sys_sona_tyyp.nimi"
					);
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());
				
				while ($sst = $sth->fetch()) {
					print "	<option value=\"$sst[sst_id]\">$sst[nimi]</option>";
				}
		?>
			</select>
			
		</td>
		</tr>
		</table>

				</td>
			</tr>
		  </table>
		<!-- / Content table with border -->
	</td>
	</tr>
	</table>
	<!-- / Adding-more-space table -->
	
	</div>
	<!-- / Scrollable area -->
</td>
</tr>
<?#################### BUTTONS ###########?>
<tr> 
	<td align="right" valign="top" class="scms_dialog_area_bottom"> 
		<input type=hidden name=op value="export">
		<input type=hidden name=op2 value="salvesta">
		<input type=hidden name=keel_id value="<?=$keel_id ?>">
	<?if(!$site->fdat['op2']) {?>
		<input type="submit" value=" <?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor"))?> ">
	<?}?>
	<input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();">
	</td>
</tr>
</table>
<!-- / Popup table -->

</FORM>

<?
}
# / EXPORT FORM
################



$site->debug->msg("SQL p�ringute arv = ".$site->db->sql_count."; aeg = ".$site->db->sql_aeg);

$site->debug->msg("T��AEG = ".$site->timer->get_aeg());

$site->debug->print_msg();

# / START HTML
###############################

?>
</body>
</html>


<?



######################
# OP: import_cvs - Only for Saurus
#
# imports system words from CVS master copy  - different database

function import_cvs(){

	global $site;

	################
	# connect with distant CVS master database
	unset($site->db);
	$site->db = new DB(array(
			"host"	=> $site->CONF["dbhost"],
			"port"	=> $site->CONF["dbport"],
			"dbname"=> "saurus4",
			"user"	=> $site->CONF["user"],
			"pass"	=> $site->CONF["passwd"],
			'mysql_set_names' => $site->CONF["mysql_set_names"],
		));
	if ($site->db->error) { 
		print "<font face=\"arial, verdana\" color=red>Error! Can't connect to database!</font>";
		exit;
	}
	# / connect with distant CVS master database
	################

	################
	# export selected language dictionary & save it to variable

	$exportfile = export2file();

	################
	# connect with default database again
	unset($site->db);
	$site->db = new DB(array(
			"host"	=> $site->CONF["dbhost"],
			"port"	=> $site->CONF["dbport"],
			"dbname"=> $site->CONF["db"],
			"user"	=> $site->CONF["user"],
			"pass"	=> $site->CONF["passwd"],
			'mysql_set_names' => $site->CONF["mysql_set_names"],
		));
	if ($site->db->error) { 
		print "<font face=\"arial, verdana\" color=red>Error! Can't connect to database!</font>";
		exit;
	}
	# connect with default database again
	################

	################
	# import file content into current database

	$site->fdat['op2'] = "salvesta";
	$site->fdat['op'] = "import_cvs";
	$site->fdat['import_type'] = "cvs";

	# import file content into current database
	################

	return $exportfile;
}
# / FUNCTION import_cvs - Only for Saurus
######################





#########################
# function import2glossary

function import2glossary() {
	global $site, $keel_id, $class_path;
	
	include_once($class_path.'lang_functions.inc.php');

	######################
	# set local_file_name
	if (preg_match("/^[^\?]*\//", $_SERVER["SCRIPT_FILENAME"], $matches)) {
		$doc_root = $matches[0];
		$doc_root = preg_replace("/\/admin\//i", "", $doc_root);
	}
	# if export
	$local_file_name = $doc_root.$site->CONF['file_path']."/language".$keel_id."_local.csv";


	########################
	# 1. import_type = UPLOAD : file given by user
	if ($site->fdat[import_type] == "upload"){
		if ($_FILES['file']['tmp_name']!='none'){
			if (move_uploaded_file($_FILES['file']['tmp_name'], $local_file_name)){    
				#print $site->sys_sona(array(sona => "msg: Language file uploaded", tyyp=>"admin")).".";
				#print "<br>";
			} else {
				$errors[] = $site->sys_sona(array(sona => "ERROR: File upload error", tyyp=>"admin"));
			}
		} 
		else {
			$errors[] = "ERROR: ".$site->sys_sona(array(sona => "filename", tyyp=>"editor"))." ".$site->sys_sona(array(sona => "missing", tyyp=>"admin"));
		}

	}

	########################
	# 2. import_type = DOWNLOAD : from Saurus site

	else if ($site->fdat[import_type] == "download"){

		$url = $site->CONF[protocol].$site->hostname.$site->wwwroot;
		$remote_file = "http://extranet.saurus.ee/register/download_lang_file4.php?lang_id=".$keel_id."&url=".$url."/&license_key=".$site->license;

			$response = fopen_url_auth($remote_file, 'register', 'register', 'Saurus CMS '.$site->cms_version);

			if ($response === false) { 
				$errors[] = "SYSTEM ERROR: The requested URL not found";
				
			} else {

				# Salvestame lang-file local-kataloogis 
				// siin mingit vea checki ei peaks olema???
				$local_file = fopen ($local_file_name, "w");
				fwrite ($local_file, $response);
				fclose ($local_file);
			}
	} 
	# 2. / import_type = DOWNLOAD : from Saurus site
	########################
	########################
	# 3. import_type = CVS : import dictionary from CVS master copy (only for Saurus)
	elseif ($site->fdat['import_type'] == "cvs"){

		$exportfile = import_cvs();

#echo $exportfile;

		# Salvestame lang-file local-kataloogis
		$local_file = fopen ($local_file_name, "w");

		fwrite ($local_file, $exportfile);
		fclose ($local_file);

	}
	# 3. import_type = CVS : import dictionary from CVS master copy (only for Saurus)
	########################
	if(!import_dict_from_file($local_file_name, ($site->fdat['overwrite_user_translations'] ? true : false), ($site->fdat['delete_old_data'] ? true : false)))
	{
		echo '<span style="color: red;">Dictionary import failed.</span>';
	}
	else
	{
		echo '<span>Dictionary import complete.</span>';
	}
	@unlink($local_file_name);
}
# / function import2glossary
#########################


#########################
# function export2file

function export2file() {
	global $site, $keel_id;

	########################
	# if keel_id is OK - number
	if (is_numeric($keel_id)){

		$sql = $site->db->prepare("SELECT sys_sona_tyyp.* 
			FROM sys_sona_tyyp"
		);
		
		# if sysword type selected in select-box
		$sql .= $site->fdat[sst_id] != '' && $site->fdat[sst_id] != 'default' ? " AND sst_id='". $site->fdat[sst_id] ."'" : "";

		# if 'All in default CMS' is selected in select-box
		# then exclude modules: 9-atp, 15-servit, 20-hex, 7 - Personnel, 
		# exclude ->  custom type: sst_id=23; metadata: sst_id=15; product_profiles: sst_id=22
		# exclude -> all types ID > 100 (extension stuff)
		$sql .= $site->fdat[sst_id] == 'default' ? " AND NOT FIND_IN_SET(sys_sona_tyyp.moodul_id,'9,20,15,7') AND NOT FIND_IN_SET(sys_sona_tyyp.sst_id,'23,15,22') AND (sys_sona_tyyp.sst_id < 100 OR sys_sona_tyyp.voti = 'saurus4')" : "";

		$sql .= " ORDER BY sst_id";

		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());	
		$total = 0;
		$tyyp_arv = 0;
		$corrupted = Array();
		$descriptions = Array();
		$translations = Array();
		$output = "";
		

		# tsykkel yle peatyybid:
		while ($styyp = $sth->fetch()){
			# [EDITOR:3:Editor]
			$output .= "\n\n\n[".$styyp[voti].":".$styyp[sst_id].":".$styyp[nimi]."]\n";
		
				$sql2 = "
			SELECT sys_sonad_kirjeldus.sst_id, sys_sonad_kirjeldus.sys_sona,
			sys_sonad_kirjeldus.sona AS description, sys_sonad.sona AS translate

			FROM sys_sonad_kirjeldus
			LEFT JOIN sys_sonad ON
			sys_sonad_kirjeldus.sst_id = sys_sonad.sst_id AND
			sys_sonad_kirjeldus.sys_sona = sys_sonad.sys_sona
			WHERE sys_sonad_kirjeldus.sst_id = ?  AND sys_sonad.keel=?
			GROUP BY sys_sonad_kirjeldus.sys_sona
			ORDER BY sys_sonad_kirjeldus.sys_sona					
				";
				$sql2 = $site->db->prepare($sql2, $styyp[sst_id], $keel_id);

			//echo "<hr>".$sql2."<hr>";
				$sth2 = new SQL($sql2);
				$site->debug->msg($sth2->debug->get_msgs());

				# tsykkel yle sys_sonad konkreetsel tyybil (sys_sona,description,translate):
				$temp_total = 0;
				while ($ssona = $sth2->fetch()){
					#Loe edasi;Link Read More after artticle;More;
					$tmpstr = $ssona[sys_sona].";".$ssona[description].";".$ssona[translate].";\n";
					$output .= $tmpstr;

					if (strlen($ssona[description])==0){$descriptions[]= strtoupper ($styyp[voti]).": ".$ssona[sys_sona];};
					if (strlen($ssona[translate])==0){$translations[]= strtoupper ($styyp[voti]).": ".$ssona[sys_sona];};
					if (substr_count($tmpstr, ";")!=3){$corrupted[] = strtoupper ($styyp[voti]).": ".$tmpstr;};

					$total++;
					$temp_total++;
				}
		# for debug:
		# $output .= "\n". $styyp[sst_id] ." SUM: ".$temp_total;	
		$tyyp_arv++;
		}

		$sql = "SELECT nimi, encoding FROM keel WHERE keel_id = ?";
		$sql = $site->db->prepare($sql, $keel_id);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$tmprec = $sth->fetch();
		$keelnimi = $tmprec['nimi'];
		$keelencoding = $tmprec['encoding'];

		#####################
		# if OK - not corrupted

		if (count($corrupted)==0) {

		# [CHECKSUM=0:3:6]
		$checksum = "[CHECKSUM=".$keel_id.":".$tyyp_arv.":".$total."]\n";

		# [DATE=2002-08-02 12:35:59]
		$date = "[DATE=".date('Y-m-d H:i:s')."]\n";

		# [ENCODING=UTF-8]
		$encoding = "[ENCODING=".$keelencoding."]\n";

		$summary = "
#
#  Language file: ".$keelnimi."
#  Supplier: www.saurus.info 
#  
#  Total sys strings: ".$total."
#  Total descriptions: ".($total-count($descriptions))."
#  ".(count($descriptions)>0 ? "Status descriptions: BAD\n#\n#  Need to be describe:\n#    ".join("\n#    ",$descriptions):"Status descriptions: OK")."
#  Total translations: ".($total-count($translations))."
#  ".(count($translations)>0 ? "Status translations: BAD\n#\n#  Need to be translate:\n#    ".join("\n#    ",$translations):"Status translations: OK")."


# SYSTEM STRING; DESCRIPTION; TRANSLATION;
";

		######################
		# return file 

		return $checksum.$date.$encoding.$summary.$output;
	
		}
		# / if OK - not corrupted
		#####################

		else {
			return "ERROR: Export data is corrupted (Lang_id: ".$keel_id."; Lang: ".$keelnimi.")!\n\n Check this words(too many semicolons):\n\n".join("",$corrupted);
		}
		# / if corrupted
		#####################

	} 
	# / if keel_id is OK - number
	########################
	
	else { return "ERROR: Language id is empty!"; }

}

# / function export2file
#########################
