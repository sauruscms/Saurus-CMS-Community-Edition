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



function smarty_function_save_profile($params, &$smarty)
{
	global $site, $class_path, $leht;
	include_once($class_path.'adminpage.inc.php'); // for check_profile_values()

	$id = (int)$params['id'];
	unset($params['id']);
	
	$parent_id = (int)$params['parent'];
	if(!$parent_id)
	{
		$parent_id = $leht->id;
		$current_objekt = $leht->objekt;
	}
	else 
	{
		$current_objekt = new Objekt(array(
			'objekt_id' => $parent_id,
		));
	}
	unset($params['parent']);
	
	if(!isset($params['name'])) 
		$name = 'insert_id';
	else
		$name = $params['name'];
	unset($params['name']);
	
	// for CMS objects on_create publishing
	$publish = (strtoupper(trim($params['on_create'])) == 'PUBLISH' ? 1 : 0);
	unset($params['on_create']);

	# get all profile data from cash
	
	# profile name is case insensitive
	$profile = strtolower($params['profile']);
	unset($params['profile']);

	$profile = $site->get_profile(array(
		'name' => $profile,
		'id' => (int)$params['profile_id'],
	));
	
	$profile_field_values = $params['fields'];
	unset($params['fields']);

	# sanity check: kui ei leitud sellise nimega profiili, anda toimetajale veateade
	
	if(!$profile['profile_id'])
	{
		if($site->admin)
		{
			print "<font color=red><b>Profile '".$profile['name']."' not found!</b></font>";
		}
		return;
	}

	// must go to source table
	$params['profile_id'] = $profile['profile_id'];
	
	// special cases for source table ID columns
	switch($profile['source_table'])
	{
		case 'users': $source_table_id_column = 'user_id'; break;
		case 'groups': $source_table_id_column = 'group_id'; break;
		default: $source_table_id_column = 'objekt_id'; break;
	}
	
	// if source_table is ext_ table
	if(strpos($profile['source_table'], 'ext_') === 0)
	{
		$source_table_id_column = 'id';
	}

	//printr($profile);
	
	$source_table_columns = array();
	$profile_data = unserialize($profile['data']);
	foreach ($profile_data as $column => $data)
	{
		if($data['is_active']) // using only active fields
		{
			if($data['is_general']) // is in general objekt table
			{
				$source_table_columns[] = 'objekt.'.$column;
			}
			else
			{
				$source_table_columns[] = $profile['source_table'].'.'.$column;
			}
		}
	}
	//printr($source_table_columns);
	
	$profile_field_values = array();
	$profile_data['id'] = 0;
	foreach (array_keys($profile_data) as $key)
	{
		$profile_field_values[$key] = '';
	}
	
	//check profile filed values, errors go into $site->fdat['form_error']
	$sql_values = check_profile_values(array(
		'profile_def' => $profile, 
		'skip_non_active_fields' => true, 
		'use_only_profile_fields' => true, 
	));
	$sql_values_skip_prepare = array();
	
	// add additional fields to sql values
	foreach ($params as $field_name => $field_value)
	{
		$sql_values[$field_name] = $field_value;
		if(array_search($profile['source_table'].'.'.$field_name, $source_table_columns) === false) $source_table_columns[] = $profile['source_table'].'.'.$field_name;
	}
	// add profile_id 
	if(array_search($profile['source_table'].'.profile_id', $source_table_columns) === false) $source_table_columns[] = $profile['source_table'].'.profile_id';

	//$sql_values = array_unique($sql_values);
	
	// special case for users
	if($profile['source_table'] == 'users')
	{
		// username is required field but readonly for already registered users
		if($site->fdat['form_error']['username'] && $params['username'])
		{
			unset($site->fdat['form_error']['username']);
		}
		
		// username must be unique for new user
		if(!$id)
		{
			$sql = $site->db->prepare('select username from users where username = ?', $sql_values['username']);
			$result = new SQL($sql);
			if($result->rows)
			{
				$site->fdat['form_error']['username'] = $site->sys_sona(array('sona' => 'user exists', 'tyyp' => 'kasutaja'));
			}
		}
		
		############ E-MAIL: CHECK FOR CORRECT FORMAT
		if ($sql_values['email'] != '' && !preg_match("/^[\w\-\&\.\d]+\@[\w\-\&\.\d]+$/", $sql_values['email']))
		{
			$site->fdat['form_error']['email'] = $site->sys_sona(array('sona' => 'wrong email format', 'tyyp' => 'kasutaja'));
		}

		############ E-MAIL: CHECK FOR DUPLICATES
		if($sql_values['email']) {
			$sql = $site->db->prepare("SELECT user_id FROM users WHERE email=? AND user_id<>?",
				$sql_values['email'],
				$id
			);
			$sth = new SQL($sql);
			if ($exists = $sth->fetchsingle()) {
				$site->fdat['form_error']['email'] = $site->sys_sona(array(sona => 'Email already exists', 'tyyp' => 'kasutaja'));
			}
		}
		
		############ PASSWORD: CHECK FOR CONFIRM MATCH & ENCRYPT
		# if password is set
		if (!$id || $params['password'])
		{
			if(!$params['password'])
			{
				$site->fdat['form_error']['password'] = $site->sys_sona(array('sona' => 'field required', 'tyyp' => 'kasutaja'));
			}
			
			if(!$params['confirm_password'])
			{
				$site->fdat['form_error']['confirm_password'] = $site->sys_sona(array('sona' => 'field required', 'tyyp' => 'kasutaja'));
			}
			
			$old_user_enc_password = $site->user->all['password'];
			if (isset($site->user->all['password'])) unset($site->user->all['password']);

			# if password expired, then check, if user inserted new password (check if this match with old one)
			if($old_user_enc_password && $site->user->all['pass_expired'])
			{
				if ($old_user_enc_password == crypt($sql_values['password'], $old_user_enc_password))
				{	
					$you_inserted_old_password = 1;
				}
			}

			if ($you_inserted_old_password)
			{
				$site->fdat['form_error']['password'] = $site->sys_sona(array('sona' => 'Password expired message', 'tyyp' => 'kasutaja'));
			}
			elseif($params['confirm_password'] != $sql_values['password'])
			{
				$site->fdat['form_error']['password'] = $site->sys_sona(array('sona' => 'wrong confirmation', 'tyyp' => 'kasutaja'));
			}
			elseif($site->CONF['users_require_safe_password'] == 1 && strlen($sql_values['password']) < 8 && !(preg_match('/[a-z]/', $sql_values['password']) && preg_match('/[A-Z]/', $sql_values['password']) && preg_match('/[0-9]/', $sql_values['password'])))
			{
				$site->fdat['form_error']['password'] = $site->sys_sona(array('sona' => 'pass_not_strong', 'tyyp' => 'kasutaja'));
			}
			# if OK then encrypt password
			else
			{
				$sql_values['password'] = crypt($sql_values['password'], Chr(rand(65,91)).Chr(rand(65,91)));
				
				// set pass_expiring date
				if(!$sql_values['pass_expires'] || $sql_values['pass_expires'] == '0000-00-00')
				{
					$source_table_columns[] = 'users.pass_expires';
					$sql_values['pass_expires'] = "DATE_ADD(now(), INTERVAL ".$site->CONF['default_pass_expire_days']." DAY)";
					$sql_values_skip_prepare['users.pass_expires'] = 1;
				}
			} # if confirm ok
			
		} # if password set
		# else if password is not set then don't save and overwrite it
		else
		{
			unset($sql_values['password']);
			$key = array_search('users.password', $source_table_columns);
			if($key !== false) unset($source_table_columns[$key]);
		}
		// remove confirm_password
		unset($sql_values['confirm_password']);
		$key = array_search('users.confirm_password', $source_table_columns);
		if($key !== false) unset($source_table_columns[$key]);
		
		// set group_id only for new users
		if(!$sql_values['group_id'] && !$id)
		{
			$sth = new SQL('SELECT group_id FROM groups  WHERE is_predefined = 1');
			$site->debug->msg($sth->debug->get_msgs());	
			$sql_values['group_id'] = $sth->fetchsingle();
			$source_table_columns[] = 'users.group_id';
		}
		
		// set created_date
		if(!$sql_values['created_date'] && !$id)
		{
			$source_table_columns[] = 'users.created_date';
			$sql_values['created_date'] = date('Y-m-d');
		}
	}
	
	// if no erros
	if(!sizeof($site->fdat['form_error']))
	{
		// UPDATE a field
		if($id)
		{
			$update_source_sql = '';
			$update_objekt_sql = '';
			
			foreach ($source_table_columns as $source_table_column)
			{
				if(strpos($source_table_column, 'objekt.') === 0)
				{
					// only pealkir allowed and it must be prepared
					if($source_table_column == 'objekt.pealkiri')
					{
						$title = $sql_values[substr($source_table_column, strpos($source_table_column, '.') + 1)];
						$update_objekt_sql .= $site->db->prepare($source_table_column.' = ?, ', $title);
						$update_objekt_sql .= $site->db->prepare('objekt.pealkiri_strip = ?, ', strip_tags($title));
					}
				}
				else 
				{
					if($sql_values_skip_prepare[$source_table_column])
					{
						$update_source_sql .= $source_table_column.' = '.$sql_values[substr($source_table_column, strpos($source_table_column, '.') + 1)].', ';
					}
					else 
					{
						$update_source_sql .= $site->db->prepare($source_table_column.' = ?, ', $sql_values[substr($source_table_column, strpos($source_table_column, '.') + 1)]);
					}
				}
			}
			// remove trailing ,
			$update_source_sql = substr_replace($update_source_sql, '', strlen($update_source_sql) - 2);
			$update_objekt_sql = substr_replace($update_objekt_sql, '', strlen($update_objekt_sql) - 2);
			
			// if this is a CMS objekt
			if(strpos($profile['source_table'], 'obj_') === 0)
			{
				$objekt = new Objekt(array('objekt_id' => $id));
				// object must have READ and UPDATE permissions
				if($objekt->objekt_id && $objekt->permission['R'] && $objekt->permission['U'])
				{
					// update the object table first
					// changed_user_id
					$update_objekt_sql .= ($update_objekt_sql ? ', ' : ' ').'objekt.changed_user_id = '.(int)$site->user->id;
					// changed_user_name
					$update_objekt_sql .= $site->db->prepare(',  objekt.changed_user_name = ?', $site->user->name);
					// changed_time
					$update_objekt_sql .= ', objekt.changed_time = now()';
					
					$sql = 'update objekt set '.$update_objekt_sql.' where objekt.objekt_id = '.$id;
					//printr($sql);
					new SQL($sql);
					
					$sql = 'update '.$profile['source_table'].' set '.$update_source_sql.' where '.$profile['source_table'].'.objekt_id = '.$id;
					//printr($sql);
					new SQL($sql);
					
					new Log(array(
						'action' => 'update',
						'objekt_id' => $objekt->objekt_id,
						'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($objekt->all['klass'])), $title, $objekt->objekt_id, "changed"),
					));
						
					$smarty->assign($name, $id);
				}
				else 
				{
					new Log(array(
						'action' => 'update',
						'type' => 'WARNING',
						'objekt_id' => $objekt->objekt_id,
						'message' => sprintf("Access denied: attempt to edit %s '%s' (ID = %s)" , ucfirst(translate_en($objekt->all['klass'])), $objekt->pealkiri(), $objekt->objekt_id),
					));
					$smarty->assign($name, 0);
				}
			}
			else 
			{
				if($profile['source_table'] == 'users' && $site->user->all['is_readonly'] == 1)
				{
						new Log(array(
							'action' => 'update',
							'type' => 'WARNING',
							'component' => 'Users',
							'message' => "User '".$site->user->all['firstname'].' '.$site->user->all['lastname']."'  tried to update an account but was unable because of a is_readonly flag",
						));
					$smarty->assign($name, 0);
				}else{
				$sql = 'update '.$profile['source_table'].' set '.$update_source_sql.' where '.$source_table_id_column.' = '.$id;
				//printr($sql);
				$result = new SQL($sql);
				if($result->error_no == 0)
				{
					// log values for new user
					if($profile['source_table'] == 'users')
					{
						new Log(array(
							'action' => 'update',
							'component' => 'Users',
							'message' => "User '".$site->user->all['firstname'].' '.$site->user->all['lastname']."' account updated",
						));
					}
					else 
					{
						new Log(array(
							'action' => 'update',
							'message' => "Record (ID: ".$id.") updated in ".$profile['source_table'],
						));
					}
					
					$smarty->assign($name, $id);
				}
				else 
				{
					$smarty->assign($name, 0);
				}
				}
			}
			}
		// insert new
		else 
		{
			$insert_source_sql = '';
			$insert_objekt_sql = '';
			foreach ($source_table_columns as $source_table_column)
			{
				if(strpos($source_table_column, 'objekt.') === 0)
				{
					// only pealkir allowed and it must be prepared
					if($source_table_column == 'objekt.pealkiri')
					{
						$title = $sql_values[substr($source_table_column, strpos($source_table_column, '.') + 1)];
						$insert_objekt_sql .= $site->db->prepare($source_table_column.' = ?, ', $title);
						$insert_objekt_sql .= $site->db->prepare('objekt.pealkiri_strip = ?, ', strip_tags($title));
					}
				}
				else 
				{
					if($sql_values_skip_prepare[$source_table_column])
					{
						$insert_source_sql .= $source_table_column.' = '.$sql_values[substr($source_table_column, strpos($source_table_column, '.') + 1)].', ';
					}
					else 
					{
						$insert_source_sql .= $site->db->prepare($source_table_column.' = ?, ', $sql_values[substr($source_table_column, strpos($source_table_column, '.') + 1)]);
					}
				}
			}
			// remove trailing ,
			$insert_objekt_sql = substr_replace($insert_objekt_sql, '', strlen($insert_objekt_sql) - 2);
			$insert_source_sql = substr_replace($insert_source_sql, '', strlen($insert_source_sql) - 2);

			// if this is a CMS objekt
			if(strpos($profile['source_table'], 'obj_') === 0)
			{
				// parent object must have create permission
				if($current_objekt->permission['C'])
				{
					//must be fields and cannot be overwritten by user data
					// tyyp_id
					$class_id = (int)array_search(str_replace('obj_', '', $profile['source_table']), $site->object_tyyp_id_klass);
					$insert_objekt_sql .= ($insert_objekt_sql ? ', ' : ' ').'objekt.tyyp_id = '.$class_id;
					// keel
					$insert_objekt_sql .= ', objekt.keel = '.$site->keel;
					// kesk (position)
					//$insert_objekt_sql .= ', kesk = '.(int)$current_objekt->all['kesk'];
					// aeg 
					$insert_objekt_sql .= ', objekt.aeg = now()';
					// publishing
					$insert_objekt_sql .= ', objekt.on_avaldatud = '.$publish;
					// created user_id
					$insert_objekt_sql .= ', objekt.created_user_id = '.(int)$site->user->id;
					// created user_name
					$insert_objekt_sql .= $site->db->prepare(', objekt.created_user_name = ?', $site->user->name);
					// created time
					$insert_objekt_sql .= ', objekt.created_time = now()';
					// comment_count, for less errors in database_repair.php
					$insert_objekt_sql .= ', objekt.comment_count = 0';
					
					$sql = 'insert into objekt set '.$insert_objekt_sql;
					//printr($sql);
					$result = new SQL($sql);
					$id = $result->insert_id;
					if($id)
					{
						$sql = 'select max(sorteering)+1 from objekt_objekt';
						$result = new SQL($sql);
						
						$sql = $site->db->prepare('insert into objekt_objekt set objekt_id = ?, parent_id = ?, sorteering = ?', $id, $parent_id, $result->fetchsingle());
						//printr($sql);
						$result = new SQL($sql);
						
						$insert_source_sql .= ', '.$profile['source_table'].'.objekt_id = '.$id;
						$sql = 'insert into '.$profile['source_table'].' set '.$insert_source_sql;
						//printr($sql);
						$result = new SQL($sql);
						new Log(array(
							'action' => 'create',
							'objekt_id' => $id,
							'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst($site->object_tyyp_id_nimi[$class_id]), $title, $id, "inserted"),
						));
							
						foreach(unserialize($profile['data']) as $key => $value)
						{
							unset($site->fdat[$key]);
						}
						$smarty->assign($name, $id);
					}
					else 
					{
						$smarty->assign($name, 0);
					}
				}
				else 
				{
					// no create permission
					new Log(array(
						'action' => 'create',
						'type' => 'WARNING',
						'message' => sprintf("Access denied: attempt to create %s under restricted category ID = %s" , ucfirst(translate_en(str_replace('obj_', '', $profile['source_table']))), $current_objekt->objekt_id),
					));
					$smarty->assign($name, 0);
				}
			
			}elseif($profile['source_table'] == 'users' && $site->user->all['is_readonly'] == 1){

					new Log(array(
						'action' => 'update',
						'component' => 'Users',
						'type' => 'WARNING',
						'message' => "User '".$site->user->all['firstname'].' '.$site->user->all['lastname']."' tried to update his account, but was unable to because of a read_only flag on his/her account",
					));


			}
			else 
			{
				$sql = 'insert into '.$profile['source_table'].' set '.$insert_source_sql;
				//printr($sql);
				$result = new SQL($sql);
				if($result->insert_id)
				{
					// log values for new user
					if($profile['source_table'] == 'users')
					{
						new Log(array(
							'action' => 'create',
							'component' => 'Users',
							'message' => "New user '".$sql_field_values['username']."' inserted",
						));
					}
					else 
					{
						new Log(array(
							'action' => 'create',
							'message' => "Record (ID: ".$result->insert_id.") inserted into ".$profile['source_table'],
						));
					}
					
					foreach(unserialize($profile['data']) as $key => $value)
					{
						unset($site->fdat[$key]);
					}
					$smarty->assign($name, $result->insert_id);
				}
				else 
				{
					$smarty->assign($name, 0);
				}
			}
		}
	}
	else 
	{
		$_POST['form_error'] = $site->fdat['form_error'];
		$smarty->assign($name, 0);
	}

}
