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
 * This script checks if the server has all the necessary Saurus CMS components.
 *
 * This is stand-alone script, no database connection or working CMS is required. 
 * This Script is also included in the:
 * - CMS installation/update script, step one
 * - CMS admin-page "System info"
 *
 *
 */

######################
# GLOBALS
global $path;
global $called_from_another_script;
global $saurus_ver;

#######################
# VERSION of Saurus CMS (3 or 4)
# Defines which directories to check
$saurus_ver = 4;

#######################
# PATH for filesystem checks
if (!isset($path)) { $path = ""; }

###########################
# HTML: if this file is not included from another script then print html 

if(!$called_from_another_script) {
	
	include('../classes/port.inc.php');
	
	$site = new Site(array(
		'on_admin_keel' => 1,
	));
	
	if (!$site->user->allowed_adminpage(array('script_name' => 'sys_info.php'))) {
		exit;
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Server requirements check</title>
</head>
<body>
<?php print_requirements_table(); ?>
</body>
</html>
<?php
}  # if this file is not included from another script

###########################
# FUNCTION print_requirements_table
function print_requirements_table() {

	global $called_from_another_script, $path, $saurus_ver;

	$CONF = read_conf(); # db connect data from config.php

	/*Assign required settings and their values*/
	$php_required_settings_to_check = array("register_globals","file_uploads","safe_mode","short_open_tag");
	$php_setting_values_required = array(0,1,0,1);

	/*Assign recommended settings and their values*/
	$php_recommended_settings_to_check = array("post_max_size","upload_max_filesize","memory_limit");
	$php_setting_values_recommended = array(16,16,16);

	/* Directories to check (see also $saurus_ver) */
	$dirs = array($path."classes/smarty/templates/", $path."classes/smarty/templates_c/", $path."classes/smarty/cache/");
	if ($saurus_ver == 3) $dirs[] = $path."failid/";
	if ($saurus_ver == 4) { $dirs[] = $path."public/"; $dirs[] = $path."shared/"; 	$dirs[] = $path."extensions/"; }

	/* Get module information from phpinfo() */
	ob_start();
	phpinfo();
	$phpinfo = ob_get_contents();
	ob_end_clean();
	/* Find out if the webserver is running PHP asi CGI or as module 

	Possible php_sapi_name() return values:

	- aolserver
	- activescript
	- apache
	- cgi-fcgi
	- cgi
	- isapi
	- nsapi
	- phttpd
	- roxen
	- java_servlet
	- thttpd
	- pi3web
	- apache2filter
	- caudium
	- apache2handler
	- tux
	- webjames
	- cli
	- embed
	- milter 

	*/

	## PHP running as CGI or FASTCGI
	if (php_sapi_name() == "cgi" || php_sapi_name() == "fcgi") {
	   $mod_rewrite = -1;	//mod_rewrite status unknown, because as CGI, phpinfo() doesn't show anything about loaded apache modules
	} else {	//if PHP is running as module (php_sapi_name() == apache)

		/* Get module information from phpinfo() */

		if (strstr($phpinfo,"mod_rewrite")) $mod_rewrite = 1;	//mod_rewrite exists

		//if $mod_rewrite doesn't exist up to this point, server is running PHP as module and it won't be shown by phpinfo()
	}
	## if ISP==Zone then "mod_rewrite = YES"  
	# (it's complicated to detect mod_rewrite module presence in PHP CGI mode)
	if($_ENV['DZSP_CP_URL'] != '') {
		$mod_rewrite = 1;	//mod_rewrite exists
	}

	/* Get PHP settings from php.ini */
	$ini = ini_get_all();

	/* Check if required and recommended settings and their value counts match */
	if (count($php_required_settings_to_check) != count($php_setting_values_required)) die(" Count of php_required_settings_to_check and php_setting_values_required do not match!");

	if (count($php_recommended_settings_to_check) != count($php_setting_values_recommended)) die(" Count of php_recommended_settings_to_check and php_setting_values_recommended do not match!");

	############ styles
	?>
	<style type="text/css">
	<!--
	/* Headline */
	<?php if(!$path) {?>
	.plk {font-size: 16px; font-family: Arial,Helvetica; color: #255AA6; font-weight: bold;}
	<?php } else { ?>
	.plk { font-family: Tahoma, Verdana, Arial, Helvetica; font-size: 14px; color: #333333; line-height: 16pt; font-weight: bold}
	<?php }?>
	/* Table */
	.scms_pane_header { font-family: Tahoma, Verdana, Helvetica; font-size: 13px; color: #FFFFFF; background-color: <?php echo($path?'#4040A9':'#255AA6')?>; font-weight: bold; padding-right: 5px; padding-left: 8px; height: 30px}
	.scms_pane_header { font-family: Tahoma, Verdana, Helvetica; font-size: 11px; color: #333333; background-color: #CCCCCC; font-weight: bold; padding-right: 5px; padding-left: 6px; height: 22px }
	.r1 { height: 22px; font-family: Tahoma, Verdana, Helvetica; font-size: 11px; color: #333333; background-color: #FFFFFF; padding-right: 5px; padding-left: 6px; line-height: 16px; vertical-align: top}
	.r2 { height: 22px; font-family: Tahoma, Verdana, Helvetica; font-size: 11px; color: #333333; background-color: #F0F0F0; padding-right: 5px; padding-left: 6px; line-height: 16px; vertical-align: top }
	-->
	</style>
	<?php############ / styles?>

	<?php##################### HTML START #################?>
	<? if(!$called_from_another_script) {?>
	<font class="plk">Server requirements</font>
	<br>
	<br>
	<?}?>

	<table border="0" cellspacing="0" cellpadding="3" width="<?=($called_from_another_script?'580':'100%')?>">
	<tr class="scms_tableheader"> 
		<td>Setting</td><td>Required value</td><td>Local value</td>
	</tr>
	<?php##################### DATABASE and WEBSERVER #################?>
	<tr class="scms_pane_header"> 
		<td nowrap colspan="4">Database and Webserver</td>
	</tr>
	<?php
	if(is_array($CONF)) { # conf file settings
		if (($conn = @mysql_connect($CONF['dbhost'].":".$CONF['dbport'],$CONF['user'],$CONF['passwd'])) && $CONF['db']) {
			if ($dbh = @mysql_select_db($CONF['db'],$conn)){
				$res = @mysql_fetch_array(@mysql_query("SELECT VERSION()"));
				$mysql_version = $res[0];
			} # dbh
		} # conn
	}

	if(!$mysql_version) { $word = 'Unknown (this value is OK during installation)'; }

	####### MYSQL version: 4.x - 5.1.35

	if(!$mysql_version || version_compare($mysql_version, "4.0") < 0  || version_compare($mysql_version, "5.1.35") > 0) {
		$color = "color='red'";
	}
	else {
		$color = "color='black'";
	}
	echo "<tr><td><font ".$color.">MySQL version</font></td><td><font ".$color.">4.x - 5.1.35</font></td><td><font ".$color.">".($mysql_version?$mysql_version:$word)."</font></td></tr>";
	$color = "color='black'";	//restore default color

	######## apache
	$tmp_arr = explode(")", $_SERVER['SERVER_SOFTWARE']);
	$webserver = $tmp_arr[0].($tmp_arr[1] ? ")":"");
	if(!stristr(strtolower($webserver), 'apache')) {
		$color = "color='red'";
	}
	echo "<tr><td><font ".$color.">Webserver software</font></td><td><font ".$color.">Apache</font></td><td><font ".$color.">".($webserver?$webserver:'Not found')."</font></td></tr>";
	$color = "color='black'";	//restore default color

	######## mod_rewrite
	if ($mod_rewrite == 1) {
		$color = "color='black'";
		$word = "Yes";

	} elseif ($mod_rewrite == -1) {
		$color = "color='red'";
		$word = "Unknown";
	} else {
		$color = "color='red'";
		$word = "No";
	}
	echo "<tr><td><font ".$color.">Apache module \"mod_rewrite\"</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";
	$color = "color='black'";	//restore default color
	?>
	<?php##################### REQUIRED PHP #################?>

	<tr class="scms_pane_header"> 
		<td nowrap colspan="4">Required PHP Settings</td>
	</tr>
	<?php
	####### PHP version: 5.0.0 - 5.2.11
	if(version_compare(phpversion(), "5.0.0") < 0 || version_compare(phpversion(), "5.2.11") > 0) {
		$color = "color='red'";
	}
	echo "<tr><td><font ".$color.">PHP version</font></td><td><font ".$color.">5.0.0 - 5.2.11</font></td><td><font ".$color.">".phpversion()."</font></td></tr>";
	$color = "color='black'";	//restore default color

	####### mysql support in php
	if (function_exists('mysql_connect')) { 
		$word = "Yes";
		$color = "color='black'";
	}
	else {
		$color = "color='red'";
		$word = "No";
	}
	echo "<tr><td><font ".$color.">MySQL Support enabled</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";
	$color = "color='black'";	//restore default color

	####### php flags/values
	reset ($php_required_settings_to_check);
	for ($i = 0; $i < count($php_required_settings_to_check); $i++) {
		//$current_setting = $php_required_settings_to_check[$i];
		if ($php_setting_values_required[$i] != (int)$ini[$php_required_settings_to_check[$i]][local_value]) $color = "color='red'";
		if ($ini[$php_required_settings_to_check[$i]][local_value] == 0) { 
			$local_value="Off"; 
		} else { 
			$local_value = "On";
		}
		if ($php_setting_values_required[$i] == 0) { 
			$required_value="Off"; 
		} else { 
			$required_value = "On";
		}
		echo "<tr><td><font ".$color.">$php_required_settings_to_check[$i]</font></td><td><font ".$color.">$required_value</font></td><td><font ".$color.">$local_value</font></td></tr>";
		$color = "color='black'";	//restore default color
	}
	/* Check for track_vars. track_vars is  always enabled since PHP 4.0.3 */
	if ( version_compare(phpversion(), "4.0.3")<0 ){
		# If version is < 4.0.3 => then check, if phpinfo has "enable-track-vars". If not, then track_vars=OFF.
		if( !strstr($phpinfo,"enable-track-vars") )  {
		$local_value= "Off";
		$color = "color='red'";
		}
		else {
		$local_value= "On";
		$color = "color='black'";
	}
	} 
	# If version >=4.0.3 => track_vars=ON
	else {
		$local_value= "On";
		$color = "color='black'";
	}
	echo "<tr><td><font ".$color.">track_vars</font></td><td><font ".$color.">On</font></td><td><font ".$color.">$local_value</font></td></tr>";

	# check for mbstring
	if( !strstr($phpinfo,"mbstring") )  { # mbstring not found
		$local_value= "No";
		$color = "color='red'";
	}
	else { # mbstring exists
		$local_value= "Yes";
		$color = "color='black'";
	}
	echo "<tr><td><font ".$color.">mbstring support</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$local_value."</font></td></tr>";

	
	
	?>

	<?php##################### RECOMMENDED PHP #################?>

	<tr class="scms_pane_header"> 
		<td nowrap colspan="4">Recommended PHP Settings</td>
	</tr>
	<?php
	reset ($php_recommended_settings_to_check);
	for ($i = 0; $i < count($php_recommended_settings_to_check); $i++) {
		echo "<tr><td>$php_recommended_settings_to_check[$i]</td><td>$php_setting_values_recommended[$i]</td><td>".(int)$ini[$php_recommended_settings_to_check[$i]][local_value]."</td></tr>";
		$color = "color='black'";	//restore default color
	}

	?>

	<?php##################### GD library #################?>

	<tr class="scms_pane_header"> 
		<td nowrap colspan="4">GD library</td>
	</tr>
	<?php

	######## GD
	#$gdinfo = gd_info();
	#printr($gdinfo['GD Version']);
	if (function_exists('imagetypes') && (ImageTypes() & IMG_PNG)) {
		$color = "color='black'";
		$word = "Yes";
	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: PNG Support</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";

	if (function_exists('imagetypes') && (ImageTypes() & IMG_GIF)) {
		$color = "color='black'";
		$word = "Yes";
	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: GIF Support</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";

	if (function_exists('imagetypes') && (ImageTypes() & IMG_JPG)) {
		$color = "color='black'";
		$word = "Yes";
	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: JPG Support</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";

	if (function_exists('imagetypes') && (ImageTypes() & IMG_JPEG)) {
		$color = "color='black'";
		$word = "Yes";
	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: JPEG Support</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";

	if (function_exists('imagetypes') && (ImageTypes() & IMG_WBMP)) {
		$color = "color='black'";
		$word = "Yes";
	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: WBMP Support</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";



	### GD functions:

	if (function_exists('getimagesize')) {
		$color = "color='black'";
		$word = "Yes";

	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: PHP function \"getimagesize\"</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";

	if (function_exists('imageCreateTrueColor')) {
		$color = "color='black'";
		$word = "Yes";

	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: PHP function \"imageCreateTrueColor\"</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";

	if (function_exists('imagecreatefromjpeg')) {
		$color = "color='black'";
		$word = "Yes";

	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: PHP function \"imagecreatefromjpeg\"</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";

	if (function_exists('imagecopyresampled')) {
		$color = "color='black'";
		$word = "Yes";

	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: PHP function \"imagecopyresampled\"</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";

	if (function_exists('imagesx')) {
		$color = "color='black'";
		$word = "Yes";

	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: PHP function \"imagesx\"</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";

	if (function_exists('imagesy')) {
		$color = "color='black'";
		$word = "Yes";

	} else {
		$color = "color='red'";
		$word = "No";
	}
	$i++;
	echo "<tr><td><font ".$color.">GD: PHP function \"imagesy\"</font></td><td><font ".$color.">Yes</font></td><td><font ".$color.">".$word."</font></td></tr>";
	?>


	<?php##################### FILESYSTEM #################?>

	<tr class="scms_pane_header"> 
		<td nowrap colspan="4">Filesystem permissions</td>
	</tr>

	<?php
	foreach ($dirs as $value) {
		# check if exists
		if (file_exists($value)) {
			if (is_writeable($value)) {
				$color = "color='black'";
				$word = "Writable";
			} else {
				$color = "color='red'";
				$word = "Not writable";
			}
		} # exists
		else {
			$color = "color='red'";
			$word = "Not found";
		} # exists
		$i++;
	   echo "<tr><td><font ".$color.">".$value."</font></td><td><font ".$color.">Writable</font></td><td><font ".$color.">".$word."</font></td></tr>";
	}
	?>
	</table>
	<?php
}
# / FUNCTION print_requirements_table
###########################
###########################
# FUNCTION read_conf
function read_conf() {

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

	if(file_exists($file)) { 
		$fp = fopen($file, "r");
		if($fp) {
			$buffer = fread($fp, 1024*1024);
			foreach (split("[\n\r]+",$buffer) as $line) {
				if (preg_match('/^\s*([^#=]+?)\s*=\s*([^#]+)\s*(?:#.*)?$/',$line,$matches)) {
					$CONF[trim($matches[1])] = trim($matches[2]);
				}
			}
			fclose($fp);
		}
		else { echo "Error: unable to open config.php for read"; } 
	}
	return $CONF;
}
# / FUNCTION read_conf
###########################
