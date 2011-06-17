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
 * Saurus CMS adminpage "Templates > Object templates"
 * 
 */

global $site;
$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");

$site = new Site(array(
	on_debug => $_COOKIE["debug"] ? 1:0,
	on_admin_keel => 1
));

if (!$site->user->allowed_adminpage()) {
	exit;
}

######### get adminpage name
$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
$parent_pagename = $adminpage_names['parent_pagename'];
$pagename = $adminpage_names['pagename'];

# -------------------------
# otsime lubatud keeled
# -------------------------

	$sql = $site->db->prepare(
		"SELECT objekt_id, keel, keel.nimi 
		FROM objekt 
		LEFT JOIN keel ON objekt.keel = keel.keel_id
		WHERE sys_alias = 'home'"
	);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	while ($home_rub = $sth->fetch()) {
		$allowed_langs[] = $home_rub['keel'];
	}

#
# SAVE !!
#

if($site->fdat[save]) {
	verify_form_token();	
	foreach($site->fdat as $key => $value) {
		if(eregi("^tyyp_id(.+)",$key,$regs)) {
			$sql = $site->db->prepare("UPDATE tyyp SET ttyyp_id = ? WHERE tyyp_id = ?",$value,$regs[1]);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}
	}
}

##################################
# What types to display
##################################

	$types = array(
					2, # art
					6, # poll
					7, # dok
					12, # pilt
					14, # kommentaar
					15, # teema
					16, # album
					19, # prod cat
					20, # asset	
					21, # file
					22, # folder
				);

?>

<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
  <META NAME="Author" CONTENT="Saurus">
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
# CONTENT TABLE
?>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
 <?
 ##############
 # FUNCTION BAR
 ?>
  <tr> 
   <td class="scms_toolbar"> 
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		<?############ save button ###########?>
	    <td nowrap><a href="javascript:document.forms['vorm'].submit();"><img src="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/gfx/icons/16x16/actions/filesave.png" border="0" id="pt"> <?=$site->sys_sona(array(sona => "salvesta", tyyp=>"editor"))?></a></td>

		<?###### wide middle cell ######?>
        <td width="100%"></td>

		</tr>
      </table>
    </td>
  </tr>
 <?
 # / FUNCTION BAR
 ################
 ?>

 <tr>
  <td width="100%" valign="top" height="100%"> 	
		<div id=listing class="scms_middle_div" style="min-height: 440px"> 
		<?
			################
			# DATA TABLE
		?>

		<FORM action="<?=$site->self?>" method="post" name="vorm">
		<?php create_form_token('edit-object-templates'); ?>
		<input type=hidden name="save" id="save" value="1">

			  <table border="0" cellspacing="0" cellpadding="3" width="100%">
				<tr  class="scms_tableheader"> 
				  <td nowrap class="scms_tableheader"><?=$site->sys_sona(array(sona =>"Type", tyyp=>"admin"))?></td>
				  <td nowrap>&nbsp;</td>
				  <td nowrap><?=$site->sys_sona(array(sona =>"Content template", tyyp=>"editor"))?></td>
				</tr>
		<?
			$sql = "SELECT * FROM tyyp WHERE tyyp_id in (".(join(",",$types)).") ORDER BY tyyp_id";
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			$idx = 0;
			while ($type = $sth->fetch()) {
			$idx++;
		?>
		<tr> 

				<td nowrap><?=$site->sys_sona(array(sona =>"tyyp_".$type[nimi], tyyp=>"system"))?></td>
				<td>&nbsp;</td>

				<td>
					<select name="tyyp_id<?= $type[tyyp_id]; ?>" class='scms_flex_input' style='max-width: 334px'>

					<?# print selectbox option rows and get selected template array, fn() in classes/adminpage.inc
					$ttyyp = print_template_selectbox($type['ttyyp_id'],'object');
					?>
					</select>
				  </td>

				</tr>
		<? }// while  ?>
			  </table>
		</div>
	</td>
</tr>
</table>
	


</FORM>
</body>
</html>