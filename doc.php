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

if($objekt->objekt_id && ($objekt->all['on_avaldatud'] == 1 || $site->in_editor)) // permission check
{
	if ($objekt->all['klass']=="dokument") {
	
		$ctype = $objekt->all['mime_tyyp'] ? $objekt->all['mime_tyyp'] : "application/saurus";
	
		$sql = $site->db->prepare("SELECT * FROM obj_dokument WHERE objekt_id = ?", $objekt->all['objekt_id']);
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$result = $sth->fetch();
	
		if (!empty($site->CONF['documents_in_filesystem']) && !empty($site->CONF['documents_directory']) && file_exists(str_replace('//', '/', $site->absolute_path.$site->CONF['documents_directory'])))
		{
			$download_filename = '';
			if (!empty($_GET['filename']) && $_GET['filename'] == 'original' && !empty($result['fail'])) { $download_filename = preg_replace(array("/[<>:\"\/\\\?\|\*]+/", "/^[\s\.]+/", "/[\s\.]+$/"), '', $result['fail']); }
			else
			{
				if (!empty($objekt->all['pealkiri'])) { $download_filename = preg_replace(array("/[<>:\"\/\\\?\|\*]+/", "/^[\s\.]+/", "/[\s\.]+$/"), '', $objekt->all['pealkiri']); }
				if (!empty($result['tyyp'])) { $download_filename .= '.' . preg_replace(array("/[<>:\"\/\\\?\|\*]+/", "/^[\s\.]+/", "/[\s\.]+$/"), '', $result['tyyp']); }
				if (!preg_replace("/\.".$result['tyyp']."$/", '', $download_filename) && !empty($result['fail'])) { $download_filename = preg_replace(array("/[<>:\"\/\\\?\|\*]+/", "/^[\s\.]+/", "/[\s\.]+$/"), '', $result['fail']); }
			}
			header("Content-Disposition: ".((!empty($_GET['disposition']) && $_GET['disposition'] == 'inline') ? 'inline' : 'attachment')."; filename=\"".safe_filename2($download_filename)."\"; filename*=UTF-8''".rawurlencode($download_filename)."");
		}
		else
		{
		header("Content-Disposition: attachment; filename=\"".$result['fail']."\"");
		}
		header("Content-Type: $ctype");
		header("Cache-control: private");
	    header("Pragma: public");
	
		if ($result['download_type']) {
			if (!empty($site->CONF['documents_in_filesystem']) && !empty($site->CONF['documents_directory']) && file_exists(str_replace('//', '/', $site->absolute_path.$site->CONF['documents_directory'])))
			{
				$doc_name = md5($result['objekt_id']);
				$doc_full_path = str_replace('//','/',$site->absolute_path.$site->CONF['documents_directory'].'/'.$doc_name[0].'/'.$doc_name);
				if (!empty($site->CONF['documents_mod_xsendfile']) && in_array('mod_xsendfile', apache_get_modules())) { header('X-Sendfile: '.$doc_full_path); exit; }
			}
			else
			{
			$doc_full_path = $site->absolute_path.$site->CONF["documents_directory"]."/".$result['fail'];
			}
			if (@file_exists($doc_full_path)) {
				$in = fopen($doc_full_path, "rb");
				if ($in) {
					echo fread($in,filesize ($doc_full_path));
				}
				fclose($in);
			}
		} else {
			$output = $result['sisu_blob'];
			$sql = $site->db->prepare("SELECT content FROM document_parts WHERE objekt_id = ? ORDER BY id ASC", $objekt->all['objekt_id']);
			$sth = new SQL ($sql);
			$site->debug->msg($sth->debug->get_msgs());
			while ( $sisu = $sth->fetch()) {
				$output .= $sisu['content'];
			}
		header("Content-Length: ".strlen($output));
		echo $output;
		}	
	
	} else if ($objekt->all['klass']=="file") {
	
		$sql = $site->db->prepare("SELECT * FROM obj_file WHERE objekt_id = ?", $id);
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$result = $sth->fetch();
	
		header("Content-Disposition: attachment; filename=\"".$result['filename']."\"");
		header("Content-Type: ".$result['mimetype']);
		header("Cache-control: private");
	    header("Pragma: public");
	
		$doc_full_path = realpath(preg_replace('#/$#', '', $site->absolute_path).$result['relative_path']);
		if (@file_exists($doc_full_path)) {
			$in = fopen($doc_full_path, "rb");
			if ($in) {
				$output = fread($in,filesize ($doc_full_path));
			}
			fclose($in);
		}
		header("Content-Length: ".strlen($output));
		echo $output;
	
	
	
	} else if ($objekt->all['klass']=="pilt") {
	
		$ctype = $objekt->all['mime_tyyp'] ? $objekt->all['mime_tyyp'] : "application/saurus";
	
		$sql = $site->db->prepare("SELECT * FROM obj_pilt WHERE objekt_id = ?", $objekt->objekt_id);
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$result = $sth->fetch();
	
		header("Content-Disposition: attachment; filename=\"".$result['fail']."\"");
		header("Content-Type: $ctype");
		header("Cache-control: private");
	  header("Pragma: public");
	
		
		$sql = $site->db->prepare("SELECT content FROM document_parts WHERE objekt_id = ? ORDER BY id ASC", $objekt->objekt_id);
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());
		while ( $sisu = $sth->fetch()) {
			$output .= $sisu['content'];
		}
	
		header("Content-Length: ".strlen($output));
		echo $output;
	
	
	} else {
		header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF[hostname].$site->CONF[wwwroot].($site->in_editor?"/editor":"")."/?404");
	}
	
}
else 
{
	header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].($site->in_editor?"/editor":"")."/?404");
}
