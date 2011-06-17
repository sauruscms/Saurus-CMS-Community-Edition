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
 * Saurus CMS admin page "Extenions Manager > Extensions" for extensions management
 * 
 * Page is divided into 2 parts:
 * LEFT: extension type tree, MIDDLE: extension list
 * Allows add, modify, delete extensions in database
 * 
 * @param int name - selected extension ID
 * @param string op - action name
 */

global $site;

$class_path = "../classes/";
include_once($class_path."port.inc.php");
include_once($class_path."adminpage.inc.php");
include_once($class_path."extension.class.php");
require_once($class_path."archive.class.php");
require_once($class_path."lgpl/pclzip.class.php");

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


##################
# SYNC extension directories with real data in database TABLE
if($site->fdat['op'] == "sync") {

	sync_extensions();

	header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->self.($site->fdat['extension_id']?"?extension_id=".$site->fdat['extension_id']:''));

}
# / SYNC with real TABLE in database
##################
if($site->fdat['op'] == "upload"){
	$ext = new extension_upload();
}

if($site->fdat['upload'])
{
	verify_form_token();
	$ext->tmp_location=$site->absolute_path."shared/".time()."_".rand(1,837838);
	$ext->extensions_folder=$site->absolute_path."extensions";
	$ext->overwrite_extension=$site->fdat['overwrite'];
	$ext->unpack_extension('extension'); // variable is the array name in $_FILES where the file resides.
	$ext->find_file('extension.config.php');
	if($ext->validate_extension()){
		sync_extensions();
		$synced = 1;
	}
	$zip= new archive();
	$zip->deltree($ext->tmp_location);
	@rmdir($ext->tmp_location);
}

if($site->fdat['download']){

	$dlzip = new extension_download($site->fdat['download']);
	$dlzip->web_folder=$site->absolute_path;
	$dlzip->validate_download();
	if(!$dlzip->error){
		$dlzip->download_extension();
	}

}


if($site->fdat['op'] == "upload"){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head> 	
		<title>Extension upload<?//=$site->sys_sona(array('sona' => 'images configuration', 'tyyp' => 'Admin'));?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/default_admin_page.css" media="screen" />
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/yld.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/ie_position_fix.js"></script>
<?if($synced == 1){?>
	<script type="text/javascript">
		window.opener.location.href = window.opener.location='extensions.php';
		window.close();
	</script>
<?}?>
		<script type="text/javascript">
			window.onload = function()
			{
				new contentBox('content_box', '40px', '0px', '40px', '0px');
			}
			
			var contentBox = function(id, top, right, bottom, left)
			{
				this.box = document.getElementById(id);
				this.box.style.top = top;
				this.box.style.right = right;
				this.box.style.bottom = bottom;
				this.box.style.left = left;
			}
		</script>
	</head>
	<body id="popup">
		<form name="upload_extension" method="POST" enctype="multipart/form-data">
		<?php create_form_token('upload-extension'); ?>
		<div id="mainContainer">
			<div class="titleArea">
				<?=$site->sys_sona(array(sona => "extension_upload", tyyp=>"admin"))?>
			</div><!-- / titleArea -->
			<div class="contentArea" id="content_box">
				<div class="contentAreaContent">

							<table cellspacing="0" cellpadding="10" width="100%">
								<tr>
									<td>
<?
if($ext->error()){
	echo "<strong><span style='color:red'>".$site->sys_sona(array(sona => "extension_upload_error", tyyp=>"admin"))."</span></strong>";
}
?>
									</td>
								</tr>
								<tr>
									<td>
									<input type="hidden" name="op" value="upload">
									<input type="file" name="extension" style="width:250px"><br>
									<input type="checkbox" name="overwrite" id="overwrite" value="yes" 
									<?
	if($site->fdat['upload']){
		if($site->fdat['overwrite']){
		echo "checked";
		}
	}else{
		echo "checked";
	}
	?>><label for="overwrite"><?=$site->sys_sona(array(sona => "overwrite", tyyp=>"admin"))?></label>
									</td>
								</tr>
							</table>

				</div><!-- / contentAreaContent -->
			</div><!-- / contentArea -->
			<div class="footerArea">
				<div class="actionButtonsArea">

						<input type="submit" value="<?=$site->sys_sona(array(sona => "upload", tyyp=>"admin"))?>" name="upload">
						<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor"))?>" onclick="window.close();" />

				</div>
			</div class="footerArea"><!-- / footerContainer -->
		</div><!-- / mainContainer -->
		</form>
	</body>
</html>


<?}else{?>
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

	<?if($site->fdat['download'] && $dlzip->error()){?>
		alert('<?=$site->sys_sona(array(sona => "extension_dl_error", tyyp=>"admin"))?>');
	<?}?>
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

	<?######### extension FUNCTION BAR ############?>
      <table border="0" cellpadding="0" cellspacing="0">
        <tr> 

		  <?############ SYNC button ###########?>
				<TD><a href="<?= $site->self ?>?op=sync" class="scms_button_img"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/refresh.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>&nbsp;<?=$site->sys_sona(array('sona' => 'Refresh', 'tyyp' => 'admin'))?></a></TD>
		  <?############ Upload button ###########?>

				<TD><a href="#" onclick="openpopup('extensions.php?op=upload', 'extension_upload', '350','200');" class="scms_button_img"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/up.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>&nbsp;<?=$site->sys_sona(array('sona' => 'Upload', 'tyyp' => 'admin'))?></a></TD>

		  
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
# extension TYPES MENUTREE
?>
<td >
	<!-- content table -->	
	<TABLE class="scms_content_area" border=0 cellspacing=0 cellpadding=0>
	<TR>


<?
############################
# MIDDLE LIST
?>
		<!-- Middle column -->
		<TD class="scms_middle_dialogwindow">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr class="scms_pane_header"> 
						<?###### icon + headline ######?>
					<td nowrap>
					<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/mime/extension.png'?>" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>
					&nbsp;
					<?=$site->sys_sona(array(sona => "Extensions", tyyp=>"admin"))?>
					 </td>
					 <td>
						<!-- Paging -->

						<!-- //Paging -->
					 </td>
                    </tr>
                 </table>
				
			<table width="100%" height="95%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td valign="top">
					<!-- Scrollable area -->
					<div id=listing class="scms_middle_div">
					<style type="text/css">
						table.scms_table td {
							vertical-align: top;
						}
						table.scms_table a {
							display: inline;
						}
						table.scms_table {
							margin: 5px 0px;
							border-bottom: 1px solid #ecebe9;
						}
					</style>

<? 
	########## FROM
	$from_sql = "FROM extensions ";

	# default values:
	$site->fdat['sortby'] = $site->fdat['sortby'] ? $site->fdat['sortby'] : 'name';
	$site->fdat['sort'] = $site->fdat['sort'] ? $site->fdat['sort'] : 'ASC';

	########### ORDER
	if($site->fdat['sortby']){
		$order = " ORDER BY ".$site->fdat['sortby']." ".$site->fdat['sort'];
	}

	########### SQL

	$sql = $site->db->prepare("SELECT DATE_FORMAT(version_date,'%d.%m.%Y') AS fversion_date, extensions.*");
	$sql .= $from_sql;
	$sql .= $order;
#	$sql .= $pagenumbers['limit_sql'];

#print $sql;
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	###########################
	# loop over rows
	while ( $ext = $sth->fetch() ) {

	$href = "javascript:document.location='".$site->self."?name=".$ext['name']."'";
	$dblclick = "void(openpopup('edit_extension.php?op=edit&name=".$ext['name']."','extension','366','475'))";
?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="scms_table">
		<tr>
			<?############# active (visible) ?>

			<td><input type="checkbox" disabled="disabled" <?if($ext[is_active]){?>checked="checked"<?}?>></td>

			<?############# icon ?>

			<td width="32"><?if($ext['icon_path']){?><img src="<?=$site->CONF['wwwroot'].'/'.$ext['path'].$ext['icon_path']?>" height="32" width="32" alt=""><?}else{?><img src="<?=$site->CONF['wwwroot'].$site->CONF['img_path']?>/px.gif" height="32" width="32" alt=""><?}?></td>
			<td width="90%">
				<table width="100%" border="0" cellspacing="5" cellpadding="0">
					<?############# name + version + date ?>
					<tr>
						<td><strong><?= $ext['title'] ?></strong> (<?= $ext['name'] ?>) <?= $ext['version'] ?> <?= ($ext['version_date']>0 ? $ext['fversion_date'] : '')?></td>
					</tr>
					<?############# description ?>
					<tr>
						<td><?= $ext['description'] ?></td>
					</tr>
					<tr>
						<td><?= $ext['author'] ?></td>
					</tr>
				</table>
			</td>

			<td width="50"><ul class="scms_button_row" style="float: right;">
			<?if($ext['is_downloadable']=='1'){?><li><a href="extensions.php?download=<?=$ext['extension_id'];?>" class="button_download"></a></li><?}?>
			<li><a href="javascript:void(openpopup('edit_extension.php?op=uninstall&name=<?= $ext['name'] ?>','extension','413','208'))" class="button_delete"></a></li></ul>
				<img src="<?=$site->CONF['wwwroot'].$site->CONF['img_path']?>/px.gif" height="32" width="32" alt=""></td>
		</tr>
	</table>
<?
}
# / loop over records
##################
?>

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
<?}