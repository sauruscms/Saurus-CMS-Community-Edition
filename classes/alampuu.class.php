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
 * Subtree class (Alampuu in Estonian) 
 * 
 * Creates array of all object ID-s under the given parent(s) object(s).
 * Saves result into property "objects".
 * Does all the permissions check for current user.
 * 
 * @param int $parent_id 
 * @param string $tyyp_idlist 
 *
 *	$puu = new Alampuu(array(
 *		parent_id => $otsingu_juur,
 *		[tyyp_idlist => "1,2"],
 *		[skip_permissions_check => 1]
 *	));
 */
class Alampuu extends BaasObjekt {
# antud objekti alampuu

	var $size;
	var $parent_id;
	var $objektid;
	var $tyyp_idlist;

	function Alampuu () {
		$args = func_get_arg(0);
		$this->BaasObjekt();
			
		$this->parent_id = $args['parent_id'];
		$this->tyyp_idlist = $args['tyyp_idlist'];
		$this->skip_permissions_check = $args['skip_permissions_check'];
		
		#$on_admin = $args['on_admin']; # parameter on_admin was in ver3-s passed as "$site->admin"
		# in ver4 it is deprecated and replaced with "skip_permissions_check":
		$on_admin = $this->skip_permissions_check;

		$this->objects = array(); # main result

		if ($this->tyyp_idlist) {
			$this->tyyp_idlist_arr = split(",",$this->tyyp_idlist);
			$wheretyyp = $this->site->db->prepare(" AND tyyp_id IN('".join("','",$this->tyyp_idlist_arr)."') ");
		}

/******* DEPRECATED: old ver 3
		$kasutaja_grupp = count($this->site->kasutaja->grupp)>0 ? join(",",$this->site->kasutaja->grupp) : "";
		# Kui kasutajal grupp ei ole defineeritud või pole ta sisselogitud, 
		# siis ta kuulub gruppile "All website visitors"
		if (!$kasutaja_grupp){$kasutaja_grupp=100;}
*********/

		$parents = explode(',',$this->parent_id);

		$tase=0;
		
		##### recursive loop over parents
		while (sizeof($parents)>0 && $tase++<50) {
			$this->debug->msg("Next Round, parents = ".join(",",$parents));

/******** new quick hack SQL - POOLELI  */
			$sql = $this->site->db->prepare("
				SELECT objekt.objekt_id, objekt.on_avaldatud, objekt.tyyp_id,objekt_objekt.parent_id
				FROM objekt 
				LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id 
				WHERE parent_id IN ('".join("','",$parents)."') $wheretyyp
				".($this->site->in_editor || $this->site->in_admin ? "":" AND objekt.on_avaldatud=1 ")."
				GROUP BY objekt.objekt_id, objekt.on_avaldatud, objekt.tyyp_id,objekt_objekt.parent_id"			
			);
			$sth = new SQL($sql);
			# print "<br>".$sql;
			$this->debug->msg($sth->debug->get_msgs());
			$this->objects = array_merge($this->objects,$parents);
			$parents = array();
			while ($obj = $sth->fetch()) {
				$is_access = 0;

				if($this->skip_permissions_check) { # if skip
					$is_access = 1;
				}
				else {				
					####### check permissions
					$perm = get_obj_permission(array(
						"objekt_id" => $obj['objekt_id'],
						"on_avaldatud" => $obj['on_avaldatud'],
						"tyyp_id" => $obj['tyyp_id'],
						"parent_id" => $obj['parent_id'],
					));
					# kas useril on õigus objekti näha? 1/0
					if($perm['is_visible'] ) { $is_access = 1; }
					else { $is_access = 0; }
				} # skip/check permissions

				######### if access granted and not duplicate object
				if ($is_access && !in_array($obj['objekt_id'], $this->objects)) {
					array_push($parents,$obj['objekt_id']);
				}
			}
		} ###### / recursive loop over parents

		$this->objektid = &$this->objects; # alias for ver3
		$this->size = sizeof($this->objects);
			
	} # / constructor

	/**
	* Deletes all subtree objects from tables. Return deleted objects count.
	* Private
	*
	* $deleted_count = $puu->delete_objects();
	*
	*/
	function delete_objects() {

		$count = 0;

		# if is something to delete
		if(sizeof($this->objects)){

		$obj_str = join(",",$this->objektid);
		
		# kustutada saidi sisu
		$sql = $this->site->db->prepare("DELETE FROM objekt WHERE FIND_IN_SET(objekt_id,?)", $obj_str); 
		$sth = new SQL($sql);
		$this->site->debug->msg($sth->debug->get_msgs());
		$count += $sth->rows;

		# ----------------------
		# tabelid obj_*		  
		# ----------------------

		$sql = "SELECT tabel FROM tyyp WHERE ISNULL(tabel)=0 GROUP BY tabel";
		$sth = new SQL($sql);
		$this->site->debug->msg($sth->debug->get_msgs());
				
		while ($tabel=$sth->fetchsingle()) {
			$sql = $this->site->db->prepare("SELECT * FROM $tabel WHERE FIND_IN_SET(objekt_id, ?)", $obj_str);
			$sth2 = new SQL($sql);
			$this->site->debug->msg($sth2->debug->get_msgs());
			
			if ($tabel && $sth2->rows) {
				# kui antud tüübi objektid leitud
				# siis iga tüübi jaoks on oma erifunktsioonid

				$this->site->debug->msg("Midagi on kustutamiseks $tabel tabelis");

				if ($tabel == "obj_dokument") {
					# DOKUMENT
					$sql = $this->site->db->prepare("SELECT fail FROM obj_dokument WHERE find_in_set(objekt_id,?)", $obj_str);
					$sth3 = new SQL($sql);
					$this->site->debug->msg($sth3->debug->get_msgs());
					while ($filename = $sth3->fetchsingle()) {
						$this->site->debug->msg("Kustutan faili ".$this->site->CONF[documentsroot]."/$filename");
						unlink($this->site->CONF[documentsroot]."/$filename");
					}
				} elseif ($tabel == "obj_gallup") {
					# GALLUP
					$sql = $this->site->db->prepare("DELETE FROM gallup_vastus WHERE FIND_IN_SET(objekt_id,?)", $obj_str);
					$sth3 = new SQL($sql);
					$this->site->debug->msg($sth3->debug->get_msgs());

					$sql = $this->site->db->prepare("DELETE FROM gallup_ip WHERE FIND_IN_SET(objekt_id,?)", $obj_str);
					$sth3 = new SQL($sql);
					$this->site->debug->msg($sth3->debug->get_msgs());
				}

				$sql = $this->site->db->prepare("DELETE FROM $tabel WHERE FIND_IN_SET(objekt_id,?)", $obj_str);
				$sth3 = new SQL($sql);
				$this->site->debug->msg($sth3->debug->get_msgs());

			} # if tabel && rows
		} # while tabel

		# ---------------------- #]
		# tabel objekt_objekt    #]
		# ---------------------- #]

		$sql = $this->site->db->prepare(
			"DELETE FROM objekt_objekt WHERE FIND_IN_SET(objekt_id,?) or FIND_IN_SET(parent_id,?)", 
			$obj_str, $obj_str
		);
		$sth3 = new SQL($sql);
		$this->site->debug->msg($sth3->debug->get_msgs());

		} # if is something to delete

		return $count;

	}
	######### / function delete_content

}
