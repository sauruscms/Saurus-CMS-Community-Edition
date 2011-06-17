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
 * Popup page for editing user data
 * 
 * Page has 4 tabs:
 * USER TAB: user general data & attributes
 * GROUPS TAB: shows all groups where user belongs
 * not done yet: PERMISSIONS TAB: shows all permissions for current user
 * ACCOUNT TAB: shows user login info
 * 
 * @param string $tab selected tab name (user/permissions/account)
 * @param int $user_id current user ID
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

############ browser check: input styles & widths are sometimes different
if (preg_match ('/.*(Gecko).*/i',$_SERVER["HTTP_USER_AGENT"], $regs) ) {
	$is_moz = 1;
}

############
# find parent group, by defult "ID=1 - Everybody"
$site->topparent_group = get_topparent_group(array("site" => $site));

$site->fdat['group_id'] = $site->fdat['group_id'] ? $site->fdat['group_id'] : $site->topparent_group;

#################
# GET user INFO

$user = new User(array(
	user_id => $site->fdat['user_id'],
));

####################################
# PERMISSIONS CHECK
# get group permissions for current user

# load permissions
if ($site->user->user_id) { 
	$site->user->aclpermissions = $site->user->load_aclpermissions();
}
$permission = get_user_permission(array(
	type => 'ACL',
	group_id => ($user->group_id? $user->group_id : $site->fdat['group_id'])
 ));
#echo printr($permission);
$site->debug->msg("Useri groupi ".($user->group_id? $user->group_id : $site->fdat['group_id'])." õigused käes");

###########################
# ACCESS allowed/denied
# decide if accessing this page is allowed or not
$access = 0;
#echo printr($site->fdat);

	# NEW USER: if parent group has CREATE permission => allow	
	if ($op=='new' || $op=='copy' || $op=='group'){
		if($permission['C']){ $access = 1; }
	}
	# EDIT USER: if parent group has READ & UPDATE => allow
	elseif( $op=='edit' || $op=='lock') {
		if($permission['R'] && $permission['U']) { $access = 1; }
	}
	# DELETE USER: if parent group has DELETE => allow
	elseif($op == 'delete') {
		if($permission['D']){ $access = 1; }
	}
################## POOLELI:
if ($site->fdat['refresh']) {
	$access = 1;
}
	####################
	# access denied
	if (!$access) {
		####### write log
		if($site->fdat['op']=='new' || $site->fdat['op']=='copy') {
			$text = sprintf("Access denied: attempt to create user under restricted group ID = %s" , $site->fdat['group_id']);
			new Log(array(
				'action' => 'create',
				'component' => 'Users',
				'type' => 'WARNING',
				'message' => $text,
			));
		} elseif($site->fdat['op']=='delete') {
			$text = sprintf("Access denied: attempt to delete user '%s' (ID = %s)" , $user->name, $user->id);
			new Log(array(
				'action' => 'delete',
				'component' => 'Users',
				'type' => 'WARNING',
				'message' => $text,
			));
		} else {
			$text = sprintf("Access denied: attempt to edit user '%s' (ID = %s)" , $user->name, $user->id); 
			new Log(array(
				'action' => 'update',
				'component' => 'Users',
				'type' => 'WARNING',
				'message' => $text,
			));
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
if(!$site->fdat['refresh'] && ($op2 == 'save' || $op2 == 'saveclose' || $op2 == 'deleteconfirmed' || $op2 == 'lockconfirmed')) {

	verify_form_token();
	
	$form_error = $site->fdat['error'];

	# get all table fields:
	$user_fields = array();
	$user_fields = split(",", $site->db->get_fields(array(tabel => 'users')) );
	# remove ID field from array:
	$id_key = array_search('user_id', $user_fields); 
	unset($user_fields[$id_key]); 


	##############
	# SAVE USER TAB
	if($site->fdat['tab'] == 'user') {

		################## GET profile 
		$profile_def = $site->get_profile(array("id"=>$site->fdat['profile_id'])); 

		################## CHECK & CHANGE profile values (required, date formats, arrays, etc)
		$sql_field_values = check_profile_values(array(
			"profile_def" => &$profile_def,
			"skip_fields" => "user_id, group_id, email, profile_id, firstname, lastname, is_predefined, username, password, pass_expires"
		));
		#printr($sql_field_values);
	
		$form_error = $site->fdat['error'];
		
		
		# if name is not defined then set it to 'undefined':
		if(trim($site->fdat['firstname']) == '') { $site->fdat['firstname'] = 'undefined';}
		#echo printr($sql_field_values);

		############ E-MAIL: CHECK FOR CORRECT FORMAT
		# if e-mail is set
		if ($site->fdat['email'] != '') {
			if (!preg_match("/^[\w\-\&\.\d]+\@[\w\-\&\.\d]+$/", $site->fdat['email'])) {
				# don't save incorrect data: 
				unset($site->fdat['email']); 
				# save error message for use in form later:
				$form_error['email'] = $site->sys_sona(array(sona => "wrong email format", tyyp=>"kasutaja"));
			}
		} # if e-mail set

		############ E-MAIL: CHECK FOR DUPLICATES
		if($site->fdat['email'] != '') {
			$sql = $site->db->prepare("SELECT user_id FROM users WHERE email=? AND user_id<>?",
				$site->fdat['email'],
				$site->fdat['user_id']
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			if ($exists = $sth->fetchsingle()) {
				# don't save incorrect data: 
				unset($site->fdat['email']); 
				# save error message for use in form later:
				$form_error['email'] = $site->sys_sona(array(sona => "Email already exists", tyyp=>"kasutaja"));
			}
		}
	

		############ DATABASE: NEW OR COPY
		if($user->all['is_readonly']!=1 && ($op == 'new' || $op == 'copy')) {
			############ 1) create always record in user table:

	  		$sql = $site->db->prepare("INSERT INTO users (group_id, email, profile_id, firstname, lastname,created_date, pass_expires) VALUES (?,?,?,?,?,now(), date_add(now(), interval 5 year))",
				$site->fdat['group_id'],
				$site->fdat['email'],
				$site->fdat['profile_id'],
				$site->fdat['firstname'],
				$site->fdat['lastname']
			);
			#print $sql;
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			$site->fdat['user_id'] = $sth->insert_id;


			foreach ($sql_field_values as $field=>$value) {

				# if field was found in form values
				# then add field into sql
				if(isset($site->fdat[$field])) {
						$update_fields[] = $site->db->prepare(" ".$field."=?",$value);
				}
			}
			############ 2) if profile fields found then save them also:
			if(count($update_fields)>0) {
		  		$sql = "UPDATE users SET ".join(",",$update_fields)." WHERE user_id='".$site->fdat['user_id']."'";
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());	
			}

			
			####### op => edit
			$site->fdat['op'] = 'edit';
			$op = 'edit';

			####### write log
			new Log(array(
				'action' => 'create',
				'component' => 'Users',
				'message' => "New user '".$site->fdat['firstname'].' '.$site->fdat['lastname']."' inserted",
			));
		}
		############ DATABASE: EDIT 
		elseif($op == 'edit') {
			$update_fields = array();
			foreach ($sql_field_values as $field=>$value) {

				# if field was found in form values
				# then add field into sql
				if(isset($site->fdat[$field])) {

					if ($field=='username' && $value==''){
						if (!$sql_field_values['password']){
							$update_fields[] = "password=''";
						}
						$update_fields[] = $field."=NULL";
					} else {
						$update_fields[] = $site->db->prepare(" ".$field."=?",$value);
					}
				} # found form field
			}
			############ 1) update always record in user table:
	  		$sql = $site->db->prepare("UPDATE users SET group_id=?, email=?, profile_id=?, firstname=?, lastname=? WHERE user_id=?",
				$site->fdat['group_id'],
				$site->fdat['email'],
				$site->fdat['profile_id'],
				$site->fdat['firstname'],
				$site->fdat['lastname'],
				$site->fdat['user_id']
			);
			#print $sql;
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	

			############ 2) if profile fields found then save them also:
			if(count($update_fields)>0) {
				#printr($update_fields);
		  		$sql = "UPDATE users SET ".join(",",$update_fields)." WHERE user_id='".$site->fdat['user_id']."'";
				#print $sql;
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());	
			}

			####### write log
			new Log(array(
				'action' => 'update',
				'component' => 'Users',
				'message' => "User '".$site->fdat['firstname'].' '.$site->fdat['lastname']."' updated",
			));
		} # op
	}
	# / SAVE user TAB
	##############

	##############
	# SAVE PERMISSIONS TAB
	elseif($site->fdat['tab'] == 'permissions') {

	}
	# / SAVE PERMISSIONS TAB
	##############


	##############
	# SAVE ACCOUNT TAB
	elseif($site->fdat['tab'] == 'account') {
		# array for gathering input errors: if errors exist then incorrect data will not be saved
		$form_error = array();

		# remove group ID field from array:
		$group_id_key = array_search('group_id', $user_fields); 
		unset($user_fields[$group_id_key]); 

		############ GET FORM DATA and put it into array

		$sql_field_values = array();
		# loop over table fields 
		foreach ($user_fields as $key=>$field) {
			# if field was found in form values (+exception for radiofields: add always)
			# then add field into sql
			#print "<br>".$field." = ".$site->fdat[$field];			
			if(isset($site->fdat[$field]) || substr($field,0,3)=='is_') {
				$sql_field_values[$field] = $site->fdat[$field]; #$site->db->quote(
			} # if field was found in form values
		} # loop over table fields 
		#printr($sql_field_values);

		############ USERNAME: CHECK FOR DUPLICATES
		if($sql_field_values['username'] != '') {
			$sql = $site->db->prepare("SELECT user_id FROM users WHERE username=? AND user_id<>?",
				$sql_field_values['username'],
				$site->fdat['user_id']
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			if ($exists = $sth->fetchsingle()) {
				# don't save incorrect data: 
				unset($sql_field_values['username']); 
				# save error message for use in form later:
				$form_error['username'] = $site->sys_sona(array(sona => "user exists", tyyp=>"kasutaja"));
			}
		}

		############ PASSWORD: CHECK FOR CONFIRM MATCH & ENCRYPT
		# if password is set
		if ($sql_field_values['password'] != '********' && $sql_field_values['password'] != '') {
			if ($site->fdat['password_confirmation'] != $sql_field_values['password']) {
				# don't save incorrect data: 
				unset($sql_field_values['password']); 
				# save error message for use in form later:
				$form_error['password'] = $site->sys_sona(array(sona => "wrong confirmation", tyyp=>"kasutaja"));
			}
			# if OK then encrypt password
			else {
				$sql_field_values['password'] = crypt($sql_field_values['password'], Chr(rand(65,91)).Chr(rand(65,91)));
			} # if confirm ok
		} # if password set
		# else if password is not set then don't save and overwrite it
		else {
			unset($sql_field_values['password']); 		
		}
		################### FORMAT DATE FIELDS
		if($sql_field_values['pass_expires']){
			$sql_field_values['pass_expires'] = $site->db->ee_MySQL($sql_field_values['pass_expires']);
		}

		################### UPDATE 
			unset($sql_field_values['is_readonly']); 		
			foreach ($sql_field_values as $field=>$value) {
				if ($field=='username' && $value==''){
					if (!$sql_field_values['password']){
						$update_fields[] = "password=''";
					}
					$update_fields[] = $field."=NULL";
				} 
				else {
					$update_fields[] = $field."='".$value."'";
				}
			}

			$sql = "UPDATE users SET ".join(", ",$update_fields)." WHERE user_id='".$site->fdat['user_id']."'";
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	

		###############
		# user roles

		# delete old roles
		$sql = $site->db->prepare("DELETE FROM user_roles WHERE user_id = ?", $site->fdat['user_id']);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		# insert new roles
		if(is_array($site->fdat['roles'])){
		foreach($site->fdat['roles'] as $role_id){
			$sql = $site->db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?,?)",
				$site->fdat['user_id'], $role_id);
			$sth = new SQL($sql);
			#print $sql;
			$site->debug->msg($sth->debug->get_msgs());
		}
		} # is array
		# / user roles
		###############

	}
	# / SAVE ACCOUNT TAB
	##############

	##############
	# SAVE MAILINGLIST TAB
	elseif($site->fdat['tab'] == 'mailinglist') {


			###################
			# kustutada vanad mailinglistid

			$sql = $site->db->prepare("DELETE FROM user_mailinglist WHERE user_id = ?", $site->fdat['user_id']);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());


				###################
				# salvestame mailinglistide valik
				# ja lisada uued mailinglistid

				# rubrrigide ID kontroll
				# kas on seal meilinglist, kas on rubriik avaldatud
				$sql = $site->db->prepare(
					"SELECT obj_rubriik.objekt_id FROM obj_rubriik,objekt WHERE obj_rubriik.objekt_id=objekt.objekt_id AND objekt.on_avaldatud='1' AND obj_rubriik.on_meilinglist = '1' AND obj_rubriik.objekt_id IN(".join(",", $site->fdat['rubriik']).")"
				);
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());

				$values = array();
				while ($objekt_id = $sth->fetchsingle()) {
					$values[] = $site->db->prepare("(?,?)", $site->fdat['user_id'], $objekt_id);
				}

				if (sizeof($values)) {
					$sql = "INSERT INTO user_mailinglist (user_id, objekt_id) VALUES ".join(",",$values);
					$sth = new SQL($sql);
					$site->debug->msg($sth->debug->get_msgs());
				}

			####### write log
			new Log(array(
				'action' => 'update',
				'component' => 'Mailinglist',
				'message' => "User '".$user->all['firstname'].' '.$user->all['lastname']."' mailinglists updated",
			));
	}
	# / SAVE MAILINGLIST TAB
	##############

	############ DELETE  USER
	if($op == 'delete') {
		if($user->user_id) {

			$user->delete();

		} # if allowed to delete
	} # op

	############ LOCK/UNLOCK  USER
	if($op == 'lock') {
		if(1 || $user->user_id) {
			$sql = $site->db->prepare("UPDATE users SET is_locked=?, failed_logins = ?, first_failed_login = ?, last_failed_login = ? WHERE user_id=?",
				$site->fdat['is_locked'], 
				0,
				0,
				0,
				$site->fdat['user_id']
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	

			####### write log
			new Log(array(
				'action' => ($site->fdat['is_locked']?'locked':'unlocked'),
				'component' => 'Users',
				'type' => 'NOTICE',
				'message' => "User '".$user->all['firstname'].' '.$user->all['lastname']."' ".($site->fdat['is_locked']?'locked':'unlocked')." ",
			));
		} # if
	} # op

	##############
	# refresh opener and close popup
	# don't close popup if it was save and errors occured during saving	
	if(($op2=='saveclose' && sizeof($form_error)==0) || $op2=='deleteconfirmed' || $op2 == 'lockconfirmed') {
		?>
		<SCRIPT language="javascript">
		<!--
			window.opener.location=window.opener.location;
			window.close();
		// -->
		</SCRIPT>
		<?
		exit;
	}

	#################
	# RELOAD user INFO after saving

	$user = new User(array(
		user_id => $site->fdat[user_id],
	));

}
# / STEP2: SAVE DATA
#################

##################
# REFRESH page withot saving
if ($site->fdat[refresh]) {
	foreach($site->fdat as $name=> $value)  {
		$user->all[$name] = $value;
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
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
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
	<?php create_form_token('delete-user'); ?>
	<input type=hidden name=user_id value="<?=$site->fdat['user_id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100px">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?
	### don't allow delete if this user is a LAST SUPERUSER (Bug #2211)
	$sql = $site->db->prepare("SELECT user_id FROM users WHERE is_predefined=? AND is_locked<>? AND user_id<>?",1,1,$user->user_id);
	$sth = new SQL($sql);
	$found_another_superuser = $sth->rows;

	# if trying to delete a last superuser then write error message and do nothing
	if(!$found_another_superuser) {
		# show error message
		echo "<font color=red>".$site->sys_sona(array(sona => "Permission denied", tyyp=>"editor"))." - Superuser</font><br><br>";
		$allow_delete = 0;
	}
	# show confirmation
	else {
		echo $site->sys_sona(array(sona => "kustuta", tyyp=>"editor"))." \"<b>".$user->name."</b>\"? ";
		echo $site->sys_sona(array(sona => "are you sure?", tyyp=>"admin"));
		$allow_delete = 1;
	}
?>
	</td>
  </tr>
  <tr align="right"> 
    <td valign="top" colspan=2 > 
		<?if($allow_delete){?>
            <input type="button" value="<?=$site->sys_sona(array(sona => "kustuta", tyyp=>"editor")) ?>" onclick="javascript:document.forms['frmEdit'].op2.value='deleteconfirmed';this.form.submit();">
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
# LOCK CONFIRMATION WINDOW
elseif($op == 'lock') {
?>
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<?php create_form_token('edit-user-lock'); ?>
	<input type=hidden name=user_id value="<?=$site->fdat['user_id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
	<input type=hidden name=is_locked value="<?=($user->all['is_locked']?0:1)?>">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100px">
  <tr> 
	<td valign=top>&nbsp;</td>
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?
	# show confirmation
	echo $site->sys_sona(array(sona => ($user->all['is_locked']?'unlock':'lock'), tyyp=>"kasutaja"))." \"<b>".$user->name."</b>\"? ";
	echo $site->sys_sona(array(sona => "are you sure?", tyyp=>"admin"));
	$allow_lock = 1;
?>
	</td>
  </tr>
  <tr align="right"> 
    <td valign="top" colspan=2 > 
			<?if($allow_lock){?>
            <input type="button" value="<?=$site->sys_sona(array(sona => ($user->all['is_locked']?'unlock':'lock'), tyyp=>"kasutaja")) ?>" onclick="javascript:document.forms['frmEdit'].op2.value='lockconfirmed';this.form.submit();">
			<?}?>
			<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

</form>
<?
}	
# / LOCK CONFIRMATION WINDOW
######################

######################
# EDIT WINDOW
else {
?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<?
######################
# TABS
?>
  <tr> 
    <td valign="top" width="100%"> 
     <table border="0" cellspacing="0" cellpadding="0" width="100%" style="height:21px">
      <tr>
       <td class="scms_tabs_empty">&nbsp;&nbsp;&nbsp;</td>
		<?
		# set all tabs: array[tab name] = tab translated name 
		$tab_arr = array();
		$tab_arr['user'] = $site->sys_sona(array(sona => "user", tyyp=>"kasutaja"));
#		$tab_arr['permissions'] = $site->sys_sona(array(sona => "permissions", tyyp=>"editor"));
		if($op!='new') {
			$tab_arr['account'] = $site->sys_sona(array(sona => "account", tyyp=>"kasutaja"));
			$tab_arr['mailinglist'] = $site->sys_sona(array(sona => "mailing_list", tyyp=>"admin"));
		}
		# default tab is first one:
		$site->fdat['tab'] = $site->fdat['tab'] ? $site->fdat['tab'] : 'user';

		foreach ($tab_arr as $tab=>$tab_title) {
		?>
        <td class="scms_<?=$site->fdat['tab']==$tab?'':'in'?>active_tab" nowrap>
		<?########## tab title #######?>
		<? # show link if not current tab AND NOT new user
			if (1 || $tab != $site->fdat['tab'] && !($op=='new') ) {
				# remove email check: bug #1218
				if (0 && $tab=='mailinglist' && !trim($user->all['email'])){
				$tab_title = "<a href=\"#\" onClick=\"alert('".$site->sys_sona(array(sona => "Missing e-mail address", tyyp=>"kasutaja"))."'); return false;\">".$tab_title."</a>";			
				} else {
				$tab_title = "<a href=".$site->self."?tab=".$tab."&user_id=".$site->fdat['user_id']."&op=".$site->fdat['op'].">".$tab_title."</a>";
				}
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
# / TABS
######################
?>

<?
######################
# 1. CONTENT: tab user
# op = new/edit/print

if($site->fdat['tab'] == 'user') {

	######################
	# FIND parent ID
	# OP = new: new user will be created as CHILD of selected group
	if($op=='new'){
		$parent_group_id =  $site->fdat['group_id'];
	}
	else {
		$parent_group_id = $user->all[group_id];
	}

	######################
	# UNSET user info 
	if($op == 'new') {
		$user = '';
	}
	
	// setup user image change
	$_SESSION['scms_filemanager_settings']['scms_user_image'] = array(
		'select_mode' => 1, // 1 - select single file
		'action_text' => $site->sys_sona(array('sona' => 'fm_choose_user_image', 'tyyp' => 'editor')),
		'action_trigger' => $site->sys_sona(array('sona' => 'fm_use_user_image', 'tyyp' => 'editor')),
		'callback' => 'window.opener.changeUserImage',
	);
	
?>
	<script type="text/javascript">
		var fm_window;
		
		function changeUserImage(data)
		{
			fm_window.window.close();
			
			var imgInput = document.getElementById('image');
			var imgSrc = document.getElementById('userimage');
			
			imgInput.value = '..' + data.files[0].folder + '/.thumbnails/' + data.files[0].filename;
			imgSrc.src = '..' + data.files[0].folder + '/.thumbnails/' + data.files[0].filename;
		}
	</script>
  <tr> 
    <td valign="top" width="100%" class="scms_dialog_area" height="100%"> 

	<!-- Scrollable area -->
<!--	<div id=listing class="scms_middle_div">-->

<?################# CONTENT - SURROUNDING SCROLL TABLE ################?>
<!--
<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_table">
<tr>
<td>
-->

      <table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<?php create_form_token('edit-user-permission'); ?>
	<input type=hidden name=tab value="<?=$site->fdat['tab']?>">
	<input type=hidden name=user_id value="<?=$site->fdat['user_id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
	<input type=hidden name=refresh value="">
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
			<?########### firstname #########?>
            <tr>
              <td width="20%" nowrap><?=$site->sys_sona(array(sona => "Eesnimi", tyyp=>"kasutaja"))?>:</td>
              <td width="100%"><input name="firstname" style="width:160px" type="text" class="scms_flex_input" <?if($user->all['is_readonly']==1){?>disabled<?}?> value="<?=($op=='copy'?'Copy of ':'').($site->fdat['refresh']?$site->fdat['firstname']:$user->all[firstname])?>"></td>
			<?########### image #########?>
				<input type="hidden" name="image" id="image" value="<?= $user->all['image'] ?>">
				<td rowspan="3" align="center" valign="middle" style="padding-right:15px"><img id="userimage" src="<?= $user->all['image']?$user->all['image']:$site->CONF['wwwroot'].$site->CONF['img_path'].'/icons/picture_big.gif' ?>" alt=""><br>
            <?if($user->all['is_readonly']!=1){?><a onclick="fm_window = openpopup('filemanager.php?setup=scms_user_image','filemanager',980, 600);" href="javascript:void(0);"><?=$site->sys_sona(array(sona => "Muuda", tyyp=>"editor"))?></a>
			<?}?>
			</td>
            </tr>
			<?########### name #########?>
            <tr>
              <td width="20%" nowrap><?=$site->sys_sona(array(sona => "Perekonnanimi", tyyp=>"kasutaja"))?>:</td>
              <td width="100%"><input name="lastname" <?if($user->all['is_readonly']==1){?>disabled<?}?> style="width:160px" type="text" class="scms_flex_input" value="<?=($site->fdat['refresh']?$site->fdat['lastname']:$user->all[lastname])?>"></td>
            </tr>
			<?########### email #########?>
            <tr>
              <td width="20%" nowrap><?=$site->sys_sona(array(sona => "Email", tyyp=>"kasutaja"))?>:</td>
              <td width="100%"><input style="width:160px" name="email" type="text" <?if($user->all['is_readonly']==1){?>disabled<?}?> class="scms_flex_input" value="<?=$form_error['email'] || $site->fdat['refresh']?$site->fdat['email']:$user->all[email]?>"><?=($form_error['email']?'<br><font color=red><b>'.$form_error['email'].'</b></font>':'')?></td>
            </tr>
			<?########### parent group #########?>
            <tr>
              <td width="20%" nowrap><?=$site->sys_sona(array(sona => "Parent group", tyyp=>"kasutaja"))?>:</td>
              <td colspan=2  width="100%">
<?

				$sql = "SELECT group_id AS id, parent_group_id AS parent, name FROM groups ORDER BY name";
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());	
				while ($data = $sth->fetch()){
					$temp_tree[] = $data;		
				}
				?>
				<select name="group_id" class="scms_flex_input" <?if($is_moz){?>style="width:260px"<?}?> <?if($user->all['is_readonly']==1){?>disabled<?}?>>
			<?	foreach (get_array_tree($temp_tree) as $key=>$value) {
				$name = str_repeat("&nbsp;&nbsp;", $value['level']).$value['name'];
				print "<option value='".$value['id']."' ".($value['id']==$parent_group_id  || ($site->fdat['refresh'] && $value['id']==$site->fdat['parent_group_id'])? '  selected':'').">".$name."</option>";
			} ?>				
				</select>

		</td>
            </tr>

		<?	
	################# type (profile_id) ########
	$profile_id = ($site->fdat['refresh'] ? $site->fdat['profile_id'] : $user->all['profile_id']);	

	# if still not found profile ID then use default profile ID
	if(!$profile_id) { $profile_id = $site->get_default_profile_id(array('source_table' => 'users')); }

	# get profile
	$profile_def = $site->get_profile(array("id"=>$profile_id)); 
	$profile_fields = unserialize($profile_def['data']);	# profile_fields is now array of ALL fields, indexes are fieldnames


  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		'users');
		$sth = new SQL($sql);
		?>
			<tr>
              <td width="20%" nowrap><?=$site->sys_sona(array(sona => "Type", tyyp=>"admin"))?>:</td>
              <td  colspan=2 width="100%">

		<select name="profile_id" class="scms_flex_input" <?if($user->all['is_readonly']==1){?>disabled<?}?> onchange="javascript:document.forms['frmEdit'].refresh.value='1';document.forms['frmEdit'].submit();" <?if($is_moz){?>style="width:260px"<?}?>>
		<option value=""></option>
		<? while ($data = $sth->fetch()){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			print "<option value='".$data['id']."' ".($data['id']==$profile_id? '  selected':'').">".$data['name']."</option>";
		} ?>				
		</select>

		
		</td>
            </tr>
	</table>
      <br>
      <br>
	<?
	###################
	# Profile info: attributes list

		?>		
	
	<?
	#read only user cannot see this	
	if($user->all['is_readonly']!=1){?>
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
          <td valign=top colspan="2" style="height:140px"> 
			<!-- Scrollable area -->
			<div id=listing class="scms_scroll_div">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="scms_table">
				<?
				###################
				# print profile fields rows
				print_profile_fields(array(
					'profile_fields' => $profile_fields,
					'field_values' => $user->all,
					'fields_width' => "200px"
				));
				?>
			</table>
			</div>
			<!-- //Scrollable area -->
          </td>
        </tr>	

	<?###### / profile fields row ?>
	</table>
		<?}?>
	<?
	# / Profile info: attributes list
	###################
	?>

<!--
</td>
</tr>
</table>
-->
<?################# / CONTENT - SURROUNDING SCROLL TABLE ################?>
<!--	</div>  -->
	<!-- //Scrollable area -->



    </td>
  </tr>
	  <?
		###################
		# buttons
		?>
	<tr> 
    <td align="right" valign="top" class="scms_dialog_area_bottom">
			<?if($user->all['is_readonly']!=1){?>
            <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript:document.forms['frmEdit'].op2.value='save';this.form.submit();">
            <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:document.forms['frmEdit'].op2.value='saveclose';this.form.submit();">
			<?}?>
			<input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> &nbsp;&nbsp;&nbsp;&nbsp;
    </td>
  </tr>


<?
}
# / 1. CONTENT: tab user
######################

######################
# 3. CONTENT: tab PERMISSIONS

elseif($site->fdat['tab'] == 'permissions') {
?>


<?
}
# / 3. CONTENT: tab PERMISSIONS
######################

######################
# 4. CONTENT: tab ACCOUNT

elseif($site->fdat['tab'] == 'account') {

?>

  <tr> 
    <td valign="top" width="100%" class="scms_dialog_area" height="100%"> 
    <table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<?php create_form_token('edit-user-account'); ?>
	<input type=hidden name=tab value="<?=$site->fdat['tab']?>">
	<input type=hidden name=user_id value="<?=$site->fdat['user_id']?>">
	<input type=hidden name=group_id value="<?=$site->fdat['group_id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">		

			<tr> 
			  <td colspan=3>
				<div style="position:relative"> 
				  <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "account", tyyp=>"kasutaja"))?></div>
				</div>
			  </td>
			</tr>


        <tr> 
          <td nowrap><?=$site->sys_sona(array(sona => "Kasutajanimi", tyyp=>"editor")) ?>:</td>
          <td>
            <input name="username" type="text" <?if($user->all['is_readonly']==1){?>disabled<?}?> class="scms_flex_input" style="width:160px" value="<?=$form_error['username']?$site->fdat['username']:$user->all[username]?>"><?=($form_error['username']?'<br><font color=red><b>'.$form_error['username'].'</b></font>':'')?>
          </td>
        </tr>
        <tr> 
          <td nowrap><?=$site->sys_sona(array(sona => "Password", tyyp=>"kasutaja")) ?>:</td>
          <td colspan=2> 
            <input name="password" type="password" <?if($user->all['is_readonly']==1){?>disabled<?}?> class="scms_flex_input" style="width:160px" value="<?=($user->all['password'] ? '********' : '')?>">
          </td>
        </tr>
        <tr> 
          <td nowrap><?=$site->sys_sona(array(sona => "Parool uuesti", tyyp=>"editor")) ?>: </td>
          <td colspan=2> 
            <input name="password_confirmation" <?if($user->all['is_readonly']==1){?>disabled<?}?> type="password" class="scms_flex_input" style="width:160px" value="<?=($user->all['password'] ? '********' : '')?>"><?=($form_error['password']?'<br><font color=red><b>'.$form_error['password'].'</b></font>':'')?>
          </td>
        </tr>
        <tr> 
          <td nowrap><?=$site->sys_sona(array(sona => "Password expires", tyyp=>"kasutaja")) ?>:</td>
          <td valign=bottom><input name="pass_expires" <?if($user->all['is_readonly']==1){?>disabled<?}?> id="pass_expires" type="text" class="scms_flex_input"  style="width:160px" value="<?=$form_error['pass_expires']?$site->fdat['pass_expires']:($user->all['pass_expires'] ? $site->db->MySQL_ee($user->all['pass_expires']) : date('d.m.Y', time() + 60 * 60 * 24 * 365 * 5)); // 5 years from now?>"></td>
		  <td style="width:100%" align="left"><?if($user->all['is_readonly']!=1){?><a href="#" onclick="init_datepicker('pass_expires');"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" title="Choose from calendar" alt="Choose from calendar" border="0"></a><?}?></td>
        </tr>
<?
#############################
# password expires
?>  
		<tr> 
			<td colspan=2>
				<table border=0 cellpadding="0" cellspacing="0" style="width:100%">
				<? $yesterday  = date ("d.m.Y",mktime (0,0,0,date("m")  ,date("d")-1,date("Y"))); ?>
				<tr>
				<td><input type=checkbox id="change_pass" <?if($user->all['is_readonly']==1){?>disabled<?}?> name="change_pass" value="1"
					onclick="javascript:if(this.checked){document.forms['frmEdit'].pass_expires.value='<?=$yesterday?>'} else {document.forms['frmEdit'].pass_expires.value='<?=$site->db->MySQL_ee($user->all[pass_expires])?>'}"></td>
				<td width="100%"><label for="change_pass"><?=$site->sys_sona(array(sona => "Change password", tyyp=>"admin")) ?></label></td>
				</tr>
				</table>
			</td>
		</tr>
<?
#############################
# is predefined (superuser): enabled only for another superuser

# 1) checkbox is disabled if current user is not superuser - access denied

$disabled = $site->user->is_superuser? 0:1;

# 2) checkbox is disabled if this is a LAST superuser existing (not locked)
if(!$disabled){
	# get superusers count
	$sql = $site->db->prepare("SELECT COUNT(*) FROM users WHERE is_predefined=? AND is_locked<>?", '1', '1');
	$sth = new SQL($sql);
	$superuser_count = $sth->fetchsingle();
	# if count is <=1 AND user is curently superuser then dont allow to change the value
	if( $superuser_count<=1 && $user->all['is_predefined']){
		$disabled = 1;
	}
}

?>
	<tr> 
			<td colspan=2>
				<table border=0 cellpadding="0" cellspacing="0" style="width:100%">
				<tr>
				<td><input id="is_predefined" name="is_predefined" type="checkbox" <?if($user->all['is_readonly']==1){?>disabled<?}?> value="1"<?=($user->all['is_predefined'] ? ' checked' : '')?><?=($disabled? ' disabled':'')?>></td>
				<td width="50%"><label for="is_predefined"><?=$site->sys_sona(array(sona => "Superuser", tyyp=>"kasutaja")) ?></label></td>

			<?######## locked ?>
				<td><input id="is_locked" name="is_locked" type="checkbox" value="1"<?=($user->all['is_locked'] ? ' checked' : '')?><?=($user->all['is_readonly'] == 1 ? ' disabled' : '');?>></td>

				<td width="50%"><label for="is_locked"><?=$site->sys_sona(array(sona => "lukus", tyyp=>"editor")) ?></label></td>
				</tr>
				</table>
			
			</td>
			</tr>

<?
#############################
# last login
?>  

		  <tr> 
          <td nowrap><?=$site->sys_sona(array(sona => "Last_access_time", tyyp=>"kasutaja"))?>:</td>
          <td colspan=2><?=$site->db->MySQL_ee($user->all['last_access_time'])?>
			  <br>
			<?=$user->all['last_ip'] ?>
          </td>
        </tr>
<?
#############################
# Role

		# get user roles:
		$user_roles = get_user_roles(array("user_id" => $user->user_id));
		#printr($user_roles);
?>  
		  <tr> 
          <td valign="top"><?=$site->sys_sona(array(sona => "Roles", tyyp=>"kasutaja"))?>:</td>
          <td colspan=2>
		<SELECT NAME="roles[]" multiple style="WIDTH: 99%; height: 46px" size=3 <?if($user->all['is_readonly']==1){?>disabled<?}?>>
<?
		$sqltmp = $site->db->prepare("SELECT * FROM roles ORDER BY name");
		$sthtmp = new SQL($sqltmp);

		while($role = $sthtmp->fetch() ){ ?>
			<option value="<?=$role['role_id']?>" <?=(in_array($role['role_id'],$user_roles)?' selected':'')?>><?=$role['name']?></option>
		<? } ?>
		</select>
          </td>
        </tr>

	  </table>
 		<!-- //Account information -->

</form>


    </td>
  </tr>

	  <?
		###################
		# buttons
		?>
	<tr> 
    <td align="right" valign="top" class="scms_dialog_area_bottom">
            <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript:document.getElementById('is_predefined').disabled=false;document.forms['frmEdit'].op2.value='save';document.forms['frmEdit'].submit();">
            <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:document.getElementById('is_predefined').disabled=false;document.forms['frmEdit'].op2.value='saveclose';document.forms['frmEdit'].submit();">
			<input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>


<?
}
# / 4. CONTENT: tab ACCOUNT
######################




######################
# 5. CONTENT: tab mailinglist

elseif($site->fdat['tab'] == 'mailinglist') {

?>








  <tr> 
    <td valign="top" width="100%" class="scms_dialog_area" height="100%"> 


		<table width="100%" height=100% border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<?php create_form_token('edit-user-mailinglist'); ?>
	<input type=hidden name=tab value="<?=$site->fdat['tab']?>">
	<input type=hidden name=user_id value="<?=$site->fdat['user_id']?>">
	<input type=hidden name=group_id value="<?=$site->fdat['group_id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">

			<tr> 
			  <td colspan=2> 
				<div style="position:relative"> 
				  <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "Uudiste rubriigid", tyyp=>"editor")) ?></div>
				</div>
			  </td>
			</tr>
			<tr height=100%>
				<td valign=top>
					<div class="scms_middle_div" style="overflow: auto; height:100%; max-height:350px;">
					<table width=100% cellpadding=0 cellspacing=0 border=0>
<?


#############################
# kasutaja meili-listid

	$meilinglistid = array();

		# kui kasutaja on registreeritud, 
		# kontrollime millised mailinglistid on tal aktiveeritud
		$sql = $site->db->prepare(
			"SELECT objekt_id FROM user_mailinglist WHERE user_id = ?", $site->fdat['user_id']
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		while ($objekt_id = $sth->fetchsingle()) {
			$meilinglistid[$objekt_id] = $site->fdat["user_id"] ? "checked" : "";
			$site->debug->msg("$objekt_id - checked ");
		}		

	# küsime uudisteliste üle kõigi kasutuselolevate keelte 
	$sth = new SQL ("SELECT keel_id FROM keel WHERE on_kasutusel='1'");
	while($keel = $sth->fetch()) {
		$keeled[] = $keel['keel_id'];
	}

	# koostame meilinglistidega rubriigide loetelu
	$sql = "SELECT * FROM obj_rubriik,objekt WHERE obj_rubriik.objekt_id=objekt.objekt_id AND objekt.on_avaldatud='1' AND obj_rubriik.on_meilinglist = '1' ORDER BY objekt.pealkiri";

	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
	##################
	# put all section IDs into array
	$koik_meilinglistid = array();
	while ($obj = $sth->fetch()) {
		$koik_meilinglistid[] = $obj['objekt_id'];
	}

	##################
	# get all sections (privilege check is already done in rubloetelu class)
	include_once($class_path."rubloetelu.class.php");

	################
	# loop over languages
	foreach ($keeled as $keel) {
		$rubs = new RubLoetelu(array(
			keel => $keel, 
		));
		$topparents = $rubs->get_loetelu();
		if(is_array($topparents)){
		asort($topparents);

		##################
		# loop over all sections

		foreach ($topparents as $obj_id=>$obj_name) {
			if ($obj_id != $site->alias("rub_home_id") && in_array($obj_id, $koik_meilinglistid)){
				$obj_name = str_replace("->"," &gt; ",$obj_name);
	?>
		<tr>
			<td><input type=checkbox name="rubriik[]" value="<?=$obj_id?>" <?=$meilinglistid[$obj_id]?>></td>
			<td> <a href="<?=(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->hostname.$site->wwwroot."/?id=".$obj_id?>" target="_new"><?=$obj_name?></a></td>
		</tr>
	<?
			$on_ml=true; // used in the button section with read-only users to verify the existance of a mailinglist
			} # if
		} # foreach section id
		} # is array topparents
	} # foreach keel
	# / loop over languages
	################

# / kasutaja meili-listid
#############################


?>

					</table>
					</div>
				</td>
			</tr>
		</table>

</td>
        </tr>

	  <?
		###################
		# buttons
		?>
	<tr> 
    <td align="right" valign="top" class="scms_dialog_area_bottom"> 
	    <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript:document.forms['frmEdit'].op2.value='save';document.forms['frmEdit'].submit();">
	    <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:document.forms['frmEdit'].op2.value='saveclose';document.forms['frmEdit'].submit();">
		<input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>


<?
}	

# / 5. CONTENT: tab mailinglist
######################

			
			
?>



</table>

<?
}
# EDIT WINDOW
######################
?>

<?	$site->debug->print_msg(); ?>
</body>

</html>