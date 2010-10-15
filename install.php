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
 * Saurus CMS installation and version upgrade script.
 * Independent script, not for including, new Site is generated.
 *
 * Process includes 6 steps:
 * 1. display intro + 
 *    + [INSTALL: check config file chmod + EULA agreement]
 *    + system requirements
 *    + backwards compability check, warnings
 *
 * 2. database settings input form
 *
 * 3. display database settings
 *    + [INSTALL: write config.php + create Database and user]
 *    + [UPGRADE: check db user permissions]
 *    + ask for db dump file
 * 
 * 4. run SQL file(s)
 *	  + [UPGRADE: import language files]
 *
 * 5. [INSTALL: CMS admin account form]
 *    + [UPGRADE: run php update-scripts]
 *    + show configuration table
 *
 * 6. [INSTALL: save cms admin account]
 *    + save configuration table data
 *    + display site links
 * 
 */

############# GLOBAL
$FDAT = (sizeof($_POST) > 0 ? $_POST : $_GET);

$op = $FDAT["op"];
$install = $FDAT["install"];

# if install.php should show any HTML output or not
$skip_html = false; # default value: false

############# ERRORS - display all errors during installation (none during version upgrade)
if($install || isset($_GET["error_reporting"])) { $display_errors = 0; } ## turn off until Step3 causes db errors
else { $display_errors = 0;}
ini_set('display_errors', $display_errors); // hide or display all errors from screen

# set script execution time to 10 min only  if general value is smaller
if ( intval(ini_get('max_execution_time')) < 600 ) {
	set_time_limit ( 600 ) ;
}
# memory limit = 24,
if ( intval(ini_get('memory_limit')) < 24 ) {
	ini_set ( "memory_limit", "24M" );
}

/***********************************/
/* INCLUDED FILES                  */
/***********************************/
$class_path = "./classes/";

$is_installation_script = true; # needed for error display handling in core
include_once($class_path."port.inc.php");
include_once($class_path."nodebug.inc.php");
include_once($class_path."install.inc.php"); # all installation related functions

# set error display second time - to override settings in port.inc.php
ini_set('display_errors', $display_errors); // hide or display all errors from screen

############# CONF
$CONF = ReadConf(); # db connect data from config.php

############# VERSION CHECK
$current_ver = current_version(); # try to connect database and find which version is installed returns 0, if no database found

############# VERSION NUMBERS

# version numbers // the CE version can only be update'd from a 4.6.6 version
$versions = array(
	'4.6.6',
	'4.7.0',
	'4.7.1',
);
##############################

# get the new version number
$new_ver = end($versions);

# kui esileht ja current versiooni ei leitud, siis järelikult install
if (!$current_ver && !$install) {
	$install = 1;
}
$step_count = 6;
$error_file = "install_errors.txt";
$url = site_url();

##########################
# default_data_files 

$default_data_files = array();

if ($install) { # install
	array_push($default_data_files, "install/default_db.sql");
} 
else { # update
	
	// scrub EE licensing and commercial modules (not used on CE)
	if($current_ver == '4.6.6') $default_data_files[] = 'admin/updates/updateEEtoCE.sql';
	
	$i = 1;
	foreach ($versions as $version_array_index => $tmpver)
	{
		$next = $versions[$version_array_index + 1];
		# if not current version yet, go to next ver
		# jooksev ver <= installitav ver
		
		if (strnatcmp($current_ver, $tmpver) <= 0) {

			# if overinstalling same ver, then go back in versions
			# jooksev ver = installitav ver
			if (strnatcmp($current_ver,$new_ver)==0) {
				array_push($default_data_files, "admin/updates/update".$versions[$i-2]."to".$tmpver.".sql");
			}
			# usual case
			else {
				array_push($default_data_files, "admin/updates/update".$tmpver."to".$next.".sql");
			}
		}
		$i++;
	} # foreach
	# remove last element if not repairing/overinstalling same version
	if (strnatcmp($current_ver,$new_ver) != 0) {
		array_pop($default_data_files);
	}

}

############# leia saidi default keele encoding ja kasuta seda siin lehel (Bug #1854)

if(!$install){
	# otsida välja default keel:
	$sqlK = "SELECT encoding FROM keel WHERE on_default = '1'"; 
	$sthK = new SQL($sqlK);
	$encoding = $sthK->fetchsingle();
}
## new install: default encoding is UTF-8 (Bug #2225)
$encoding = $encoding ? $encoding : 'UTF-8';

/***********************************/
/* HTML START                      */
/***********************************/

if( ! $skip_html) { # display HTML output
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Saurus CMS CE <?=($install?"Installation":"Update")." ".$new_ver?></title>
		<meta name="author" content="Saurus - www.saurus.info">
		<meta http-equiv="Cache-Control" content="no-cache">
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding?>">
		<link rel="stylesheet" href="styles/default/scms_general.css">
		<link rel="stylesheet" href="styles/default/scms_install.css">
	</head>

<body>
<center>
<?
######################
# header tabel, logo

$step_nr = substr(strtolower($op?$op:"step1"),-1);
?>  

<div id="installheader">
	<table width="750">
	<tr>
		<td valign="bottom"><h1>Saurus CMS CE <?=$new_ver." ".($install?"Installation":"Update")?>: Step <?=$step_nr?> of 6</h1></td>
		<td align="right"><img class="logo" src="styles/default/gfx/install/logo.gif" height="20" width="101" alt="Saurus" /></td>
	</tr>
	</table>
</div>

<?
######################
# wizard
?>  

<!-- Scrollable area -->
<div id="listing" class="scms_scroll_div">

<?
} # if display HTML output 

/***********************************/
/* STEPS START                     */
/***********************************/
//$op = 'Step5';
switch(@$op) {

     case "Step2":
/**************************
STEP 2
INSTALL:
- ask db connect info
UPDATE:
- ask db user info with all privileges
***************************/
?>


<table width="700" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><font class="txt">

<?
#########################
# if INSTALL  step2

if ($install) {
?>
<?
######################
# sisutabel
?>  

	<h2>Database</h2>
	<form action="install.php" method="POST" name="form" style="margin-top: 25px;">

	<p>
		<input type="radio" name="mysql_root" id="mysql_root0" value="0" checked onclick="document.getElementById('dbRootAccess').style.display='none';">
		<label for="mysql_root0"><strong>Use existing database</strong>. I have user account with nescessary permissions to connect and create tables.</label>
	</p>
	<p>
		<input type="radio" name="mysql_root" id="mysql_root1" value="1" onclick="form.create_user.checked=true; document.getElementById('dbRootAccess').style.display='block';">
		<label for="mysql_root1"><strong>Create new database</strong>. I have root access to the database server.</label>

		<table id="dbRootAccess" style="display: none; margin: 5px 0 5px 30px;" border="0" width="300">
			<tr>
			<td></td>
			<td align="left">Database root username</td>
			<td><input type="text" NAME="dbrootname"  maxlength=80 value="root"></td>
			</tr>
			<tr>
			<td></td>
			<td align="left">Database root password</td>
			<td><input type="password" NAME="dbrootpass" maxlength=80 value=""></td>
			</tr>
			<tr>
			<td></td>
			<td colspan=2 align="left"><input type="checkbox" name="create_user" id="create_user" value="1" checked><label for="create_user">Create a new database user for database connection</label>
			</table>
	</p>

	<?php print_dbdata_editabletext() ?>

	<p>Next step will write database connection parameters into the file "config.php". Please check that you have entered valid information before continuing.</p>

	<center>
	<INPUT type="button" value="Previous" onclick="javascript:document.getElementById('op').value='';document.form.submit();" class="redbutton">
	<INPUT type="submit" value="Next" class="redbutton">

	<INPUT type="hidden" name="op" id="op" value="Step3">
	<INPUT type="hidden" name="install" value="1">
	</center>
	</form>
<?
}
# / if INSTALL step2
#########################

#########################
# if UPDATE step2

else {
?>
	<h2>Database</h2>
	<form action="install.php" method="post" name="form">
	
	Full access to database "<?=$CONF["db"]?>" is needed to update table structures. Click Next if user "<?=$CONF["db"]?>" has appropriate privileges or specify another account.
	
	<br /><br />
	
    <table border="0" width="750">
            <tr>
			<td align="left">Database host</td>
            <td align="left"><?=$CONF[dbhost];?></td>
			</tr>
            <tr>
			<td align="left">Database port</td>
            <td align="left"><?=$CONF[dbport];?></td>
			</tr>
            <tr>
			<td align="left">Database name</td>
            <td align="left"><?=$CONF[db];?></td>
			</tr>
			<tr>
			<td align="left">Database user name</td>
            <td><input type="text" NAME="update_user" SIZE=30 maxlength=80 value="<?=$CONF[user];?>"></td>
			</tr>
            <tr>
			<td align="left">Database user password</td>
            <td><input type="password" NAME="update_passwd" SIZE=30 maxlength=80 value="<?=$CONF[passwd];?>"></td>
			</tr>
            <tr>
			<td align="left">Database type</td>
            <td align="left"><?=$CONF[dbtype];?></td>
		</tr>
   </table>
	<br />

	<center>
	<INPUT type="button" value="Previous" onclick="javascript:document.getElementById('op').value='';document.form.submit();" class="redbutton">
	<INPUT type="submit" value="Next" class="redbutton">

	<INPUT type="hidden" name="op" id="op" value="Step3">
	</center>
	</form>

	<br />

<?
}
# / if UPDATE step2
#########################
?>
	</font>
	</td>
  </tr>
  </table>

<?   break;

    case "Step3":
/**************************
STEP 3
INSTALL:
- write db connect data into config file
- show db connect data 
- if root: create db and user
- if no root: check if db exists
- ask db dump file
UPDATE:
- show db connect data 
- check user privileges CREATE, DROP, ALTER, INDEX
- if not OK, show error
- if OK, ask for db dump file

***************************/

?>
  <table width="750" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><font class="txt">
<?

#########################
# if INSTALL  step3

if ($install) {
?>
	<h2>Database info confirmation</h2>

	<form action="install.php" method="post" name="form" enctype="multipart/form-data">

	<?/*** write db connect data into config file ***/?>
	Database connection parameters were saved into file:
	<?    

	$conf_update_result = update_config_php($FDAT["dbhost"], $FDAT["dbport"], $FDAT["db"], $FDAT["dbtype"], $FDAT["user"], $FDAT["passwd"]); 


    if (preg_match("/Error/", $conf_update_result)) {
		echo '<font color="red">';
		echo($conf_update_result);
		echo '</font>';
		?>
		<br /><br />
		<INPUT type="button" value="Back" onclick="javascript:document.getElementById('op').value='Step2';document.form.submit();" class="redbutton">		
		<?
	}
	else {
		echo($conf_update_result);

	# read CONF again because file was modified
	$CONF = ReadConf();

	/*** end: write db connect data into config file ***/

	?>
	<br />
	<br />
	<?/*** show db connect data ***/?>
	<? print_dbdata_text() ?>
	<br />
<?
	/*** end: show db connect data ***/

	######################
	# connect to database 

	if ($FDAT["mysql_root"] == 1) { 
		// connect to database as root
		$conn = 0;
		dbconnect(1, $FDAT["dbrootname"], $FDAT["dbrootpass"], '');
	} 
	else {
		// search for database
		$db_found = check_db();
	}
	
	######################
	# if connect error

	if ($conn->error) {
		?>
		<font color=red>Error: <?=$conn->error?></font>
		<INPUT type="button" value="Back" onclick="javascript:document.getElementById('op').value='Step2';document.form.submit();" class="redbutton">
		<INPUT type="hidden" name="mysql_root" value="<?=$FDAT["mysql_root"] ?>">
		<br />		
		<?

	} 
	######################
	# go on
	else {

		######################
		# if root access to database 

		if ($FDAT["mysql_root"] ) { 
			##################
			# create database
			$db_created = make_db();

			if ($db_created) { ?>
				Database "<?=$CONF["db"] ?>" is created.
			<? } else { ?>
				Database "<?=$CONF["db"] ?>" already exists. Database has not been created.
			<? } 

			##################
			# if create user
			if ($FDAT["create_user"]) { 

				$user_created = make_user();

				if ($user_created) { ?>
					<br />
					User "<?=$CONF["user"] ?>" for database "<?=$CONF["db"] ?>" is created.
				<? } else { ?>
					<br />
					User "<?=$CONF["user"] ?>" already exists. User has not been created. Be sure that this user has access to database "<?=$CONF["db"] ?>"!
				<? } ?>
			<? } 
		######################
		# if no root access to database
		} else { 

			# 1. db found, but user access error
			if ($conn->error) { 
					?>
					<font color=red>Error: <?=$conn->error?></font>
					<INPUT type="button" value="Back" onclick="javascript:document.getElementById('op').value='Step2';document.form.submit();" class="redbutton">
					<INPUT type="hidden" name="mysql_root" value="<?=$FDAT["mysql_root"] ?>">
					<br />				
				<? 
			}
			# 2. db found, all OK
			else if ($db_found) { 
				?>				
				Database "<?=$CONF["db"] ?>" found.
				<?

			# 3. db not found, error, diplay back button
			} else { 
					?>
					Database "<?=$CONF["db"] ?>" not found. Please create a new database before continuing.
					<br />
					
					<INPUT type="button" value="Back" onclick="javascript:document.getElementById('op').value='Step2';document.form.submit();" class="redbutton">
					<INPUT type="hidden" name="mysql_root" value="<?=$FDAT["mysql_root"] ?>">
					<br />
				<? 
			} # conn 
		} 
		/*** end: if root access to database ***/

		## OK message
		if ($FDAT["mysql_root"] || $db_found) { 
		?>


		<p>In next step, the database will be updated using default SQL definitions at <br>
		<?=join(", ",$default_data_files)?></p>
		
		
		<p>Advanced users may select a custom SQL file:<br>
		<input type="file" NAME="data_file"></p>


			<center>
			<INPUT type="button" value="Previous" onclick="javascript:document.getElementById('op').value='Step2';document.form.submit();" class="redbutton">
			<INPUT type="submit" value="Next" class="redbutton">
			</center>


	
		<? 
	
		} # OK message 
	}
	# if not db connect error
	######################

	} # config.php udpate OK
	
	?>
	<INPUT type="hidden" name="update_user" value="<?=$CONF["user"] ?>">
	<INPUT type="hidden" name="update_passwd" value="<?=$CONF["passwd"] ?>">

	<INPUT type="hidden" name="op" id="op" value="Step4">
	<INPUT type="hidden" name="install" value="1">
	</form>

	<br />
<?
}
# / if INSTALL  step3
#########################
#########################
# if UPDATE  step3
/*
UPDATE:
- show db connect data 
- check user privileges CREATE, DROP, ALTER, INDEX
- if not OK, show error
- if OK, ask for db dump file
*/
else {
?>
	<h2>Database info confirmation</h2>
    <table border="0" width="400">
            <tr>
			<td align="left">Database host</td>
            <td align="left"><?=$CONF[dbhost];?></td>
			</tr>
            <tr>
			<td align="left">Database port</td>
            <td align="left"><?=$CONF[dbport];?></td>
			</tr>
            <tr>
			<td align="left">Database name</td>
            <td align="left"><?=$CONF[db];?></td>
			</tr>
            <tr>
			<td align="left">Database user name</td>
            <td><?=$FDAT["update_user"];?></td>
			</tr>
            <tr>
			<td align="left">Database user password</td>
            <td><?=$FDAT["update_passwd"];?></td>
			</tr>
            <tr>
			<td align="left">Database type</td>
            <td align="left"><?=$CONF[dbtype];?></td>
			</tr>
    </table>

	<?
	# check if user has all privileges 
	#	$error = check_allpriv($FDAT["update_user"],$FDAT["update_passwd"]);	
	$error = 0;


	if ($error) {
	?>
	<br /><br /><font color=red><?=$error?></font>
	<br />
	<form action="install.php" method="post" name="form">
		<center>
		<INPUT type="button" value="Previous" onclick="javascript:document.getElementById('op').value='Step2';document.form.submit();" class="redbutton">
		</center>
		<INPUT type="hidden" name="op" id="op" value="Step4">
	</form>

	<? } else { ?>
	<form action="install.php" method="post" name="form" enctype="multipart/form-data">
		<br />
		<br />
		The database will be updated using default SQL file (<?=join(", ",$default_data_files)?>).<br />If you have a custom SQL file, select it here:<br />
		<input type="file" NAME="data_file">
		
		<INPUT type="hidden" name="op" id="op" value="Step4">

		<INPUT type="hidden" name="update_user" value="<?=$FDAT["update_user"]?>">
		<INPUT type="hidden" name="update_passwd" value="<?=$FDAT["update_passwd"]?>">
		
		<br />
		<br />
		
		<center>
		<INPUT type="button" value="Previous" onclick="javascript:document.getElementById('op').value='Step2';document.form.submit();" class="redbutton">
		<INPUT type="submit" value="Next" class="redbutton">
		</center>

	</form>
<?
	}
}
# / if UPDATE  step3
#########################

?>	
	</font>
	</td>
  </tr>
  </table>
<?

	break;

    case "Step4":
/**************************
STEP 4
INSTALL:
- create tables
UPDATE:
- run sql files
- import language files
***************************/

#########################
# if INSTALL  step4
if ($install) {

?>

	<h2>Updating Database</h2>
	
	<form action="install.php" method="post" name="form">
	<?	

	if (!$FDAT["dont_make_db"]) {
		$tbl_error = run_dumpfile();
	}

	?>
	<? 
	
	if ($tbl_error != '') { 
	?>
		<font color="red">
		<?=$tbl_error?>
		<br />Error, tables have not been created.</font>
		<INPUT type="button" value="Previous" onclick="javascript:document.getElementById('op').value='Step3';document.form.submit();" class="redbutton">
		<?	
		print_dbdata_hidden(); 
	
	} else if ($FDAT["dont_make_db"]) { 
		?>
		<font color="red">
		<br />Tables have not been created.</font>
	<? 
	} else { 

	?>
			<p>Tables for database "<?=$CONF["db"] ?>" have been created.</p>
	<?
	} # tbl error 

	?>

	<center>
		  <p><input type="submit" value="Next" class="redbutton"></p>
	</center>

		<input type="hidden" name="op" id="op" value="Step5">
		<input type="hidden" name="install" value="1">
	      
	</form>

	<?
}
# / if INSTALL  step4
#########################
#########################
# if UPDATE  step4
else {
?>

	<h2>Updating Database</h2>
	<font class="txt">
	<form action="install.php" method="post" name="form">
	<?
	###############
	# run sql
	$upd_error = run_dumpfile();
?>
  <table width="580" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><font class="txt">
<?
	if ($upd_error != '') { ?>
		<font color="red">
		<br />Fatal error occured during updating database "<?=$CONF["db"] ?>"!</font>
		<INPUT type="button" value="Previous" onclick="javascript:document.getElementById('op').value='Step3';document.form.submit();" class="redbutton">
	<? } else { 

	?>
		Database "<?=$CONF["db"] ?>" update finished.<br>

<?
	flush();usleep(500000);

?>	
	</font>
	</td>
  </tr>
  </table>
  <br />
	
	<font class="txt">
<?	

	###############
	# lang file import: importida ainult need keeled, mis saidis aktiivsed

	# 0 - Estonian, 1 - English
	$default_languages = array('0','1');

	# get languages in use
	$sqlK = "select distinct b.glossary_id as keel_id, b.encoding as encoding from keel as a left join keel as b on a.keel_id = b.glossary_id where b.on_kasutusel = '1'";
	$sthK = new SQL($sqlK);

	###############
	# loop over active languages
	while ($keel = $sthK->fetch()) {

		# check if it is default language?
		if(!in_array($keel['keel_id'], $default_languages)) {
			continue;
		}

		# get site encoding, default is UTF-8 if not set
		$lang_encoding = $keel['encoding'] ? strtoupper($keel['encoding']) : "UTF-8";

		# file = admin/updates/language0.csv	
		$file = "admin/updates/".$lang_encoding."/language".$keel['keel_id'].".csv";

		# kui leidub selle keele keelefail
		if(file_exists($file)) {
			echo "Importing file '".$file."'...";
			echo "<script>document.getElementById('listing').scrollTop = document.getElementById('listing').scrollHeight - 500;</script>";
			flush();usleep(500000);

			$one_lang_error = import_langfile($file,$keel);
			if(!$one_lang_error) { # import OK
				$lang_error .= $one_lang_error;
				echo " Done.<br>";
			}
			echo "<script>document.getElementById('listing').scrollTop = document.getElementById('listing').scrollHeight - 500;</script>";
			flush();usleep(500000);
		} 	
		
	}
	# /  loop over active languages
	###############

	
	?>
	<br />
	<table width=600 border=0 cellspacing=0 cellpadding=0><tr><td><font class=txt>

	<?###### language result #######?>
	<? if ($lang_error != '') { ?>
		<font color="red">
		<?=$lang_error?>
		<br />Fatal error occured during importing language files into database "<?=$CONF["db"] ?>"!</font>
		<INPUT type="button" value="Previous" onclick="javascript:document.getElementById('op').value='Step3';document.form.submit();" class="redbutton">
	<? } else { 
		echo "Glossary import finished.";
	} # if import language files ok

	?>
	</font></td></tr></table>
	<?
	##################
	# next nupp
	?>
	<br /><br />
	<center>
	<INPUT type="submit" value="Next" class="redbutton">
	</center>

	<INPUT type="hidden" name="op" id="op" value="Step5">
	</form>
	<script>document.getElementById('listing').scrollTop = document.getElementById('listing').scrollHeight - 500;</script>

	<? 
	} # if run sql ok
	
}
# / if UPDATE  step4
#########################

    break;

    case "Step5":
/**************************
STEP 5
INSTALL
- CMS admin account form
- show configuration table
- poll free
UPDATE
- run php update-scripts
- save new templates info into db
- show configuration table
***************************/
?>
  <table width="580" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><font class="txt">
<?
#########################
# if INSTALL step5

if ($install) {
?>

	<h2>Site Settings</h2>
	<form action="install.php" method="post" name="form">
	<? 
	// connect to database 
	if (!$conn) { dbconnect(0, '', '', ''); }
	?>
	
	<p>Please create user account for logging in to Saurus CMS. You can not use "saurus" for password.</p>

	<?
		// read the other config data from config table
		$CONFDB = ReadConfDB();

		###################
		# if table config doesn't exist at all
		if (!$CONFDB) {
?>
			Table "config" not found!
			<INPUT type="button" value="Previous" onclick="javascript:document.getElementById('op').value='Step4';document.form.submit();" class="redbutton">
			
<?		} 
		###################
		# go on
		else {

			#####################
			# ask admin login access
			?>
			<table border="0">
				<tr>
				<td align="left">Username</td>
				<td><input type="text" NAME="admin" SIZE=30 maxlength=80 value="admin"></td>
				<td></td>
				</tr>
				<tr><td align="left">Name</td>
				<td><input type="text" NAME="adminname" SIZE=30 maxlength=80 value="Default 	"></td>
				<td></td>
				</tr>
				<tr><td align="left">Password</td>
				<td><input type="password" NAME="adminpasswd" SIZE=30 maxlength=80 value=""></td>
				<td></td>
				</tr>
				<tr><td align="left">Password confirmation</td>
				<td><input type="password" NAME="adminpasswd_check" SIZE=30 maxlength=80 value=""></td>
				<td></td>
				</tr>
			</table>
			<br />
			<?
			#####################
			# print config rows
			?>
			<table width="580" border=0>
			<?
			# create new site for config script
			include_once($class_path.'port.inc.php');
			$site = new Site(array(
				'on_debug' => ($_COOKIE['debug'] ? 1:0),
				'on_admin_keel' => 1
			));
			include_once("admin/change_config.php");

			print_config_table();

			$site->site_polling(2); // poll Saurus for site stats
			?>

			</table>

			<br />
			<br />

			<center>
			<INPUT type="button" value="Previous" onclick="javascript:document.getElementById('op').value='Step4';document.form.submit();" class="redbutton">
			<INPUT type="submit" value="Next" class="redbutton">

 	<?
	} # if config tabelit pole olemas
	?>
		<INPUT type="hidden" name="op" id="op" value="Step6">
		<INPUT type="hidden" name="install" value="1">
		<INPUT type="hidden" name="dont_make_db" value="1">
		</form>
		</center>
<?
}
# / if INSTALL  step5
#########################
#########################
# if UPDATE  step5
else {
?>
	<h2>Site Settings</h2>
	<br />

	<form action="install.php" method="post" name="form">
	<table border="0" cellspacing="0" cellpadding="3" width="580">
	<?
	#####################
	# connect to database 
	if (!$conn) { dbconnect(0, '', '', ''); }

	#####################
	# print config rows

	include_once("admin/change_config.php");
	print_config_table();
?>
	</table>

	<br />
	<center>
	<INPUT type="submit" value="Next" class="redbutton">

	<INPUT type="hidden" name="op" id="op" value="Step6">
	</form>
	</center>

<?
}
# / if UPDATE  step5
#########################
?>	
	</font>
	</td>
  </tr>
  </table>
<?

	break;
/**************************
STEP 6
INSTALL
- save cms admin account
- save configuration table data
UPGRADE
- save configuration table data
***************************/
    case "Step6":
?>
  <table width="580" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><font class="txt">

	<form action="install.php" method="post" name="form">
<?
#########################
# if INSTALL step6

if ($install) {

	set_hostname_wwwroot($_SERVER['HTTP_HOST'].str_replace('install.php', '', $_SERVER['REQUEST_URI']));

	$error = store_admin_data(); 
	
		###################
		# if error
		if ($error) {
?>
			<h2>Installation error</h2>

			<font color="red"><?=$error?></font>

			<br />
			<br />
			<INPUT type="button" value="Back" class="redbutton" onclick="javascript:history.back();">
			
<?		} 
		###################
		# go on
		else { ?>
			<h2>Installation Finished</h2>

<?			store_config_data();
?>
			<p>Congratulations, we hope you enjoy your new copy of Saurus CMS!</p>
			<p>Please write down your login and password. If you lose it, the password cannot be recovered.</p>
			<p>
				Username: <strong><?=$FDAT["admin"] ?></strong><br />
				Password: <strong><?=$FDAT["adminpasswd"]?$FDAT["adminpasswd"]:"saurus" ?></strong>
			</p>

			<p>&nbsp;</p>
			<p>If you are in Linux environment don't forget to run post_install.sh script now! <br>It will delete install.php from your website root directory for security reasons and also change file permissions for config.php back to normal.
			</p>
<?
		} # if OK
}
# / if INSTALL  step6
#########################
#########################
# if UPDATE  step6
else {
	// clean template and site cache
	ini_set('display_errors', 'On');
	new SQL("DELETE FROM cache WHERE url <> ''");
	
	include_once($class_path.'adminpage.inc.php');
	clear_template_cache(getcwd().'/classes/smarty/templates_c/');
?>
	<h2>Update Finished</h2>
	<br />
	<? store_config_data(); ?>
	
	Congratulations, we hope you enjoy your new copy of Saurus CMS!<br />
	
	What to do next?<br />
	
	<br />
<?
}
# / if UPDATE  step6
#########################

###################
# if not error
if (!$error) {
?>

<li><a href="index.php">View your website</a></li>
<li><a href="editor/index.php">Log in to content editor's view</a></li>
<li><a href="http://www.saurus.info/">Visit Saurus CMS homepage for docs and downloads</a></li>
<br />
<br />
<?
}
?>
<input type="hidden" name="op" id="op" value="Step6">
</form>

	</font>
	</td>
  </tr>
  </table>
<?

    break;

	default:
/**************************
STEP 1
INSTALL:
-intro
-check config file chmod
-EULA agreement
-system requirements
UPDATE:
-intro
-system requirements
- backwards compability check
***************************/
?>
  <table width="730" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><font class="txt">
<?
#########################
# if INSTALL step1
//$install = 1;
if ($install) {

	?>
	<h2>Welcome</h2>
	<p>This will install a fresh copy of Saurus CMS Community Edition version 4.7.0.</p>
	<p>You will be taken through a number of pages, each configuring a different portion of your site. <br />We estimate that the entire process will take about 5 minutes.</p>
	
	<?

	#########################
	# check if file config.php is writable

	######## get absolute path of website root
	$absolute_path = getcwd().'/';
	# strip /admin|editor|classes/ from the end
	if (preg_match("/(.*)\/(admin|editor|classes|temp|test)\/$/", $absolute_path, $matches) || preg_match("/(.*)\\\(admin|editor|classes|temp|test)\/$/", $absolute_path, $matches)) {
		$absolute_path = $matches[1];
	}
	# add slash to the end
	if (!preg_match("/\/$/",$absolute_path)) {$absolute_path .= "/"; }

	####### read config.php and config-old.php
	$file = $absolute_path."config.php";
	$file2 = $absolute_path."config-old.php";

	#########################
	# if config is writable
	if(is_writable($file) && is_writable($file2)){
?>
		<script type="text/javascript">
		function toggle_next(check_box)
		{
			if(check_box.checked) document.getElementById('next_button').disabled = false;
			else document.getElementById('next_button').disabled = true;
		}
		</script>
		<p><input id="eula_agree_check" type="checkbox" onclick="toggle_next(this);"><label for="eula_agree_check">I agree with the <a href="eula_en.html" target="_blank">license agreement</a></label></p>
	
	<?
	#####################
	# print requirements table
	?>
	<br />
	<h2>System Requirements</h2>
	<p>
		Please scroll down to check the system requirements and press Next to continue. Incompatibilities between required values and your system are marked red.
	</p>
	<?
	$called_from_another_script = 1;
	include_once("admin/check_requirements.php");
	print_requirements_table();
	unset($called_from_another_script);
	
	?>
			<form action="install.php" method="post" name="form">
			<center>
			<INPUT type="hidden" name="op" id="op" value="Step2">
			<INPUT type="submit" id="next_button" value="Next" class="redbutton" disabled>
			</center>
			</form>
			<br />

	<?
	#########################
	# if config is NOT writable
	} else { ?>
		<font color=red>
		<br />
		Error: File permissions are incorrect! Configuration files must be temporarily writable for the webserver during installation, please set 666 permissions for following files:<br /><br />
		<b><?echo $file?></b>
		<br />
		<b><?echo $file2?></b>
		</font>
		<br />
		<br />
		<form action="install.php" method="post" name="form">
		<center>
		<INPUT type="hidden" name="op" id="op" value="">
		<INPUT type="submit" value=" Check again " class="redbutton">
		</center>
		</form>
	<?    
	} 
	# / if config is writable
	#########################


}
# / if INSTALL step1
#########################

#########################
# if UPDATE step1
else {
	
	$sql = 'select license_key from license order by date desc limit 1';
	$result = new SQL($sql);
	
	$license_key = $result->fetchsingle();
?>
	<h2>Welcome</h2>

	This will update your Saurus CMS <?php echo (strtolower($license_key) == 'free' ? 'Free' : (!$license_key ? 'Community Edition' : '')); ?> <?=$current_ver?><?php echo ($license_key && strtolower($license_key) != 'free' ? ' using license: '.$license_key : ''); ?> to Saurus CMS Community Edition version <?=$new_ver?>.<br />
	
	You will be taken through a number of pages, each configuring a different portion of your site. The entire process should take about 5 minutes.<br />
	
<? if ($current_ver) {		?>

	<br />
	<br />	
	<? 
	####################
	# same version reinstall

	if ($current_ver == $new_ver) { ?>
		<br /><font color=red>
		NB! You already have version <?=$new_ver?>. If you want to repair or re-install it, click Next.</font>
		<br />
	<?}


	#####################
	# print requirements table
?>
	<br /><h2>System requirements</h2>
	
	Please scroll down to check the system requirements and press Next to continue. Incompatibilities between required values and your system are marked red.<br />
	
	<?
	$called_from_another_script = 1;
	include_once("admin/check_requirements.php");
	print_requirements_table();
	unset($called_from_another_script);

	if (version_compare($current_ver, '4.6.6') == -1) { ?>
	
	<br />
	<br />
	Because of compatibility issues the CMS version must be at least <b>4.6.6</b> before you can upgrade to Community Edition.
	<br />
	<br />
	
	<?php } else { 
	###################
	# NEXt button
	?>
	<br />
	<br />
		<form action="install.php" method="post" name="form">
		<center>
		<INPUT type="hidden" name="op" id="op" value="Step2">
		<INPUT type="submit" value="Next" class="redbutton">
		</center>
		</form>
		<br /><br />
	<?php }  ?>
	<?

	} # site not found => show error
	else {?>
		<br />
		<br /><font color=red>
		not found!</font>
		<br />
	<?}?>

	<?	#####################
	# check backwards compability (initially for version 4.0.6, Bug #1597)
?>
	<?
	$called_from_another_script = 1;
	
	foreach ($versions as $version_array_index => $tmpver) {
		$next = $versions[$version_array_index + 1];
		# if not current version yet, go to next ver
		# jooksev ver <= installitav ver
		
		if ($current_ver != $tmpver && strnatcmp($current_ver,$tmpver)<=0) {

			if(file_exists('admin/updates/check_compability'.$tmpver.'.php'))
			{
				include_once('admin/updates/check_compability'.$tmpver.'.php');
				$check_compability_function = 'check_compability_'.str_replace('.', '', $tmpver);
				$check_compability_function();
			}
		}
	} # foreach
	
	unset($called_from_another_script);
}
# / if UPDATE step1
#########################

	if( ! $skip_html) { # display HTML output			

?>	
	</font>
	</td>
  </tr>
  </table>
<?
	} # if display HTML output 
	
 break;
} # op

if( ! $skip_html) { # display HTML output
?>
		</div>
			<!-- //Scrollable area -->
	   
<?
######################
# footer 
?>  

<div id="installfooter">
	&copy; Copyright 2000 - 2010 Saurus | <a href="http://www.saurus.info" target="_blank">www.saurus.info</a>
</div>

</center>
</body>
</html>
<?
} # if display HTML output 
/**************************
END HTML
***************************/

/***********************************/
/* PRINT_DBDATA_EDITABLETEXT       */
/* shows config form               */
/***********************************/
function print_dbdata_editabletext()
{
	global $CONF;

?>
    <table border=0 width="500">	
	<tr>
	<td align="left">Database host</td>
    <td><input type="text" NAME="dbhost" SIZE=30 maxlength=60 value="<?=$CONF[dbhost];?>"></td>
	</tr>
    <tr>
	<td align="left">Database port</td>
    <td><input type="text" NAME="dbport" SIZE=30 maxlength=16 value="<?=$CONF[dbport];?>"></td>
	</tr>
	<td align="left">Database name</td>
    <td><input type="text" NAME="db" SIZE=30 maxlength=32 value="<?=$CONF[db];?>"></td>
	</tr>
    <tr>
	<td align="left">Database user name</td>
    <td><input type="text" NAME="user" SIZE=30 maxlength=16 value="<?=$CONF[user];?>"></td>
	</tr>
    <tr>
	<td align="left">Database user password</td>
    <td><input type="password" NAME="passwd" SIZE=30 maxlength=80 value="<?=$CONF[passwd];?>"></td>
	</tr>
    <tr>
	<td align="left">Database type</td>
    <td><select name="dbtype">
            <option value="mysql">&nbsp;MySQL&nbsp;</option>
        </select>
    </td>
	</tr>
    </table>

<?php
}
/***********************************/
/* PRINT_DBDATA_TEXT               */
/* shows config table for confirmation */
/***********************************/
function print_dbdata_text()
{
	global $CONF;
	
?>
    <table border=0 width="400">
            <tr>
			<td align="left">Database host</td>
            <td align="left"><?=$CONF[dbhost];?></td>
			</tr>
            <tr>
			<td align="left">Database port</td>
            <td align="left"><?=$CONF[dbport];?></td>
			</tr>
            <tr>
			<td align="left">Database name</td>
            <td align="left"><?=$CONF[db];?></td>
			</tr>
            <tr>
			<td align="left">Database user name</td>
            <td><?=$CONF[user];?></td>
			</tr>
            <tr>
			<td align="left">Database user password</td>
            <td><?=$CONF[passwd];?></td>
			</tr>
            <tr>
			<td align="left">Database type</td>
            <td align="left"><?=$CONF[dbtype];?></td>
			</tr>
    </table>
<? 
} 
/***********************************/
/* PRINT_DBDATA_HIDDEN               */
/* shows config table hidden */
/***********************************/
function print_dbdata_hidden()
{
	global $CONF;

?>
<input type="hidden" NAME="dbhost" SIZE=30 maxlength=80 value="<?=$CONF[dbhost];?>">
<input type="hidden" NAME="dbport" SIZE=30 maxlength=80 value="<?=$CONF[dbport];?>">
<input type="hidden" NAME="db" SIZE=30 maxlength=80 value="<?=$CONF[db];?>">
<input type="hidden" NAME="user" SIZE=30 maxlength=80 value="<?=$CONF[user];?>">
<input type="hidden" NAME="passwd" SIZE=30 maxlength=80 value="<?=$CONF[passwd];?>">
<input type="hidden" NAME="dbtype" SIZE=30 maxlength=80 value="<?=$CONF[dbtype];?>">

<? 
} 