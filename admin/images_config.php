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

// check permissions
$site->user->adminpermissions = $site->user->load_adminpermissions();			
if (!$site->user->allowed_adminpage()) exit;
// / check permissions

// get languages
$sql = "select distinct a.nimi as nimi, b.glossary_id as keel_id from keel as a left join keel as b on a.keel_id = b.glossary_id where b.on_kasutusel = '1' order by a.nimi";
$result = new SQL($sql);
while ($row = $result->fetch('ASSOC')) { $languages[] = $row; }
// / get languages

// set translation language
if(isset($site->fdat['lang']))
{
	setcookie('images_config_lang', (int)$site->fdat['lang']);
	$_COOKIE['images_config_lang'] = $site->fdat['lang'];
}
$selected_lang = (isset($_COOKIE['images_config_lang']) ? $_COOKIE['images_config_lang'] : $_SESSION['keel_admin']['glossary_id']);
// / set translation language

// save image configs
if($_POST['save_configs'])
{
	new SQL($site->db->prepare('update config set sisu = ? where nimi = \'image_width\'', $site->fdat['image_width']));
	new SQL($site->db->prepare('update config set sisu = ? where nimi = \'thumb_width\'', $site->fdat['thumb_width']));
}
// / save image configs

// get image configs
$configs = array();
$sql = 'select * from config where nimi in (\'image_width\', \'thumb_width\')';
$result = new SQL($sql);
while ($row = $result->fetch('ASSOC')) { $configs[$row['nimi']] = $row; }
// / get image configs

// get custom translations group sst_id
$sql = 'select sst_id from sys_sona_tyyp where voti = \'custom\' limit 1';
$result = new SQL($sql);
$custom_sst_id = $result->fetchsingle();
// / get custom translations group sst_id

// get image size definitions
$definitions = array();
$sql = 'select * from config_images';
$result = new SQL($sql);
while($row = $result->fetch('ASSOC'))
{
	$definitions[$row['definition_id']] = $row;
}
// /get image size definitions

// process image definitions form
$delete_ids = array();
if(is_array($site->fdat['definitions']))
{
	foreach($site->fdat['definitions'] as $def_id => $definition)
	{
		// delete
		if($definition['del'] == 1 && $definitions[$def_id])
		{
			$delete_ids[] = (int)$def_id;
		}
		// insert
		elseif (eregi('^new_', $def_id) && $definition['name'])
		{
			// create new config
			$sql = $site->db->prepare('insert into config_images (name, value) values (?, ?)', $definition['name'], $definition['value']);
			new SQL($sql);
			
			// add translation keys
			$sql = $site->db->prepare('select id from sys_sonad where sys_sona = ?', 'image_definitions_'.$definition['name']);
			$result = new SQL($sql);
			if(!$result->rows)
			{
				foreach($languages as $language)
				{
					// insert new sysword
					$sql = $site->db->prepare('insert into sys_sonad (sys_sona, keel, sst_id) values(?, ?, ?)', 'image_definitions_'.$definition['name'], $language['keel_id'], $custom_sst_id);
					$result = new SQL($sql);
				}
	
				$sql = $site->db->prepare('insert into sys_sonad_kirjeldus (sys_sona, sst_id) values(?, ?)', 'image_definitions_'.$definition['name'], $custom_sst_id);
				new SQL($sql);
			}
		}
		// update
		elseif ($definition['value'] != $definitions[$def_id]['value'])
		{
			$sql = $site->db->prepare('update config_images set value = ? where definition_id = ?', $definition['value'], $def_id);
			new SQL($sql);
		}
	}
	
	// delete
	if(count($delete_ids))
	{
		$sql = 'delete from config_images where definition_id in ('.implode(',', $delete_ids).')';
		new SQL($sql);
	}
	
	$definitions = array();
	
	$sql = 'select * from config_images';
	$result = new SQL($sql);
	while($row = $result->fetch('ASSOC'))
	{
		$definitions[$row['definition_id']] = $row;
	}
}
// / process image definitions form

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head> 	
		<title><?=$site->sys_sona(array('sona' => 'Image manipulation', 'tyyp' => 'Admin'));?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/default_admin_page.css" media="screen">
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/ie_position_fix.js"></script>
		<script type="text/javascript">
			var definitions = 0;
		
			window.onload = function()
			{
				new ContentBox('image_definitions', '15px', '0px', '50%', '0px');
				new ContentBox('image_configs', '50%', '0px', '15px', '0px');
				
				definitions = new Definitions(document.getElementById('definitions_table'));
				<?php foreach ($definitions as $definition) { ?>
				definitions.add({id: <?=$definition['definition_id'];?>, name: '<?=htmlspecialchars($definition['name']);?>', translation: '<?=htmlspecialchars($site->sys_sona(array('sona' => 'image_definitions_'.$definition['name'], 'tyyp' => 'Custom', 'lang_id' => (int)$selected_lang)));?>', value: '<?=htmlspecialchars($definition['value']);?>'}); 
				<?php } ?>
			}
			
			var ContentBox = function(id, top, right, bottom, left)
			{
				this.box = document.getElementById(id);
				this.box.style.top = top;
				this.box.style.right = right;
				this.box.style.bottom = bottom;
				this.box.style.left = left;
			}
		</script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/yld.js"></script>
		<script type="text/javascript">
			var Definitions = function(def_table)
			{
				this.insert_count = 0;
				
				this.insert = function ()
				{
					this.insert_count++;
					
					// insert table row
					var row = def_table.insertRow(-1);
					row.id = 'definition_new_' + this.insert_count;
					
					// insert cells
					var name_cell = row.insertCell(-1);
					var trans_cell = row.insertCell(-1);
					var trans_button_cell = row.insertCell(-1);
					var value_cell = row.insertCell(-1);
					var button_cell = row.insertCell(-1);
					
					name_cell.width = 125;
					
					// cell contents
					name_cell.innerHTML = '<input type="text" name="definitions[new_' + this.insert_count + '][name]" class="text">';
					trans_cell.innerHTML = '<input type="text" name="definitions[new_' + this.insert_count + '][translation]" class="text_long" disabled="disabled">';
					trans_button_cell.innerHTML = '&nbsp;';
					value_cell.innerHTML = '<input type="text" name="definitions[new_' + this.insert_count + '][value]" class="text_small">';
					button_cell.innerHTML = '<ul class="s_Buttons_container"><li><a href="javascript:definitions.remove(\'' + row.id + '\');" class="button_delete_nolabel"></a></li></ul>';
				}
				
				this.remove = function(row_id)
				{
					var row = document.getElementById(row_id);
					if(row)
					{
						row.parentNode.removeChild(row);
					}
				}
				
				this.add = function (definition)
				{
					// insert table row
					var row = def_table.insertRow(-1);
					row.id = 'definition_' + definition.id;
					
					// insert cells
					var name_cell = row.insertCell(-1);
					var trans_cell = row.insertCell(-1);
					var trans_button_cell = row.insertCell(-1);
					var value_cell = row.insertCell(-1);
					var button_cell = row.insertCell(-1);
					
					name_cell.width = 125;
					
					// cell contents
					name_cell.innerHTML = definition.name;
					trans_cell.innerHTML = '<input type="text" name="definitions[' + definition.id + '][translation]" value="' + definition.translation + '" class="text_long" disabled="disabled">';
					trans_button_cell.innerHTML = '<ul class="s_Buttons_container"><li><a href="javascript:edit_translation(\'image_definitions_' + definition.name + '\');" class="button"><?=$site->sys_sona(array('sona' => 'muuda', 'tyyp' => 'editor'));?></a></li></ul>';
					value_cell.innerHTML = '<input type="text" name="definitions[' + definition.id + '][value]" value="' + definition.value + '" class="text_small">';
					button_cell.innerHTML = '<input type="hidden" name="definitions[' + definition.id + '][del]" value="0"><ul class="s_Buttons_container"><li><a href="javascript:definitions.del(\'' + definition.id + '\');" class="button_delete_nolabel"></a></li></ul>';
				}
				
				this.del = function(definition_id)
				{
					var row = document.getElementById('definition_' + definition_id);
					var form = document.getElementById('definitions');
					if(row && form)
					{
						form['definitions[' + definition_id + '][del]'].value = 1;
						row.style.display = 'none';
					}
				}
			}
			
			function edit_translation(sys_word)
			{
				var edit_popup = avapopup('<?=$site->CONF['wwwroot'];?>/admin/sys_sonad_loetelu.php?sys_word=' + sys_word + '&sst_id=<?=$custom_sst_id;?>&flt_keel=<?=(int)$selected_lang?>','glossary','400','200','no');
			}
		</script>
		<style type="text/css">
			input {
				height: 16px !important;
			}		
		</style>
	</head>
	<body>
		<div id="mainContainer">
			<div class="contentArea" id="image_definitions">
				<div class="contentAreaTitle">
					<table cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td><?=$site->sys_sona(array('sona' => 'image_resizing', 'tyyp' => 'Admin'));?></td>
						</tr>
					</table>
				</div><!-- / contentAreaTitle -->
				<div class="toolbarArea">
            		<form name="filter_form" id="filter_form" method="POST" action="<?=$_SERVER['PHP_SELF'];?>">
		    			<table cellpadding="0" cellspacing="0" width="100%">
		    				<tr>
		    					<td>
				            		<ul class="s_Buttons_container">
				            			<li><a href="javascript:document.getElementById('definitions').submit();" id="button_save" class="button_save"><?=$site->sys_sona(array('sona' => 'salvesta', 'tyyp' => 'Editor'));?></a></li>
				            			<li><a href="javascript:definitions.insert();" id="button_new" class="button_new"><?=$site->sys_sona(array('sona' => 'new', 'tyyp' => 'Editor'));?></a></li>
				            		</ul>
		    					</td>
		    					<td align="right">
				            		<ul class="s_Buttons_container" style="float: right;">
				            			<li><span><?=$site->sys_sona(array('sona' => 'Language', 'tyyp' => 'Admin'));?>:  <select name="lang" class="select" onchange="this.form.submit();">
				            				<?php foreach($languages as $language) { ?>
				            					<option value="<?=$language['keel_id'];?>"<?=($language['keel_id'] == $selected_lang ? ' selected="selected"' : '');?>><?=$language['nimi'];?></option>
				            				<?php } ?>
					            				</select></span></li>
				            		</ul>
		    					</td>
		    				</tr>
		    			</table>
	            	</form><!-- /form filters -->
				</div><!-- / toolbarArea -->
				<div class="contentAreaContent withTitleAndToolBar">
                	<form name="definitions" id="definitions" method="POST" action="<?=$_SERVER['PHP_SELF'];?>">
                		<input type="hidden" name="lang" value="<?=(int)$selected_lang;?>">
	                	<table cellpadding="0" cellspacing="0" class="data_table">
	                		<thead>
	                			<tr>
		                			<td><?=$site->sys_sona(array('sona' => 'Nimi', 'tyyp' => 'Editor'));?></td>
		                			<td><?=$site->sys_sona(array('sona' => 'Tolkimine', 'tyyp' => 'Admin'));?></td>
		                			<td>&nbsp;</td>
		                			<td><?=$site->sys_sona(array('sona' => 'Laius', 'tyyp' => 'Editor'));?></td>
		                			<td>&nbsp;</td>
	                			</tr>
	                		</thead>
	                		<tbody id="definitions_table"></tbody>
	                	</table>
	                </form>
				</div><!-- / contentAreaContent -->
			</div><!-- / contentArea -->
			<div class="contentArea" id="image_configs">
				<div class="contentAreaTitle">
					<table cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td><?=$site->sys_sona(array('sona' => 'Configuration', 'tyyp' => 'Admin'));?></td>
						</tr>
					</table>
				</div><!-- / contentAreaTitle -->
				<div class="toolbarArea">
	    			<table cellpadding="0" cellspacing="0" width="100%">
	    				<tr>
	    					<td>
			            		<ul class="s_Buttons_container">
			            			<li><a href="javascript:document.getElementById('configs_form').submit();" class="button_save"><?=$site->sys_sona(array('sona' => 'salvesta', 'tyyp' => 'Editor'));?></a></li>
			            		</ul>
	    					</td>
	    				</tr>
	    			</table>
				</div><!-- / toolbarArea -->
				<div class="contentAreaContent withTitleAndToolBar">
					<form name="configs_form" id="configs_form" method="POST" action="<?=$_SERVER['PHP_SELF'];?>">
						<input type="hidden" name="save_configs" value="1">
		    			<table cellpadding="0" cellspacing="0" class="data_table">
		    				<tr>
		    					<td><input type="text" name="image_width" class="text_config" value="<?=$configs['image_width']['sisu'];?>"></td>
		    					<td><?=$configs['image_width']['kirjeldus'];?></td>
		    				</tr>
		    				<tr>
		    					<td><input type="text" name="thumb_width" class="text_config" value="<?=$configs['thumb_width']['sisu'];?>"></td>
		    					<td><?=$configs['thumb_width']['kirjeldus'];?></td>
		    				</tr>
		    			</table>
	    			</form>
				</div><!-- / contentAreaContent -->
			</div><!-- / contentArea -->
		</div><!-- / mainContainer -->
	</body>
</html>