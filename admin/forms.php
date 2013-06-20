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
 * Saurus CMS admin page "Tools > Forms" for fill-in forms management
 * 
 * Form list with toolbar;
 * Allows add, modify, delete forms in database
 * 
 * @param int form_id - selected form ID
 * @param string op - action name
 * @param string op2 - second action name
 * 
 */

global $site;

$class_path = "../classes/";
include_once($class_path."port.inc.php");
include_once($class_path."adminpage.inc.php");

$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));
if (!$site->user->allowed_adminpage()) {
	exit;
}

######### get adminpage name
$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
$parent_pagename = $adminpage_names['parent_pagename'];
$pagename = $adminpage_names['pagename'];

$site->fdat['form_id'] = (int)$site->fdat['form_id'];

################ get selected  FORM data
if($site->fdat['form_id']) {
	$sql = $site->db->prepare("SELECT * FROM forms WHERE form_id=? ",$site->fdat['form_id']);
	$sth = new SQL($sql);
	$form_def = $sth->fetch();

	if($form_def['name']){
		$breadcrumb_focus_str = ",'".$form_def['name']."'";
	}
}
#printr($form_def);
###############################
# OP2 & SAVE

if($site->fdat['op2'] == 'save' || $site->fdat['op2'] == 'saveclose') {
#	echo printr($site->fdat);

		verify_form_token();

		if($site->fdat['op']=='new') {

			## New
			$sql = $site->db->prepare("INSERT INTO forms (name,profile_id,source_table,description) VALUES (?,?,?,?)",$site->fdat['name'],$site->fdat['profile_id'], $site->fdat['source_table'], $site->fdat['description']);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			$site->fdat['form_id']= $sth->insert_id;

			####### write log
			new Log(array(
				'action' => 'create',
				'component' => 'Forms',
				'message' => "New Form '".$site->fdat['name']."' inserted",
			));
			##### if new was saved, then make it 'edit'
			if($site->fdat['op2'] == 'save'){
				$site->fdat['op'] = "edit";
			}
		} 
		elseif($site->fdat['op']=='edit') {
			## Update
			$sql = $site->db->prepare("UPDATE forms SET name=?, profile_id=?, source_table=?, description=? WHERE form_id=?",$site->fdat['name'],$site->fdat['profile_id'],$site->fdat['source_table'],$site->fdat['description'], $site->fdat['form_id']);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			####### write log
			new Log(array(
				'action' => 'update',
				'component' => 'Forms',
				'message' => "Form '".$site->fdat['name']."' updated",
			));
		}

	################
	# kui vajutati salvesta nuppu ja ei olnud erroreid, pane aken kinni
	if ($site->fdat['op2']=='saveclose' && count($form_error)==0) {
	?>
	<HTML>

<link rel="stylesheet" href="<?=$site->conf['wwwroot'].$site->conf['styles_path']?>/datepicker.css">
<script type="text/javascript" src="<?=$site->conf['wwwroot'].$site->conf['js_path']?>/yld.js"></script>
<script type="text/javascript" src="<?=$site->conf['wwwroot'].$site->conf['js_path'] ?>/jquery.js"></script>
<script type="text/javascript" src="<?=$site->conf['wwwroot'].$site->conf['js_path'] ?>/datepicker.js"></script>
<script type="text/javascript" src="<?=$site->conf['wwwroot'];?>/common.js.php"></script>
	<SCRIPT language="javascript"><!--
		var oldurl = window.opener.location.toString();
		oldurl = oldurl.replace(/\?form_id=(\d+)/g, "");
		if('<?=$op?>'=='new') {
			newurl = oldurl + '?form_id=<?=$site->fdat['form_id']?>'; 
			window.opener.location=newurl;
		} else {
			window.opener.location=window.opener.location;	
		}
		window.close();
	// --></SCRIPT>
	</HTML>
	<?
	exit;
	}
}
# / OP2 & SAVE
###############################

###############################
# DELETE form 

if($site->fdat['op2'] == 'deleteconfirmed' && $site->fdat['form_id']) {

	# delete form
	$sql = $site->db->prepare("DELETE FROM forms WHERE form_id=?",$site->fdat['form_id']);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	####### write log
	new Log(array(
		'action' => 'create',
		'component' => 'Forms',
		'message' => "Form '".$form_def['name']."' deleted",
	));
	?>
	<HTML>
	<SCRIPT language="javascript"><!--
		window.opener.location=window.opener.location;
		window.close();
	// --></SCRIPT>
	</HTML>
	<?
	exit;
}
# / DELETE 
###############################

###############################
# OP = PREVIEW

if($site->fdat['op'] == 'preview') {

	include_once($class_path."adminpage.inc.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding ? $encoding : $site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>
<body onLoad="this.focus();">


<!-- Scrollable area -->
<div id=listing class="scms_scroll_div">

<?	if($form_def['profile_id']){ ?>

<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_table">
<tr class="scms_pane_header">
<td colspan=2><?=$form_def['name']?></td>
</tr>

<?		# get profile
		$profile_def = $site->get_profile(array("id"=>$form_def['profile_id'])); 
		$profile_fields = unserialize($profile_def['data']);	# profile_fields is now array of ALL fields, indexes are fieldnames

		###################
		# print profile fields rows
		print_profile_fields(array(
			'profile_fields' => $profile_fields,
			'field_values' => $objekt->all,
		));
?>
	</table>
<?	}
	### error: no profile id
	else {
		echo "Error! no profile found";
	}
?>
</div>
<!-- // Scrollable area -->

	</body>
	</html>
<?	exit;
}
# / OP = PREVIEW
###############################

######################
# 1. DELETE CONFIRMATION WINDOW 
if($site->fdat['op'] == 'delete' && $site->fdat['form_id']) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->admin->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>
<body class="popup_body">
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<?php create_form_token('edit-forms'); ?>
	<input type=hidden name=form_id value="<?=$site->fdat['form_id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">


<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100px">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?
	# check if allowed to delete
	# 1. if exists any data row with that form, then don't allow to delete

	$data_count = 0;
	############ form content data
		$sql = $site->db->prepare("SELECT COUNT(*) FROM ".$form_def['source_table']." WHERE form_id=? ",$form_def['form_id']);
		$sth = new SQL($sql);
		$form_count = $sth->fetchsingle();
		$data_count += $form_count;

	if($data_count > 0) {
		# show error message
		echo "<font color=red>".$site->sys_sona(array(sona => "Permission denied", tyyp=>"editor"))."</font><br><br>";
		echo $site->sys_sona(array(sona => "Children count", tyyp=>"admin")).": <b>".$data_count."</b>";
	}
	# show confirmation
	else {
		echo $site->sys_sona(array(sona => "kustuta", tyyp=>"editor"))." \"<b>".$form_def['name']."</b>\"? ";
		echo $site->sys_sona(array(sona => "are you sure?", tyyp=>"admin"));
		$allow_delete = 1;
	}
?>
	</td>
  </tr>
  <tr align="right"> 
    <td valign="top" colspan=2 > 
		<?if($allow_delete){?>
            <input type="button" value="<?=$site->sys_sona(array(sona => "kustuta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='deleteconfirmed';frmEdit.submit();">
			<?}?>
			<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

</form>
</body>
</html>
<?
	############ debug
	# user debug:
	if($site->user) { $site->user->debug->print_msg(); }
	# guest debug: 
	if($site->guest) { 	$site->guest->debug->print_msg(); }
	$site->debug->print_msg(); 
	exit;
}	
# / 1. DELETE CONFIRMATION WINDOW 
######################




###############################
# OP = NEW / EDIT

if($site->fdat['op'] == "new" || ($site->fdat['op'] == "edit" && is_numeric($site->fdat['form_id'])) ) {


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding ? $encoding : $site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>
<body class="popup_body" onLoad="this.focus();document.forms['vorm'].name.focus();">

<FORM action="<?=$site->self ?>" method="post" name="vorm">
<?php create_form_token('edit-forms'); ?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<tr> 
    <td valign="top" width="100%" class="scms_dialog_area_top"  height="100%">
	  <table width="100%"   border="0" cellspacing="0" cellpadding="2">
	  <?############ name #########
		if($form_error['name']){
			$name = $site->fdat['name'];
		} else {
			$name = $form_def['name'];
		}
		?> 
	  <tr> 
		<td><?=$site->sys_sona(array(sona => "nimi", tyyp=>"editor"))?>: </td>
		<td width="100%"><input type=text name=name value="<?= ($site->fdat['op']=="new" && !$form_error['name']? '' : $name) ?>" class="scms_flex_input" onkeyup="javascript: if(event.keyCode==13){vorm.submit();}">
			
		<?=($form_error['name']? '<br><font color=red>'.$form_error['name'].'</font>':'')?>
		</td>
	  </tr>
	<?############### profile selectbox #######?>
	<?
	# get all profiles having the source_table form_*:
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table, name FROM object_profiles WHERE source_table LIKE ? ORDER BY name",
		'form_%');
		$sth = new SQL($sql);
	?>
		<SCRIPT language="javascript"><!--
			var source_tables = new Array();
			<? while ($tmp_profile = $sth->fetch('ASSOC')){ ?>
			source_tables[<?=$tmp_profile['id']?>] = '<?=$tmp_profile['source_table']?>';
			<?}?>
		--></SCRIPT>
	<tr>
		<td><?=$site->sys_sona(array(sona => "profile", tyyp=>"editor"))?>: </td>
		<td width="95%" STYLE="padding-bottom:5px">
		<select name="profile_id" style="width:99%" onclick="document.getElementById('source').innerHTML=source_tables[this.options[this.options.selectedIndex].value]; document.getElementById('source_table').value=source_tables[this.options[this.options.selectedIndex].value]">
			<option value=""></option>
		<? 
		$sth = new SQL($sql);	
		while ($tmp_profile = $sth->fetch('ASSOC')){ ?>
			<option value="<?=$tmp_profile['id']?>" <?= ($form_def['profile_id'] == $tmp_profile['id'])?"selected":""; ?>><?=$site->sys_sona(array(sona => $tmp_profile['name'], tyyp=>"custom"))?></option>
		<? 
		
			# remember table names for source table div
			if($form_def['profile_id']==$tmp_profile['id']){
				$active_table_name = $tmp_profile['source_table'];
			}

		} ?>
		</select>
	</td>
	</tr>

	<?############### source table text #######?>
	<tr>
		<td nowrap><?=$site->sys_sona(array(sona => "DB Table", tyyp=>"xml"))?>: </td>
		<td width="100%">
		<DIV id="source">
		<?=$active_table_name?>
		</DIV>
		<input type=hidden id=source_table name=source_table value="<?=$active_table_name?>">
	</td>
	</tr>

	<?############### description #######?>
	<tr>
		<td valign=top><?=$site->sys_sona(array(sona => "kirjeldus", tyyp=>"editor"))?>: </td>
		<td width="100%">
		<textarea name="description" id="description" rows="10" style="width:100%"><?= $form_def['description'] ?></textarea>
	</td>
	</tr>


	  </table>
	</td>
</tr>
	<?############ buttons #########?>
	<tr> 
	  <td align="right" valign="top" class="scms_dialog_area_bottom"> 
         <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript: document.forms['vorm'].op2.value='save';this.form.submit();">
         <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:document.forms['vorm'].op2.value='saveclose';this.form.submit();">
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

<?########### hidden ########?>
<INPUT TYPE="hidden" name="form_id" value="<?= $site->fdat['form_id'] ?>">
<INPUT TYPE="hidden" name="op" value="<?=$site->fdat['op']?>">
<INPUT TYPE="hidden" name="op2" value="save">
</form>
</body>
</html>
<?
############ debug
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 

exit;
	
}

# / OP = NEW / EDIT
##################

##################
# LIST
else {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>' <?=$breadcrumb_focus_str?>);
//-->
</SCRIPT>
</head>

<body style="overflow-y: auto; overflow-x: auto;">

<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
<?
################################
# FUNCTION BAR
?>
<!-- Toolbar -->
<TR>
<TD class="scms_toolbar">

	<?######### form FUNCTION BAR ############?>
      <table border="0" cellpadding="0" cellspacing="0">
        <tr> 
			<?############ new dropdown ###########?>
				<TD nowrap><a href="javascript:void(openpopup('forms.php?op=new','form','366','450'))" id="top4" ><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/filenew.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id=pt> <?=$site->sys_sona(array(sona => "new", tyyp=>"editor"))?></a>
				</TD>
		  <?############ edit form button ###########?>
				<TD nowrap><?if($site->fdat['form_id']){?><a href="javascript:void(openpopup('forms.php?op=edit&form_id=<?= $site->fdat['form_id']?>','form','366','450'))"><?}?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/edit<?=(!$site->fdat['form_id'] ? '_inactive' : '')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle> <?=$site->sys_sona(array(sona => "muuda", tyyp=>"editor"))?><?if($site->fdat['form_id']){?></a><?}?></TD>

		  <?############ delete form button ###########?>
				<TD><?if($site->fdat['form_id']){?><a href="javascript:void(openpopup('forms.php?op=delete&form_id=<?= $site->fdat['form_id']?>','form','413','108'))"><?}?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete<?=(!$site->fdat['form_id'] ? '_inactive' : '')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle><?if($site->fdat['form_id']){?></a><?}?></TD>

		  
        </tr>
      </table>
</TD>
</TR>

<?
# / FUNCTION BAR
################################
?>
  <!-- //Toolbar -->
  <!-- Content area -->

  <tr valign="top"> 
<?
############################
# MIDDLE LIST
?>
		<!-- Middle column -->
		<TD class="scms_middle_dialogwindow">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr class="scms_pane_header"> 
                     <td>			
					   <?=$site->sys_sona(array(sona => "Forms", tyyp=>"admin"))?>
					 </td>
					 <td>
						<!-- Paging -->

						<!-- //Paging -->
					 </td>
                    </tr>
                 </table>
				
			<table width="100%" height="95%" border="0" cellspacing="0" cellpadding="0">
		   <!-- Table header -->	
			  <tr height=10> 
                <td valign="top" class="scms_tableheader">
<?
#######################
# COLUMN HEADERS
?>
					<table width="100%"  border="0" cellspacing="0" cellpadding="0">
						<tr> 

						  <td width="16" nowrap><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/visible.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="Visibility"></td>
						  <td width="25%" nowrap><?=$site->sys_sona(array(sona => "Name", tyyp=>"admin"))?></td>
						  <td width="20%"><?=$site->sys_sona(array(sona => "Profile", tyyp=>"editor"))?></td>
						  <td width="20%"><?=$site->sys_sona(array(sona => "Fields", tyyp=>"admin"))?></td>
						  <td width="20%"><?=$site->sys_sona(array(sona => "DB Table", tyyp=>"xml"))?></td>
						  <td width="15"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/px.gif" WIDTH="15" HEIGHT="1" BORDER="0" ALT=""></td>
						</tr>
					</table>


				</td>
			</tr>
			<!-- // Table header -->

			<tr>
				<td valign=top>
					<!-- Scrollable area -->
					<div id=listing class="scms_middle_div">


				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="scms_table">

				<?

################ get selected  form data
$sql = $site->db->prepare("SELECT * FROM forms ORDER BY form_id DESC ");
$sth = new SQL($sql);

##################
# loop over forms
while($value = $sth->fetch()){

	$href = "javascript:document.location='".$site->self."?form_id=".$value['form_id']."'";
	$dblclick = "void(openpopup('forms.php?op=edit&form_id=".$value['form_id']."','form','366','450'))";


	$profile_def = $site->get_profile(array(id=>$value['profile_id'])); 

	$label = $site->sys_sona(array(sona => $profile_def['name'], tyyp=>"custom", lang_id=>$site->keel));
	$label = $label != '['.$form_info["name"].']' ? $label : '';	# kui s�steemis�na puudub

	$profile_href = "javascript:void(openpopup('profiles.php?profile_id=".$value['profile_id']."','profiles','700','500'))";

?>
				<tr <?=($value['form_id'] == $site->fdat['form_id'] ? ' class="scms_activerow"' : '')?>> 
				<?############# active (visible) ?>
				<td width="20"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/mime/<?if($value[is_active]){?>visible<?}else{?>hidden<?}?>.png" width="16" height="16" alt="">

				<?############# name ?>
                  <td width="25%" nowrap><a href="<?=$href?>" ondblclick="<?=$dblclick?>"><?= $value['name'] ?></a></td>
				
				  <?############# Profile name ?>
				  <td width="20%" nowrap><a href="<?=$href?>" ondblclick="<?=$dblclick?>"><?=$label?></a></td>

				  <?############# Profile fields button ?>
				  <td width="20%" nowrap><a href="<?=$profile_href?>"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/edit.png" BORDER="0" ALT=""></a></td>

				  <?############# source table ?>
				  <td width="20%" nowrap><a href="<?=$href?>" ondblclick="<?=$dblclick?>"><?= $profile_def['source_table'] ?></a></td>
				<?############# preview  ?>

				  <td width="16" align="right"><a href="javascript:void(avaaken('forms.php?op=preview&form_id=<?= $value['form_id'] ?>','600','500','preview'))"><img alt="" src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/viewmag.png" width="16" height="16"  border=0></a></td>
                </tr>
<?
}
# / loop over forms
##################
?>

              </table>
           </div>
		<!-- //Scrollable area -->

          </td>
        </tr>
      </table>

		</TD>
	</TR>
	</TABLE>
	<!-- content table -->	

	
	
	</td>
  </tr>
</table>

</body>
</html>
<?
}
# / LIST
##################
