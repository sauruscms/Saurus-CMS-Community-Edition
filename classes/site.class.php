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
 * @package 	SaurusCMS
 * @copyright 	2000-2010 Saurused Ltd (http://www.saurus.info/)
 * @license		Mozilla Public License 1.1 (http://www.opensource.org/licenses/mozilla1.1.php)
 * 
 */

/**
 * Main class
 *
 * @param bool $on_debug Debug messages are ON or OFF
 * @param bool $on_admin_keel
 */
class Site {

	var $config; // class CONFIG (config failist laetud)
	var $CONF;	 // array (failist + ab'st)

	var $keel;
	var $encoding;
	var $locale;
	var $glossary_id;

	var $debug;
	var $on_debug;
	
	var $db;
	var $admin;
	var $URI;
	var $safeURI;
	var $kasutaja;
	var $dbstyles;
	var $self;
	var $script_name;
	var $agent;
	var $timer;

	var $cash;

	var $img_path ;
	var $fatal_error;

	var $fdat;
	var $cookie;

	var $rub_loetelu;
	var $session;
	
	var $master_tpl;
	var $master_cont_tpl;
	
	var $args; //pointer to constructor arguments
	
	// wwwroot and hostname as they appear in the database
	var $db_wwwroot;
	var $db_hostname;
	
	// tyyp table, to avoid useless join with that table
	var $object_classes = array();
	var $object_tyyp_id_klass = array();
	var $object_tyyp_id_nimi = array();
	// the contents of these array's are linked

    function Site() {

		global $class_path;

		$args = func_get_arg(0);
		$this->args = &$args;
		
		$this->timer = new Timer();
		$this->cash = array();
		$this->rub_loetelu = array();
		$this->noaccess_hash = array();

		$this->fatal_error='';

		$this->script_version = '4.7.0';
		
		$this->site_poll_url = "http://extranet.saurus.ee/register/cms_site_polling.php";  // auth is register:register

		######################
		# this->fdat
		
		$this->fdat = array();
		
		$this->fdat = (sizeof($_POST) > 0 ? $_POST : $_GET);

		## set magic_quotes_gpc to OFF using transcribe() function
		$this->fdat = $this->transcribe($this->fdat);


		######################
		# alternatiiv "?id" muutujale: tï¿½ï¿½tab ka "pg"

		$this->fdat['id'] = $this->fdat['id'] ? $this->fdat['id'] : $this->fdat['pg'];

		######################
		# this->cookie

		$this->cookie = array();
		if (sizeof($_COOKIE)>0){	
			while (list ($name, $value) = each ($_COOKIE)) {
	
				if (is_array($value)) {
					while (list ($arrname, $arrvalue) = each ($value)) {
						$this->cookie[$name][$arrname] = $arrvalue;
					}
				} else {
					$this->cookie[$name] = $value;	
				}
			}
		}

		######################
		# alias translation are in custom.inc.php
		$this->fdat['op'] = translate_ee($this->fdat['op']);
		if(isset($this->fdat['query'])) $this->fdat['otsi'] =& $this->fdat['query'];
		######################
		# this->self: URL koos scriptinimega, nt /port/index.php 

		# lets check if web server is Apache or not
		if(preg_match("/apache/i", $_SERVER["SERVER_SOFTWARE"]) || preg_match("/apache/i", $_SERVER["SERVER_SOFTWARE"])){
			$this->self = $_SERVER["REQUEST_URI"]; # kui apache
		} else {
			$this->self = $_SERVER["SCRIPT_NAME"]; # kui muu (nt IIS)
		}
#echo "<font color=red>test=".$this->self."</font></br>";
		#$this->self = "/index.php";
		$this->fullself = $this->self;

		# failinimi lï¿½pust maha
		if (preg_match("/^[^\?]*\//", $this->self, $matches)) {
			$path = $matches[0];
		} else {
			$path = $this->self;
		}
		# slash lï¿½ppu!
		if (!preg_match("/\/$/",$path)) {$path .= "/"; }

		$this->self = $path;
		# this->self
		######################

		######################
		# find hostname & wwwroot from url
		#
		# this->hostname: serveri nimi, nt dino.saurus.ee 
		# this->wwwroot: URL ilma scriptinimega, nt /port
		#    on tï¿½histring kui saidil oma virtuaalhost ja dns-kirje.


		$this->wwwroot = $path;

		# vï¿½ta lï¿½pust "/extensions/<MY_EXTENSION_NAME>/admin/" maha kui on (Bug #2190)
		$this->wwwroot = preg_replace("/\/extensions\/(.*)\/admin\/$/i","",$this->wwwroot);

		# vï¿½tame admin/ ja editor/ osa maha
		$re = '/'.preg_replace("/\//","\\\/", '(editor|admin|classes|temp)/.*$').'/i';
		$this->wwwroot = preg_replace($re, "", $this->wwwroot);

		# slash lï¿½pust maha!
		$this->wwwroot = preg_replace("/\/$/","",$this->wwwroot);

		# find hostname from url
		$this->hostname = $_SERVER["HTTP_HOST"];

		# / find hostname & this->wwwroot from url
		####################


		######################
		# $this->script_name - ainult scriptinimi, nt index.php. Bug #2690: $site->script_name leitakse valesti kui url-is esineb kaldkriips

		$this->script_name = $_SERVER["SCRIPT_NAME"];
		$break = explode('/', $this->script_name);
		$this->script_name = $break[count($break) - 1]; 

		# $this->script_name
		######################

		# self = url + failinimi
		$this->self .= $this->script_name;

		######################
		# $this->URI - $site->self + ? + $_SERVER["QUERY_STRING"], nt /port/index.php?id=666 

		# Kui asi seotud aliastega, siis tyhjendame query_string:
		$ENV_QUERY_STRING = $_SERVER["QUERY_STRING"];

		# bug #791
		if (substr_count($ENV_QUERY_STRING,'mod_rewrite') || substr_count($this->self, "/map/")){
			$this->URI = $this->self;
			if (count($this->fdat>1)){
				$this->URI .= "?";
				foreach($this->fdat as $key => $value){
					if ($value && $key!='keel' && $key!='mod_rewrite' && $key!='cmd'){
					$this->URI .= $key."=".$value."&";
					}
				}
			}
		} else {
			$this->URI = $this->self.($ENV_QUERY_STRING ? "?".$ENV_QUERY_STRING : "");
		}
		#$this->URI = $this->self."?".(substr_count($ENV_QUERY_STRING,'mod_rewrite') ? "":$ENV_QUERY_STRING);
		$this->safeURI = urlencode($this->URI);


		######################
		# $this->absolute_path - absolute path of website root

		$this->absolute_path = getcwd().'/';
		# lï¿½pust /admin|editor|classes/ maha
		if (preg_match("/(.*)\/(admin|editor|classes|temp)\/$/", $this->absolute_path, $matches) || preg_match("/(.*)\\\(admin|editor|classes|temp)\/$/", $this->absolute_path, $matches)) {
			$this->absolute_path = $matches[1];
		}
		# slash lï¿½ppu!
		if (!preg_match("/\/$/",$this->absolute_path)) {$this->absolute_path .= "/"; }
		# windows compatible
		$this->absolute_path = str_replace('\\','/',$this->absolute_path);

		######################
		# $this->on_debug		

		$this->on_debug = $args["on_debug"];

		$this->agent = 1;

		# defineerime debug classi sï¿½ltuvalt selles, 
		# kas on_debug = 1 vï¿½i 0
		if ($this->on_debug) {
			include_once($class_path."debug.inc.php");
		} else {
			include_once($class_path."nodebug.inc.php");
		}
		$this->debug = new Debug();
		$this->editor_debug = new Debug();

		$this->debug->msg("Site->hostname: ".$this->hostname);
		$this->debug->msg("Site->wwwroot: ".$this->wwwroot);
		$this->debug->msg("Site->self: ".$this->self);
		$this->debug->msg("Site->script_name: ".$this->script_name);
		$this->debug->msg("Site->URI: ".$this->URI);

		######################
		# $this->CONF: values from file 'config.php'

		$this->CONF = $this->ReadConf();

		$this->dbstyles = array();

		######################
		# $this->db 
		# andmebaasist sï¿½ltumatu API

		include_once($class_path.$this->CONF["dbtype"].".inc.php");

		$this->db = new DB(array(
			"host"	=> $this->CONF["dbhost"],
			"port"	=> $this->CONF["dbport"],
			"dbname"=> $this->CONF["db"],
			"user"	=> $this->CONF["user"],
			"pass"	=> $this->CONF["passwd"],
			'mysql_set_names' => $this->CONF["mysql_set_names"],
		));	

		if ($this->db->error) { 
			print "<font face=\"arial, verdana\" color=red>Error! Can't connect to database!</font>";
			exit;
		}
		# OMG, php OO sakib nii kohutavalt, seep???rast tuleb kasutada globaalset muutujat site instance-i k???tte saamiseks kui see pole veel l???puni valmis looddud. seda on vaja SQL classis.
		global $site;
		$site = $this;


		######################
		# $this->CONF: merge values from file and database
		
		$this->CONF = array_merge($this->CONF, $this->ReadConfDB());

		######################
		# hostname & wwwroot

		/* save old values */
		$this->db_hostname = $this->CONF['hostname'];
		$this->db_wwwroot = $this->CONF['wwwroot'];
		
		## 1. CRON: if hostname is still empty => we may have cron-job running here, 
		# in that case: get hostname and wwwroot from database conf variables (Bug #1903)
		if(trim($this->hostname)=='') {
			$this->hostname = $this->CONF['hostname'];
			$this->wwwroot = $this->CONF['wwwroot'];
		} 
		## 2. PAGE: usual webpage load
		else {
			# arvesta tegelikke vï¿½ï¿½rtuseid ja mitte andmebaasi kirjutatud vï¿½ï¿½rtuseid (Bug #1439):
			$this->CONF["hostname"] = $this->hostname;

			# Bug #2319. ï¿½rme kirjuta ï¿½le CONF['wwwroot'] vï¿½ï¿½rtust, sest aliaste puhul kui meil on nt URL
			# www.site.com/aliaste/rodu/ on suht vï¿½imatu vï¿½lja peilida, mis siis ikkagi on 
			# TEGELIK wwwroot. Seepï¿½rast kasutame andmebaasi vï¿½ï¿½rtust.
			# old: $this->CONF["wwwroot"] = $this->wwwroot;		
			
			#$this->wwwroot = $this->CONF['wwwroot']; # new. no ï¿½kki peaks tegema
		}

		######################
		# $this->img_path

		$this->img_path = $this->CONF["wwwroot"].$this->CONF["img_path"];

		######################
		# current version nr in database

		$sql = "SELECT version_nr FROM version ORDER BY release_date DESC LIMIT 1";
		$sth = new SQL($sql);
		$this->cms_version = $sth->fetchsingle();
		$this->debug->msg("Site CMS version: ".$this->cms_version);

		######################
		# minimum (install) version nr in database

		$sql = "SELECT version_nr FROM version ORDER BY release_date ASC LIMIT 1";
		$sth = new SQL($sql);
		$this->cms_min_version = $sth->fetchsingle();
		$this->debug->msg("Site CMS minimum (install) version: ".$this->cms_min_version);

		######################
		# current version nr in script

		$this->debug->msg("Site->script version: ".$this->script_version);
		
		######################
		# lang/keel in URL

		$this->fdat['keel'] = isset($this->fdat['lang']) ? $this->fdat['lang'] : $this->fdat['keel'];

		# $this->keel
		# $this->encoding
		# $this->extension
		# $this->locale

		$tmp_arr = $this->get_keel(array("on_admin_keel" => $args["on_admin_keel"]));
		$this->keel = $tmp_arr['keel_id'];
		$this->encoding = $tmp_arr['encoding'];
		$this->extension = $tmp_arr['extension'];
		$this->locale = $tmp_arr['locale'];
		$this->glossary_id = $tmp_arr['glossary_id'];

		############### aliases
		$this->load_aliases();

		$this->license = 'Saurus CMS Community Edition';
		$this->title = 'Saurus CMS Community Edition';

		######################
		# $this->admin

		# "in_editor" on true juhul kui ollakse toimetaja keskkonnas: 
		# kui URLis leidub editor/
		$pattern = "/^".preg_replace("/\//","\\\/",$this->CONF['wwwroot'])."\/(editor)\//";
		if (preg_match($pattern, $this->URI)) {
			$this->in_editor = 1;		
		}
		else {
			$this->in_editor = 0;
		}
		# "in_admin" on true juhul kui ollakse admin keskkonnas: 
		# kui URLis leidub admin/
		$pattern = "/^".preg_replace("/\//","\\\/",$this->CONF['wwwroot'])."\/(admin)\//";
		if (preg_match($pattern, $this->URI)) {
			$this->in_admin = 1;		
		}
		else {
			$this->in_admin = 0;
		}
		# for compability with old ver 3: is_admin = treu if we are in admin/ or editor/ area
		if($this->in_editor || $this->in_admin) {
			$this->admin = 1;
		}
		else {
			$this->admin = 0;	
		}
		
		#################################################
		# force HTTPS for editor
		if($this->in_editor && $this->CONF['force_https_for_editing'] && empty($_SERVER['HTTPS']))
		{
			header('Location: https://'.$this->CONF['hostname'].$this->CONF['wwwroot'].'/editor/index.php');
			exit;
		}
		# / force HTTPS for editor
		#################################################

		#################################################
		# force HTTPS for admin
		if($this->in_admin && $this->CONF['force_https_for_admin'] && empty($_SERVER['HTTPS']))
		{
			header('Location: https://'.$this->CONF['hostname'].$_SERVER['REQUEST_URI']);
			exit;
		}
		# / force HTTPS for editor
		#################################################


		$this->debug->msg("MC RUNTIME ".get_magic_quotes_runtime());
		$this->debug->msg("MC CONF ".get_magic_quotes_gpc());
		
		$this->debug->msg("Session id = ".session_id());

		#####################
		# $this->user
		
		$this->create_user();

		//$this->update_wwwroot();


		##############################
		# get object classes
		
		$result = new SQL('select * from tyyp order by tyyp_id');
		while($row = $result->fetch('ASSOC'))
		{
			$this->object_classes[$row['tyyp_id']] = $row;
			
			$this->object_tyyp_id_klass[$row['tyyp_id']] =& $this->object_classes[$row['tyyp_id']]['klass'];
			$this->object_tyyp_id_nimi[$row['tyyp_id']] =& $this->object_classes[$row['tyyp_id']]['nimi'];
		}
		
		# / get object classes
		##############################
		
		#####################
		# global cookies (used through the site)
		# 1) save cookie
		if($this->fdat['group_id']) {
			setcookie("scms_group_id", $this->fdat['group_id']);
		}
		# 2) get cookie
		else {
			$this->fdat['group_id'] = $_COOKIE["scms_group_id"]; 
		}
		# 3) if group_id is still empty then get  top parent group id (Everybody)
		if(!$this->fdat['group_id']) {
			$this->fdat['group_id'] = get_topparent_group(array("site" => $this));
		}
	
		# Kalendri kuup???eva "meelde j???tmine"
		if ($this->fdat['start_date'] && $this->fdat['end_date']) {
			$scms_calendar_date[] = "start_date=".$this->fdat['start_date']."&end_date=".$this->fdat['end_date'];
		}
		if ($this->fdat['week']) {
			$scms_calendar_date[] = "week=".$this->fdat['week'];
		}
		if ($this->fdat['day'] && $this->fdat['month'] && $this->fdat['year']) {
			$scms_calendar_date[] = "day=".$this->fdat['day']."&month=".$this->fdat['month']."&year=".$this->fdat['year'];
		}
		if(is_array($scms_calendar_date)) {
			$_COOKIE['scms_calendar_date'] = htmlentities(urlencode(join("&", $scms_calendar_date)));
			setcookie("scms_calendar_date", $_COOKIE['scms_calendar_date']);
		}

		# Get calendar date cookie
		if ($_COOKIE['scms_calendar_date']) {
			$calendar_date = urldecode($_COOKIE['scms_calendar_date']);
			$calendar_date = split("&", $calendar_date);
			foreach ($calendar_date as $value) {
				$result = split("=", $value);
				$cookie_calendar_date[$result[0]] = $result[1];
			}
		}
		if($cookie_calendar_date && !$this->fdat['start_date'] && !$this->fdat['end_date']) {
			$this->fdat['start_date'] = $cookie_calendar_date['start_date'];
			$this->fdat['end_date'] = $cookie_calendar_date['end_date'];
		}
		if($cookie_calendar_date && !$this->fdat['week']) {
			$this->fdat['week'] = $cookie_calendar_date['week'];
		}
		if($cookie_calendar_date && !$this->fdat['day'] && !$this->fdat['month'] && !$this->fdat['year']) {
			$this->fdat['day'] = $cookie_calendar_date['day'];
			$this->fdat['month'] = $cookie_calendar_date['month'];
			$this->fdat['year'] = $cookie_calendar_date['year'];
		}
		# // Get calendar date cookie

		# / global cookies (used through the site)
		#####################


		#######################
		# leia saidi p???himallid - lehemall ja sisumall

		$this->get_master_tpl(); # leitakse $this->master_tpl, $this->master_cont_tpl

		#######################
		# leia k???igi objektit??????pide p???himallid

		$this->get_objtype_tpl(); # leitakse $this->objtype_tpl

	}
    # function Site
	#####################

	function create_user($args=array())
	{
		
		global $class_path;
		
		# if tulek useri LOGIN VORMIST: 
		# OK: save cookie & redirect
		# not OK: show sys article

		######## FORGOTTEN PASSWORD form (bug #2296)
		if($this->fdat["op"] == 'remindpass' || $this->fdat["op"] == 'saadaparool') {
			include_once($class_path."login_html.inc.php");
			# step2: send e-mail
			$this->fdat['form_error'] = send_remindpass(array("site" => $this));
			# step1: show default entire page form (if no custom templates used)
			if(!$site->fdat['tpl'] && !$site->fdat['c_tpl']){
				print_remindpass_form(array("site" => $this));
				exit;
			}
		}

		if($this->fdat["op"] == 'login' && $this->fdat["url"]) {
			$this->user = new User(array(
				user => $this->fdat["user"],
				pass => $this->fdat["pass"],
				"site" => &$this,
			));
			$user_id = $this->user->user_id;

			# kui ???nnelikult sisse loginud user, siis redirect
			if ($user_id) {
				# kirjuta log
				new Log(array(
					'action' => 'log in',
					'component' => 'Users',
					'user_id' => $user_id,
					'message' => "User '".$this->user->all['firstname']." ".$this->user->all['lastname']."' logged in from IP: '".$_SERVER["REMOTE_ADDR"]."'".($this->user->auth_type ? ' (Authentication:  '.$this->user->auth_type.')': '' ),
				));
				# tee redirect
				$this->sess_save(array(user_id => $user_id));
				setcookie("logged", "1"); // need for cache
				
				header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$this->CONF['hostname'].urldecode($this->fdat["url"]));
				exit;
			}
			# kui eba???nnestunud login, siis n???idatakse vastavat veateadet:
			else {
				# kirjuta logi
				new Log(array(
					'action' => 'log in',
					'component' => 'Users',
					'type' => 'NOTICE',
					'message' => "Unauthorized access to CMS: username '".$this->fdat["user"]."', IP: '".$_SERVER["REMOTE_ADDR"]."'",
				));
				$this->debug->msg("USER login by username & password => failed");

				# nullida sessioonimuutuja
				$this->sess_save(array(user_id => 0));

				# veateade: kui ollakse admin-osas siis n???idatakse seda admin login vormis				
				if($this->in_admin || $this->in_editor) {
					include_once($class_path."login_html.inc.php");
					admin_login_form(array("site" => $this, "auth_error" => ($this->user->is_locked ? 2 : 1)));
				}
				# veateade: kui ollakse editor osas v???i tava osas siis n???idatakse vastavat s???steemi artiklit
				else {
					# leida ???ige s???steemiartikkel
					if ($this->user && $this->user->all['is_locked']) {
						$this->sys_alias = "kasutaja_locked";
					} else {
						$this->sys_alias = "login_incorrect";
					}
				}
				$this->user=0; 
			}
		}
		
		#######################
		# USERI LOOMINE: kas SESSION p???hjal v???i AUTOLOGIN IP p???hjal 
		# 1. first auth by session

		$this->user = new user(array(
			"user_id" => $this->sess_get("user_id"),
			"site" => &$this,
		));
		$this->debug->msg("USER from session => ".($this->user->user_id ? 'Found: '.$this->user->name : 'NONE'));

		######## ADMIN are login form
		# if attempt to admin/ area but user doesn't exist then show login form
		if(($this->in_admin || $this->in_editor)&& !$this->user->user_id) {
			include_once($class_path."login_html.inc.php");
			admin_login_form(array("site" => $this, "auth_error" => 0));
		}

		######## LOAD PERMISSIONS

			# if no user created then unset user instance
			if (!$this->user->user_id) { 
				$this->user=0; 
				# create guest instance; guest has also name and permissions and group info
				$this->guest = new guest(array(
					"site" => &$this,
				));
				# get *object* permissions
				$this->guest->permissions = $this->guest->load_objpermissions();
			}
			# if user successfully created then start loading permissions etc
			else {		

				# permissionite loadimise funktsioone tuleks teha s???ltuvalt asukohast, kas asutakse admin osas vms:
				
				# load  *object* permissions
				$this->user->permissions = $this->user->load_objpermissions();

				# load  *admin pages* permissions

					$this->user->adminpermissions = $this->user->load_adminpermissions();			


				# juhul kui user parool vajab vahetamist (ja tegemist pole styles.php-ga)
				# viia registreerumisvormile
				if ($this->user->all['pass_expired'] && $this->fdat[op] != "register" && $this->script_name != 'styles.php') {
					if($this->in_admin){ # if logging into admin-area
						header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$this->CONF['hostname'].$this->CONF['wwwroot']."?op=register");
					}
					else { 
						header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$this->CONF['hostname'].$this->URI.($_SERVER["QUERY_STRING"]?'&':'?')."op=register");
					}
					exit;
				}
			}

		# ALIAS for compability with ver 3 :
		$this->kasutaja = &$this->user;

		# / $this->user
		#####################
		
	}

	#####################
    # function sess_save
	#####################

	function sess_save () {
		$args = func_get_arg(0);
		$this->debug->msg("Session Save");
		foreach ($args as $key => $value) {
			$_SESSION[$key] = $value;
			$this->debug->msg("Session Save: $key => $value");
		}
	}

	#####################
    # function sess_get
	#####################

	function sess_get ($key) {
		$ret = $_SESSION[$key];
		$this->debug->msg("Session Get: $key => $ret");
		return $ret;
	}


	#########################
    # FUNCTION update_wwwroot
	#########################

	function update_wwwroot() {

		# Kirjutada tegelik hostname ja wwwroot tabelisse 'config' ainult litsentsi downloadi ajal. (Bug #1439)
		# Nï¿½ï¿½d enam ei toetuta mitte andmebaasi vï¿½ï¿½rtustele vaid tegelikele vï¿½ï¿½rtustele, mis URL-is leiti. 

		## DEPRECATED: config var "dont_check_hostname" - Use different hostname for the administrator environment (0/1)
		# alates 3.3.15 vaikimisi vï¿½ï¿½rtus on 1 (YES), enne oli 0 (NO). alates 3.5.1 ï¿½ldse deprecated and deleted.

		$tmp_sript = $_SERVER["SCRIPT_NAME"];
		$name_pos = strrpos($tmp_sript, "/");

		if (!substr_count($_SERVER["QUERY_STRING"],'mod_rewrite') && substr($tmp_sript,$name_pos+1) != 'map'){ # this seems to be pointless check, but keep it at the moment..

			# update database
			$sql = "UPDATE config SET sisu='".$this->hostname."' WHERE nimi='hostname'";
			$sth = new SQL($sql);

			# update variable
			$this->CONF["hostname"] = $this->hostname;

			# update database
			$sql = "UPDATE config SET sisu='".$this->wwwroot."' WHERE nimi='wwwroot'";
			$sth = new SQL($sql);

			# update variable
			$this->CONF["wwwroot"] = $this->wwwroot;
		} # if not alias
	}
    # / FUNCTION update_wwwroot
	#########################

	/**
	 * Polls Saurus server with site info for statistics about installed version and URL, can be turned off from config.php by defining: disable_site_polling = 1
	 *
	 * @param integer $accessed_by
	 */
	function site_polling($accessed_by) 
	{
		if(!$this->CONF['disable_site_polling'])
		{
			$url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$this->hostname.$this->wwwroot;
			
			# ping register.saurus.ee with version info
			$latest_ver = fopen_url_auth($this->site_poll_url."?url=".$url."&license_key=".$this->license."&ver=".$this->cms_version."&accessed_by=".$accessed_by, 'register', 'register', 'Saurus CMS '.$this->cms_version);
		}
	}
	
	#####################
    # function security_check
	#####################

	function security_check() {

		if(!$this->skip_security_check) {
			# check for install script file

		if ($this->fdat['debug']!='on' && $this->fdat['debug']!='off'){
			if(file_exists("../install.php") || file_exists("../install/")) { 
				print "<span style=\"color: red;\">Security warning: please delete file \"install.php\"and folder \"install/\" after installation!</span><br>";
			}

			# check if versions in database and in script match
			if($this->cms_version != $this->script_version) {
				print "<span style=\"color: red;\">Warning: database version ".$this->cms_version." and program version ".$this->script_version." do not match!</span><br>";
			}
			
		}
			
		}
	}

	#####################
    # function ReadConf
	#####################

	function ReadConf() {
		//$file = $this->absolute_path."config.php";
		$file = preg_replace('/extensions\/(.*)/', '', $this->absolute_path).'config.php';
		$CONFIG = fopen ($file, "r");
		# check if file config.php exists at all
		if( !file_exists($file)) { 
			print "<font color=red>Error: file \"$file\" not found!</font>";
			exit;
		} 
		$this->config = new CONFIG(fread($CONFIG, 1024*1024));
		fclose($CONFIG);
		return $this->config->CONF;
	}

	#####################
    # function ReadConfDB
	#####################

	function ReadConfDB() {
	# ---------------------
	# config faili lugemine
	# ---------------------
		$sql = "SELECT nimi,sisu FROM config";
		$sth = new SQL($sql);
		while ($conf_rida = $sth->fetch()) {
			$conf[$conf_rida["nimi"]] = $conf_rida["sisu"];
		}
		$this->debug->msg($sth->debug->get_msgs());
		return $conf;
	}

	#####################
    # function error
	#####################
	
	function error($message) {
		# paneb $message muutujasse $this->fatal_error
		$this->fatal_error = $message;
	}


	#####################
    # function sys_sona
	#####################
	function sys_sona($input) {
		return $this->sys_sona_sql($input);
	}

	function sys_sona_sql($sona) {

	# ---------------------
	# s???steemis???na lugemine
	# old style: sys_sona("s???na")
	# 
	# sys_sona(array(
	#	sona => "s???na", 
	#	tyyp => "custom"
	#	[tyyp_id => "3"]  # alternative to tyyp
	#	[lang_id => "1"]  # kui vaja k???sida kindla keele s???steemis???na
	# ))
	# ---------------------
	$args = func_get_arg(0);
	if (is_array($args)) {
		$sona = $args["sona"];
		$tyyp_id = $args["tyyp_id"];
		$tyyp = $args["tyyp"] ? $args["tyyp"] : "vana";
		$skip_convert = $args["skip_convert"];
		$load_all = $args["load_all"];
	} else {
		$sona = $args;
		$tyyp = "vana";
	}
	# kui keeleparameetrit ei ole, kasuta saidi keelt
	$keel_id = isset($args['lang_id']) ? $args['lang_id'] : $this->glossary_id;

	$tyyp = strtolower($tyyp);

		$sona = $this->db->quote($sona);
		$sona = preg_replace("/\s+/","_",$sona);
		$sona = strtolower($sona);

		$tyyp_id = $this->db->quote($tyyp_id);


		$cash_value = $this->cash(array(klass => "sys_sonad", kood => $sona."_".$tyyp."_".$tyyp_id."_".$keel_id));
		

		if (!strcmp($cash_value,'')) {
			$sql = "
				SELECT IF(LENGTH(sona)>0,sona,origin_sona) AS sona, id, sys_sona
				FROM sys_sona_tyyp, sys_sonad 
				WHERE sys_sonad.sst_id = sys_sona_tyyp.sst_id AND keel='".$keel_id."'";
			

			if (($tyyp=='kujundus' || $tyyp=='kasutaja' || $tyyp=='otsing' || $tyyp=='kalender' || $load_all) && !$this->cash(array(klass => "sys_sonad", kood => $tyyp."_".$tyyp_id."_".$keel_id."LOAD_ALL"))){

				$load_all = 1;			
			} else {
				$sql .= " AND UCASE(sys_sona) LIKE UCASE('$sona') ";	
			}

			if ($tyyp) {
				$sql.=" AND voti='$tyyp' ";
			} elseif ($tyyp_id) {
				$sql.=" AND tyyp_id='$tyyp_id' ";
			}

			$sth = new SQL($sql);

			# T6mbame kogu tyyp cashi:
			if ($load_all){

				$this->cash(array(klass => "sys_sonad", kood => $tyyp."_".$tyyp_id."_".$keel_id."LOAD_ALL", sisu => 1));
				$this->debug->msg("SYS_SONAD: TYYP='".$tyyp."', KEEL ID=".$keel_id."; Put all system strings from this type into cache. Total: ".$sth->rows);

				while($data = $sth->fetch()){

					$mytmp = $this->db->quote($data['sys_sona']);
					$mytmp = preg_replace("/\s+/","_",$mytmp);
					$mytmp = strtolower($mytmp);

					$this->cash(array(klass => "sys_sonad", kood => $mytmp."_".$tyyp."_".$tyyp_id."_".$keel_id, sisu => $data['sona']));
					if ($sona==$mytmp){
						$result = $data;
					}

				}

				
			} else {
				$result = $sth->fetch();
			}

			$sys_sona = $result['sona'];
			$sys_sona_id = $result['id'];
			$tmp_sys_sona = $sys_sona;
			if (($this->on_debug || $this->admin) && !$sys_sona && substr($sona, 0, 8)!='uus_tyyp') {
				$sys_sona = "[$sona]";
				//$tmp_sys_sona = $sona;
			}

#			$this->debug->msg($sth->debug->get_msgs());
			if (!$load_all){
				$this->cash(array(klass => "sys_sonad", kood => $sona."_".$tyyp."_".$tyyp_id."_".$keel_id, sisu => $sys_sona));
			}
			unset($load_all);

		} else {
			$sys_sona = $cash_value;
#			$this->debug->msg("SysSona $sona on juba teatud, see on $cash_value");
		}


		if ( $this->admin && $tyyp !="admin" ) {
			$imgpath = $this->CONF['wwwroot'].$this->CONF['adm_img_path'];
			$sys_sona = $this->cookie["mode"] == "editsysword" ? $sys_sona."\"> <input type=image src=".$imgpath."/e.gif border=0 alt=\"Edit\" onclick=\"javascript:void(avapopup('../admin/sys_sonad_loetelu.php?id=".$sys_sona_id."&keel=".$keel_id."&keeled=all','strukt','400','200','no')); return false;\">" : $sys_sona;
		}


		if ($this->admin){
			$admkeel = $this->sess_get("keel_admin");		
		}

		if (($keel_id=='2' || $admkeel['glossary_id']=='2') && $this->CONF['cyr_convert_encoding'] && !$skip_convert){
			$sys_sona = convert_cyrillic($sys_sona, $this->CONF['cyr_convert_encoding']);
		}	

		#echo "test:".$admkeel['keel_id']."<br>";
		#echo "test=".$this->CONF['cyr_convert_encoding']."<br>";

		return $sys_sona;		
	}

	#####################
    # function dbstyle
	#####################

	function dbstyle($key,$param) {
		return $this->dbstyle[$key][$param];
	}

	#####################
    # function load_aliases
	#####################

	function load_aliases() {
	# preload sys_aliases

		$this->debug->msg("loen sys aliased");
		$sql = $this->db->prepare(
			"SELECT objekt_id,sys_alias FROM objekt WHERE sys_alias>'' AND keel=? ",
			$this->keel
		);
		$sth = new SQL($sql);
		$this->debug->msg($sth->debug->get_msgs());
		
		while ($record = $sth->fetch()) {
			# save to cache
			$this->cash(array(
				"klass" => "sys_alias", 
				"kood" => $record["sys_alias"]."_".$this->keel, 
				"sisu" => $record['objekt_id']
			));
		}
		# get folder ID of "public/"
		$sql = $this->db->prepare("SELECT objekt_id FROM obj_folder WHERE relative_path = ? LIMIT 1",
			$this->CONF['file_path']
		);
		$sth = new SQL($sql);
		$tmp = $sth->fetch();
		
		$this->cash(array(
			"klass" => "sys_alias", 
			"kood" => 'public_'.$this->keel, 
			"sisu" => $tmp['objekt_id']
		));

	}

	#####################
    # function alias
	#####################

	function alias() {
		$args = func_get_arg(0);
		if (is_array($args)) {
			$newargs=&$args;
		} else {
			$newargs=array();
			$newargs["key"] = $args;
			$newargs["keel"] = $this->keel;
		}
		if($newargs['key'] === null) return null;
		
		if ($newargs["keel"] === '') {$newargs["keel"] = $this->keel;}
		$newargs["skip_lang"] = $args['skip_lang'];
		if (preg_match('/^(rub_|art_)(.*?)(_id)?$/i', $newargs["key"], $matches)) {
			$newargs["key"] = $matches[2];
		}

		$newargs["key"] = strtolower($newargs["key"]);
		
		# seacrh value form cache
		$cash_value = $this->cash(array("klass" => "sys_alias", "kood" => $newargs["key"]."_".$newargs["keel"]));
		$this->debug->msg("otsime alias ".$newargs["key"]."_".$newargs["keel"]);

		if (!strcmp($cash_value,'')) {

			# for sys_aliases where language doesn't matter (eg resource root)
			if($newargs["skip_lang"]) {
				$sql = $this->db->prepare(
					"SELECT objekt_id FROM objekt WHERE sys_alias = ?",
					$newargs["key"]
				);
			}
			# but usually it matters..
			else {
				$sql = $this->db->prepare(
					"SELECT objekt_id FROM objekt WHERE sys_alias = ? and keel=?",
					$newargs["key"], $newargs["keel"]
				);		
			}


			$sth = new SQL($sql);
			$this->debug->msg($sth->debug->get_msgs());
			$sys_alias = $sth->fetchsingle();

			# save to cache
			$this->cash(array(
				"klass" => "sys_alias", 
				"kood" => $newargs["key"]."_".$newargs["keel"], 
				"sisu" => $sys_alias
			));
		} else {
			$this->debug->msg("tean juba alias ".$newargs["key"]."_".$newargs["keel"].": $cash_value");
			$sys_alias = $cash_value;
		}
		return $sys_alias;
	}

	#####################
    # function get_keel
	#####################

	function get_keel() {
	# ---------------------------------
	# tagastab keele numbri URLi pï¿½hjal 
	# 0-eesti, 1-inglise, 2-vene
	# ---------------------------------
		$args = @func_get_arg(0);
		//printr($_SESSION);
		# kui f-n otsib admin-osa keelt
		if ($args["on_admin_keel"]) {
			$prefix="_admin";
			$this->debug->msg("Kasutame admini keel");
		} 
		# kui f-n otsib saidi keelt
		else {
			$prefix="";
			$this->debug->msg("Kasutame kasutaja keel");
		}

		##################
		# 1. kui URLis on keel määratud: nt keel=en või lang=en
		# siis leida see keel tabelist

		if (strcmp($this->fdat["keel"],'') && !is_numeric($this->fdat["keel"])) {			
			# urlis on keel, kontrollime olemasolu, 
			$this->debug->msg("KEEL(1): on leitud URLis: ".$this->fdat["keel"]);

			###### et > ee
			# Default Estonian lang extension is changed: "ee" => "et" starting from version 4.5.0, create alias if needed.
			# If full install is older than 4.5.0 AND ?lang=et => check if lang=ee found in db. If found then make alias et > ee.
			if(substr(str_replace('.','',$this->cms_min_version),0,2) < 45 && $this->fdat['keel'] == 'et'){ # compare 20 < 33 < 44 < 45
				# if found "ee" in lang table then change et => ee
				$sqltmp = $this->db->prepare("SELECT keel_id,encoding,extension, locale, site_url, glossary_id FROM keel WHERE on_kasutusel=1 AND extension=?", 'ee');
				$sthtmp = new SQL($sqltmp);
				$this->debug->msg($sthtmp->debug->get_msgs());
				if ($keeltmp = $sthtmp->fetch()) {
					$this->debug->msg("KEEL(1A): muuda keel URLis: ".$this->fdat["keel"]. ' => ee');				
					$this->fdat['keel'] = 'ee';
				}
			} ##### / et > ee

			# GET LANG: string (et, en): päring extension-i järgi
			$sql = $this->db->prepare("SELECT keel_id,encoding,extension, locale, site_url, glossary_id FROM keel WHERE on_kasutusel=1 AND extension=?", $this->fdat["keel"]);
			$sth = new SQL($sql);
			$this->debug->msg($sth->debug->get_msgs());
			$keel = $sth->fetch();
		} 
		
		####################
		# 2. Kui URLis keelt ei olnud, siis vaadata kas keeletabelis leidub site_url, mis klapib tegeliku URL-iga?
		# Kui leidub, siis leiame selle keele ja paneme automaatselt keele cookie.
		# Keeletabelist otsitakse kõiki neid variante:
		#	site_url
		#	site_url/
		#	http://site_url
		#	http://site_url/

		if (!strcmp($keel["keel_id"],'')) {

			$sql = $this->db->prepare("SELECT keel_id, encoding, extension, locale, site_url, glossary_id FROM keel 
				WHERE on_kasutusel=1 AND (site_url=? OR site_url=? OR site_url=? OR site_url=?)",
				$this->hostname.$this->wwwroot,
				$this->hostname.$this->wwwroot."/",
				"http://".$this->hostname.$this->wwwroot,
				"http://".$this->hostname.$this->wwwroot."/"
				);
			$sth = new SQL($sql);
			$keel = $sth->fetch();
			$this->debug->msg("KEEL(2): Otsime kas leidub keelt, mille sait klapiks käesolevaga.. ".($sth->rows ? "leitud" : "ei leidnud"));
		}

		//printr($keel);

		####################
		# 3.kui URLis keelt ei olnud ja ka keeletabelis käesoleva saidi URLi ei olnud,
		# siis vaadata kas alias_language_format on sisse lülitatud ning kui on
		# siis suunata default keelele
		# bug #2872

		if ($this->script_name == 'index.php' && ($this->URI != $this->wwwroot . '/editor/' . $this->script_name) && ($this->URI != $this->wwwroot . '/admin/' . $this->script_name) && !$this->in_editor && $prefix != '_admin' && !(sizeof($_POST) || sizeof($_GET)) && ($this->CONF['alias_language_format'] == 1 || $this->CONF['alias_language_format'] == 2) && $this->CONF['use_aliases'] && !strcmp($keel["keel_id"],'')) {
			$sql = $this->db->prepare("SELECT keel_id, encoding, extension, locale, site_url, glossary_id FROM keel 
				WHERE on_kasutusel=1 AND on_default=1");
			$sth = new SQL($sql);
			$keel = $sth->fetch();

			$this->debug->msg("KEEL(3): Võtame default keele KEEL_ID = ".$keel["keel_id"]."  ENCODING: ".$keel["encoding"]);
		}
		
		##################
		# 4. kui URLis keelt ei olnud, keeletabelis käesoleva saidi URLi ei olnud
		# ja alias_language_format on välja lülitatud,
		# siis vaadata kas sessioonimuutujas "keel" (või "keel_admin") on väärtus

		if (!strcmp($keel["keel_id"],'')) {
			$keel = $this->sess_get("keel$prefix");

			$this->debug->msg("KEEL(4): sessioonist KEEL_ID = ".$keel["keel_id"]."  ENCODING: ".$keel["encoding"]);
		}
			
		####################
		# 5. Kui seni ikka veel keel määramata,
		# siis otsime keeletabelist, milline keel on default

		#by Dima 02.06.2003
		if (!strcmp($keel["keel_id"],'')) {
			# kui pole kuskil, 
			# kasutame default
			$this->debug->msg("Otsime default keel ");
			$sql = "select keel_id,encoding,extension, locale, site_url, glossary_id from keel where on_default$prefix='1'";
			$sth = new SQL($sql);
			if ($keel = $sth->fetch()) {
				$this->debug->msg("KEEL(5): leitud default keel=".$keel["keel_id"]." ENCODING: ".$keel["encoding"]);
			}
		}

		####################
		# 6. kui error ja keeletabelist keelt ei leitud,
		# kasutada keeleks ID-ga 1 keelt: inglise oma

		if (!strcmp($keel["keel_id"],'')) {
			$this->debug->msg("KEEL(6): error! keeletabelis keelt pole, kasutan default 1 (en)");
			$keel["keel_id"]=1;
		}
		#####################
		# debug info

		$this->debug->msg("KEEL määratud: ".$keel["keel_id"]." ENCODING: ".$keel["encoding"]);	
		#####################
		# kirjutada leitud keel cookiesse

		if($prefix == '_admin') $keel['glossary_id'] = $keel['keel_id'];
		$this->sess_save(array("keel$prefix"=>$keel));
		//printr($_SESSION);

		return $keel;
	}

	#####################
    # function change_keel
	#####################

	function change_keel($keel) {
	# ---------------------------------
	# tagastab keele numbri URLi pï¿½hjal 
	# 0-eesti, 1-inglise, 2-vene
	# ---------------------------------

		if(is_numeric($keel)) {
			$sql = $this->db->prepare("SELECT keel_id,encoding,extension, locale, site_url, glossary_id FROM keel WHERE on_kasutusel=1 AND keel_id=?", $keel);
			$sth = new SQL($sql);
			$this->debug->msg($sth->debug->get_msgs());
			$keel = $sth->fetch();

			$this->keel = $keel['keel_id'];
			$this->encoding = $keel['encoding'];
			$this->extension = $keel['extension'];
			$this->locale = $keel["locale"];
			$this->glossary_id = $keel['glossary_id'];

			$this->debug->msg("KEEL muudetud: ".$keel["keel_id"]." ENCODING: ".$keel["encoding"]);	
			//printr("KEEL muudetud: ".$keel["keel_id"]." ENCODING: ".$keel["encoding"]);

			# salvestada cookiesse uus vï¿½ï¿½rtus
			$this->sess_save(array("keel"=>$keel));
			$this->sess_save(array("keel"=>$keel));

			# leia uuesti saidi sï¿½saliased
			$this->load_aliases();

			# leia uuesti saidi pï¿½himallid - lehemall ja sisumall
			$this->get_master_tpl();
		}

	}
	#####################
    # function cash
	#####################

	function cash() {
		# see on cash - paneb mï¿½lusse objekt
		# ja tagastab seda tagasi kui vajadus tekkib
		# 
		# salvestamiseks vaja kutsuda site->cash($objekt)
		# taastamiseks 
		# site->cash(array(
		#	id = 123, (objekt_id vï¿½i parent_id alamlisti jaoks)
		#	klass = "objekt" || "alamlist"
		# ))

		$args = func_get_arg(0);

		if (is_object($args) && $args->kood) {
			# salvesta objekt
			$this->cash[get_class($args).$args->kood] = &$args;
			# ï¿½ra trï¿½ki debug infot sï¿½ssï¿½nade cashi kohta (liiga palju mï¿½ra)
#PHP5			if ($args["klass"] != 'sys_sonad' && $args["klass"] != 'smarty_syswords') {
#PHP5				$this->debug->msg("CASH: Objekt ".get_class($args)." salvestatud, kood = ".$args->kood);
#PHP5			}
		} elseif (is_array($args) && isset($args['sisu'])) {
			# salvesta scalar
			$this->cash[$args["klass"].$args["kood"]] = $args["sisu"];
			# ï¿½ra trï¿½ki debug infot sï¿½ssï¿½nade cashi kohta (liiga palju mï¿½ra)
			if ($args["klass"] != 'sys_sonad' && $args["klass"] != 'smarty_syswords') {
				$this->debug->msg("CASH: Objekt ".$args["klass"]." salvestatud, kood = ".$args["kood"]);
			}
		} elseif (is_array($args)) {
			# otsime vï¿½ï¿½rtus
			$obj = &$this->cash[$args["klass"].$args["kood"]];
			if ($obj) {
				# ï¿½ra trï¿½ki debug infot sï¿½ssï¿½nade cashi kohta (liiga palju mï¿½ra)
				if ($args["klass"] != 'sys_sonad' && $args["klass"] != 'smarty_syswords') {
					$this->debug->msg("CASH: Objekt ".$args["klass"]." leitud, kood = ".$args["kood"]);
				}
				return $obj;
			} else {
				# ï¿½ra trï¿½ki debug infot sï¿½ssï¿½nade cashi kohta (liiga palju mï¿½ra)
				if ($args["klass"] != 'sys_sonad' && $args["klass"] != 'smarty_syswords') {
					$this->debug->msg("CASH: Objekt ".$args["klass"]." pole leitud, kood = ".$args["kood"]);
				}
			}
		} else {
			$this->debug->msg("CASH: ei saanud aru mida sa tahad :(");
		}
	}

	#####################
    # function eesti_aeg
	#####################

	function eesti_aeg () {
		return date("d.m.Y");
	}

	#####################
    # function kirjuta_log, deprecated, acts as a wrapper for Log class
	#####################

	function kirjuta_log($args = array())
	{
		new Log(array(
			'type' => ($args['on_error'] ? 'ERROR' : 'message'),
			'action' => ($args['on_import'] ? 'import' : ($args['on_export'] ? 'export' : '')),
			'message' => $args['text'],
			'user_id' => ($args['sisestaja'] ? $args['sisestaja'] : $this->user->user_id),
			'objekt_id' => $args['objekt_id'],
		));
	}
	
	#####################
    # function get_master_tpl
	#####################
	
	function get_master_tpl() {

		# leia master malli ID
		$sql = $this->db->prepare("SELECT page_ttyyp_id, ttyyp_id FROM keel WHERE keel_id=?",
			$this->keel
		);
		$sth = new SQL($sql);
		$this->debug->msg($sth->debug->get_msgs());
		list($page_ttyyp_id, $ttyyp_id) = $sth->fetchrow();

		# pï¿½ri master malli andmed templ_tyyp tabelist
		if($page_ttyyp_id) {
			$sql = $this->db->prepare("SELECT * FROM templ_tyyp WHERE ttyyp_id=?",
				$page_ttyyp_id
			);
			$sth = new SQL($sql);
			$tpl = $sth->fetch();

			# assign site variable
			$this->master_tpl = $tpl;
		}
		else {
			$this->master_tpl = '';
		}

		if($ttyyp_id) {
			$sql = $this->db->prepare("SELECT * FROM templ_tyyp WHERE ttyyp_id=?",
				$ttyyp_id
			);
			$sth = new SQL($sql);
			$tpl = $sth->fetch();

			# assign site variable
			$this->master_cont_tpl = $tpl;
		}
		else {
			$this->master_cont_tpl = '';
		}

		$this->debug->msg("Site master page template is '".$this->master_tpl['nimi']."' (ID=".$this->master_tpl['ttyyp_id'].")");
		$this->debug->msg("Site master content template is '".$this->master_cont_tpl['nimi']."' (ID=".$this->master_cont_tpl['ttyyp_id'].")");


	}
    # / function get_master_tpl
	#####################


	#####################
    # function get_objtype_tpl
	# salvestab massiivi : 
	# $this->objtype_tpl[objekti tï¿½ï¿½p] => malli ID
	#####################
	
	function get_objtype_tpl() {

		$this->objtype_tpl = array();
		$this->objtype = array();

		# leia k???igi objektit??????pide p???himallid:
		$sql = $this->db->prepare("SELECT tyyp_id, ttyyp_id, klass, nimi FROM tyyp");
		$sth = new SQL($sql);

		# assign site variable
		while($tpl = $sth->fetch()) {
			$this->objtype_tpl[$tpl['tyyp_id']] = $tpl['ttyyp_id'];
			$this->objtype[$tpl['tyyp_id']] = $tpl['klass'];
			$this->objtype_name[$tpl['tyyp_id']] = $tpl['nimi'];
		}

		$this->debug->msg("All object type templates are loaded");


	}
    # / function get_objtype_tpl
	#####################

	#####################
    # function get_profile
	# cash used;
	# usage : 	$profile = $site->get_profile(array(
	#	name => $profile_name
	#   [id => $profile_id]
	# ));
	#####################

	function get_profile() {

		$args = func_get_arg(0);
		# f-ni vï¿½ib vï¿½lja kutsuda kas nime vï¿½i id jï¿½rgi
		if($args['name']) {
			$profile_kood = strtolower($args['name']); # profile name is case insensitive
			$koodnimi = "name";
		} elseif($args['id']) {
			$profile_kood = $args['id'];
			$koodnimi = "profile_id";
		}
		# check if profiles are already loaded in cash
		$profiles_loaded = $this->cash(array(klass => "GET_PROFILE", kood => 'LOAD_ALL'));

		# if not in cash, query them and save into cache
		if (!$profiles_loaded && !$args['no_cache']){	
			$sql = $this->db->prepare("SELECT * FROM object_profiles ");
			$sth = new SQL($sql);
			while ($profile = $sth->fetch()){
				$this->cash(array(klass => 'GET_PROFILE', kood => strtolower($profile['name']), sisu => $profile));
				$this->cash(array(klass => 'GET_PROFILE', kood => $profile['profile_id'], sisu => $profile));

				$all_profiles[] = $profile;
			}
			# save to cash info, that all profiles are loaded
			$this->cash(array(klass => 'GET_PROFILE', kood => 'LOAD_ALL', sisu => 1));

			# save all profiles info to cache
			$this->cash(array(klass => 'GET_PROFILE', kood => 'ALL_PROFILES_INFO', sisu => $all_profiles));
		}

		$cash_value = $this->cash(array(klass => "GET_PROFILE", kood => $profile_kood));
		#echo $profile_kood.": ".printr($cash_value)."<hr>";
		if($profile_kood) {
			return $cash_value;
		}
	}
    # / function get_profile
	#####################

	#####################
    # function get_default_profile_id
	#   returns default profile ID for given profile type (=source_table)
	#
	# usage : $site->get_default_profile_id(array(source_table => 'users'));
	#####################
	function get_default_profile_id() {
		$args = func_get_arg(0);
		# source_table param is required
		if(!$args['source_table']) {
			return;
		}
		$sql = $this->db->prepare("SELECT profile_id FROM object_profiles WHERE source_table=? AND is_default=?", $args['source_table'], 1);
		$sth = new SQL($sql);
		return $sth->fetchsingle();
	}
    # / function get_default_profile_id
	#####################


	#####################
	# FUNCTION transcribe
	# for turning magic_quotes_gpc to OFF inside the CMS (without using php values)
	# copied from PHP manual: http://ee.php.net/manual/en/function.get-magic-quotes-gpc.php#49612
	function transcribe($aList, $aIsTopLevel = true) {

		$gpcList = array();
		$isMagic = get_magic_quotes_gpc();

		# if PHP4 
		if(version_compare(phpversion(), "5.0.0") < 0){
			foreach ($aList as $key => $value) {
				$decodedKey = ($isMagic && !$aIsTopLevel)?stripslashes($key):$key;
				
				if (is_array($value)) {
					$decodedValue = $this->transcribe($value, false);
				} else {
					$decodedValue = ($isMagic)?stripslashes($value):$value;
				}
				$gpcList[$decodedKey] = $decodedValue;
			}
		} # PHP4
		# if PHP5
		else {
			foreach ($aList as $key => $value) {
			if (is_array($value)) {
				$decodedKey = ($isMagic && !$aIsTopLevel)?stripslashes($key):$key;
				$decodedValue = $this->transcribe($value, false);
			} else {
				$decodedKey = stripslashes($key);
				$decodedValue = ($isMagic)?stripslashes($value):$value;
			}
			$gpcList[$decodedKey] = $decodedValue;
		   }		
		} # if PHP5

		return $gpcList;		
	}
	# FUNCTION transcribe
	#####################

}
# / class
########################################
