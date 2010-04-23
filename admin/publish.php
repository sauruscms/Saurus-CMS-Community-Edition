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


# DESCRIPTION
#  objektide avaldamine


global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");

$debug = $_COOKIE["debug"] ? 1:0;

$site = new Site(array(
	on_debug=>$debug,
	on_admin_keel => 1
));

$objekt = new Objekt(array(
	objekt_id => $site->fdat[id],
	no_cache => 1
));

	# kui objektil on rohkem, kui 1 parent, siis loodame objekti uuesti uue parentiga:
	if ($objekt->all['parents_count']>1 && $objekt->parent_id!=$site->fdat['parent_id']){
		$site->debug->msg("Leidsin mitu parenti (".$objekt->all['parents_count']."). Kasutan parent_id=".$site->fdat['parent_id']);
		unset($objekt); 
		$objekt = new Objekt(array(
			objekt_id => $site->fdat['id'],
			parent_id => $site->fdat['parent_id'],
			no_cache =>1,
		));	
	}


# -------------------------------------
# Objekt leitud
# -------------------------------------
if ($objekt) {


###########################
# ACCESS allowed/denied
# decide if accessing this page is allowed or not

# PUBLISH: if current object has PUBLISH permission => allow
if( $objekt->permission['P']) {
	$access = 1;
}
else {
	$access = 0;
}

	####################
	# access denied
	if (!$access) {
		new Log(array(
			'action' => ($site->fdat['op'] == 'publish' ? 'publish' : 'hide'),
			'type' => 'WARNING',
			'objekt_id' => $objekt->objekt_id,
			'message' => sprintf("access denied: attempt to ".($site->fdat[op] == "publish"?'publish':'hide')." %s '%s' (ID = %s)" , ucfirst(translate_en($objekt->all[klass])), $objekt->pealkiri(), $objekt->objekt_id),
		));
		print "<center><b><font class=\"txt\">".$site->sys_sona(array(sona => "access denied", tyyp=>"editor"))."</font></b></center>";
		if($site->user) { $site->user->debug->print_msg(); }
		if($site->guest) { 	$site->guest->debug->print_msg(); }
		$site->debug->print_msg();
		########### EXIT
		exit;
	}
# / ACCESS allowed/denied
###########################


###########################
# GO ON with real work


		if ($site->fdat[op] == "publish") {
			$on_avaldatud=1;
		} else {
			$on_avaldatud=0;
		}

		$sql = $site->db->prepare("update objekt set on_avaldatud=? where objekt_id=?", $on_avaldatud, $objekt->objekt_id);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		
		# Run mailinglists after each publishing
		if ($site->CONF['maillist_sending_after_publishing'] && $site->fdat['op'] == "publish") {
			require_once($class_path."auto.inc.php");
			auto_maillist(0,0);
		}
		
		# ------------------------
		# Kustutame chache-ist
		# ------------------------
		clear_cache("ALL");

		if (!$site->on_debug) {
			header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF[hostname].$site->fdat[url]);
		}

		new Log(array(
			'action' => ($on_avaldatud ? 'publish' : 'hide'),
			'type' => 'WARNING',
			'objekt_id' => $objekt->objekt_id,
			'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($objekt->all[klass])), $objekt->pealkiri(), $objekt->objekt_id, $on_avaldatud ? "published" : "hidden"),
		));
	} else {
	?>
	Wrong ID
	<?
}
if($site->user) { $site->user->debug->print_msg(); }
if($site->guest) { 	$site->guest->debug->print_msg(); }

$site->debug->print_msg();