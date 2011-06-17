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


##############################
# Main page
# : is independent script, not for including, new Site is generated
##############################

error_reporting(7);

global $site;
global $class_path;
global $CMS_SETTINGS;
global $forceRedirect;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);

if ($matches[1]=="editor"){
	$class_path = "../classes/";
	$CMS_SETTINGS['cache_enabled'] = 0; # Cache: deny cache using for editor-area
} else {
	$class_path = "./classes/";
	$CMS_SETTINGS['cache_enabled'] = 1;	 # Cache: allow using cache for user-area
	$CMS_SETTINGS['switch_lang_enabled'] = 1;
	
	//ini_set('session.cache_limiter', 'private'); // cant be done, messes up reg users view and log in ifno is not available
}

#################################################
# Debug cookies
if ($_GET['debug'] == 'on')
{
		setcookie ('debug', '1');
		$_COOKIE['debug'] = 1;
}
else if ($_GET['debug'] == 'off')
{
		setcookie ('debug', '0', time()-100000);
		$_COOKIE['debug'] = 0;
}

$debug = $_COOKIE["debug"] ? 1:0;

################################
# Cache: URL parameter "speed_debug=on" displayes cache and speed info
if ($_GET["speed_debug"]=="on") {
	setcookie ("speed_debug", "1");
	$_COOKIE["speed_debug"] = 1;
} else if ($_GET["speed_debug"]=="off") {
	setcookie ("speed_debug", "0", time()-100000);
	$_COOKIE["speed_debug"] = 0;
}
$speed_debug = $_COOKIE["speed_debug"] ? 1:0;


####################################################################################
# BEGIN: Cache related stuff, PART 1

function mygetmicrotime(){ 
 list($usec, $sec) = explode(" ",microtime()); 
 return ((float)$usec + (float)$sec); 
} 
$startaeg = mygetmicrotime();

if ($CMS_SETTINGS['cache_enabled']) { # if we are using cache => go on
	########################
	# FUNCTION get_active_lang
	# get active lang from session or from db; returns extension string; used for homepage
	#	$keel_ext = get_active_lang();
	function get_active_lang(){
		Global $CMS_SETTINGS, $DB, $site;

		# a) at first search keel from session
		if(isset($_SESSION['keel'])) {
			#echo "FOUND SESSION KEEL:".$_SESSION['keel']['keel_id'];
			$sql = $DB->prepare("SELECT extension FROM keel WHERE keel_id=?",$_SESSION['keel']['keel_id']);
			$sth = new SQL($sql);
			$keel_ext = $sth->fetchsingle();
		}
		# b) if not extension found at this point (either no session set or faulty value set) 
		# then get default keel
		if(!$keel_ext){
			$sql = $DB->prepare("SELECT extension FROM keel WHERE on_default=1");
			$sth = new SQL($sql);
			$keel_ext = $sth->fetchsingle();
		}
		return $keel_ext;
	}

	########################
	# FUNCTION save_to_cache
	# cache saving is done at the end of the page:
	#	save_to_cache(Array(
	#		'data' => $cache_data,
	#		'objekt_id' => $_GET['id'], # oldstyle
	#		'url' => $site->fullself
	#	));
	function save_to_cache(){
		Global $CMS_SETTINGS, $DB, $site;

		$args = func_get_arg(0);
		$url = $args['url'];

		$nocache_ids = Array();
		$nocache_ids = split(',', preg_replace("/\s+/","",$CMS_SETTINGS['dont_cache_objects']));

		$CMS_SETTINGS['cache_inserted']=1; # inserted or not, mark as inserted anyway

		########## SAVE if:
		# - setting 'cache_not_found' is true (khm, what's that?)
		# - parameter "data" found (is smth to save)
		# - if POST values doesn't exist

		if ($CMS_SETTINGS['cache_not_found'] && strlen($args['data'])>0 && !count($_POST)){

			### 1. if parameter "objekt_id" (== URL parameter "id") found => 
			# check if it's not in conf array 'dont_cache_objects'. 
			if( is_numeric($_GET['id']) && in_array($_GET['id'], $nocache_ids) ) {
				# If found, dont save cache and exit function
				return;
			}
			### 2. if NO URL PARAMETERS found OR URL parameter "id" not found => then we have page where we dont know language info,
			# do some extra logic: add parameter "lang" automatically to the end of URL
			if(!count($_GET) || !is_numeric($_GET['id']) && !isset($_GET['lang'])  ) {
				$keel_ext = get_active_lang(); # get keel from session or from db
				## homepage
				if(!count($_GET)) {
					$url .= "?lang=".$keel_ext; # assign new URL		
					
					// don't cache homepage (ID:0)
					if(in_array(0, $nocache_ids))
					{
						return;
					}
				}
				## parameters found in URL
				else {
					$url .= "&lang=".$keel_ext; # assign new URL						
				}
			}
			
			// don't cache homepage (ID:0)
			if(count($_GET) == 1 && $_GET['lang'] && in_array(0, $nocache_ids))
			{
				return;
			}

			$sql = $DB->prepare("INSERT INTO cache (aeg, sisu, objekt_id, url, site_id) VALUES (NOW(),?,?,?,".(is_numeric($args['site_id']) ? (int)$args['site_id'] : 'NULL').")", $args['data'], $args['objekt_id'], $url);
			$sth = new SQL($sql);

			$CMS_SETTINGS['cache_inserted']=2; # put '2', if actually inserted (for debug)

		} # save cache to table
	}
	# / FUNCTION save_to_cache
	########################

	#####################
	# Classes include, only some necessary classes:
	include_once($class_path."timer.class.php");

	if ($debug) {
		include_once($class_path."debug.inc.php");
	} else {
		include_once($class_path."nodebug.inc.php");
	}
	include_once($class_path."config.class.php");

	#####################
	# Read config-file:
	######## get absolute path of website root
	$absolute_path = getcwd().'/';
	# strip /admin|editor|classes/ from the end
	if (preg_match("/(.*)\/(admin|editor|classes|temp|test)\/$/", $absolute_path, $matches) || preg_match("/(.*)\\\(admin|editor|classes|temp|test)\/$/", $absolute_path, $matches)) {
		$absolute_path = $matches[1];
	}
	# add slash to the end
	if (!preg_match("/\/$/",$absolute_path)) {$absolute_path .= "/"; }

	####### read config.php
	$file = $absolute_path."config.php";
	# check if file config.php exists at all
	if(!file_exists($file)) { 
		print "<font color=red>Error: file \"$file\" not found!</font>";
		exit;
	}
	$fp = fopen($file, "r");
	$config = new CONFIG(fread($fp, 1024*1024));
	fclose($fp);
	$dbconf = $config->CONF;

	#############################################
	# include database independent API functions:
	include_once($class_path.$dbconf["dbtype"].".inc.php");

	$DB = new DB(array(
		host	=> $dbconf["dbhost"],
		port	=> $dbconf["dbport"],
		dbname	=> $dbconf["db"],
		user	=> $dbconf["user"],
		pass	=> $dbconf["passwd"],
		'mysql_set_names'	=> $dbconf["mysql_set_names"],
	));
	if ($DB->error) { # Bug #2468
		print "<font face=\"arial, verdana\" color=red>Error! Can't connect to database!</font>";
		exit;
	}

	$sql = "SELECT nimi, sisu FROM config WHERE nimi IN ('cache_expired','dont_cache_objects','kasuta_ip_filter','display_errors_ip','save_error_log','hostname','wwwroot')";
	$sth = new SQL($sql);
	while ($tmpconf = $sth->fetch()){
		$CMS_SETTINGS[$tmpconf['nimi']] = $tmpconf['sisu'];
	}

	define('DISPLAY_ERRORS_IP', $CMS_SETTINGS['display_errors_ip']);
	define('SAVE_ERROR_LOG', $CMS_SETTINGS['save_error_log']);

	session_start();

	####################################
	# USE CACHE, if:
	# if allowed in config
	# AND if no POST values found

	# Cached page is saved into variable "$cache_data"

	if (!count($_POST) && $CMS_SETTINGS['cache_expired']){

		############ DISABLE CACHE, if:
		# - we are in the after-login page (cookie "logged")
		# - in the debug mode
		# - if IP filter is used for users (1) or for both admin and users (3)
		# - if no referer found (khm, why?)  removed: " || !$_SERVER['HTTP_REFERER']"
		# - if user is logged in (#2676)

		if ($_COOKIE["logged"]==1 || $debug || ($CMS_SETTINGS['kasuta_ip_filter']==1 || $CMS_SETTINGS['kasuta_ip_filter']==3) || $_SESSION['user_id']) {
				$CMS_SETTINGS['cache_enabled'] = 0;
			}

		if ($CMS_SETTINGS['cache_enabled']){ # we are actually using cache
		#echo "<br><b>trying to use the cache</b><br>";

			$url = $_SERVER["REQUEST_URI"];

			### if NO URL PARAMETERS found OR URL parameter "id" not found => then we have page where we dont know language info,
			# do some extra logic: add parameter "lang" automatically to the end of URL
			if(!count($_GET) || !is_numeric($_GET['id']) && !isset($_GET['lang']) ) {
				$keel_ext = get_active_lang(); # get keel from session or from db

				## homepage
				if(!count($_GET)) {
					$url .= "?lang=".$keel_ext; # assign new URL		
				}
				## parameters found in URL
				else {
					$url .= "&lang=".$keel_ext; # assign new URL						
				}
			}
			elseif (isset($_GET['lang']))
			{
				$sql = $DB->prepare("SELECT keel_id, encoding, extension, locale, glossary_id FROM keel WHERE extension=? LIMIT 1", $_GET['lang']);
				$sth = new SQL($sql);
				$_SESSION['keel'] = $sth->fetch();
			}
			# Bug #2407: Cache moodulis saab teha SQL injectionit
			$sql = "SELECT aeg, sisu FROM cache WHERE url='".mysql_escape_string($url)."';";
			$sth = new SQL($sql);
			$cache = $sth->fetch();
			$cache_data = $cache['sisu'];

			if (1 && $sth->rows){
				$CMS_SETTINGS['cache_found'] = 1;
			} else {			
				$CMS_SETTINGS['cache_not_found'] = 1;
			};

		} # we are actually using cache
	} # we try to use cache
}  // if ($CMS_SETTINGS['cache_enabled'])

# Check (at the start of cms-engine) language of the given object and send it forward into $fdat[keel]
# -if cached data not found
# -if parameter "lang" or "keel" not found
# -if parameter "id" or "pg" found (GRR FIX IT - IT's BUG! 'pg' is in configuration and changable)

if ($CMS_SETTINGS['switch_lang_enabled'] && !$cache_data && !$_GET['lang'] && !$_GET['keel'] && (is_numeric($_GET['id']) || is_numeric($_GET['pg']))){
	$myid = $_GET['id'] ? $_GET['id'] : $_GET['pg'];
	$sql = "SELECT keel.extension FROM objekt LEFT JOIN keel ON keel.keel_id=objekt.keel WHERE objekt_id='".$myid."'";
	$sth = new SQL($sql);
	$mykeel = $sth->fetchsingle();
	if ($mykeel){
# merle kommenteeris v�lja bug #2398:
#		$_GET['keel'] = $mykeel;
	}
}


##########################
# SHOW cached page or START SAVING cache info

######### 1. SHOW CACHED HTML
# If Cache was found, return screen-data from cache:
if ($CMS_SETTINGS['cache_found']){
	echo $cache_data;
	$cache_msg = " <b>Cache used!</b>";
} else {


if (!$CMS_SETTINGS['cache_inserted'] && $CMS_SETTINGS['cache_expired']){
	ob_start();
}

# / END: Cache related stuff, PART 1
####################################################################################


#####################################
# Here will be content of site:

include_once($class_path."port.inc.php");

#################################################
# error_reporting
error_reporting(7);

#################################################
# create site and page
$site = new Site(array(
	on_debug=>($debug ? 1 : 0),

));


######### PHP memory limit
# sets the maximum amount of memory in CONF["php_memory_limit"] Mbytes 
# that a script is allowed to allocate
# if general value is smaller
if ( intval(ini_get('memory_limit')) < intval($site->CONF["php_memory_limit"]) ) {
	ini_set ( "memory_limit", $site->CONF["php_memory_limit"]."M" );
}


# Kui keegi sisestas mitte numbriline id:
if ($site->fdat['id'] && !is_numeric($site->fdat['id'])){$site->fdat['id']=$site->alias("404error");}

if ( $site->fdat[rep_id]>0 ) {
	$sql = $site->db->prepare("
		SELECT objekt_id FROM objekt WHERE related_objekt_id = ?",
		$site->fdat[rep_id]
	);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
	$site->fdat[id] = $sth->rows ? $sth->fetchsingle() : $site->alias("404error");
}
# if not admin tries to query system section (using URL or smth)
# show 404 error page instead
# WHY was it commented out before??
if ($site->fdat[id] == $site->alias("system") && !$site->in_editor) {
	$site->fdat[id] = $site->alias("404error");
}
# create page
$leht = new Leht(array(
	id => $site->fdat[id] ? $site->fdat[id] : $site->alias($site->sys_alias ? $site->sys_alias : 'rub_home_id'),
));
# / create site and page
#################################################

#################################################
# redirect to alias

if ($leht->objekt && $site->CONF['use_aliases'] && $site->CONF['redirect_to_alias'] && ($_SERVER['PHP_SELF'] != $site->wwwroot . '/map.php' || $forceRedirect) && !$site->in_editor) {

	$variable = array();

	foreach ($_GET as $k=>$v) {
		if ($k != 'mod_rewrite' && $k != 'cmd') {
			if (!($k == 'id' && is_numeric($v)) && !($k == 'lang' && $v != '')) {
				$variable[] = $k . '=' . $v;
			}
		}
	}

	if (is_array($variable) && sizeof($variable) > 0) {
		$qs = '?' . implode('&', $variable);
	}

	$sql1 = $site->db->prepare('SELECT site_url FROM keel WHERE keel_id = ?', $leht->objekt->all['keel']);
	$sth1 = new SQL($sql1);
	if (!($site_url = $sth1->fetchsingle())) {
		$site_url = $site->CONF['hostname'];
	}
	
	$cmdArray = explode('/', $_GET['cmd']);
	while (end($cmdArray) == '' && count($cmdArray) > 0) {
		array_pop($cmdArray);
	}

	$aliasArray = explode('/', $leht->objekt->get_object_href());
	while (end($aliasArray) == '' && count($aliasArray) > 0) {
		array_pop($aliasArray);
	}

	// compare alias and redirect cmd. do not redirect if they are the same to prevent endless redirecting
	if ((end($cmdArray) != end($aliasArray) || !is_numeric(end($cmdArray))) && !(count($cmdArray) == 0 && !$_GET['id'])) {
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $site_url . $leht->objekt->get_object_href() . $qs);
	}
}

#
#################################################


#################################################
# find all parents
# m��ratakse kas objekt on avaldatud v�i mitte parentite p�hjal
if (!$leht->parents) {exit;}

$leht->level = 0;
$i = -1;
$myobj = $leht->parents->get($i++);

// get permssions
$perm = get_obj_permission(array(
	"objekt_id" => $leht->id,
));

if (is_object($myobj)) {
	while ( $myobj->objekt_id > 0 && $i<100 ){
		# if trying to open existing, but not published object JA kui useril POLE �igust n�ha avaldamata objekte, then show 404 error page
		if ($myobj->on_avaldatud == 0 && !$site->in_editor) {

			$site->fdat['id'] = $site->alias("404error");
			# If sys_artikkel 404error puudub, siis viskame esilehele (varem naitas tyhi ekraan):
			if (!$site->fdat['id']){
				header("Refresh: 0;url=".$site->CONF['wwwroot']."/?404"); // param ?404 - ainult infoks
				exit;
			}
			$leht = new Leht(array(
				id => $site->alias("404error"),
			));
			$site->debug->msg("Attempt to open existing, but not published object (ID ".$myobj->objekt_id.") => show 404 error page");
		}
		if (is_object($leht->parents)){
			$myobj = $leht->parents->get($i++);
		} else {
			$i=101;
			$site->fdat['id'] = $site->alias("404error");
			# If sys_artikkel 404error puudub, siis viskame esilehele (varem naitas tyhi ekraan):
			if (!$site->fdat['id']){
				header("Refresh: 0;url=".$site->CONF['wwwroot']."/?404"); // param ?404 - ainult infoks
				exit;
			}
			$leht = new Leht(array(
				id => $site->alias("404error"),
			));
		}
		# level-i leidmisel arvestakse ainult rubriikidega
		if($myobj->all[klass] == "rubriik"){
			$leht->level++;
		}
	}
	$leht->level -= 1;
}
// kui on olemasolev objekt permissionid aga ei ole avaldatud bug #2740
elseif($perm['R'] && !$perm['is_visible'])
{
	// 404
	$site->fdat['id'] = $site->alias("404error");
	# If sys_artikkel 404error puudub, siis viskame esilehele (varem naitas tyhi ekraan):
	if (!$site->fdat['id']){
		header("Refresh: 0;url=".$site->CONF['wwwroot']."/?404"); // param ?404 - ainult infoks
		exit;
	}
	$leht = new Leht(array(
		id => $site->alias("404error"),
	));
	$site->debug->msg("Attempt to open existing, but not published object (ID ".$myobj->objekt_id.") => show 404 error page");
}
# / find parents
#################################################


#################################################
# for error page send correct 404 header
if( $leht->objekt->on_404 ) {
	if (isset($_ENV['REDIRECT_STATUS'])) { // compatible with php CGI-mode
		header ("Status: 404 Not found");
	} else {
		header ("HTTP/1.1 404 Not found");
	}
}
# for error page send correct 404 header
#################################################

#################################################
#  Save Bookmark

if($site->fdat['op'] == "bookmark" && is_numeric($site->fdat['id'])) {
	$site->user->toggle_favorite(array(
				objekt_id => $site->fdat['id']
			));
	$site->user->load_favorites(true);
}

#################################################
# put to cookie "current_section"

if ($leht->objekt->all["klass"] == "rubriik") {
	# current section 
	$myparent = $leht->parents->get(0);
#	$leht->site->sess_save(array(current_section => $myparent->objekt_id));
}
else {
	# parent section
	$myparent = $leht->parents->get(1);

#	$leht->site->sess_save(array(current_section => $myparent->objekt_id));
}

# POOLELI - bossu arvestamine v�ga madalas objektipuus
#print "minu boss on: ".$myparent->all[pealkiri]." (".$myparent->objekt_id.")";

if($myparent->all['klass'] == 'rubriik'){ # set cookie only if we have section
	if (setcookie("current_section", $myparent->objekt_id, time()+1800, $site->CONF['wwwroot'] . "/")) {
		$site->debug->msg("Put to cookies current_section: ".$myparent->objekt_id);
	} else {
		$site->debug->msg("CAN'T put to cookies current_section: ".$myparent->objekt_id."; possible reason - headers already have been sent");
	}
	#print "current section is:".$myparent->objekt_id. ' (class:'.$myparent->all['klass'].')';
}	

# / put to cookie "current_section"
#################################################


#################################################
# syswords changing mode

if ( $site->fdat[mode]=="editsysword" ) {
	if (setcookie ("mode", "editsysword","","/")){
		#echo "<B>Panen cookie!</B>";
	} else {
		#echo "<FONT COLOR=red>headers already sent!</FONT>";
	}
} else if ( $site->fdat[mode]=="noeditsysword" ) {
	if (setcookie ("mode", "editsysword",time()-100000,"/")){
		#echo "<B>Panen cookie!</B>";
	} else {
		#echo "<FONT COLOR=red>headers already sent!</FONT>";
	}
}
# / syswords changing mode
#################################################

#################################################
# Gallup_id=1 cookie-sse:

if ($site->fdat[op]=="vote" && preg_match("/^\d+$/",$site->fdat[vastus]) && preg_match("/^\d+$/",$site->fdat[gallup_id]) && $site->CONF[gallup_ip_check]==2){
			if (setcookie ("gallup[".$site->fdat[gallup_id]."]", "1", time()+15768000)){
				#echo "<B>Panen cookie!</B>";
			} else {
				#echo "<FONT COLOR=red>headers already sent!</FONT>";
			}
}
# / Gallup_id=1 cookie-sse:
#################################################

#### kontrolli kas install.php on kustutatud:
if ($site->in_editor || $site->in_admin) {
	$site->security_check();
}

$site->debug->print_hash($site->fdat,0,"FDAT");

$REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];

# featuur! Kui kasutaja klikib otsele m�nele keelatud lingile,
# mis n�uab sisselogimist, siis n�idata
# sisselogimise akent. Kui kasutaja on sisselogitud ja
# kui objekt on peidetud ja piiratud kasutajale
# siis n�idata 404 error page
##############################################################
####### check permissions

$perm = get_obj_permission(array(
	"objekt_id" => $leht->id,
));
# kas useril on �igus objekti n�ha? 1/0
if (!$perm['R'] && !$leht->site->in_editor) {
	if ($leht->site->user) {
# POOLELI in ver 4
#		header("Location: ".$site->CONF['protocol'].$site->CONF['hostname'].$site->CONF['wwwroot']."?id=".$site->alias("404error"));
	} else {
		$leht->site->fdat[op] = "";
		include_once($class_path."login_html.inc.php");
		admin_login_form(array("site" => $site, "auth_error" => 0));
	}
}

#$leht->parents->debug->print_msg();
#$leht->topmeny->debug->print_msg();
#$site->debug->print_hash($site->fdat,1,"FDAT");
#$leht->debug->print_msg();

##############################
#  create template 

$template = new Template($leht);


###########################
# 1. CONTENT TEMPLATE, kui master template on SAPI template

# tr�kkida: admin-header & page-html (kas parenti oma v�i master) & content-html

if(!$template->on_page_templ && $site->master_tpl['ttyyp_id']) {

		#######################
		# hoiame meeles sisumalli - see kutsutakse hiljem v�lja smarty tag-iga {print_content}

		$content_template = $template;
		$leht->content_template = &$content_template;

		#######################
		# 1. kui sisumall on m�tteliselt rubriigiga seotud
		# (op = <empty>/ arhiiv) AND (op=<empty> AND otsi<>'') - v�listada otsingutulemuste mall
		# siis kasutada �mbritsevaks malliks esimese parent-rubriigi malli

		if(($site->fdat[op] == '' || $site->fdat[op] == 'arhiiv')
			&& !$site->fdat[otsi]) {

			#######################
			# hakka otsima �mbritsevat page malli:
			# k�igepealt vaadata, kas aktiivsel rubriigil endal on m��ratud page-mall
			# kui on, siis v�tta see page-malliks

			# kui rubriik, vaata ise-ennast
			if($leht->objekt->all['klass'] == 'rubriik') {
				$i = 0;
			} 
			# kui muu objekt, otsi parent rubriik
			else { 
				$i = 1;
			}
			$par = $leht->parents->get($i);
			$page_ttyyp_id = $par->all[page_ttyyp_id];

			#### kui parent on artikkel (e vaadatakse alamartiklit), 
			# siis leia �lem-artikli parent RUBRIIK ja v�tta selle mall (bug #966)
			if ($par->all[tyyp_id] == 2){
				$par = $leht->parents->get(2);
				$page_ttyyp_id = $par->all[page_ttyyp_id];
				# kui endiselt on parent artikkel => j�tka
				if ($par->all[tyyp_id] == 2){
					$par = $leht->parents->get(3);
					$page_ttyyp_id = $par->all[page_ttyyp_id];
				}
			}

			# Evgeny: et n�idata ekraanil s�steem-artiklid, otsime esialgu s�steemrubriigi mall, 
			# ja kui see defineeritud, siis kasutame seda:
			if ($leht->objekt->all['klass']=='artikkel' && $leht->objekt->all['parent_id']==$site->alias("system")){

				# leia s�steemirubriigi objekt
				$system_obj = new Objekt(array(
					objekt_id => $site->alias("system")
				));

				# kui s�steemirubriigil on mall m��ratud, siis kasuta seda �mbritsevaks malliks
				if ($system_obj->all['page_ttyyp_id']){
					$page_ttyyp_id = $system_obj->all['page_ttyyp_id'];

					$template->debug->msg("Page mall: S�steemi artikkel on fookuses. S�steemi rubriigil on defineeritud mall ja me kasutame seda: ID=".$page_ttyyp_id);
				}			 
			};

			
			# kui ei objektile endal ei olenud page-malli m��ratud,
			# siis kasutada saidi p�himalli 
			if(!$page_ttyyp_id) {
				$page_ttyyp_id = $site->master_tpl['ttyyp_id'];

				$template->debug->msg("Page mall: Objekti enda page-mall oli t�hi, kasutan saidi p�himalli: ID=".$page_ttyyp_id);
			}
			else {
				$template->debug->msg("Page mall: Kasutan objekti enda page-malli: ID=".$page_ttyyp_id. ", Parent ID=".$par->objekt_id);
			}

			# erijuht: kui objekt on teema v�i kommentaar (foorum)
			# JA mall on ette antud URL-il (&tpl=..),
			# siis mitte panna page-malliks �sjaleitud malli, sest mall antakse ette malli html-is
			if ($leht->objekt->all[klass] == "teema" && $site->fdat[tpl]) {
				$template->debug->msg("Page mall: Kuna tegu teemaga ja kasutatud URL-il 'tpl' parameetrit, ignoreerin leitud page-malli");
			}
			# tavajuht:
			else {

				##########################
				# CONTENT mall debug
				# enne page malli tegemist printida content malli debug v�lja	
				$template->debug->print_msg();

				###########################
				# TEE PAGE mall

				# POOLELI: igaks juhuks kontrollida, et �mbritsev mall poleks tegelikult content tyypi //

				# kui mall on d�naamiline (id >= 1000), siis j�tka ja tee mall ID p�hjal
				if ($page_ttyyp_id >= 1000 ) {
					$template  = new Template($leht, '', $page_ttyyp_id);
				}

				# kui mall on mingil p�hjusel fix mall,
				# siis ignoreerida fix-malli ja kasutada �mbriseks home d�n.malli
				else {

					$template  = new Template($leht, '', $site->master_tpl['ttyyp_id']);
					$template->debug->msg("Page mall: Kuna parenti mall oli fix-sisumall (ID=".$page_ttyyp_id."), ignoreerisin seda ja kasutasin saidi p�himalli: ".$template->fail);
				}
				# / TEE PAGE mall
				###########################

			} # if objekti t��p on teema v�i mingi muu
		}
		#######################
		# 2. kui sisumall on �ldine eritemplate 
		# op = tappisotsing/ kaart/ error/ register/ gallup-arhiiv
		# siis kasutada �mbritsevaks malliks master malli
		else if ( $site->fdat["op"] != 'print'){

			#####################
			# erijuht: aga kui URLi peal on mall ette antud  (&tpl=<malli ID>),
			# siis miks mitte kasutada seda lehemallina :)
			if($site->fdat[tpl] != '' && is_numeric($site->fdat[tpl])) {
				$page_ttyyp_id = $site->fdat[tpl];
	
				$template->debug->msg("Page mall: Kuna mall on URL-is antud, siis kasutan seda malli: ID=".$page_ttyyp_id);
			
			}

			####################
			# tavajuht: kasuta erimallide jaoks p�himalli
			else {
				$page_ttyyp_id = $site->master_tpl['ttyyp_id'];

				$template->debug->msg("Page mall: Kuna sisumall on erimall, kasutan saidi p�himalli: ID=".$page_ttyyp_id);
			}

			##########################
			# CONTENT mall debug
			# enne uue malli tegemist printida vana malli debug v�lja	
			$template->debug->print_msg();

			###########################
			# TEE PAGE mall

			# tee mall ID p�hjal
			$template  = new Template($leht, '', $page_ttyyp_id);

			# / TEE PAGE mall
			###########################

		}


		#######################
		# 3. kui sisumall on print template 
		# op = print
		# siis tr�kkida default html-head, sest stiile on ju vaja
		else {
		?>
			<html>
			<head>
			 <title><?=$leht->meta[title] ?></title>
			  <meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
			  <meta http-equiv="Cache-Control" content="no-cache">
			  <meta name="Author" content="SAURUS - www.saurus.info">
			  <meta name="keywords" content="<?=$leht->meta[keywords] ?>">
			  <meta name="description" content="<?=$leht->meta[description] ?>">
			  <link rel="stylesheet" href="<?=$leht->site->CONF[wwwroot] ?>/styles.php">

		<?
		}

		# / kui sisumall on ..
		#######################

		# =========================
		#   template
		# =========================

		$template->print_text();
}

# / 1. CONTENT TEMPLATE, kui master template on SAPI template
###########################

###########################
# 2. PAGE TEMPLATE (html)

# page mall, siis tr�kkida: # admin-header & html

else {

	# =========================
	#   template
	# =========================

	$template->print_text();
}
# / 2. PAGE TEMPLATE (html)
###################

###########################
# debug info

$site->debug->msg("SQL p�ringute arv = ".$site->db->sql_count."; aeg = ".$site->db->sql_aeg);

$site->debug->msg("T��AEG = ".$site->timer->get_aeg());


if(is_object($template)){
	$template->debug->print_msg();
}

if($content_template){
	$content_template->debug->print_msg();
}
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }

$site->debug->print_msg();

#$menyy->debug->print_msg();

$leht->parents->debug->print_msg();
#$leht->debug->print_msg();


$site->db->debug->print_msg();

# / debug info
###########################

# /	Here will be content of site
#####################################

####################################################################################
# BEGIN: Cache related stuff, PART 2

# Hidden feature: remove object from cache, if 'nocache' parameter found in URL
if ($site->fdat['nocache'] && is_numeric($site->fdat['id'])){
	$sql = $site->db->prepare("DELETE FROM cache WHERE objekt_id=?", $site->fdat['id']);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
}

##############################################
# If vead on nahtav, siis ei salvesta cache!

if (!$CMS_SETTINGS['cache_inserted'] && $CMS_SETTINGS['cache_expired'] && $CMS_SETTINGS['cache_enabled'] /*&& is_numeric($_GET['id'])*/){
	$cache_data = ob_get_contents();
	ob_end_clean();
	if ($cache_data){ # if found smth to save
		save_to_cache(Array(
			'data' => $cache_data,
			'objekt_id' => $_GET['id'], # oldstyle, optional
			'url' => $site->fullself,
			'site_id' => $leht->objekt->all['keel'],
		));
	}
}

############## PRINT CACHE to screen:
	echo $cache_data;
	

} # / 2. OR START saving page info

# / SHOW cached page or START SAVING cache info
##########################


##########################
# SHOW "speed_debug" message

if ($speed_debug){
	$loppaeg = mygetmicrotime();
	$itog = $loppaeg-$startaeg;

	
	$sth = new SQL("SELECT count(*) FROM cache");
	$total_in_cache = $sth->fetchsingle();

	echo "<hr size=1> <center><font face=Verdana size=2 color=black>Page was generated in ".number_format($itog, 5, ".", " ")." seconds. ".$cache_msg.($CMS_SETTINGS['cache_inserted']==2 ? "<b>Saved to cache!</b>":"");
	echo (( ($CMS_SETTINGS['kasuta_ip_filter']==1 || $CMS_SETTINGS['kasuta_ip_filter']==3) && $CMS_SETTINGS['cache_expired']) ? "<br><b>IP Filter for users enabled. Cache skipped!</b>":"");
	echo "<br>Total cached objects: ".$total_in_cache."; Queries: ".($site->db->sql_count-1)."</font></center>"; // (sql_count-1), because 1 query used for speed_debug
	flush();
}
# /	END: Cache related stuff, PART 2
####################################################################################
