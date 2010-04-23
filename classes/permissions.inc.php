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


#################
# FUNCTION EDIT_PERMISSIONS
/**
 * show object permission rows
 *
 * Shows all objected permissions: user/group name + CRUPD matrix + subtree checkbox
 * Allows to add new user/group as new permission row and delete rows
 * NB! Used some global variables (doesn't have to be this way later... only for development convienence now)
 * 
 * @param string type - permission type (OBJ/ADMIN/ACL/..)
 *
 * Call:
 *		edit_permissions(array(
 *			"type" => 'OBJ'	
 *		));
 */
function edit_permissions ($args) {
	global $site;
	global $objekt;
	global $class_path;
	global $keel;

	$db_permissions = array();
	$existing_users = array();
	$existing_groups = array();
	$selected_users = array();
	$selected_groups = array();
	$crud = array();

	# if objekt is not created (probably error situation), try to create it again
	if(!$objekt->objekt_id){
		$objekt = new Objekt(array(
			objekt_id => $site->fdat['id']
		));
	}

	$everybody_group_id = get_topparent_group(array("site" => $site));

	# default permission set
	if($args['permissions']){ $crud = split(",",$args['permissions']); }
	else { $crud = split(",","C,R,U,P,D,S"); }
	$crudnames = array(
		"C"=>"Create", 
		"R"=>"Read",
		"U"=>"Update",
		"P"=>"Publish",
		"D"=>"Delete",
		"S"=>"",
	);

	if($args['type']) {


##################
# HTML
?>
<SCRIPT LANGUAGE="JavaScript"><!--

function sanity_check(obj,acl,type,id){
//	alert(obj.checked+type+id);
	// rule 1: !R => !C & !U & !P & !D
	if(type=='R' && !obj.checked) {
		if(document.getElementById(acl+"_C_"+id)) { document.getElementById(acl+"_C_"+id).checked=false; }
		if(document.getElementById(acl+"_U_"+id)) { document.getElementById(acl+"_U_"+id).checked=false; }
		if(document.getElementById(acl+"_P_"+id)){ document.getElementById(acl+"_P_"+id).checked=false; }
		if(document.getElementById(acl+"_D_"+id)) { document.getElementById(acl+"_D_"+id).checked=false; }
	}
	// rule 2: C || U || P || D => R
	if(type=='C' && obj.checked || 
		type=='U' && obj.checked || 
		type=='P' && obj.checked || 
		type=='D' && obj.checked) {
		document.getElementById(acl+"_R_"+id).checked=true;
	}
}
function get_copypermissions_url(acl,id){
	var crud = '';
	if(document.getElementById(acl+"_C_"+id)) { 
		if(document.getElementById(acl+"_C_"+id).checked) { crud = crud + '1'; } else { crud = crud + '0'; }
	}
	if(document.getElementById(acl+"_R_"+id)) { 
		if(document.getElementById(acl+"_R_"+id).checked) { crud = crud + '1'; } else { crud = crud + '0'; }
	}
	if(document.getElementById(acl+"_U_"+id)) { 
		if(document.getElementById(acl+"_U_"+id).checked) { crud = crud + '1'; } else { crud = crud + '0'; }
	}
	if(document.getElementById(acl+"_P_"+id)) { 
		if(document.getElementById(acl+"_P_"+id).checked) { crud = crud + '1'; } else { crud = crud + '0'; }
	}
	if(document.getElementById(acl+"_D_"+id)) { 
		if(document.getElementById(acl+"_D_"+id).checked) { crud = crud + '1'; } else { crud = crud + '0'; }
	}
	return crud;
}
-->
</script>
<?
	######## gather all fdat values into url string
	foreach($site->fdat as $fdat_field=>$fdat_value) { 
		if($fdat_field != 'id'){
			$url_parameters .= '&'.$fdat_field."=".$fdat_value;
#not used?			$hidden_parameters .= '<input type=hidden name="'.$fdat_field.'" value="'.$fdat_value.'">';
		} 
	} 

	######################
	# OBJECT PERMISSIONS

	$sql = $site->db->prepare("SELECT permissions.*, roles.name AS role_name, groups.name AS group_name, CONCAT(users.firstname,' ',users.lastname) AS user_name, groups.is_predefined AS predefined_group 
	FROM permissions 
		LEFT JOIN roles ON permissions.role_id=roles.role_id 
		LEFT JOIN groups ON permissions.group_id=groups.group_id
		LEFT JOIN users ON permissions.user_id=users.user_id 
	WHERE permissions.type=? AND permissions.source_id=?
	ORDER BY permissions.group_id DESC, permissions.user_id DESC, groups.name, users.firstname,users.lastname
		",
		$args['type'],
		$site->fdat['id']
	);
	$sth = new SQL($sql);
	$saved_permissions_found = $sth->rows ? true : false;

	$site->debug->msg($sth->debug->get_msgs());	
#print $sql;
	while ($permtmp = $sth->fetch()){
		$permtmp['is_role'] = $permtmp['role_id'] ? 1 : 0;
		$permtmp['is_group'] = $permtmp['group_id'] ? 1 : 0;
		$permtmp['name'] = $permtmp['role_id'] ? $permtmp['role_name'] : ($permtmp['group_id'] ? $permtmp['group_name'] : $permtmp['user_name']);


		$db_permissions[] = $permtmp;
		if($permtmp['user_id']) {
			$existing_users[] = $permtmp['user_id'];
		}
		if($permtmp['group_id']) {
			$existing_groups[] = $permtmp['group_id'];
		}
	}
	######################

	######################
	# HOME section permissions (sys_alias=home) will be default permissions through all website
	# get site permissions for everybody:
	$sql = $site->db->prepare("SELECT permissions.*, groups.name
	FROM permissions 
		LEFT JOIN groups ON permissions.group_id=groups.group_id
	WHERE permissions.type=? AND permissions.source_id=?
		",
		'OBJ',
		$site->alias("rub_home_id")
	);

	$sth = new SQL($sql);
	$home_permissions_found = $sth->rows ? true : false;
	$site->debug->msg($sth->debug->get_msgs());	
#print $sql;
	while ($permtmp = $sth->fetch()){
		$permtmp['is_group'] = $permtmp['group_id'] ? 1 : 0;

		$home_permissions[] = $permtmp;
		if($permtmp['group_id']) {
			$home_existing_groups[] = $permtmp['group_id'];
		}
	}
	# if for some reason home section doesn't have permissions
	# then use default mask: only Read permission (CRUPD=01000)
	if(!is_array($home_permissions)) {
		$home_permissions[] = array(
			id => '',
			type => 'OBJ',
			source_id => $site->alias("rub_home_id"),
			group_id => 1,
			user_id => '',
			C => 0,
			R => 1,
			U => 0,
			P => 0,
			D => 0,
			is_role => 0,
			is_group => 1,
			name => 'Everybody'
		);
	}
#printr($home_permissions);

	######################
	# TEMPORALLY SELECTED USERS & GROUPS PERMISSIONS
	# they are in the list but not in database yet
#echo printr($site->fdat['selected_groups']);
	if(trim($site->fdat['selected_users'])) {
		$selected_users = split(",",trim($site->fdat['selected_users']));
		$selected_users = array_unique($selected_users);

		### remove user from array if asked in url
		if( $site->fdat['remove_user_id'] ) {
			$key = array_search($site->fdat['remove_user_id'], $selected_users);  
			unset($selected_users[$key]);
		}
	}
	if(trim($site->fdat['selected_groups'])) {
		$selected_groups = split(",",trim($site->fdat['selected_groups']));
		$selected_groups = array_unique($selected_groups);
		### remove group from array if asked in url
		if( $site->fdat['remove_group_id'] ) {
			$key = array_search($site->fdat['remove_group_id'], $selected_groups);  
			unset($selected_groups[$key]);
		}
	}
#printr($selected_groups);
#printr($existing_groups);

	# add selected groups to permission list
	if(sizeof($selected_groups) > 0) {
		foreach($selected_groups as $group_id) {
			# if group not found in existing groups (in database) then add it
			if( !in_array($group_id,$existing_groups) ) {
				# get group info: to get group name
				$group = new Group(array(
					group_id => $group_id,
				));
				$permtmp = array();
				$permtmp['type'] = $args['type'];
				$permtmp['source_id'] = $site->fdat['id'];
				$permtmp['group_id'] = $group_id;
				$permtmp['is_group'] = 1;
				$permtmp['name'] = $group->name;
				# insert permission to the permissions array:
				$db_permissions[] = $permtmp;
			} # if group not found in existing groups
		} # foreach group id
	} # if selected_groups
	if(sizeof($selected_users) > 0) {
		foreach($selected_users as $user_id) {
			# if user not found in existing users (in database) then add it
			if( !in_array($user_id,$existing_users) ) {

				# get user info: to get user full name
				$user = new User(array(
					user_id => $user_id,
				));
				$permtmp = array();
				$permtmp['type'] = $args['type'];
				$permtmp['source_id'] = $site->fdat['id'];
				$permtmp['user_id'] = $user_id;
				$permtmp['is_group'] = 0;
				$permtmp['name'] = $user->all['firstname'].' '.$user->all['lastname'];

				# insert permission to the permissions array:
				$db_permissions[] = $permtmp;
			} # if user not found in existing users
		} # foreach user id
	} # if selected_users
	# / TEMPORALLY SELECTED USERS & GROUPS PERMISSIONS
	######################
#printr($db_permissions);
?>
  <tr> 
    <td valign="top" width="100%" class="scms_dialog_area" height="100%"> 
      <div class="scms_scrolltable_border"> 
        <div style="width:100%;" class="scms_scrolltable_header">
		   <table width="100%" cellpadding="0" cellspacing="0">
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<input type=hidden name=tab value="<?=$site->fdat['tab']?>">
	<input type=hidden name=id value="<?=$site->fdat['id']?>">
	<input type=hidden name=keel value="<?=$site->fdat['keel']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name="callback" value="<?=$site->fdat['callback']?>">
	<input type=hidden name=op2 value="">
	<input type=hidden name=selected_users value="<?=join(',',$selected_users)?>">
	<input type=hidden name=selected_groups value="<?=join(',',$selected_groups)?>">
	
	<tr> 
              <td><?=$site->sys_sona(array(sona => "role", tyyp=>"kasutaja"))?> / <?=$site->sys_sona(array(sona => "group", tyyp=>"kasutaja"))?> / <?=$site->sys_sona(array(sona => "user", tyyp=>"kasutaja"))?></td>
			  <td align="right"><a href="javascript:void(openpopup('select_group.php','selectgroup','980','600'))"><?=$site->sys_sona(array(sona => "lisa", tyyp=>"editor"))?></a></td>
            </tr>
            <tr> 
              <td colspan="2" align="right" class="scms_scrolltable_header2" style="padding-right:30px"> 
                <table  border="0" cellspacing="0" cellpadding="3" class="scms_scrolltable_header2" >
				<?############## C R U P D S ###########?>
                  <tr> 
				  <? foreach($crud as $char) {?>
                    <td width="24" align="center"><?if($char!='S'){?><a href="#" title="<?=$crudnames[$char]?>"><?=$char?></a><?}?></td>
				  <?}?>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </div>
        <div id="scrolltableDiv" class="scms_scrolltable" style="height:290px"> 
          <table width="100%"  border="0" cellspacing="0" cellpadding="3">
<?	
####################
# 1. OBJECT PERMISSIONS saved into database (if found any)
#printr($db_permissions);
if(sizeof($db_permissions)>0) {

	foreach ($db_permissions as $key=>$perm){ 

		######### create remove link
		$remove_href = $site->self."?tab=".$site->fdat['tab']."&id=".$site->fdat['id'].$url_parameters;
		$remove_href .= sizeof($selected_users) > 0 ? "&selected_users=".join(',',$selected_users) : '';
		$remove_href .= sizeof($selected_groups) > 0 ? "&selected_groups=".join(',',$selected_groups) : '';
		$remove_href .=  "&remove_".($perm['is_group']?'group_id='.$perm['group_id']:'user_id='.$perm['user_id']);

		######### create copy link (permission data will be added later)
		$copy_href = $site->self."?tab=".$site->fdat['tab']."&id=".$site->fdat['id'].$url_parameters;

		######### dont print permission row if it's the removed
		if($perm['is_group'] && $perm['group_id'] == $site->fdat['remove_group_id'] || 
			!$perm['is_group'] && $perm['user_id'] == $site->fdat['remove_user_id']) 		{

			# goto next row
			continue;
		}
		########### print permission row
		else {

			print_permission_row(array(
				"perm" => $perm,
				"remove_href" => $remove_href,
				"copy_href" => $copy_href,
				"crud" => $crud
			));

		} # if not in remove list => print permission row
	} # foreach
}# if object permissions found	
# / 1. OBJECT PERMISSIONS saved into database (if found any)
####################

####################
# 2. HOME permissions row: when NO SAVED PERMISSIONS found in database
# - get permission values from default site values
# - dont allow to delete everybody row
#sizeof($db_permissions)==0
if(!$saved_permissions_found && ($args['type']=='OBJ' || $args['type']=='ACL') ){

	foreach ($home_permissions as $key=>$perm){ 
		######### create copy link (permission data will be added later)
		$copy_href = $site->self."?tab=".$site->fdat['tab']."&id=".$site->fdat['id'].$url_parameters;

		########### print permission row
		print_permission_row(array(
			"perm" => $perm,
			"remove_href" => $remove_href,
			"copy_href" => $copy_href,
			"crud" => $crud
		));
	} 
}
# / 2. HOME permissions 
####################
?>
			</table>
        </div></div>
    </td>
  </tr>
  <tr> 
    <td align="right" valign="top" class="scms_dialog_area_bottom"> 
     <input type="button" value="<?=$site->sys_sona(array(sona => "apply", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='save';this.form.submit();">
    <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='saveclose';this.form.submit();">
	<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>

</form>
<?
	}  # if permission type provided
}
# / FUNCTION EDIT_PERMISSIONS
#################

#################
# FUNCTION PRINT_PERMISSION_ROW
function print_permission_row($args){
	global $site;
	global $objekt;

	$perm = $args['perm'];
	$remove_href = $args['remove_href'];
	$copy_href = $args['copy_href'];
	$crud = $args['crud'];
#printr($perm);
	$id = $perm['is_role'] ? $perm['role_id'] : ($perm['is_group'] ? $perm['group_id'] : $perm['user_id']);

	# check if we have public folder objekt - it has some exceptional behaviour
	$is_public_folder = false;

	if($objekt->objekt_id && $objekt->all['tyyp_id'] == 22){
		$objekt->load_sisu(); # load content table to get fullpath value
		if(strpos($objekt->all['relative_path'], '/public') === 0)
		{
			$is_public_folder = true;
		}
	}

		# mouseover message for group/user name, displays full path of group membership
		if($perm['is_role']) {
		}
		elseif($perm['is_group']) {
			$grouptree = get_grouptree(array("group_id" => $perm['group_id']));
		}
		else {
			$tmpuser = new User(array(
				user_id => $perm['user_id'],
			));
			$grouptree = get_grouptree(array("group_id" => $tmpuser->group_id));		
		}
		$group_msg = array();
		if(sizeof($grouptree)>0){
			foreach($grouptree as $key=>$group){
				$group_msg[] = $group['name'];
			}
		}
		if($perm['is_role']) { 	$href_title = $site->sys_sona(array(sona => "role", tyyp=>"kasutaja")); }
		else { $href_title = join(" > ",$group_msg); }

		# acl - shows if we have user or group or role
		$acl = $perm['is_role'] ? 'role': ($perm['is_group']?'group': 'user');
	?>

		<?######### name ########?>
			<tr> 
              <td nowrap  width="16"><img alt="" src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/users/<?=$perm['is_role'] ? 'contacts': ($perm['is_group']?'group':'user')?>.png" width="16" height="16"></td>
			  <td nowrap><a href="#" title="<?=$href_title?>"><?=$perm['name']?></a>

		<?######### hidden ID (eg permission_user_11, permission_group_1) ########?>
			 <input type=hidden name=permission_<?=$acl?>_<?=$id?> value="<?=$id?>">
			 </td>
		  <? foreach($crud as $char) {?>
			<?if($char == 'S') { ########## subtree copy button 

			$copy_href .= '&perm_user_id='.$perm['user_id'];
			$copy_href .= '&perm_group_id='.$perm['group_id'];
			$copy_href .= '&perm_role_id='.$perm['role_id'];
			?>
			 <td align="center" width="24"> 
				<ul class="scms_button_row"><li><a href="javascript:void(openpopup('<?=$copy_href?>&copypermissions='+get_copypermissions_url('<?=$acl?>','<?=$id?>'),'copypermissions','300','108'))"  class="button_subtree" title="<?=$site->sys_sona(array(sona => "Copy permissions to subtree", tyyp=>"editor"))?>"></a></li></ul>

<!--old checkbox
				<input id="<?=$acl?>_<?=$char?>_<?=$id?>" name="<?=$acl?>_<?=$char?>_<?=$id?>" type="checkbox" value="1">
old checkbox-->
              </td>
			<?} else { ############ C/R/U/P/D ?>
			 <td align="center" width="24">
				
				<?
				### exception for public folder: Read is already ON and disabled (Bug #2216)
				if($char == 'R' && $is_public_folder) {  ?>
					<input type="hidden"	name="<?=$acl?>_<?=$char?>_<?=$id?>" value="1">
					<input name="tmp" type="checkbox" value="1" checked disabled> 
				<?}
				### usual case
				else{?>
	                <input id="<?=$acl?>_<?=$char?>_<?=$id?>" name="<?=$acl?>_<?=$char?>_<?=$id?>" type="checkbox" value="1" <?=($perm[$char]?' checked':'')?> onclick="sanity_check(this,'<?=$acl?>','<?=$char?>','<?=$id?>');">
				<?}?>
              </td>
			<?}?>
		  <? } ?>
			<?	######## delete button: OBJ ONLY:dont allow to delete everybody row ?>
              <td align="center" width="24"><ul class="scms_button_row"><li><?if(!($args['type']=='OBJ' && $perm['predefined_group'])){?><a href="<?=$remove_href?>" class="button_delete" title="<?=$site->sys_sona(array(sona => "Kustuta", tyyp=>"editor"))?>"></a><?} else { ?><img src="<?=$site->CONF['wwwroot'].$site->CONF['img_path']?>/px.gif"  width="11" height="12" border="0"><?}?></li></ul></td>

<!--old              <td align="center" width="24"><?if(!($args['type']=='OBJ' && $perm['predefined_group'])){?><a href="<?=$remove_href?>"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete.png" alt="Remove" width="16" height="16" border="0"></a><?} else { ?><img src="<?=$site->CONF['wwwroot'].$site->CONF['img_path']?>/px.gif"  width="11" height="12" border="0"><?}?></td>
-->
            </tr>
<?
}
# / FUNCTION PRINT_PERMISSION_ROW
#################



#################
# FUNCTION SAVE_PERMISSIONS
/**
* save permissions to database
*
* Deletes all old values and inserts new ones.
* NB! Used some global variables (doesn't have to be this way later... only for development convienence now)
* 
* @package CMS
* 
* @param string type - permission type (OBJ/ADMIN/ACL/..)
*
* Call:
*		save_permissions(array(
*			"type" => 'OBJ'	
*		));
*/
function save_permissions($args) {
	global $site;
	global $objekt;
	global $class_path;
	global $keel;

	# if objekt is not created (probably error situation), try to create it again
	if(!$objekt->objekt_id){
		$objekt = new Objekt(array(
			objekt_id => $site->fdat['id']
		));
	}

	if($args['type']) {

		############ 1. DELETE ALL OLD PERMISSIONS for object
		$sql = $site->db->prepare("DELETE FROM permissions WHERE type=? AND source_id=?", 	
			$args['type'], 
			$site->fdat['id']
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());	
#		print "<br>".$sql;

		############
		# loop over permission rows
		foreach ($site->fdat as $field=>$value) {
			if(substr($field,0,strlen('permission')) == 'permission') {
				# get type (role/group/user) and ID (role_id or group_id or user_id) from fieldname
				list($name,$type,$id) = split("_",$field);

#print "<br>".$field." => ".$acl. ", ".$type. ", ". $id. " => C:".$site->fdat[$type.'_C_'.$id]." R:".$site->fdat[$type.'_R_'.$id];
#echo " U:".$site->fdat[$type.'_U_'.$id]; echo " P:".$site->fdat[$type.'_P_'.$id]; echo " D:".$site->fdat[$type.'_D_'.$id];
#echo " subtree:".$site->fdat[$type.'_S_'.$id];

				############ 2. INSERT NEW PERMISSIONS for object

				$sql = $site->db->prepare("INSERT INTO permissions (type,source_id,role_id,group_id,user_id,C,R,U,P,D) VALUES (?,?,?,?,?,?,?,?,?,?)", 	
					$args['type'], 
					$site->fdat['id'], 
					($type=='role'?$id:0),
					($type=='group'?$id:0),
					($type=='user'?$id:0),
					($site->fdat[$type.'_C_'.$id]?$site->fdat[$type.'_C_'.$id]:0),
					($site->fdat[$type.'_R_'.$id]?$site->fdat[$type.'_R_'.$id]:0),
					($site->fdat[$type.'_U_'.$id]?$site->fdat[$type.'_U_'.$id]:0),
					($site->fdat[$type.'_P_'.$id]?$site->fdat[$type.'_P_'.$id]:0),
					($site->fdat[$type.'_D_'.$id]?$site->fdat[$type.'_D_'.$id]:0)
				);
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());	
				#print "<br>".$sql;

			} # if permission field
		} 
		# / loop over permission rows
		############

		############
		# write log

		# type= OBJ
		if($args['type'] == 'OBJ') {
			new Log(array(
				'action' => 'update',
				'component' => 'ACL',
				'objekt_id' => $objekt->objekt_id,
				'message' => "Object '".$objekt->all['pealkiri']."' (ID=".$site->fdat['id'].") permissions updated",
			));
		}
		# type= ADMIN
		elseif($args['type'] == 'ADMIN') {
			#USE FUNCTIONN! POOELLI
			####### get adminpage name
			$sql = $site->db->prepare("SELECT eng_nimetus FROM admin_osa WHERE id=?", 	
				$site->fdat['id']
			);
			$sth = new SQL($sql);
			$pagename = $sth->fetchsingle();
			$site->debug->msg($sth->debug->get_msgs());	
			
			####### write log
			new Log(array(
				'action' => 'update',
				'component' => 'ACL',
				'message' => "Adminpage '".$pagename."' permissions updated",
			));
		} # if permission type

		# / write log
		############
	} # if permission type provided
}
# / FUNCTION SAVE_PERMISSIONS
#################


#################
# FUNCTION COPY_PERMISSIONS
/**
* Copy one permission row to the subtree
* 
* 
* @package CMS
* 
* @param string type - permission type (OBJ/ACL)
* @param integer source_id - source ID (obejct ID or group ID)
* @param string crud - CRUPD mask to be copied eg "01000"
* @param integer user_id - User ID
* @param integer group_id - Group ID
* @param integer role_id - Role ID
*
* Call:
*		copy_permissions(array(
*			"type" => 'OBJ',
*			"source_id" => $site->fdat['id'],
*			"crud" => $site->fdat['crud']
*			"user_id" => $site->fdat['perm_user_id'],
*			"group_id" => $site->fdat['perm_group_id'],
*			"role_id" => $site->fdat['perm_role_id'],
*		));
*/
function copy_permissions ($args) {
	global $site, $class_path;

	$source_id = $args['source_id'];
	$crud = $args['crud'];
	$user_id = $args['user_id'];
	$group_id = $args['group_id'];
	$role_id = $args['role_id'];

	## how many objects/groups were actually updated 
	$updated_count = 0;

	# make string "01000" to array
	$crud_arr['C'] = substr($crud,0,1);
	$crud_arr['R'] = substr($crud,1,1);
	$crud_arr['U'] = substr($crud,2,1);
	$crud_arr['P'] = substr($crud,3,1);
	$crud_arr['D'] = substr($crud,4,1);
	?>
	<tr>
	<td valign="top" class="scms_confirm_alert_cell" height="100%">
	<?
	#echo "ID:". $source_id.' / CRUD: '.$crud;

	if($args['type']) {

		############ 1) GET SUBTREE HERE (query 1 time)

		# get object subtree: children sections (ignore objects languages, to get folders also. there is no risk because we get always one certain branch)
		if($args['type']=='OBJ'){

			include_once($class_path."rubloetelu.class.php"); # used in subtree proc
			$rubs = new RubLoetelu(array(
				"keel" => $keel,
				"required_perm" => "U",
				"object_type_ids" => "1,22", # get sections, folders (Bug #1996)
				"ignore_lang" => 1 # ignore objects languages
			));
			#printr($rubs->get_loetelu());
			#$rubs->debug->print_msg();
			
			# get branch: is array of all section children with update permission:
			$branch = $rubs->get_branch_byID(array(id => $site->fdat['id']));
			#printr($branch);
		}
		# get group subtree: children subgroups
		elseif($args['type']=='ACL'){

	  		$sql = "SELECT group_id AS id, parent_group_id AS parent, name FROM groups ORDER BY name";
			$sth = new SQL($sql);
			while ($data = $sth->fetch()){
				$temp_tree[] = $data;		
			}
			############# generate tree 
			require_once($class_path.'menu.class.php');
			$menu = new Menu(array(
				width=> "100%",
				tree => $temp_tree,
				datatype => "group"
			));
			$menu->get_full_subtree(array("parent_id" => $site->fdat['id']));
			# $menu->full_subtree is variable from group tree and is all ID-s of group children
			#echo printr($menu->full_subtree);
			foreach($menu->full_subtree as $subgroup_id) {
				$branch[$subgroup_id] = ""; # name is not important
			};

		}

		###################
		# 2. INSERT PERMISSIONS

		# loop over subtree
		# branch is array of all children
		foreach($branch as $child_id=>$child_name) {
			# omit source object itself
			if($child_id == $source_id) {
				continue;
			}
			########### CREATE CHILD (to get permissions and title)

			if($args['type'] == 'OBJ') {
				## create child object
				$child = new Objekt(array(
					objekt_id => $child_id
				));
				$child->title = $child->all['pealkiri'];
			}
			elseif($args['type'] == 'ACL') {
				## create child group
				$child = new Group(array(
					group_id => $child_id,
				));
				$child->permission = get_user_permission(array(
					type => 'ACL',
					group_id => $child_id
				 ));
				$child->title = $child->name;
			}
			#printr($child->permission);

			########### CHECK UPDATE PERMISSION - does user has U permission for this object? (Bug #2203)
			if(!$child->permission['U']) {
				continue; # user doesn't have U permission => don't change child
			}

			# insert permission also to child:
			#print "<br>insert permission also to child: ". $child_id. " => ".$child_name;
			##### 1) DELETE OLD permission
			$sql = $site->db->prepare("DELETE FROM permissions WHERE type=? AND source_id=? AND ",$args['type'], $child_id);
			if($role_id){
				$sql .= $site->db->prepare(" role_id=? ", $role_id);
			} elseif($group_id){
				$sql .= $site->db->prepare(" group_id=? ", $group_id);
			} elseif($user_id){
				$sql .= $site->db->prepare(" user_id=? ", $user_id);
			}
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			#print "<br>".$sql;

			##### 2) INSERT permission
			$sql = $site->db->prepare("INSERT INTO permissions (type,source_id,role_id,group_id,user_id,C,R,U,P,D) VALUES (?,?,?,?,?,?,?,?,?,?)", 	
				$args['type'], 
				$child_id, 
				($role_id?$role_id:0),
				($group_id?$group_id:0),
				($user_id?$user_id:0),
				$crud_arr['C'],
				$crud_arr['R'],
				$crud_arr['U'],
				$crud_arr['P'],
				$crud_arr['D']
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			#print "<br>".$sql;
			if($sth->rows) {
				$updated_count++;
			}

			############
			# 3. WRITE LOG

			# type= OBJ
			if($args['type'] == 'OBJ') {
				new Log(array(
					'action' => 'update',
					'component' => 'ACL',
					'objekt_id' => $child_id,
					'message' => "Object '".$child->title."' (ID=".$child_id.") permissions updated inside subtree",
				));
			}
			# type= ACL
			elseif($args['type'] == 'ACL') {
				new Log(array(
					'action' => 'update',
					'component' => 'ACL',
					'objekt_id' => $child_id,
					'message' => "Object '".$child->title."' (ID=".$child_id.") permissions updated inside subtree",
				));
			}
			# / write log
			############
		}
		# / loop over subtree
		###################

	} # if permission type provided
	################## 

	######### MESSAGE
	echo $site->sys_sona(array(sona => "Permissions copied to subtree", tyyp=>"editor"));
	echo ': '.$updated_count.'';
	?>
    </td>
  </tr>
	<?#################### BUTTONS ###########?>
	  <tr> 
	  <td align="right" valign="top" class="scms_dialog_area_bottom"> 
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();">
    </td>
  </tr>
<?
}
# / FUNCTION COPY_PERMISSIONS
#################
