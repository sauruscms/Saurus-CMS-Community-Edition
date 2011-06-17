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

$class_path = 'classes/';

include_once($class_path.'port.inc.php');

$site = new Site(array());

# set script execution time to 10 min only  if general value is smaller
if ( intval(ini_get('max_execution_time')) < 1200 )
{
	set_time_limit ( 1200 ) ;
}
# memory limit = 24,
if ( intval(ini_get('memory_limit')) < 24 )
{
	ini_set ( 'memory_limit', '24M' );
}

/*---------------------------	Code Begin	------------------------------------------*/

// sync extension for new error template

include_once($class_path.'extension.class.php');

sync_extensions();

// op'ed PHP templates switch from v4 SAPI templates

$sql = "select ttyyp_id, nimi, templ_fail, op from templ_tyyp where ttyyp_id < 100 and op <> ''";
$result = new SQL($sql);
while($row = $result->fetch('ASSOC'))
{
	switch($row['op'])
	{
		// sitemap
		case 'kaart':
		case 'sitemap':
		
			// update only if the op has not been set
			$sql = "select op from templ_tyyp where templ_fail = '../../../extensions/saurus4/content_templates/sitemap.html'";
			$inner_result = new SQL($sql);
			if(!$inner_result->fetchsingle())
			{
				$sql = "update templ_tyyp set op = '".$row['op']."' where templ_fail = '../../../extensions/saurus4/content_templates/sitemap.html'";
				new SQL($sql);
			}
			
		break;
		
		// advanced seacrh
		case 'tappisotsing':
		case 'advsearch':
		
			// update only if the op has not been set
			$sql = "select op from templ_tyyp where templ_fail = '../../../extensions/saurus4/content_templates/advanced_search.html'";
			$inner_result = new SQL($sql);
			if(!$inner_result->fetchsingle())
			{
				$sql = "update templ_tyyp set op = '".$row['op']."' where templ_fail = '../../../extensions/saurus4/content_templates/advanced_search.html'";
				new SQL($sql);
			}
			
		break;
		
		// search
		case 'otsi':
		case 'search':
		
			// update only if the op has not been set
			$sql = "select op from templ_tyyp where templ_fail = '../../../extensions/saurus4/content_templates/search_results.html'";
			$inner_result = new SQL($sql);
			if(!$inner_result->fetchsingle())
			{
				$sql = "update templ_tyyp set op = '".$row['op']."' where templ_fail = '../../../extensions/saurus4/content_templates/search_results.html'";
				new SQL($sql);
			}
			
		break;
		
		// archive
		case 'arhiiv':
		case 'archive':
		
			// update only if the op has not been set
			$sql = "select op from templ_tyyp where templ_fail = '../../../extensions/saurus4/content_templates/news_archive.html'";
			$inner_result = new SQL($sql);
			if(!$inner_result->fetchsingle())
			{
				$sql = "update templ_tyyp set op = '".$row['op']."' where templ_fail = '../../../extensions/saurus4/content_templates/news_archive.html'";
				new SQL($sql);
			}
			
		break;
		
		// register
		case 'register':
		
			// update only if the op has not been set
			$sql = "select op from templ_tyyp where templ_fail = '../../../extensions/saurus4/content_templates/register.html'";
			$inner_result = new SQL($sql);
			if(!$inner_result->fetchsingle())
			{
				$sql = "update templ_tyyp set op = '".$row['op']."' where templ_fail = '../../../extensions/saurus4/content_templates/register.html'";
				new SQL($sql);
			}
			
		break;
		
		// error
		case 'error':
		
			// update only if the op has not been set
			$sql = "select op from templ_tyyp where templ_fail = 'templ_error.php'";
			$inner_result = new SQL($sql);
			if($inner_result->fetchsingle())
			{
				$sql = "update templ_tyyp set op = '".$row['op']."' where templ_fail = '../../../extensions/saurus4/object_templates/error_article.html'";
				new SQL($sql);
			}
			
		break;
		
		default: break;
	}
}

include_once($class_path.'adminpage.inc.php');
include_once($class_path.'picture.inc.php');

// export galleries from database to public/galleries

$sql = 'select objekt_id, pealkiri, friendly_url, ttyyp_params from objekt where tyyp_id = 16';
$result = new SQL($sql);
while($album = $result->fetch('ASSOC'))
{
	$conf = new CONFIG($album['ttyyp_params']);
	
	if(!$conf->get('path'))
	{
		// v3 db based gallery, export it
		$album_folder_path = $clean_path = ($album['friendly_url'] ? $album['friendly_url'] : create_alias_from_string($album['pealkiri']));
		
		$supplement = 2;
		
		// unlikely to happen
		if($album_folder_path === '') $album_folder_path = $clean_path = rand(10000, 20000);
	
		while(file_exists($site->absolute_path.'public/galleries/'.$album_folder_path))
		{
			$album_folder_path = create_alias_from_string($clean_path.'-'.$supplement);
			$supplement++;
			
			// guard, also unlikely
			if($supplement > 1000)
			{
				exit;
			}
		}
		
		$album_folder_path = 'public/galleries/'.$album_folder_path;
		
		// create folder
		
		$folder_id = create_folder_from_path($album_folder_path);
		
		if(is_int($folder_id))
		{
			$sql = $site->db->prepare("select obj_pilt.objekt_id, obj_pilt.fail, obj_pilt.sisu_blob, obj_pilt.size from obj_pilt left join objekt_objekt on obj_pilt.objekt_id = objekt_objekt.objekt_id where parent_id = ?", $album['objekt_id']);
			$images_result = new SQL($sql);
			while($file = $images_result->fetch('ASSOC'))
			{
				$parts_sql = $site->db->prepare('SELECT content FROM document_parts WHERE objekt_id = ? ORDER BY id ASC', $file['objekt_id']);
				$parts_result = new SQL ($sql);
				while ($part = $parts_result->fetch())
				{
					$file['sisu_blob'] .= $part['content'];
				}
				
				$fpc_result = file_put_contents($site->absolute_path.$album_folder_path.'/'.$file['fail'], $file['sisu_blob']);
				if($fpc_result === false)
				{
					echo '<font color=red>Error: could not create file: '.$album_folder_path.'/'.$file['fail'].', check filesystem permissions.</font>';
					exit;
				}
				
				$sql = $site->db->prepare("delete from obj_pilt where objekt_id = ?", $file['objekt_id']);
				new SQL($sql);
				
				$sql = $site->db->prepare("delete from document_parts where objekt_id = ?", $file['objekt_id']);
				new SQL($sql);
				
				$sql = $site->db->prepare("update objekt_objekt set parent_id = ? where objekt_id = ?", $folder_id, $file['objekt_id']);
				new SQL($sql);
				
				$sql = $site->db->prepare("update objekt set tyyp_id = 21 where objekt_id = ?", $file['objekt_id']);
				new SQL($sql);
				
				$sql = $site->db->prepare("insert into obj_file (objekt_id, relative_path, filename, size) values (?, ?, ?, ?)", $file['objekt_id'], '/'.$album_folder_path.'/'.$file['fail'], $file['fail'], $file['size']);
				new SQL($sql);
			}
			
			if($images_result->rows)
			{
				// generate album images
				generate_images($site->absolute_path.$album_folder_path, $conf->get('tn_size') ? $conf->get('tn_size') : $site->CONF['thumb_width'], $conf->get('pic_size') ? $conf->get('pic_size') : $site->CONF['image_width']);
			}
			
			$conf->put('folder_id', $folder_id);
			$conf->put('path', $album_folder_path);
			$sql = $site->db->prepare("UPDATE objekt SET ttyyp_params = ? WHERE objekt_id=?", $conf->Export(), $album['objekt_id']);
			new SQL($sql);
		}
		else 
		{
			echo '<font color=red>Error: could not create folder: '.$album_folder_path.', check filesystem permissions.</font>';
			exit;
		}
	}
}

// export documents from database to public/documents
$sql = 'select objekt_id, pealkiri, friendly_url, ttyyp_params from objekt where ttyyp_id = 11';
$result = new SQL($sql);
while($document = $result->fetch('ASSOC'))
{
	$conf = new CONFIG($document['ttyyp_params']);
	
	if(!$conf->get('path'))
	{
		// v3 db based gallery, export it
		$document_folder_path = $clean_path = ($document['friendly_url'] ? $document['friendly_url'] : create_alias_from_string($document['pealkiri']));
		
		$supplement = 2;
		
		// unlikely to happen
		if($document_folder_path === '') $document_folder_path = $clean_path = rand(10000, 20000);
	
		while(file_exists($site->absolute_path.'public/documents/'.$document_folder_path))
		{
			$document_folder_path = create_alias_from_string($clean_path.'-'.$supplement);
			$supplement++;
			
			// guard, also unlikely
			if($supplement > 1000)
			{
				exit;
			}
		}
		
		$document_folder_path = 'public/documents/'.$document_folder_path;
		
		// create folder
		
		$folder_id = create_folder_from_path($document_folder_path);
		
		if(is_int($folder_id))
		{
			$sql = $site->db->prepare("select obj_dokument.objekt_id, obj_dokument.fail, obj_dokument.sisu_blob, obj_dokument.size from obj_dokument left join objekt_objekt on obj_dokument.objekt_id = objekt_objekt.objekt_id where parent_id = ?", $document['objekt_id']);
			$documents_result = new SQL($sql);
			while($file = $documents_result->fetch('ASSOC'))
			{
				$parts_sql = $site->db->prepare('SELECT content FROM document_parts WHERE objekt_id = ? ORDER BY id ASC', $file['objekt_id']);
				$parts_result = new SQL ($sql);
				while ($part = $parts_result->fetch())
				{
					$file['sisu_blob'] .= $part['content'];
				}
				
				$fpc_result = file_put_contents($site->absolute_path.'/'.$document_folder_path.'/'.$file['fail'], $file['sisu_blob']);
				if($fpc_result === false)
				{
					echo '<font color=red>Error: could not create file: '.$document_folder_path.'/'.$file['fail'].', check filesystem permissions.</font>';
					exit;
				}
				
				
				$sql = $site->db->prepare("delete from obj_dokument where objekt_id = ?", $file['objekt_id']);
				new SQL($sql);
				
				$sql = $site->db->prepare("delete from document_parts where objekt_id = ?", $file['objekt_id']);
				new SQL($sql);
				
				$sql = $site->db->prepare("update objekt_objekt set parent_id = ? where objekt_id = ?", $folder_id, $file['objekt_id']);
				new SQL($sql);
				
				$sql = $site->db->prepare("update objekt set tyyp_id = 21 where objekt_id = ?", $file['objekt_id']);
				new SQL($sql);
				
				$sql = $site->db->prepare("insert into obj_file (objekt_id, relative_path, filename, size) values (?, ?, ?, ?)", $file['objekt_id'], '/'.$document_folder_path.'/'.$file['fail'], $file['fail'], $file['size']);
				new SQL($sql);
			}
			
			$conf->put('folder', $document_folder_path);
			$sql = $site->db->prepare("UPDATE objekt SET ttyyp_params = ? WHERE objekt_id=?", $conf->Export(), $document['objekt_id']);
			new SQL($sql);
		}
		else 
		{
			echo '<font color=red>Error: could not create folder: '.$document_folder_path.', check filesystem permissions.</font>';
			exit;
		}
	}
}
// convert existing event objects to articles
// create article profile for events

$sql = "insert into object_profiles (name, data, source_table) values ('converted_event', '".'a:6:{s:6:"author";a:9:{s:4:"name";s:6:"author";s:4:"type";s:4:"TEXT";s:13:"source_object";s:0:"";s:13:"default_value";s:0:"";s:7:"db_type";s:7:"varchar";s:11:"is_required";i:0;s:9:"is_active";i:1;s:13:"is_predefined";i:1;s:10:"is_general";i:1;}s:3:"aeg";a:9:{s:4:"name";s:3:"aeg";s:4:"type";s:8:"DATETIME";s:13:"source_object";s:0:"";s:13:"default_value";s:0:"";s:7:"db_type";s:8:"datetime";s:11:"is_required";i:0;s:9:"is_active";i:1;s:13:"is_predefined";i:1;s:10:"is_general";i:1;}s:18:"avaldamisaeg_algus";a:9:{s:4:"name";s:18:"avaldamisaeg_algus";s:4:"type";s:8:"DATETIME";s:13:"source_object";s:0:"";s:13:"default_value";s:0:"";s:7:"db_type";s:8:"datetime";s:11:"is_required";i:0;s:9:"is_active";i:1;s:13:"is_predefined";i:1;s:10:"is_general";i:1;}s:17:"avaldamisaeg_lopp";a:9:{s:4:"name";s:17:"avaldamisaeg_lopp";s:4:"type";s:8:"DATETIME";s:13:"source_object";s:0:"";s:13:"default_value";s:0:"";s:7:"db_type";s:8:"datetime";s:11:"is_required";i:0;s:9:"is_active";i:1;s:13:"is_predefined";i:1;s:10:"is_general";i:1;}s:9:"starttime";a:9:{s:4:"name";s:9:"starttime";s:4:"type";s:8:"DATETIME";s:13:"source_object";s:0:"";s:13:"default_value";s:0:"";s:7:"db_type";s:8:"datetime";s:11:"is_required";i:0;s:9:"is_active";i:1;s:13:"is_predefined";i:0;s:10:"is_general";i:0;}s:7:"endtime";a:9:{s:4:"name";s:7:"endtime";s:4:"type";s:8:"DATETIME";s:13:"source_object";s:0:"";s:13:"default_value";s:0:"";s:7:"db_type";s:8:"datetime";s:11:"is_required";i:0;s:9:"is_active";i:1;s:13:"is_predefined";i:0;s:10:"is_general";i:0;}}'."', 'obj_artikkel')";
$result = new SQL($sql);
$events_profile_id = $result->insert_id;

// create data columns (existance check?)
$sql = "alter table `obj_artikkel` add column `starttime` datetime NULL after `profile_id`, add column `endtime` datetime NULL after `starttime`;"; // starttime and endtime already match fields used in init_calendar
$result = new SQL($sql);

// get the events
$sql = 'select objekt.objekt_id, objekt.pealkiri, obj_event.start_time, obj_event.end_time, obj_event.description from obj_event left join objekt on objekt.objekt_id = obj_event.objekt_id where objekt.tyyp_id = 18';
$result = new SQL($sql);

while($row = $result->fetch('ASSOC'))
{
	$sql = $site->db->prepare("delete from obj_event where objekt_id = ?", $row['objekt_id']);
	new SQL($sql);
	
	$sql = $site->db->prepare("update objekt set tyyp_id = 2 where objekt_id = ?", $row['objekt_id']);
	new SQL($sql);
	
	$sql = $site->db->prepare("insert into obj_artikkel (objekt_id, starttime, endtime, profile_id, sisu) values (?, ?, ?, ?, ?)",
		$row['objekt_id'],
		$row['start_time'],
		$row['end_time'],
		$events_profile_id,
		$row['description']
	);
	new SQL($sql);
}

// remove events from db
$sql = 'DELETE FROM objekt_objekt WHERE objekt_id IN (SELECT objekt_id FROM objekt WHERE tyyp_id = 18)';
new SQL($sql);

$sql = 'DELETE FROM objekt_objekt WHERE objekt_id in (select objekt_id from obj_event)';
new SQL($sql);

$sql = 'DELETE FROM objekt WHERE objekt_id IN (SELECT objekt_id FROM obj_event)';
new SQL($sql);

$sql = 'DELETE FROM objekt WHERE tyyp_id = 18';
new SQL($sql);

$sql = 'DROP TABLE `obj_event`';
new SQL($sql);

// remove templates from database
$sql = 'delete from templ_tyyp where ttyyp_id < 100';
new SQL($sql);

/*---------------------------	Code End	------------------------------------------*/
