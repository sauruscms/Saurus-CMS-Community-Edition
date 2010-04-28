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

 
global $class_path;
global $CMS_SETTINGS;
global $CMS_PARAMS;

$CMS_PARAMS = array(
	'op',
	'url',
	'uri',
	'id',
	'c_tpl',
	'tpl',
	'query',
	'otsi',
	'lang',
	'keel',
	'show',
	'bool',
	'section',
	'parent',
	'exclude',
	'time',
	'order',
	'prod_id',
	'year',
	'month',
	'lisa_alert',
);

#Get debug cookie muutuja
$debug = $_COOKIE["debug"] ? 1:0;

function show_debug()
{
	static $ips;
	
	# constant DISPLAY_ERRORS_IP came from index.php. It was taken from config table in db.
	if(DISPLAY_ERRORS_IP)
	{
		$ips = DISPLAY_ERRORS_IP;
	}
	
	if ($ips)
	{
		$d_ips = explode(';', DISPLAY_ERRORS_IP);
		foreach ($d_ips as $err_ip)
		{
			if ($_SERVER['REMOTE_ADDR'] == trim($err_ip))
			{
				return true;
			}
		}
	}
	
	return false;
}

###################################
# Error reporting is always "7"
error_reporting(7);
ini_set('display_errors', 0); // hide all errors from screen

if ($debug && show_debug()){
	ini_set('display_errors', 1);

	# exception: dont show errors during full installation procedure
	if($is_installation_script){
		ini_set('display_errors', 0);
	}
}	

/**
* saurusErrorHandler
*
* parses php-errors and saves them into DataBase, if parameter save_error_log=1 in config table
*
* @package CMS
* 
* @param - all params are set by defaults
*/

function saurusErrorHandler($errno, $errmsg, $filename, $linenum, $vars){

	$errortype = "Error";

	if ($errno == E_WARNING){
		$errortype = "Warning";
	}
	if ($errno == E_NOTICE){
		$errortype = "Notice";
	}

   if ($errno == E_WARNING){

	if (ini_get('display_errors')){
		echo "<font face=Verdana size=2><br><b>".$errortype.":</b> ".$errmsg." in <b>".$filename."</b> on line <b>".$linenum."</b><br></font>"; 
	}

		$fdat = $_POST ? $_POST : $_GET;
		if ($fdat){
			$serialized_fdat = serialize($fdat);
		}

	# Evgeny: *HARDCODED* we use here direct mysql-functions, because class DB may not be initialized jet.
	if (!defined("SAVE_ERROR_LOG")){

		$res = @mysql_query("SELECT sisu FROM config WHERE nimi='save_error_log'");
		if ($res){
			list($tmp) = @mysql_fetch_array($res);
		}
		define("SAVE_ERROR_LOG", ($tmp ? 1:0));
	}


		if (SAVE_ERROR_LOG && !substr_count($errmsg, 'mysql_num_fields')){
			@mysql_query("INSERT INTO error_log (time_of_error, source, err_text, err_type, domain, referrer, fdat_scope, ip, remote_user) VALUES (NOW(), '".addslashes($filename." line ".$linenum)."', '".addslashes($errmsg)."', 'PHP', '".addslashes($_SERVER['HTTP_HOST'])."', '".addslashes($_SERVER['REQUEST_URI'])."', '".addslashes($serialized_fdat)."', '".$_SERVER['REMOTE_ADDR']."', '".addslashes($_SERVER['REMOTE_USER'])."')");
		}
   }

}

# Redefine error handler
$old_error_handler = set_error_handler("saurusErrorHandler");



################
# cookie parameetrid
# leiame tegeliku wwwroot-i

##Kontrollime kas server jooksetab apachet v�i mitte
if(preg_match("/apache/i", $_SERVER["SERVER_SOFTWARE"]) || preg_match("/apache/i", $_SERVER["SERVER_SOFTWARE"])){
		$self = $_SERVER["REQUEST_URI"]; # kui apache
} else {
	$self = $_SERVER["SCRIPT_NAME"]; # kui muu (nt IIS)
}
if (preg_match("/^[^\?]*\//", $self, $matches)) {
	$path = $matches[0];
} else {
	$path = $self;
}
# slash l�ppu!
if (!preg_match("/\/$/",$path)) {$path .= "/"; }
# panna path cookiesse

$use_browser_cache=1;
if (substr($path, -7) == '/admin/') { $path = substr($path, 0, -6); $use_browser_cache=0;}
if (substr($path, -8) == '/editor/') { $path = substr($path, 0, -7); $use_browser_cache=0;}

# 15.12.03 Evgeny: enam ei kasuta browser cache. Teeb rohkem probleemi, kui kasu :(
if ((1 || $_COOKIE['skip_browser_cache'] || $_COOKIE['logged']) && !$_COOKIE['use_browser_cache']) {$use_browser_cache=0;}

# set session not for the entire domain (as default),
# but for the current path only:
session_set_cookie_params(0, $path);

unset($path);
# / cookie parameetrid
################

#########################
# sessiooni parameetrid

# kui sess_path ei ole muudetud failis index.php

#####################
# Classes include:
include_once($class_path."timer.class.php");
include_once($class_path.'Log.class.php');

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
# windows compatible
$absolute_path = str_replace('\\','/',$absolute_path);


####### read config.php
//$file = $absolute_path."config.php";
$file = preg_replace('/extensions\/(.*)/', '', $absolute_path).'config.php';

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
	'mysql_set_names' => $dbconf["mysql_set_names"],
));

$sql = "SELECT nimi, sisu FROM config WHERE nimi IN ('hostname','wwwroot')";
$sth = new SQL($sql);
while ($tmpconf = $sth->fetch()){
	$CMS_SETTINGS[$tmpconf['nimi']] = $tmpconf['sisu'];
}

##########################


	# 19.06.2003 Evgeny: don't need to reload page every time. 
	# Also if you submit any form, and after return back, all values in the fields are empty:
	if ($use_browser_cache){
		$max_age = 300;	# Cache expires max. after 5 minutes
		session_cache_limiter('public'); # none, public, private, nocache
	}

############## START SESSION
if (!session_id()){
	session_start();
}

if ($use_browser_cache){
	header("Pragma: public");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");;
	header("Expires: ".gmdate("D, d M Y H:i:s", time() + $max_age)." GMT");
	header("Cache-Control: public, max-age=".$max_age.", pre-check=".$max_age);
}

$admin_user = $_SESSION["admin_user"];
$admin_type = $_SESSION["admin_type"];
$ldap_params = $_SESSION["ldap_params"];

############################
# user logout

if ($_GET["op"] == 'logout' || $_POST["op"] == 'logout') {

# in ver4 new ACL
	session_unregister("user_id");
	unset($_SESSION["user_id"]);


	$url = $_GET["url"] ? $_GET["url"] : $_POST["url"];
	if (!$url) {
        $url = 'index.php';
        #bug #2883
        include_once $class_path."config.class.php";
        include_once($class_path."custom.inc.php");
        include_once $class_path."site.class.php";
        include_once $class_path."objekt.class.php";
        include_once($class_path."user.class.php");
        include_once($class_path."group.class.php");
        include_once $class_path."template.class.php";
        include_once $class_path."objekt_array.class.php";
        include_once $class_path."html.inc.php";
        include_once $class_path."leht.class.php";
        include_once($class_path.'Log.class.php');
        $site = new Site(array());

        if (($site->CONF['alias_language_format'] == 1 || $site->CONF['alias_language_format'] == 2) && $site->CONF['use_aliases']) {
            $leht = new Leht(array(
            	id => $site->alias("rub_home_id"),
            ));

            $sql1 = $site->db->prepare('SELECT site_url FROM keel WHERE keel_id = ?', $leht->objekt->all['keel']);
            $sth1 = new SQL($sql1);
            if (!($site_url = $sth1->fetchsingle())) {
                $site_url = $_SERVER['SERVER_NAME'];
            }
            $url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $site_url . $leht->objekt->get_object_href();
        }
    }

	setcookie("logged", "0", time()-36600);
	
	header("Location: ".$url); 
	exit;
}

###########################################################################
# K�ivitame see osa ainult �ks kord, kui omistatakse uus session_id
# Kontrollime igaks juhuks, et muutuja "HTTP_HOST" on olemas
# see t�hendab seda, et see skript jookseb veebi serverist, mitte k�sureast
if (!$_COOKIE[session_name()] && session_id() && $_SERVER['HTTP_HOST'] && !$include_once) {
	# Lisame uus session
	$sql = "INSERT INTO session (sess_id, update_time, user_id, url, ip) VALUES ('".addslashes(session_id())."', '".time()."', '0', '".addslashes($self)."', '".addslashes($_SERVER["REMOTE_ADDR"])."')";
	$sth = new SQL($sql);

	# Vanade sessionide kustutamine on siin
	$sql = "DELETE FROM session WHERE update_time < '".(time()-ini_get("session.gc_maxlifetime"))."'";
	$sth = new SQL($sql);
}
# //
###########################################################################

###########################################################################
# Sessioni uuendamine on siin
if ($_COOKIE[session_name()] && $_SERVER['HTTP_HOST'] && !$include_once) {
	$sql = "UPDATE session SET update_time = '".time()."', user_id = '".addslashes($_SESSION['user_id'])."', url = '".addslashes($self)."', ip = '".$_SERVER["REMOTE_ADDR"]."' WHERE sess_id = '".addslashes($_COOKIE[session_name()])."'";
	$sth = new SQL($sql);
}
# //
###########################################################################

#######################
# Baasobjekt - creating new debug and timer instances for calling objects

class BaasObjekt {
# ---------------------------------------
# p�hiobjekt millest k�ik teised tulevad
# ---------------------------------------

	var $site;
	var $debug;
	var $timer;

	function BaasObjekt() {
		$this->site = &$GLOBALS{site};
		$this->debug = new Debug();
		$this->editor_debug = new Debug();
		$this->timer = new Timer();
	} # function BaasObjekt
} # class BaasObjekt

#######################
# HTML class

class HTML extends BaasObjekt {
/*
	HTML text mis oskab enda tr�kkida,
	ja tegid [nimi] t�ida
*/
	var $source;
	
	function HTML() {
		$this->BaasObjekt();
		$this->source = func_num_args()>0 ? func_get_arg(0) : "";
		$this->debug->msg("Uus HTML Objekt loodud, teksti suurus ".strlen($this->source)." symbs");
	} #function HTML

	function Fill ($data) {		
		$this->source=preg_replace("/(\[)(.*?)(\])/e",'$data[\\2] ? $data[\\2] : "\\0"',$this->source);		
		$this->debug->msg("Filled: ".join(",",array_keys($data)));
		return join(",",array_keys($data));
	} #function Fill

	function get_text() {
	# ---------------------------------------
	# vana hea print_text'i analoog
	# ---------------------------------------
		$text = $this->source;
		//$text = preg_replace("/^(\s*<\/?p>\s*)+/i","",$text);
		//$text = preg_replace("/(\s*<\/?p>\s*)+$/i","",$text);
		return $text;
	} #function get_text

	function print_text() {
		print $this->get_text();
	} #function print_text

	function add($html) {

		$this->source .= $html;
	} #function add

} 
# / HTML class
#######################

#######################
# Timer class

include_once $class_path."timer.class.php";

#######################
# Config class
include_once $class_path."config.class.php";

	include_once($class_path."custom.inc.php");


	include_once $class_path."site.class.php";
	include_once $class_path."objekt.class.php";
	
	include_once($class_path."user.class.php");
	include_once($class_path."group.class.php");

	include_once $class_path."template.class.php";	
	include_once $class_path."objekt_array.class.php";
	include_once $class_path."html.inc.php";
	include_once $class_path."leht.class.php";
	
	include_once($class_path.'Log.class.php');

function detect_xss_in_string($string)
{
	if($string && urldecode($string) != xss_clean(urldecode($string)))
	{
		return true;
	}
	else 
	{
		return false;
	}
}

function detect_xss_in_saurus_params($variables)
{
	$checkable = array();
	
	if(!is_array($variables)) // params from url ex: op=muff&blah=156 or /saurus4/?op=muff&blah=156
	{
		if(strpos($variables, '?') !== false) $variables = substr($variables, strpos($variables, '?') + 1);
		$variables = explode('&', $variables);
		foreach($variables as $variable)
		{
			if(strpos($variable, '=') !== false)
			{
				$variable = explode('=', $variable);
				$checkable[$variable[0]] = $variable[1]; 
			}
			else 
			{
				$checkable[$variable] = null;
			}
		}
	}
	else 
	{
		$checkable = $variables;
	}
	
	global $CMS_PARAMS;
	
	foreach($checkable as $key => $value)
	{
		//printr(htmlspecialchars($key.$value));
		if(in_array(strtolower($key), $CMS_PARAMS) && detect_xss_in_string($value))
		{
			//printr(urldecode($value));
			//printr(xss_clean(urldecode($value)));
			return true;
		}
	}
	
	return false;
}
//echo (detect_xss_in_saurus_params($_SERVER['PHP_SELF']) ? 1 : 0);
if(strstr($_SERVER['REQUEST_URI'], $CMS_SETTINGS['wwwroot'].'/admin/') === false && (
	detect_xss_in_saurus_params($_SERVER['QUERY_STRING']) || 
	detect_xss_in_saurus_params($_SERVER['REQUEST_URI']) ||
	detect_xss_in_string($_SERVER['PHP_SELF']) ||
	detect_xss_in_saurus_params($_POST) ||
	detect_xss_in_saurus_params($_GET))
)
{
	header('Location: '.$CMS_SETTINGS['wwwroot'].'/index.php');
	exit;
}
//printr($_SERVER);
