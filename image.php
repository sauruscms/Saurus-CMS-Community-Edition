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
 * @package   SaurusCMS
 * @copyright 2000-2010 Saurused Ltd (http://www.saurus.info/)
 * @license   Mozilla Public License 1.1 (http://www.opensource.org/licenses/mozilla1.1.php)
 * 
 */


##############################
# Prints picture object as stand-alone html
# : is usually value for image source (<img src="image.php?555t" ..>)
# : is independent script, not for including, new Site is generated
##############################

# CURRENT FILE WAS pilt.php" in ver 3

global $site;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";
include($class_path."port.inc.php");


# otsitakse id-d enne "t"-d
# nt image.php?555t => id = 555
if (preg_match("/^(\d+)(t)?.*?$/", $_SERVER['QUERY_STRING'], $matches)) {
	$id = $matches[1];
	$is_thumb = $matches[2];
	$_GET['id'] = $id;
} elseif ( $_GET['id'] ) {
	$id = $_GET['id'];
}

$site = new Site(array(
	on_debug=>0
));

$objekt = new Objekt(array(
	objekt_id => $id,
	on_sisu=>1,
));

if (($objekt->all[klass]=="pilt" && ($objekt->on_avaldatud || $site->admin!=0) )) {

	$ctype = $objekt->all[mime_tyyp] ? $objekt->all[mime_tyyp] : "application/saurus";

	header("Content-Type: $ctype");

	$sql = $site->db->prepare("select * from obj_pilt WHERE objekt_id = ?",$objekt->objekt_id);
	$sth = new SQL($sql);
	$data = $sth->fetch();
	$site->debug->msg($sth->debug->get_msgs());

	if ($is_thumb) {
		$body = $data[vaike_blob];
		
	} else {
		$body = $data[sisu_blob];
	}
	print $body;

} else {
	header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF[hostname].$site->CONF[wwwroot].($site->admin?"/editor":"")."/?op=error&error_id=404");
}
$site->debug->print_msg();