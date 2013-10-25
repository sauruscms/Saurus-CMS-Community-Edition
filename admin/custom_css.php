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
 * Saurus CMS admin page "Visual Design > CSS Styles"
 * 
 */


global $site;

$class_path = '../classes/';
include_once($class_path.'port.inc.php');
include($class_path.'adminpage.inc.php');

$site = new Site(array(
	on_debug=>0,
	on_admin_keel => 1
));

######### get adminpage name
$adminpage_names = get_adminpage_name(array('script_name' => $site->script_name));
$parent_pagename = $adminpage_names['parent_pagename'];
$pagename = $adminpage_names['pagename'];


$op = $site->fdat['op'];

$site->debug->msg($site->CONF['wwwroot'].$site->CONF['adm_img_path']);
$site->debug->msg("OP = $op");

if (!$site->user->allowed_adminpage()) {
	exit;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/admin_menu.js"></script>
<script type="text/javascript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>');
//-->
</script>
</head>

<body>
<?php
#####################
# SAVE
if ($site->fdat['save']) {

	verify_form_token();
	############# CUSTOM CSS

	$sql = "SELECT * FROM css WHERE name='custom_css'";
	$sth = new SQL($sql);
	$css = $sth->fetch();

	### UPDATE
	if($sth->rows){ 
		$sql = $site->db->prepare("UPDATE css SET data=?, is_active=? WHERE name=?",
			$site->fdat['custom_css'],
			$site->fdat['is_active_custom_css'],
			'custom_css'
		);
		$sth = new SQL($sql);
	}
	### INSERT
	else {
		$sql = $site->db->prepare("INSERT INTO css (name, data, is_active) VALUES(?,?,?)",
			'custom_css',
			$site->fdat['custom_css'],
			$site->fdat['is_active_custom_css']
		);
		$sth = new SQL($sql);
	}

	############# WYSIWYG FONTS CSS (always active)

	$sql = "SELECT * FROM css WHERE name='wysiwyg_css'";
	$sth = new SQL($sql);
	$css = $sth->fetch();

	### UPDATE
	if($sth->rows){ 
		$sql = $site->db->prepare("UPDATE css SET data=?, is_active=? WHERE name=?",
			$site->fdat['wysiwyg_css'],
			1,
			'wysiwyg_css'
		);
		$sth = new SQL($sql);
		
	}
	### INSERT
	else {
		$sql = $site->db->prepare("INSERT INTO css (name, data, is_active) VALUES(?,?,?)",
			'wysiwyg_css',
			$site->fdat['wysiwyg_css'],
			1
		);
		$sth = new SQL($sql);
	}

	############# WYSIWYG GENERAL CSS (always active)

	$sql = "SELECT * FROM css WHERE name='wysiwyg_css_general'";
	$sth = new SQL($sql);
	$css = $sth->fetch();

	### UPDATE
	if($sth->rows){ 
		$sql = $site->db->prepare("UPDATE css SET data=?, is_active=? WHERE name=?",
			$site->fdat['wysiwyg_css_general'],
			1,
			'wysiwyg_css_general'
		);
		$sth = new SQL($sql);

	}
	### INSERT
	else {
		$sql = $site->db->prepare("INSERT INTO css (name, data, is_active) VALUES(?,?,?)",
			'wysiwyg_css_general',
			$site->fdat['wysiwyg_css_general'],
			1
		);
		$sth = new SQL($sql);
	}
	
?>
	<script type="text/javascript">
	<!--
		window.location=window.location;
	//-->
	</script>
<?php
	
}
# / SAVE
#####################
?>
<?php
#################
# CONTENT TABLE

## LOAD DATA
$sql = "SELECT * FROM css";
$sth = new SQL($sql);
while ($tmp = $sth->fetch()){
	$css[$tmp['name']] = $tmp;
}
?>


<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<form action="<?=$site->self ?>" name="dataform" method="POST">
	<?php create_form_token('edit-styles'); ?>
	<input type="hidden" name="save" value="1">

 <?php 
 ##############
 # FUNCTION BAR
 ?>
  <!-- Toolbar -->
  <tr>
	<td class="scms_toolbar">
		<TABLE cellpadding=0 cellspacing=0 border=0>
			<TR>	
				<?php ############ save button ###########?>
				 <td nowrap><a href="javascript:document.forms['dataform'].submit();"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/filesave.png" border="0" id="pt"> <?=$site->sys_sona(array(sona => "salvesta", tyyp=>"editor"))?></a>
				 </td>

				<?php ###### wide middle cell ######?>
				<td width="100%"></td>				
			</TR>
			</TABLE>
	</td>
  </tr>
  <!-- //Toolbar -->
<?php 
 # / FUNCTION BAR
 ################
?>


  <!-- Content area -->
  <tr valign="top"> 
    <td >
	
	<TABLE class="scms_content_area" border=0 cellspacing=0 cellpadding=0>
	<TR>
		<!-- Middle column -->
		<TD class="scms_middle_dialogwindow" style="padding-left:10px">
			<?php 
			################
			# DATA TABLE
			?>  
			<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td valign=top width="50%" style="padding-right: 7px;">
								<input type="checkbox" id="is_active_custom_css" name="is_active_custom_css" value="1" <?=($css['custom_css']['is_active'] == 1 ? 'checked' : '');?>>
							&nbsp;<label for="is_active_custom_css"><?=$site->sys_sona(array(sona => "Site CSS styles", tyyp=>"admin"))?></label>
                </td>
				<td valign=center width="50%" style="padding-left: 7px;">
					<?=$site->sys_sona(array(sona => "WYSIWYG Fonts", tyyp=>"admin"))?>
                </td>
              </tr>
			<tr>
				<td valign=top height="100%" width="50%" style="padding-right: 7px;">
					<?php 
					#################
					# CUSTOM CSS
					?>	  
					<!-- Scrollable area -->
					<textarea name="custom_css" class="scms_flex_input" style=" width:100%; height: 99%; min-height:380px"><?=htmlspecialchars($css['custom_css']['data']);?></textarea>
					<!-- //Scrollable area -->
                </td>
				<td valign=top height=100% width="50%" style="padding-left: 7px;">
					<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td height="50%">
								<?php 
								#################
								# WYSIWYG Dropdown Fonts CSS
								?>	  
								<!-- Scrollable area -->
								<textarea name="wysiwyg_css" class="scms_flex_input" style=" width:100%; height: 99%; min-height:190px"><?=htmlspecialchars($css['wysiwyg_css']['data']);?></textarea>
								<!-- //Scrollable area -->
							</td>
						</tr>
						<tr>
							<td>
								<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td height="30">
											<?=$site->sys_sona(array(sona => "WYSIWYG General", tyyp=>"admin"))?>
										</td>
								</table>
			                </td>
						</tr>
						<tr>
							<td height="50%" style="padding-bottom: 4px;">
								<?php 
								#################
								# WYSIWYG General CSS
								?>	  
								<!-- Scrollable area -->
								<textarea name="wysiwyg_css_general" class="scms_flex_input" style=" width:100%; height: 99%; min-height:190px"><?=htmlspecialchars($css['wysiwyg_css_general']['data']);?></textarea>
								<!-- //Scrollable area -->
							</td>
						</tr>
					</table>
                </td>
              </tr>
            </table>
			<?php 
			# / DATA TABLE
			################
			?> 
		</TD>
	</TR>
	</TABLE>
   </div>

    </td>
	</form>
  </tr>
  <!-- // Content area -->
</table>

<?php 
# / CONTENT TABLE
################
$site->debug->print_msg(); 

?>
</body>
</html>
