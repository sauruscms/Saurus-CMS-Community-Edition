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

global $site;

global $class_path;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";

include_once($class_path."port.inc.php");

$site = new Site(array(
	on_debug=>0
));


/*---------------------------	Code Begin	------------------------------------------*/

################# 1. GENEREERI UUESTI objekti STRIP-väljad (Bug #1568)

$sql = $site->db->prepare("SELECT objekt_id, lyhi, sisu FROM obj_artikkel");
$sth = new SQL ($sql);
if($debug) { print "<br>".$sql; }
if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font><br>"; }

while($rec = $sth->fetch()){
	$objekt_id = $rec['objekt_id'];
	$lyhi = $rec['lyhi'];
	$sisu = $rec['sisu'];

	###################
	# strip HTML tags from lyhi, sisu for strip-fields

	$sisu_strip = $lyhi." ".$sisu;

	$replace_tags_arr = array("<br>", "<BR>", "<br />", "<BR />", "&nbsp;");
	# replace some tags with space before stripping tags (bug #1568 )
	$sisu_strip = str_replace($replace_tags_arr, " ",$sisu_strip);

	$replace_tags_arr = array("&amp;");
	$sisu_strip = str_replace($replace_tags_arr, "&",$sisu_strip);

	$sisu_strip = strip_tags($sisu_strip);

#	print("<br>".$objekt_id);


	$sql2 = $site->db->prepare("UPDATE objekt SET sisu_strip=? WHERE objekt_id= ?",
		$sisu_strip,
		$objekt_id
	);
	$sth2 = new SQL ($sql2);

#	print("<br>".htmlspecialchars($sql2)."<br>");
}
################# / 1. GENEREERI UUESTI objekti STRIP-väljad (Bug #1568)


/*---------------------------	Code End	------------------------------------------*/

if ($site->on_debug){

	$site->debug->msg("SQL päringute arv = ".$site->db->sql_count."; aeg = ".$site->db->sql_aeg);
	$site->debug->msg("TÖÖAEG = ".$site->timer->get_aeg());
#	$site->debug->print_msg();

}
?>