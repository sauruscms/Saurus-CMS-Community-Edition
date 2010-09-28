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
# Generates style section for html in main page
# : styles are taken from database
# : generates all content as style file
# : is called from main page (index.php)
# : is called from some stand-alone html pages were necessary (external.php, kalender.php, etc)
# : is independent script, not for including, new Site is generated
##############################

error_reporting(7);

global $site;
global $class_path;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";

if($matches[1] == "editor" || $matches[1] == "admin") $is_admin = 1; else $is_admin = 0;

		#####################
		# Classes include:

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


	# get config from db
	$sql = "SELECT nimi, sisu FROM config WHERE nimi IN ('styles_path', 'wwwroot')";
	$sth = new SQL($sql);
	while ($tmpconf = $sth->fetch()){
		$CMS_CONF[$tmpconf['nimi']] = $tmpconf['sisu'];
	}
	
	session_start();

	############### 1. STYLES IN DATABASE
	############ styles stuff start

	# Bug #1844: write correct header (to avoid JS warnings)
	header('Content-type: text/css');

	############ EDITOR-BUTTONS: import styles for wonder-button
	# Bug #1499: styles.php @import rpeab olema esimene rida CSSis
	# Bug #1844: include always (was e only for editor and admin-area)

	#### get correct wwwroot (copied from site.class.php, bug #2193 + #1439)
	$self = $_SERVER["REQUEST_URI"]; # kui apache
	# failinimi lõpust maha
	if (preg_match("/^[^\?]*\//", $self, $matches)) {
		$path = $matches[0];
	} else {
		$path = $self;
	}
	# slash lõppu!
	if (!preg_match("/\/$/",$path)) {$path .= "/"; }
	$wwwroot = $path;
	# võtame admin/ ja editor/ osa maha
	$re = '/'.preg_replace("/\//","\\\/", '(editor|admin|classes|temp)/.*$').'/i';
	$wwwroot = preg_replace($re, "", $wwwroot);
	# slash lõpust maha!
	if (preg_match("/\/$/",$wwwroot)) {	$wwwroot = substr($wwwroot,0,-1); }


	if($_GET['with_wysiwyg'] != 1) {
		print "@import url(\"".$wwwroot.$CMS_CONF['styles_path']."/scms_dropdown.css\"); \n ";
	}


	########### CSS
	$sql = "SELECT * FROM css WHERE is_active = 1;";  # Bug #2294
	$sth = new SQL($sql);
	while ($style = $sth->fetch()) {
		$css[$style['name']] = $style['data'];
	} # while

	if($_GET['with_wysiwyg'] != 1) {
		echo "\n\n/***********************************************************\n Custom CSS: \n***********************************************************/\n\n";
		echo $css['custom_css'];
		echo "\n";
	}

	if($_GET['with_wysiwyg'] == 1) {
		echo "\n\n/***********************************************************\n WYSIWYG GENERAL CSS:\n***********************************************************/\n\n";
		echo $css['wysiwyg_css_general']."\n";
?>
/* Flowplayer */
.SCMS__FlashVideo {
	display: block;
	border:#a9a9a9 1px solid;
	background-position:center center;
	background-image:url(js/fckeditor/editor/skins/scms/images/flowplayer.png);
	background-repeat:no-repeat;
	width:60px;
	height:41px;
}
/* / Flowplayer */
<?php
		echo "\n\n/***********************************************************\n WYSIWYG FONTS CSS:\n***********************************************************/\n\n";
		echo $css['wysiwyg_css']."\n";
		echo "\n";
		echo "\n/***********************************************************\n Editor lead:\n***********************************************************/\n\n";
		
		?>

editor\:lead {
	display: block;
	height: 15px;
	background: url(<?=$wwwroot.'/admin/images/lead.gif';?>);
}
		<?php
	}

############### / 1. STYLES IN DATABASE

############### TOOLBAR STYLES
# only if user is logged in
if($_SESSION['user_id'] && !$_GET['with_wysiwyg'])
{
?>
/***********************************************************
 Toolbar styles:
***********************************************************/

div#scms_editor_toolbar {
	position: absolute;
	z-index: 2147483642;
	top: 0px;
	left: 0px;
	margin: 0px;
	padding: 0px;
	height: 28px;
	width: 100%;
	font-family: Tahoma, Verdana, sans-serif;
	font-size: 12px;
	line-height: 14px;
	color: #fefefe;
	background: url(<?php echo $wwwroot.$CMS_CONF['styles_path']; ?>/gfx/general/white_pixel.gif) repeat-x left bottom;
}

ul.scms_editor_dropdown {
	margin: 0px 0px 0px 38px;
	padding: 0px;
	line-height: 14px;
	border: none;
}

ul.scms_editor_dropdown li {
	float: left;
	margin: 0px;
	padding: 0px;
	list-style: none;
	text-align: left;
	line-height: 14px;
	border-width: 1px;
	_border-width: 0px;
	border-color: transparent;
	border-style: solid;
	border-bottom: none;
	background: transparent;
}

ul.scms_editor_dropdown li a {
	color: #fefefe;
	text-decoration: none;
	display: block;
	padding: 6px 9px 6px 9px;
	margin: 0px;
	font-weight: normal;
	line-height: 14px;
	font-family: Tahoma, Verdana, sans-serif;
	font-size: 12px;
	border: none;
	background-color: transparent;
	outline: 0;	
}

ul.scms_editor_dropdown li.onmouseover {
	background-color: #808b9f;
	border-right-color:#6D7788;
	border-left-color:#6D7788;
}

ul.scms_editor_dropdown li.onmouseover a,
ul.scms_editor_dropdown li.onmouseover a:hover {
	border-bottom: 1px solid #808b9f;
	background: #808b9f;
}

ul.scms_editor_dropdown li a {
	white-space: nowrap;
}

ul.scms_editor_dropdown li ul {
	margin: 0px 0px 0px -1px;
	padding: 5px 10px 2px 0px;
	display: none;
	position: absolute;
	margin-top: 0px;
	background-color: #808b9f;
	border-width: 1px;
	border-color: #6D7788;
	border-style: solid;
	border-top: none;
}

ul.scms_editor_dropdown li ul li {
	margin: 0px;
	padding: 0px;
	float: none;
	border-color: #808b9f;
}

ul.scms_editor_dropdown li ul li a {
	padding: 0px 9px 6px 9px;
	background: #808b9f;
	text-align: left;
}

ul.scms_editor_dropdown li ul li a:hover {
	text-decoration: underline;
	margin: 0px;
}

a#scms_editor_toolbar_logo {
	margin: 0px;
	padding: 6px 9px 6px 9px;
	display: block;
	background: url(<?php echo $wwwroot.$CMS_CONF['styles_path']; ?>/gfx/general/toolbar_logo.gif) no-repeat left top;
	position: absolute;
	top: 0px;
	left: 0px;
	width: 28px;
	height: 14px;
	cursor: default;
	border: none;
	outline: 0; /* no border when clicked in FF */
}

ul#site_links {
	float: right;
	margin: 0px;
	margin-right: 15px;
	padding: 0px;
	border: none;
}

ul#toolbar_tools {
	float: right;
	margin: 0px;
	margin-right: 15px;
	padding: 0px;
	border: none;
}

ul#toolbar_tools li {
	float: left;
	margin: 0px;
	list-style: none;
	border-width: 1px;
	_border-width: 0px;
	border-color: transparent;
	border-style: solid;
	border-bottom: none;
	padding: 6px 3px 6px 3px;
	line-height: 14px;
	font-family: Tahoma, Verdana, sans-serif;
	font-size: 12px;
	background-color: transparent;
	background: transparent;
	color: #fefefe;
}

ul#toolbar_tools li.separator {
	color: #5F6F7F;
	padding-left: 1px;
	padding-right: 1px;
}

ul#toolbar_tools li a {
	color: #fefefe;
	text-decoration: none;
	font-weight: normal;
	text-decoration: none;
	font-weight: normal;
	line-height: 14px;
	font-family: Tahoma, Verdana, sans-serif;
	font-size: 12px;
	border: none;
	background-color: transparent;
	margin: 0px;
	padding: 0px;
	outline: 0;	
}

ul#toolbar_tools li a strong {
	text-decoration: none;
	font-weight: bold;
	border: none;
	line-height: 14px;
	font-family: Tahoma, Verdana, sans-serif;
	font-size: 12px;
	background-color: transparent;
	color: #fefefe;
	margin: 0px;
	padding: 0px;
}

ul#toolbar_tools li a:hover,
ul#toolbar_tools li a:hover strong {
	text-decoration: underline;
}

ul#toolbar_tools li#toolbar_tools_username {
	color: #fefefe;
	font-weight: bold;
	padding-left: 13px;
	background: url(<?php echo $wwwroot.$CMS_CONF['styles_path']; ?>/gfx/general/toolbar_usericon.gif) 0px 9px no-repeat;
}

/* ----------------------------------------------------------------------------------------------------------------*/
/* ---------->>> thickbox specific link and font settings <<<------------------------------------------------------*/
/* ----------------------------------------------------------------------------------------------------------------*/
#TB_window {
	font: 12px Tahoma,Verdana,sans-serif;
}

#TB_secondLine {
	font: 10px Tahoma,Verdana,sans-serif;
	color:#666666;
}

#TB_window a {
		color: #fff;
		text-decoration: none;
		font-weight: bold;
		font-size: 12px;
		padding: 4px 6px 3px 7px;
		margin-right: -6px;
}

/* ----------------------------------------------------------------------------------------------------------------*/
/* ---------->>> thickbox settings <<<-----------------------------------------------------------------------------*/
/* ----------------------------------------------------------------------------------------------------------------*/
#TB_overlay {
	position: fixed;
	z-index: 2147483644;
	top: 0px;
	left: 0px;
	height:100%;
	width:100%;
}

.TB_overlayMacFFBGHack {background: url(<?php echo $wwwroot.$CMS_CONF['styles_path']; ?>/gfx/general/thickbox_mac_bg.png) repeat;}
.TB_overlayBG {
	background-color: #C2D7E0;
	filter:alpha(opacity=85);
	-moz-opacity: 0.85;
	opacity: 0.85;
}

* html #TB_overlay { /* ie6 hack */
     position: absolute;
     height: expression(document.body.scrollHeight > document.body.offsetHeight ? document.body.scrollHeight : document.body.offsetHeight + 'px');
}

#TB_window {
	position: fixed;
	background: #ffffff;
	z-index: 2147483645;
	color:#000000;
	display:none;
	border: 1px solid #999;
	text-align:left;
	top:50%;
	left:50%;
}

* html #TB_window { /* ie6 hack */
position: absolute;
margin-top: expression(0 - parseInt(this.offsetHeight / 2) + (TBWindowMargin = document.documentElement && document.documentElement.scrollTop || document.body.scrollTop) + 'px');
}

#TB_window img#TB_Image {
	display:block;
	margin: 15px 0 0 15px;
	border-right: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
	border-top: 1px solid #666;
	border-left: 1px solid #666;
}

#TB_caption{
	height:25px;
	padding:7px 30px 10px 25px;
	float:left;
}

#TB_closeWindow{
	height:25px;
	padding:11px 25px 10px 0;
	float:right;
}

#TB_closeAjaxWindow {
	padding: 22px 21px 0px 0px;
	margin: 0px;
	text-align:right;
	float:right;
}

#TB_closeAjaxWindow a#TB_closeWindowButton {
	height: 24px;
	width: 24px;
	margin: 0px;
	padding: 2px;
	display: block;
	background: url(<?php echo $wwwroot.$CMS_CONF['styles_path']; ?>/gfx/general/thickbox_close.gif) 2px 2px no-repeat;
	border: none;
}

#TB_closeAjaxWindow a#TB_closeWindowButton:hover {
	background: url(<?php echo $wwwroot.$CMS_CONF['styles_path']; ?>/gfx/general/thickbox_close_hover.gif) 2px 2px no-repeat;
}

#TB_ajaxWindowTitle{
	float:left;
	padding: 8px 0px 0px 0px;
	margin: 11px 30px;
	color: #333333;
	font: normal 24px "Trebuchet MS";
	letter-spacing: -1px;
	display: inline; /* IE double margin fix */
	text-shadow: 1px 1px 0 rgba(255, 255, 255, 1);
}

#TB_title{
	background-color: #f4f4f4;

	background: url(<?php echo $wwwroot.$CMS_CONF['styles_path']; ?>/gfx/general/tools_head_bg.jpg) repeat-x;
	height: 63px;
	margin: 0px;
	padding: 0px;
}

#TB_ajaxContent{
	clear:both;
	padding:2px 15px 15px 15px;
	overflow:auto;
	text-align:left;
	line-height:1.4em;
}

#TB_ajaxContent.TB_modal{
	padding:15px;
}

#TB_ajaxContent p{
	padding:5px 0px 5px 0px;
}

#TB_load{
	position: fixed;
	display:none;
	height:13px;
	width:208px;
	z-index: 2147483646; /* one below maximum */
	top: 50%;
	left: 50%;
	margin: -6px 0 0 -104px; /* -height/2 0 0 -width/2 */
}

* html #TB_load { /* ie6 hack */
position: absolute;
margin-top: expression(0 - parseInt(this.offsetHeight / 2) + (TBWindowMargin = document.documentElement && document.documentElement.scrollTop || document.body.scrollTop) + 'px');
}

#TB_HideSelect{
	z-index: 2147483643;
	position:fixed;
	top: 0;
	left: 0;
	background-color:#fff;
	border:none;
	filter:alpha(opacity=0);
	-moz-opacity: 0;
	opacity: 0;
	height:100%;
	width:100%;
}

* html #TB_HideSelect { /* ie6 hack */
     position: absolute;
     height: expression(document.body.scrollHeight > document.body.offsetHeight ? document.body.scrollHeight : document.body.offsetHeight + 'px');
}

#TB_iframeContent{
	clear:both;
	border:none;
	margin-bottom:-1px;
	margin-top:1px;
	_margin-bottom:1px;
}
<?php
} // toolbar styles
?>
	
/* editor:lead is deprecated since 4.6.4 */
editor\:lead {
	display: block;
	height: 15px;
	background: url(<?=$wwwroot.'/admin/images/lead.gif';?>);
}

hr.scms_lead_body_separator {
	height: 15px;
	background: url(<?=$wwwroot.'/admin/images/lead.gif';?>);
	border: 0px solid black;
	width: 100%;
}
