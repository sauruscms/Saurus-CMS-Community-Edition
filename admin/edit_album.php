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

function edit_objekt ()
{
	global $site;
	global $objekt;
	global $keel;
	global $class_path;
	global $tyyp;
	
	// refreshing (fdat['refresh'] = 1) looses object data, I don't know why and because it's done in edit_object.php, I'm not going to fix it, lord knows what it'll screw up
	if($site->fdat['refresh'] && $site->fdat['id'])
	{
		$obj = new Objekt(array(
			'objekt_id' => $site->fdat['id'],
		));
		
		$objekt->objekt_id = $obj->objekt_id;
		$objekt->parent_id = $obj->parent_id;
	}
	
	$parent = new Objekt(array(
		'objekt_id' => ($objekt->objekt_id ? $objekt->parent_id : $site->fdat['parent_id']),
	));
	
	$pearubriik = $parent->all['sys_alias'] == 'home' ? 1 : 0;
	
	// parent path
	if($objekt->all['sys_alias'] == '' && $site->fdat['sys_alias'] == '') {

		// this needs serious rethink and optmisation: there's no need to get the entire tree, parent object's path to top is only needed
		include_once $class_path.'rubloetelu.class.php';
		$rubs = new RubLoetelu(array(
			'keel' => $keel,
			'required_perm' => 'C',
			'ignore_perm_for_obj' => $parent->objekt_id,
		));
		#$rubs->debug->print_msg();
		$topparents = $rubs->get_loetelu();
		if(is_array($topparents)) {
			asort($topparents);
		}
		
		foreach($topparents as $k=>$v) if($parent->objekt_id == $k){
			$section_name=$v; break;
	  	}
	}
	
	// publishing
	$publish_start = $objekt->all['avaldamisaeg_algus']>0 ? $site->db->MySQL_ee_long($objekt->all['avaldamisaeg_algus']) : '';
	/* Don't print out time which is 00:00:00 */
	if (preg_match("/(\d?\d[\:\\\.\/\-]\d?\d[\:\\\.\/\-]\d?\d?\d\d)\s(\d?\d)[\:\\\.\/\-](\d?\d)/",$publish_start,$aa_reg)) {
		$publish_start = ($aa_reg[2]=="00"&&$aa_reg[3]=="00")?$aa_reg[1]:$publish_start;
	}
	$publish_end = $objekt->all['avaldamisaeg_lopp'] > 0 ? $site->db->MySQL_ee_long($objekt->all['avaldamisaeg_lopp']) : '';
	/* Don't print out time which is 23:59 */
	if (preg_match("/(\d?\d[\:\\\.\/\-]\d?\d[\:\\\.\/\-]\d?\d?\d\d)\s(\d?\d)[\:\\\.\/\-](\d?\d)/",$publish_end,$la_reg)) {
		$publish_end = ($la_reg[2]=="23"&&$la_reg[3]=="59")?$la_reg[1]:$publish_end;
	}

	// to get the correct path to parent objects set use_alises on
	$site->CONF['use_aliases'] = 1;
	$parent_href = $parent->get_object_href();
	
	if($site->CONF['alias_trail_format'] == 0 || $parent->all['sys_alias'] == 'home' || $parent->all['sys_alias'] == 'trash' || $parent->all['sys_alias'] == 'system' || $parent->all['sys_alias'] == 'gallup_arhiiv') $parent_href = preg_replace('#'.preg_quote('/'.($parent->all['friendly_url'] ? $parent->all['friendly_url'] : $parent->objekt_id), '#').'/$#', '/', $parent_href);
	
	$parent_href = $site->CONF['hostname'].$parent_href;
	
	// setup for section selection
	$_SESSION['parent_selection']['callback'] = 'window.opener.updateSection';
	$_SESSION['parent_selection']['selectable'] = 1;
	$_SESSION['parent_selection']['hide_language_selection'] = '1';
	$_SESSION['parent_selection']['mem_classes'] = array('rubriik', ); //this sucks, really
	$_SESSION['parent_selection']['db_fields'] = array('select_checkbox', 'objekt_id', 'pealkiri', );
	$_SESSION['parent_selection']['display_fields'] = array('select_checkbox', 'pealkiri', );

	// setup folder select
	$_SESSION['scms_filemanager_settings']['scms_select_album_folder'] = array(
		'select_mode' => 2, // 2 - select single folder
		'action_text' => $site->sys_sona(array('sona' => 'use_this_folder_for_album', 'tyyp' => 'editor')), // not used for folder selection
		'action_trigger' => $site->sys_sona(array('sona' => 'use_this_folder_for_album', 'tyyp' => 'editor')),
		'callback' => 'window.opener.setFolder',
	);
	
	$conf = new CONFIG($objekt->all['ttyyp_params']);
	
	$args['cols'] = $conf->get('cols');
	$args['rows'] = $conf->get('rows');
	$args['path'] = $conf->get('path');
	//$args['path'] = 1;
	$args['tn_size'] = $conf->get('tn_size');
	$args['desc'] = $conf->get('desc');
	$args['pic_size'] = $conf->get('pic_size');
	$args['folder_id'] = $conf->get('folder_id');
	
	if(!$args['path'])
	{
		if($objekt->all['pealkiri'])
		{
			$album_folder_path = $clean_path = create_alias_from_string($objekt->all['pealkiri']);
		}
		else 
		{
			$result = new SQL('select max(objekt_id) + 1 from objekt');
			$album_folder_path = $clean_path = $result->fetchsingle();
		}
		
		$supplement = 2;
		
		// unlikely to happen
		if($album_folder_path === '') $album_folder_path = $clean_path = rand(10000, 20000);
	
		while(file_exists($site->absolute_path.'/public/galleries/'.$album_folder_path))
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
	}
	
	//printr($album_folder_path);
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head> 	
		<title><?php echo $site->title;?> <?php echo $site->cms_version;?></title>
		
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $encoding ? $encoding : $site->encoding ?>" />
		<meta http-equiv="Cache-Control" content="no-cache" />
		
		<link rel="stylesheet" href="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/datepicker.css" />
		<link rel="stylesheet" href="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/album_editor.css" />
		<!--[if IE 6]>
			<style type="text/css">
				input.inline_button {
					padding: 0px 0px 0px 0px;
					height: 21px;
				}
			</style>
		<![endif]-->
		<!--[if IE 7]>
			<style type="text/css">
				input.inline_button {
					padding: 0px 0px 0px 0px;
					height: 21px;
				}
			</style>
		<![endif]-->
		
		<script type="text/javascript" src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path'] ?>/yld.js"></script>
		<script type="text/javascript" src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path'] ?>/edit_popup.js"></script>
		<script type="text/javascript" src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path'] ?>/jquery.js"></script>
		<script type="text/javascript" src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path'] ?>/datepicker.js"></script>
		<script type="text/javascript" src="<?php echo $site->CONF['wwwroot'];?>/common.js.php"></script>
		<?php if($site->CONF['fm_allow_multiple_upload'] && $parent->all['ttyyp_id'] != 39) { ?>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/swfupload/swfupload.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/swfupload/swfupload.queue.js"></script>
		<?php } ?>
		
		<script type="text/javascript">
			var isIE = navigator.appVersion.match(/MSIE/); // assume gecko on false
			
			var folder_path = '<?php echo  $album_folder_path; ?>';
			
			var swfu;
			
			window.onload = function ()
			{
				var title = document.getElementById('pealkiri');
				
				var advanced_panel_state = document.getElementById('advanced_panel_state');
				if(advanced_panel_state.value == 1)
				{
					togglePanel('advanced');
				}
				
				this.focus();
				title.focus();
				
				resizeWindow();
				
				<?php if($site->CONF['fm_allow_multiple_upload'] && $parent->all['ttyyp_id'] != 39) { ?>
				swfu = new SWFUpload({
					flash_url : '<?php echo $site->CONF['wwwroot'].$site->CONF['js_path']?>/swfupload/swfupload.swf',
					upload_url: '<?php echo $site->CONF['wwwroot']?>/admin/ajax_response.php',
					post_params: {'PHPSESSID' : '<?php echo session_id(); ?>', 'op': 'add_image_to_album'},
					file_size_limit : '<?php echo (is_int(ini_get('upload_max_filesize')) ? round(ini_get('upload_max_filesize') / 1024) : ini_get('upload_max_filesize').'B'); ?>',
					file_types : '*.gif;*.png;*.jpeg;*.jpg',
					file_types_description : 'Images',
					file_upload_limit : 0,
					file_queue_limit : 100,
					custom_settings : {
						cancelButtonId : 'cancel_file_upload_button'
					},
					debug: false,
			
					// Button settings
					button_image_url: '<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path'];?>/gfx/general/album_upload_button_bg.gif',	// Relative to the Flash file
					button_width: '95',
					button_height: '21',
					button_placeholder_id: 'span_upload_button_place_holder',
					button_text: '<span class="upload_button"><?php echo $site->sys_sona(array('sona' => 'add_images', 'tyyp' => 'editor')); ?></span>',
					button_text_style: '.upload_button { font-family: "Trebuchet MS"; font-size: 12px; font-weight: bold; color: #ffffff }',
					button_text_left_padding: 8,
					button_text_top_padding: 1,
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
				}
			
			function resizeWindow()
			{
				resizeWindowTo($('#size_wrapper').width(), $('#size_wrapper').height());
			}
			
			var filemanager_window;
			var uploadFolderPathSet = false;

			function chooseFolder()
			{
				filemanager_window = openpopup('filemanager.php?setup=scms_select_album_folder', 'filemanager', 980, 600);
			}
			
			function setFolder(data)
			{
				filemanager_window.close();
				uploadFolderPathSet = true;
				
				$('input#path').attr('value', data.folders[0].relative_path.replace(/^\//, ''));
				$('a#images_folder_path_link').text(data.folders[0].relative_path.replace(/^\//, ''));
				
				$('td#images_folder_cf_container_cell').removeClass('hidden');
				$('td#images_choose_folder_button_cell').addClass('hidden');
			}
			
			
			function clearFolder()
			{
				$('input#path').attr('value', '');
				
				$('td#images_folder_cf_container_cell').addClass('hidden');
				$('td#images_choose_folder_button_cell').removeClass('hidden');
				
				resizeWindow();
			}
			
			function chooseSection()
			{
				explorer_window = openpopup('explorer.php?objekt_id=home&editor=1&swk_setup=parent_selection&remove_objects=<?php echo $site->fdat['id'];?>&pre_selected=' + document.getElementById('rubriik').value, 'cms_explorer', '800','600');
			}
			
			function updateSection(sections)
			{
				explorer_window.close();
				var section_name = document.getElementById('section_name');
				var section_id = document.getElementById('rubriik');
				var trail_path= new Array();

					for(var j = 0; j < sections[0].trail.length; j++){
						trail_path[j] = sections[0].trail[j].pealkiri;
					}

				section_name.innerHTML = '<a href="javascript:chooseSection();">' + trail_path.join("->") + '</a>';
				section_id.value = sections[0].objekt_id;
			}

			function editAlias()
			{
				var alias_placeholder = document.getElementById('alias_placeholder');
				var alias_value = document.getElementById('alias_value');
				
				alias_placeholder.innerHTML = '<input type="text" id="alias" value="' + alias_value.value + '" onblur="saveAlias();">';
				
		    	resizeWindow();
		    	
				var alias = document.getElementById('alias');
				alias.focus();
			}
			
			function saveAlias()
			{
				var alias_placeholder = document.getElementById('alias_placeholder');
				var alias_value = document.getElementById('alias_value');
				var alias = document.getElementById('alias');
				
				if(alias_value.value != alias.value)
				{
					$.ajax({
					    url: 'ajax_response.php?rand=' + Math.random(9999),
					    data: {op: 'generate_alias', string: alias.value, language_id: '<?php echo $keel;?>'},
					    type: 'POST',
					    dataType: 'json',
					    timeout: 1000,
					    error: function()
					    {
							alias_placeholder.innerHTML = '<a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + (alias_value.value.length > 30 ? alias_value.value.substring(0, 30) + '...' : alias_value.value) + '</a>';
					    },
					    success: function(response)
					    {
					    	if(response.alias)
					    	{
								alias_value.value = response.alias;
								alias_placeholder.innerHTML = '<a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + (alias_value.value.length > 30 ? alias_value.value.substring(0, 30) + '...' : alias_value.value) + '</a>';
					    	}
					    	else
					    	{
								alias_value.value = '';
								<?php if($objekt->objekt_id) { ?>
								alias_placeholder.innerHTML = '<a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + '<?php echo $objekt->objekt_id;?>' + '</a>';
								<?php } else { ?>
						    	alias_placeholder.innerHTML = '<input type="text" id="alias" value="" onblur="saveAlias();">';
								<?php } ?>
					    	}
							
					    	resizeWindow();
					    }
					});
				}
				else
				{
					if(!alias.value)
					{
						alias_value.value = '';
						<?php if($objekt->objekt_id) { ?>
						alias_placeholder.innerHTML = '<a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + '<?php echo $objekt->objekt_id;?>' + '</a>';
						<?php } else { ?>
				    	alias_placeholder.innerHTML = '<input type="text" id="alias" value="" onblur="saveAlias();">';
						<?php } ?>
					}
					else
					{
						alias_placeholder.innerHTML = '<a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + (alias_value.value.length > 30 ? alias_value.value.substring(0, 30) + '...' : alias_value.value) + '</a>';
					}
			    	
					resizeWindow();
				}
			}
			
			function createAlias()
			{
				var alias_value = document.getElementById('alias_value');
				var title = document.getElementById('pealkiri')
				
				if(0 || (!alias_value && title.value))
				{
					$.ajax({
					    url: 'ajax_response.php?rand=' + Math.random(9999),
					    data: {op: 'generate_alias', string: title.value, language_id: '<?php echo $keel;?>'},
					    type: 'POST',
					    dataType: 'json',
					    timeout: 1000,
					    error: function()
					    {
					    },
					    success: function(response)
					    {
					    	var alias_cell = document.getElementById('alias_cell');
					    	alias_cell.className = 'alias';
					    	if(response.alias)
					    	{
						    	alias_cell.innerHTML = '<input type="hidden" name="friendly_url" id="alias_value" value="' + response.alias + '"><?php echo $parent_href;?><span id="alias_placeholder"><a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + (response.alias.length > 30 ? response.alias.substring(0, 30) + '...' : response.alias) + '</a></span>';
						    	if(swfu && !swfu.uploadFolderPathSent && !uploadFolderPathSet) $('input#path').attr('value', 'public/galleries/' + response.alias);
					    	}
					    	else
					    	{
						    	alias_cell.innerHTML = '<input type="hidden" name="friendly_url" id="alias_value" value=""><?php echo $parent_href;?><span id="alias_placeholder"><input type="text" id="alias" value="" onblur="saveAlias();"></span>';
					    	}
					    	
					    	$('a#images_folder_path_link').text($('input#path').attr('value'));
					    	
					    	$('#alias_row').show();
					    	//var alias_row = document.getElementById('alias_row');
					    	//alias_row.style.display = (isIE ? 'block' : 'table-row');

	    					resizeWindow();
					    }
					});			
				}
			}
			
			function saveForm(op2)
			{
				var form = document.getElementById('editForm');
				
				var title = document.getElementById('pealkiri');
				
				if(title.value.length == 0)
				{
					alert('<?php echo $site->sys_sona(array('sona' => 'please_fill_in_the_title!', 'tyyp' => 'admin'));?>');
					return;
				}
				
				var alias_value = document.getElementById('alias_value');
				var alias = document.getElementById('alias');
				
				if((title.value && !alias_value) || (alias && alias_value && alias.value != alias_value.value))
				{
					$.ajax({
					    url: 'ajax_response.php?rand=' + Math.random(9999),
					    data: {op: 'generate_alias', string: title.value, language_id: '<?php echo $keel;?>'},
					    type: 'POST',
					    dataType: 'json',
					    timeout: 1000,
					    error: function()
					    {
					    	var form = document.getElementById('editForm');
			 				
					    	form.op2.value = op2;
			 				form.submit();
					    },
					    success: function(response)
					    {
					    	var alias_value = document.getElementById('alias_value');
					    	
					    	if(!alias_value && response.alias)
					    	{
						    	var alias_cell = document.getElementById('alias_cell');
						    	alias_cell.innerHTML = '<input type="hidden" name="friendly_url" id="alias_value" value="' + response.alias + '"><?php echo $parent_href;?><span id="alias_placeholder"><a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + (response.alias.length > 30 ? response.alias.substring(0, 30) + '...' : response.alias) + '</a></span>';
					    	}
							
					    	var form = document.getElementById('editForm');
			 				
					    	form.op2.value = op2;
			 				form.submit();
					    }
					});
				}
				else
				{
	 				form.op2.value = op2;
	 				form.submit();
				}
			}
			
			<?php if($site->CONF['fm_allow_multiple_upload'] && $parent->all['ttyyp_id'] != 39) { ?>
			// SWFupload handler functions
			function fileQueued(file) {
				try {
				} catch (ex) {
					this.debug(ex);
				}
			}
			

			function fileQueueError(file, errorCode, message) {
				try {
					
					if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
						alert('<?php echo $site->sys_sona(array('sona' => 'upload_queue_limit', 'tyyp' => 'Files')); ?>' + ': ' + this.settings.file_queue_limit);
						return;
					}
				
					if (errorCode === SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT) {
						alert(file.name + ' ' + '<?php echo $site->sys_sona(array('sona' => 'upload_limit_size', 'tyyp' => 'Files')); ?>' + ' ' + this.settings.file_size_limit);
						return;
					}
			
					switch (errorCode) {
					default:
						if (file !== null) {
							alert("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
						}
						this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
						break;
					}
				} catch (ex) {
			        this.debug(ex);
			    }
			}
			
			function fileDialogComplete(numFilesSelected, numFilesQueued) {
				try {
					
					if(numFilesQueued > 0)
					{
						$('td#images_folder_cf_container_cell').addClass('hidden');
						$('td#images_choose_folder_button_cell').addClass('hidden');
						$('table#form_submit_buttons_table').addClass('hidden');
						
						this.setButtonDisabled(true);
						this.setButtonDimensions(1, 1);
						
						$('td#upload_progress_cell').removeClass('hidden');
						$('td#upload_progress_text_cell').removeClass('hidden');
						$('table#upload_cancel_table').removeClass('hidden');
						
						$('div#upload_progress_grow').width(0);
						
						this.numFilesQueued = numFilesQueued;
						
						if(!$('input#path').attr('value')) $('input#path').attr('value', folder_path);
						
						this.addPostParam('folder_path', $('input#path').attr('value'));
						this.uploadFolderPathSent = true;
						this.startUpload();
					}
				} catch (ex)  {
			        this.debug(ex);
				}
			}
			
			function uploadStart(file) {
				try {
					
					$('td#upload_progress_text_cell').html(file.name + ' <span id="percent_placeholder">0</span>%');
					this.progressBarWidth = $('div#upload_progress_grow').width();
					
				} catch (ex)  {
			        this.debug(ex);
				}
				
				return true;
			}
			
			function uploadProgress(file, bytesLoaded, bytesTotal) {
				try {
					var percent = Math.round((bytesLoaded / bytesTotal) * 100);
					
					$('div#upload_progress_grow').width(this.progressBarWidth + Math.round(($('div#upload_progress_bar').width() / this.numFilesQueued * percent) / 100));
					
					$('span#percent_placeholder').html(percent);
					
				} catch (ex) {
					this.debug(ex);
				}
			}
			
			function uploadSuccess(file, serverData) {
				try {
				} catch (ex) {
					this.debug(ex);
				}
			}
			
			function uploadError(file, errorCode, message) {
				try {
					
					switch (errorCode) {
						case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
						case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
							// upload canceled
						break;
						
						case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
							alert('Error occured while trying to connect.');
						break;
						
						default:
							alert("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
						break;
					}
				} catch (ex) {
			        this.debug(ex);
			    }
			}
			
			function uploadComplete(file) {
				
				$('td#upload_progress_text_cell').empty();
				
				if (this.getStats().files_queued === 0) {
					
					// all files are finished
					$('td#upload_progress_cell').addClass('hidden');
					$('td#upload_progress_text_cell').addClass('hidden');
					$('table#upload_cancel_table').addClass('hidden');
						
					this.setButtonDisabled(false);
					this.setButtonDimensions(95, 21);
					
					$('a#images_folder_path_link').text($('input#path').attr('value'));
					
					$('table#form_submit_buttons_table').removeClass('hidden');
					$('td#images_folder_cf_container_cell').removeClass('hidden');
				}
			}
			
			function swfuLoaded()
			{
				$('input#add_images_button').addClass('hidden');
			}
			<?php } ?>
			
		</script>
	</head>
	
	<body>
		
		<? if ($site->fdat['op']=='edit') {?>
			<iframe src="checkin.php?objekt_id=<?php echo $objekt->objekt_id ?>" style="width: 0; height: 0; display: none; visibility: hidden;"></iframe>
		<? } ?>
		
		<form action="edit.php" name="editForm" id="editForm" method="POST"  enctype="multipart/form-data">
		
		<input type="hidden" name="tab" value="<?php echo $site->fdat['tab']?>" />
		<input type="hidden" id="op" name="op" value="<?php echo htmlspecialchars($site->fdat['op'])?>" />
		<input type="hidden" id="op2" name="op2" value="" />
		<input type="hidden" id="refresh" name="refresh" value="0" />
		
		<input type="hidden" name="tyyp_id" value="<?php echo $tyyp['tyyp_id']?>" />
		<input type="hidden" name="tyyp" value="<?php echo $tyyp['klass']?>" />
		
		<input type="hidden" name="pearubriik" value="<?php echo $pearubriik ?>" />
		<input type="hidden" name="id" value="<?php echo $site->fdat['id'] ?>" />
		<input type="hidden" name="parent_id" value="<?php echo $site->fdat['parent_id']?>" />
		<input type="hidden" name="previous_id" value="<?php echo $site->fdat['previous_id']?>" />
		<input type="hidden" name="keel" value="<?php echo $keel?>" />
		<input type="hidden" name="on_pealkiri" value="1" />
		
        <input type="hidden" name="sorting" value="<?=$site->fdat['sorting'];?>">

        <input type="hidden" name="extension_path" value="<?php echo $site->fdat['extension_path']?>" />
		
		<input type="hidden" name="opener_location" value="" />
		<input type="hidden" name="publish" value="<?php echo ($site->fdat['publish'] || $objekt->all['on_avaldatud'] ? 1 : 0); ?>">

		<input name="permanent_parent_id" type="hidden" value="<?php echo $objekt->parent_id?>" />
		<input name="sys_alias" type="hidden" value="<?php echo ($site->fdat['sys_alias'] ? $site->fdat['sys_alias'] : $objekt->all['sys_alias'])?>" />
		
		<input name="advanced_panel_state" id="advanced_panel_state" type="hidden" value="<?php echo ($site->fdat['advanced_panel_state'] ? htmlspecialchars($site->fdat['advanced_panel_state']) : 0) ?>" />
		
		<div id="size_wrapper" class="section_editor">
		
		<div id="main_container">
			<?php ########### Tabs  ########?>
			<div id="tab_container">
				<a href="javascript:void(0);" class="selected"><?php echo $site->sys_sona(array('sona' => 'tyyp_album', 'tyyp' => 'System'));?></a>
			</div>
			
			<div id="content_container">
		
				<table cellpadding="0" cellspacing="0" class="form_row">
					<tr>
						<td class="label"><label><?php echo $site->sys_sona(array('sona' => 'Pealkiri', 'tyyp' => 'editor'))?>:</label></td>
						<td class="input"><input type="text" class="text" name="pealkiri" id="pealkiri" value="<?php echo htmlspecialchars($objekt->all['pealkiri'])?>" onblur="createAlias();" /></td>
					</tr>
					<?php if(($objekt->objekt_id || isset($objekt->all['friendly_url'])) && !($objekt->all['sys_alias'] == 'home' || $objekt->all['sys_alias'] == 'trash' || $objekt->all['sys_alias'] == 'system' || $objekt->all['sys_alias'] == 'gallup_arhiiv')) { ?>
					<tr>
						<td class="label">&nbsp;</td>
						<td class="input"><input type="hidden" id="alias_value" name="friendly_url" name="friendly_url" value="<?php echo htmlspecialchars($objekt->all['friendly_url']);?>" /><?php echo $parent_href;?><span id="alias_placeholder"><a href="javascript:void(0);" onclick="editAlias();" id="alias_link"><?php echo ($objekt->all['friendly_url'] ? (strlen(htmlspecialchars($objekt->all['friendly_url'])) > 30 ? substr(htmlspecialchars($objekt->all['friendly_url']), 0, 30).'...' : htmlspecialchars($objekt->all['friendly_url'])) : $objekt->objekt_id);?></a></span></td>
					</tr>
					<?php } else { ?>
					<tr id="alias_row">
						<td class="label">&nbsp;</td>
						<td class="input" id="alias_cell"></td>
					</tr>
					<?php } ?>
					
					<?php ########### images folder  ########?>
					<?php if($parent->all['ttyyp_id'] != 39) { ?>
					<tr id="images_folder">
						<td class="label"><?php echo $site->sys_sona(array('sona' => 'Image files directory', 'tyyp' => 'editor'))?>:</td>
						<td class="input">
							<table cellpadding="0" cellspacing="0" class="container" id="images_folder_cf_container_table">
								<tr>
									<?php ########### images folder  ########?>
									<td id="images_folder_cf_container_cell"<?php echo ($args['path'] ? '' : ' class="hidden"'); ?>>
										<table cellpadding="0" cellspacing="0" class="cf_container">
											<tr>
												<th><input type="hidden" name="path" id="path" value="<?php echo ($args['path'] ? $args['path'] : ''); ?>"><span id="images_folder_path"><a href="javascript:chooseFolder();" id="images_folder_path_link" title="<?php echo $site->sys_sona(array('sona' => 'choose_a_folder', 'tyyp' => 'editor'))?>"><?php echo $args['path']; ?></a></span></th>
												<td><a href="javascript:chooseFolder();" title="<?php echo $site->sys_sona(array('sona' => 'choose_a_folder', 'tyyp' => 'editor'))?>">..</a></td>
												<td><a href="javascript:clearFolder();">X</a></td>
											</tr>
										</table>
									</td>
									
									<?php ########### add images  ########?>
									<?php if($site->CONF['fm_allow_multiple_upload']) { ?>
									<td id="images_add_button_cell">
										<span id="span_upload_button_place_holder"></span>
									</td>
									
									<?php ########### upload progress  ########?>
									<td id="upload_progress_cell" class="hidden"><div id="upload_progress_bar"><div id="upload_progress_grow"></div></div></td><!-- / scms_upload_progress -->
									<td id="upload_progress_text_cell" class="hidden"></td><!-- / scms_upload_text -->
									<?php } ?>
									
									<?php ########### choose_a_folder  ########?>
									<td id="images_choose_folder_button_cell"<?php echo ($args['path'] ? ' class="hidden"' : ''); ?>>
										<?php if($site->CONF['fm_allow_multiple_upload']) { ?>&nbsp;<?php echo $site->sys_sona(array('sona' => 'or', 'tyyp' => 'editor'))?><?php } ?>
										<input type="button" value="<?php echo $site->sys_sona(array('sona' => 'choose_a_folder', 'tyyp' => 'editor'))?>" class="inline_button" onclick="chooseFolder();" />
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<?php } ?>
					
					<?php ########### description  ########?>
					<tr>
						<td class="label"><?php echo $site->sys_sona(array('sona' => 'Kirjeldus', 'tyyp' => 'editor'))?>:</td>
						<td class="input"><textarea name="desc"><?php echo $args['desc'] ? $args['desc'] : ""?></textarea></td>
					</tr>
					
					<?php ########### publishing  ########?>
					<tr>
						<td class="label"><?php echo $site->sys_sona(array('sona' => 'visible_to_visitors', 'tyyp' => 'editor'))?>:</td>
						<td><input type="radio" name="publish" id="object_published" value="1"<?=($site->fdat['publish'] || $objekt->all['on_avaldatud'] ? ' checked' : '')?><?php echo (!$objekt->permission['P'] ? ' disabled="disabled"' : NULL); ?>> <label for="object_published"><?=$site->sys_sona(array('sona' => 'published', 'tyyp' => 'editor'))?></label>	<input type="radio" name="publish" id="object_unpublished" value="0"<?=($site->fdat['publish'] == 0 && $objekt->all['on_avaldatud'] == 0 ? ' checked' : '')?><?php echo (!$objekt->permission['P'] ? ' disabled="disabled"' : NULL); ?>> <label for="object_unpublished"><?=$site->sys_sona(array('sona' => 'unpublished', 'tyyp' => 'editor'))?></label></td>
					</tr>
				</table>
				
				<br />
				
				<?php ########### advanced  ########?>
				<div class="panel_toggler" onclick="togglePanel('advanced');">
					<a href="javascript:void(0);"><?php echo $site->sys_sona(array('sona' => 'Advanced', 'tyyp' => 'editor'))?> <span id="advanced_panel_link_state">&raquo;</span></a>
				</div>
				
				<div id="advanced_panel" class="panel">
					
					<?php ########### image sizes  ########?>
					<table cellpadding="0" cellspacing="0" class="form_row">
						<tr>
							<td class="label">Image sizes:</td>
							<td><?php echo $site->sys_sona(array('sona' => 'Image size', 'tyyp' => 'editor'))?>:</td>
							<td>
								<input name="pic_size" class="text_number" value="<?php echo $args['pic_size'] ? $args['pic_size'] : $site->CONF['image_width']?>" />
								<input name="old_pic_size" type="hidden" value="<?php echo $args['pic_size'] ? $args['pic_size'] : $site->CONF['image_width']?>" />
							</td>
							<td>px&nbsp;</td>
							<td><?php echo $site->sys_sona(array('sona' => 'Thumbnail size', 'tyyp' => 'editor'))?>:</td>
							<td>
								<input name="tn_size" class="text_number" value="<?php echo $args['tn_size'] ? $args['tn_size'] : $site->CONF['thumb_width']?>" />
								<input name="old_tn_size" type="hidden" value="<?php echo $args['tn_size'] ? $args['tn_size'] : $site->CONF['thumb_width']?>" />
							</td>
							<td>px</td>
						</tr>
					</table>
					
					<?php ########### parent section  ########?>
					<?php if($section_name) { ?>
					<table cellpadding="0" cellspacing="0" class="form_row">
						<tr>
							<td class="label"><label><?php echo $site->sys_sona(array('sona' => 'Rubriigid', 'tyyp' => 'editor'))?>:</label></td>
							<td class="input">
								<table cellpadding="0" cellspacing="0" class="cf_container">
									<tr>
										<th><input type="hidden" name="rubriik[]" id="rubriik" value="<?php echo $parent->objekt_id;?>"><span id="section_name"><a href="javascript:chooseSection();"><?php echo $section_name;?></a></span></th>
										<td><a href="javascript:chooseSection();">..</a></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<?php } ?>
					
					<table cellpadding="0" cellspacing="0" class="form_row">
						<tr>
							<td class="label">&nbsp;</td>
							<td><label><?php echo $site->sys_sona(array('sona' => 'Avaldatud', 'tyyp' => 'editor'))?>:</label></td>
							<td><input type="text" id="publish_start" name="avaldamise_algus" maxlength="16" class="text_date" value="<?php echo $publish_start?>" /></td>
							<td><a href="javascript:init_datepicker('publish_start', 'publish_start', 'publish_end');"><img src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" title="Choose from calendar" alt="Choose from calendar" /></a></td>
							<td><label><?php echo $site->sys_sona(array('sona' => 'Kuni', 'tyyp' => 'editor'))?>:</label></td>
							<td><input type="text" id="publish_end" name="avaldamise_lopp" maxlength="16" class="text_date" value="<?php echo $publish_end?>" /></td>
							<td><a href="javascript:init_datepicker('publish_end', 'publish_start', 'publish_end');"><img src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" title="Choose from calendar" alt="Choose from calendar" /></a></td>
						</tr>
					</table>
					
					<?php ########### position  ########?>
					<?php if($site->CONF['allow_change_position']) { ?>
					<table cellpadding="0" cellspacing="0" class="form_row">
						<tr>
							<td class="label"><label><?php echo $site->sys_sona(array('sona' => 'Position', 'tyyp' => 'editor'))?>:</label></td>
							<td><input type="text" maxlength="5" class="text_position" name="kesk" value="<?php echo ($site->fdat['op']=='edit' ? $objekt->all['kesk'] : $site->fdat['kesk']);?>" /></td>
						</tr>
					</table>
					<?php } else { ?>
						<input type="hidden" name="kesk" value="<?php echo ($site->fdat['op']=='edit' ? $objekt->all['kesk'] : $site->fdat['kesk']);?>" />
					<?php } ?>
					
				</div>
			</div>
			
		</div>
		
		<div id="button_container">
			
			<table cellspacing="0" cellpadding="0" id="form_submit_buttons_table">
				<tr>
					<td id="apply_button_cell">
						<input type="button" class="button" value="<?php echo $site->sys_sona(array('sona' => 'Apply', 'tyyp' => 'editor'))?>" onclick="saveForm('save');" />
					</td>
					<td id="save_close_button_cell">
						<input type="button" class="button" value="&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $site->sys_sona(array('sona' => 'save_and_close', 'tyyp' => 'editor'))?>&nbsp;&nbsp;&nbsp;&nbsp;" onclick="saveForm('saveclose');" />
						<input type="button" class="button" value="<?php echo $site->sys_sona(array('sona' => 'Close', 'tyyp' => 'editor'))?>" onclick="window.close();" />		
					</td>
				</tr>
			</table>
			
			<table cellspacing="0" cellpadding="0" class="hidden" id="upload_cancel_table">
				<tr>
					<td id="cancel_button_cell">
						<input type="button" class="button" value="<?php echo $site->sys_sona(array('sona' => 'katkesta', 'tyyp' => 'editor'))?>" onclick="swfu.cancelQueue();" />		
					</td>
				</tr>
			</table>
			
		</div> <!-- / button_container -->
		
		</div> <!-- / size_wrapper -->
		
		</form>
	</body>
</html>

<?php
	
}

function save_tyyp_params (){

	global $site;
	$args = func_get_arg(0);
	$objekt = $args["objekt"];

	$conf = new CONFIG($objekt->all[ttyyp_params]);
	$conf->put("cols", $site->fdat[cols]);
	$conf->put("rows", $site->fdat[rows]);

	$conf->put('path', $site->fdat['path']);
	$conf->put('tn_size', $site->fdat['tn_size']);
	$conf->put('desc', $site->fdat['desc']);
	$conf->put('pic_size', $site->fdat['pic_size']);

	// get the folder ID
	$sql = $site->db->prepare('select objekt_id from obj_folder where relative_path = ?', '/'.$site->fdat['path']);
	$result = new SQL($sql);
	
	$conf->put('folder_id', $result->fetchsingle());

	return $conf->Export();
}


function salvesta_objekt () {
	global $site;
	global $objekt;

	if ($objekt->objekt_id) {

		if ($objekt->on_sisu_olemas) {
			# -------------------------------
			# Objekti uuendamine andmebaasis
			# -------------------------------
			$sql = $site->db->prepare("update obj_rubriik set on_peida_vmenyy=?, on_printlink=?, on_meilinglist=? WHERE objekt_id=?",
				$objekt->all[on_peida_vmenyy],
				$site->fdat[on_printlink] ? 1 : 0,
				$site->fdat[on_meilinglist] ? 1 : 0,
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("insert into obj_rubriik (objekt_id,on_peida_vmenyy, on_printlink, on_meilinglist) values (?,?,?,?)",
				$objekt->objekt_id,
				$objekt->all[on_peida_vmenyy],
				$site->fdat[on_printlink] ? 1 : 0,
				$site->fdat[on_meilinglist] ? 1 : 0
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			// Here we make objekt_id like current id (in main window)
?>
			<script language=javascript><!--		
				variableFromEditRubriik_id='<?php echo $objekt->objekt_id ?>';
			//--></script>
<?		
		}

		$site->debug->msg("sisu on salvestatud, objekt_id = ".$objekt->objekt_id);
		#$site->debug->print_hash($site->fdat,1,"FDAT");	

	} else {
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}

    ############################
    ### image and thumb generation
	if(($site->fdat['old_tn_size'] != $site->fdat['tn_size'] || $site->fdat['old_path'] != $site->fdat['path'] || $site->fdat['old_pic_size'] != $site->fdat['pic_size']) || $site->fdat['op2'] == 'save')
    {
        if($site->fdat['path'])
		{
			global $class_path;

			include_once($class_path.'picture.inc.php');
			generate_images($site->absolute_path.$site->fdat['path'],$site->fdat['tn_size'],$site->fdat['pic_size']);
		}
    }
    ### end image and thumb generation
    ############################
}
