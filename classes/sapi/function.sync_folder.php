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


#################################
# function sync_folder
#	id => id of the folder to sync
#	path => path to folder to sync
#
function smarty_function_sync_folder ($params, &$smarty) {
	global $site, $leht, $class_path;

	##############
	# default values
	extract($params);
	//if(!isset($name)) { $name = 'folder'; }
    
	//eelista id'd
	if(isset($id))
	{
		$id = (int)$id;
		
		$objekt = new Objekt(array(
			'objekt_id' => $id,
		));
		
		//printr($objekt->all);
		//kui on album
		if($objekt->all['tyyp_id'] == 16)
		{
			$conf = new CONFIG($objekt->all['ttyyp_params']);
			if($folder_path = $conf->get('path'))
			{
				$folder_path = preg_replace('#^/#', '', $folder_path);
				$folder_path = preg_replace('#/$#', '', $folder_path);
				
				$folder_abs_path = $site->absolute_path.$folder_path;
				
				$sql = $site->db->prepare('select objekt_id from obj_folder where relative_path = ?', '/'.$folder_path);
			    $result = new SQL($sql);
			    if($result->rows)
			    {
			    	$id = $result->fetchsingle();
					include_once($class_path.'picture.inc.php');
					generate_images($folder_abs_path, $conf->get('tn_size'), $conf->get('pic_size'));
			    	
			    }
			    else
			    {
			    	//no such folder
			        return;
			    }
			}
			else 
			{
				//no image folder set
				return;
			}
		}
		//pole sobiv objekt/ei ole folder
		elseif($objekt->all['tyyp_id'] != 22)
		{
			return;
		}
	}
	elseif (isset($path))
	{
		$path = (string)$path;
		
		$path = preg_replace('#^/#', '', $path);
		$path = preg_replace('#/$#', '', $path);
		
		$sql = $site->db->prepare('select objekt_id from obj_folder where relative_path = ?', $path);
	    $result = new SQL($sql);
	    if($result->rows) $id = $result->fetchsingle();
	    else
	    {
	    	//no such folder
	        return;
	    }
	}
	
	include_once($class_path.'adminpage.inc.php');
	synchronise_folder($id);
}