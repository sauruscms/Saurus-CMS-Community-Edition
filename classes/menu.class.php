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
 * Menu tree functions
 * 
 */


/**
 * Class for creating and printing menu tree 
 * 
 * 
 * 
 * @package CMS
 * 
 * @param array tree 
 * @param string datatype   optional, for custom operations, shows which data we are handling: group/profile
 * @param string param_name   parameter name passed to href
 * @param string params   all additional parameters, will be passed to href
 * @param string tree_title - optional, first row headline
 * @param string tree_icon - optional, will be placed before first row headline
 * @param bool selectform - 1/0, if selectform submit or usual href
 *
 * constructor new Menu(array(
 *   tree => $data_array,
 *	[datatype => "group"] # 
 *   [param_name => "group_id"] # .. err.. should deprecate it soon... 
 *	[tree_title => $site->sys_sona(array(sona => "groups", tyyp=>"admin"))]
 *	[expand_all  => 0/1] default : 0 // expand all branches in the tree
 *	[show_checkboxes  => 0/1]  default: 0 // shows checkboxes near sections
 *
 * ));
 * 
 */
class Menu extends BaasObjekt {

var $parents;
var $max_tase;
var $tree;
var $parent_object;
var $object_parent;
var $path_arr; // array of parent headlines
	####################
	# MENU
	function Menu () {
		$args = func_get_arg(0);
		$this->BaasObjekt($args);
		$this->args = &$args;

		$this->maximum_allowed = 20; // protect from infinite cycles
		$this->group_id = $this->site->fdat['group_id']+1-1; // to numeric format
		if (!is_array($args['tree']))$args['tree'] = array();

		$this->with_checkboxes = $args['with_checkboxes'];

		###########
		# PERMISSIONS CHECK - get group permissions for current user
		if($args['datatype']=='group'){
			if ($this->site->user->user_id && !isset($this->site->user->aclpermissions)) { 
				$this->site->user->aclpermissions = $this->site->user->load_aclpermissions();
			} 
			elseif($this->site->guest && !isset($this->site->guest->aclpermissions)) { 
				$this->site->guest->aclpermissions = $this->site->guest->load_aclpermissions();
			}
			$aclpermissions = ($this->site->user->user_id ? $this->site->user->aclpermissions : $this->site->guest->aclpermissions);
			#echo printr($aclpermissions);

			# save read-allowed groups ID-s
			$read_allowed_groups = array();
			foreach($aclpermissions as $perm_group_id => $perm){
				if(is_array($perm) && $perm['R']){ $read_allowed_groups[] = $perm_group_id; }
			}
			#echo printr($read_allowed_groups);
		}

		######## pass_href
		if (is_array($this->site->fdat)){
			foreach ($this->site->fdat as $key => $val){
				$this->pass_href .= "&".$key."=".$val;
			}
		}
		# array of all leafs in open tree
		$this->parents_arr = array();
		# array of all IDs under the ID
		$this->subtree_arr = array();

		$i=0;
		$this->tree[0]['id'] = 0;
		$this->tree[0]['parent'] = '';
		$this->tree[0]['name'] = $args['tree_title'];

		##############
		# TREE
			foreach ($args['tree'] as $key => $val){
				$this->parent_object[$val['parent']] = $val['id'];
				$this->object_parent[$val['id']] = $val['parent'];
				#	echo "TEST:".$val['id'].":p ".$val['parent']."<br>";

				$this->subtree_arr[$val['parent']][] = $val['id'];

				if (!$first[$val['parent']]) {
					$this->subtrees[$val['parent']] = $val['id'];
					$first[$val['parent']] = 1;
				}
				# permissions check: don't add group data to tree if leaf(group) is not readable
				if($args['datatype']=='group' && !$this->site->user->is_superuser && !in_array($val['id'],$read_allowed_groups)){ continue; }

				$this->tree[$val['id']]['id'] = &$args['tree'][$key]['id'];
				$this->tree[$val['id']]['parent'] = &$args['tree'][$key]['parent'];
				$this->tree[$val['id']]['name'] = &$args['tree'][$key]['name'];
			}
		# / TREE
		##############

			########## CHILDREN

			# if group menu then get group children (sql in function is different)
			if($args['datatype']=='group'){
				$this->find_group_childrens();
			}
			# if profile menu then get source table children (sql in function is different)
			if($args['datatype']=='profile'){
				$this->find_profile_childrens();
			}

			########## PARENTS
			$this->find_parents($this->group_id, 0);

			########## MAXTASE: on vaja teada, kui sügav on Menu
			$this->max_tase = count($this->parents_arr);

#				$this->add('
#				<table width="100%" height="100%"  border="0" cellpadding="0" cellspacing="0">');

				########### FAVOURITES row
#				$this->print_favourites();

			$obj = $this->tree[0];

			#####################
			# print 1. row for profile tree, it's type name
			if($args['datatype']=='profile'){
				$this->title = '
						<tr>
							<td '.($args['no_separator']?'':'class="scms_groupheader"').'>';
				if($args['table']) $this->title .='<a href="?source_table='.$args['table'].'">'.($this->site->fdat['source_table']==$args['table']?'<span class="scms_selected">':'');

				$this->title .= '<IMG SRC="'.$this->args['tree_icon'].'" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>&nbsp;&nbsp;';
				$this->title .= ($this->args['tree_title'] ? $this->args['tree_title'] : $obj['name']);

				if($args['table']) $this->title .= ($this->site->fdat['source_table']==$args['table']?'</span>':'').'</a>';
				$this->title .= '
							</td>
						</tr>';

			} # profile tree title

			############# SET TITLE ROW 
			else {
				$this->title = '
						<tr>
							<td '.($args['no_separator']?'':'class="scms_groupheader"').'>
									<a href="'.$href.'" '.$js_event.'><IMG SRC="'.$this->args['tree_icon'].'" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle></a>&nbsp;&nbsp;<a href="'.$href.'">'.$this->args['tree_title'].'</a>
							</td>
						</tr>
				';
			}

			$tase = 0;
		
			######## PARAMS: set parameter name: will be in "href" link
			if($args['datatype']=='group') { $param_name = "group_id"; }
			if($args['datatype']=='profile') { $param_name = "profile_id"; }			
			if($args['datatype']=='extension') { $param_name = "extension_id"; }			
			if(!$param_name) { $param_name = $args['param_name']; }

			########################
			# start making menu
				$this->make_tase(array(
					parent => $obj,
					tase => ++$tase,
					param_name => $param_name,
				));
#			$this->add('</td>
#					</tr>			
#				</table>');

	} # constructor
	# / MENU
	####################

	####################
	# FUNCTION make_tase - recursive func

	function make_tase() {
		global $site;

		$args = func_get_arg(0);
		if ($args['tase']>10){ # prevent infinite loop
			return 0;
		}
		# kui tase on suurem kui lubatud max-väärtus
		# siis väljuda
		if ($args['tase']>=$this->maximum_allowed) {
			#$this->debug->msg("Jõuti max lubatud tasemeni (".$args['tase']."). Lõpetan menüü tegemise.");
			return 0;
		}
		if(!($this->args['datatype'] == 'group' && $args['tase'] == 1)){ # special case, first is everybody
			$this->add('<ul  class="scms_tree_menu">');
		}
		########### TREE DATA
		reset($this->tree);
		$list = array();
		foreach ($this->tree as $key => $val){
			if ($val['parent']==$args['parent']['id']){
				$list[] = &$this->tree[$key];
			}				
		}
			$list_on_viimane = $list[count($list)-1]['id'];
	####################
	# loopover folders
	foreach ($list as $obj) {

			$is_selected = in_array($obj['id'], $this->parents_arr);

			if ($this->args['expand_all']){
				$is_selected = 1;
			}

			if (in_array($obj['id'], $this->parents_arr)){
				$is_in_current = 1;
				# teeme kus-ma-olen riba:
				if ($obj['id']) {$this->path_arr[] = $obj['name'];}
			}
		####################
		# print folder row 
		# ara prindi obj_id=0 :
		if ($obj['id']){
			########## GET HREF value
			if($this->args['selectform']) {
				$href = "javascript:select_group('".$obj['id']."')";
			}
			# default case:
			else {
				$href = '?'.$args['param_name'].'='.$obj['id'].$this->args['params'];
			}

			########## STYLE:
			$span_class = ""; 
			$span_end = ""; 
			# selected group or profile 
			if($obj['id'] == $this->site->fdat[$args['param_name']]) { 
				# class with subtree and without it:	
				$class = $this->subtrees[$obj['id']] ?  "scms_minus" : "scms_plain";
				$span_class = '<span class="scms_selected">'; 
				$span_end = '</span>';
				# for public usage  - to get group name without loading all group info
				$this->sel_group_name = $obj['name'];
			}
			# if subtree exist & leaf IS selected in tree
			elseif($this->subtrees[$obj['id']] && $is_selected) { $class = "scms_minus"; }
			# if subtree exist & leaf IS NOT selected in tree
			elseif($this->subtrees[$obj['id']] && !$is_selected) { $class = "scms_plus"; }
			# default: open subtree or last leaf
			else { 	$class = "scms_plain"; }

			######### JS EVENT
			if($this->args['datatype'] == 'group'){
				$js_event = "ondblclick=\"javascript:void(openpopup('".$this->site->CONF['wwwroot'].$this->site->CONF['adm_path']."/edit_group.php?group_id=".$obj['id']."&tab=group&op=edit','group','366','450'))\"";
			}
			############# PRINT EVERYBODY
			# special case: if first row of groups (everybody group)
			if($this->args['datatype'] == 'group' && $args['tase'] == 1){
				$this->title = '
						<tr>
							<td '.($args['no_separator']?'':'class="scms_groupheader"').'>
									<a href="'.$href.'" '.$js_event.'><IMG SRC="'.$this->args['tree_icon'].'" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle></a>&nbsp;&nbsp;<a href="'.$href.'">'.$site->sys_sona(array(sona => "Groups", tyyp=>"kasutaja")).'</a>
							</td>
						</tr>
				';
			}
			############# PRINT FOLDER
			else {

#				$this->add($args['tase']);
			$this->add('<li class="'.$class.'">');
		

			# Show cheboxes, if need:
			if ($this->args['show_checkboxes']){
				$checkbox = '<input type=checkbox onClick="sel_box_group('.$obj['id'].', this)" name="selgroup_'.$obj['id'].'" id="selgroup_'.$obj['id'].'">';
			} else {
				$checkbox = '';
			}			

			########## PRINT link & name:

			$this->add('<a href="'.$href.'" '.$js_event.'>'.$span_class.$checkbox.$obj['name'].$span_end.'</a>');

			$this->add('</li>');

			########## print checkboxes if required (for selecting groups):
			if($this->with_checkboxes) {
#				$this->add('<td width="16"><input type="checkbox" name="select_group" value="'.$obj['id'].'"></td>');
			}

			} # usual case

		}
		# / print row
		###################

			$viimane_tasemel = &$args['viimane_tasemel'];
			$viimane_tasemel[$args['tase']] = ($obj['id']==$list_on_viimane ? 1:0);

			######## Recursive step
			if ($is_selected && $obj['id']) {
				$this->make_tase(array(
					parent => $obj,
					viimane_tasemel => &$viimane_tasemel,					
					tase => $args['tase']+1,
					param_name => $args['param_name']				
				));
			}
			$y++;
	} # while alamlist
	# / loopover folders
	####################

		if(!($this->args['datatype'] == 'group' && $args['tase'] == 1)){ # special case, first is everybody
			$this->add('</ul>');
		}

	}
	# / FUNCTION make_tase
	####################


	####################
	# FUNCTION find_group_childrens

		# NB! is datatype specific function; 
		# returns group_id after jumping:
		function find_group_childrens($z=0){ # private function

			$z++;
			if (!( $z>50)){
				if (!$this->subtrees[$this->group_id]){
					return $this->group_id;
				}
				
				$sql = $this->site->db->prepare("SELECT count(*) FROM groups  WHERE parent_group_id=? ", $this->group_id);
				$sth = new SQL($sql);
				$has_childrens = $sth->fetchsingle();

				if ($has_childrens){
					return $this->group_id;
				} else {
					$this->group_id = $this->subtrees[$this->group_id];
					$this->find_group_childrens($z);
				}
			}
		} # function

	####################
	# FUNCTION find_profile_childrens

		# NB! is datatype specific function; 
		# returns profile_id after jumping:
		function find_profile_childrens($z=0){ # private function

			$z++;
			if (!( $z>50)){

				if (!$this->subtrees[$this->profile_id]){
					return $this->profile_id;
				}
				
				$sql = $this->site->db->prepare("SELECT count(*) FROM object_profiles  WHERE source_table=? ", $this->profile_id);
				$sth = new SQL($sql);
				$has_childrens = $sth->fetchsingle();

				if ($has_childrens){
					return $this->profile_id;
				} else {
					$this->profile_id = $this->subtrees[$this->profile_id];
					$this->find_profile_childrens($z);
				}
			}
		} # function


	####################
	# FUNCTION find_parents

		# retuns array of parents:
		function find_parents($id, $i){
			if (!$i){
				$this->parents_arr[]=$id;
			}
			$i++;
			if ($i>50) {
				#echo "cycle!"; 
				return 0;
			}
			
			if ($this->object_parent[$id]){				
				$this->parents_arr[] = $this->object_parent[$id];
				$this->find_parents($this->object_parent[$id], $i);
			} else {
				$this->parents_arr[] = 0;
				return 0;
			}		
		}


	####################
	# FUNCTION add - adding html to source variable
	function add($html) {

		$this->source .= $html;
	}

	####################
	# FUNCTION get_full_subtree

	# public to get ALL ID-s recursively under given ID
	function get_full_subtree() {
		$args = func_get_arg(0);
		
		$id = $args[parent_id];
		if($id){
			$this->full_subtree[] = $id;
			# if children exist:
			if(sizeof($this->subtree_arr[$id]) > 0) {
				foreach($this->subtree_arr[$id] as $key=>$value) {
					# recursive step:
					$subtree = $this->get_full_subtree(array("parent_id" => $value));
				}
			}
		} # parameter not null

		return $full_subtree;
	}

	####################
	# FUNCTION print_favourites

	function print_favorites() {

		$this->add('
					<tr>
						<td class="scms_groupheader">
								<a href="#"><IMG SRC="'.$this->site->CONF['wwwroot'].$this->site->CONF[styles_path].'/gfx/icons/16x16/actions/bookmark.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle></a>&nbsp;&nbsp;<a href="#">Lemmikud</a>
						</td>
					</tr>
					<tr>
						<td valign=top>
								<!-- Favorites -->
								<TABLE width="100%" border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td>									
											
										<ul class="scms_tree_menu">
											<li class="scms_plain"><a href="#">Teine Tase</a></li>
											<li class="scms_plain"><a href="#">Teine Tase</a></li>
										</ul>

										
										</td>
									</tr>
								</TABLE>
								<!-- //Favorites -->
						</td>
					</tr>
			');	
	}
	# / FUNCTION print_favourites
	####################

}
