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
 * Saurus CMS admin page "Organization > People", for permissions management
 * 
 * Page is divided into 2 parts:
 * LEFT: permission type tree, MIDDLE: permission list 
 * Allows add, modify, delete permissions in database
 * 
 * @param int permission_id - selected permission ID
 * @param string op - action name
 * 
 */

global $site;


$class_path = "../classes/";
include_once($class_path."port.inc.php");
include_once($class_path."adminpage.inc.php");
include_once($class_path."user_html.inc.php");

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


global $read_allowed_groups;
global $all_levels;
global $selected_parents;
global $column_count;


###########
# PERMISSIONS CHECK - get read-allowed group ID-s for current user
$read_allowed_groups = get_allowed_groups();
#echo printr($read_allowed_groups);

$top_group = get_topparent_group(array("site" => $site));

########### find user_id & group_id & role_id (what was selected in selectbox)
if($site->fdat['selected_group']) {
	list($type,$sel_id) = split(":",$site->fdat['selected_group']);
	$site->fdat['user_id'] = $type=='user_id' ? $sel_id : '';
	$site->fdat['group_id'] = $type=='group_id' ? $sel_id : '';
	$site->fdat['role_id'] = $type=='role_id' ? $sel_id : '';
	if($type=='user_id'){
		$site->fdat['group_id'] = get_my_group(array("who" => $site->fdat['selected_group']));
	}
}
else {
	$site->fdat['selected_group'] = 'group_id:'.$site->fdat['group_id'];
}
#echo $site->fdat['selected_group']. " gr:".$site->fdat['group_id'];

########### find ALL GROUPS as TREE
# push all groups to level array
$all_levels = array();

foreach(get_groupleafs(array("group_id" => $top_group)) as $key=>$tmpgroup){
	if(!$tmpgroup['level']){$tmpgroup['level']=1;}
	$all_levels[$tmpgroup['level']][] = $tmpgroup;
}
#printr($all_levels);

################## get SELECTED item group PARENTS
$grouptree = get_grouptree(array("group_id" => $site->fdat['group_id']));
#printr($grouptree);
foreach($grouptree as $tmgroup) {
	$selected_parents[] = $tmgroup['id'];
}
#printr($selected_parents);
#printr($site->fdat['user_id']);

#################
# SAVE
if($site->fdat['op']=='save'){
	save_all_permissions();

	$site->fdat['op']=='';
}


# / SAVE
#################

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/admin_menu.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>' <?=$breadcrumb_focus_str?>);

function select_acl(value){
	if(value!=''){
	document.getElementById('selectform_selected_group').value = value;
//	alert(value);
	document.forms['selectform'].submit();
	}
}
//-->
</SCRIPT>
<script language="JavaScript1.2">
	<!--

var detect = navigator.userAgent.toLowerCase();
var OS,browser,version,total,thestring;

if (checkIt('konqueror'))
{
	browser = "Konqueror";
	OS = "Linux";
}
else if (checkIt('safari')) browser = "Safari"
else if (checkIt('omniweb')) browser = "OmniWeb"
else if (checkIt('opera')) browser = "Opera"
else if (checkIt('webtv')) browser = "WebTV";
else if (checkIt('icab')) browser = "iCab"
else if (checkIt('msie')) browser = "Internet Explorer"
else if (!checkIt('compatible'))
{
	browser = "Netscape Navigator"
	version = detect.charAt(8);
}
else browser = "An unknown browser";

if (!version) version = detect.charAt(place + thestring.length);

if (!OS)
{
	if (checkIt('linux')) OS = "Linux";
	else if (checkIt('x11')) OS = "Unix";
	else if (checkIt('mac')) OS = "Mac"
	else if (checkIt('win')) OS = "Windows"
	else OS = "an unknown operating system";
}

function checkIt(string)
{
	place = detect.indexOf(string) + 1;
	thestring = string;
	return place;
}


	function ExpandDetail(idx) {
//alert(idx);
		var image = document.getElementById('image' + idx);
		var tr_tags = document.getElementsByTagName("tr");
		var children = [];
		var srch = new RegExp("overview" + idx + "_", "i");

		if(/_closed/.test(image.src)) {
			image.src = "<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/arrow_open.gif";
			for(var t=0;t<tr_tags.length;t++) {
				if(srch.test(tr_tags[t].id)) {
//	alert(tr_tags[t].id);
                    if(browser == 'Internet Explorer') tr_tags[t].style.display = 'block'
                    else tr_tags[t].style.display = 'table-row';
				}
			}
		} else {
			image.src = "<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/arrow_closed.gif";
			for(var t=0;t<tr_tags.length;t++) {
				if(srch.test(tr_tags[t].id)) {
//		alert(tr_tags[t].id);
					tr_tags[t].style.display = 'none';
				}

			}
		}
	}
	//-->
	</script>

</head>

<body style="overflow-y: auto; overflow-x: auto;">

<?############ FORM #########?>
<form name="selectform" action="<?=$site->self?>" method="POST">
<?
######## gather all fdat values into hidden fields
#foreach($site->fdat as $fdat_field=>$fdat_value) { 
#	if($fdat_field != 'op' && substr($fdat_field,0,4) != 'tmp_'){
#		echo '<input type=hidden id="selectform_'.$fdat_field.'" name="'.$fdat_field.'" value="'.$fdat_value.'">';
#	} 
#} 
?>
<input type=hidden id="selectform_op" name="op" value="">
<input type=hidden id="selectform_selected_group" name="selected_group" value="<?=$site->fdat['selected_group']?>">
<input type=hidden id="selectform_user_id" name="user_id" value="<?=$site->fdat['user_id']?>">
<input type=hidden id="selectform_group_id" name="group_id" value="<?=$site->fdat['group_id']?>">



<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
  <!-- Toolbar -->
  <tr>
	<td class="scms_toolbar">
	
			<TABLE cellpadding=0 cellspacing=0 border=0>
			<TR>
			<?######### SAVE button?>
				<TD nowrap><a href="javascript:document.getElementById('selectform_op').value='save';document.forms['selectform'].submit();"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/filesave.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id="pt"> <?=$site->sys_sona(array(sona => "salvesta", tyyp=>"editor"))?></a></TD>
			<?######### NEW role button?>
				<TD nowrap><a href="javascript:void(openpopup('edit_role.php?op=new','role','366','150'))"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/filenew.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id="pt"> <?=$site->sys_sona(array(sona => "new", tyyp=>"editor"))?></a></TD>

		  <?############ edit role button ###########?>
				<TD nowrap><?if($site->fdat['role_id']){?><a href="javascript:void(openpopup('edit_role.php?op=edit&role_id=<?= $site->fdat['role_id']?>','role','366','150'))"><?}?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/edit<?=(!$site->fdat['role_id'] ? '_inactive' : '')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle> <?=$site->sys_sona(array(sona => "muuda", tyyp=>"editor"))?><?if($site->fdat['role_id']){?></a><?}?></TD>

		  <?############ delete role button ###########?>
				<TD><?if($site->fdat['role_id']){?><a href="javascript:void(openpopup('edit_role.php?op=delete&role_id=<?= $site->fdat['role_id']?>','role','413','108'))"><?}?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete<?=(!$site->fdat['role_id'] ? '_inactive' : '')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle><?if($site->fdat['role_id']){?></a><?}?></TD>

			</TR>
			</TABLE>
	</td>
  </tr>
  <!-- //Toolbar -->
  <!-- Content area -->
  <tr valign="top"> 
    <td >
	
	<TABLE class="scms_content_area" border=0 cellspacing=0 cellpadding=0>
	<TR>
		<!-- Middle column -->
		<TD class="scms_middle_dialogwindow" style="padding-left:10px">
			<TABLE  width="100%" height=100% border="0" cellspacing="0" cellpadding="0">
				<!-- Table title -->
				<TR height=25>
					<TD>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr class="scms_pane_header"> 
							 <td>			
							  <IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/users/group.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle> <?=$site->sys_sona(array(sona => "Permissions", tyyp=>"admin"))?>
							 </td>
							 <td>
								
							 </td>
							</tr>
						 </table>
					</TD>
				</TR>
				<!-- // Table title -->
				<!-- Table data -->
				<TR height=100%>
					<TD valign=top>
<?
#####################
# CONTENT
?>
						<!-- Scrollable area -->
						<div id=listing class="scms_middle_div">
							<TABLE width=100% height=100% cellpadding=0 cellspacing=0 border=0>
							<TR>

								<!-- Permissions table -->
								<TD valign=top>
<?
###################
# PERMISSIONS TABLE
?>
							<TABLE cellpadding=0 cellspacing=0 border=0 class="scms_permissions_table">
	<?
	##################
	# GROUP ROW
	?>
										<TR>
											<td>&nbsp;</td>
											<!-- Group selectors -->
				<?	
				####################
				# GROUP SELECTBOXES
					# ALL 
					$level=1;
					foreach($selected_parents as $group) {
						print_group_selectbox(array(
							"group_id" => $group,
							"level" => $level
						));
						$level++;							
					} 
					# ADDITIONAL cell with next level, printed only if found data
					print_group_selectbox(array(
						"group_id" => '',
						"level" => $level
					));
				# / GROUP SELECTBOXES
				####################
				?>
											<!-- //Group selectors -->
										</TR>
	<?
	# / GROUP ROW
	##################
	?>

<?############# idx - unique counter over all rows
$idx = 0;
?>

	<!-- Division header -->
	<?
	##################
	# 1. OBJECT 
	
	########### get ALL PERMISSIONS FOR this section, huge array
	$source_permissions = &get_source_permissions(array("perm_type" => 'OBJ'));
#	printr($source_permissions);
	
	?>
		<!-- //Division header -->
		
<?
	############ get ACTIVE LANGUAGES
	$sql = $site->db->prepare("SELECT * FROM keel WHERE keel.on_kasutusel='1'");
	$sth = new SQL($sql);
	$lang_arr = array();
	$lang_names = array();
	while ($lang = $sth->fetch()) {
		$lang_arr[] = $lang['keel_id'];
		$lang_names[$lang['keel_id']] = $lang['nimi']. " (".$lang['extension'].")";
	}
	######### loop over LANGUAGES
	foreach($lang_arr as $keel){

	print_header_row(array(
		"permissions" => 'C,R,U,P,D',
		"perm_type" => 'OBJ',
		"title" => $lang_names[$keel]
	));


	##########################
		# Koostame objektide massiivi
		$sql = $site->db->prepare("
			SELECT objekt.objekt_id, objekt.pealkiri, objekt.on_avaldatud, objekt.tyyp_id, objekt_objekt.parent_id, objekt.kesk, objekt_objekt.sorteering as sort
			FROM objekt 
			LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id
			WHERE objekt.keel=? AND tyyp_id IN(?) AND (objekt_objekt.parent_id<>0 OR objekt.sys_alias=? OR objekt.sys_alias=?)",
			$keel, "1", 'home', 'system'
		);
		$sql .= " ORDER BY objekt.kesk ASC, objekt_objekt.sorteering DESC ";
		$sth = new SQL ($sql);
		#print $sql;
		$obj_count = $sth->rows;
		$temp_tree = array();
		while ($obj=$sth->fetch()) {
			####### check permissions
			$permtmp = get_obj_permission(array(
				"objekt_id" => $obj['objekt_id'],
				"on_avaldatud" => $obj['on_avaldatud'],
				"tyyp_id" => $obj['tyyp_id'],
				"parent_id" => $obj['parent_id'],
			));
			# kas useril on õigus objekti näha? 1/0
			if($permtmp['is_visible'] ) { $is_access = 1; }
			else { $is_access = 0; }
			
			######### if access granted
			if ($is_access){
				$data = array();
				$data['id'] = $obj['objekt_id'];
				$data['parent'] = $obj['parent_id'];
				$data['name'] = $obj['pealkiri'];
				$temp_tree[] = $data;		
			} # is access
		}
		# / Koostame objektide massiivi
		##########################
#printr($temp_tree);

?>	
		<!-- 1st level -->
		<?
		##################
		# data row
		$current_level = 1;
		$obj_tree = get_array_tree($temp_tree);
		if(is_array($obj_tree)){
		foreach ($obj_tree as $key=>$value) {
			$idx++;
			print_obj_row(array(
				"parent" => $value['parent'],
				"obj" => $value,
				"leafs_found" => is_array(get_array_leafs($temp_tree, $value['id'])) ? 1 : 0
			));
		} 
		}
		######## / loop over rows
	}
	######### loop over LANGUAGES

	# / 1. OBJECT 
	##################
	?>

	<!-- Division header -->
	<?
	##################
	# 2. ADMIN

	print_header_row(array(
		"permissions" => 'R,U',
		"perm_type" => 'ADMIN',
		"title" => 'Admin'
	));

	########### get ALL PERMISSIONS FOR this section, huge array
	$source_permissions = &get_source_permissions(array("perm_type" => 'ADMIN'));
	#printr($source_permissions);
	
	?>
		<!-- //Division header -->
	<?
	list($peaosad,$alamlipikud_joined) = get_adminpages_arr();	
#printr($peaosad);
	######## loop over MAIN PAGES
	foreach($peaosad['nimi'] as $pea_id => $pea_name) {
		$idx++;
		# otsime lubatud alamlipikud
		$sql = "SELECT * FROM admin_osa WHERE parent_id='".$peaosad['id'][$pea_id]."' AND  id IN('".$alamlipikud_joined."') ORDER BY sorteering DESC";
	    $sth = new SQL($sql);
	    $alam_rows = $sth->rows;
	?>
		<TR>
			<td id="section">
			<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/arrow_closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" alt="expand" id="image<?=$idx?>" style="cursor:hand" onclick="ExpandDetail(<?=$idx?>)" onkeypress="ExpandDetail(<?=$idx?>)" align=absmiddle><a href="#" onclick="ExpandDetail(<?=$idx?>)"><?= $site->sys_sona(array(sona => $pea_name , tyyp=>"admin")) ?></a>
			</td>
		<?	#### emtpy cells
		$level=1;
		foreach($selected_parents as $group) {
			echo '<td><div id="add_row_div"><INPUT TYPE="checkbox" NAME="tmp_" style="border: 1px solid #f4f4f4" disabled></div></td>';
		} 
		echo '<td><div id="add_row_div"><INPUT TYPE="checkbox" NAME="tmp_" style="border: 1px solid #f4f4f4" disabled></div></td>';
		?>
		</TR>
		<?######## loop over SUBPAGES 
			if($alam_rows) { 
			 while ($alamlp = $sth->fetch()) {
			?>
				<TR style="display: none" id="overview<?=$idx.'_'.$sth->i?>ADMIN">
					<td id="section">
						<ul class="scms_tree_menu">
							<ul class="scms_tree_menu">
								<li class="scms_plain"><a href="#"><?= $site->sys_sona(array(sona => $alamlp[eng_nimetus] , tyyp=>"admin")) ?></a></li>
							<ul>
						<ul>
					</td>
				<?	
				#### permission cell 
				$level=1;
				foreach($selected_parents as $group) {
					# if role is selected in the first selectbox, get role permissions
					if($level==1 && $site->fdat['role_id']) {
						$perm = $source_permissions[$alamlp['id']]['role'][$site->fdat['role_id']];
					}
					# else get group permissions
					else { 	$perm = $source_permissions[$alamlp['id']]['group'][$group]; }		
					#echo printr($perm);
					print_permission(array(
						"role_id" => $site->fdat['role_id'],
						"group_id" => (!$site->fdat['role_id']?$group:''),
						"user_id" => '',
						"source_id" => $alamlp['id'],
						"permissions" => 'R,U',
						"perm_type" => 'ADMIN',
						"perm" => $perm
					));
					$level++;							
				} 
				# if USER is selected in the last selectbox:
				if($site->fdat['user_id']) {
					$user_id = $site->fdat['user_id'];
					$perm = $source_permissions[$alamlp['id']]['user'][$user_id]; 
					#echo printr($perm);
					print_permission(array(
						"group_id" => '',
						"user_id" => $user_id,
						"source_id" => $alamlp['id'],
						"permissions" => 'R,U',
						"perm_type" => 'ADMIN',
						"perm" => $perm
					));			
				}
				else {
					echo '<td><div id="add_row_div"><INPUT TYPE="checkbox" NAME="tmp_" style="border: 1px solid #f4f4f4" disabled></div></td>';
				}
				?>
				</TR>


			<?
			 } # while
			}	
			######## / loop over SUBPAGES ?>
	<?
	} 	######## / loop over main pages 
	# / 2. ADMIN
	######################
	?>

	<?
	##################
	# 3. ACL
	
	print_header_row(array(
		"permissions" => 'C,R,U,D',
		"perm_type" => 'ACL',
		"title" => $site->sys_sona(array(sona => "groups", tyyp=>"kasutaja"))
	));
	########### get ALL PERMISSIONS FOR this section, huge array
	$source_permissions = &get_source_permissions(array("perm_type" => 'ACL'));
	#printr($source_permissions);
	
	?>
		<!-- //Division header -->
	<?
	####### get groups
	if($site->user->is_superuser) { $group_where_str = " 1=1 ";  }
	else {  $group_where_str = $site->db->prepare(" group_id IN('".join("','",$read_allowed_groups)."')"); }

	####### SQL with permissions check: get only groups, which are read-allowed to user
  	$sql = $site->db->prepare("SELECT group_id AS id, parent_group_id AS parent, name FROM groups ");
	$sql .= " WHERE ".$group_where_str;
	$sql .= " ORDER BY name";
	#print $sql;
	$sth = new SQL($sql);
	$temp_tree = array();
	while ($data = $sth->fetch()){
		$temp_tree[] = $data;		
	}
	#printr($temp_tree);
	########## loop over groups
	if(sizeof($temp_tree)>0){  # avoid php warnings
	$sorted_tree = get_array_tree($temp_tree);
	if(is_array($sorted_tree)){ # avoid php warnings
	foreach ($sorted_tree as $key=>$value) {

		$idx++;

		$group_name = $value['name'];
		$group_level = $value['level'];
	#echo '<br>'.$group_level.". ".$group_name;
	?>
		<!-- 1st level -->
		<TR style="display: <?if($group_level>0) { ?>none<?}?>" id="overview<?=$value['parent'].'_'.$idx?>ACL">
			<td id="section"><?echo str_repeat('&nbsp;&nbsp;',$group_level);?>
			<? #### if subtree exists
			if(is_array(get_array_leafs($temp_tree, $value['id'])) ) {  ?>
			<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/arrow_closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" alt="expand" id="image<?=$value['id']?>" style="cursor:hand" onclick="ExpandDetail(<?=$value['id']?>)" onkeypress="ExpandDetail(<?=$value['id']?>)" align=absmiddle><a href="#" onclick="ExpandDetail(<?=$value['id']?>)"><?=$group_name?></a>
			<? } 
			#### if no subtree, show inactive arrow and no link
			else { ?>
				<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/arrow_inactive.gif" WIDTH="16" HEIGHT="16" BORDER="0" align=absmiddle><?=$group_name?>
			<?} # if subtree ?>
			</td>
			<?	#### permission cell 
			$level=1;
			foreach($selected_parents as $group) {
				# if role is selected in the first selectbox, get role permissions
				if($level==1 && $site->fdat['role_id']) {
					$perm = $source_permissions[$value['id']]['role'][$site->fdat['role_id']];
				}
				# else get group permissions
				else { 	$perm = $source_permissions[$value['id']]['group'][$group]; }		
				#echo printr($perm);
				print_permission(array(
					"role_id" => $site->fdat['role_id'],
					"group_id" => (!$site->fdat['role_id']?$group:''),
					"user_id" => '',
					"source_id" => $value['id'],
					"permissions" => 'C,R,U,D',
					"perm_type" => 'ACL',
					"perm" => $perm
				));
				$level++;							
			} 
				# if USER is selected in the last selectbox:
				if($site->fdat['user_id']) {
					$user_id = $site->fdat['user_id'];
					$perm = $source_permissions[$value['id']]['user'][$user_id]; 
					#echo printr($perm);
					print_permission(array(
						"group_id" => '',
						"user_id" => $user_id,
						"source_id" => $value['id'],
						"permissions" => 'C,R,U,D',
						"perm_type" => 'ACL',
						"perm" => $perm
					));			
				}
				else {
					echo '<td><div id="add_row_div"><INPUT TYPE="checkbox" NAME="tmp_" style="border: 1px solid #f4f4f4" disabled></div></td>';
				}

			?>
		</TR>
	<?
	}
	} # is_array sorted_tree
	} # sizeof temp_tree
	########## / loop over groups

	# / 3. ACL
	##################
	?>


		<?
	##################
	# 4. EXTENSIONS
	
	print_header_row(array(
		"permissions" => 'C,R,U,P,D',
		"perm_type" => 'EXT',
		"title" => $site->sys_sona(array(sona => "extensions", tyyp=>"admin"))
	));
	########### get ALL PERMISSIONS FOR this section, huge array
	$source_permissions = &get_source_permissions(array("perm_type" => 'EXT'));
#	printr($source_permissions);
	
	?>
	<!-- //Division header -->
	<?
	####### get extensions
	if($site->user->is_superuser) { $extension_where_str = " 1=1 ";  }
	elseif(	sizeof($read_allowed_extensions)>0 ) {  $extension_where_str = $site->db->prepare(" extension_id IN('".join("','",$read_allowed_extensions)."')"); }
	else { $extension_where_str = " 0 "; }

	####### SQL with permissions check: get only extensions, which are read-allowed to user
  	$sql = $site->db->prepare("SELECT extension_id AS id, parent_id AS parent, name FROM extensions ");
	$sql .= " WHERE ".$extension_where_str;
	$sql .= " ORDER BY name";
	#print $sql;
	$sth = new SQL($sql);
	$temp_tree = array();
	while ($data = $sth->fetch()){
		$temp_tree[] = $data;		
	}
	#printr(get_array_tree($temp_tree));
	########## loop over extensions
	if(sizeof($temp_tree)>0){
	foreach (get_array_tree($temp_tree) as $key=>$value) {

		$idx++;

		$extension_name = $value['name'];
		$extension_level = $value['level'];
	#echo '<br>'.$extension_level.". ".$extension_name;
	?>
		<!-- 1st level -->
		<TR style="display: <?if($extension_level>0) { ?>none<?}?>" id="overview<?=$value['parent'].'_'.$idx?>EXT">
			<td id="section"><?echo str_repeat('&nbsp;&nbsp;',$extension_level);?>
			<? #### if subtree exists
			if(is_array(get_array_leafs($temp_tree, $value['id'])) ) {  ?>
			<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/arrow_closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" alt="expand" id="image<?=$value['id']?>" style="cursor:hand" onclick="ExpandDetail(<?=$value['id']?>)" onkeypress="ExpandDetail(<?=$value['id']?>)" align=absmiddle><a href="#" onclick="ExpandDetail(<?=$value['id']?>)"><?=$extension_name?></a>
			<? } 
			#### if no subtree, show inactive arrow and no link
			else { ?>
				<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/arrow_inactive.gif" WIDTH="16" HEIGHT="16" BORDER="0" align=absmiddle><?=$extension_name?>
			<?} # if subtree ?>
			</td>
			<?	#### permission cell 
			$level=1;
				foreach($selected_parents as $group) {
					# if role is selected in the first selectbox, get role permissions
					if($level==1 && $site->fdat['role_id']) {
						$perm = $source_permissions[$value['id']]['role'][$site->fdat['role_id']];
					}
					# else get group permissions
					else { 	$perm = $source_permissions[$value['id']]['group'][$group]; }		
					#echo printr($perm);
					print_permission(array(
						"role_id" => $site->fdat['role_id'],
						"group_id" => (!$site->fdat['role_id']?$group:''),
						"user_id" => '',
						"source_id" => $value['id'],
						"permissions" => 'C,R,U,P,D',
						"perm_type" => 'EXT',
						"perm" => $perm
					));
					$level++;							
				} 
				# if USER is selected in the last selectbox:
				if($site->fdat['user_id']) {
					$user_id = $site->fdat['user_id'];
					$perm = $source_permissions[$value['id']]['user'][$user_id]; 
					#echo printr($perm);
					print_permission(array(
						"group_id" => '',
						"user_id" => $user_id,
						"source_id" => $value['id'],
						"permissions" => 'C,R,U,P,D',
						"perm_type" => 'EXT',
						"perm" => $perm
					));			
				}
				else {
					echo '<td><div id="add_row_div"><INPUT TYPE="checkbox" NAME="tmp_" style="border: 1px solid #f4f4f4" disabled></div></td>';
				}
			?>
		</TR>
	<?
	}
	} # is array
	########## / loop over extensions

	# / 4. EXTENSIONS
	##################
	?>


	</TABLE>
<?
# / PERMISSIONS TABLE
###################
?>
								
								</TD>
								<!-- Permissions table -->
							</TR>
							</TABLE>		
						</div>
<?
# / CONTENT
#####################
?>

						<!-- //Scrollable area -->
					</TD>
				</TR>
				<!-- //Table data -->
			</TABLE>			
		</TD>
	</TR>
	</TABLE>
      

    </td>
  </tr>
  <!-- // Content area -->



</form>
<?############ / FORM #########?>

</table>
</body>
</html>


<?
#################################
# FUNCTION print_group_selectbox
/**
* print_group_selectbox
*
* prints selectbox with group name and with members list
*
* usage:
*	print_group_selectbox(array(
*		"group_id" => $top_group
*	));
*/
function print_group_selectbox(){
	global $site;
	global $read_allowed_groups;
	global $all_levels;	
	global $selected_parents;
	global $column_count;

	$args = func_get_arg(0);
#printr($read_allowed_groups);
#printr($selected_parents);
	$group_id = $args['group_id']; # group ID
	$level = $args['level']; # group ID
	###### PERMISSIONS: show group selectbox if READ is allowed OR is superuser
	if($group_id == '' || in_array($group_id,$read_allowed_groups) || $site->user->is_superuser) {

	###### get group itself
	$group = new Group(array(group_id => $group_id));

	###### get subgroups html
#printr($all_levels[$level]);
	if(is_array($all_levels[$level])){
	foreach($all_levels[$level] as $tmpgroup) {
		
		# check parent (don-t check parent for evreybody) AND permission to read
		if((in_array($tmpgroup['id'],$read_allowed_groups) || $site->user->is_superuser) && (!$tmpgroup['parent'] || in_array($tmpgroup['parent'],$selected_parents) )){

			$role_selected = ($level == 1 && $site->fdat['role_id']? true : false);

			$options_html .= '<option value="group_id:'.$tmpgroup['id'].'"'.($group_id==$tmpgroup['id'] && !$role_selected?' selected':'').">".$tmpgroup['name']."</option>";
		} # if is child of selected parent
	} # is array
	} # foreach group in this level

	########## members
	# get previous level (min is 1):
	$prev_level = $level>1 ? $level-1 : 1;
	###### get members list of previous group (group in preceeding selectbox)
	$prev_group_id = $selected_parents[$prev_level-1];
	if($prev_group_id) { 
		$prev_group = new Group(array(group_id => $prev_group_id));	
		$members = $prev_group->get_members();
	}

# print cell if found any subgroup OR any member
if($options_html || sizeof($members)>0) {

	# keep record of global column count
	if($column_count < $level){ $column_count = $level; }

?>
<TD>

<SELECT NAME="tmp_<?=$group_id?>" class="scms_flex_input" style="width:160px" onchange="javascript:select_acl(this.options[this.selectedIndex].value)">
	<? # print empty select (not for everybody)
	########### CHOOSE
	if($level!='1') { ?>
		<option value="">--- <?=$site->sys_sona(array(sona => "vali", tyyp=>"admin"))?> ---</option>
	<?}
	########### 1) ROLES: print roles only in the first selectbox (everybody)
	if($level=='1') { 
		$sqltmp = $site->db->prepare("SELECT * FROM roles ORDER BY name");
		$sthtmp = new SQL($sqltmp);
	?>
		<optgroup label="<?=$site->sys_sona(array(sona => "roles", tyyp=>"kasutaja"))?>">
		<? 
		while($role = $sthtmp->fetch() ){ ?>
		<option value="role_id:<?=$role['role_id']?>" <?=($site->fdat['role_id']==$role['role_id']?' selected':'')?>><?=$role['name']?></option>

<?		}
		echo '</optgroup>';
	}
	########### 2) GROUPS
	?>
	<optgroup label="<?=$site->sys_sona(array(sona => "groups", tyyp=>"kasutaja"))?>">
	<? echo $options_html; 
	echo '</optgroup>';

	########### 3) MEMBERS: print if previous selectbox group ID found and not everybody
	if($level!='1' && $prev_group_id) { 
?>

	<optgroup label="<?=$site->sys_sona(array(sona => "users", tyyp=>"admin"))?>">
<?
		foreach($members as $member){
	?>
		<option value="user_id:<?=$member['user_id']?>" <?=($site->fdat['user_id']==$member['user_id']?' selected':'')?>><?=$member['firstname']?> <?=$member['lastname']?></option>
		<? } # while
		echo '</optgroup>';
	} # /if previous selectbox group ID found
?>
</SELECT>
<?} # print cell if found something ?>
</TD>
<?
	} # show group selectbox if READ is allowed OR is superuser
}
# / FUNCTION print_group_selectbox
#################################

#################################
# FUNCTION print_header_row
/**
* print_header_row
*
* prints one header row  with titile and CRUPD
*
* print_header_row(array(
*	"permissions" => 'C,R,U,P,D',
*	"perm_type" => 'OBJ',
*	"title" => 'EE'
* ));
*/
function print_header_row(){
	global $site;
	global $column_count;

	$args = func_get_arg(0);
	
	$permissions = $args['permissions']; # "C,R,U,P,D"
	$perm_type = $args['perm_type']; # OBJ/ACL/..
	$title = $args['title']; # name for header

	# permissions mask
	if($permissions){ $crud = split(",",$permissions); }
	else { $crud = split(",","C,R,U,P,D,S"); }
	$crudnames = array(
		"C"=>"Create", 
		"R"=>"Read",
		"U"=>"Update",
		"P"=>"Publish",
		"D"=>"Delete",
		"S"=>"Apply permission to subtree",
	);


	# get gif:
	if($perm_type == 'OBJ') { $gif = 'mime/folder_open.png'; }
	elseif($perm_type == 'ACL') { $gif = 'users/group.png'; }
	elseif($perm_type == 'ADMIN') { $gif = 'mime/admin.png'; }
	else { $gif = 'mime/object.png'; }

?>

<TR>
	<td class="scms_groupheader"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/<?=$gif?>" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle> 
	<?=$title?>
	</td>
	<? for ($i=1;$i<=$column_count;$i++) {?>
	<td nowrap>
		<div id="pm_hd1">&nbsp;</div>
		<?foreach($crud as $char){ ?>
			<div id="pm_hd" title="<?=$crudnames[$char]?>"><?=$char?> </div>
		<?}?>
	</td>
	<? } ?>
</TR>
<?
}
# / FUNCTION print_header_row
#################################

#################################
# FUNCTION print_permission
/**
* print_permission
*
* print one permission row value  - CRUPD matrix value
*
*
*/
function print_permission(){
	global $site;
	
	$args = func_get_arg(0);
	
	$user_id = $args['user_id']; # user ID
	$group_id = $args['group_id']; # group ID
	$role_id = $args['role_id']; # role ID
	$source_id = $args['source_id']; # source ID
	$perm_type = $args['perm_type']; # OBJ/ACL/ADMIN/EXT
	$permissions = $args['permissions']; # "C,R,U,P,D"

	$perm = $args['perm']; # array

	# permissions mask
	if($permissions){ $crud = split(",",$permissions); }
	else { $crud = split(",","C,R,U,P,D,S"); }

	$found = sizeof($perm)>0 ? 1 : 0;

	###### prefix: "ACL_source_user_group_role_" eg "ACL_72_5_0_0"
	$prefix = $perm_type.'_'.$source_id.'_'.$user_id.'_'.$group_id."_".$role_id."_";

	# does user itself has U permission for this permission?
	$u_permission = get_user_permission(array(
		type => $perm_type,
		objekt_id => $perm_type=='OBJ'? $source_id : '',
		adminpage_id => $perm_type=='ADMIN'? $source_id : '',
		group_id => $perm_type=='ACL'? $source_id : '',
		extension_id => $perm_type=='EXT'? $source_id : '',
	));

?>
<TD>
<?######## CONTROL checkbox?>
<div id="add_row_div">
	<INPUT TYPE="checkbox" NAME="tmpcontrol" title="Add permissions" onclick="javascript:if(this.checked){document.getElementById('<?=$prefix?>').style.display='block';document.getElementById('<?=$prefix?>control').value='1';}else{document.getElementById('<?=$prefix?>').style.display='none';document.getElementById('<?=$prefix?>control').value='0';};" style="border: 1px solid #f4f4f4" <?=$found?'checked':''?> value="1" <?=$u_permission[U]?'':'disabled'?>>
</div>

<?######## hidden "control" bit: 1/0, if permission row exists in database or not ?>

<INPUT TYPE="hidden" NAME="<?=$prefix?>control" id="<?=$prefix?>control" value="<?=$found?'1':'0'?>">

<?######## CRUD checkboxes?>
<div id="<?=$prefix?>" name="<?=$prefix?>" style="display: <?=$found?'block':'none'?>">
<?foreach($crud as $char){ ?>
	<INPUT TYPE="checkbox" NAME="<?=$prefix.$char?>" id="<?=$prefix.$char?>" <?=($perm[$char]?' checked':'')?> value="1" <?=$u_permission[U]?'':'disabled'?>>
<?}?>
</div>
</TD>
<?
}
# / FUNCTION print_permission
#################################

#################################
# FUNCTION get_adminpages_arr
/**
* get_adminpages_arr
*
* get all allowed (by modul and by permission) visible adminpages;
* return array
*
* $peaosad = get_adminpages_arr();
*/
function get_adminpages_arr(){

	global $site;

	$alamlipikud = array();

	# 1. küsi kõik admin-lehed
	$sql = $site->db->prepare("SELECT admin_osa.id
	FROM admin_osa
	WHERE admin_osa.parent_id!=1 ");
	$sql .= " ORDER BY sorteering DESC";
	$sth = new SQL($sql);
#print $sql;
	while ($adminpage = $sth->fetch()) {
		# 3. vaata kas admin-leht on userile lubatud
		$perm = get_user_permission(array(
			type => 'ADMIN',
			adminpage_id => $adminpage['id'],
			site => $site
		));
		# kas useril on selle admin-lehe kohta Read õigus?
		if(!$perm['R']){
			# if forbidden, go to next adminpage
			continue;
		}
		# 4. kui kõik lubatud, siis pane lõpp-massiivi
		array_push($alamlipikud,$adminpage['id']);
	}

	# see on nüüd kõigi vaatamiseks lubatud adminlehtede massiiv:
	$alamlipikud_joined = join("','",$alamlipikud);

	############## Alamlipiku id jargi otsime pealipikud
	 $sql = $site->db->prepare("SELECT A.id AS peaid, A.nimetus AS peanimetus, A.eng_nimetus AS eng_peanimetus, A.sorteering FROM admin_osa
		LEFT JOIN admin_osa as A ON A.id = admin_osa.parent_id
		WHERE admin_osa.id IN('".$alamlipikud_joined."')
		GROUP BY A.id, A.nimetus, A.eng_nimetus, A.sorteering
		ORDER BY A.sorteering DESC"
	);
	$sth = new SQL($sql);
	$pea_total = $sth->rows;
	$site->debug->msg($sth->debug->get_msgs());

	$i=0;
    while ($lipik = $sth->fetch()) {
		$peaosad[id][$i] = $lipik[peaid];
		$peaosad[nimi][$i] = $lipik[eng_peanimetus];
	  $i++;
	}
	$ret[] = &$peaosad; 
	$ret[] = &$alamlipikud_joined; 
	return $ret;
}
# / FUNCTION get_adminpages_arr
#################################

#################################
# FUNCTION save_all_permissions
/**
* save_all_permissions
*
* saves all permission checkboxes values on the page (all types).
* 
*
* usage:
*	save_all_permissions();
*/
function save_all_permissions(){
	global $site;
	global $read_allowed_groups;

#	$args = func_get_arg(0);

	# checkbox names are in format:
	# ACL_source_user_group_role_char" eg "ACL_72_5_0_0_C"

	$updates1 = array();
	$updates2 = array();

	########## loop over fdat values
	foreach($site->fdat as $fdat_field=>$fdat_value) { 
		list($perm_type,$source_id,$user_id,$group_id,$role_id,$char) = split("_",$fdat_field);
		
		# check data sanity:
		if($perm_type && $source_id && ($user_id || $group_id || $role_id) && $char) {

			##### gather data to array
			if($user_id) {
				$updates[$perm_type][$source_id]['u'.$user_id][$char] = $fdat_value;
				$updates[$perm_type][$source_id]['u'.$user_id]['user_id'] = $user_id;
				$updates[$perm_type][$source_id]['u'.$user_id]['source_id'] = $source_id;
				$updates[$perm_type][$source_id]['u'.$user_id]['type'] = $perm_type;
			}
			elseif($role_id) {
				$updates[$perm_type][$source_id]['r'.$role_id][$char] = $fdat_value;
				$updates[$perm_type][$source_id]['r'.$role_id]['role_id'] = $role_id;
				$updates[$perm_type][$source_id]['r'.$role_id]['source_id'] = $source_id;
				$updates[$perm_type][$source_id]['r'.$role_id]['type'] = $perm_type;
			}
			elseif($group_id) {
				$updates[$perm_type][$source_id]['g'.$group_id][$char] = $fdat_value;
				$updates[$perm_type][$source_id]['g'.$group_id]['group_id'] = $group_id;
				$updates[$perm_type][$source_id]['g'.$group_id]['source_id'] = $source_id;
				$updates[$perm_type][$source_id]['g'.$group_id]['type'] = $perm_type;
			}
#			if($perm_type=='ADMIN') {
#					echo "<br>".$fdat_field." = ".$fdat_value;
#					echo " OK: ".$char;
#			}
			
		} # if sane data
	}
#printr($updates['ADMIN']['20']);

	######## loop over types
	foreach($updates as $type => $source_arr) {

		foreach($source_arr as $source_id=>$data_arr){

		foreach($data_arr as $tmp=>$data){

#		print "<br>".$type. " ".$data['source_id'];
#printr($data);
			# if sane data
			if($data['user_id'] || $data['group_id'] || $data['role_id']){
			########### 1) OBJ & ACL & ADMIN & EXT: can modify with Update permissions
			if($data['type']=='OBJ' || $data['type']=='ACL' || $data['type']=='ADMIN' || $data['type']=='EXT') {

				# does user has U permission?
				$permission = get_user_permission(array(
					type => $data['type'],
					objekt_id => $data['type']=='OBJ'? $data['source_id'] : '',
					adminpage_id => $data['type']=='ADMIN'? $data['source_id'] : '',
					group_id => $data['type']=='ACL'? $data['source_id'] : '',
					extension_id => $data['type']=='EXT'? $data['source_id'] : '',
				));
				#printr($permission);
				if($permission[U]) {
	#printr($data);

#					echo " YES";
					############ 1. DELETE OLD PERMISSION for source object
					$sql = $site->db->prepare("DELETE FROM permissions WHERE type=? AND source_id=? ", 	
						$data['type'], 
						$data['source_id']
					);
					if($data['user_id']) { 
						$sql .= $site->db->prepare(" AND user_id=?",$data['user_id']); }
					elseif($data['group_id']) { 
						$sql .= $site->db->prepare(" AND group_id=?",$data['group_id']); }
					else { 
						$sql .= $site->db->prepare(" AND role_id=?",$data['role_id']); }
					$sth = new SQL($sql);
					$site->debug->msg($sth->debug->get_msgs());	
					#print "<br>".$sql;

					############ 2. INSERT NEW PERMISSIONS for object

					# insert only if control bit is 1 (otherwise entire permssion row is deleted)
					if($data['control']){
					$sql = $site->db->prepare("INSERT INTO permissions (type,source_id,role_id,group_id,user_id,C,R,U,P,D) VALUES (?,?,?,?,?,?,?,?,?,?)", 	
						$data['type'], 
						$data['source_id'], 
						$data['role_id'],
						(!$data['user_id'] && !$data['role_id']?$data['group_id']:0),
						($data['user_id']?$data['user_id']:0),
						($data[C]==1?1:0),
						($data[R]==1?1:0),
						($data[U]==1?1:0),
						($data[P]==1?1:0),
						($data[D]==1?1:0)
					);
					$sth = new SQL($sql);
					$site->debug->msg($sth->debug->get_msgs());	
#					print "<br>".$sql;
					} # if control=1

				}
				else {
#					echo " NO";
				}

			}
			########### 2) EXTENSIONS : can modify only superuser
			else {
				if($site->user->is_superuser) {



				} # if superuser
			} # perm type

			} # if sane data
		} # loop
		} # loop over data
	} 	
	######## / loop over types

	########## / loop over fdat values
}
# / FUNCTION save_all_permissions
#################################

#################################
# FUNCTION get_source_permissions
/**
* get_source_permissions
*
* returns ALL PERMISSIONS array FOR given type (ACL/ADMIN/..) and for currently selected
* users/groups/roles (in selectboxes)
* Executes SQL QUERY function.
*
* usage (use always pointer to array, because return result is huuge):
*	$source_permissions = &get_source_permissions(array("perm_type" => 'ACL'));
*/
function get_source_permissions(){
	global $site;
	global $selected_parents;

	$args = func_get_arg(0);

	$perm_type = $args['perm_type'];

	if($perm_type){ # if sane parameters

	########### 1. get ALL PERMISSIONS (Bug #2640)
	$level=1;
	$source_permissions = array();

	########### 1.1 for given ROLE
	if($site->fdat['role_id']) {
		###### get all permissions for given role
		$permissions = get_all_permissions(array(
			"type" => $perm_type,
			"role_id" => $site->fdat['role_id'],
			"with_inheriting" => 0
		));
		###### RE-STRUCTURE data to "source_permissions[$source_id]['user'][$user_id]"
		foreach ($permissions as $perm) {
			if($perm['user_id']) { 
				$source_permissions[$perm['source_id']]['user'][$perm['user_id']] = $perm;
			}
			elseif($perm['group_id']) {
				$source_permissions[$perm['source_id']]['group'][$perm['group_id']] = $perm;
			}
			elseif($perm['role_id']) {
				$source_permissions[$perm['source_id']]['role'][$perm['role_id']] = $perm;
			}
		}
	} # if role is set
	########### 1.2 for given GROUPS
	foreach($selected_parents as $group) {

		###### get all permissions for given group
		$permissions = get_all_permissions(array(
			"type" => $perm_type,
			"group_id" => $group,
			"with_inheriting" => 0
		));
		#printr($permissions);
#		echo "<hr>";
		###### RE-STRUCTURE data to "source_permissions[$source_id]['user'][$user_id]"
		foreach ($permissions as $perm) {
			if($perm['user_id']) { 
				$source_permissions[$perm['source_id']]['user'][$perm['user_id']] = $perm;
			}
			elseif($perm['group_id']) {
				$source_permissions[$perm['source_id']]['group'][$perm['group_id']] = $perm;
			}
			elseif($perm['role_id']) {
				$source_permissions[$perm['source_id']]['role'][$perm['role_id']] = $perm;
			}
		}
#		print("<br>".$perm_type."(".$perm['source_id'].") r:".$site->fdat['role_id']." g:".(!$site->fdat['role_id']?$group:'')." u:".(!$site->fdat['role_id'] && $group==$site->fdat['group_id']?$site->fdat['user_id']:'')." (count=".sizeof($permissions).")<br>");
		$level++;							
	}	
	########### 1.3 for given USER
	if($site->fdat['user_id']) {
		###### get all permissions for given user
		$permissions = get_all_permissions(array(
			"type" => $perm_type,
			"user_id" => $site->fdat['user_id'],
			"with_inheriting" => 0
		));
		###### RE-STRUCTURE data to "source_permissions[$source_id]['user'][$user_id]"
		foreach ($permissions as $perm) {
			if($perm['user_id']) { 
				$source_permissions[$perm['source_id']]['user'][$perm['user_id']] = $perm;
			}
			elseif($perm['group_id']) {
				$source_permissions[$perm['source_id']]['group'][$perm['group_id']] = $perm;
			}
			elseif($perm['role_id']) {
				$source_permissions[$perm['source_id']]['role'][$perm['role_id']] = $perm;
			}
		}
	} # if user is set

	} # if perm_type provided
	return $source_permissions;
}
# / FUNCTION get_source_permissions
#################################

#################################
# FUNCTION print_obj_row
/**
* print_obj_row
*
* prints one object row
*
*
* print_obj_row(array(
*	"parent" => $parent,
*	"obj" => $obj,
* 	"leafs_found" => is_array(get_array_leafs($temp_tree, $value['id'])) ? 1 : 0
* ));
*/
function print_obj_row(){
	global $site;
	global $idx;
	global $selected_parents;
	global $source_permissions;

	$args = func_get_arg(0);
	
	$parent = intval($args['parent']); # 
	$obj = $args['obj']; # 
	$obj['id'] = intval($obj['id']);
	$leafs_found = $args['leafs_found'];

	$parent +=10000; # just to make it unique 
	$tmp_id = $obj['id'] + 10000;  # just to make it unique 
	$level = $obj['level'];

		?>
		<TR  style="display: <?if($level>1) { ?>none<?}?>" id="overview<?=$parent.'_'.$idx?>OBJ">
			<td id="section"><?echo str_repeat('&nbsp;&nbsp;',$level);?>
			<? #### if subtree exists
			if($leafs_found) {  ?>
			<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/arrow_closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" alt="expand" id="image<?=$tmp_id?>" style="cursor:hand" onclick="ExpandDetail(<?=$tmp_id?>)" onkeypress="ExpandDetail(<?=$tmp_id?>)" align=absmiddle><a href="#" onclick="ExpandDetail(<?=$tmp_id?>)"><?=$obj['name']?></a>
			<? } 
			#### if no subtree, show inactive arrow and no link
			else { ?>
				<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/arrow_inactive.gif" WIDTH="16" HEIGHT="16" BORDER="0" align=absmiddle><?=$obj['name']?>
			<?} # if subtree ?>
			</td>
	<?	######### permission cell 
	$level=1;
	foreach($selected_parents as $group) {
		# if role is selected in the first selectbox, get role permissions
		if($level==1 && $site->fdat['role_id']) {
			$perm = $source_permissions[$obj['id']]['role'][$site->fdat['role_id']];
		}
		# else get group permissions
		else { 	$perm = $source_permissions[$obj['id']]['group'][$group]; }		
		#echo printr($perm);
		print_permission(array(
			"role_id" => $site->fdat['role_id'],
			"group_id" => (!$site->fdat['role_id']?$group:''),
			"user_id" => '',
			"source_id" => $obj['id'],
			"permissions" => 'C,R,U,P,D',
			"perm_type" => 'OBJ',
			"perm" => $perm
		));
		$level++;							
	} 
	# if USER is selected in the last selectbox:
	if($site->fdat['user_id']) {
		$user_id = $site->fdat['user_id'];
		$perm = $source_permissions[$obj['id']]['user'][$user_id]; 
		#echo printr($perm);
		print_permission(array(
			"group_id" => '',
			"user_id" => $user_id,
			"source_id" => $obj['id'],
			"permissions" => 'C,R,U,P,D',
			"perm_type" => 'OBJ',
			"perm" => $perm
		));			
	}
	else {
		echo '<td><div id="add_row_div"><INPUT TYPE="checkbox" NAME="tmp_" style="border: 1px solid #f4f4f4" disabled></div></td>';
	}
	?>
	</TR>
<? 
}
# / FUNCTION print_obj_row
#################################
