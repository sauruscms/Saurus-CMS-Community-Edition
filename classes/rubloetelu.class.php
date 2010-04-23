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


class RubLoetelu extends BaasObjekt {
	# RubLoetelu()
	# tagastab rubriikide loetelu
	# väljastada hash: rubriigi nimetus kõik parentega	
	# rub_loetelu(array(
	#	keel => 1, (optional)
	#	exclude_id => "1,13", (optional)
	#	object_type_ids = "1,19", (optional)
	#	max_headline_length = "25", (optional) // default "25"
	#	separator = " > " (optional) // default "->"
	#   required_perm => "C" (optional)
	#   ignore_perm_for_obj => "12, 34" (optional) use it with "required_perm" if you desperately need to add some objects regarding it's permissions
	#   ignore_lang => 1 (default: 0) ignore objects languages
	# ));
	var $ary;

	function RubLoetelu() {
		$this->BaasObjekt();
		if (func_num_args()>0) {
			$args = func_get_arg(0);
		}
		$this->ary = array();
		$keel = isset($args['keel']) ? $args['keel'] : $this->site->keel;

		$object_type_ids = $args['object_type_ids'] ? $args['object_type_ids'] : "1,19";
		$tmp_arr = explode(",", $object_type_ids);
		$object_type_ids_arr = array();
		foreach ($tmp_arr as $tyyp_id){
			$object_type_ids_arr[] = trim($tyyp_id);
		}
		$types_str = "'".join("','", $object_type_ids_arr)."'";

		$max_headline_length = $args['max_headline_length'] ? $args['max_headline_length'] : 25;
		$separator = $args['separator'] ? $args['separator'] : "->";
		
		
		#########################
		# show objects having required permissions for current user
		$required_perm = array();
		if($args['required_perm']) {
			$required_perm = split(",",$args['required_perm']);
		}
		# default is is_visible
		if(sizeof($required_perm) <= 0) {
			$required_perm[] = 'is_visible';
		}
		# ignore_perm_for_obj - use it only with "required_perm" if you desperately need to add some objects 
		# regarding it's permissions (Bug #1988)
		$ignore_perm_for_obj = array();
		if($args['ignore_perm_for_obj']) {
			$ignore_perm_for_obj = split(",",$args['ignore_perm_for_obj']);
		}

		#########################
		# get all sections with their parent info

		## optimization: removed "SELECT A.*" as very greedy select
		$sql = $this->site->db->prepare("
			SELECT A.objekt_id, A.on_avaldatud, A.tyyp_id, A.pealkiri, A.sys_alias, A.friendly_url, objekt_objekt.parent_id, B.tyyp_id as parenttyyp, B.pealkiri as parentname 
			FROM objekt as A 
			LEFT JOIN objekt_objekt on A.objekt_id=objekt_objekt.objekt_id
			LEFT JOIN objekt as B ON B.objekt_id=objekt_objekt.parent_id 
			WHERE A.tyyp_id IN(".$types_str.")"
		);
		if(!$args['ignore_lang']) { # created for Bug #1996
			$sql .= $this->site->db->prepare("AND A.keel=? ", $keel);
		}

		$sth = new SQL ($sql);
		$this->debug->msg($sth->debug->get_msgs());

		#########################
		# loop over sections / Product Category / Folder
		while ($rubriigid = $sth->fetch()) {
			$is_access = 0;
			if ($rubriigid['parenttyyp']=='' || in_array($rubriigid['parenttyyp'],$object_type_ids_arr) ) {
				$key = $rubriigid['objekt_id'];

				####### check permissions
				$perm = get_obj_permission(array(
					"objekt_id" => $rubriigid['objekt_id'],
					"on_avaldatud" => $rubriigid['on_avaldatud'],
					"tyyp_id" => $rubriigid['tyyp_id'],
					"parent_id" => $rubriigid['parent_id'],
				));
				# kas useril on vajalik õigus selle objekti kohta olemas
				foreach($required_perm as $req_perm) {
					if( $perm[$req_perm] ) { # hm, kas ei peaks mitte olema lisaks: && $perm['is_visible'] ?
						$is_access = 1; 
					}
					if( is_array($ignore_perm_for_obj) && in_array($rubriigid['objekt_id'],$ignore_perm_for_obj) ){
						$is_access = 1; 
					}
					#print("<hr>	".$key." = ".$rubriigid[pealkiri]." ! ".$req_perm." ! :: access = ".$is_access);

				}
				#print("<br>	".$key." = ".$rubriigid[pealkiri]." :: access = ".$is_access);
	
				#################
				# debug
				$this->debug->msg($key." = ".$rubriigid[pealkiri]." :: access = ".$is_access);

					#################
					# make section names array
					$names[$key] = $rubriigid['pealkiri'];
					# strip name longer than 25 char
					if (strlen($names[$key])>$max_headline_length) {
						$names[$key] = substr($names[$key],0,$max_headline_length)."...";
					}

				#################
				# if privileges are OK, go on
				if( $is_access ) {
					#################
					# make parents array
					$parents[$key]=$rubriigid['parent_id'];

					## for extra, save sys_alias array: id => sys_alias
					if(trim($rubriigid['sys_alias'])){
						$this->sys_alias_arr[$key] = $rubriigid['sys_alias'];
					}

					## for extra, save alias array: id => alias
					if(trim($rubriigid['friendly_url'])){
						$this->alias_arr[$key] = $rubriigid['friendly_url'];
					}

				} # if privileges are OK

				### save all parents to separate array (Bug #1650)
				$all_parents[$key]=$rubriigid['parent_id'];

			} # if parenttyyp
		}
		# / loop over sections
		#########################

		$this->debug->msg("Start looping over allowed sections");

		#################
		# loop over parents array
		if (is_array($parents)) {

		foreach (array_keys($parents) as $key) {
			$this->debug->msg("key: $key");
			$path="";
			$parent=$key;
			$debug_parent="";
			$loop = 0;
			do {
				$debug_parent .= "$parent...";
				# exclude IDs from path when needed
				if(!in_array($parent, split(",",$args['exclude_id']))){
					$path = $names[$parent].($parent!=$key ? $separator:"").$path;
				}
				$parent = $all_parents[$parent];
				
				$loop++;
				if($loop > 100)
				{
					new Log(array(
						'type' => 'ERROR',
						'message' => 'Neverending loop! ID: '.$parent,
						'user_id' => 0,
					));
					exit;
				}
			} while ($parent);

			## save main array: id => name path
			$topparents[$key]=$path;

			$this->debug->msg("$debug_parent :: $path");

		}
		}
		# / loop over parents array
		#################

		$this->ary = $topparents;
	}

	function get_loetelu() {
		return $this->ary;
	}

	function get_branch_byID() {
		# võtab argumendiks saadetud ID ja väljastab ainult selle alluvad (ID ise sh)
		$args = func_get_arg(0);
		$result = array();
		if($args['id']) {
			$tree = array();
			$tree = $this->ary;
			$parent = $args['id'];
			asort($tree);
			foreach ($tree as $key=>$value) {
				$gencount = substr_count($value, "->");

				# if we are got out from own subtree 
				if($is_own_subtree && ($gencount <= $mycount )){
					$is_own_subtree = 0;
				}
				# if is current section then go to the own subtree
				if($key == $parent) {
					$is_own_subtree = 1;
					$mycount = substr_count($value, "->");
				}
				if($is_own_subtree) {			
					$result[$key] = $tree[$key];
				} 
			}# foreach

			return $result;
		} # if argument
	} # function get_branch_byID

	# return array: $arr[id] = sys_alias
	function get_sys_alias_arr() {
		return $this->sys_alias_arr;
	}

	# return array: $arr[id] = alias
	function get_alias_arr() {
		return $this->alias_arr;
	}


} # class
