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


################################################################
#	This file loaded into hidden frame, located in file edit.php
#	It updates check in time for cmw objekt in the database.

global $class_path;
$class_path = "../classes/";

$fdat = $_GET;

$seconds_to_reload = 110; // must be little less, than in file edit.php (at moment it's 2 min = 120 sec)
$url_to_reload = "checkin.php?objekt_id=".$fdat['objekt_id']."&nocache=".time();



if (is_numeric($fdat['objekt_id'])){

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
		'mysql_set_names' => $dbconf["mysql_set_names"],
	));	

		$sql = "UPDATE objekt SET check_in=NOW() WHERE objekt_id='".addslashes($fdat['objekt_id'])."'";
		$sth = new SQL($sql);

# Hidden feature:
if ($fdat['unlock']){
		$sql = "UPDATE objekt SET check_in=0 WHERE objekt_id='".addslashes($fdat['objekt_id'])."'";
		$sth = new SQL($sql);
		echo "<b>Object unlocked!</b>";
} else {
?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>CHECK IN/OUT</title>
<META HTTP-EQUIV="refresh" content="<?=$seconds_to_reload ?>; <?=$url_to_reload ?>">
<script language="JavaScript">
<!--
function checkInReload(){
	window.location = '<?=$url_to_reload ?>';
}
$seconds_to_reload = <?=$seconds_to_reload ?>;
setTimeout("checkInReload()", (1000*$seconds_to_reload));
//-->
</script>
</head>

<body>
nocache=<?=time() ?>

</body>
</html>

<? 
} // if !unlock	
} // if fdat[objekt_id] 