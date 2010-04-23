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
 * Popup page for editing group data
 * 
 * Page has 2 tabs:
 * GROUP TAB: group general data & attributes
 * todo in future: PERMISSIONS TAB: shows all permissions for current group
 * 
 * @param string $tab selected tab name (group/members/permissions)
 * @param int $group_id current group ID
 * @param string $op action name
 * @param string $op2 step 2 action name
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

$op = $site->fdat['op'];
$op2 = $site->fdat['op2'];

if($op2 && !$site->fdat['refresh'] && $site->fdat['tab'] == 'permissions') {
	$site->fdat['group_id'] = $site->fdat['id'];
}

#################
# GET GROUP INFO

$group = new Group(array(
	group_id => $site->fdat['group_id'],
));

#echo printr($site->fdat);
####################################
# PERMISSIONS CHECK
# get group permissions for current user

# load permissions
if ($site->user->user_id) { 
	$site->user->aclpermissions = $site->user->load_aclpermissions();
}
$permission = get_user_permission(array(
	type => 'ACL',
	group_id => $site->fdat['group_id']
 ));
#echo printr($permission);
$site->debug->msg("Grupi ".$site->fdat['group_id']." õigused käes");

###########################
# ACCESS allowed/denied
# decide if accessing this page is allowed or not
$access = 0;
#echo printr($site->fdat);

	# NEW GROUP: if parent group has CREATE permission => allow	
	if ($op=='new' || $op=='copy' || $op=='group'){
		if($permission['C']){ $access = 1; }
	}
	# EDIT GROUP: if current group has READ & UPDATE => allow
	elseif( $op=='edit') {
		if($permission['R'] && $permission['U']) { $access = 1; }
	}
	# DELETE GROUP: if current group has DELETE => allow
	elseif($op == 'delete') {
		if($permission['D']){ $access = 1; }
	}

if ($site->fdat[refresh]) {
	$access = 1;
}
	####################
	# access denied
	if (!$access) {
		####### write log
		if($site->fdat['op']=='new' || $site->fdat['op']=='copy') {
			$text = sprintf("Access denied: attempt to create group under restricted group ID = %s", $site->fdat['group_id']);
			new Log(array(
				'action' => 'create',
				'component' => 'User groups',
				'type' => 'WARNING',
				'message' => $text,
			));
		} elseif($site->fdat['op']=='delete') {
			new Log(array(
				'action' => 'delete',
				'component' => 'User groups',
				'type' => 'WARNING',
				'message' => $text,
			));
			$text = sprintf("Access denied: attempt to delete group '%s' (ID = %s)" , $group->name, $group->id);
		} else {
			new Log(array(
				'action' => 'update',
				'component' => 'User groups',
				'type' => 'WARNING',
				'message' => $text,
			));
			$text = sprintf("Access denied: attempt to edit group '%s' (ID = %s)" , $group->name, $group->id);
		}
		####### print error html
		print_error_html(array(
			"message" => $site->sys_sona(array(sona => "access denied", tyyp=>"editor"))
		));
		####### print debug
		if($site->user) { $site->user->debug->print_msg(); }
		if($site->guest) { 	$site->guest->debug->print_msg(); }
		$site->debug->print_msg();
		########### EXIT
		exit;
	}
# / ACCESS allowed/denied
###########################

# / PERMISSIONS CHECK
####################################


######### GO ON WITH REAL WORK


#################
# STEP2: SAVE DATA 
if($op2 && !$site->fdat['refresh']) {
	$form_error = array();

	##############
	# SAVE GROUP TAB
	if($site->fdat['tab'] == 'group') {

		################## GET profile 
		$profile_def = $site->get_profile(array("id"=>$site->fdat['profile_id'])); 

		################## CHECK & CHANGE profile values (required, date formats, arrays, etc)
		$sql_field_values = check_profile_values(array(
			"profile_def" => &$profile_def,
			"skip_fields" => "group_id,name,parent_group_id"
		));
		#printr($sql_field_values);


		############ NEW OR COPY
		if($op == 'new' || $op == 'copy') {
			$parent_id = $site->fdat['group_id'];

	  		$sql = $site->db->prepare("INSERT INTO groups (profile_id, name, parent_group_id, auth_type ".(count($update_fields)?','.join(",",array_keys($sql_field_values)):'').") VALUES (?,?,?,? ".(count($update_fields)?",'".join("','",array_values($sql_field_values))."'":"")." )", 
				($site->fdat['profile_id'] ? $site->fdat['profile_id'] : 0),
				(trim($site->fdat['name']) == ''? 'undefined' : $site->fdat['name']),
				$site->fdat['parent_group_id'],
				$site->fdat['auth_type']
			);
			#print $sql;
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			$site->fdat['group_id'] = $sth->insert_id;
			
			########################
			# INSERT PERMISSIONS
			# lisame uuele grupile täpselt samad õigused nagu on tema parent grupil:
			# leia kõik parenti õigused userite/gruppide kohta:
			$sql = $site->db->prepare("SELECT * FROM permissions WHERE type=? AND source_id=?",
				'ACL',
				 $parent_id
			);
			$sth = new SQL ($sql);
			# tsükkel üle parenti õiguste
			while($perm = $sth->fetch()){
				# lisa õigus uuele objektile
					$sql2 = $site->db->prepare("INSERT INTO permissions (type,source_id,group_id,user_id,C,R,U,P,D) VALUES (?,?,?,?,?,?,?,?,?)", 	
						'ACL', 
						$site->fdat['group_id'], 
						$perm['group_id'],
						$perm['user_id'],
						$perm['C'],
						$perm['R'],
						$perm['U'],
						$perm['P'],
						$perm['D']
					);
					$sth2 = new SQL($sql2);
			} # tsükkel üle parenti õiguste
			# / INSERT PERMISSIONS
			########################


			####### op => edit
			$site->fdat['op'] = 'edit';
			$op = 'edit';

			####### write log
			new Log(array(
				'action' => 'create',
				'component' => 'User groups',
				'message' => "New group '".$sql_field_values['name']."' inserted",
			));
		}
		############ EDIT 
		elseif($op == 'edit') {
			## update main data 
	  		$sql = $site->db->prepare("UPDATE groups SET profile_id=?, name=?, parent_group_id=?, auth_type=? WHERE group_id=?",
				$site->fdat['profile_id'], 
				(trim($site->fdat['name']) == ''? 'undefined' : $site->fdat['name']),
				$site->fdat['parent_group_id'],				
				$site->fdat['auth_type'],
				$site->fdat['group_id']
			);
			#print $sql;
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	

			#### update profile data 
			foreach ($sql_field_values as $field=>$value) {
#				$update_fields[] = $site->db->prepare($field."=?", $value);
				$update_fields[] = $field."=".$value;
			}
			if(count($update_fields) > 0){
				$sql = "UPDATE groups SET ".join(",",$update_fields)." WHERE group_id=".$site->fdat['group_id'];
				#print $sql;
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());	
			} # if found profile data

			####### write log
			new Log(array(
				'action' => 'update',
				'component' => 'User groups',
				'message' => "Group '".$sql_field_values['name']."' updated",
			));
		} # op
	}
	# / SAVE GROUP TAB
	##############

	##############
	# SAVE MEMBERS TAB
	elseif($site->fdat['tab'] == 'members') {

		if($op2=='remove_member' && $site->fdat['user_id']) {
			print "remove member: ".$site->fdat['user_id'];
		}

	}
	# / SAVE MEMBERS TAB
	##############

	##############
	# SAVE PERMISSIONS TAB
	elseif($site->fdat['tab'] == 'permissions') {
		$site->fdat['group_id'] = '';

		####### save permissions to database
		include_once($class_path."permissions.inc.php");
		save_permissions(array(
			"type" => 'ACL'	
		));

		$site->fdat['group_id'] = $site->fdat['id'];

	}
	# / SAVE PERMISSIONS TAB
	##############


	############ DELETE
	# -delete is allowed only the when no user is in the group
	# -Everybody group can't be deleted
	if($op == 'delete') {
		# do double-checks if allowed to delete

		# 1. if subgroups exist, don't allow to delete
		$group->subgroups_count = $group->get_subgroups_count();
		# 2. if members exist, don't allow to delete
		$group->members_count = $group->get_members_count();

		if(!$group->subgroups_count && !$group->members_count) {	
			# delete if allowed and is not everybody (is_predefined)
			$sql = $site->db->prepare("DELETE FROM groups WHERE group_id=? AND is_predefined<>?",$site->fdat['group_id'],1);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			# delete permisssions
			$sql = $site->db->prepare("DELETE FROM permissions WHERE type=? AND source_id=?", 
				'ACL',
				$site->fdat['group_id']
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			####### write log
			new Log(array(
				'action' => 'delete',
				'component' => 'User groups',
				'message' => "Group '".$group->all['name']."' deleted",
			));
		} # if allowed delete

	} # op

	##############
	# refresh opener and close popup
	# don't close popup if it was save and errors occured during saving	
	if(($op2=='saveclose' && sizeof($form_error)==0) || $op2=='deleteconfirmed') {
		?>
		<SCRIPT language="javascript">
		<!--
		var oldurl = window.opener.location.toString();
		// remove old parameters from url:
		oldurl = oldurl.replace(/\?(.*)/g, "");
		if('<?=$op2?>'=='deleteconfirmed') {
			newurl = oldurl + '?group_id=<?=($group->parent_id?$group->parent_id:1)?>';
			window.opener.location=newurl;
		}
		else {
			window.opener.location=window.opener.location;
		}
		window.close();
		// -->
		</SCRIPT>
		<?
		exit;
	}

	#################
	# RELOAD GROUP INFO after saving

	$group = new Group(array(
		group_id => $site->fdat[group_id],
	));

}
# / STEP2: SAVE DATA
#################

##################
# REFRESH page withot saving
if ($site->fdat[refresh]) {
	foreach($site->fdat as $name=> $value)  {
		$group->all[$name] = $value;
	}
	#echo printr($group->all);
}


##################
# POPUP HTML
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/datepicker.css">
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/jquery.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/datepicker.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'];?>/common.js.php"></script>
</head>

<body class="popup_body" onLoad="this.focus()">
<?
######################
# DELETE CONFIRMATION WINDOW
if($op == 'delete') {
?>
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<input type=hidden name=group_id value="<?=$site->fdat['group_id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?
	# check if allowed to delete
	# 1. if subgroups exist, don't allow to delete
	$group->subgroups_count = $group->get_subgroups_count();
	# 2. if members exist, don't allow to delete
	$group->members_count = $group->get_members_count();

	if($group->subgroups_count > 0) {
		# show error message
		echo "<font color=red>".$site->sys_sona(array(sona => "Permission denied", tyyp=>"editor"))."</font><br><br>";
		echo $site->sys_sona(array(sona => "Children count", tyyp=>"admin")).": <b>".$group->subgroups_count."</b>";
	}
	elseif($group->members_count > 0) {
		# show error message
		echo "<font color=red>".$site->sys_sona(array(sona => "Permission denied", tyyp=>"editor"))."</font><br><br>";
		echo $site->sys_sona(array(sona => "Children count", tyyp=>"admin")).": <b>".$group->members_count."</b>";
	}
	# show confirmation
	else {
		echo $site->sys_sona(array(sona => "kustuta", tyyp=>"editor"))." \"<b>".$group->name."</b>\"? ";
		echo $site->sys_sona(array(sona => "are you sure?", tyyp=>"admin"));
		$allow_delete = 1;
	}
?>
	</td>
  </tr>
  <tr align="right"> 
    <td valign="top" colspan=2 > 
			<?if($allow_delete){?>
            <input type="button" value="<?=$site->sys_sona(array(sona => "kustuta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='deleteconfirmed';frmEdit.submit();">
			<?}?>
			<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>
</form>
<?
}	
# / DELETE CONFIRMATION WINDOW
######################


######################
# 2. tab PERMISSIONS

# Note: permissions is new feature/page, starting from ver 4

elseif($site->fdat['tab'] == 'permissions') { 

	$site->fdat['id'] = $site->fdat['group_id'];
?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<?
	include_once($class_path."permissions.inc.php");

	## action "Copy permissions to subtree"
	if($site->fdat['copypermissions']) {
		copy_permissions(array(
			"type" => 'ACL',
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
		print_tabs();

		edit_permissions(array(
			"type" => 'ACL',
			"permissions" => 'C,R,U,D,S'
		));
	}
}
# / 2. tab PERMISSIONS
######################


######################
# 1. tab EDIT
else {
?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<?
	########### tabs
	print_tabs();

?>

<?
######################
# 1. CONTENT: tab GROUP
# op = new/copy/edit/print

if($site->fdat['tab'] == 'group') {

	######################
	# FIND parent ID
	# OP = new/copy/edit: new group will be created as CHILD of selected group
	if($site->fdat['op']=='new'){
		$parent_group_id =  $group->group_id;
	}
	else {
		$parent_group_id =  $group->parent_group_id;
	}
	######################
	# UNSET parent group info for new group
	if($site->fdat['op'] == 'new') {
		$group = '';
	}
?>
  <tr> 
    <td valign="top" width="100%" class="scms_dialog_area" height="100%"> 
      <table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<input type=hidden name=tab value="<?=$site->fdat['tab']?>">
	<input type=hidden name=group_id value="<?=$site->fdat['group_id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
	<input type=hidden id=refresh name=refresh value="">
			<?
			###################
			# General info
			?>		  
			<?########### page title #########?>
        <tr> 
          <td colspan="3"> 
            <div style="position:relative"> 
              <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "Main info", tyyp=>"kasutaja"))?></div>
            </div>
          </td>
        </tr>
			<?########### name #########?>
            <tr>
              <td width="20%" nowrap><?=$site->sys_sona(array(sona => "nimetus", tyyp=>"editor"))?>:</td>
              <td width="100%"><input name="name" type="text" class="scms_flex_input" value="<?=($op=='copy'?'Copy of ':'').($site->fdat['refresh']?$site->fdat['name']:$group->all['name'])?>"></td>
            </tr>
			<?########### parent group #########?>
			 <? if(!$group->all['is_predefined']) { # dont show selectbox for everybody group ?>
            <tr>
              <td width="20%" nowrap><?=$site->sys_sona(array(sona => "Parent group", tyyp=>"kasutaja"))?>:</td>
              <td width="100%">
			<?
			$sql = "SELECT group_id AS id, parent_group_id AS parent, name FROM groups ORDER BY name";
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			while ($data = $sth->fetch()){
				$temp_tree[] = $data;		
			}
			?>
			<select name="parent_group_id" class="scms_flex_input">
		<?	foreach (get_array_tree($temp_tree) as $key=>$value) {
			$name = str_repeat("&nbsp;&nbsp;", $value['level']).$value['name'];

			## dont show group itself (prevent endless loop)

			if($group->group_id != $value['id']){

				# chop string to 60 chars:
				if(strlen($name) > 68) {
					$name = substr($name,0,65).'...';
				}
				print "<option value='".$value['id']."' ".($value['id']==$parent_group_id || ($site->fdat['refresh'] && $value['id']==$site->fdat['parent_group_id'])? '  selected':'').">".$name."</option>";
			}
		} ?>				
			</select></td>
            </tr>
			<? } # !is_predefined?>
			<?########### description #########?>
            <tr>
              <td width="20%" nowrap><?=$site->sys_sona(array(sona => "Kirjeldus", tyyp=>"editor"))?>:</td>
              <td width="100%"><input name="description" type="text" class="scms_flex_input" value="<?=($site->fdat['refresh']?$site->fdat['description']:$group->all['description'])?>"></td>
            </tr>

			<input type="hidden" name="auth_type" value="CMS">
		<?	
		################# type (profile_id) ########

		$profile_id = ($site->fdat['refresh'] ? $site->fdat['profile_id'] : $group->all['profile_id']);

  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		'groups');
		$sth = new SQL($sql);
		?>
			<tr>
              <td width="20%" nowrap><?=$site->sys_sona(array(sona => "Type", tyyp=>"admin"))?>:</td>
              <td width="100%"><select name="profile_id" class="scms_flex_input" onchange="javascript:document.getElementById('refresh').value='1';document.forms['frmEdit'].submit();">
		<option value=""></option>
		<? while ($data = $sth->fetch()){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			print "<option value='".$data['id']."' ".($data['id']==$profile_id? '  selected':'').">".$data['name']."</option>";
		} ?>				
				</select></td>
            </tr>
	</table>
      <br>
      <br>
		<?
	###################
	# Additional info: attributes list

	# get profile
	$profile_def = $site->get_profile(array("id"=>$profile_id)); 
	$profile_fields = unserialize($profile_def['data']);	# profile_fields is now array of ALL fields, indexes are fieldnames
		?>	
	<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">
        <tr> 
          <td colspan="2"> 
            <div style="position:relative"> 
              <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "Additional info", tyyp=>"kasutaja"))?></div>
            </div>
          </td>
        </tr>
	<?###### profile fields row ?>
		<tr>
          <td valign=top colspan="2"> 
			<!-- Scrollable area -->
			<div id=listing class="scms_scroll_div" style="height: 180">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="scms_table">
				<?
				###################
				# print profile fields rows
				print_profile_fields(array(
					'profile_fields' => $profile_fields,
					'field_values' => $group->all,
					'fields_width' => '300px',
				));
				?>
			</table>
			</div>
			<!-- //Scrollable area -->
          </td>
        </tr>	
	<?###### / profile fields row ?>
		</table>

    </td>
  </tr>
	  <?
		###################
		# buttons
		?>
	<tr> 
    <td align="right" valign="top" class="scms_dialog_area_bottom"> 
            <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='save';frmEdit.submit();">
            <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='saveclose';frmEdit.submit();">
			<input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
	</form>

<?
}
# / 1. CONTENT: tab GROUP
######################
?>

</table>

<?
}
# 1. tab EDIT
######################
?>

<?	$site->debug->print_msg(); ?>
</body>

</html>


<?
######################
# FUNCTION PRINT_TABS()

function print_tabs() {
	#$args = @func_get_arg(0);
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
		$tab_arr['group'] = $site->sys_sona(array(sona => "group", tyyp=>"kasutaja"));
		$tab_arr['permissions'] = $site->sys_sona(array(sona => "permissions", tyyp=>"editor"));
		# default tab is first one:
		$site->fdat['tab'] = $site->fdat['tab']? $site->fdat['tab'] : 'group';

		foreach ($tab_arr as $tab=>$tab_title) {
		?>
        <td class="scms_<?=$site->fdat['tab']==$tab?'':'in'?>active_tab" nowrap>
		<?########## tab title #######?>
		<? 
			# if new object: disable tab
			if ($site->fdat['op']=='new' || $site->fdat['op']=='copy' ) {
				$tab_title = "<a href='#'>".$tab_title."</a>";	
			}
			else {
				$tab_title = "<a href=".$site->self."?tab=".$tab."&group_id=".$site->fdat['group_id']."&op=".$site->fdat['op'].">".$tab_title."</a>";
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
