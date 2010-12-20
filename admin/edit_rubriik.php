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



function edit_objekt()
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
	
	if($objekt->parent_id || $site->fdat['op'] == 'new')
	{
		$parent = new Objekt(array(
			'objekt_id' => ($objekt->objekt_id ? $objekt->parent_id : $site->fdat['parent_id']),
		));
		
		$pearubriik = ($parent->all['sys_alias'] == 'home' ? 1 : 0);
	}
	else 
	{
		$pearubriik = 0;
	}
	
	$content_templates = get_templates('CONTENT', $objekt->all['ttyyp_id']);
	if($content_templates['template_variable_html'])
	{
		$template_variable_html = $content_templates['template_variable_html'];
		unset($content_templates['template_variable_html']);
	}
	
	$page_templates = get_templates('PAGE', $objekt->all['page_ttyyp_id']);
	
	$sql = $site->db->prepare('select ttyyp_id, page_ttyyp_id from keel where keel_id = '.$keel);
	$default_templates = new SQL($sql);
	$default_templates = $default_templates->fetch('ASSOC');
	
	foreach($page_templates as $name => $group)
	{
		if($group[$default_templates['page_ttyyp_id']])
		{
			$default_page_template = array(
				'id' => $default_templates['page_ttyyp_id'],
				'group' => $name,
				'name' => $group[$default_templates['page_ttyyp_id']]['nimi'],
			);
		}
	}
	
	foreach($content_templates as $name => $group)
	{
		if($group[$default_templates['ttyyp_id']])
		{
			$default_content_template = array(
				'id' => $default_templates['ttyyp_id'],
				'group' => $name,
				'name' => $group[$default_templates['ttyyp_id']]['nimi'],
			);
		}
	}
	
	// parent path
	if($objekt->all['sys_alias'] == '' && $site->fdat['sys_alias'] == '') {

		// this needs serious rethink and optmisation: there's no need to get the entire tree, parent object's path to top is only needed
		include_once $class_path.'rubloetelu.class.php';
		$rubs = new RubLoetelu(array(
			'keel' => $keel,
			'required_perm' => 'C',
			'ignore_perm_for_obj' => ($parent ? $parent->objekt_id : 0),
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
	
	// setup for section selection
	$_SESSION['parent_selection']['callback'] = 'window.opener.updateSection';
	$_SESSION['parent_selection']['selectable'] = 1;
	$_SESSION['parent_selection']['hide_language_selection'] = '1';
	$_SESSION['parent_selection']['mem_classes'] = array('rubriik', ); //this sucks, really
	$_SESSION['parent_selection']['db_fields'] = array('select_checkbox', 'objekt_id', 'pealkiri', );
	$_SESSION['parent_selection']['display_fields'] = array('select_checkbox', 'pealkiri', );

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
	if($parent)
	{
		$parent_href = $parent->get_object_href();
	}
	else 
	{
		$alias = '';
		
		if($site->CONF['alias_language_format'])
		{
			$languages = $site->cash(array('klass' => 'GET_LANGUAGES', 'kood' => 'ALL_LANGUAGE_INFO'));
			
			if(empty($languages))
			{
				$sql = "select keel_id, extension, on_default from keel where on_kasutusel = 1";
				$result = new SQL($sql);
				while($row = $result->fetch('ASSOC'))
				{
					$languages[$row['keel_id']] = $row;
				}
				
				$site->cash(array('klass' => 'GET_LANGUAGES', 'kood' => 'ALL_LANGUAGE_INFO', 'sisu' => $languages));
			}
		}
		
		// add languge alias - alias language format 0: none, 1: always, 2: for non-default lang objs
		if($site->CONF['alias_language_format'] == 1)
		{
			$alias .= $languages[$objekt->all['keel']]['extension'].'/';
		}
		elseif ($site->CONF['alias_language_format'] == 2)
		{
			if(!$languages[$objekt->all['keel']]['on_default'])
			{
				$alias .= $languages[$objekt->all['keel']]['extension'].'/';
			}
		}
		
		$parent_href = '/'.$alias;
	}
	
	
	if($parent_href && $parent_href != '/' && ($site->CONF['alias_trail_format'] == 0 || $parent->all['sys_alias'] == 'home' || $parent->all['sys_alias'] == 'trash' || $parent->all['sys_alias'] == 'system' || $parent->all['sys_alias'] == 'gallup_arhiiv'))
	{
		$parent_href = preg_replace('#'.preg_quote('/'.($parent->all['friendly_url'] ? $parent->all['friendly_url'] : $parent->objekt_id), '#').'/$#', '/', $parent_href);
	}
	
	$parent_href = $site->CONF['hostname'].$parent_href;
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head> 	
		<title><?=$site->title;?> <?=$site->cms_version;?></title>
		
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding ? $encoding : $site->encoding ?>" />
		<meta http-equiv="Cache-Control" content="no-cache" />
		
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/datepicker.css" />
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/section_editor.css" />
		
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/yld.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/edit_popup.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/jquery.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/datepicker.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'];?>/common.js.php"></script>
		
		<script type="text/javascript">
			var isIE = navigator.appVersion.match(/MSIE/); // assume gecko on false
			
			window.onload = function ()
			{
				var title = document.getElementById('pealkiri');
				
				resizeWindow();
				
				var advanced_panel_state = document.getElementById('advanced_panel_state');
				if(advanced_panel_state.value == 1)
				{
					togglePanel('advanced');
				}
				
				this.focus();
				title.focus();
			}
			
			function resizeWindow()
			{
				resizeWindowTo($('#size_wrapper').width(), $('#size_wrapper').height());
			}
			
			
			function chooseSection()
			{
				explorer_window = openpopup('explorer.php?objekt_id=home&editor=1&swk_setup=parent_selection&remove_objects=<?=$site->fdat['id'];?>&pre_selected=' + document.getElementById('rubriik').value, 'cms_explorer', '800','600');
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
					    data: {op: 'generate_alias', string: alias.value, language_id: '<?=$keel;?>'},
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
								alias_placeholder.innerHTML = '<a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + '<?=$objekt->objekt_id;?>' + '</a>';
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
						alias_placeholder.innerHTML = '<a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + '<?=$objekt->objekt_id;?>' + '</a>';
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
					    data: {op: 'generate_alias', string: title.value, language_id: '<?=$keel;?>'},
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
						    	alias_cell.innerHTML = '<input type="hidden" name="friendly_url" id="alias_value" value="' + response.alias + '"><?=$parent_href;?><span id="alias_placeholder"><a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + (response.alias.length > 30 ? response.alias.substring(0, 30) + '...' : response.alias) + '</a></span>';
					    	}
					    	else
					    	{
						    	alias_cell.innerHTML = '<input type="hidden" name="friendly_url" id="alias_value" value=""><?=$parent_href;?><span id="alias_placeholder"><input type="text" id="alias" value="" onblur="saveAlias();"></span>';
					    	}
					    	
					    	var alias_row = document.getElementById('alias_row');
					    	alias_row.style.display = (isIE ? 'block' : 'table-row');

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
					alert('<?=$site->sys_sona(array('sona' => 'please_fill_in_the_title!', 'tyyp' => 'admin'));?>');
					return;
				}
				
				var alias_value = document.getElementById('alias_value');
				var alias = document.getElementById('alias');
				
				if((title.value && !alias_value) || (alias && alias_value && alias.value != alias_value.value))
				{
					$.ajax({
					    url: 'ajax_response.php?rand=' + Math.random(9999),
					    data: {op: 'generate_alias', string: title.value, language_id: '<?=$keel;?>'},
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
						    	alias_cell.innerHTML = '<input type="hidden" name="friendly_url" id="alias_value" value="' + response.alias + '"><?=$parent_href;?><span id="alias_placeholder"><a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + (response.alias.length > 30 ? response.alias.substring(0, 30) + '...' : response.alias) + '</a></span>';
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
		</script>
	</head>
	
	<body>
		
		<? if ($site->fdat['op']=='edit') {?>
			<iframe src="checkin.php?objekt_id=<?=$objekt->objekt_id ?>" style="width: 0; height: 0; display: none; visibility: hidden;"></iframe>
		<? } ?>
		
		<form action="edit.php" name="editForm" id="editForm" method="POST"  enctype="multipart/form-data">
		
		<input type="hidden" name="tab" value="<?=$site->fdat['tab']?>" />
		<input type="hidden" id="op" name="op" value="<?=htmlspecialchars($site->fdat['op'])?>" />
		<input type="hidden" id="op2" name="op2" value="" />
		<input type="hidden" id="refresh" name="refresh" value="0" />
		
		<input type="hidden" name="tyyp_id" value="<?=$tyyp['tyyp_id']?>" />
		<input type="hidden" name="tyyp" value="<?=$tyyp['klass']?>" />
		
		<input type="hidden" name="pearubriik" value="<?=$pearubriik ?>" />
		<input type="hidden" name="id" value="<?=$site->fdat['id'] ?>" />
		<input type="hidden" name="parent_id" value="<?=$site->fdat['parent_id']?>" />
		<input type="hidden" name="previous_id" value="<?=$site->fdat['previous_id']?>" />
		<input type="hidden" name="keel" value="<?=$keel?>" />
		<input type="hidden" name="on_pealkiri" value="1" />
		
        <input type="hidden" name="sorting" value="<?=$site->fdat['sorting'];?>">

        <input type="hidden" name="extension_path" value="<?=$site->fdat['extension_path']?>" />
		
		<input type="hidden" name="opener_location" value="" />
		<input type="hidden" name="publish" value="<?php echo ($site->fdat['publish'] || $objekt->all['on_avaldatud'] ? 1 : 0); ?>">

		<input name="permanent_parent_id" type="hidden" value="<?=$objekt->parent_id?>" />
		<input name="sys_alias" type="hidden" value="<?=($site->fdat['sys_alias'] ? $site->fdat['sys_alias'] : $objekt->all['sys_alias'])?>" />
		
		<input name="advanced_panel_state" id="advanced_panel_state" type="hidden" value="<?=($site->fdat['advanced_panel_state'] ? htmlspecialchars($site->fdat['advanced_panel_state']) : 0) ?>" />
		
		<div id="size_wrapper" class="section_editor">
		
		<div id="main_container">
			<?php ########### Tabs  ########?>
			<div id="tab_container">
				<a href="javascript:void(0);" class="selected"><?=$site->sys_sona(array('sona' => 'tyyp_rubriik', 'tyyp' => 'System'));?></a>
				<?php 
					if($objekt->objekt_id) { ?>
					<a href="<?=$site->self.'?tab=seo&id='.$site->fdat['id'].'&keel='.$keel.'&op='.$site->fdat['op']?>" onclick="resizeDocumentHeightTo(430);"><?=$site->sys_sona(array('sona' => 'meta-info', 'tyyp' => 'admin'))?></a>
					<?php } else { ?>
					<a href="javascript:void(0);"><?=$site->sys_sona(array('sona' => 'meta-info', 'tyyp' => 'admin'))?></a>
				<?php } ?>
				<?php 
					if($objekt->objekt_id) { ?>
					<a href="<?=$site->self.'?tab=permissions&id='.$site->fdat['id'].'&keel='.$keel.'&op='.$site->fdat['op']?>" onclick="resizeDocumentHeightTo(430);"><?=$site->sys_sona(array('sona' => 'permissions', 'tyyp' => 'admin'))?></a>
					<?php } else { ?>
					<a href="javascript:void(0);"><?=$site->sys_sona(array('sona' => 'permissions', 'tyyp' => 'admin'))?></a>
				<?php } ?>
			</div>
			
			<div id="content_container">
		
				<table cellpadding="0" cellspacing="0" class="form_row">
					<tr>
						<td class="label"><label><?=$site->sys_sona(array('sona' => 'Pealkiri', 'tyyp' => 'editor'))?>:</label></td>
						<td class="input"><input type="text" class="text" name="pealkiri" id="pealkiri" value="<?=htmlspecialchars($objekt->all['pealkiri'])?>" onblur="createAlias();" /></td>
					</tr>
					<?php if(($objekt->objekt_id || isset($objekt->all['friendly_url'])) && !($objekt->all['sys_alias'] == 'trash' || $objekt->all['sys_alias'] == 'system' || $objekt->all['sys_alias'] == 'gallup_arhiiv')) { ?>
					<tr>
						<td class="label">&nbsp;</td>
						<td class="input"><input type="hidden" id="alias_value" name="friendly_url" name="friendly_url" value="<?=htmlspecialchars($objekt->all['friendly_url']);?>" /><?=$parent_href;?><span id="alias_placeholder"><a href="javascript:void(0);" onclick="editAlias();" id="alias_link"><?=($objekt->all['friendly_url'] ? (strlen(htmlspecialchars($objekt->all['friendly_url'])) > 30 ? substr(htmlspecialchars($objekt->all['friendly_url']), 0, 30).'...' : htmlspecialchars($objekt->all['friendly_url'])) : $objekt->objekt_id);?></a></span></td>
					</tr>
					<?php } else { ?>
					<tr id="alias_row">
						<td class="label">&nbsp;</td>
						<td class="input" id="alias_cell"></td>
					</tr>
					<?php } ?>
					<tr>
						<td class="label"><label><?=$site->sys_sona(array('sona' => 'content template', 'tyyp' => 'editor'))?>:</label></td>
						<td class="input"><select class="select" id="template_select" name="ttyyp_id" onchange="refreshForm();"><option value="0"><?=$site->sys_sona(array('sona' => 'default', 'tyyp' => 'admin'))?> (<?=$default_content_template['name'];?>)</option>
						<?php foreach($content_templates as $template_group_name => $templates_group) { ?>
							<optgroup label="<?=$template_group_name?>">
						<?php  foreach ($templates_group as $template_id => $template) { ?>
						<?php  if($objekt->all['ttyyp_id'] == $template_id) { $ttyyp = $template; } ?>
								<option value="<?=$template_id?>"<?=($objekt->all['ttyyp_id'] == $template_id ? ' selected="selected" style="color: #a7a6aa;"' : '');?>><?=$template['nimi']?></option>
						<?php } ?>
							</optgroup>
						<?php } ?>
						</select></td>
					</tr>
					<?php ########### publishing  ########?>
					<tr>
						<td class="label"><?=$site->sys_sona(array('sona' => 'visible_to_visitors', 'tyyp' => 'editor'))?></td>
						<td><input type="radio" name="publish" id="object_published" value="1"<?=($site->fdat['publish'] || $objekt->all['on_avaldatud'] ? ' checked' : '')?><?php echo (!$objekt->permission['P'] ? ' disabled="disabled"' : NULL); ?>> <label for="object_published"><?=$site->sys_sona(array('sona' => 'published', 'tyyp' => 'editor'))?></label>	<input type="radio" name="publish" id="object_unpublished" value="0"<?=($site->fdat['publish'] == 0 && $objekt->all['on_avaldatud'] == 0 ? ' checked' : '')?><?php echo (!$objekt->permission['P'] ? ' disabled="disabled"' : NULL); ?>> <label for="object_unpublished"><?=$site->sys_sona(array('sona' => 'unpublished', 'tyyp' => 'editor'))?></label></td>
					</tr>
				</table>
				
				<br />
				
				<?php ########### advanced  ########?>
				<div class="panel_toggler" onclick="togglePanel('advanced');">
					<a href="javascript:void(0);"><?=$site->sys_sona(array('sona' => 'Advanced', 'tyyp' => 'editor'))?> <span id="advanced_panel_link_state">&raquo;</span></a>
				</div>
				
				<div id="advanced_panel" class="panel">
					
					<?php ########### parent section  ########?>
					<?php if($section_name) { ?>
					<table cellpadding="0" cellspacing="0" class="form_row">
						<tr>
							<td class="label"><label><?=$site->sys_sona(array('sona' => 'Rubriigid', 'tyyp' => 'editor'))?>:</label></td>
							<td class="input">
								<table cellpadding="0" cellspacing="0" class="cf_container">
									<tr>
										<th><input type="hidden" name="rubriik[]" id="rubriik" value="<?=($parent ? $parent->objekt_id : 0);?>"><span id="section_name"><a href="javascript:chooseSection();"><?=$section_name;?></a></span></th>
										<td><a href="javascript:chooseSection();">..</a></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<?php } ?>
					
					<?php ########### page template  ########?>
					<table cellpadding="0" cellspacing="0" class="form_row">
						<tr>
							<td class="label"><label><?=$site->sys_sona(array('sona' => 'page template', 'tyyp' => 'editor'))?>:</label></td>
							<td class="input"><select class="select" name="page_ttyyp_id"><option value="0"><?=$site->sys_sona(array('sona' => 'default', 'tyyp' => 'admin'))?> (<?=$default_page_template['name'];?>)</option>
							<?php foreach($page_templates as $template_group_name => $templates_group) { ?>
								<optgroup label="<?=$template_group_name?>">
							<?php  foreach ($templates_group as $template_id => $template) { ?>
									<option value="<?=$template_id?>"<?=($objekt->all['page_ttyyp_id'] == $template_id ? ' selected="selected" style="color: #a7a6aa;"' : '');?>><?=$template['nimi']?></option>
							<?php } ?>
								</optgroup>
							<?php } ?>
							</select></td>
						</tr>
					</table>
					
					<?php ########### hiding in menu and mailinglist  ########?>
					<table cellpadding="0" cellspacing="0" class="form_row">
						<tr>
							<td class="label">&nbsp;</td>
							<td><input type="checkbox" class="checkbox" id="hide_in" name="is_hided_in_menu" value="1"<?=($objekt->all['is_hided_in_menu'] ? ' checked="checked"' : '')?> /></td>
							<td width="145"><label for="hide_in"><?=$site->sys_sona(array('sona' => 'Hide in menu', 'tyyp' => 'editor'))?></label></td>
							<td><input type="checkbox" class="checkbox" id="add_mailinglist" name="on_meilinglist" value="1"<?=($objekt->all['on_meilinglist'] ? ' checked="checked"' : '')?> /></td>
							<td><label for="add_mailinglist"><?=$site->sys_sona(array('sona' => 'On meilinglist', 'tyyp' => 'editor'))?></label></td>
						</tr>
					</table>
					
					<table cellpadding="0" cellspacing="0" class="form_row">
						<tr>
							<td class="label">&nbsp;</td>
							<td><label><?=$site->sys_sona(array('sona' => 'Avaldatud', 'tyyp' => 'editor'))?>:</label></td>
							<td><input type="text" id="publish_start" name="avaldamise_algus" maxlength="16" class="text_date" value="<?=$publish_start?>" /></td>
							<td><a href="javascript:init_datepicker('publish_start', 'publish_start', 'publish_end');"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" title="Choose from calendar" alt="Choose from calendar" /></a></td>
							<td><label><?=$site->sys_sona(array('sona' => 'Kuni', 'tyyp' => 'editor'))?>:</label></td>
							<td><input type="text" id="publish_end" name="avaldamise_lopp" maxlength="16" class="text_date" value="<?=$publish_end?>" /></td>
							<td><a href="javascript:init_datepicker('publish_end', 'publish_start', 'publish_end');"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/calendar/cal.gif" width="16" height="15" title="Choose from calendar" alt="Choose from calendar" /></a></td>
						</tr>
					</table>
					
					<?########### ONLY FOR SAURUS 3 BUILT-IN TEMPLATES: subarticles +  Add print icon  ########?>
					<?if(($objekt || $site->fdat['refresh']) && $ttyyp['ttyyp_id'] > 0 && $ttyyp['ttyyp_id'] < 1000) { # if ver3 content template?>
					<table cellpadding="0" cellspacing="0" class="form_row">
			              <tr> 
							<td class="label">&nbsp;</td>
			
							<?########### subarticles  ########?>
			                <td><input type="checkbox" id="on_alamartiklid" name="on_alamartiklid"<?=($objekt->all['on_alamartiklid'] ? ' checked="checked"' : '');?> /></td>
			                <td width="145"><label for="on_alamartiklid"><?=$site->sys_sona(array('sona' => 'Naita alamartiklid', 'tyyp' => 'editor')); ?></label></td>
			
							<?########### Add print icon  ########?>
			                <td><input type="checkbox" id="on_printlink" name="on_printlink"  value="1" <?=$objekt->all['on_printlink'] ? ' checked="checked"' : '' ?> /></td>
			                <td><label for="on_printlink"><?=$site->sys_sona(array('sona' => 'Naita prindi ikoon', 'tyyp' => 'editor'));?></label></td>
			
						  </tr>
					</table>
					<?} # if ver3 content template?>
					<?php if($template_variable_html) { ?>
					<table cellpadding="0" cellspacing="0" class="form_row">
						<?=$template_variable_html; ?>
					</table>
					<?php } ?>
					
					<?php ########### position  ########?>
					<?php if($site->CONF['allow_change_position']) { ?>
					<table cellpadding="0" cellspacing="0" class="form_row">
						<tr>
							<td class="label"><label><?=$site->sys_sona(array('sona' => 'Position', 'tyyp' => 'editor'))?>:</label></td>
							<td><input type="text" maxlength="5" class="text_position" name="kesk" value="<?=($site->fdat['op']=='edit' ? $objekt->all['kesk'] : $site->fdat['kesk']);?>" /></td>
						</tr>
					</table>
					<?php } else { ?>
						<input type="hidden" name="kesk" value="<?=($site->fdat['op']=='edit' ? $objekt->all['kesk'] : $site->fdat['kesk']);?>" />
					<?php } ?>
					
				</div>
			</div>
			
		</div>
		
		<div id="button_container">
			
			<table width="100%" cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<td align="left">
							<input type="button" class="button" value="<?=$site->sys_sona(array('sona' => 'Apply', 'tyyp' => 'editor'))?>" onclick="saveForm('save');" />
						</td>
						<td align="right">
							<input type="button" class="button" value="&nbsp;&nbsp;&nbsp;&nbsp;<?=$site->sys_sona(array('sona' => 'save_and_close', 'tyyp' => 'editor'))?>&nbsp;&nbsp;&nbsp;&nbsp;" onclick="saveForm('saveclose');" />
							<input type="button" class="button" value="<?=$site->sys_sona(array('sona' => 'Close', 'tyyp' => 'editor'))?>" onclick="window.close();" />		
						</td>
					</tr>
				</tr>
				</tbody>
			</table>
			
			
			
			
		</div>
		
		</div> <!-- / size_wrapper -->
		
		</form>
	</body>
</html>

<?php

}

function salvesta_objekt () {
	global $site;
	global $objekt;

	$class_path = "../classes/";

	if ($objekt->objekt_id) {

		if ($objekt->on_sisu_olemas) {
			# -------------------------------
			# Objekti uuendamine andmebaasis    
			# -------------------------------
			$sql = $site->db->prepare("update obj_rubriik set on_peida_vmenyy=?, on_printlink=?, on_meilinglist=?, on_alamartiklid=? WHERE objekt_id=?",
				$objekt->all[on_peida_vmenyy],
				$site->fdat[on_printlink] ? 1 : 0,
				$site->fdat[on_meilinglist] ? 1 : 0,
				$site->fdat[on_alamartiklid] ? 1 : 0,
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------

			$sql = $site->db->prepare("insert into obj_rubriik (objekt_id,on_peida_vmenyy, on_printlink, on_meilinglist, on_alamartiklid) values (?,?,?,?,?)",
				$objekt->objekt_id,
				$objekt->all[on_peida_vmenyy],
				$site->fdat[on_printlink] ? 1 : 0,
				$site->fdat[on_meilinglist] ? 1 : 0,
				$site->fdat[on_alamartiklid] ? 1 : 0
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}

		$site->debug->msg("sisu on salvestatud, objekt_id = ".$objekt->objekt_id);

	} else {
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}
}
