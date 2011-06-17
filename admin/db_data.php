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
 *  Saurus CMS admin page for managing external tables in database
 * 
 * Page is divided into 2 parts:
 * LEFT: table names
 * RIGHT: show read-only field list 
 * 
 * @param int $table_name selected table name
 * @param string $db_search db search string
 * @param string $data_search user search string
 * 
 */


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

#################
# GET TABLE INFO
if($site->fdat['table_name']) {
	$breadcrumb_focus_str = ",'".$site->sys_sona(array(sona => "tabel", tyyp=>"editor")). " ".$site->fdat['table_name']."'";
}

###############################
# SAVE & CLOSE

if($site->fdat['op2'] == 'save' || $site->fdat['op2'] == 'saveclose') {
	verify_form_token();	
	if($site->fdat['table_name']) {
		###### create table with default fields id, profile_id, name
		$sql = $site->db->prepare("create table ".$site->fdat['table_name']." (id int UNSIGNED NOT NULL AUTO_INCREMENT , profile_id int (4) UNSIGNED NOT NULL DEFAULT '0', name varchar (255) , PRIMARY KEY (id))");
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		####### write log
		new Log(array(
			'action' => 'create',
			'component' => 'External tables',
			'message' => "Table '".$site->fdat['table_name']."' inserted",
		));
	}

	################
	# kui vajutati salvesta nuppu, pane aken kinni
	if ($site->fdat['op2']=='saveclose') {
	?>
	<HTML>
	<SCRIPT language="javascript"><!--
		var oldurl = window.opener.location.toString();
		oldurl = oldurl.replace(/\?table_name=(\w+)/g, "");
		if('<?=$site->fdat[op]?>'=='new') {
			newurl = oldurl + '?table_name=<?=$site->fdat['table_name']?>'; 
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
# / SAVE & CLOSE
###############################


###############################
# DELETE TABLE or ONE FIELD
if($site->fdat['op2'] == 'deleteconfirmed' && $site->fdat['table_name']) {
	verify_form_token();
	# double check: delete only when no data found
	$data_count = 0;
	$sql = $site->db->prepare("SELECT COUNT(*) FROM ".$site->fdat['table_name']." ");
	$sth = new SQL($sql);
	$data_count = $sth->fetchsingle();


	if(!$data_count) { # delete only when allowed
		####### 1. delete FIELD
		if($site->fdat['field_name']) {
			# security check: check if user didn't tried to mess with input and entered comma separated list
			if(!stristr($site->fdat['field_name'], ',')){
				# 1) You can't delete last column with ALTER TABLE. Use DROP TABLE instead
				$existing_fields = array();
				if(trim($site->db->get_fields(array(tabel => $site->fdat['table_name'])))){
 					$existing_fields = split(",", trim($site->db->get_fields(array(tabel => $site->fdat['table_name']))) );
				}
				if(sizeof($existing_fields) == 1){
					$sql = $site->db->prepare("DROP TABLE ".$site->fdat['table_name']." ");
				} else {
					$sql = $site->db->prepare("ALTER TABLE ".$site->fdat['table_name']." DROP ".$site->fdat['field_name']);
				}
				$sth = new SQL($sql);			
				if($sth->error){print $sth->error; $errors=1;}
				####### write log
				if(!$sth->error){
					new Log(array(
						'action' => 'delete',
						'component' => 'External tables',
						'message' => "Field '".$site->fdat['field_name']."' from table '".$site->fdat['table_name']."' deleted".(sizeof($existing_fields) == 1? ". Table also deleted.":''),
					));
				}
			}
			else { # write error message to log
				####### write log
				new Log(array(
					'action' => 'delete',
					'component' => 'External tables',
					'type' => 'WARNING',
					'message' => sprintf("Access denied: attempt to delete mupltiple fields: %s", $site->fdat['field_name']),
				));
			}

		}
		####### 2. delete TABLE
		else {
			# security check: check if user didn't tried to mess with input and entered comma separated list
			if(!stristr($site->fdat['table_name'], ',')){
				$sql = $site->db->prepare("DROP TABLE ".$site->fdat['table_name']." ");
				$sth = new SQL($sql);		
				if($sth->error){print $sth->error; $errors=1;}				
				####### write log
				if(!$sth->error){
					new Log(array(
						'action' => 'delete',
						'component' => 'External tables',
						'message' => "Table '".$site->fdat['table_name']."' deleted",
					));
				}
			}
			else { # write error message to log
				####### write log
				new Log(array(
					'action' => 'delete',
					'component' => 'External tables',
					'type' => 'WARNING',
					'message' => sprintf("Access denied: attempt to delete mupltiple fields: %s", $site->fdat['table_name']),
				));
			}
		}
	} # allowed to delete
	if(!$errors){
	?>
	<HTML>
	<SCRIPT language="javascript"><!--
		var oldurl = window.opener.location.toString();
		oldurl = oldurl.replace(/\?table_name=(\w+)/g, "");
		if('<?=$site->fdat[field_name]?>'=='') {
			newurl = oldurl + '?table_name=<?=$site->fdat['table_name']?>'; 
			window.opener.location=newurl;
		} else {
			window.opener.location=window.opener.location;	
		}
		window.close();
	// --></SCRIPT>
	</HTML>
	<?
	}
	exit;
}
# / DELETE TABLE or ONE FIELD
###############################


###############################
# 1. NEW TABLE

if($site->fdat['op'] == "new") {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/admin_menu.js"></SCRIPT>
</head>
<body class="popup_body" onLoad="this.focus();document.forms['vorm'].table_name.focus();">

<FORM action="<?=$site->self ?>" method="post" name="vorm">
<?php create_form_token('data-new'); ?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<tr> 
    <td valign="top" width="100%" class="scms_dialog_area_top"  height="100%">
	  <table width="100%"   border="0" cellspacing="0" cellpadding="2">
	  <?############ name #########?> 
	  <tr> 
		<td><?=$site->sys_sona(array(sona => "nimi", tyyp=>"editor"))?>: </td>
		<td width="100%"><input type=text name=table_name value="<?= $site->fdat['op']=='new'? 'ext_':$site->fdat['table_name']?>" class="scms_flex_input" onkeyup="javascript: if(event.keyCode==13){vorm.submit();}"></td>
	  </tr>

	  </table>
	</td>
</tr>
	<?############ buttons #########?>
	<tr> 
	  <td align="right" valign="top" class="scms_dialog_area_bottom"> 
    <!--     <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript: document.forms['vorm'].op2.value='save';this.form.submit();">
	-->
         <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:document.forms['vorm'].op2.value='saveclose';this.form.submit();">
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

<?########### hidden ########?>
<?php create_form_token(''); ?>
<INPUT TYPE="hidden" name="op" value="<?=$site->fdat['op']?>">
<INPUT TYPE="hidden" name="op2" value="save">
</form>
</body>
</html>
<?
exit;
}
# / 1. NEW TABLE
###############################


######################
# 2.  DELETE CONFIRMATION WINDOW (both table and one field)
if($site->fdat['op'] == 'delete' && $site->fdat['table_name']) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
</head>
<body  class="popup_body">
	<form name="frmEdit" action	="<?=$site->self?>" method="POST">
	<?php create_form_token('delete-data'); ?>
	<input type=hidden name=table_name value="<?=$site->fdat['table_name']?>">
	<input type=hidden name=field_name value="<?=$site->fdat['field_name']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?
	# check if allowed to delete
	# 1. if exists any data in that table, then don't allow to delete
	$data_count = 0;
	$sql = $site->db->prepare("SELECT COUNT(*) FROM ".$site->fdat['table_name']." ");
	$sth = new SQL($sql);
	$data_count = $sth->fetchsingle();

	# 2. if table deleting AND exists any field in that table, then don't allow to delete
	if(!$site->fdat['field_name']){ 
		$existing_fields = array();
		if(trim($site->db->get_fields(array(tabel => $site->fdat['table_name'])))){
 			$existing_fields = split(",", trim($site->db->get_fields(array(tabel => $site->fdat['table_name']))) );
		}
	}
	if($data_count > 0) {
		# show error message
		echo "<font color=red>".$site->sys_sona(array(sona => "Permission denied", tyyp=>"editor"))."</font><br><br>";
		echo $site->sys_sona(array(sona => "Children count", tyyp=>"admin")).": <b>".$data_count."</b>";
	}
	elseif(!$site->fdat['field_name'] && sizeof($existing_fields) > 0) {
		# show error message
		echo "<font color=red>".$site->sys_sona(array(sona => "Permission denied", tyyp=>"editor"))."</font><br><br>";
	}
	# show confirmation
	else {
		echo $site->sys_sona(array(sona => "kustuta", tyyp=>"editor"))." \"<b>".($site->fdat['field_name']?$site->fdat['field_name']:$site->fdat['table_name'])."</b>\"? ";
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
	exit;
}	
# / 2. DELETE CONFIRMATION WINDOW (both table and one field)
######################

###############################
# 3. LIST
else {

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/admin_menu.js"></SCRIPT>
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

	<?######### FUNCTION BAR ############?>
      <table border="0" cellpadding="0" cellspacing="0">
        <tr> 
		  <?############ new button ###########?>
            <td nowrap><a href="javascript:void(openpopup('<?=$site->self?>?op=new','table','366','150'))"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/filenew.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id=pt></td>

		  <?############ delete table button ###########?>
			<TD><?if($site->fdat['table_name']){?><a href="javascript:void(openpopup('<?=$site->self?>?op=delete&table_name=<?= $site->fdat['table_name']?>','table','413','108'))" ><?}?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete<?=($site->fdat['table_name']?'':'_inactive')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle><?if($site->fdat['table_name']){?></a><?}?></TD>


		<?###### wide middle cell ######?>
		<td width="100%"></td>
		  
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
# CONTENT TABLE

?>
		<!-- Middle column -->
		<TD class="scms_middle_dialogwindow">
<?
############################
# TABLES MENU
?>
	<!-- content table -->	
	<TABLE class="scms_content_area" border=0 cellspacing=0 cellpadding=0>
	<TR>
		<!-- Left column -->
		<TD class="scms_left">
			<div id=navigation class="scms_left_div">
		
				<table width="100%" height="100%"  border="0" cellpadding="0" cellspacing="0">
					<!-- I grupp -->
					<tr>
						<td valign=top>
     <?
	  #####################
	  # TREE
		require_once($class_path.'menu.class.php');

		######## TABLE NAMES TREE
		$sql = $site->db->prepare("show tables");
		$sth = new SQL($sql);
		while ($tbl_data = $sth->fetchsingle()){
			$tables[] = $tbl_data;
		}

		$temp_tree = array();
		foreach($tables as $table){
			$tmp = array("id" => $table, "name"=>$table, "parent"=>'');
			$temp_tree[] = $tmp;
		}
		$menu = new Menu(array(
			width=> "100%",
			tree => $temp_tree,
			param_name => "table_name",

		));
		# print tree
		echo $menu->source;
		echo "<br>";
?>
						</td>
					</tr>
					<!-- //IV grupp -->
							
				</table>


		</DIV>
	</TD>	<!--cell in content table -->	

<?
# / db TYPES MENU
############################
?>

<?
############################
# MIDDLE LIST
?>
		<!-- Middle column -->
		<TD class="scms_middle_dialogwindow">
 
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="height:100%">
     <tr> 
      <td  valign="top"> 
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr class="scms_pane_header">
		        <td ><?=$site->sys_sona(array(sona => "fields", tyyp=>"admin"));?></td>
		        </tr>
		    </table>

<?#############
# if db selected
if($site->fdat['table_name']) {
?>
          </td>
        </tr>
	<?
	#################
	# table column names
	?>
	<tr><td>
		<table width="100%"  border="0" cellspacing="0" cellpadding="0">
		   <tr id="headerrow"  class="scms_tableheader">
			<td width="20%" class="scms_tableheader_active"><?=$site->sys_sona(array(sona => "name", tyyp=> "admin"))?></td>
			<td width="20%" ><?=$site->sys_sona(array(sona => "type", tyyp=> "admin"))?></td>
			<td width="10%" >Null</td>
			<td width="10%" >Key</td>
			<td width="20%" >Default</td>
			<td width="20%" >Extra</td>

		</tr></table>
	</td></tr>
	<?
	# / COLUMN NAMES
	#################
	?>

		<tr>
          <td height="100%" valign="top"> 
			<!-- Scrollable area -->
			<div id=listing class="scms_middle_div">
			  <table width="100%"  border="0" cellspacing="0" cellpadding="3">
<?
	########### SQL

 	$sql = "SHOW COLUMNS FROM ".$site->fdat['table_name'];
#print $sql;
	$sth = new SQL($sql);

		#################
		# loop over fields
		while($row = $sth->fetch()){
		?>
          <tr>
			<?########## name ?>
			<td width="20%"><?=($row['Field'] ? $row['Field'] : '&nbsp;')?></td>
			<td width="20%"><?=($row['Type'] ? $row['Type'] : '&nbsp;')?></td>
			<td width="10%"><?=($row['Null'] ? $row['Null'] : '&nbsp;')?></td>
			<td width="10%"><?=($row['Key'] ? $row['Key'] : '&nbsp;')?></td>
			<td width="20%"><?=($row['Default'] ? $row['Default'] : '&nbsp;')?></td>
			<td width="20%"><?=($row['Extra'] ? $row['Extra'] : '&nbsp;')?></td>

			<?##### delete ######?>
			<td width="16"><a href="javascript:void(openpopup('<?=$site->self?>?op=delete&table_name=<?=$site->fdat['table_name']?>&field_name=<?=$row['Field']?>','delete','413','108'));"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete.png" alt="Remove" width="16" height="16" border="0"  hspace="3"></a></td>
          </tr>
		<?
		}
		# / loop over obj_asset
		#################
		?>
		</table>

    </div>
	<!-- //Scrollable area -->
<?
}
# /if db selected
#############
?>

	</td>
     </tr>
	
	</table>
	
	
	</td>
<?
# / MIDDLE LIST
############################
?>

  </tr>
</table>
</body>
</html>
<?
}
# / 3. LIST
#######################