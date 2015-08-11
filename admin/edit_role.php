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
 * Popup page for editing role data
 * 
 * tbl 'roles'
 * 
 * @param string role_id 
 * @param string op - action name
 * @param string op2 - step 2 action name
 * 
 */

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");


$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));

if (!$site->user->allowed_adminpage(array('adminpage_id' => 83,))) { # adminpage_id=83 => "Organization > Permissions"
	############ debug
	if($site->user) { $site->user->debug->print_msg(); } # user debug
	if($site->guest) { 	$site->guest->debug->print_msg(); } 	# guest debug
	$site->debug->print_msg(); 
	exit;
}

$op = $site->fdat['op'];
$op2 = $site->fdat['op2'];


######################
# leida valitud keele p�hjal �ige lehe encoding,
# admin-osa keel j��b samaks

$keel_id = isset($site->fdat['flt_keel']) ? $site->fdat['flt_keel'] : $site->fdat['keel_id'];
if (!strlen($keel_id)) { $keel_id = $site->keel; }


###############################
# role: Save role name & close

if($site->fdat['op2'] == 'save_role' || $site->fdat['op2'] == 'saveclose_role') {
	verify_form_token();
	if($site->fdat['role_name']) {
		if($op=='new') {
			## New
			$sql = $site->db->prepare("INSERT INTO roles (name) VALUES (?)",$site->fdat['role_name']);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			$site->fdat['role_id']= $sth->insert_id;

			####### write log
			new Log(array(
				'action' => 'create',
				'component' => 'Roles',
				'message' => "New Role '".$site->fdat['role_name']."' inserted",
			));
			##### if new was saved, then make it 'edit'
			$op = $site->fdat['op'] = "edit";
			$op2 = $site->fdat['op2'] = "";

		} 
		elseif($op=='edit') {
			## Update
			$sql = $site->db->prepare("UPDATE roles SET name=? WHERE role_id=?",$site->fdat['role_name'],$site->fdat['role_id']);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			####### write log
			new Log(array(
				'action' => 'update',
				'component' => 'Roles',
				'message' => "Role '".$site->fdat['role_name']."' updated",
			));
		}
	}
	################
	# kui vajutati salvesta nuppu, pane aken kinni
	if ( 1 || $site->fdat['op2']=='saveclose_role') {
	?>
	<HTML>
	<SCRIPT language="javascript"><!--
		window.opener.location=window.opener.location;	
		window.close();
	// --></SCRIPT>
	</HTML>
	<?php 
	exit;
	}
} # op2=save_role

###############################
# role: DELETE ENTIRE role 

if($op2 == 'deleteconfirmed' && is_numeric($site->fdat['role_id']) ) {

	verify_form_token();
	# delete permissions
	$sql = $site->db->prepare("DELETE FROM permissions WHERE role_id=?",$site->fdat['role_id']);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	# delete user roles
	$sql = $site->db->prepare("DELETE FROM user_roles WHERE role_id=?",$site->fdat['role_id']);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	# delete role
	$sql = $site->db->prepare("DELETE FROM roles WHERE role_id=?",$site->fdat['role_id']);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	####### write log
	new Log(array(
		'action' => 'delete',
		'component' => 'Roles',
		'message' => "Role '".$site->fdat['role_name']."' deleted",
	));

	if(!$smth_not_deleted){
	?>
	<HTML>
	<SCRIPT language="javascript"><!--
		window.opener.location=window.opener.location;
		window.close();
	// --></SCRIPT>
	</HTML>
	<?php 
	}
	exit;
}

######################
# 1. DELETE CONFIRMATION WINDOW (ENTIRE role)
if($op == 'delete' && $site->fdat['role_id']) {
	$sql = $site->db->prepare("SELECT * FROM roles WHERE role_id=? ",	$site->fdat['role_id']	);
	$sth = new SQL($sql);
	$role = $sth->fetch();
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
<body class="popup_body">
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<?php create_form_token('delete-role'); ?>
	<input type=hidden name=role_id value="<?=$site->fdat['role_id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
	<input type=hidden name=role_name value="<?=$role_name?>">


<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?php 
	# check warnings
	# 1. if exist any user with that role, then give a warning
	$data_count = 0;

	############ permissions
	$sql = $site->db->prepare("SELECT COUNT(*) FROM user_roles WHERE role_id=?",$site->fdat['role_id']);
	$sth = new SQL($sql);
	$data_count = $sth->fetchsingle();
	
	if($data_count > 0) {
		# show error message
#		echo "<font color=red>".$site->sys_sona(array(sona => "Permission denied", tyyp=>"editor"))."</font><br><br>";
		echo $site->sys_sona(array(sona => "Children count", tyyp=>"admin")).": <font color=red><b>".$data_count."</b></font><br><br>";
	}
	# show confirmation
	echo $site->sys_sona(array(sona => "kustuta", tyyp=>"editor"))." \"<b>".$role['name']."</b>\"? ";
	echo $site->sys_sona(array(sona => "are you sure?", tyyp=>"admin"));
	$allow_delete = 1;
?>
	</td>
  </tr>
  <tr align="right"> 
    <td valign="top" colspan=2 > 
		<?php if($allow_delete){?>
            <input type="button" value="<?=$site->sys_sona(array(sona => "kustuta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='deleteconfirmed';frmEdit.submit();">
			<?php }?>
			<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

</form>
</body>
</html>
<?php 
	############ debug
	# user debug:
	if($site->user) { $site->user->debug->print_msg(); }
	# guest debug: 
	if($site->guest) { 	$site->guest->debug->print_msg(); }
	$site->debug->print_msg(); 
	exit;
}	
# / 1. DELETE CONFIRMATION WINDOW (ENTIRE role)
######################


###############################
# 2. NEW/EDIT role NAME
if($site->fdat['op'] == "new" || 
	( ($site->fdat['op'] == "edit") && $site->fdat['role_id'] )
) {

# get role info 
if($site->fdat['role_id']) {
	$sql = $site->db->prepare("SELECT * FROM roles WHERE role_id=? ",	$site->fdat['role_id']	);
	$sth = new SQL($sql);
	$role = $sth->fetch();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding ? $encoding : $site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
</head>
<body class="popup_body" onLoad="this.focus();document.forms['vorm'].role_name.focus();">

<FORM action="<?=$site->self ?>" method="post" name="vorm">
	<?php create_form_token('edit-role'); ?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<tr> 
    <td valign="top" width="100%" class="scms_dialog_area_top"  height="100%">
	  <table width="100%"   border="0" cellspacing="0" cellpadding="2">
	  <?php ############ name #########?> 
	  <tr> 
		<td><?=$site->sys_sona(array(sona => "nimi", tyyp=>"editor"))?>: </td>
		<td width="100%"><input type=text name=role_name value="<?= ($site->fdat['op']=="new" ? '' : $role['name']) ?>" class="scms_flex_input" onkeyup="javascript: if(event.keyCode==13){vorm.submit();}"></td>
	  </tr>

	  </table>
	</td>
</tr>
	<?php ############ buttons #########?>
	<tr> 
	  <td align="right" valign="top" class="scms_dialog_area_bottom"> 
         <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:this.form.submit();">
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

<?php ########### hidden ########?>
<INPUT TYPE="hidden" name="role_id" value="<?= $site->fdat['role_id'] ?>">
<INPUT TYPE="hidden" name="op" value="<?=$site->fdat['op']?>">
<INPUT TYPE="hidden" name="op2" value="saveclose_role">
</form>
</body>
</html>
<?php 
############ debug
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 

exit;
}
# / 2. NEW/EDIT role NAME
###############################
?>


<?php 
############ debug
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 
?>
</body>
</html>