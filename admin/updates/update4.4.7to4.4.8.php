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

global $site;

global $class_path;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == 'editor' ? '../classes/' : './classes/';

include_once($class_path.'port.inc.php');

$site = new Site(array(
	'on_debug' => 0,
));


/*---------------------------	Code Begin	------------------------------------------*/

// empty template cache, because the SAPI function are now in Smarty plugin format
// copy-paste from admin/clear_templ_cache.php (its nice that this is not a stand-alone fuction ...)

$templ_cache_path = $site->absolute_path.'classes/smarty/templates_c/';

	function deletedir($file) { 
		chmod($file,0777); 
		if (is_dir($file)) { 
			$handle = opendir($file); 
			//while($filename = readdir($handle)) { 
			while (false !== ($file = readdir($handle))) { 
				if ($filename != "." && $filename != "..") { 
					deletedir($file."/".$filename); 
				} 
			} #while
			closedir($handle); 
			if (@rmdir($file)){return 1;}; 
		} else { 
			if(@unlink($file)) return 1; 
		} 
	} 

	if ($DIR = @opendir($templ_cache_path)) {


		############################
		# tsükkel üle failide
		while (false !== ($file = readdir($DIR))) { 
			if ($file != "." && $file != "..") { 
				if (!@deletedir($templ_cache_path.$file)){
					$err_catalogs[] = $templ_cache_path.$file;
				};
			} # ./..
		}
		# / tsükkel üle failide
		############################

		if (count($err_catalogs)){
			$error .= "<br><br><font color=red><b>Error! Make sure that directories:</b><br><br>";
			$error .= join("<br>", $err_catalogs);
			$error .=  "<br><br><b>have write permissions for the web server.</b><br></font>";
		}
		closedir($DIR); 
		clear_cache("ALL");
	}
	# kui kataloogi ei saa avada, kirjutada logisse veateade
	else {
		print "<br><font color=red>Error! Can't open directory '".$templ_cache_path."'</font>";
	}


/*---------------------------	Code End	------------------------------------------*/

if ($site->on_debug){

	$site->debug->msg('SQL päringute arv = '.$site->db->sql_count.'; aeg = '.$site->db->sql_aeg);
	$site->debug->msg('TÖÖAEG = '.$site->timer->get_aeg());
#	$site->debug->print_msg();

}
?>