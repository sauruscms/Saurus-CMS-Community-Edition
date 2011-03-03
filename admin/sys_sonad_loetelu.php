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

// return from translation editing
$_SESSION['scms_return_to'] = $_SERVER['REQUEST_URI'];

$json_encoder = new Services_JSON();

$sites = array();

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

$sql = "select distinct keel.keel_id, keel.nimi, keel.on_default_admin, keel.encoding, keel.locale from keel left join sys_sonad on keel.keel_id = sys_sonad.keel where sys_sonad.keel is not null and keel.keel_id < 500 order by keel.nimi";
$result = new SQL($sql);
while($row = $result->fetch('ASSOC'))
{
	$glossaries[$row['keel_id']] = $row;
}

$all_glossaries = array();

$sql = "select keel_id, nimi, on_default_admin, locale from keel where keel_id < 500 order by nimi";
$result = new SQL($sql);
while($row = $result->fetch('ASSOC'))
{
	$all_glossaries[$row['keel_id']] = $row;
}

# Sys sõnade tüübid
$sql = $site->db->prepare("SELECT sys_sona_tyyp.sst_id, sys_sona_tyyp.nimi 
	FROM sys_sona_tyyp
	ORDER BY sys_sona_tyyp.nimi"
);
$sth = new SQL($sql);
$site->debug->msg($sth->debug->get_msgs());

$glossary_word_types = array();

while ($sst = $sth->fetch('ASSOC'))
{
	$glossary_word_types[] = $sst;
}

$sst_id = ($site->fdat['sst_id'] ? $site->fdat['sst_id'] : $glossary_word_types[0]['sst_id']);

if(is_numeric($site->fdat['flt_keel']))
{
	if ($site->fdat['filter'])
	{
		$otsi = mysql_real_escape_string($site->fdat['filter']);
		$sst_id = 0;
	}
	$otsi = $otsi ? preg_replace('/%/', '\\%', $otsi) : '';
	
	$keel_id = $site->fdat['flt_keel'];
	
	$otsi = $otsi ? " (sys_sonad_kirjeldus.sona LIKE '%".$otsi."%' OR sys_sonad.sona LIKE '%".$otsi."%' OR sys_sonad.origin_sona LIKE '%".$otsi."%' OR sys_sonad.sys_sona LIKE '%".$otsi."%' OR sys_sonad_kirjeldus.sys_sona LIKE '%".$otsi."%') " : " sys_sonad.sst_id=".$sst_id;
	$where_str = $site->db->prepare(" WHERE sys_sonad.keel=? AND ".$otsi." ",
		$keel_id,
		1
	);
	
	$from_sql = " FROM sys_sonad
		LEFT JOIN sys_sona_tyyp ON sys_sonad.sst_id = sys_sona_tyyp.sst_id
		LEFT JOIN sys_sonad_kirjeldus ON sys_sonad.sst_id=sys_sonad_kirjeldus.sst_id and sys_sonad.sys_sona=sys_sonad_kirjeldus.sys_sona";
	
	########### ORDER
	$order = " ORDER BY sys_sonad.origin_sona "; # Bug 1904
	
	########### SQL
	
	$sql = $site->db->prepare("SELECT sys_sonad_kirjeldus.sys_sona, sys_sonad.id, sys_sonad.sona, sys_sonad.origin_sona, sys_sonad.sys_sona as hint_sona, sys_sona_tyyp.nimi,sys_sonad_kirjeldus.sst_id, sys_sonad_kirjeldus.sona as kirjeldus");
	$sql .= $from_sql;
	$sql .= $where_str;
	$sql .= $order;
	
	//printr($sql);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
	
	$words = array();
	###########################
	# loop over rows
	while ( $mysona = $sth->fetch('ASSOC') )
	{
		$words[] = $mysona;
	}
}

function can_user_change_translation($sst_id)
{
	return 1;
}

$adminpage_names = get_adminpage_name(array('script_name' => $site->script_name));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>

<title><?php echo $site->sys_sona(array('sona' => 'translations', 'tyyp' => 'admin')); ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $site->encoding; ?>" />

<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/glossary.css" />

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

	var site_url = '<?php echo (empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'];?>';
	var ajax_token = <?php echo create_form_token_json('filemanager'); ?>;
	var glossaries = <?php echo $json_encoder->encode($glossaries); ?>;
	var allGlossaries = <?php echo $json_encoder->encode($all_glossaries); ?>;
	
</script>

<script type="text/javascript">
$(document).ready(function()
{
	make_breadcrumb('<?php echo $adminpage_names['parent_pagename'];?>', '<?php echo $adminpage_names['pagename']; ?>');
	
	$('tr.glossary_row').live('mouseover', function ()
	{
		var glossary_id = $(this).attr('id').replace('glossary_row_', '');
		
		if(glossaries[glossary_id].on_default_admin == '0') $(this).children('td.glossary_delete_button_cell').removeClass('invisible');
		$(this).children('td.glossary_edit_button_cell').removeClass('invisible');
		
		$(this).addClass('selected');
	});

	$('tr.glossary_row').live('mouseout', function ()
	{
		$(this).children('td.glossary_delete_button_cell').addClass('invisible');
		$(this).children('td.glossary_edit_button_cell').addClass('invisible');
		
		$(this).removeClass('selected');
	});
	
	$('tr.word_row').live('mouseover', function ()
	{
		$(this).children('td.word_delete_button_cell').removeClass('invisible');
		$(this).addClass('selected');
	});

	$('tr.word_row').live('mouseout', function ()
	{
		$(this).children('td.word_delete_button_cell').addClass('invisible');
		$(this).removeClass('selected');
	});
	
	$('tr.word_row').live('click', function ()
	{
		var word_id = $(this).attr('id').replace('word_row_', '');
		
		var href = site_url + '/admin/edit_translation.php?op=edit&word_id=' + word_id;
		
		window.location.replace(href);
	});
	
	$('input#add_glossary_word').live('click', function ()
	{
		var sst_id = $('select#glossary_word_type').attr('value');
		
		var href = site_url + '/admin/edit_translation.php?op=new&sst_id=' + sst_id;
		
		window.location.replace(href);
	});
	
	$('select#new_glossary_id').change(function ()
	{
		if(allGlossaries[$(this).attr('value')].locale.length) $('input#new_glossary_locale').attr('value', allGlossaries[$(this).attr('value')].locale);
	});
	
	$('input#create_glossary_button').click(function ()
	{
		$('div#scms_header_bar').children('div').addClass('hidden')
		$('div#create_glossary_form_buttons').removeClass('hidden');
		
		$('tr#glossary_row_create').removeClass('hidden');
	});
	
	$('input#create_glossary_cancel_button').click(function ()
	{
		$('tr#glossary_row_create').addClass('hidden');
		
		$('div#scms_header_bar').children('div').addClass('hidden')
		
		$('div#action_buttons').removeClass('hidden');
	});

	$('input#create_glossary_save_button').click(function ()
	{
		$('div#scms_content_cover').removeClass('hidden');
		
		var data = {
	    	op: 'create_glossary',
	    	keel_id: $('select#new_glossary_id').attr('value'),
	    	locale: $('input#new_glossary_locale').attr('value'),
	    	encoding: $('select#new_glossary_encoding').attr('value')
		}
		
		if(glossaries[data.keel_id])
		{
			messageDialog('"' + glossaries[data.keel_id].nimi + '" <?php echo $site->sys_sona(array('sona' => 'glossary_exists', 'tyyp' => 'admin')); ?>');
			return;
		}
		
		if(data.keel_id.length == 0 || data.encoding.length == 0)
		{
			messageDialog('<?php echo $site->sys_sona(array('sona' => 'glossary_name_and_encoding_required', 'tyyp' => 'admin')); ?>');
			return;
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
		    		glossaries[response.glossary.keel_id] = {
			    		keel_id: response.glossary.keel_id,
			    		nimi: allGlossaries[response.glossary.keel_id].nimi,
			    		on_default_admin: 0,
			    		locale: response.glossary.locale,
			    		encoding: response.glossary.encoding
		    		}
		    		
		    		allGlossaries[response.glossary.keel_id].locale = response.glossary.locale;
		    		
		    		$("select#new_glossary_id option[value='" + response.glossary.keel_id + "']").attr('disabled', 'disabled');
		    		$('select#new_glossary_id').attr('value', '');
		    		$('input#new_glossary_locale').attr('value', '');
					
		    		var glossary_row_html = '<tr id="glossary_row_' + response.glossary.keel_id + '" class="glossary_row"><td class="glossary_delete_button_cell invisible"><img class="glossary_delete_button" id="glossary_delete_button_' + response.glossary.keel_id + '" src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete.png" width="16" height="16" alt="<?php echo $site->sys_sona(array('sona' => 'glossary_delete', 'tyyp' => 'admin')); ?>" title="<?php echo $site->sys_sona(array('sona' => 'glossary_delete', 'tyyp' => 'admin')); ?>" /></td><td class="glossary_edit_button_cell invisible"><img class="glossary_edit_button" id="glossary_edit_button_' + response.glossary.keel_id + '" src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/edit.png" width="16" height="16" alt="<?php echo $site->sys_sona(array('sona' => 'glossary_edit', 'tyyp' => 'admin')); ?>" title="<?php echo $site->sys_sona(array('sona' => 'glossary_edit', 'tyyp' => 'admin')); ?>" /></td><td class="on_default_admin_cell"></td><td class="glossary_name_cell">' + allGlossaries[response.glossary.keel_id].nimi + '</td><td class="glossary_locale_cell">' + response.glossary.locale + '</td><td class="glossary_encoding_cell">' + response.glossary.encoding + '</td></tr>';
		    		
		    		$('tr#glossary_row_create').addClass('hidden');
		    		
		    		$('tr#glossary_row_create').after(glossary_row_html);
					
					$('div#scms_header_bar').children('div').addClass('hidden')
					
					$('div#action_buttons').removeClass('hidden');
					
		    		fadingMessage('"' + allGlossaries[response.glossary.keel_id].nimi + '" <?php echo $site->sys_sona(array('sona' => 'glossary_created', 'tyyp' => 'admin')); ?>');
		    	}
		    }
		});		
		
	});

	$('img.glossary_delete_button').live('click', function ()
	{
		var glossary_id = $(this).attr('id').replace('glossary_delete_button_', '');
		
		var data = {
	    	op: 'get_glossary_usage',
	    	glossary_id: glossary_id
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
				var confirmText = (response.sites.length > 0 ? glossaries[glossary_id].nimi + ' <?php echo $site->sys_sona(array('sona' => 'glossary_is_used_by', 'tyyp' => 'admin')); ?>: ' + response.sites.join(', ') + '. <?php echo $site->sys_sona(array('sona' => 'glossary_delete_confirm', 'tyyp' => 'admin')); ?>?' : '<?php echo $site->sys_sona(array('sona' => 'glossary_delete_confirm', 'tyyp' => 'admin')); ?>: "' + glossaries[glossary_id].nimi + '"?');
		    	
		    	confirmDialog(confirmText , function ()
				{
					$('div#scms_content_cover').removeClass('hidden');
					
					var data = {
				    	op: 'remove_glossary',
				    	glossary_id: glossary_id
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
					    		$('tr#glossary_row_' + glossary_id).remove();
					    		
					    		$("select#new_glossary_id option[value='" + glossary_id + "']").removeAttr('disabled');
					    		
					    		fadingMessage('"' + glossaries[glossary_id].nimi + '" <?php echo $site->sys_sona(array('sona' => 'glossary_removed', 'tyyp' => 'admin')); ?>');
					    		
					    		delete glossaries[glossary_id];
					    	}
					    }
					});
				});
		    }
		});

		return false;	// stop click bubbling/propagation
	});
	
	$('img.word_delete_button').live('click', function ()
	{
		var word_id = $(this).attr('id').replace('word_delete_button_', '');
		
		var data = {
	    	op: 'delete_sys_word',
	    	word_id: word_id
		}
		
		$('div#scms_content_cover').removeClass('hidden');
		
    	confirmDialog('<?php echo $site->sys_sona(array('sona' => 'glossary_translation_delete_confirm', 'tyyp' => 'admin')); ?>' , function ()
		{
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
			    		messageDialog(response.error_message);
			    	}
			    	else
			    	{
			    		$('tr#word_row_' + word_id).remove();
						$('div#scms_content_cover').addClass('hidden');
						fadingMessage('<?php echo $site->sys_sona(array('sona' => 'glossary_translation_deleted', 'tyyp' => 'admin')); ?>');
			    	}
			    }
			});
		});
				
		return false;	// stop click bubbling/propagation
	});
	
	$('img.glossary_edit_button').live('click', function ()
	{
		var glossary_id = $(this).attr('id').replace('glossary_edit_button_', '');
		
		$('tr#glossary_row_create').addClass('hidden');
		
		$('div#scms_header_bar').children('div').addClass('hidden')
		
		var edit_row = $('tr#glossary_row_edit').insertAfter('tr#glossary_row_' + glossary_id);
		
		if(glossaries[glossary_id].on_default_admin != '0')
		{
			$('input#edit_glossary_is_default').attr('checked', 'checked');
			$('input#edit_glossary_is_default').attr('disabled', 'disabled');
		}
		else
		{
			$('input#edit_glossary_is_default').removeAttr('checked');
			$('input#edit_glossary_is_default').removeAttr('disabled');
		}
		
		$('td#edit_glossary_name_cell').text(glossaries[glossary_id].nimi);
		$('input#edit_glossary_locale').attr('value', glossaries[glossary_id].locale);
		
		$('select#edit_glossary_encoding option:selected').removeAttr('selected');
		$("select#edit_glossary_encoding option[value='" + glossaries[glossary_id].encoding + "']").attr('selected', 'selected');
		
		$('tr#glossary_row_' + $('input#edit_glossary_id').attr('value')).removeClass('hidden');
		
		$('tr#glossary_row_' + glossary_id).addClass('hidden');
		
		$('div#edit_glossary_form_buttons').removeClass('hidden');
		
		$('input#edit_glossary_id').attr('value', glossary_id);
		
		$(edit_row).removeClass('hidden');
		
		return false;	// stop click bubbling/propagation
	});
	
	$('input#edit_glossary_cancel_button').click(function ()
	{
		$('tr#glossary_row_edit').addClass('hidden');
		$('tr#glossary_row_' + $('input#edit_glossary_id').attr('value')).removeClass('hidden');
		
		$('div#scms_header_bar').children('div').addClass('hidden');
		$('div#action_buttons').removeClass('hidden');
		
	});
	
	$('input#edit_glossary_save_button').click(function ()
	{
		$('div#scms_content_cover').removeClass('hidden');
		
		var glossary_id = $('input#edit_glossary_id').attr('value');
		
		var data = {
	    	op: 'edit_glossary_settings',
	    	glossary_id: glossary_id,
	    	on_default_admin: ($('input#edit_glossary_is_default').attr('checked') ? 1 : 0),
	    	locale: $('input#edit_glossary_locale').attr('value'),
	    	encoding: $('select#edit_glossary_encoding').attr('value')
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
		    		glossaries[glossary_id].on_default_admin = data.on_default_admin;
		    		glossaries[glossary_id].locale = data.locale;
		    		glossaries[glossary_id].encoding = data.encoding;
		    		
					if(data.on_default_admin)
					{
						for(var i in glossaries) if(i != data.glossary_id)
						{
							glossaries[i].on_default_admin = '0';
						}
						
						
						$('td.on_default_admin_cell').empty();
						$('tr#glossary_row_' + glossary_id + ' td.on_default_admin_cell').html('<input type="checkbox" disabled="disabled" checked="checked" />');
					}
				
					$('tr#glossary_row_' + glossary_id + ' td.glossary_locale_cell').text(data.locale);
					$('tr#glossary_row_' + glossary_id + ' td.glossary_encoding_cell').text(data.encoding);
		    		
					$('tr#glossary_row_' + glossary_id).removeClass('hidden');
					
					$('tr#glossary_row_edit').addClass('hidden');
					
					$('div#scms_header_bar').children('div').addClass('hidden')
					$('div#action_buttons').removeClass('hidden');
					
		    		fadingMessage('"' + glossaries[glossary_id].nimi + '" <?php echo $site->sys_sona(array('sona' => 'glossary_changed', 'tyyp' => 'admin')); ?>');
		    	}
		    }
		});				
	});
	
	$('input#import_glossary_button').click(function ()
	{
		if($('tr.glossary_row.pre_selected').length == 1)
		{
			var glossary_id = $('tr.glossary_row.pre_selected').attr('id').replace('glossary_row_', '');
		}
		else
		{
			var glossary_id = null;
		}
		
		openpopup(site_url + '/admin/lang_file.php?op=import' + (glossary_id != null ? '&flt_keel=' + glossary_id : ''),'langfile','466','400');
	});
	
	$('input#export_glossary_button').click(function ()
	{
		if($(this).hasClass('button'))
		{
			openpopup(site_url + '/admin/lang_file.php?op=export&flt_keel=<?php echo $site->fdat['flt_keel']; ?>','langfile','466','400');
		}
	});
	
	$('tr.glossary_row').live('click', function ()
	{
		var glossary_id = $(this).attr('id').replace('glossary_row_', '');
		
		var href = window.document.location.href;
		
		if(href.match(/flt_keel[0..9]*(?:=[^&]*)/))
		{
			href = href.replace(/flt_keel[0..9]*(?:=[^&]*)/, 'flt_keel=' + glossary_id);
		}
		else
		{
			href += (href.match(/\?/) ? '&' : '?') + 'flt_keel=' + glossary_id;
		}
		
		window.location.replace(href);
	});
	
	$('div#search_start').click(function ()
	{
		$('input#search_text').focus();
		
		if($('input#search_text').attr('value') != '<?php echo $site->sys_sona(array('sona' => 'otsi', 'tyyp' => 'otsing')); ?>' + ': ')
		{
			submitSearch();
		}
	});

	$('div#search_clear').click(function ()
	{
		$('input#search_text').attr('value', '<?php echo $site->sys_sona(array('sona' => 'otsi', 'tyyp' => 'otsing')); ?>' + ': ');
		
		$('div#search_start').removeClass('hidden');
		$('div#search_clear').addClass('hidden');
		
		var href = window.document.location.href;
		
		if(href.match(/filter[0..9]*(?:=[^&]*)/))
		{
			href = href.replace(/filter[0..9]*(?:=[^&]*)/, '');
			href = href.replace(/&$/, '');
			href = href.replace(/&&/, '');
		}
		
		window.location.replace(href);
	});

	$('input#search_text').focus(function ()
	{
		if(this.value == '<?php echo $site->sys_sona(array('sona' => 'otsi', 'tyyp' => 'otsing')); ?>' + ': ') this.value = '';
	});
	
	$('input#search_text').blur(function ()
	{
		if(this.value == '') this.value = '<?php echo $site->sys_sona(array('sona' => 'otsi', 'tyyp' => 'otsing')); ?>' + ': ';
	});
	
	$('input#search_text').keypress(function (event)
	{
		$('div#search_start').removeClass('hidden');
		$('div#search_clear').addClass('hidden');
		
		// enter
		if(event.which == 13)
		{
			submitSearch();
		}
		// esc = 0
	});
	
	$('select#glossary_word_type').change(function ()
	{
		var sst_id = $(this).attr('value');
		
		var href = window.document.location.href;
		
		if(href.match(/filter[0..9]*(?:=[^&]*)/))
		{
			href = href.replace(/filter[0..9]*(?:=[^&]*)/, '');
			href = href.replace(/&$/, '');
			href = href.replace(/&&/, '');
		}
		
		if(href.match(/sst_id[0..9]*(?:=[^&]*)/))
		{
			href = href.replace(/sst_id[0..9]*(?:=[^&]*)/, 'sst_id=' + sst_id);
		}
		else
		{
			href += (href.match(/\?/) ? '&' : '?') + 'sst_id=' + sst_id;
		}
		
		window.location.replace(href);
	});
	
	setContentDimensions();
	
	$(window).resize(function()
	{
		setContentDimensions();
	});
});

function submitSearch()
{
	var searchText = $('input#search_text').attr('value');
	
	if(searchText.length > 0)
	{
		$('div#search_start').addClass('hidden');
		$('div#search_clear').removeClass('hidden');
		
		var href = window.document.location.href;
		
		if(href.match(/sst_id[0..9]*(?:=[^&]*)/))
		{
			href = href.replace(/sst_id[0..9]*(?:=[^&]*)/, 'filter=' + searchText);
		}
		else
		{
			href += (href.match(/\?/) ? '&' : '?') + 'filter=' + searchText;
		}
		
		window.location.replace(href);
	}
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

function setContentDimensions()
{
	// set content height
	$('div#scms_content_cover').height($(window).height());
	
	// set content height
	<?php if(is_numeric($site->fdat['flt_keel'])) { ?>
	$('div#scms_content_body').height($(window).height() - $('div#scms_header_bar').height() - $('div#scms_header_content').height() - $('div#scms_content_header_bar').height() - (8 + 2 + 2 + 8)); // paddings and borders need to be added
	<?php } else { ?>
	$('div#scms_header_content').height($(window).height() - $('div#scms_header_bar').height() - (8 + 2 + 2)); // paddings and borders need to be added
	<?php } ?>
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
				<td id="text_cell">Site has been saved</td>
				<td id="close_message_cell">&nbsp;</td>
			</tr>
		</table>
	</div><!-- / scms_dialog -->
	
	<div id="scms_header_bar">
		
		<div id="action_buttons">
			<input type="button" id="create_glossary_button" value="<?php echo $site->sys_sona(array('sona' => 'create_glossary', 'tyyp' => 'admin')); ?>" class="button" />
			<input type="button" id="import_glossary_button" value="<?php echo $site->sys_sona(array('sona' => 'import_glossary', 'tyyp' => 'admin')); ?>" class="button" />
			<input type="button" id="export_glossary_button" value="<?php echo $site->sys_sona(array('sona' => 'export_glossary', 'tyyp' => 'admin')); ?>" class="<?php echo (is_numeric($site->fdat['flt_keel']) ? 'button' : 'disabled_button'); ?>" />
		</div>
		
		<div id="create_glossary_form_buttons" class="hidden">
			<input type="button" id="create_glossary_save_button" value="<?php echo $site->sys_sona(array('sona' => 'save', 'tyyp' => 'admin')); ?>" class="button" />
			<input type="button" id="create_glossary_cancel_button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'admin')); ?>" class="cancel" />
		</div>
		
		<div id="edit_glossary_form_buttons" class="hidden">
			<input type="button" id="edit_glossary_save_button" value="<?php echo $site->sys_sona(array('sona' => 'save', 'tyyp' => 'admin')); ?>" class="button" />
			<input type="button" id="edit_glossary_cancel_button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'admin')); ?>" class="cancel" />
		</div>
		
	</div><!-- / scms_header_bar -->
	
	<div id="scms_header_content">
	
		<div id="glossaries_container">
			
			<table cellpadding="0" cellspacing="0" id="glossaries_list" class="data_table">
			
				<thead>
				
					<tr>
						<td></td>
						<td></td>
						<td><img src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/inadmin.png" width="16" height="16" border="0" alt="<?php echo $site->sys_sona(array('sona' => 'Vaikimisi_editoris', 'tyyp' => 'editor')); ?>" title="<?php echo $site->sys_sona(array('sona' => 'Vaikimisi_editoris', 'tyyp' => 'editor')); ?>"></td>
						<td><?php echo $site->sys_sona(array('sona' => 'translations', 'tyyp' => 'admin')); ?></td>
						<td><?php echo $site->sys_sona(array('sona' => 'locale', 'tyyp' => 'admin')); ?></td>
						<td><?php echo $site->sys_sona(array('sona' => 'Encoding', 'tyyp' => 'editor')); ?></td>
					</tr>
				
				</thead>
				
				<tbody>
					
					<tr id="glossary_row_create" class="selected hidden">
						<td></td>
						<td></td>
						<td></td>
						<td>
							<select id="new_glossary_id" class="select">
								<option value=""></option>
							<?php foreach($all_glossaries as $glossary) { ?>
								<option value="<?php echo $glossary['keel_id'] ?>"<?php echo ($glossaries[$glossary['keel_id']]  ? ' disabled="disabled"' : ''); ?>><?php echo $glossary['nimi']; ?></option>
							<?php } ?>
							</select>
						</td>
						<td><input type="text" class="text" id="new_glossary_locale" /></td>
						<td>
							<select id="new_glossary_encoding" class="select">
							<?php foreach($encodings as $encoding) { ?>
								<option value="<?php echo $encoding ?>"<?php echo ($encoding == 'UTF-8' ? ' selected="selected"' : ''); ?>><?php echo $encoding; ?></option>
							<?php } ?>
							</select>
						</td>
					</tr>
					
					<tr id="glossary_row_edit" class="selected hidden">
						<td></td>
						<td></td>
						<td><input id="edit_glossary_id" type="hidden" /><input id="edit_glossary_is_default" type="checkbox" /></td>
						<td id="edit_glossary_name_cell"></td>
						<td><input type="text" class="text" id="edit_glossary_locale" /></td>
						<td>
							<select id="edit_glossary_encoding" class="select">
							<?php foreach($encodings as $encoding) { ?>
								<option value="<?php echo $encoding ?>"><?php echo $encoding; ?></option>
							<?php } ?>
							</select>
						</td>
					</tr>
					
					<?php foreach($glossaries as $glossary) { ?>
					<tr id="glossary_row_<?php echo $glossary['keel_id']; ?>" class="glossary_row<?php echo ($site->fdat['flt_keel'] == $glossary['keel_id'] ? ' pre_selected' : ''); ?>">
						<td class="glossary_delete_button_cell invisible"><img class="glossary_delete_button" id="glossary_delete_button_<?php echo $glossary['keel_id']; ?>" src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete.png" width="16" height="16" alt="<?php echo $site->sys_sona(array('sona' => 'glossary_delete', 'tyyp' => 'admin')); ?>" title="<?php echo $site->sys_sona(array('sona' => 'glossary_delete', 'tyyp' => 'admin')); ?>" /></td>
						<td class="glossary_edit_button_cell invisible"><img class="glossary_edit_button" id="glossary_edit_button_<?php echo $glossary['keel_id']; ?>" src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/edit.png" width="16" height="16" alt="<?php echo $site->sys_sona(array('sona' => 'glossary_edit', 'tyyp' => 'admin')); ?>" title="<?php echo $site->sys_sona(array('sona' => 'glossary_edit', 'tyyp' => 'admin')); ?>" /></td>
						<td class="on_default_admin_cell"><?php echo ($glossary['on_default_admin'] ? '<input type="checkbox" disabled="disabled" checked="checked" />' : ''); ?></td>
						<td class="glossary_name_cell"><?php echo $glossary['nimi']; ?></td>
						<td class="glossary_locale_cell"><?php echo $glossary['locale']; ?></td>
						<td class="glossary_encoding_cell"><?php echo $glossary['encoding']; ?></td>
					</tr>
					<?php } ?>
				
				</tbody>
				
			</table>
			
		</div>
		
	</div><!-- / scms_header_content -->
	
	<?php if(is_numeric($site->fdat['flt_keel'])) { ?>

	<div id="scms_content_header_bar">
		
		<select id="glossary_word_type" class="select">
			<option></option>
		<?php foreach($glossary_word_types as $sst) { ?>
			<option value="<?php echo $sst['sst_id']; ?>"<?php echo ($sst['sst_id'] == $sst_id ? ' selected="selected"' : ''); ?>"><?php echo $sst['nimi']; ?></option>
		<?php } ?>
		</select>
		
		<input type="button" id="add_glossary_word" value="<?php echo $site->sys_sona(array('sona' => 'add_translation', 'tyyp' => 'admin')); ?>" class="button" />
		
		<div id="scms_search_tools">
			<div id="search_wrapper"><div id="search_clear" class="<?php echo (isset($site->fdat['filter']) ? '' : 'hidden'); ?>"></div><div id="search_start" class="<?php echo (isset($site->fdat['filter']) ? 'hidden' : ''); ?>"></div><input type="text" id="search_text" value="<?php echo ($site->fdat['filter'] ? htmlspecialchars($site->fdat['filter']) : $site->sys_sona(array('sona' => 'otsi', 'tyyp' => 'otsing')).': '); ?>" class="search_text" /></div>
		</div><!-- / scms_search_tools -->
		
	</div><!-- / scms_content_header_bar -->
	
	<div id="scms_content_body">
		
		<div id="glossary_words_container">
			<table id="glossary_words" cellpadding="0" cellspacing="0" class="data_table">
				<thead>
					<tr> 
						<td></td>
						<td><?=$site->sys_sona(array(sona => "Tolkimine", tyyp=>"admin"))?></td>
						<td><?=$site->sys_sona(array(sona => "Original string", tyyp=>"admin"))?></td>
						<td><?=$site->sys_sona(array(sona => "sys sona", tyyp=>"editor"))?></td>
						<td><?=$site->sys_sona(array(sona => "type", tyyp=>"admin"))?></td>
					</tr>
				</thead>
				<tbody>
			<? 
			
			###########################
			# loop over rows
			foreach($words as $mysona)
			{
				if(strlen($mysona[kirjeldus]) < 0) {
					$mysona[sys_sona] = '<b>!!! - </b>'.$mysona[sys_sona];
				}
			
				# bold: tee sõna, mida kasutatakse bold-iks
				# kui tõlge on erinev kui tõlge tootes, siis tee tõlge bold-iks
				if(trim($mysona[sona]) != trim($mysona[origin_sona])) { $bold = 1;}
				# kui tõlge puudub üldse (tühistring), siis tee tõlge tootes bold-iks
				if(trim($mysona[sona]) == '') { $bold_orig = 1;}
				
				if ($keel_id=='2' && $site->CONF['cyr_convert_encoding']){
					$mysona['sona'] = convert_cyrillic($mysona['sona'], $site->CONF['cyr_convert_encoding']);
					$mysona['origin_sona'] = convert_cyrillic($mysona['origin_sona'], $site->CONF['cyr_convert_encoding']);
				}
				
				$href = "javascript:void(avapopup('".$site->self."?id=".$mysona[id]."&sst_id=".$mysona[sst_id]."&flt_keel=".$keel_id."','glossary','400','200','no'))";
				?>
				
					<tr class="word_row" id="word_row_<?php echo $mysona['id']; ?>"> 
						<td class="word_delete_button_cell invisible"><?php if(can_user_change_translation($mysona['sst_id'])) { ?><img class="word_delete_button" id="word_delete_button_<?php echo $mysona['id']; ?>" src="<?php echo $site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete.png" width="16" height="16" alt="Delete word" title="Delete word" /><?php } ?></td>
						<td><?php echo ($bold?"<b>":"").$mysona[sona].($bold?"</b>":"")?></td>
						<td><?php echo ($bold_orig?"<b>":"").$mysona[origin_sona].($bold_orig?"</b>":"")?></td>
						<td><?php echo $mysona[sys_sona]?></td>
						<td><?php echo $mysona[nimi]?></td>
					</tr>
				</tbody>
				<?
				++$i;
				$bold=0; $bold_orig=0;
			}
			# / loop over rows
			###########################
			?>
			</table>

		</div><!-- / glossary_words -->

	</div><!-- / scms_content_body -->
	
	<?php } ?>
	
</body>

</html>