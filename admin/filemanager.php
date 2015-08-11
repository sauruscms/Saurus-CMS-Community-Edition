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
 * Filemanager 
 */

global $site;

$class_path = '../classes/';
include_once($class_path.'port.inc.php');
include_once($class_path.'adminpage.inc.php');
include_once($class_path.'custom.inc.php');
include_once($class_path.'lgpl/Services_JSON.class.php');

$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1:0),
	'on_admin_keel' => 1
));


// default filemanager mode and callback settings
$settings['default'] = array(
	'select_mode' => 0, // 1 - select single file
	'action_text' => '',
	'action_trigger' => '',
	'callback' => '',
); 

// determine setup
$setup = ((string)$site->fdat['setup'] ? (string)$site->fdat['setup'] : 'default');

if(isset($_SESSION['scms_filemanager_settings'][$setup])) $settings = $_SESSION['scms_filemanager_settings'][$setup];

// do not check admin page permissions if called from outside with a callback
if(!$settings['callback'])
{
	if (!$site->user->allowed_adminpage())
	{
		exit;
	}
}

$json_encoder = new Services_JSON();

// get adminpage name
$adminpage_names = get_adminpage_name(array('script_name' => $site->script_name));

# get folder ID of "public/"
$sql = $site->db->prepare('SELECT obj_folder.objekt_id as obj_folder_objekt_id, objekt_objekt.objekt_id as objekt_objekt_objekt_id, objekt.objekt_id as objekt_objekt_id FROM obj_folder left join objekt_objekt on obj_folder.objekt_id = objekt_objekt.objekt_id left join objekt on obj_folder.objekt_id = objekt.objekt_id WHERE relative_path = ? LIMIT 1',	$site->CONF['file_path']);
$result = new SQL($sql);
$public_folder_info = $result->fetch('ASSOC');

$public_folder_id = $public_folder_info['obj_folder_objekt_id'];

if(!$public_folder_id)
{
	// create "public/" folder
	// objekt
	new SQL("delete from objekt where sys_alias = 'public'");
	
	$sql = "insert into objekt (pealkiri, tyyp_id, on_avaldatud, keel, pealkiri_strip, aeg, sys_alias, created_time) values ('public', 22, '1', 1, 'public', now(), 'public', now())";
	$result = new SQL($sql);
	$public_folder_id = $result->insert_id;
	
	// objekt_objekt
	$sql = "insert into objekt_objekt (objekt_id, parent_id, sorteering) values (".$public_folder_id.", 0, 2)";
	new SQL($sql);
	
	// obj_folder
	$sql = "insert into obj_folder (objekt_id, relative_path) values (".$public_folder_id.", '".$site->CONF['file_path']."')";
	new SQL($sql);
	
	// permissions
	$sql = "insert into permissions (type, source_id, group_id, user_id, C, R, U, P, D) VALUES ('OBJ', ".$public_folder_id.", 1, 0, '1', '1', '1', '1', '1')";
	new SQL($sql);
}

// missing objekt record
if(!$public_folder_info['objekt_objekt_id'])
{
	new SQL("delete from objekt where sys_alias = 'public' or objekt_id = ".$public_folder_id);

	$sql = "insert into objekt (objekt_id, pealkiri, tyyp_id, on_avaldatud, keel, pealkiri_strip, aeg, sys_alias, created_time) values (".$public_folder_id.", 'public', 22, '1', 1, 'public', now(), 'public', now())";
	$result = new SQL($sql);
	
	// permissions
	$sql = "insert into permissions (type, source_id, group_id, user_id, C, R, U, P, D) VALUES ('OBJ', ".$public_folder_id.", 1, 0, '1', '1', '1', '1', '1')";
	new SQL($sql);
}

// missing objekt_objekt relation
if(!$public_folder_info['objekt_objekt_objekt_id'])
{
	$sql = "insert into objekt_objekt (objekt_id, parent_id, sorteering) values (".$public_folder_id.", 0, 2)";
	new SQL($sql);
}

# get folder ID of "shared/"
$sql = $site->db->prepare('SELECT obj_folder.objekt_id as obj_folder_objekt_id, objekt_objekt.objekt_id as objekt_objekt_objekt_id, objekt.objekt_id as objekt_objekt_id FROM obj_folder left join objekt_objekt on obj_folder.objekt_id = objekt_objekt.objekt_id left join objekt on obj_folder.objekt_id = objekt.objekt_id WHERE relative_path = ? LIMIT 1', $site->CONF['secure_file_path']);
$result = new SQL($sql);

$shared_folder_info = $result->fetch('ASSOC');

//printr($shared_folder_info);

$shared_folder_id = $shared_folder_info['obj_folder_objekt_id'];

if(!$shared_folder_id)
{
	$sql = "insert into objekt (pealkiri, tyyp_id, on_avaldatud, keel, pealkiri_strip, aeg, sys_alias, created_time) values ('shared', 22, '1', 1, 'shared', now(), 'shared', now())";
	$result = new SQL($sql);
	$shared_folder_id = $result->insert_id;
	
	// objekt_objekt
	$sql = "insert into objekt_objekt (objekt_id, parent_id, sorteering) values (".$shared_folder_id.", 0, 1)";
	new SQL($sql);
	
	// obj_folder
	$sql = "insert into obj_folder (objekt_id, relative_path) values (".$shared_folder_id.", '".$site->CONF['secure_file_path']."')";
	new SQL($sql);
	
	// permissions
	$sql = "insert into permissions (type, source_id, group_id, user_id, C, R, U, P, D) VALUES ('OBJ', ".$shared_folder_id.", 1, 0, '0', '0', '0', '0', '0')";
	new SQL($sql);
}

// missing objekt record
if(!$shared_folder_info['objekt_objekt_id'])
{
	new SQL("delete from objekt where sys_alias = 'shared' or objekt_id = ".$shared_folder_id);

	$sql = "insert into objekt (objekt_id, pealkiri, tyyp_id, on_avaldatud, keel, pealkiri_strip, aeg, sys_alias, created_time) values (".$shared_folder_id.", 'shared', 22, '1', 1, 'shared', now(), 'shared', now())";
	$result = new SQL($sql);
	
	// permissions
	$sql = "insert into permissions (type, source_id, group_id, user_id, C, R, U, P, D) VALUES ('OBJ', ".$shared_folder_id.", 1, 0, '0', '0', '0', '0', '0')";
	new SQL($sql);
}

// missing objekt_objekt relation
if(!$shared_folder_info['objekt_objekt_objekt_id'])
{
	$sql = "insert into objekt_objekt (objekt_id, parent_id, sorteering) values (".$shared_folder_id.", 0, 1)";
	new SQL($sql);
}

// create the tree
$folder_tree = array();

// open folder
$open_folder_id = (int)(isset($site->fdat['folder_id']) ? $site->fdat['folder_id'] : (isset($_COOKIE['scms_filemanager_open_folder_id']) ? $_COOKIE['scms_filemanager_open_folder_id'] : 0));

// default is public
if(!$open_folder_id) $open_folder_id = $public_folder_id;

$view_mode = (isset($_COOKIE['scms_filemanager_view_mode']) ? $_COOKIE['scms_filemanager_view_mode'] : 'thumbs');

if($view_mode != 'thumbs' && $view_mode != 'list') $view_mode = 'thumbs';

$objekt = new Objekt(array('objekt_id' => $open_folder_id));
if($objekt->objekt_id != $open_folder_id || $objekt->all['tyyp_id'] != 22)
{
	$open_folder_id = $public_folder_id;
}

//always get public folder
$parent_id = $public_folder_id;

while($parent_id)
{
	$folder_tree += get_subfolders($parent_id);
	
	$parent_id = $folder_tree[$parent_id]['parent_id'];
}

// get folders starting from open folder
$parent_id = $open_folder_id;

while($parent_id)
{
	$subfolders = get_subfolders($parent_id);
	if(is_array($subfolders)) $folder_tree += $subfolders;
	
	$parent_id = $folder_tree[$parent_id]['parent_id'];
}

$shared_folders = get_subfolders($shared_folder_id);
if(is_array($shared_folders)) $folder_tree += $shared_folders;

// resort folder tree

$folders = array();
foreach($folder_tree as $objekt_id => $folder)
{
	$folders[$objekt_id] = $folder['relative_path'];
}

asort($folders);

$temp_folder_tree = $folder_tree;
$folder_tree = array();

foreach($folders as $objekt_id => $path)
{
	$folder_tree['_'.$objekt_id] = $temp_folder_tree[$objekt_id];
}

// mark open folders
//$folder_tree[$public_folder_id]['open'] = 1;

$objekt_id = $open_folder_id;

while($objekt_id)
{
	$folder_tree['_'.$objekt_id]['open'] = 1;
	$objekt_id = $folder_tree['_'.$objekt_id]['parent_id'];
}

$folder_tree[1] = array(
    'objekt_id' => 1,
    'parent_id' => 0,
    'title' => 'search',
    'relative_path' => 'search',
    'has_children' => 0,
    'level' => 1,
    'open' => 0,
);

//printr($json_encoder->encode($folder_tree));

// get files in the open folder
$files = array();

$files[$open_folder_id] = get_files_from_folder($open_folder_id);
$files[$open_folder_id] = array(
	'total_files' => $files[$open_folder_id]['total_files'],
	'files' => array(
		1 => $files[$open_folder_id]['files'],
	),
);

//$files[$open_folder_id] = get_files_by_search('IMG');

//printr($files);
//printr($json_encoder->encode($files));

// setup for folder selection
$_SESSION['folder_selection']['callback'] = 'window.opener.moveFilesHandler';
$_SESSION['folder_selection']['selectable'] = 1;
$_SESSION['folder_selection']['classes'] = array('folder', ); //this sucks, really
$_SESSION['folder_selection']['mem_classes'] = array('folder', ); //this sucks, really
$_SESSION['folder_selection']['db_fields'] = array('select_checkbox', 'objekt_id', 'pealkiri', );
$_SESSION['folder_selection']['display_fields'] = array('select_checkbox', 'pealkiri', );
$_SESSION['folder_selection']['hide_language_selection'] = 1;
// /setup for folder selection

$favorites = get_filemanager_favorites();
//printr($favorites);

$selected_file_id = 0;

if($site->fdat['file_id'])
{
	$selected_file_id = (int)$site->fdat['file_id'];
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>

<title>Filemanager</title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $site->encoding; ?>" />

<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/filemanager.css" />

<!--[if IE 6]>
	<style type="text/css">
		input.button, input.cancel, input.disabled_button {
			padding: 1px 4px 0px 4px;
		}
	</style>
<![endif]-->

<!--[if IE 7]>
	<style type="text/css">
		input.button, input.cancel, input.disabled_button {
			padding: 1px 8px 0px 8px;
			min-width: 0;
			overflow: visible;
		}
		
		input#create_folder_button, input#save_folder_button, input#cancel_save_folder_button {
			width: 85px;
		}
		
		div#scms_listing_contents {
			margin: 0px 0px 0px 6px;
		}
		
		div.context_button_container {
			margin-left: 0px;
		}
		
		div.thumbnail div.thumbnail_links {
			width: 107px;
		}
		
		div.thumbnail_links table {
			width: 107px;
		}
		
		div.thumbnail_links table table {
			width: 107px;
		}
	</style>
<![endif]-->

<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/admin_menu.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/swfupload/swfupload.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/swfupload/swfupload.queue.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/jquery.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/jquery.scrollTo.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/scms_filemanager.js"></script>
	
<script type="text/javascript">

var folder_tree = <?php echo $json_encoder->encode($folder_tree);?>;
var files = <?php echo $json_encoder->encode($files);?>;
var open_folder_id = <?php echo $open_folder_id;?>;
var site_url = '<?php echo (empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'];?>';
var folder_selection_window;
var files_to_move = [];
var view_mode = '<?php echo $view_mode; ?>';
var sorting_column = 'filename';
var sorting_direction = 'asc';
var open_folder_id_save = open_folder_id;
var swfu;
var ajax_timeout = 60000;
var favorites = <?php echo $json_encoder->encode($favorites);?>;
var selected_file = <?php echo $selected_file_id; ?>;
var file_page = 1;
var settings = <?php echo $json_encoder->encode($settings);?>;
var ajax_token = <?php echo create_form_token_json('filemanager'); ?>;
var translations = {
	search_files: '<?php echo $site->sys_sona(array('sona' => 'search_files', 'tyyp' => 'Files')); ?>',
	upload_queue_limit: '<?php echo $site->sys_sona(array('sona' => 'upload_queue_limit', 'tyyp' => 'Files')); ?>',
	upload_limit_size: '<?php echo $site->sys_sona(array('sona' => 'upload_limit_size', 'tyyp' => 'Files')); ?>',
	file: '<?php echo $site->sys_sona(array('sona' => 'file', 'tyyp' => 'Files')); ?>',
	files: '<?php echo $site->sys_sona(array('sona' => 'files', 'tyyp' => 'Files')); ?>',
	add_folder_favorite: '<?php echo $site->sys_sona(array('sona' => 'add_folder_favorite', 'tyyp' => 'Files')); ?>',
	synchronise_folder: '<?php echo $site->sys_sona(array('sona' => 'synchronise_folder', 'tyyp' => 'Files')); ?>',
	rename_folder: '<?php echo $site->sys_sona(array('sona' => 'rename_folder', 'tyyp' => 'Files')); ?>',
	folder_permissions: '<?php echo $site->sys_sona(array('sona' => 'folder_permissions', 'tyyp' => 'Files')); ?>',
	folder_delete_confirmation: '<?php echo $site->sys_sona(array('sona' => 'folder_delete_confirmation', 'tyyp' => 'Files')); ?>',
	delete_folder: '<?php echo $site->sys_sona(array('sona' => 'delete_folder', 'tyyp' => 'Files')); ?>',
	create_subfolder: '<?php echo $site->sys_sona(array('sona' => 'create_subfolder', 'tyyp' => 'Files')); ?>',
	delete_file: '<?php echo ucfirst($site->sys_sona(array('sona' => 'delete_file', 'tyyp' => 'Files'))); ?>',
	file_delete_confirmation: '<?php echo $site->sys_sona(array('sona' => 'file_delete_confirmation', 'tyyp' => 'Files')); ?>',
	files_delete_confirmation: '<?php echo $site->sys_sona(array('sona' => 'files_delete_confirmation', 'tyyp' => 'Files')); ?>',
	edit_file: '<?php echo $site->sys_sona(array('sona' => 'edit_file', 'tyyp' => 'Files')); ?>',
	move_file: '<?php echo $site->sys_sona(array('sona' => 'move_file', 'tyyp' => 'Files')); ?>',
	add_file_favorite: '<?php echo $site->sys_sona(array('sona' => 'add_file_favorite', 'tyyp' => 'Files')); ?>',
	edit_file: '<?php echo $site->sys_sona(array('sona' => 'edit_file', 'tyyp' => 'Files')); ?>',
	view_file: '<?php echo $site->sys_sona(array('sona' => 'view_file', 'tyyp' => 'Files')); ?>',
	file_date: '<?php echo $site->sys_sona(array('sona' => 'file_date', 'tyyp' => 'Files')); ?>',
	size: '<?php echo $site->sys_sona(array('sona' => 'size', 'tyyp' => 'Files')); ?>',
	folder_path: '<?php echo $site->sys_sona(array('sona' => 'folder_path', 'tyyp' => 'Files')); ?>',
	filename: '<?php echo $site->sys_sona(array('sona' => 'filename', 'tyyp' => 'Files')); ?>',
	folder_has_no_fs_permissions: '<?php echo $site->sys_sona(array('sona' => 'folder_has_no_fs_permissions', 'tyyp' => 'Files')); ?>',
	no_permissions_to_create_folder: '<?php echo $site->sys_sona(array('sona' => 'no_permissions_to_create_folder', 'tyyp' => 'Files')); ?>',
	some_files_could_not_be_deleted: '<?php echo $site->sys_sona(array('sona' => 'some_files_could_not_be_deleted', 'tyyp' => 'Files')); ?>',
	no_permissions_to_delete_some_files: '<?php echo $site->sys_sona(array('sona' => 'no_permissions_to_delete_some_files', 'tyyp' => 'Files')); ?>',
	unable_to_move_files: '<?php echo $site->sys_sona(array('sona' => 'unable_to_move_files', 'tyyp' => 'Files')); ?>'
};

$(document).ready(function()
{
	make_breadcrumb('<?=$adminpage_names['parent_pagename'];?>', '<?=$adminpage_names['pagename'];?>');
	
	<?php if($site->CONF['fm_allow_multiple_upload']) { ?>
	
	var post_params = {'<?php echo session_name(); ?>' : '<?php echo session_id(); ?>', 'op': 'file_upload'};
	$.extend(post_params, ajax_token);
	
	swfu = new SWFUpload({
		flash_url : '<?php echo $site->CONF['wwwroot'].$site->CONF['js_path']?>/swfupload/swfupload.swf',
		upload_url: '<?php echo $site->CONF['wwwroot']?>/admin/ajax_response.php',
		post_params: post_params,
		file_size_limit : '<?php echo (is_int(ini_get('upload_max_filesize')) ? round(ini_get('upload_max_filesize') / 1024) : ini_get('upload_max_filesize').'B'); ?>',
		file_types : '*.*',
		file_types_description : 'All Files',
		file_upload_limit : 0,
		file_queue_limit : 100,
		custom_settings : {
			cancelButtonId : 'cancel_file_upload_button'
		},
		debug: false,

		// Button settings
		button_image_url: '<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path'];?>/gfx/filemanager/upload_button_bg.gif',	// Relative to the Flash file
		button_width: '97',
		button_height: '25',
		button_placeholder_id: 'spanButtonPlaceHolder',
		button_text: '<?php echo $site->sys_sona(array('sona' => 'upload_files', 'tyyp' => 'Files')); ?>',
		button_text_left_padding: 10,
		button_text_top_padding: 2,
		button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT,
		
		// The event handler functions
		swfupload_loaded_handler: swfuLoaded,
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete
		//queue_complete_handler : queueComplete	// Queue plugin event
	});
	<?php } ?>
	
	if(settings.callback)
	{
		$('span#custom_action_text').html(settings.action_text);
		
		$('a#custom_action').html(settings.action_trigger);
		
		$('a#custom_action').click(customActionTrigger);
		
		settings.callbackHandler = function (data)
		{
			<?php echo $settings['callback']; ?>(data);
		}
	}
});

</script>

</head>

<body>

	<div id="scms_content_cover" class="hidden"></div>
	
	<div id="scms_dialog" class="hidden">
		<table cellpadding="0" cellspacing="0" id="scms_dialog_box">
			<tr>
				<td id="message_cell"></td>
			</tr>
			<tr>
				<td id="buttons_cell">
					<input id="message_ok_button" type="button" value="<?php echo $site->sys_sona(array('sona' => 'ok', 'tyyp' => 'Files')); ?>" class="button hidden" />
					<input id="message_cancel_button" type="button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'Files')); ?>" class="cancel hidden" />
				</td>
			</tr>
		</table>
	</div><!-- / scms_dialog -->
	
	<div id="scms_header_bar">
		
		<table cellpadding="0" cellspacing="0" id="scms_file_and_folder_tools">
			<tr>
				<td id="scms_folder_tools">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td><input type="button" id="show_create_folder_button" value="<?php echo $site->sys_sona(array('sona' => 'add_folder', 'tyyp' => 'Files')); ?>" class="button" /></td>
							<td class="hidden"><input type="text" id="save_folder_name" value="" class="text" /><input type="hidden" id="save_folder_parent_id" value="" /><input type="hidden" id="save_folder_id" value="" /></td>
							<td class="hidden"><input type="button" id="create_folder_button" value="<?php echo $site->sys_sona(array('sona' => 'create_folder', 'tyyp' => 'Files')); ?>" class="button" /></td>
							<td class="hidden"><input type="button" id="save_folder_button" value="<?php echo $site->sys_sona(array('sona' => 'save_folder', 'tyyp' => 'Files')); ?>" class="button" /></td>
							<td class="hidden"><input type="button" id="cancel_save_folder_button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'Files')); ?>" class="cancel" /></td>
						</tr>
					</table>
				</td><!-- / scms_folder_tools -->
				<td id="scms_file_upload">
					<input type="button" id="upload_new_file" value="<?php echo $site->sys_sona(array('sona' => 'upload_files', 'tyyp' => 'Files')); ?>" class="button" /><span id="spanButtonPlaceHolder"></span>
				</td><!-- / scms_file_upload -->
				<td id="scms_upload_cancel" class="hidden"><input type="button" id="cancel_file_upload_button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'Files')); ?>" class="cancel" onclick="swfu.cancelQueue();" /></td><!-- / scms_upload_cancel -->
				<td id="scms_upload_progress" class="hidden"><div id="upload_progress_bar"><div id="upload_progress_grow"></div></div></td><!-- / scms_upload_progress -->
				<td id="scms_upload_text" class="hidden"></td><!-- / scms_upload_text -->
			</tr>
		</table><!-- / scms_file_and_folder_tools -->
		
		<div id="scms_search_tools">
			<div id="search_wrapper"><div id="search_clear" class="hidden"></div><div id="search_start"></div><input type="text" id="search_text" value="<?php echo $site->sys_sona(array('sona' => 'search_files', 'tyyp' => 'Files')); ?>: " class="search_text" /></div>
		</div><!-- / scms_search_tools -->
		
	</div><!-- / scms_header_bar -->
	
	<div id="scms_fm_body_cover" class="hidden"></div>
	
	<div id="scms_left_pane_cover" class="hidden"></div>
		
	<div id="scms_fm_body">
		
		<div id="scms_left_pane">
			
			<div id="scms_favorites" class="hidden">
			
				<table cellpadding="0" cellspacing="0" id="scms_favorites_table">
				</table>
				
			</div><!-- / scms_favorites -->
			
			<div id="scms_folder_tree">
			
				<table cellpadding="0" cellspacing="0" id="scms_folder_tree_table">
					<tr></tr>
				</table>
				
			</div><!-- / scms_folder_tree -->
			
		</div><!-- / scms_left_pane -->
		
		<div id="scms_files_listing">
		
			<div id="scms_listing_taskbar">
				
				<div id="scms_listing_actions_bar">
					<div id="files_are_selected" class="hidden">
						<?php echo $site->sys_sona(array('sona' => 'files_selected', 'tyyp' => 'Files')); ?>: <a href="javascript:void(0);" id="file_multi_move"><?php echo $site->sys_sona(array('sona' => 'move_file', 'tyyp' => 'Files')); ?></a>, <a href="javascript:void(0);" id="file_multi_delete"><?php echo $site->sys_sona(array('sona' => 'delete_file', 'tyyp' => 'Files')); ?></a>.
					</div>
					<div id="no_files_are_selected" class="hidden">
						<?php echo $site->sys_sona(array('sona' => 'no_files_selected', 'tyyp' => 'Files')); ?> <a class="all_files_selector" href="javascript:void(0);"><?php echo $site->sys_sona(array('sona' => 'select_all_files', 'tyyp' => 'Files')); ?></a>
					</div>
					<div id="custom_actions" class="hidden">
						<span id="custom_action_text"></span><a id="custom_action" class="hidden" href="javascript:void(0);"></a>
						<span id="default_actions" class="hidden"> | <?php echo $site->sys_sona(array('sona' => 'files_selected', 'tyyp' => 'Files')); ?>: <a href="javascript:void(0);" id="file_multi_move"><?php echo $site->sys_sona(array('sona' => 'move_file', 'tyyp' => 'Files')); ?></a>, <a href="javascript:void(0);" id="file_multi_delete"><?php echo $site->sys_sona(array('sona' => 'delete_file', 'tyyp' => 'Files')); ?></a>.</span>
					</div>
				</div><!-- / scms_files_actions_bar -->
				
				<div id="scms_listing_left_actions_bar" class="hidden">
					<a id="switch_to_thumbs" href="javascript:void(0);" class="hidden"><?php echo $site->sys_sona(array('sona' => 'thumbnail_view', 'tyyp' => 'Files')); ?></a>
					<a id="switch_to_list" href="javascript:void(0);" class="hidden"><?php echo $site->sys_sona(array('sona' => 'file_list_view', 'tyyp' => 'Files')); ?></a>
				</div><!-- / scms_listing_left_actions_bar -->
				
			</div><!-- / scms_listing_taskbar -->
			
			<div id="scms_listing_contents">
				
				<div id="scms_file_thumbnails" class="hidden">
				</div><!-- / scms_file_thumbnails -->
				
				<div id="scms_file_list" class="hidden">
				</div><!-- / scms_file_thumbnails -->
				
				<div id="scms_no_files" class="hidden">
					<?php echo $site->sys_sona(array('sona' => 'no_files_in_folder', 'tyyp' => 'Files')); ?>
				</div><!-- / scms_no_files -->
				
				<div id="scms_no_search_results" class="hidden">
					<?php echo $site->sys_sona(array('sona' => 'no_files_found', 'tyyp' => 'Files')); ?>
				</div><!-- / scms_no_files -->
				
			</div><!-- / scms_listing_contents -->
			
		</div><!-- / scms_files_listing -->
		
	</div><!-- / scms_fm_body -->
	
	<div id="scms_footer_bar">
	
		<div id="scms_left_pane_footer">
		</div><!-- / scms_left_pane_footer -->
		
		<div id="scms_files_listing_footer">
			
			<div id="scms_files_info" class="hidden">
				<span id="files_counter"></span>, <a class="all_files_selector" href="javascript:void(0);"><?php echo $site->sys_sona(array('sona' => 'select_all_files', 'tyyp' => 'Files')); ?></a><a class="all_files_deselector hidden" href="javascript:void(0);"><?php echo $site->sys_sona(array('sona' => 'unselect_all_files', 'tyyp' => 'Files')); ?></a>
			</div><!-- / scms_files_info -->
			
		</div><!-- / scms_files_listing_footer -->
			
		<div id="scms_paging" class="hidden">
			<a id="paging_previous" href="javascript:void(0);">&nbsp;</a> <input type="text" id="paging_text" class="paging_text" /> / <span id="paging_total_pages">0</span> <a id="paging_next" href="javascript:void(0);">&nbsp;</a>
		</div><!-- / scms_paging -->
		
	</div><!-- / scms_footer_bar -->

</body>

</html>