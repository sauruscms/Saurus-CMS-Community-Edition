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
 * About popup html
 * 
 */

$class_path = "../classes/";
include($class_path."port.inc.php");

#Get debug cookie muutuja
$debug = $_COOKIE["debug"] ? 1:0;

$site = new Site(array(
	on_debug=>($debug ? 1 : 0),
	on_admin_keel => 1
));

# get version release date
$sql = $site->db->prepare("SELECT DATE_FORMAT(release_date,'%d.%m.%Y')  FROM version	WHERE version_nr=?",$site->cms_version);
$sth = new SQL($sql);
$version_date = $sth->fetchsingle();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link href="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/scms_general.css" rel="stylesheet" type="text/css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>

</head>

<body>
	
	<div style="padding:15px 30px;">
	<h2><?=$site->title?><br>
	<?=$site->cms_version?></h2>
	<?=$version_date?>
	<br><br>
	<iframe name="lic_box" src="../eula_en.html" width=100% height=604 frameborder=0></iframe>
	
	</div>
</body>

</html>