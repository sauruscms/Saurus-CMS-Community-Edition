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
 * Admin-page functions
 *
 * Frequently used admin-page functions. Not included by default.
 *
 */


/**
* FUNCTION get_visible_fields
*
* Returns assoc.array of visible fieldnames and translations for given preference name,
* from table 'preferences'. Queries 'preferences' table.
*
* @package CMS
*
*	get_visible_fields(array(
*		"prefpage_name" => 'xml_dir_fields',
*		"sst_name" => 'xml,custom'
*	));
*/
function get_visible_fields() {
	global $site;

	$args = func_get_arg(0);

	$prefpage_name = $args['prefpage_name']; # required, "preferences.name"
	$sst_name = $args['sst_name']; # optional, system word type NAME-s, comma separated, used in translation search

#deprecated	$labels = $args['labels']; # optional, for passing custom translations
#deprecated	$sst_id = $args['sst_id']; # required, system word type ID-s, comma separated, used in translation search

	$sst_arr = array();
	$sst_arr = split(",",$sst_name);
	$visible_fields = array();

	############## get preferences: which fields are visible
	$sql = $site->db->prepare("SELECT data FROM preferences WHERE name=?", $prefpage_name);
	$sth = new SQL($sql);
	$pref = $sth->fetchsingle();
	$data = unserialize($pref);

	############# if preference NOT FOUND and it's COLUMN FIELDS prefernce
	# then ADD NEW preference - get DEFAULT values and make these fields visible
	if(!is_array($data) ){
		############ DEFAULT preference values
		$tables = array(
			"user_management_fields" => array("table" => "users", "fields" => "lastname,firstname,email"),
			"xml_dir_fields" => array("table" => "xml", "fields" => "dir_path,direction,delete_old_data,el_start,is_active"),
			"select_group" => array("table" => "users", "fields" => "lastname,firstname,email"),
			"keeled_fields" => array("table" => "keel", "fields" => "nimi,encoding,extension,on_default,on_default_admin,site_url"),
			"pagetemplates_fields" => array("table" => "templ_tyyp", "fields" => "nimi, templ_fail,on_nahtav"),
			"contenttemplates_fields" => array("table" => "templ_tyyp", "fields" => "nimi, templ_fail,on_nahtav"),
			"log_fields" => array("table" => "logi", "fields" => "aeg,sisestaja,text"),
			"glossary_fields" => array("table" => "sys_sonad", "fields" => "sona,origin_sona,sst_id"),
			"file_management_fields" => array("table" => "obj_file", "fields" => "pealkiri,filename, mimetype,size"),
		);
		# if we know, from which table to retrieve fields info, go on
		if(in_array($prefpage_name,array_keys($tables))){
			# 1) get DEFAULT values
			$default_fields = split(",",$tables[$prefpage_name]["fields"]);
#			printr($default_fields);
			# 2) get all table FIELDS
			$fields = split(",",$site->db->get_fields(array("table" => $tables[$prefpage_name]["table"] )));
			foreach($fields as $field){
				# make it visible if it's default field
				$data[$field] = array('fieldname' => $field, 'is_visible' => (in_array($field,$default_fields)?'1':'0') );
			}
			######### INSERT to database
			if(!$sth->rows) {
			$sql = $site->db->prepare("INSERT INTO preferences (data,name) VALUES (?,?)",serialize($data),$prefpage_name);
			$sth = new SQL($sql);
			}
			######### UPDATE
			else {
			$sql = $site->db->prepare("UPDATE preferences SET data=? WHERE name=?",serialize($data),$prefpage_name);
			$sth = new SQL($sql);

			} # insert/update
		} # if table is known
	} # preference not found

	############# LOOP over fields
	foreach($data as $key => $value) {
		if($value['is_visible']) {
			# get translation: search translation from given types (in the given order!)
			$translation = '';
			foreach($sst_arr as $sysword_type) {
				$translation = $site->sys_sona(array(sona => $value['fieldname'], tyyp=> $sysword_type));
#				print "<br> sysword_type: ".$sysword_type." => found ".$translation;
				# if found translation then don't search anymore
				if($translation && substr($translation,0,1)!='[' && substr($translation,-1)!=']' ) { break; }
			}
			$visible_fields[$value['fieldname']] = $translation;
		}
	}
	return $visible_fields;
}


/**
* FUNCTION print_column_headers
*
* @package CMS
*
*	print_column_headers(array(
*		"visible_fields" => $visible_fields,
*		"page_prefs_url" => '&table=keel&name=keeled_fields&sst_name=custom,kasutaja'
*	));
*/
function print_column_headers() {
	global $site;
	$args = func_get_arg(0);

	$visible_fields = &$args['visible_fields']; # required, assoc.array of fieldnames and translations
	$page_prefs_url = $args['page_prefs_url']; # rquired, url for popup pref page

	echo '<table width="100%"  border="0" cellspacing="0" cellpadding="0">
       <tr id="headerrow">';
	echo '<td nowrap><IMG SRC="'.$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/px.gif" WIDTH="16" HEIGHT="1" BORDER="0" ALT=""></td>';

	# set sort base link, viska vana parameeter lingist v???lja:
	$sort_baselink = $site->URI;
	$sort_baselink = preg_replace("/\&sortby=(\w+)/i","",$sort_baselink); # field to sort by
	$sort_baselink = preg_replace("/\&sort=(\w+)/i","",$sort_baselink); # sort direction: desc/asc
	$sort_baselink = preg_replace("/\?sortby=(\w+)/i","?",$sort_baselink); # field to sort by
	$sort_baselink = preg_replace("/\?sort=(\w+)/i","?",$sort_baselink); # sort direction: desc/asc
	# add & or ? to the end of URL if not found:
	$sort_baselink = $sort_baselink.(substr($sort_baselink,-1)!='&' && substr($sort_baselink,-1)!='?'?($_SERVER["QUERY_STRING"]?"&":"?"):'');

	##### width: calculate percents
	if ($visible_fields){
		$td_width = intval((100/sizeof(array_keys($visible_fields)))).'%';
	} else {
		$td_width = '1%';
	}

	if (!is_array($visible_fields)){$visible_fields = array();}

	# loop over visible fields
	foreach(array_keys($visible_fields) as $key=>$field){
		if($field=='fullname') { $sortfield = 'firstname';}
		else {  $sortfield = $field; }

		###### default sort: if no sorting set - sort by first column asc
		if(!$site->fdat['sortby'] && $key==0) {
			$site->fdat['sortby'] = $sortfield;
			if(!$site->fdat['sort']) {
				$site->fdat['sort'] = 'asc';
			}
		}
		##### href
		$href = $sort_baselink.'sort='.($site->fdat['sortby']==$sortfield && $site->fdat['sort']=='asc'?'desc':'asc').'&sortby='.$sortfield;
		?>
        <td width="<?=$td_width?>" onClick="document.location='<?=$href?>'"  <?=($site->fdat['sortby']==$sortfield ? 'class="scms_tableheader_active"' : '')?> ><a href="<?=$href?>"><?=$visible_fields[$field]?><?####### arrow ?><?if($site->fdat['sortby']==$sortfield) {?><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/sort_<?=$site->fdat['sort']=='asc'?'up':'down'?>.gif" width="20" height="9" border=0><?}?></a></td>

	<? } # foreach
	########## open preferences popup
	?>

	</tr></table>
	<?
}
# / function print_column_headers()
######################



/**
* FUNCTION print_pagenumbers
*
* @package CMS
*
*	print_pagenumbers(array(
*		"total_count" => 2000,
*		"rows_count" => 20,
*       OR
*		"rows_count" => array(20,50,100),
*		"always_visible" => 0/1 default "0", // If "1", then show pagenumbers, even not found any records
*	));
*/
function print_pagenumbers() {
	global $site;

	$args = func_get_arg(0);

	$total_count = $args['total_count']; # required
	$rows_count = $args['rows_count']; # optional
	$always_visible = $args['always_visible']; # optional

	if (is_array($rows_count) && count($rows_count)>0){
		$rows_count_arr = $rows_count;
		if (is_numeric($site->fdat['rows_count'])){
			$rows_count = $site->fdat['rows_count'];
		} else {
			$rows_count = $rows_count_arr[0];
		}
	}

	if(!$rows_count) { $rows_count = $site->CONF['komment_arv_lehel']; }
	if(!$rows_count) { $rows_count = 20; }

	######## gather all fdat values into url string
	foreach($site->fdat as $fdat_field=>$fdat_value) {
		if($fdat_field != 'page'){
			#echo $fdat_field."=".$fdat_value."<hr>";
			$url_parameters .= '&'.$fdat_field."=".$fdat_value;
			$hidden_parameters .= '<input type=hidden name="'.$fdat_field.'" value="'.$fdat_value.'">';
		}
	}
	$pagenumbers = get_page_numbers(array(
		"total" => $total_count,
		"limit" => $rows_count,
		"amount_of_comment_pages" => 1,
		"p_text" => '&nbsp;',
		"next_chr" => '<img src="'.$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/actions/right.png" alt="Previous" width="16" height="16" border="0">',
		"prev_chr" => '<img src="'.$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/actions/left.png" alt="Previous" width="16" height="16" border="0">',
		"url" => $url_parameters,
	));
	$site->fdat['page'] = $pagenumbers['current_pagenumber']?$pagenumbers['current_pagenumber']:'1';

	if($pagenumbers['pagenumbers_count'] > 1 || $always_visible){
	echo '<table width="100" border="0" cellspacing="0" cellpadding="0" align=right>
    <tr>
	<td nowrap> '.$site->sys_sona(array(sona => "found", tyyp=>"admin")).' '.$total_count.'</td>
    <TD><IMG SRC="'.$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/general/s_toolbar_divider.gif" WIDTH="14" HEIGHT="20" BORDER="0" ALT="" align=absmiddle></TD>';
	?>
	  <form name="pageform" action="<?=$site->self?>" method="GET">
			<?=$hidden_parameters?>


<? if (count($rows_count_arr)>0) { ?>
			<td>
				<select name="rows_count" class="scms_flex_input" style="width:44px" onChange="document.forms['pageform'].page.value=1; this.form.submit();">
<?
foreach ($rows_count_arr as $cnt){
	echo '<option value="'.$cnt.'"  '.($site->fdat['rows_count']==$cnt ? 'selected':'').'>'.$cnt.'</option>\n';
}
?>
				</select>
			</td>

			<TD><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/s_toolbar_divider.gif" WIDTH="14" HEIGHT="20" BORDER="0" ALT="" align=absmiddle></TD>
<? } ?>



            <td align="right" valign="middle"><?if($pagenumbers['previous']){ echo $pagenumbers['previous']; } else {?><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/left.png" alt="Previous" width="16" height="16" border="0"><?}?></td>
			<td align="right" valign="top" nowrap><input name="page" id="page" type="text" class="scms_flex_input" value="<?=$site->fdat['page']?>" size="2" style="width:24px"></td>
			<td align="left" valign="middle" nowrap> &nbsp; / <?=$pagenumbers['pagenumbers_count'] ?></td>
            <td align="left" valign="middle" style="padding-right:4px"><?if($pagenumbers['next']){ echo $pagenumbers['next']; } else {?><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/right.png" alt="Next" width="16" height="16" border="0"><?}?></td>
	</form>
	</tr>
	</table>
	<?

	}
	return $pagenumbers;
}
# / function print_pagenumbers
#################


/**
* FUNCTION print_language_selectbox
*
* Prints language selectbox on admin-pages
*
* @package CMS
*
*	print_language_selectbox();
*/
function print_language_selectbox() {
	global $site;
	global $keel_id;

	$sql = "SELECT nimi,keel_id FROM keel WHERE on_kasutusel = '1' ORDER BY nimi";
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	?>
    <table border="0" cellspacing="0" cellpadding="1">
	<form name="languageform" method=get>
	<? foreach($site->fdat as $fdat_field=>$fdat_value) {
		if($fdat_field != 'flt_keel') {?>
		<input type=hidden name="<?=$fdat_field?>" value="<?=$fdat_value?>">
	<?	}
	}?>
	<?php if($sth->rows > 1) { ?>
        <tr>
          <td nowrap align="right"><?=$site->sys_sona(array(sona => "keel", tyyp=>"editor"))?>:</td>
          <td>
	<?if(!isset($site->fdat['lang_swiched'])) {?>
		<input type=hidden name=lang_swiched value=0>
	<?}?>
	<select name="flt_keel" onChange="document.forms['languageform'].lang_swiched.value='1';document.forms['languageform'].submit();">
	<?
			while ($keel = $sth->fetch()) {
				if($keel['keel_id'] == $site->fdat['flt_keel']) {
					$selected='selected';
				}
				elseif(!isset($site->fdat['flt_keel']) && ($keel['keel_id'] == $keel_id)){
					$selected='selected';
				}
				else {$selected='';}
				print "	<option value=\"".$keel[keel_id]."\" ".$selected.">".$keel[nimi]."</option>";
			}
	?>		</select>
          </td>
        </tr>
	<?php } else { $keel = $sth->fetch('ASSOC') ?>
		<input type="hidden" name="flt_keel" value="<?=$keel['keel_id']?>">
	<?php } ?>
	</form>
	</table>
<?
}
# / function print_language_selectbox
#################

/**
* FUNCTION print_glossary_selectbox
*
* Prints glossary selectbox on admin-pages
*
* @package CMS
*
*/
function print_glossary_selectbox() {
	global $site;
	global $keel_id;

	//$sql = "select distinct a.nimi as nimi, b.glossary_id as glossary_id from keel as a left join keel as b on a.keel_id = b.glossary_id where b.on_kasutusel = '1' order by a.nimi";
	$sql = "select distinct keel as glossary_id, nimi from sys_sonad left join keel on keel = keel_id order by nimi";
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	?>
    <table border="0" cellspacing="0" cellpadding="1">
	<form name="languageform" method=get>
	<? foreach($site->fdat as $fdat_field=>$fdat_value) {
		if($fdat_field != 'flt_keel') {?>
		<input type=hidden name="<?=$fdat_field?>" value="<?=$fdat_value?>">
	<?	}
	}?>
	<?php if($sth->rows > 1) { ?>
        <tr>
          <td nowrap align="right"><?=$site->sys_sona(array('sona' => 'translations', 'tyyp' =>'admin'))?>:</td>
          <td>
	<?if(!isset($site->fdat['lang_swiched'])) {?>
		<input type=hidden name=lang_swiched value=0>
	<?}?>
	<select name="flt_keel" onChange="document.forms['languageform'].lang_swiched.value='1';document.forms['languageform'].submit();">
	<?
			while ($keel = $sth->fetch()) {
				if($keel['glossary_id'] == $site->fdat['flt_keel']) {
					$selected='selected';
				}
				elseif(!isset($site->fdat['flt_keel']) && ($keel['glossary_id'] == $keel_id)){
					$selected='selected';
				}
				else {$selected='';}
				print "	<option value=\"".$keel['glossary_id']."\" ".$selected.">".$keel[nimi]."</option>";
			}
	?>		</select>
          </td>
        </tr>
	<?php } else { $keel = $sth->fetch('ASSOC') ?>
		<input type="hidden" name="flt_keel" value="<?=$keel['glossary_id']?>">
	<?php } ?>
	</form>
	</table>
<?
}
# / function print_glossary_selectbox
#################


/**
* get_adminpage_name
*
* returns adminpage name and page parent name
*
* @package CMS
*
* @param string script_name - filename of adminpage
*
* list($parent_pagename, $pagename,$parent_pagename_eng, $pagename_eng) = get_adminpage_name(array(
*	"script_name" => $site->script_name
* ));
*/
function get_adminpage_name() {
	global $site;
	$args = func_get_arg(0);

	$sql = $site->db->prepare("SELECT b.eng_nimetus AS parent_pagename, a.eng_nimetus AS pagename FROM admin_osa a, admin_osa b WHERE a.parent_id = b.id AND a.fail like ?",'%'.$args['script_name']);
	$sth = new SQL($sql);

	$names = array();
	$names = $sth->fetch('ASSOC');
	$names['parent_pagename'] = $site->sys_sona(array(sona => $names['parent_pagename'] , tyyp=>"admin"));
	$names['pagename'] = $site->sys_sona(array(sona => $names['pagename'] , tyyp=>"admin"));

	return $names;

}
# / function print_language_selectbox
#################


/**
* print_profile_fields
*
* prints profile input fields in right format;
* used in object/user/group/etc edit-popup window;
* prints 2 columns: 1) profile label 2) input field
*
* Note: function "print_profile_fields()" is used for EDITING profile fields,
* function "format_profile_values()" is used for just SHOWING profile fields
*
* @package CMS
*
* @param array field_values - array of object values, $field_values['field_name'] = $field_value
* @param array profile_fields - array of profile fields, already unserialized data
* @param fields_width - width of cell, where field located. Can be used as integer or with % (150 or 50%)
* @param boolean return_fields - 0: print hml (default); 1: return fields html as array
*
*/
function print_profile_fields() {
	global $site;

	$args = func_get_arg(0);
	$profile_fields = $args['profile_fields'];
	$field_values = $args['field_values'];
	$fields_width =  ($args['fields_width'] ? $args['fields_width'] : '');
	$return_fields =  $args['return_fields'] ? true : false;
	$load_defaults =  $args['load_defaults'] ? true : false;

	$fields_html = array(); ## for gathering fields html into array (if "return_fields" is true)

	$row_html = ''; ## row html generated in this function
	$field_html = ''; ## one field html generated in this function

	$form_error = $site->fdat['form_error']; # is array of errors, keys are fieldnames and values are error messages

	###################
	# loop over attributes / profile fields
		if(is_array($profile_fields)) {
	foreach($profile_fields as $field => $value) {

		$field = strtolower($field); #Bug #2560
		# if field is active
		if( $value['is_active'] ) {
		########## label

		if ($value['sys_sona'] && $value['sys_sona_tyyp']){
		$label = $site->sys_sona(array('sona' => $value['sys_sona'], 'tyyp'=> $value['sys_sona_tyyp'], 'lang_id'=>$site->glossary_id));
		} else {
		$label = $site->sys_sona(array('sona' => $value['name'], 'tyyp'=>"custom", 'lang_id'=>$site->glossary_id));
		}
		$label = ($label != '['.$profile_info["name"].']' ? $label : '');	# kui s???steemis???na puudub

		########### value
		# 1) if page was saved and field has error then show uncorrect field value
		if($form_error[$field]) {
			$field_values[$field] = $site->fdat[$field];
		}
		# 2) if page was only refreshed then show entered form field value
		elseif($site->fdat['refresh']){
##			$field_values[$field] =  $site->fdat[$field];
		}



		# for select lists, get profile id
		$source_profile_id = $value['source_object'];

		####################
		# get selectlist
		$sel_list = array();
		if ($source_profile_id) {
			# mis on tabeli nimi??
			## get profile -st tabeli nimi!!
			$sql_table = $site->db->prepare("SELECT source_table FROM object_profiles WHERE profile_id =?", $source_profile_id);
			$sth_table = new SQL ($sql_table);
			list($source_profile_table) = $sth_table->fetch();


			#print "--".$source_profile_table."--";


			########### get all users
			if ($source_profile_table=='users') { # if data source is system table "users"
				$sql_select = $site->db->prepare("
					SELECT CONCAT(users.firstname,' ',users.lastname) AS pealkiri, users.user_id AS id
					FROM users WHERE profile_id LIKE ?
					ORDER BY pealkiri ", ($source_profile_id=='38'?'%':$source_profile_id)
				);
			}
			########### get ext_ table content (  if feilds "name" and "id" found)
			elseif (substr($source_profile_table,0,4)=='ext_') { # prefix is "ext_"
				$sql_select = $site->db->prepare("
					SELECT name AS pealkiri, id
					FROM ".$source_profile_table."
					WHERE profile_id LIKE ?
					ORDER BY name ",
					$source_profile_id
				);
			}
			########### get all ASSETS (exclude objects in trash)
			elseif($source_profile_table=='obj_asset') {
				# for compability with version 3: field "varchar_1" is used as additional field for title (for same title values). bug #1582
				if(version_compare($site->cms_min_version,"4.0.0")<0){ # if coming from version 3
					$select_add = ", obj_asset.varchar_1 as pealkiri2";
				}
				#NB! maha v???etud: WHERE ---> objekt.keel=? AND <---   + $site->keel,
				# Bug #2611
				$sql_select = $site->db->prepare("
					SELECT objekt.pealkiri,obj_asset.objekt_id AS id ".$select_add."
					FROM objekt
					LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id
					LEFT JOIN obj_asset on objekt.objekt_id=obj_asset.objekt_id
					WHERE objekt.tyyp_id=? AND obj_asset.profile_id=? AND objekt_objekt.parent_id<>?
					ORDER BY objekt.pealkiri ",
					"20",
					$source_profile_id,
					$site->alias("trash")
				);
			} # if source_object
			########### get all ARTICLES (exclude objects in trash)
			elseif($source_profile_table=='obj_artikkel') {
				$sql_select = $site->db->prepare("
					SELECT objekt.pealkiri,obj_artikkel.objekt_id AS id ".$select_add."
					FROM objekt
					LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id
					LEFT JOIN obj_artikkel on objekt.objekt_id=obj_artikkel.objekt_id
					WHERE objekt.tyyp_id=? AND obj_artikkel.profile_id=? AND objekt_objekt.parent_id<>?
					ORDER BY objekt.pealkiri ",
					"2",
					$source_profile_id,
					$site->alias("trash")
				);
			} # if source_object
			#print $sql_select;
			$sth_select = new SQL ($sql_select);
			######## 1. add record to array
			$tmp_title = '';
			while($tmp = $sth_select->fetch() ) {
				# if asset titles are equal, then remember these titles
				if($tmp_title == $tmp['pealkiri'] ) {
					$rememb_titles[] = $tmp['pealkiri'];
				}
				$sel_list[] = $tmp;
				$tmp_title = $tmp['pealkiri'];
			} # while asset list
			#printr($rememb_titles);

			######### 2. rare case (for compability with version 3): if asset titles are equal, then show additional title after / symbol
			# ( info is taken from the field obj_asset.varchar_1)

			for($i=0; $i<sizeof($sel_list);$i++) {

				if(is_array($rememb_titles) && in_array( $sel_list[$i]['pealkiri'], $rememb_titles) ) {
					# get additional asset name:
					$sql_select2 = $site->db->prepare("SELECT objekt.pealkiri FROM objekt 				LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id					LEFT JOIN obj_asset on objekt.objekt_id=obj_asset.objekt_id	WHERE objekt.keel=? AND objekt.tyyp_id=? AND objekt_objekt.parent_id<>? AND objekt.objekt_id=?	ORDER BY objekt_objekt.sorteering DESC ", $site->keel,	"20",	$site->alias("trash"),	$sel_list[$i]['pealkiri2'] );
					$sth_select2 = new SQL ($sql_select2);
					$tmp_pealkiri2 = $sth_select2->fetchsingle();
					if($tmp_pealkiri2){
						$sel_list[$i]['pealkiri'] .= ' / '. $tmp_pealkiri2;
					}
				} # rare case: if titles are equal
			} # 2. if asset titles are equal

		}
		# / get selectlist
		####################

	# set FIELDNAME: html fieldname can be given as parameter, usually its the same with field value. or with prefix
	if($value['html_fieldname']) {
		$fieldname = $value['html_fieldname'];
	}
	else { $fieldname = $field; }

	# set FIELDVALUE: if error then show previous value, if no error => show object value or default value
	# fixed Bug #2044: Profiili v???lja vaikev??????rtus ei m???junud tavalise tekstiv???lja korral
	if(($site->fdat['op']=='new' || $load_defaults) && $value['default_value']){
		$fieldvalue = $value['default_value']; # default value
	}
	elseif ($site->fdat['op']=='new' && $site->fdat[$field])
	{
		$fieldvalue =  $site->fdat[$field];
	}
	elseif(($field_values[$field] && !$form_error[$field]) || $field_values[$field] == "0" ){ 	# fixed Bug #2600: Profiilis kohustuslikuks m��ratud v�lja ei saa sisestada v��rtuseks 0
		$fieldvalue = $field_values[$field]; # object value
	}
	else { # if error
		$fieldvalue = $site->fdat[$fieldname]; # entered form value
	}

	########################### START ROW HTML

	$row_html = '<tr>';
	######## LABEL + required #######
	$row_html .= '<td nowrap valign=top>'. ($value['is_required']?'*':'').$label.':' .'<input type=hidden name="required_'. $fieldname .'" value="'. $value['is_required'] .'"></td>';

	####### FIELD #######
	$row_html .= '<td width="100%">';

		$field_html = '';
		#### 1) kui v???li pole read-only, siis n???ita input elementi:
		if( !$value['is_readonly'] ) {
			#### vali vastavalt t??????bile ???ige html v???ljastuskuju: #####
			########### TEXTAREA ###########

			if($value['type'] == "TEXTAREA") {

				# replace line breaks
				$fieldvalue = str_replace("<br />", "\n",$fieldvalue);
				$fieldvalue = str_replace("<br>", "\n",$fieldvalue);

				/* bug #2230 */
				$field_html = '<textarea name="'. $fieldname .'" id="'.  $field  .'" rows="10" style="width:100%">'.  stripslashes($fieldvalue)  .'</textarea>';
			########### SELECT ###########
			} elseif($value['type'] == "SELECT") {
				$field_html = '<select name="'.  $fieldname  .'" id="'.  $field  .'" style="width:100%">
				<option value="" '. ($field_values[$field]?"":"selected") .'></option>';
				foreach($sel_list as $sel_item) {
					$field_html .= '<option value="'.  $sel_item['id']  .'" '.  ($fieldvalue == $sel_item['id'] || (($site->fdat['op']=='new' || $load_defaults) && $value['default_value'] == $sel_item['id'])?"selected":"")  .'>'.  htmlspecialchars(stripslashes($sel_item['pealkiri']))  .'</option>';
				}
				$field_html .= '</select>';

			########### MULTIPLE SELECT ###########
			} elseif($value['type'] == "MULTIPLE SELECT") {

				$field_html = '<select name="'.  $fieldname  .'[]" id="'.  $field  .'" multiple style="WIDTH: 99%; height: 70px" size=7>';
				foreach($sel_list as $sel_item) {
					$field_html .= '<option value="'.  $sel_item['id']  .'" '. ( in_array($sel_item['id'], split(",",$fieldvalue))?"selected":"")  .'>'.  htmlspecialchars(stripslashes($sel_item['pealkiri']))  .'</option>';
				}
				$field_html .= '</select>';
			########### BROWSE ###########
			} elseif($value['type'] == "BROWSE") {

				##### DISABLE invalid data sources by disabling browse button:
				# right now allow BROWSE only for system table "users" and "groups",
				# because similar file to "admin/select_group.php" is always needed to open browse popup
				if (! ($source_profile_table=='users' || $source_profile_table=='groups') ) { $disabled=' disabled'; }

				if(!$value) { # if default value was passed as parameter
					$value = $site->fdat[$fieldname];
				}
				####### show value : user readable value
				$show_value = $field_values[$field];

				# show value: if data source is system table "users" or "groups", do extra splitting
				if ($source_profile_table=='users' || $source_profile_table=='groups' ) {
					# split value 'user_id:65' or 'group_id:20'
					list($sel_type,$sel_id) = split(":",$field_values[$field]);
					if($sel_type == 'group_id') {
						$group = new Group(array(group_id => $sel_id));
						$show_value = $group->name;
					} # if group ID
					elseif($sel_type == 'user_id') {
						$user = new User(array(user_id => $sel_id));
						$show_value = $user->name;
					} # if user ID
				}
				####### / show value : user readable value


				$js = "javascript:void(openpopup('".$site->CONF['wwwroot'].$site->CONF['adm_path']."/select_group.php?select_one=1&paste2box=".$fieldname."&pastename2box=tmptmp_".$fieldname."','selectgroup','500','500'))";

			 $field_html = '<input type="text" name="tmptmp_'.  $fieldname  .'" id="tmptmp_'.  $fieldname  .'" value="'.  $show_value  .'" class="scms_flex_input" style="width:80%" onchange="javascript:if(this.value==\'\'){document.getElementById(\''. $fieldname .'\').value=\'\';}"><input type="hidden" name="'.  $fieldname  .'" id="'.  $fieldname  .'" value="'.  $field_values[$field]  .'">&nbsp;<INPUT TYPE="button" value="..." onclick="'. $js .'" '. $disabled .'>';

			########### RADIO ###########
			} elseif($value['type'] == "RADIO") {
			$field_html = '<table border="0" cellspacing="0" cellpadding="0">';
			foreach($sel_list as $sel_item) {
				$z++;
              $field_html .= '<tr>
                <td style="padding:0px">
				<input id="'.  $fieldname.$z  .'" type=radio name="'.  $fieldname  .'" value="'.  $sel_item['id']  .'" '. ( (in_array($sel_item['id'], split(",",$fieldvalue))  || (($site->fdat['op']=='new' || $load_defaults) && $value['default_value'] == $sel_item['id']) ) ?"checked":"" )  .'>
				</td>
                <td style="padding:0px"><label for="'. $fieldname.$z .'">'.  htmlspecialchars(stripslashes($sel_item['pealkiri']))  . '</label></td>
			  </tr>';
			}
			$field_html .= '</table>';

			########### CHECKBOX ###########
			} elseif($value['type'] == "CHECKBOX") {
            $field_html = '<table border="0" cellspacing="0" cellpadding="0">';
			foreach($sel_list as $sel_item) {
				$z++;
				########### undocumented feature: field with value "[separator]" is seprating line
				if($sel_item['pealkiri'] == '[separator]') {
					$field_html .= '<tr><td colspan=2 style="padding:0px"><hr "size=1px"></td></tr>';
				}
				## usual stuff
				else {
	              $field_html .= '<tr>
                <td style="padding:0px"><input id="'.  $fieldname.$z  .'" type=checkbox name="'.  $fieldname  .'[]" value="'.  $sel_item['id']  .'" '. ( (in_array($sel_item['id'], split(",",$fieldvalue))  || (($site->fdat['op']=='new' || $load_defaults) && $value['default_value'] == $sel_item['id']) )?"checked":"" )  .'>
					</td>
		            <td style="padding:0px"><label for="'. $fieldname.$z  .'">'.  htmlspecialchars(stripslashes($sel_item['pealkiri']))  .'</label></td>
			  </tr>';
				}
			}
			$field_html .= '</table>';
			########### BOOLEAN ###########
			} elseif($value['type'] == "BOOLEAN") {
            $field_html = '<table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td style="padding:0px">
				<input type=checkbox id="tmptmp_'.  $fieldname  .'" name="tmptmp_'.  $fieldname  .'" value="1" '.  ($fieldvalue || (($site->fdat['op']=='new' || $load_defaults) && $value['default_value'])  ?"checked":"") .' onclick="if(this.checked){document.getElementById(\''. $fieldname .'\').value=\'1\';}else {document.getElementById(\''. $fieldname .'\').value=\'0\';}">
				<input type=hidden id="'.  $fieldname  .'" name="'.  $fieldname  .'" value="'.  ($fieldvalue ||  (($site->fdat['op']=='new' || $load_defaults) && $value['default_value'])?"1":"0") .'">
				</td>
                <td style="padding:0px"><label for="tmptmp_'. $fieldname .'">'. $site->sys_sona(array(sona => "yes", tyyp=>"editor")) .'</label></td>
			  </tr>
			</table>';
			########### FILE ###########
			} elseif($value['type'] == "FILE") {
			// setup file insert
			$_SESSION['scms_filemanager_settings']['scms_profile_file_'.$fieldname] = array(
				'select_mode' => 1, // 1 - select single file
				'action_text' => $site->sys_sona(array('sona' => 'fm_choose_file_into_profile_field', 'tyyp' => 'editor')),
				'action_trigger' => $site->sys_sona(array('sona' => 'fm_insert_file_into_profile_field', 'tyyp' => 'editor')),
				'callback' => 'window.opener.profile_file_callback_'.$fieldname,
			);

			$field_html = '<script type="text/javascript">var filemanager; function profile_file_callback_'.$fieldname.'(data) { document.getElementById("'.$fieldname.'").value = ".." + data.files[0].folder + "/" + data.files[0].filename; filemanager.window.close();}</script>
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
              <tr>
                <td style="padding:0px" width="90%">
					<input type="text" id="'.  $fieldname  .'" name="'.  $fieldname  .'" value="'.  $fieldvalue  .'" class="scms_flex_input" style="'. ($fields_width ? "width:".$fields_width:"")  .'">
				</td>
				<td style="padding:0px">
					<a href="javascript:void(0);" onclick="filemanager = openpopup(\''.$site->CONF['wwwroot'].'/admin/filemanager.php?setup=scms_profile_file_'.$fieldname.'\',\'filemanager\', 980, 600);"><IMG title="'. $site->sys_sona(array(sona => "nupp: Lisa pilt", tyyp=>"editor"))  .'"  alt="" src="'. $site->CONF['wwwroot'].$site->CONF['adm_img_path']  .'/image.gif"  border=0></a>
				</td>
			  </tr>
			</table>';

			########### DATE ###########
			} elseif($value['type'] == "DATE") {
				$date = $fieldvalue;
				### format date but only if there's no error and the date is not already formatted
				$date = $date && $date != '0000-00-00' ? ( !$form_error[$field] && !strpos($date, '.') ? $site->db->MySQL_ee($date) : $date) : $date = "";

			$field_html = '<table border="0" cellspacing="0" cellpadding="0">
              <tr>';
				######### date box
                $field_html .= '<td style="padding:0px">
					<input type="text" id="'. $fieldname .'" name="'. $fieldname .'" value="'.  $date  .'" class="scms_flex_input" style="width:80px; text-align: right;">
				</td>
                <td style="padding:0px">&nbsp;</td>';
				######### calendar
                $field_html .= '<td style="padding:0px"><a href="#"><img src="'. $site->CONF['wwwroot'].$site->CONF['styles_path'] .'/gfx/calendar/cal.gif" width="16" height="15" hspace="4" border="0" alt="Calendar" onclick="init_datepicker(\''. $fieldname.'\')"></a></td>
              </tr>
            </table>';

			########### DATETIME ###########
			} elseif($value['type'] == "DATETIME") {
				### format date but only if there's no error and the date is not already formatted
				$datetime = $fieldvalue;
				$datetime = $datetime && $datetime != '0000-00-00 00:00:00' ? ( !$form_error[$field] && !strpos($datetime, '.') ? $site->db->MySQL_ee_long($datetime) : $datetime) : "";

				# datetime is now in format dd.mm.yyyy hh:mm => split it
				if(!$form_error[$field])
				{
					$date = substr($datetime,0,10);
					$time = substr($datetime,11,5);
				}
				else
				{
					//split from space
					$date = trim(substr($datetime, 0, strpos($datetime,' ')));
					$time = trim(substr($datetime, strpos($datetime,' ')));
				}
			 $field_html = '<table border="0" cellspacing="0" cellpadding="0">
              <tr>';
				######### generate result refreshing js
				$refresh_result = 'document.getElementById(\''. $fieldname .'\').value=document.getElementById(\'tmp_datedate'. $fieldname .'\').value+ \' \'+document.getElementById(\'tmp_time'. $fieldname .'\').value';

				######### date box
                $field_html .= '<td style="padding:0px">
					<input type="text" id="tmp_datedate'. $fieldname .'" name="tmp_datedate'. $fieldname .'" value="'.  $date  .'" class="scms_flex_input" style="width:80px; text-align: right;" onkeyup="'.$refresh_result.'" onfocus="'.$refresh_result.'" onblur="'.$refresh_result.'">
				</td>
                <td style="padding:0px">&nbsp;</td>';
				######### time box
                $field_html .= '<td style="padding:0px">
                  <input type="text" id="tmp_time'. $fieldname .'" name="tmp_time'. $fieldname .'"  class="scms_flex_input" style="width:40px; text-align: right;" value="'.  $time .'" onkeyup="'.$refresh_result.'" onblur="'.$refresh_result.'">';
				######### hidden field with result value
				$field_html .= ' <input type="hidden" id="'.  $fieldname  .'" name="'.  $fieldname  .'" value="'.  $datetime  .'" >
                </td>';
				######### calendar
                $field_html .= '<td style="padding:0px"><a href="#" onblur="'.$refresh_result.'" onclick="init_datepicker(\'tmp_datedate'. $fieldname.'\')"><img src="'. $site->CONF['wwwroot'].$site->CONF['styles_path'] .'/gfx/calendar/cal.gif" width="16" height="15" hspace="4" border="0" alt="Calendar"></a></td>
              </tr>
            </table>';

			########### INTEGER/FLOAT => TEXT (short) ###########
			} elseif($value['db_type'] == "integer" || $value['db_type'] == "float") {
				$field_html = '<input type="text" name="'.  $fieldname  .'" id="'.  $fieldname  .'" value="'.  htmlspecialchars(stripslashes($fieldvalue))  .'" class="scms_flex_input" style="width:80px; text-align: right;">';

			########### addition type PASSWORD ###########
			} elseif($value['type'] == "password" || $value['name']=='password') {
				$field_html = '<input type="password" name="'.  $fieldname  .'" id="'.  $fieldname  .'" value="" class="scms_flex_input" style="'. ($fields_width ? "width:".$fields_width:"")  .'">';

			########### else => TEXT (long) ###########
			} else {
				$field_html = '<input type="text" name="'.  $fieldname  .'" id="'.  $fieldname  .'" value="'.  htmlspecialchars(stripslashes($fieldvalue))  .'" class="scms_flex_input" style="'. ($fields_width ? "width:".$fields_width:"")  .'">';
			}
		######## error message ######
		$field_html .= $form_error[$field]?'<br><font color=red><b>'.$form_error[$field].'</b></font>':'';

		}
		#### 2) kui v???li on read-only, siis n???ita ainult v???lja v??????rtust:
		else {
			$formatted_values = format_profile_values(array(
				"profile_data" => $args['profile_fields'],
				"data" => $args['field_values'],
			));
			$field_html = $formatted_values[$value['name']];
			## show hidden field with correct value

			if($value['type'] == "DATE") {
				$fieldvalue = $fieldvalue ? $site->db->MySQL_ee($fieldvalue) : "";
			}
			elseif($value['type'] == "DATETIME") {
				$fieldvalue = $fieldvalue ? $site->db->MySQL_ee_long($fieldvalue) : "";
			}
#			elseif($value['type'] == "BOOLEAN") {
#				$fieldvalue = ($fieldvalue == $site->sys_sona(array(sona => "yes", tyyp=>"editor")) ? 1 : 0);
#			}
			$field_html .= '<input type="hidden" name="'. $fieldname .'" value="'.htmlspecialchars(stripslashes($fieldvalue)). '">';

		} # if read-only

		####### / field html
		$row_html .= $field_html;
		$row_html .= '</td></tr>';

		########################### / END ROW HTML

		### depending on parameter "return_fields" either print html out or gather into variable
		if($return_fields) { # gather variable
			$fields_html[$value['name']] = $field_html;
		}
		### PRINT OUT HTML (default)
		else {
			echo $row_html;
		} # print html or save it
		} # if active

		### reset html values
		$field_html = '';
		$row_html = '';
	} # foreach
	} # if array
	# / loop over attributes
	###################

	if($return_fields) { # if gather variable
		return $fields_html;  # return html-array
	}
}

/**
* format_profile_values
*
* returns profile fields array in right format:
* changes selectlist ID-s to names, changes date formats, etc
* It is used before printing out profile fields rows: name + value.
*
* Note: function "print_profile_fields()" is used for EDITING profile fields,
* function "format_profile_values()" is used for just SHOWING profile fields.
*
* @package CMS
*
* @param pointer profile_data - pointer to array of profile fields
* @param pointer data - array of object data
*
* Call example:
*	$profile_data = unserialize($profile_def['data']); # field object_profiles.data
*	$data = $group->all;
*	$formatted_values = format_profile_values(array(
*		"profile_data" => &$profile_data,
*		"data" => &$data,
*	));
* where
*/
function format_profile_values() {
	global $site;

	$args = func_get_arg(0);
	$profile_data = $args['profile_data'];
	$data = $args['data'];

	$profile_fields = array_keys($profile_data);

	# result values:
	$profile_values = array();

	if(is_array($profile_fields)) {

	########## 1. loop over fields and get fieldvalues for select-lists (beacuse only ID-s are saved in database)
	foreach ($profile_fields as $field) {
		# if is select && if value is not empty
		if($profile_data[$field]['source_object'] && $data[$field]){
			# value can be comma-separated list of ID-s, split it
			$values = split(",",$data[$field]);
			foreach($values as $value){
				$selectlist[] = $value;
			}
		} # if can add
	}
	######### 2. get selectlist values - 1 extra sql per function; sql is fast
	if( sizeof($selectlist)>0 ) {
		$selectlist = array_unique($selectlist);
		####### get names of asset objects
		$sql = $site->db->prepare("SELECT objekt.pealkiri,objekt.objekt_id	FROM objekt WHERE objekt.objekt_id IN(".join(",",$selectlist).")" );
		$sth_names = new SQL ($sql);

		while($tmp_names = $sth_names->fetch()) {
			$asset_names[$tmp_names['objekt_id']] = $tmp_names['pealkiri'];
		}
	}
	######### 3. get final field value

	reset($profile_fields);

	foreach ($profile_fields as $field) {
		# if SELECT LIST
		if($profile_data[$field]['source_object']) {
			# change attribute from asset ID => asset NAME
			# value can be comma-separated list of ID-s, split it
			$ids = split(",",$data[$field]);
			$new_value = $data[$field];
			foreach($ids as $id){
				$new_value = str_replace($id,$asset_names[$id],$new_value);
			}
			# pane komade taha t???hikud:
			$new_value = str_replace(",",", ",$new_value);
			$profile_values[$field] = $new_value;
		}
		# if FILE
		elseif($profile_data[$field]['type'] == 'FILE') {
			$profile_values[$field] = $data[$field] ? '<a href="'.$data[$field].'" target=_blank>'.$data[$field].'</a>' : "";
		}
		# if DATE
		elseif($profile_data[$field]['type'] == 'DATE') {
			$profile_values[$field] = $data[$field] && $data[$field]!='0000-00-00'? $site->db->MySQL_ee($data[$field]) : "";
		}
		# if DATETIME
		elseif($profile_data[$field]['type'] == 'DATETIME') {
			$profile_values[$field] = $data[$field] ? $site->db->MySQL_ee_long($data[$field]) : "";
		}
		# if BOOLEAN
		elseif($profile_data[$field]['type'] == 'BOOLEAN') {
			$profile_values[$field] = $data[$field] ? $site->sys_sona(array(sona => "yes", tyyp=>"editor")) : $site->sys_sona(array(sona => "no", tyyp=>"editor"));
		}
		# usual TEXT value
		else{
			/* bug #2230 */
			$profile_values[$field] = stripslashes($data[$field]);
		}
	} # foreach

	} # is array
	return $profile_values;
}


/**
* save_systemword
*
* saves system word to database:
* checks if word exist for given language, if not then inserts it, othwerwise updates translation.
*
*
* @package CMS
*
* @param string sysword - unique systemword name itself (sys_sona)
* @param string translation - translation for given language
* @param int lang_id - language ID where to save systemword
* @param int sst_id - systemword type ID where to save systemword
*
*/
function save_systemword() {
	global $site;

	$args = func_get_arg(0);
	$sysword = $args['sysword'];
	$translation = $args['translation'];
	$lang_id = $args['lang_id'];
	$sst_id = $args['sst_id'];

	########## 1. kontrolli, kas selle keele jaoks s???ss???na leidub
	# erand: kui v???li on predefined, siis v???ib tema t???lge olla juba olemas teise t??????bi all kui parameetriga antud t??????p.
	# seep???rast otsi predefined v???ljade puhul s???ss???na K???IGIST t??????pidest:
	if($site->fdat['is_predefined']){ # predefined v???li
		$sql = $site->db->prepare("SELECT COUNT(*) FROM sys_sonad WHERE sys_sona LIKE ? AND keel=? ", $sysword, $lang_id);
	}
	else { # tavaline/custom v???li
		$sql = $site->db->prepare("SELECT count(*) FROM sys_sonad WHERE sys_sona LIKE ? AND keel=? AND sst_id=?", $sysword, $lang_id, $sst_id);
	}
	$sth_s = new SQL($sql);
	$site->debug->msg($sth_s->debug->get_msgs());
	$exists = $sth_s->fetchsingle();

	######### 2. kui selle keele s???ss???na ei leidu, siis LISA
	if (!$exists) {
		# lisa ainult siis kui s???ss???na ja t???lge pole t???hi
		if($sysword && $translation) {
			# sys_sonad
			$sql = $site->db->prepare("INSERT INTO sys_sonad (sys_sona, keel, sona, sst_id) values(?,?,?,?)", $sysword, $lang_id, $translation, $sst_id);
			$sth_i = new SQL($sql);
			$site->debug->msg($sth_i->debug->get_msgs());

			# sys_sonad_kirjeldusse lisa ainult siis kui s???ss???na ???hegi keele jaoks olemas ei ole
			# kontrolli, kas ???le???ldse s???ss???na leidub
			$sql = $site->db->prepare("SELECT count(*) FROM sys_sonad_kirjeldus WHERE sys_sona like ? and sst_id=?", $sysword, $sst_id);
			$sth_s = new SQL($sql);
			$site->debug->msg($sth_s->debug->get_msgs());
			$sysword_exists = $sth_s->fetchsingle();
			# kui ???ldse s???s???na ei leidu, siis lisa see:
			if (!$sysword_exists) {
				$sql = $site->db->prepare("INSERT INTO sys_sonad_kirjeldus (sys_sona, sona, sst_id, last_update) values(?,?,?,".$site->db->unix2db_datetime(time()).")", $sysword,  $sysword, $sst_id);
				$sth_i = new SQL($sql);
				$site->debug->msg($sth_i->debug->get_msgs());
			} # s???ss???na ei leidinud ???ldse
		} # kui s???ss???na ja t???lge olemas
	}
	########### 3. kui leidub selle keele s???steemis???na, siis UUENDA:
	else {
		$sql = $site->db->prepare("UPDATE sys_sonad SET sona=? WHERE sys_sona=? AND keel=?", $translation, $sysword, $lang_id);
		$sth_i = new SQL($sql);
		$site->debug->msg($sth_i->debug->get_msgs());
	}
} # function


/**
* print_error_html
*
* Prints error page (entire page), erro message with red color.
* Usually used in popups.
*
* @package CMS
*
*	print_error_html(array(
*		"message" => $site->sys_sona(array(sona => "access denied", tyyp=>"editor"))
*	));
*
*/
function print_error_html(){
	global $site;

	$args = func_get_arg(0);
	$message = $args['message'];
	$close_js = $args['close_js'];
	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
</head>
<body class="popup_body" >

<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
  <tr>
	<td valign="top" width="100%" class="scms_confirm_alert_cell" height="100%">

<font color=red><?=$message?></font>

	</td>
  </tr>
  <tr align="right">
    <td valign="top" colspan=2 >
			<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:<?=$close_js?>window.close();">
    </td>
  </tr>
</table>

</body>
</html>
	<?
}
# / FUNCTION print_error_html
################################

/**
* check_profile_values
*
* checks entered profile values after save-form submitting.
* Returns SQL-ready array of fields (can feed to SQL).
* Errors are saved to $site->fdat['form_error']
*
* It is used before executing UPDATE/INSERT SQL
*
*
* @package CMS
*
* @param pointer profile_def - pointer to profile definition (record)
* @param string skip_fields - comma separated list of fields not for checking
*
* Call example:
* 	$profile_def = $site->get_profile(array("id"=>$site->fdat['profile_id']));
*
*	$sql_field_values = check_profile_values(array(
*		"profile_def" => &$profile_def,
*		"skip_fields" => "objekt_id, aeg",
*		"skip_non_active_fields" => true|false,
*		"use_only_profile_fields" => true|false,
*		"field_suffix" => '_'.$site->fdat['profile_id']
*	));
*/
function check_profile_values() {
	global $site;

	$args = func_get_arg(0);
	$profile_def = $args['profile_def'];
	$skip_fields = $args['skip_fields']; # these fields will not be checked
	$skip_non_active_fields = $args['skip_non_active_fields']; # non active fields will not be checked or returned
	$use_only_profile_fields = $args['use_only_profile_fields']; # use only fields defined in the profile
	$field_suffix = $args['field_suffix']; # all form fields have certain suffix


	$profile_data = unserialize($profile_def['data']);
	if(is_array($profile_data)) {
		$profile_fields = array_keys($profile_data); # profile_fields is now array of ALL field names
	}
	# result values:
	$sql_field_values = array();

	if(is_array($profile_fields)) {
		# get all table fields:
		$table_fields = array();
		$table_fields = split(",", $site->db->get_fields(array(tabel => $profile_def['source_table'])) );

		# we must have array of fields existing in profile AND really existing in table
		# (because we dont want to include zombie fields: existing in profile but not in database and vice versa)
		#old, more strict, checks zombies:
		if($use_only_profile_fields)
		{
			$save_fields = array_unique($profile_fields);
		}
		else
		{
			$save_fields = array_unique(array_merge($table_fields,$profile_fields));
		}

		# remove skipped field from array:
		$skip_fields_arr = array();
		if ($skip_fields) $skip_fields_arr = explode(',', $skip_fields);

		if($skip_non_active_fields){
			foreach($profile_data as $skip_field){
				if(!$skip_field['is_active']) $skip_fields_arr[] = $skip_field['name'];
			}
		} # is array

		if(is_array($skip_fields_arr)){
			foreach($skip_fields_arr as $skip_field){
				$id_key = array_search(trim($skip_field), $save_fields);
				if($id_key !== false) unset($save_fields[$id_key]);
			}
		} # is array

		# remove non active field from array:

		############ GET FORM DATA and put it into array

		$sql_field_values = array();
		# loop over "OK to save" fields  - we can all add into SQL
		foreach ($save_fields as $key=>$field) {
			# btw, if profile field is not found in form values (should exist always!) => the value is set to NULL
			$sql_field_values[$field] = $site->fdat[$field.$field_suffix];
		} # loop over table fields

		# + REQUIRED: CHECK FOR REQUIRED FIELDS
		# + CHECK FORMAT
		# + REPLACE LINE BREAKS
		foreach ($sql_field_values as $field=>$value) {

			# if field was required:
			if (($site->fdat['required_'.$field.$field_suffix] == 1 || $profile_data[$field]['is_required'] == 1) && $sql_field_values[$field] !="0" && (!$sql_field_values[$field] || $sql_field_values[$field]=="''")) {# fixed Bug #2600: Profiilis kohustuslikuks m��ratud v�lja ei saa sisestada v��rtuseks 0
				# don't save incorrect (empty) data:
				unset($sql_field_values[$field]);
				# save error message for use in form later:
				$form_error[$field] = $site->sys_sona(array(sona => "field required", tyyp=>"kasutaja"));
			} # if required

			#print "<br>".$field." =>".$profile_data[$field]['db_type']. " required:".$site->fdat['required_'.$field.$field_suffix]." (required_".$field.$field_suffix.")";
			#printr($profile_data[$field]);

            ####### FLOAT (SHOULD BE: remove all non-decimal digits (except ',' and '.') with ''.
			# BUT IS temporarily NOW: replace '-' with '')
            if(strtoupper($profile_data[$field]['db_type']) == 'FLOAT'){
					$sql_field_values[$field] = str_replace('-','',$sql_field_values[$field]);
					$sql_field_values[$field] = str_replace('.-','',$sql_field_values[$field]);
            }

            ####### DATETIME
			if(strtoupper($profile_data[$field]['db_type']) == 'DATETIME'){
				$sql_field_values[$field]=trim($sql_field_values[$field]);
				if($sql_field_values[$field] && (!(preg_match("/^(0[0-9]|[12][0-9]|3[01])[.](0[0-9]|1[012])[.](19|20|00)[0-9][0-9]$/", $sql_field_values[$field]) || preg_match("/^(0[0-9]|[12][0-9]|3[01])[.](0[0-9]|1[012])[.](19|20|00)[0-9][0-9]\s[0-2][0-9][:][0-5][0-9]$/", $sql_field_values[$field])) || preg_match("/^(0[0-9]|[12][0-9]|3[01])[.](0[0-9]|1[012])[.](19|20|00)[0-9][0-9]\s[0-2][0-9][:][0-5][0-9][:][0-5][0-9]$/", $sql_field_values[$field])))
				{
					$form_error[$field]=$site->sys_sona(array('sona' => 'Please use format dd.mm.yyyy', 'tyyp' =>'Editor'));
				}
				else
				{
					$sql_field_values[$field] = $site->db->ee_MySQL_long($sql_field_values[$field]);
				}
			}
			####### DATE
			if(strtoupper($profile_data[$field]['db_type']) == 'DATE'){
				if($sql_field_values[$field] && !preg_match("/^(0[0-9]|[12][0-9]|3[01])[.](0[0-9]|1[012])[.](19|20|00)[0-9][0-9]$/", $sql_field_values[$field]))
				{
					$form_error[$field]='wrong format!';
				}
				else
				{
					$sql_field_values[$field] = $site->db->ee_MySQL($sql_field_values[$field]);
				}
			}
			####### MULTIPLE SELECTIONS
			# if value is array (multiple selections - checkbox, multiple select, ..)
			if(is_array($value)){
				$sql_field_values[$field] = join(",",$sql_field_values[$field]);
			}
			####### TEXTAREA - replace line breaks
			if(strtoupper($profile_data[$field]['type']) == 'TEXTAREA'){
				$sql_field_values[$field] = str_replace("\r\n", "<br />",$sql_field_values[$field]);
				$sql_field_values[$field] = str_replace("\n", "<br />",$sql_field_values[$field]);
			}

			####### QUOTE:
			$sql_field_values[$field] = $site->db->quote($sql_field_values[$field]);

		} # loop over fields

		#### Loop again for custom validation
		foreach ($sql_field_values as $field=>$value)
		{
			######## CUSTOM VALIDATION
			if ($args['custom_validation'][$field])
			{
				if(function_exists($args['custom_validation'][$field]['function']))
				{
					$function=$args['custom_validation'][$field]['function'];
					if(is_array($args['custom_validation'][$field]['args'])) foreach ($args['custom_validation'][$field]['args'] as $profile_field => $arg)
					{
						if($arg == 'USE_PROFILE_FIELD')
							if(in_array($profile_field, array_keys($sql_field_values)))
							{
								$args['custom_validation'][$field]['args'][$profile_field] = $sql_field_values[$profile_field];
							}
							else
							{
								$args['custom_validation'][$field]['args'][$profile_field] = null;
 							}
					}
					if($got_error = $function($sql_field_values[$field], $args['custom_validation'][$field]['args']))
						$form_error[$field] = $got_error;
				}
			}
		}

	} # is array

	$site->fdat['form_error'] = $form_error; # save form errors to global var

	return $sql_field_values;

}
# / FUNCTION check_profile_values()
######################################

######################################
# FUNCTION br2nl
# for use with HTML forms, etc. */
function br2nl($text)  {
	/* Remove XHTML linebreak tags. */
	$text = str_replace("<br />","",$text);
	/* Remove HTML 4.01 linebreak tags. */
	$text = str_replace("<br>","",$text);
	/* Return the result. */
	return $text;
}

############################
# FUNCTION print_template_selectbox
#
# Prints content template selectbox, only <option..> rows
#
# return selected template array
function print_template_selectbox($selected_value,$templ_type) {
	global $site;
	global $class_path;

	include_once($class_path."extension.class.php"); # for printing extensions template groups

	##########################
	# general SQL:
	# show all templates (both visible and hidden) that
	# are allowewd by modules or not depending on any module at all

	$gen_sql = $site->db->prepare("SELECT templ_tyyp.ttyyp_id,templ_tyyp.nimi,templ_tyyp.templ_fail, templ_tyyp.extension
		FROM templ_tyyp
		WHERE 1 "
	);

	$sql_user_defined = " AND (templ_tyyp.ttyyp_id >= 1000 AND templ_tyyp.ttyyp_id < 2000) ";
	$sql_saurus3 = " AND ((on_nahtav='1' AND templ_tyyp.ttyyp_id < 1000) OR templ_tyyp.ttyyp_id >= 2000) ";
	$sql_no_extension = " AND (templ_tyyp.extension = '' OR ISNULL(templ_tyyp.extension)) ";
	$sql_extension = " AND (templ_tyyp.extension <> '' OR NOT ISNULL(templ_tyyp.extension)) ";

	$sql_page_templ = " AND templ_tyyp.on_page_templ = '1' ";
	$sql_content_templ = " AND templ_tyyp.on_page_templ = '0' ";

	$order_by = " ORDER BY templ_tyyp.nimi ";

	################################
	# Template selectbox

	$ttyyp="";	# init ttyyp

	if($templ_type == 'object')
		print "<option value=\"\"></option>";

	############################
	# group USER DEFINED:
	# SAPI templates (ttyyp_id >= 1000), not predefined (ttyyp_id < 2000), not extension template

	$sql = $gen_sql . ($templ_type=='page'? $sql_page_templ : $sql_content_templ) . $sql_no_extension. $sql_user_defined;
	$sql .=	$order_by;
	$sth = new SQL ($sql);

	# if found templates
	if($sth->rows) {
		print '<optgroup label="User defined">';
		while ($templ=$sth->fetch()) {
			if ($templ['ttyyp_id'] == $selected_value) {$ttyyp = $templ;}

			print "<option value=\"".$templ['ttyyp_id']."\"".($selected_value==$templ['ttyyp_id']?" selected":"").">";
			print $templ['nimi'];
			print "</option>\n";
		}
		print '</optgroup>';
	} # if found templates

	############################
	# group EXTENSIONS:
	# SAPI extension CONTENT templates

	$sql = $gen_sql . ($templ_type=='page'? $sql_page_templ : $sql_content_templ) . $sql_extension;
	$sql .=	$order_by;

	# print extensions templates rows and get selected template array
	$ttyyp_e = print_extension_templates($sql,$selected_value);

	############################
	# group SAURUS 3 (was PREDEFINED):
	# BUILT-IN PHP-templates (ttyyp_id < 1000) + predefined SAPI templates (ttyyp_id >= 2000)

	$sql = $gen_sql . ($templ_type=='page'? $sql_page_templ : $sql_content_templ) . $sql_no_extension . $sql_saurus3;
	$sql .=	$order_by;
	$sth = new SQL ($sql);

	# if found templates
	if($sth->rows) {
		print '<optgroup label="Saurus 3">';
		$tmp_templ_arr = array();	$tmp_arr = array();
		while ($templ=$sth->fetch()) {
			$tmp_templ_arr[$site->sys_sona(array(sona => $templ['nimi'], tyyp=>"system"))] = $templ;
			$tmp_arr[] = $site->sys_sona(array(sona => $templ['nimi'], tyyp=>"system"));
		}
		asort($tmp_arr); reset($tmp_arr);
		foreach($tmp_arr as $templ_name){
			$templ = $tmp_templ_arr[$templ_name];
			if ($templ['ttyyp_id'] == $selected_value) {$ttyyp = $templ;}
			print "<option value=\"".$templ['ttyyp_id']."\"".($selected_value==$templ['ttyyp_id']?" selected":"").">";
			print $templ_name;
			print "</option>\n";
		}
		print '</optgroup>';
	} # if found templates

	# / Template selectbox
	################################


	### return selected template array
	return ($ttyyp ? $ttyyp : $ttyyp_e);

}
# / FUNCTION print_template_selectbox
############################

/**
 * Changes the default templates
 *
 * @param integer $language_id language ID
 * @param integer $template_id template ID
 * @param string $type template type of one of the following: page, content, object
 * @param integer $object_type_id object type ID
 */
function change_default_template($language_id, $template_id, $type, $object_type_id = 0)
{
	//global $site;
	$template_id = (int)$template_id;
	$language_id = (int)$language_id;
	$object_type_id = (int)$object_type_id;

	switch ($type)
	{
		case 'page':
			$sql = 'update keel set page_ttyyp_id = '.$template_id.' where keel_id = '.$language_id;
			new SQL($sql);
		break;

		case 'content':
			$sql = 'update keel set ttyyp_id = '.$template_id.' where keel_id = '.$language_id;
			new SQL($sql);
		break;

		case 'object':
			$sql = 'update tyyp set ttyyp_id = '.$template_id.' where tyyp_id = '.$object_type_id;
			new SQL($sql);
		break;

		default:
			// unknown template type
		break;
	}
}

/**
 * changes the op template
 *
 * @param string $op
 * @param integer $template_id
 */
function change_op_template($op, $template_id)
{
	global $site;

	$template_id = (int)$template_id;

	if($op && $template_id)
	{
		$op = translate_ee($op);

		$sql = $site->db->prepare("update templ_tyyp set op = NULL where op = ?", $op);
		new SQL($sql);

		$sql = $site->db->prepare("update templ_tyyp set op = ? where ttyyp_id = ?", $op, $template_id);
		new SQL($sql);
	}
}

/**
 * function to empty Smarty's compiled templates
 *
 * @param string $templ_cache_path the cache location
 * @return boolean
 */
function clear_template_cache ($templ_cache_path)
{
	$return = true;

	function deletedir($file) {
		chmod($file,0777);
		if (is_dir($file)) {
			$handle = opendir($file);
			//while($filename = readdir($handle)) {
			while (false !== ($file = readdir($handle))) {
				if ($filename != "." && $filename != "..") {
					deletedir($file."/".$filename);
				}
			} #while
			closedir($handle);
			if (@rmdir($file)){return 1;};
		} else {
			if(@unlink($file)) return 1;
		}
	}

	if ($DIR = @opendir($templ_cache_path)) {


		############################
		# ts�kkel �le failide
		while (false !== ($file = readdir($DIR))) {
			if ($file != "." && $file != "..") {
				if (!@deletedir($templ_cache_path.$file)){
					$err_catalogs[] = $templ_cache_path.$file;
				};
			} # ./..
		}
		# / ts�kkel �le failide
		############################

		if (count($err_catalogs)){
			$error .= "<br><br><font color=red><b>Error! Make sure that directories:</b><br><br>";
			$error .= join("<br>", $err_catalogs);
			$error .=  "<br><br><b>have write permissions for the web server.</b><br></font>";
			$return = false;
		}
		closedir($DIR);
	}
	# kui kataloogi ei saa avada, kirjutada logisse veateade
	else {
		print "<br><font color=red>Error! Can't open directory '".$templ_cache_path."'</font>";
		$return = false;
	}

	return $return;
}

function admin_menu_list(){
global $site;
	$alamlipikud = array();
	$peaosad = array("id" => array(),"nimi" => array());
	$admin_menu = array();
        ############### otsime lubatud alamlipikud

	# 1. k�si k�ik admin-lehed
	$sql = $site->db->prepare("SELECT admin_osa.id
	FROM admin_osa
	WHERE admin_osa.parent_id!=1 ");
	$sql .= " ORDER BY sorteering DESC";


	$sth = new SQL($sql);

	while ($adminpage = $sth->fetch()) {

		# 3. vaata kas admin-leht on userile lubatud
		$perm = get_user_permission(array(
			type => 'ADMIN',
			adminpage_id => $adminpage['id'],
			site => $site
		));

		# kas useril on selle admin-lehe kohta Read �igus?
		if(!$perm['R']){
			# if forbidden, go to next adminpage
			continue;
		}

		# 4. kui k�ik lubatud, siis pane l�pp-massiivi
		array_push($alamlipikud,$adminpage['id']);
	}
#printr($alamlipikud);
	# see on n��d k�igi vaatamiseks lubatud adminlehtede massiiv:
	$alamlipikud_joined = join("','",$alamlipikud);


	############## Alamlipiku id jargi otsime pealipikud
	 $sql = $site->db->prepare("SELECT A.id AS peaid, A.nimetus AS peanimetus, A.eng_nimetus AS eng_peanimetus, A.sorteering FROM admin_osa
		LEFT JOIN admin_osa as A ON A.id = admin_osa.parent_id
		WHERE ".(!$site->in_admin?"A.show_in_editor=1 AND ":"")." admin_osa.id IN ('$alamlipikud_joined')
		GROUP BY A.id, A.nimetus, A.eng_nimetus, A.sorteering ORDER BY A.sorteering DESC"
	);

	$sth = new SQL($sql);
	$pea_total = $sth->rows;
	$site->debug->msg($sth->debug->get_msgs());

    while ($lipik = $sth->fetch()) {

		$admin_menu[]['id']= $lipik['peaid'];
		$admin_menu[sizeof($admin_menu)-1]['name']= $lipik['eng_peanimetus'];
		$admin_menu[sizeof($admin_menu)-1]['translated_name']= $site->sys_sona(array(sona => $lipik['eng_peanimetus'] , tyyp=>'admin', lang_id=>$_SESSION['keel_admin']['glossary_id']));
		$admin_menu[sizeof($admin_menu)-1]['submenus']=admin_menu_sublist($lipik['peaid'],$alamlipikud_joined);


	}

/*
We now need to find out if there are duplicate menus (because different modules might share the same menus and they are both in use, which means there are double menus in the array. Easiest example is the E-Commerce module that requires the existance of E-Payment module. Both of them use the same menus, but E-Payment can also be as a standalone module, not requireing E-Commerce.

We try to find out if the duplicate menus are basically the same thing just linked to a different module. If so, we just remove one of them. But if they are different things (say the name is the same, but the file it links to is different) we just add them under one main menu.

Peeter 26.08.2008

*/

$list = array();

// Count the times main menu names are listed in the array.
foreach($admin_menu as $am){
	$list[$am['name']]++;
}

//remove all the unique (occurs 1 time) names.
foreach($list as $k=>$v){
	if($v < 2){
		unset($list[$k]);
	}
}


//if any menus are represented several times we need to check their submenus and merge them.
if(sizeof($list)>0){

	//we sort through each of the main menus and get their submenus into one array.

	foreach($list as $k=>$v){
		$sm="";
		$first_occurance=""; //first occurance of the menu, duplicates are unset after submenu values are extracted. Reasoning behind this is that the main menu names are and will be in the future, unique.

		foreach($admin_menu as $ka=>$av){

			if($av['name']==$k){
				if($first_occurance ==""){
					$first_occurance = $ka;
				}
				foreach($av['submenus'] as $menu){

					$sm[]=$menu;
				}
				if($first_occurance != $ka){
					unset($admin_menu[$ka]);
				}

			}

		}

		//we remove duplicate submenus, in this case that have a matching "fail" value.

		$admin_menu[$first_occurance]['submenus']=remove_duplicates($sm,array("fail","eng_nimetus"));
	}
}

		//Now that we have removed the duplicate main menus and sub-menus there is still a chance that there are duplicate submenus (For instance Extension menu may have several fields by the same name and path, so we basically run through the menu/submenu tree and remove duplicates.


		foreach($admin_menu as $k=>$v){
			$admin_menu[$k]['submenus']=remove_duplicates($admin_menu[$k]['submenus'],array("fail","eng_nimetus"));
		}


	return $admin_menu;

}




//this function need an array to process and a list of field names (in array form) that need to be unique.


function remove_duplicates($array, $field)
{
	$new_array=array();
	$matched="";

	if(is_array($array)){
	foreach($array as $k=>$v){

		foreach($new_array as $nk=>$nv){
			$count="";

			foreach($field as $f){
				if($nv[$f] == $v[$f]){
					$count++;
				}
			}

			if($count == sizeof($field)){
				$matched=true;
			}
		}

		//the array has no match in the new array list so we add it to the list.

		if(!$matched){
			$new_array[]=$array[$k];
		}else{
			$matched="";
		}

	}

	return $new_array;
	}
}

function admin_menu_sublist($parent_id,$alamlipikud_joined){
global $site;


		# otsime lubatud alamlipikud
		$sql = "SELECT * FROM admin_osa WHERE parent_id='".$parent_id."' AND ".(!$site->in_admin?" show_in_editor=1 AND ":"")." id IN ('$alamlipikud_joined') ORDER BY sorteering DESC";

		$sth = new SQL($sql);


		$pattern = "/^".preg_replace("/\//","\\\/",$site->CONF['wwwroot'])."\/(editor)\//";
		if (preg_match($pattern, $site->wwwroot)) {
			$in_editor=true;

		}


		if($_GET['mod_rewrite'])
		{
			if((preg_replace("/\/$/","",$_GET['mod_rewrite'])==$site->CONF['wwwroot']))
			{
				$tava_vaade=true;
			}
		}
		else
		{
			if((preg_replace("/\/$/","",$site->wwwroot)==$site->CONF['wwwroot']))
			{
				$tava_vaade=true;
			}
		}
				while ($alamlp = $sth->fetch("ASSOC")) {


					$submenu[]=$alamlp;
					$submenu[sizeof($submenu)-1]['translated_name']=$site->sys_sona(array(sona => $alamlp['eng_nimetus'] , tyyp=>'admin', lang_id=>$_SESSION['keel_admin']['glossary_id']));
					if($in_editor){


						if(eregi("^/",$submenu[sizeof($submenu)-1]['fail'])||eregi("^http:",$submenu[sizeof($submenu)-1]['fail'])||eregi("^https:",$submenu[sizeof($submenu)-1]['fail'])){

						}elseif(eregi("'about.php'",$submenu[sizeof($submenu)-1]['fail'])){
						$submenu[sizeof($submenu)-1]['fail']=eregi_replace("'about.php'","'../admin/about.php'",$submenu[sizeof($submenu)-1]['fail']);


						}else{

						$submenu[sizeof($submenu)-1]['fail']="../admin/".$submenu[sizeof($submenu)-1]['fail'];
						}
					}elseif($tava_vaade){

						if(eregi("^/",$submenu[sizeof($submenu)-1]['fail'])||eregi("^http:",$submenu[sizeof($submenu)-1]['fail'])||eregi("^https:",$submenu[sizeof($submenu)-1]['fail'])){

						}elseif(eregi("'about.php'",$submenu[sizeof($submenu)-1]['fail'])){
						$submenu[sizeof($submenu)-1]['fail']=eregi_replace("'about.php'","'".$site->wwwroot."/admin/about.php'",$submenu[sizeof($submenu)-1]['fail']);


						}else{

						$submenu[sizeof($submenu)-1]['fail']=$site->wwwroot."/admin/".$submenu[sizeof($submenu)-1]['fail'];
						}
					}
				}

				return $submenu;


}


/**
 * Creates a friendly URL/filename from a (UTF-8) string
 *
 * @param string $string
 * @return string
 */
function create_alias_from_string($string, $preserve_dot_underscore = false)
{
	// wordpress remove accents filter
	$chars = array(
	// Decompositions for Latin-1 Supplement
	chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
	chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
	chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
	chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
	chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
	chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
	chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
	chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
	chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
	chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
	chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
	chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
	chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
	chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
	chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
	chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
	chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
	chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
	chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
	chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
	chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
	chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
	chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
	chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
	chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
	chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
	chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
	chr(195).chr(191) => 'y',
	// Decompositions for Latin Extended-A
	chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
	chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
	chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
	chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
	chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
	chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
	chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
	chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
	chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
	chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
	chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
	chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
	chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
	chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
	chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
	chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
	chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
	chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
	chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
	chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
	chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
	chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
	chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
	chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
	chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
	chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
	chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
	chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
	chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
	chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
	chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
	chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
	chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
	chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
	chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
	chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
	chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
	chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
	chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
	chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
	chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
	chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
	chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
	chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
	chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
	chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
	chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
	chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
	chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
	chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
	chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
	chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
	chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
	chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
	chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
	chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
	chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
	chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
	chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
	chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
	chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
	chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
	chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
	chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
	// Euro Sign
	chr(226).chr(130).chr(172) => 'E',
	// GBP (Pound) Sign
	chr(194).chr(163) => '');

	$string = strtr($string, $chars);

	if(!$preserve_dot_underscore){
	// lower case
	$string = strtolower($string);
	}

	// filter out all non roman and numeric characters except "-", "_" and whitespace
	if($preserve_dot_underscore){
	$string = preg_replace('/[^a-zA-Z0-9\.\-\s_]+/', '', $string);
	}else{
	$string = preg_replace('/[^a-zA-Z0-9\-\s_]+/', '', $string);
	}

	if(!$preserve_dot_underscore){
	// replace whitespace with "-"
	$string = preg_replace('/[\s]+/', '-', $string);
	}else{
	// replace whitespace with "_"
	$string = preg_replace('/[\s]+/', '_', $string);
	}

	// replace multiple "-" with single "-"
	$string = preg_replace('/\-+/', '-', $string);

	// remove first and last "-"
	$string = preg_replace('/^\-/', '', $string);
	$string = preg_replace('/\-$/', '', $string);

	if($string == '-') $string = '';

	return $string;
}






/**
 * Check if an alias already exists in given language, returns true if exists
 *
 * @param string $alias
 * @param integer $language_id
 * @return boolean
 */
function check_for_existing_alias($alias, $language_id)
{
	global $site;

	$sql = $site->db->prepare("select friendly_url from objekt where keel = ? and friendly_url = ? limit 1", $language_id, $alias);
	$result = new SQL($sql);

	if($result->rows)
	{
		return true;
	}

	return false;
}

/**
 * Creates unique Friendly URL (alias) in specified language from given UTF-8 string
 *
 * @param string $utf8_string
 * @param integer $language_id
 * @return string
 */
function create_alias_for_object($utf8_string, $language_id)
{
	$supplement = 2;

	$alias = create_alias_from_string($utf8_string);

	$blacklist=array(
		'admin',
		'classes',
		'editor',
		'failid',
		'js',
		'px',
		'styles',
		'public',
		'map',
	);

	if(in_array($alias,$blacklist)){
		$alias.='-'.$supplement;
	}

	if($alias !== '') while(check_for_existing_alias($alias, $language_id))
	{
		$alias = create_alias_from_string($utf8_string.'-'.$supplement);
		$supplement++;

		// guard
		if($supplement > 1000)
		{
			$alias = '';
			break;
		}
	}

	return $alias;
}

/**
 * retrieves the folder and his subfolders
 *
 * @param int $parent_id
 * @return array
 */
function get_subfolders($parent_id)
{
	global $site;

	$parent = new Objekt(array('objekt_id' => $parent_id, 'on_sisu' => 1));

	if($parent->objekt_id && $parent->all['tyyp_id'] == 22)
	{
		$parent_folders = explode('/', $parent->all['relative_path']);

		$site_abs_path = preg_replace('#/$#', '', $site->absolute_path);

		$folders = array(
			$parent->objekt_id => array(
				'objekt_id' => $parent->objekt_id,
				'parent_id' => $parent->parent_id,
				'title' => $parent->all['pealkiri'],
				'relative_path' => $parent->all['relative_path'],
				'has_children' => 0,
				'level' => count($parent_folders) - 1,
				'open' => 0, 'file_count' => 0,
				'is_writeable' => (is_writeable($site_abs_path.$parent->all['relative_path']) ? 1 : 0),
				'permissions' => $parent->permission,
			),
		);

		$list_sql = new AlamlistSQL(array(
			'parent' => $parent_id,
			'klass'	=> 'folder',
			'order' => ' relative_path ',
		));

		$list_sql->add_select("obj_folder.relative_path");

		$list_sql->add_from("LEFT JOIN obj_folder ON objekt.objekt_id=obj_folder.objekt_id");

		$list = new Alamlist(array(
			'alamlistSQL' => $list_sql,
		));

		while ($folder = $list->next())
		{
			$parent_folders = explode('/', $folder->all['relative_path']);

			$folders[$folder->objekt_id] = array(
				'objekt_id' => $folder->objekt_id,
				'parent_id' => $folder->parent_id,
				'title' => $folder->all['pealkiri'],
				'relative_path' => $folder->all['relative_path'],
				'has_children' => 0,
				'level' => count($parent_folders) - 1,
				'open' => 0,
				'file_count' => 0,
				'is_writeable' => (is_writeable($site_abs_path.$folder->all['relative_path']) ? 1 : 0),
				'permissions' => $folder->permission,
			);
		}

		$sql = 'select parent_id from obj_folder left join objekt_objekt on obj_folder.objekt_id = objekt_objekt.objekt_id where parent_id in ('.implode(',', array_keys($folders)).')';
		$result = new SQL($sql);

		while($objekt_id = $result->fetchsingle())
		{
			$folders[$objekt_id]['has_children'] = 1;
		}

		$sql = 'select parent_id, count(objekt_objekt.objekt_id) as file_count from objekt_objekt left join objekt on objekt_objekt.objekt_id = objekt.objekt_id where objekt_objekt.parent_id in ('.implode(',', array_keys($folders)).') and objekt.tyyp_id = 21 group by objekt_objekt.parent_id';
		$result = new SQL($sql);

		while($row = $result->fetch('ASSOC'))
		{
			$folders[$row['parent_id']]['file_count'] = $row['file_count'];
		}

		return $folders;
	}
	else
	{
		return 'no_such_parent_folder';
	}

}

function get_folder_list($start = 'public')
{
	global $site;

	$folders = array();

	$sql = $site->db->prepare("select objekt_id, relative_path from obj_folder where relative_path like '/".$start."/%' order by relative_path");
	$result = new SQL($sql);

	while($row = $result->fetch('ASSOC'))
	{
		$folders[$row['objekt_id']] = $row;
	}

	return $folders;
}

/**
 * Creates a subfolder. Returns created folders ID or error message.
 *
 * @param string $name
 * @param int $parent_id
 * @return mixed
 */
function create_folder($name, $parent_id)
{
	global $site;

	$safe_name = safe_filename2($name);
	$parent_id = (int)$parent_id;

	if($safe_name && $parent_id)
	{
		$parent = new Objekt(array('objekt_id' => $parent_id, 'on_sisu' => 1));

		if($parent->objekt_id == $parent_id && $parent->all['tyyp_id'] == 22)
		{
			// check for Create permission
			if(!$parent->permission['C'])
			{
				new Log(array(
					'action' => 'create',
					'type' => 'WARNING',
					'component' => 'Files',
					'message' => "Attempt to create folder under '".$parent->all['relative_path']."' (ID = ".$parent->objekt_id.") with no create permission.",
				));

				return 'no_permissions_to_create_folder';
			}

			$folder_name = $parent->all['relative_path'].'/'.$safe_name;
			$folder_path = preg_replace('#/$#', '', $site->absolute_path).$folder_name;

			// check if folder already exists
			$sql = $site->db->prepare("select objekt_id from obj_folder where relative_path = ?", $folder_name);
			$result = new SQL($sql);
			if($result->rows)
			{
				return 'folder_already_exists';
			}
			else
			{
				$current_path = preg_replace('#/$#', '', $site->absolute_path).$parent->all['relative_path'].'/'.$name;

				// try to rename to safe name
				if(file_exists($current_path) && $name != $safe_name)
				{
					if(rename($current_path, $folder_path))
					{
						$folder_created = true;
					}
					else
					{
						new Log(array(
							'action' => 'create',
							'component' => 'Files',
							'type' => 'ERROR',
							'message' => "Could not rename '".$parent->all['relative_path'].'/'.$name."' to '".$folder_name."', file system error.",
						));

						return 'could_not_rename_folder';
					}
				}

				if(!file_exists($folder_path))
				{
					$mask = umask(0);
					$folder_created = mkdir($folder_path, 0777);
					umask($mask);
				}
				else
				{
					$folder_created = true;
				}

				if($folder_created)
				{
					// objekt
					$sql = $site->db->prepare("insert into objekt (pealkiri, tyyp_id, on_avaldatud, keel, pealkiri_strip, aeg, created_time, created_user_id, created_user_name)
																	values (?, 22, '1', 1, ?, now(), now(), ?, ?)", $safe_name, $safe_name, $site->user->user_id, $site->user->name);
					$result = new SQL($sql);
					$folder_id = $result->insert_id;

					$sql = 'select max(sorteering) from objekt_objekt';
					$result = new SQL($sql);
					$sorting = $result->fetchsingle();

					// objekt_objekt
					$sql = 'insert into objekt_objekt (objekt_id, parent_id, sorteering) values ('.$folder_id.', '.$parent_id.', '.$sorting.')';
					new SQL($sql);

					// obj_folder
					$sql = "insert into obj_folder (objekt_id, relative_path) values (".$folder_id.", '".$folder_name."')";
					new SQL($sql);

					// copy parent permissions
					$sql = $site->db->prepare("SELECT * FROM permissions WHERE type=? AND source_id=?",
						'OBJ',
						 $parent_id
					);
					$sth = new SQL ($sql);
					while($perm = $sth->fetch())
					{
							$sql2 = $site->db->prepare("INSERT INTO permissions (type,source_id,role_id,group_id,user_id,C,R,U,P,D) VALUES (?,?,?,?,?,?,?,?,?,?)",
								'OBJ',
								$folder_id,
								$perm['role_id'],
								$perm['group_id'],
								$perm['user_id'],
								$perm['C'],
								$perm['R'],
								$perm['U'],
								$perm['P'],
								$perm['D']
							);
							$sth2 = new SQL($sql2);
					}

					new Log(array(
						'action' => 'create',
						'component' => 'Files',
						'objekt_id' => $folder_id,
						'message' => "Folder '".$folder_name."' (ID = ".$folder_id.") created.",
					));

					return (int)$folder_id;
				}
				else
				{
					new Log(array(
						'action' => 'create',
						'component' => 'Files',
						'type' => 'ERROR',
						'message' => "Could not create folder: '".$folder_name."', file system error.",
					));

					return 'could_not_create_folder';
				}
			}
		}
		else
		{
			return 'no_such_parent_folder';
		}

	}
	else
	{
		return 'parameters_missing';
	}
}

/**
 * Deletes folder, returns true or error message
 *
 * @param int $folder_id
 * @return mixed
 */
function delete_folder($folder_id)
{
	global $site;

	$folder_id = (int)$folder_id;

	$objekt = new Objekt(array('objekt_id' => $folder_id, 'on_sisu' => 1));

	if($objekt->objekt_id == $folder_id && $objekt->all['tyyp_id'] == 22)
	{
		// check for Delete permission
		if(!$objekt->permission['D'])
		{
			new Log(array(
				'action' => 'delete',
				'type' => 'WARNING',
				'component' => 'Files',
				'objekt_id' => $objekt->objekt_id,
				'message' => "Attempt to delete folder '".$objekt->all['relative_path']."' (ID = ".$objekt->objekt_id.") with no delete permission.",
			));

			return 'no_permissions_to_delete_folder';
		}

		if($objekt->all['relative_path'] == '/public' || $objekt->all['relative_path'] == '/shared')
		{
			return 'unable_to_modify_public_shared_folder';
		}
		else
		{
			$sql = "select count(objekt_id) from obj_folder where relative_path like '".$objekt->all['relative_path']."/%'";
			$result = new SQL($sql);

			if($result->fetchsingle() > 0)
			{
				return 'folder_has_subfolders';
			}
			else
			{
				$sql = "select count(objekt_id) from obj_file where relative_path like '".$objekt->all['relative_path']."/%'";
				$result = new SQL($sql);

				if($result->fetchsingle() > 0)
				{
					return 'folder_has_files';
				}
				else
				{
					$dir = preg_replace('#/$#', '', $site->absolute_path).$objekt->all['relative_path'];

					if(file_exists($dir))
					{
						delete_directory($dir.'/.thumbnails');
						delete_directory($dir.'/.gallery_pictures');
						delete_directory($dir.'/.gallery_thumbnails');

						if(!rmdir($dir))
						{
							return 'could_not_delete_folder_from_filesystem';
						}
					}

					$objekt->del();

					new Log(array(
						'action' => 'delete',
						'component' => 'Files',
						'objekt_id' => $objekt->objekt_id,
						'message' => "Folder '".$objekt->all['relative_path']."' (ID = ".$objekt->objekt_id.") deleted.",
					));

					return true;
				}
			}
		}
	}
	else
	{
		return 'no_such_folder_object';
	}
}

/**
 * Deletes a folder and its contents (recursive)
 * TODO: error checking
 *
 * @param string $folder_path
 */
function delete_directory($dir)
{
	if(!file_exists($dir))
	{
		return false;
	}

	if(!$dh = @opendir($dir))
	{
		return false;
	}

	while(false !== ($obj = readdir($dh)))
	{
		if($obj == '.' || $obj == '..') continue;
		if(!@unlink($dir.'/'.$obj)) delete_directory($dir.'/'.$obj);
	}

	@closedir($dh);

	return @rmdir($dir);
}

/**
 * Renames folder, returns true or error message
 *
 * @param string $name
 * @param int $folder_id
 * @return mixed
 */
function rename_folder($name, $folder_id)
{
	global $site;

	$folder_id = (int)$folder_id;

	$name = safe_filename2($name);

	$objekt = new Objekt(array('objekt_id' => $folder_id, 'on_sisu' => 1));

	if($objekt->objekt_id == $folder_id && $name && $objekt->all['tyyp_id'] == 22)
	{
		// check for Update permission
		if(!$objekt->permission['U'])
		{
			new Log(array(
				'action' => 'update',
				'type' => 'WARNING',
				'component' => 'Files',
				'objekt_id' => $objekt->objekt_id,
				'message' => "Attempt to update folder '".$objekt->all['relative_path']."' (ID = ".$objekt->objekt_id.") with no update permission.",
			));

			return 'no_permissions_to_update_folder';
		}

		if($objekt->all['relative_path'] == '/public' || $objekt->all['relative_path'] == '/shared')
		{
			return 'unable_to_modify_public_shared_folder';
		}
		else
		{
			$new_folder_name = explode('/', $objekt->all['relative_path']);
			$new_folder_name = $new_folder_name[count($new_folder_name) - 1];
			$new_folder_name = preg_replace('#/'.preg_quote($new_folder_name, '#').'$#', '/'.$name, $objekt->all['relative_path']);

			$from_name = preg_replace('#/$#', '', $site->absolute_path).$objekt->all['relative_path'];
			$to_name = preg_replace('#/$#', '', $site->absolute_path).$new_folder_name;

			// check if folder already exists
			$sql = $site->db->prepare("select objekt_id from obj_folder where relative_path = ? and objekt_id <> ?", $new_folder_name, $folder_id);
			$result = new SQL($sql);
			if($result->rows)
			{
				return 'folder_already_exists';
			}
			else
			{
				if(rename($from_name, $to_name))
				{
					$sql = $site->db->prepare("update objekt set pealkiri = ?, pealkiri_strip = ?, changed_time = now(), changed_user_id = ?, changed_user_name = ? where objekt_id = ?", $name, $name, $site->user->user_id, $site->user->name, $folder_id);
					new SQL($sql);

					$sql = $site->db->prepare("update obj_folder set relative_path = ? where objekt_id = ?", $new_folder_name, $folder_id);
					new SQL($sql);

					$sql = $site->db->prepare("update obj_folder set relative_path = replace(relative_path, ?, ?) where relative_path like '".$objekt->all['relative_path']."/%'", $objekt->all['relative_path'], $new_folder_name);
					new SQL($sql);

					$sql = $site->db->prepare("update obj_file set relative_path = replace(relative_path, ?, ?) where relative_path like '".$objekt->all['relative_path']."/%'", $objekt->all['relative_path'], $new_folder_name);
					new SQL($sql);

					new Log(array(
						'action' => 'update',
						'component' => 'Files',
						'objekt_id' => $objekt->objekt_id,
						'message' => "Folder '".$new_folder_name."' (ID = ".$objekt->objekt_id.") renamed from: '".$objekt->all['relative_path']."'.",
					));

					return true;
				}
				else
				{
					return 'could_not_rename_folder';
				}
			}
		}
	}
	else
	{
		return 'no_such_folder_object';
	}
}

/**
 * creates a array of files for filemanager from objekt list
 *
 * @param Alamlist $list
 * @return array
 */
function get_files_list($list)
{
	global $site;

	$files = array();

	while ($file = $list->next())
	{
		if(strpos($file->all['relative_path'], $site->CONF['file_path']) === 0 || strpos($file->all['relative_path'], $site->CONF['secure_file_path']) === 0)
		{
			$thumbnail_file = str_replace($file->all['filename'], '.thumbnails/'.$file->all['filename'], $file->all['relative_path']);
			$thumbnail_path = preg_replace('#/$#', '', $site->absolute_path).$thumbnail_file;

			$pathinfo = pathinfo($file->all['relative_path']);
			$pathinfo['extension'] = strtolower($pathinfo['extension']);

			if(file_exists($thumbnail_path))
			{
				$thumbnail_url = $site->CONF['wwwroot'].$thumbnail_file;
			}
			else
			{
				$thumbnail_path = $site->absolute_path.$site->CONF['styles_path'].'/gfx/icons/48x48/mime/'.$pathinfo['extension'].'.png';

				if(file_exists($thumbnail_path))
				{
					$thumbnail_url = $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/48x48/mime/'.$pathinfo['extension'].'.png';
				}
				else
				{
					$thumbnail_url = $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/48x48/mime/unknown.png';
				}
			}

			$files[$file->objekt_id] = array(
				'title' => $file->all['pealkiri'],
				'objekt_id' => $file->objekt_id,
				'thumbnail' => $thumbnail_url,
				'size' => $file->all['size'],
				'hr_size' => human_readable_file_size($file->all['size']),
				'mimetype' => $file->all['mimetype'],
				'filename' => $file->all['filename'],
				'extension' => $pathinfo['extension'],
				'folder' => str_replace('/'.$file->all['filename'], '', $file->all['relative_path']),
				'parent_id' => $file->parent_id,
				'date' => $file->all['aeg'],
				'hr_date' => $site->db->MySQL_ee($file->all['aeg']),
				'permissions' =>$file->permission,
			);
		}
	}

	return $files;
}

function get_files_by_search($keyword, $sorting = 'objekt.pealkiri asc', $page = 1)
{
	global $site;

	$keyword = (string)$keyword;

	if($keyword)
	{
		$list_sql = new AlamlistSQL(array(
			'klass'	=> 'file',
			'order' => $sorting,
			'where' => "(objekt.pealkiri like '%".mysql_real_escape_string($keyword)."%' or obj_file.filename like '%".mysql_real_escape_string($keyword)."%')",
	 	));

		$list_sql->add_select("obj_file.profile_id, obj_file.relative_path, obj_file.filename, obj_file.mimetype, obj_file.size");

		$list_sql->add_from("LEFT JOIN obj_file ON objekt.objekt_id=obj_file.objekt_id");

		$list = new Alamlist(array(
			'alamlistSQL' => $list_sql,
			'start' => ($page - 1) * 100,
			'limit' => 100,
		));

		$count_sql = new AlamlistSQL(array(
			'klass'	=> 'file',
			'where' => "(objekt.pealkiri like '%".mysql_real_escape_string($keyword)."%' or obj_file.filename like '%".mysql_real_escape_string($keyword)."%')",
		));

		$count_sql->add_from("LEFT JOIN obj_file ON objekt.objekt_id=obj_file.objekt_id");

		$count = new Alamlist(array(
			'alamlistSQL' => $count_sql,
			'on_counter' => 1,
		));

		//return get_files_list($list);

		return array(
			'total_files' => (int)$count->rows,
			'files' => get_files_list($list),
		);
	}
	else
	{
		return 'no_keyword_given';
	}
}

function get_files_from_folder($folder_id, $sorting = 'objekt.pealkiri asc', $page = 1)
{
	global $site;

	$objekt = new Objekt(array('objekt_id' => $folder_id));

	if($objekt->objekt_id == $folder_id && $objekt->all['tyyp_id'] == 22)
	{
		$list_sql = new AlamlistSQL(array(
			'parent' => $folder_id,
			'klass'	=> 'file',
			'order' => $sorting,
		));

		$list_sql->add_select("obj_file.profile_id, obj_file.relative_path, obj_file.filename, obj_file.mimetype, obj_file.size");

		$list_sql->add_from("LEFT JOIN obj_file ON objekt.objekt_id=obj_file.objekt_id");

		$list = new Alamlist(array(
			'alamlistSQL' => $list_sql,
			'start' => ($page - 1) * 100,
			'limit' => 100,
		));

		$count_sql = new AlamlistSQL(array(
			'parent' => $folder_id,
			'klass'	=> 'file',
		));

		$count = new Alamlist(array(
			'alamlistSQL' => $count_sql,
			'on_counter' => 1,
		));

		return array(
			'total_files' => (int)$count->rows,
			'files' => get_files_list($list),
		);
	}
	else
	{
		return 'no_such_folder_object';
	}
}

function delete_files($files)
{
	global $site;

	$files = (array)$files;
	$deleted_files = array();

	$return = array(
		'error' => 0,
		'error_message' => '',
		'deleted_files' => array(),
	);

	foreach ($files as $file_id)
	{
		$objekt = new Objekt(array('objekt_id' => $file_id, 'on_sisu' => 1));

		if($objekt->objekt_id == $file_id && $objekt->all['tyyp_id'] == 21)
		{
			// check for Delete permission
			if(!$objekt->permission['D'])
			{
				new Log(array(
					'action' => 'delete',
					'type' => 'WARNING',
					'component' => 'Files',
					'objekt_id' => $objekt->objekt_id,
					'message' => "Attempt to delete file '".$objekt->all['relative_path']."' (ID = ".$objekt->objekt_id.") with no delete permission.",
				));

				$return['error'] = 3;
				$return['error_message'] = 'item_error';

				continue;
			}

			$file_fullpath = preg_replace('#/$#', '', $site->absolute_path).$objekt->all['relative_path'];

			if(file_exists($file_fullpath))
			{
				if(unlink($file_fullpath))
				{
					$pathinfo = pathinfo($file_fullpath);
					$thumbnail_path = $pathinfo['dirname'].'/.thumbnails/'.$objekt->all['filename'];

					if(file_exists($thumbnail_path)) unlink($thumbnail_path);

					$objekt->del();

					new Log(array(
						'action' => 'delete',
						'component' => 'Files',
						'objekt_id' => $objekt->objekt_id,
						'message' => "File '".$objekt->all['relative_path']."' (ID = ".$objekt->objekt_id.") deleted.",
					));

					$return['deleted_files'][] = $file_id;
				}
				else
				{
					new Log(array(
						'action' => 'delete',
						'component' => 'Files',
						'type' => 'ERROR',
						'objekt_id' => $objekt->objekt_id,
						'message' => "Could not delete file '".$objekt->all['relative_path']."' (ID = ".$objekt->objekt_id."), file system error.",
					));

					$return['error'] = 2;
					$return['error_message'] = 'item_error';
				}
			}
			else
			{
				$objekt->del();

				new Log(array(
					'action' => 'delete',
					'component' => 'Files',
					'objekt_id' => $objekt->objekt_id,
					'message' => "File '".$objekt->all['relative_path']."' (ID = ".$objekt->objekt_id.") deleted.",
				));

				$return['deleted_files'][] = $file_id;
			}
		}
		else
		{
			$return['error'] = 1;
			$return['error_message'] = 'item_error';
		}
	}

	return $return;
}

function synchronise_folder($folder_id)
{
	global $site;

	$folder_id = (int)$folder_id;

	$objekt = new Objekt(array('objekt_id' => $folder_id));

	if($objekt->objekt_id == $folder_id && $objekt->all['tyyp_id'] == 22)
	{
		$objekt->load_sisu();

		$folder_path = preg_replace('#/$#', '', $site->absolute_path).$objekt->all['relative_path'];

		// get folder & file count in this from database
		$sql = 'select count(objekt_id) from objekt_objekt where parent_id = '.$folder_id;
		$result = new SQL($sql);

		$db_object_count = $result->fetchsingle();

		// check if folder & file count from db matches count in file system
		$fs_object_count = 0;

		if ($dir = opendir($folder_path))
		{
			while (false !== ($file = readdir($dir)))
			{
				if (strpos($file, '.') !== 0)
				{
					$fs_object_count++;
					// break counting if there are more files, syncro is needed
					if($fs_object_count > $db_object_count) break;
				}
			}
			closedir($dir);
		}
		else
		{
			// not a folder ... TODO
		}

		//if the fs and db object count do not match syncronise folder contents
		if($fs_object_count != $db_object_count)
		{
			// collect files and folder from fs
			$fs_files = array();
			$fs_folders = array();

			$dir = opendir($folder_path);

			while (false !== ($file = readdir($dir)))
			{
				if (strpos($file, '.') !== 0)
				{
					if(is_dir($folder_path.'/'.$file))
					{
						$fs_folders[$file] = $file;
					}
					else
					{
						$fs_files[$file] = array(
							'filename' => $file,
							'size' => @filesize($folder_path.'/'.$file),
							'mimetype' => get_file_mime_content_type($folder_path.'/'.$file),
						);
					}
				}
			}
			closedir($dir);


			// files first
			$sql = 'select obj_file.objekt_id, filename, mimetype, size from obj_file left join objekt_objekt on obj_file.objekt_id = objekt_objekt.objekt_id where parent_id = '.$folder_id;
			$result = new SQL($sql);

			$files_to_delete = array();

			while($row = $result->fetch('ASSOC'))
			{
				// mark files not found in fs for deletion
				if(!$fs_files[$row['filename']])
				{
					$files_to_delete[] = $row['objekt_id'];
				}
				else
				{
					// update file size, mimetype if needed
					if($fs_files[$row['filename']]['size'] != $row['size'] || $fs_files[$row['filename']]['mimetype'] != $row['mimetype'])
					{
						$sql = $site->db->prepare("update obj_file set size = ?, mimetype = ? where objekt_id = ?", $fs_files[$row['filename']]['size'], $fs_files[$row['filename']]['mimetype'], $row['objekt_id']);
						new SQL($sql);

						// set
						$sql = $site->db->prepare("update objekt set changed_time = now(), changed_user_id = ?, changed_user_name = ? where objekt_id = ?",
																					$site->user->user_id, $site->user->name, $row['objekt_id']);
						new SQL($sql);
					}

					// check for thumbnail
					if(!file_exists($folder_path.'/.thumbnails/'.$row['filename']))
					{
						// create thumbnail
						create_file_thumbnail($folder_path.'/'.$row['filename']);
					}


					// remove from fs object array
					unset($fs_files[$row['filename']]);
				}
			}

			// delete files not in fs TODO: catch errors from file delete
			delete_files($files_to_delete);

			// left over files are new, create them
			foreach($fs_files as $filename => $file)
			{
				$safe_filename = safe_filename2($filename);
				if($safe_filename != $filename)
				{
					if(rename($folder_path.'/'.$filename, $folder_path.'/'.$safe_filename))
					{
					}
					else
					{
						// log unable to rename, skip
						new Log(array(
							'action' => 'create',
							'component' => 'Files',
							'type' => 'ERROR',
							'message' => "Could not rename '".$objekt->all['relative_path'].'/'.$filename."' to '".$objekt->all['relative_path'].'/'.$safe_filename."', file system error.",
						));

						continue;
					}
				}

				// objekt
				insert_new_file_object($objekt, $filename, $file['size'], $file['mimetype']);

				// create thumbnail
				create_file_thumbnail($folder_path.'/'.$safe_filename);
			}

			// folders next
			$sql = 'select obj_folder.objekt_id, relative_path from obj_folder left join objekt_objekt on obj_folder.objekt_id = objekt_objekt.objekt_id where parent_id = '.$folder_id;
			$result = new SQL($sql);

			$folders_to_delete = array();

			while($row = $result->fetch('ASSOC'))
			{
				$folder_name = str_replace($objekt->all['relative_path'].'/', '', $row['relative_path']);

				// mark folders not found in fs for deletion
				if(!$fs_folders[$folder_name])
				{
					$folders_to_delete[] = $row['objekt_id'];
				}
				else
				{
					// remove from fs object array
					unset($fs_folders[$folder_name]);
				}
			}

			// delete folders present in db but not present in fs
			foreach($folders_to_delete as $folder_id)
			{
				// TODO: error catching from folder deleting
				delete_folder($folder_id);
			}

			// create new folders
			foreach($fs_folders as $folder_name)
			{
				create_folder($folder_name, $objekt->objekt_id);
			}

			new Log(array(
				'action' => 'sync',
				'component' => 'Files',
				'objekt_id' => $objekt->objekt_id,
				'message' => "Folder '".$objekt->all['relative_path']."' (ID = ".$objekt->objekt_id.") synchronised.",
			));

			// recurse?
			return true;
		}
		else
		{
			// nothing to syncro
			return true;
		}

	}
	else
	{
		return 'no_such_folder_object';
	}
}

function insert_new_file_object($folder, $filename, $size, $mimetype)
{
	global $site;

	$safe_filename = safe_filename2($filename);

	$pathinfo = pathinfo($filename);
	$title = str_replace('.'.$pathinfo['extension'], '', $pathinfo['basename']);

	// objekt
	$sql = $site->db->prepare("insert into objekt (pealkiri, tyyp_id, on_avaldatud, keel, pealkiri_strip, aeg, created_time, created_user_id, created_user_name)
													values (?, 21, '1', 1, ?, now(), now(), ?, ?)", $title, $filename, $site->user->user_id, $site->user->name);
	$result = new SQL($sql);
	$file_id = $result->insert_id;

	$sql = 'select max(sorteering) + 1 from objekt_objekt';
	$result = new SQL($sql);
	$sorting = $result->fetchsingle();

	// objekt_objekt
	$sql = 'insert into objekt_objekt (objekt_id, parent_id, sorteering) values ('.$file_id.', '.$folder->objekt_id.', '.$sorting.')';
	new SQL($sql);

	// obj_file
	$sql = $site->db->prepare("insert into obj_file (objekt_id, filename, size, mimetype,  relative_path) values (?, ?, ?, ?, ?)",
																	$file_id, $safe_filename, $size, $mimetype, $folder->all['relative_path'].'/'.$safe_filename);
	new SQL($sql);

	new Log(array(
		'action' => 'create',
		'component' => 'Files',
		'objekt_id' => $file_id,
		'message' => "File '".$folder->all['relative_path'].'/'.$safe_filename."' (ID = ".$file_id.") created.",
	));

	return $file_id;
}

/**
 * Creates a thumbnail of from an image
 *
 * @param string $fullpath
 */
function create_file_thumbnail($fullpath)
{
	global $site, $class_path;

	include_once($class_path.'picture.inc.php');

	// Check if Thumbnail directory exists

	if(!is_dir(dirname(realpath($fullpath)).'/.thumbnails'))
	{
		$mask = umask(0);
		mkdir(dirname(realpath($fullpath)).'/.thumbnails', 0777);
		umask($mask);
	}

	$pathinfo = pathinfo($fullpath);

	if(preg_match("/(jpeg|pjpeg|jpg|png|gif)/i", $pathinfo['extension']))
	{
		$thumb_max_size = 95;

		$image = new ImageShopper(str_replace($site->absolute_path, '',  $fullpath));

		//Get file Info

		$cur_x = $image->image_src_x;
		$cur_y = $image->image_src_y;

		//Calculate thumb size

		$image_rate = max($cur_x/$thumb_max_size,$cur_y/$thumb_max_size);

		$new_x = round($cur_x/$image_rate);
		$new_y = round($cur_y/$image_rate);

		$image->image_resize = true;
		$image->image_x = $new_x;
		$image->image_ratio_y = true;
		$image->file_auto_rename = false;
		$image->process($pathinfo['dirname'].'/.thumbnails');

		@chmod($pathinfo['dirname'].'/.thumbnails/'.$pathinfo['basename'], 0666);
	}
}

function upload_file_to_folder($data, $folder_fullpath)
{
	global $site;

	$data['name'] = safe_filename2($data['name']);

	$file = realpath($folder_fullpath).'/'.$data['name'];
	$file = str_replace('\\', '/', $file);

	if(move_uploaded_file($data['tmp_name'], $file))
	{
		chmod($file, 0666);
		$site->debug->msg("File uploaded as ".$file);
	}
	else
	{
		//print $site->sys_sona(array(sona => "Faili salvestamisel tekkis viga", tyyp=>"editor"));
		return 'error_on_file_upload';
	}

	return true;
}

function move_files_to_folder($from_folder_id, $to_folder_id, $files)
{
	global $site;

	$return = array(
		'error' => 0,
		'error_message' => '',
		'moved_files' => array(),
	);

	if(count($files) && $to_folder_id && $to_folder_id != $from_folder_id)
	{
		$to_folder_obj = new Objekt(array('objekt_id' => (int)$to_folder_id, 'on_sisu' => 1));
		$from_folder_obj = new Objekt(array('objekt_id' => (int)$from_folder_id, 'on_sisu' => 1));

		if($to_folder_obj->objekt_id == $to_folder_id && $to_folder_obj->all['klass'] == 'folder' && $from_folder_obj->objekt_id == $from_folder_id && $from_folder_obj->all['klass'] == 'folder')
		{
			if($to_folder_obj->permission['C'] == 1)
			{
				$to_folder_obj->all['fullpath'] = preg_replace('#/$#', '', $site->absolute_path).$to_folder_obj->all['relative_path'];
				$from_folder_obj->all['fullpath'] = preg_replace('#/$#', '', $site->absolute_path).$from_folder_obj->all['relative_path'];

				foreach($files as $object_id)
				{
					$file_obj = new Objekt(array('objekt_id' => (int)$object_id, 'on_sisu' => 1));
					if($file_obj->objekt_id && $file_obj->parent_id == $from_folder_obj->objekt_id && $file_obj->all['klass'] == 'file' && $file_obj->permission['D'] == 1)
					{
						//if file exists and there is not a file with the same name in the destination folder
						$file_obj->all['fullpath'] = preg_replace('#/$#', '', $site->absolute_path).$file_obj->all['relative_path'];

						if(file_exists($file_obj->all['fullpath']) && !file_exists($to_folder_obj->all['fullpath'].'/'.$file_obj->all['filename']))
						{
							$relative_path = $to_folder_obj->all['relative_path'].'/'.$file_obj->all['filename'];

							if(rename($file_obj->all['fullpath'], $to_folder_obj->all['fullpath'].'/'.$file_obj->all['filename']))
							{
								// file successfully moved, update db object
								$sql = "update obj_file set relative_path = '".$relative_path."' where objekt_id = ".$file_obj->objekt_id;
								//printr($sql);
								new SQL($sql);

								// update parent -> object relation
								$sql = "update objekt_objekt set parent_id = ".$to_folder_obj->objekt_id." where objekt_id = ".$file_obj->objekt_id." and parent_id = ".$from_folder_obj->objekt_id;
								//printr($sql);
								new SQL($sql);
								########## write log
								new Log(array(
									'action' => 'update',
									'component' => 'Files',
									'objekt_id' => $file_obj->objekt_id,
									'message' => "File '".$file_obj->all['relative_path']."' (ID = ".$file_obj->objekt_id.") moved to '".$relative_path."'",
								));

								$return['moved_files'][] = $file_obj->objekt_id;

								// also move thumbnails, keep quiet about success?
								if(file_exists($from_folder_obj->all['fullpath'].'/.thumbnails/').$file_obj->all['filename'])
								{
									if(!file_exists($to_folder_obj->all['fullpath'].'/.thumbnails'))
									{
										$mask = umask(0);
										$thumbnails_folder = mkdir($to_folder_obj->all['fullpath'].'/.thumbnails', 0777);
										umask($mask);
									}
									else
									{
										$thumbnails_folder = is_dir($to_folder_obj->all['fullpath'].'/.thumbnails/');
									}

									if($thumbnails_folder)
									{
										rename($from_folder_obj->all['fullpath'].'/.thumbnails/'.$file_obj->all['filename'], $to_folder_obj->all['fullpath'].'/.thumbnails/'.$file_obj->all['filename']);
									}
								}
							}
							else
							{
								// file move failed
								new Log(array(
									'action' => 'update',
									'component' => 'Files',
									'type' => 'ERROR',
									'objekt_id' => $file_obj->objekt_id,
									'message' => "File '".$file_obj->all['relative_path']."' (ID = ".$file_obj->objekt_id.") move to '".$to_folder_obj->all['relative_path']."' failed, file system error.",
								));

								$return['error'] = 5;
								$return['error_message'] = 'item_error';
							}
						}
						elseif(file_exists($to_folder_obj->all['fullpath'].'/'.$file_obj->all['filename']))
						{
							// no overwriting
							new Log(array(
								'action' => 'update',
								'component' => 'Files',
								'type' => 'NOTICE',
								'objekt_id' => $file_obj->objekt_id,
								'message' => "File '".$file_obj->all['relative_path']."' (ID = ".$file_obj->objekt_id.") could not be moved to '".$to_folder_obj->all['relative_path']."'. File already exists.",
							));

							$return['error'] = 4;
							$return['error_message'] = 'item_error';
						}
						else
						{
							// no such file, del from db
							$file_obj->del();

							// file is moved in a sense, to nothing
							$return['moved_files'][] = $file_obj->objekt_id;
						}
					}
					else
					{
						//no file to move or no cms permissions
						new Log(array(
							'action' => 'update',
							'component' => 'Files',
							'type' => 'ERROR',
							'objekt_id' => $file_obj->objekt_id,
							'message' => "File (ID = ".$object_id.") move to '".$to_folder_obj->all['relative_path']."' failed, access denied.",
						));

						$return['error'] = 3;
						$return['error_message'] = 'no_permissions_to_move_files';
					}
				}
			}
			else
			{
				//no file to move or no cms permissions
				new Log(array(
					'action' => 'update',
					'component' => 'Files',
					'type' => 'ERROR',
					'objekt_id' => $file_obj->objekt_id,
					'message' => "File (ID = ".$object_id.") move to '".$to_folder_obj->all['relative_path']."' failed, access denied.",
				));

				$return['error'] = 4;
				$return['error_message'] = 'no_permissions_to_move_files';
			}
		}
		else
		{
			$return['error'] = 2;
			$return['error_message'] = 'no_such_folder_object';
		}
	}
	else
	{
		$return['error'] = 1;
		$return['error_message'] = 'parameters_missing';
	}

	return $return;
}

function upload_to_folder($file, $folder_id)
{
	global $site;

	$folder_id = (int)$folder_id;

	$objekt = new Objekt(array('objekt_id' => $folder_id, 'on_sisu' => 1));

	if($objekt->objekt_id == $folder_id && $objekt->all['tyyp_id'] == 22)
	{
		// check for Create permission
		if(!$objekt->permission['U'])
		{
			new Log(array(
				'action' => 'create',
				'type' => 'WARNING',
				'component' => 'Files',
				'objekt_id' => $objekt->objekt_id,
				'message' => "Attempt to create file under '".$objekt->all['relative_path']."' (ID = ".$objekt->objekt_id.") with no create permission.",
			));

			return 'no_permissions_to_create_file';
		}

		$folder_path = preg_replace('#/$#', '', $site->absolute_path).$objekt->all['relative_path'];

		$filename = $file['name'];
		$filename_parts = explode('.', $file['name']);
		if(sizeof($filename_parts) > 1)
		{
			$file_extension = array_pop($filename_parts);
			$file_basename = implode('.', $filename_parts);
		}
		else
		{
			$file_extension = '';
			$file_basename = $filename;
		}

		$i = 0;
		while(file_exists($folder_path.'/'.$filename))
		{
			$filename = $file_basename.'_'.++$i.($file_extension ? '.'.$file_extension : '');

			// loop guard
			if($i > 1000) return 'file_already_exists';
		}

		$file['name'] = $filename;

		$upload_result = upload_file_to_folder($file, $folder_path);
		if($upload_result === true)
		{
			$file_id = insert_new_file_object($objekt, $file['name'], $file['size'], $file['type']);

			// thumbnail?
			create_file_thumbnail($folder_path.'/'.safe_filename2($file['name']));

			return (int)$file_id;
		}
		else
		{
			new Log(array(
				'action' => 'update',
				'component' => 'Files',
				'type' => 'ERROR',
				'message' => "Could not create file in '".$objekt->all['relative_path']."', file system error.",
			));

			return $upload_result;
		}
	}
	else
	{
		return 'no_such_folder_object';
	}
}

function get_filemanager_favorites()
{
	global $site;

	$favorites = $site->user->get_favorites(array(
		'tyyp_id' => '21,22',
		'order' => 'objekt.tyyp_id desc, objekt.pealkiri',
	));

	if(is_array($favorites)) foreach ($favorites as $key => $favorite)
	{
		$favorites[$key] = array(
			'objekt_id' => $favorite['objekt_id_r'],
			'parent_id' => $favorite['parent_id'],
			'relative_path' => $favorite['relative_path'],
			'class' => ($favorite['tyyp_id'] == 22 ? 'folder' : 'file'),
			'klass' => ($favorite['tyyp_id'] == 22 ? 'folder' : 'file'),
			'title' => $favorite['pealkiri'],
		);
	}

	return $favorites;
}

function create_folder_from_path($folder_path)
{
	global $site;

	$folder_path = trim($folder_path);

	// only public folders
	if(strpos($folder_path, 'public/') === 0)
	{
		$public_folders = get_folder_list('public');
		$folders = array();
		foreach ($public_folders as $folder)
		{
			$folders[$folder['objekt_id']] = preg_replace('#^/#', '', $folder['relative_path']);
		}

		$folders[$site->alias(array('key' => 'public', 'keel' => 1))] = 'public';

		$folder_id = (int)array_search($folder_path, $folders);

		// if folder id doesn't exist create folder
		if(!$folder_id)
		{
			$folder_path_parts = explode('/', $folder_path);
			$folders_to_create = array();

			while($folder = array_pop($folder_path_parts))
			{
				$folders_to_create[] = $folder;
				$folder_id = (int)array_search(implode('/', $folder_path_parts), $folders);
				if($folder_id) break;
			}

			$folders_to_create = array_reverse($folders_to_create);

			if($folder_id)
			{
				foreach($folders_to_create as $folder)
				{
					$folder_create_result = create_folder($folder, $folder_id);
					if(!is_int($folder_create_result))
					{
						return $folder_create_result;
					}
					else
					{
						$folder_id = $folder_create_result;
					}
				}

				return $folder_id;
			}
			else
			{
				return 'cant_create_folder';
			}
		}
		else
		{
			return $folder_id;
		}
	}
	else
	{
		return 'no_such_folder';
	}
}

// add single image to album
function add_image_to_album($file, $folder_path)
{
	global $site;

	$folder_path = trim($folder_path);

	// only public folders
	if(strpos($folder_path, 'public/') === 0)
	{

		$folder_id = create_folder_from_path($folder_path);

		if(is_int($folder_id))
		{
			// upload the file
			$upload_result = upload_to_folder($file, $folder_id);

			if(is_int($upload_result))
			{
				global $site, $class_path;

				$folder = new Objekt(array('objekt_id' => $folder_id));
				$conf = new CONFIG($folder->all['ttyyp_params']);

				include_once($class_path.'picture.inc.php');

				generate_images($site->absolute_path.$conf->get('path'), $conf->get('tn_size'), $conf->get('pic_size'));
			}

			return $upload_result;
		}
		else
		{
			// error message
			return $folder_id;
		}
	}
	else
	{
		return 'no_such_folder';
	}
}

/**
 * prints the HTML for editor and admin section toolbar
 *
 */
function print_editor_toolbar()
{
	global $site, $leht;

	if($site->user->all['user_id']) {

		//We make a query where we check if the user or his group or his roles might give access to any menus. If so, show the toolbar.

		if($site->user->group_id){
			$where[]="group_id=".$site->user->group_id;
		}
		if($site->user->user_id){
			$where[]="user_id=".$site->user->user_id;
		}
		if(is_array($site->user->roles) && sizeof($site->user->roles) >= 1){
			$where[]="role_id in (".implode(",",$site->user->roles).")";
		}

		$sql = "select id from permissions where (".implode(" or ",$where).") and (C=1 or U=1) and group_id!=1 limit 1";

		$sth = new SQL($sql);
		if($sth->rows || $site->user->is_superuser){

		include_once($class_path."adminpage.inc.php");
		$menu_list=admin_menu_list();

?><script type="text/javascript">
	var noConflict = false;
	if (typeof jQuery == 'undefined')
	{
		document.write('<script src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path']?>/jquery.js" type="text/javascript"><\/script>');
		noConflict = true;
	}
</script>
<script type="text/javascript">
	if(noConflict) jQuery.noConflict();

	jQuery(document).ready(function ()
	{
		jQuery('ul.scms_editor_dropdown').children('li').hover(displaySubMenu, hideSubMenu);

		jQuery('a.boxit, a.dont_boxit').click(function ()
		{
			jQuery(this).parent('li').parent('ul').css('display', 'none');
		});

		<?php if(0 || !$site->in_admin) { ?>jQuery('body').css('margin-top', '28px');<?php } ?>
	});

	function displaySubMenu()
	{
		jQuery(this).addClass('onmouseover');

		jQuery(this).children('ul').each(function (i)
		{
			jQuery(this).css('display', 'block');
		});
	}

	function hideSubMenu()
	{
		jQuery(this).removeClass('onmouseover');

		jQuery(this).children('ul').css('display', 'none');
	}

	var wwwroot = '<?php echo $site->CONF['wwwroot'];?>';
	var styles_path = '<?php echo $site->CONF['styles_path'];?>';
</script>
<script src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path']?>/thickbox_admin_pages.js" type="text/javascript"></script>

<div id="scms_editor_toolbar">
	<a href="javascript:void(0);" id="scms_editor_toolbar_logo"></a>
	<ul id="scms_toolbar_menu" class="scms_editor_dropdown"><?php
		foreach ((array)$menu_list as $main_menu) {
			?><li><a href="javascript:void(0);"><?php echo $main_menu['translated_name'];?></a><ul><?php
			foreach((array)$main_menu['submenus'] as $sub_menu) {
				// use thickbox for editors and don't use for ajavascript links
				if(!$site->in_admin && strpos($sub_menu['fail'], 'javascript') !== 0)
				{
					$class = 'boxit';

					if(strpos($sub_menu['fail'], '?') !== false)
						$sub_menu['fail'] .= '&';
					else
						$sub_menu['fail'] .= '?';

					$sub_menu['fail'] .= 'keepThis=true&TB_iframe=true&height=500&width=850';
				}
				else
				{
					$class = 'dont_boxit';
				}
				?><li><a href="<?php echo $sub_menu['fail'];?>" class="<?php echo $class?>" title="<?php echo $sub_menu['translated_name'];?>"<?php echo ($site->in_admin ? ' target="admin_page_container"' : ''); ?>><?php echo $sub_menu['translated_name'];?></a></li><?
			} ?></ul></li><?
		} ?></ul>
	<ul id="toolbar_tools">
		<li id="toolbar_tools_username"><?php echo $site->user->all['firstname']; ?></li>
		<li class="separator">|</li>
		<li><a href="<?php echo ($site->in_editor ? $site->CONF['wwwroot'].'/?id='.$leht->id : $site->CONF['wwwroot'].'/editor/?id='.$leht->id);?>"><?php echo ($site->in_editor ? 'Browse' : 'Edit');?></a></li>
		<?php if (sizeof((array)$menu_list)) { ?><li class="separator">|</li><li><a href="<?php echo $site->CONF['wwwroot']?>/<?php echo ($site->in_admin ? '' : 'admin') ?>"><?php echo ($site->in_admin ? 'Browse' : 'Admin');?></a></li><?php } ?>
		<li class="separator">|</li>
		<li><a href="?op=logout"><?php echo $site->sys_sona(array('sona' => 'logout', 'tyyp' => 'kujundus', 'lang_id' => $_SESSION['keel_admin']['glossary_id']));?></a></li>
	</ul>
	<?php // sites dropdwon
	if(!$site->in_admin)
	{
		$sql = "select keel_id, extension, nimi from keel where on_kasutusel = '1' order by nimi";
		$result = new SQL($sql);
		if($result->rows > 1)
		{
			$sql = "select nimi from keel where keel_id = ".(int)$_SESSION['keel']['keel_id'];
			$l_result = new SQL($sql);
			$active_site_name = $l_result->fetchsingle();

			?><ul id="site_links" class="scms_editor_dropdown"><li><a href="#"><?php echo (strlen($active_site_name) > 15 ? substr($active_site_name, 0 , 15).'..' : $active_site_name); ?></a><ul><?php

			while($row = $result->fetch('ASSOC'))
			{
				?><li><a href="<?php echo $site->CONF['wwwroot'].($site->in_editor ? '/editor' : '').'/?lang='.$row['extension']; ?>"><?php echo $row['nimi']; ?></a></li><?php
			}

		?></ul></li></ul><?php
		}
	}
	?>
</div><?php
		}
	}
}

function print_context_button_init()
{
	global $site;

		?>
<script type="text/javascript">
	var noConflict = false;
	if (typeof jQuery == 'undefined')
	{
		noConflict = true;
		document.write('<script src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path']; ?>/jquery.js" type="text/javascript"><\/script>');
	}

</script>
<script type="text/javascript">
	if(noConflict) jQuery.noConflict();
</script>
<script type="text/javascript">
	if (typeof jQuery.fn.contextMenu == 'undefined')
	{
		document.write('<script src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path']; ?>/jquery.contextMenu.js" type="text/javascript"><\/script>');
	}
</script>
<script type="text/javascript">
	if (typeof scmsNewObject == 'undefined')
	{
		document.write('<script src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path']; ?>/scms_context_menu.js" type="text/javascript"><\/script>');
	}
</script>
<script type="text/javascript">
	// All the SCMS action bindings
	jQuery.fn.contextMenu.addAction({name: 'scms_new_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> ...', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_new_section_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> <?php echo strtolower($site->sys_sona(array('sona' => 'tyyp_rubriik', 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));?>', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_new_article_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> <?php echo strtolower($site->sys_sona(array('sona' => 'tyyp_artikkel', 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));?>', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_new_link_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> <?php echo strtolower($site->sys_sona(array('sona' => 'tyyp_link', 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));?>', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_new_poll_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> <?php echo strtolower($site->sys_sona(array('sona' => 'tyyp_gallup', 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));?>', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_new_document_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> <?php echo strtolower($site->sys_sona(array('sona' => 'tyyp_dokument', 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));?>', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_new_image_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> <?php echo strtolower($site->sys_sona(array('sona' => 'tyyp_pilt', 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));?>', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_new_comment_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> <?php echo strtolower($site->sys_sona(array('sona' => 'tyyp_kommentaar', 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));?>', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_new_topic_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> <?php echo strtolower($site->sys_sona(array('sona' => 'tyyp_teema', 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));?>', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_new_album_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> <?php echo strtolower($site->sys_sona(array('sona' => 'tyyp_album', 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));?>', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_new_file_object', title: '<?php echo $site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?> <?php echo strtolower($site->sys_sona(array('sona' => 'tyyp_file', 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));?>', bind: scmsNewObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_edit_object', title: '<?php echo $site->sys_sona(array('sona' => 'context_button_edit', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?>', bind: scmsEditObject});
	jQuery.fn.contextMenu.addAction({name: 'scms_move_up_object', title: '<?php echo $site->sys_sona(array('sona' => 'context_button_move_up', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?>', bind: scmsMoveObjectUp});
	jQuery.fn.contextMenu.addAction({name: 'scms_move_down_object', title: '<?php echo $site->sys_sona(array('sona' => 'context_button_move_down', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?>', bind: scmsMoveObjectDown});
	jQuery.fn.contextMenu.addAction({name: 'scms_publish_object', title: '<?php echo $site->sys_sona(array('sona' => 'publish', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?>', bind: scmsToggleObjectPublishing});
	jQuery.fn.contextMenu.addAction({name: 'scms_unpublish_object', title: '<?php echo $site->sys_sona(array('sona' => 'context_button_unpublish', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?>', bind: scmsToggleObjectPublishing});
	jQuery.fn.contextMenu.addAction({name: 'scms_delete_object', title: '<?php echo $site->sys_sona(array('sona' => 'kustuta', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])); ?>', bind: scmsDeleteObject});

	jQuery.fn.contextMenu.settings.menuOpenEvent = '<?php echo ($site->CONF['context_menu_open_event'] ? $site->CONF['context_menu_open_event'] : 'click'); ?>';

	// attach buttons
	jQuery(document).ready(function ()
	{
		jQuery('.scms_context_button_anchor').contextMenu();
	});
</script>
		<?php

}

function save_sub_site_settings($settings)
{
	global $site;

	if(is_numeric($settings['keel_id']))
	{
		if($settings['on_default'])
		{
			$sql = "update keel set on_default = 0";
			new SQL($sql);
		}

		$sql = $site->db->prepare('update keel set nimi = ?, encoding = ?, glossary_id = ?, extension = ?, on_default = ?, site_url = ?, page_ttyyp_id = ?, ttyyp_id = ? where keel_id = ?',
			$settings['nimi'],
			$settings['encoding'],
			$settings['glossary_id'],
			$settings['extension'],
			$settings['on_default'],
			$settings['site_url'],
			$settings['page_ttyyp_id'],
			$settings['ttyyp_id'],
			$settings['keel_id']
		);

		new SQL($sql);

		// make sure glossary sys_words are present
		copySiteGlossary($settings['glossary_id']);

		new Log(array(
			'action' => 'update',
			'component' => 'Languages',
			'message' => "Language '".$settings['nimi']."' was updated.",
		));

		return true;
	}
	else
	{
		return false;
	}
}

function get_sub_site_objects_count($keel_id)
{
	global $site, $class_path;


	$obj_count = 0;
	include_once($class_path."alampuu.class.php");

	####### home
	$home_id = $site->alias(array('key' => 'rub_home_id', 'keel' => $keel_id));
	if($home_id) {
		$puu = new Alampuu(array(
			'parent_id' => $home_id,
			'skip_permissions_check' => 1
		));
		$obj_count += $puu->size;
	}
	####### system
	$system_id = $site->alias(array('key' => 'rub_system_id', 'keel' => $keel_id));
	if($system_id) {
		$puu = new Alampuu(array(
			'parent_id' => $system_id,
			'skip_permissions_check' => 1
		));
		$obj_count += $puu->size;
	}
	####### gallup_arhiiv
	$gallup_arhiiv_id = $site->alias(array('key' => 'rub_gallup_arhiiv_id', 'keel' => $keel_id));
	if($gallup_arhiiv_id) {
		$puu = new Alampuu(array(
			'parent_id' => $gallup_arhiiv_id,
			'skip_permissions_check' => 1
		));
		$obj_count += $puu->size;
	}
	####### trash
	$trash_id = $site->alias(array('key' => 'rub_trash_id', 'keel' => $keel_id));
	if($trash_id) {
		$puu = new Alampuu(array(
			'parent_id' => $trash_id,
			'skip_permissions_check' => 1
		));
		$obj_count += $puu->size;
	}

	return $obj_count;
}

function delete_sub_site($keel_id)
{
	global $site, $class_path;

	$sql = $site->db->prepare('select * from keel where keel_id = ?', $keel_id);
	$result = new SQL($sql);
	if($result->rows)
	{
		$site_data = $result->fetch('ASSOC');
	}
	else
	{
		return 'no_such_site';
	}

	$deleted_count = 0;

	include_once($class_path.'alampuu.class.php');

	# we skip all permissions check while deleting site objects

	####################
	# 2. DELETE HOME-TREE
	$home_id = $site->alias(array('key' => 'rub_home_id', 'keel' => $keel_id));

	### if found HOME section, start deleting
	if($home_id) {
		$puu = new Alampuu(array(
			'parent_id' => $home_id,
			'skip_permissions_check' => 1
		));
		$deleted_count += $puu->delete_objects();
	}	### / if found HOME section, start deleting
	# / 2. DELETE HOME-TREE
	####################

	####################
	# 3. DELETE SYSTEM-TREE
		$system_id = $site->alias(array('key' => 'rub_system_id', 'keel' => $keel_id));
		if($system_id) {
			$puu = new Alampuu(array(
				'parent_id' => $system_id,
				'skip_permissions_check' => 1
			));
			$deleted_count += $puu->delete_objects();
		}
	####################
	# 4. DELETE POLL'S ARCHIVE
		$gallup_arhiiv_id = $site->alias(array('key' => 'rub_gallup_arhiiv_id', 'keel' => $keel_id));
		if($gallup_arhiiv_id) {
			$puu = new Alampuu(array(
				'parent_id' => $gallup_arhiiv_id,
				'skip_permissions_check' => 1
			));
			$deleted_count += $puu->delete_objects();
		}
	####################
	# 5. DELETE RECYCLE BIN
		$trash_id = $site->alias(array('key' => 'rub_trash_id', 'keel' => $keel_id));
		if($trash_id) {
			$puu = new Alampuu(array(
				'parent_id' => $trash_id,
				'skip_permissions_check' => 1
			));
			$deleted_count += $puu->delete_objects();
		}

	####################
	# 6. SET LANGUAGE => NOT ACTIVE
	if($keel_id < 500)
	{
		$sql = $site->db->prepare('UPDATE keel SET on_kasutusel=?, extension=? WHERE keel_id=?', '', '', $keel_id);
	}
	else
	{
		// delete custom language
		$sql = $site->db->prepare('delete from keel WHERE keel_id=?', $keel_id);
	}
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	##################
	# WRITE LOG
	new Log(array(
		'action' => 'delete',
		'component' => 'Languages',
		'message' => "Language '".$site_data['nimi']."' was deleted. Objects deleted: ".$deleted_count,
	));

	return $deleted_count;
}

function create_sub_site($site_data)
{
	global $site;

	array_walk($site_data, 'trim');

	if($site_data['extension'])
	{
		$sql = $site->db->prepare('select keel_id from keel where extension = ?', $site_data['extension']);
		$result = new SQL($sql);

		if($result->rows)
		{
			return  'extension_must_be_unique';
		}
	}
	else
	{
		return 'extension_must_be_given';
	}

	######## 0. get first Saurus API page template ordered by template ID
	$sql = $site->db->prepare(
		"SELECT ttyyp_id FROM templ_tyyp WHERE on_page_templ=? AND ttyyp_id >=1000 ORDER BY ttyyp_id LIMIT 1",
		1
	);
	$sth = new SQL($sql);
	$first_page_ttyyp_id = $sth->fetchsingle();

	######## 1. set lang active + assign page template
	$sql = $site->db->prepare(
		"insert into keel SET nimi = ?, glossary_id = ?, encoding=?, extension=?, on_kasutusel='1', page_ttyyp_id=?, ttyyp_id = ?, site_url = ?",
			$site_data['name'],
			$site_data['glossary_id'],
			$site_data['encoding'],
			$site_data['extension'],
			$site_data['page_template_id'] ? $site_data['page_template_id'] : $first_page_ttyyp_id,
			$site_data['content_template_id'],
			$site_data['site_url']
		);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
	$new_keel_id = $sth->insert_id;

	//print "NEW: $settings["new_keel_id"]";

	######## 3. create system sections (sys_alias)
	$aliased = array('home','system','trash','gallup_arhiiv');

	$headline['home'] = "Home";
	$headline['system'] = "System section";
	$headline['trash'] = "Recycle Bin";
	$headline['gallup_arhiiv'] = "Poll's archive";

	foreach($aliased as $alias){
		$alias_id = $site->alias(array('key' => $alias, 'keel'=>$new_keel_id));

		# alias ei eksisteeri, teeme uue objekti:
		if (!$alias_id){

			#####################
			# insert into objekt:
			$sql = $site->db->prepare("
				INSERT INTO objekt (tyyp_id, pealkiri, on_avaldatud, keel, sys_alias, aeg, check_in, created_user_id, created_user_name)
				VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
				1,
				$headline[$alias],
				1,
				$new_keel_id,
				$alias,
				0,
				$site->user->id,
				$site->user->name
			);
			$sth = new SQL ($sql);
			$id = $sth->insert_id;

			if ($id){
				#####################
				# insert into objekt_objekt:
				$sql = "SELECT MAX(sorteering) FROM objekt_objekt";
				$sth = new SQL ($sql);
				$sorteering=$sth->fetchsingle();

				$sql = $site->db->prepare("INSERT INTO objekt_objekt (objekt_id, parent_id, sorteering) VALUES (?,?,?)",
					$id,
					0,
					$sorteering+1
				);
				$sth = new SQL($sql);

				$sql = $site->db->prepare("INSERT INTO obj_rubriik (objekt_id) VALUES (?)",	$id);
				$sth = new SQL($sql);

			} # if new object successfully inserted

		} # if sys_alias not found
	} # / loop over aliases

	########### / NEW LANGUAGE (set active + create system objects)

	new Log(array(
		'action' => 'create',
		'component' => 'Languages',
		'message' => "Language '".$site_data['name']." (".$site_data['extension'].")' was created",
	));

	// make sure glossary sys_words are present
	copySiteGlossary($site_data['glossary_id']);

	return $new_keel_id;
}

function copySiteGlossary($glossary_id)
{
	global $site;

	# kui korras, kopeerime k�ik sys_sonad
	# otsime k6ik sysonad, mis on tabelis 'sys_sonad_kirjeldus' ja ei ole tabelis 'sys_sona' vastava keeltega
	$sql = "
		SELECT sys_sonad_kirjeldus.sst_id, sys_sonad_kirjeldus.sys_sona, sum(sys_sonad.keel=?) AS cnt
		FROM sys_sonad_kirjeldus
		LEFT JOIN sys_sonad ON
		sys_sonad_kirjeldus.sst_id = sys_sonad.sst_id AND
		sys_sonad_kirjeldus.sys_sona = sys_sonad.sys_sona
		GROUP BY sys_sonad_kirjeldus.sst_id, sys_sonad_kirjeldus.sys_sona
		HAVING cnt=0
	";
	$sql = $site->db->prepare($sql, $glossary_id);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
	while ( $tmpsona = $sth->fetch() ) {
		$sql = $site->db->prepare(
		"INSERT INTO sys_sonad (sys_sona, keel, sst_id) values(?, ?, ?)",
			$tmpsona[sys_sona], $glossary_id, $tmpsona[sst_id]
		);
		$sth_ins = new SQL($sql);
		$site->debug->msg($sth_ins->debug->get_msgs());
	}
}

function create_glossary($glossary)
{
	global $site;

	array_walk($glossary, 'trim');

	if(!is_numeric($glossary['keel_id']))
	{
		return 'no_such_glossary';
	}

	$sql = $site->db->prepare('select keel_id, nimi from keel where keel_id = ?', $glossary['keel_id']);
	$result = new SQL($sql);
	if($result->rows == 1)
	{
		$glossary_data = $result->fetch('ASSOC');

		$sql = $site->db->prepare('update keel set encoding = ?, locale = ? where keel_id = ?', $glossary['encoding'], $glossary['locale'], $glossary['keel_id']);
		$result = new SQL($sql);

		new Log(array(
			'action' => 'create',
			'component' => 'Languages',
			'message' => 'Glossary "'.$glossary_data['nimi'].' has been created."',
		));

		copySiteGlossary($glossary['keel_id']);

		return true;
	}
	else
	{
		return 'no_such_glossary';
	}
}

function remove_glossary($glossary_id)
{
	global $site;

	$sql = $site->db->prepare('select keel_id, nimi from keel where keel_id = ?', $glossary_id);
	$result = new SQL($sql);
	if($result->rows == 1)
	{
		$glossary_data = $result->fetch('ASSOC');

		$sql = $site->db->prepare("DELETE FROM sys_sonad WHERE keel=?", $glossary_id);
		$sth = new SQL($sql);

		$sql = $site->db->prepare('select glossary_id from keel where on_default = 1');
		$result = new SQL($sql);

		$default_glossary_id = $result->fetchsingle();

		$sql = $site->db->prepare('update keel set glossary_id = ? where glossary_id = ?', $default_glossary_id, $glossary_id);
		$result = new SQL($sql);

		new Log(array(
			'action' => 'delete',
			'component' => 'Languages',
			'message' => 'Glossary "'.$glossary_data['nimi'].' has been removed."',
		));

		return true;
	}
	else
	{
		return 'no_such_glossary';
	}
}

function edit_glossary($glossary_data)
{
	global $site;

	$sql = $site->db->prepare('select keel_id, nimi from keel where keel_id = ?', $glossary_data['glossary_id']);
	$result = new SQL($sql);
	if($result->rows == 1)
	{
		$glossary = $result->fetch('ASSOC');

		if($glossary_data['on_default_admin'])
		{
			$sql = $site->db->prepare('update keel set on_default_admin = 0');
			$result = new SQL($sql);
		}

		$sql = $site->db->prepare('update keel set locale = ?, encoding = ?, on_default_admin = ? where keel_id = ?', $glossary_data['locale'], $glossary_data['encoding'], $glossary_data['on_default_admin'], $glossary_data['glossary_id']);
		$result = new SQL($sql);

		new Log(array(
			'action' => 'edit',
			'component' => 'Languages',
			'message' => 'Glossary "'.$glossary['nimi'].' has been changed."',
		));

		return true;
	}
	else
	{
		return 'no_such_glossary';
	}
}

function delete_system_word($word_id)
{
	global $site;

	########################
	# get sys_sona
	$sql = $site->db->prepare("SELECT sys_sona, sst_id FROM sys_sonad WHERE id=?", $word_id);
	$sth_s = new SQL($sql);
	$site->debug->msg($sth_s->debug->get_msgs());
	$delete_sysword = $sth_s->fetch();

	########################
	# delete FROM sys_sonad
	$sql = $site->db->prepare("DELETE FROM sys_sonad WHERE sys_sona=? AND sst_id=?", $delete_sysword['sys_sona'],$delete_sysword['sst_id']);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	# delete FROM sys_sonad
	$sql = $site->db->prepare("DELETE FROM sys_sonad_kirjeldus WHERE sys_sona=? AND sst_id=?", $delete_sysword['sys_sona'],$delete_sysword['sst_id']);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	########################
	# kirjuta toimetajate logi
	new Log(array(
		'action' => 'update',
		'component' => 'Language',
		'message' => 'Translations for "'.$delete_sysword['sys_sona'].'" deleted.',
	));

	return true;
}

function site_select_widget($id = 'site-select-widget', $selected_id = NULL)
{
	global $site;

	$sql = 'SELECT keel_id, nimi, extension FROM keel WHERE on_kasutusel = 1';
	$result = new SQL($sql);

	echo '<select id="'.$id.'" class="site-select-widget"><option></option>';
	while($row = $result->fetch('ASSOC'))
	{
		echo '<option value="'.$row['keel_id'].'"'.(isset($selected_id) && $row['keel_id'] == $selected_id ? ' selected="selected"' : NULL).'>'.$row['nimi'].' ('.$row['extension'].')</option>';
	}
	echo '</select>';
}

function user_select_widget($id = 'user-select-widget', $selected_id = NULL)
{
	global $site;

	$sql = 'SELECT user_id, firstname, lastname, username FROM users';
	$result = new SQL($sql);

	echo '<select id="'.$id.'" class="user-select-widget"><option value="0"></option>';
	while($row = $result->fetch('ASSOC'))
	{
		echo '<option value="'.$row['user_id'].'"'.(isset($selected_id) && $row['user_id'] == $selected_id ? ' selected="selected"' : NULL).'>'.$row['firstname'].' '.$row['lastname'].($row['username'] ? ' ('.$row['username'].')' : NULL).'</option>';
	}
	echo '</select>';
}

function group_role_select_widget($id = 'group-role-select-widget', $selected_gid = NULL, $selected_rid = NULL)
{
	global $site;

	echo '<select id="'.$id.'" class="group-role-select-widget"><option value="0"></option>';
	
	echo '<optgroup data-type="group" label="'.$site->sys_sona(array('sona' => 'groups', 'tyyp' => 'kasutaja')).'">';
	$groups = array();
	$sql = 'SELECT group_id, name, parent_group_id FROM groups ORDER BY parent_group_id, name';
	$result = new SQL($sql);
	while($row = $result->fetch('ASSOC'))
	{
		if($row['name'] == 'Everybody')
		{
			$groups[$row['group_id']] = $row + array('level' => 0);
		}
		else
		{
			$groups[$row['parent_group_id']]['children'][] = $row['group_id'];
			$groups[$row['group_id']] = $row + array('level' => $groups[$row['parent_group_id']]['level'] + 1);
		}
	}

	reset($groups);
	$group = current($groups);
	$traverse = array($group['group_id']);

	while($group_id = array_pop($traverse))
	{
		$group = $groups[$group_id];
		echo '<option data-type="group" value="'.$group['group_id'].'"'.(isset($selected_gid) && $group['group_id'] == $selected_gid ? ' selected="selected"' : NULL).'>'.str_repeat('&nbsp;', $group['level'] * 4).$group['name'].'</option>';
		if($group['children'])
		{
			foreach(array_reverse($group['children']) as $child_id)
			{
				$traverse[] = $child_id;
			}
		}
	}
	echo '</optgroup>';

	echo '<optgroup data-type="role" label="'.$site->sys_sona(array('sona' => 'roles', 'tyyp' => 'kasutaja')).'">';
	$sql = 'SELECT role_id, name FROM roles ORDER BY name';
	$result = new SQL($sql);
	while($row = $result->fetch('ASSOC'))
	{
		echo '<option data-type="role" value="'.$row['role_id'].'"'.(isset($selected_rid) && $row['role_id'] == $selected_rid ? ' selected="selected"' : NULL).'>'.$row['name'].'</option>';
	}
	echo '</optgroup>';

	echo '</select>';
}

// html helper functions
function print_js_variables()
{
	global $site, $class_path;
	include_once($class_path.'lgpl/Services_JSON.class.php');

	$json_encoder = new Services_JSON();
	
	$vars = add_js_variable('wwwroot', $site->CONF['wwwroot']);
	
	echo '<script>';
	echo 'var SCMS = {};';
	echo 'SCMS.variables = '.$json_encoder->encode($vars).';';
	echo '</script>';
}

function add_js_variable($name, $value)
{
	static $vars;
	
	$vars[$name] = $value;
	
	return $vars;
}
