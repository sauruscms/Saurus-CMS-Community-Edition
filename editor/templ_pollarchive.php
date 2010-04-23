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
# TEMPLATE: Gallup archive ( ttyyp_id = 38 )
# : is called in template class
# : can also call by adding "op=gallup_arhiiv" to URL
# : is script for including
##############################

# CURRENT FILE WAS "templ_gallup_arhiiv.php" in ver 3

# keskel on:

# artiklid (0)
# artiklid (6) (kui olemas)

function print_me ($template) {

$leht = &$template->leht;
#$curr_rub_id = $leht->id;

$args[tulemuste_arv] = 10;

$curr_rub_id = $leht->site->alias("gallup_arhiiv");

$template->debug->msg("Rubriik: $curr_rub_id");

?>

<table width="<?=$template->site->dbstyle("sisu_tabeli_laius","layout")?>" height="<?=$template->site->dbstyle("sisu_tabeli_korgus","layout")?>"  border="0" cellspacing="<?=$template->site->dbstyle("sisu_tabeli_cellspacing","layout")?>" cellpadding="0">
    <tr valign="top"> 

<? 

$leht->debug->msg($leht->site->dbstyle("menyy","layout"));
?>
<td width="100%">
	<h1 class="pealkiri"><?=$template->site->sys_sona(array("sona" => $template->all["nimi"], "tyyp"=>"kujundus"))?></h1><br><hr noshade size="1" style="color:#dddddd;">

<?
	
# ---------------------------
# Gallup on siin
# ---------------------------
$alamlist_count = new Alamlist(array(
	parent => $curr_rub_id,
	klass	=> "gallup",
	on_counter => 1
));


########## 1. ONE GALLUP

if ($leht->site->fdat["gallup_id"]) {
	$obj = new Objekt(array(
		objekt_id => $leht->site->fdat["gallup_id"],
	));

} 

if (!$obj) {
	$alamlist = new Alamlist(array(
		parent => $curr_rub_id,
		klass	=> "gallup",
		start => 0,
		limit => 1,
	));
	$obj = $alamlist->next();
}

if ($obj->on_404) {
	header('Locate: '.$site->CONF['wwwroot'].'/?id='.$obj->objekt_id);
	exit;

} elseif($obj) {

	printf ("<font class=sub_pealkiri>%s</font><br>",$obj->get_edit_buttons(array(
		tyyp_idlist => "6",
		nupud => array("edit","delete"),
	)).$obj->pealkiri);

	$sql = $leht->site->db->prepare("SELECT * FROM gallup_vastus WHERE objekt_id=?",$obj->objekt_id);
	$sth = new SQL($sql);

	# tulemused
	#### 1. for MSSQL
	if(strtoupper($leht->site->CONF["dbtype"]) == 'MSSQL'){
		$sql = $leht->site->db->prepare("SELECT SUM([count]) AS kokku, MAX([count]) AS maksi FROM gallup_vastus WHERE objekt_id=?",$obj->objekt_id);
	}
	##### 2 default SQL
	else {
		$sql = $leht->site->db->prepare("SELECT SUM(count) AS kokku, MAX(count) AS maksi FROM gallup_vastus WHERE objekt_id=?",$obj->objekt_id);
	}
	$sth_c = new SQL($sql);
	$stat = $sth_c->fetch();
	$obj->debug->msg("kokku = $stat[kokku], maks = $stat[maksi]");
	$obj->debug->msg($sth_c->debug->get_msgs());
?>
		<table border=0>
			 <tr>
				 <td valign="top"><img src="<?=$obj->site->img_path ?>/px.gif" width="300" height="3"></td>
			 </tr>
<?
				while ($vastus = $sth->fetch()) {
					$percent = $stat[kokku] ? sprintf('%2.0f',100*($vastus[count])/$stat[kokku]) : 0;
?>					
			 <tr>
				 <td valign="top" class="<?=($obj->site->agent ? "txt" : "txt1")?>"><?=$vastus[vastus] ?></td>
			 </tr>
			 <tr>
				 <td valign="top"><b><font class="<?=($obj->site->agent ? "txt" : "txt1")?>">- <?=$percent ?>%</font></b> <img src="<?=$obj->site->img_path ?>/gallup_bar<?=(($stat[maksi]==$vastus[count] && $vastus[count])? "2":"1") ?>.gif" width="<?=0.01*250*$percent ?>" height=8 border="1"></td>
			 </tr>
<?
				} # while vastus

?>
			 <tr>
				 <td valign="top" class="<?=($obj->site->agent ? "txt" : "txt1")?>"><?=$leht->site->sys_sona(array(sona => "vastajaid", tyyp=>"kujundus"))?>: <b><?=$stat[kokku] ?></b></td>
			 </tr>
		 </table>
		 <br><br>
<?
}

########## 2. GALLUP LIST

$alamlistSQL = new AlamlistSQL(array(
	"parent" => $curr_rub_id,
	"klass"	=> "gallup",
));
$alamlistSQL->add_select(" DATE_FORMAT(objekt.aeg, '%d.%m.%y') as faeg ");

$alamlistSQL->debug->print_msg();
$template->debug->msg("SQL: ".$alamlistSQL->make_sql());


$alamlist = new Alamlist(array(
	alamlistSQL => $alamlistSQL,
));

$alamlist->debug->print_msg();
?>

	<table>
<?
while ($obj = $alamlist->next()) {	
?>
	<tr>
	<td class=date><?=$obj->all['faeg']?></td>
	<td><a href="?op=gallup_arhiiv&page=<?=$site->fdat['page']?>&gallup_id=<?=$obj->objekt_id?>"><?=$obj->get_edit_buttons(array("tyyp_idlist"=>6, "nupud" => array("edit","delete")))?><?=$obj->all['pealkiri']?></a></td>

	</tr>
<?
}
?>
	</table>



</td>
</tr>
</table>
<?


}
