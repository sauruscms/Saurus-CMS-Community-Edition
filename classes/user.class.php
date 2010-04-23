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
 * user handling functions
 * 
 */


/**
 * user class
 * 
 * All user handling functions - group info, permissions, registration etc
 * User is only logged in user: username and password provided
 * 
 * @param int $user_id 
 *
 * $user = new user(array(
 *	user_id => ID,
 *	[user => username]
 *   [pass => password]
 *	[site => &$this],  # pointer to site isntance
 * ));
 * 
 */
class user extends BaasObjekt {

	# NB! keep constructor small and use public functions to get additional info for user,
	# mimimum user instance will return only user data from table 'users', nothing else

	function user () {
		$args = func_get_arg(0);
		$this->BaasObjekt($args);
		
		$this->args = $args;
		
		# if new user instance is called in the middle of site class,
		# then current site instance is passed as parameter, otherwise usual way is used
		if ($args['site']) {
			$this->site = &$args['site'];
		}

		##################
		# GET USER ID

		# 1. user_id as parameter
		if($args['user_id']) {
			$this->user_id = $args['user_id'];
		}
		# 2. username & password as parameter => AUTHENT by password
		elseif($args['user'] && $args['pass']) {
			$this->user_id = $this->auth_user();		
		}

		####################
		# CHECK USER_ID, at this point must user_id exist 
		# (as parameter or as authented user), but if not
		# then exit and dont create user instance
		if(!$this->user_id)	{
			$this->site->debug->msg("User not logged in => exit");
			$this->user_id = '';
			$this->id = '';
			$this->kasutaja_id = ''; # ALIAS for compability with ver 3
			return;
		}

		#####################
		# 2. LOGGED IN USER: start new debug block for user
		$this->debug->msg("User logged in => User ID: ".$this->user_id);

		####################
		# SET last IP & last access time
		# iga 10 minuti parast uuendame kasutajate 'last_access_time' ja IP aadress:
		if (!$args['skip_last_access_time_update'] && ($this->site->sess_get("kasutaja_ip_kontrollitud") < time() )){
			$sql3 = $this->site->db->prepare("UPDATE users SET last_access_time=".$this->site->db->unix2db_datetime(time()).", last_ip=? WHERE user_id = ?",$_SERVER["REMOTE_ADDR"],$this->user_id);
			$sth3 = new SQL($sql3);
			$this->debug->msg($sth3->debug->fetch_msgs());
			$this->site->debug->msg($sth3->debug->fetch_msgs());
			$sess_add_check = time()+600;
			$this->site->sess_save(array(kasutaja_ip_kontrollitud => $sess_add_check));
		}

		###################
		# GET ALL USER DATA:
		$sql = $this->site->db->prepare("SELECT users.*, pass_expires, if(pass_expires < now(), 1, 0) as is_scms_pass_expired FROM users WHERE user_id=?",
			$this->user_id
		);
		$sth = new SQL($sql);
		# if user found
		if ($sth->rows) {
			$this->all = $sth->fetch('ASSOC');
			# set boolean "pass_expired"; 1 if password is expired
			$this->all['pass_expired'] = $this->all['is_scms_pass_expired'];
			# find user ROLES: array of role ID-s
			$this->roles = get_user_roles(array("user_id" => $this->user_id, "site" => &$this->site));

			$this->debug->msg("User created: ".$this->all['firstname'].' '.$this->all['lastname'].", Group ID: ".$this->all['group_id'].", Role ID: ".join(",",$this->roles). ". Auth type:".$this->auth_type);

			# superuser check
			if($this->all['is_predefined']){
				$this->is_superuser = 1; # never change this attribute, it is widely used
				$this->debug->msg("User is almighty-SUPERUSER.. wow");
			}

			# common properties:
			$this->user_id = $this->all['user_id'];
			$this->id = $this->all['user_id'];
			$this->kasutaja_id = $this->all['user_id']; # ALIAS for compability with ver 3
	
			$this->group_id = $this->all['group_id'];
			$this->parent_id = $this->all['group_id'];

			# keep this name
			$this->name = $this->all['firstname'].' '.$this->all['lastname'];
			# only for old 3.x compability: where '$site->admin->nimi' is used:
			$this->nimi = $this->all['firstname'].' '.$this->all['lastname'];	

			$this->username = $this->all['username'];
			$this->profile_id = $this->all['profile_id'];

			$this->auth_type = $this->auth_type ? $this->auth_type : 'CMS';

		}
		##################
		# IF USER NOT FOUND IN DATABASE
		else {
			$this->user_id = 0;
			return 0;
		}

	} # constructor user
	###################


/**
* auth_user (private)
* 
* returns user_id if user successfully authentitcated using username & password
* returns 0 if failed
*
* 
* @package CMS
* 
*/
function auth_user(){

	$args = $this->args;
	# bug #1914 & #2272
	$args['user'] = htmlspecialchars(xss_clean(strip_tags($args['user'])));
	$site = &$this->site;

	$user_id = 0;

	############ SEARCH PASSWORD
	$sql = $this->site->db->prepare("SELECT password AS pass, user_id, pass_expires, is_locked, failed_logins, first_failed_login, last_failed_login FROM users WHERE username=? AND (is_locked <> '1'  OR ISNULL(is_locked) OR failed_logins > 0)", $args['user']);
	$sth = new SQL($sql);

	$user_passwords = array();
	
	#first fetch from CMS
	$pass_row = $sth->fetch();
	# set boolean "pass_expired"; 1 if password is expired
	$pass_row['pass_expired'] = strtotime($pass_row['pass_expires']) <= time() ? 1 : 0;

	# Kontrollime, kas lukustamise aeg on veel kestab?
	$login_locked_time = ($pass_row['last_failed_login'] + $this->site->CONF['login_locked_time'] * 60) >= time();
	if ($pass_row['is_locked'] && $login_locked_time) {
		$this->is_locked = true;
		return;
	}
	elseif($pass_row['pass']) {
		$pass = $user_passwords[0]['pass'] = $pass_row['pass'];
		$enc_pass = $user_passwords[0]['enc_pass'] = crypt($args['pass'], $pass_row['pass']);
		$user_passwords[0]['type'] = 'CMS';
		$user_passwords[0]['params'] = '';
		$user_passwords[0]['ok'] = ($pass == $enc_pass)?1:0;
		$user_passwords[0]['user_id'] = $pass_row['user_id'];
		$user_passwords[0]['is_locked'] = $pass_row['is_locked'];
		$user_passwords[0]['failed_logins'] = $pass_row['failed_logins'];
		$user_passwords[0]['first_failed_login'] = $pass_row['first_failed_login'];
		$user_passwords[0]['last_failed_login'] = $pass_row['last_failed_login'];
	}

	###### LDAP: skipping tempor. lines 143-190 in admin.class ... ######

	# if found any password
	if (sizeof($user_passwords)>0) {
		### Go throuh all the stuff
		unset($pass);
		unset($enc_pass);
		$pass = 0;
		$enc_pass = 1;
		foreach($user_passwords as $key => $value) {
			if($value['ok']==1) {
				$pass = $value['pass'];
				$enc_pass = $value['enc_pass'];
				$passed_item = $key;
				$ok_user_id = $value['user_id'];
				$failed_logins = $value['failed_logins'];
				$first_failed_login = $value['first_failed_login'];
				$last_failed_login = $value['last_failed_login'];
				break;
			}
		}
		
		//Tegelik sisse logimine toimub siin (topelt kontroll)

		
		
		############ IF PASSWORDS MATCH
		if ($pass == $enc_pass) {

			######### OK: SET USER ID
			$user_id = $ok_user_id;

			########## SAVE ADMIN/ area LANGUAGE to COOKIE
			$sql = $this->site->db->prepare(
				"SELECT keel_id,encoding,extension,locale, glossary_id FROM keel WHERE keel_id=?", $this->site->fdat['keel']
			); 
			$sth = new SQL($sql);
			$keel_result = $sth->fetch();
			$this->site->sess_save(array("keel_admin"=>$keel_result));
			$_SESSION["keel_admin"] = $keel_result;
	
			#################################
			# REDIRECT: kui ID oli peidetult kaasa antud JA t???isURLI polnud kaasa antud
			# siis tee redirect, et saada URL-i ???igeks. bug #668.
			if ($this->site->fdat['id'] && !$this->site->fdat["url"]) {
				header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$this->site->CONF[hostname].$this->site->CONF[wwwroot]."?id=".$args[fdat][id]);
				exit;
			}

			########## SET SITE LANGUAGE to admin language if in admin/ area
			$keel_admin = $this->site->fdat['keel'];

			# if admin-kataloogis siis pane saidi keeleks admin-keel, muidu mitte 
			# 10.06.03 Evgeny: encoding siis ka:
			if ($this->site->in_admin) {
				$this->site->keel  = $keel_admin;
				$this->site->encoding = $keel_result['encoding'];
				$this->site->locale = $keel_result['locale'];
				$this->site->glossary_id = $keel_result['glossary_id'];
				$this->site->debug->msg("KEEL: paneme saidi keeleks admin-keele, sest asume admin/ osas. Keel=".$this->site->keel);
			}
			
			####################################################
			# Toimub ainult siis, kui kasutajal eba???nnestunud 
			# sisselogida v???hemalt ???ks kord. Kui kasutaja oli
			# lukkus, siis avame teda.
			if ($failed_logins > 0) {
				$sql = $this->site->db->prepare(
					"UPDATE users SET is_locked = '0', failed_logins = '0', first_failed_login = '0', last_failed_login = '0' WHERE user_id = ?",
					$user_id
				); 
				$sth = new SQL($sql);
				$this->debug->msg($sth->debug->fetch_msgs());
			}
			# // 
			####################################################

		} # passwords match
		else {
			############################################################
			# USER LOCKOUT
			# User will be locked after number of failed login attempts,
			# from CONFIG['max_login_attempts']
			# 
			$this->site->CONF['max_login_attempts'] = (int) $this->site->CONF['max_login_attempts'];
			if ($this->site->CONF['max_login_attempts']) {
				$login_duration_time = (int) $this->site->CONF['login_duration_time'];
				foreach($user_passwords as $key => $value) {
					##########################################################
					# Siin kontrollime, et aeg esimese ja viimase 
					# eba???nnestunud sisselogimise vahel oleks
					# v???hem kui konfi muutuja 
					# `login_duration_time` - Time within the login attempts 
					# are considered as one (in minutes).
					# - Kui see vahemik on suurem, siis alustame loendama
					#   eba???nnestunud sisselogimised uuesti.
					# - Kui kasutaja on juba lukus, siis j???tkame loendama.
					$failed_logins = (($value['first_failed_login'] + $login_duration_time*60 >= time()) || !$login_duration_time || $value['is_locked']) ? $value['failed_logins'] + 1 : 1;
					# //
					###########################################################
					
					#############################################
					# Kasutaja lukustamine on siin
					$this->is_locked = ($failed_logins >= $this->site->CONF['max_login_attempts']) ? true : false;
					$sql = $this->site->db->prepare(
						"UPDATE users SET ".($this->is_locked ? "is_locked = '1', ":"")."failed_logins = ?, ".($failed_logins == 1 ? "first_failed_login = '".time()."', " : "")."last_failed_login = ? WHERE user_id = ?",
						$failed_logins,
						time(),
						$value['user_id']
					);
					$sth = new SQL($sql);
					$this->debug->msg($sth->debug->fetch_msgs());
					# // kasutaja lukustamine
					##############################################

					##############################################
					# Saadame emeil adminile juhul, kui 
					# kasutaja enne ei ole olnud lukus
					if ($this->is_locked && !$value['is_locked']) {
						$url = $this->site->CONF['hostname'].$this->site->CONF['wwwroot'];
						$output = "Maximum number of login attempts exceeded on the website \"".$url."\".\n";
						$output .= "User \"".$args['user']."\" is locked for ".$this->site->CONF['login_locked_time']." minutes.\n";
						$output .= "User IP: ".$_SERVER["REMOTE_ADDR"]."\n";
						$output .= "Time: ".date("d.m.Y H:i:s")."\n";
						$output .= str_repeat("_", 37)."\n".$this->site->CONF["site_name"]."\nhttp://".$url;
						$send_status = mail($this->site->CONF['default_mail'], "Security notice for \"".$url."\"", $output, "From: ".$this->site->CONF['from_email']."\nContent-Type: text/plain; charset=".$this->site->encoding);
						
					}
					# //
					##############################################
				}
			}
			# // USER LOCKOUT
			####################################################
		} # passwords DOES NOT match => lockout user if needed
	} # if found any password
	
	return $user_id;

} 
# function
########################

/**
* load_objpermissions 
* 
* returns all object permissions for user 
* returns 0 if failed
*
* 
* 
* @package CMS
* 
* @param -
*/
function load_objpermissions(){

	$args = $this->args;

	$perm = array();
	$user_id = $this->user_id;

	$timer = new Timer(); # start Timer
	if($user_id) {

	# 1. if SUPERUSER dont waste time for loading permissions, ALL is allowed by default

	if($this->is_superuser) {
		$this->debug->msg("Hey, we have superuser here: don't waste time for loading permissions, ALL is allowed. Load time: ".$timer->get_aeg());
	}

	# 2. if ordinary user (not almighty superuser) then go on
	else {
		$cemented_perm = get_all_permissions(array(
			type => 'OBJ',
			user => $this, 
			site => $this->site,  
			with_inheriting => 1
		));

	######## double-check DEFAULT site PERMISSIONS:
	# HOME section permssions (sys_alias=home) will be default permissions through all website

	$home_id = $this->site->alias("rub_home_id");
	# set default permissions:
	$this->site->permissions = $cemented_perm[$home_id];

	# if for some reason home section doesn't have permissions
	# then use default mask: only Read permission (CRUPD=01000)
	if(!is_array($this->site->permissions)) {
		$this->site->permissions = array(
			id => '',
			type => 'OBJ',
			source_id => $home_id,
			group_id => '',
			user_id => '',
			C => 0,
			R => 1,
			U => 0,
			P => 0,
			D => 0
		);
		$this->debug->msg("Error: Home section (ID=".$home_id.") permissions not found, using default mask: (CRUPD=01000)");
	}
	
	########## DEBUG MSG
	$perm_msg = " CRUPD = ";
	$perm_msg .= $this->site->permissions['C'] ? $this->site->permissions['C'] : '0';
	$perm_msg .= $this->site->permissions['R'] ? $this->site->permissions['R'] : '0';
	$perm_msg .= $this->site->permissions['U'] ? $this->site->permissions['U'] : '0';
	$perm_msg .= $this->site->permissions['P'] ? $this->site->permissions['P'] : '0';
	$perm_msg .= $this->site->permissions['D'] ? $this->site->permissions['D'] : '0';
	if($this->site->permissions['user_id']){ 
		$perm_msg .= " (set for User ID: ".$this->site->permissions['user_id'].")";
	} elseif($this->site->permissions['group_id']) {
		$perm_msg .= " (set for Group ID: ".$this->site->permissions['group_id'].")";
	} elseif($this->site->permissions['role_id']) {
		$perm_msg .= " (set for Role ID: ".$this->site->permissions['role_id'].")";
	} else {
		$perm_msg .= " (default mask)";
	} $this->debug->msg("Default site permissions are: ".$perm_msg. ". Home ID=".$home_id);

	$this->debug->msg("Object permissions loaded:  ".sizeof(array_keys($cemented_perm))." object permissions found. Load time: ".$timer->get_aeg());

	} # if not superuser

	########### RETURN PERMISSIONS
	return $cemented_perm;

	} # if user_id
	########### give error message in debug info
	else {
		$this->debug->msg("Error: permissions not loaded: no USER ID");
	} # if !user id

}
# /function
##########################


/**
* load_adminpermissions 
* 
* returns all admin-pages permissions for user;
* returns 0 if failed;
* function is called only when user is in admin-area (admin/)
* 
* 
* @package CMS
* 
* @param -
*/
function load_adminpermissions(){

	$args = $this->args;

	$perm = array();
	$user_id = $this->user_id;

	$timer = new Timer(); # alustame m??????tmine
	if($user_id) {

	# 1. if SUPERUSER dont waste time for loading permissions, ALL is allowed by default

	if($this->is_superuser) {
		$this->debug->msg("Hey, we have superuser here: don't waste time for loading permissions, ALL admin-pages is allowed. Load time: ".$timer->get_aeg());
	}
	# 2. if ordinary user (not almighty superuser) then go on
	else {
		$cemented_perm = get_all_permissions(array(
			type => 'ADMIN',
			user => $this, 
			site => $this->site,  
			with_inheriting => 1
		));
	}

	####### debug
	if($this->is_superuser) {
		$this->debug->msg("Hey, we have superuser here: don't waste time for loading permissions, ALL admin-pages is allowed. Load time: ".$timer->get_aeg());
	}
	else {
		$this->debug->msg("Admin-page permissions loaded:  ".sizeof(array_keys($cemented_perm))." permissions found. Load time: ".$timer->get_aeg());
	}

	########### RETURN PERMISSIONS
	return $cemented_perm;

	} # if user_id
	########### give error message in debug info
	else {
		$this->debug->msg("Error: admin permissions not loaded: no USER ID");
	} # if !user id

}
# /function
##########################

/**
* load_aclpermissions 
* 
* returns all acl (group) permissions for user;
* returns 0 if failed;
* function is not called by default in site class but
* in edit-group-window and edit-user-window, 
* also in all templates where any group or user info is used/printed/etc
* 
* 
* @package CMS
* 
* @param -
*/
function load_aclpermissions(){

	$args = $this->args;

	$perm = array();
	$user_id = $this->user_id;

	$timer = new Timer(); # alustame m??????tmine
	if($user_id) {

	# 1. if SUPERUSER dont waste time for loading permissions, ALL is allowed by default
	# if superuser then return almighty 11111 array
	if($this->is_superuser) {
		$cemented_perm = array(
			id => '',
			type => 'ACL',
			source_id => 'ALL',
			group_id => '',
			user_id => $site->user->user_id,
			C => 1,
			R => 1,
			U => 1,
			P => 1,
			D => 1
		);		
	}
	# 2. if ordinary user (not almighty superuser) then go on
	else {
		$cemented_perm = get_all_permissions(array(
			type => 'ACL',
			user => $this, 
			site => $this->site,  
			with_inheriting => 1
		));
	}
#	printr($cemented_perm);

	####### debug
	if($this->is_superuser) {
		$this->debug->msg("Hey, we have superuser here: ALL ACL is allowed. Load time: ".$timer->get_aeg());
	}
	else {
		$this->debug->msg("ACL permissions loaded:  ".sizeof(array_keys($cemented_perm))." permissions found. Load time: ".$timer->get_aeg());
	}
	########### RETURN PERMISSIONS
	return $cemented_perm;

	} # if user_id


	########### give error message in debug info
	else {
		$this->debug->msg("Error: ACL permissions not loaded: no USER ID");
	} # if !user id
}
# /function
##########################


/**
* allowed_adminpage
* 
* Returns 1/0; check if current script (adminpage) is allowed for reading;
* Checks if adminpage is allowed by module setting AND allowed for current user 
* (uses previously loaded adminpages permissions array for this decision)
* NB! This function should be in the beginning of each admin-page which is protected by permissions.
* Protected should be all admin-pages existing in table "admin_osa". 
* Note: some pages may reside in the admin-area (admin/) but are not protected at all.
*
* You can use parameter adminpage_id to check different adminpages, by default current scriptname is used.
* 
* @package CMS
* 
* @param string adminpage_id  
* 
* Usage example (usually called directly after creating $site):
* if (!$site->user->allowed_adminpage()) {
*	exit;
* }
*/
function allowed_adminpage($args = array()) /* PUBLIC */ {
	global $class_path;

	# if adminpage_id is given as parameter, use this
	$adminpage_id = $args['adminpage_id'];
	
	if($args['script_name']) {
		$file = $args['script_name'];		
	}
	# by default try to figure out what is current scriptname 
	else {
		# 1. if usual CMS admin-page
		if($this->site->in_admin){
			$file = $this->site->script_name; # eg admin/cahnge_config.php
		}
		# 2. if custom extensions' admin-page (Bug #2293)
		else {
			$file = $this->site->self; # eg /extensions/my_extension/admin/my_adminpage.php
		}
	}

	# kontrollime, kas see fail on sï¿½ltub moodulist

	$sql = $this->site->db->prepare("
		SELECT admin_osa.fail FROM admin_osa");
	if($adminpage_id) {
		$sql .= $this->site->db->prepare(" WHERE admin_osa.id = ?", $adminpage_id);
	}
	else { # default
		$sql .= $this->site->db->prepare(" WHERE admin_osa.fail LIKE ?", $file);	
	}
	$sth = new SQL($sql);
	$file = $sth->fetch();

	########## go on with non-superuser admin:

	if (!$file) {
		$file = "index.php";
	}

	$sql = $this->site->db->prepare("
		SELECT admin_osa.id FROM admin_osa
		WHERE admin_osa.fail LIKE ?",
		$file
	);
	$sth = new SQL($sql);
	$osa_id = $sth->fetchsingle();
	$on_osad = $sth->rows;

	# n??????d kontrolli sisselogitud useri privileege selle admin-lehe kohta

	############ permissions check
	# kas useril on selle admin-lehe kohta Read ???igus?
	$perm = get_user_permission(array(
		type => 'ADMIN',
		adminpage_id => $osa_id,
		site => $this->site
	));

	# DENIED: if not superuser AND (no admin-pages found OR found admin-page and it was denied for read)
	if (!$this->is_superuser && (!$on_osad || !$perm['R']) ) {	
		######## error: "access denied"
		echo "<html><head>";
		echo "<link rel=\"stylesheet\" href=\"".$this->site->CONF[wwwroot].$this->site->CONF[styles_path]."/scms_general.css\"></head><body>";
		print ' <table width="100%" border="0" cellspacing="3" cellpadding="0" class="scms_borderbox"><tr>';
		print '<td valign="top" width="100%" height="100%">';
		print "<font color=red>".$this->site->sys_sona(array(sona => "access denied", tyyp=>"editor"))."</font>";
		print "</td></tr></table>";
		echo "</body></html>";
		return 0;
	} 
	### OK
	else { return 1; }
} 
# / FUNCTION allowed_adminpage
########################

/**
* load_favorites 
* 
* loads all favourites into $this->favorites for user;
* returns <int> (number of favorites) if there is any;
* returns 0 if no favorites;
* 
* 
* @package CMS
* 
* @param -
*/
function load_favorites(){

	$args = $this->args;
	$user_id = $this->user_id;

	$sql = $this->site->db->prepare("
		SELECT * FROM favorites WHERE user_id = ?",
		$user_id
	);
	$sth = new SQL($sql);
	$no_of_favorites = $sth->rows;
	while($favorite = $sth->fetch()) {
		if($favorite['fav_objekt_id'] > 0) {
			$favorite_ids[] = $favorite['fav_objekt_id'];
		} else if ($favorite['fav_user'] > 0) {
			$favorite_ids[] = "u".$favorite['fav_user'];
		} else if ($favorite['fav_group'] > 0) {
			$favorite_ids[] = "g".$favorite['fav_group'];
		}
	}

	if($no_of_favorites) {
		$this->favorites = $favorite_ids;
		return $no_of_favorites;
	} else {
		$this->favorites = null;
		return 0;
	}
	
} 
# / FUNCTION load_favorites
########################


/**
* get_favorites 
*
* gets favorites by type
* 
* @package CMS
* 
* @param string tyyp_id  
* @param string order  
*/
function get_favorites(){
	if(func_num_args()>0) {
		$args = func_get_arg(0);
	};

	$this->load_favorites();

	$user_id = $this->user_id;
	
	if(is_array($this->favorites) && !$args['fetch_user_favorits'] && !$args['fetch_group_favorits']) {

		/*
		 * Find out what types we are dealing with
		*/
		$sql = $this->site->db->prepare("
			SELECT distinct(tyyp.tabel), tyyp.tyyp_id, tyyp.nimi 
			FROM favorites 
			LEFT JOIN objekt ON favorites.fav_objekt_id = objekt.objekt_id 
			LEFT JOIN tyyp ON objekt.tyyp_id = tyyp.tyyp_id 
			WHERE favorites.user_id = ? ",
			$user_id 
		);
		$sth = new SQL($sql);

		//If we get here there must be an error
		if(!$sth->rows) return false;

		//Put the stuff in a ary
		$tables_to_join = array();
		while($tmp = $sth->fetch()) {
			if($tmp['tyyp_id'] > 0) {
				if(in_array((int)$tmp['tyyp_id'],array(21,22,23))) $tables_to_join[] = $tmp['tabel'];
				$tyyp_name_id_hash[strtolower($tmp['nimi'])] = $tmp['tyyp_id'];
			}
		}

		/*
		 * Get favorites
		*/

		//Limit search
		$where_sql = '';
		if($args['tyyp_id']) {
			$tyyp_id_ary = split(',',$args['tyyp_id']);
			$where_sql = ' AND objekt.tyyp_id in ('.$args['tyyp_id'].')';
		}

		
		//Make join statemants
		$select_sql = "";
		$join_sql = "";
		foreach($tables_to_join as $table) {
			// Special cases
			if($table == 'obj_file') {
				$select_sql .= ", obj_file.* ";
			} else if($table == 'obj_folder') {
				$select_sql .= ", obj_folder.* ";
			} else {
				/* DEFAULT */
				$select_sql .= ", $table.* ";
			}
			$join_sql .= " LEFT JOIN $table ON objekt.objekt_id = $table.objekt_id ";
		}
		
		$join_sql .= ' LEFT JOIN objekt_objekt ON objekt.objekt_id = objekt_objekt.objekt_id ';

		//SQL fun
		$sql = $this->site->db->prepare("SELECT objekt.pealkiri, objekt.objekt_id as objekt_id_r, objekt.tyyp_id, objekt_objekt.parent_id ".$select_sql." FROM objekt ".$join_sql." WHERE objekt.objekt_id IN ('".join("','",$this->favorites)."') $where_sql ORDER BY ".($args['order']?$args['order']:"objekt.pealkiri") );
		$sth = new SQL($sql);
		//Put the stuff in a ary
		$favorites_to_del = array();
		
		$abs_path = preg_replace('#/$#', '', $this->site->absolute_path);
		
		while($obj = $sth->fetch()) {
			
			$object_path = $abs_path.$obj['relative_path'];
			
			// OooO, YEAH now check if the file exists
			if($obj['tyyp_id'] == 21 && is_file($object_path)) {
				$result[] = $obj;
			} else if($obj['tyyp_id'] == 21 && !is_file($object_path)) {
				$favorites_to_del[] = $obj['objekt_id_r'];
			}
			if($obj['tyyp_id'] == 22 && is_dir($object_path)) {
				$result[] = $obj;
			} else if($obj['tyyp_id'] == 22 && !is_dir($object_path)) {
				$favorites_to_del[] = $obj['objekt_id_r'];
			}
			if($obj['tyyp_id'] != 21 &&  $obj['tyyp_id'] != 22) {
				$result[] = $obj;
			}
		}
		
		// Delete all nonexistant files
		if(count($favorites_to_del) > 0) {
			$sql = $this->site->db->prepare("DELETE FROM favorites WHERE fav_objekt_id IN (".join(",",$favorites_to_del).")");
			$sth = new SQL($sql);
		}

		return $result;
	} else if (is_array($this->favorites) && ($args['fetch_user_favorits'] || $args['fetch_group_favorits'])) {

		## was: if(groups.group_id,1,0) as sorting_thing (removed by MS SQL dev)
		$sql = $this->site->db->prepare("
			SELECT favorites.id, favorites.is_selected, users.user_id, users.firstname, users.lastname, users.is_predefined as is_superuser, users.group_id as user_group_id, groups.group_id, groups.name, groups.group_id AS sorting_thing 
			FROM favorites 
			LEFT JOIN groups ON groups.group_id = favorites.fav_group 
			LEFT JOIN users ON users.user_id = favorites.fav_user 
			WHERE  (fav_objekt_id=0 OR ISNULL(fav_objekt_id) ) AND favorites.user_id = ? ORDER BY sorting_thing DESC, name, lastname",$user_id);
		$sth = new SQL($sql);

		//Put the stuff in a ary
		while($obj = $sth->fetch('ASSOC')) {
			if(($obj['group_id']>0) && $args['fetch_group_favorits']) $result[] = $obj;
			if(($obj['user_id']>0) && $args['fetch_user_favorits']) $result[] = $obj;
		}
		return $result;
	}
	
} 
# / FUNCTION load_favorites
########################


/**
* save_favorite 
* 
* Saves a favorite (objekt_id) 2 DB
* 
* Returns FALSE if error otherwise TRUE
*
* @package CMS
* 
* @param int objekt_id
*/
function save_favorite(){
	if(func_num_args()>0) {
		$args = func_get_arg(0);
	} else return false;

	$user_id = $this->user_id;

	if((is_numeric($args['objekt_id']) || is_numeric($args['user_id']) || is_numeric($args['group_id']) ) && $user_id) {
		
		if($args['objekt_id']) {
			$where_sql = $this->site->db->prepare(" AND fav_objekt_id = ? ",$args['objekt_id']);
		} else if($args['user_id']) {
			$where_sql = $this->site->db->prepare(" AND fav_user = ? ",$args['user_id']);
		} else if($args['group_id']) {
			$where_sql = $this->site->db->prepare(" AND fav_group = ? ",$args['group_id']);
		}
		

		//First find out if bookmark allready exists
		$sql = $this->site->db->prepare("
			SELECT * FROM favorites WHERE user_id = ? ".$where_sql,
			$user_id 
		);
		$sth = new SQL($sql);
		//Bookmark allready exists
		if($sth->rows) return false;
		
		if(empty($args['objekt_id'])) $args['objekt_id'] = NULL;
		if(empty($args['user_id'])) $args['user_id'] = NULL;
		if(empty($args['group_id'])) $args['group_id'] = NULL;

		//Add bookmark
		$sql = $this->site->db->prepare("
			INSERT INTO favorites (user_id, fav_objekt_id, fav_user, fav_group, is_selected) VALUES (? ,? ,? ,? ,?)",
			$user_id, 
			$args['objekt_id'],
			$args['user_id'],
			$args['group_id'],
			$args['is_selected']
		);
		$sth = new SQL($sql);
		return true;
	}
	return false;
} 
# / FUNCTION save_favorite
########################


/**
* is_favorite 
* 
* Finds out if objekt is favorite for user
* 
* Returns TRUE if is, otherwise FALSE
*
* @package CMS
* 
* @param int objekt_id
*/
function is_favorite(){
	if(func_num_args()>0) {
		$args = func_get_arg(0);
	} else return false;

	if(!$this->favorites) {
		$this->load_favorites();
	}
	if(is_array($this->favorites)) {
		if($args['objekt_id']) return in_array($args['objekt_id'],array_values($this->favorites));
		if($args['user_id']) return in_array("u".$args['user_id'],array_values($this->favorites));
		if($args['group_id']) return in_array("g".$args['group_id'],array_values($this->favorites));
	}
	return false;
} 
# / FUNCTION is_favorite
########################


/**
* delete_favorite 
* 
* Deletes a favorite (objekt_id) from DB
* 
* Returns FALSE if error otherwise TRUE
*
* @package CMS
* 
* @param int objekt_id
*/
function delete_favorite(){
	if(func_num_args()>0) {
		$args = func_get_arg(0);
	} else return false;

	$user_id = $this->user_id;

	if(is_numeric($args['objekt_id']) && $user_id) {
		//First find out if bookmark allready exists
		$sql = $this->site->db->prepare("
			DELETE FROM favorites WHERE user_id = ? AND fav_objekt_id = ?",
			$user_id, 
			$args['objekt_id']
		);
		$sth = new SQL($sql);
		if($sth->rows) return true;
		return false;
	}
	if(is_numeric($args['user_id']) && $user_id) {
		//First find out if bookmark allready exists
		$sql = $this->site->db->prepare("
			DELETE FROM favorites WHERE user_id = ? AND fav_user = ?",
			$user_id, 
			$args['user_id']
		);
		$sth = new SQL($sql);
		if($sth->rows) return true;
		return false;
	}
	if(is_numeric($args['group_id']) && $user_id) {
		//First find out if bookmark allready exists
		$sql = $this->site->db->prepare("
			DELETE FROM favorites WHERE user_id = ? AND fav_group = ?",
			$user_id, 
			$args['group_id']
		);
		$sth = new SQL($sql);
		if($sth->rows) return true;
		return false;
	}
	return false;
} 
# / FUNCTION delete_favorite
########################


/**
* toggle_favorite 
* 
* Toggles (add || del) favorite
* 
* Returns FALSE if error otherwise TRUE
*
* @package CMS
* 
* @param int objekt_id
*/
function toggle_favorite(){
	if(func_num_args()>0) {
		$args = func_get_arg(0);
	} else return false;

	$user_id = $this->user_id;

	if(is_numeric($args['objekt_id']) && $user_id) {
		if($this->is_favorite(array(objekt_id => $args['objekt_id']))) {
		/* DELETE */
			return $this->delete_favorite(array(objekt_id => $args['objekt_id']));
		} else {
		/* ADD */
			return $this->save_favorite(array(objekt_id => $args['objekt_id']));
		}
	}
	if(is_numeric($args['user_id']) && $user_id) {
		if($this->is_favorite(array(user_id => $args['user_id']))) {
		/* DELETE */
			return $this->delete_favorite(array(user_id => $args['user_id']));
		} else {
		/* ADD */
			return $this->save_favorite(array(user_id => $args['user_id']));
		}
	}
	if(is_numeric($args['group_id']) && $user_id) {
		if($this->is_favorite(array(group_id => $args['group_id']))) {
		/* DELETE */
			return $this->delete_favorite(array(group_id => $args['group_id']));
		} else {
		/* ADD */
			return $this->save_favorite(array(group_id => $args['group_id']));
		}
	}
	return false;
} 
# / FUNCTION toggle_favorite
########################

/**
* delete
*
* Delete all user data from database
* 
* @package CMS
* 
*/
function delete() {

	global $site;

	### don't allow delete if this user is a LAST SUPERUSER (Bug #2211)
	$sql = $site->db->prepare("SELECT user_id FROM users WHERE is_predefined=? AND is_locked<>? AND user_id<>?",1,1,$this->user_id);
	$sth = new SQL($sql);
	$found_another_superuser = $sth->rows;

	# if trying to delete a last superuser then write error message to the website log and do nothing
	if(!$found_another_superuser) {

		new Log(array(
			'action' => 'delete',
			'component' => 'Users',
			'type' => 'ERROR',
			'message' => "Access denied: attempt to delete a last superuser - '".$this->name."' (ID=".$this->user_id.")",
		));
		return;
	}


	$sql = $site->db->prepare("DELETE FROM users WHERE user_id=?",$this->user_id);
	$sth = new SQL($sql);

	# delete from all other tables 

	######## cache
	$sql = $site->db->prepare("DELETE FROM cache WHERE user_id=?",$this->user_id);
	$sth = new SQL($sql);

	######## favorites
	$sql = $site->db->prepare("DELETE FROM favorites WHERE user_id=?",$this->user_id);
	$sth = new SQL($sql);
	$sql = $site->db->prepare("DELETE FROM favorites WHERE fav_user=?",$this->user_id);
	$sth = new SQL($sql);

	######## permissions
	$sql = $site->db->prepare("DELETE FROM permissions WHERE user_id=?",$this->user_id);
	$sth = new SQL($sql);

	######## user_mailinglist
	$sql = $site->db->prepare("DELETE FROM user_mailinglist WHERE user_id=?",$this->user_id);
	$sth = new SQL($sql);

	######## user_roles
	$sql = $site->db->prepare("DELETE FROM user_roles WHERE user_id=?",$this->user_id);
	$sth = new SQL($sql);

	####### write log
	new Log(array(
		'action' => 'delete',
		'component' => 'Users',
		'message' => "User '".$this->name."' (ID=".$this->user_id.") deleted",
	));
}

/**
* get_mailinglist
*
* Returns array of section ID-s where user is subscribed
* 
* @package CMS
* 
*/
function get_mailinglist() {
	$subscribed_mailinglist = array();

	$site = &$this->site;

	# if user OK
	if($this->user_id){
		# kui kasutaja on registreeritud, 
		# kontrollime millised mailinglistid on tal aktiveeritud
		$sql = $site->db->prepare(
			"SELECT objekt_id FROM user_mailinglist WHERE user_id = ?", $this->user_id
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		while ($objekt_id = $sth->fetchsingle()) {
			$subscribed_mailinglist[] = $objekt_id;
			$site->debug->msg("$objekt_id - checked ");
		}
	} # if user OK
	return $subscribed_mailinglist;
}

/**
* get_sso
*
* Returns array of SSO info for user 
* 
* @package CMS
* 
*/
function get_sso() {

	$site = &$this->site;
	# if user OK
	if($this->user_id){
		$user_apps = array();

		# loadime kï¿½ik selle kasutaja rakenduste login info massiivi
		$sql = $site->db->prepare(
			"SELECT sso_id, kgrupp_id, user_value, pwd_value FROM kasutaja_sso WHERE kasutaja_id = ?", $this->user_id
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		while ($user_sso = $sth->fetch()) {
			$user_apps[$user_sso['sso_id']] = $user_sso;
		}		
	} # if user OK
	return $user_apps;
}


/* ols tyle in ver 3 function
*/
###### see siin ei ole adekvaatne funktsioon ja sellest tuleb lahti saada:

	function on_access($input) { # PUBLIC
		# see funktsiooni kaudu suhtleme skriptidega
		# parameeter 'input' on on objekt

		if (is_object($input)) {
			if(!isset($input->permission)){ # load permissions if not loaded yet
				$input->permission = $input->get_permission();
			}
			return $input->permission['is_visible'];
		} else {
			return 0;
		}
	}
/*

*/
	
	function lock($lock_message, $locker_id)
	{
		if($this->user_id)
		{
			$sql = 'update users set is_locked = 1 where user_id = '.(int)$this->user_id;
			new SQL($sql);
			new Log(array(
				'action' => 'lock',
				'type' => 'NOTICE',
				'component' => 'Users',
				'user_id' => $locker_id,
				'message' => $lock_message,
			));
		}
	}

} 

# / class User
###################




########################################## GUEST #######################################
/**
* guest class
* 
* All guest handling functions - group info, permissions
* Guest (or anonymous) is NOT logged in user: that means no username&password is provided,
* is just another website visitor
* 
* @package CMS
* 
*
* constructor new guest(array(
*	[site => &$this],  # pointer to site isntance
* ));
*/
class guest extends BaasObjekt {

	# NB! keep constructor small and use public functions to get additional info for guest

	function guest () {
		$args = func_get_arg(0);
		$this->BaasObjekt($args);
		
		$this->args = $args;
		
		# if new guest instance is called in the middle of site class,
		# then current site instance is passed as parameter, otherwise usual way is used
		if ($args['site']) {
			$this->site = &$args['site'];
		}

		####################
		$this->debug->msg("Guest created => name set to 'Guest'");

		$this->group_id = 1; # everybody HARDCODED

		$this->all = array();
		$this->all['firstname'] = 'Guest';

		# keep this name: it's used in site->kirjuta_log function
		$this->name = $this->all['firstname'];

		return;
	}


/**
* load_objpermissions 
* 
* loads all object permissions for guest from database- these are group everybody permissions
* returns 0 if failed
*
* 
* @package CMS
* 
* @param -
*/
function load_objpermissions(){

	$args = $this->args;

	$perm = array();

	$timer = new Timer(); # alustame m??????tmine

	######### GET EVERYBODY GROUP
	$grouptree = get_grouptree(array("group_id" => ''));

	foreach($grouptree as $key=>$group){
		$group_names[] = $group['name'];
		$group_arr[] = $group['id'];
		$group_msg[] = $group['name']." (ID ".$group['id'].")";
	}
	$this->debug->msg("Guest group info: ".join(" => ",$group_msg));

	######### LOAD ALL GROUP PERMISSIONS: type OBJ

	$sql = $this->site->db->prepare("SELECT * FROM permissions WHERE type=? ", 'OBJ');
	$sql .= $this->site->db->prepare(" AND (group_id IN('".join("','",$group_arr)."') AND user_id=?)",
		0
	); 
	#print $sql;
	$sth = new SQL($sql);
	$cemented_perm = array();
	while ($permtmp = $sth->fetch('ASSOC')) {
		$obj_id = $permtmp['source_id'];
		if($permtmp['group_id']){ # group permission
			$cemented_perm[$obj_id] = $permtmp;
		}
	}

#echo printr($cemented_perm);

	######## double-check DEFAULT site PERMISSIONS:
	# HOME section permssions (sys_alias=home) will be default permissions through all website

	$home_id = $this->site->alias("rub_home_id");
	# set default permissions:
	$this->site->permissions = $cemented_perm[$home_id];

	# if for some reason home section doesn't have permissions
	# then use default mask: only Read permission (CRUPD=01000)
	if(!is_array($this->site->permissions)) {
		$this->site->permissions = array(
			id => '',
			type => 'OBJ',
			source_id => $home_id,
			group_id => '',
			user_id => '',
			C => 0,
			R => 1,
			U => 0,
			P => 0,
			D => 0
		);
		$this->debug->msg("Error: Home section (ID=".$home_id.") permissions not found, using default mask: (CRUPD=01000)");
	}
	########## DEBUG MSG
	$perm_msg = " CRUPD = ";
	$perm_msg .= $this->site->permissions['C'] ? $this->site->permissions['C'] : '0';
	$perm_msg .= $this->site->permissions['R'] ? $this->site->permissions['R'] : '0';
	$perm_msg .= $this->site->permissions['U'] ? $this->site->permissions['U'] : '0';
	$perm_msg .= $this->site->permissions['P'] ? $this->site->permissions['P'] : '0';
	$perm_msg .= $this->site->permissions['D'] ? $this->site->permissions['D'] : '0';
	if($this->site->permissions['user_id']){ 
		$perm_msg .= " (set for User ID: ".$this->site->permissions['user_id'].")";
	} elseif($this->site->permissions['group_id']) {
		$perm_msg .= " (set for Group ID: ".$this->site->permissions['group_id'].")";
	} elseif($this->site->permissions['role_id']) {
		$perm_msg .= " (set for Role ID: ".$this->site->permissions['role_id'].")";
	} else {
		$perm_msg .= " (default mask)";
	} $this->debug->msg("Default site permissions are: ".$perm_msg. ". Home ID=".$home_id);

	$this->debug->msg("Permissions loaded:  ".sizeof(array_keys($cemented_perm))." object permissions found. Load time: ".$timer->get_aeg());

	########### RETURN PERMISSIONS
	return $cemented_perm;

}
# /function
##########################

/**
* load_aclpermissions 
* 
* returns all acl (group) permissions for guest (that means for group Everybody);
* returns 0 if failed;
* function is not called by default in site class but
* in edit-group-window and edit-user-window, 
* also in all templates where any group or user info is used/printed/etc
* 
* 
* @package CMS
* 
* @param -
*/
function load_aclpermissions(){

	$args = $this->args;

	$perm = array();

	$timer = new Timer(); # alustame m??????tmine

	######### GET EVERYBODY GROUP
	$grouptree = get_grouptree(array("group_id" => ''));

	foreach($grouptree as $key=>$group){
		$group_names[] = $group['name'];
		$group_arr[] = $group['id'];
		$group_msg[] = $group['name']." (ID ".$group['id'].")";
	}
	$this->debug->msg("Guest group info: ".join(" => ",$group_msg));
	
	######### LOAD ALL EVERYBODY GROUP PERMISSIONS: type ACL

	$sql = $this->site->db->prepare("SELECT * FROM permissions WHERE type=? ", 'ACL');
	$sql .= $this->site->db->prepare(" AND (group_id IN('".join("','",$group_arr)."') AND user_id=?)",
		0
	); 
	#print $sql;
	$sth = new SQL($sql);
	$cemented_perm = array();
	while ($permtmp = $sth->fetch('ASSOC')) {
		$obj_id = $permtmp['source_id'];
		if($permtmp['group_id']){ # group permission
			$cemented_perm[$obj_id] = $permtmp;
		}
	}	
	$this->debug->msg("ACL permissions loaded:  ".sizeof(array_keys($cemented_perm))." permissions found. Load time: ".$timer->get_aeg());

	########### RETURN PERMISSIONS
	return $cemented_perm;

}
# /function
##########################

}
# / class guest
###################

/**
* get ALL permissions for given TYPE and CURRENT USER (logged in) or GIVEN USER (any user by ID) 
* 
* This is BASE FUNCTION retrieving permission data from table 'permissions'. 
* Returns permission array for given user (not for guest);
* checks if passed user is ordinary user [or superuser] and returns all permissions as array of arrays.
* This function EXECUTES SQL QUERY from database.
* If user is asked by ID then also current user permission check is done - can he/she view this user data at all?
*
* Example: returned array of permissions arrays, type='ACL': 
*	[id] => 117
*   [type] => ACL
*   [source_id] => 1
*   [role_id] => 0
*   [group_id] => 1
*   [user_id] => 0
*   [C] => 0
*   [R] => 1
*   [U] => 0
*   [P] => 0
*   [D] => 0
*   [subtree] => 0
* 
* @package CMS
* 
*
* $cemented_perm = get_all_permissions(array(
*	type => 'ACL',
*   user => $this, # get permissions for current user, use pointer
*   [user_id => 5,],  # get permissions by user ID
*   [group_id => 14],  # get permissions by group ID
*   [role_id => 14],  # get permissions by role ID
*	[site => &$this],  # pointer to site instance (if omitted eg. get by user ID, global is used)
*   with_inheriting => 1 # 1/0  if all parents permissions are also considered or not
* ));
*/
function get_all_permissions () {

	$args = func_get_arg(0);
	global $site;

	# if new user instance is called in the middle of site class,
	# then current site instance is passed as parameter, otherwise usual way is used
	if ($args['site']) {
		$site = &$args['site'];
	}
	$user = &$args['user']; # pointer to current user instance
	$type = $args['type']; # ACL/OBJ/ADMIN/EXT
	$with_inheriting = $args['with_inheriting']; # 1/0 if all parents permissions are also considered

#echo " ".$type.", ";
	######## 1) if CURRENT USER
	if($user->id) {
		$current_user = true;
		$user_id = $user->id;
		$group_id = $user->group_id;
		$role_arr = $user->roles;
#		$is_superuser = $user->is_superuser;
#		echo 'current user: '.$user->id;
	}
	else {
		$current_user = false;
		######## 2) if ANY USER
		if($args['user_id']){
			$user = new user(array("user_id" => $args['user_id']));

			$user_id = $user->id;
			$group_id = $user->group_id;
#			$is_superuser = $user->is_superuser;
#		echo 'whatever user by ID: '.$user->id;
		}

		######## 3) if ANY GROUP
		elseif($args['group_id']) {
		
			$group_id = $args['group_id'];
#		echo 'whatever group by ID: '.$group_id;

		}
		######## 4) if ANY ROLE
		elseif($args['role_id']) {
		
			$role_id = $args['role_id'];
#		echo 'whatever role by ID: '.$role_id;

		}
		######## ERROR - no parameters given
		else {
			return;
		}

		# If user is asked by ID then also current user permission check is done - can he/she view this user data at all?
	
	}
	$cemented_perm = array();

	############# POOLELI:  grouptree peaks panema CASHi vms, sest see v???ib olla juba genreeritud f-ni load_objpermissions k???igus
	if($with_inheriting) { # 1) user final permissions considering also parents

		######### GET ALL USER GROUPS in right order:
		# lowest first, highest last
		$grouptree = get_grouptree(array("group_id" => $group_id));

		foreach($grouptree as $key=>$group){
			$group_names[] = $group['name'];
			$group_arr[] = $group['id'];
			$group_msg[] = $group['name']." (ID ".$group['id'].")";
		}
		if($current_user) {
			if($site->user->user_id) {
				$site->user->debug->msg("User group info: ".join(" => ",$group_msg));
			}
		}
	} # inheriting
	############# / POOLELI:  grouptree peaks panema CASHi vms, sest see v???ib olla juba genreeritud f-ni load_objepermissions k???igus

	######### LOAD ALL user PERMISSIONS: type given

	$sql = $site->db->prepare("SELECT * FROM permissions WHERE type=? ", $type);
	if($with_inheriting) { # 1) user final permissions considering also parents
		# fixing Bug #1656: ???iguste mismatch - v???imalik admin-osale ligi p??????seda
		$sql .= " AND (";
		if(sizeof($role_arr)>0){  # must-be check
			$sql .= "role_id IN('".join("','",$role_arr)."') OR ";
		}
		if(sizeof($group_arr)>0){  # must-be check
			$sql .= "group_id IN('".join("','",$group_arr)."') OR ";
		}
		$sql .= $site->db->prepare(" user_id=?)",
			$user_id
		); 
	}
	elseif($user_id) { # 2) only user permissions
		$sql .= $site->db->prepare(" AND user_id=?", $user_id); 
	}
	elseif($group_id) { # 3) only group permissions
		$sql .= $site->db->prepare(" AND group_id=?", $group_id	); 
	}
	elseif($role_id) { # 4) only role permissions
		$sql .= $site->db->prepare(" AND role_id=?", $role_id	); 
	}
	else { return; } # missing parameters

	#print $sql."<br>";
	$sth = new SQL($sql);
	$permgrp = array();
	$permuser = array();
	while ($permtmp = $sth->fetch('ASSOC')) {
		$obj_id = $permtmp['source_id'];
		if($permtmp['user_id']){ # user personal permission
			if($with_inheriting) { # 1) user final permissions considering also parents
				$permuser[$obj_id] = $permtmp;
			}
			elseif($user_id) { # 2) only user permissions
				$cemented_perm[$obj_id] = $permtmp;
			}
		} # user_id
		elseif($permtmp['group_id']){ # group permission
			if($with_inheriting) { # 1) user final permissions considering also parents
				$permgrp[$permtmp['group_id']][$obj_id] = $permtmp;
			}
			elseif($group_id) { # 3) only group permissions
				$cemented_perm[$obj_id] = $permtmp;
			}			
		} # group_id
		elseif($permtmp['role_id']){ # role permission
			if($with_inheriting) { # 1) user final permissions considering also parents
				$permrole[$permtmp['role_id']][$obj_id] = $permtmp;
			}
			elseif($role_id) { # 3) only role permissions
				$cemented_perm[$obj_id] = $permtmp;
			}			
		} # role_id
	} # while

	########## SET ROLE + GROUP + USER PRIORITIES to right order (user is most important)
	# PRIORITIES: user (HIGHEST) => group => role (LOWEST)

	if($with_inheriting) { # 1) user final permissions considering also parents

		$cemented_perm = array();

		######## 1) ROLES 
		# puudub sisemine konfliktilahendus rollide vahel,
		# praegu lihtsalt viimaste rollide ???igused kirjutavad eelmised ???le
		foreach($role_arr as $role_id){
	#		echo printr($permrole[$role_id]);
			# if role permission exists then overwrite it
			if(is_array($permrole[$role_id])){
				foreach( array_keys($permrole[$role_id]) as $key2=>$obj_id) {
					$cemented_perm[$obj_id] = $permrole[$role_id][$obj_id];	
				}
			} # if role permission exists
		}
		######## 2) GROUPS 
		# priority - atomaarsemad e t???htsamad grupid on allpool,
		# viimaste gruppide ???igused kirjutavad eelmised ???le
		# NB! Siin on erand grupi "Everybody" korral (mille abil keelatakse KEELA KÕIGILE reegel): 
		# juhul kui kasutaja kuulub ka mõnda rolli siis ignoreeritakse Everybody grupi KEELAVAID õiguseid. 

		reset($grouptree);
		foreach($grouptree as $key=>$group){

			# if group permission exists then overwrite it
			if(is_array($permgrp[$group['id']])){
				foreach( array_keys($permgrp[$group['id']]) as $key2=>$obj_id) {

					# kui grupp on Everybody (ID=1) JA leidub rolli-õigused selle objekti kohta,					
					if($group['id'] == 1 && sizeof($cemented_perm[$obj_id]) > 0) {
						# siis ignoreeri Everybody grupi KÕIKI õiguseid selle objekti kohta, sest peavad mõjuma rolli omad. (Bug#2552, #2701)
						continue;
					}
					
					$cemented_perm[$obj_id] = $permgrp[$group['id']][$obj_id];	
				}
			} # if group permission exists
		}
		######### 3) SAVE ALL USER PERSONAL PERMISSIONS to cemented perm
		foreach( $permuser as $obj_id=>$tmp) {
			$cemented_perm[$obj_id] = $permuser[$obj_id];	
		}
	} # with_inheriting
	########## / SET ROLE + GROUP + USER PRIORITIES to right order


	return $cemented_perm;



}


/**
* get_user permission 
* 
* Returns object's permission array for given type for current user
* checks if we have user or guest or superuser and returns 1 array of permissions.
* This function doesn't load any permissions from database
* since permissions are already loaded inside user or guest object
* It only returns suitable permission for object/admin page/etc.
*
* Example: returned permissions array, type='OBJ': 
*	[id] => 117
*   [type] => OBJ
*   [source_id] => 1
*   [group_id] => 1
*   [user_id] => 0
*   [C] => 0
*   [R] => 1
*   [U] => 0
*   [P] => 0
*   [D] => 0
*   [subtree] => 0
* 
* @package CMS
* 
* @param string $type permission type: OBJ/ADMIN/.. 
* @param int $object_id 
* @param string $adminpage_id 
*
* $permission = get_user_permission(array(
*	type => 'OBJ',
*   objekt_id => 222
*   [adminpage_id => 2]
*   [group_id => 2]
*   [extension_id => 2]
*	[site => &$this],  # pointer to site isntance
* ));
*/
	function get_user_permission () {

		$args = func_get_arg(0);
		global $site;

		# if new user instance is called in the middle of site class,
		# then current site instance is passed as parameter, otherwise usual way is used
		if ($args['site']) {
			$site = &$args['site'];
		}
		$perm = array();
	
		##############
		# TYPE = OBJ
		# if parameter object ID not provided
		if($args['type']=='OBJ' && $args['objekt_id']) {
			# if logged in user then load user permissions
			if($site->user->user_id){

				# if superuser then return almighty 11111 array
				if($site->user->is_superuser) {
					$perm = array(
						id => '',
						type => 'OBJ',
						source_id => $args['objekt_id'],
						group_id => '',
						user_id => $site->user->user_id,
						C => 1,
						R => 1,
						U => 1,
						P => 1,
						D => 1
					);
					$is_superuser = 1;
				}
				# else return user personal permissions
				else {
					$perm = $site->user->permissions[$args['objekt_id']];
				} # if not superuser
			}
			# load guest permissions
			else {
				$perm = $site->guest->permissions[$args['objekt_id']];
			}

			# if no permission found for object then use site default permissions:
			if(sizeof($perm)==0){
				$perm = $site->permissions;
				$default = 1;
			}
		}	# if object ID provided
		# / TYPE = OBJ

		##############
		# TYPE = ADMIN
		# if parameter adminpage ID is provided AND logged in user (admin/ area is accessible only for logged in users and not for guests)
		elseif($args['type']=='ADMIN' && $args['adminpage_id'] && $site->user->user_id) {
				# if superuser then return almighty 11111 array
				if($site->user->is_superuser) {
					$perm = array(
						id => '',
						type => 'ADMIN',
						source_id => $args['adminpage_id'],
						group_id => '',
						user_id => $site->user->user_id,
						C => 1,
						R => 1,
						U => 1,
						P => 1,
						D => 1
					);
					$is_superuser = 1;
				}
				# else return user personal permissions
				else {
					$perm = $site->user->adminpermissions[$args['adminpage_id']];
				} # if not superuser	
		}	# if adminpage ID provided AND logged in user
		# / TYPE = ADMIN
		###################

		##############
		# TYPE = ACL
		# 
		elseif($args['type']=='ACL') {
				# if superuser then return almighty 11111 array
				if($site->user->is_superuser) {
					$perm = array(
						id => '',
						type => 'ACL',
						source_id => $args['group_id'],
						group_id => '',
						user_id => $site->user->user_id,
						C => 1,
						R => 1,
						U => 1,
						P => 1,
						D => 1
					);
					$is_superuser = 1;
				}
				# else return user personal permissions
				else {
					$perm = $site->user->aclpermissions[$args['group_id']];
				} # if not superuser	
		}	# if group ID provided
		# / TYPE = ACL
		###################

		##############
		# TYPE = EXT
		# 
		elseif($args['type']=='EXT') {
				# if superuser then return almighty 11111 array
				if($site->user->is_superuser) {
					$perm = array(
						id => '',
						type => 'EXT',
						source_id => $args['group_id'],
						group_id => '',
						user_id => $site->user->user_id,
						C => 1,
						R => 1,
						U => 1,
						P => 1,
						D => 1
					);
					$is_superuser = 1;
				}
				# else return user personal permissions
				else {
					$perm = $site->user->extpermissions[$args['group_id']];
				} # if not superuser	
		}	# if group ID provided
		# / TYPE = EXT
		###################

		################## debug info
		# be sure to have 0 and not empty string:
		$perm['C'] = $perm['C'] ? $perm['C'] : '0';
		$perm['R'] = $perm['R'] ? $perm['R'] : '0';
		$perm['U'] = $perm['U'] ? $perm['U'] : '0';
		$perm['P'] = $perm['P'] ? $perm['P'] : '0';
		$perm['D'] = $perm['D'] ? $perm['D'] : '0';

		$perm_msg .= " CRUPD = ".$perm['C'].$perm['R'].$perm['U'].$perm['P'].$perm['D'];
			if($is_superuser) {
				$perm_msg .= " (superuser)";
			} elseif($default){
				$perm_msg .= " (default)";
			} elseif($perm['user_id']){
				$perm_msg .= " (set for User ID: ".$perm['user_id'].")";
			} elseif($perm['group_id']) {
				$perm_msg .= " (set for Group ID: ".$perm['group_id'].")";
			} elseif($perm['role_id']) {
				$perm_msg .= " (set for Role ID: ".$perm['role_id'].")";
			} else {
				$perm_msg .= " (default)";
			}
		if($args[type]=='OBJ') {
			$debug_msg = "Permission: Obj ".$args['objekt_id']." => ".$perm_msg;
		}
		if($args[type]=='ADMIN') {
			$debug_msg = "Permission: Adminpage ".$args['adminpage_id']." => ".$perm_msg;
		}
		if($args[type]=='ACL') {
			$debug_msg = "Permission: ACL, group".$args['group_id']." => ".$perm_msg;
		}


		########### debug msg
		if($site->user->user_id) {
			$site->user->debug->msg($debug_msg);
		} elseif($site->guest) {
			$site->guest->debug->msg($debug_msg);
		}
		return $perm;
	}

/**
* get_user_roles (public)
* 
* returns array of role ID-s, where user belongs
* returns 0 if failed
*
* 
* @package CMS
* 
* @param user_id # user ID
* $this->roles = get_user_roles(array("user_id" => $this->user_id));
*/
function get_user_roles() {
	$args = func_get_arg(0);
	global $site;
	# if new user instance is called in the middle of site class,
	# then current site instance is passed as parameter, otherwise usual way is used
	if ($args['site']) {
		$site = &$args['site'];
	}
	$user_id = $args['user_id'];

	$user_roles = array();

	$sql = $site->db->prepare("SELECT role_id FROM user_roles WHERE user_id=?", $user_id);
	$sth = new SQL($sql);
	while($tmp = $sth->fetch()){
		$user_roles[] = $tmp['role_id'];
	}

	return $user_roles;
}

#######################################################################
# Funktsioon "count_active_users" arvutab aktiivsete kasutajate arv.
# Aktiivne kasutaja - see on kasutaja, kes k???lastas sait
# viimase aja jooksul. See aeg on m??????ratud
# php.ini failis, muutujaga 'session.gc_maxlifetime'
# ---------------------------------------------------------------------
# "next_counting_active_users" - see on aeg,
# millal toimub j???rgmine arvutamine. Selle aja tagant toimub arvutamine,
# mille resultaat salvestatakse konfi muutujasse "active_users"
# "active_users" - muutuja formaat on kaks numbrit eraldatud kooloniga.
# Esimene number on mitte-sisselogitud kasutajad (guests),
# teine number on hetkel sisse loginud kasutajad.
# Nt. 1:2 - t???hendab, et saidil on 1 mitte-sisselogitud kasutaja ja
# 2 - sisse loginud kasutajat.
function count_active_users() {
	global $site;

	if ($site->CONF['next_counting_active_users'] < time() || !$site->CONF['active_users']) {
		# V???tame siin andmed 'session' tabelist
		$sql = $site->db->prepare("
			SELECT SUM(IF(user_id,1,0)) AS users, COUNT(user_id) AS total 
			FROM session 
			WHERE update_time > ?",
			(time()-ini_get("session.gc_maxlifetime"))
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$result = $sth->fetch();
		$count['guests'] = (int) $result['total'] - (int) $result['users'];
		$count['users'] = (int) $count['users'];
		$site->CONF['active_users'] = $count['guests'].":".$count['users'];

		# update config variable "active_users"
		$sql = $site->db->prepare("UPDATE config SET sisu = ? WHERE nimi = 'active_users'", $site->CONF['active_users']);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		# set next run
		$session_lifetime = ini_get("session.gc_maxlifetime");
		$session_lifetime = $session_lifetime ? $session_lifetime : 1440;
		$sql = $site->db->prepare("UPDATE config SET sisu = ? WHERE nimi = 'next_counting_active_users'", time() + $session_lifetime);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
	} else {
		$count = array('guests' => 0, 'users' => 0);
		list($count['guests'], $count['users']) = split(":",$site->CONF['active_users']);
	}

	return $count;
}
# // count_active_users
######################################################################

######################################################################
# Funktsioon tagastab online kasutajate massiiv
# Selles massiivis, v???li 'time' - viimase login-i aeg,
# kui kasutaja on sisselogitud,
# muidu v???li 'time' - viimase refresh-i aeg
function get_active_users() {
	global $site;

	$sql = $site->db->prepare("
		SELECT session.sess_id, users.username, CONCAT(users.firstname, ' ', users.lastname) AS name, 
		IF(users.last_access_time, DATE_FORMAT(users.last_access_time, \"%d.%m.%Y %H:%i\"), DATE_FORMAT(FROM_UNIXTIME(session.update_time), \"%d.%m.%Y %H:%i\")) AS time, session.url, session.ip 
		FROM session LEFT JOIN users USING (user_id) 
		WHERE session.update_time > ? 
		ORDER BY session.update_time DESC",
		(time()-ini_get("session.gc_maxlifetime"))
	);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	$users = array();
	while ($result = $sth->fetch('ASSOC')) {
		$users[] = $result;
	}

	return $users;
}
# //
#######################################################################

#######################################################################
# Funktsioon kustutab ???ra k???ik sessioni failid, 
# mis on seotud antud user_id parameetriga
# tagastab 1, kui kasutaja v???ljalogimine ???nnestus. T???hendab seda, et
# v???hemalt ???ks sessiooni fail oli kustutatud.
function logout_user($user_id = 0) {
	global $site;

	if ($user_id) {
		# get all active user sessions
		$sql = $site->db->prepare("SELECT sess_id FROM session WHERE user_id = ?", $user_id);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		
		$logout = false;
		# get real session path
		$path = session_save_path();
		while ($sess_id = $sth->fetchsingle()) {
			# check if session file exists
			if(file_exists($path.'/sess_'.$sess_id)) {
				# delete it
				if (unlink($path.'/sess_'.$sess_id)) {
					$logout = true;
				}
			}
		}
		return $logout;
	} else {
		return false;
	}
}
# //
#######################################################################
