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
 * edit_artikkel.php
 *
 */

function edit_objekt()
{
	function print_profiles()
	{
		global $site, $objekt, $parent;

		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",'obj_artikkel');
		$sth = new SQL($sql);

		# get object profile
		if($objekt->all['profile_id']) {
			$profile_def = $site->get_profile(array("id"=>$objekt->all['profile_id']));
			$site->fdat['profile_id'] = $profile_def['profile_id'];
		}
		# if still not found then use default profile for this class
		if(!$profile_def['profile_id'] && !$site->fdat['profile_id']) {

			$site->fdat['profile_id'] = $site->get_default_profile_id(array("source_table" => 'obj_artikkel'));
			$profile_def = $site->get_profile(array("id"=>$site->fdat['profile_id']));
		}
		?>

<fieldset>
	<legend>
		<select onchange="changeProfile(this)" name="profile_id">
		<?php
		$all_profiles_hash = array();
		while ($profile_data = $sth->fetch()){
			$all_profiles_hash[] = $profile_data['id'];
			print "<option value='".$profile_data['id']."' ".($profile_data['id']==$site->fdat['profile_id'] ? '  selected':'').">".$site->sys_sona(array(sona => $profile_data['name'], tyyp=>"custom"))."</option>";
		} ?>
		</select>
	</legend>

	<?php foreach($all_profiles_hash as $profile_id) {	?>

	<div id="profile_<?= $profile_id ?>" style="display: <?=($site->fdat['profile_id'] == $profile_id ? 'block' : 'none');?>;">
		<table cellpadding="0" cellspacing="0">
	<?php
		$profile_def = $site->get_profile(array("id"=>$profile_id));
		$profile_fields = unserialize($profile_def['data']);

		# if profile fields exist
		if(is_array($profile_fields) && sizeof($profile_fields)>0){

			## add suffix for each field, to get unique id-s
			foreach($profile_fields as $key=>$tmp_prof){
				$profile_fields[$key]['html_fieldname'] = $profile_fields[$key]['name']."_".$profile_id;

				# field can be INPUT or READ-ONLY value - this info may be passed from triggers file "actions.inc.php", using "$site->fdat" array
				$profile_fields[$key]['is_readonly'] = $site->fdat['is_readonly_'.$key];
			}
			#printr($profile_fields);

			###################
			# print profile fields rows
			print_profile_fields(array(
				'profile_fields' => $profile_fields,
				'field_values' => $objekt->all,
			));

		} # if profile fields exist

	?>
		</table>
	</div>

	<?php } //foreach ?>

</fieldset>

<fieldset>
	<legend><?=$site->sys_sona(array('sona' => 'visible_to_visitors', 'tyyp' => 'editor'))?></legend>
	<input type="radio" name="publish" id="object_published" value="1"<?=($site->fdat['publish'] || $objekt->all['on_avaldatud'] ? ' checked' : '')?><?php echo (($objekt->permission && !$objekt->permission['P']) || (!$objekt->permission && !$parent->permission['P']) ? ' disabled="disabled"' : NULL); ?>> <label for="object_published"><?=$site->sys_sona(array('sona' => 'published', 'tyyp' => 'editor'))?></label><br>
	<input type="radio" name="publish" id="object_unpublished" value="0"<?=($site->fdat['publish'] == 0 && $objekt->all['on_avaldatud'] == 0 ? ' checked' : '')?><?php echo (($objekt->permission && !$objekt->permission['P']) || (!$objekt->permission && !$parent->permission['P']) ? ' disabled="disabled"' : NULL); ?>> <label for="object_unpublished"><?=$site->sys_sona(array('sona' => 'unpublished', 'tyyp' => 'editor'))?></label><br>
</fieldset>

<fieldset>
	<?php ####### dont show checkbox "Headline is visible" if config variable "killheadlineisvisible" is true in file config.php
	if($site->CONF['killheadlineisvisible']) { ?>

	<input type="hidden" id="on_pealkiri" name="on_pealkiri" value="<?=($site->fdat['op'] == 'new' ? 1 : $objekt->all['on_pealkiri'])?>">

	<?php } else { # by default: show it ?>
	<div>
		<input type="checkbox" id="on_pealkiri" name="on_pealkiri" value="1" <?=($site->fdat['op'] == 'new') ? 'checked' : ($objekt->all['on_pealkiri'] ? 'checked' : null)?>>
		<label for="on_pealkiri"><?=$site->sys_sona(array(sona => 'Pealkiri on nahtav', tyyp => 'editor'));?></label>
	</div>
	<?php } ?>
	<div>
		<input type="checkbox" name="on_foorum" id="on_foorum" value="1" <?=($objekt->all['on_foorum'] || ($site->fdat['op'] == 'new' && ($site->CONF['default_comments'] || $site->fdat['allow_comments'])) ? 'checked' : null)?>>
		<label for="on_foorum"><?=$site->sys_sona(array(sona => "Foorum lubatud", tyyp=>"editor"))?></label>
	</div>

	<?php if ($site->CONF['enable_mailing_list']) {  ## Bug #2590
	## fuzzy logic: if editor checks here checkbox "is mailinglist",
	# then field "on_saadetud" is set to "0" and that means article is included in next mailinglist routine.
	?>
	<div>
		  <input type="checkbox" name="on_saadetud" id="on_saadetud" value="1" <?=($objekt->all['on_saadetud'] ? null : "checked");?>>
		  <label for="on_saadetud"><?=$site->sys_sona(array(sona => 'On meilinglist', tyyp=>'editor'));?></label>
	</div>
	<?php } ?>

</fieldset>

<fieldset>
	<table cellpadding="1" cellspacing="1" border="0"  style="color: #999;">
		<?php if ($objekt->all['created_user_name']) { ?>
		<?php if ($site->CONF['allow_change_position']) { ?>
		<tr>
			<td><?=$site->sys_sona(array('sona' => 'position', 'tyyp' => 'Editor'));?>:</td>
			<td>
				<input type="text" name="kesk" value="<?=$objekt->all['kesk']?>" style="width: 40px; text-align: right;">
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td><?=$site->sys_sona(array('sona' => 'object_created', 'tyyp' => 'Editor'));?>:</td>
			<td><?=date('d.m.Y H:i', strtotime($objekt->all['created_time']));?></td>
		</tr>
		<tr>
			<td></td>
			<td><?=$objekt->all['created_user_name'];?></td>
		</tr>
		<?php } ?>
		<?php if ($objekt->all['changed_user_name']) { ?>
		<tr>
			<td><?=$site->sys_sona(array('sona' => 'object_changed', 'tyyp' => 'Editor'));?>:</td>
			<td><?=date('d.m.Y H:i', strtotime($objekt->all['changed_time']));?></td>
		</tr>
		<tr>
			<td></td>
			<td><?=$objekt->all['changed_user_name'];?></td>
		</tr>
		<?php } ?>
	</table>
</fieldset>




	<?php
	} // end function print_profile()

	function print_sections()
	{
		global $site, $objekt, $class_path, $keel;

		$tmpkeel = $keel;
		if (!is_numeric($tmpkeel)){
			$sql = $site->db->prepare("SELECT keel FROM objekt WHERE objekt_id=?",  $site->fdat['parent_id']);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			$tmpkeel = $sth->fetchsingle();
		}

	$sections = array($site->fdat['parent_id']);

	if ($objekt->objekt_id) {

		$sql = $site->db->prepare("SELECT parent_id FROM objekt_objekt WHERE objekt_id=?", $objekt->objekt_id);
		$result = new SQL ($sql);

		while ($data = $result->fetch('ASSOC'))
		{
			$sections[] = $data['parent_id'];
		}
	}

	if ($site->fdat['permanent_parent_id'] == '')
	{
		include_once($class_path.'rubloetelu.class.php');

		$all_sections = new RubLoetelu(array('keel' => $keel));
		$all_sections = $all_sections->get_loetelu();

		asort($all_sections);
	}
?>
<script type="text/javascript">
// the sections list
var all_sections = Array();

// for Mozilla the section list doesn't stretch dynamically so add this increment value to the containing elements height
var heightIncrement = 10;

function createSectionNodeSet(sectionNode, section_id, section_name)
{
	var input = document.createElement('input');
	input.type = 'hidden';
	input.name = 'rubriik[]';
	input.value = section_id;

	sectionNode.appendChild(input);

	var buttons = document.createElement('div');
	buttons.id='button_' + section_id;

	if(sectionNode.parentNode)
	{
		for(var i = 0; i < sectionNode.parentNode.childNodes.length; i++) if(sectionNode.parentNode.childNodes[i].tagName == 'LI') break;

		if(sectionNode.id != sectionNode.parentNode.childNodes[i].id)
		{
			var del_button = document.createElement('a');
			del_button.href = "javascript:deleteSection('" + section_id +"');";
			del_button.innerHTML = '<img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/gfx/editor/delete.gif">';
			buttons.appendChild(del_button);
		}
	}

	sectionNode.appendChild(buttons);

	var link = document.createElement('a');
	link.href = "javascript:opopup('"+ section_id +"');";
	link.innerHTML = section_name;

	sectionNode.appendChild(link);

	return sectionNode;
}
// add a new item to the section list

function opopup(section_id){
var pre_selected='';

if(section_id !=''){
	pre_selected = '&pre_selected=' + section_id;
	explorer_window = openpopup('explorer.php?objekt_id=home&editor=1&swk_setup=current_article_parent_selection' + pre_selected, 'cms_explorer', '800','600', 'auto');
}else{
	explorer_window = openpopup('explorer.php?objekt_id=home&editor=1&swk_setup=article_parent_selection', 'cms_explorer', '800','600', 'auto');
}


}


function addNewSection(node)
{

	explorer_window.close();
		for(var j = 0; j < node.length; j++){

			var error = false;
			for(var i = 0; i < all_sections.length; i++){
			/* no duplicates */
				if (all_sections[i] == node[j].objekt_id){
					//return;
					error = true;
				}else{

				}
			}
			if(!error){

				var trail_path= new Array();

					for(var z = 0; z < node[j].trail.length; z++){
						trail_path[z] = node[j].trail[z].pealkiri;
					}

				var sections = document.getElementById('sections');

				var item = document.createElement('li');
				item.id = 'section_' + node[j].objekt_id;
				sections.appendChild(item);
				item = createSectionNodeSet(item, node[j].objekt_id, trail_path.join("->"));
				var container = document.getElementById('sections_container');
				container.height = Number(container.height) + heightIncrement;

				all_sections[all_sections.length] = node[j].objekt_id;
				}




				for(var b = 0; b < all_sections.length; b++){
					var c = all_sections[b];
					var x = document.getElementById("button_" + c);

					x.style.visibility = "visible";

				}


		}


	return;

}
// /function addNewSection

function deleteSection(section_id)
{
	var section = document.getElementById('section_' + section_id);

	section.parentNode.removeChild(section);

	var container = document.getElementById('sections_container');
	//container.height = Number(container.height) - heightIncrement;

	// delete from duplicate checklist
	var new_all_sections =new Array();

	for(var i = 0; i < all_sections.length; i++){
		if (all_sections[i] == section_id){
			all_sections[i] = null;
		}else{

			new_all_sections.push(all_sections[i]);
		}
	}
all_sections=new_all_sections;

	var c = 0;
	var d = '';

	for(var b = 0; b < all_sections.length; b++){
		if (all_sections[b] != null){
			c++;
			d=all_sections[b];
		}
	}

	if(c == 1){
		document.getElementById('button_' + d).style.visibility = 'hidden';
	}

	return;
}
// /function deleteSection

function modifySection(node,section_id)
{

	explorer_window.close();
		for(var j = 0; j < node.length; j++){

			var error = false;
			for(var i = 0; i < all_sections.length; i++){
			/* no duplicates */
				if (all_sections[i] == node[j].objekt_id){
					//return;
					error = true;
				}else{

				}
			}
			if(!error){

				var trail_path= new Array();

					for(var z = 0; z < node[j].trail.length; z++){
						trail_path[z] = node[j].trail[z].pealkiri;
					}

				var section = document.getElementById('section_' + section_id);
				section.innerHTML = '';

				section = createSectionNodeSet(section, node[j].objekt_id, trail_path.join("->"));
				section.id = 'section_' + node[j].objekt_id;

					/* delete from duplicate checklist */
						for(var i = 0; i < all_sections.length; i++) if (all_sections[i] == section_id) all_sections[i] = null;
						all_sections[all_sections.length] = node[j].objekt_id;


				}




		}


	return;
}

// /function modifySection
</script>
<?
// setup for new section selection
$_SESSION['article_parent_selection']['callback'] = 'window.opener.addNewSection';
$_SESSION['article_parent_selection']['selectable'] = 2;
$_SESSION['article_parent_selection']['hide_language_selection'] = '1';
$_SESSION['article_parent_selection']['mem_classes'] = array('rubriik', ); //this sucks, really
$_SESSION['article_parent_selection']['db_fields'] = array('select_checkbox', 'objekt_id', 'pealkiri', );
$_SESSION['article_parent_selection']['display_fields'] = array('select_checkbox', 'pealkiri', );

// setup for current section change
$_SESSION['current_article_parent_selection']['callback'] = 'window.opener.modifySection';
$_SESSION['current_article_parent_selection']['selectable'] = 1;
$_SESSION['current_article_parent_selection']['hide_language_selection'] = '1';
$_SESSION['current_article_parent_selection']['mem_classes'] = array('rubriik', ); //this sucks, really
$_SESSION['current_article_parent_selection']['db_fields'] = array('select_checkbox', 'objekt_id', 'pealkiri', );
$_SESSION['current_article_parent_selection']['display_fields'] = array('select_checkbox', 'pealkiri', );
?>
		<div class="sections_header">
			<div>
				<a href="#" id="new_section" onClick="opopup(''); return false;"><?=$site->sys_sona(array(sona => "New", tyyp=>"editor"))?></a>
			</div>
			<?=$site->sys_sona(array(sona => "Rubriigid", tyyp=>"editor"))?>
		</div>
		<ul id="sections" class="sections">
		<?php

		$home_section = $site->alias(array('key'=>'rub_home_id', 'keel'=>$tmpkeel));
		$i = 0;
		foreach ($all_sections as $section_id => $section_name)
		{
			# Bug #2264: Uuele artiklile KAKS v�i rohkem eeldefineeritud parentit (triggers)
			if ($section_id != $home_section &&

			(in_array($section_id, $sections) || (is_array($site->fdat['parents_arr']) && in_array($section_id,$site->fdat['parents_arr'])))

			)
			{
			?>
			<script type="text/javascript">
				all_sections[all_sections.length] = <?=$section_id?>;
			</script>
<?if($i==0){$first_section=$section_id;}?>
			<li id="section_<?=$section_id;?>">
				<input type="hidden" name="rubriik[]" value="<?=$section_id;?>">
				<div id="button_<?=$section_id;?>"><a href="javascript:deleteSection('<?=$section_id;?>');"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/gfx/editor/delete.gif"></a></div>
				<a href="javascript:opopup('<?=$section_id;?>');"><?=$section_name;?></a>
			</li>
			<?php
				$i++;
			}
		}
?>
		</ul>
		<?php if($i == 1){?>

			<script type="text/javascript">
					document.getElementById('button_<?=$first_section;?>').style.visibility = 'hidden';
			</script>
		<?}?>

	  <!-- /rubriigid -->
	  <?php
	} /* end function print_sections2() */


	global $site, $class_path, $objekt, $tyyp, $keel;
	
	include_once($class_path.'adminpage.inc.php');
	include_once($class_path.'SCMSEditor.php');
	include_once($class_path.'extension.class.php');

	$editor = new SCMSEditor('scms_article_editor') ;

	if($site->fdat['op'] == 'new')
	{
		$editor->Value = '';
	}
	else
	{
		$editor->Value = ($objekt->lyhi->get_text() ? $objekt->lyhi->get_text().'<hr class="scms_lead_body_separator" />' : '').($objekt->sisu->get_text() ? $objekt->sisu->get_text() : '');

		// bug #2388  Tagaside vormist eemaldada e-maili aadress, tagasiasendus
		if(preg_match_all('/<input(.*?)>/', $editor->Value, $matches))
		{
			$systemfields = array();
			foreach ($matches[0] as $match)
			{
				if(strpos($match,'type="hidden"') && strpos($match,'name="systemfield"')) $systemfields[] = $match;
			}
			foreach ($systemfields as $systemfield)
			{
				if(preg_match('/value="(.*?)\|\|\|(.*?)\|\|\|(.*?)\|\|\|/', $systemfield, $matches))
				{
					$sql = $site->db->prepare('select mail from allowed_mails where id = ?;', $matches[1]);
					$result = new SQL($sql);
					$mail = $result->fetchsingle();

					$editor->Value = str_replace('value="'.$matches[1].'|||', 'value="'.$mail.'|||', $editor->Value);
				}
			}
		}
		// /form allowed mails check/insert
	}

	$editor->Height = '100%';
	$editor->Width = '100%';
	$editor->ToolbarSet = '';

	$editor->BasePath = (empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].$site->CONF['js_path'].'/fckeditor/';

	// create config array
	$Config['CustomConfigurationsPath'] = $editor->BasePath.'scms_config.js';
	$Config['SkinPath'] = $editor->BasePath.'editor/skins/scms/';
	$Config['ToolbarCanCollapse'] = false;

	$Config['SCMSFormName'] = $site->CONF['feedbackform_form_name'];
	$Config['SCMSFormAction'] = $site->CONF['feedbackform_action'];
	$Config['SCMSFormMethod'] = $site->CONF['feedbackform_method'];

	$Config['SCMSFormHiddenName'] = 'systemfield';
	$Config['SCMSFormHiddenString'] = $site->CONF["default_mail"].'|||index.php?id='.$site->alias(array('key'=>"error_page", 'keel'=>$keel )).'|||index.php?id='.$site->alias(array('key'=>"ok_page", 'keel'=>$keel )).'|||'.$site->CONF["subject"];

	$default_toolbar = 'SCMS_simple';
	if($_COOKIE['scms_toolbar'])
	{
		$default_toolbar = $_COOKIE['scms_toolbar'];
	}

	// load custom values for FCKeditor config
	foreach (get_extensions('DB', true) as $act_ext)
	{
		if(file_exists($act_ext['fullpath'].'/extension.config.php')) // assume this is the right one
		{
			$EXTENSION =& load_extension_config($act_ext);

			// set the toolbar, later TODO user based toolbars
			if($site->user->is_superuser)
			{
				if($EXTENSION['wysiwyg_config']['SuperUserToolbarSet'])
				{
					$editor->ToolbarSet = $EXTENSION['wysiwyg_config']['SuperUserToolbarSet'];
				}
				elseif($EXTENSION['wysiwyg_config']['DefaultToolbarSet'])
				{
					$editor->ToolbarSet = $EXTENSION['wysiwyg_config']['DefaultToolbarSet'];
				}
				else
				{
					$editor->ToolbarSet = $default_toolbar;
				}
			}
			else
			{
				$roles = array();
				$sql = 'select role_id, name from roles;';
				$result = new SQL($sql);
				while($row = $result->fetch('ASSOC')) {	$roles[$row[role_id]] = $row['name']; }

				foreach((array)$EXTENSION['wysiwyg_config']['ToolbarSets'] as $role => $set)
				{
					if($role)
					{
						$key = array_search($role, $roles);
						if($key !== null && in_array($key, $site->user->roles))
						{
							$editor->ToolbarSet = $set;
							break;
						}
					}
				}

				if(!$editor->ToolbarSet)
				{
					($EXTENSION['wysiwyg_config']['DefaultToolbarSet'] ? $editor->ToolbarSet = $EXTENSION['wysiwyg_config']['DefaultToolbarSet'] : $editor->ToolbarSet = $default_toolbar);
				}
			}
			// set the config
			if(is_array($EXTENSION['wysiwyg_config']['Config'])) $Config = array_merge($Config, $EXTENSION['wysiwyg_config']['Config']);
			break; // get only the first
		}
	}
	if(!$editor->ToolbarSet) $editor->ToolbarSet = $default_toolbar;

	$editor->Config = $Config;
	// somethings are not allowed to be overwritten
	$editor->Config['PluginsPath'] = $editor->BasePath.'editor/plugins/';
	$editor->Config['EditorAreaCSS'] = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/styles.php?with_wysiwyg=1';
	$editor->Config['CustomStyles'] = '';
	$editor->Config['StylesXmlPath'] = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/admin/fckstyles.php';

	$editor->Config['FormatOutput'] = false;
	$editor->Config['AutoDetectLanguage'] = false;
	$editor->Config['DefaultLanguage'] = substr($_SESSION['keel_admin']['locale'], 0, 2);
	$editor->Config['ProcessHTMLEntities'] = false;
	$editor->Config['Debug'] = false;
	//$editor->Config['FitWindow_autoFitToResize'] = true;
	$editor->Config['CurrentToolbar'] = $editor->ToolbarSet;

	//printr($editor->Config);
	//printr($editor->ToolbarSet);

	// setup for site linking
	$_SESSION['site_linking']['callback'] = 'window.opener.frames[0].site_linking';
	$_SESSION['site_linking']['selectable'] = 1;
	$_SESSION['site_linking']['mem_classes'] = array('rubriik', 'artikkel', ); //this sucks, really
	$_SESSION['site_linking']['db_fields'] = array('select_checkbox', 'objekt_id', 'pealkiri', 'klass',);
	$_SESSION['site_linking']['display_fields'] = array('select_checkbox', 'pealkiri', 'klass',);
	// /setup for site linking

	$parent = new Objekt(array('objekt_id' => $site->fdat['parent_id']));
	// to get the correct path to parent objects set use_alises on
	$site->CONF['use_aliases'] = 1;
	$parent_href = $parent->get_object_href();

	if($site->CONF['alias_trail_format'] == 0 || $parent->all['sys_alias'] == 'home' || $parent->all['sys_alias'] == 'trash' || $parent->all['sys_alias'] == 'system' || $parent->all['sys_alias'] == 'gallup_arhiiv') $parent_href = preg_replace('#'.preg_quote('/'.($parent->all['friendly_url'] ? $parent->all['friendly_url'] : $parent->objekt_id), '#').'/$#', '/', $parent_href);

	$parent_href = $site->CONF['hostname'].$parent_href;

	// setup file insert
	$_SESSION['scms_filemanager_settings']['scms_wysiwyg_insert_file'] = array(
		'select_mode' => 1, // 1 - select single file
		'action_text' => $site->sys_sona(array('sona' => 'fm_choose_file_into_article', 'tyyp' => 'editor')),
		'action_trigger' => $site->sys_sona(array('sona' => 'fm_insert_file_into_article', 'tyyp' => 'editor')),
		'callback' => 'window.opener.frames[0].SCMSImageFileInsert',
	);
?>
<html>

<head>

	<title><?=$site->title;?> <?=$site->cms_version;?></title>

	<meta http-equiv="Content-Type" content="text/html; charset=<?=($encoding ? $encoding : $site->encoding);?>">
	<meta http-equiv="Cache-Control" content="no-cache">
	<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'].'/article_editor.css';?>" media="screen">

	<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/yld.js"></script>
	<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/edit_popup.js"></script>
	<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/datepicker.css">
	<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/jquery.js"></script>
	<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/datepicker.js"></script>
	<script type="text/javascript" src="<?=$site->CONF['wwwroot'];?>/common.js.php"></script>

	<script type="text/javascript">
	function FCKeditor_OnComplete( editorInstance )
	{
		<?php if($objekt->objekt_id) { ?>
		var oSCMSEditor = FCKeditorAPI.GetInstance('scms_article_editor') ;
		oSCMSEditor.Focus();
		<?php } else { ?>
		document.frmEdit.pealkiri.focus();
		<?php } ?>
		window.moveTo((screen.width - 880) / 2, (screen.height - 660) / 2);
		window.resizeTo(880, 660);
	}

	function editAlias()
	{
		var alias_placeholder = document.getElementById('alias_placeholder');
		var alias_value = document.getElementById('alias_value');

		alias_placeholder.innerHTML = '<input type="text" id="alias" value="' + alias_value.value + '" onblur="saveAlias();">';

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
			    }
			});
		}
	}

	function saveForm(op2)
	{
		var form = document.getElementById('frmEdit');

		var title = document.getElementById('pealkiri')
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
			    	var form = document.getElementById('frmEdit');

			    	form.op2.value = op2;
	 				form.submit();
			    },
			    success: function(response)
			    {
			    	var alias_value = document.getElementById('alias_value');

			    	if(!alias_value && response.alias)
			    	{
				    	var alias_cell = document.getElementById('alias_cell');
				    	alias_cell.className = 'alias';
				    	alias_cell.innerHTML = '<input type="hidden" name="friendly_url" id="alias_value" value="' + response.alias + '"><?=$parent_href;?><span id="alias_placeholder"><a href="javascript:void(0);" onclick="editAlias();" id="alias_link">' + (response.alias.length > 30 ? response.alias.substring(0, 30) + '...' : response.alias) + '</a></span>';
			    	}

			    	var form = document.getElementById('frmEdit');

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

	<?php if ($editor->browser() == 'Gecko') { ?>
	<style type="text/css">
		table.layout td.editor div {
			height: 100%;
		}
	</style>
	<?php } ?>

</head>

<body id="scms_editor_popup">

	<form action="edit.php" method="POST" name="frmEdit" id="frmEdit" class="article_submit_form">
		
		<?php create_form_token('edit-article'); ?>
		
		<input type=hidden name="op" value="<?=$site->fdat['op'];?>">
		<input type=hidden name="op2" id="op2" value="saveclose">
		<input type=hidden name="refresh" value="0">

		<input type="hidden" name="tyyp_id" value="<?=$tyyp['tyyp_id'];?>">
		<input type="hidden" name="tyyp" value="<?=$tyyp['klass'];?>">
		<input type="hidden" name="sys_alias" value="<?=$site->fdat['sys_alias'];?>">

		<input type="hidden" name="id" value="<?=$site->fdat['id'];?>">
		<input type="hidden" name="kesk" value="<?=$site->fdat['kesk'];?>">
		<input type="hidden" name="parent_id" value="<?=$site->fdat['parent_id'];?>">
		<input type="hidden" name="previous_id" value="<?=$site->fdat['previous_id'];?>">
		<input type="hidden" name="keel" value="<?=$keel;?>">
		<input type="hidden" name="baseurl" value="<?=(empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'];?>/">
		<input type="hidden" name="wwwroot" value="<?=$site->CONF['wwwroot'];?>/">

        <input type="hidden" name="sorting" value="<?=$site->fdat['sorting'];?>">

		<input type="hidden" name="extension_path" value="<?=$site->fdat['extension_path'];?>">
		
		<input type="hidden" name="publish" value="<?php echo ($site->fdat['publish'] || $objekt->all['on_avaldatud'] ? 1 : 0); ?>">

	<table cellpadding="0" cellspacing="0" class="layout" border="0">
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" class="layout" border="0">
					<tr>
						<td class="header">
							<table cellpadding="0" cellspacing="0" border="0">
								<tr>
									<td style="	font-size: 12px;font-weight: bold;"><label for="pealkiri"><?=$site->sys_sona(array('sona' => 'Pealkiri', 'tyyp' => 'editor'))?>:&nbsp;</label></td>
									<td width="100%"><input type="text" tabindex="1" id="pealkiri" name="pealkiri" value="<?=htmlspecialchars($objekt->pealkiri);?>" onblur="createAlias();"></td>
								</tr>
							</table>
						</td>
					</tr>
					<?php if($objekt->objekt_id) { ?>
					<tr>
						<td class="alias"><input type="hidden" name="friendly_url" id="alias_value" value="<?=htmlspecialchars($objekt->all['friendly_url']);?>"><?=$parent_href;?><span id="alias_placeholder"><a href="javascript:void(0);" onclick="editAlias();" id="alias_link"><?=($objekt->all['friendly_url'] ? (strlen(htmlspecialchars($objekt->all['friendly_url'])) > 30 ? substr(htmlspecialchars($objekt->all['friendly_url']), 0, 30).'...' : htmlspecialchars($objekt->all['friendly_url'])) : $objekt->objekt_id);?></a></span></td>
					</tr>
					<?php } else { ?>
					<tr>
						<td id="alias_cell" class="alias">&nbsp;</td>
					</tr>
					<?php } ?>
					<tr>
						<td class="editor">
							<?=$editor->Create();?>
						</td>
					</tr>
					<tr>
						<td id="sections_container" class="sections">
							<?=print_sections();?>
						</td>
					</tr>
				</table>
			</td>
			<td class="profiles">
				<?=print_profiles();?>
			</td>
		</tr>
	</table>


	</form>
	<? if ($site->fdat['op']=='edit') {?>
		<iframe src="checkin.php?objekt_id=<?=$objekt->objekt_id ?>" style="width: 0; height: 0; display: none; visibility: hidden;"></iframe>
	<? } ?>

</body>

</html>

<?php

}
# / FUNCTION edit_objekt
#################################

#################################
# FUNCTION salvesta_objekt
function salvesta_objekt () {
	global $site;
	global $objekt;

	$class_path = "../classes/";
	
	verify_form_token();

	# -----------------------------
	# lyhi ja sisu koristamine
	# -----------------------------

	# ------
	# SISU
	# ------

	$sisu = $site->fdat['scms_article_editor'];

	//printr(htmlspecialchars($site->fdat['scms_article_editor']));

	//$hostname = ($site->CONF['protocol'] ? $site->CONF['protocol'] : "http://").$site->CONF['hostname'].$site->CONF['wwwroot'].$site->CONF['file_path'].'/';
	$hostname = (empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/';

	# asendada piltidel abosluutne tee  koodiga "##saurus649code##"
	$pattern1 = "|".'(src\s*=\s*[\"\']?)'.$hostname."|i";
	$pattern2 = "|".'(background\s*=\s*[\"\']?)'.$hostname."|i";
	# asendada failidel abosluutne tee  koodiga "##saurus649code##"
	$pattern3 = "|".'(href\s*=\s*[\"\']?)'.$hostname."|i";

	$sisu = preg_replace($pattern1, "\\1"."##saurus649code##".'/', $sisu);
	$sisu = preg_replace($pattern2, "\\1"."##saurus649code##".'/', $sisu);
	$sisu = preg_replace($pattern3, "\\1"."##saurus649code##".'/', $sisu);

	$lyhi = '';
	
	## search for LEAD tag and divide content into 2 fields in teh database: lyhi and sisu
	if(strpos($sisu, '<hr class="scms_lead_body_separator" />') !== false)
	{
		$sisu = explode('<hr class="scms_lead_body_separator" />', $sisu);
		$lyhi = $sisu[0];
		$sisu = $sisu[1];
	}
	
	if(strpos($sisu, '<hr class="scms_lead_body_separator">') !== false)
	{
		$sisu = explode('<hr class="scms_lead_body_separator">', $sisu);
		$lyhi = $sisu[0];
		$sisu = $sisu[1];
	}
	
	//Sisu
	$site->debug->msg('sisu: '.$sisu);
	$site->debug->msg('pattern: '.$pattern1);
	$site->debug->msg('pattern: '.$pattern2);
	$site->debug->msg('pattern: '.$pattern3);
	$site->debug->msg('sisu: '.$sisu);

	if ($objekt->objekt_id) {

		// form allowed mails check/insert bug #2277
		// teststring: value="merle@saurus.ee|||index.php?id=26675|||index.php?id=26674|||midagimidagi
		if(preg_match_all('/<input(.*?)>/', $lyhi.$sisu, $matches))
		{
			$systemfields = array();
			foreach ($matches[0] as $match)
			{
				if(strpos($match,'type="hidden"') && strpos($match,'name="systemfield"')) $systemfields[] = $match;
			}
			foreach ($systemfields as $key => $systemfield)
			{
				if(preg_match('/value="(.*?)\|\|\|(.*?)\|\|\|(.*?)\|\|\|/', $systemfield, $matches))
				{
					// delete form id from objekt_id_list
					$sql = "select id, objekt_id_list from allowed_mails where objekt_id_list like '%".$objekt->objekt_id.'_'.$key."%';";
					$result = new SQL($sql);
					while($row = $result->fetch('ASSOC'))
					{
						if($row['objekt_id_list']) $row['objekt_id_list'] = explode(',', $row['objekt_id_list']);
						else $row['objekt_id_list'] = array();

						if($row['id'])
						{
							unset($row['objekt_id_list'][array_search($objekt->objekt_id.'_'.$key, $row['objekt_id_list'])]);
							$sql = $site->db->prepare('update allowed_mails set objekt_id_list = ? where id = ?;', implode(',', $row['objekt_id_list']), $row['id']);
							new SQL($sql);
						}
					}
					// /delete form id from objekt_id_list

					$sql = $site->db->prepare('select id, objekt_id_list from allowed_mails where mail = ?;', trim($matches[1]));
					$result = new SQL($sql);
					$result = $result->fetch('ASSOC');
					$mail_id = $result['id'];
					if($result['objekt_id_list']) $objekt_id_list = explode(',', $result['objekt_id_list']);
					else $objekt_id_list = array();

					// insert id
					if(!$mail_id)
					{
						$sql = $site->db->prepare('insert into allowed_mails (mail, objekt_id_list) values (?, ?);', trim($matches[1]), $objekt->objekt_id.'_'.$key);
						$result = new SQL($sql);
						$mail_id = $result->insert_id;
					}
					//insert objekt_id, in obj_id_list
					$objekt_id_list[] = $objekt->objekt_id.'_'.$key;
					$sql = $site->db->prepare('update allowed_mails set objekt_id_list = ? where id = ?;', implode(',', $objekt_id_list), $mail_id);
					new SQL($sql);

					//replace mail address with allowed mails row id
					$lyhi = str_replace('value="'.$matches[1].'|||', 'value="'.$mail_id.'|||', $lyhi);
					$sisu = str_replace('value="'.$matches[1].'|||', 'value="'.$mail_id.'|||', $sisu);
				}
			}
		}
		// /form allowed mails check/insert

		if ($objekt->on_sisu_olemas)
		{
			# -------------------------------
			# Objekti uuendamine andmebaasis
			# -------------------------------
			$sql = $site->db->prepare("update obj_artikkel set lyhi=?, sisu=?, profile_id=?  WHERE objekt_id=?",
				$lyhi,
				$sisu,
				$site->fdat['profile_id'],
				$objekt->objekt_id
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}
		else
		{
			# -------------------------------
			# Objekti loomine andmebaasis
			# -------------------------------

			$sql = $site->db->prepare("insert into obj_artikkel (objekt_id, lyhi, sisu, profile_id) values (?,?,?,?)",
				$objekt->objekt_id,
				$lyhi,
				$sisu,
				$site->fdat['profile_id']
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

		}

		$site->debug->msg("sisu on salvestatud, objekt_id = ".$objekt->objekt_id);
	}
	else
	{
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}
}
# / FUNCTION salvesta_objekt
#################################
