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


##############################
# Deletes a comment from database - for registered users deleting their own comments
# : opens popup window, asks for confirmation and deletes a comment
# : will refresh the calling page
# : is independent script, not for including, new Site is generated
##############################

# CURRENT FILE WAS "kommentaaride_kustutamine.php" in ver 3

global $site;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";
include($class_path."port.inc.php");

$site = new Site(array(
	on_debug=>0,
	on_admin_keel => 1
));

$objekt = new Objekt(array(
	objekt_id => $site->fdat['id']
));

if (!$site->CONF['users_can_delete_comment']) {
?>
<SCRIPT LANGUAGE="JavaScript"><!--
		window.opener.location = window.opener.location;
		window.close();
//--></SCRIPT>
<?
}

if ($site->fdat['op2'] == 'deleteconfirmed') {

	/*-------------------------------------
	//Valime kasutaja_id kelle oma see kommentaar on
	--------------------------------------*/
	verify_form_token();

	$sql = $site->db->prepare("SELECT kasutaja_id FROM obj_kommentaar WHERE objekt_id = ?",$site->fdat[id]);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());	
	$kommenteerija_id = $sth->fetchsingle();

	$curr_obj = new Objekt(array(
		objekt_id	=> $site->fdat['id'],
	));

	$parent_obj = new Objekt(array(
		objekt_id	=> $curr_obj->parent_id,
	));
	
	# $alamlist_count - kui palju üldse kommentaare on, parent jaoks

	$alamlist_count = new Alamlist(array(
		parent => $curr_obj->parent_id,
		klass	=> "kommentaar",
		asukoht	=> 0,
		on_counter => 1		
	));
	
	
	# Siin valime viimane kommentaar, parent jaoks
	
	$alamlist = new Alamlist(array(
		parent => $curr_obj->parent_id,
		klass	=> "kommentaar",
		start =>$alamlist_count->rows-1,
		limit =>1,
		asukoht	=> 0,
		order => " objekt.aeg, objekt.objekt_id ASC ",
	));

	# $alamlist_count - kui palju child kommentaare on, kui neid on, siis kustutada ei tohi

	$alamlist_count2 = new Alamlist(array(
		parent => $site->fdat['id'],
		klass	=> "kommentaar",
		asukoht	=> 0,
		on_counter => 1		
	));

	$obj_from_alamlist = $alamlist->next();

	
	/*--------------------------------------------------------
	//Juhul kui sisselooginud kasutaja_id = kommenteerija_id 
	//ja see kommentaar on viimane järjekorras, artiklite kommentaarides või
	//kui sellele kirjale ei ole veel ühtegi vastust foorumis
	// kustutame ära :(
	-----------------------------------------------------------*/
	#echo "1.".$obj_from_alamlist->objekt_id."<BR>2.".$obj_from_alamlist2->objekt_id."<BR>index1: ".$alamlist->index."<BR>index 2:".$alamlist_count2->rows;

	$allow_delete=true;
	
	if($parent_obj->all['klass'] != "teema"){
		$allow_delete = ($obj_from_alamlist->objekt_id==$site->fdat['id'] && $alamlist->index == 0?true:false);
	} else {
		$allow_delete = ($alamlist_count2->rows == 0?true:false);
	}
	
	if ($kommenteerija_id == $site->user->user_id && $site->user->user_id && $allow_delete) {

		$sql = $site->db->prepare("DELETE FROM objekt WHERE objekt_id=?", $site->fdat['id']);
		$sth = new SQL($sql);
		$sql = $site->db->prepare("DELETE FROM objekt_objekt WHERE objekt_id=?", $site->fdat['id']);
		$sth = new SQL($sql);
		$sql = $site->db->prepare("DELETE FROM obj_kommentaar WHERE objekt_id=?", $site->fdat['id']);
		$sth = new SQL($sql);


		#####################
		# UPDATE LAST_COMMENTED_TIME, COMMENT_COUNT
		
		# get comment count for object:
		$alamlist_count = new Alamlist(array(
				parent => $curr_obj->parent_id,
				klass	=> "kommentaar",
				asukoht	=> 0,
				on_counter => 1	
			));
		$comment_count = $alamlist_count->rows;

		$sql = $site->db->prepare("UPDATE objekt SET last_commented_time=".$site->db->unix2db_datetime(time()).", comment_count=? WHERE objekt_id=?",
			$comment_count,
			$curr_obj->parent_id
		);		
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());



?>
<SCRIPT LANGUAGE="JavaScript"><!--
		window.opener.location = window.opener.location;
		window.close();
//--></SCRIPT>
<?
	}
} 
######################
# DELETE CONFIRMATION WINDOW
else {
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
	<body class="popup_body" onLoad="this.focus()">

	<form name="editform" action="<?=$site->self ?>" method=get>
		<?php create_form_token('delete-comments'); ?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?
	$objekt->load_sisu();
	echo $site->sys_sona(array(sona => "kustuta", tyyp=>"editor"))." \"<b>".substr($objekt->all['text'],0,20).(strlen($objekt->all['text'])>20?'...':'')."</b>\"? ";
	echo $site->sys_sona(array(sona => "are you sure?", tyyp=>"admin"));
	
?>
	</td>
  </tr>
  <tr align="right"> 
    <td valign="top" colspan=2 > 
            <input type="button" value="<?=$site->sys_sona(array(sona => "kustuta", tyyp=>"editor")) ?>" onclick="javascript:document.getElementById('op2').value='deleteconfirmed';document.forms['editform'].submit();">
			<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>
<input type=hidden id=id name=id value="<?=$objekt->objekt_id?>">
<input type=hidden id=op2 name=op2 value="">
</form>
	
	</body>
	</html>
<?php 
}
# / DELETE CONFIRMATION WINDOW
######################
