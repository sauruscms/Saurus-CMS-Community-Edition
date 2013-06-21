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


/**
 * Included file for object tab in object properties popup
 *
 * Shows all info for current object and saves them
 * func edit_object() - shows object table
 * func save_object() - saves object to database
 *
 */
#printr($site->fdat);
#################
# GENERAL ACTIONS for both saving and editing

	global $site;
	
if(empty($site)) exit;
	
	global $objekt;
	global $par_obj;
	global $class_path;
	global $keel;

	global $tyyp;
	global $op;
	global $op2;

######## t��p
$tyyp = get_tyyp();


###########################
# OBJEKTI T��BI VALIKU LIST -> pane HTML muutujasse $idlist_output

if ($site->fdat['op'] == "new" && !$tyyp[tyyp_id]) {
	$site->debug->msg("EDIT: Uue objekti loomine, tyyp_idlist = ".$site->fdat[tyyp_idlist]);

	$sql = "SELECT * FROM tyyp ";
	if ($site->fdat['tyyp_idlist']) {
		$sql .= "WHERE tyyp.tyyp_id IN (".$site->fdat[tyyp_idlist].") ";
	}
	$sql .= "ORDER BY nimi";

	$sth = new SQL($sql);
	$site->debug->msg("EDIT: ".$sth->debug->get_msgs());
	if ($sth->rows==1) {
		# ainult �ks tyyp - avame editor
		$tyyp = $sth->fetch();
	} else {
		$idlist_output = "";
		$params = "";
		foreach ($site->fdat as $key => $value) {
			$params .= "$key=$value&";
		}
		while ($tyyp_l = $sth->fetch()) {

		$sona = $site->sys_sona(array(sona => "tyyp_".$tyyp_l[nimi], tyyp=>"System"));

		$idlist_output .= "<tr><td>&#149;</td>";
		$idlist_output .= "<td width='100%'><a href=\"".$site->self."?".$params."tyyp_id=".$tyyp_l[tyyp_id]."\">".$sona."</a></td>";
		$idlist_output .= "</tr>";
		} # while
	} # if rows=1
}
# / OBJEKTI T��BI VALIKU LIST
###########################

###########################
# ASSETI T��BI VALIKU LIST -> pane HTML muutujasse $idlist_output
$profile_idlist = split(",",$site->fdat[profile_id]);
if ($site->fdat['op'] == "new" && $tyyp['tyyp_id']=='20' && sizeof($profile_idlist)>1) {
	$site->debug->msg("EDIT: Uue ASSETI loomine, profile_idlist = ".$site->fdat[profile_id]);
	##############
	# parameetrid lingile kaasa
	$params = "";
	foreach ($site->fdat as $key => $value) {
		$params .= "$key=$value&";
	}
	##############
	# get all profile data from cash
	foreach($profile_idlist as $profile_id) {
		$profile_def = $site->get_profile(array(id=>$profile_id));
		# sanity check: kui ei leitud sellise nimega profiili, anda toimetajale veateade ja v�ljuda:
		if(!$profile_def[profile_id]) {
			if($site->in_admin || $site->in_editor) {
				print "<font color=red><b>Profile '".htmlspecialchars(xss_clean($profile_id))."' not found!</b></font>";
			}
		}
		# k�ik OK
		else {
			$idlist_output .= "
				&#149;
			<b><a href=\"".$site->self."?".$params."profile_id=".$profile_def[profile_id]."\">".$profile_def[name]."</a></b><br>
			";
		}
	}
	##############
	# make html
	$idlist_output =  "<table width=\"100%\" cellspacing=\"15\" cellpadding=\"0\" border=\"0\">
		  <tr>
			<td width=\"100%\" valign=top style=\"\"> <font class=\"roheline\">".$site->sys_sona(array(sona => "New", tyyp=>"Editor")).":</font><br>
			  <br><font class=txt>
	".$idlist_output."</font></td></tr></table>";
	$is_asset_list = 1;
}
# / ASSETI T��BI VALIKU LIST
###########################



##################
# aeg ja author (default v��rtused)
# bug #2001 $site->fdat['aeg'] = $site->fdat['aeg'] ? $site->fdat['aeg'] : ($objekt->all['aeg'] ? $site->db->MySQL_ee($objekt->all['aeg']) : $site->eesti_aeg());
$site->fdat['aeg'] = $site->fdat['aeg'] ? $site->fdat['aeg'] : ($objekt->all['aeg'] ? $objekt->all['aeg'] : date('Y-m-d H:i'));

# get object value or get default value form triggers file (actions.inc.php)
$site->fdat['author'] = $objekt->all['author'] ? $objekt->all['author'] : $site->fdat['author'];

# / aeg ja author
##################

##################
# REFRESH page withot saving
if ($site->fdat[refresh]) {
	$objekt = new Objekt(array(
		ary => $site->fdat
	));
	foreach($site->fdat as $name=> $value)  {
		$objekt->all[$name] = $value;
	}
	#echo printr($objekt->all);
}

###########################################
# Feature "Check IN/OUT"

if ($op=='edit' && !$site->fdat['op2'])
{
		$sql = $site->db->prepare("
			SELECT objekt.objekt_id, users.user_id, CONCAT(users.firstname,' ',users.lastname) AS name, users.username, users.email FROM objekt
			LEFT JOIN users ON users.user_id = objekt.check_in_admin_id
			WHERE check_in between date_sub(now(), interval 2 minute) and now()
			AND objekt_id=?",
			$objekt->objekt_id
		);
		$sth= new SQL ($sql);
		$site->debug->msg("EDIT: ".$sth->debug->get_msgs());
		$changer = $sth->fetch();

		if ($sth->rows && $changer['username'] && $site->user->id!=$changer['user_id']){
			$checkin_msg = "<br>".$site->sys_sona(array(sona => "Another editor is editing this document right now", tyyp=>"editor")).": <br>".$changer['name']." (".$changer['username'].") <a href=\"mailto:".$changer['email']."\">".$changer['email']."</a>";

			####### print error html
			print_error_html(array(
				"message" => $checkin_msg
			));

			$site->debug->print_hash($site->fdat,1,"FORM DATA");
			$site->debug->print_hash($objekt,1,"Objekt");
			$site->debug->print_msg();

			exit;

		} else {
			$sql = $site->db->prepare("UPDATE objekt SET check_in=now(), check_in_admin_id=? WHERE objekt_id=?",
				$site->user->id, $objekt->objekt_id
			);
			$sth= new SQL ($sql);
			$site->debug->msg("EDIT: ".$sth->debug->get_msgs());
		}
} # SQL

# / Feature "Check IN/OUT"
###########################################

##################
# ONLOAD

# / GENERAL ACTIONS
#################

#################
# FUNCTION EDIT_object
/**
* show object info
*
* Shows general info from table "objekt" and customized info from table "obj_*".
* Uses sub-scripts to show different data for different object types.
* No parameters used, only globals, it is included file.
*
* @package CMS
*
* Call:
*		include_once("edit_object.php");
*		edit_object();
*/
function edit_general_object () {
	global $site;
	global $objekt;
	global $par_obj;
	global $class_path;
	global $keel;

	global $tyyp;
	global $ttyyp;
	global $op;
	global $op2;
?>

	<!-- Scrollable area -->
	<div id=listing class="scms_middle_div">
<?
#####################
# pearubriigi leidmine

$par_obj = new Objekt(array(
	objekt_id => $site->fdat['parent_id']
));
$pearubriik = $par_obj->all[sys_alias]=="home" ? 1 : 0;

# ----------------------
# Uue objekti vorm
# ----------------------
?>
<table border="0" cellspacing="0" cellpadding="0">
<tr><form name=global>
	<td>
	<input type=hidden name="controll_form_was_opened" value="">
	<input type=hidden name="button_checkform_was_pressed" value="">

<? if ($op=='edit') {?>
	<iframe src="checkin.php?objekt_id=<?=$objekt->objekt_id ?>" style="width:1;height:1;visibility:hidden;"></iframe>
<? } ?>

	</td>
</tr></form>

<? /* Paneme selle tag siia, et ei olnud liiga suur tyhi rida */ ?>
<form action="edit.php" method=post name=frmEdit id=frmEdit  enctype="multipart/form-data">
</table>

<?php create_form_token('edit-object'); ?>
<input type=hidden name=tab value="<?=$site->fdat['tab']?>">
<input type=hidden id="op" name="op" value="<?=$site->fdat['op']?>">
<input type=hidden id="op2" name=op2 value="">
<input type=hidden id="refresh" name="refresh" value=0>


<input type="hidden" name="tyyp_id" value="<?=$tyyp[tyyp_id]?>">
<input type="hidden" name="tyyp" value="<?=$tyyp['klass']?>">
<input type="hidden" name="sys_alias" value="<?=$site->fdat[sys_alias]?>">

<input type="hidden" name="pearubriik" value="<?=$pearubriik ?>">
<input type="hidden" name="id" value="<?=$site->fdat['id'] ?>">
<input type="hidden" name="parent_id" value="<?=$site->fdat['parent_id']?>">
<input type="hidden" name="previous_id" value="<?=$site->fdat['previous_id']?>">
<input type="hidden" name="keel" value="<?=$keel?>">

<input type="hidden" name="sorting" value="<?=$site->fdat['sorting'];?>">

<input type="hidden" name="opener_location" value="">
<input type="hidden" name="publish" value="<?php echo ($site->fdat['publish'] || $objekt->all['on_avaldatud'] ? 1 : 0); ?>">
	<script>
		document.frmEdit.opener_location.value = window.opener.location;
	</script>
<?
	###################
	# special case: if object is  picture

	if ($tyyp['klass']=="pilt" && !$objekt->objekt_id) { // if new pilt
		include_once("edit_".$tyyp['klass'].".php");
		if (function_exists("new_objekt")) {
			new_objekt();
		}

	}
	###################
	# usual case: if object is not picture
	else {
?>
<?################# CONTENT - SURROUNDING SCROLL TABLE ################?>

<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_table">
<tr>
<td>

<br>
<?######################### MAIN TABLE ##########################?>
<!--<fieldset class="scms_borderbox">
<legend><?=$site->sys_sona(array(sona => "Main info", tyyp=>"kasutaja"))?></legend>
-->


	  <table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">
        <tr>
          <td colspan="2">
            <div style="position:relative">
              <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "Main info", tyyp=>"kasutaja"))?></div>
            </div>
          </td>
        </tr>
<?
	###################
	# pealkiri


	$pealkiri = htmlspecialchars($objekt->all['pealkiri']);

	# if pealkiri is empty then fill with profile default value (Bug #2819)
	if($pealkiri == '' && $site->fdat['profile_id']){
		$profile_def = $site->get_profile(array(id=>$site->fdat['profile_id']));
		$profile_fields = unserialize($profile_def['data']);	# profile_fields is now array of ALL fields, indexes are fieldnames
		# if found profile default value
		if ($profile_fields['pealkiri']['default_value'] <>'' ){
			$pealkiri = $profile_fields['pealkiri']['default_value'];
		}
	}
?>
		<tr>
          <td nowrap><?=$site->sys_sona(array(sona => "Pealkiri", tyyp=>"editor"))?>:</td>
          <td width="100%">
            <input name="pealkiri" id="pealkiri" type="text" class="scms_flex_input" value="<?=$pealkiri?>" onkeyup="javascript: if(event.keyCode==13){document.getElementById('op2').value='saveclose'; frmEdit.submit();}">
			<textarea name="pastearea" rows="5" cols="30" style="width:1;height:1;visibility:hidden;"></textarea>
          </td>
        </tr>
<?	# / pealkiri
	###################

	###################
	# ONLY image: checkbox "pealkiri on n�htav" + "foorum lubatud"
	if ($tyyp['klass'] == "pilt") {
?>
		<tr>
          <td nowrap><?=$site->sys_sona(array(sona => "Pealkiri on nahtav", tyyp=>"editor"))?>:</td>
          <td width="100%"><input type=checkbox name="on_pealkiri" value="1" <?=($op=="new") ? "checked" : ($objekt->all[on_pealkiri] ? "checked" : "")?>>
	<?=$site->sys_sona(array(sona => "Foorum lubatud", tyyp=>"editor"))?>
	<input type=checkbox name="on_foorum" value="1" <?=($objekt->all[on_foorum] || ($op=="new" && $site->CONF[default_comments]) ? "checked" : "")?>>
            </td>
        </tr>
<? } else { ?>
		<input type="hidden" name="on_pealkiri" value=1>
<? }
	# / checkbox "pealkiri on n�htav" + "foorum lubatud"
	###################

	############################
	# template selectbox
	if ($tyyp[on_kujundusmall]) {
		# print selectboxes and get selected template array
		$ttyyp = print_template_selectboxes();
	}

?>
</table>
<!--</fieldset>-->

<?######################### / MAIN TABLE ##########################?>
<br><br>
<?###################### ADVANCED TABLE #################?>

	  <table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">
        <tr>
          <td colspan="2">
            <div style="position:relative">
              <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "Additional info", tyyp=>"kasutaja"))?></div>
            </div>
          </td>
        </tr>
<?
if($tyyp['klass'] == 'file') {
	print_parent_selectbox();
}

###########################
# OBJECT TYPE: include "edit_*.php"

	include_once("edit_".$tyyp['klass'].".php");
	if (function_exists("edit_objekt")) {
		edit_objekt();
	}

###########################
# type/class configuration

if ($tyyp[on_konfigureeritav]) {
		if (function_exists("edit_tyyp_params")) {
			edit_tyyp_params(array(
				objekt => $objekt
			));
		}
}
# / type/class configuration
###########################


####################################
# Kuulub rubriiki
if($tyyp['klass'] != 'file' && $tyyp['klass'] != 'folder') {
	print_parent_selectbox();
}
?>

</table>
<?###################### / ADVANCED TABLE #################?>
<?  if($tyyp['klass'] != 'file' || $site->CONF['allow_change_position']) { # show advanced table?>
<br>
<br>
     <table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">

<?###################### PUBLISHING & LOCATION TABLE #################?>
<?
	### dont show publish information for files and folders
	if($tyyp['klass'] != 'file' && $tyyp['klass'] != 'folder') {

	$avaldamise_algus = $objekt->all[avaldamisaeg_algus]>0 ? $site->db->MySQL_ee_long($objekt->all[avaldamisaeg_algus]) : "";
	/* Don't print out time which is 00:00:00 */
	if (preg_match("/(\d?\d[\:\\\.\/\-]\d?\d[\:\\\.\/\-]\d?\d?\d\d)\s(\d?\d)[\:\\\.\/\-](\d?\d)/",$avaldamise_algus,$aa_reg)) {
		$avaldamise_algus = ($aa_reg[2]=="00"&&$aa_reg[3]=="00")?$aa_reg[1]:$avaldamise_algus;
	}
	$avaldamise_lopp = $objekt->all[avaldamisaeg_lopp]>0 ? $site->db->MySQL_ee_long($objekt->all[avaldamisaeg_lopp]) : "";
	/* Don't print out time which is 23:59 */
	if (preg_match("/(\d?\d[\:\\\.\/\-]\d?\d[\:\\\.\/\-]\d?\d?\d\d)\s(\d?\d)[\:\\\.\/\-](\d?\d)/",$avaldamise_lopp,$la_reg)) {
		$avaldamise_lopp = ($la_reg[2]=="23"&&$la_reg[3]=="59")?$la_reg[1]:$avaldamise_lopp;
	}
?>
        <tr>
          <td colspan="2">
            <div style="position:relative">
              <div class="scms_borderbox_label"><?=$site->sys_sona(array(sona => "Publishing", tyyp=>"editor"))?></div>
            </div>
          </td>
        </tr>
        <tr>
          <td nowrap>
		  <?=$site->sys_sona(array(sona => "Avaldatud", tyyp=>"editor"))?>:</td>
          <td width="100%">
            <table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td style="padding:0px">
                  <input name="avaldamise_algus" id="avaldamise_algus" type="text" class="scms_flex_input_date" style="width:80px" value="<?=$avaldamise_algus ?>">
                </td>
                <td style="padding:0px"><a href="#" onclick="init_datepicker('avaldamise_algus','avaldamise_algus','avaldamise_lopp');"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" title="Choose from calendar" alt="Choose from calendar" border="0"></a></td>
                <td style="padding:0px">&nbsp;&nbsp;<?=$site->sys_sona(array(sona => "Kuni", tyyp=>"editor"))?>:&nbsp; </td>
                <td style="padding:0px">
                  <input name="avaldamise_lopp" id="avaldamise_lopp" type="text" class="scms_flex_input_date" style="width:80px" value="<?=$avaldamise_lopp ?>">
                </td>
                <td style="padding:0px"><a href="#" onclick="init_datepicker('avaldamise_lopp','avaldamise_algus','avaldamise_lopp');"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" title="Choose from calendar" alt="Choose from calendar" border="0"></a></td>
              </tr>
            </table>
          </td>
        </tr>
<?
	} # not "file"

	############################
	# position
	# n�idata kui configi v��rtus "" on sees JA kui on rubriik v�i artikkel
	$tmp_kesk = split(",",$site->fdat[kesk]);
	if(sizeof($tmp_kesk)>0){ $site->fdat[kesk] = trim($tmp_kesk[0]);}

	if($site->CONF['allow_change_position']) {
?>
	<tr>
		<td nowrap><?=$site->sys_sona(array(sona => "Position", tyyp=>"editor"))?>:</td>
        <td nowrap width="100%">
		<input name="kesk" size="2" class="scms_flex_input" style="width:40px" value="<?=($op=='edit'? $objekt->all[kesk] : $site->fdat[kesk]) ?>">
		</td>
	</tr>
<?	}
	# muidu n�idata hidden value-t
	else { ?>
		<input type="hidden" name="kesk" value="<?=($op=='edit'? $objekt->all[kesk] : $site->fdat[kesk]) ?>">
<?	}
	# / position
	############################
?>
      </table>
<?}# show advanced table?>
<?###################### / PUBLISHING & LOCATION TABLE #################?>
</td>
</tr>
</table>
<?################# / CONTENT - SURROUNDING SCROLL TABLE ################?>
	</div>
	<!-- //Scrollable area -->

    </td>
  </tr>
	<?#################### BUTTONS ###########?>
	  <tr>
	  <td align="right" valign="top" class="scms_dialog_area_bottom">
	  <?php if ($tyyp['klass'] == 'asset') { ?>
	  	<table cellpadding="0" cellspacing="0" width="100%">
	  		<tr>
				<td><input type="button" value="<?=$site->sys_sona(array('sona' => 'New', 'tyyp' => 'editor'))?>" onclick="javascript:frmEdit.op.value='new'; frmEdit.id.value=0; frmEdit.submit();"></td>
	  			<td align="right">
	  <?php } ?>

	<? # dont show "Apply" button for new file if opened from WYSIWYG editor ?>
	<?if( ! ($site->fdat['op'] == 'new' && $tyyp[klass] == 'file' && $site->fdat['in_wysiwyg']) ){?>

		<input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='save'; body.style.cursor = 'wait';if(typeof url_browse == 'object'){url_browse.removeNode()}; frmEdit.submit();">

	<?}?>
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='saveclose'; body.style.cursor = 'wait';if(typeof url_browse == 'object'){url_browse.removeNode()}; frmEdit.submit();">

	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();">
	  <?php if ($tyyp['klass'] == 'asset') { ?>
	  			</td>
	  		</tr>
	  	</table>
	  <?php } ?>
    </td>
  </tr>

</table>

<?
}
# / if object is not picture
###################
?>

</form>
<? $site->debug->msg("EDIT: Objekti loomine, vormi kuvamine");?>

<?
}
# / FUNCTION EDIT_object
#################



#################
# FUNCTION SAVE_OBJECT
/**
* save object info to database
*
* Uses sub-scripts to save different data for different object types.
* No parameters used, only globals, it is included script.
*
* @package CMS
*
* Call:
*		include_once("edit_object.php");
*		save_object();
*/
function save_object () {
	global $site;
	global $objekt;
	global $class_path;
	global $keel;

	global $tyyp;

verify_form_token();

###################
# 1. special case: if object is NEW picture

if ($tyyp['klass']=="pilt" && !$objekt->objekt_id) {
		include_once("edit_".$tyyp['klass'].".php");
		if (function_exists("save_objekts")) {
			save_objekts(array(tyyp_id=>$tyyp[tyyp_id], keel=>$keel));
		}

}
# / 1. special case: if object is NEW picture
###################

###################
# 2. usual case
else {

	# pealkiri peab olema!
	if ($site->fdat[pealkiri] == '' && $tyyp['klass'] != "kommentaar") {
		$errors .= $site->sys_sona(array(sona => "maaratud pealkiri", tyyp=>"editor"))."<br>";
	}
	###################
	# Salvestame tyybi parameetrid
	if (file_exists("edit_".$tyyp['klass'].".php")) {
		include_once("edit_".$tyyp['klass'].".php");

		if (function_exists("save_tyyp_params")) {
			$templ_params = save_tyyp_params(array(
				objekt => $objekt
			));
		}
	}

	###################
	# Salvestame malli parameetrid - old ver3 style
	# arvestame, et malli parameetrid k�ivad ainult sisumalli kohta

	$sql = $site->db->prepare("SELECT ttyyp_id, templ_fail FROM templ_tyyp WHERE ttyyp_id = ?", $site->fdat['ttyyp_id']);
	$sth= new SQL ($sql);
	$site->debug->msg("EDIT: ".$sth->debug->get_msgs());
	$temp_ttyyp=$sth->fetch();

	if ($temp_ttyyp['templ_fail'] && strpos($temp_ttyyp['templ_fail'], '../') !== 0 && file_exists("../".$temp_ttyyp['templ_fail'])) {
		include_once("../".$temp_ttyyp['templ_fail']);
	}

	if (function_exists("save_params")) {
		$templ_params = save_params(array(
			objekt => $objekt
		));

	}
	### ttyyp_params - ver3 style vs ver4. Bug #2506
	if(!empty($templ_params)){
		$oldstyle_tyyp_params = true; # if old-ver-style fixed params are used
	}
	else { # use new ver4 style custom conf save/load by default
		$oldstyle_tyyp_params = false;
	}

	###################
	# if no errors occured , begin saving to database

	if (!$errors) {

		$site->debug->msg("EDIT: Objekti salvestamine");

		###################
		# strip HTML tags from headline, lyhi, sisu for strip-fields

		// folder title is folder filesystem name
		if($site->fdat['tyyp_id'] == 22)
		{
			$site->fdat['pealkiri'] = safe_filename2($site->fdat['pealkiri']);
		}

		$pealkiri_strip = $site->fdat['pealkiri'];

		$sisu_strip = ($site->fdat['scms_article_editor'] ? $site->fdat['scms_article_editor'] : ($site->fdat['sisu'] ? $site->fdat['sisu'] : $site->fdat['text'] ));

		# replace some tags with space before stripping tags (bug #1568 )
		$replace_tags_arr = array("<br>", "<BR>", "<br />", "<BR />", "&nbsp;");
		$pealkiri_strip = str_replace($replace_tags_arr, " ",$pealkiri_strip);
		$sisu_strip = str_replace($replace_tags_arr, " ",$sisu_strip);

		$replace_tags_arr = array("&amp;");
		$pealkiri_strip = str_replace($replace_tags_arr, "&",$pealkiri_strip);
		$sisu_strip = str_replace($replace_tags_arr, "&",$sisu_strip);

		$pealkiri_strip = strip_tags($pealkiri_strip);
		$sisu_strip = strip_tags($sisu_strip);
		// remove excess spaces
		$sisu_strip = preg_replace('/\s+/', ' ', $sisu_strip);

		// overwrite catch for files, this is here so when a new file is being uploaded but
		// a file with a same name already exists
		// there wouldn't be double objects
		// instead use the existing object and move on as that objects update
		if($site->fdat['tyyp_id'] == 21 && $_FILES['fileupload']['name'])
		{
			$parent_folder = new Objekt(array('objekt_id' => $objekt->parent_id, 'on_sisu' => 1));
			$parent_folder_path = preg_replace('#/$#', '', $site->absolute_path).$parent_folder->all['relative_path'];

			// delete file
			if(file_exists($parent_folder_path.'/'.safe_filename2($_FILES['fileupload']['name'])))
			{
				unlink($parent_folder_path.'/'.safe_filename2($_FILES['fileupload']['name']));
			}

			$file_path = preg_replace('#/$#', '', $site->absolute_path).$objekt->all['relative_path'];

			// delete the file itself (bug #2586)
			if($objekt->objekt_id && file_exists($file_path))
			{
				unlink($file_path);
			}

			$sql = $site->db->prepare('select objekt_id from obj_file where relative_path = ?', $parent_folder->all['relative_path'].'/'.safe_filename2($_FILES['fileupload']['name']));
			$result = new SQL($sql);
			if($result->rows && $existing_id = $result->fetchsingle())
			{
				// delete the object used to overwrite
				// don't delete if it's the same object (bug # 2576)
				if($objekt->objekt_id && $objekt->objekt_id != $existing_id)
				{
					$objekt->del();
				}

				$objekt = new Objekt(array(
					'objekt_id' => $existing_id,
					'on_sisu' => 1,
					'no_cache' =>1,
				));
			}
		}
		// / overwrite catch
		###################
		# UPDATE

		if ($objekt->objekt_id) {

			/* Check if avaldamise_algus & avaldamise_lopp has the right format
			   if not fix it.
			*/
			if (preg_match("/(\d?\d[\:\\\.\/\-]\d?\d[\:\\\.\/\-]\d?\d?\d\d)\s?(\d?\d?)[\:\\\.\/\-]?(\d?\d?)/",$site->fdat[avaldamise_algus],$aa_reg)) {
				if (!$aa_reg[2] && !$aa_reg[3]) {
					$site->fdat['avaldamise_algus'] = $aa_reg[1]." 00:00:00";
				} else {
					$site->fdat['avaldamise_algus'] = $aa_reg[1]." ".$aa_reg[2].":".$aa_reg[3].":00";
				}
			}

			if (preg_match("/(\d?\d[\:\\\.\/\-]\d?\d[\:\\\.\/\-]\d?\d?\d\d)\s?(\d?\d?)[\:\\\.\/\-]?(\d?\d?)/",$site->fdat['avaldamise_lopp'],$al_reg)) {
				if (!$al_reg[2] && !$al_reg[3]) {
					$site->fdat['avaldamise_lopp'] = $al_reg[1]." 23:59:59";
				} else {
					$site->fdat['avaldamise_lopp'] = $al_reg[1]." ".$al_reg[2].":".$al_reg[3].":59";
				}
			}
			/* End of check */

			$sql = $site->db->prepare("UPDATE objekt SET pealkiri=?, on_pealkiri=?, on_foorum=?, on_saadetud=?, ttyyp_id=?, page_ttyyp_id=?, pealkiri_strip=?, sisu_strip=?, aeg=?, avaldamisaeg_algus=?, avaldamisaeg_lopp=?, last_modified=".time().", author=?, friendly_url=?, is_hided_in_menu=?, kesk=?, check_in=?, changed_user_id=?, changed_user_name=?, changed_time=?, on_avaldatud = ? WHERE objekt_id=?",
				$site->fdat['pealkiri'],
				$site->fdat['on_pealkiri'],
				$site->fdat['on_foorum'] ? 1 : 0,
				($site->fdat['on_saadetud'] ? 0:1),
				$site->fdat['ttyyp_id'],
				$site->fdat['page_ttyyp_id'],
				$pealkiri_strip,
				$sisu_strip,
				$site->db->ee_MySQL($site->fdat['aeg']),
				/* bug #2242 */
				$site->db->ee_MySQL_long($site->fdat['avaldamise_algus']),
				$site->db->ee_MySQL_long($site->fdat['avaldamise_lopp']),
				$site->fdat['author'],
				$site->fdat['friendly_url'],
				$site->fdat['is_hided_in_menu'] ? 1 : 0,
				$site->fdat['kesk'],
				0,
				$site->user->id,
				$site->user->name,
				date("Y-m-d H:i:s"),
				(isset($site->fdat['publish']) && is_numeric($site->fdat['publish'])) ? (int)$site->fdat['publish'] : $objekt->all['on_avaldatud'],
				$objekt->objekt_id
			);
			$sth = new SQL ($sql);
			$site->debug->msg("EDIT: ".$sth->debug->get_msgs());

			# save old-ver3-style tyyp_params. Bug #2506
			# this SQL should happen only as exception and not by default
			if($oldstyle_tyyp_params === true){
				$sql = $site->db->prepare("UPDATE objekt SET ttyyp_params = ? WHERE objekt_id=?",
					($templ_params ? $templ_params : 'ttyyp_params'),
					$objekt->objekt_id
				);
				$sth = new SQL ($sql);
				$site->debug->msg("EDIT: ".$sth->debug->get_msgs());
			}
			# ------------------------
			# Kustutame chache-ist ka
			# ------------------------
			clear_cache("ALL");
			new Log(array(
				'action' => 'update',
				'objekt_id' => $objekt->objekt_id,
				'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($objekt->all['klass'])), $objekt->pealkiri(), $objekt->objekt_id, "changed"),
			));
		}
		# / UPDATE
		###################

		###################
		# INSERT
		else {

			/* Check if avaldamise_algus & avaldamise_lopp has the right format
			   if not fix it.
			*/
			if (preg_match("/(\d?\d[\:\\\.\/\-]\d?\d[\:\\\.\/\-]\d?\d?\d\d)\s?(\d?\d?)[\:\\\.\/\-]?(\d?\d?)/",$site->fdat['avaldamise_algus'],$aa_reg)) {
				if (!$aa_reg[2] && !$aa_reg[3]) {
					$site->fdat['avaldamise_algus'] = $aa_reg[1]." 00:00:00";
				} else {
					$site->fdat['avaldamise_algus'] = $aa_reg[1]." ".$aa_reg[2].":".$aa_reg[3].":00";
				}
			}

			if (preg_match("/(\d?\d[\:\\\.\/\-]\d?\d[\:\\\.\/\-]\d?\d?\d\d)\s?(\d?\d?)[\:\\\.\/\-]?(\d?\d?)/",$site->fdat['avaldamise_lopp'],$al_reg)) {
				if (!$al_reg[2] && !$al_reg[3]) {
					$site->fdat['avaldamise_lopp'] = $al_reg[1]." 23:59:59";
				} else {
					$site->fdat['avaldamise_lopp'] = $al_reg[1]." ".$al_reg[2].":".$al_reg[3].":59";
				}
			}
			/* End of check */

			$sql = $site->db->prepare("INSERT INTO objekt (pealkiri, on_pealkiri, on_foorum, on_saadetud, tyyp_id, author, on_avaldatud, keel, kesk, ttyyp_id, page_ttyyp_id, pealkiri_strip, sisu_strip, aeg, sys_alias, ttyyp_params, avaldamisaeg_algus, avaldamisaeg_lopp, last_modified, friendly_url, is_hided_in_menu, check_in, check_in_admin_id, created_user_id, created_user_name, created_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
				$site->fdat['pealkiri'],
				$site->fdat['on_pealkiri'],
				$site->fdat['on_foorum'],
				($site->fdat['on_saadetud'] ? 0:1),
				$tyyp['tyyp_id'],
				$site->fdat['author'],
				($site->fdat['sys_alias'] || $site->fdat['publish']) ? 1 : 0,
				($tyyp['tyyp_id'] == 21 || $tyyp['tyyp_id'] == 22 ? 1 : $keel), //faili ja folderi puhul on keel alati 1 (inglise)
				$site->fdat['kesk'],
				$site->fdat['ttyyp_id'],
				$site->fdat['page_ttyyp_id'],
				$pealkiri_strip,
				$sisu_strip,
				$site->db->ee_MySQL($site->fdat['aeg']),
				$site->fdat['sys_alias'],
				$templ_params,
				$site->db->ee_MySQL_long($site->fdat['avaldamise_algus']),
				$site->db->ee_MySQL_long($site->fdat['avaldamise_lopp']),
				time(),
				$site->fdat['friendly_url'],
				($site->fdat['is_hided_in_menu'] ? 1 : 0),
				0,
				$site->user->id,
				$site->user->id,
				$site->user->name,
				date("Y-m-d H:i:s")
			);
			$sth = new SQL ($sql);
			$site->debug->msg("EDIT: ".$sth->debug->get_msgs());
			$obj_insert_id = $sth->insert_id;


			# ------------------------
			# Kustutame chache-ist ka
			# ------------------------
			clear_cache("ALL");

			$objekt = new Objekt(array(
				objekt_id => $obj_insert_id,
				no_cache => 1,
				creating => 1
			));

			if (!is_numeric($objekt->objekt_id)){$objekt->objekt_id = $obj_insert_id;}
			$site->fdat['id'] = $objekt->objekt_id;


		new Log(array(
			'action' => 'create',
			'objekt_id' => $objekt->objekt_id,
			'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($objekt->all['klass'])), $objekt->pealkiri(), $objekt->objekt_id, "inserted"),
		));
			$is_new = 1;
		}
		# / INSERT
		###################

		###################
		# WHAT IS THIS?

		$objekt->all["on_peida_vmenyy"] = $site->fdat["on_peida_vmenyy"] ? 1 : 0;

		###################
		# SALVESTA t��bist s�ltuv osa

		#$fdat{objekt}->{klass} = "artikkel" if ($fdat{objekt}->{klass} eq "oigusakt");
		# INCLUDE t��bist s�ltuv fail

		include_once("edit_".$tyyp['klass'].".php");
		# salvesta objekti t��bist s�ltuv osa
		salvesta_objekt();

		# save all profile fields:
		if($site->fdat['profile_id']){ # if profile set
			save_obj_profile();
		}

		# / SALVESTA t��bist s�ltuv osa
		###################

		###################
		# PARENTS (tbl 'objekt_objekt')
		###################

		$site->debug->msg("------------ PARENTS -------------");

		###################
		#######################
		# 1. FIND NEW PARENTS

		$new_parents=array();

		# parentit on lubatud select-boxis muuta j�rgmistel objektidel:
		# artikkel, dokument, rubriik, album, asset, gallup, kommentaar
		# 1) kui tegu on lubatud objektiga JA vormis oli parent rubriik valitud,
		# siis
		if (($tyyp['klass'] == "artikkel" || $tyyp['klass'] == "dokument" || $tyyp['klass'] == "rubriik" || $tyyp['klass'] == "album" || $tyyp['klass'] == "asset" || $tyyp['klass'] == "gallup" || $tyyp['klass'] == "kommentaar" || $tyyp['klass'] == "link") && is_array($site->fdat['rubriik'])) {
			# salvesta k�ik vormis valitud rubriigid massiivi
			foreach ($site->fdat['rubriik'] as $value) {
				$new_parents[$value]=1;
			};
			# kui parenti ID oli 0, siis what the hell siin tehakse?
			/*
			if ($site->fdat['parent_id']==0) {
				$new_parents[$site->fdat['parent_id']] = 1;
			}
			*/
		}
		# 2) kui sellel objektit��bil ei tohi parentit muuta V�I vormis oli parent rubriik valimata e t�hi,
		# siis pane parentiks 'parent_id' parameeter
		elseif($site->fdat['parent_id']) {
			$new_parents[$site->fdat['parent_id']]=1;
		}

		######### get parent object
		if($site->fdat['parent_id']) {
			$parent = new Objekt(array(
				objekt_id => $site->fdat['parent_id']
			));
		}
		# kui objektil leidub korrektne parent ja pole tegu rubriigiga,
		# siis pane parentiks 'parent_id' parameeter (eee, j�lle?)
		# Lauri: parent ise ei tohiks saada new_parentiks. seega kommentaari juures konkreetselt keelan ara
		if ($parent && $parent->all['klass']!="rubriik" && $tyyp['klass'] != "kommentaar") {
			$new_parents[$site->fdat['parent_id']]=1;
		}

		######## gallupi erijuht
		if ($tyyp['klass'] == "gallup") {
			if ($site->fdat['on_avatud']) {
				$objekt->load_sisu();
				$site->debug->msg("EDIT: vana parent rullib!");
				# removed by Bug #1896: gallupit ei saa teise rubriigi alla t�sta
				# $new_parents = array($objekt->all["orig_parent_id"] => 1);
			} else {
				# kui suletud gallup, siis liiguta gallupi arhiivi
				$new_parents=array($site->alias("gallup_arhiiv") => 1);
			}
		}
		$site->debug->msg("EDIT: Selected new parents: ".join(",",array_keys($new_parents)));

		# 1. / FIND NEW PARENTS
		#######################


		#######################
		# 2. FIND CURRENT PARENTS

		$current_parents = array();
		########### RUBRIIK V�I LINGIKAST
		if ($tyyp['klass'] == "rubriik") {

			######## Otsime, kas rubriik kuulub ka m�ne uudistekogu alla - need on vaja uutele parentitele vaikselt lisada
			$sql = $site->db->prepare("SELECT objekt_objekt.parent_id FROM objekt_objekt LEFT JOIN objekt ON objekt.objekt_id=objekt_objekt.parent_id WHERE objekt_objekt.objekt_id=? and objekt.tyyp_id=9", $objekt->objekt_id);
			$sth = new SQL($sql);

			while ($tmp_data = $sth->fetchsingle()) {
				$newslist_parents[$tmp_data] = 1;
				# lisa salaja uudistekogu ID samuti uute parentite massiivile
				$new_parents[$tmp_data] = 1;
			}
			unset($tmp_data);
			if ($sth->rows){
				$site->debug->msg("EDIT: Parent news lists: ".join(",",array_keys($newslist_parents)));
			}

			########## leia rubriigi praegused parentid
			$sql = $site->db->prepare("SELECT parent_id FROM objekt_objekt WHERE objekt_id=?", $objekt->objekt_id);
			$sth = new SQL($sql);
			while ($tmp_data = $sth->fetch()) {
				$parent_id = $tmp_data['parent_id'];
				$current_parents[$parent_id] = 1;
			}

		######### K�IK �LEJ��NUD objektid v.a rubriik ja lingikast
		} else {
			# -----------------------------------
			# siin on need objektid mille jaoks
			# on lubatud rohkem kui 1 �lema omama
			# -----------------------------------

			$sql = $site->db->prepare("SELECT parent_id FROM objekt_objekt WHERE objekt_id=?", $objekt->objekt_id);
			$sth = new SQL($sql);

			while ($tmp_data = $sth->fetch()) {
				$parent_id = $tmp_data['parent_id'];
				$current_parents[$parent_id] = 1;
			}
			# mis siin tehakse?
			# Lauri: oeldakse jargmise IF-i jaoks, et ara sinna sisse mine. vaata 10 rida allapoole
			if ($current_parents[0]) {
				$new_parents[0] = 1;
			}
		}
		$site->debug->msg("EDIT: Current parents: ".join(",",array_keys($current_parents)));
		# / 2. FIND CURRENT PARENTS
		#######################
		# kui uute parentite massiiv on t�hi, siis kasuta vormis alati kaasas olnud
		# peidetud v��rtust 'permanent_parent_id'
		if ($site->fdat['permanent_parent_id'] != "" && !count(array_keys($new_parents))) {
			$new_parents[$site->fdat['permanent_parent_id']] = 1;
		}
		$site->debug->msg("EDIT: Final parents: ".join(",",array_keys($new_parents)));


		#######################
		# 3. CHECK NEW PARENTS PERMISSIONS
		foreach (array_keys($new_parents) as $parent_id) {
			if($parent_id) {
			# kui uus �lem (varem polnud), siis kontrolli �iguseid
			if (!$current_parents[$parent_id]) {
				####### check permissions
				$perm = get_obj_permission(array(
					"objekt_id" => $parent_id,
				));
				# kui uuel parentil on CREATE �igus, siis luba lisada objekt selle parenti alla,
				# muidu mitte
				if ($perm['C']){
					$site->debug->msg("EDIT: New parent ".$parent_id." permissions ".$perm['mask']." allow to create object under it => OK");
				}
				# kui objekti lisamine uue parenti alla on keelatud
				else {
					$site->debug->msg("EDIT: New parent ".$parent_id." permissions ".$perm['mask']." don't allow to create object under it => FORBIDDEN");

					# v�ta see parent maha uute parentite massiivist
					unset($new_parents[$parent_id]);

				} # new parent permissions
			}
			} # if parent_id
		}
		$site->debug->msg("EDIT: Final parents after permission check: ".join(",",array_keys($new_parents)));
		# / 3. CHECK NEW PARENTS PERMISSIONS
		#######################

		# kui uute parentite arv on 0 st objekti ei tohi uue parenti alla lisada,
		# siis �ra tee �ldse midagi
		if( !count(array_keys($new_parents)) ) {
			$site->debug->msg("EDIT: Can't move under new parent => don't do anything at all");
			$fatal_parent_error = 1;
		}
		# kui uued parentid on ainult uudistekogud (samad mis enne), siis �ra tee �ldse midagi
		if(sizeof($newslist_parents)>0) {
			$result = array_diff(array_keys($new_parents),array_keys($newslist_parents));
			if(sizeof($result) <= 0) { # kui massiivid pole erinevad
			$site->debug->msg("EDIT: New parents are equal to existing newslist parents => don't do anything at all");
			$fatal_parent_error = 1;
			}
		}

# testimiseks, kui ei taha, et tegelikult parenteid baasis muudetakse:
#$fatal_parent_error = 1;

		################################## DATABASE SQL-s ##################################

		else if(!$fatal_parent_error) { # if not fatal parent error

		#######################
		# 4. INSERT NEW PARENTS (siin on juba ainult need parentid, mille alla v�ib objekti lisada)
		foreach (array_keys($new_parents) as $parent_id) {
			if($parent_id) {
			# �lem on olemas, siis pole vaja midagi teha
			if ($current_parents[$parent_id]) {

				$current_parents[$parent_id] = 0;
				$site->debug->msg("EDIT: Parent $parent_id j��b nagu oli");
			}
			# uus �lem, tuleb seda lisada
			else {
			
                if ($site->fdat["sorting"]) {
                    $sorteering = $site->fdat["sorting"];
                } else {
                    $sql = "SELECT max(sorteering) FROM objekt_objekt";
                    $sth = new SQL ($sql);
                    $sorteering = $sth->fetchsingle();
                }

				#move objects forward so new object can be inserted in the middle
                $sql = $site->db->prepare("UPDATE objekt_objekt SET sorteering=sorteering+1 WHERE sorteering>?",
					$sorteering
				);
				$sth = new SQL($sql);
				$site->debug->msg("EDIT: ".$sql);

                ################ INSERT
				$sql = $site->db->prepare("INSERT INTO objekt_objekt (objekt_id, parent_id, sorteering) VALUES (?,?,?)",
					$objekt->objekt_id,
					$parent_id,
					$sorteering+1
				);
				$sth = new SQL($sql);
				$site->debug->msg("EDIT: ".$sql);

				if ($tyyp['klass'] == 'kommentaar') {
					$sql = $site->db->prepare("UPDATE objekt SET comment_count = comment_count+1 WHERE objekt_id=?",
						$parent_id
					);
					$sth = new SQL ($sql);
					$site->debug->msg("EDIT: ".$sql);
				}
			} # uus �lem, lisa
			} # if parent_id
		}
		# / 4. INSERT NEW PARENTS
		#######################

		#######################
		# 5. DELETE OLD PARENTS
		function notnull($a) {
			return $a>0;
		}

		$to_delete = join(",",array_keys(array_filter($current_parents, "notnull")));
		$site->debug->msg("EDIT: Current Parents, allowed to delete: ".$to_delete);

		# kui vormis oli valitud m�ni parent rubriik JA objekt on lubatud t��pi (tal v�ib parentit muuta),
		# siis... mis tehakse?
		if ( sizeof($site->fdat['rubriik'])>0 &&
			($tyyp['klass'] == "artikkel" || $tyyp['klass'] == "dokument" || $tyyp['klass'] == "gallup" || $tyyp['klass'] == "rubriik" || $tyyp['klass'] == "album" || $tyyp['klass'] == "asset" || $tyyp['klass'] == "dokument" || $tyyp['klass'] == "gallup" || $tyyp['klass'] == "kommentaar" || $tyyp['klass'] == "link")
		) {
			$parent_ids = array();
			foreach(array_unique(array_merge($site->fdat['rubriik'],array_keys($new_parents))) as $tmp_id)
			{
				if((int)$tmp_id) $parent_ids[] = (int)$tmp_id;
			}
			########### Lauri 04092009: store parents that will be deleted so we can reduce their comment_count
			$sql = $site->db->prepare("SELECT parent_id FROM objekt_objekt WHERE objekt_id=? AND parent_id NOT IN(".implode(',', $parent_ids).")",
				$objekt->objekt_id
			);
			$sth = new SQL ($sql);
			$site->debug->msg("EDIT: ".$sql);
			$parents_to_delete = array();
			while($return_row = $sth->fetch()){
				$parents_to_delete[] = $return_row['parent_id'];
			}

			############ DELETE
			$sql = $site->db->prepare("DELETE FROM objekt_objekt WHERE objekt_id=? AND parent_id NOT IN(".implode(',', $parent_ids).")",
				$objekt->objekt_id
			);

			$sth = new SQL ($sql);
			$site->debug->msg("EDIT: ".$sql);

			########## Lauri 04092009: reduce comment count for parents from which kommentaar was deleted
			if ($tyyp['klass'] == 'kommentaar') {
				$sql = $site->db->prepare("UPDATE objekt SET comment_count = comment_count-1 WHERE objekt_id IN(".implode(',', $parents_to_delete).")"
				);
				$sth = new SQL ($sql);
				$site->debug->msg("EDIT: ".$sql);
			}
		}

		############ DELETE
		# kui on parenteid, mida kustutada:
		if ($to_delete) {
			$sql = $site->db->prepare("DELETE FROM objekt_objekt WHERE objekt_id=? AND parent_id IN(?)",
				$objekt->objekt_id,
				$to_delete
			);
			$sth = new SQL ($sql);
			$site->debug->msg("EDIT: ".$sql);

			############ Lauri 04092009: if some more parents got removed, reduce comment_count on them too. have to make sure not to do it twice tho
			$comments_to_substract = array_diff((array)$to_delete, $parents_to_delete);
			if ($tyyp['klass'] == 'kommentaar' && count($comments_to_substract)) {
				$sql = $site->db->prepare("UPDATE objekt SET comment_count = comment_count-1 WHERE objekt_id IN (".implode(',', $comments_to_substract).")"
				);
				$sth = new SQL ($sql);
				$site->debug->msg("EDIT: ".$sql);
			}
		}
		# / 5. DELETE OLD PARENTS
		#######################

		} # if not fatal parent error (new parent permissions)

		################################## / DATABASE SQL-s ##################################

		$site->debug->msg("------------ PARENTS END -------------");

		# / PARENTS
		###################

		########################
		# INSERT PERMISSIONS

		# lisame uuele objektile t�pselt samad �igused nagu on tema parent objektile.
		# OBJ class check: save permissions only for objects having class "rubriik" (1) or "folder" (22).
		# NB! if you change class conditions here, be sure to change them in Repair database script also!
		# (see also bug #1545)
		if($tyyp[tyyp_id] == 1 || $tyyp[tyyp_id] == 22) { # if object is section or folder

			if($is_new) # if new, just created object
			{  
				# leia k�ik parenti �igused userite/gruppide kohta:
				$sql = $site->db->prepare("SELECT * FROM permissions WHERE type=? AND source_id=?",
					'OBJ',
					 $parent->objekt_id
				);
				$sth = new SQL ($sql);
				# ts�kkel �le parenti �iguste
				while($perm = $sth->fetch())
				{
					# lisa �igus uuele objektile
						$sql2 = $site->db->prepare("INSERT INTO permissions (type,source_id,role_id,group_id,user_id,C,R,U,P,D) VALUES (?,?,?,?,?,?,?,?,?,?)",
							'OBJ',
							$objekt->objekt_id,
							$perm['role_id'],
							$perm['group_id'],
							$perm['user_id'],
							$perm['C'],
							$perm['R'],
							$perm['U'],
							$perm['P'],
							$perm['D']
						);
						$sth2 = new SQL($sql2);
				} # ts�kkel �le parenti �iguste
				
				// reload permissions for user
				if(!$site->user->is_superuser)
				{
					if($site->user)
					{
						$site->user->permissions = $site->user->load_objpermissions();
					}
					elseif($site->guest)
					{
						$site->guest->permissions = $site->guest->load_objpermissions();
					}
				}
			}  # if new, just created object
		} # if object is section or folder
		# / INSERT PERMISSIONS
		########################

	return 1;
	}
	# / if no errors occured
	###################
	###################
	# print errors
	else { ?>
	<center><font class=txt>
		<br>
		<font color=red>&nbsp;<?=$errors?></font>
		<br>
		<a href="javascript:history.back();"><?=$site->sys_sona(array(sona => "Tagasi", tyyp=>"editor")) ?></a>
	</font></center>
<?
	}
	# / print errors
	###################

}
# / 2. usual case
###################

}
# / FUNCTION SAVE_object
#################


######################
# FUNCTION  GET_TYYP()
function get_tyyp() {
	global $site;
	global $tyyp;

	# kui uus JA t��p antud V�I objektil t��p olemas V�I t��p antud parameetriga
	if (($site->fdat['op'] == "new" && $tyyp[tyyp_id]) || $tyyp[tyyp_id] || $site->fdat['tyyp_id']>0) {
		if ($site->fdat['op'] == "new") {
			$site->debug->msg("EDIT: Uue objekti loomine, tyyp_id = ".$site->fdat['tyyp_id']);
		} else {
			$tyyp[tyyp_id] = $tyyp[tyyp_id] ? $tyyp[tyyp_id] : $site->fdat['tyyp_id'];
			$site->debug->msg("EDIT: Objekti tyybi kontroll, tyyp_id = ".$tyyp[tyyp_id]);
		}
		$sql = "SELECT * FROM tyyp ";
		if ($tyyp[tyyp_id] || $site->fdat['tyyp_id']) {
			$sql .= " WHERE tyyp.tyyp_id = ".$site->db->quote($tyyp[tyyp_id]?$tyyp[tyyp_id]:$site->fdat['tyyp_id']);
		}
		$sth = new SQL($sql);
		$site->debug->msg("EDIT: ".$sth->debug->get_msgs());
		$tyyp = $sth->fetch();
		return $tyyp;
	} else {
		$site->debug->msg("EDIT: OP = ".$site->fdat['op'].", tyyp_id = ".$site->fdat['tyyp_id']);
		return $tyyp;
	}
}
# / FUNCTION  GET_TYYP()
######################


/**
 * get grouped array of content or page templates
 *
 * @param string $type [CONTENT|PAGE]
 * @param integer $selected_template
 * @return array
 */
function get_templates($type, $selected_template)
{
	global $site;
	global $class_path;
	global $objekt; // TODO: get rid of global objekt

	include_once($class_path."extension.class.php"); # for printing extensions template groups

	$template_data = array();

	##########################
	# general SQL:
	# show visible templates that
	# are allowewd by modules or not depending on any module at all

	$gen_sql = $site->db->prepare("SELECT templ_tyyp.ttyyp_id,templ_tyyp.nimi,templ_tyyp.templ_fail,templ_tyyp.extension
		FROM templ_tyyp
		WHERE (templ_tyyp.on_nahtav='1'".
		($objekt->all['ttyyp_id'] ? ' or templ_tyyp.ttyyp_id = '.$objekt->all['ttyyp_id'] : '').
		($objekt->all['page_ttyyp_id'] ? ' or templ_tyyp.ttyyp_id = '.$objekt->all['page_ttyyp_id'] : '').
		")"
	);

	$sql_user_defined = " AND (templ_tyyp.ttyyp_id >= 1000 AND templ_tyyp.ttyyp_id < 2000) ";
	$sql_saurus3 = " AND (templ_tyyp.ttyyp_id < 1000 OR templ_tyyp.ttyyp_id >= 2000) ";
	$sql_no_extension = " AND (templ_tyyp.extension = '' OR ISNULL(templ_tyyp.extension)) ";
	$sql_extension = " AND (templ_tyyp.extension <> '' OR NOT ISNULL(templ_tyyp.extension)) ";

	$sql_page_templ = " AND templ_tyyp.on_page_templ = '1' ";
	$sql_content_templ = " AND templ_tyyp.on_page_templ = '0' ";

	$order_by = " ORDER BY templ_tyyp.nimi ";

	################################
	# 1. CONTENT Template selectbox

	############################
	# group USER DEFINED:
	# SAPI templates (ttyyp_id >= 1000), not predefined (ttyyp_id < 2000), not extension template

	$sql = $gen_sql . ($type == 'CONTENT' ? $sql_content_templ : $sql_page_templ) . $sql_no_extension. $sql_user_defined;
	$sql .=	$order_by;
	$sth = new SQL ($sql);

	# if found templates
	if($sth->rows) {
		while ($templ=$sth->fetch()) {
			$template_data['User defined'][$templ['ttyyp_id']] = $templ;
		}
	} # if found templates

	############################
	# group EXTENSIONS:
	# SAPI extension CONTENT templates

	$sql = $gen_sql . ($type == 'CONTENT' ? $sql_content_templ : $sql_page_templ) . $sql_extension;
	$sql .=	$order_by;

	# print extensions templates rows and get selected template array
	$template_data = array_merge($template_data, get_extension_templates($sql));

	############################
	# group SAURUS 3 (was PREDEFINED):
	# BUILT-IN PHP-templates (ttyyp_id < 1000) + predefined SAPI templates (ttyyp_id >= 2000)

	$sql = $gen_sql . ($type == 'CONTENT' ? $sql_content_templ : $sql_page_templ) . $sql_no_extension . $sql_saurus3;
	$sql .=	$order_by;
	$sth = new SQL ($sql);

	# if found templates
	if($sth->rows) {
		$tmp_templ_arr = array();	$tmp_arr = array();
		while ($templ=$sth->fetch()) {
			$tmp_templ_arr[$site->sys_sona(array(sona => $templ['nimi'], tyyp=>"system"))] = $templ;
			$tmp_arr[] = $site->sys_sona(array(sona => $templ['nimi'], tyyp=>"system"));
		}
		asort($tmp_arr); reset($tmp_arr);
		foreach($tmp_arr as $templ_name){
			$templ = $tmp_templ_arr[$templ_name];
			if($selected_template == $templ['ttyyp_id']) $ttyyp = $templ;
			$template_data['Saurus 3'][$templ['ttyyp_id']] = $templ;
		}
	} # if found templates

	############################
	# CONTENT TEMPLATE configuration
	# arvestame, et konfigureeritavad v�ivad olla ainult v3 fiks php sisumallid
	if ($ttyyp[on_konfigureeritav]) {
	# -----------------------
	# Templaidi konfigureerimine
	# -----------------------
		if ($ttyyp[templ_fail] && file_exists("../".$ttyyp[templ_fail])) {
			include_once("../".$ttyyp[templ_fail]);
		}
		if (function_exists("edit_params")) {
			// TODO: get rid of html buffering
			ob_start();
			edit_params(array(
				objekt => $objekt
			));
			$template_data['template_variable_html'] = ob_get_contents();
			ob_end_clean();
		}
	}
	# / template configuration
	############################

	return $template_data;
}

############################
# FUNCTION print_template_selectboxes
#
# return selected template array
function print_template_selectboxes() {
	global $site;
	global $objekt; // TODO: get rid of global objekt

	// content templates
	$ttyyp="";	# init ttyyp

	$selected_value = $objekt->all['ttyyp_id'];

	print "
	<tr>
	  <td nowrap>".$site->sys_sona(array(sona => "Content template", tyyp=>"editor")).":</td>
      <td width='100%'>
	  <select name=ttyyp_id class='scms_flex_input' style='max-width: 334px' onChange=\"document.frmEdit.refresh.value='1';document.frmEdit.submit();\">
	    <option value=\"\"></option>
	";

	$content_templates = get_templates('CONTENT', $selected_value);
	if($content_templates['template_variable_html'])
	{
		$template_variable_html = $content_templates['template_variable_html'];
		unset($content_templates['template_variable_html']);
	}

	foreach($content_templates as $group => $templates)
	{
		print '<optgroup label="'.$group.'">';
		foreach($templates as $templ)
		{
			if($selected_value == $templ['ttyyp_id']) $ttyyp = $templ;
			print "<option value=\"".$templ['ttyyp_id']."\"".($selected_value==$templ['ttyyp_id']?" selected":"").($templ['ttyyp_id'] == $objekt->all['ttyyp_id'] || $templ['ttyyp_id'] == $objekt->all['page_ttyyp_id'] ? " style=\"color: #a7a6aa;\"" : "").">";
			print $templ['nimi'];
			print "</option>\n";
		}
		print '</optgroup>';
	}

	print "
	  </select>
	  </td>
    </tr>";

	// page templates
	$page_ttyyp="";	# init ttyyp
	$selected_value = $objekt->all['page_ttyyp_id'];

	print "
	<tr>
	  <td nowrap>".$site->sys_sona(array(sona => "Page template", tyyp=>"editor")).":</td>
      <td width='100%'>";

	print "<select name=page_ttyyp_id class='scms_flex_input' style='max-width: 334px'>
		<option value=\"\"></option>
	";

	$page_templates = get_templates('PAGE', $selected_value);

	foreach($page_templates as $group => $templates)
	{
		print '<optgroup label="'.$group.'">';
		foreach($templates as $templ)
		{
			if($selected_value == $templ['ttyyp_id']) $page_ttyyp = $templ; // TODO: find out why this is here and possibly get rid of it
			print "<option value=\"".$templ['ttyyp_id']."\"".($selected_value==$templ['ttyyp_id']?" selected":"").($templ['ttyyp_id'] == $objekt->all['ttyyp_id'] || $templ['ttyyp_id'] == $objekt->all['page_ttyyp_id'] ? " style=\"color: #a7a6aa;\"" : "").">";
			print $templ['nimi'];
			print "</option>\n";
		}
		print '</optgroup>';
	}

	print "
	  </select>
	  </td>
    </tr>";

	############################
	# CONTENT TEMPLATE configuration
	if ($ttyyp['on_konfigureeritav']) {
		echo $template_variable_html;
	}
	# / template configuration
	############################

	return $ttyyp;
}
# / FUNCTION print_template_selectboxes
############################


############################
# FUNCTION print_parent_selectbox
function print_parent_selectbox() {
	global $site;
	global $objekt;
	global $tyyp;
	global $class_path;
	global $keel;

	$parent_is_rubriik = true; # kas objekti parent(s) on rubriik v�i mitte
	############ 1. objekt olemas, leia tema parent-objektid:
	if ($objekt->objekt_id) {

		$sql = $site->db->prepare("SELECT objekt_objekt.parent_id, objekt.tyyp_id, objekt.pealkiri
			FROM objekt_objekt
				LEFT JOIN objekt ON objekt_objekt.parent_id = objekt.objekt_id
			WHERE objekt_objekt.objekt_id=?",
			$objekt->objekt_id
		);
	}
	########## 2. uus objekt, leia tema parentid URL-i parameetri p�hjal
	elseif($site->fdat['parent_id']) {
		$sql = $site->db->prepare("SELECT objekt_objekt.objekt_id AS parent_id, objekt.tyyp_id, objekt.pealkiri
			FROM objekt_objekt
				LEFT JOIN objekt ON objekt_objekt.objekt_id = objekt.objekt_id
			WHERE objekt_objekt.objekt_id=?",
			$site->fdat['parent_id']
		);
	}
	if($sql) {
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());

		while ($tmp = $sth->fetch()) {
			$rubriigid[$tmp['parent_id']]=1;

			# kui t��p ei ole rubriik, siis pane hoiatusm�rge p�sti
			if($tmp['tyyp_id'] != 1) $parent_is_rubriik = false;

			# salvesta parentite nimed:
			$parent_name_arr[] = $tmp['pealkiri'];
		}

		######### get parent object	(only for existing object)
		$current_parent = new Objekt(array(
			objekt_id => $objekt->parent_id
		));
	} # if sql

	# luba rubriigi selectboxi ainult j�rgmiste klasside korral:
	# rubriik, album, asset, dokument, gallup
	# JA siis kui parent on rubriik
	if($parent_is_rubriik && ($tyyp['klass'] == "rubriik" || $tyyp['klass'] == "album" || $tyyp['klass'] == "asset" || $tyyp['klass'] == "dokument" || $tyyp['klass'] == "gallup" || $tyyp['klass'] == "link") || ($tyyp['klass'] == "kommentaar")) {

	# rubriike, mis on sys_alias, ei tohi �mber t�sta
	if($objekt->all["sys_alias"] == '' && $site->fdat['sys_alias'] == '') {

		########### parentid leitud, koosta rubriikide puu:
		# Bug #1988: juhul kui objekt on olemas, aga useril ei ole tema parentile C �igust,
		# siis tuleb see parent rubriikide nimistusse ise lisada (ignore_perm_for_obj).
		$class_path = "../classes/";

		include_once $class_path."rubloetelu.class.php";
		if ($parent_is_rubriik) {
			$rubs = new RubLoetelu(array(
				"keel" => $keel,
				"required_perm" => "C",
				"ignore_perm_for_obj" => $current_parent->objekt_id
			));
		} else {
			if ($tyyp['klass'] == 'kommentaar') {
				$rubs = new RubLoetelu(array(
					"keel" => $keel,
					"required_perm" => "C",
					"ignore_perm_for_obj" => $current_parent->objekt_id,
					"object_type_ids" => '1,2,14,15'
				));
			}
		}
		#$rubs->debug->print_msg();
		$topparents = $rubs->get_loetelu();
		if(is_array($topparents)) {
			asort($topparents);
		}

		// setup for section selection
		$_SESSION['parent_selection']['callback'] = 'window.opener.updateSection';
		$_SESSION['parent_selection']['selectable'] = 1;
		$_SESSION['parent_selection']['hide_language_selection'] = '1';
		$_SESSION['parent_selection']['db_fields'] = array('select_checkbox', 'objekt_id', 'pealkiri', );
		$_SESSION['parent_selection']['display_fields'] = array('select_checkbox', 'pealkiri', );
		if ($parent_is_rubriik) {
			$_SESSION['parent_selection']['mem_classes'] = array('rubriik', ); //this sucks, really
		} else {
			$_SESSION['parent_selection']['mem_classes'] = array('rubriik', 'artikkel', 'kommentaar', 'teema', ); //this sucks, really
		}
		########## print section selectbox:
		?>
		  <tr>
		  <td nowrap><?=$site->sys_sona(array(sona => "Rubriigid", tyyp=>"editor"))?>:</td>
		  <td width="99%">
<!-- SITE EXPLORER -->
		  <script type="text/javascript">



			function chooseSection()
			{

			explorer_window = openpopup('explorer.php?objekt_id=home&editor=1&swk_setup=parent_selection&remove_objects=<?=$site->fdat['id'];?>&pre_selected=' + document.getElementById('rubriik').value, 'cms_explorer', '800','600');

			}

			function updateSection(sections)
			{
				explorer_window.close();
				var section_name = document.getElementById('section_name');
				var section_id = document.getElementById('rubriik');
				var trail_path= new Array();

					for(var j = 0; j < sections[0].trail.length; j++){
						trail_path[j] = sections[0].trail[j].pealkiri;
					}

				section_name.innerHTML = '<a href="#" onclick="chooseSection();">' + trail_path.join("->") + '</a>';
				section_id.value = sections[0].objekt_id;
			}

		  </script>
<?
if(is_array($rubriigid)){
foreach($topparents as $k=>$v){
	if(key ($rubriigid) == $k){
		$section_name=$v;
	}
  }
}?>
		  <table cellpadding="0" cellspacing="0" class="cf_container">
			<tr>
				<th>
				<input type="hidden" name="rubriik[]" id="rubriik" value="<?echo key ($rubriigid);?>">
				<span id="section_name"><a href="javascript:chooseSection();"><?=($section_name ? $section_name : $site->sys_sona(array('sona' => 'choose_section', 'tyyp' => $EXTENSION['name'])));?></a></span></th>
				<td ><a href="javascript:chooseSection();">..</a></td>
			</tr>
		</table>
<!-- END OF SITE EXPLORER -->

		  </td>
		</tr>
	<?
	} # rubriike, mis on sys_alias, ei tohi �mber t�sta
	} # if lubatud klass, kellele n�idata rubriigi selectboxi

	##########	kui parent EI OLE rubriik => n�ita vaid parenti nime(sid)

	# Bug #1681: Juhul kui rubriigi selectboxi ei n�ita, siis salvestamise funktsioonis ei tehta parentitega midagi
	else {
		?>
		  <tr>
		  <td nowrap><?=$site->sys_sona(array(sona => "Rubriigid", tyyp=>"editor"))?>:</td>
		  <td width="100%">
			<? # print parent names
			if(sizeof($parent_name_arr)>0)	{
				echo join("<br>",$parent_name_arr);
			}
		?>
		  </td>
		</tr>
	<?
	} # dont show selectbox

}
# / FUNCTION print_parent_selectbox
############################

####################################
# FUNCTION save_obj_profile
function save_obj_profile () {
	global $site;
	global $objekt;
	global $tyyp;
	global $class_path;

	include_once($class_path."adminpage.inc.php");

	### set skipped (system) fields for each class:
	$skipped_fields['asset'] = 'objekt_id,profile_id';
	$skipped_fields['event'] = 'objekt_id, profile_id, start_time, end_time, description,username,kasutaja_id,parent_id,recure_days, recure_week, recure_weeks,recure_month,recure_months,recure_year,recure_start,recure_end,recure_times,location,is_private,profile_id,progress,tracking_start_time,tracking_end_time,tracking_total_hours';
	$skipped_fields['artikkel'] = 'objekt_id, profile_id, lyhi, sisu';
	$skipped_fields['dokument'] = 'objekt_id';
	$skipped_fields['file'] = 'objekt_id, profile_id, fullpath, relative_path, filename, mimetype, size, lastmodified, is_deleted';

	### set field suffixes for each class (usually, if profile dropdown is used then it should be "_<profile_id>"):
	$field_suffix['asset'] = '';
	$field_suffix['event'] = '_'.$site->fdat['profile_id'];
	$field_suffix['artikkel'] = '_'.$site->fdat['profile_id'];
	$field_suffix['file'] = '_'.$site->fdat['profile_id'];

	#printr($skipped_fields[$tyyp['klass']]);

	if ($objekt->objekt_id) {

		################## GET profile
		$profile_def = $site->get_profile(array("id"=>$site->fdat['profile_id']));

		################ CUSTOM VALIDATION
		if(file_exists($site->absolute_path.'/extensions/validations.inc.php')) require_once($site->absolute_path.'/extensions/validations.inc.php');

		################## CHECK & CHANGE profile values (required, date formats, arrays, etc)
		$sql_field_values = check_profile_values(array(
			'profile_def' => &$profile_def,
			'skip_fields' => $skipped_fields[$tyyp['klass']],
			'skip_non_active_fields' => true, // skip also the fields that are not active
			'use_only_profile_fields' => true, // skip also the fields that are in the table but not in the profile
			'field_suffix' => $field_suffix[$tyyp['klass']],
			'custom_validation' => $VALIDATION_FUNCTIONS,
		));

		//printr($sql_field_values);

		# check if usual field (save to 'obj_..' table) OR general object field (save to 'objekt' table)
		$profile_data = unserialize($profile_def['data']);
		if(is_array($profile_data)) {
			foreach($profile_data as $profile_field) {
				if($profile_field['is_general']){
					$general_obj_field[$profile_field['name']] = true;
				}
			} # loop over fields
		}
		#########################
		# save profile data
		foreach ($sql_field_values as $field=>$value) {
			if($general_obj_field[$field]) { # (save to 'objekt' table)

				$update_fields_gen[] = $site->db->prepare($field."=?", html_entity_decode($value));
			}
			else { # (save to 'obj_..' table)
				$update_fields[] = $site->db->prepare($field."=?", html_entity_decode($value));
			} # which table
		}
		//printr($update_fields);
		if(is_array($update_fields) && sizeof($update_fields)>0) {
			$sql = "UPDATE ".$tyyp['tabel']." SET ".join(",",$update_fields); # Bug 2246
			$sql .= $site->db->prepare(" WHERE objekt_id=?", $objekt->objekt_id);
			$sth = new SQL($sql);
			//printr($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}
		if(is_array($update_fields_gen) && sizeof($update_fields_gen)>0) {
			$sql = "UPDATE objekt SET ".join(",",$update_fields_gen); # Bug 2246
			$sql .= $site->db->prepare(" WHERE objekt_id=?", $objekt->objekt_id);
			$sth = new SQL($sql);
			#print $sql;
			$site->debug->msg($sth->debug->get_msgs());
		}
		#print $sql;exit;

	} # obj OK
	return;
}

# / FUNCTION save_obj_profile
####################################