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
 * 
 * 4. run SQL file(s)
 *
 * 5. [INSTALL: CMS admin account form]
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

if(php_sapi_name() != 'cli') session_start();

$install = php_sapi_name() == 'cli' ? 1 : $_SESSION['install'];
if(php_sapi_name() != 'cli') unset($_SESSION['install']);

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

// create config.php

$is_installation_script = true; # needed for error display handling in core

if(!(file_exists('config.php') && filesize('config.php')))
{
	include_once($class_path."install.inc.php"); # all installation related functions
	update_config_php('localhost', '3306', 'sauruscms', 'sauruscms', 'nopasswdset');
}

include_once($class_path."port.inc.php");
include_once($class_path."nodebug.inc.php");
include_once($class_path."install.inc.php"); # all installation related functions

# set error display second time - to override settings in port.inc.php
ini_set('display_errors', $display_errors); // hide or display all errors from screen

############# CONF
$CONF = ReadConf(); # db connect data from config.php

############# VERSION CHECK
$current_ver = current_version(); # try to connect database and find which version is installed returns 0, if no database found

// CLI install
if(!$current_ver && php_sapi_name() == 'cli')
{
	$opts  = array(
		'h:', // dbhost:dbport default localhost:3306
		'd:', // db
		'u:', // dbuser
		'p:', // dbpass
		'U:', // cms user
		'P:', // cms pass
		'E:', // cms user e-mail
		'H:', // cms hostname/wwwroot
		'L:', // cms default language and default admin language
		'T:', // cms page template
	);	
	
	$options = array(
		'dbhost' => 'localhost',
		'dbport' => '3306',
		'db' => NULL,
		'dbuser' => NULL,
		'dbpasswd' => NULL,
		'cmsuser' => NULL,
		'cmspasswd' => NULL,
		'cmsemail' => NULL,
		'cmshostname' => NULL,
		'cmswwwroot' => NULL,
		'cmslanguage' => 1,
		'cmsadminlanguage' => 1,
		'cmspagetemplate' => NULL
	);
	
	$opts = getopt(implode('', $opts));
	
	foreach ($opts as $key => $opt)
	{
		if($opt === false)
		{
			echo 'Value missing for '.$key."\n"; echo 11;
			exit(1);
		}
		
		switch ($key)
		{
			case 'h':
				$opt = explode(':', $opt);
				if($opt[0]) $options['dbhost'] = $opt[0];
				if($opt[1]) $options['dbport'] = $opt[1];
			break;
			
			case 'H':
				$opt = explode('/', $opt);
				if($opt[0]) $options['cmshostname'] = $opt[0];
				if($opt[1]) $options['cmswwwroot'] = '/'.$opt[1];
			break;
			
			case 'L':
				$opt = explode(',', $opt);
				if(is_numeric($opt[0])) $options['cmslanguage'] = (int) $opt[0];
				if(is_numeric($opt[1])) $options['cmsadminlanguage'] = (int) $opt[1];
			break;
			
			case 'd': $options['db'] = $opt; break;
			
			case 'u': $options['dbuser'] = $opt; break;
			
			case 'p': $options['dbpasswd'] = $opt; break;
			
			case 'U': $options['cmsuser'] = $opt; break;
			
			case 'P': $options['cmspasswd'] = $opt; break;
			
			case 'E': $options['cmsemail'] = $opt; break;
			
			case 'T': $options['cmspagetemplate'] = $opt; break;
			
			default: break;
		}
	}
	
	foreach ($options as $key => $opt)
	{
		if(is_null($opt) && !in_array($key, array('cmswwwroot', 'cmspagetemplate', 'cmsemail')))
		{
			echo 'Value missing for '.$key."\n";
			exit(1);
		}
	}
	
	if($options['cmspasswd'] == 'saurus')
	{
		echo "The CMS password can't be 'saurus'.\n";
		exit(1);
	}
	
    if ($options['cmsemail'] && !filter_var($options['cmsemail'], FILTER_VALIDATE_EMAIL)) {
        echo "Illegal administrator e-mail address.\n";
        exit(1);
    }
	//var_dump($options);
	
	// write config 
	update_config_php($options['dbhost'], $options['dbport'], $options['db'], $options['dbuser'], $options['dbpasswd']);
	
	global $conn;
	
	$CONF = ReadConf();
	
	$db_found = check_db();
	
	if(!$db_found)
	{
		echo 'Could not connect to database.'."\n";
		if($conn->error) echo $conn->error."\n";
		exit(1);
	}
	
	############# VERSION CHECK
	$current_ver = current_version(); # try to connect database and find which version is installed returns 0, if no database found
	
	if($current_ver)
	{
		echo 'CMS is already installed, to update run update.php'."\n";
		exit(1);
	}
	
	// create folders
	files_folders_permissions();
	
	// dump database
	include_once('admin/updates/full_install_db.php');
	
	echo 'Installing database: ';
	dump_full_database();
	echo "\n";
	
	// set hostname, wwwroot and default languages
	# update database
	new SQL("UPDATE config SET sisu='".$options['cmshostname']."' WHERE nimi='hostname'");

	# update database
	new SQL("UPDATE config SET sisu='".$options['cmswwwroot']."' WHERE nimi='wwwroot'");

	# update database
	new SQL('UPDATE keel SET on_default=0 WHERE on_default=1');
	new SQL('UPDATE keel SET on_default_admin=0 WHERE on_default_admin=1');
	new SQL('UPDATE keel SET on_default=1 WHERE keel_id = '.$options['cmslanguage']);
	new SQL('UPDATE keel SET on_default_admin=1 WHERE keel_id = '.$options['cmsadminlanguage']);
	
	$site = new Site(array(
		'on_debug' => ($_COOKIE['debug'] ? 1:0),
		'on_admin_keel' => 1
	));

	$site->site_polling(2); // poll Saurus for site stats
	
	// create the user
	$FDAT['adminname'] = $FDAT['admin'] = $options['cmsuser'];
	$FDAT['adminpasswd'] = $FDAT['adminpasswd_check'] = $options['cmspasswd'];
	$FDAT['adminemail'] = $options['cmsemail'];
	
	store_admin_data();
	
	// run updates
	include_once($class_path.'Update.class.php');
	$update = new Update();
	
	$update->runUpdates();

	echo 'Synchronising extensions';
	$update->synchroniseExtensions();
	echo '.'."\n";
	
	echo 'Importing glossaries:'."\n";
	$update->importGlossaries();
	echo 'done.'."\n";
	
	echo 'Clearing caches';
	$update->clearCaches();
	echo '.'."\n";

    if(isset($options['cmspagetemplate']))
    {
        echo 'Setting default page template to '.$options['cmspagetemplate'];
        $sql = $conn->prepare("SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail LIKE ? LIMIT 1",'%'.$options['cmspagetemplate']);
        $sth = new SQL($sql);
        $template_id = $sth->fetchsingle();
        if($template_id)
        {
            $sql = $conn->prepare("UPDATE keel SET page_ttyyp_id=? WHERE on_kasutusel=?",$template_id,1);
            $sth = new SQL($sql);
        }
        echo 'done.'."\n";
    }

	echo 'Done.'."\n";
	exit(0);
}
elseif (php_sapi_name() == 'cli')
{
	echo 'CMS is already installed, to update run update.php'."\n";
	exit(1);
}

# kui esileht ja current versiooni ei leitud, siis j�relikult install
if (!$current_ver && !$install) {
	$install = 1;
}
$step_count = 6;
$url = site_url();

##########################
# default_data_files 

$default_data_files = array();

if (!$install)
{
	print '<font color=red>Error: Saurus CMS is already installed, to update run the <a href="update.php">update.php</a> script.</font>';
	exit;
}

$encoding = 'UTF-8';

/***********************************/
/* HTML START                      */
/***********************************/

if( ! $skip_html) { # display HTML output
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Saurus CMS CE Installation</title>
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
		<td valign="bottom"><h1>Saurus CMS CE Installation: Step <?=$step_nr?> of 6</h1></td>
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
	<?php $_SESSION['install'] = 1; ?>
	</center>
	</form>
<?
}
# / if INSTALL step2
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

	<form action="install.php" method="POST" name="form" enctype="multipart/form-data">

	<?/*** write db connect data into config file ***/?>
	Database connection parameters were saved into file:
	<?    

	$conf_update_result = update_config_php($FDAT["dbhost"], $FDAT["dbport"], $FDAT["db"], $FDAT["user"], $FDAT["passwd"]); 


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
	<?php $_SESSION['install'] = 1; ?>
	</form>

	<br />
<?
}
# / if INSTALL  step3
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
***************************/

#########################
# if INSTALL  step4
if ($install) {

?>

	<h2>Updating Database</h2>
	
	<form action="install.php" method="post" name="form">
	<?	

	if (!$FDAT["dont_make_db"]) {
		if ($_POST["update_user"] != '' || $_POST["update_passwd"] != '') {
			$conn = 0;
			dbconnect(1, $_POST["update_user"], $_POST["update_passwd"], $CONF["db"]);
		} 
		### use CONF values
		elseif($CONF["user"] != '' || $CONF["passwd"] != '') {
			$conn = 0;
			dbconnect(1, $CONF["user"], $CONF["passwd"], $CONF["db"]);
		}
		else {
			print "<font color=red>Error: DB user name and password were empty!</font><br />";
			exit;
		}
		
		include_once('admin/updates/full_install_db.php');
		dump_full_database();
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
			<p>Done.</p>
	<?
	} # tbl error 

	?>

	<center>
		  <p><input type="submit" value="Next" class="redbutton"></p>
	</center>

		<input type="hidden" name="op" id="op" value="Step5">
		<?php $_SESSION['install'] = 1; ?>
	      
	</form>

	<?
}
# / if INSTALL  step4
#########################

    break;

    case "Step5":
/**************************
STEP 5
INSTALL
- CMS admin account form
- show configuration table
- poll free
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

	<form action="install.php" method="post" name="form">
	<? 
	// connect to database 
	if (!$conn) { dbconnect(0, '', '', ''); }
	
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
			# create new site for config script
			include_once($class_path.'port.inc.php');
			$site = new Site(array(
				'on_debug' => ($_COOKIE['debug'] ? 1:0),
				'on_admin_keel' => 1
			));

			$site->site_polling(2); // poll Saurus for site stats
			
			// run updates
			
			include_once($class_path.'Update.class.php');
			$update = new Update();
			
			?>
			<p><?php $update->runUpdates(); ?></p>
			
			<p>Synchronising extensions<?php $update->synchroniseExtensions(); ?>.</p>
		
			<p>Importing glossaries:<br> <?php $update->importGlossaries(); ?> Done.</p>
			
			<h2>Site Settings</h2>
			
			<p>Please create user account for logging in to Saurus CMS. You can not use "saurus" for password.</p>

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
			include_once("admin/change_config.php");

			print_config_table();

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
		<?php $_SESSION['install'] = 1; ?>
		<INPUT type="hidden" name="dont_make_db" value="1">
		</form>
		</center>
<?
}
# / if INSTALL  step5
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
			
			<?php $_SESSION['install'] = 1; ?>

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
<?
		} # if OK
}
# / if INSTALL  step6
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
	<p>This will install a fresh copy of Saurus CMS Community Edition.</p>
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
	files_folders_permissions();
	$called_from_another_script = 1;
	include_once("admin/check_requirements.php");
	print_requirements_table();
	unset($called_from_another_script);
	
	?>
			<form action="install.php" method="post" name="form">
			<center>
			<INPUT type="hidden" name="op" id="op" value="Step2">
			<?php $_SESSION['install'] = 1; ?>
			<INPUT type="submit" id="next_button" value="Next" class="redbutton" disabled>
			</center>
			</form>
			<br />

	<?

}
# / if INSTALL step1
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
