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
 * Saurus CMS admin page "System > Profiles" for profiles management
 * 
 * Page is divided into 2 parts:
 * LEFT: profile type tree, MIDDLE: profile list
 * Allows add, modify, delete profiles in database
 * 
 * @package CMS
 * 
 * @param int profile_id - selected profile ID
 * @param int did - selected 1 field ID
 * @param string op - action name
 * 
 */

global $site;

$class_path = "../classes/";
include_once($class_path."port.inc.php");
include_once($class_path."adminpage.inc.php");

$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));
if (!$site->user->allowed_adminpage()) {
	exit;
}

######### get adminpage name
$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
$parent_pagename = $adminpage_names['parent_pagename'];
$pagename = $adminpage_names['pagename'];

###############################
# MOVE profile field up/down (1 row)

if(($site->fdat['op'] == "up" || $site->fdat['op'] == "down") && is_numeric($site->fdat['profile_id']) && $site->fdat['did']) {
	## Get data
	$sql = $site->db->prepare("SELECT data FROM object_profiles WHERE profile_id=?",$site->fdat['profile_id']);
	$sth = new SQL($sql);
	$existing_data = $sth->fetchsingle();
	$site->debug->msg($sth->debug->get_msgs());
	$existing_data = unserialize($existing_data);
	
#printr($existing_data);
	
	## Get keys and SRC index
	$existing_data_keys = array_keys($existing_data);
	$src_index = array_search ($site->fdat['did'], $existing_data_keys);
	## Get values
	$existing_data_values = array_values($existing_data);

	## Get dest index
	if ($site->fdat['op'] == "down") {
		if($src_index>(sizeof($existing_data_keys)-2)) {
			$dest_index = 0;
		} else {
			$dest_index = $src_index+1;
		}
	} else {
		if($src_index<1) {
			$dest_index = sizeof($existing_data_keys)-1;
		} else {
			$dest_index = $src_index-1;
		}
	}

	## Now swap keys and values
	$src_key = $existing_data_keys[$src_index];
	$src_value = $existing_data_values[$src_index];

	$existing_data_keys[$src_index] = $existing_data_keys[$dest_index];
	$existing_data_values[$src_index] = $existing_data_values[$dest_index];

	$existing_data_keys[$dest_index] = $src_key;
	$existing_data_values[$dest_index] = $src_value;
	
	##Make new array
	unset($existing_data);
	foreach($existing_data_keys as $index => $key) {
		if($key != ''){ # avoid empty defs
			$existing_data[$key] = $existing_data_values[$index];
		}
	}
#printr($existing_data);
	$update_data = serialize($existing_data);
	$sql = $site->db->prepare("UPDATE object_profiles SET data=? WHERE profile_id=?",$update_data,$site->fdat['profile_id']);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
	$site->fdat['profile_id'] = $site->fdat['profile_id'];

	
}

# / MOVE UP/DOWN
##################


##################
# SYNC with real TABLE in database
if($site->fdat['op'] == "sync" && is_numeric($site->fdat['profile_id']) ) {
	$tbl_fields = array();
	$system_fields = array();
	$existing_fields = array();
	$missing_fields = array();

	## Get existing profile
	$prof_row = $site->get_profile(array(id=>$site->fdat['profile_id'])); 
	$existing_data = unserialize($prof_row['data']);
	if(sizeof($existing_data)>0 && is_array($existing_data)){
		$existing_fields = array_keys($existing_data);
	} # if fields found

	if($prof_row['source_table']){
	# get table fields
	$tbl_fields = split(",", $site->db->get_fields(array(tabel => $prof_row['source_table'])) );

	# get system_fields - fields that doesn't have to be visible to user after sync operation
	if($prof_row['source_table'] == 'users') {
		$system_fields = array('user_id','group_id','email','is_predefined','profile_id','username','password','firstname','lastname','image','created_date','session_id','last_access_time','is_locked','pass_expires','autologin_ip','last_ip');
	}
	elseif($prof_row['source_table'] == 'groups') {
		$system_fields = array('group_id','name','parent_group_id','is_predefined','auth_type','auth_params','profile_id');
	}
	elseif($prof_row['source_table'] == 'obj_dokument') {
		$system_fields = array('fail', 'size', 'tyyp', 'objekt_id','mime_tyyp', 'sisu_blob', 'profile_id', 'repl_last_modified', 'download_type');
	}
	elseif($prof_row['source_table'] == 'obj_asset') {
		$system_fields = array('objekt_id','profile_id');
	}
	elseif($prof_row['source_table'] == 'obj_file') {
		$system_fields = array('objekt_id','fullpath','filename','mimetype','size','lastmodified','is_deleted','profile_id');
	}
	elseif($profile['source_table'] == 'obj_folder') {
		$system_fields = array('objekt_id','profile_id','fullpath');
	}
	elseif(substr($prof_row['source_table'],0,4) == 'obj_') {
		$system_fields = array('objekt_id', 'profile_id');
	}
	# get missing fields: in table but not in profile:
	if(sizeof($tbl_fields)>0 && sizeof($existing_fields)>0){
		$missing_fields = array_minus_array($tbl_fields,$existing_fields);
	}
	else { $missing_fields = $tbl_fields; }
	# exclude system fields:
	if(sizeof($missing_fields)>0 && sizeof($system_fields)>0){
		$missing_fields = array_minus_array($missing_fields,$system_fields);
	}

#printr($missing_fields);
	if(sizeof($missing_fields)>0){
	foreach($missing_fields as $missing_field){
		$new_field = array(
			"name" => $missing_field,
			"type" =>'TEXT',
			"source_object" =>'',
			"db_type" => 'varchar',
			"is_required" => 0,
			"is_active" => 0,
			"is_predefined" => 0,
		);
		$existing_data[$missing_field] = $new_field;
	
	}# foreach missing
	} # if missing found

#printr($existing_data);

		$update_data = serialize($existing_data);
		$sql = $site->db->prepare("UPDATE object_profiles SET data=? WHERE profile_id=?",$update_data,$site->fdat['profile_id']);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

	} # if source_table

	header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->self."?profile_id=".$site->fdat['profile_id']);

}

# / SYNC with real TABLE in database
##################

################ get profile data
$profile_def = $site->get_profile(array(id=>$site->fdat['profile_id'])); 
if($profile_def['name']){
	$breadcrumb_focus_str = ",'".$site->sys_sona(array(sona => $profile_def['name'], tyyp=>"custom"))."'";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>' <?=$breadcrumb_focus_str?>);
//-->
</SCRIPT>
</head>

<body style="overflow-y: auto; overflow-x: auto;">

<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
<?php 
################################
# FUNCTION BAR
?>
<!-- Toolbar -->
<TR>
<TD class="scms_toolbar">

	<?php ######### PROFILE FUNCTION BAR ############?>
      <table border="0" cellpadding="0" cellspacing="0">
        <tr> 
			<?php ############ new dropdown ###########?>
				<TD nowrap><a href="javascript:void(0)" id="top4" onclick="show_menu('sub4')"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/filenew.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id=pt> <?=$site->sys_sona(array(sona => "new", tyyp=>"editor"))?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/dropmenu.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align="absmiddle"></a>
					<!-- Dropdown -->
						<div id="sub4" class="scms_dropdown_div" style="padding-left:0;">
							<TABLE cellpadding=0 cellspacing=0 border=0 width="100%" class="scms_dropdown">
							<?php ######## new field?>
							<TR>
								<TD class="scms_dropdown_item"><a href="javascript:void(openpopup('edit_profile.php?op=newdef&pid=<?= $site->fdat['profile_id'] ?>','profile','366','450'))" ><?=$site->sys_sona(array(sona => "vali", tyyp=>"editor"))?></A></TD>
							</TR>
							<?php ######## new profile?>
							<TR>
								<TD class="scms_dropdown_item"><a href="javascript:void(openpopup('edit_profile.php?op=new&pid=<?= $site->fdat['profile_id']?>&source_table=<?= $site->fdat['source_table']?>','profile','366','450'))" ><?=$site->sys_sona(array(sona => "profile", tyyp=>"editor"))?></A></TD>
							</TR>
							<?php ######## new custom asset?>
							<TR>
								<TD class="scms_dropdown_item"><a href="javascript:void(openpopup('edit_profile.php?op=new','profile','366','450'))" ><?=$site->sys_sona(array(sona => "asset", tyyp=>"editor"))?></A></TD>
							</TR>
							<?php ######## new external table?>
							<TR>
								<TD class="scms_dropdown_item"><a href="javascript:void(openpopup('db_data.php?op=new','profile','366','150'))" ><?=$site->sys_sona(array(sona => "tabel", tyyp=>"editor"))?></A></TD>
							</TR>
							</TABLE>
						</div>
					<!-- Dropdown -->
				</TD>
		  <?php ############ edit profile button ###########?>
				<TD nowrap><?php if($site->fdat['profile_id']){?><a href="javascript:void(openpopup('edit_profile.php?op=edit&pid=<?= $site->fdat['profile_id']?>&did=<?= $site->fdat['did']?>','profile','366','450'))"><?php }?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/edit<?=(!$site->fdat['profile_id'] ? '_inactive' : '')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle> <?=$site->sys_sona(array(sona => "muuda", tyyp=>"editor"))?><?php if($site->fdat['profile_id']){?></a><?php }?></TD>

		  <?php ############ delete profile button ###########?>
				<TD><?php if($site->fdat['profile_id']){?><a href="javascript:void(openpopup('edit_profile.php?op=delete&pid=<?= $site->fdat['profile_id']?>','profile','413','108'))"><?php }?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete<?=(!$site->fdat['profile_id'] ? '_inactive' : '')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle><?php if($site->fdat['profile_id']){?></a><?php }?></TD>

		  <?php ############ duplicate profile/field button ###########?>
				<TD><?php if($site->fdat['profile_id']){?><a href="javascript:void(openpopup('edit_profile.php?op=duplicate&pid=<?= $site->fdat['profile_id']?>&did=<?=$site->fdat['did']?>','profile','366','450'))"><?php }?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/editcopy<?=(!$site->fdat['profile_id'] ? '_inactive' : '')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle><?php if($site->fdat['profile_id']){?></a><?php }?></TD>

				<?php /*********************
					*  SYNC BUTTON
					*********************/?>
				<TD><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/s_toolbar_divider.gif" WIDTH="14" HEIGHT="20" BORDER="0" ALT="" align=absmiddle></TD>
				<TD><a href="<?= $site->self ?>?profile_id=<?= $site->fdat['profile_id']?>&op=sync" class="scms_button_img"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/refresh.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle></a></TD>

		  
        </tr>
      </table>
</TD>
</TR>

<?php 
# / FUNCTION BAR
################################
?>
  <!-- //Toolbar -->
  <!-- Content area -->

  <tr valign="top"> 
<?php 
############################
# PROFILE TYPES MENUTREE
?>
<td >
	<!-- content table -->	
	<TABLE class="scms_content_area" border=0 cellspacing=0 cellpadding=0>
	<TR>
		<!-- Left column -->
		<TD class="scms_left">

			<div id=navigation class="scms_left_div">
				<table width="100%" height="100%"  border="0" cellpadding="0" cellspacing="0">
					<!-- I grupp -->
					<tr>
						<td valign=top>
     <?php 
	  #####################
	  # TREE
		require_once($class_path.'menu.class.php');

		######## USER TREE
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		'users');
		$sth = new SQL($sql);
		$temp_tree = array();
		while ($data = $sth->fetch('ASSOC')){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			$temp_tree[] = $data;		
		}
		$menu = new Menu(array(
			width=> "100%",
			tree => $temp_tree,
			datatype => "profile",
			table => 'users',
			tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/users/user.png',
			tree_title =>  $site->sys_sona(array(sona => "user", tyyp=>"kasutaja")),
			no_separator => 1
		));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr><td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;
?>
								
						</td>
					</tr>
					<!-- //I grupp -->
					<!-- II grupp -->
					<tr>
						<td valign=top>

<?php 	######## GROUP TREE
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		'groups');
		$sth = new SQL($sql);
		$temp_tree = array();
		while ($data = $sth->fetch()){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			$temp_tree[] = $data;		
		}
		$menu = new Menu(array(
			width=> "100%",
			tree => $temp_tree,
			datatype => "profile",
			table => 'groups',
			tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/users/group.png',
			tree_title => $site->sys_sona(array(sona => "Group", tyyp=>"kasutaja"))
		));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%"  border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr >
						<td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;
?>
						</td>
					</tr>
					<!-- //II grupp -->


					<!-- article -->
					<tr>
						<td valign=top>
<?php 

		######## ARTICLE TREE
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		'obj_artikkel');
		$sth = new SQL($sql);
		$temp_tree = array();
		while ($data = $sth->fetch()){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			$temp_tree[] = $data;		
		}
		$menu = new Menu(array(
			width=> "100%",
			tree => $temp_tree,
			datatype => "profile",
			table => 'obj_artikkel',
			tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/mime/contenthtml.png',
			tree_title => $site->sys_sona(array(sona => "artikkel", tyyp=>"editor"))
		));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%"  border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr >
						<td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;
?>
						</td>
					</tr>

					<!-- //II grupp -->

					<!-- III grupp -->
					<tr>
						<td valign=top>

<?php 
		######## FILEMANAGER TREE
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		'obj_file');
		$sth = new SQL($sql);
		$temp_tree = array();
		while ($data = $sth->fetch()){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			$temp_tree[] = $data;		
		}
		$menu = new Menu(array(
			width=> "100%",
			tree => $temp_tree,
			datatype => "profile",
			table => 'obj_file',
			tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/mime/folder_open.png',
			tree_title => $site->sys_sona(array(sona => "files", tyyp=>"admin"))
		));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr >
						<td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;
?>
						</td>
					</tr>
					<!-- //III grupp -->
					<!-- IV grupp -->
<!--						<tr>
						<td valign=top>
-->
<?php 
		######## DOCUMENT TREE

		/** COMMENT OUT FOR NOW
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		'obj_dokument');
		$sth = new SQL($sql);
		$temp_tree = array();
		while ($data = $sth->fetch()){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			$temp_tree[] = $data;		
		}
		$menu = new Menu(array(
			width=> "100%",
			tree => $temp_tree,
			datatype => "profile",
			table => 'obj_dokument',
			tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/mime/files.png',
			tree_title => $site->sys_sona(array(sona => "dokument", tyyp=>"editor"))
		));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr >
						<td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;

COMMENT OUT FOR NOW **/

?>
<!--						</td>
					</tr>
-->
					<!-- //III grupp -->
					
					<!-- assets group -->
					<tr>
						<td valign=top>

<?php 

		######## CUSTOM ASSET TREE
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		'obj_asset');
		$sth = new SQL($sql);
		$temp_tree = array();
		while ($data = $sth->fetch()){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			$temp_tree[] = $data;		
		}
		$menu = new Menu(array(
			width=> "100%",
			tree => $temp_tree,
			datatype => "profile",
			table => 'obj_asset',
			tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/mime/asset.png',
			tree_title => '<a href="'.$site->self.'?source_table=obj_asset">'.$site->sys_sona(array(sona => "asset", tyyp=>"editor")).'</a>'
		));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr >
						<td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;
?>

						</td>
					</tr>
					<!-- / assets group -->

					<!-- forms group -->
					<tr>
						<td valign=top>

<?php 

		######## FORMS TREE
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table LIKE ? ORDER BY name",
		'form_%');
		$sth = new SQL($sql);
		$temp_tree = array();
		while ($data = $sth->fetch()){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			$temp_tree[] = $data;		
		}
		$menu = new Menu(array(
			width=> "100%",
			tree => $temp_tree,
			datatype => "profile",
			table => 'forms_',
			tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/mime/files.png',
			tree_title => '<a href="'.$site->self.'?source_table=form_">'.$site->sys_sona(array(sona => "Form", tyyp=>"admin")).'</a>'
		));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr >
						<td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;
?>

						</td>
					</tr>
					<!-- / forms group -->

					
					<!-- extrenal tabels group -->
					<tr height=100%>
						<td valign=top>

<?php 	########### EXTERNAL TABLES TREES

		$sql = $site->db->prepare("show tables");
		$sth = new SQL($sql);
		while ($tbl_data = $sth->fetchsingle()){
			$tables[] = $tbl_data;
		}
#printr($tables);

		$ext_tables = array();
		foreach($tables as $table){
			# add table name to array if this has right external prefix
			if(substr($table,0,4)=='ext_'){
				$ext_tables[] = $table;
			} # if correct prefix
		}
		##### loop over external tables
		foreach($ext_tables as $ext_table) {
			############ PRINT TREE
			$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
			$ext_table);
			$sth = new SQL($sql);
			$temp_tree = array();
			while ($data = $sth->fetch()){
				### change technical profile name to translation in current language:
				$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
				$temp_tree[] = $data;		
			}
			$menu = new Menu(array(
				width=> "100%",
				tree => $temp_tree,
				datatype => "profile",
				table => $ext_table,
				tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/mime/object.png',
				tree_title => '<a href="'.$site->self.'?source_table='.$ext_table.'">'.substr($ext_table,4).'</a>'
			));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr >
						<td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;
		}
		##### / loop over external tables

	  ?>          

						</td>
					</tr>
					<!-- / extrenal tabels group -->
							
				</table>


</DIV>
</TD>

<?php 
# / PROFILE TYPES MENUTREE
############################
?>

<?php 
############################
# MIDDLE LIST
?>
		<!-- Middle column -->
		<TD class="scms_middle_dialogwindow">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr class="scms_pane_header"> 
                     <td>			
					 <?=($site->fdat['profile_id']?$site->sys_sona(array(sona => $profile_def['name'], tyyp=>"custom")).' &gt; ':'')?>
					   <?=$site->sys_sona(array(sona => "Fields", tyyp=>"admin"))?>
					 </td>
					 <td>
						<!-- Paging -->

						<!-- //Paging -->
					 </td>
                    </tr>
                 </table>
				
			<table width="100%" height="95%" border="0" cellspacing="0" cellpadding="0">
		   <!-- Table header -->	
			  <tr height=10> 
                <td valign="top" class="scms_tableheader">
	<?php 
	####### get assoc.array of visible fieldnames and translations
#	$visible_fields = get_visible_fields(array(
#		"prefpage_name" => 'profiles',
#		"sst_name" => 'custom',
#		"labels" => $labels,
#	));
	####### print column headers table
#	print_column_headers(array(
#		"visible_fields" => $visible_fields,
#		"page_prefs_url" => '&name=profiles&sst_name=custom&table=object_profiles',
#	));
	##### td width: calculate percents
#	$td_width = intval((100/sizeof(array_keys($visible_fields)))).'%';

	?>
					<table width="100%"  border="0" cellspacing="0" cellpadding="0">
						<tr> 

						  <td width="16" nowrap><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/visible.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="Visibility"></td>
						  <td width="25%" nowrap><?=$site->sys_sona(array(sona => "Fieldname", tyyp=>"editor"))?></td>
						  <td width="25%"><?=$site->sys_sona(array(sona => "Tolkimine", tyyp=>"admin"))?></td>
						  <td width="25%"><?=$site->sys_sona(array(sona => "input type", tyyp=>"admin"))?></td>
						  <td width="25%"><?=$site->sys_sona(array(sona => "on noutud", tyyp=>"editor"))?></td>
						  <td width="16" align="right"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/px.gif" WIDTH="16" HEIGHT="1" BORDER="0" ALT=""></td>
						  <td width="16" align="right"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/px.gif" WIDTH="16" HEIGHT="1" BORDER="0" ALT=""></td>
						  <td width="17"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/px.gif" WIDTH="17" HEIGHT="1" BORDER="0" ALT=""></td>
						  <td width="15"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/px.gif" WIDTH="15" HEIGHT="1" BORDER="0" ALT=""></td>
						</tr>
					</table>


				</td>
			</tr>
			<!-- // Table header -->

			<tr>
				<td valign=top>
					<!-- Scrollable area -->
					<div id=listing class="scms_middle_div">


				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="scms_table">

				<?php 
# sanity check: kui ei leitud sellise nimega profiili, anda toimetajale veateade ja v�ljuda:
if(!$profile_def['profile_id']) {
	if($site->in_admin && $site->fdat['profile_id']) {
#		print "<font color=red><b>Profile '".$site->fdat['profile_id']."' not found!</b></font>";
	}
	exit;
}

#echo printr($profile_def);
$profile_fields = array();
$profile_fields = unserialize($profile_def[data]);
#echo printr($profile_fields);

#Bug #1530: Eriobjektide vaates n�htavaks teha Title v�li
if($profile_def[source_table]=='obj_asset'){
	$tmp_pealkiri_arr['pealkiri'] = array(
		"name" => "pealkiri",
		"type" => "TEXT",
		"source_object" => '',
		"db_type" => "varchar",
		"is_required" => 1,
		"is_active" => 1,
		"is_predefined" => 1
	);
	if(is_array($profile_fields) && sizeof($profile_fields)>0){  $profile_fields = array_merge($tmp_pealkiri_arr,$profile_fields); }
	else { $profile_fields = $tmp_pealkiri_arr; }
}

##################
# loop over fields
if(is_array($profile_fields)) {
foreach($profile_fields as $key => $value) {

	$href = "javascript:document.location='".$site->self."?profile_id=".$site->fdat['profile_id']."&did=".$key."'";
	$dblclick = "void(openpopup('edit_profile.php?op=edit&did=".$key."&pid=".$site->fdat['profile_id']."','profile','366','450'))";
	$label = $site->sys_sona(array(sona => $value[name], tyyp=>"custom", lang_id=>$site->glossary_id));
	$label = $label != '['.$profile_info["name"].']' ? $label : '';	# kui s�steemis�na puudub

?>
				<tr <?=($site->fdat['did'] == $key ? ' class="scms_activerow"' : '')?>> 
				<?php ############# active (visible) ?>
				<td width="20"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/mime/<?php if($value[is_active]){?>visible<?php }else{?>hidden<?php }?>.png" width="16" height="16" alt="">

				<?php ############# name ?>
                  <td width="25%" nowrap><a href="<?=$href?>" ondblclick="<?=$dblclick?>"><?= $value[name] ?></a></td>
				<?php ############# label ?>
				  <td width="25%" nowrap><a href="<?=$href?>" ondblclick="<?=$dblclick?>"><?=$label?></a></td>
				<?php ############# data-type ?>
				  <td width="25%" nowrap><a href="<?=$href?>" ondblclick="<?=$dblclick?>"><?= $value['type'] ?></a></td>
				<?php ############# mandatory ?>
				  <td width="25%" ><?php if($value[is_required]){?><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/check.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="Yes"><?php }else{?>&nbsp;<?php }?></td>
				<?php ############# up + down  ?>

				  <td  width="16" align="right"><a href="<?= $site->self ?>?op=up&did=<?= $key ?>&profile_id=<?= $site->fdat['profile_id'] ?>"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/up_arrow.png" width="16" height="16" border="0" alt="Up"></a></td>
                  <td  width="16" align="right"><a href="<?= $site->self ?>?op=down&did=<?= $key ?>&profile_id=<?= $site->fdat['profile_id'] ?>"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/down_arrow.png" width="16" height="16" border="0" alt="Down"></a></td>
				<?php ############# delete  ?>
				  <td width="16" align="right"><a href="javascript:void(openpopup('edit_profile.php?op=delete&did=<?= $key ?>&profile_id=<?= $site->fdat['profile_id'] ?>','profile','413','108'))"><img alt="" src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete.png" width="16" height="16"  border=0></a></td>
                </tr>
<?php 
} # if array
}
# / loop over fields
##################
?>

              </table>
           </div>
		<!-- //Scrollable area -->

          </td>
        </tr>
      </table>

		</TD>
	</TR>
	</TABLE>
	<!-- content table -->	

	
	
	</td>
  </tr>
</table>

</body>

</html>

