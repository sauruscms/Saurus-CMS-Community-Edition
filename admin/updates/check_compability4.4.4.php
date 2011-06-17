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

/**
 * Script checks if in templates the position is fixed to 9.
 * If found any, prints out warning for user. If not found, prints out OK message.
 * See more details in Bug #2546.
 * 
 * This is stand-alone script, should be copied to the website root directory. 
 * This Script is also included in the:
 * - CMS installation/update script, step one
 *
 * @package CMS
 * @author Saurus development team, http://www.saurus.info/
 * @version 4.4.4
 * @copyright 2002-2007 Saurused O�.
 * 
 * 
 */

######################
# GLOBALS
global $called_from_another_script;
global $site;
global $class_path;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";

include_once($class_path."port.inc.php");

$site = new Site(array(
	'on_debug' => 0,
));

###########################
# HTML: if this file is not included from another script then print html 

if(!$called_from_another_script) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Check compability for version 4.4.4</title>
</head>
<body>
<?php check_compability_444(); ?>
</body>
</html>
<?php
}  # if this file is not included from another script

###########################
# FUNCTION check_compability
function check_compability_444() {

	global $called_from_another_script, $class_path, $site;

	$errors = array();

	##########################
	# otsi SAPI malle, mille moodulid on kas aktiivsed v�i mis pole seotud mooduliga �ldse

	$sql = $site->db->prepare("SELECT templ_tyyp.ttyyp_id,templ_tyyp.nimi,templ_tyyp.templ_fail
		FROM templ_tyyp
		WHERE 
			templ_tyyp.ttyyp_id >= 1000 AND templ_tyyp.ttyyp_id < 2000  
		ORDER BY templ_tyyp.nimi");
		
	$sth = new SQL ($sql);
	while ($tpl = $sth->fetch()) {
		$data = '';
		$file_path = $class_path.'smarty/templates/'.$tpl['templ_fail'];

		if(file_exists($file_path) && $fp = fopen($file_path, 'r'))
		{
			while (!feof ($fp))
			{
				$data .= fgets($fp, 4096);
			}
			fclose($fp);	
			
			// get all smarty tags content
			if(preg_match_all("/\{\s*(.*?)\s*\}/", $data, $matches))
			{
				foreach ($matches[1] as $match)
				{
					if(preg_match("/position(\s*)=(\s*)(\"|'?)(\s*)([\d,]*)(?<=(\s|,|=|\"|'))[9|5]{1}(?!(\d))/", $match))
					{
						$errors[] = 'Warning: Found Saurus API code with position=9 or position=5 in template "<b>'.$tpl['nimi'].'</b>".';
					}
				}
			}
			
			if(preg_match("/asukoht(\"|')(\s*)=>(\s*)(\"|'?)(\s*)([\d,]*)(?<=(\s|,|=|\"|'))[9|5]{1}(?!(\d))/", $data) && !in_array('Warning: Found Saurus API code with position=9 or position=5 in template "<b>'.$tpl['nimi'].'</b>".', $errors))
			{
				$errors[] = 'Warning: Found Saurus API code with position=9 or position=5 template "<b>'.$tpl['nimi'].'</b>".';
			}

		} # fopen OK
	} # while tpl

	if(count($errors))
	{
		$errors[] = 'Warning: Sections\' default positions are re-designed and may cause backwards compability issues because because 4.4.4 version update converts position values 9 and 5 to 0. The converted object ID\'s will be logged in the Site log after the update is completed.';
	}
	
	############### PRINT WARNINGS

	if(sizeof($errors)>0) {
		echo '<font color=red>';
		print (join('<br>',$errors));

		echo '<br><br><b>NB! If any of these templates are in active use on the website, please cancel the version update process and contact your site developer for further instructions.</b>';
		echo '</font>';
		echo '<br><br>';
	}
	## all OK
	else {
		if(!$called_from_another_script){
			echo "Compability check passed OK. Sleep peacefully.<br><br>";
		}
	}
}
# / FUNCTION check_compability
###########################
