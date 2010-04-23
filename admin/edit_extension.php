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
 * Popup page for editing extension data
 * 
 * tbl 'extensions'
 * 
 * @param string name 
 * @param string op - action name
 * @param string op2 - step 2 action name
 */

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");
include($class_path."extension.class.php");


$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));

if (!$site->user->allowed_adminpage(array('adminpage_id' => 86,)) ) { # adminpage_id=86 => "System > Extensions"
	############ debug
	if($site->user) { $site->user->debug->print_msg(); } # user debug
	if($site->guest) { 	$site->guest->debug->print_msg(); } 	# guest debug
	$site->debug->print_msg(); 
	exit;
}

$op = $site->fdat['op'];
$op2 = $site->fdat['op2'];


######## create EXTENSION

$extension = new extension(array(
	name => $site->fdat['name']
));
#printr($extension->all);

######################
# leida valitud keele p�hjal �ige lehe encoding,
# admin-osa keel j��b samaks

$keel_id = isset($site->fdat['flt_keel']) ? $site->fdat['flt_keel'] : $site->fdat['keel_id'];
if (!strlen($keel_id)) { $keel_id = $site->keel; }


###############################
# DEPRECATED: extension: Save extension name & close
/*
if($site->fdat['op2'] == 'save_extension' || $site->fdat['op2'] == 'saveclose_extension') {

	################
	# new
	if($site->fdat['op'] == 'new'){
		global $_FILES;

		$file = $_FILES["package_file"];
printr($file);

		$ext_dir = $site->absolute_path.'extensions/';
		$uploadfile = $ext_dir . basename($file['name']);

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
   echo "File is valid, and was successfully uploaded.\n";
} else {
	$errors = "Can't upload ";
}

echo $errors;
exit;

			## New
			$sql = $site->db->prepare("INSERT INTO extensions (extension_id,name,path) VALUES (NULL,?,?)",$site->fdat['name'],'extensions/'.$site->fdat['name'].'/'
			);
#			$sth = new SQL($sql);
#			$site->debug->msg($sth->debug->get_msgs());

			####### write log
			$site->kirjuta_log(array(
				text => "New extension '".$site->fdat['name']."' inserted",
			));

	}
	# / new
	################

	################
	# edit	
	if($site->fdat['op'] == 'edit' && $site->fdat['name']) {

		#### Update
			$sql = $site->db->prepare("UPDATE extensions SET title=?, description=?, author=?, version=?, version_date=?, icon_path=?, min_saurus_version=?, min_saurus_modules=?, is_active=? WHERE name=?",
				$site->fdat['title'],
				$site->fdat['description'],
				$site->fdat['author'],
				$site->fdat['version'],
				$site->db->ee_MySQL($site->fdat['version_date']),
				$site->fdat['icon_path'],
				$site->fdat['min_saurus_version'],
				$site->fdat['min_saurus_modules'],
				$site->fdat['is_active'],
				$site->fdat['name']
			);
			#print $sql;			
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			####### write log
			$site->kirjuta_log(array(
				text => "Extension '".$site->fdat['name']."' updated",
			));

			### reload extension:
			$extension = new extension(array(
				name => $site->fdat['name']
			));


	} # if name set
} # op2=save_extension
DEPRECATED */



###############################
# extension: UNINSTALL extension 
if($site->fdat['op2'] == 'uninstallconfirmed' && $site->fdat['name']) {

	$extension->uninstall();

	if(!$smth_not_deleted){
	?>
	<HTML>
	<SCRIPT language="javascript"><!--
		window.opener.location=window.opener.location;
		window.close();
	// --></SCRIPT>
	</HTML>
	<?
	}
	exit;
}


######################
# 1. DELETE CONFIRMATION WINDOW (ENTIRE extension)
if($op == 'uninstall' && $site->fdat['name']) {

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->admin->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>
<body class="popup_body">
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<input type=hidden name=name value="<?=$site->fdat['name']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">


<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:200px">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?	
	############ # get extension templates
	$extension->templates_arr = $extension->get_templates();
	foreach($extension->templates_arr as $templ){
		$templ_arr[] = $templ['templ_fail'];
	}
	
	# show confirmation
	echo $site->sys_sona(array(sona => "kustuta", tyyp=>"editor"))." \"<b>".$site->fdat['name']."</b>\"? ";
	echo $site->sys_sona(array(sona => "are you sure?", tyyp=>"admin"));
	$allow_delete = 1;

	######## show extension info:

?>
	<br>
	<br><b><?=$extension->all['path']?></b>
	<?if(count($templ_arr)){?>
		<br><?=join(", ",$templ_arr)?>
	<?}?>

	</td>
  </tr>
  <tr align="right"> 
    <td valign="top" colspan=2 > 
		<?if($allow_delete){?>
            <input type="button" value="<?=$site->sys_sona(array(sona => "kustuta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='uninstallconfirmed';frmEdit.submit();">
			<?}?>
			<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

</form>
</body>
</html>
<?
	############ debug
	# user debug:
	if($site->user) { $site->user->debug->print_msg(); }
	# guest debug: 
	if($site->guest) { 	$site->guest->debug->print_msg(); }
	$site->debug->print_msg(); 
	exit;
}	
# / 1. DELETE CONFIRMATION WINDOW (ENTIRE extension)
######################




###############################
# 2. EDIT extension (VIEW ONLY)
# get extension info 
if($site->fdat['op']=='edit') {

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding ? $encoding : $site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>
<body class="popup_body" onLoad="this.focus();">

<FORM action="<?=$site->self ?>" method="post" name="vorm">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<tr> 
    <td valign="top" width="100%" class="scms_dialog_area_top"  height="100%">
	  <table width="100%"   border="0" cellspacing="0" cellpadding="2">
	  <?############ name (user can't change, it's the same as directory name) #########?> 
	  <tr> 
		<td ><?=$site->sys_sona(array(sona => "nimi", tyyp=>"editor"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=$extension->name?></td>
	  </tr>
	<?############### path #######?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "path", tyyp=>"files"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=$extension->all['path'] ?></td>
	</tr>
	<?############### title #######?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "pealkiri", tyyp=>"editor"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=$extension->all['title'] ?></td>
	</tr>
	<?############### description #######?>
	<tr>
		<td valign="top"><?=$site->sys_sona(array(sona => "kirjeldus", tyyp=>"editor"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=$extension->all['description'] ?></td>
	</tr>
	<?############### author #######?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "autor", tyyp=>"editor"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=htmlspecialchars($extension->all['author']) ?></td>
	</tr>
	<?############### version #######?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "Version", tyyp=>"extensions"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=$extension->all['version'] ?></td>
	</tr>
	<?############### version_date #######?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "date", tyyp=>"kalender"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=$extension->all['fversion_date'] ?></td>
	</tr>
	<?############### is_official #######?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "official", tyyp=>"extensions"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=($extension->all['is_official'] ? $site->sys_sona(array(sona => "yes", tyyp=>"editor")) : $site->sys_sona(array(sona => "no", tyyp=>"editor")))?></td>
	</tr>
	<?############### min_saurus_version #######?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "min_saurus_version", tyyp=>"extensions"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=$extension->all['min_saurus_version'] ?></td>
	</tr>
	<?############### min_saurus_modules #######?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "min_saurus_modules", tyyp=>"extensions"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=$extension->all['min_saurus_modules'] ?></td>
	</tr>

	<?############### icon_path #######?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "icon_path", tyyp=>"extensions"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?=$extension->all['icon_path'] ?></td>
	</tr>
	<?############### is_active #######?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "active", tyyp=>"admin"))?>: </td>
		<td width="50%" STYLE="padding-bottom:5px"><?= ($extension->all['is_active']?$site->sys_sona(array(sona => "yes", tyyp=>"editor")):$site->sys_sona(array(sona => "no", tyyp=>"editor")))?></td>
	</tr>


	  </table>
	</td>
</tr>
	<?############ buttons #########?>
	<tr> 
	  <td align="right" valign="top" class="scms_dialog_area_bottom"> 
		<!--deprecated
         <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript: document.forms['vorm'].op2.value='save_extension';this.form.submit();">
         <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:document.forms['vorm'].op2.value='saveclose_extension';this.form.submit();">
		 -->
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

<?########### hidden ########?>
<INPUT TYPE="hidden" name="name" value="<?= $site->fdat['name'] ?>">
<INPUT TYPE="hidden" name="op" value="<?=$site->fdat['op']?>">
<INPUT TYPE="hidden" name="op2" value="save_extension">
</form>
</body>
</html>
<?
############ debug
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 

exit;
}
# / 2. EDIT extension NAME
###############################


###############################
# 3. NEW extension 

if($site->fdat['op']=='new') {

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding ? $encoding : $site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>
<body class="popup_body" onLoad="this.focus();">

<FORM action="<?=$site->self ?>" method="post" name="vorm"  enctype="multipart/form-data">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<tr> 
    <td valign="top" width="100%" class="scms_dialog_area_top"  height="100%">
	  <table width="100%"   border="0" cellspacing="0" cellpadding="2">
	<?############### browse #######?>
	<tr>
		<td nowrap><?=$site->sys_sona(array(sona => "extension", tyyp=>"extensions"))?>: </td>
		<td width="100%" STYLE="padding-bottom:5px"><input type=file name="package_file"></td>
	</tr>


	  </table>
	</td>
</tr>
	<?############ buttons #########?>
	<tr> 
	  <td align="right" valign="top" class="scms_dialog_area_bottom"> 
<!--         <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript: document.forms['vorm'].op2.value='save_extension';this.form.submit();">
-->
		 <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:document.forms['vorm'].op2.value='saveclose_extension';this.form.submit();">
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

<?########### hidden ########?>
<INPUT TYPE="hidden" name="name" value="<?= $site->fdat['name'] ?>">
<INPUT TYPE="hidden" name="op" value="<?=$site->fdat['op']?>">
<INPUT TYPE="hidden" name="op2" value="save_extension">
</form>
</body>
</html>
<?
############ debug
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 

exit;
}
# / 3. NEW extension NAME
###############################



############ debug
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 
?>
</body>
</html>
