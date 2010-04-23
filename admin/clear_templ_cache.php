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
# POPUP: clears template cache
# deletes all files and directories in classes/smarty/templates_c

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");

//$templ_cache_path = $class_path."smarty/templates_c/";

$site = new Site(array(
	on_debug=>0,
	on_admin_keel => 1
));

$templ_cache_path = $site->absolute_path.'classes/smarty/templates_c/';

#################################
# check privileges
if (!$site->user->allowed_adminpage()) {
	exit;
}

#################################
# start html
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
</head>

<body class="popup_body" onLoad="this.focus()" >

<?
#############################
# KUSTUTA 
#############################

if ($site->fdat[kustuta]) {
	
	clear_cache("ALL");
	
	if(clear_template_cache($templ_cache_path))
	{
		########################
		# close window
	?>
		<SCRIPT language="javascript">
		<!--
	//		window.close();
		// -->
		</SCRIPT>
	<?
	}

}
#############################
# FORM
#############################
?>
<form action="<?$site->self?>" method="post" name="vorm">
 
<?###### 1. Master table ?>
<TABLE border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<TR>
<TD valign="top" width="100%" class="scms_dialog_area"  height="100%">


	<?###### 2. White dialog table ?>
	<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">

	<tr valign=top> 
          <td colspan="2"> 
            <div style="position:relative"> 
              <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "Clear cache", tyyp=>"sapi"))?></div>
            </div>
          </td>
        </tr>
	<tr>
	<td>
	<!-- Scrollable area -->
	
		<div id=listing class="scms_middle_div">

		<?###### 3. Content table ?>		
		<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_table">
		

<?
#######################
# list of directories and files
?>
<tr> 
            <td align="right" valign=top nowrap><?=$site->sys_sona(array(sona => "file_dir", tyyp=>"admin"))?>:</td>
            <td width="100%">
<?

if ($DIR = @opendir($templ_cache_path)) {
	############################
	# tsükkel üle failide
	while (false !== ($file = readdir($DIR))) { 
		if ($file != "." && $file != "..") { 
			print $file."<br>";

		} # ./..
	}
	# / tsükkel üle failide
	############################
	closedir($DIR); 
}
# kui kataloogi ei saa avada, kirjutada logisse veateade
else {
	print "<br><font color=red>Error! Can't open directory '".$templ_cache_path."'</font>";
}

?>
		<input type="hidden" name="kustuta" value="1">
		</table>
		<?###### / 3. Content table ?>		
        
		</div>
		<!-- //Scrollable area -->
	</td>
	</tr>
	</table>
	<?###### / 2. White dialog table ?>


</TD>
</TR>
<?############ buttons #########?>
<TR> 
<TD align="right" valign="top" class="scms_dialog_area_bottom"> 
		<input type=button value="<?=$site->sys_sona(array(sona => "Kustuta" , tyyp=>"Editor"))?>"  onclick="this.form.submit();">
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 

</TD>
</TR>
</TABLE>
<?###### / 1. Master table ?>

</form>
<?
$site->debug->print_msg();
?>
</body>
</html>
