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



header('Content-type: text/javascript');

$class_path = '../classes/';

// for multi-upload session, the flash does not send cookie values
if(isset($_POST['PHPSESSID']))
{
	session_id($_POST['PHPSESSID']);
	session_start();
}

include($class_path.'port.inc.php');

$site = new Site(array());

// so there would be no parse errors in json
ini_set('display_errors', 0);

// generate alias
if($site->user->user_id && $_REQUEST['op'] == 'generate_alias' && isset($_REQUEST['string']) && isset($_REQUEST['language_id']))
{
	include_once($class_path.'adminpage.inc.php');
	
	$alias = create_alias_for_object($_REQUEST['string'], $_REQUEST['language_id']);
	
	echo '{"alias": "'.$alias.'"}';
	
	exit;
}

// check if a file exists
if($site->user->user_id && $_REQUEST['op'] == 'check_file' && $site->fdat['name'])
{
	include_once($class_path.'adminpage.inc.php');
	
	$pathinfo = str_replace(array('../', './', '..\\', '.\\'), '', $site->fdat['name']);
	$pathinfo = explode('/', $pathinfo);
	$filename = create_alias_from_string($pathinfo[count($pathinfo) - 1],true);
	unset($pathinfo[count($pathinfo) - 1]);
	$dirname = implode('/', $pathinfo);
	
	if(file_exists($site->absolute_path.$dirname.'/'.$filename))
	{
		echo '{"file_exists": 1}';
	}
	else 
	{
		echo '{"file_exists": 0}';
	}
	
	exit;
}

// get subfolders
if($site->user->user_id && $site->fdat['op'] == 'get_folders' && (int)$site->fdat['parent_id'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$parent_id = (int)$site->fdat['parent_id'];
	
	$folders = get_subfolders($parent_id);
	// dont send the parent itself
	reset($folders);
	unset($folders[key($folders)]);
	
	$response = array(
		'error' => 0,
		'folders' => $folders,
	);
	
	echo $json_encoder->encode($response);
	
	exit;
}

// create subfolder
if($site->user->user_id && $site->fdat['op'] == 'create_folder' && (int)$site->fdat['parent_id'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$parent_id = (int)$site->fdat['parent_id'];
	
	$name = safe_filename2($site->fdat['name']);
	
	$folder_id = create_folder($name, $parent_id);
	
	if(is_int($folder_id) && $folder_id)
	{
		$folders = get_subfolders($parent_id);
		// dont send the parent itself
		reset($folders);
		unset($folders[key($folders)]);
		
		$response = array(
			'error' => 0,
			'folder_id' => $folder_id,
			'folders' => $folders,
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $folder_id,
		);
	}
	
	echo $json_encoder->encode($response);
	
	exit;
}

// delete folder
if($site->user->user_id && $site->fdat['op'] == 'delete_folder' && (int)$site->fdat['folder_id'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$folder_id = (int)$site->fdat['folder_id'];
	
	$delete_message = delete_folder($folder_id);
	
	if($delete_message === true)
	{
		$response = array(
			'error' => 0,
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $delete_message,
		);
	}
	
	echo $json_encoder->encode($response);
	
	exit;
}

// edit folder
if($site->user->user_id && $site->fdat['op'] == 'edit_folder' && (int)$site->fdat['folder_id'] && $site->fdat['name'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$folder_id = (int)$site->fdat['folder_id'];
	
	$name = safe_filename2($site->fdat['name']);
	
	$rename_message = rename_folder($name, $folder_id);
	
	if($rename_message === true)
	{
		$objekt = new Objekt(array('objekt_id' => $folder_id));
		
		$folders = get_subfolders($objekt->parent_id);
		// dont send the parent itself
		reset($folders);
		unset($folders[key($folders)]);
		
		$response = array(
			'error' => 0,
			'folder_id' => $folder_id,
			'folders' => $folders,
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $rename_message,
		);
	}
	
	echo $json_encoder->encode($response);
	
	exit;
}

// get folder files
if($site->user->user_id && $site->fdat['op'] == 'get_folder_files' && (int)$site->fdat['folder_id'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$folder_id = (int)$site->fdat['folder_id'];
	
	switch ($site->fdat['sort_by'])
	{
		case 'filename': $sort_by = 'objekt.pealkiri'; break;
		case 'date': $sort_by = 'objekt.aeg'; break;
		case 'size': $sort_by = 'obj_file.size'; break;
		case 'folder': $sort_by = 'obj_file.relative_path'; break;
		default: $sort_by = 'objekt.pealkiri'; break;
	}
	
	switch ($site->fdat['sort_dir'])
	{
		case 'asc': $sort_by .= ' asc'; break;
		case 'desc': $sort_by .= ' desc'; break;
		default: $sort_by .= ' asc'; break;
	}
	
	$page = ((int)$site->fdat['page'] ? (int)$site->fdat['page'] : 1);
	
	$files = get_files_from_folder($folder_id, $sort_by, $page);
	
	if(is_array($files))
	{
		$files = array(
			'total_files' => $files['total_files'],
			'files' => array(
				$page => $files['files'],
			),
		);
		
		$response = array(
			'error' => 0,
			'folder_id' => $folder_id,
			'files' => $files,
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $files,
		);
	}
	
	echo $json_encoder->encode($response);
	
	exit;
}
// search files
if($site->user->user_id && $site->fdat['op'] == 'search_files' && $site->fdat['keyword'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	switch ($site->fdat['sort_by'])
	{
		case 'filename': $sort_by = 'objekt.pealkiri'; break;
		case 'date': $sort_by = 'objekt.aeg'; break;
		case 'size': $sort_by = 'obj_file.size'; break;
		case 'folder': $sort_by = 'obj_file.relative_path'; break;
		default: $sort_by = 'objekt.pealkiri'; break;
	}
	
	switch ($site->fdat['sort_dir'])
	{
		case 'asc': $sort_by .= ' asc'; break;
		case 'desc': $sort_by .= ' desc'; break;
		default: $sort_by .= ' asc'; break;
	}
	
	$page = ((int)$site->fdat['page'] ? (int)$site->fdat['page'] : 1);
	
	$files = get_files_by_search($site->fdat['keyword'], $sort_by, $page);
	
	if(is_array($files))
	{
		$files = array(
			'total_files' => $files['total_files'],
			'files' => array(
				$page => $files['files'],
			),
		);
		
		$response = array(
			'error' => 0,
			'folder_id' => 1,
			'files' => (count($files) ? $files : 0),
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $files,
		);
	}
	
	echo $json_encoder->encode($response);
	
	exit;
}

// delete files
if($site->user->user_id && $site->fdat['op'] == 'delete_files' && $site->fdat['files'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$files = explode(',', $site->fdat['files']);
	
	$response = delete_files($files);
	
	echo $json_encoder->encode($response);
	
	exit;
}

// move files
if($site->user->user_id && $site->fdat['op'] == 'move_files' && (int)$site->fdat['from_folder_id'] && (int)$site->fdat['to_folder_id'] && $site->fdat['files'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$files = explode(',', $site->fdat['files']);
	
	$response = move_files_to_folder((int)$site->fdat['from_folder_id'], (int)$site->fdat['to_folder_id'], $files);
	
	echo $json_encoder->encode($response);
	
	exit;
}

// synchronise folder
if($site->user->user_id && $site->fdat['op'] == 'synchronise_folder' && (int)$site->fdat['folder_id'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$folder_id = (int)$site->fdat['folder_id'];
	
	switch ($site->fdat['sort_by'])
	{
		case 'filename': $sort_by = 'objekt.pealkiri'; break;
		case 'date': $sort_by = 'objekt.aeg'; break;
		case 'size': $sort_by = 'obj_file.size'; break;
		case 'folder': $sort_by = 'obj_file.relative_path'; break;
		default: $sort_by = 'objekt.pealkiri'; break;
	}
	
	switch ($site->fdat['sort_dir'])
	{
		case 'asc': $sort_by .= ' asc'; break;
		case 'desc': $sort_by .= ' desc'; break;
		default: $sort_by .= ' asc'; break;
	}
	
	$synchro = synchronise_folder($folder_id);
	
	if($synchro === true)
	{
		$files = get_files_from_folder($folder_id, $sort_by);
		
		$files = array(
			'total_files' => $files['total_files'],
			'files' => array(
				1 => $files['files'],
			),
		);
		
		$folders = get_subfolders($folder_id);
		// dont send the parent itself
		reset($folders);
		unset($folders[key($folders)]);
		
		$response = array(
			'error' => 0,
			'folder_id' => $folder_id,
			'files' => $files,
			'folders' => $folders,
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $synchro,
		);
	}
	
	echo $json_encoder->encode($response);
	
	exit;
}

// upload file
if($site->user->user_id && $site->fdat['op'] == 'file_upload' && (int)$site->fdat['folder_id'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	//include_once($class_path.'lgpl/Services_JSON.class.php');
	
	//$json_encoder = new Services_JSON();
	
	$folder_id = (int)$site->fdat['folder_id'];
	
	//echo $json_encoder->encode($response);
	
	$file_id = upload_to_folder($_FILES['Filedata'], $folder_id);
	
	if(is_int($file_id))
	{
		echo "{file_id: '".$file_id."'}";
	}
	else 
	{
		echo "{error: '".$file_id."'}";
	}
	
	exit;
}

// toggle favorite
if($site->user->user_id && $site->fdat['op'] == 'toggle_favorite' && (int)$site->fdat['objekt_id'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$objekt_id = (int)$site->fdat['objekt_id'];
	
	$site->user->toggle_favorite(array(
		'objekt_id' => $objekt_id,
	));
	
	$favorites = get_filemanager_favorites();
	
	//printr($favorites);
	
	$response = array(
		'error' => 0,
		'favorites' => $favorites,
	);
	
	echo $json_encoder->encode($response);
	
	exit;
}

// album images upload
if($site->user->user_id && $site->fdat['op'] == 'add_image_to_album' && $site->fdat['folder_path'])
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'custom.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$response = add_image_to_album($_FILES['Filedata'], $site->fdat['folder_path']);
	
	$response = array(
		'error' => 0,
		'error_message' => $response,
	);
	
	echo $json_encoder->encode($response);
	
	exit;
}

// subsite (keel) setting edit
if($site->user->user_id && $site->fdat['op'] == 'edit_site_settings' && $site->user->allowed_adminpage(array('script_name' => 'sys_sonad_loetelu.php')))
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	
	$settings = array(
		'keel_id' => $site->fdat['site_id'],
		'nimi' => $site->fdat['name'],
		'encoding'=> $site->fdat['encoding'],
		'glossary_id'=> $site->fdat['glossary_id'],
		'extension'=> $site->fdat['extension'],
		'on_default'=> $site->fdat['is_default'],
		'site_url'=> $site->fdat['site_url'],
		'page_ttyyp_id'=> $site->fdat['page_template_id'],
		'ttyyp_id'=> $site->fdat['content_template_id'],
	);
	
	if(save_sub_site_settings($settings))
	{
		echo '{"error": 0}';
	}
	else 
	{
		echo '{"error": 1}';
	}
	
	exit;
}

// subsite (keel) object count
if($site->user->user_id && $site->fdat['op'] == 'get_site_objects_count' && is_numeric($site->fdat['site_id']) && $site->user->allowed_adminpage(array('script_name' => 'sys_sonad_loetelu.php')))
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	
	echo '{"count": '.get_sub_site_objects_count($site->fdat['site_id']).'}';
	
	exit;
}

// subsite (keel) delete
if($site->user->user_id && $site->fdat['op'] == 'delete_site' && is_numeric($site->fdat['site_id']) && $site->user->allowed_adminpage(array('script_name' => 'sys_sonad_loetelu.php')))
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	if($site->user->is_superuser)
	{
		$sql = $site->db->prepare('select on_default from keel where keel_id = ?', $site->fdat['site_id']);
		$result = new SQL($sql);
		if($result->rows)
		{
			if($result->fetchsingle() == '1')
			{
				$response = array(
					'error' => 1,
					'error_message' => 'No permissions to delete.',
				);
			}
			else 
			{
				$response = delete_sub_site($site->fdat['site_id']);
				
				$response = array(
					'error' => 0,
					'count' => $response,
				);
			}
		}
		else 
		{
			$response = array(
				'error' => 1,
				'error_message' => 'No such site.',
			);
		}
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => 'No permissions to delete.',
		);
	}
	
	echo $json_encoder->encode($response);
	
	exit;
}

// subsite (keel) create
if($site->user->user_id && $site->fdat['op'] == 'create_new_site' && $site->user->allowed_adminpage(array('script_name' => 'sys_sonad_loetelu.php')))
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$site_data = array(
		'name' => $site->fdat['name'],
		'extension' => $site->fdat['extension'],
		'site_url' => $site->fdat['site_url'],
		'encoding' => $site->fdat['encoding'],
		'glossary_id' => $site->fdat['glossary_id'],
		'page_template_id' => $site->fdat['page_template_id'],
		'content_template_id' => $site->fdat['content_template_id'],
	);
	
	array_walk($site_data, 'trim');
	
	$response = create_sub_site($site_data);
	
	if(is_numeric($response))
	{
		$site_data['site_id'] = $response;
		
		$response = array(
			'error' => 0,
			'site_data' => $site_data,
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $response,
		);
	}
	
	echo $json_encoder->encode($response);
	
	exit;
}

// glossary create
if($site->user->user_id && $site->fdat['op'] == 'create_glossary' && $site->user->allowed_adminpage(array('script_name' => 'sys_sonad_loetelu.php')))
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$glossary = array(
		'keel_id' => $site->fdat['keel_id'],
		'encoding' => $site->fdat['encoding'],
		'locale' => $site->fdat['locale'],
	);
	
	array_walk($glossary, 'trim');
	
	$response = create_glossary($glossary);
	
	if($response === true)
	{
		$response = array(
			'error' => 0,
			'glossary' => $glossary,
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $response,
		);
	}
	
	echo $json_encoder->encode($response);
	
	exit;
}

// glossary usage
if($site->user->user_id && $site->fdat['op'] == 'get_glossary_usage' && is_numeric($site->fdat['glossary_id']) && $site->user->allowed_adminpage(array('script_name' => 'sys_sonad_loetelu.php')))
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$sites = array();
	
	$sql = $site->db->prepare('select nimi from keel where on_kasutusel = 1 and glossary_id = ?', $site->fdat['glossary_id']);
	$result = new SQL($sql);
	while($site = $result->fetchsingle())
	{
		$sites[] = $site;
	}
	
	$response = array(
		'error' => 0,
		'sites' => $sites,
	);
	
	echo $json_encoder->encode($response);
	
	exit;
}

// glossary remove
if($site->user->user_id && $site->fdat['op'] == 'remove_glossary' && is_numeric($site->fdat['glossary_id']) && $site->user->allowed_adminpage(array('script_name' => 'sys_sonad_loetelu.php')))
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$response = remove_glossary($site->fdat['glossary_id']);
	
	if($response === true)
	{
		$response = array(
			'error' => 0,
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $response,
		);
	}
	
	
	echo $json_encoder->encode($response);
	
	exit;
}

// glossary edit
if($site->user->user_id && $site->fdat['op'] == 'edit_glossary_settings' && is_numeric($site->fdat['glossary_id']) && $site->user->allowed_adminpage(array('script_name' => 'sys_sonad_loetelu.php')))
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$glossary_data = array(
		'glossary_id' => $site->fdat['glossary_id'],
		'encoding' => $site->fdat['encoding'],
		'locale' => $site->fdat['locale'],
		'on_default_admin' => $site->fdat['on_default_admin'],
	);
	
	$response = edit_glossary($glossary_data);
	
	if($response === true)
	{
		$response = array(
			'error' => 0,
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $response,
		);
	}
	
	
	echo $json_encoder->encode($response);
	
	exit;
}

// system word delete
if($site->user->user_id && $site->fdat['op'] == 'delete_sys_word' && is_numeric($site->fdat['word_id']) && $site->user->allowed_adminpage(array('script_name' => 'sys_sonad_loetelu.php')))
{
	verify_form_token();
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'lgpl/Services_JSON.class.php');
	
	$json_encoder = new Services_JSON();
	
	$response = delete_system_word($site->fdat['word_id']);
	
	if($response === true)
	{
		$response = array(
			'error' => 0,
		);
	}
	else 
	{
		$response = array(
			'error' => 1,
			'error_message' => $response,
		);
	}
	
	echo $json_encoder->encode($response);
	
	exit;
}

echo '{"error": 404}';

exit;
