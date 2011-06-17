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
#  objektide kustutamine
#

global $site;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);

if ($matches[1]=="admin" || $matches[1]=="editor"){
	$class_path = "../classes/";
} else {
	$class_path = "./classes/";
}

include_once($class_path."port.inc.php");

#Get debug cookie muutuja
$debug = $_COOKIE["debug"] ? 1:0;


$hidden_output = 0;

	$site = new Site(array(
		on_debug=>$debug,
		on_admin_keel => 1
	));


$objekt = new Objekt(array(
	objekt_id => $site->fdat['id']
));


if ($objekt){$rub_trash_id = $site->alias(array('key' => 'trash', 'keel'=>$objekt->all['keel']));}


if ($objekt->objekt_id==$rub_trash_id && $rub_trash_id){
	echo "<font face=verdana size=2><b>You can not delete section \"Recycle Bin\" !</b></font>";
	exit();
}
	
####################################
# GET PERMISSIONS
# get object permissions for current user

$site->debug->msg("EDIT: Kustutava objekti ".$objekt->objekt_id." �igused = ".($system_admin ? "System admin" : $objekt->permission['mask']));

###########################
# ACCESS allowed/denied
# decide if accessing this page is allowed or not

# DELETE: if current object has DELETE => allow
if( $objekt->permission['D']) {
	$access = 1;
}
else {
	$access = 0;
}

	####################
	# access denied
	if (!$access) {
		new Log(array(
			'action' => 'delete',
			'type' => 'WARNING',
			'objekt_id' => $objekt->objekt_id,
			'message' =>  sprintf("access denied: attempt to delete %s '%s' (ID = %s)" , ucfirst(translate_en($objekt->all[klass])), $objekt->pealkiri(), $objekt->objekt_id),
		));
		if (!$hidden_output){
			print "<center><b><font class=\"txt\">".$site->sys_sona(array(sona => "access denied", tyyp=>"editor"))."</font></b></center>";
		}
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




if ($objekt) {

if (!$hidden_output){
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->admin->cms_version;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<link rel="stylesheet" href="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/scms_general.css">
<script language="JavaScript"><!--

			// Here we make parent_id like current id (in main window)

			function replace(string,text,by) {
			// Replaces text with by in string
				var strLength = string.length, txtLength = text.length;
				if ((strLength == 0) || (txtLength == 0)) return string;
				var i = string.indexOf(text);
				if ((!i) && (text != string.substring(0,txtLength))) return string;
				if (i == -1) return string;
				var newstr = string.substring(0,i) + by;
				if (i+txtLength < strLength)
					newstr += replace(string.substring(i+txtLength,strLength),text,by);
				return newstr;
			}

			mylocation = ''+window.opener.location+'';
			
			<? 				
			$jump_array = array(1,2,12,15,16);			
			if (in_array($objekt->all[tyyp_id],$jump_array)) { ?>
				mylocation = replace(mylocation,'?id=','?old_id=');
				mylocation = replace(mylocation,'&id=','&old_id=');
				temp= mylocation.indexOf('?');
				if (temp == -1) {
					mylocation+='?id=<?=$objekt->parent_id ?>';
				} else {
					mylocation+='&id=<?=$objekt->parent_id ?>';
				};
			<? } ?>
//--></script>
</head>
<body class="popup_body"  onLoad="this.focus();">
<?
}

		$sql = $site->db->prepare("SELECT * FROM tyyp where tyyp_id=?", $objekt->all['tyyp_id']);
		$sth15 = new SQL($sql);
		$site->debug->msg($sth15->debug->get_msgs());
		$tyyp = $sth15->fetch();

}


# Vaja vee lisada parameter "use_trash" tabelisse 'tyyp'.
# kui use_trash=0, siis kustuta objekt kohe

#############################
# 1. REMOVE PERMANENTLY FROM DATABASE

# if you want to use old method, use fdat['permanent_remove']=1
if ($site->fdat['permanent_remove'] || $objekt->parent_id==$rub_trash_id || (is_array($tyyp) && !$tyyp['use_trash']) || (is_numeric($site->CONF['trash_expires']) && $site->CONF['trash_expires']=='0')){

	$site->debug->msg("KUSTUTAN BAASIST KOHE!");


# -------------------------------------
# Objekt leitud, k�ik korras
# -------------------------------------
if ($objekt) {

	# vaatame millised t��bid lubavad alampuu kustutada

	$sql = "select tyyp_id from tyyp where on_alampuu_kustutamine='1'";
	$sth15 = new SQL($sql);
	$site->debug->msg($sth15->debug->get_msgs());

	$tyybid = array();

	while ($tid = $sth15->fetchsingle()) {
		array_push($tyybid, $tid);
	}
	$site->debug->msg("T��bid mida alampuuga kustutame: ".join(",",$tyybid));

	$parents=array();
	$alampuu=array();
	$blocked=array();
	$warnings=array();
	array_push($parents, $objekt->objekt_id);
	$h=0;

	while (sizeof($parents) && $h++ < 25) {			
		$site->debug->msg("parents = ".join(", ",$parents));
		######## get parent count
		$sql = $site->db->prepare("
			SELECT count(objekt_objekt.parent_id) as parents_count
			FROM objekt 
			LEFT JOIN objekt_objekt ON objekt.objekt_id=objekt_objekt.objekt_id 
			LEFT JOIN objekt as parent ON objekt_objekt.parent_id=parent.objekt_id 
			WHERE objekt_objekt.parent_id IN(?) AND parent.tyyp_id IN('".join("','",$tyybid)."')
			GROUP BY objekt.objekt_id",
			join(",",$parents)
		);
		$sthcount = new SQL($sql);
		$site->debug->msg($sthcount->debug->get_msgs());
		$parents_count = $sthcount->fetch();
		
		######## get parents
		$sql = $site->db->prepare("
			SELECT objekt.objekt_id, objekt.pealkiri, parent.tyyp_id 
			FROM objekt 
			LEFT JOIN objekt_objekt ON objekt.objekt_id=objekt_objekt.objekt_id 
			LEFT JOIN objekt as parent ON objekt_objekt.parent_id=parent.objekt_id 
			WHERE objekt_objekt.parent_id IN(?) AND parent.tyyp_id IN('".join("','",$tyybid)."')
			GROUP BY objekt.objekt_id, objekt.pealkiri, parent.tyyp_id ",
			join(",",$parents)
		);
		$sth15 = new SQL($sql);
		$site->debug->msg($sth15->debug->get_msgs());

		$alampuu = array_merge($alampuu, $parents);
		$parents = array();			

		while ($record=$sth15->fetch()) {
			if (!$blocked[$record['objekt_id']]) {
				$blocked[$record['objekt_id']] = 1;
				if ($parents_count > 1) {
					array_push ($warnings, $record);
					array_push ($parents, $record['objekt_id']);
				} else {
					array_push ($parents, $record['objekt_id']);
				}
				$site->debug->msg($record['objekt_id']." (".$parents_count.") - ".$record[pealkiri]." tyyp=".$record[tyyp_id]);
			}
		}
	}


if ($site->fdat['kinnitus']) {
	
verify_form_token();
	# kontrollime �igused alampuus
		if (!function_exists('on_access')) {    
		function on_access($id) {
			global $site;
			# kas on �igus kustutada?
				$perm = get_obj_permission(array(
					"objekt_id" => $id,
				));
			return $perm['D'];
		}
		}
		$site->debug->msg("Alampuus on ".sizeof($alampuu)." objekte");
		# filtreeri alampuust v�lja need, mis pole lubatud kustutada
		$alampuu = array_filter($alampuu, "on_access");
	$site->debug->msg("L�plikus alampuus on ".sizeof($alampuu)." objekte");

	# ----------------------
	# kustutame �ra :(      
	# ----------------------

		# ----------------------
		# tabel objekt			
		# ----------------------

		$sql = $site->db->prepare("DELETE FROM objekt WHERE objekt_id IN(?)", 
			join(",",$alampuu)	
		);
		$sth15 = new SQL($sql);
		$site->debug->msg($sth15->debug->get_msgs());

		# ------------------------
		# Kustutame chache-ist ka
		# ------------------------
		clear_cache("ALL");

		# ----------------------
		# tabelid obj_*		  
		# ----------------------

		$sql = "SELECT tabel FROM tyyp WHERE tabel<>'' GROUP BY tabel";
		$sth50 = new SQL($sql);
		$site->debug->msg($sth50->debug->get_msgs());
				
		while ($tabel=$sth50->fetchsingle()) {
			$sql = $site->db->prepare("SELECT * FROM $tabel WHERE objekt_id IN(?)", join(",",$alampuu));
			$sth20 = new SQL($sql);
			$site->debug->msg($sth20->debug->get_msgs());
			
			if ($tabel && $sth20->rows) {
				# kui antud t��bi objektid leitud
				# siis iga t��bi jaoks on oma erifunktsioonid

				$site->debug->msg("Midagi on kustutamiseks $tabel taabelis");

				if ($tabel == "obj_gallup") {
					# GALLUP
					$sql = $site->db->prepare("DELETE FROM gallup_vastus WHERE objekt_id IN(?)", join(",",$alampuu));
					$sth30 = new SQL($sql);
					$site->debug->msg($sth30->debug->get_msgs());

					$sql = $site->db->prepare("DELETE FROM gallup_ip WHERE objekt_id IN(?)", join(",",$alampuu));
					$sth30 = new SQL($sql);
					$site->debug->msg($sth30->debug->get_msgs());
				}
				
				// Kui dokumendid hoiatakse k�vaketas, siis kustutame need ka
				if ($tabel == "obj_dokument") {
					$sql = $site->db->prepare("SELECT fail FROM obj_dokument WHERE objekt_id IN(?) AND download_type", join(",",$alampuu));
					$sth30 = new SQL($sql);
					$site->debug->msg($sth30->debug->get_msgs());
					$unlink_err = "";
					while ($document_file = $sth30->fetchsingle()) {
						$document_file = str_replace("//","/",$site->absolute_path.$site->CONF["documents_directory"]."/".$document_file);
						if (file_exists($document_file)) {
							if (!@unlink($document_file)) {
								$unlink_err .= " <br>Warning! Can't delete file: <b>".$document_file."</b> Permission denied<br>";
							}
						} else {
							$unlink_err .= " <br>Warning! Can't delete file: <b>".$document_file."</b>. File not found<br>";
						}
					}
				}
				
				// if this is a file or folder object delete them also
				if($tabel == 'obj_file' || $tabel == 'obj_folder')
				{
					$sql = 'select relative_path from '.$tabel.' where objekt_id in('.join(',', $alampuu).')';	
					$result = new SQL($sql);
					while($fullpath = $result->fetchsingle())
					{
						$fullpath = preg_replace('#/$#', '', $site->absolute_path).$fullpath;
						if($tabel == 'obj_file')
						{
							if (file_exists($fullpath)) {
								
								$pathinfo = pathinfo($fullpath);
								
								// delete generated images in hidden folders
								// 1. thumbnail
								if(file_exists($pathinfo['dirname'].'/.thumbnails/'.$pathinfo['basename']))
								{
									unlink($pathinfo['dirname'].'/.thumbnails/'.$pathinfo['basename']);
								}
								
								// 2. gallery thumbnail
								if(file_exists($pathinfo['dirname'].'/.gallery_thumbnails/'.$pathinfo['basename']))
								{
									unlink($pathinfo['dirname'].'/.gallery_thumbnails/'.$pathinfo['basename']);
								}
								
								// 3. gallery picture
								if(file_exists($pathinfo['dirname'].'/.gallery_pictures/'.$pathinfo['basename']))
								{
									unlink($pathinfo['dirname'].'/.gallery_pictures/'.$pathinfo['basename']);
								}
								
								if (!@unlink($fullpath)) {
									$unlink_err .= " <br>Warning! Can't delete file: <b>".$fullpath."</b> Permission denied<br>";
								}
							} else {
								$unlink_err .= " <br>Warning! Can't delete file: <b>".$fullpath."</b>. File not found<br>";
							}
						}
						else 
						{
							if (is_dir($fullpath)) {
								if (!@rmdir($fullpath)) {
									$unlink_err .= " <br>Warning! Can't delete folder: <b>".$fullpath."</b> Permission denied<br>";
								}
							} else {
								$unlink_err .= " <br>Warning! Can't delete folder: <b>".$fullpath."</b>. Folder not found<br>";
							}
						}
					}
				}

				$sql = $site->db->prepare("DELETE FROM $tabel WHERE objekt_id IN(?)", join(",",$alampuu));
				$sth30 = new SQL($sql);
				$site->debug->msg($sth30->debug->get_msgs());

				# DOCUMENT_PARTS

				if ($tabel == "obj_dokument" || $tabel == "obj_pilt") {
					$sql = $site->db->prepare("DELETE FROM document_parts WHERE objekt_id IN(?)", join(",",$alampuu));
					$sth30 = new SQL($sql);
					$site->debug->msg($sth30->debug->get_msgs());
				}

			} # if taabel && rows
		} # while taabel


		# ---------------------- #]
		# tabel objekt_objekt    #]
		# ---------------------- #]

		$sql = $site->db->prepare(
			"DELETE FROM objekt_objekt WHERE objekt_id IN(?) OR parent_id IN(?)", 
			join(",",$alampuu), join(",",$alampuu)
		);
		$sth30 = new SQL($sql);
		$site->debug->msg($sth30->debug->get_msgs());


		# -----------------------
		# kustutame �ra permissions
		# -----------------------

		$sql = $site->db->prepare("DELETE FROM permissions WHERE type=? AND source_id IN(?)", 
			'OBJ',
			join(",",$alampuu)
		);
		$sth15 = new SQL($sql);
		$site->debug->msg($sth15->debug->get_msgs());


		# -----------------------
		# kustutame �ra user_mailinglist
		# -----------------------

		$sql = $site->db->prepare("DELETE FROM user_mailinglist WHERE objekt_id IN(?)", 
			join(",",$alampuu)
		);
		$sth15 = new SQL($sql);
		$site->debug->msg($sth15->debug->get_msgs());
		
		# get comment count for object:
		$alamlist_count = new Alamlist(array(
				'parent' => $objekt->parent_id,
				'klass'	=> 'kommentaar',
				'asukoht'=> 0,
				'on_counter' => 1,
			));
		$comment_count = $alamlist_count->rows;

		$sql = $site->db->prepare("UPDATE objekt SET last_commented_time=".$site->db->unix2db_datetime(time()).", comment_count=? WHERE objekt_id=?",
			$comment_count,
			$objekt->parent_id
		);		
		$sth16 = new SQL($sql);
		$site->debug->msg($sth16->debug->get_msgs());
		
		new Log(array(
			'action' => 'delete',
			'objekt_id' => $objekt->objekt_id,
			'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($objekt->all[klass])), $objekt->pealkiri(), $objekt->objekt_id, ($system_admin ? " removed from Recycle Bin " : "deleted").$unlink_err),
		));
?>
		<script type="text/javascript">
			window.opener.location=mylocation;
			window.close();
		</script>
<?
		exit;		
} 
else {
	# ------------------
	# K�sime kinnitust  
	# ------------------

	$kinnitus = $site->sys_sona(array(sona => "tyyp_".$tyyp['nimi'], tyyp=>"System")). ' "<b>'.$objekt->pealkiri.'</b>": '.$site->sys_sona(array(sona => "do you want to permanently delete", tyyp=>"editor"));

	if ($objekt->all[klass] == "rubriik") {
		$alam_list = new Alamlist(array(
			parent => $objekt,
			klass  => 'rubriik'
		));
		$alamrubriigid = array();
		while ($rubriik = $alam_list->next()) {
			array_push ($alamrubriigid, $rubriik->pealkiri);
		}
	} elseif ($objekt->all[klass] == "artikkel") {		


	}

	##################### print permanent remove confirm 

	if (!$hidden_output){
?>
<form action="<?=$site->self ?>" method=get>
<?php create_form_token('delete-object'); ?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;  height:100px">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_alert_cell" height="100%">
	<? echo $kinnitus; if ($site->on_debug) {echo " (permanent remove)";} ?>		
<?
	}

	// what? TODO: replace getPealkiri calls with Objekt::pealkiri
	if (!function_exists('getPealkiri')) {  
	function getPealkiri($objekt) {
		return $objekt->pealkiri;
	}
	}

	# --------------------- 
	# kontrollime kuhu veel 
	# see objekt kuulub     
	# ---------------------

	$sql = $site->db->prepare("
			SELECT parent_id 
			FROM objekt_objekt 
			WHERE parent_id<>? AND objekt_id=?",
		$site->fdat[parent_id], $objekt->objekt_id 
	);

	$sth15 = new SQL($sql);
	$site->debug->msg($sth15->debug->get_msgs());

	if ($sth15->rows > 0) {
		if (!$hidden_output){
		print "<p><b>".$objekt->pealkiri."</b> ".$site->sys_sona(array(sona => "asub veel kohtades", tyyp=>"editor"))." <br>";
		}
		while ($parent_id=$sth15->fetchsingle()) {
			$tee = new Parents(array(
				parent => $parent_id
			));
			$vtee = join(" &gt; ", array_map("getPealkiri", array_reverse($tee->list)));
			if (!$hidden_output){print "<dt>$vtee</dt><br>";}
		}		
	}
	
# ---------------------------
# alampuu objektide hoiatused
# ---------------------------

	if (!$hidden_output){print "<p>";}
	foreach ($warnings as $obj) {
		$sql = $site->db->prepare("SELECT parent_id 
			FROM objekt_objekt
			WHERE parent_id IN(?)=0 AND objekt_id=?",
			join(",",$alampuu), $obj['objekt_id']
		);
		$sth15 = new SQL($sql);
		$site->debug->msg($sth15->debug->get_msgs());

		if ($sth15->rows>0) {
		if (!$hidden_output){print "<p><b>$obj[pealkiri]</b> ".$site->sys_sona(array(sona => "asub veel kohtades", tyyp=>"editor"))." <br>";}
		while ($parent_id=$sth15->fetchsingle()) {
			$tee = new Parents(array(
				parent => $parent_id
			));
			$vtee = join(" &gt; ", array_map("getPealkiri", array_reverse($tee->list)));
			if (!$hidden_output){print "<dt>$vtee</dt><br>";}
		}
	}	
}
if (!$hidden_output){
?>	
	</td>
  </tr>
  <tr align="right"> 
    <td valign="top" colspan=2 > 
	<input type=submit name=kinnitus value="OK" class="btn2" >
	<input type=button name=tagasi value="<?=$site->sys_sona(array(sona => "Katkesta", tyyp=>"editor"))?>" onclick="window.close()" class="bluebtn">
    </td>
  </tr>
</table>

	<input type=hidden name=id value="<?=$objekt->objekt_id?>">
	<input type=hidden name=parent_id value="<?=$site->fdat['parent_id']?>">
	<input type=hidden name=permanent_remove value="<?=$site->fdat['permanent_remove']?>">
	</form>	
<?
}
} # / permanent remove kinnitus


} else if (!$hidden_output) { # / objekt leitud
?>
Wrong ID
<?
}


}
# / 1. REMOVE PERMANENTLY FROM DATABASE
#############################

#############################
# 2. MOVE TO RECYCLE BIN 
# NEW method for deleting (Recycle Bin)
else if (!$hidden_output){

	if ($objekt) {
	$site->debug->msg("PANEN OBJEKT RecycleBin-i");

	if (!is_numeric($rub_trash_id)){ ?>
	<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
	  <tr> 
		<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
	<?
		echo $site->sys_sona(array('sona' => 'tyyp_Rubriik' , 'tyyp'=>"system"))." <b>".$site->sys_sona(array('sona' => 'trash' , 'tyyp'=>"system"))."</b> ".$site->sys_sona(array('sona' => 'missing' , 'tyyp'=>"admin"))." <b>".$site->sys_sona(array('sona' => 'Languages' , 'tyyp'=>"admin"))." &gt; ".$site->sys_sona(array('sona' => 'System articles' , 'tyyp'=>"admin"))."</b>!";
	?>
		</td>
	  </tr>
	</table>
	<? 
		exit;
	}

	################################
	# Move to Recycle Bin here:
	if ($site->fdat['kinnitus']){
		
		verify_form_token();

		$sql = $site->db->prepare("UPDATE objekt_objekt SET parent_id=? WHERE objekt_id=? AND parent_id=?", $rub_trash_id, $objekt->objekt_id, $objekt->parent_id);
		$sth15 = new SQL($sql);
		$site->debug->msg($sth15->debug->get_msgs());

		# DELETE all other parents except trash
		# (Bug #491: ja tegelikult ka n��d pannakse rubriik ikka t�ielikult pr�gikasti. mitte ei panda �heks parentiks pr�gikast ja teiseks j�etakse teine vana parent alles, nagu vanasti)

		$sql = $site->db->prepare("DELETE FROM objekt_objekt WHERE objekt_id=? AND parent_id<>?", $objekt->objekt_id, $rub_trash_id);
		$sth15 = new SQL($sql);
		$site->debug->msg($sth15->debug->get_msgs());

		// v3 style: $sql = $site->db->prepare("UPDATE objekt SET on_avaldatud='0', last_modified=?, friendly_url='' WHERE objekt_id=?", time(), $objekt->objekt_id);
		$sql = $site->db->prepare("UPDATE objekt SET on_avaldatud = 0, changed_time = now(), changed_user_id = ?, changed_user_name = ? WHERE objekt_id = ?", $site->user->user_id, ($site->user->name ? $site->user->name : $site->user->username), $objekt->objekt_id);
		$sth15 = new SQL($sql);
		$site->debug->msg($sth15->debug->get_msgs());

		new Log(array(
			'action' => 'delete',
			'objekt_id' => $objekt->objekt_id,
			'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($objekt->all[klass])), $objekt->pealkiri(), $objekt->objekt_id, " moved to Recycle Bin"),
		));
		
?>
			<script type="text/javascript">
				window.opener.location=mylocation;
				window.close();
			</script>
<?
		exit;		

################################
# Ask confirmation
} else {

?>
	<form action="<?=$site->self ?>" method=get>
	<?php create_form_token('delete-object'); ?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100px">
  <tr> 
    <td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?
######## 1. ask confirmation:  "Do you want to delete this and that?"	

$kinnitus = $site->sys_sona(array(sona => "tyyp_".$tyyp['nimi'], tyyp=>"System")). ' "<b>'.$objekt->pealkiri.'</b>": '.$site->sys_sona(array(sona => "Kas tahate kustutada", tyyp=>"editor"));
echo $kinnitus;

######## 2. for section only, show alert:  "NB! This section includes objects which are published also in other sections. These objects are deleted only from this section."

	# If we have tyyp=rubriik, check for objects, that are under some other rubrics too
	if($objekt->all['klass'] == "rubriik"){
		$sql = $site->db->prepare("SELECT DISTINCT objekt.objekt_id, objekt.pealkiri FROM objekt 
			LEFT JOIN objekt_objekt AS parents_objekt ON parents_objekt.parent_id = ? 
			LEFT JOIN objekt_objekt AS alam_objekt ON alam_objekt.objekt_id=parents_objekt.objekt_id 
			WHERE objekt.tyyp_id = 2 AND objekt.objekt_id = alam_objekt.objekt_id AND alam_objekt.parent_id != ?", $objekt->objekt_id, $objekt->objekt_id);

		
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());

		if($sth->rows){#if we have articles that belong also for other rubrics
			$parents = array();
			while($record=$sth->fetch()){
				array_push ($parents, $record['pealkiri']);
			}
			
			$kinnitus2 = $site->sys_sona(array(sona => "delete_section_alert", tyyp=>"editor"))."<BR><B>".implode("<BR>", $parents)."</B>";
		}

		if($sth->rows){ echo "<br><br>".$kinnitus2; }
	}

####### 3. for all objects: show information if they are located in other places also (Bug #491)


	if (!function_exists('getPealkiri')) {  
	function getPealkiri($objekt) {
		return $objekt->pealkiri;
	}
	}

	# --------------------- 
	# kontrollime kuhu veel 
	# see objekt kuulub     
	# ---------------------

	$sql = $site->db->prepare("
			SELECT parent_id 
			FROM objekt_objekt 
			WHERE parent_id<>? AND objekt_id=?",
		$objekt->all['parent_id'], $objekt->objekt_id 
	);

	$sth15 = new SQL($sql);
	$site->debug->msg($sth15->debug->get_msgs());
	if ($sth15->rows > 0) {
		print "<br><br>".$site->sys_sona(array(sona => "delete_object_alert", tyyp=>"editor"))."<br>";
		while ($parent_id=$sth15->fetchsingle()) {
			$tee = new Parents(array(
				parent => $parent_id
			));
			$vtee = join(" &gt; ", array_map("getPealkiri", array_reverse($tee->list)));
			print "<dt><B>$vtee</B></dt><br>";
		}		
	}

?>

	</td>
  </tr>
<tr> 
	  <td align="right" valign="top" class="scms_dialog_area_bottom"> 
	<input type=submit name=kinnitus value="OK" class="btn2" >
	<input type=button name=tagasi value="<?=$site->sys_sona(array(sona => "Katkesta", tyyp=>"editor"))?>" onclick="window.close()" class="bluebtn">
    </td>
  </tr>
</table>

	<input type=hidden name=id value="<?=$objekt->objekt_id?>">
	<input type=hidden name=parent_id value="<?=$objekt->parent_id?>">
	</form>	


<?
} # Ask confirmation

} # if object exists

}
# / 2. MOVE TO RECYCLE BIN 
#############################
if($site->user) { $site->user->debug->print_msg(); }
if($site->guest) { 	$site->guest->debug->print_msg(); }

$site->debug->print_msg();

if (!$hidden_output){
	echo "</body></html>";
} 
