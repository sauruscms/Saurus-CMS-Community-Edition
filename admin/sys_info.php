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
 * Saurus CMS admin page "System > System Info"
 * 
 */

global $site;
$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");

#Get debug cookie muutuja
$debug = $_COOKIE["debug"] ? 1:0;

$site = new Site(array(
	on_debug=> $debug,
	on_admin_keel => 1
));

$site_url = $site->CONF['protocol'].$site->CONF['hostname'].$site->CONF['wwwroot'];

$failid_url = $site->CONF['protocol'].$site->CONF['hostname'].$site->CONF['wwwroot'].$site->CONF['file_path'];


if (!$site->fdat['search']){$site->fdat['only_broken']=1;}

######### get adminpage name
$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
$parent_pagename = $adminpage_names['parent_pagename'];
$pagename = $adminpage_names['pagename'];

######################
# leida valitud keele põhjal õige lehe encoding,
# admin-osa keel jääb samaks

$keel_id = isset($site->fdat['flt_keel']) ? $site->fdat['flt_keel'] : $site->fdat['keel_id'];
if (!strlen($keel_id)) { $keel_id = $site->keel; }

$sql = "SELECT encoding FROM keel where keel_id = ?";
$sql = $site->db->prepare($sql,$keel_id);
$sth = new SQL($sql);
$site->debug->msg($sth->debug->get_msgs());	
$page_encoding = $sth->fetchsingle();


$op = $site->fdat[op];
$site->debug->msg("OP = $op");

if (!$site->user->allowed_adminpage()) {
	exit;
}

###############################
# Calculate DataBase size:

$need_optimize = 0; // if there are free fragments in DB, show link "Optimize DB"
$db_size = 0;		// Var to calculate total weight of DB
$sql = "SHOW TABLE STATUS";
$sql = $site->db->prepare($sql);
$sth = new SQL($sql);
$site->debug->msg($sth->debug->get_msgs());	

	while ($data = $sth->fetch()) {
		$db_size += $data['Data_length']; #bugfix #1703

		if ($data['Data_free']){
		
			# here is DB optimisation:
			if ($site->fdat['optimize']){
				$sql2 = $site->db->prepare("OPTIMIZE TABLE ".$data["Name"]);
				$sth2 = new SQL($sql2);
				$site->debug->msg($sth2->debug->get_msgs());
			}
			$need_optimize += $data['Data_free'];
		}
		$tables++;
	}
	# write log
	if ($site->fdat['optimize']){
		new Log(array(
			'action' => 'optimize',
			'component' => 'Config',
			'message' => "Database optimized in page '$parent_pagename>$pagename'",
		));
	}
# / Calculate DataBase size:
###############################


?>
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$page_encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/admin_menu.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>');
//-->
</SCRIPT>
</head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
<?php 
################################
# FUNCTION BAR
?>
<!-- Toolbar -->
<TR>
<TD class="scms_toolbar">

	<?php ######### FUNCTION BAR ############?>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr> 
		  <?php ############ optimize database  ###########?>
				<TD nowrap><?php if($need_optimize && !$site->fdat['optimize']){?><a href="?optimize=1"><?php }?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/exec.png" WIDTH="16" HEIGHT="16" BORDER="0" align=absmiddle><?php if($need_optimize && !$site->fdat['optimize']){ echo  '&nbsp;'.$site->sys_sona(array(sona => 'optimize database' , tyyp=>'powertools'))?></a><?php } else { echo '&nbsp;'.$site->sys_sona(array(sona => 'Database optimized' , tyyp=>'powertools')); }?></TD>

		
		<?php ###### wide middle cell ######?>
		<td width="100%"></td>

		</tr>

      </table>
</TD>
</TR>

<?php 
# / FUNCTION BAR
################################
?>
  <!-- //Toolbar -->
  <!-- Content area -->

  <tr valign="top"> 


<?php 
############################
# CONTENT TABLE
?>
		<!-- Middle column -->
		<TD class="scms_middle_dialogwindow">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr class="scms_pane_header"> 
					<?php ###### icon + headline ######?>
					<td nowrap>
					<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/mime/metainfo.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>
					&nbsp;
					   <?=$site->sys_sona(array(sona => "system info", tyyp=>"admin"))?>
					 </td>	
	
                    </tr>
                 </table>


			<table width="100%" height="95%" border="0" cellspacing="0" cellpadding="0">
		   <!-- Table header -->	
			<tr>
				<td valign=top>
					<!-- Scrollable area -->
					<div id=listing class="scms_middle_div">


<?php 


###########################
#  print REQUIREMENTS TABLE
?>

<?php 
$called_from_another_script = 1;
$path = "../";
include_once("check_requirements.php");
print_requirements_table();
unset($called_from_another_script);
unset($path);

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

<?php 
if($site->user) { $site->user->debug->print_msg(); }
$site->debug->print_msg();
?>
	
	</td>
  </tr>
</table>

</body>
</html>
<?php 
################################
# function Calculate Files size
function calc_size($adr,&$total,&$dir,&$size){            
	$adr=realpath($adr);
  $dp=OpenDir($adr);

  do{
    $itm=ReadDir($dp);
    if (($itm!=".")&&($itm!="..")&&($itm!="")&&Is_Dir("$adr/$itm")){
      calc_size("$adr/$itm",$total,$dir,$size);
      $dir++;
    }
    elseif (($itm!=".")&&($itm!="..")&&($itm!="")){
      $size = $size+FileSize("$adr/$itm");
      $total++;
    }
  } while ($itm!=false);

  CloseDir($dp);
}
# / function Calculate Files size
################################	