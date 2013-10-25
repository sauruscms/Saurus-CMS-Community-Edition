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
 * Saurus CMS adminpage "Tools > Error Logs"
 * Shows PHP and SQL errors from the table "error-log".
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

######### get adminpage name
$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
$parent_pagename = $adminpage_names['parent_pagename'];
$pagename = $adminpage_names['pagename'];

# default start = week ago
$start_d = mktime(0, 0, 0, date("m"),date("d")-7,date("Y"));

# default values: !!!!!! vale fomraat:
$algus_aeg = $site->fdat['algus']? $site->fdat['algus'] : date("d.m.Y",$start_d);
$lopp_aeg = $site->fdat['lopp']? $site->fdat['lopp'] : date("d.m.Y");


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/datepicker.css">
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/jquery.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/datepicker.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'];?>/common.js.php"></script>
		<script type="text/javascript">

		window.onload = function() { 
			make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>' <?=$breadcrumb_focus_str?>);
		}

		</script>
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
		  <?php ############ delete  ###########?>
				<TD nowrap><a href="javascript:void(avaaken('delete_log.php?tbl=error_log','366','450','log'))"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle> <?=$site->sys_sona(array(sona => 'kustuta' , tyyp=>"editor"))?></a></TD>
		<?php ###### refresh button ######?>
				<TD nowrap><a href="javascript:document.forms['searchform'].submit();" class="scms_button_img"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/refresh.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle> <?=$site->sys_sona(array(sona => 'refresh' , tyyp=>"admin"))?></a></TD>

		
		<?php ###### wide middle cell ######?>
        <td width="100%"></td>


		<?php ###### search box ######?>
		<form id="searchform" name="searchform" action="<?=$site->self?>" method="GET">
	<?php foreach($site->fdat as $fdat_field=>$fdat_value) { ?>
		<input type=hidden name="<?=$fdat_field?>" value="<?=$fdat_value?>">
	<?php } ?>
		<input type="hidden" name="otsi" value=1>
		<input type="hidden" name="page" value=""><?php # if search smth => reset page number to 1 (Bug #1697)?>

		
		<td style="padding-right: 10px">
			<?php $search_str = $site->sys_sona(array(sona => "otsi", tyyp=>"editor")); ?>
	          <input name="filter" type="text" class="scms_flex_input" style="width:150px" value="<?=$site->fdat['filter']? $site->fdat['filter'] : $search_str.':'?>" onFocus="if(this.value=='<?=$search_str?>:') this.value='';" onBlur="if(this.value=='')this.value='<?=$search_str?>:';" onkeyup="javascript: if(event.keyCode==13){this.form.submit();}">

		</td>

		<?php ########## starting + cal ?>

        <td><?=$site->sys_sona(array(sona => "Alates", tyyp=>"editor"))?>:</td>
        <td> 
          <input id="algus"  name="algus" size=10 value="<?=$algus_aeg?>" class="scms_flex_input" maxlength="10" style="width:64px" onkeyup="javascript: if(event.keyCode==13){this.form.submit();}">
        </td>
        <td><a href="#" onclick="init_datepicker('algus');"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" title="Choose from calendar" alt="Choose from calendar" border="0"></a>
        </td>

		<?php ########## until + cal ?>

		<td>&nbsp;<?=$site->sys_sona(array(sona => "Kuni", tyyp=>"editor"))?>:&nbsp; </td>
        <td>
          <input id="lopp" name="lopp" size=10 value="<?=$lopp_aeg?>" class="scms_flex_input" maxlength="10" style="width:64px"  onkeyup="javascript: if(event.keyCode==13){this.form.submit();}">
        </td>
        <td><a href="#" onclick="init_datepicker('lopp');"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" title="Choose from calendar" alt="Choose from calendar" border="0">
        </td>


		<td style="padding-right: 10px">
			<?php ########## user dropdown ?>
					<table border="0" cellspacing="0" cellpadding="1">
					  <tr> 
						<td width="15"> 
       <select name="err_type" onchange="javascript:<?=$set_page?>this.form.submit();">
         <option value="" <?=($site->fdat['err_type']?'':'selected')?>>- <?=$site->sys_sona(array(sona => "koik", tyyp=>"editor"))?> -</option>
<?php 
		foreach(array('PHP','SQL') as $type) { ?>
			<option value="<?=$type?>" <?=($site->fdat['err_type']==$type?'selected':'')?>><?=$type?></option>
		<?php  }	?>
       </select>
						</td>
					  </tr>
					</table>
			<?php ########## / type dropdown ?>
		</td>
		</form>
	  
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

	$from_sql = " FROM error_log";

?>
		<!-- Middle column -->
		<TD class="scms_middle_dialogwindow">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr class="scms_pane_header"> 
					<?php ###### icon + headline ######?>
					<td nowrap>
					<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/history.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>
					&nbsp;
					<?=$site->sys_sona(array(sona => "Error log", tyyp=>"admin"))?>
					 </td>
					<?php ###### wide middle cell ######?>
					<td width="100%"></td>

<?php ########### WHERE SQL (NB! must be done before pagenumbers AND after type dropdown)

	$where_sql = array();
	
	###### search string
	if ($site->fdat['filter'] && $site->fdat['filter']!=$site->sys_sona(array(sona => "otsi", tyyp=>"editor")).':' ) {
		$otsi = mysql_real_escape_string($site->fdat['filter']);
		$otsi = preg_replace("/%/", "\\%", $otsi);
		$where_sql[] = " (error_log.err_text LIKE '%".$otsi."%' OR error_log.referrer LIKE '%".$otsi."%') ";
	}
	if ($algus_aeg) {
		$where_sql[] = " error_log.time_of_error>='".mysql_real_escape_string($site->db->ee_MySQL($algus_aeg))." 00:00' "; 
	}
	if ($lopp_aeg) {
		$where_sql[] = " error_log.time_of_error<='".mysql_real_escape_string($site->db->ee_MySQL($lopp_aeg))." 23:59' "; 
	}
	if ($site->fdat['err_type']) {
		$where_sql[] = " error_log.err_type = '".mysql_real_escape_string($site->fdat['err_type'])."' ";
	}

	$where_str = sizeof($where_sql)>0 ? " WHERE ".join(" AND ",$where_sql) : '';
#	print $where_str;
	########### / WHERE SQL
?>
		<?php ######  pagenumbers ######?>
	   <td>
		<?php 
		# get records total count
		$sql = "SELECT COUNT(*) ".$from_sql.$where_str;
		$sth = new SQL($sql);
		$total_count = $sth->fetchsingle();

		######### print pagenumbers table
		$pagenumbers = print_pagenumbers(array(
			"total_count" => $total_count,
			"rows_count" => 20,
		));
		?>
		</td>
		<?php ######  / pagenumbers ######?>	

                    </tr>
                 </table>
				
			<table width="100%" height="95%" border="0" cellspacing="0" cellpadding="0">
		   <!-- Table header -->	
	<?php 
	#################
	# COLUMN NAMES
	?>

			  <tr height=10> 
                <td valign="top" class="scms_tableheader">

					<table width="100%"  border="0" cellspacing="0" cellpadding="0">
						<tr> 

						  <td width="10%" nowrap class="scms_tableheader_active"><?=$site->sys_sona(array(sona => "Aeg", tyyp=>"editor"))?></td>
						  <td width="5%"><?=$site->sys_sona(array(sona => "type", tyyp=>"admin"))?></td>
						  <td width="50%"><?= $site->sys_sona(array(sona => "actions", tyyp=>"admin"))?></td>
						  <td width="30%"><?= $site->sys_sona(array(sona => "referrer", tyyp=>"statistics"))?></td>
						  <td width="16" align="right"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/px.gif" WIDTH="16" HEIGHT="1" BORDER="0" ALT=""></td>
						</tr>
					</table>


				</td>
			</tr>
			<!-- // Table header -->
	<?php 
	# / COLUMN NAMES
	#################

	#################
	# DATA ROWS
	?>	  

			<tr>
				<td valign=top>
					<!-- Scrollable area -->
					<div id=listing class="scms_middle_div">


				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="scms_table">
<?php 
	# default values:
	$site->fdat['sort'] = $site->fdat['sort'] == 'ASC' ? 'ASC' : 'DESC';

	########### ORDER
	$order = " ORDER BY 'time_of_error' ".$site->fdat['sort'];

	########### SQL

	$sql = $site->db->prepare("SELECT DATE_FORMAT(time_of_error,'%d.%m.%y %T') AS time_of_errorf, error_log.*");
	$sql .= $from_sql;
	$sql .= $where_str;
	$sql .= $order;
	$sql .= $pagenumbers['limit_sql'];
	
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

		###########################
		# loop over rows
		while ( $log = $sth->fetch() ) {
?>
				<tr> 

				<?php ############# time_of_error ?>
                  <td width="15%" nowrap ><?= $log['time_of_errorf'] ?></td>
				<?php ############# err_type ?>
				  <td width="5%" nowrap><?= $log['err_type'] ?></td>
				<?php ############# err_text ?>
				  <td width="60%"><?php echo htmlspecialchars(xss_clean($log['err_text'])); ?></td>
				<?php ############# referrer ?>
				  <td width="20%"><?= $log['source'] ?></td>

			<td width="16"><img src="<?=$site->CONF['wwwroot'].$site->CONF['img_path']?>/px.gif" width="11" height="18" border="0"  hspace="3"></td>

                </tr>
<?php 
		}
		# / loop over rows
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
<?php 
$site->debug->print_msg();