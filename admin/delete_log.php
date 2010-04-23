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
 * Popup page for deleting log data
 * 
 * Deletes log records of selected month (table 'logi' or table 'error_log')
 * 
 * @param string $tbl table name in teh database
 * 
 */


global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");

#Get debug cookie muutuja
$debug = $_COOKIE["debug"] ? 1:0;

$site = new Site(array(
	on_debug=>$debug,
	on_admin_keel => 1
));

#################################
# check privileges
# only superuser can delete
if (!$site->user->allowed_adminpage()) {
	exit;
}

##### default table is "logi" (Site log)
if($site->fdat['tbl']=='error_log'){
	$tbl = 'error_log';
	$time_field = 'time_of_error';
	$title = $site->sys_sona(array(sona => "Error Log", tyyp=>"admin"));
}
else {
	$tbl = 'sitelog';
	$time_field = 'date';
	$title = $site->sys_sona(array(sona => "Log", tyyp=>"admin"));
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
	foreach ($site->fdat as $key=>$value) {
		if ( substr ($key, 0, 8) == "delmonth" ) {			
			$sql = $site->db->prepare("DELETE FROM ".$tbl." WHERE CONCAT(YEAR(".$time_field."),'.',MONTH(".$time_field."))=?", $value); 
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			new Log(array(
				'component' => 'Site log',
				'action' => 'delete',
				'message' => "Log '".$value."' deleted on page 'Tools > ".($tbl=='error_log'?'Error':'Site')." Log'",
			));
		}
	}	
	########################
	# error
	if ($error) {
		print "<p align=center>$error<br><br><a href=\"javascript:history.back()\">".$site->sys_sona(array(sona => "Tagasi", tyyp=>"editor"))."</a></p>";
		exit;
	} 
	elseif (!$site->on_debug) {
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
              <div class="scms_borderbox_label"><?=$title?></div>
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
# list of years existing in log table
$sql = $site->db->prepare("
	SELECT COUNT(*) AS mcount, CONCAT(YEAR(".$time_field."),'.',MONTH(".$time_field.")) AS mname
	FROM ".$tbl." 
	GROUP BY CONCAT(YEAR(".$time_field."),'.',LPAD(MONTH(".$time_field."),2,'0'))
	");
$sth = new SQL($sql);

	############################
	# tsükkel üle kuude
	while ($log = $sth->fetch()) {
		list($year, $month) = split("\.",$log[mname]);
?>
		<tr> 
            <td width="10%"><input type="checkbox" name="delmonth_<?=$log[mname]?>" value="<?=$log[mname]?>" ></td>
            <td nowrap  width="60%"><?=$year?>&nbsp;<?=$site->sys_sona(array(sona => "month".$month, tyyp=>"kalender"))?></td>
            <td align="right" width="40%"><?=$log[mcount]?></td>
          </tr>

<?
	}

	# / tsükkel üle kuude
	############################
?>
		<input type="hidden" name="kustuta" value="1">
		<input type="hidden" name="tbl" value="<?=$site->fdat['tbl']?>">
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
<? $site->debug->print_msg(); ?>
</body>
</html>
