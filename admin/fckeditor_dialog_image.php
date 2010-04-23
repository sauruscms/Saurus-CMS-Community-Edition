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



global $class_path;

$class_path = '../classes/';

include($class_path.'port.inc.php');
include($class_path.'adminpage.inc.php');

$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1 : 0),
	'on_admin_keel' => 1,
));

include_once($class_path.'picture.inc.php');

$object = new Objekt(array(
	'objekt_id' => (int)$site->fdat['file_id'],
	'on_sisu' => 1,
));

if($object->all['relative_path'])
{
	$object->all['fullpath'] = preg_replace('#/$#', '', $site->absolute_path).$object->all['relative_path'];
}

if($object->objekt_id && $object->all['fullpath'])
{
	list($width, $height, $type, $attr) = getimagesize($object->all['fullpath']);
	
	$default_image = array(
		'width' => $width,
		'height' => $height,
		'filepath' => (empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/'.str_replace($site->absolute_path, '', $object->all['fullpath']),
		'name' => $site->sys_sona(array('sona' => 'original picture', 'tyyp' => 'Editor')).' ('.$width.'x'.$height.')',
	);
	
	$definitions = array();
	$sql = 'select definition_id, value, name from config_images order by value desc';
	$result = new SQL($sql);
	while($row = $result->fetch('ASSOC'))
	{
		$definitions[$row['definition_id']]['width'] = $row['value'];
		$definitions[$row['definition_id']]['name'] = $row['name'];
		
		if(!$definitions[$row['definition_id']]['width'] || $definitions[$row['definition_id']]['width'] > $width) unset($definitions[$row['definition_id']]);
		else 
		{
			$definitions[$row['definition_id']]['height'] = round($height / ($width / $definitions[$row['definition_id']]['width']));
			$definitions[$row['definition_id']]['name'] = $site->sys_sona(array('sona' => 'image_definitions_'.$definitions[$row['definition_id']]['name'], 'tyyp' => 'Custom')).' ('.$definitions[$row['definition_id']]['width'].'x'.$definitions[$row['definition_id']]['height'].')';
			$pathinfo = pathinfo($object->all['fullpath']);
			$image_path = str_replace($site->absolute_path, '', $object->all['fullpath']);
			
			// create images
			$image = new ImageShopper($image_path);
			$image->file_name_body_add = '_'.$definitions[$row['definition_id']]['width'].'x'.$definitions[$row['definition_id']]['height'];
			$image->file_auto_rename = false;
			
			$image->image_resize = true;
			$image->image_x = $definitions[$row['definition_id']]['width'];
			$image->image_y = $definitions[$row['definition_id']]['height'];
			$image->process($pathinfo['dirname'].'/.thumbnails');
			if(file_exists($image->file_dst_pathname))
			{
				$definitions[$row['definition_id']]['filepath'] = (empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/'.str_replace($site->absolute_path, '', $image->file_dst_pathname);
			}
			else 
			{
				unset($definitions[$row['definition_id']]);
				//printr($image->log);
			}
		}
	}
	
	$definitions[] = $original_image = $default_image;
	
	if(count($definitions) > 1)
	{
		reset($definitions);
		$default_image = current($definitions);
	}
	
	include_once('../js/fckeditor/editor/'.$site->fdat['dialog']);
	exit;
}
else 
{
	// no perms
	// exit;
}