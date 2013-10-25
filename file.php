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


##############################
# Download objects: documents, pictures
# : is usually link target for document object links (opened in new window)
# : is independent script, not for including, new Site is generated
##############################

global $site;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";
include($class_path."port.inc.php");

$site = new Site(array(
	on_debug=>0
));

$id = preg_replace("/^(\d+).*?$/", "\\1", $_SERVER['QUERY_STRING']);

$objekt = new Objekt(array(
	objekt_id => $id,
	on_sisu=>1,
));

if ($objekt->all['klass']=="file" && $objekt->permission['R']) {

	$sql = $site->db->prepare("SELECT * FROM obj_file WHERE objekt_id = ?", $id);
	$sth = new SQL ($sql);
	$site->debug->msg($sth->debug->get_msgs());
	$result = $sth->fetch();
	
	$result['fullpath'] = preg_replace('#/$#', '', $site->absolute_path).$result['relative_path'];
	
	$DB =& $site->db;
	$log_object_type = 'doc';
	global $DB, $log_object_type;
	
	//Find out if is picture or not 
	// how the hell is this secure with shared folder?!?!?!?!?!?!
	if(0 && preg_match("/(jpeg|png|gif)/",$result['mimetype'])) {
		/* is img */
		if (function_exists ("getimagesize")) {
			list($i_width, $i_height, $i_type, $i_attr) = getimagesize($result['fullpath']);
		} else {
			$i_width = 720; 
			$i_height = 470;
		}
		
		//Find out if we are in secure or public dir
		if(false !== strpos($result['fullpath'],$site->CONF['secure_file_path'])) {
		/* SECURE */
			$root_dir = $site->CONF['secure_file_path'];
		} else {
		/* PUBLIC */
			$root_dir = $site->CONF['file_path'];
		}

		if(preg_match("/^.*(".str_replace('/','\/',$root_dir).".*)$/",$result['fullpath'],$regs)) {
			$root_dir = preg_replace('/\/[^\/]+$/i','',$regs[1]);
		} else {
			$root_dir = '..'.$root_dir;
		}

/* Display HTML */
?>
<html>
<head>
<title><?= $result['filename'] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<SCRIPT LANGUAGE="JavaScript">
<!--
	function init() {
		window.resizeTo(<?= $i_width+20 ?>,<?= $i_height+40 ?>);
		window.moveBy(-<?= ceil($i_width/2) ?>,-<?= ceil($i_height/2) ?>);
	}
//-->
</SCRIPT>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="overflow:auto;" onload="init()">
<table cellspacing=0 cellpadding=0 width="100%" height="100%" bgcolor="FFFFFF">
<tr><td valign="middle" align="center"><a href="javascript:close();" target="_self"><img src="<?= $site->CONF['wwwroot'].$root_dir.'/'.$result['filename'] ?>" border="0"></a></td>
</tr>
</table>
</body>
</html>
<?php 
	} else {


		header("Content-Disposition: attachment; filename=\"".$result['filename']."\"");
		header("Content-Type: ".$result['mimetype']);
		header("Cache-control: private");
		header("Pragma: public");

		$doc_full_path = realpath($result['fullpath']);
		if (@file_exists($doc_full_path)) {
			$in = fopen($doc_full_path, "rb");
			if ($in) {
				$output = fread($in,filesize ($doc_full_path));
			}
			fclose($in);
		}
		header("Content-Length: ".strlen($output));
		echo $output;
	}

} else {
	header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF[hostname].$site->CONF[wwwroot].($site->admin?"/editor":"")."/?op=error&error_id=404");
}
