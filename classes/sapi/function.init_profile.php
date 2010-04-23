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



function smarty_function_init_profile($params, &$smarty)
{
	global $site, $class_path;
	
	include_once($class_path.'adminpage.inc.php'); // for print_profile_fields()
	
	extract($params);
	
	$id = (int)$id;
	
	if(!isset($name)) $name = 'profile';
	
	// make comma separated readonly fields into array
	if($readonly_fields)
	{
		$readonly_fields = explode(',', $readonly_fields);
		// trim whitespace
		for($i = 0; $i < sizeof($readonly_fields); $i++) $readonly_fields[$i] = trim($readonly_fields[$i]);
	}
	else
	{
		$readonly_fields = array();
	}
	
	# get all profile data from cash
	
	# profile name is case insensitive
	$profile = strtolower($profile);

	$profile = $site->get_profile(array(
		'name' => $profile,
		'id' => (int)$profile_id,
	));
	# sanity check: kui ei leitud sellise nimega profiili, anda toimetajale veateade
	
	if(!$profile['profile_id'])
	{
		if($site->admin)
		{
			print "<font color=red><b>Profile '".$profile['name']."' not found!</b></font>";
		}
		return;
	}
	
	switch($profile['source_table']) // special cases for source table ID columns
	{
		case 'users': $source_table_id_column = 'user_id'; break;
		case 'groups': $source_table_id_column = 'group_id'; break;
		default: $source_table_id_column = 'objekt.objekt_id'; break;
	}
	
	// if source_table is ext_ table
	if(strpos($profile['source_table'], 'ext_') === 0)
	{
		$source_table_id_column = 'id';
	}

	//printr($profile);
	
	$source_table_columns = array();
	$profile_data = unserialize($profile['data']);
	//printr($profile_data);
	$do_left_join_objekt = false;
	foreach ($profile_data as $column => $data)
	{
		if($data['is_active']) // using only active fields
		{
			if($data['is_general']) // is in general objekt table
			{
				$source_table_columns[] = 'objekt.'.$column;
				$do_left_join_objekt = true;
			}
			else
			{
				$source_table_columns[] = $profile['source_table'].'.'.$column;
			}
		}
	}
	
	$profile_field_values = array();
	$profile_data['id'] = 0;
	foreach (array_keys($profile_data) as $key)
	{
		$profile_field_values[$key] = '';
	}
	
	if($id) // ID given, load data from source table
	{
		$sql = 'select '.$source_table_id_column.' as id'.(sizeof($source_table_columns) ? ', '.implode(', ', $source_table_columns) : '').' from '.$profile['source_table'].($do_left_join_objekt ? ' left join objekt using(objekt_id)' : '').' where '.$source_table_id_column.' = '.$id;
		$result = new SQL($sql);
		//printr($sql);
		if($result->rows)
		{
			$profile_field_values = $result->fetch('ASSOC');
		}
	}
	
	$profile_field_html = print_profile_fields(array(
		'profile_fields' => unserialize($profile['data']), 
		'field_values' => $profile_field_values,
		'return_fields' => true,
		'load_defaults' => ($id ? false : true),
	));
	
	$profile_out = null;
	$profile_out->all = $profile;
	$profile_out->id = $profile['profile_id'];
	$profile_out->table = $profile['source_table'];
	$profile_out->name = $profile['name'];
	$profile_out->label = $site->sys_sona(array('sona' => $profile['name'], 'tyyp' => 'custom', ));
	$profile_out->title = $profile_out->label;
	$profile_out->data = array();

	$i = 0;
	foreach ($profile_data as $data)
	{
		if($data['is_active'])
		{
			$profile_out->data[$i] = null;
			$profile_out->data[$i]->value = (($data['type'] == 'DATETIME' || $data['type'] == 'DATE') ? $site->db->MySQL_ee($profile_field_values[$data['name']]) : $profile_field_values[$data['name']]);
			$profile_out->data[$i]->type = $data['type'];
			$profile_out->data[$i]->is_required = $data['is_required'];
			$profile_out->data[$i]->name = $data['name'];
			$profile_out->data[$i]->label = $site->sys_sona(array('sona' => $data['name'], 'tyyp' => 'custom', ));
			$profile_out->data[$i]->title = $profile_out->data[$i]->label;

		
	if($site->user->all['is_readonly'] == 1){

		if(!in_array($data['name'], $readonly_fields))
			$profile_out->data[$i]->HTML = $profile_out->data[$i]->html = $profile_out->data[$i]->value;

	}else{

		if(!in_array($data['name'], $readonly_fields))
			$profile_out->data[$i]->HTML = $profile_out->data[$i]->html = $profile_field_html[$data['name']];
		else 
			$profile_out->data[$i]->HTML = $profile_out->data[$i]->html = $profile_out->data[$i]->value;

	}


			$i++;
		}
	}
	
	//printr($profile_out);
	$smarty->assign($name, $profile_out);
}
