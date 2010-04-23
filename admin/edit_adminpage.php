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
 * Popup page for modifying adminpage properties
 * 
 * Page has 1 tab:
 * PERMISSIONS TAB: shows all permissions for current adminpage
 * 
 * 
 * @param string $tab selected tab name (permissions)
 * @param int $id current adminpage ID
 * @param string $op action name
 * @param string $op2 step 2 action name
 */

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");

$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));

#temporary:
error_reporting(7);

# default tab is first one:
$site->fdat['tab'] = $site->fdat['tab']? $site->fdat['tab'] : 'permissions';
$op = $site->fdat['op'];
$op2 = $site->fdat['op2'];

###########################
# ACCESS allowed/denied
# decide if accessing this page is allowed or not
$access = 0;

# PERMISSIONS tab : if current user has READ privilege for this adminpage => allow
if( $site->fdat['tab'] == 'permissions'){

	# kas useril on selle admin-lehe kohta Read õigus?
	if($site->user->allowed_adminpage(array("adminpage_id" => $site->fdat['id'])) ) {
		$access = 1;
	}
}
	####################
	# access denied
	if (!$access) {
		new Log(array(
			'action' => 'create',
			'type' => 'WARNING',
			'objekt_id' => $objekt->objekt_id,
			'message' => $objekt ? sprintf("Access denied: attempt to edit %s '%s' (ID = %s)" , ucfirst(translate_en($objekt->all['klass'])), $objekt->pealkiri(), $objekt->objekt_id) : sprintf("Access denied: attempt to create %s under restricted category ID = %s" , ucfirst(translate_en($objekt->all['klass'])), $site->fdat['parent_id']),
		));
		?>
		<center><b><?=$site->sys_sona(array(sona => "access denied", tyyp=>"editor"))?></b>
		<?
		if($site->user) { $site->user->debug->print_msg(); }
		if($site->guest) { 	$site->guest->debug->print_msg(); }
		$site->debug->print_msg();
		########### EXIT
		exit;
	}
# / ACCESS allowed/denied
###########################


###########################
# GO ON with real work


#################
# STEP2:  SAVE DATA : close popup or don't close and refresh
if($op2) {
	
	##############
	# SAVE PERMISSIONS TAB
	if($site->fdat['tab'] == 'permissions') {
		####### save permissions to database
		include_once($class_path."permissions.inc.php");
		save_permissions(array(
			"type" => 'ADMIN'	
		));

		############# if update then REDIRECT PAGE: to get correct url again
		if($site->fdat['op2']!='saveclose') {
			header("Location: ".$site->self."?tab=".$site->fdat['tab']."&id=".$site->fdat['id']);
		}

	}
	# / SAVE PERMISSIONS TAB
	##############

	##############
	# refresh opener and close popup
	if($op2=='saveclose' || $op2=='deleteconfirmed') {
		?>
		<SCRIPT language="javascript">
		<!--
//dont refresh entire admin-area			window.opener.location=window.opener.location;
			window.close();
		// -->
		</SCRIPT>
		<?
		exit;
	}

}
# / STEP2:  SAVE DATA 
#################




##################
# 0. HTML
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>

<?
######################
# 1. tab PERMISSIONS

# Note: permissions is new feature/page, starting from ver 4

if($site->fdat['tab'] == 'permissions') { 
	################# BODY START
?>
<body class="popup_body">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<?
	########### tabs
	print_tabs();

	include_once($class_path."permissions.inc.php");
	edit_permissions(array(
		"type" => 'ADMIN',
		"permissions" => 'R,U'
	));

}
# / 1. tab PERMISSIONS
######################
?>
			  
</table>

<?

$site->debug->print_hash($site->fdat,1,"FORM DATA");
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 

?>
</body>
</html>




<?
######################
# FUNCTION PRINT_TABS()

function print_tabs() {
	global $site;
?>
  <tr> 
    <td valign="top" width="100%"> 
     <table border="0" cellspacing="0" cellpadding="0" width="100%" style="height:21px">
      <tr>
       <td class="scms_tabs_empty">&nbsp;&nbsp;&nbsp;</td>
		<?
		# set all tabs: array[tab name] = tab translated name 
		$tab_arr = array();
		$tab_arr['permissions'] = $site->sys_sona(array(sona => "permissions", tyyp=>"editor"));

		foreach ($tab_arr as $tab=>$tab_title) {

		?>
        <td class="scms_<?=$site->fdat['tab']==$tab?'':'in'?>active_tab" nowrap>
		<?########## tab title #######?>
		<?	
		$tab_title = "<a href=".$site->self."?tab=".$tab."&id=".$site->fdat['id']."&op=".$site->fdat['op'].">".$tab_title."</a>";
		?>
		<?=$tab_title?></td>
		<? } # loop over tabs ?>

          <td width="100%" class="scms_tabs_empty">&nbsp;</td>
      </tr>
    </table>
    </td>
  </tr>
<?
}
# / FUNCTION PRINT_TABS()
######################
