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

#################################
# query 1 template

global $site;

if(empty($site)) exit;

if ($site->fdat[id]) {
	$sql = $site->db->prepare(
		"SELECT templ_tyyp.*
	     FROM templ_tyyp
	     WHERE ttyyp_id=? ", $site->fdat[id]);

	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	$ttyyp = $sth->fetch();
	$id = $ttyyp[ttyyp_id];

	if($fd = fopen ($templ_path.$ttyyp[templ_fail], "rb")){
		$templ_content = fread($fd, 1024*1024);
		fclose($fd);
		$templ_content = eregi_replace('<textarea','<#textarea',$templ_content);
		$templ_content = eregi_replace('</textarea','<#/textarea',$templ_content);
	} else {
		$templ_content = "File ".$ttyyp[templ_fail]." not found!";
	}

} else {
	$id = '';
	$ttyyp = '';
}
# / query 1 template
#################################

if (!$ttyyp) {
	$ttyyp_id = '';
	$ttyyp = '';
}


$site->debug->msg("OP = $site->fdat['op']");

#################################
# check privileges
if (!$site->user->allowed_adminpage()) {
	exit;
}

#################
# STEP2:  SAVE DATA : close popup or don't close and refresh
if($site->fdat['op2'] && !$site->fdat['refresh']) {

	$site->debug->msg("Salvestamine, $site->fdat['op']");

	###############################
	# dont let change default templates - changed by Bug #1462
#	if($id >= 2000) {
#		print "<br><font class=txt><font color=red><b>".$site->sys_sona(array(sona => "Templ_saving_denied", tyyp=>"sapi"))."</b></font></font>";
#		exit;
#	}

	###############################
	# n�utud v��rtuste olemasolu kontroll
	if ($site->fdat["nimi"] == '') {
		$error = "<br><br><font face=verdana size=2>Name is missing!</font>";
	}

	###########################################
	# ALLOW_PHP_TAGS: check for disabled content (PHP-code):
	if (!$site->CONF['allow_php_tags']){

		$check_content = strtolower($site->fdat['templ_content']);

		$all_php_funcs = array();
		$arr = get_defined_functions();
		$ar=array();

		foreach($arr as $k=>$v){

				foreach($arr[$k] as $k2=>$v2){

					if(!$error){
						$ar[$k][]=$v2;
					}

					if($v2=="zend_optimizer_version"){
						break;
					}


				}
		}

		# Comment this line, if you want search ONLY custom words
		$all_php_funcs = $ar['internal']; 

		$custom_disabled_words = array('{php}','{/php}','`','file', 'site->db', 'feof', 'exec', 'eval', 'msql', 'bind', 'read', 'printr');
		$skip_bad_words = array ('each', 'define', 'date', 'link', 'round', 'header', 'print', 'foreach', 'count', 'floor', 'defined', 'current', 'reset'); // remove this words from common array

		# put together all arrays:
		$disabled_words = array_diff(array_merge($custom_disabled_words, $all_php_funcs), $skip_bad_words); 
		
		foreach($disabled_words as $badword){	 

			if (in_array($badword, $custom_disabled_words) || strlen($badword)>4){

				$from_repl = preg_replace("/\//", "\\\/", $badword);
				$badword_found = preg_match('/\b('.$from_repl.')\b/i', $check_content);

				# pregmatch ei taha neid v6tta millegiparast:
				if ($badword=='{php}' || $badword=='{/php}' || $badword=='`'){
					if (substr_count($check_content, $badword)){
						$err_word[] = $badword;
					}
				}

				if ($badword_found){
					# Compatible with SAPI 'init_documents':
					if ($badword=='file'){
						if (substr_count($check_content, 'file') != substr_count($check_content, '->file')){
							$err_word[] = $badword;
						}
					} else {
						$err_word[] = $badword;
					}
				}
			}
		};

		if (count($err_word)){
			$error .= "<font face=verdana size=2><br><br>PHP-tags and functions within templates are not allowed in this site!<br> For further information contact your site administrator.";
			if ($site->on_debug||1) $error .= "<br><br>".join(", ", array_unique($err_word));
			$error .= "</font>";
		}
	}
	# / ALLOW_PHP_TAGS: check for disabled content (PHP-code):
	###########################################


	###############################
	# tee malli faili f��s.nimi malli nime p�hjal
	if (!$error && ($site->fdat['op'] == "edit" || $site->fdat['op'] == "new")) {	
		if ($site->fdat['op'] == "edit") {	
			$filename = $ttyyp[templ_fail];
			# ------------------------
			# Puhastame CACHE table
			# ------------------------
			clear_cache("ALL");
		} 
		else {
			#############################
			# andmebaasis topelt malli nime olemasolu kontroll

			$sql = $site->db->prepare(
				"SELECT count(*) from templ_tyyp where nimi = ?",
				$site->fdat["nimi"]
			); 
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			$on_olemas = $sth->fetchsingle();
			
			if ($on_olemas) {
				# malli nimi juba olemas, veateade
				$error = $site->sys_sona(array(sona => "Faili nimi juba kinni", tyyp=>"editor"));
			}

			if(!$error) {

				#############################
				# safe failinime tegemine

				$filename = trim(preg_replace("/[^\w\.]/","_",$site->fdat[nimi])).".html";
				$filename = safe_filename($filename);
				$site->debug->msg("Template file safe name = $filename");

				############################
				# topelt kontroll: ega pole f��siliselt sellist faili olemas
				# kui on, pane number l�ppu

				$filename_original = $filename;
				$i=1;
				while (file_exists($templ_path.$filename)) {
					# Keerle tsyklis kuni leiad vaba failinime
					if (preg_match("/^(.*)\.(.*?)$/",$filename_original, $matches)) {
						$filename = $matches[1].++$i.".".$matches[2];
					}
				}
			} # if !error

		}
		$site->debug->msg("Template file final name = $filename");
	}
	# / tee malli faili f��s.nimi malli nime p�hjal
	###############################

	###############################
	# MALLI SISU: textarea sisu faili
	if(!$error){

		$site->fdat["templ_content"] = eregi_replace('<#textarea','<textarea',$site->fdat["templ_content"]);
		$site->fdat["templ_content"] = eregi_replace('<#/textarea','</textarea',$site->fdat["templ_content"]);

	    $out = fopen($templ_path.$filename, "wb");
		fputs($out, $site->fdat["templ_content"]);
		if(!$out) {
			$error = $site->sys_sona(array(sona => "Faili salvestamisel tekkis viga", tyyp=>"editor")).$templ_path.$filename;
		}
		fclose($out);
	}
	# / MALLI SISU: textarea sisu faili
	###############################

	
	###############################
	# salvesta baasi

	/* translate op into estonian */
	$site->fdat['op_value'] = translate_ee($site->fdat['op_value']);
	
	if (1) {
		# --------------------------
		# INSERT INTO - UUS MALL
		# --------------------------

		if (!$error && $site->fdat['op'] == "new") {

			#######
			# find new id
			# allowed are: 1000..1999, 2100..N/A

			$sql = $site->db->prepare(
				"SELECT max(ttyyp_id) FROM templ_tyyp WHERE ttyyp_id >= 1000 AND ttyyp_id < 2000 
				OR ttyyp_id >= 2100"
			); 
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			$max_id = $sth->fetchsingle();
			if($max_id) {
				$max_id++;
			}
			else {
				$max_id = 1000;
			}

			############################
			## set op with posted value to NULL
			if($type != 'page'){ # dont show op for page templates (bug #1964)

				$sql=$site->db->prepare("update templ_tyyp set op=NULL where op=?;",$site->fdat['op_value']);
				$result=new SQL($sql);
				unset($result);
			}
			##
			###########################
			
			$sql = $site->db->prepare("INSERT INTO templ_tyyp (ttyyp_id, op, nimi, templ_fail, on_page_templ, on_nahtav, on_auto_avanev) values(?,?,?,?,?,?,?)", 
				$max_id,
				$site->fdat['op_value'], 
				$site->fdat[nimi], 
				$filename, 
				$site->fdat[on_page_templ],
				$site->fdat[on_nahtav],
				$site->fdat[on_auto_avanev]
				); 
			$sth_i = new SQL($sql);
			$site->debug->msg($sth_i->debug->get_msgs());
			new Log(array(
				'action' => 'create',
				'component' => 'Templates',
				'message' => "New template '".$site->fdat[nimi]."' (ID=".$max_id.") added",
			));
			$site->fdat['id'] = $sth_i->insert_id;

		} elseif ($site->fdat['op'] == "edit") {
		# --------------------------
		# UPDATE - MUUDA            
		# --------------------------

			if(!$filename)
			{
					$filename = $ttyyp['templ_fail'];
					clear_cache('ALL');
			}
			
			############################
			## set op with posted value to NULL
			if($type != 'page'){ # dont show op for page templates (bug #1964)

				$sql=$site->db->prepare("update templ_tyyp set op=NULL where op=?;",$site->fdat['op_value']);
				$result=new SQL($sql);
				unset($result);
			}
			##
			###########################

			$sql = $site->db->prepare("UPDATE templ_tyyp SET nimi=?, op=?, templ_fail=?, on_page_templ=?, on_nahtav=?, on_auto_avanev=? WHERE ttyyp_id=?", 
				$site->fdat[nimi], 
				$site->fdat['op_value'], 
				$filename, 
				$site->fdat[on_page_templ],
				$site->fdat[on_nahtav],
				$site->fdat[on_auto_avanev],
				$id); 
			$sth_i = new SQL($sql);
			$site->debug->msg($sth_i->debug->get_msgs());

			new Log(array(
				'action' => 'update',
				'component' => 'Templates',
				'message' => "Template '".$ttyyp[nimi]."' (ID=".$id.") updated",
			));
		}
	}
	# / salvesta baasi
	###############################

	########################
	# error
	if ($error) {
		print "<p align=center>$error<br><br><a href=\"javascript:history.back()\">".$site->sys_sona(array(sona => "Tagasi", tyyp=>"editor"))."</a></p>";
		exit;
	} 
	############## if update then REDIRECT PAGE: to get correct GET URL again
	elseif($site->fdat['op2']=='save') {
		header("Location: ".$site->self."?op=edit&id=".$site->fdat['id']);
	}
	########################
	# close window
	elseif (!$site->on_debug && $site->fdat['op2']=='saveclose') {
?>
	<SCRIPT language="javascript">
	<!--
		window.opener.location=window.opener.location;
		window.close();
	// -->
	</SCRIPT>
<?
	}
	exit;
} 

# / SALVESTA: INSERT ja UPDATE
#############################

#################################
# start html

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>');
//-->
</SCRIPT>
</head>

<body class="popup_body" onLoad="this.focus()" >
<?

#############################
# DELETE
#############################

if ($site->fdat['op'] == "delete_confirmed") {

	########################
	# dont let change default templates
	if($ttyyp[ttyyp_id] >= 2000) {
		print "<br><font class=txt><font color=red><b>".$site->sys_sona(array(sona => "Templ_saving_denied", tyyp=>"admin"))."</b></font></font>";
		exit;
	}
	########################
	# delete record from database
	$sql = $site->db->prepare("DELETE FROM templ_tyyp WHERE ttyyp_id=?", $ttyyp[ttyyp_id]); 
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	########################
	# delete file
	unlink($templ_path.$ttyyp[templ_fail]);

	########################
	# close window
	if (!$site->on_debug)  {
		?>
		<SCRIPT language="javascript">
		<!--
			window.opener.location=window.opener.location;
			window.close();
		// -->
		</SCRIPT>
		<?
	}
	########################
	# kirjuta toimetajate logi
	new Log(array(
		'action' => 'delete',
		'component' => 'Templates',
		'message' => "Template '".$ttyyp[nimi]."' (ID=".$id.") deleted",
	));
	exit;
}

# / DELETE
#############################

#################################
# start html (always use fixed charset=ISO-8859-15)

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-15">
<link rel="stylesheet" href="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>');
//-->
</SCRIPT>
</head>

<body class="popup_body" onLoad="this.focus()" >
<?

#############################
# Popup aken
# Muutmise vorm
#############################

if ($site->fdat['op'] == "edit" || $site->fdat['op'] == "new") {

# kui tegu muutmisega
if ($site->fdat[id]) {
	$myttyyp = &$ttyyp;
	$site->debug->print_hash($ttyyp,"1","ttyyp");
}
?>
<form action="<?$site->self?>" method="post" name="vorm" enctype="multipart/form-data">

<?###### 1. Master table ?>
<TABLE border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<TR>
<TD valign="top" width="100%" class="scms_dialog_area"  height="100%">


	<?###### 2. White dialog table ?>
	<table width="100%"  height="100%" border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">

	<tr valign=top> 
          <td colspan="2"> 
            <div style="position:relative"> 
              <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "Templates", tyyp=>"admin"))?></div>
            </div>
          </td>
        </tr>

	<tr valign=top>
	<td>

		<?###### 3. Content table ?>		
		<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_table">
<?
#######################
# name
?>
<tr> 
            <td><?=$site->sys_sona(array(sona => "Nimetus", tyyp=>"editor"))?>:</td>
            <td style="width:40%" align="left">
			<?if($myttyyp['is_readonly']){?>
				<?=$myttyyp['nimi']?>
				<input type=hidden name="nimi" value="<?=$myttyyp['nimi']?>" class="scms_flex_input">
			<?}else{?>
				<input type=text name="nimi" value="<?=$myttyyp['nimi']?>" class="scms_flex_input">
			<?}?>
			</td>

			<?
			#######################
			# templ_fail
			?>
			<td width=300 rowspan=1 valign=top>
            <?=$site->sys_sona(array(sona => "Filename", tyyp=>"editor"))?>: <?=$myttyyp['templ_fail']?>
            <input type=hidden name="templ_fail" value="<?=$myttyyp['templ_fail']?>">	
			</td>			
</tr>

<?
#######################
# is active + is autoavanev + op
$myttyyp['op'] = ($myttyyp['op']=='kaart' ? 'sitemap' : $myttyyp['op']);
$myttyyp['op'] = ($myttyyp['op']=='tappisotsing' ? 'advsearch' : $myttyyp['op']);
$myttyyp['op'] = ($myttyyp['op']=='remindpass' ? 'saadaparool' : $myttyyp['op']);
$myttyyp['op'] = ($myttyyp['op']=='arhiiv' ? 'archive' : $myttyyp['op']);
$myttyyp['op'] = ($myttyyp['op']=='gallup_arhiiv' ? 'poll_archive' : $myttyyp['op']);
?>
	<tr> 
    <td nowrap colspan="2">
	<input type=checkbox id="on_nahtav" name="on_nahtav" value="1" <?=($myttyyp[on_nahtav] ||  $site->fdat['op'] == "new"?" checked":"")?>>
	<label for="on_nahtav"><?=$site->sys_sona(array(sona => "Visible in section editor", tyyp=>"admin"))?></label></td>

	<td nowrap >
	<input type=checkbox id="on_auto_avanev" name="on_auto_avanev" value="1" <?=($myttyyp[on_auto_avanev] ||  $site->fdat['op'] == "new"?" checked":"")?>>
	<label for="on_auto_avanev"><?=$site->sys_sona(array(sona => "auto-jump", tyyp=>"admin"))?></label>
	&nbsp;&nbsp;&nbsp;
<?if($type != 'page'){ # dont show op for page templates (bug #1964)?>
	<label>op: <input type="text" name="op_value" value="<?=$myttyyp['op']?>" class="scms_flex_input" style="width: 80px;" /></label>
<?}
else { # Bug #2371?>
	<input type="hidden" name="op_value" value="<?=$myttyyp['op']?>"  />
<?}?>
	</td>
    </tr>

<?
#######################
# edit area
?>
          <TR valign="top"> 
			<?if($myttyyp['is_readonly']){?>
				<TD align=left colspan="3"> 
				<div style="overflow:auto;height:450px;border: 1px solid black;padding:5px" >
				<?=nl2br(htmlspecialchars($templ_content))?>
				</div>
	            <input type=hidden name="templ_content" value="<?=htmlspecialchars($templ_content)?>">	
				</TD>
			<?}else{?>
            <TD align=right colspan="3"> 
              <textarea name="templ_content" style="width:100%"  rows=34><?=htmlspecialchars($templ_content)?></textarea>
			  </TD>
			<?}?>
          </TR>

	<input type="hidden" name="id" value="<?=$id?>">
	<input type="hidden" name="op" id="op" value="<?=$site->fdat['op']?>">
	<input type="hidden" name="op2" id="op2" value="<?=$site->fdat['op2']?>">
	<input type=hidden id="refresh" name="refresh" value=0>
	<input type="hidden" name="lisa" value="<?=$site->fdat['lisa']?>">
    <input type=hidden name="on_page_templ" value="<?=($type=='page'?"1":"0")?>">
	</table>
		<?###### / 3. Content table ?>		
        


	</td>
	</tr>
	</table>
	<?###### / 2. White dialog table ?>


</TD>
</TR>
<?############ buttons #########?>
<TR> 
<TD align="right" valign="top" class="scms_dialog_area_bottom"> 
	  <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript:document.getElementById('op2').value='save'; body.style.cursor = 'wait';if(typeof url_browse == 'object'){url_browse.removeNode()}; this.form.submit();">

	   <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:document.getElementById('op2').value='saveclose'; body.style.cursor = 'wait';if(typeof url_browse == 'object'){url_browse.removeNode()}; this.form.submit();">
		
		<input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
</TD>
</TR>
</TABLE>
<?###### / 1. Master table ?>



</body>
</html>
<? 
}
# / Popup aken
#############################


#############################
# LIST
#############################

else { # mallide list
#################
# CONTENT TABLE
	$from_sql = $site->db->prepare("FROM templ_tyyp
		WHERE templ_tyyp.ttyyp_id>=1000
		 AND 1 ",
	1
	);
	$where = $site->db->prepare(" AND templ_tyyp.on_page_templ=? ",	$type=='page' ? 1 : 0 );
	if($site->fdat['templ_keyword'])
	{
		$where .= " AND (templ_tyyp.nimi like '%".mysql_real_escape_string($site->fdat['templ_keyword'])."%' or templ_tyyp.templ_fail like '%".mysql_real_escape_string($site->fdat['templ_keyword'])."%') ";
	}

	if($site->fdat['extension'])
	{
		$where .= " AND templ_tyyp.extension = '".mysql_real_escape_string($site->fdat['extension'])."' ";
	}

?>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
 <?
 ##############
 # FUNCTION BAR
 ?>
  <tr> 
    <td class="scms_toolbar"> 
      <table border="0" cellpadding="0" cellspacing="0">
		<tr>
		<?############ new button ###########?>
	    <td nowrap><a href="javascript:void(openpopup('<?=$site->self?>?op=new','xml','670','620'))"><img src="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/gfx/icons/16x16/actions/filenew.png" border="0" id="pt">&nbsp; <?=$site->sys_sona(array(sona => "new", tyyp=>"editor"))?></a></td>

		<?############ clear cache button ###########?>
	    <td nowrap><a href="javascript:void(openpopup('clear_templ_cache.php','cache','366','450'))"><img src="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/gfx/icons/16x16/actions/delete.png" border="0" id="pt">&nbsp;<?=$site->sys_sona(array(sona => "Clear cache", tyyp=>"sapi"))?></a></td>


		<?###### wide middle cell ######?>
        <td width=100%></td>
       
        <form id="filterForm" method="GET" name="filterForm" onsubmit="this.page.value = 1; return true;">
        
        <input type="hidden" name="page" value="<?=(int)$site->fdat['page'];?>">
        
        <td nowrap style="padding-right: 5px;">
        	<?=$site->sys_sona(array('sona' => 'otsi', 'tyyp' => 'otsing'));?>
        </td>
        <td nowrap style="padding-right: 5px;">
        	<input type="text" value="<?=htmlspecialchars($site->fdat['templ_keyword']);?>" name="templ_keyword" class="scms_flex_input" style="width: 150px;">
        </td>
        <?php
        // get extensions, only those which are in templ_tyyp table, that is only those whicha have templates
        $sql = 'select distinct extensions.name, extensions.title from templ_tyyp left join extensions on templ_tyyp.extension = extensions.name where extension is not null';
        $extensions = new SQL($sql);
        ?>
        <td nowrap style="padding-right: 5px;">
        	<select name="extension" onchange="this.form.page.value = 1; this.form.submit();" class="scms_flex_input" style="width: 150px;">
        		<option value=""></option>
        		<?php while($extension = $extensions->fetch('ASSOC')) { ?>
        		<option value="<?=$extension['name'];?>"<?=($site->fdat['extension'] == $extension['name'] ? 'selected="selected"' : '');?>><?=$extension['title'];?></option>
        		<?php } ?>
        	</select>
        </td>

        </form>

		<?######  pagenumbers ######?>
	   <td class="scms_small_toolbar">
		<?
		# get records total count
		$sql = "SELECT COUNT(*) ".$from_sql.$where;
		$sth = new SQL($sql);
		$total_count = $sth->fetchsingle();

		######### print pagenumbers table
		$pagenumbers = print_pagenumbers(array(
			"total_count" => $total_count,
			"rows_count" => 40,
		));
		?>
		</td>
		<?######  / pagenumbers ######?>	
		</tr>
      </table>
    </td>
  </tr>
 <?
 # / FUNCTION BAR
 ################
 ?>

 <tr>
  <td width="100%" valign="top" class="scms_pane_area" height="100%"> 
	<?
	################
	# DATA TABLE
	?>    
     <table width="100%" border="0" cellspacing="0" cellpadding="0" style="height:100%">
	<?
	#################
	# COLUMN NAMES

	?>
	<tr>
		<td>
			<table width=100% cellpadding=0 cellspacing=0 border=0>
				<col width="50">
				<col width=25%>
				<col width=25%>
			<?if($type != 'page'){ # dont show op for page templates (bug #1964)?>
				<col width=25%>
			<?}?>
				<col width=25%>
				<col width=16>
				<tr class="scms_tableheader"> 
					  <td>ID&nbsp;&nbsp;&nbsp;&nbsp;</td>
					  <td nowrap class="scms_tableheader"><?=$site->sys_sona(array(sona => "name", tyyp=>"admin"))?></td>
					  <td><?=$site->sys_sona(array(sona => "Filename", tyyp=>"editor"))?></td>
				<?if($type != 'page'){ # dont show op for page templates (bug #1964)?>
					  <td>op</td>
				<?}?>
					  <td><?=$site->sys_sona(array(sona => "Active", tyyp=>"admin"))?></td>
<!--					  <td nowrap><?=$site->sys_sona(array(sona => "Extensions", tyyp=>"admin"))?></td>-->
					  <td></td>
					  <td><IMG SRC="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/gfx/general/px.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALT=""></td>
					</tr>
				</table>
		</td>
	</tr>
	<?
	# / COLUMN NAMES
	#################
	?>

	<?
	#################
	# DATA ROWS
	?>	  
	  <tr>
      <td height="100%" valign="top"> 
		<div id=listing class="scms_middle_div" style="min-height: 440px">
		  <table width="100%" border="0" cellspacing="0" cellpadding="3">
				<col width="50">
				<col width=25%>
				<col width=25%>
				<col width=25%>
				<col width=25%>
<? 
	########### ORDER
	$order = " ORDER BY nimi";

	########### SQL

	#- n�idata ainult html-malle: ID >= 1000
	#- kui ID >= 2000 (e default mall), siis arvestada v�lja "on_nahtav" v��rtust, muidu mitte

	$sql = "SELECT templ_tyyp.* ";
	$sql .= $from_sql;
	$sql .= $where;
	$sql .= $order;
	$sql .= $pagenumbers['limit_sql'];

#print $sql;
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

		###########################
		# loop over rows
		while ( $myttyyp = $sth->fetch() ) {
	?>
        <tr> 
          <td class="r<?= $i%2+1 ?>"><?=$myttyyp['ttyyp_id'];?></td>
          <td class="r<?= $i%2+1 ?>" nowrap><a href="javascript:void(avaaken('<?=$site->self?>?op=edit&id=<?=$myttyyp[ttyyp_id]?>','670','620','template'))"><?=$myttyyp[nimi]?></a></td>
          <td class="r<?= $i%2+1 ?>" ><?=$myttyyp[templ_fail]?></td>
		<?if($type != 'page'){ # dont show op for page templates (bug #1964)?>
          <td class="r<?= $i%2+1 ?>" ><?=translate_en($myttyyp['op']);?></td>
		<?}?>
          <td class="r<?= $i%2+1 ?>" ><?=$myttyyp[on_nahtav]?"Y":"N"?></td>
<!--          <td class="r<?= $i%2+1 ?>" ><?=$myttyyp[extension]?></td>-->
          <td class="r<?= $i%2+1 ?>" align="right">
		<? 
		# if default template, dont show edit & delete buttons 
		if($myttyyp[ttyyp_id] < 2000) { ?>
			<a href="javascript:void(avaaken('<?=$site->self?>?op=edit&id=<?=$myttyyp[ttyyp_id]?>','670','620','template'))"><img src="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/gfx/icons/16x16/actions/edit.png" border="0" id="pt"></a
		
		  ><a href='javascript: if (confirm("<?=$site->sys_sona(array(sona => "Kas tahate kustutada", tyyp=>"editor"))?>")) {void(avapopup("<?=$site->self?>?op=delete_confirmed&id=<?=$myttyyp[ttyyp_id] ?>","template","400","400","no"))}'><img src="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/gfx/icons/16x16/actions/delete.png" border="0" id="pt"></a
			><? 
		} 
		 else {
			 print "default";
		 } #if default templ ?></td>
        </tr>
	<?
		++$i;
		}
		# / loop over rows
		###########################
	?>
      </table>

	  </div>
     </td>
    </tr>
	<?
	# / DATA ROWS
	#################
	?>	  

    </table>
	<?
	# / DATA TABLE
	################
	?>    


</td>
</tr>
</table>
<?
# / CONTENT TABLE
################
}

# / LIST
#############################


# kui incude-itud
#############################
