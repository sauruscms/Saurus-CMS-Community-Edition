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
 * Title row display functions
 */

/**
 * Default row display function, attemps to echo row title from site dictionary Custom group
 *
 * @param string $name	field name
 */
function swk_title_row_default($name)
{
	global $site;
	
	echo '<td class="'.$name.'"><span alt="'.$site->sys_sona(array('sona' => $name, 'tyyp' => 'Custom')).'" title="'.$site->sys_sona(array('sona' => $name, 'tyyp' => 'Custom')).'">'.$site->sys_sona(array('sona' => $name, 'tyyp' => 'Custom')).'</span></td>';
}

/**
 * select_checkbox title
 *
 * @param string $name	field name
 */
function swk_title_row_select_checkbox($name)
{
	echo '<td class="select_checkbox"></td>';
}

/**
 * objekt_id title
 *
 * @param string $name	field name
 */
function swk_title_row_objekt_id($name)
{
	global $site;
	
	echo '<td class="objekt_id"><span alt="ID" title="ID">ID</span></td>';
}

/**
 * on_avaldatud title
 *
 * @param string $name	field name
 */
function swk_title_row_on_avaldatud($name)
{
	global $site;
	
	echo '<td class="on_avaldatud"><img src="'.$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/actions/check.png" alt="Published" title="Published"></td>';
}

/**
 * pealkiri title
 *
 * @param string $name	field name
 */
function swk_title_row_pealkiri($name)
{
	global $site;
	
	echo '<td class="pealkiri"><span alt="'.$site->sys_sona(array('sona' => 'title', 'tyyp' => 'saurus4')).'" title="'.$site->sys_sona(array('sona' => 'title', 'tyyp' => 'saurus4')).'">'.$site->sys_sona(array('sona' => 'title', 'tyyp' => 'saurus4')).'</span></td>';
}

/**
 * friendly_url title
 *
 * @param string $name	field name
 */
function swk_title_row_friendly_url($name)
{
	global $site;
	
	echo '<td class="friendly_url"><span alt="'.$site->sys_sona(array('sona' => 'Friendly_URL', 'tyyp' => 'editor')).'" title="'.$site->sys_sona(array('sona' => 'Friendly_URL', 'tyyp' => 'editor')).'">'.$site->sys_sona(array('sona' => 'Friendly_URL', 'tyyp' => 'editor')).'</span></td>';
}

/**
 * page_ttyyp_id title
 *
 * @param string $name	field name
 */
function swk_title_row_page_ttyyp_id($name)
{
	global $site;
	
	echo '<td class="page_ttyyp_id"><span alt="'.$site->sys_sona(array('sona' => 'page template', 'tyyp' => 'editor')).' ID" title="'.$site->sys_sona(array('sona' => 'page template', 'tyyp' => 'editor')).' ID">tpl</span></td>';
}

/**
 * kesk title
 *
 * @param string $name	field name
 */
function swk_title_row_kesk($name)
{
	global $site;
	
	echo '<td class="kesk"><span alt="'.$site->sys_sona(array('sona' => 'position', 'tyyp' => 'editor')).'" title="'.$site->sys_sona(array('sona' => 'position', 'tyyp' => 'editor')).'">Pos</span></td>';
}

/**
 * ttyyp_id title
 *
 * @param string $name	field name
 */
function swk_title_row_ttyyp_id($name)
{
	global $site;
	
	echo '<td class="ttyyp_id"><span alt="'.$site->sys_sona(array('sona' => 'content template', 'tyyp' => 'editor')).' ID" title="'.$site->sys_sona(array('sona' => 'content template', 'tyyp' => 'editor')).' ID">c_tpl</span></td>';
}

/**
 * klass title
 *
 * @param string $name	field name
 */
function swk_title_row_klass($name)
{
	global $site;
	
	echo '<td class="klass"><span alt="'.$site->sys_sona(array('sona' => 'class', 'tyyp' => 'admin')).'" title="'.$site->sys_sona(array('sona' => 'class', 'tyyp' => 'admin')).'">'.$site->sys_sona(array('sona' => 'class', 'tyyp' => 'admin')).'</span></td>';
}

/**
 * aeg title
 *
 * @param string $name	field name
 */
function swk_title_row_aeg($name)
{
	global $site;
	
	echo '<td class="aeg"><span alt="'.$site->sys_sona(array('sona' => 'aeg', 'tyyp' => 'editor')).'" title="'.$site->sys_sona(array('sona' => 'aeg', 'tyyp' => 'editor')).'">'.$site->sys_sona(array('sona' => 'aeg', 'tyyp' => 'editor')).'</span></td>';
}

/**
 * Title row search functions  
 */

/**
 * Default row search function
 *
 * @param string $name	field name
 * @param string $value	field search value
 */
function swk_title_search_default($name, $value)
{
	global $site;
	
	echo '<th class="'.$name.'"><input name="'.$name.'" id="'.$name.'" type="text" value="'.htmlspecialchars($value).'" onkeyup="if(event.keyCode == 13) submitFilters();"></th>';
}

/**
 * select_checkbox row search function
 *
 * @param string $name	field name
 * @param string $value	field search value
 */
function swk_title_search_select_checkbox($name, $value)
{
	global $site;
	
	echo '<th class="select_checkbox"></th>';
}

/**
 * on_avaldatud row search function
 *
 * @param string $name	field name
 * @param string $value	field search value
 */
function swk_title_search_on_avaldatud($name, $value)
{
	global $site;
	
	echo '<th class="on_avaldatud"><input name="on_avaldatud" id="on_avaldatud" type="checkbox" value="1" onkeyup="if(event.keyCode == 13) submitFilters();"'.($value == 1 ? ' checked="chekced"' : '').'></th>';
}

/**
 * aeg row search function
 *
 * @param string $name	field name
 * @param string $value	field search value
 */
function swk_title_search_aeg($name, $value)
{
	global $site;
	
	echo '<th class="aeg">&nbsp;</th>';
}

/**
 * on_avaldatud row search function
 *
 *
 * @param string $name	field name
 * @param string $value	field search value
 */
function swk_title_search_klass($name, $value)
{
	global $site;
	
	?><th class="klass">
		<select name="classes" class="select" onchange="this.form.submit();">
			<option></option>
		
			<?php foreach ($_SESSION[$_GET['swk_setup']]['mem_classes'] as $i => $class)
			{
				// array('rubriik', 'artikkel', 'folder', 'file', 'asset', 'teema', 'kommentaar', 'link', 'dokument')
				switch ($class)
				{
					case 'rubriik':
						$class_filter = array('value' => 'rubriik', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_rubriik', 'tyyp' => 'System'))), ); break;
						
					case 'artikkel':
						$class_filter = array('value' => 'rubriik,artikkel', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_artikkel', 'tyyp' => 'System'))), ); break;
						
					case 'folder':
						$class_filter = array('value' => 'folder', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_folder', 'tyyp' => 'System'))), ); break;

					case 'file':
						$class_filter = array('value' => 'folder,file', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_file', 'tyyp' => 'System'))), ); break;
						
					case 'asset':
						$class_filter = array('value' => 'rubriik,asset', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_asset', 'tyyp' => 'System'))), ); break;
						
					case 'teema':
						$class_filter = array('value' => 'rubriik,teema', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_teema', 'tyyp' => 'System'))), ); break;
						
					case 'kommentaar':
						$class_filter = array('value' => 'rubriik,artikkel,teema,kommentaar', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_kommentaar', 'tyyp' => 'System'))), ); break;
						
					case 'link':
						$class_filter = array('value' => 'rubriik,link', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_link', 'tyyp' => 'System'))), ); break;
						
					case 'dokument':
						$class_filter = array('value' => 'rubriik,dokument', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_dokument', 'tyyp' => 'System'))), ); break;
						
					case 'gallup':
						$class_filter = array('value' => 'rubriik,gallup', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_gallup', 'tyyp' => 'System'))), ); break;
						
					case 'album':
						$class_filter = array('value' => 'rubriik,album', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_album', 'tyyp' => 'System'))), ); break;
						
					case 'pilt':
						$class_filter = array('value' => 'rubriik,album,pilt', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_pilt', 'tyyp' => 'System'))), ); break;
						
					default:
						$class_filter = array('value' => 'rubriik,artikkel', 'translation' => strtolower($site->sys_sona(array('sona' => 'tyyp_artikkel', 'tyyp' => 'System'))), ); break;
				}
				
				echo '<option value="'.$class_filter['value'].'"'.($value == $class_filter['value'] ? ' selected="selected"' : '').'>'.$class_filter['translation'].'</option>';
			?>
			<?php } ?>
		</select>
	</th><?php
}
