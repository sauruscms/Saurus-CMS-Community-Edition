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
 * Saurus CMS admin page "Languages > Language Settings"
 * 
 */

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");

#Get debug cookie muutuja
$debug = $_COOKIE["debug"] ? 1:0;

$site = new Site(array(
	on_debug=>$debug,
	on_admin_keel => 1
));

if (!$site->user->allowed_adminpage()) {
	exit;
}

######################
# leida valitud keele põhjal õige lehe encoding,
# admin-osa keel jääb samaks

$keel_id = isset($site->fdat[flt_keel]) ? $site->fdat[flt_keel] : $site->fdat[keel_id];
if (!strlen($keel_id)) { $keel_id = $site->keel; }

$sql = "SELECT encoding FROM keel where keel_id = ?";
$sql = $site->db->prepare($sql,$keel_id);
$sth = new SQL($sql);
$site->debug->msg($sth->debug->get_msgs());	
$page_encoding = $sth->fetchsingle();


$sst_id = $site->fdat[sst_id];

######### get adminpage name
$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
$parent_pagename = $adminpage_names['parent_pagename'];
$pagename = $adminpage_names['pagename'];


if ($site->fdat[id]>0) {
	$op = "edit";
} elseif ($site->fdat["lisa"]) {
	$op = "new";
} elseif ($site->fdat[lisa_keel]) {
	if ($site->fdat[keel_nimi]) {		
		# kas siukene keel juba olemas?
		$sql = $site->db->prepare("SELECT keel_id, nimi FROM keel where nimi like ?", $site->fdat[keel_nimi]); 
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$vana_keel = $sth->fetch();
	}
	$op = "lisa_keel";
}

#######################
# define sys_alias groups - all possible sys_aliases must be defined here

$sysalias_groups = array(
	"main_sections" => array(
		"home", 
		"system",
		"gallup_arhiiv",
		"trash"
	),
	"system_messages" => array(
		"404error",
		"gallup_ip_olemas",
		"your_IP_disabled",
		"kasutaja_locked",
		"kasutaja_registreeritud",
		"kasutaja_uuendatud",
		"tyhiotsing",
		"unustatud_parool_saadetud",
		"login_incorrect",
		"add_cart",
		"save_cart"
	),
	"form_messages" => array(
		"ok_page",
		"error_page",
	),
	"user_defined_messages" => array(),
);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title><?=$site->title?> <?= $site->cms_version ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=$page_encoding ?>">
	<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/default_admin_page.css"  media="screen">
	<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/scms_dropdown.css" media="screen">
	<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/admin_menu.js"></script>
	<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></script>
	<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/ie_position_fix.js"></script>
	<script type="text/javascript">
		window.onload = function()
		{
			make_breadcrumb('<?=$adminpage_names['parent_pagename'];?>','<?=$adminpage_names['pagename'];?>');
			new ContentBox('content_box', '40px', '0px', '15px', '0px');
		}
		
		var ContentBox = function(id, top, right, bottom, left)
		{
			this.box = document.getElementById(id);
			this.box.style.top = top;
			this.box.style.right = right;
			this.box.style.bottom = bottom;
			this.box.style.left = left;
		}
	</script>
<?php print_context_button_init(); ?>
</head>

<body>

<div id="mainContainer">

	<div class="toolbarArea">
		<form name="toolbar_form" id="toolbar_form" method="POST" action="<?=$_SERVER['PHP_SELF'];?>">
				<table cellpadding="0" cellspacing="0" width="100%" align="right">
				<tr>
					<td>
	            		<ul class="s_Buttons_container" style="float: right;">
	            			<li><span><?=$site->sys_sona(array('sona' => 'Language', 'tyyp' => 'Admin'));?>: <select name="flt_keel" onchange="submit()" class="drop"><?php	
							######################
							# language dropdown
							$sql = "SELECT nimi,keel_id FROM keel WHERE on_kasutusel = '1' ORDER BY nimi";
							$sth = new SQL($sql);
							$site->debug->msg($sth->debug->get_msgs());
							
							while ($keel = $sth->fetch()) {
								print "	<option value=\"$keel[keel_id]\" ".($keel[keel_id] == $keel_id ? "selected":"").">$keel[nimi]</option>";
							}
						?></select></span></li></ul>
					</td>
				</tr>
			</table>
    	</form><!-- /form filters -->
	</div><!-- / toolbarArea -->
	
	<div class="contentArea" id="content_box">
		<div class="contentAreaTitle">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td class="icon" width="16" style="padding-right: 3px;"><img src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/mime/languages.gif" width="16" height="16"></td>
					<td class="title"><?php echo $pagename ?></td>
				</tr>
			</table>
		</div><!-- / contentAreaTitle -->
		<div class="contentAreaContent withTitleBar">
					<? 
					#########################
					# FORM
					?>
					<FORM action="<?=$site->self ?>" method="get" name="vorm">

					<table cellpadding="0" cellspacing="0" class="data_table">
		  
			<?
			################################		
			# süsaliasega objektid
			################################		

					####################
					# get existing sys_alias objects

					$sql = $site->db->prepare(
						"SELECT * FROM objekt WHERE sys_alias>'' and keel=?",
						$keel_id
					);
					$sth = new SQL($sql);
#					print $sql;
					$site->debug->msg($sth->debug->get_msgs());
					$leitud_aliased = array();
					while ($obj = $sth->fetch()) {
						$leitud_aliased[trim($obj[sys_alias])] = new Objekt($obj);
#						echo trim($obj[sys_alias])."<br>";
					}

					if ($leitud_aliased["system"]) {
						$sys_rub = new Alamlist(array(
							parent => $leitud_aliased["system"]
						));
					} else {
						$sys_rub=0;
					}

					######################
					# loop over groups
					foreach ($sysalias_groups as $group_name=>$values) {

						##################
						# tyyp_id for NEW-buttons

						if($group_name == 'main_sections') {
							$tyyp_id = 1;
						}
						else {
							$tyyp_id = 2;
						}
						##################
						# print group name
						?>
					<thead>
						<tr>
						  <td nowrap colspan=2><?=$site->sys_sona(array(sona => $group_name, tyyp=>"admin"))?></td>
						</tr>
					</thead>

				<?
						######################
						# 1. loop over sys_aliases
						foreach($values as $alias) {
				?>
							<tr> 
							  <td nowrap>
								
							<?  #####################
								# button
							# if objekt.sys_alias <> ''print EDIT-button
							if (is_object($leitud_aliased[$alias])) {
								$leitud_aliased[$alias]->edit_buttons(array(
									nupud => array("edit","hide"),
									keel => $keel_id,
									sys_alias => $alias,
								));
							} 
							# kui sys_alias="home", siis on teine link
							elseif (in_array($alias,array("home","system"))) {
								// this if doesnt seem to show up
								$result = '<a class="scms_new_object" href="javascript:void(0);" onclick="javascript:avaaken(\''.(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/admin/edit.php?op=new&keel='.$keel_id.'&parent_id=0&ttyyp_id=&tyyp_idlist=1&profile_id=&sys_alias='.$alias.'\','.$new_object_popupsize.')">'.$site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])).'</a>';
								echo $result;

							} 
							# print NEW-button
							else{

if(!$sys_rub->objekt_id){
	# erand: nuppu ei saa kutsuda objekti meetodina, sest pole objekti. 
	# system-aliase parent hakkab olema 0.
				//$result = '<editor:buttons baseurl="'.(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/" url="" object_id="" parent_id="'.($tyyp_id=='1'?'0':$leitud_aliased["system"]->objekt_id).'" previous_id="" position="" ttyyp_id="1" tyyp_idlist="'.$tyyp_id.'" lang="'.$keel_id.'" sys_alias="'.$alias.'" visible="0" buttons="N" permissions="N" class="scms_arrow_visible"><IMG SRC="'.(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/styles/default/gfx/px.gif" WIDTH="13" HEIGHT="13" BORDER=0 ALT=""></editor:buttons>';
				$result = '<a class="scms_new_object" href="javascript:void(0);" onclick="javascript:avaaken(\''.(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/admin/edit.php?op=new&keel='.$keel_id.'&parent_id='.($tyyp_id=='1'?'0':$leitud_aliased["system"]->objekt_id).'&ttyyp_id=1&tyyp_idlist='.$tyyp_id.'&profile_id=&sys_alias='.$alias.'\',\'450,430\')">'.$site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])).'</a>';
				echo $result;
}
else {
								
								$sys_rub->edit_buttons(array(
									ainult_kui_tyhi => 0,
									keel => $keel_id,
									tyyp_idlist => $tyyp_id,
									sys_alias => $alias,
								));
}


							} 
							?>
							<? ##################### name ?>
							<?=$site->sys_sona(array(sona => $alias, tyyp=>"system"))?>								
							</td>
							<? ##################### headline ?>
							  <td colspan="2" nowrap><?=$leitud_aliased[$alias] ? $leitud_aliased[$alias]->pealkiri : "-" ?></td>
							</tr>
				<?
							$i++;
						} 
						# / 1. loop over sys_aliases
						######################

						######################
						# 2. loop over user_defined system articles

						if($group_name == 'user_defined_messages') {
							$alamlistSQL = new AlamlistSQL(array(
								parent => $leitud_aliased["system"]->objekt_id,
								klass	=> "artikkel",
								asukoht	=> 0,
								order => "aeg",
							));
							# User defined articles: show also articles having non-empty "sys_alias" field value:
							$alamlistSQL->add_where("objekt.sys_alias NOT IN ('".join("','",array_merge($sysalias_groups["system_messages"],$sysalias_groups["form_messages"]))."')");

							$alamlistSQL->add_where($site->db->prepare("objekt.keel=?",$keel_id));

							$alamlist = new Alamlist(array(
								alamlistSQL => $alamlistSQL,
							));

							$alamlist->debug->print_msg();

							while ($obj = $alamlist->next()) {	

						?>
							<tr> 
							  <td nowrap>
							<?
							$obj->edit_buttons(array(
								nupud => array("new","edit","hide","delete"),
								tyyp_idlist => "2",
								keel => $keel_id
							));
							?>							
							<?=$obj->objekt_id ?>
							</td>
							  <td nowrap><?=$obj->pealkiri() ?></td>
							</tr>


						<?
							$i++;
							}

							# kui pole artikleid, näidata new-nuppu
							if(!$alamlist->rows) { 
					?>
								<tr> 
								  <td nowrap colspan=2><?
								$alamlist->edit_buttons(array(
									tyyp_idlist => "2",
									keel => $keel_id,
								));?></td>
								</tr>
							<? 
						
							echo '<tr><td>
							<IMG SRC="'.(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/px/px.gif" WIDTH="13" HEIGHT="13" BORDER=0 ALT="">	
							</td></tr>';
						
							} 
							# if found custom system articles, then add extra emprty space for button-menu
							else { 
						
							echo '<tr><td>
							<IMG SRC="'.(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/px/px.gif" WIDTH="13" HEIGHT="90" BORDER=0 ALT="">	
							</td></tr>';
						}
						}
						# 2. loop over user_defined system articles
						######################
					

					}
					# / loop over groups
					######################

				?>

					</table>					
					</form><!-- / actionForm -->


					<? $site->debug->print_msg(); ?>
		</div><!-- / contentAreaContent -->
	</div><!-- / contentArea -->
	
</div><!-- / mainContainer -->


</body>
</html>