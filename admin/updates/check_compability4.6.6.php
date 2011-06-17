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
 * Script checks the statistics module is in use and warns the user
 * 
 * This is stand-alone script, should be copied to the website root directory. 
 * This Script is also included in the:
 * - CMS installation/update script, step one
 *
 * @package CMS
 * @author Saurus development team, http://www.saurus.info/
 * @copyright 2002-2010 Saurused O�.
 * 
 * 
 */

######################
# GLOBALS
global $called_from_another_script;
global $site;
global $class_path;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";

include_once($class_path."port.inc.php");

$site = new Site(array(
	'on_debug' => 0,
));

###########################
# HTML: if this file is not included from another script then print html 

if(!$called_from_another_script) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Check compability for version 4.6.6</title>
</head>
<body>
<?php check_compability_466(); ?>
</body>
</html>
<?php
}  # if this file is not included from another script

###########################
# FUNCTION check_compability
function check_compability_466() {

	global $called_from_another_script, $class_path, $site;

	$errors = array();
	
	// removed modules
	// stats
	$errors[] = 'Warning: Statistics module is deprecated since version 4.6.6. This update will remove collected data from the database. If you wish to maintain it, please make a backup of your database before continuing.';
	
	// resources
	$errors[] = 'Warning: The Resources module is deprecated since version 4.6.6.';
	
	// e-shop
	$errors[] = 'Warning: The E-commerce module is deprecated since version 4.6.6.. This update will remove E-commerce data from the database.';
	
	// removed v3 PHP templates
	$sql = 'select objekt_id from objekt where (page_ttyyp_id < 100 and page_ttyyp_id <> 0) or (ttyyp_id < 100 and ttyyp_id <> 0)';
	$result = new SQL($sql);
	if($result->rows)
	{
		$objects = array();
		
		while($row = $result->fetchsingle())
		{
			$objects[] = $row;
		}
		
		$errors[] = 'Warning: It seems that objects with these ID-s are using deprecated PHP-based templates from version 3: '.implode(',', $objects).'. This update will attempt to make use of Smarty-based templates but it might be that you have to adjust your site CSS styles afterwards.';
	}
	
	$sql = 'select nimi from keel where (page_ttyyp_id < 100 and page_ttyyp_id <> 0) or (ttyyp_id < 100 and ttyyp_id <> 0)';
	$result = new SQL($sql);
	if($result->rows)
	{
		$sites = array();
		
		while($row = $result->fetchsingle())
		{
			$sites[] = $row;
		}
		
		$errors[] = 'Warning: Version 3 built-in PHP templates are now longer available but are set as default in sites: '.implode(',', $sites).'.';
	}
	
	if(sizeof($errors)>0) {
		echo '<font color=red>';
		print (join('<br>',$errors));

		echo '</font>';
		echo '<br><br>';
	}
	## all OK
	else {
		if(!$called_from_another_script){
			echo "Compability check passed OK. Sleep peacefully.<br><br>";
		}
	}

}
# / FUNCTION check_compability
###########################
