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
#  objektide nihutamine

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");

#Get debug cookie muutuja
$debug = $_COOKIE["debug"] ? 1:0;

$site = new Site(array(
	on_debug=>($debug ? 1 : 0),
	on_admin_keel => 1
));

$objekt = new Objekt(array(
	objekt_id => $site->fdat[id],
	parent_id => $site->fdat[parent_id],
));

$args[asukoht] = $objekt->all[kesk];

####################################
# GET PERMISSIONS
# get object permissions for current user

$site->debug->msg("EDIT: Liigutatava objekti ".$objekt->objekt_id." õigused = ".$objekt->permission['mask']);

###########################
# ACCESS allowed/denied
# decide if accessing this page is allowed or not

# MOVE UP/DOWN: if current object has UPDATE permission => allow
if( $objekt->permission['U'] || $system_admin) {
	$access = 1;
}
else {
	$access = 0;
}

####################
# access denied
if (!$access) {
	new Log(array(
		'action' => 'update',
		'objekt_id' => $objekt->objekt_id,
		'type' => 'WARNING',
		'message' => sprintf("access denied: attempt to move %s '%s' (ID = %s)" , ucfirst(translate_en($objekt->all[klass])), $objekt->pealkiri(), $objekt->objekt_id),
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

# -------------------------------------
# Objekt leitud, küik korras
# -------------------------------------
if ($objekt) {

	if ($site->fdat[op] == "down") {
		# alla
		$site->debug->msg("DOWN");

		$vordluse_op = "<";
		$order = "desc";
#		$group_op = "max";
	
	} else {
		$site->debug->msg("UP");
		# üle

		$vordluse_op = ">";
		$order = "";
#		$group_op = "min";
	
	}

	# Millise sorteeringu väärtusega asendame?
	# (leiame lähim vüürtus üleval või all)

	$sql = $site->db->prepare("SELECT objekt.objekt_id, sorteering 
		FROM objekt 
		LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id 
		WHERE kesk=? AND parent_id=? AND sorteering $vordluse_op ? 
		ORDER BY sorteering $order
		LIMIT 0,1",
		$args['asukoht'], $objekt->parent_id, $objekt->all['sorteering']
	);

	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
	$asendusobjekt = $sth->fetch();

	if ($sth->rows>0) {
		$site->debug->msg("Vahetame, asenduobjekt id = $asendusobjekt[objekt_id]; sort = $asendusobjekt[sorteering]");
		
		# vahetame kahe objektide
		# sorteering vöörtused
		$sql = $site->db->prepare("UPDATE objekt_objekt SET sorteering=? WHERE objekt_id=?  and parent_id=?", 
			$objekt->all['sorteering'],  $asendusobjekt['objekt_id'], $objekt->parent_id
		);
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());

		$sql = $site->db->prepare("UPDATE objekt_objekt SET sorteering=? WHERE objekt_id=? and parent_id=?",
			$asendusobjekt['sorteering'], $objekt->objekt_id, $objekt->parent_id
		);
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());

		# 20.03.2003 Evgeny bugfix: kui sorteering=0
		if (!$asendusobjekt['sorteering'] || !$objekt->all['sorteering']){
			$viga['parent_id'] = $objekt->parent_id;
		}

	} else {
		# kui objekt oli kõige ülemine või alumine
		# siis paneme seda loetelu teise otsa

		$site->debug->msg("Hüppame!");
	
		# leiame kõik nihutavad objektid

		$sql = $site->db->prepare("SELECT objekt.objekt_id, sorteering 
				FROM objekt 
				LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id
				WHERE kesk=? AND parent_id=? order by objekt_objekt.sorteering $order",
			$args['asukoht'], $objekt->parent_id
		);
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());
		
		$objekt_id = array();
		$objekt_sort = array();

		while ($obj = $sth->fetch()) {
			array_push($objekt_id,$obj['objekt_id']); 
			array_push($objekt_sort,$obj['sorteering']); 
		}

		$uus_sort = $objekt_sort;
		$esimene = array_shift($uus_sort);
		array_push($uus_sort, $esimene);

		$site->debug->msg("objektid: ".join(",",$objekt_id));
		$site->debug->msg("objekt sort: ".join(",",$objekt_sort));
		$site->debug->msg("uus sort: ".join(",",$uus_sort));


		# 21.03.2003 Evgeny bugfix: ah voot kust tuli need fucking nullid!
		$back_sort = $uus_sort;
		unset($uus_sort);
		$tmp_sort = Array();
		$uus_sort = array_merge($tmp_sort, $back_sort);

# echo "Before: ".printr($back_sort)."<hr>";
#echo "After: ".printr($uus_sort)."<hr>";


		for ($i=0; $i<sizeof($objekt_id); $i++) {
			$sql = $site->db->prepare("update objekt_objekt set sorteering = ? where objekt_id =? and parent_id = ?",
				$uus_sort[$i], $objekt_id[$i], $objekt->parent_id
			);
			$sth = new SQL ($sql);
			$site->debug->msg($sth->debug->get_msgs());

			$site->debug->msg("i=".$i."; sort=".$uus_sort[$i]."; obj=".$objekt_id[$i]."; parent=".$objekt->parent_id);

				# 20.03.2003 Evgeny bugfix: kui sorteering=0
				if (!$uus_sort[$i]){
					$viga['parent_id'] = $objekt->parent_id;
				}
		}

	}
	new Log(array(
		'action' => 'update',
		'objekt_id' => $objekt->objekt_id,
		'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($objekt->all[klass])), $objekt->pealkiri(), $objekt->objekt_id, "re-sorted"),
	));

	if ($viga['parent_id']){

			$site->debug->msg("Wrong sort parameter found! Trying to fix it...");

			$sql = $site->db->prepare("SELECT objekt.objekt_id, sorteering FROM objekt 
			LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id 
			WHERE kesk=? AND parent_id=? AND sorteering='0'
			ORDER BY objekt_objekt.sorteering DESC", 
			$args['asukoht'], $viga['parent_id']);
			$sth = new SQL ($sql);
			$site->debug->msg($sth->debug->get_msgs());


			$sth2 = new SQL ("SELECT MAX(sorteering) FROM objekt_objekt");
			$site->debug->msg($sth2->debug->get_msgs());
			$max_sort = $sth2->fetchsingle();
			$super_sort = $max_sort+$sth->rows;

			while ($data = $sth->fetch()){
				$sql3 = $site->db->prepare("UPDATE objekt_objekt SET sorteering=? WHERE objekt_id=? AND sorteering=0", $super_sort, $data['objekt_id']);
				$sth3 = new SQL ($sql3);
				$site->debug->msg($sth3->debug->get_msgs());
				$super_sort--;
			}
	}



	clear_cache("ALL");

	if (!$site->on_debug) {
		header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->fdat['url']);
	}
	

} else {
?>
Wrong ID
<?
}
if($site->user) { $site->user->debug->print_msg(); }
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg();
