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



global $site;
global $class_path;

if(!isset($class_path)) {
	$class_path = "../classes/";
}

include_once($class_path."port.inc.php");

$site = new Site(array(
	on_debug=>1,
	on_admin_keel => 1
));


########### SUPERUSER CHECK:
if(!$site->user->is_superuser) {
	echo $site->sys_sona(array(sona => "Permission denied", tyyp=>"editor"));
	exit;
}


$objekti_arv = 0;

########### TYPES to repair
$types_arr = array();
if(isset($site->fdat['type'])) {
	$types_arr = $site->fdat['type'];
}
# all types are selcted by default
else {
	$types_arr = array(
		'objects',
		'permissions',
		'users',
		'mailinglists',
		'sso',
		'polls',
		'favorites',
		'systemwords',
		'files'
	);
}

#printr($types_arr);

#####################
# HTML
?>

<html>
<head>
<title>Repair Database</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>

<body style="overflow:auto">
<?php ######## FORM ?>
<form id="repair" name="repair" method="get">
<input type="hidden" id="run" name="run" value="0">

<table border="0" cellspacing="0" cellpadding="3" width="100%">
<TD class="scms_toolbar">
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr> 
		<?php ###### repair database button ######?>
				<TD nowrap><a href="javascript:document.getElementById('run').value='<?=(!$site->fdat['run']?'1':'0')?>';document.forms['repair'].submit();"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/exec.png" WIDTH="16" HEIGHT="16" BORDER="0" align=absmiddle><?php if(!$site->fdat['run']){ echo  '&nbsp;Repair database'?></a><?php } else { echo '&nbsp;Database repaired'; }?></a></TD>	
		<?php ###### refreshe button ######?>
				<TD nowrap><a href="javascript:document.getElementById('run').value='0';document.forms['repair'].submit();" class="scms_button_img"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/refresh.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>&nbsp;Refresh</a></TD>

				
				<?php ###### wide middle cell ######?>
		<td width="100%"></td>

		</tr>

      </table>
</TD>
<tr> 
<td>
	&nbsp;&nbsp;<a href="#objects">1. Objects</a><br>
	&nbsp;&nbsp;<a href="#permissions">3. Permissions</a><br>
	&nbsp;&nbsp;<a href="#users">4. Users, groups, roles</a><br>
	&nbsp;&nbsp;<a href="#mailinglists">5. Mailinglists</a><br>
	&nbsp;&nbsp;<a href="#polls">7. Polls</a><br>
	&nbsp;&nbsp;<a href="#favorites">9. Favorites</a><br>
	&nbsp;&nbsp;<a href="#systemwords">11. System words</a><br>
	&nbsp;&nbsp;<a href="#files">12. Files</a><br>
	&nbsp;&nbsp;<a href="#aliases">13. Friendly URLs (Aliases)</a><br>
</td>
</tr>
</table>



<table border="0" cellspacing="2" cellpadding="3" width="100%">
<tr class="scms_tableheader"> 
	<td nowrap>ID</td>
	<td nowrap>Error</td>
	<td nowrap>Extra</td>
	<td nowrap>Table</td>
</tr>

<?php
#################################### OBJECTS ######################################
?>
	<?php $type = 'objects'; ?>
	<tr class='scms_pane_header'> 	
	<td colspan=4><a name='<?=$type?>'></a><input type="checkbox" id="type_<?=$type?>" name="type[]" value="<?=$type?>" <?=(in_array($type,$types_arr)?'checked':'')?>><label for="type_<?=$type?>"><?=ucfirst($type)?></label></td>
	</tr>
<?php 
# if type selected
if(in_array($type,$types_arr)){

/*----------------------------------------------------
# Vigased objektid: otsida v�lja k�ik objektid, 
# millel parentid vigased ja kustutada
-----------------------------------------------------*/

$sql = "
	SELECT objekt_objekt.parent_id, objekt_objekt.objekt_id, objekt.objekt_id AS tmp , objekt.pealkiri
	FROM objekt_objekt
	LEFT JOIN objekt on objekt.objekt_id = objekt_objekt.parent_id
	WHERE parent_id!='0' 
	HAVING isnull(tmp)
	";
$sthdel = new SQL($sql);
$objekti_arv += $sthdel->rows;

##################
# sql
if (!$site->fdat['run']){
	echo "
	<tr> 	
	<td><b>object: parent set, but parent object not found</b><br>".$sql."</td>
	</tr>
	";
}
while ($tmp = $sthdel->fetch() ) {

	$objekt = new Objekt(array(
		objekt_id => $tmp[objekt_id],
		no_cache => 1,
		skip_sanity_check => 1,
	));

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[objekt_id]."&nbsp;&nbsp;&nbsp;".$tmp['pealkiri'].($objekt->objekt_id ? "": ": delete skipped, unable to create object")."</td>
		<td nowrap>Faulty parent object</td>
		<td nowrap>".$alam_obj."&nbsp;</td>
		<td nowrap>&nbsp;</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		if($objekt->objekt_id) {
			$objekt->del();
		}
	}
}

/*----------------------------------------------------
# Vigased objektid: otsida v�lja k�ik objektid, 
# millel parentid vigased ja kustutada
-----------------------------------------------------*/

$sql = "
	SELECT objekt_objekt.parent_id, objekt_objekt.objekt_id, objekt.objekt_id AS tmp , objekt.pealkiri
	FROM objekt_objekt 
	LEFT JOIN objekt on objekt.objekt_id = objekt_objekt.parent_id 
	WHERE parent_id!='0'  
	GROUP BY parent_id
	HAVING isnull(tmp)
	";
$sthdel = new SQL($sql);
$objekti_arv += $sthdel->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>object: parent set, but parent object not found</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sthdel->fetch() ) {
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[parent_id]."&nbsp;&nbsp;&nbsp;".$tmp['pealkiri']."</td>
		<td nowrap>Faulty parent object</td>
		<td nowrap>".$alam_obj."&nbsp;</td>
		<td nowrap>&nbsp;</td>
		</tr>
		";
	} else {
		$sql = $site->db->prepare("DELETE FROM objekt_objekt WHERE parent_id=?", $tmp[parent_id]);
		$sth = new SQL($sql);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql.($sth->error? '<font color=red>Error: '.$sth->error.'</font>':'')."</td>
		</tr>
		";
	}
}

/*----------------------------------------------------
# Vigased objektid: otsida v�lja k�ik objektid, 
# millel parent �ldse m��ramata (aga mis pole s�steemiobjektid) ja kustutada
-----------------------------------------------------*/
## except "folder" and "resource" objects - they can have separate tree (mm..not so sure about resources..)
$sql = "
	SELECT DISTINCT objekt_objekt.parent_id, objekt.objekt_id, objekt.pealkiri
	FROM objekt_objekt
	RIGHT JOIN objekt on objekt_objekt.objekt_id = objekt.objekt_id
	WHERE ISNULL(objekt_objekt.objekt_id) AND sys_alias='' AND tyyp_id <> '22' AND tyyp_id <> '23'
	";
# OLD: AND tyyp_id <> '21'
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>object: parent not set at all</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {
	
	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[objekt_id]."&nbsp;&nbsp;&nbsp;".$tmp['pealkiri']."</td>
		<td nowrap>Faulty parent object</td>
		<td nowrap>".$alam_obj."&nbsp;</td>
		<td nowrap>&nbsp;</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM objekt WHERE objekt_id = ?", $tmp[objekt_id]);
		$sth2 = new SQL($sql2);		
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}
/*----------------------------------------------------
# Objektid mille parent_id on '0'
-----------------------------------------------------*/

$sql = "
	SELECT objekt_objekt.objekt_id, objekt.pealkiri
	FROM objekt_objekt 
	LEFT JOIN objekt ON objekt.objekt_id=objekt_objekt.objekt_id 
	WHERE objekt_objekt.parent_id='0' AND ( isnull(objekt.sys_alias) or objekt.sys_alias='') 
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>object: parent set, but = 0 && !sys_alias</b><br>".$sql."</td>
	</tr>
	";

while ($tmp = $sth->fetch() ) {

	$objekt = new Objekt(array(
		objekt_id => $tmp[objekt_id],
		no_cache => 1,
		skip_sanity_check => 1,
	));

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[objekt_id]."&nbsp;&nbsp;&nbsp;".$tmp['pealkiri'].($objekt->objekt_id ? "": ": delete skipped, cant  create objekt")."</td>
		<td nowrap>Faulty objects, having parent_id=0</td>
		<td nowrap>".$alam_obj."&nbsp;</td>
		<td nowrap>&nbsp;</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		if($objekt->objekt_id) {
			$objekt->del();
		}
	}
}

/*----------------------------------------------------
# Faulty object content: k�ia l�bi k�ik obj_*
# tabelid+objekt_objekt ja vaadata kas tabelis objekt
# on vastavad kirjed olemas
# TABEL obj_artikkel
-----------------------------------------------------*/

repair_obj_table("obj_artikkel");
repair_obj_table("obj_asset");
repair_obj_table("obj_dokument");
repair_obj_table("document_parts");
repair_obj_table("obj_file");
repair_obj_table("obj_folder");
repair_obj_table("obj_gallup");
repair_obj_table("obj_kommentaar");
repair_obj_table("obj_link");
repair_obj_table("obj_pilt");
repair_obj_table("obj_rubriik");

repair_obj_table("objekt_objekt");



################################
# STRIP values

/*----------------------------------------------------
# Vigased objektide STRIP sisud: otsida v�lja artiklid, 
# ja genereerida uuesti objekti strip-v�ljad (vt ka bug #1568) 
# T�iendatud: lisatud ka kommentaaride ja s�ndmuste muutmine (Bug #1692)
-----------------------------------------------------*/

###################
function convert_sisu_strip($sisu_strip){
	$replace_tags_arr = array("<br>", "<BR>", "<br />", "<BR />", "&nbsp;");
	# replace some tags with space before stripping tags (bug #1568 )
	$sisu_strip = str_replace($replace_tags_arr, " ",$sisu_strip);
	$replace_tags_arr = array("&amp;");
	$sisu_strip = str_replace($replace_tags_arr, "&",$sisu_strip);
	$sisu_strip = strip_tags($sisu_strip);

	return $sisu_strip;
}

$faulty_obj_arr = array();

####### 1) articles
$sql = "SELECT objekt_id, lyhi, sisu FROM obj_artikkel";
$sth = new SQL ($sql);
while($rec = $sth->fetch()){
	# strip HTML tags from lyhi, sisu for strip-fields
	$sisu_strip = $rec['lyhi']." ".$rec['sisu'];
	$sisu_strip = convert_sisu_strip($sisu_strip);

	##### check if fields match
	$sql2 = $site->db->prepare("SELECT sisu_strip FROM objekt WHERE objekt_id= ?",	$rec['objekt_id']);
	$sth2 = new SQL ($sql2);
	$sisu_strip_in_db = $sth2->fetchsingle();

	if(trim($sisu_strip_in_db) != trim($sisu_strip) ){

		$faulty_obj_arr[$rec['objekt_id']] = $sisu_strip;
	}
} # while rec

####### 2) comments/messages
$sql = "SELECT objekt_id, text FROM obj_kommentaar";
$sth = new SQL ($sql);
while($rec = $sth->fetch()){
	# strip HTML tags from lyhi, sisu for strip-fields
	$sisu_strip = $rec['text'];
	$sisu_strip = convert_sisu_strip($sisu_strip);

	##### check if fields match
	$sql2 = $site->db->prepare("SELECT sisu_strip FROM objekt WHERE objekt_id= ?",	$rec['objekt_id']);
	$sth2 = new SQL ($sql2);
	$sisu_strip_in_db = $sth2->fetchsingle();

	if(trim($sisu_strip_in_db) != trim($sisu_strip) ){
		$faulty_obj_arr[$rec['objekt_id']] = $sisu_strip;
	}
} # while rec

##################
# sql
if (!$site->fdat['run']){
	echo "
	<tr> 	
	<td><b>object search: Faulty strip-fields</b><br></td>
	</tr>
	";
}

# if found faulty objects
if(sizeof($faulty_obj_arr)>0){

$sql = "SELECT objekt_id, pealkiri FROM objekt WHERE objekt_id IN('".join("','",array_keys($faulty_obj_arr))."')";

$sthdel = new SQL($sql);
$objekti_arv += $sthdel->rows;

while ($tmp = $sthdel->fetch() ) {

	$objekt = new Objekt(array(
		objekt_id => $tmp[objekt_id],
		no_cache => 1,
		skip_sanity_check => 1,
	));

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[objekt_id]."&nbsp;&nbsp;&nbsp;".$tmp['pealkiri'].($objekt->objekt_id ? "": ": delete skipped, unable to create object")."</td>
		<td nowrap>Faulty strip-field</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>&nbsp;</td>
		</tr>
		";
	}
	#####################
	# generate
	else {
		$sql2 = $site->db->prepare("UPDATE objekt SET sisu_strip=? WHERE objekt_id= ?",
			$faulty_obj_arr[$tmp['objekt_id']],
			$tmp['objekt_id']
		);
		$sth2 = new SQL ($sql2);
		if ($sth2->error) { print "<font color=red>Error: ".$sth2->error."</font><br>"; }
	}
}

} # if found faulty obj


/*----------------------------------------------------
# Arvuta comment_count: leia k�ik objektid, 
# millel kommentaaride arv ei ole �ige ja arvuta uuesti
-----------------------------------------------------*/

$faulty_obj_arr = array();
$sql = "
	SELECT objekt.objekt_id, objekt.pealkiri, objekt.comment_count
	FROM objekt
	";
$sth = new SQL($sql);

while($rec = $sth->fetch()){
	# get comment count
	$alamlist_count = new Alamlist(array(
		parent => $rec['objekt_id'],
		klass	=> "kommentaar",
		asukoht	=> 0,
		on_counter => 1	
	));
	
	$comment_count = $alamlist_count->rows;
	
	# vale arv
	if($rec['comment_count'] <> $comment_count){
		$faulty_obj_arr[$rec['objekt_id']] = $comment_count;
	}
} # while rec



##################
# sql
if (!$site->fdat['run']){
	echo "
	<tr> 	
	<td><b>object: wrong comment_count</b></td>
	</tr>
	";
}
# if found faulty objects
if(sizeof($faulty_obj_arr)>0){

$sql = "SELECT objekt_id, pealkiri,comment_count FROM objekt WHERE objekt_id IN('".join("','",array_keys($faulty_obj_arr))."')";

$sthdel = new SQL($sql);
$objekti_arv += $sthdel->rows;

while ($tmp = $sthdel->fetch() ) {

	$objekt = new Objekt(array(
		objekt_id => $tmp[objekt_id],
		no_cache => 1,
		skip_sanity_check => 1,
	));

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[objekt_id]."&nbsp;&nbsp;&nbsp;".$tmp['pealkiri'].($objekt->objekt_id ? "": ": update skipped, unable to create object")."</td>
		<td nowrap>Wrong comment_count (".$tmp['comment_count']." => ".$faulty_obj_arr[$tmp['objekt_id']].")</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>&nbsp;</td>
		</tr>
		";
	}
	#####################
	# generate
	else {
		$sql2 = $site->db->prepare("UPDATE objekt SET comment_count=? WHERE objekt_id= ?",
			$faulty_obj_arr[$tmp['objekt_id']],
			$tmp['objekt_id']
		);
		$sth2 = new SQL ($sql2);
		if ($sth2->error) { print "<font color=red>Error: ".$sth2->error."</font><br>"; }
	}
}

} # if found faulty obj


}# if type selected

#################################### / OBJECTS ######################################

#################################### PERMISSIONS ######################################
?>
	<?php $type = 'permissions'; ?>
	<tr class='scms_pane_header'> 	
	<td colspan=4><a name='<?=$type?>'></a><input type="checkbox" id="type_<?=$type?>" name="type[]" value="<?=$type?>" <?=(in_array($type,$types_arr)?'checked':'')?>><label for="type_<?=$type?>"><?=ucfirst($type)?></label></td>
	</tr>
<?php 
# if type selected
if(in_array($type,$types_arr)){


/*----------------------------------------------------
# Vigased objekti privileegid:
# otsida v�lja privileegid, mis on m��ratud objektide
# kohta, mida enam pole olemas ja 
# kustutada (tabelid permissions)
-----------------------------------------------------*/
##################
# permissions (type=OBJ AND source_id not found as object)

$sql = "
	SELECT DISTINCT permissions.source_id AS objekt_id, objekt.objekt_id AS tmp, permissions.id
	FROM permissions
	LEFT JOIN objekt on objekt.objekt_id = permissions.source_id
	WHERE permissions.type='OBJ'
	HAVING isnull(tmp)
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>permissions: object not found</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {
	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[objekt_id]."</td>
		<td nowrap>Faulty permission (OBJ)</td>
		<td nowrap>".$alam_obj."&nbsp;</td>
		<td nowrap>permissions</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM permissions WHERE id = ?",
			$tmp[id]);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}


/*----------------------------------------------------
# Vigased userid permissions tabelis.
# Otsida tabelist 'permissions' k�ik useri �iguste kirjed, 
# mille kohta user puudub tabelis 'user' ja kustutada
-----------------------------------------------------*/
##################
# permissions
$sql = "
	SELECT a.user_id,a.id
	FROM permissions AS a 
	LEFT JOIN users AS b ON a.user_id=b.user_id
	WHERE ISNULL(b.user_id) AND (a.role_id=0 AND a.group_id=0 AND a.user_id!=0)
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>permissions: user not found</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[user_id]."</td>
		<td nowrap>Faulty permission (ACL)</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>permissions</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM permissions WHERE id = ?", $tmp[id]);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}

}
/*----------------------------------------------------
# Vigased grupid permissions tabelis.
# Otsida tabelist 'permissions' k�ik grupi �iguste kirjed, 
# mille kohta grupp puudub tabelis 'group' ja kustutada
-----------------------------------------------------*/
##################
# permissions
$sql = "
	SELECT a.group_id,a.id
	FROM permissions AS a 
	LEFT JOIN groups AS b ON a.group_id=b.group_id
	WHERE ISNULL(b.group_id) AND (a.role_id=0 AND a.group_id!=0 AND a.user_id=0)
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>permissions: group not found</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[group_id]."</td>
		<td nowrap>Faulty permission (ACL)</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>permissions</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM permissions WHERE id = ?", $tmp[id]);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}

}
/*----------------------------------------------------
# Vigased rollid permissions tabelis.
# Otsida tabelist 'permissions' k�ik rolli �iguste kirjed, 
# mille kohta roll puudub tabelis 'role' ja kustutada
-----------------------------------------------------*/
##################
# permissions
$sql = "
	SELECT a.role_id,a.id
	FROM permissions AS a 
	LEFT JOIN roles AS b ON a.role_id=b.role_id
	WHERE ISNULL(b.role_id) AND (a.role_id!=0 AND a.group_id=0 AND a.user_id=0)
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>permissions: role not found</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[role_id]."</td>
		<td nowrap>Faulty permission (ACL)</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>permissions</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM permissions WHERE id = ?", $tmp[id]);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}

/*----------------------------------------------------
# Vigased read permissions tabelis - m��ratud vale objekti t��bi kohta.
# Otsida tabelist 'permissions' k�ik �iguste kirjed, 
# mis on m��ratud objektidele, mis ei ole t��p "rubiik (1), folder (22)" ja kustutada
-----------------------------------------------------*/
##################
# permissions
$sql = "
	SELECT a.id,b.pealkiri, b.objekt_id
	FROM permissions AS a
	LEFT JOIN objekt AS b ON b.objekt_id=a.source_id
	WHERE a.`type` = 'OBJ' AND (b.tyyp_id<>'1' AND b.tyyp_id<>'22')
";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>permissions: wrong object class</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[pealkiri]." (ID: ".$tmp[objekt_id].")</td>
		<td nowrap>Faulty permission (wrong class)</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>permissions</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM permissions WHERE id = ?", $tmp[id]);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}


/*----------------------------------------------------
# OTSI �les k�ik FOLDER objektid, mis on "public" kataloogi alla ja pane neile
# EVERYBODY jaoks PERMISSION CRUPD=11111 - k�ik v�ivad uploadida neisse folderitesse.
-----------------------------------------------------*/
include_once($class_path."alampuu.class.php");
$otsingu_juur = $site->alias('public');
#printr($otsingu_juur);
	$puu = new Alampuu(array(
		parent_id => $otsingu_juur,
		tyyp_idlist => "22"  # folder
	));
#printr($puu->objektid);

##################
# sql
	echo "
	<tr> 	
	<td><b>permissions: public/ folders to CRUPD=11111</b><br></td>
	</tr>
	";
######### loop
foreach($puu->objektid as $folder_id){

	## create folder object
	$objekt = new Objekt(array(
		objekt_id => $folder_id,
		no_cache => 1,
		skip_sanity_check => 1,
	));
	$objekt->load_sisu();

	### get this folder object permission mask directly from database
	$sql2 = $site->db->prepare("SELECT * FROM permissions  WHERE group_id=? AND source_id = ?", 1, $folder_id);
	$sth2 = new SQL($sql2);
	$tmp = $sth2->fetch();
	$perm_mask = $tmp['C'].$tmp['R'].$tmp['U'].$tmp['P'].$tmp['D'];

#		echo "<tr bgcolor=\"FFFFFF\"><td>"; 	
#printr($perm_mask);
#	echo "</td></tr>"; 	
#printr($objekt->all['pealkiri']. ' => '.$perm_mask.' (ID: '.$folder_id.')');

	if($perm_mask != '11111') { # wrong perm mask

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>Folder ".$objekt->all['pealkiri']." (ID: ".$folder_id.")</td>
		<td nowrap>Faulty permission CRUPD=".$perm_mask." (set to CRUPD=11111)</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>permissions</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		############ 1. DELETE ALL OLD PERMISSIONS for object
		$sql = $site->db->prepare("DELETE FROM permissions WHERE type=? AND source_id=?", 	
			'OBJ', 
			$folder_id
		);
		$sth = new SQL($sql);
				############ 2. INSERT NEW PERMISSIONS for object
				$sql2 = $site->db->prepare("INSERT INTO permissions (type,source_id,role_id,group_id,user_id,C,R,U,P,D) VALUES (?,?,?,?,?,?,?,?,?,?)", 	
					'OBJ', 
					$folder_id, 
					0,
					1, # everybody
					0,
					1, # C
					1, # R
					1, # U
					1, # P
					1 # D
				);
				$sth2 = new SQL($sql2);
		
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql.'<br>'.$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}

	} # f wrong perm mask

} # loop folders


} # perm
#################################### USERS ######################################
?>
	<?php $type = 'users'; ?>
	<tr class='scms_pane_header'> 	
	<td colspan=4><a name='<?=$type?>'></a><input type="checkbox" id="type_<?=$type?>" name="type[]" value="<?=$type?>" <?=(in_array($type,$types_arr)?'checked':'')?>><label for="type_<?=$type?>"><?=ucfirst($type)?></label></td>
	</tr>
<?php 
# if type selected
if(in_array($type,$types_arr)){


/*----------------------------------------------------
# Kasutajad ilma grupita.
# Otsida k�ik kasutajad ilma grupita
# ja panna nende grupiks "Everybody" (ID=1).
-----------------------------------------------------*/
##################
# user
$sql = "
	SELECT user_id, CONCAT(users.firstname,' ',users.lastname) AS name
	FROM users
	WHERE NOT group_id
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>users: user has no group set</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[user_id]."&nbsp;&nbsp;&nbsp;".$tmp['name']."</td>
		<td nowrap>User without group</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>users</td>
		</tr>
		";
	}
	#####################
	# update
	else {
		$sql2 = $site->db->prepare("UPDATE users SET group_id=? WHERE user_id=?", 1,$tmp['user_id']);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}
/*----------------------------------------------------
# Kasutajad mitte-eksisteeriva grupiga.
# Otsida k�ik kasutajad, mille gruppi poel olemas
# ja panna nende grupiks "Everybody" (ID=1).
-----------------------------------------------------*/
##################
# user
$sql = "
	SELECT DISTINCT users.user_id, CONCAT(users.firstname,' ',users.lastname) AS name, users.group_id, groups.group_id AS tmp 
	FROM users 
	LEFT JOIN groups on users.group_id = groups.group_id 
	HAVING ISNULL(tmp) 
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>users: user has non-existing group</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[user_id]."&nbsp;&nbsp;&nbsp;".$tmp['name']." (group_id=".$tmp['group_id'].")</td>
		<td nowrap>Faulty group</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>users</td>
		</tr>
		";
	}
	#####################
	# update
	else {
		$sql2 = $site->db->prepare("UPDATE users SET group_id=? WHERE user_id=?", 1,$tmp['user_id']);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}


/*----------------------------------------------------
# Otsida k�ik grupid, millel id=0
# ja kustutada
-----------------------------------------------------*/
##################
# group
$sql = "
	SELECT group_id, name
	FROM groups
	WHERE NOT group_id
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>groups: group_id=0</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[group_id]."&nbsp;&nbsp;&nbsp;".$tmp[name]."</td>
		<td nowrap>Faulty group</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>groups</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM groups WHERE group_id=?", $tmp['group_id']);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}
/*----------------------------------------------------
# Otsida k�ik userid, millel id=0
# ja kustutada
-----------------------------------------------------*/
##################
# user
$sql = "
	SELECT user_id, CONCAT(firstname,' ',lastname) AS name
	FROM users
	WHERE NOT user_id
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>users: user_id=0</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[user_id]."&nbsp;&nbsp;&nbsp;".$tmp[name]."</td>
		<td nowrap>Faulty user</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>users</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM users WHERE user_id=?", $tmp['user_id']);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}
#########################
# user_roles: user

$sql = "
	SELECT DISTINCT user_roles.user_id, users.user_id AS tmp 
	FROM user_roles 
	LEFT JOIN users on user_roles.user_id = users.user_id 
	HAVING ISNULL(tmp) 
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
if (!$site->fdat['run']){
	echo "
	<tr> 	
	<td><b>user_roles: user not found</b><br>".$sql."</td>
	</tr>
	";
}

while ($tmp = $sth->fetch() ) {
	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[user_id]."</td>
		<td nowrap>Faulty user</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>user_roles</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM user_roles WHERE user_id = ?", $tmp[user_id]);
		$sth2 = new SQL($sql2);
		if ($sth2->error) { print "<font color=red>Error: ".$sth2->error."</font><br>"; }
	}
}
#########################
# user_roles: roles

$sql = "
	SELECT DISTINCT user_roles.role_id, roles.role_id AS tmp 
	FROM user_roles 
	LEFT JOIN roles on user_roles.role_id = roles.role_id 
	HAVING ISNULL(tmp) 
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
if (!$site->fdat['run']){
	echo "
	<tr> 	
	<td><b>user_roles: role not found</b><br>".$sql."</td>
	</tr>
	";
}
while ($tmp = $sth->fetch() ) {
	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[role_id]."</td>
		<td nowrap>Faulty role</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>user_roles</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM user_roles WHERE role_id = ?", $tmp[role_id]);
		$sth2 = new SQL($sql2);
		if ($sth2->error) { print "<font color=red>Error: ".$sth2->error."</font><br>"; }
	}
}

}
#################################### MAILINGLISTS ######################################
?>
	<?php $type = 'mailinglists'; ?>
	<tr class='scms_pane_header'> 	
	<td colspan=4><a name='<?=$type?>'></a><input type="checkbox" id="type_<?=$type?>" name="type[]" value="<?=$type?>" <?=(in_array($type,$types_arr)?'checked':'')?>><label for="type_<?=$type?>"><?=ucfirst($type)?></label></td>
	</tr>
<?php 
# if type selected
if(in_array($type,$types_arr)){

/*----------------------------------------------------
# Vigased meilinglistid: k�ia l�bi tabel 
# user_mailinglist ja kustutad need objektid, 
# mida pole olemas
-----------------------------------------------------*/
##################
# user_mailinglist: objekt
$sql = "
	SELECT DISTINCT user_mailinglist.objekt_id, objekt.objekt_id AS tmp 
	FROM user_mailinglist 
	LEFT JOIN objekt on user_mailinglist.objekt_id = objekt.objekt_id 
	HAVING ISNULL(tmp) 
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>user_mailinglist: object not found</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[objekt_id]."</td>
		<td nowrap>Faulty object</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>user_mailinglist</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM user_mailinglist WHERE objekt_id = ?", $tmp[objekt_id]);
		$sth2 = new SQL($sql2);
		if ($sth2->error) { print "<font color=red>Error: ".$sth2->error."</font><br>"; }
	}

}


##################
# user_mailinglist: user

$sql = "
	SELECT DISTINCT user_mailinglist.user_id, users.user_id AS tmp 
	FROM user_mailinglist 
	LEFT JOIN users on user_mailinglist.user_id = users.user_id 
	HAVING ISNULL(tmp) 
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>user_mailinglist: user not found</b><br>".$sql."</td>
	</tr>
	";

while ($tmp = $sth->fetch() ) {
	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[user_id]."</td>
		<td nowrap>Faulty user</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>user_mailinglist</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM user_mailinglist WHERE user_id = ?", $tmp[user_id]);
		$sth2 = new SQL($sql2);
		if ($sth2->error) { print "<font color=red>Error: ".$sth2->error."</font><br>"; }
	}
}

}
#################################### POLLS ######################################
?>
	<?php $type = 'polls'; ?>
	<tr class='scms_pane_header'> 	
	<td colspan=4><a name='<?=$type?>'></a><input type="checkbox" id="type_<?=$type?>" name="type[]" value="<?=$type?>" <?=(in_array($type,$types_arr)?'checked':'')?>><label for="type_<?=$type?>"><?=ucfirst($type)?></label></td>
	</tr>
<?php 
# if type selected
if(in_array($type,$types_arr)){

/*----------------------------------------------------
# Vigaste gallupi vastuste kontroll: k�ia l�bi tabelid 
# gallup_ip ja gallup_vastus ja vaadata kas vastavad 
# kirjed on tabelis obj_gallup olemas
-----------------------------------------------------*/

##################
# gallup_ip

$sql = "
	SELECT DISTINCT gallup_ip.objekt_id, obj_gallup.objekt_id AS tmp 
	FROM gallup_ip 
	LEFT JOIN obj_gallup on gallup_ip.objekt_id = obj_gallup.objekt_id 
	WHERE gallup_ip.objekt_id!='0' 
	HAVING ISNULL(tmp) 
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>gallup_ip: object not found</b><br>".$sql."</td>
	</tr>
	";

while ($tmp = $sth->fetch() ) {
	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[objekt_id]."</td>
		<td nowrap>Faulty poll content</td>
		<td nowrap>".$alam_obj."&nbsp;</td>
		<td nowrap>gallup_ip</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM gallup_ip WHERE objekt_id = ?", $tmp[objekt_id]);
		$sth2 = new SQL($sql2);
		if ($sth2->error) { print "<font color=red>Error: ".$sth2->error."</font><br>"; }
	}
}

##################
# gallup_vastus

$sql = "
	SELECT DISTINCT gallup_vastus.objekt_id, obj_gallup.objekt_id AS tmp 
	FROM gallup_vastus 
	LEFT JOIN obj_gallup on gallup_vastus.objekt_id = obj_gallup.objekt_id 
	WHERE gallup_vastus.objekt_id!='0' 
	HAVING ISNULL(tmp) 
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>gallup_vastus: object not found</b><br>".$sql."</td>
	</tr>
	";

while ($tmp = $sth->fetch() ) {
	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[objekt_id]."</td>
		<td nowrap>Faulty poll content</td>
		<td nowrap>".$alam_obj."&nbsp;</td>
		<td nowrap>gallup_vastus</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM gallup_vastus WHERE objekt_id = ?", $tmp[objekt_id]);
		$sth2 = new SQL($sql2);
		if ($sth2->error) { print "<font color=red>Error: ".$sth2->error."</font><br>"; }
	}
}
}

#################################### FAVORITES ######################################
?>
	<?php $type = 'favorites'; ?>
	<tr class='scms_pane_header'> 	
	<td colspan=4><a name='<?=$type?>'></a><input type="checkbox" id="type_<?=$type?>" name="type[]" value="<?=$type?>" <?=(in_array($type,$types_arr)?'checked':'')?>><label for="type_<?=$type?>"><?=ucfirst($type)?></label></td>
	</tr>
<?php 
# if type selected
if(in_array($type,$types_arr)){

/*----------------------------------------------------
# Vigased Favorites sidumised useriga:
# otsida v�lja Favorites, mis on m��ratud userite
# kohta, mida enam pole olemas ja 
# kustutada (tabel favorites)
-----------------------------------------------------*/
##################
# favorites (user_id not found as user)

$sql = "
	SELECT DISTINCT favorites.id, favorites.user_id, users.user_id AS tmp
	FROM favorites
	LEFT JOIN users on users.user_id = favorites.user_id
	HAVING isnull(tmp)
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>favorites: user not found</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {
	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[user_id]."</td>
		<td nowrap>Faulty user</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>favorites</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM favorites WHERE id = ?",
			$tmp['id']);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}


/*----------------------------------------------------
# Vigased lemmik-userid favorites tabelis .
# Otsida tabelist 'favorites' k�ik lemmik-useri kirjed, 
# mille kohta user puudub tabelis 'user' ja kustutada
-----------------------------------------------------*/
##################
# favorites
$sql = "
	SELECT a.fav_objekt_id,a.fav_user,a.fav_group,a.id
	FROM favorites AS a 
	LEFT JOIN users AS b ON a.fav_user=b.user_id
	WHERE ISNULL(b.user_id) AND NOT a.fav_objekt_id AND NOT a.fav_group
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>favorites: favorite-user not found</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[fav_user]."</td>
		<td nowrap>Faulty user</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>favorites</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM favorites WHERE id=?", $tmp['id']);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}
/*----------------------------------------------------
# Vigased lemmik-groupid favorites tabelis .
# Otsida tabelist 'favorites' k�ik lemmik-groupi kirjed, 
# mille kohta group puudub tabelis 'groups' ja kustutada
-----------------------------------------------------*/
##################
# favorites
$sql = "
	SELECT a.fav_objekt_id,a.fav_user,a.fav_group,a.id
	FROM favorites AS a 
	LEFT JOIN groups AS b ON a.fav_group=b.group_id
	WHERE ISNULL(b.group_id) AND NOT a.fav_objekt_id AND NOT a.fav_user
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>favorites: favorite-group not found</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[fav_group]."</td>
		<td nowrap>Faulty group</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>favorites</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM favorites WHERE id=?", $tmp['id']);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}
/*----------------------------------------------------
# Vigased lemmik-objectid favorites tabelis .
# Otsida tabelist 'favorites' k�ik lemmik-objecti kirjed, 
# mille kohta object puudub tabelis 'objekt' ja kustutada
-----------------------------------------------------*/
##################
# favorites
$sql = "
	SELECT a.fav_objekt_id,a.fav_user,a.fav_group,a.id
	FROM favorites AS a 
	LEFT JOIN objekt AS b ON a.fav_objekt_id=b.objekt_id
	WHERE ISNULL(b.objekt_id) AND NOT a.fav_user AND NOT a.fav_group
	";
$sth = new SQL($sql);
$objekti_arv += $sth->rows;

##################
# sql
	echo "
	<tr> 	
	<td><b>favorites: favorite-object not found</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp[fav_objekt_id]."</td>
		<td nowrap>Faulty object</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>favorites</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM favorites WHERE id=?", $tmp['id']);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}

}
#################################### / FAVORITES ######################################


#################################### systemwords ######################################
/*
?>
	<?php $type = 'systemwords'; ?>
	<tr class='scms_pane_header'> 	
	<td colspan=4><a name='<?=$type?>'></a><input type="checkbox" id="type_<?=$type?>" name="type[]" value="<?=$type?>" <?=(in_array($type,$types_arr)?'checked':'')?>><label for="type_<?=$type?>"><?=ucfirst($type)?></label></td>
	</tr>
<?php 
# if type selected
if(in_array($type,$types_arr)){

#----------------------------------------------------
# Mite kasutusel s�steemis�nad:
# otsida v�lja systemwords, mis pole �hegi kasutusel oleva keele omad 
# (v.a. estonian & english) ja kustutada (tabel sys_sonad, sys_sonad_kirjeldus)
#-----------------------------------------------------
##################
# systemwords


$sql = "SELECT keel_id, nimi,on_kasutusel FROM keel	";
$sth = new SQL($sql);
$lang_arr = array();
$active_lang_arr = array();
while ($tmp = $sth->fetch() ) {
	$lang_arr[$tmp['keel_id']] = $tmp['nimi'];
	if($tmp['on_kasutusel']){
		$active_lang_arr[] = $tmp['keel_id'];
	}
}
$sql = "
	SELECT COUNT(*) AS arv,keel FROM `sys_sonad` 
	WHERE keel NOT IN('0','1','".join("','",$active_lang_arr)."')
	GROUP BY keel
	";
$sth = new SQL($sql);

##################
# sql
	echo "
	<tr> 	
	<td><b>sys_sonad: not in use</b><br>".$sql."</td>
	</tr>
	";
while ($tmp = $sth->fetch() ) {
	$objekti_arv += $tmp['arv'];

	#####################
	# debug info
	if (!$site->fdat['run']){
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td nowrap>".$tmp['keel'].'&nbsp;&nbsp;&nbsp;'.$lang_arr[$tmp[keel]].': '.$tmp[arv]." words</td>
		<td nowrap>Words not in use</td>
		<td nowrap>&nbsp;</td>
		<td nowrap>sys_sonad</td>
		</tr>
		";
	}
	#####################
	# delete
	else {
		$sql2 = $site->db->prepare("DELETE FROM sys_sonad WHERE keel = ?",
			$tmp['keel']);
		$sth2 = new SQL($sql2);
		echo "
		<tr bgcolor=\"FFFFFF\"> 	
		<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
		</tr>
		";
	}
}


}

*/
#################################### files ######################################
?>
	<?php $type = 'files'; ?>
	<tr class='scms_pane_header'>
	<td colspan=4><a name='<?=$type?>'></a><input type="checkbox" id="type_<?=$type?>" name="type[]" value="<?=$type?>" <?=(in_array($type,$types_arr)?'checked':'')?>><label for="type_<?=$type?>"><?=ucfirst($type)?></label></td>
	</tr>
<?php 
# if type selected
if(in_array($type,$types_arr)){

####################
# wrong "keel"
$sql = "SELECT objekt_id, pealkiri, keel FROM objekt WHERE tyyp_id in ('22','21') AND keel <> 1";

	echo "
	<tr>
	<td><b>Folders & Files: wrong \"keel\" parameter </b><br>".$sql."</td>
	</tr>
	";
	$sth = new SQL($sql);
	$objekti_arv += $sth->rows;
	while ($tmp = $sth->fetch()) {
		$idd[] = $tmp['objekt_id'];
		if (!$site->fdat['run']){
		echo "
			<tr bgcolor=\"FFFFFF\">
			<td nowrap>objekt_id: ".$tmp['objekt_id']."; title: \"".$tmp['pealkiri']."\"</td>
			<td nowrap>keel: ".$tmp['keel']."</td>
			<td nowrap>&nbsp;</td>
			<td nowrap>objekt</td>
			</tr>
			";
		}
	}
	
	if ($site->fdat['run'] && sizeof($idd) > 0){
		$sql_pack[] = "DELETE FROM objekt WHERE objekt_id IN(".join(",",$idd).");";
		$sql_pack[] = "DELETE FROM obj_folder WHERE objekt_id IN(".join(",",$idd).");";
		$sql_pack[] = "DELETE FROM objekt_objekt WHERE objekt_id IN(".join(",",$idd).") OR parent_id IN(".join(",",$idd).");";
		foreach ($sql_pack AS $sql) {
			$sth = new SQL($sql);
			echo "
			<tr bgcolor=\"FFFFFF\">
			<td colspan=\"4\">".$sql.($sth->error? '<font color=red>Error: '.$sth->error.'</font>':'')."</td>
			</tr>
			";
		}
	}
# / wrong "keel"
####################


##########################
# SYNC ALL FOLDERS & FILES

	echo "
	<tr>
	<td><b>Folders: Sync all folders & files</b><br></td>
	</tr>
	";

	########## public/ & shared/
	$public_dir = $site->absolute_path.substr($site->CONF['file_path'],1);
	$shared_dir = $site->absolute_path.substr($site->CONF['secure_file_path'],1);


	if ($site->fdat['run']) {

		/*
		include_once($class_path.'objectmanager.class.php');
		$manager = new objManagement();

		########## public/

		# create folders recursively
		$dirs = $manager->getDirectories(array(
			root_dir => $public_dir
		));
		# create files in all folders
		$manager->syncFolders(array(
			directory => $public_dir
		));
		if(is_array($manager->synced_folders)){
		foreach ($manager->synced_folders as $folder_path) {
			echo "
			<tr bgcolor=\"FFFFFF\">
			<td colspan=\"3\">".$folder_path." </td>
			<td>SYNC OK</td>
			</tr>
			";
		} 
		} # is array

		########## shared/
		unset($manager->synced_folders);
		# create folders recursively
		$dirs = $manager->getDirectories(array(
			root_dir => $shared_dir
		));
		# create files in all folders
		$manager->syncFolders(array(
			directory => $shared_dir
		));
		if(is_array($manager->synced_folders)){
		foreach ($manager->synced_folders as $folder_path) {
			echo "
			<tr bgcolor=\"FFFFFF\">
			<td colspan=\"3\">".$folder_path." </td>
			<td>SYNC OK</td>
			</tr>
			";
		} 
		} # is array
		*/
	} # run

	else {
			echo "
			<tr bgcolor=\"FFFFFF\">
			<td colspan=\"3\">".$public_dir." </td>
			<td>SYNC</td>
			</tr>
			<tr bgcolor=\"FFFFFF\">
			<td colspan=\"3\">".$shared_dir." </td>
			<td>SYNC</td>
			</tr>
			";
	
	}

# / SYNC ALL FOLDERS & FILES
##########################

} # if type

#################################### aliases ######################################
{
?>
	<?php $type = 'aliases'; ?>
	<tr class='scms_pane_header'>
	<td colspan=4><a name='<?=$type?>'></a><input type="checkbox" id="type_<?=$type?>" name="type[]" value="<?=$type?>" <?=(in_array($type,$types_arr)?'checked':'')?>><label for="type_<?=$type?>"><?=ucfirst($type)?></label></td>
	</tr>
<?php 
	// object classes with aliases
	$objects_with_alias_types = array(
		'1' => 'section', // section
		'2' => 'article', // article
		'16' => 'album', // album
	);
	
# if type selected
if(in_array($type,$types_arr)){

	####################
	# missing alias
	// don't include objects with sys_alias (trash, home etc)
	$sql = "SELECT objekt_id, convert(pealkiri using utf8) as pealkiri, keel, tyyp_id FROM objekt WHERE tyyp_id in (".implode(',', array_keys($objects_with_alias_types)).") and (friendly_url = '' or friendly_url is null) and (sys_alias = '' || sys_alias is null)";
	$sth = new SQL($sql);
	$objekti_arv += $sth->rows;

	if ($site->fdat['run'] && $sth->rows)
	{
		include_once($class_path.'adminpage.inc.php');
		
		echo "
		<tr>
		<td><b>Create aliases: </b><br>".$sql."</td>
		</tr>
		";
		while ($tmp = $sth->fetch()) {
			
			$alias = create_alias_for_object($tmp['pealkiri'], $tmp['keel']);
			
			if($alias !== '')
			{
				$sql = $site->db->prepare('update objekt set friendly_url = ? where objekt_id = ?', $alias, $tmp['objekt_id']);
				new SQL($sql);
			}
			
			echo "
				<tr bgcolor=\"FFFFFF\">
				<td nowrap>objekt_id: ".$tmp['objekt_id']."; type: ".$objects_with_alias_types[$tmp['tyyp_id']]."; title: \"".$tmp['pealkiri']."\"; ".($alias !== '' ? 'alias: <b>'.$alias.'</b>': 'Could not create alias')."</td>
				<td nowrap>keel: ".$tmp['keel']."</td>
				<td nowrap>&nbsp;</td>
				<td nowrap>objekt</td>
				</tr>
				";
		}
	}
	elseif(!$site->fdat['run'] && $sth->rows)
	{
		echo "
		<tr>
		<td><b>Create aliases: objects with no alias</b><br>".$sql."</td>
		</tr>
		";
		while ($tmp = $sth->fetch()) {
			echo "
				<tr bgcolor=\"FFFFFF\">
				<td nowrap>objekt_id: ".$tmp['objekt_id']."; type: ".$objects_with_alias_types[$tmp['tyyp_id']]."; title: \"".$tmp['pealkiri']."\"</td>
				<td nowrap>keel: ".$tmp['keel']."</td>
				<td nowrap>&nbsp;</td>
				<td nowrap>objekt</td>
				</tr>
				";
		}
	}
	else 
	{
		// ?
	}
	
	# / missing alias
	####################

} # if type

} // if module is active
################################## SUMMARY ################################

?>
<tr class='scms_pane_header'>
	<td nowrap colspan="4">Found: <?=$objekti_arv?></td>
</tr>
</table>
<?php 
#$site->debug->print_msg();
?>

</form>
</body>
</html>
<?php 


#####################
# FUNCTION repair_obj_table
# Faulty object content: k�ia l�bi k�ik obj_*
# tabelid (parameeter) ja vaadata kas tabelis objekt
# on vastavad kirjed olemas

function repair_obj_table($table){
	global $site, $objekti_arv;

	$sql = "
		SELECT DISTINCT ".$table.".objekt_id, objekt.objekt_id AS tmp , objekt.pealkiri
		FROM ".$table." 
		LEFT JOIN objekt on ".$table.".objekt_id = objekt.objekt_id 
		HAVING ISNULL(tmp) 
		";
	$sth = new SQL($sql);
	$objekti_arv += $sth->rows;

	##################
	# sql
		echo "
		<tr> 	
		<td><b>".$table."</b><br>".$sql."</td>
		</tr>
		";

	while ($tmp = $sth->fetch() ) {
		
		#####################
		# debug info
		if (!$site->fdat['run']){
			echo "
			<tr bgcolor=\"FFFFFF\"> 	
			<td nowrap>".$tmp[objekt_id]."&nbsp;&nbsp;&nbsp;".$tmp['pealkiri']."</td>
			<td nowrap>Faulty object content</td>
			<td nowrap>".$alam_obj."&nbsp;</td>
			<td nowrap>".$table."</td>
			</tr>
			";
		}
		#####################
		# delete
		else {
			$sql2 = $site->db->prepare("DELETE FROM ".$table." WHERE objekt_id = ?", $tmp[objekt_id]);
			$sth2 = new SQL($sql2);
			echo "
			<tr bgcolor=\"FFFFFF\"> 	
			<td>".$sql2.($sth2->error? '<font color=red>Error: '.$sth2->error.'</font>':'')."</td>
			</tr>
			";
		}

	} # while
}

# / FUNCTION repair_obj_table
#####################