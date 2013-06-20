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


###########################
# Changing configuration

global $site;
global $class_path;
global $groups;
global $selected_group;

if(!isset($class_path)) {
	$class_path = "../classes/";
}

include_once($class_path."port.inc.php");
include_once($class_path."adminpage.inc.php");

# if this file is included from "install.php" then dont do some part
global $is_installation_script;
if ($is_installation_script){
	$called_from_install_script = 1;
}

function print_group_selectbox($parent = 0) {
global $groups;
global $selected_group;

	$groups2 = $groups;
	foreach ($groups2 as $key=>$group) {
		if (is_array($group) && $group["parent"] == $parent) {
			echo '<option value="'.$group["id"].'" '.($group["id"] == $selected_group ? 'selected' : '').'>';
			for ($i = 2; $i < $group["level"]; $i++)
				echo "&nbsp;&nbsp;&nbsp;";
			echo $group["name"];
			echo '</option>';
			print_group_selectbox($group["id"]);
		}
	}
}
# / FUNCTION print_group_selectbox
#################################

if(!$called_from_install_script) {

	$site = new Site(array(
		on_debug=>0,
		on_admin_keel => 1
	));
	$site->fdat['flt_keel'] = (int)$site->fdat['flt_keel'];
	$site->fdat['group'] = (int)$site->fdat['group'];

	if (!$site->user->allowed_adminpage()) {
		exit;
	}

	######### get adminpage name
	$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
	$parent_pagename = $adminpage_names['parent_pagename'];
	$pagename = $adminpage_names['pagename'];

	if($site->fdat['group']==1){

		$sql = "SELECT sisu FROM config where nimi='time_zone'";

		$sthtz = new SQL($sql);
		$tz = $sthtz->fetch('ASSOC');

		$g1_tz=$tz[sisu];

		//We find the timezone ID from the database.

		$sst_id = $site->fdat[sst_id];

		######################
		# leida valitud keele p�hjal �ige lehe encoding,
		# admin-osa keel j��b samaks

		$sql = "SELECT keel_id, encoding FROM keel where on_kasutusel = '1'";
		$sth = new SQL($sql);
		$i = 0;
		while($result = $sth->fetch('ASSOC'))
		{
			if($i === 0)
			{
				$page_encoding = $result['encoding'];
				$keel_id = $result['keel_id'];
			}

			if(isset($site->fdat['flt_keel']) && $site->fdat['flt_keel'] == $result['keel_id'])
			{
				$page_encoding = $result['encoding'];
				$keel_id = $result['keel_id'];
			}
			$i++;
		}

		$op = $site->fdat[op];
		$site->debug->msg("OP = $op");

		# -------------------------
		# otsime Home-rubriigid
		# -------------------------
			$curr_objekt = new Objekt(array(
				objekt_id => $site->alias(array(
					'key' => 'rub_home_id',
					'keel' => $keel_id
				)),
				parent_id => "0",
				no_cache => 1
			));
			## BUG: juhul kui home objekti ei leidu, on $curr_objekt 404 vea objekt, ja sellele pole ju m�tet metadata-t salvestada. (Bug #1875)
			# seep�rast kontrolli �le, et sys_alias oleks �ige e "home":
			$conf = new CONFIG($curr_objekt->all['ttyyp_params']);
			foreach ($conf->CONF as $k=>$v){
				if($k=="page_end_html"){
					$curr_objekt->all[$k]=str_replace("XXYYZZ","\n",$v);
				}else{
					$curr_objekt->all[$k]=$v;
				}
			}
	#printr($conf->CONF);

			if($curr_objekt->all['sys_alias'] == 'home'){

				if ($site->fdat[save] && $curr_objekt && !$site->fdat[lang_swiched])
				{
					verify_form_token();

					$site->debug->print_hash($site->fdat,0,"FDAT");
					new Log(array(
						'action' => 'update',
						'component' => 'Admin',
						'message' => "Page '$parent_pagename > $pagename' was updated",
					));


					$q="update config set sisu='".(int)$site->fdat['timezone']."' where nimi='time_zone'";
					new SQL($q);


					$conf = new CONFIG($curr_objekt->all['ttyyp_params']);
					$conf->put('site_name', $site->fdat['site_name']);
					$conf->put('slogan', $site->fdat['slogan']);
					$conf->put('page_end_html', eregi_replace("\n","XXYYZZ",str_replace("\r\n","XXYYZZ",$site->fdat['page_end_html'])));

					$sql = $site->db->prepare(
						"UPDATE objekt SET meta_title=?, meta_keywords=?, meta_description=?, ttyyp_params=? WHERE objekt_id=?",
						$site->fdat['meta_title'], $site->fdat['meta_keywords'], $site->fdat['meta_description'], $conf->Export(), $curr_objekt->objekt_id
					);

					$sth = new SQL($sql);
					$site->debug->msg($sth->debug->get_msgs());
					clear_cache("ALL");

					$curr_objekt = new Objekt(array(
						objekt_id => $site->alias(array(
								'key' => 'rub_home_id',
								'keel' => $keel_id
						)),
						no_cache => 1
					));

					// is this in editor mode?
					if($site->fdat['keepThis'])
					{
						$editor_mode = true;
					}
					else
					{
						$editor_mode = false;
					}

					// if in editor mode refresh the original window and close the admin-popup
					if($editor_mode && $site->fdat['op'] == 'saveclose')
					{
						?>
						<script type="text/javascript">
							window.parent.location.href = window.parent.location.href.replace(/#$/, '');
							window.close();
						</script>
						<?php
						exit;
					}

					########### redirect
					header("Location: ".$site->self."?group=".$site->fdat['group']."&flt_keel=".$keel_id.($site->fdat['keepThis'] ? '&keepThis=true' : ''));
					exit;

				}
			} # sys_alias == 'home'

			## give error message
			else {
				echo "<font color=red>Error: Home section not found!</font>";
			}

	}



}


#$called_from_install_script = 1;
###########################
# function print_config_row

function print_config_row($tmp, $i) {
	global $site;
	global $groups;
	global $selected_group;

	############ 1) boolean YES/NO fields
	if (in_array($tmp[nimi],Array("only_regusers_comment", 'send_error_notifiations_to_superusers', 'fm_allow_multiple_upload', 'users_require_safe_password', 'feedbackform_check_for_captcha', 'force_https_for_editing', 'force_https_for_admin', 'check_for_captcha', 'allow_commenting', "default_comments", "users_can_register", "original_picture_saved", "alamartiklid_paises", "allow_autologin_from_ip", "enable_mailing_list",  "allow_change_position", "regusers_access_enabled", "allow_forgot_password", "notification_about_new_user_enabled","add_new_user_to_mailinglists","maillist_sending_after_publishing","save_error_log","users_can_delete_comment", 'use_aliases','redirect_to_alias','replace_links_with_alias','save_site_log','disable_form_based_login'))) {
		echo "
		<tr>
		<td style='width:331px' valign='top' align='left'>".$tmp[kirjeldus]."</td>
		<td nowrap class='scms_table_row'><input type='radio' name='cff_".$tmp[nimi]."' id='cff_".$tmp[nimi]."_1' value='1' ".($tmp[sisu]=="1"?"CHECKED":"")."> <label for='cff_".$tmp[nimi]."_1'>".($tmp[nimi] == "alamartiklid_paises" ? "Top-right" : "Yes")."</label> <input type='radio' name='cff_".$tmp[nimi]."' id='cff_".$tmp[nimi]."_2' value='0' ".($tmp[sisu]=="0"?"CHECKED":"")."> <label for='cff_".$tmp[nimi]."_2'>".($tmp[nimi] == "alamartiklid_paises" ? "Below" : "No")."</label></td>

		</tr>
		";
	}
	elseif($tmp[nimi]=='gallup_ip_check'){


		# Define array for possible answers

		$answers = array(
			1 => $site->sys_sona(array(sona => "Based on IP address", tyyp=>"admin")),
			2 => $site->sys_sona(array(sona => "Based on cookies", tyyp=>"admin")),
			3 => $site->sys_sona(array(sona => "Based on User id", tyyp=>"admin"))
		);

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";

			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>
		</tr>
		";



	}
	############ 2) THEME selectbox - scans directory
	elseif (in_array($tmp[nimi],Array("styles_path"))) {

		$tmp['sisu'] = $tmp['sisu'] ? $tmp['sisu'] : 'default'; # defautl value if config value is empty

		$dir_path = $site->absolute_path.'styles/';
		$dir = opendir($dir_path);
		while (false !== ($file = readdir($dir))) {
			if ($file != '.' && $file != '..' && $file != 'CVS') {
				$filelist[] = str_replace('.php', '', $file);
			} # if
		} # while
		closedir($dir);
		sort($filelist);
		foreach ($filelist as $file) {
			if ('/styles/'.$file == $tmp['sisu']) { $sel = ' selected'; }
			else { $sel = ''; }
			$text .= "<option value=\"/styles/".$file."\"".$sel.">".$file."</option>";
		}
		echo "
		<tr>
		<td style='width:331px' valign='top' align='left'>".$tmp[kirjeldus]."</td>
		<td nowrap class='scms_table_row'><select name='cff_".$tmp[nimi]."' id='cff_".$tmp[nimi]."' style='width:400px'> ";
		echo $text;
		echo "</select></td>
		</tr>
		";
	}
	############ 3) IMAGE MODE selectbox - GD/Imagemagick
	else if (in_array($tmp[nimi],Array("image_mode"))){

		# Define array for possible answers

		$answers = array ("Imagemagick", "GD lib");

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp[kirjeldus]."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp[nimi]."' id='cff_".$tmp[nimi]."_1' class='scms_flex_input' style='width:400px'>";

			foreach ($answers as $key => $value){

				echo "
				<OPTION value='".strtolower($value)."' ".($tmp[sisu]==strtolower($value)?"SELECTED":"").">$value</OPTION>
				";
			}

			echo "
			</select>

			</td>
		</tr>
		";
	}
	############  Form method selectbox
	else if (in_array($tmp['nimi'],Array('feedbackform_method',))){

		# Define array for possible answers

		$answers = array ('POST', 'GET');

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";

			foreach ($answers as $key => $value)
			{
				echo '<option value="'.strtolower($value).'"'.($tmp['sisu'] == strtolower($value) ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>

		</tr>
		";
	}
	############  error notifications selection
	else if ($tmp['nimi'] == 'send_error_notifiations_setting'){

		# Define array for possible answers

		$answers = array(
			0 => 'inactive',
			1 => 'pageload',
			2 => 'cronjob',
		);

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";

			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>
		</tr>
		";
	}
	else if ($tmp['nimi'] == 'maillist_format'){

		# Define array for possible answers

		$answers = array(
			0 => 'HTML',
			1 => 'Plain text',
			2 => 'HTML+plain text',
		);

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";

			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>
		</tr>
		";
	}
	else if ($tmp['nimi'] == 'mailinglist_sending_option'){

		# Define array for possible answers

		$answers = array(
			0 => 'One e-mail with all articles',
			1 => 'One e-mail for each mailing list',
			2 => 'One e-mail for each article',
		);

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>

			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";

			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>
		</tr>
		";
	}

	else if ($tmp['nimi'] == 'maillist_subject'){

		# Define array for possible answers

		$answers = array(
			0 => 'Default subject',
			1 => 'Section headline: article headline',
			2 => 'Section headline',
			3 => 'Article headline',
		);

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";

			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>
		</tr>
		";
	}
	else if ($tmp['nimi'] == 'maillist_article_title'){

		# Define array for possible answers

		$answers = array(
			0 => 'Article title',
			1 => 'Article time + date',
		);

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";

			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>

		</tr>
		";
	}
	else if ($tmp['nimi'] == 'maillist_article_content'){

		# Define array for possible answers

		$answers = array(
			0 => 'article lead + details link',
			1 => 'article lead + body',
		);

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";

			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>

		</tr>
		";
	}
	else if ($tmp['nimi'] == 'time_zone'){

		# Define array for possible answers

		$sql = "SELECT * from ext_timezones order by UTC_dif asc";
		$sth = new SQL($sql);
			while($data = $sth->fetch("ASSOC")){

				$answers[$data['id']]=$data['name'];

			}
#printr($answers);
		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";
				?><option value=""><?=$site->sys_sona(array(sona => "default_timezone", tyyp=>"admin"))?></option><?
			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>

		</tr>
		";
	}
	else if ($tmp['nimi'] == 'alias_trail_format'){

		# Define array for possible answers
		$answers = array(
			0 => '(/object-alias) - Object alias',
			1 => '(/section/subsection/object-alias) - Object alias with section trail',
		);

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";
			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>

		</tr>
		";
	}
	else if ($tmp['nimi'] == 'alias_language_format'){

		# Define array for possible answers
		$answers = array(
			0 => 'No site extension',
			2 => '(/en) - Site extension for non-default sites',
			1 => '(/en) - Site extension for all sites',
		);

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";
			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>

		</tr>
		";
	}
	else if ($tmp['nimi'] == 'context_menu_open_event'){

		# Define array for possible answers
		$answers = array(
			'click' => 'Left mouse click on the button',
			'hover' => 'When mouse hovers over the button',
		);

		echo "
		<tr>
		<td class='scms_table_row' style='width:331px' valign='top' align='left'>".$tmp['kirjeldus']."</td>
		<td nowrap class='scms_table_row'>
			<select name='cff_".$tmp['nimi']."' id='cff_".$tmp['nimi']."_1' class='scms_flex_input' style='width:400px'>";
			foreach ($answers as $key => $value)
			{
				echo '<option value="'.$key.'"'.($tmp['sisu'] ==$key ? ' selected="selected"':'').'>'.$value.'</option>';
			}

			echo "
			</select>

			</td>

		</tr>
		";
	}
	############ 4) TEXTAREA fields
	else if ($tmp['nimi'] == 'maillist_header' || $tmp['nimi'] == 'maillist_footer'){

		echo "
		<tr>
			<td style='width:331px' valign='top' align='left' style=\"padding-left: 3px;\">".$tmp['kirjeldus']."</td>
			<td nowrap class='scms_table_row'><textarea name=\"cff_".$tmp[nimi]."\" class='scms_flex_input' style=\"width:400px;height:30px\" cols='5'>".$tmp['sisu']."</textarea></td>

		</tr>
			";



	}

	############ 5) TEXT fields
	else {
		echo "
		<tr>
			<td style='width:331px' valign='top' align='left'>".$tmp[kirjeldus]."</td>
			<td nowrap class='scms_table_row'><input class='scms_flex_input' style='width:400px' type='text' name='cff_".$tmp[nimi]."' value='".$tmp[sisu]."'></td>

		</tr>
		";
	}

	############ additional HIDDEN fields
	if ($tmp[nimi] == "start_date_of_objects_counting") {
		echo "
			<input type=\"hidden\" name=\"objects_counting\" value=\"".$tmp[sisu]."\">
		";
	}
}
###########################
# function print_config_table

function print_config_table() {

	global $site;
	global $called_from_install_script;
#	$called_from_install_script = true;
	if ($called_from_install_script) {
		$site = new Site(array(
			on_debug=>0,
		));
		# force language to english when called from installation script:
		$site->keel = 1;
		$site->fdat['flt_keel'] = (int)$site->fdat['flt_keel'];
		$site->fdat['group'] = (int)$site->fdat['group'];
	}
	###########################
	# Define groups here - different for install.php and admin-pages
	###########################

	## 1. Conf groups displayed during installation (install.php)
	if ($called_from_install_script) {

		$configuration_group = array(
			"website_properties" => array(
				"from_email",
				"default_mail",
			),
			'aliases' => array(
				'use_aliases',
			),
		);

	}
	## 2. Conf groups displayed on conf admin-pages
	else {

		$site_properties_group = array(
			'forums_and_comments' => array(
				'allow_commenting',
				'only_regusers_comment',
				'comment_max_chars',
				'check_for_captcha',
				'feedbackform_check_for_captcha',
			),

		);

		$configuration_group = array(
			'users' => array(
				'users_can_register',
				'notification_about_new_user_enabled',
				'default_pass_expire_days',
				'users_require_safe_password',
				'new_user_password',
			),
			'users_login' => array(
				'max_login_attempts',
				'login_locked_time',
				'login_duration_time',
				'lock_inactive_user_after_x_days',
				'allow_forgot_password',
				'custom_login_url',
				'disable_form_based_login',
			),
			'forums_and_comments' => array(
				'allow_commenting',
				'only_regusers_comment',
				"default_comments",
				'comment_max_chars',
				'check_for_captcha',
				'feedbackform_check_for_captcha',
			),
			'mailing_list' => array(
				'enable_mailing_list',
				'maillist_sending_after_publishing',
				'maillist_interval',
				'maillist_sender_address',
				'maillist_send_newer_than',
				'maillist_reporter_address',
				'add_new_user_to_mailinglists',
			),

			'mailinglist_format' => array(
				'maillist_format',
				'mailinglist_sending_option',
				'maillist_subject',
				'maillist_article_title',
				'maillist_article_content',
				'maillist_header',
				'maillist_footer',
			),
			'feedbackforms_properties' => array(
				'from_email',
				'default_mail',
				'subject',
				'feedbackform_action',
				'feedbackform_form_name',
				'feedbackform_method',
			),
			"recycle_bin" => array(
				"trash_expires",
			),
			'aliases' => array(
				'use_aliases',
				'redirect_to_alias',
				'replace_links_with_alias',
				'alias_language_format',
				'alias_trail_format',
			),
			'timezone' => array(
				'time_zone',
			),
			'cache' => array(
				'cache_expired',
				'dont_cache_objects',
			),
			'Filemanager' => array(
				'fm_allow_multiple_upload',
			),
			'logging' => array(
				'save_site_log',
				'save_error_log'
			),
			'error_notifications' => array(
				'send_error_notifiations_to',
				'send_error_notifiations_to_superusers',
				'send_error_notifiations_setting',
			),
			'content_editing' => array(
				"allow_change_position",
				'context_menu_open_event',
			),

			'protocol' => array(
				"protocol",
				'force_https_for_editing',
				'force_https_for_admin'
			),

			'session_and_timeouts' => array(
				"php_max_execution_time",
				"php_memory_limit"
			),

			'debugging' => array(
				"display_errors_ip",
			),

			'proxy' => array(
				"proxy_server",
				"proxy_server_port"
			),
			'Gallup' => array(
				'gallup_ip_check'
			),
		);

	} # conf groups for install.php or admin-pages

	### icons:
	$icons = array(
		"website_properties" => '<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/actions/inweb.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"system" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/sysinfo.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"protocol" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/sysinfo.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"session_and_timeouts" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/sysinfo.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"debugging" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/sysinfo.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"proxy" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/sysinfo.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"logging" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/sysinfo.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"Gallup" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/sysinfo.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"recycle_bin" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/sysinfo.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"time_zones" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/html.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"gallery" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/images.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"content_editing" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/html.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',



		"counter" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/counter.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"users" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/users/group.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"users_login" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/users/group.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"cache" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/cache.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"mailing_list" => '<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/actions/mail_send.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',

		"mailinglist_format" => '<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/actions/mail_send.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',


		"Gallup" =>'<IMG SRC="'.($called_from_install_script? 'styles/default/' : $site->CONF['wwwroot'].$site->CONF['styles_path'].'/').'gfx/icons/16x16/mime/poll.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>',
	);


	$group_one = array_keys ($site_properties_group ? $site_properties_group : array());
	$group_two = array_keys ($configuration_group);

	# put all config values into one array
	$is_visible = array_merge($group_one,$group_two);

	if ($site->fdat['group'] == '1') {
		//$group = &$site_properties_group;
	}
	else {
		$group = &$configuration_group;
	}

	if (!is_array($group)) {
		$group = array();
	}
	$sql = "SELECT nimi, sisu, kirjeldus, on_nahtav FROM config ";

	$sth = new SQL($sql);

	while ($tmp = $sth->fetch() ) {

		# if this file is included from "install.php" then put real hostname & wwwroot values
		if($called_from_install_script && $tmp[nimi] == "hostname" && $_POST["install"]) {
			$tmp[sisu] = $site->hostname;
		}
		if($called_from_install_script && $tmp[nimi] == "wwwroot" && $_POST["install"]) {
			$tmp[sisu] = $site->wwwroot;
		}

		$v_config[$tmp[nimi]] = $tmp;
	}
	if ($site->fdat['group'] || $called_from_install_script) {
		############################################################
		#"gallery", "users", "mailing_list", "cache", "counter"
		############################################################

		foreach ($group as $grupp_name=>$values) {
			if (is_array($values)) {
				$i = 0;
				echo "
				<tr class='scms_pane_header'>
					<td nowrap colspan=\"2\">".$icons[$grupp_name]."&nbsp;&nbsp;".$site->sys_sona(array(sona => $grupp_name , tyyp=>"admin"))."</td>
				</tr>
				";
				foreach ($values as $config) {
					$i++;
					print_config_row($v_config[$config], $i);
				}
			}
		}
	}

	if ((is_array($v_config) && !$site->fdat['group']) && !$called_from_install_script) {

		#################################################
		#"built_in_templates", "website_properties", "system"
		#################################################
		foreach ($group as $grupp_name=>$values) {
			if (is_array($values)) {
				$i = 0;
				echo "
				<tr class='scms_pane_header'>
					<td nowrap colspan=\"2\">".($icons[$grupp_name]?$icons[$grupp_name].'&nbsp;':'').$site->sys_sona(array(sona => $grupp_name , tyyp=>"admin"))."</td>
				</tr>
				";
				foreach ($values as $config) {
					$i++;
					print_config_row($v_config[$config], $i);
				}
			}
		}

		if (!$called_from_install_script) {
			echo "
				<tr class='scms_pane_header'>
					<td  nowrap colspan=\"2\">Advanced</td>
				</tr>
				";
			$i = 0;

			foreach ($v_config as $conf_name=>$tmp) {
				if (!$tmp['on_nahtav'] && !in_array($conf_name, $is_visible)) {
					$i++;
					print_config_row($tmp, $i);
				}
			}
		}
	}
}
# / function print_config_table
###########################

// is this in editor mode?
if($site->fdat['keepThis'])
{
	$editor_mode = true;
}
else
{
	$editor_mode = false;
}

###########################
# if this file is not included from "install.php" print all

if(!$called_from_install_script) {


	##################
	# SAVE
	if ($site->fdat[salvesta]==1) {
		foreach ($site->fdat as $key=>$value) {

			if ( substr ($key, 0, 4) == "cff_" ) {
				$sql = $site->db->prepare("UPDATE config SET sisu=? WHERE nimi=?", $value, substr ($key, 4));
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());
			}
		}

		# special processing
		if ($site->fdat['cff_trash_expires']){
			$sql = $site->db->prepare("UPDATE config SET sisu=? WHERE nimi=?", time(), 'next_empty_trash');
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}

		#################################################
		# Special processing for object counting
		# if 'start_date_of_objects_counting' was changed
		# then delete all counters and restart
		# counting from that date
		if (isset($site->fdat['objects_counting'])) {
			if (strcmp($site->fdat['cff_start_date_of_objects_counting'],$site->fdat['objects_counting'])) {
				$sql = "UPDATE objekt SET count='0'";
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());
			}
		}
		# //
		#################################################
		clear_cache("ALL");
		new Log(array(
			'action' => 'update',
			'component' => 'Config',
			'message' => "Page '$parent_pagename > $pagename' was updated",
		));
	}
	# / SAVE
	##################

########### IF NEEDED ???:
	######### get adminpage name
	$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
	$parent_pagename = $adminpage_names['parent_pagename'];
	$pagename = $adminpage_names['pagename'];

	if ($site->fdat['group'] == '1') {
		$page_title = $site->sys_sona(array(sona => "Site_properties", tyyp=>"admin"));
	}
	else {
		$page_title = $site->sys_sona(array(sona => "Configuration", tyyp=>"admin"));
	}

	?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>');
//-->
</SCRIPT>
</head>

<body>



<?
#################
# CONTENT TABLE
?>
<table width="100%"  border="0" cellpadding="0" cellspacing="0" style="height:100%">
 <?
 ##############
 # FUNCTION BAR
 ?>
  <tr>
    <td class="scms_toolbar">
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		<?############ save button ###########?>
		<?php if($editor_mode) { ?>
	    <td nowrap><a href="javascript:document.forms['dataform'].op.value = 'save'; document.forms['dataform'].submit();"><img alt="<?=$site->sys_sona(array('sona' => 'apply', 'tyyp' => 'editor'))?>" title="<?=$site->sys_sona(array('sona' => 'apply', 'tyyp' => 'editor'))?>" src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/filesave.png" border="0" id="pt">&nbsp;</a></td>
	    <td nowrap><a href="javascript:document.forms['dataform'].op.value = 'saveclose'; document.forms['dataform'].submit();"><img alt="<?=$site->sys_sona(array('sona' => 'salvesta', 'tyyp' => 'editor'))?>" title="<?=$site->sys_sona(array('sona' => 'salvesta', 'tyyp' => 'editor'))?>" src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/saveclose.gif" border="0" id="pt"> <?=$site->sys_sona(array('sona' => 'salvesta', 'tyyp' => 'editor'))?></a></td>
		<?php } else { ?>
	    <td nowrap><a href="javascript:document.forms['dataform'].submit();""><img src="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/gfx/icons/16x16/actions/filesave.png" border="0" id="pt"> <?=$site->sys_sona(array(sona => "salvesta", tyyp=>"editor"))?></a></td>
		<?php } ?>

		<?###### wide middle cell ######?>
        <td width="100%"></td>
	<?if($site->fdat['group']==1){?>
		<?######  language ######?>
		<td style="padding-right: 10px">
		<? print_language_selectbox(); ?>
		</td>
		<?######  / language ######?>
	<?}?>

		</tr>
      </table>
    </td>
  </tr>
 <?
 # / FUNCTION BAR
 ################
 ?>
<?
if($site->fdat['group']==1){
?>


<tr>
  <td valign="top" height="100%">
	<div id=listing class="scms_middle_div" style="min-height: 440px">
	<?
	################
	# DATA TABLE
	?>
     <table width="100%" border="0" class="scms_table" cellspacing="10" cellpadding="0">
	<form action="<?=$site->self ?>" name="dataform" method=post>
	<?php create_form_token('change-config'); ?>
	<input type="hidden" name="group" value="1">
	<?php if($editor_mode) { ?>
		<input type="hidden" name="op" value="">
		<input type="hidden" name="keepThis" value="true">
	<?php } ?>
	<?
	################
	# Header
	?>
	<tr class="scms_pane_header">
		<td><!--<img src="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/gfx/icons/16x16/mime/metainfo.png" border="0" align=absmiddle>&nbsp; --><?=$site->sys_sona(array(sona => "Site_properties", tyyp=>"admin"))?></td>
	</tr>

	<?
	#################
	# DATA ROWS
	?>
	  <tr>
      <td height="100%" valign="top">

		  <table width="50%" border="0" cellspacing="0" cellpadding="3">

	<tr>
	<td class="scms_table_row">
	<?=$site->sys_sona(array(sona => "site_name", tyyp=>"admin"))?>
	</td>
	</tr>

	<tr>
	<td class="scms_table_row">
      <input type="text" name="site_name" class="scms_flex_input" value="<?=$curr_objekt->all['site_name']?>">
	</td>
	</tr>

	<tr>
	<td class="scms_table_row">	<?=$site->sys_sona(array(sona => "slogan", tyyp=>"admin"))?>	</td>
	</tr>

	<tr>
	<td class="scms_table_row">
      <input type="text" name="slogan" class="scms_flex_input" value="<?=$curr_objekt->all['slogan']?>">
	</td>
	</tr>
      </table>
		</div>
     </td>
    </tr>
	<?
	# / DATA ROWS
	#################
	?>
	<?
	################
	# DATA TABLE
	?>
     <table width="100%" border="0" class="scms_table" cellspacing="10" cellpadding="0">
	<?
	################
	# Header
	?>
	<tr class="scms_pane_header">

		<td><!--<img src="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/gfx/icons/16x16/mime/metainfo.png" border="0" align=absmiddle>&nbsp; --><?=$site->sys_sona(array(sona => "meta-info", tyyp=>"admin"))?></td>
	</tr>

	<?
	#################
	# DATA ROWS
	?>
	  <tr>
      <td height="100%" valign="top">

		  <table width="50%" border="0" cellspacing="0" cellpadding="3">

	<tr>
	<td class="scms_table_row">
	<?=$site->sys_sona(array(sona => "Saidi tiitel", tyyp=>"editor"))?>
	</td>
	</tr>

	<tr>
	<td class="scms_table_row">
      <input type="text" name="meta_title" class="scms_flex_input" value="<?=$curr_objekt->all[meta_title]?>">
	</td>
	</tr>

	<tr>
	<td class="scms_table_row">Meta-description	</td>
	</tr>

	<tr>
	<td class="scms_table_row">
      <textarea name="meta_description" class="scms_flex_input" rows="3" style="width:98%; height: 30px;"><?=$curr_objekt->all[meta_description]?></textarea>
	</td>
	</tr>
	<tr>

	<tr>
	<td class="scms_table_row">Meta-keywords</td>
	</tr>

	<tr>
	<td class="scms_table_row">
      <textarea name="meta_keywords" class="scms_flex_input" rows="2" style="width:98%; height: 30px;"><?=$curr_objekt->all[meta_keywords]?></textarea>
	</td>
	</tr>

	<td class="scms_table_row">
	<input type=hidden name=save value=1>
	<input type=hidden name=flt_keel value="<?=$site->fdat['flt_keel']?>">

	</td>
	</tr>

      </table>
		</div>
     </td>
    </tr>
	<?
	# / DATA ROWS
	#################
	?>
	</table>
	<?
	################
	# DATA TABLE
	?>
     <table width="100%" border="0" class="scms_table" cellspacing="10" cellpadding="0">
	<?
	################
	# Header
	?>
	<tr class="scms_pane_header">
		<td><?=$site->sys_sona(array(sona => "page_end_html", tyyp=>"admin"))?></td>
	</tr>

	<?
	#################
	# DATA ROWS
	?>
	  <tr>
      <td height="100%" valign="top">

		  <table width="50%" border="0" cellspacing="0" cellpadding="3">

	<tr>
	<td class="scms_table_row">
      <textarea name="page_end_html" class="scms_flex_input" rows="2" style="width:98%; height: 70px;"><?=$curr_objekt->all[page_end_html]?></textarea>
	</td>
	</tr>

      </table>
		</div>
     </td>
    </tr>
	<?
	# / DATA ROWS
	#################
	?>

	</table>
	<?
	################
	# DATA TABLE
	?>
     <table width="100%" border="0" class="scms_table" cellspacing="10" cellpadding="0">
	<?
	################
	# Header
	?>
	<tr class="scms_pane_header">
		<td><!--<img src="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/gfx/icons/16x16/mime/metainfo.png" border="0" align=absmiddle>&nbsp;--> <?=$site->sys_sona(array(sona => "timezone", tyyp=>"admin"))?></td>
	</tr>

	<?
	#################
	# DATA ROWS
		$sql = "SELECT * from ext_timezones order by UTC_dif asc";
		$sth = new SQL($sql);
			while($data = $sth->fetch("ASSOC")){

				$answers[$data['id']]=$data['name'];

			}
	?>
	  <tr>
      <td height="100%" valign="top">

	  <table width="50%" border="0" cellspacing="0" cellpadding="3">
			<tr>
			<td class="scms_table_row"><?=$site->sys_sona(array(sona => "timezone", tyyp=>"admin"))?></td>
			</tr>
			<tr>
			<td class="scms_table_row" >
			<select name="timezone" class="scms_flex_input" style="width:98%;">
					<option value=""><?=$site->sys_sona(array(sona => "default_timezone", tyyp=>"admin"))?></option>
				<?foreach($answers as $k=>$v){?>
					<option value="<?=$k;?>" <?if($k==$g1_tz){?> selected<?}?>><?=$v;?></option>
				<?}?>
			</select>
			</td>
			</tr>
      </table>
		</div>
     </td>
    </tr>
	<?
	# / DATA ROWS
	#################
	?>

	</table>
	</form>
	<?
	# / DATA TABLE
	################
	?>


</td>
</tr>


<?}else{?>

 <tr>
  <td width="100%" valign="top" class="scms_pane_area" height="100%">
	<?
	################
	# DATA TABLE
	?>
    <div id=listing class="scms_middle_div" style="min-height: 440px">
	<table width="100%" border="0" cellspacing="10" cellpadding="0" style="height:100%;">
	<form action="<?=$site->self ?>" name="dataform" method=post>
	<?php create_form_token('change-config'); ?>
	<?
	#################
	# DATA ROWS
	?>
		<tr>
			<td height="100%" valign="top">

			<table class="scms_table" width="100%"  border="0" cellspacing="0" cellpadding="4" id="contenttable">

				<?
				print_config_table();
				?>

			<tr><td></td></tr>
			</table>

			</td>
		</tr>
	<?
	# / DATA ROWS
	#################
	?>

	<input type=hidden name=salvesta value=1>
	<input type="hidden" name="group" value="<?=$site->fdat['group']?>">
	</form>
	</table>
	</div>
	<?
	# / DATA TABLE
	################
	?>


</td>
</tr>
<?}?>
</table>
<?
# / CONTENT TABLE
################
?>
	</body>
	</html>

	<?
	$site->debug->msg("SQL p�ringute arv = ".$site->db->sql_count."; aeg = ".$site->db->sql_aeg);
	$site->debug->msg("T��AEG = ".$site->timer->get_aeg());
	$site->debug->print_msg();
}
# IF INSTALL
