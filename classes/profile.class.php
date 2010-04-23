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
 * profile handling functions
 * 
 */


/**
 * Profile class
 * 
 * All more specific and time-consuming profile handling functions - set object profile fields etc
 * profile class is NOT always included, for general purposes use $site->get_profile() function.
 * 
 * @package CMS
 * 
 * @param int $profile_id 
 *
 * $profile = new Profile(array(
 *	id => profile ID,
 *	[name => profile name]
 * ));
 * 
 */
class profile extends BaasObjekt {

	# NB! keep constructor small and use public functions to get additional info for profile,
	# mimimum profile instance will return only profile data from table 'object_profiles', nothing else

	function profile () {

		global $site;

		$args = func_get_arg(0);
		$this->BaasObjekt($args);
		
		$this->args = $args;
		$this->site = &$site;

		# 1. profile_id as parameter
		if($args['id']) {

			$profile = $site->get_profile(array(
				"id" => $args['id']
			));
		}
		# 2. profile name as parameter 
		elseif($args['name']) {

			$profile = $site->get_profile(array(
				"name" => $args['name']
			));		
		}		
#		printr($profile);

		$this->id = $this->profile_id = $profile['profile_id'];
		$this->name = $profile['name'];
		$this->source_table = $profile['source_table'];
		$this->data = unserialize($profile['data']);

	
	} # constructor profile
	###################


	/**
	* set_obj_general_fields (private)
	* 
	* set profile fields as object attributes,
	* poiner to object id passed as parameter, object properties will be changed, nothing returned
	*
	* 
	* @package CMS
	* 
	* set_obj_general_fields(array(
	*	"obj" => &$obj,
	*	"get_object_fields" => $get_object_fields
	* ));
	*/
	function set_obj_general_fields(){

		$args = func_get_arg(0);
		$site = &$this->site;
		
		$obj = &$args['obj']; ## pointer to object instance
		$get_object_fields = $args['get_object_fields']; ## com.sep.list of field names to include
		$get_object_fields_arr = split(",",$get_object_fields);
		$i=0;foreach($get_object_fields_arr as $tmp){ $get_object_fields_arr[$i] = trim($tmp); $i++; }

		$this->selectlist = array(); # save selectlists for later, array of field values
		$this->change_fields = array(); # save select-type fields for later, array of field names
		$profile_fields = array(); # for using in SELECT fields assigning

			
			###############
			# ATTRIBUTES
			# 1. set profile fields as attributes
			if($profile_def = $this->data) {
				$profile_data = Array();
				foreach($profile_def as $key => $data) {
#printr($data);
					####################
					# if FIELDS FILTER parameter is set, then use it
					if( !$get_object_fields || (sizeof($get_object_fields_arr)>0 && in_array($data[name],$get_object_fields_arr)) ) {

						##############
						# format dates and datetimes:
						$temp_dat=array(); //this is to fix bug #1927
						if($data['type'] == 'DATE') {
							if($obj->all[$key]) {$temp_dat[$key] = $site->db->MySQL_ee($obj->all[$key]);}

						} else if ($data['type'] == 'DATETIME') {
							if($obj->all[$key]) {$temp_dat[$key] = $site->db->MySQL_ee_long($obj->all[$key]);}
						}
						$profile_data[$data[name]] = $temp_dat[$key];
						##############
						# set all profile attributes as properties 
						$obj->{$data[name]} = $temp_dat[$key];
						# profile attribute is case insensitive:
						$obj->{strtolower($data[name])} = $temp_dat[$key];

						##############
						# save selectlists for later
						# here values are asset object ID-s
						if($data['source_object']) { #SELECT/MULTIPLE SELECT/RADIO/CHECKBOX/BROWSE..
							# add only if value is not empty
							if($obj->all[$key]) {
								# value can be comma-separated list of ID-s, split it
								$values = split(",",$obj->all[$key]);
								foreach($values as $value){
									$this->selectlist[] = $value;
								}
								# remember field: values must be changed later
								$this->change_fields[] = $data[name];
							} # if value not epmty

							# set all profile fields as array (for excluding them in assigning SELECT fields, otherwise we may overwrite found values)
							$profile_fields[] = strtolower($data[name]);
						
						} # if select

					} # FILTER
				}
			}
			# set all profile attributes and values as one array:
			$obj->profile_data = $profile_data;
			#####################
			# 2. set SELECT fields as attributes
			foreach($obj->all as $real_field => $real_value) {
				# assign only if not in the profile array AND
				# if GET_OBJECT_FIELDS FILTER parameter is set, then use it
				if(!in_array($real_field,$profile_fields) &&
					(!$get_object_fields || (sizeof($get_object_fields_arr)>0 && in_array($data[name],$get_object_fields_arr)))	
				) {

					/* bug #2230 */
					$obj->all[$real_field] = stripslashes($obj->all[$real_field]);
					$obj->{$real_field} = $obj->all[$real_field];
				}
			} # loop over ALL possible fields
			# /2. set SELECT fields as attributes
			#####################

			# / ATTRIBUTES
			##############


		###################
		# get selectlist values - 1 extra sql per function; sql is fast

		if(is_array($this->selectlist)) {
			$this->selectlist = array_unique($this->selectlist);
		}
		#printr($this->selectlist);

	} 
	# / FUNCTION set_obj_general_fields
	#####################

	/**
	* get_asset_names (private)
	* 
	* get asset names for selectlist values
	* Must be 1 (or 2, if system tables involved) extra SQL per SAPI tag function; sql is fast
	* Saves an array $this->asset_names[ID]	= NAME
	* 
	* @package CMS
	* 
	* $obj_profile->get_asset_names(array(
	*		"selectlist" => $all_selectlist
	* ));
	*/
	function get_asset_names(){

		$args = func_get_arg(0);
		$site = &$this->site;

		$selectlist = $args["selectlist"]; # array of ID-s which need additional steps to convert ID-s to human readable NAME-s (assets or users/groups)

		$this->asset_names = array(); ## values we are seacrhing for and saving

		if(sizeof($selectlist)>0){ # if found any ID

			###############
			# loop over selectlist ID-s and define their data source
			$selectlist_asset = array(); # obj_asset.objekt_id array
			$selectlist_user = array(); # users.user_id array
			$selectlist_group = array(); # groups.group_id array
			foreach($selectlist as $sel_id) {
				# 1) we have user ID or group ID (if type=browse and data source is system table "users" or "groups")
				# value is in format like 'user_id:65'
				if(substr($sel_id,0,strlen('user_id:')) == 'user_id:') {
					$selectlist_user[] = substr($sel_id,strlen('user_id:'));
				}
				# value is in format like 'group_id:20'
				elseif(substr($sel_id,0,strlen('group_id:')) == 'group_id:') {
					$selectlist_group[] = substr($sel_id,strlen('group_id:'));
				}
				# 2) we have asset ID
				else {
					$selectlist_asset[] = $sel_id; 
				}
			}
			#echo printr($selectlist_asset);
			#echo printr($selectlist_user);
			#echo printr($selectlist_group);

			###############
			# get names SQL

			# 1) asset objects
			if (sizeof($selectlist_asset)>0) {
				$sql = $site->db->prepare("SELECT objekt.pealkiri,objekt.objekt_id FROM objekt WHERE objekt.objekt_id IN('".join("','",$selectlist_asset)."')");
				$sth = new SQL ($sql);	
				while($tmp_names = $sth->fetch()) {
					$this->asset_names[$tmp_names[objekt_id]] = $tmp_names[pealkiri];
				}
			} # 1) asset objects

			# 2) users
			if (sizeof($selectlist_user)>0) {
				$sql = $site->db->prepare("SELECT CONCAT(users.firstname,' ',users.lastname) AS pealkiri, users.user_id AS objekt_id FROM users WHERE users.user_id IN('".join("','",$selectlist_user)."')");
				$sth = new SQL ($sql);	
				while($tmp_names = $sth->fetch()) {
					$this->asset_names['user_id:'.$tmp_names[objekt_id]] = $tmp_names[pealkiri];
				}
			} # 2) users

			# 3) groups
			if (sizeof($selectlist_group)>0) {
				$sql = $site->db->prepare("SELECT groups.name AS pealkiri, groups.group_id AS objekt_id  FROM groups WHERE groups.group_id IN('".join("','",$selectlist_group)."')" );
				$sth = new SQL ($sql);	
				while($tmp_names = $sth->fetch()) {
					$this->asset_names['group_id:'.$tmp_names[objekt_id]] = $tmp_names[pealkiri];
				}
			} # 3) groups

			# / get names SQL
			###############

		### array $this->asset_names contains all values

		} # if found any ID

	} 
	# / FUNCTION get_asset_names
	#####################


	/**
	* set_obj_selectlist_fields (private)
	* 
	* set selectlist profile fields as object attributes, use $this->asset_names array or values.
	* poiner to object id passed as parameter, object properties will be changed, nothing returned
	*
	* 
	* @package CMS
	* 
	* set_obj_selectlist_fields(array(
	*	"obj" => &$obj,
	*	"change_fields" => $obj_profile->change_fields
	* ));
	*/
	function set_obj_selectlist_fields(){

		$args = func_get_arg(0);
		$site = &$this->site;
		
		$obj = &$args['obj']; ## pointer to object instance
		$change_fields = $args["change_fields"]; ## array of fields, needed to chenge

		if(sizeof($obj->profile_data) > 0) {
			############
			# loop over attributes
			foreach($obj->profile_data as $key=>$value) {
				$value = $obj->all[$key];
				#print "<br>-  ".$key." = ".$value. " => ".(in_array($key,$change_fields)? 'YES':'NO');
				# if profile def name matches (change only correct attributes):
				if(in_array($key,$change_fields)) {
					# change attribute from asset ID => asset NAME
					# value can be comma-separated list of ID-s, split it
					$ids = split(",",$obj->all[$key]);
					$new_value = $obj->all[$key];
					foreach($ids as $id){
						$new_value = str_replace($id,$this->asset_names[$id],$new_value);
					}
					# pane komade taha tühikud:
					$new_value = str_replace(",",", ",$new_value);

					# change values
					$obj->{$key} = $new_value;
					$obj->{strtolower($key)} = $new_value;
					$obj->profile_data[$key] = $new_value;
					#print "<br>-  ".$key." = ".$value. " => ".$new_value;
				}
			}
			# / loop over attributes
			############
		} # if exist profile fields

	} 
	# / FUNCTION set_obj_selectlist_fields
	#####################


} 
# / class profile
###################
