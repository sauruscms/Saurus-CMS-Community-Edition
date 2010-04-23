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
 * Popup page for modifying external table records
 * 
 * Page has 1 tab:
 * EDIT TAB: shows all data for current record
 * 
 * @param string $tab selected tab name (edit)
 * @param int $id current record ID
 * @param string $op action name
 * @param string $op2 step 2 action name
 */

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");

$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));

#temporary:
error_reporting(7);

# default tab is first one:
$site->fdat['tab'] = $site->fdat['tab']? $site->fdat['tab'] : 'edit';
$op = $site->fdat['op'];
$op2 = $site->fdat['op2'];

###########################
# ACCESS allowed/denied
# decide if accessing this page is allowed or not
$access = 0;

# edit tab : if current user has READ privilege for adminpage "DB data" => allow
if( $site->fdat['tab'] == 'edit'){

	# kas useril on selle admin-lehe kohta Read �igus?
	if($site->user->allowed_adminpage(array('adminpage_id' => 76,))) {
		$access = 1;
	}
}
	####################
	# access denied
	if (!$access) {
		new Log(array(
			'action' => 'access',
			'component' => 'External tables',
			'type' => 'WARNING',
			'message' => sprintf("Access denied: attempt to access page '%s'" , $site->script_name),
		));
		?>
		<center><b><?=$site->sys_sona(array(sona => "access denied", tyyp=>"editor"))?></b>
		<?
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


#################
# STEP2:  SAVE DATA : close popup or don't close and refresh
if($op2) {
	
	##############
	# SAVE EDIT TAB
	if($site->fdat['tab'] == 'edit') {
		####### save data to table

	# get all table fields:
	$external_table_fields = array();
	$external_table_fields = split(",", $site->db->get_fields(array(tabel => $site->fdat['external_table'])) );
	# remove ID field from array:

	$id_key = array_search('id', $external_table_fields); 
	unset($external_table_fields[$id_key]); 

		############ GET FORM DATA and put it into array

		$sql_field_values = array();
		# loop over table fields 
		foreach ($external_table_fields as $key=>$field) {
			# if field was found in form values
			# then add field into sql
			if(isset($site->fdat[$field])) {
				$sql_field_values[$field] = $site->db->quote($site->fdat[$field]);
				if (is_array($sql_field_values[$field])) {
					$sql_field_values[$field] = implode(',',$sql_field_values[$field]);
				}
			} # if field was found in form values
		} # loop over table fields 
		# if name is not defined then set it to 'undefined':
		if(trim($sql_field_values['name']) == '') { $sql_field_values['name'] = 'undefined';}

		############ NEW OR COPY
		if($op == 'new' || $op == 'copy') {
			$parent_id = $site->fdat['group_id'];

	  		$sql = $site->db->prepare("INSERT INTO ".$site->fdat['external_table']." (".join(",",array_keys($sql_field_values)).") VALUES ('".join("','",array_values($sql_field_values))."')");
			#print $sql;
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			$site->fdat['id'] = $sth->insert_id;

			####### op => edit
			$site->fdat['op'] = 'edit';
			$op = 'edit';

			####### write log
			new Log(array(
				'action' => 'create',
				'component' => 'External tables',
				'message' => "New record '".$sql_field_values['name']."' (ID=".$site->fdat['id'].") in table '".$site->fdat['external_table']."' inserted",
			));
		}
		############ EDIT 
		elseif($op == 'edit') {
			foreach ($sql_field_values as $field=>$value) {
				$update_fields[] = $field."='".$value."'";
			}
	  		$sql = $site->db->prepare("UPDATE ".$site->fdat['external_table']." SET ".join(",",$update_fields)." WHERE id=?",$site->fdat['id']);
			#print $sql;
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	

			####### write log
			new Log(array(
				'action' => 'update',
				'component' => 'External tables',
				'message' => "Record '".$sql_field_values['name']."' (ID=".$site->fdat['id'].") in table '".$site->fdat['external_table']."' updated",
			));
		} # op
		############# if update then REDIRECT PAGE: to get correct url again
		if($site->fdat['op2']!='saveclose') {
			header("Location: ".$site->self."?tab=".$site->fdat['tab']."&external_table=".$site->fdat['external_table']."&profile_id=".$site->fdat['profile_id']."&id=".$site->fdat['id'].($site->fdat['callback'] ? '&callback='.$site->fdat['callback'] : ''));
		}

	}
	# / SAVE EDIT TAB
	##############

	############ DELETE
	if($op == 'delete') {
		$sql = $site->db->prepare("DELETE FROM ".$site->fdat['external_table']." WHERE id=?",$site->fdat['id']);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());	
#echo "op2:".$op2;
		####### write log
		new Log(array(
			'action' => 'delete',
			'component' => 'External tables',
			'message' => "Record ID=".$site->fdat['id']." in table '".$site->fdat['external_table']."' deleted",
		));
	} # op

	##############
	# refresh opener and close popup
	if($site->fdat['callback'])
	{ 
		?>
		<script type="text/javascript">
			<?=$site->fdat['callback'];?>('<?=$op2;?>', <?=(int)$site->fdat['id'];?>);
		</script>
		<?php
	}
	
	if($op2=='saveclose' || $op2=='deleteconfirmed') {
		?>
		<SCRIPT language="javascript">
		<!--
//dont refresh entire admin-area			
			window.opener.location=window.opener.location;
			window.close();
		// -->
		</SCRIPT>
		<?
		exit;
	}
	

}
# / STEP2:  SAVE DATA 
#################




##################
# 0. HTML
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding ? $encoding : $site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/datepicker.css">
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/jquery.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/datepicker.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'];?>/common.js.php"></script>
</head>

<?
######################
# DELETE CONFIRMATION WINDOW
if($op == 'delete') {
?>
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<input type=hidden name=id value="<?=$site->fdat['id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
	<input type=hidden id=external_table name=external_table value="<?=$site->fdat['external_table']?>">

<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?
	# show confirmation
	echo $site->sys_sona(array(sona => "kustuta", tyyp=>"editor"))."? ";
	echo $site->sys_sona(array(sona => "are you sure?", tyyp=>"admin"));
	$allow_delete = 1;
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
<?
}	
# / DELETE CONFIRMATION WINDOW
######################


######################
# 1. tab EDIT

elseif($site->fdat['tab'] == 'edit') { 
################# BODY START
?>
<body class="popup_body">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<?
	########### tabs
	print_tabs();
?>
  <tr> 
    <td valign="top" width="100%" class="scms_dialog_area" height="100%"> 
<div id="scroll">
	<table width="100%"  border="0" cellspacing="0" cellpadding="3">
	
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<input type=hidden id=tab name=tab value="<?=$site->fdat['tab']?>">
	<input type=hidden id=id name=id value="<?=$site->fdat['id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
	<input type=hidden id=external_table name=external_table value="<?=$site->fdat['external_table']?>">
	<?php if($site->fdat['callback']) { ?><input type="hidden" id="callback" name="callback" value="<?=$site->fdat['callback']?>"><?php } ?>
<?
$sql = $site->db->prepare("SELECT * FROM ".$site->fdat['external_table']." WHERE id=?",$site->fdat['id']);
$sth = new SQL($sql);
$data = array();
while($tmp_rec = $sth->fetch('ASSOC')) {
	$data[] = $tmp_rec;
}
foreach($data as $arr){
	foreach($arr as $field=>$value){
		$rec_data[$field] = $value;
	}
}

	####### ID & Profile ID
?>
	<tr><td>ID</td><td><?=$rec_data['id']?></td></tr>
	<tr><td nowrap>Profile ID</td><td><input type="text" name="profile_id" class="scms_flex_input" value="<?=$site->fdat['profile_id']?>"></td></tr>
<?	
	####################
	# Additional info: attributes list

	# get profile
	$profile_def = $site->get_profile(array("id"=>$site->fdat['profile_id'])); 
	$profile_fields = unserialize($profile_def['data']);	# profile_fields is now array of ALL fields, indexes are fieldnames

	###################
	# print profile fields rows
	print_profile_fields(array(
		'profile_fields' => $profile_fields,
		'field_values' => $rec_data,
		'fields_width' => '300px',
	));
?>

        </table>
			
		</div>
    </td>
  </tr>
	<?#################### BUTTONS ###########?>
	<tr> 
    <td align="right" valign="top" class="scms_dialog_area_bottom"> 
     <input type="button" value="<?=$site->sys_sona(array(sona => "apply", tyyp=>"editor")) ?>" onclick="actionButtonClick(1);">
    <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="actionButtonClick(2);">
	<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="actionButtonClick(0);">
	<script type="text/javascript">
		function actionButtonClick(action)
		{
			if(action == 0) //close
			{
				<?php if($site->fdat['callback']) { ?><?=$site->fdat['callback'];?>('close', <?=(int)$site->fdat['id'];?>);<?php } ?>
				window.close();
			}
			else if(action == 1) //apply
			{
				document.frmEdit.op2.value = 'save';
				document.frmEdit.submit();
			}
			else if(action == 2) //saveclose
			{
				document.frmEdit.op2.value = 'saveclose';
				document.frmEdit.submit();
			}
		}
	</script> 
    </td>
  </tr>

</form>

<?
}
# / 1. tab EDIT
######################
?>
			  
</table>

<?

$site->debug->print_hash($site->fdat,1,"FORM DATA");
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 

?>
</body>
</html>




<?
######################
# FUNCTION PRINT_TABS()

function print_tabs() {

	global $site;
?>
  <tr> 
    <td valign="top" width="100%"> 
     <table border="0" cellspacing="0" cellpadding="0" width="100%" style="height:21px">
      <tr>
       <td class="scms_tabs_empty">&nbsp;&nbsp;&nbsp;</td>
		<?
		# set all tabs: array[tab name] = tab translated name 
		$tab_arr = array();
		$tab_arr['edit'] = $site->fdat['external_table'];

		foreach ($tab_arr as $tab=>$tab_title) {
		?>
        <td class="scms_<?=$site->fdat['tab']==$tab?'':'in'?>active_tab" nowrap>
		<?########## tab title #######?>
		<?	
		$tab_title = "<a href=".$site->self."?tab=".$tab."&op=".$site->fdat['op']."&external_table=".$site->fdat['external_table']."&id=".$site->fdat['id']."&profile_id=".$site->fdat['profile_id'].">".$tab_title."</a>";
		?>
		<?=$tab_title?></td>
		<? } # loop over tabs ?>

          <td width="100%" class="scms_tabs_empty">&nbsp;</td>
      </tr>
    </table>
    </td>
  </tr>
<?
}
# / FUNCTION PRINT_TABS()
######################