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
* redirect foreign urls, for statistic purposes
*
* @param url the URL to redirect
*
*/

####################
# Stuff copied from index.php
preg_match('/\/(admin|editor)\//i', $_SERVER['REQUEST_URI'], $matches);

if ($matches[1]=='editor'){
	$class_path = '../classes/';
	$CMS_SETTINGS['cache_enabled'] = 0; # Cache: deny cache using for editor-area
} else {
	$class_path = './classes/';
	$CMS_SETTINGS['cache_enabled'] = 1;	 # Cache: allow using cache for user-area
	$CMS_SETTINGS['switch_lang_enabled'] = 1;
}

$debug = $_COOKIE['debug'] ? 1 : 0;

	#####################
# Classes include, only some necessary classes:
include_once($class_path."timer.class.php");
if ($debug) {
	include_once($class_path.'debug.inc.php');
} else {
	include_once($class_path.'nodebug.inc.php');
}

include_once($class_path.'config.class.php');
$absolute_path = getcwd().'/';
# strip /admin|editor|classes/ from the end
if (preg_match("/(.*)\/(admin|editor|classes|temp|test)\/$/", $absolute_path, $matches) || preg_match("/(.*)\\\(admin|editor|classes|temp|test)\/$/", $absolute_path, $matches)) {
	$absolute_path = $matches[1];
}
# add slash to the end
if (!preg_match("/\/$/",$absolute_path)) $absolute_path .= "/"; 

####### read config.php
$file = $absolute_path.'config.php';
# check if file config.php exists at all
if(!file_exists($file)) { 
	echo '<font color="red">Error: file '.$file.' not found!</font>';
	exit;
}
$fp = fopen($file, 'r');
$config = new CONFIG(fread($fp, 1024*1024));
fclose($fp);
$dbconf = $config->CONF;

#############################################
# include database independent API functions:
include_once($class_path.$dbconf['dbtype'].'.inc.php');

$DB = new DB(array(
	'host'	=> $dbconf['dbhost'],
	'port'	=> $dbconf['dbport'],
	'dbname'=> $dbconf['db'],
	'user'	=> $dbconf['user'],
	'pass'	=> $dbconf['passwd'],
	'mysql_set_names' => $dbconf["mysql_set_names"],
s));

$sql = "SELECT nimi, sisu FROM config WHERE nimi IN ('cache_expired','dont_cache_objects','kasuta_ip_filter','display_errors_ip','save_error_log','hostname','wwwroot')";
$sth = new SQL($sql);
while ($tmpconf = $sth->fetch()){
	$CMS_SETTINGS[$tmpconf['nimi']] = $tmpconf['sisu'];
}

define('DISPLAY_ERRORS_IP', $CMS_SETTINGS['display_errors_ip']);
define('SAVE_ERROR_LOG', $CMS_SETTINGS['save_error_log']);



# / stuff copied from index.php
##############################

if($_GET['url'])
{
	$url = urldecode($_GET['url']);
	//prevent Response Splitting attack
	$url = preg_replace("!\r|\n.*!s", "", $url);
	
	header('Location: '.$_GET['url']);
}
else 
{
	header('Location: index.php');
}
