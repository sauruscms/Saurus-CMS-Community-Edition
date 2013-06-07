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
 * Popup page for modifying object properties
 * 
 * Page has 4 tabs:
 * PERMISSIONS TAB: shows all permissions for current object
 * 
 * @param string $tab selected tab name (group/members/permissions)
 * @param int $id current object ID
 * @param string $op action name
 * @param string $op2 step 2 action name
 * 
 */

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");
include_once($class_path.'picture.inc.php');

###############
# BRAUSER: leia �ige brauseri versioon

# NB! POOLELI: ver 4-s lisandub MOZILLA ETC

if (preg_match ('/.*MSIE\s+(\d+\.\d+)/i',$_SERVER["HTTP_USER_AGENT"], $regs) ) {
	$on_textarea = "";
	if($regs[1]<5.5){
	   $on_textarea = "_textarea";
	}
} else {
	if(!$args['site']->on_debug && $_SERVER["HTTP_USER_AGENT"]) {
		if (preg_match ('/.*(Gecko).*/i',$_SERVER["HTTP_USER_AGENT"], $regs) ) {
			$is_moz = 1;
		}
	} else {
		$on_textarea = "_textarea";
	}
}

###############
# KEEL: teha sait admin-keelega (et s�ss�nad etc �iged tuleks)

$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));

//printr($site->fdat);

$op = $site->fdat['op'];
$op2 = $site->fdat['op2'];

# include global actions file
if(file_exists('../extensions/actions_custom.inc.php'))
{
	$actions_file = '../extensions/actions_custom.inc.php';
}
else 
{
	$actions_file = '../extensions/actions.inc.php';
}
$site->debug->msg('actions_file = '.$actions_file);	

if (file_exists($actions_file)){
	include_once($actions_file);
	$site->debug->msg("Included successfully: ".$actions_file);
} else {
	$site->debug->msg("File not found: ".realpath($actions_file));
}


# default tab is first one:
$site->fdat['tab'] = $site->fdat['tab']? $site->fdat['tab'] : 'object';
# default op for object tab is 'edit'
if($site->fdat['tab'] == 'object' && !$site->fdat['op']){
	$site->fdat['op'] = 'edit';
	$op = $site->fdat['op'];
}

$site->debug->msg("EDIT start: OP = ".$site->fdat['op']. " OP2 = ".$site->fdat['op2']);

#################
# leia keel

# NB! siin failis v�ivad olla $site->keel (admini keel) ja $keel (saidi keel) erinevad.

### 1. get OBJECT LANG: get it from object or from parent (if new object) (Bug #1966)
$sql = $site->db->prepare("SELECT keel FROM objekt WHERE objekt_id=?",  ($site->fdat['id'] ? $site->fdat['id'] : $site->fdat['parent_id']));
$sth = new SQL($sql);
$site->debug->msg($sth->debug->get_msgs());	
$keel = $sth->fetchsingle();

$sql = $site->db->prepare("SELECT keel_id,encoding,locale FROM keel WHERE keel_id=?", $keel);
$sth = new SQL($sql);
$tmp = $sth->fetch();
$site->encoding = $encoding = $tmp['encoding'];

### if lang not found => search parameter in URL => search site lang
if (!is_numeric($keel)){
	
	### 2. parameter "keel" given in URL
	if(is_numeric($site->fdat["keel"])) {
		$sql = $site->db->prepare("SELECT keel_id,encoding,locale FROM keel WHERE keel_id=?", $site->fdat["keel"]);
		$sth = new SQL($sql);
		$tmp = $sth->fetch();
		$keel = $tmp['keel_id'];
		$site->encoding = $encoding = $tmp['encoding'];
	}
	elseif (strcmp($site->fdat[keel],'')) {
		$keel = $site->fdat[keel];
	} 
	### 3. use site lang
	else {
		$tmp = $site->get_keel(array("on_admin_keel" => 0));
		$keel = $tmp['keel'];
		$site->encoding = $encoding = $tmp['encoding'];
	}
}
# / leia keel
#################


#################
# var initial
$adm_img_path = $site->CONF['wwwroot'].$site->CONF['adm_img_path'];
$tyyp = array();

############ browser check
if ($site->agent) { 


###################
# GET OBJECT by ID

if ($site->fdat['id']) {
	$site->debug->msg("EDIT: ID = ".$site->fdat['id']);

	#	$all_parents = $site->get_obj_all_parents($site->fdat['id']);
	#	echo "all_parents=".printr($all_parents);

	$objekt = new Objekt(array(
		objekt_id => $site->fdat['id'],
		on_sisu => 1,
		no_cache =>1,
	));
	
	# kui objektil on rohkem, kui 1 parent, siis loodame objekti uuesti uue parentiga:
	if ($objekt->all['parents_count']>1 && $objekt->parent_id!=$site->fdat['parent_id']){
		$site->debug->msg("EDIT: Leidsin mitu parenti (".$objekt->all['parents_count']."). Kasutan parent_id=".$site->fdat['parent_id']);
		unset($objekt); 
		$objekt = new Objekt(array(
			objekt_id => $site->fdat['id'],
			parent_id => $site->fdat['parent_id'],
			no_cache =>1,
			on_sisu => 1,
		));	
	}

	$tyyp['tyyp_id'] = $objekt->all['tyyp_id'];
	$site->debug->msg("EDIT: ".$objekt->debug->get_msgs());

	$site->debug->msg("EDIT: Tyyp_id detected: ".$tyyp['tyyp_id']);

	if (!$objekt->objekt_id) {
		$site->error("EDIT: Vale objekti ID");
	}
}
# / GET OBJECT
###################

###################
# GET PARENT OBJECT
else {
	# default parent for file (folder "public/")
	if($site->fdat['op']=='new' && $site->fdat['tyyp_id'] == 21 && !$site->fdat['parent_id']){ # file object and no parent ID set
		# get folder ID of "public/", Bug #2342
		$sql = $site->db->prepare("SELECT objekt_id FROM obj_folder WHERE relative_path = ? LIMIT 1",
			$site->CONF['file_path']
		);
		$sth = new SQL($sql);
		$tmp = $sth->fetch();
		$site->fdat['parent_id'] = $tmp['objekt_id'];
	}

	$parent_objekt = new Objekt(array(
		objekt_id => $site->fdat['parent_id'],
		no_cache =>1
	));	
}
# / GET PARENT OBJECT
###################

####################################
# GET PERMISSIONS
# get object permissions for current user

# if object exists then get it's own permissions
if ($objekt) {
	$site->debug->msg("EDIT: Muudetava objekti ".$objekt->objekt_id." �igused = ".$objekt->permission['mask']);
} 

###########################
# ACCESS allowed/denied
# decide if accessing this page is allowed or not
$access = 0;
#echo printr($site->fdat);
# OBJECT TAB 
if( $site->fdat['tab'] == 'object') {

	# NEW OBJECT: if parent object has CREATE permission => allow	
	if ($op=='new'){
/*		echo "<!--";
printr($parent_objekt->permission);
printr($site->fdat['parent_id']);
printr($parent_objekt->objekt_id);
		echo "-->";
*/		if($parent_objekt->permission['C']){
			$access = 1;
		}
	}
	# EDIT OBJECT: if current object has READ & UPDATE => allow
	if( $op=='edit') {
		if($objekt->permission['R'] && $objekt->permission['U']) {
			$access = 1;
		}
	}
}

# PERMISSIONS tab : if current object has READ & UPDATE => allow
if( $site->fdat['tab'] == 'permissions'){
	# EDIT OBJECT
	if ($objekt) {
		if($objekt->permission['R'] && $objekt->permission['U']) {
			$access = 1;
		}
	}
	# NEW OBJECT: tab is denied
}

//SEO tab
if( $site->fdat['tab'] == 'seo'){
	# EDIT OBJECT
	if ($objekt) {
		if($objekt->permission['R'] && $objekt->permission['U']) {
			$access = 1;
		}
	}
	# NEW OBJECT: tab is denied
}

// editing for public and shared folders is denied
if( $site->fdat['tab'] == 'object' && ($objekt->all['sys_alias'] == 'public' || $objekt->all['sys_alias'] == 'shared'))
{
	$access = 0;
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

		####### print error html
		print_error_html(array(
			"message" => $site->sys_sona(array(sona => "access denied", tyyp=>"editor"))
		));

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
if($op2 && !$site->fdat[refresh]) {

	##############
	# SAVE SEO TAB
	if($site->fdat['tab'] == 'seo') {
		include_once('edit_object_metadata.php');
		if($site->fdat['op'] == 'edit') 
		{
			if($site->fdat['op2'] == 'saveclose') salvesta_objekt_metadata();
		}

	}
	# / SAVE SEO TAB
	##############

	##############
	# SAVE PERMISSIONS TAB
	if($site->fdat['tab'] == 'permissions') {
		####### save permissions to database
		include_once($class_path."permissions.inc.php");
		save_permissions(array(
			"type" => 'OBJ'
		));

		############# if update then REDIRECT PAGE: to get correct GET URL again
		if($site->fdat['op2']!='saveclose') {
			header("Location: ".$site->self."?tab=".$site->fdat['tab']."&id=".$site->fdat['id']."&keel=".$site->fdat['keel'].'&callback='.$site->fdat['callback']);
		}
	}
	# / SAVE PERMISSIONS TAB
	##############

	##############
	# SAVE OBJECT TAB
	if($site->fdat['tab'] == 'object') {
		$is_new = $site->fdat['op']=='new' ? true : false;

		if (function_exists('onBeforeObjectSave')){
			$site->globals['onBeforeObjectSave'] = onBeforeObjectSave($objekt);
		}

		include_once("edit_object.php");
		$save_result = save_object();
		$site->fdat['op'] = 'edit';
		$op = $site->fdat['op'];
		
		if(isset($objekt->all['in_wysiwyg_filename']))
		{
			$save_in_wysiwyg_filename = $objekt->all['in_wysiwyg_filename']; /* very ugly workaround for bug #2269 */
		}

		########reload object
		$objekt = new Objekt(array(
			objekt_id => $objekt->objekt_id,
			on_sisu => 1,
			no_cache =>1,
		));
		 
		if (function_exists('onObjectSave') && $save_result){
			$site->globals['onObjectSave'] = onObjectSave($objekt);
		}

		$site->fdat['id'] = $objekt->objekt_id;
		$tyyp['tyyp_id'] = $objekt->all['tyyp_id'];

	}
	# / SAVE OBJECT TAB
	##############

	$form_error = $site->fdat['form_error'];

	######### if debug => print out debug info and dont close popup
	if($site->debug->on_debug) {
#printr($site->debug);
		$site->debug->print_hash($site->fdat,1,"FORM DATA");
		$site->debug->print_hash($objekt,1,"Objekt");
		# user debug:
		if($site->user) { $site->user->debug->print_msg(); }
		# guest debug: 
		if($site->guest) { 	$site->guest->debug->print_msg(); }
		$site->debug->print_msg(); 
	
	}
	##############
	# refresh opener
	# don't close popup if it was save and errors occured during saving	
	elseif($site->fdat['op2'] == 'saveclose' || $site->fdat['op2'] == 'save')
	{
	?>
		<script type="text/javascript">
		<?php
		# 1) if saving new section => open it
		if($is_new && ( $objekt->all['klass']=="rubriik"  ) ){?>
			var oldurl = window.opener.location.toString();
			var re = new RegExp("[?|&]id=");
			// if match found in opener URL then replace it
			if (oldurl.match(re)) {
				newurl = oldurl.replace(/id=(\d+)/g, "id=<?=$objekt->objekt_id?>");
			} else { // else add it to the end
				newurl = oldurl;
				if(oldurl.indexOf("?")>-1) newurl += "&"; else newurl += "?";
				newurl += "id=<?=$objekt->objekt_id?>";
			}
			window.opener.location = newurl.replace(/#$/, '');;
		<?}
		# 2) if saving new article => open parent section
		elseif($is_new && ( $objekt->all['klass']=="artikkel" ) ){ ?>
			var oldurl = window.opener.location.toString();
			var re = new RegExp("[?|&]id=");
			// if match found in opener URL then replace it
			if (oldurl.match(re)) {
				newurl = oldurl.replace(/id=(\d+)/g, "id=<?=$objekt->parent_id?>");
			} else { // else add it to the end
				newurl = oldurl;
				if(oldurl.indexOf("?")>-1) newurl += "&"; else newurl += "?";
				newurl += "id=<?=$objekt->parent_id?>";
			}
			window.opener.location = newurl.replace(/#$/, '');;
		<?}
		# 4) if saving new file and opener is WYSIWYG editor, then insert file directly
        elseif($is_new && $objekt->all['klass'] == 'file' && $site->fdat['in_wysiwyg'] == '1' && $save_in_wysiwyg_filename /* very ugly workaround for bug #2269 */)
        { 
        	?>
		// copy-paste from admin/filemanager.php	
		var vars = {
			files: [{
				folder : unescape('<?=$site->fdat['dir'];?>'),
				filename : unescape('<?=$objekt->all['filename'];?>'),
				title : unescape('<?=$objekt->all['pealkiri'];?>'),
				objekt_id : unescape('<?=$objekt->objekt_id;?>')
			}]
		}

		if(vars.files[0].folder && vars.files[0].filename) {
			/*FCKeditor insert */
			var editor = window.opener.frames[0];
			if(editor)
			{	
				editor.window.SCMSImageFileInsert(vars);
			}

			window.close();
		} else {
			alert('No file selected!')
		}
		<? }
        elseif($objekt->all['klass'] == 'file' && $site->fdat['callback']) 
        {
       	?>
			var file = {
				'relative_path' : unescape('<?=$objekt->all['relative_path'];?>'),
				'filename' : unescape('<?=$objekt->all['filename'];?>'),
				'title' : unescape('<?=$objekt->all['pealkiri'];?>'),
				'size' : unescape('<?=$objekt->all['size'];?>'),
				'mimetype' : unescape('<?=$objekt->all['mimetype'];?>'),
				'objekt_id' : unescape('<?=$objekt->objekt_id;?>'),
				'parent_id' : unescape('<?=$objekt->parent_id;?>')
			};
        	<?php echo $site->fdat['callback'];?>(file);
       	<?php
        }
        elseif($objekt->all['klass'] == 'folder' && $site->fdat['callback']) 
        {
       	?>
			var folder = {
				'relative_path' : unescape('<?=$objekt->all['relative_path'];?>'),
				'title' : unescape('<?=$objekt->all['pealkiri'];?>'),
				'objekt_id' : unescape('<?=$objekt->objekt_id;?>'),
				'parent_id' : unescape('<?=$objekt->parent_id;?>')
			};
        	<?php echo $site->fdat['callback'];?>(folder);
       	<?php
        }
        # 5) default action
		else { ?>
			var href = window.opener.location.href.replace(/#$/, '');
			
			window.opener.location.href = href;
		<? } ?>
		</script>
		<?php
		
		// close popup if no errors and saveclose
		if($site->fdat['op2'] == 'saveclose' && (sizeof($form_error) == 0 || $site->fdat['op2'] == 'deleteconfirmed'))
		{
			?>
			<script type="text/javascript">
				window.close();
			</script>
			<?
			exit;
		}
	} # refresh opener

}
# / STEP2:  SAVE DATA
#################

##################
# 0. HTML

########## INC edit_object.php

### load trigger
if (function_exists('onBeforeObjectLoad')){
	$site->globals['onBeforeObjectLoad'] = onBeforeObjectLoad($objekt);
}

include_once("edit_object.php");

###########################
# Have to put editor stuff here
# Otherwise it won't work
#
# I.e. I can't use the other HTML

if($tyyp[klass] == 'artikkel') {
	# printr($site->fdat);
	include_once("edit_artikkel.php");
	edit_objekt();
	### load trigger
	if (function_exists('onAfterObjectLoad')){
		$site->globals['onAfterObjectLoad'] = onAfterObjectLoad($objekt);
	}
	exit;
}

# / Editor stuff
##########################

###########################
# Section editor
if(($tyyp['klass'] == 'rubriik' && $site->fdat['tab'] == 'object')) {
	include_once("edit_rubriik.php");
	edit_objekt();
	### load trigger
	if (function_exists('onAfterObjectLoad')){
		$site->globals['onAfterObjectLoad'] = onAfterObjectLoad($objekt);
	}
	exit;
}
# / Section editor
##########################

###########################
# Album editor
if(($tyyp['klass'] == 'album' && $site->fdat['tab'] == 'object')) {
	include_once("edit_album.php");
	edit_objekt();
	### load trigger
	if (function_exists('onAfterObjectLoad')){
		$site->globals['onAfterObjectLoad'] = onAfterObjectLoad($objekt);
	}
	exit;
}
# / Album editor
##########################

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title;?> <?=$site->cms_version;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding ? $encoding : $site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/edit_popup.js"></SCRIPT>
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/datepicker.css">
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/jquery.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/datepicker.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'];?>/common.js.php"></script>
</head>

<?

######################
# get type name for object TABNAME
# specialcase: if type is asset, then show profile name instead of word "Asset"
if(strtolower($tyyp['nimi']) == 'asset') {
	# k�si profiili nimi pealkirjaks
	$profile_id = $objekt->objekt_id ? $objekt->all['profile_id'] : $site->fdat['profile_id'];
	$sqltmp = $site->db->prepare("SELECT name FROM object_profiles WHERE object_profiles.profile_id = ?",$profile_id);
	$sthtmp = new SQL($sqltmp);
	$typename = $sthtmp->fetchsingle();
}
# usual case:
else {
	$typename = $site->sys_sona(array(sona => "tyyp_".$tyyp['nimi'], tyyp=>"System"));
}

######################
# 1. tab OBJECT

# Note: this page was entire edit-page before in ver 3

if($site->fdat['tab'] == 'object') { 

	# ONLOAD
	if ($site->fdat['op2']!='saveclose') {
			$body_par .= "setHeadlineFocus();\"";
	}

################# BODY START
?>
<body class="popup_body" onLoad="this.focus();<?=$body_par ?>">

<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<?

########### tabs : print tabs olny if it's not type list
if(!$idlist_output){
	print_tabs(array("object_type" => $typename));
}
?>
<?
###########################
# a. PRINT OBJECT TYPE LIST	
if($idlist_output) { ?>
<tr> 
	<TD valign="top" width="100%" class="scms_table"  height="100%" style="background: #fff;">

	<!-- Scrollable area -->
	<div id=listing class="scms_middle_div">

	<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_table">
	<tr>
	<td>
	<br />

		  <table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">
			<tr> 
			  <td colspan="2"> 
				<div style="position:relative"> 
				  <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "New", tyyp=>"Editor"))?></div>
				</div>
			  </td>
			</tr>
			<tr> 
			  <td colspan=2 class="scms_table">
					<table width="100%" border="0" cellspacing="0" cellpadding="0"  class="scms_table">
					<?=$idlist_output; ?>
					</table>
				</td>
			</tr>
		  </table>

	</td>
	</tr>
	</table>

  </div>
  <!-- / Scrollable area -->

</td>
</tr>
	<?#################### BUTTONS ###########?>
	  <tr> 
	  <td align="right" valign="top" class="scms_dialog_area_bottom"> 
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();">
    </td>
  </tr>
</table>
<?
}
#######################
# b. NEW/EDIT FORM
else {
?>
<tr> 
<?## changed: scms_dialog_area => scms_table ?>
	<TD valign="top" width="100%" class="scms_table"  height="100%">
<?
# -tavaline NEW
# -NOT asseti new list
# -edit
if ($op == "new" && $tyyp['tyyp_id']
&& !($tyyp['tyyp_id']=='20' && sizeof($profile_idlist)>1)
|| $op=="edit") {


	$site->debug->msg("EDIT: finally, OP = ".$op.", tyyp_id = ".$tyyp['tyyp_id']);

	edit_general_object();

	### load trigger
	if (function_exists('onAfterObjectLoad')){
		$site->globals['onAfterObjectLoad'] = onAfterObjectLoad($objekt);
	}

}
## NB! entire table is closed (and buttons printed) in the edit_object.php file.

} 
# / b. NEW/EDIT FORM
#######################

}
# / 1. tab OBJECT
######################

######################
# 2. tab PERMISSIONS

# Note: permissions is new page starting from ver 4

elseif($site->fdat['tab'] == 'permissions') { 
	################# BODY START
?>
<body>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<?

	include_once($class_path."permissions.inc.php");
	## action "Copy permissions to subtree"
	if($site->fdat['copypermissions']) {
		copy_permissions(array(
			"type" => 'OBJ',
			"source_id" => $site->fdat['id'],
			"crud" => $site->fdat['copypermissions'],
			"user_id" => $site->fdat['perm_user_id'],
			"group_id" => $site->fdat['perm_group_id'],
			"role_id" => $site->fdat['perm_role_id'],
		));
	}
	## default: tab "Permissions"
	else {
		########### tabs
		print_tabs(array("object_type" => $typename));
		edit_permissions(array(
			"type" => 'OBJ'	
		));
	}
?>
	</table>
<?
}
# / 2. tab PERMISSIONS
######################

######################
# 2. tab SEO

elseif($site->fdat['tab'] == 'seo') { 
	################# BODY START
?>
<body>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<?

	########### tabs
	print_tabs(array("object_type" => $typename));
	include_once('edit_object_metadata.php');
	if($site->fdat['op'] == 'edit') 
	{
		if($site->fdat['op2'] == 'save') salvesta_objekt_metadata();
		edit_objekt_metadata();
	}
?>
	</table>
<?
}
# / 2. tab SEO
######################

} # browser OK

###################
# BROWSER ERROR MESSAGE

else { # browseri kontroll, agent=Netscape
	$msg1 = array("Sinu brauseriks on","Your browser is","�� ����������� ���������");
	$msg2 = array("palun kasuta meie editori jaoks Internet Explorerit!", "please use Internet Explorer for our editor!", "����������� Internet Explorer ��� ������ ���������!");
?>
<body>
<center>
<TABLE width=100% height=100% align=center>
<TR>
	<TD valign=middle align=center>
	<font size="2" face="Arial" class="tava"><?=$msg1[$site->CONF['editori_keel']] ?></font>
	"<font size="2" face="Arial" color=red class="tava"><?=$_SERVER['HTTP_USER_AGENT']; ?></font>
	<font size="2" face="Arial" class="tava">", <?=$msg2[$site->CONF['editori_keel']] ?></font>
	</TD>
</TR>
</TABLE>
</center>
</body>
<?
} # browser check

$site->debug->print_hash($site->fdat,1,"FORM DATA");
$site->debug->print_hash($objekt,1,"Objekt");
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

function print_tabs($args) {
	global $site;
	global $objekt;
	global $tyyp;
	global $keel;

?>
  <tr> 
    <td valign="top" width="100%"> 
     <table border="0" cellspacing="0" cellpadding="0" width="100%" style="height:21px">
      <tr>
       <td class="scms_tabs_empty">&nbsp;&nbsp;&nbsp;</td>
		<?
		# set all tabs: array[tab name] = tab translated name 
		$tab_arr = array();
		if($args['object_type']){ # if object name was fiven as paremter use this
			$tab_arr['object'] = $args['object_type'];
		} 
		# else use general word:
		else { 	$tab_arr['object'] = $site->sys_sona(array(sona => "object", tyyp=>"editor")); }

		##### print permissions for sections, topics and folders
		if($tyyp['tyyp_id'] == 1 || $tyyp['tyyp_id'] == 15 || $tyyp['tyyp_id'] == 22) {
			
			##### print SEO tab only for section object
			if($tyyp['tyyp_id'] == 1) $tab_arr['seo'] = $site->sys_sona(array(sona => "meta-info", tyyp=>"admin"));
			
			##### print permissions only if ACL is present
			$tab_arr['permissions'] = $site->sys_sona(array(sona => "permissions", tyyp=>"editor"));
		}
		foreach ($tab_arr as $tab=>$tab_title) {

		?>
        <td class="scms_<?=$site->fdat['tab']==$tab?'':'in'?>active_tab" nowrap>
		<?########## tab title #######?>
		<?	
		# if new object: disable tab
		if (!$objekt) {
			$tab_title = "<a href='#'>".$tab_title."</a>";	
		}
		else {
			$tab_title = "<a href=".$site->self."?tab=".$tab."&id=".$site->fdat['id']."&keel=".$keel."&op=".$site->fdat['op'].">".$tab_title."</a>";
		}
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
