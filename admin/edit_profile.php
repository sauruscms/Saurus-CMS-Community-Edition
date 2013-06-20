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
 * Popup page for editing profile data
 * 
 * tbl 'object_profiles'
 * 
 * @param string pid 
 * @param string did 
 * @param string op - action name
 * @param string op2 - step 2 action name
 * 
 */

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");


$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));

if (!$site->user->allowed_adminpage(array('adminpage_id' => 74,))) { # adminpage_id=74 => "System > Profiles"
	############ debug
	if($site->user) { $site->user->debug->print_msg(); } # user debug
	if($site->guest) { 	$site->guest->debug->print_msg(); } 	# guest debug
	$site->debug->print_msg(); 
	exit;
}

$op = $site->fdat['op'];
$op2 = $site->fdat['op2'];

$site->fdat['pid'] = (int)$site->fdat['pid'];

######################
# leida valitud keele p�hjal �ige lehe encoding,
# admin-osa keel j��b samaks

$keel_id = isset($site->fdat['flt_keel']) ? $site->fdat['flt_keel'] : $site->fdat['keel_id'];
if (!strlen($keel_id)) { $keel_id = $site->glossary_id; }

$sql = "select sst_id from sys_sona_tyyp where voti = 'custom'";
$result = new SQL($sql);
$custom_sst_id = $result->fetchsingle();

//printr($custom_sst_id);


/**
* FUNCTION delete_profile_field
* 
* Checks if this field is allowed actually to delete from table (execute drop field command);
* Profile field is not allowed to delete if one of following conditions is true:
*	- fields is predefined
*	- field doesn't exists in other profiles
*   - field is system field (minimum to operate with table)
*	
* returns 1/0
* 
* usage:
* $is_deleted = delete_profile_field(array(
*	"did" => $site->fdat['did'],
*	"profile" => $profile
* ));
*/
function delete_profile_field(){
	global $site;
	
	$args = @func_get_arg(0);
	
	$did = $args['did']; # profile field ID
	$profile = $args['profile']; # profile array, result of function "$site->get_profile"

	if(!$did) { return 0; } 
	if(!sizeof($profile)) { return 0; }

	$drop_denied = 0;

	$data = unserialize($profile['data']);
	## 1) deny deleting if field is predefined OR general object field
	if( $data[$did]['is_predefined'] || $data[$did]['is_general']) {
		$drop_denied = 1;
		$explanation = " - it's predefined";
	}

	## 2) field exists in other profiles?
	# get all profiles with same source table:
	$sql = $site->db->prepare("SELECT data FROM object_profiles WHERE source_table=? AND profile_id<>? ",$profile['source_table'],$profile['profile_id']);
	$sth = new SQL($sql);
	while($others = $sth->fetch()){
		$others_data = unserialize($others['data']);
		if(is_array($others_data)){
			$others_fields = array_keys($others_data);
			# if found field with same name
			if(in_array($did,$others_fields)){
				$drop_denied = 1;
				$explanation = " - found in another profile";
				continue;
			} 
		}
	}

	## 3) if field is system field (minimum to operate with table OR provided with default installation), then dont delete it
	if($profile['source_table'] == 'users') {
		$system_fields = array('user_id','group_id','email','is_predefined','profile_id','username','password','firstname','lastname','title','image','created_date','session_id','last_access_time','is_locked','idcode','address','postalcode','tel','pass_expires','autologin_ip','last_ip','account_nr','reference_nr','city','country','delivery_address','delivery_city','delivery_zip','delivery_country','contact_phone','contactperson','birthdate');
	}
	elseif($profile['source_table'] == 'groups') {
		$system_fields = array('group_id','name','parent_group_id','is_predefined','description','auth_type','auth_params','profile_id','tel','email');
	}
	elseif($profile['source_table'] == 'obj_dokument') {
		$system_fields = array('objekt_id','profile_id');
	}
	elseif($profile['source_table'] == 'obj_asset') {
		$system_fields = array('objekt_id','profile_id');
	}
	elseif($profile['source_table'] == 'obj_file') {
		$system_fields = array('objekt_id','profile_id','fullpath','relative_path','filename','mimetype','size','lastmodified','is_deleted');
	}
	elseif($profile['source_table'] == 'obj_folder') {
		$system_fields = array('objekt_id','profile_id','fullpath');
	}
	elseif($profile['source_table'] == 'obj_artikkel') {
		$system_fields = array('objekt_id','profile_id','lyhi','sisu','algus_aeg','lopp_aeg');
	}
	elseif(substr($prof_row['source_table'],0,4) == 'obj_') {
		$system_fields = array('objekt_id', 'profile_id');
	}

	# if field is system field:
	if( is_array($system_fields) ){
	if(in_array($did,$system_fields)){
		$drop_denied = 1;
		$explanation = " - it's system field";
	}
	} # is array

	#echo "did: ".$did." => denied:  ".$drop_denied;
	# if drop allowed

	if(!$drop_denied){
�+		$sql = "ALTER TABLE ".$profile['source_table']." DROP ".$did;
		$sth = new SQL($sql);
		if($sth->error){ $msg = $sth->error; $drop_denied = 1;}
	}
	else { $msg = "<br>Field '".$did."' was not deleted from table".$explanation; }

	if($drop_denied) {
		print_error_html(array(
			"message" => $msg,
			"close_js" => 'window.opener.location=window.opener.location;'
		));
	}

	# return 1 if successfully deleted, 0 otherwise	
	return !$drop_denied;

}


###############################
# PROFILE: Save profile name & close

if($site->fdat['op2'] == 'save_profile_name' || $site->fdat['op2'] == 'saveclose_profile_name') {
	verify_form_token();
	if($site->fdat['profile_name']) {

		$site->fdat['profile_name'] = strtolower($site->fdat['profile_name']); // #2743
		$site->fdat['source_table'] = strtolower($site->fdat['source_table']); // #2743
		
		###### check if profile name exists
		$check_profile_def = $site->get_profile(array("name"=>$site->fdat['profile_name'])); 
		## if not found OR itself found, go on with saving
		if(($check_profile_def['name'] != $site->fdat['profile_name']) || $check_profile_def['profile_id'] == $site->fdat['pid']) {

		##### if is_default is set to "1" then set all other profiles (with same type) to "is_default => 0".
		if($site->fdat['is_default'] == 1){
			$sql = $site->db->prepare("UPDATE object_profiles SET is_default=? WHERE source_table=?",0,$site->fdat['source_table']);
			$sth = new SQL($sql);		
		}
		
		if($op=='new') {

			## New
			if($site->fdat['source_table'] == 'obj_asset')
				// create default content
				$data = 'a:1:{s:8:"pealkiri";a:9:{s:4:"name";s:8:"pealkiri";s:4:"type";s:4:"TEXT";s:13:"source_object";s:0:"";s:13:"default_value";s:0:"";s:7:"db_type";s:7:"varchar";s:11:"is_required";i:0;s:9:"is_active";i:0;s:13:"is_predefined";i:1;s:10:"is_general";i:1;}}';
			else 
				$data = 'NULL';
			$sql = $site->db->prepare("INSERT INTO object_profiles (name,data, source_table,is_default) VALUES (?,?,?,?)",$site->fdat['profile_name'], $data, $site->fdat['source_table'],($site->fdat['is_default']?1:0));
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			$site->fdat['pid']= $sth->insert_id;

			####### write log
			new Log(array(
				'action' => 'create',
				'component' => 'Profiles',
				'message' => "New profile '".$site->fdat['profile_name']."' inserted",
			));
			##### if new was saved, then make it 'edit'
			if($site->fdat['op2'] == 'save_profile_name'){
				$site->fdat['op'] = "edit";
			}

			############### CREATE new FORM (create transparently new form)
			if(substr($site->fdat['source_table'],0,5) == 'form_'){

			$sql = $site->db->prepare("INSERT INTO forms (name,profile_id,source_table) VALUES (?,?,?)",$site->fdat['profile_name'],$site->fdat['pid'], $site->fdat['source_table']);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			}# CREATE new FORM

		} 
		elseif($op=='edit') {
			## Update
			$sql = $site->db->prepare("UPDATE object_profiles SET name=?, is_default=? WHERE profile_id=?",$site->fdat['profile_name'],($site->fdat['is_default']?1:0),$site->fdat['pid']);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			####### write log
			new Log(array(
				'action' => 'update',
				'component' => 'Profiles',
				'message' => "Profile '".$site->fdat['profile_name']."' updated",
			));
		}
		elseif($op=='duplicate') {
			## 1. select source row
			$sql = $site->db->prepare("SELECT name, data, source_table FROM object_profiles WHERE profile_id=?",$site->fdat['pid']);
			$sth = new SQL($sql);
			$prof_row = $sth->fetch();

			## 2. insert as new row
			$sql = $site->db->prepare("INSERT INTO object_profiles (name,data, source_table) VALUES (?,?,?)",$site->fdat['profile_name'],$prof_row['data'],$prof_row['source_table']);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			$site->fdat['pid']= $sth->insert_id;
						
			####### write log
			new Log(array(
				'action' => 'create',
				'component' => 'Profiles',
				'message' => "Profile '".$site->fdat['profile_name']."' inserted",
			));
		}
		#######################
		# save system word to group "Custom":
		save_systemword(array(
			"sysword" => $site->fdat['profile_name'],
			"translation" => $site->fdat['profile_label'],
			"lang_id" => $site->fdat['word_keel_id'],
			"sst_id" => $custom_sst_id,
		));

		#######################
		# CREATE NEW TABLE (if doesnt exist)

		$sql = $site->db->prepare("show tables");
		$sth = new SQL($sql);
		while ($tbl_data = $sth->fetchsingle()){
			$tables[] = $tbl_data;
		}
		#printr($tables);
		
		# if table doesn't exist:
		if(!in_array($site->fdat['source_table'],$tables)){

			###### create table with 1 default field "profile_id"
			$sql = $site->db->prepare("CREATE TABLE ".$site->fdat['source_table']." (profile_id INT DEFAULT '0' NOT NULL)");
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			$sql = "ALTER TABLE ".$site->fdat['source_table']." ADD INDEX profile_id (profile_id) ";
			$sth = new SQL($sql);

			### for forms: create field "workflow_proc_id"
			if(substr($site->fdat['source_table'],0,5) == 'form_'){

				$sql = "ALTER TABLE ".$site->fdat['source_table']." ADD workflow_proc_id INT ";
				$sth = new SQL($sql);
				$sql = "ALTER TABLE ".$site->fdat['source_table']." ADD INDEX workflow_proc_id (workflow_proc_id)";
				$sth = new SQL($sql);


			}

			####### write log
			new Log(array(
				'action' => 'create',
				'component' => 'Profiles',
				'message' => "Table '".$site->fdat['source_table']."' inserted",
			));
		}# if table doesn't exist


		} 
		##### if profile name exists => show error
		else {
			$form_error['profile_name'] = $site->sys_sona(array(sona => "value exists", tyyp=>"editor"));
		}

	} # if name set

	#################
	# kui keelt muudeti selectboxis, siis �ra pane akent kinni vaid n�ita sama akent uue keelega
	if($site->fdat['keel_changed']) {
		$site->fdat['op2'] = "";
	}
	################
	# kui vajutati salvesta nuppu ja ei olnud erroreid, pane aken kinni
	elseif ($op2=='saveclose_profile_name' && count($form_error)==0) {
	?>
	<HTML>
	<SCRIPT language="javascript"><!--
		var oldurl = window.opener.location.toString();
		oldurl = oldurl.replace(/\?profile_id=(\d+)/g, "");
		if('<?=$op?>'=='new') {
			newurl = oldurl + '?profile_id=<?=$site->fdat['pid']?>'; 
			window.opener.location=newurl;
		} else {
			window.opener.location=window.opener.location;	
		}
		window.close();
	// --></SCRIPT>
	</HTML>
	<?
	exit;
	}
	################
	# kui vajutati uuenda nuppu, siis tee refresh
	elseif ($op2=='save_profile_name' && count($form_error)==0) {

		header("Location: ".$site->self."?op=edit&pid=".$site->fdat['pid']);
		exit;
	}
} # op2=save_profile_name

###############################
# PROFILE: DELETE ENTIRE profile 

# Note: fields in database will be also deleted if they doesn't exist in the other profiles

if($op2 == 'deleteconfirmed' && is_numeric($site->fdat['pid']) && !$site->fdat['did']) {

	verify_form_token();
	## Get existing profile
	$prof_row = $site->get_profile(array(id=>$site->fdat['pid'])); 
	$existing_data = unserialize($prof_row['data']);

	$smth_not_deleted = 0;
	if(is_array($existing_data) && sizeof($existing_data)>0){
	foreach($existing_data as $did=>$did_arr){
		########## drop field in table 
		$is_deleted = delete_profile_field(array(
			"did" => $did,
			"profile" => $prof_row
		));
		if(!$is_deleted) { $smth_not_deleted = 1; }			
	}
	}
	# delete pofile 
	$sql = $site->db->prepare("DELETE FROM object_profiles WHERE profile_id=?",$site->fdat['pid']);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());

	######### delete table
	if($site->fdat['delete_source_table']) {
		$sql = $site->db->prepare("DROP TABLE ".$site->fdat['source_table']." ");
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		######## delete form (if there is no content table then no need for header table also)
		$sql = $site->db->prepare("DELETE FROM forms WHERE profile_id=?",$site->fdat['pid']);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		### log message
		$additional_log = ". Table '".$site->fdat['source_table']."' deleted";	
	}



	####### write log
	new Log(array(
		'action' => 'delete',
		'component' => 'Profiles',
		'message' => "Profile '".$site->fdat['profile_name']."' deleted".$additional_log,
	));

	if(!$smth_not_deleted){
	?>
	<HTML>
	<SCRIPT language="javascript"><!--
//		var oldurl = window.opener.location.toString();
//		newurl = oldurl.replace(/\?profile_id=(\d+)/g, "");
		window.opener.location=window.opener.location;
		window.close();
	// --></SCRIPT>
	</HTML>
	<?
	}
	exit;
}

###############################
# FIELD: DELETE a profile field (1 row)
if($site->fdat['op2'] == "deleteconfirmed" && is_numeric($site->fdat['profile_id']) && $site->fdat['did']) {
		verify_form_token();
		## Get existing profile
		$prof_row = $site->get_profile(array(id=>$site->fdat['profile_id'])); 
		$existing_data = unserialize($prof_row['data']);

		$is_predefined = $existing_data[$site->fdat['did']]['is_predefined'];
		$is_general = $existing_data[$site->fdat['did']]['is_general'];
		# remember field name for log:
		$deleted_field_name = $existing_data[$site->fdat['did']]['name'];
		
		##Delete field data (1 row)
		unset($existing_data[$site->fdat['did']]);

		$update_data = serialize($existing_data);
		$sql = $site->db->prepare("UPDATE object_profiles SET data=? WHERE profile_id=?",$update_data,$site->fdat['profile_id']);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$site->fdat['profile_id'] = $site->fdat['profile_id'];

		########## dont delete from table if predefined/system field
		if(!$is_predefined){
			########## drop field in table 
			$is_deleted = delete_profile_field(array(
				"did" => $site->fdat['did'],
				"profile" => $prof_row
			));

		} # if
		########## / dont delete from table if predefined/system field

		####### write log
		new Log(array(
			'action' => 'delete',
			'component' => 'Profiles',
			'message' => "Field '".$deleted_field_name."' in profile '".$prof_row['name']."' deleted.".($is_deleted? '':' Table field not deleted.'),
		));
		if($is_deleted){
		?>
		<HTML>
		<SCRIPT language="javascript"><!--
			window.opener.location=window.opener.location;
			window.close();
		// --></SCRIPT>
		</HTML>
		<?
		} # if successfully deleted
		exit;

}
# / DELETE a profile field (1 row)
###############################

###############################
# FIELD: SAVE 1 PROFILE FIELD  & close
#
if($op2=='save_profile_definition' || $op2=='saveclose_profile_definition') {
#echo printr($site->fdat);

	verify_form_token();
	############################
	# 1. kui t��pe v�is ka selectboxist muuta
	# profile_map on tegelik v�ljanimi tabelis
	if($site->fdat['definition_name'] && $site->fdat['pid'] && $site->fdat['profile_map']) {

		$site->fdat['definition_name'] = strtolower($site->fdat['definition_name']); // #2743
		$site->fdat['profile_map'] = strtolower($site->fdat['profile_map']); // #2743
		
		############# 1. GATHER DATA : NEW OR UPDATE: if new or update in the same profile
		if(1 || $site->fdat['op']=='new' || $site->fdat['op']=='edit') {

		# change profile id where to save the field, if duplicate operation then change it;

		$site->fdat['pid'] = $site->fdat['op']=='duplicate' ? $site->fdat['dest_pid']: $site->fdat['pid'];

			## Get any existing data
			$sql = $site->db->prepare("SELECT name, data, source_table FROM object_profiles WHERE profile_id=?",	$site->fdat['pid']	);
			$sth = new SQL($sql);
			$prof_row = $sth->fetch();
			$site->debug->msg($sth->debug->get_msgs());
			$existing_data = unserialize($prof_row['data']);

			## Update data: get existing data
			if(!empty($site->fdat['did']) && $site->fdat['op']!='duplicate') {
				## Get keys and change keyname
				$existing_data_keys = array_keys($existing_data);
				$index = array_search ($site->fdat['did'], $existing_data_keys);
				$existing_data_keys[$index] = $site->fdat['profile_map'];
				
				## Get values and change them
				$existing_data_values = array_values($existing_data);
				$existing_data_values[$index] = array(
						"name" =>$site->fdat['definition_name'],
						"type" =>$site->fdat[display_type],
						"source_object" =>$site->fdat['source_object'],
						"default_value" =>$site->fdat['default_value'],
						"db_type" =>$site->fdat['profile_type'],
						"is_required" => ($site->fdat[is_required]?1:0),
						"is_active" => ($site->fdat[is_active]?1:0),
						"is_predefined" => ($site->fdat[is_predefined]?1:0),
						"is_general" => ($site->fdat[is_general]?1:0),
						);
				##Make new array
				unset($existing_data);
				foreach($existing_data_keys as $index => $key) {
					$existing_data[$key] = $existing_data_values[$index];
				}
			} 
			# New field: get form data
			else {
				$existing_data[$site->fdat['profile_map']] = array(
						"name" =>$site->fdat['definition_name'],
						"type" =>$site->fdat[display_type],
						"source_object" =>$site->fdat['source_object'],
						"default_value" =>$site->fdat['default_value'],
						"db_type" =>$site->fdat['profile_type'],
						"is_required" => ($site->fdat[is_required]?1:0),
						"is_active" => ($site->fdat[is_active]?1:0),
						"is_predefined" => ($site->fdat[is_predefined]?1:0),
						"is_general" => ($site->fdat[is_general]?1:0),
						);		
				# juhul kui oli uus data JA vahetati keelt:
				$site->fdat['did'] = $site->fdat['profile_map'];
				$insert = 1; # save for log message 
				$site->fdat['op'] = "edit";
			} # new or update

		}
		############# / 1. GATHER DATA : NEW OR UPDATE: if new or update in the same profile

		############# 2. GATHER DATA : DUPLICATE: if duplicate to the destination profile
		elseif($site->fdat['op']=='duplicate') {


		}
		############# / 2. GATHER DATA : DUPLICATE: if duplicate to the destination profile

		# NOW we have all necessary data in the existing_data array 
		# (both, recently entered data and data being in the table previously) 

		############# 1. SAVE DATA : if new or update in the same profile
		if($site->fdat['op']=='new' || $site->fdat['op']=='edit') {

			$update_data = serialize($existing_data);
			$sql = $site->db->prepare("UPDATE object_profiles SET data=? WHERE profile_id=?",$update_data,$site->fdat['pid']);
#			print $sql;
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
		} 

		############# 2. SAVE DATA : if duplicate to the same or to different destination profile
		elseif($site->fdat['op']=='duplicate') {
			$update_data = serialize($existing_data);
			$sql = $site->db->prepare("UPDATE object_profiles SET data=? WHERE profile_id=?",$update_data,$site->fdat['pid']);
#			print $sql;
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			# redirect?

		}

		######### create new field in TABLE if not found (type depends on db_type):
		# check if field already exists:
		$all_fields = array();
		$all_fields = split(",", $site->db->get_fields(array(tabel => $prof_row['source_table'])) );
		# add new field only if it doesn't exist in table AND if it's not general object field (already exists)
		if(!in_array($site->fdat['profile_map'],$all_fields) && !$site->fdat['is_general']) {
			if($site->fdat['profile_type']=='varchar') {
					$type = "VARCHAR(255)";
			}
			elseif($site->fdat['profile_type']=='integer') {
					$type = "BIGINT(11)";
			}
			elseif($site->fdat['profile_type']=='float') {
					$type = "FLOAT(7,2)";
			}
			elseif($site->fdat['profile_type']=='text') {
				$type = "TEXT";
			}
			elseif($site->fdat['profile_type']=='date') {
					$type = "DATE";
			}
			elseif($site->fdat['profile_type']=='datetime') {
				$type = "DATETIME";
			}
			elseif($site->fdat['profile_type']=='tinyint') {
					$type = "TINYINT(1)";
			}
			$sql = "ALTER TABLE ".$prof_row['source_table']." ADD ".$site->fdat['profile_map']." ".$type;
#print $sql;
			$sth = new SQL($sql);
			if($sth->error){ print $sth->error; }

		} # if field doesn't exist in table
		######### / create new field in TABLE

		####### write log
		new Log(array(
			'action' => ($insert || $site->fdat['op'] == "duplicate"?'create':'update'),
			'component' => 'Profiles',
			'message' => "Field '".$site->fdat['definition_name']."' in profile '".$prof_row['name']."' ".($insert || $site->fdat['op'] == "duplicate"?'inserted':'updated'),
		));
	} # save profile data
	####################

	#######################
	# save system word to group "Custom":
	save_systemword(array(
		"sysword" => $site->fdat['definition_name'],
		"translation" => $site->fdat['definition_label'],
		"lang_id" => $site->fdat['word_keel_id'],
		"sst_id" => $custom_sst_id,
	));

	#################
	# kui keelt muudeti selectboxis, siis �ra pane akent kinni vaid n�ita akent uue keelega
	if($site->fdat['keel_changed']) {
		$site->fdat['op2'] = "";
	}
	################
	# kui vajutati salvesta nuppu, pane aken kinni
	elseif ($op2=='saveclose_profile_definition') {
	?>
	<HTML>
	<SCRIPT language="javascript"><!--
		window.opener.location=window.opener.location;
		window.close();
	// --></SCRIPT>
	</HTML>
	<?
	exit;
	}
}
# / SAVE 1 PROFILE FIELD & close
#############################

######################
# 1. DELETE CONFIRMATION WINDOW (ENTIRE PROFILE)
if($op == 'delete' && $site->fdat['pid']) {
	$profile_def = $site->get_profile(array(id=>$site->fdat['pid'])); 
	$profile_name = $profile_def['name'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->admin->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>
<body class="popup_body">
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<?php create_form_token('delete-profile'); ?>
	<input type=hidden name=pid value="<?=$site->fdat['pid']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
	<input type=hidden name=profile_name value="<?=$profile_name?>">


<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100px">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?
	# check if allowed to delete
	# 1. if exist any object / user / group/ document with that profile, then don't allow to delete
	$data_count = 0;

	############ assets
	if($profile_def['source_table'] == 'obj_asset'){
		$sql = $site->db->prepare("SELECT COUNT(*) FROM obj_asset LEFT JOIN objekt_objekt on obj_asset.objekt_id=objekt_objekt.objekt_id WHERE profile_id=? AND objekt_objekt.parent_id<>?",$site->fdat['pid'],$site->alias("trash"));
		$sth = new SQL($sql);
		$asset_count = $sth->fetchsingle();
		$data_count += $asset_count;
	}
	############ forms
	elseif(substr($profile_def['source_table'],0,5) == 'form_'){
		$sql = $site->db->prepare("SELECT COUNT(*) FROM ".$profile_def['source_table']." WHERE profile_id=? ",$site->fdat['pid']);
		$sth = new SQL($sql);
		$form_count = $sth->fetchsingle();
		$data_count += $form_count;

		# get other profiles with this source_table
		$sql = $site->db->prepare("SELECT COUNT(*) FROM object_profiles WHERE source_table=? AND profile_id<>? ",$profile_def['source_table'], $site->fdat['pid']);
		$sth = new SQL($sql);
		$other_profiles_count = $sth->fetchsingle();
	}
	
	# POOLELI: kontrolilida ka teisi lapsi...

	if($data_count > 0) {
		# show error message
		echo "<font color=red>".$site->sys_sona(array(sona => "Permission denied", tyyp=>"editor"))."</font><br><br>";
		echo $site->sys_sona(array(sona => "Children count", tyyp=>"admin")).": <b>".$data_count."</b>";
	}
	# 2. dont delete default profiles 
	elseif($profile_def['is_predefined']) {
		# show error message
		echo "<font color=red>".$site->sys_sona(array(sona => "Permission denied", tyyp=>"editor"))."</font><br><br>";
	}
	# show confirmation
	else {
		echo $site->sys_sona(array(sona => "kustuta", tyyp=>"editor"))." \"<b>".$profile_name."</b>\"? ";
		echo $site->sys_sona(array(sona => "are you sure?", tyyp=>"admin"));
		######## show additonal table delete checkbox for FORMS

		if(substr($profile_def['source_table'],0,5) == 'form_'){
			# show checkbox enabled only when no other profiles found with this source_table:
			if($other_profiles_count > 0){  $disabled = "disabled";}
			?>
			<br><br>
			<input type=checkbox id="delete_source_table" name="delete_source_table" value=1 <?=$disabled?>><input type=hidden name=source_table value="<?=$profile_def['source_table']?>">
			<label for="delete_source_table"><?=$site->sys_sona(array(sona => "delete_table", tyyp=>"editor"))?> "<b><?=$profile_def['source_table']?></b>"</label>
			<?
		}
		$allow_delete = 1;
	}
?>
	</td>
  </tr>
  <tr align="right"> 
    <td valign="top" colspan=2 > 
		<?if($allow_delete){?>
            <input type="button" value="<?=$site->sys_sona(array(sona => "kustuta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='deleteconfirmed';frmEdit.submit();">
			<?}?>
			<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

</form>
</body>
</html>
<?
	############ debug
	# user debug:
	if($site->user) { $site->user->debug->print_msg(); }
	# guest debug: 
	if($site->guest) { 	$site->guest->debug->print_msg(); }
	$site->debug->print_msg(); 
	exit;
}	
# / 1. DELETE CONFIRMATION WINDOW (ENTIRE PROFILE)
######################



######################
# 2. DELETE CONFIRMATION WINDOW (ONE FIELD)
if($op == 'delete' && is_numeric($site->fdat['profile_id']) && $site->fdat['did']) {
	$profile_def = $site->get_profile(array(id=>$site->fdat['profile_id'])); 
	$profile_data = unserialize($profile_def['data']);	# profile_data is now array of ALL fields, indexes are table fieldnames
	$profile_info = $profile_data[$site->fdat['did']];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->admin->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>
<body class="popup_body">
	<form name="frmEdit" action="<?=$site->self?>" method="POST">
	<?php create_form_token('delete-profile-field'); ?>
	<input type=hidden name=did value="<?=$site->fdat['did']?>">
	<input type=hidden name=profile_id value="<?=$site->fdat['profile_id']?>">
	<input type=hidden name=op value="<?=$site->fdat['op']?>">
	<input type=hidden name=op2 value="">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100px">
  <tr> 
	<td valign="top" width="100%" class="scms_confirm_delete_cell" height="100%">
<?
	# show confirmation
		echo $site->sys_sona(array(sona => "kustuta", tyyp=>"editor"))." \"<b>".$profile_info['name']."</b>\"? ";
		echo $site->sys_sona(array(sona => "are you sure?", tyyp=>"admin"));
		$allow_delete = 1;
?>
	</td>
  </tr>
  <tr align="right"> 
    <td valign="top" colspan=2 > 
		<?if($allow_delete){?>
            <input type="button" value="<?=$site->sys_sona(array(sona => "kustuta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='deleteconfirmed';frmEdit.submit();">
			<?}?>
			<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

</form>
</body>
</html>
<?
	############ debug
	# user debug:
	if($site->user) { $site->user->debug->print_msg(); }
	# guest debug: 
	if($site->guest) { 	$site->guest->debug->print_msg(); }
	$site->debug->print_msg(); 

	exit;
}	
# / 2. DELETE CONFIRMATION WINDOW (ONE FIELD)
######################


###############################
# 3. NEW/EDIT/DUPLICATE PROFILE NAME
# for new profile is required profile id being on same level OR source table name
if($site->fdat['op'] == "new" || 
	( ($site->fdat['op'] == "edit" || $site->fdat['op'] == "duplicate") && $site->fdat['pid'] && !$site->fdat['did'] )
) {

# get profile info 
if($site->fdat['pid']) {
	$profile_def = $site->get_profile(array(id=>$site->fdat['pid'])); 
	$profile_name = $profile_def['name'];
	if($site->fdat['op'] == "duplicate") {
#		$profile_name = 'Copy of '.$profile_name;
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding ? $encoding : $site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
</head>
<body class="popup_body" onLoad="this.focus();document.forms['vorm'].profile_name.focus();">

<FORM action="<?=$site->self ?>" method="post" name="vorm">
	<?php create_form_token('edit-profile'); ?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
<tr> 
    <td valign="top" width="100%" class="scms_dialog_area_top"  height="100%">
	  <table width="100%" border="0" cellspacing="0" cellpadding="2">
		<?############### keel ############?>
		<tr> 
          <td align="right" colspan="2" style="padding:3px" bgcolor="#F1F1EC"> 
            <table width="100" cellspacing="0" cellpadding="0">
              <tr> 
                <td><?=$site->sys_sona(array(sona => "keel", tyyp=>"editor"))?>:</td>
                <td style="padding-left:3px"> 
                  <select name="flt_keel" onchange="document.forms['vorm'].keel_changed.value='1';document.forms['vorm'].submit()">
				<?	# Keeled
				$sql = "select distinct b.glossary_id as keel_id, a.nimi as nimi from keel as a left join keel as b on a.keel_id = b.glossary_id where b.on_kasutusel = '1'";
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());		
				while ($keel = $sth->fetch()) {
					print "	<option value=\"".$keel['keel_id']."\" ".($keel['keel_id'] == $keel_id ? "selected":"").">$keel[nimi]</option>";
					# t�lke juures peidetud keelev�lja jaoks:
					if($keel['keel_id'] == $keel_id){
						$word_keel_id = $keel['keel_id'];
					}
				} ?>	
				</select>
                </td>
              </tr>
            </table>				
		</td>
        </tr>
	  <?############ name #########
		if($form_error['profile_name']){
			$profile_name = $site->fdat['profile_name'];
		}
		?> 
	  <tr> 
		<td><?=$site->sys_sona(array(sona => "nimi", tyyp=>"editor"))?>: </td>
		<td width="100%"><input type=text name=profile_name value="<?= ($site->fdat['op']=="new" && !$form_error['profile_name']? '' : $profile_name) ?>" class="scms_flex_input" onchange="this.value = this.value.toLowerCase();" onkeyup="javascript: if(event.keyCode==13){vorm.submit();}">
			
		<?=($form_error['profile_name']? '<br><font color=red>'.$form_error['profile_name'].'</font>':'')?>
		</td>
	  </tr>
	<?############### label/translation + hidden keele v�li #######?>
	<? 
	if($site->fdat['op'] != 'new'){
		$label = $site->sys_sona(array(sona => $profile_name, tyyp=>"custom", lang_id=>$word_keel_id));
		# kui s�steemis�na puudub:
		$label = ($label != '['.strtolower($profile_name).']') ? $label : '';
	}
	?>
	<tr>
		<td><?=$site->sys_sona(array(sona => "Tolkimine", tyyp=>"admin"))?>: </td>
		<td width="95%" STYLE="padding-bottom:5px"><input type=text name="profile_label" value="<?=$label ?>" class="scms_flex_input"><input type=hidden name="word_keel_id" value="<?=$word_keel_id?>"></td>
	</tr>

	<?############### source table #######?>
	<tr>
		<td nowrap><?=$site->sys_sona(array(sona => "DB Table", tyyp=>"xml"))?>: </td>
		<td width="95%" STYLE="padding-bottom:5px"><INPUT TYPE="text" name="source_table" value="<?=($profile_def['source_table']?$profile_def['source_table']:($site->fdat['source_table']?$site->fdat['source_table']:'obj_asset'))?>" class="scms_flex_input" onchange="this.value = this.value.toLowerCase();" onblur="this.value = this.value.toLowerCase();"></td>
	</tr>

	<?############### is_default #######?>
	<tr>
		<td nowrap></td>
		<td width="95%"><INPUT TYPE="checkbox" name="is_default" id="is_default" value="1" <?=($site->fdat['op']!='new' && $profile_def['is_default']==1?"checked":"")?>><label for="is_default"><?=$site->sys_sona(array(sona => "Default", tyyp=>"admin"))?></label></td>
	</tr>



	  </table>
	</td>
</tr>
	<?############ buttons #########?>
	<tr> 
	  <td align="right" valign="top" class="scms_dialog_area_bottom"> 
         <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript: document.forms['vorm'].op2.value='save_profile_name';this.form.submit();">
         <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:document.forms['vorm'].op2.value='saveclose_profile_name';this.form.submit();">
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

<?########### hidden ########?>
<INPUT TYPE="hidden" name="pid" value="<?= $site->fdat['pid'] ?>">
<INPUT TYPE="hidden" name="op" value="<?=$site->fdat['op']?>">
<INPUT TYPE="hidden" name="op2" value="save_profile_name">
<input type=hidden name=keel_changed value="">
</form>
</body>
</html>
<?
############ debug
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 

exit;
}
# / 3. NEW/EDIT PROFILE NAME
###############################


###############################
# 4. NEW/EDIT/DUPLICATE PROFILE FIELD

if($site->fdat['op'] == "newdef" || 
	(($site->fdat['op'] == "edit" || $site->fdat['op'] == "duplicate") && $site->fdat['did'])
) {

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$encoding ? $encoding : $site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<?
		################
		# Get PROFILE INFO
		if($site->fdat['pid']) {
			$profile_def = $site->get_profile(array(id=>$site->fdat['pid'])); 
			$profile_data = $profile_def['data'];
			$profile_data = unserialize($profile_data);	# profile_data is now array of ALL fields, indexes are table fieldnames
		}
		# sanity check: kui ei leitud sellise nimega profiili, anda toimetajale veateade ja v�ljuda:
		if(!$profile_def['profile_id']) {
			print "<font color=red><b>Profile '".$site->fdat['pid']."' not found!</b></font>";
			exit;		
		}
		############ get ONE FIELD INFO (edit existing)
		# param $site->fdat['did'] is current fieldname in table (eg username, varchar_1)
		if($site->fdat['did']) { # edit
			$profile_info = $profile_data[$site->fdat['did']];
		}

		############ get ALL FIELDS from source table
		$all_fields = array();
		$all_fields = split(",", $site->db->get_fields(array(tabel => $profile_def['source_table'])) );

		if(is_array($profile_data)){ # if any profile field exists in database

			############ gather EXISTING PROFILE FIELDNAMES into arr
			$existing_fields = array_keys($profile_data);
			############ gather EXISTING PROFILE NAMES into arr
			foreach($profile_data as $key=>$tmpprof){ 
				$existing_names[] = $tmpprof['name'];
			}

		} # if any profile field exists in database

		############ find AVAILABLE FIELDNAMES for new field
		if(sizeof($existing_fields) > 0) {
			$available_fields = array_minus_array($all_fields,$existing_fields);
		}
		else { $available_fields = $all_fields; }

		############ make available fields BY TYPE array: for using in javascript
		if($profile_def['source_table'] == 'obj_asset') {
			foreach($available_fields as $key=>$field){
				if(eregi("varchar_(\d*)",$field)) {
					$available_fields_bytype["varchar"][] = $field;
				}
				elseif(eregi("int_(\d*)",$field)) {
					$available_fields_bytype["int"][] = $field;
				}
				elseif(eregi("float_(\d*)",$field)) {
					$available_fields_bytype["float"][] = $field;
				}
				elseif(eregi("text_(\d*)",$field)) {
					$available_fields_bytype["text"][] = $field;
				}
				elseif(eregi("date_(\d*)",$field)) {
					$available_fields_bytype["date"][] = $field;
				}
				elseif(eregi("datetime_(\d*)",$field)) {
					$available_fields_bytype["datetime"][] = $field;
				}
			}
		} # if obj_asset

?>
<SCRIPT LANGUAGE="JavaScript"><!--

	var existing_fields = new Array();
	<? if(is_array($existing_fields)) {
		foreach($existing_fields as $key=>$field){ ?>
		existing_fields[<?=$key?>] = '<?=$field?>';
	<? } } ?>

	var existing_names = new Array();
	<? if(is_array($existing_names)) {
		foreach($existing_names as $key=>$field){ ?>
		existing_names[<?=$key?>] = '<?=$field?>';
	<? } } ?>


	function change_profile_map_value1(db_type) {
	<? # 1. if obj_asset, then paramter is db_type, take first value from available fields: ?>
	<? if($profile_def['source_table'] == 'obj_asset' && $site->fdat['op'] == "newdef") { ?>
		if(db_type=="varchar") {
			document.forms['vorm'].profile_map.value='<?=$available_fields_bytype["varchar"][0]?>';	
		}
		else if(db_type=="integer") {
			document.forms['vorm'].profile_map.value='<?=$available_fields_bytype["int"][0]?>';	
		}
		else if(db_type=="float") {
			document.forms['vorm'].profile_map.value='<?=$available_fields_bytype["float"][0]?>';	
		}
		else if(db_type=="text") {
			document.forms['vorm'].profile_map.value='<?=$available_fields_bytype["text"][0]?>';	
		}
		else if(db_type=="date") {
			document.forms['vorm'].profile_map.value='<?=$available_fields_bytype["date"][0]?>';	
		}
		else if(db_type=="datetime") {
			document.forms['vorm'].profile_map.value='<?=$available_fields_bytype["datetime"][0]?>';	
		}
		else if(db_type=="tinyint") {
			document.forms['vorm'].profile_map.value='<?=$available_fields_bytype["tinyint"][0]?>';	
		}
		// if fieldname still not set => set it to profilefield name
		if(document.forms['vorm'].profile_map.value=='') {
			// convert to safe format
			document.forms['vorm'].profile_map.value= safe_filename(document.forms['vorm'].definition_name.value.toLowerCase());
		}
		document.forms['vorm'].profile_map_show.value='<?= $profile_def['source_table'] ?>.'+document.forms['vorm'].profile_map.value.toLowerCase();
	<? }?>
	}
	function change_profile_map_value2(definition_name) {
	<? # 2.  (users, etc), then parameter is already fieldvalue, just copy-paste it 
		# fixed bug in 4.0.0 full versions: table obj_asset doesnt contain all these predefined fields anymore
	?>
	<? if ( ($profile_def['source_table'] != 'obj_asset' || !sizeof($available_fields_bytype["varchar"])) && ($site->fdat['op'] == "newdef" || $site->fdat['op'] == "duplicate")) { ?>

	definition_name = definition_name!=''?definition_name:'undefined';
	// convert to safe format
	safevalue = safe_filename(definition_name);
	
	// if duplicating to another profile then keep field name
	if(document.forms['vorm'].dest_pid && '<?=$site->fdat[pid]?>' != document.forms['vorm'].dest_pid.options[document.forms['vorm'].dest_pid.options.selectedIndex].value) {
		// do nothing
	}
	// if duplicating to the current profile then add suffix "_2" to the field name
	else {	// if new field
		// check if field exists
		for ( i = 0; i < existing_fields.length; i++) {
		   if(existing_fields[i] == safevalue) {
				safevalue += '_2';
		   }
		} 
	} // new or duplicate
		document.forms['vorm'].profile_map.value=safevalue.toLowerCase();
		document.forms['vorm'].profile_map_show.value='<?= $profile_def['source_table'] ?>.'+safevalue.toLowerCase();
	<? } ?>
	}
	function refresh_source_object(type) {
		if(type=="SELECT" || type=="MULTIPLE SELECT" || type=="RADIO" || type=="CHECKBOX" || type=="BROWSE") {
			document.getElementById("source_object").disabled = false;
		} else {
			document.getElementById("source_object").disabled = true;
		}
	}
	function refresh_db_type(type) {
		if(type=="SELECT" || type=="MULTIPLE SELECT" || type=="RADIO" || type=="FILE" || type=="CHECKBOX") {
			// force selectbox selection to varchar
			document.forms['vorm'].profile_type.options.selectedIndex = 0;
			document.forms['vorm'].profile_type.disabled = true;
		}
		else if(type=="DATE") {
			// force selectbox selection to datetime
			document.forms['vorm'].profile_type.options.selectedIndex = 4;
			document.forms['vorm'].profile_type.disabled = true;
		}
		else if(type=="DATETIME") {
			// force selectbox selection to datetime
			document.forms['vorm'].profile_type.options.selectedIndex = 5;
			document.forms['vorm'].profile_type.disabled = true;
		}
		else if(type=="TEXTAREA") {
			// force selectbox selection to text
			document.forms['vorm'].profile_type.options.selectedIndex = 3;
			document.forms['vorm'].profile_type.disabled = false;
		}
		else if(type=="BOOLEAN") {
			// force selectbox selection to tinyint
			document.forms['vorm'].profile_type.options.selectedIndex = 6;
		} else {
			document.forms['vorm'].profile_type.disabled = false;
		}

	}
	function change_label(name) {
		var label = name.toString();
		label = label.substring(0,1).toUpperCase() + label.substring(1);
		if(document.forms['vorm'].definition_label.value==''){
			document.forms['vorm'].definition_label.value=label;
		};
	}
	function check_name(name) {
		for ( i = 0; i < existing_names.length; i++) {
		   if(existing_names[i] == name) {
				document.forms['vorm'].definition_name.value = name+'_2';
		   }
		} 
	}

	function enable_input_fields(){
		document.forms['vorm'].display_type.disabled = false;
		document.forms['vorm'].profile_type.disabled = false;
		document.forms['vorm'].source_object.disabled = false;
		document.forms['vorm'].default_value.disabled = false;
		document.forms['vorm'].profile_map.disabled = false;
	}
//--></SCRIPT>
</head>
<body class="popup_body" onload="refresh_source_object('<?=$profile_info["type"]?>'); <?
###### initialize table field value: 1) if asset => db-type 2) else => fieldname or 'undefined'
if($profile_def['source_table'] == 'obj_asset'){?>change_profile_map_value1(document.forms['vorm'].profile_type.options[document.forms['vorm'].profile_type.options.selectedIndex].value);<?}else{?>change_profile_map_value2(document.forms['vorm'].definition_name.value);<?}?>">

<FORM action="<?=$site->self ?>" method="post" name="vorm">
	<?php create_form_token('edit-profile-field'); ?>
<?
###################
# luba profiili MUUTA ja nime muuta ainult siis kui andmeid SELLE KONKREETSE V�LJA KOHTA pole veel sisestatud:
# otsi, kas leidub selle profiiliga andmeid kus antud v�li ei ole t�hi (aga ignoreeri default pealkirja v�lja, sest see asub objekt tabelis Bug #2818)
$locked = 0;
if($site->fdat['op'] == "edit" && $site->fdat['did'] && !$profile_info["is_general"] && $site->fdat['did']<>'pealkiri') {
	$sql2 = "SELECT COUNT(*) FROM ".$profile_def['source_table']." WHERE profile_id = ? AND ";
	$sql2 .= " ".$site->fdat['did']." <>''";
#print $sql2;
	$sql2 = $site->db->prepare($sql2, $site->fdat['pid']);
	$sth2 = new SQL($sql2);
	$site->debug->msg($sth2->debug->get_msgs());
	$found_data = $sth2->fetchsingle();
	$locked = $found_data ? 1 : 0;
}
# testimiseks: $locked = 0;
?>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%; height:100%">
  <tr> 
    <td valign="top" width="100%" class="scms_dialog_area_top" height="100%"> 
      <table width="100%"  border="0" cellspacing="0" cellpadding="2">
		<?############### keel ############?>
		<tr> 
          <td bgcolor="#F1F1EC"><?=$profile_def['name']?></td>
          <td align="right" colspan="2" style="padding:3px" bgcolor="#F1F1EC"> 
            <table width="100" cellspacing="0" cellpadding="0">
              <tr> 
                <td><?=$site->sys_sona(array(sona => "keel", tyyp=>"editor"))?>:</td>
                <td style="padding-left:3px"> 
                  <select name="flt_keel" onchange="document.forms['vorm'].keel_changed.value='1';document.forms['vorm'].profile_map.disabled=false;document.forms['vorm'].submit()">
				<?	# Keeled
				//$sql = "SELECT nimi,keel_id FROM keel WHERE on_kasutusel = '1' ORDER BY nimi";
				$sql = "select distinct b.glossary_id as keel_id, a.nimi as nimi from keel as a left join keel as b on a.keel_id = b.glossary_id where b.on_kasutusel = '1'";
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());		
				while ($keel = $sth->fetch()) {
					print "	<option value=\"".$keel['keel_id']."\" ".($keel['keel_id'] == $keel_id ? "selected":"").">$keel[nimi]</option>";
					# t�lke juures peidetud keelev�lja jaoks:
					if($keel['keel_id'] == $keel_id){
						$word_keel_id = $keel['keel_id'];
					}
				} ?>	
				</select>
                </td>
              </tr>
            </table>				
		</td>
        </tr>
<?############### DUPLICATE: destination profiles selectbox  ################?>
<?	if($site->fdat['op'] == "duplicate") { ?>
<tr>
	<td nowrap><?=$site->sys_sona(array(sona => "profile", tyyp=>"editor"))?>: </td>
	<td>
	<?
#	printr($site->fdat['pid']);
	# get all profiles having the same source_table:
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		$profile_def['source_table']);
		$sth = new SQL($sql);
	?>
		<select name="dest_pid" style="width:99%" onchange="javascript: change_profile_map_value2(document.forms['vorm'].definition_name.value);">
		<? while ($tmp_profile = $sth->fetch('ASSOC')){ ?>
			<option value="<?=$tmp_profile['id']?>" <?= ($site->fdat['pid'] == $tmp_profile['id'])?"selected":""; ?>><?=$site->sys_sona(array(sona => $tmp_profile['name'], tyyp=>"custom"))?></option>
		<? } ?>
		</select>
		</td>
</tr>
<?
	} # if duplicate
?>
<?############### name ################?>
<?
	if($site->fdat['op'] == "duplicate") {
#		$profile_info["name"] = 'Copy of '.$profile_info["name"];
	}
	
?>
<tr>
	<td><?=$site->sys_sona(array(sona => "Fieldname", tyyp=>"editor"))?>: </td>
	<td width="95%" style="padding-top:4px"><?if($locked){?><?= $profile_info["name"] ?><input type=hidden name="definition_name" value="<?= $profile_info["name"] ?>"><?} else {?><input type=text name="definition_name" value="<?= $profile_info["name"] ?>" class="scms_flex_input" onchange="javascript:this.value = this.value.toLowerCase(); check_name(this.value); change_label(this.value); change_profile_map_value2(this.value);"><?}?></td>
</tr>
<?############### label/translation + hidden keele v�li #######?>
<? $label = $site->sys_sona(array(sona => $profile_info["name"], tyyp=>"custom", lang_id=>$word_keel_id));
# kui s�steemis�na puudub:
$label = $label != '['.$profile_info["name"].']' ? $label : '';
?>
<tr>
	<td><?=$site->sys_sona(array(sona => "Tolkimine", tyyp=>"admin"))?>: </td>
	<td width="95%" STYLE="padding-bottom:5px"><input type=text name="definition_label" value="<?=$label ?>" class="scms_flex_input"><input type=hidden name="word_keel_id" value="<?=$word_keel_id?>"></td>
</tr>
<?############### display_type selectbox => valitud v��rtus m�jub "db type" v�ljale (enables/disables)  ################?>
<tr>
	<td nowrap><?=$site->sys_sona(array(sona => "input type", tyyp=>"admin"))?>: </td>
	<td>
		<select name="display_type" style="width:99%" <?=($locked?'disabled':'')?> onchange="refresh_source_object(this.options[this.options.selectedIndex].value);refresh_db_type(this.options[this.options.selectedIndex].value);change_profile_map_value1(document.forms['vorm'].profile_type[document.forms['vorm'].profile_type.selectedIndex].value);">
			<option value="TEXT" <?= ($profile_info["type"] == "TEXT")?"selected":""; ?>>Text</option>
			<option value="TEXTAREA" <?= ($profile_info["type"] == "TEXTAREA")?"selected":""; ?>>Textarea</option>
			<option value="SELECT" <?= ($profile_info["type"] == "SELECT")?"selected":""; ?>>Select</option>
			<option value="MULTIPLE SELECT" <?= ($profile_info["type"] == "MULTIPLE SELECT")?"selected":""; ?>>Multiple select</option>
			<option value="BROWSE" <?= ($profile_info["type"] == "BROWSE")?"selected":""; ?>>Browse</option>
			<option value="RADIO" <?= ($profile_info["type"] == "RADIO")?"selected":""; ?>>Radio</option>
			<option value="CHECKBOX" <?= ($profile_info["type"] == "CHECKBOX")?"selected":""; ?>>Checkbox</option>
			<option value="BOOLEAN" <?= ($profile_info["type"] == "BOOLEAN")?"selected":""; ?>>Yes/No</option>
			<option value="FILE" <?= ($profile_info["type"] == "FILE")?"selected":""; ?>>File</option>
			<option value="DATE" <?= ($profile_info["type"] == "DATE")?"selected":""; ?>>Date</option>
			<option value="DATETIME" <?= ($profile_info["type"] == "DATETIME")?"selected":""; ?>>Datetime</option>
		</select>
		</td>
</tr>
<?############### db_type selectbox => valitud v��rtus m�jub "profile map" v�ljale ################?>
<tr>
	<td nowrap><?=$site->sys_sona(array(sona => "Data type", tyyp=>"admin"))?>: </td>
	<td>
	<? # selectbox is disabled when :
		# - edit existing field (you can't change type of created field. at least now)
		# - selected input type is SELECT/MULTIPLE SELECT/RADIO/CHECKBOX/FILE, then force it to varchar
	?>
		<select id="profile_type" name="profile_type" onChange="change_profile_map_value1(this.options[this.options.selectedIndex].value);"  style="width:99%" <?=($site->fdat['op'] == "edit" && $site->fdat['did']?'disabled':'')?>>
			<option value="varchar" <?= ($profile_info["db_type"] == "varchar")?"selected":""; ?>>VARCHAR(255)</option>
			<option value="integer" <?= ($profile_info["db_type"] == "integer")?"selected":""; ?>>INTEGER</option>
			<option value="float" <?= ($profile_info["db_type"] == "float")?"selected":""; ?>>FLOAT(7,2)</option>
			<option value="text" <?= ($profile_info["db_type"] == "text")?"selected":""; ?>>TEXT</option>
			<option value="date" <?= ($profile_info["db_type"] == "date")?"selected":""; ?>>DATE</option>
			<option value="datetime" <?= ($profile_info["db_type"] == "datetime")?"selected":""; ?>>DATETIME</option>
			<option value="tinyint" <?= ($profile_info["db_type"] == "tinyint")?"selected":""; ?>>TINYINT(1)</option>
		</select>
	</td>
</tr>
<?############### source_object selectbox ################?>
<?
$sql = "SELECT profile_id, name, source_table FROM object_profiles ";
$sth = new SQL($sql);
?>
<tr>
	<td nowrap><?=$site->sys_sona(array(sona => "get values from", tyyp=>"admin"))?>: </td>
	<td>
	<?
	# pane selectbox lukku uue v�lja puhul v�i kui valitud on mitte m�tet omav t��p
	if($site->fdat['op']=='newdef' ||
	($profile_info && ($profile_info["type"]=="TEXT" || $profile_info["type"]=="TEXTAREA"))  ) {
		$cms_locked = 1; 
	}
	?>
		<select id="source_object" name="source_object" style="width:99%" <?=($locked || $cms_locked?'disabled':'')?>>
		<option value=""></option>
		<?while( $asset = $sth->fetch() ){?>
			<option value="<?=$asset['profile_id']?>" <?= ($profile_info["source_object"] == $asset['profile_id'] )?"selected":""; ?>><?=$site->sys_sona(array(sona => $asset['name'], tyyp=>"custom"))?></option>
			<?php if($profile_info["source_object"] == $asset['profile_id']) $get_values_from_source_table = $asset['source_table']; ?>
		<?}?>
		</select>
	</td>
</tr>

<?############### default_value selectbox ################?>
<?
if($profile_info["source_object"] || $profile_info["type"] == "BOOLEAN") {

	if($get_values_from_source_table == 'obj_asset')
	{
		$sql = $site->db->prepare("SELECT objekt.pealkiri,obj_asset.objekt_id AS id 
						FROM objekt 
						LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id
						LEFT JOIN obj_asset on objekt.objekt_id=obj_asset.objekt_id
						WHERE objekt.tyyp_id=? AND obj_asset.profile_id=? AND objekt_objekt.parent_id<>?
						ORDER BY objekt.pealkiri ",
						"20",
						$profile_info["source_object"],
						$site->alias("trash"));
	}
	if($get_values_from_source_table == 'obj_artikkel')
	{
		$sql = $site->db->prepare("SELECT objekt.pealkiri,obj_artikkel.objekt_id AS id 
						FROM objekt 
						LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id
						LEFT JOIN obj_artikkel on objekt.objekt_id=obj_artikkel.objekt_id
						WHERE objekt.tyyp_id=? AND obj_artikkel.profile_id=? AND objekt_objekt.parent_id<>?
						ORDER BY objekt.pealkiri ",
						"2",
						$profile_info["source_object"],
						$site->alias("trash"));
	}
	elseif(strpos($get_values_from_source_table, 'ext_') === 0)
	{
		$sql = $site->db->prepare("SELECT name as pealkiri, id 
						FROM ".$get_values_from_source_table." 
						WHERE ".$get_values_from_source_table.".profile_id=? 
						ORDER BY name ",
						$profile_info["source_object"]);
	}
	$sth = new SQL($sql);
}
?>
<tr>
	<td nowrap><?=$site->sys_sona(array(sona => "default_value", tyyp=>"admin"))?>: </td>
	<td>
	<?
	if($profile_info["type"] == "BOOLEAN") { ###### YES checkbox ?>
		<input type=checkbox id="tmptmp_default_value" name="tmptmp_default_value" value="1" <?=($profile_info["default_value"]?"checked":"") ?> onclick="if(this.checked){document.getElementById('default_value').value='1';}else {document.getElementById('default_value').value='0';}">
		<input type=hidden id="default_value" name="default_value" value="<?=($profile_info["default_value"]?"1":"0")?>">

		<label for="tmptmp_default_value"><?= $site->sys_sona(array(sona => "yes", tyyp=>"editor"))?></label>
	<?	
	} elseif($profile_info["source_object"]) { ###### values selectbox
	?>
		<select id="default_value" name="default_value" style="width:99%">
		<option value=""></option>
		<?while( $data = $sth->fetch() ){?>
			<option value="<?=$data['id']?>" <?= ($profile_info["default_value"] == $data['id'] )?"selected":""; ?>><?=$data['pealkiri']?></option>
		<?}?>
		</select>
	<?  
	} else { ###### free TEXT ?>
		<input type=text id="default_value" name="default_value" class="scms_flex_input" value="<?= $profile_info["default_value"]?>">
<?	}
	?>
	</td>
</tr>

<?############### profile_map ( table fieldname in database) 
# <= v��rtust m�jutab slectboxi "db_type" valitud v��rtus ################?>
<?
# NB! v�li on userile disabled, aga muutub ise s�ltuvalt "db_type" valikust

########## generate new fieldname or use existing
# 1. existing field (edit)
if($site->fdat['did']){ # siia alla l�hevad ka predefined, neid ei saa samuti muuta
	$profile_map = $site->fdat['did'];
	if($site->fdat['op']=='duplicate'){
#		$profile_map = 'copy_of_'.$site->fdat['did'];
	}
}
# 2. if source table is 'obj_asset' then fieldanme is first available field
# 3. if source table is users / groups / obj_dokument / .. then fieldname is field name in safe format
# 4. field may also be predefined general object field from table "objekt" (is_general)

?>
<tr>
	<td nowrap><?=$site->sys_sona(array(sona => "Map to", tyyp=>"admin"))?>: </td>
	<td>
		<input type=text name="profile_map_show" value="<?= ($profile_info["is_general"]?'objekt':$profile_def['source_table']).'.'.$profile_map ?>" class="scms_flex_input" onblur="this.value = this.value.toLowerCase();">
		<input type=hidden name="profile_map" value="<?= $profile_map ?>" class="scms_flex_input" onchange="this.value = this.value.toLowerCase();">
	</td>
</tr>

<?############### is_required ################?>

        <tr> 
          <td nowrap><?=$site->sys_sona(array(sona => "on noutud", tyyp=>"editor"))?>:</td>
          <td width="95%"> 
            <input type="checkbox" name="is_required" value="1" <?=($profile_info["is_required"]?"checked":"")?>>
          </td>
        </tr>
<?############### is_active ################?>

		<tr> 
          <td nowrap><?=$site->sys_sona(array(sona => "Active", tyyp=>"admin"))?>:</td>
          <td width="95%"> 
            <input type="checkbox" name="is_active" value="1" <?=($profile_info["is_active"] || $site->fdat['op'] == "newdef"?"checked":"")?>>
          </td>
        </tr>
<?############### is_predefined ################?>
		<tr> 
          <td nowrap>Predefined:</td>
          <td width="95%"> 
            <input type="checkbox" name="is_predefined" value="1" <?=($profile_info["is_predefined"]?"checked":"")?>>
			(advanced)
          </td>
        </tr>
<?############### is_general ################?>
		<tr> 
          <td nowrap>General object field:</td>
          <td width="95%"> 
            <input type="checkbox" name="is_general" value="1" <?=($profile_info["is_general"]?"checked":"")?>>
			(advanced)
          </td>
        </tr>

		</table>
    </td>
  </tr>
	<?############ buttons #########?>
	<tr> 
	  <td align="right" valign="top" class="scms_dialog_area_bottom"> 
         <input type="button" value="<?=$site->sys_sona(array(sona => "Apply", tyyp=>"editor")) ?>" onclick="javascript:enable_input_fields(); document.forms['vorm'].op2.value='save_profile_definition';this.form.submit();">
         <input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:enable_input_fields(); document.forms['vorm'].op2.value='saveclose_profile_definition';this.form.submit();">
	   <input type="button" value="<?=$site->sys_sona(array(sona => "Close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
    </td>
  </tr>
</table>

<input type="hidden" name="op" value="<?= $site->fdat['op'] ?>">
<input type="hidden" name="op2" value="">
<input type=hidden name=keel_changed value="">
<INPUT TYPE="hidden" name="did" value="<?= $site->fdat['did'] ?>">
<INPUT TYPE="hidden" name="pid" value="<?= $site->fdat['pid'] ?>">
</form>

</body>
</html>
<?
############ debug
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 
exit;
}
# / 4. NEW/EDIT PROFILE FIELD
###############################
?>


<? 
############ debug
# user debug:
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }
$site->debug->print_msg(); 
?>
</body>
</html>