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
 * @package 	SaurusCMS
 * @copyright 	2000-2010 Saurused Ltd (http://www.saurus.info/)
 * @license		Mozilla Public License 1.1 (http://www.opensource.org/licenses/mozilla1.1.php)
 * 
 */

$class_path = 'classes/';

include($class_path.'port.inc.php');
include($class_path.'Update.class.php');
include_once($class_path."install.inc.php"); # all installation related functions

$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1 : 0),
	'on_admin_keel' => 1,
));
ini_set('display_errors', 0);

$sqlK = "SELECT encoding FROM keel WHERE on_default = '1'"; 
$sthK = new SQL($sqlK);
$encoding = $sthK->fetchsingle();
$encoding = $encoding ? $encoding : 'UTF-8';

$cli = php_sapi_name() == 'cli' ? true : false;

if(!($site->user->is_superuser || $cli))
{
	print '<font color=red>Error: you need permissions to run updates.</font>';
	exit;
}

############# CONF
$CONF = ReadConf(); # db connect data from config.php

############# VERSION CHECK
$current_ver = current_version(); # try to connect database and find which version is installed returns 0, if no database found
//$current_ver = '4.7.FINAL';

############# VERSION NUMBERS
$versions = array(
	'4.0.0',
	'4.0.1',
	'4.0.2',
	'4.0.3',
	'4.0.4',
	'4.0.5',
	'4.0.6',
	'4.0.7',
	'4.0.8',
	'4.0.9',
	'4.0.10',
	'4.0.11',
	'4.0.12',
	'4.0.13',
	'4.0.14',
	'4.0.15',
	'4.1.0',
	'4.1.1',
	'4.2.0',
	'4.2.1',
	'4.2.2',
	'4.2.3',
	'4.2.4',
	'4.3.0',
	'4.3.1',
	'4.3.2',
	'4.3.3',
	'4.3.4',
	'4.3.5',
	'4.3.6',
	'4.4.0',
	'4.4.1',
	'4.4.2',
	'4.4.3',
	'4.4.4',
	'4.4.5',
	'4.4.6',
	'4.4.7',
	'4.4.8',
	'4.5.0',
	'4.5.1',
	'4.5.2',
	'4.5.3',
	'4.5.4',
	'4.5.5',
	'4.5.6',
	'4.5.7',
	'4.5.8',
	'4.6.0',
	'4.6.1',
	'4.6.2',
	'4.6.3',
	'4.6.4',
	'4.6.5',
	'4.6.6',
	'4.7.0',
	'4.7.1',
);
##############################

# get the new version number
$new_ver = end($versions);
//$url = site_url();

##########################
# default_data_files 

$default_data_files = array();

foreach ($versions as $version_array_index => $tmpver)
{
	$next = $versions[$version_array_index + 1];
	# if not current version yet, go to next ver
	# jooksev ver <= installitav ver
	
	if (strnatcmp($current_ver, $tmpver) <= 0) {

		# if overinstalling same ver, then go back in versions
		# jooksev ver = installitav ver
		if (strnatcmp($current_ver,$new_ver)==0) {
		}
		# usual case
		else {
			array_push($default_data_files, "admin/updates/update".$tmpver."to".$next.".sql");
		}
	}
} # foreach

# remove last element if not repairing/overinstalling same version
if (strnatcmp($current_ver,$new_ver) != 0) {
	array_pop($default_data_files);
}

$update = new Update();

$update->scanUpdates();

if($cli)
{
	if($update->lastUpdate != $update->updateTo)
	{
		echo 'This will apply '.(sizeof($default_data_files) ? ' version updates "'.implode('", "', $default_data_files).'" and ' : NULL).'updates '.($update->lastUpdate + 1).' through '.$update->updateTo.' to Saurus CMS, import dictonary files and syncronise extensions.'."\n";
		
		new Log(array(
			'action' => 'update',
			'type' => 'NOTICE',
			'message' => 'Update started',
		));
		
		if(sizeof($default_data_files))
		{
			echo 'Version updates: '.implode(', ', $default_data_files);
			$update->runVersionUpdates($default_data_files);
			echo '.'."\n";
		}
				
		$update->runUpdates();

		echo 'Synchronising extensions';
		$update->synchroniseExtensions();
		echo '.'."\n";
		
		echo 'Importing glossaries';
		$update->importGlossaries();
		echo '.'."\n";
		
		echo 'Clearing caches';
		$update->clearCaches();
		echo '.'."\n";

		new Log(array(
			'action' => 'update',
			'type' => 'NOTICE',
			'message' => 'Update finished',
		));
		
		echo 'Finished.'."\n";
	}
	else 
	{
		echo 'There are no new updates, the last update applied was "Update '.$update->lastUpdate.'"'."\n";
	}
}
else 
{

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Saurus CMS Community Edition Update</title>
		<meta name="author" content="Saurus - www.saurus.info">
		<meta http-equiv="Cache-Control" content="no-cache">
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding?>">
		<link rel="stylesheet" href="styles/default/scms_general.css">
		<link rel="stylesheet" href="styles/default/scms_install.css">
	</head>

<body>
<center>
<div id="installheader">
	<table width="750">
	<tr>
		<td valign="bottom"><h1>Saurus CMS Community Edition Update</h1></td>
		<td align="right"><img class="logo" src="styles/default/gfx/install/logo.gif" height="20" width="101" alt="Saurus" /></td>
	</tr>
	</table>
</div>

<div id="listing" class="scms_scroll_div">

	<table width="700" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td><font class="txt">
			
			<?php if($_GET['update'] && ($update->lastUpdate != $update->updateTo)): ?>
			
				<?php
					new Log(array(
						'action' => 'update',
						'type' => 'NOTICE',
						'message' => 'Update started',
					));
				?>
			
				<h2>Updating</h2>
				
				<?php if(sizeof($default_data_files)): ?>
				<p>Version updates: <?php $update->runVersionUpdates($default_data_files); ?></p>
				<?php endif; ?>
				
				<p><?php $update->runUpdates(); ?></p>
				
				<p>Synchronising extensions<?php $update->synchroniseExtensions(); ?>.</p>
				
				<p>Importing glossaries <?php $update->importGlossaries(); ?>.</p>
			
				<p>Clearing caches<?php $update->clearCaches(); ?>.</p>
				
				<h2>Update Finished</h2>
				
				<li><a href="index.php">View your website</a></li>
				<li><a href="editor/index.php">Log in to content editor's view</a></li>
				<li><a href="http://www.saurus.info/">Visit Saurus CMS homepage for docs and downloads</a></li>
				
				<?php
					new Log(array(
						'action' => 'update',
						'type' => 'NOTICE',
						'message' => 'Update finished',
					));
				?>
			
			<?php elseif($update->lastUpdate == $update->updateTo): ?>
			
				<p>There are no new updates, the last update applied was "Update <?php echo $update->lastUpdate; ?>".</p>
			
			<?php else: ?>
			
				<p>This will apply <?php echo (sizeof($default_data_files) ? ' version updates "'.implode('", "', $default_data_files).'" and ' : NULL); ?>updates <?php echo ($update->lastUpdate + 1); ?> through <?php echo $update->updateTo; ?> to Saurus CMS, import dictonary files and syncronise extensions.</p>
				<p>Make sure to have backups before proceeding.<p>
				
				<h2>System Requirements</h2>
				<p>Please scroll down to check the system requirements and press Next to continue. Incompatibilities between required values and your system are marked red.</p>
				<?php
				$called_from_another_script = 1;
				include_once("admin/check_requirements.php");
				print_requirements_table();
				unset($called_from_another_script);
				?>
				
				<form method="GET">
					<INPUT type="submit" name="update" value="Update" class="redbutton">
					<input type="hidden" name="update" value="1">
				</form>
				
			<?php endif; ?>
			
			</td>
		</tr>
	</table>
</div>

<div id="installfooter">
	&copy; Copyright 2000 - 2011 Saurus | <a href="http://www.saurus.info" target="_blank">www.saurus.info</a>
</div>

</center>
</body>
</html>
<?php }