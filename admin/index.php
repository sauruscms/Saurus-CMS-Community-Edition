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


#
# admin area index
# generates menus and content
#

global $site;
global $class_path;
$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");
error_reporting(7);
#Get debug cookie muutuja
$debug = $_COOKIE["debug"] ? 1:0;

$site = new Site(array(
	on_debug=>($debug ? 1 : 0),
	on_admin_keel => 1
));

if ($site->in_admin) {
	$site->security_check();
}

$op = $site->fdat[op] ? $site->fdat[op] : "admin";

// wth is this?
if ( $site->fdat[mode]=="editsysword" ) {
	if (setcookie ("mode", "editsysword",time()+15768000,"/")){
		#echo "<B>Panen cookie!</B>";
	} else {
		#echo "<FONT COLOR=red>headers already sent!</FONT>";
	}
} else if ( $site->fdat[mode]=="noeditsysword" ) {
	if (setcookie ("mode", "editsysword",time()-15768000,"/")){
		#echo "<B>Panen cookie!</B>";
	} else {
		#echo "<FONT COLOR=red>headers already sent!</FONT>";
	}
}

if ($site->user->is_superuser){

	if ($_GET["debug"]=="on") {
			setcookie ("debug", "1", 0, $site->CONF['wwwroot']."/");
			$_COOKIE["debug"] = 1;
	} else if ($_GET["debug"]=="off") {
			setcookie ("debug", "0", time()-100000, $site->CONF['wwwroot']."/");
			$_COOKIE["debug"] = 0;
	}
	$debug = $_COOKIE["debug"] ? 1:0;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>

<title><?=$site->title;?> <?=$site->cms_version;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>" />

<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/admin_index.css" />

<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/jquery.js"></script>
<script type="text/javascript">

$(document).ready(function()
{
	setContentDimensions();
	
	$(window).resize(function()
	{
		setContentDimensions();
	});
});

function setContentDimensions()
{
	// set content height
	$('iframe#admin_page_container').height($(window).height() - $('div#admin_header').height());
}

function setAdminPageTitle(args)
{
	$('#header_content h1').html(args[1]);
}
</script>

</head>
<body>

	<div id="admin_header">
		<?php print_editor_toolbar(); ?>
	
		<div id="header_content">
			<h1></h1>
			<div id="header_logo<?php echo ($site->license == 'Saurus CMS Community Edition' ? '_ce' : '_ee'); ?>"></div>
		</div>
	</div>
	
	<iframe id="admin_page_container" name="admin_page_container" frameborder="0" src="<?php echo ($op == "help" ? "help.php" : 'dashboard.php'); ?>"></iframe>
		
</body>
</html>