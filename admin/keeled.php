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



global $site;

$class_path = '../classes/';
include_once($class_path.'port.inc.php');
include_once($class_path.'adminpage.inc.php');
include_once($class_path.'lgpl/Services_JSON.class.php');

$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1:0),
	'on_admin_keel' => 1
));

if (!$site->user->allowed_adminpage())
{
	exit;
}

$json_encoder = new Services_JSON();

$sites = array();

$sql = "select a.nimi as glossary, b.*, c.nimi as page_template_name, d.nimi as content_template_name from keel as a left join keel as b on a.keel_id = b.glossary_id left join templ_tyyp as c on c.ttyyp_id = b.page_ttyyp_id left join templ_tyyp as d on d.ttyyp_id = b.ttyyp_id where b.on_kasutusel = '1' order by b.nimi";
$result = new SQL($sql);
while($row = $result->fetch('ASSOC'))
{
	$sites[] = $row;
	
	if($row['on_default'])
	{
		 $default_page_template_id = $row['page_ttyyp_id'];
		 $default_content_template_id = $row['ttyyp_id'];
	}
}

//printr($sites);

$encodings = array ( 
	'ISO-8859-1',
	'ISO-8859-2',
	'ISO-8859-3',
	'ISO-8859-4',
	'ISO-8859-5',
	'ISO-8859-6',
	'ISO-8859-6-e',
	'ISO-8859-6-i',
	'ISO-8859-7',
	'ISO-8859-8',
	'ISO-8859-8-e',
	'ISO-8859-8-i',
	'ISO-8859-9',
	'ISO-8859-10',
	'ISO-8859-13',
	'ISO-8859-14',
	'ISO-8859-15',
	'UTF-8',
	'ISO-2022-JP',
	'EUC-JP',
	'Shift_JIS',
	'GB2312',
	'Big5',
	'EUC-KR',
	'windows-1250',
	'windows-1251',
	'windows-1252',
	'windows-1253',
	'windows-1254',
	'windows-1255',
	'windows-1256',
	'windows-1257',
	'windows-1258',
	'KOI8-R',
	'KOI8-U',
	'cp866',
	'cp874',
	'TIS-620',
	'VISCII',
	'VPS',
	'TCVN-5712',
);

$glossaries = array();

//$sql = "SELECT nimi, keel_id as glossary_id FROM keel WHERE keel_id < 500 ORDER BY nimi";
$sql = "select distinct keel.keel_id as glossary_id, keel.nimi from keel left join sys_sonad on keel.keel_id = sys_sonad.keel where sys_sonad.keel is not null and keel.keel_id < 500 order by keel.nimi";
$result = new SQL($sql);
while($row = $result->fetch('ASSOC'))
{
	$glossaries[] = $row;
}

$adminpage_names = get_adminpage_name(array('script_name' => $site->script_name));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>

<title>Sites</title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $site->encoding; ?>" />

<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/sites_settings.css" />

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
		
		input#create_site_button, input#save_site_button, input#cancel_site_button, input#new_site_cancel_button, input#new_site_create_button {
			width: 85px;
		}
	</style>
<![endif]-->


<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/admin_menu.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/jquery.js"></script>

<script type="text/javascript">

	var ajax_token = <?php echo create_form_token_json('sites'); ?>;
	var site_url = '<?php echo (empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'];?>';
	var sites = [];
	<?php foreach ($sites as $site_data)  { ?>
	sites[<?php echo $site_data['keel_id'] ?>] = <?php echo $json_encoder->encode($site_data) ?>;
	<?php } ?>
	
</script>

<script type="text/javascript">
$(document).ready(function()
{
	make_breadcrumb('<?php echo $adminpage_names['parent_pagename']; ?>', '<?php echo $adminpage_names['pagename']; ?>');
	
	$('tr.site_listing_row').live('mouseover', function()
	{
		var site_id = $(this).attr('id').replace('site_listing_', '');
		
		$(this).addClass('selected');
		
		if(sites[site_id].on_default == '0') $(this).children('td.site_delete_button_cell').removeClass('invisible');
	});
	
	$('tr.site_listing_row').live('mouseout', function()
	{
		$(this).children('td.site_delete_button_cell').addClass('invisible');
		
		$(this).removeClass('selected');
	});
	
	$('tr.site_listing_row').live('click', function ()
	{
		var site_id = $(this).attr('id').replace('site_listing_', '');
		
		$('tr#site_create_new_row').addClass('hidden');
		
		$('input#new_site_cancel_button').addClass('hidden');
		$('input#new_site_create_button').addClass('hidden');
		
		var edit_row = $('tr#site_edit_row').insertAfter('tr#site_listing_' + site_id);
		
		if(sites[site_id].on_default != '0')
		{
			$('input#is_site_default').attr('checked', 'checked');
			$('input#is_site_default').attr('disabled', 'disabled');
		}
		else
		{
			$('input#is_site_default').removeAttr('checked');
			$('input#is_site_default').removeAttr('disabled');
		}
		
		$('input#site_name').attr('value', sites[site_id].nimi);
		$('input#site_extension').attr('value', sites[site_id].extension);
		$('input#site_url').attr('value', sites[site_id].site_url);
		
		$('select#site_encoding option:selected').removeAttr('selected');
		$("select#site_encoding option[value='" + sites[site_id].encoding + "']").attr('selected', 'selected');
		
		$('select#site_glossary_id option:selected').removeAttr('selected');
		$("select#site_glossary_id option[value='" + sites[site_id].glossary_id + "']").attr('selected', 'selected');
		
		$('select#site_page_template_id option:selected').removeAttr('selected');
		$("select#site_page_template_id option[value='" + sites[site_id].page_ttyyp_id + "']").attr('selected', 'selected');
		
		$('select#site_content_template_id option:selected').removeAttr('selected');
		$("select#site_content_template_id option[value='" + sites[site_id].ttyyp_id + "']").attr('selected', 'selected');
		
		$('tr#site_listing_' + $('input#site_id').attr('value')).removeClass('hidden');
		
		$('tr#site_listing_' + site_id).addClass('hidden');
		
		$('input#create_site_button').addClass('hidden');
		
		$('input#save_site_button').removeClass('hidden');
		$('input#cancel_site_button').removeClass('hidden');
		
		$('input#site_id').attr('value', site_id);
		
		$(edit_row).removeClass('hidden');
		
	});
	
	$('img.site_delete_button').live('click', function ()
	{
		var site_id = $(this).attr('id').replace('site_delete_button_', '');
		
		var data = {
	    	op: 'get_site_objects_count',
	    	site_id: site_id
		}
		$.extend(data, ajax_token);
		
		$('div#scms_content_cover').removeClass('hidden');
		
		$.ajax({
		    url: site_url + '/admin/ajax_response.php',
		    cache: false,
		    data: data,
		    type: 'POST',
		    dataType: 'json',
		    timeout: 10000,
		    error: function(XMLHttpRequest, textStatus, errorThrown)
		    {
		    	alert(textStatus);
				$('div#scms_content_cover').addClass('hidden');
		    },
		    success: function(response, textStatus)
		    {
				confirmDialog('<?php echo $site->sys_sona(array('sona' => 'sites_delete_confirm', 'tyyp' => 'admin')); ?> "' + sites[site_id].nimi + '"? ' + response.count + ' <?php echo $site->sys_sona(array('sona' => 'site_objects_will_be_deleted', 'tyyp' => 'admin')); ?>!', function ()
				{
					$('div#scms_content_cover').removeClass('hidden');
					
					var data = {
				    	op: 'delete_site',
				    	site_id: site_id
					}
					
					$.extend(data, ajax_token);
					
					$.ajax({
					    url: site_url + '/admin/ajax_response.php',
					    cache: false,
					    data: data,
					    type: 'POST',
					    dataType: 'json',
					    timeout: 10000,
					    error: function(XMLHttpRequest, textStatus, errorThrown)
					    {
					    	alert(textStatus);
							$('div#scms_content_cover').addClass('hidden');
					    },
					    success: function(response, textStatus)
					    {
					    	if(response.error)
					    	{
					    		messageDialog(response.error_message);
					    	}
					    	else
					    	{
					    		$('tr#site_listing_' + site_id).remove();
					    		fadingMessage('"' + sites[site_id].nimi + '" <?php echo $site->sys_sona(array('sona' => 'site_deleted', 'tyyp' => 'admin')); ?>');
					    		
					    		delete sites[site_id];
					    	}
					    }
					});				
				});
		    }
		});

		return false;	// stop click bubbling/propagation
	});
	
	$('input#save_site_button').click(function ()
	{
		$('div#scms_content_cover').removeClass('hidden');
		
		var data = {
	    	op: 'edit_site_settings',
	    	site_id: $('input#site_id').attr('value'),
	    	is_default: ($('input#is_site_default').attr('checked') ? 1 : 0),
	    	name: $('input#site_name').attr('value'),
	    	extension: $('input#site_extension').attr('value'),
	    	site_url: $('input#site_url').attr('value'),
	    	encoding: $('select#site_encoding').attr('value'),
	    	glossary_id: $('select#site_glossary_id').attr('value'),
	    	page_template_id: $('select#site_page_template_id').attr('value'),
	    	content_template_id: $('select#site_content_template_id').attr('value')
		}
		
		$.extend(data, ajax_token);
		
		$.ajax({
		    url: site_url + '/admin/ajax_response.php',
		    cache: false,
		    data: data,
		    type: 'POST',
		    dataType: 'json',
		    timeout: 10000,
		    error: function(XMLHttpRequest, textStatus, errorThrown)
		    {
		    	alert(textStatus);
				$('div#scms_content_cover').addClass('hidden');
		    },
		    success: function(response, textStatus)
		    {
		    	if(response.error)
		    	{
		    		messageDialog('Error: site settings not changed');
		    	}
		    	else
		    	{
		    		sites[data.site_id].nimi = data.name;
		    		sites[data.site_id].on_default = data.is_default;
		    		sites[data.site_id].extension = data.extension;
		    		sites[data.site_id].site_url = data.site_url;
		    		sites[data.site_id].encoding = data.encoding;
		    		sites[data.site_id].glossary_id = data.glossary_id;
		    		sites[data.site_id].page_ttyyp_id = data.page_template_id;
		    		sites[data.site_id].ttyyp_id = data.content_template_id;
		    		
					if(data.is_default)
					{
						for(var i in sites) if(i != data.site_id)
						{
							sites[i].on_default = '0';
						}
						
						
						$('td.is_site_default_cell').empty();
						$('tr#site_listing_' + data.site_id + ' td.is_site_default_cell').html('<input type="checkbox" disabled="disabled" checked="checked" />');
					}
				
					$('tr#site_listing_' + data.site_id + ' td.site_name_cell').text(data.name);
					$('tr#site_listing_' + data.site_id + ' td.site_extension_cell').text(data.extension);
					$('tr#site_listing_' + data.site_id + ' td.site_url_cell').text(data.site_url);
					$('tr#site_listing_' + data.site_id + ' td.site_encoding_cell').text($("select#site_encoding option[value='" + data.encoding + "']").html());
					$('tr#site_listing_' + data.site_id + ' td.site_glossary_cell').text($("select#site_glossary_id option[value='" + data.glossary_id + "']").html());
					$('tr#site_listing_' + data.site_id + ' td.site_page_template_cell').text($("select#site_page_template_id option[value='" + data.page_template_id + "']").html());
					$('tr#site_listing_' + data.site_id + ' td.site_content_template_cell').text($("select#site_content_template_id option[value='" + data.content_template_id + "']").html());
		    		
					$('tr#site_listing_' + data.site_id).removeClass('hidden');
					
					clearSiteForm();
					$('tr#site_edit_row').addClass('hidden');
					
					$('input#save_site_button').addClass('hidden');
					$('input#cancel_site_button').addClass('hidden');
					$('input#create_site_button').removeClass('hidden');
					
		    		fadingMessage(data.name + ' <?php echo $site->sys_sona(array('sona' => 'site_changed', 'tyyp' => 'admin')); ?>');
		    	}
		    }
		});				
	});
	
	$('input#cancel_site_button').click(function ()
	{
		var site_id = $('input#site_id').attr('value');
		
		$('input#save_site_button').addClass('hidden');
		$('input#cancel_site_button').addClass('hidden');
		
		$('tr#site_edit_row').addClass('hidden');
		
		$('input#create_site_button').removeClass('hidden');
		$('tr#site_listing_' + site_id).removeClass('hidden');
		
		clearSiteForm();
	});
	
	$('input#create_site_button').click(function ()
	{
		$('tr#site_create_new_row').removeClass('hidden');
		
		$('input#create_site_button').addClass('hidden');
		$('input#new_site_cancel_button').removeClass('hidden');
		$('input#new_site_create_button').removeClass('hidden');
		
	});
	
	$('input#new_site_cancel_button').click(function ()
	{
		$('tr#site_create_new_row').addClass('hidden');
		
		$('input#new_site_cancel_button').addClass('hidden');
		$('input#new_site_create_button').addClass('hidden');
		$('input#create_site_button').removeClass('hidden');
	});

	$('input#new_site_create_button').click(function ()
	{
		var data = {
	    	op: 'create_new_site',
	    	name: $('input#new_site_name').attr('value'),
	    	extension: $('input#new_site_extension').attr('value'),
	    	site_url: $('input#new_site_url').attr('value'),
	    	encoding: $('select#new_site_encoding').attr('value'),
	    	glossary_id: $('select#new_site_glossary_id').attr('value'),
	    	page_template_id: $('select#new_site_page_template_id').attr('value'),
	    	content_template_id: $('select#new_site_content_template_id').attr('value')
		}
		
		if(data.name.length == 0 || data.extension.length == 0 || data.encoding.length == 0 || data.glossary_id.length == 0)
		{
			messageDialog('<?php echo $site->sys_sona(array('sona' => 'site_name_extension_encoding_glossary_required', 'tyyp' => 'admin')); ?>');
		}
		else
		{
			for(var i in sites) if(sites[i].extension == data.extension)
			{
				messageDialog('<?php echo $site->sys_sona(array('sona' => 'site_extension_must_be_unique', 'tyyp' => 'admin')); ?>');
				return;
			}
			
			$('div#scms_content_cover').removeClass('hidden');
			
			$.extend(data, ajax_token);
			
			$.ajax({
			    url: site_url + '/admin/ajax_response.php',
			    cache: false,
			    data: data,
			    type: 'POST',
			    dataType: 'json',
			    timeout: 10000,
			    error: function(XMLHttpRequest, textStatus, errorThrown)
			    {
			    	alert(textStatus);
					$('div#scms_content_cover').addClass('hidden');
			    },
			    success: function(response, textStatus)
			    {
			    	if(response.error)
			    	{
			    		messageDialog('Error: ' + response.error_message);
			    	}
			    	else
			    	{
			    		sites[response.site_data.site_id] = {
				    		nimi: response.site_data.name,
				    		on_default: 0,
				    		extension: response.site_data.extension,
				    		site_url: response.site_data.site_url,
				    		encoding: response.site_data.encoding,
				    		glossary_id: response.site_data.glossary_id,
				    		page_ttyyp_id: response.site_data.page_template_id,
				    		ttyyp_id: response.site_data.content_template_id
			    		}
			    		
						
			    		var site_row_html = '<tr id="site_listing_' + response.site_data.site_id + '" class="site_listing_row"><td class="site_delete_button_cell invisible"><img class="site_delete_button" id="site_delete_button_' + response.site_data.site_id + '" src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete.png" width="16" height="16" alt="<?php echo $site->sys_sona(array('sona' => 'site_delete', 'tyyp' => 'admin')); ?>" title="<?php echo $site->sys_sona(array('sona' => 'site_delete', 'tyyp' => 'admin')); ?>" /></td><td class="is_site_default_cell"></td><td class="site_name_cell">' + response.site_data.name + '</td><td class="site_extension_cell">' + response.site_data.extension + '</td><td class="site_page_template_cell">' + $("select#site_page_template_id option[value='" + response.site_data.page_template_id + "']").html() + '</td><td class="site_content_template_cell">' + $("select#site_content_template_id option[value='" + response.site_data.content_template_id + "']").html() + '</td><td class="site_encoding_cell">' + response.site_data.encoding + '</td><td class="site_glossary_cell">' + $("select#site_glossary_id option[value='" + response.site_data.glossary_id + "']").html() + '</td><td class="site_url_cell">' + response.site_data.site_url + '</td></tr>';
			    		
			    		$('tr#site_create_new_row').addClass('hidden');
			    		
			    		$('tr#site_create_new_row').after(site_row_html);
						
						$('input#new_site_cancel_button').addClass('hidden');
						$('input#new_site_create_button').addClass('hidden');
						$('input#create_site_button').removeClass('hidden');
						
			    		fadingMessage(response.site_data.name + ' <?php echo $site->sys_sona(array('sona' => 'site_created', 'tyyp' => 'admin')); ?>');
			    	}
			    }
			});
		}
	});
	
	setContentDimensions();
	
	$(window).resize(function()
	{
		setContentDimensions();
	});
});

function clearSiteForm()
{
	$('input#site_id').attr('value', '');
	$('input#is_site_default').removeAttr('checked');
	$('input#site_name').attr('value', '');
	$('input#site_extension').attr('value', '');
	$('input#site_url').attr('value', '');
	$('select#site_encoding option:selected').removeAttr('selected');
	$('select#site_glossary_id option:selected').removeAttr('selected');
	$('select#site_page_template_id option:selected').removeAttr('selected');
	$('select#site_content_template_id option:selected').removeAttr('selected');
}

function setContentDimensions()
{
	// set content height
	$('div#scms_content_cover').height($(window).height());
	
	// set content height
	$('div#scms_content_body').height($(window).height() - $('div#scms_header_bar').height() - (8 + 2 + 2)); // paddings and borders need to be added
}

function fadingMessage(message)
{
   	$('div#scms_content_cover').addClass('hidden');
	
	$('div#scms_message').removeClass('hidden');
	
	$('td#text_cell').text(message);
	
	$('td#close_message_cell').click(function () {
		$('div#scms_message').addClass('hidden');
	});
	
	$('td#close_message_cell').click(function ()
	{
		$('div#scms_message').addClass('hidden');
		
		$(this).unbind('click');
		
	});

	var delayTimer;
	
	if(delayTimer)
	{
		clearTimeout(delayTimer);
		delayTimer = null;
	}
	
	delayTimer = setTimeout(function ()
	{
		$('div#scms_message').addClass('hidden');
		
		$(this).unbind('click');
	}, 4000);	
}

function messageDialog(message)
{
   	$('div#scms_content_cover').removeClass('hidden');
	
	$('div#scms_dialog, input#message_ok_button').removeClass('hidden');
	
	$('td#message_cell').text(message);
	
	$('input#message_ok_button').click(function ()
	{
		$('div#scms_dialog, input#message_ok_button').addClass('hidden');
		
		$('input#message_ok_button').unbind('click');
		
	   	$('div#scms_content_cover').addClass('hidden');
	});
}

function confirmDialog(question, ok_handler)
{
	$('div#scms_content_cover').removeClass('hidden');
	
	$('div#scms_dialog, input#message_ok_button, input#message_cancel_button').removeClass('hidden');
	
	$('td#message_cell').text(question);
	
	$('input#message_ok_button').click(function ()
	{
		hideConfirmDialog();
		ok_handler();
	});
	
	$('input#message_cancel_button').click(hideConfirmDialog);
}

function hideConfirmDialog()
{
	$('div#scms_dialog, input#message_ok_button, input#message_cancel_button').addClass('hidden');
	
	$('input#message_ok_button, input#message_cancel_button').unbind('click');
	
   	$('div#scms_content_cover').addClass('hidden');
}


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
					<input id="message_ok_button" type="button" value="<?php echo $site->sys_sona(array('sona' => 'ok', 'tyyp' => 'admin')); ?>" class="button hidden" />
					<input id="message_cancel_button" type="button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'admin')); ?>" class="cancel hidden" />
				</td>
			</tr>
		</table>
	</div><!-- / scms_dialog -->
	
	<div id="scms_message" class="hidden">
		<table cellpadding="0" cellspacing="0" id="scms_message_box">
			<tr>
				<td id="text_cell"></td>
				<td id="close_message_cell"></td>
			</tr>
		</table>
	</div><!-- / scms_dialog -->
	
	<div id="scms_header_bar">
		
		<input type="button" id="create_site_button" value="<?php echo $site->sys_sona(array('sona' => 'create_sub_site', 'tyyp' => 'admin')); ?>" class="button" />
		
		<input type="button" id="save_site_button" value="<?php echo $site->sys_sona(array('sona' => 'save', 'tyyp' => 'admin')); ?>" class="button hidden" />
		<input type="button" id="cancel_site_button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'admin')); ?>" class="cancel hidden" />
		
		<input type="button" id="new_site_create_button" value="<?php echo $site->sys_sona(array('sona' => 'save', 'tyyp' => 'admin')); ?>" class="button hidden" />
		<input type="button" id="new_site_cancel_button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'admin')); ?>" class="cancel hidden" />
		
	</div><!-- / scms_header_bar -->
	
	<div id="scms_content_body">
		
		<div id="scms_sites_listing">
	
		<table cellpadding="0" cellspacing="0" id="sites_listing">
			
			<thead>
				<tr>
					<td></td>
					<td><img src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/inweb.png" width="16" height="16" border="0" alt="<?php echo $site->sys_sona(array('sona' => 'Vaikimisi_veebis', 'tyyp' => 'editor')); ?>" title="<?php echo $site->sys_sona(array('sona' => 'Vaikimisi_veebis', 'tyyp' => 'editor')); ?>" /></td>
					<td><?php echo $site->sys_sona(array('sona' => 'name', 'tyyp' => 'admin')); ?></td>
					<td><?php echo $site->sys_sona(array('sona' => 'Luhend', 'tyyp' => 'editor')); ?></td>
					<td><?php echo $site->sys_sona(array('sona' => 'Page template', 'tyyp' => 'editor')); ?></td>
					<td><?php echo $site->sys_sona(array('sona' => 'Content template', 'tyyp' => 'editor')); ?></td>
					<td><?php echo $site->sys_sona(array('sona' => 'Encoding', 'tyyp' => 'editor')); ?></td>
					<td><?php echo $site->sys_sona(array('sona' => 'translations', 'tyyp' => 'admin')); ?></td>
					<td><?php echo $site->sys_sona(array('sona' => 'URL', 'tyyp' => 'editor')); ?></td>
				</tr>
			</thead>
			
			<tbody>
				<tr id="site_edit_row" class="hidden">
					<td></td>
					<td><input id="site_id" type="hidden" /><input id="is_site_default" type="checkbox" value="1" /></td>
					<td><input id="site_name" type="text" class="text" value="" /></td>
					<td><input id="site_extension" type="text" class="text_abbr" value="" /></td>
					<td>
						<select id="site_page_template_id" class="select">
							<?php print_template_selectbox(0, 'page'); ?>
						</select>
					</td>
					<td>
						<select id="site_content_template_id" class="select">
							<?php print_template_selectbox(0, 'content'); ?>
						</select>
					</td>
					<td>
						<select id="site_encoding" class="select">
						<?php foreach($encodings as $encoding) { ?>
							<option value="<?php echo $encoding ?>"><?php echo $encoding; ?></option>
						<?php } ?>
						</select>
					</td>
					<td>
						<select id="site_glossary_id" class="select">
						<?php foreach($glossaries as $glossary) { ?>
							<option value="<?php echo $glossary['glossary_id'] ?>"><?php echo $glossary['nimi']; ?></option>
						<?php } ?>
						</select>
					</td>
					<td><input id="site_url" type="text" class="text" value="" /></td>
				</tr>
				<tr id="site_create_new_row" class="hidden">
					<td></td>
					<td></td>
					<td><input id="new_site_name" type="text" class="text" value="" /></td>
					<td><input id="new_site_extension" type="text" class="text_abbr" value="" /></td>
					<td>
						<select id="new_site_page_template_id" class="select">
							<?php print_template_selectbox($default_page_template_id, 'page'); ?>
						</select>
					</td>
					<td>
						<select id="new_site_content_template_id" class="select">
							<?php print_template_selectbox($default_content_template_id, 'content'); ?>
						</select>
					</td>
					<td>
						<select id="new_site_encoding" class="select">
						<?php foreach($encodings as $encoding) { ?>
							<option value="<?php echo $encoding ?>"<?php echo ($encoding == 'UTF-8' ? ' selected="selected"' : ''); ?>><?php echo $encoding; ?></option>
						<?php } ?>
						</select>
					</td>
					<td>
						<select id="new_site_glossary_id" class="select">
						<?php foreach($glossaries as $glossary) { ?>
							<option value="<?php echo $glossary['glossary_id'] ?>"><?php echo $glossary['nimi']; ?></option>
						<?php } ?>
						</select>
					</td>
					<td><input id="new_site_url" type="text" class="text" value="" /></td>
				</tr>
			<?php foreach($sites as $site_data) { ?>
				<tr id="site_listing_<?php echo $site_data['keel_id']; ?>" class="site_listing_row">
					<td class="site_delete_button_cell invisible"><?php if($site->user->is_superuser) { ?><img class="site_delete_button" id="site_delete_button_<?php echo $site_data['keel_id']; ?>" src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete.png" width="16" height="16" alt="<?php echo $site->sys_sona(array('sona' => 'site_delete', 'tyyp' => 'admin')); ?>" title="<?php echo $site->sys_sona(array('sona' => 'site_delete', 'tyyp' => 'admin')); ?>" /><?php } ?></td>
					<td class="is_site_default_cell"><?php echo ($site_data['on_default'] ? '<input type="checkbox" disabled="disabled" checked="checked" />' : ''); ?></td>
					<td class="site_name_cell"><?php echo $site_data['nimi']; ?></td>
					<td class="site_extension_cell"><?php echo $site_data['extension']; ?></td>
					<td class="site_page_template_cell"><?php echo $site_data['page_template_name']; ?></td>
					<td class="site_content_template_cell"><?php echo $site_data['content_template_name']; ?></td>
					<td class="site_encoding_cell"><?php echo $site_data['encoding']; ?></td>
					<td class="site_glossary_cell"><?php echo $site_data['glossary']; ?></td>
					<td class="site_url_cell"><?php echo $site_data['site_url']; ?></td>
				</tr>
			<?php } ?>
			</tbody>
		
		</table>
	
		</div><!-- / scms_sites_listing -->
		
	</div><!-- / scms_content_body -->
	
</body>

</html>