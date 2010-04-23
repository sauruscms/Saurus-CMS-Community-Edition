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
# TEMPLATE: Poll ( ttyyp_id = 30 )
# : is called in template class
# : is script for including
##############################

# CURRENT FILE WAS "templ_gallup.php" in ver 3

function print_me ($template) {

$leht = &$template->leht;
$curr_rub_id = $leht->id;
$site = &$template->site;

?>
<table width="<?=$template->site->dbstyle("sisu_tabeli_laius","layout")?>" height="<?=$template->site->dbstyle("sisu_tabeli_korgus","layout")?>"  border="0" cellspacing="<?=$template->site->dbstyle("sisu_tabeli_cellspacing","layout")?>" cellpadding="0">
<tr><td>
<?

$objekt = new Objekt(array(
	objekt_id => $site->fdat[gallup_id],
	on_sisu=>1,
));

if ($objekt && $objekt->all[on_avatud] && preg_match("/^\d+$/",$site->fdat[vastus])) {
	# gallup on korras

	######## CHECK IF VISITOR is VOTED
	# 1) IP-based gallup
	if ($site->CONF[gallup_ip_check]==1) {
		# kas kasutaja juba hääletanud?

		$sql = $site->db->prepare(
			"SELECT COUNT(gi_id) FROM gallup_ip WHERE objekt_id=? AND ip=?",
			$objekt->objekt_id, $_SERVER["REMOTE_ADDR"]
		);
		$sth = new SQL($sql);
		$template->debug->msg($sth->debug->get_msgs());
		$is_ip_ok = !$sth->fetchsingle();

	} 
	# 2) cookie based gallup
	else if ($site->CONF[gallup_ip_check]==2 && $site->cookie["gallup[".$kast->objekt_id."]"]==1) {

		$is_ip_ok = 0;
	} 
	# 3) user based gallup (only logged in users)
	else if ($site->CONF[gallup_ip_check]==3){
		$sql = $site->db->prepare(
			"SELECT COUNT(gi_id) FROM gallup_ip WHERE objekt_id=? AND user_id=?",
			$objekt->objekt_id, $site->user->user_id
		);
		$sth = new SQL($sql);
		$template->debug->msg($sth->debug->get_msgs());
		$is_ip_ok = !$sth->fetchsingle();
	} else {
		$is_ip_ok = 1;
	}
	######## / CHECK IF VISITOR is VOTED

	######## visitor CAN VOTE => UPDATE votes in DATABASE
	if ($is_ip_ok) {
		# UPDATE votes SUM
		$sql = $site->db->prepare(
			"UPDATE gallup_vastus SET count=count+1 WHERE gv_id=? AND objekt_id=?",
			$site->fdat[vastus], $objekt->objekt_id
		);
		$sth = new SQL($sql);
		$template->debug->msg($sth->debug->get_msgs());

		# ------------------------
		# Kustutame cache-ist
		# ------------------------
		clear_cache("ALL");
/*
		$artikkel = new Objekt(array(
			objekt_id => $site->alias("art_gallup_ok_id"),
			on_sisu	=> 1,
		));
		$template->debug->msg($artikkel->debug->get_msgs());
*/
		$return_ok = 1;

		# paneme kirja: IP + user_id + time + vastus(gv_id)
		$sql = $site->db->prepare(
			"INSERT INTO gallup_ip (objekt_id, ip, user_id, vote_time, gv_id) VALUES (?, ?, ?, ".$site->db->unix2db_datetime(time()).", ?)",
			$objekt->objekt_id, $_SERVER["REMOTE_ADDR"], $site->user->user_id, $site->fdat[vastus]
		);
		$sth = new SQL($sql);
		$template->debug->msg($sth->debug->get_msgs());

	} 
	######## / visitor CAN VOTE => UPDATE votes in DATABASE
	
	######## visitor is already voted => show system message "you have already voted"
	else {
		$artikkel = new Objekt(array(
			objekt_id => $site->alias("art_gallup_ip_olemas_id"),
			on_sisu	=> 1,
		));
		$template->debug->msg($artikkel->debug->get_msgs());
	}

	####### SHOW POLL
	header('Locate: '.$site->CONF['wwwroot'].'/?id='.$artikkel->objekt_id);
	exit;
}

#$site->debug->print_msg();
$site->debug->print_hash($site->CONF,1,"FDAT");
?>

<? if ($return_ok) { ?>
<script language="JavaScript">
<!--
window.location.replace('<?=$site->fdat[uri]?>');
//-->
</script>
<? } else { ?>
	<p><a href="<?=$site->fdat[uri]?>"><?=$site->sys_sona(array(sona => "Tagasi", tyyp=>"kujundus"))?></a>
<? } ?>

</td>
</tr>
</table>
<?

}
