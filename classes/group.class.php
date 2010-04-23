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
 * Group handling functions
 * 
 */


/**
 * Group class
 * 
 * Only read data functions, not saving
 * 
 * @param int $group_id 
 *
 * constructor new Group(array(
 *	group_id => ID,
 * ));
 * 
 */
class Group extends BaasObjekt {


	function Group () {
		$args = func_get_arg(0);
		$this->BaasObjekt($args);

		$this->group_id = $args['group_id'];
		
		# get all general info about group:
  		$sql = $this->site->db->prepare("SELECT * FROM groups WHERE group_id=?",
			$this->group_id
		);
		$sth = new SQL($sql);
		# if group found
		if ($sth->rows) {
			$this->all = $sth->fetch('ASSOC');

			# common properties:
			$this->group_id = $this->all['group_id'];
			$this->id = $this->all['group_id'];

			$this->parent_group_id = $this->all['parent_group_id'];
			$this->parent_id = $this->all['parent_group_id'];

			$this->name = $this->all['name'];
			$this->description = $this->all['description'];

		}
		# if group not found
		else {
			$this->group_id = 0;
			return 0;
		}

	} # constructor

	# public
	function get_members_count(){

  		$sql = $this->site->db->prepare("SELECT COUNT(*) FROM users WHERE group_id=?",$this->group_id);
		$sth = new SQL($sql);
		$members_count = $sth->fetchsingle();
		return $members_count;
	}
	# public
	function get_subgroups_count(){

  		$sql = $this->site->db->prepare("SELECT COUNT(*) FROM groups WHERE parent_group_id=?",$this->group_id);
		$sth = new SQL($sql);
		$subgroups_count = $sth->fetchsingle();
		return $subgroups_count;
	}
	# public
	# returns all same level groups ID-s
	function get_cogroups(){

  		$sql = $this->site->db->prepare("SELECT group_id FROM groups WHERE parent_group_id=? ORDER BY name",$this->parent_group_id);
		$sth = new SQL($sql);
		while($group = $sth->fetch()) {
			$cogroups[] = $group['group_id'];			
		}
		return $cogroups;
	}


	# public
	function get_members(){

  		$sql = $this->site->db->prepare("SELECT * FROM users WHERE group_id=?",$this->group_id);
		$sth = new SQL($sql);
		$members = array();
		while($user = $sth->fetch('ASSOC')){
			$members[] = $user;
		}

		return $members;
	}


	function load_additional_info(){

	}

}
# / class group
####################

####################
# Standalone and public group-related functions 

/**
* grouptree (public)
* 
* returns array of parent groups starting from lowest group (Everybody) and moving to higher,
* last element is a direct parent group
* if parameter group ID is 0 then return only everybody group
* 
* @package CMS
* 
* @param int $group_id 
*
* $grouptree = get_grouptree(array("group_id" => $this->group_id));
*/
function get_grouptree(){

	$args = func_get_arg(0);

  	$sql = "SELECT group_id AS id, parent_group_id AS parent, name FROM groups ORDER BY name";
	$sth = new SQL($sql);
	while ($data = $sth->fetch()){
		$temp_tree[] = $data;		
	}
	$grouptree = get_array_branch($temp_tree, $args['group_id']);

	# if no tree genreated then get evrybody group
	if(!is_array($grouptree)) {
	  	$sql = "SELECT group_id AS id, parent_group_id AS parent, name FROM groups WHERE is_predefined='1'";
		$sth = new SQL($sql);
		$grouptree[] = $sth->fetch('ASSOC');
	}
	$grouptree = array_reverse($grouptree);

	return $grouptree;

}


function get_topparent_group($args){
	#global $site;

	# if called in the middle of site class,
	# then current site instance is passed as parameter, otherwise usual way is used
/*	
	if ($mysite){
		$site = &$mysite;
	}
*/
	$site = &$args['site'];

	# find parent group, by defult "ID=1 - Everybody"
	$sql = $site->db->prepare("SELECT group_id FROM groups WHERE parent_group_id=0");
	$sth = new SQL($sql);
	$topgroup_id = $sth->fetchsingle();
	return $topgroup_id;
}



/**
* get_groupleafs (public)
* 
* returns array of child groups recursively starting from given group, 
* array includes also given group (by parameter) itself
* 
* @package CMS
* 
* @param int $group_id 
*
* $groupleafs = get_groupleafs(array("group_id" => $this->group_id));
*/
function get_groupleafs(){

	global $current_level;


	$args = func_get_arg(0);

  	$sql = "SELECT group_id AS id, parent_group_id AS parent, name FROM groups ORDER BY name";
	$sth = new SQL($sql);
	while ($data = $sth->fetch('ASSOC')){
		$temp_tree[] = $data;		
		# remember current group
		if($data['id'] == $args['group_id']){
			$current_group = $data;
		}
	}

	$current_level = 1; # set NULL global variable

	$groupleafs = get_array_leafs($temp_tree, $args['group_id']);
	# include also curretn group (by parameter) to array:
	$groupleafs[] = $current_group;
#echo printr($groupleafs);

	return $groupleafs;
}
