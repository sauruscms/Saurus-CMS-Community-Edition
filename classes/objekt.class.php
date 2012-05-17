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


class Objekt extends BaasObjekt {
# ---------------------------------------
# constructor new Objekt(array(
#	objekt_id	=> 123,
#	on_sisu		=> 1,
#	no_cache	=> 1,
#   skip_sanity_check => 1,
#   (parent_id	=> )
# ))
# ---------------------------------------

#	var $site;
#	var $debug;

	var $objekt_id;
	var $parent_id;
	var $pealkiri;
	var $on_avaldatud;
	var $on_keelatud;
	var $on_sisu;
	var $on_sisu_olemas;
	var $on_404;

	var $lyhi;
	var $sisu;
	var $template;
	var $all;
	
	/**
	 * object html link, used in SAPI
	 *
	 * @var string
	 */
	var $href;
	
	function Objekt() {
		$args = func_get_arg(0);
		$this->BaasObjekt($args);
#		$this->debug = new Debug();
		if ($args[parent_id]) { $this->parent_id = $args[parent_id]; }

		if ($args[objekt_id]) {
			# --------------------------
			# kontrollime cash'i
			# --------------------------
			if (!$args[no_cache] && $obj = $this->site->cash(array(kood => $args[objekt_id], "klass"=> "objekt"))) {
				$arr = get_object_vars($obj);
				while (list($prop, $val) = each($arr)) {
					$this->$prop = $val;
				}
				if ($args[on_sisu]) {
					# -----------------------------------
					# loeme sisu, kui on vaja (on_sisu=1)
					# -----------------------------------
					$this->load_sisu();
				}
				return;
			}
			# --------------------------
			# otsime objekti p�hiosa
			# --------------------------
			$this->all = $this->get_obj_by_id($args[objekt_id]);

		} else if ($args[ary]) {
			$this->all = $this->get_obj_from_array($args[ary]);

		}
		# if trying to open not existing object (ID not found) or if file is unpublished and
		# user is not in editor or admin mode
		# then show 404 error page
		if ((!$this->all && !$args[skip_sanity_check]) || (!$this->all["on_avaldatud"] && !$this->site->in_editor && !$this->site->in_admin)) {
			$this->all = $this->get_obj_by_id($this->site->alias("404error"));
			$this->on_404 = 1;
		}

			
		if ($this->all) {
			$this->objekt_id = $this->all["objekt_id"];
			$this->pealkiri = $this->all["pealkiri"];
			$this->parent_id = $this->all["parent_id"];
			$this->on_avaldatud = $this->all["on_avaldatud"];
		}
		############ get permissions
		if(!is_array($this->permission)){ # if not found yet
			$this->permission = $this->get_permission();
		}

		# objekti v�ib vaadata kui ta on kasutajale n�htav (t�psem valem get_permission f-nis)

		if( $args[creating] || $this->permission['is_visible'] || $args['superuser'] == 1) {
			if ($args[on_sisu]) {
				# -----------------------------------
				# loeme sisu, kui on vaja (on_sisu=1)
				# -----------------------------------
				$this->load_sisu();
			}
	
			if ($args[objekt_id]) {
				# salvestada objekt
				$this->kood = $this->objekt_id;
				$this->site->cash($this);
			}

			$this->debug->msg("Objekt leitud: ".$this->objekt_id.". ".$this->pealkiri);

		}
		# objekt on keelatud vaatamiseks
		else {
			$this->debug->msg("Objekt keelatud: ".$this->objekt_id.". ".$this->pealkiri);

			//printr($this->objekt_id.':keelatud');
			
			$this->objekt_id = 0;
			$this->pealkiri = "";
			$this->parent_id = 0;
			$this->on_avaldatud = 0;
		}
	}

	#####################
	# FUNCTION del

	function del() {
		
		$args = func_num_args()==1 ? func_get_arg(0) : func_get_args();
		$privelege = $args[privelege] ? 0 : 1;

		$sql = $this->site->db->prepare("SELECT * FROM tyyp where tyyp_id=?",$this->all[tyyp_id]);
		$sth = new SQL($sql);
		$this->debug->msg($sth->debug->get_msgs());
		$tyyp = $sth->fetch();

		# vaatame millised t��bid lubavad alampuu kustutada

		$sql = "select tyyp_id from tyyp where on_alampuu_kustutamine='1'";
		$sth = new SQL($sql);
		$this->debug->msg($sth->debug->get_msgs());

		$tyybid = array();

		while ($tid = $sth->fetchsingle()) {
			array_push($tyybid, $tid);
		}
		$this->debug->msg("T��bid mida alampuuga kustutame: ".join(",",$tyybid));

		$parents=array();
		$alampuu=array();
		$blocked=array();
		$warnings=array();
		$has_multiple_parents=array();
		array_push($parents, $this->objekt_id);
		$h=0;

		while (sizeof($parents) && $h++ < 25) {
			$this->debug->msg("parents = ".join(", ",$parents));
			$sql = $this->site->db->prepare("
				SELECT objekt.objekt_id, COUNT(parents_objekt.parent_id) AS parents_count, objekt.pealkiri, parent.tyyp_id 
				FROM objekt 
				LEFT JOIN objekt_objekt ON objekt.objekt_id=objekt_objekt.objekt_id 
				LEFT JOIN objekt_objekt AS parents_objekt ON objekt.objekt_id=parents_objekt.objekt_id 
				LEFT JOIN objekt as parent ON objekt_objekt.parent_id=parent.objekt_id 
				WHERE objekt_objekt.parent_id IN(".join(",",$parents).") AND parent.tyyp_id IN(".join(",",$tyybid).") 
				GROUP BY objekt.objekt_id, objekt.pealkiri, parent.tyyp_id"
			);
			$sth = new SQL($sql);
			$this->debug->msg($sth->debug->get_msgs());

			$alampuu = array_merge($alampuu, $parents);
			$parents = array();

			while ($record=$sth->fetch()) {
				if (!$blocked[$record[objekt_id]]) {
					$blocked[$record[objekt_id]] = 1;
					if ($record[parents_count] > 1) { 
						array_push ($warnings, $record);
						# check if we are deleting recycle bin? 
						# If yes then in case of multiple parents we should delete only parent relation of recycle bin and not the object itself. (Bug #2306)
						if($this->site->fdat['empty_recycle_bin']){
							$has_multiple_parents[] = $record[objekt_id];
						}

						array_push ($parents, $record[objekt_id]);
					} else {
						array_push ($parents, $record[objekt_id]);
					}
					$this->debug->msg("$record[objekt_id] ($record[parents_count]) - $record[pealkiri] tyyp=$record[tyyp_id]");
				}
			}
		} # while parents

		$this->debug->msg("L�plikus alampuus on ".sizeof($alampuu)." objekte");

		####### divide final objects array into 2 parts: 
		# 1. delete entirely - $alampuu		
		# 2. delete only trash parent relation - $has_multiple_parents
		if(sizeof($alampuu)>0 && sizeof($has_multiple_parents)>0) {
			$alampuu = array_minus_array($alampuu, $has_multiple_parents);
		}
		
		######## 1. DELETE OBJECTS ENTIRELY
		
		if(sizeof($alampuu)>0) { # for sanity
		# ----------------------
		# kustutame �ra :(
		# ----------------------

		# ----------------------
		# tabel objekt
		# ----------------------

		$sql = "DELETE FROM objekt WHERE objekt_id IN(".join(",",$alampuu).")";
		$sth = new SQL($sql);
		$this->debug->msg($sth->debug->get_msgs());

		# ----------------------
		# tabelid obj_*
		# ----------------------

		$sql = "SELECT tabel FROM tyyp WHERE tabel<>'' OR NOT ISNULL(tabel) GROUP BY tabel";
		$sth = new SQL($sql);
		$this->debug->msg($sth->debug->get_msgs());
				
		while ($tabel=$sth->fetchsingle()) {
			$sql = $this->site->db->prepare("SELECT * FROM $tabel WHERE objekt_id IN(".join(",",$alampuu).")");
			$sth2 = new SQL($sql);
			$this->debug->msg($sth2->debug->get_msgs());
			
			if ($tabel && $sth2->rows) {
				# kui antud t��bi objektid leitud
				# siis iga t��bi jaoks on oma erifunktsioonid

				$this->debug->msg("Midagi on kustutamiseks $tabel taabelis");

				if ($tabel == "obj_dokument") {
					# DOKUMENT
					if (!empty($this->site->CONF['documents_in_filesystem']) && !empty($this->site->CONF['documents_directory']) && file_exists(str_replace('//', '/', $this->site->absolute_path.$this->site->CONF['documents_directory'])))
					{
						$sql = $this->site->db->prepare('SELECT objekt_id FROM obj_dokument WHERE objekt_id IN('. join(',',$alampuu).')');
						$sth3 = new SQL($sql);
						$this->debug->msg($sth3->debug->get_msgs());
						while ($oid = $sth3->fetchsingle())
						{
							$filename = md5($oid);
							$filepath = str_replace('//','/',$this->site->absolute_path.$this->site->CONF['documents_directory'].'/'.$filename[0].'/'.$filename);
							if (@file_exists($filepath))
							{
								$this->debug->msg('Kustutan faili '.$filepath);
								unlink($filepath);
							}
						}
					}
					else
					{
  					$sql = $this->site->db->prepare("SELECT fail FROM obj_dokument WHERE objekt_id IN(". join(",",$alampuu).")");
  					$sth3 = new SQL($sql);
  					$this->debug->msg($sth3->debug->get_msgs());
  					while ($filename = $sth3->fetchsingle()) {
  						$this->debug->msg("Kustutan faili ".$this->site->CONF[documentsroot]."/$filename");
  						unlink($this->site->CONF[documentsroot]."/$filename");
  					}
					}
				} elseif ($tabel == "obj_gallup") {
					# GALLUP
					$sql = $this->site->db->prepare("DELETE FROM gallup_vastus WHERE objekt_id IN(".join(",",$alampuu).")");
					$sth3 = new SQL($sql);
					$this->debug->msg($sth3->debug->get_msgs());

					$sql = $this->site->db->prepare("DELETE FROM gallup_ip WHERE objekt_id IN(".join(",",$alampuu).")");
					$sth3 = new SQL($sql);
					$this->debug->msg($sth3->debug->get_msgs());
				}

				$sql = $this->site->db->prepare("DELETE FROM $tabel WHERE objekt_id IN(".join(",",$alampuu).")");
				$sth3 = new SQL($sql);
				$this->debug->msg($sth3->debug->get_msgs());

				# DOCUMENT_PARTS

				if ($tabel == "obj_dokument" || $tabel == "obj_pilt") {
					$sql = $this->site->db->prepare("DELETE FROM document_parts WHERE objekt_id IN(".join(",",$alampuu).")");
					$sth3 = new SQL($sql);
					$this->debug->msg($sth3->debug->get_msgs());
				}

			} # if taabel && rows
		} # while taabel

		# ---------------------- #]
		# tabel objekt_objekt    #]
		# ---------------------- #]

		$sql = $this->site->db->prepare(
			"DELETE FROM objekt_objekt WHERE objekt_id IN(".join(",",$alampuu).") OR parent_id IN(".join(",",$alampuu).")");
		$sth3 = new SQL($sql);
		$this->debug->msg($sth3->debug->get_msgs());
	

		# -----------------------
		# kustutame �ra grupp_objekt
		# -----------------------

		$sql = $this->site->db->prepare("DELETE FROM permissions WHERE type=? AND source_id IN(".join(",",$alampuu).")", 
			'OBJ'
		);
		$sth = new SQL($sql);
		$this->debug->msg($sth->debug->get_msgs());

		# -----------------------
		# kustutame �ra user_mailinglist
		# -----------------------

		$sql = $this->site->db->prepare("DELETE FROM user_mailinglist WHERE objekt_id IN(".join(",",$alampuu).")");
		$sth = new SQL($sql);
		$this->debug->msg($sth->debug->get_msgs());

		} # if on midagi kustutada
		######## / 1. DELETE OBJECTS ENTIRELY

		######## 2. DELETE OBJECTS TRASH PARENT RELATION - because object has more parents
		if(sizeof($has_multiple_parents)>0) {

			# ---------------------- #]
			# tabel objekt_objekt    #]
			# ---------------------- #]
			$trash_id = $this->site->alias(array('key' => 'trash'));

			$sql = $this->site->db->prepare(
				"DELETE FROM objekt_objekt WHERE objekt_id IN(".join(",",$has_multiple_parents).") AND objekt_objekt.parent_id=?", $trash_id);
			$sth3 = new SQL($sql);
			$this->debug->msg($sth3->debug->get_msgs());
		}
		######## 2. / DELETE OBJECTS TRASH PARENT RELATION - because object has more parents
	}
	# / FUNCTION del
	#####################


	function load_sisu () {

		if (!$this->on_sisu) {
			$sisu = $this->get_sisu();
		}
		
		// bug #2771, PHP 5.2 ja ZO 3.3'ga cache'st v�etud objektil on k�ll on_sisu = 1 aga lyhi ja sisu ei ole enam objektid
		if(is_array($sisu)) { $this->all = array_merge($sisu,$this->all); }

		$lyhi = $this->all["lyhi"];
		
		# Bug #1448: kui saidi protokoll on https, n�idatakse artiklis olevaid pilte ikkagi http protokolliga
		#$hostname = ($this->site->CONF[protocol]?$this->site->CONF[protocol]:"http://").$this->site->CONF[hostname].$this->site->CONF[wwwroot].$this->site->CONF[file_path];
		
		$hostname = (empty($_SERVER['HTTPS']) ? 'http://': 'https://').$this->site->CONF['hostname'].$this->site->CONF['wwwroot'];
		
		if ($lyhi) {
			$lyhi = str_replace("##saurus649code##", $hostname, $lyhi);
		}
		$this->lyhi = new HTML($lyhi);
		$this->debug->msg($this->lyhi->debug->get_msgs());

		$sisu = $this->all["sisu"];
		if ($sisu) {
			$sisu = str_replace("##saurus649code##", $hostname, $sisu);
		}
		$this->sisu = new HTML($sisu);
		$this->debug->msg($this->sisu->debug->get_msgs());
			
		# 28.02.2011 Mati: replace links to local files with hostname+url for them to work in editor or with aliases
		$url = $this->all["url"];
		global $site;
		if ($url
		    && (substr($url, 0, 7) == 'public/' || substr($url, 0, 7) == 'shared/')
		    # the replacement is not necessary when editing the link
		    && $site->script_name != 'edit.php') {
			$this->all["url"] = $hostname.'/'.$url;
		}
		
		$this->on_sisu = 1;
		$this->debug->msg("Sisu on loetud");

	}

	function pealkiri($meny = 0) {
		$pealkiri = $this->pealkiri;
		return $pealkiri;
	}

	function pealkiri_link($class = '') {

		$link = $this->site->self;

		# erandid artikli pealkirja linkimisel, s�ltuvad l�hist ja sisust
		# (ei m�ju kommentaaride ega rubriikide pealkirjade linkidele)

		$this->load_sisu();
		$lyhi = $this->lyhi->get_text();
		$sisu = $this->sisu->get_text();

		# kui sisu on t�hi ja l�hi on ainult 1 link kujul "<a href .. >..</a>",
		# siis pannakse pealkiri linkima otse sellele lingile

		if (preg_match ('/^(\s*)\<a(\s+)href="([\w\.\:\/\=\?\&\-]+)"(\s*)\>(.*)\<\/a>(\s*)$/i',$lyhi, $match) && !$sisu){

			# 1) $match[3] on url esimese href="" sees.
			# 2) $match[5] on sisu peale linki kuni lingi l�puni, seega

			# -juhul kui $match[5] sees on html tag-e, siis kasuta tavalist kuju (Bugs #512, #1139).
			if (stristr($match[5], '<')){
				$link = $this->site->self."?id=".$this->objekt_id;
			}
			# -juhul kui selle sees ei ole html-tage, siis j�relikult on kogu tekst ainult �ks link,
			# rakenda teist lingikuju =>  otselink "href=.." v��rtusele.
			else {
				$link = $match[3];
				$target = " target=\"_new\"";
			}
		}# link found in lyhi 

		# kui l�hi on t�hi ja sisu on ainult 1 link kujul "<a href .. >..</a>",
		# siis pannakse pealkiri linkima otse sellele lingile

#		print ("SISU:".htmlspecialchars($sisu));

		if (preg_match ('/^(\s*)\<a(\s+)href="([\w\.\:\/\=\?\&\-]+)"(\s*)\>(.*)\<\/a>(\s*)$/i',$sisu, $match) && !$lyhi){

#			print ("<hr>MATCH 1:".htmlspecialchars($match[1]).";");
#			print ("<hr>MATCH 2:".htmlspecialchars($match[2]).";");
#			print ("<hr>MATCH 3:".htmlspecialchars($match[3]).";");
#			print ("<hr>MATCH 4:".htmlspecialchars($match[4]).";");
#			print ("<hr>MATCH 5:".htmlspecialchars($match[5]).";");
#			print ("<hr>MATCH 6:".htmlspecialchars($match[6]).";");

			# 1) $match[3] on url esimese href="" sees.
			# 2) $match[5] on sisu peale linki kuni lingi l�puni, seega

			# -juhul kui $match[5] sees on html tag-e, siis kasuta tavalist kuju (Bugs #512, #1139).
			if (stristr($match[5], '<')){
				$link = $this->site->self."?id=".$this->objekt_id;
			}
			# -juhul kui selle sees ei ole html-tage, siis j�relikult on kogu tekst ainult �ks link,
			# rakenda teist lingikuju =>  otselink "href=.." v��rtusele.
			else {
				$link = $match[3];
				$target = " target=\"_new\"";
			}
		} # link found in sisu 
		# usual case
		if (!$target) {
			$pealkiri = sprintf("<a href=\"%s?id=%s\" class=\"%s\"$target>%s</a>", $link, $this->objekt_id, $class, $this->pealkiri());
		}
		# special case
		else {
			$pealkiri = sprintf("<a href=\"%s\" class=\"%s\"$target>%s</a>", $link, $class, $this->pealkiri());
		}	
		return $pealkiri;
	}



	function get_obj_by_id($objekt_id) {
	# ---------------------------------------
	# objekti peaosa lugemine tabelist objekt
	# ---------------------------------------
		if ($objekt_id) {
			# --------------------------
			# vajalikud andmed on olemas
			# --------------------------

			##FIX bug [471]
			if(!empty($this->parent_id)) {
				$parent = "and objekt_objekt.parent_id = '".$this->parent_id."'";
			} else {
				$parent = "";
			}

			# query optimized by merle 28.04.2003 
			# (decreases in special cases parsed rows count, eg from 40 to 2)
			# was before: 
			#FROM objekt,tyyp 
			#	LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id
			#	LEFT JOIN templ_tyyp on objekt.ttyyp_id = templ_tyyp.ttyyp_id 
			#	WHERE objekt.tyyp_id=tyyp.tyyp_id and objekt.objekt_id=? ". $parent ."
			#	GROUP BY objekt.objekt_id",

			# 09.05 merle: this optimized thing is still not working,
			# i will rollback whole thing. optimized version was:
			#	FROM objekt_objekt 
			#	LEFT JOIN objekt on objekt.objekt_id=objekt_objekt.objekt_id 
			#	LEFT JOIN tyyp ON objekt.tyyp_id = tyyp.tyyp_id
			#	LEFT JOIN templ_tyyp on objekt.ttyyp_id = templ_tyyp.ttyyp_id
			#	WHERE objekt_objekt.objekt_id=? ". $parent ."
			#	GROUP BY objekt_objekt.objekt_id",
			
			$sql = $this->site->db->prepare("
				SELECT 
					objekt.objekt_id, objekt.pealkiri, objekt.on_pealkiri, objekt.tyyp_id,
					objekt.on_avaldatud, objekt.keel, objekt.kesk, 
					objekt.ttyyp_id, objekt.page_ttyyp_id, objekt.on_foorum, objekt.aeg, objekt.meta_keywords, objekt.meta_title,
					objekt.meta_description, objekt.count, objekt.sys_alias, objekt.ttyyp_params, 
					objekt_objekt.parent_id, objekt_objekt.sorteering, objekt.avaldamisaeg_algus, objekt.avaldamisaeg_lopp, objekt.friendly_url, objekt.is_hided_in_menu, objekt.fulltext_keywords,
					tyyp.tyyp_id, tyyp.tabel, tyyp.klass,
					templ_tyyp.on_auto_avanev, tyyp.on_kast, objekt.last_modified, objekt.related_objekt_id, objekt.author,
					objekt.created_user_id, objekt.created_user_name, objekt.changed_user_id, objekt.changed_user_name, objekt.created_time, objekt.changed_time, objekt.last_commented_time, objekt.comment_count, objekt.on_saadetud,
				COUNT(objekt_objekt.parent_id) as parents_count 
			FROM objekt
				LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id
				LEFT JOIN templ_tyyp on objekt.ttyyp_id = templ_tyyp.ttyyp_id 
				LEFT JOIN tyyp ON objekt.tyyp_id=tyyp.tyyp_id
				WHERE  objekt.objekt_id=? ". $parent ."
				GROUP BY objekt.objekt_id",
			$objekt_id
			);

			#if (!$admin) { $sql .= " AND objekt.on_avaldatud=1 "; }

			$sth = new SQL($sql);
			$this->debug->msg($sth->debug->fetch_msgs());

			return $sth->fetch();

		} else { 
			# --------------------------
			# vajalikud andmed puuduvad
			# --------------------------
			$this->debug->msg("objekt ID on t�hi!");
		}
	}
	function get_obj_all_parents($objekt_id='') {
	# ---------------------------------------
	# objekti k�igi parentite v�ljastamine massiivi
	# ---------------------------------------
		$parents = array();
		
		if(!is_numeric($objekt_id)) { $objekt_id = $this->objekt_id; }

		if ($objekt_id) {
			$sql = $this->site->db->prepare("
				SELECT objekt_objekt.parent_id
				FROM objekt_objekt 
				WHERE objekt_objekt.objekt_id=?",
				$objekt_id
			);
			$sth = new SQL($sql);
			while($par = $sth->fetchsingle()) {
				$parents[] = $par;
			}
			return $parents;
		}
	}

	function get_obj_from_array($ary) {
	# ---------------------------------------
	# objekti peaosa lugemine antud massiivist
	# ---------------------------------------
		return $ary;
	}

	function get_sisu() {
	# ---------------------------------------
	# objekti sisu lugemine tabelist obj_*
	# ---------------------------------------
		if ($this->objekt_id && $this->all[tabel]) {
			# --------------------------
			# vajalikud andmed on olemas
			# --------------------------
			$fields = $this->site->db->get_fields(array(
				tabel => $this->all[tabel],
			));

			if (!$fields) {
				$fields  = "*";
			}

			$sql = "SELECT $fields FROM ".$this->all[tabel]." WHERE objekt_id=".$this->objekt_id;

			$sth = new SQL($sql);
			$this->on_sisu_olemas = $sth->rows;
			$this->debug->msg($sth->debug->fetch_msgs());
			return $sth->fetch();
		} else { 
			# --------------------------
			# vajalikud andmed puuduvad
			# --------------------------
			$this->debug->msg("objekt ID v�i tabeli nimi on t�hi!");
		}
	}

	function aeg() {
		$aeg = $this->all[aeg];
		$aeg = preg_replace("/(\d\d\d\d)\-(\d?\d)-(\d?\d)/","\\3.\\2.\\1",$aeg);
		return $aeg;
	}

	function get_comment_count() {

		$this->comment_count = $this->all['comment_count'];
	}
	
	function get_text() {
	# ---------------------------------------
	# kui lyhi ja sisu on olemas,
	# tagastab m�lemad kleebitud "<br><br>"-iga
	# v�i l�hi ja "loe edasi" kui param loe_edasi=1
	# ---------------------------------------
		$args = func_num_args()==1 ? func_get_arg(0) : func_get_args();

		$this->load_sisu();
		
		// #2824
		if($args['fill'])
		{
			$this->lyhi->Fill($args['fill']);
			$this->sisu->Fill($args['fill']);
		}
		
		$text1 = $this->lyhi->get_text();
		$text2 = $this->sisu->get_text();
		
		if ($text1 != "" && $text2 != "") {
			if ($args[loe_edasi]) {		
				# erandiks 'loe edasi' linkimisel on juht, kus sisu koosneb ainult lingist kujul "<a href .. >..</a>"
				# siis pannakse 'loe edasi' linkima otse sellele lingile

				if (preg_match ('/^(\s*)\<a(\s+)href="([\w\.\:\/\=\?\&\-]+)"(\s*)\>(.*)\<\/a>(\s*)$/i',$text2, $match)){
					# 1) $match[3] on url esimese href="" sees.
					# 2) $match[5] on sisu peale linki kuni lingi l�puni, seega

					# -juhul kui $match[5] sees on html tag-e, siis kasuta tavalist kuju (Bugs #512, #1139).
					if (stristr($match[5], '<')){
						$link = $this->site->self."?id=".$this->objekt_id;
					}
					# -juhul kui selle sees ei ole html-tage, siis j�relikult on kogu tekst ainult �ks link,
					# rakenda teist lingikuju =>  otselink "href=.." v��rtusele.
					else {
						$link = $match[3];
						$target = " target=\"_new\"";
					}
				}
				# link found in sisu 
				else { $link = $this->site->self."?id=".$this->objekt_id; }

				return $text1."<br><a href=\"".$link."\"$target><b>".$this->site->sys_sona(array(sona=>'Loe edasi', tyyp=>"kujundus"))."</b></a><br>";
			} else {
				return $text1."<br><br>".$text2;
			}
		} else {
			return $text1.$text2;
		}
	}

	function print_text() {
		print $this->get_text();

	}

# oldstyle ver 3 func, deprecated. preserved for compability only,
# returns if object is visible to user or not 1/0:
	function on_access() {	
		if(!is_array($this->permission)){ # load permissions if not loaded yet
			$this->permission = $this->get_permission();
		}
		return $this->permission['is_visible'];
	}
# / oldstyle ver 3 func, is deprecated

/**
* get_permission 
* 
* simplifies usage of general public get_obj_permission() function:
* inside object class you can get permissions without sending required parameters,
* this function sends them forward itself (values are taken from object values)
*
* @package CMS
*
* Call: $obj->permission = $obj->get_permission();
*/
	function get_permission () {

		
		# if object ID provided
		if($this->objekt_id) {
			$perm = get_obj_permission(array(
				"objekt_id" => $this->objekt_id,
				"on_avaldatud" => $this->all['on_avaldatud'],
				"tyyp_id" => $this->all['tyyp_id'],
				"parent_id" => $this->parent_id,
			));
#if($this->objekt_id == $this->site->fdat[id]) {
#print "<br>get_obj_permission: objekt_id => ".$this->objekt_id.", on_avaldatud => ".$this->all['on_avaldatud'].", tyyp_id => ".$this->all['tyyp_id'].", parent_id => ".$this->parent_id;
#}


		}	# if object ID provided

		# set permission array also as object property
		$this->permission = $perm;

		return $perm;
	}

###########################
# buttons
		
	function edit_buttons () {
		$args = func_num_args()==1 ? func_get_arg(0) : func_get_args();
		print $this->get_edit_buttons($args);
	}

	function get_edit_buttons () {
		# EDITOR-AREA CHECK: print buttons only for editor-area and admin-area, else return nothing
		if(!$this->site->in_editor && !$this->site->in_admin) {
			return "";
		}

		$args = func_get_arg(0);

		$ttyyp_id = $args[ttyyp_id] ? $args[ttyyp_id] : 0;
		if ($this->on_404) {return "";}

		if(!is_array($this->permission)){ # load permissions if not loaded yet
			$this->permission = $this->get_permission();
		}
		$perm = $this->permission;

		# get parent permissions for new button
		$parent_perm = get_user_permission(array(
			type => 'OBJ',
			objekt_id => $this->parent_id,
			site => $this->site
		));
		$perm['C'] = $parent_perm['C'];

		######### if user has C or U or P or D  permission (see also Bug #1985)
		# then show buttons, otherwise show nothing
		if( !($perm['C'] || $perm['U'] || $perm['P'] || $perm['D']) ) {
			return "";
		}

		$self = $this->site->safeURI;
		$parent_id = $this->parent_id;
		$kesk = $this->all[kesk];
		$keel = isset($args[keel]) ? $args[keel] : $this->site->keel;

		$nupud = is_array($args[nupud]) ?  $args[nupud] : array("new","edit","move","hide","delete");
		
		// reorder buttons and put CMS buttons first in list, this is because every init_ has different order and it is easier to keep the correct order here
		$temp_nupud = $nupud;
		$reordered_nupud = array();
		foreach($temp_nupud as $i => $nupp)
		{
			switch ($nupp)
			{
				case 'new': $reordered_nupud[0] = 'new'; unset($temp_nupud[$i]); break;
				case 'edit': $reordered_nupud[1] = 'edit'; unset($temp_nupud[$i]); break;
				case 'move': $reordered_nupud[2] = 'move'; unset($temp_nupud[$i]); break;
				case 'hide': $reordered_nupud[3] = 'hide'; unset($temp_nupud[$i]); break;
				case 'delete': $reordered_nupud[4] = 'delete'; unset($temp_nupud[$i]); break;
				default: break;
			}
		}
		ksort($reordered_nupud);
		$nupud = array_merge($reordered_nupud, $temp_nupud);
		
		$buttons = array();
		
		foreach ($nupud as $nupp)
		{
			switch ($nupp)
			{
				case 'new':
					if($perm['C']) switch($args['tyyp_idlist'])
					{
						case '1': $buttons[] = 'scms_new_section_object'; break;
						case '2': $buttons[] = 'scms_new_article_object'; break;
						case '3': $buttons[] = 'scms_new_link_object'; break;
						case '6': $buttons[] = 'scms_new_poll_object'; break;
						case '7': $buttons[] = 'scms_new_document_object'; break;
						case '12': $buttons[] = 'scms_new_image_object'; break;
						case '14': $buttons[] = 'scms_new_comment_object'; break;
						case '15': $buttons[] = 'scms_new_topic_object'; break;
						case '16': $buttons[] = 'scms_new_album_object'; break;
						case '21': $buttons[] = 'scms_new_file_object'; break;
						default: $buttons[] = 'scms_new_object'; break;
					}
				break;
					
				case 'edit':
					if($perm['U']) $buttons[] = 'scms_edit_object';
				break;
					
				case 'move':
					if($perm['U'])
					{
						$buttons[] = 'scms_move_up_object';
						$buttons[] = 'scms_move_down_object';
					}
				break;
				
				case 'hide':
					if($perm['P']) ($this->on_avaldatud ? $buttons[] = 'scms_unpublish_object' : $buttons[] = 'scms_publish_object');
				break;
					
				case 'delete':
					if($perm['D']) $buttons[] = 'scms_delete_object';
				break;
					
				default:
					$buttons[] = $nupp;
				break;
			}
		}

		# visible: black
		# hidden: red
		# hiddenvisible: yellow (hided in menu)

		$class = 'scms_arrow_'.($this->on_avaldatud?($this->all['is_hided_in_menu'] ? 'hiddenvisible' :"visible"):"hidden");

		$baseurl = (empty($_SERVER['HTTPS']) ? 'http://': 'https://').$this->site->CONF['hostname'].$this->site->CONF['wwwroot'];
		
		$result = '<img class="scms_context_button_anchor '.$class.'" src="'.$baseurl.'/styles/default/gfx/px.gif" width="13" height="13" border="0" buttons="'.implode(',', $buttons).'" scms_url="'.$baseurl.'/" scms_self="'.$self.'" scms_object_id="'.$this->objekt_id.'" scms_object_parent_id="'.$parent_id.'" scms_object_position="'.$kesk.'" scms_object_template_id="'.$ttyyp_id.'" scms_object_type_list="'.$args['tyyp_idlist'].'" scms_object_lang_id="'.$keel.'" scms_object_is_published="'.($this->on_avaldatud ? 1 : 0).'" scms_object_is_hidden_in_menu="'.($this->all['is_hided_in_menu']?1:0).'" scms_object_profile_id="'.$args['profile_id'].'" scms_object_publish="'.$args['publish'].'"'.($args['tyyp_idlist'] == 2 ? ' scms_object_allow_comments="'.$args['allow_comments'].'"' : '').' scms_object_sorting="'.$this->all['sorteering'].'">';
		
		return $result;
	}

	/**
	 * Sets and returns object href value (used in API: $obj->href)
	 *
	 * @param object $object
	 * @return string
	 */
	function get_object_href()
	{
		// if href is already set return it
		if($this->href) return $this->href;
		
		global $site;
		
		// this is the normal object link, no aliases
		$this->href = $site->self.'?id='.$this->objekt_id;
		
		// no aliases for editor
		if($site->in_editor)
		{
			return $this->href;
		}
		
		// if not in editor and use aliases has been enabled
		if(!$site->in_editor && $site->CONF['use_aliases'])
		{
			$alias = '';
			// get the parent object so we can check that it's not home, trash, system etc
			if($site->CONF['alias_trail_format'] == 1 && $this->parent_id)
			{
				$parent = new Objekt(array('objekt_id' => $this->parent_id));
			}
			else 
			{
				$parent = false;
			}
			
			// if alias must show trail, the alias = parent object alias + this alias
			if($site->CONF['alias_trail_format'] == 1 && $parent && $parent->all['sys_alias'] != 'home' && $parent->all['sys_alias'] != 'trash' && $parent->all['sys_alias'] != 'system')
			{
				// recursion
				$alias = $parent->get_object_href();
			}
			// else create the first part of alias (wwwroot + lang)
			else 
			{
				// add site root
				$alias .= $site->CONF['wwwroot'].'/';
				
				// if language aliases are used get active languages
				if($site->CONF['alias_language_format'])
				{
					$languages = $site->cash(array('klass' => 'GET_LANGUAGES', 'kood' => 'ALL_LANGUAGE_INFO'));
					
					if(empty($languages))
					{
						$sql = "select keel_id, extension, on_default from keel where on_kasutusel = 1";
						$result = new SQL($sql);
						while($row = $result->fetch('ASSOC'))
						{
							$languages[$row['keel_id']] = $row;
						}
						
						$site->cash(array('klass' => 'GET_LANGUAGES', 'kood' => 'ALL_LANGUAGE_INFO', 'sisu' => $languages));
					}
				}
				
				// add languge alias - alias language format 0: none, 1: always, 2: for non-default lang objs
				if($site->CONF['alias_language_format'] == 1)
				{
					$alias .= $languages[$this->all['keel']]['extension'].'/';
				}
				elseif ($site->CONF['alias_language_format'] == 2)
				{
					if(!$languages[$this->all['keel']]['on_default'])
					{
						$alias .= $languages[$this->all['keel']]['extension'].'/';
					}
				}
			}
			
			// add this alias to the end
			// object alias, if not defined use object ID instead
			if($this->all['friendly_url'])
			{
				$alias .= $this->all['friendly_url'];
			}
			else
			{
				$alias .= $this->objekt_id;
			}
			
			$this->href = $alias;
		}
		// no alias module
		
		if(strpos($this->href, '?id=') === false) $this->href .= '/';
		
		return $this->href;
	}

}
# / class Objekt
###########################


####################
# Standalone and public object-related functions 
# (use when object itself is not created and only object ID is known)


/**
* get_obj_permission 
* 
* returns current user permission array for given object ID
* sets also some useful properties: only_read, is_visible, mask.
* 
*
* @package CMS
* 
* @param int objekt_id - Object ID who's permissions are queried
* @param boolean on_avaldatud - if object is published or not (1/0), needed for properties
* @param int tyyp_id - type ID, for deciding if use object's or it's parent permissions
* 
* usage: $perm = get_obj_permission(array("objekt_id" => $objekt->objekt_id));
*/
function get_obj_permission () {
		$args = func_get_arg(0);
		global $site;

		$perm = array();
		$perm_msg = ''; # debug message

		# if object ID provided
		if($args['objekt_id']) {

		# Check if we have superuser. If yes, then don't waste time for loading permissions, 
		# mask is always CRUPD=11111 (Bug #1974)
		if($site->user->is_superuser) {
			$perm = array(
			id => '',
			type => 'OBJ',
			source_id => $args['objekt_id'],
			group_id => '',
			user_id => '',
			C => 1,
			R => 1,
			U => 1,
			P => 1,
			D => 1
			);
			$perm_msg .= "get_obj_permission: ".$args['objekt_id']." => CRUPD = ".$perm['C'].$perm['R'].$perm['U'].$perm['P'].$perm['D'];
			$perm_msg .= " (superuser)";
		} 
		else { # if user is not superuser
			
			# check if object has it's own permissions set
			if($site->user) { $ownperm = $site->user->permissions[$args['objekt_id']]; }
			elseif($site->guest) { $ownperm = $site->guest->permissions[$args['objekt_id']]; }

			########## 1. if section (tyyp_id=1), then use object's permissions
			if($args['tyyp_id'] == 1){
				$objekt_id = $args['objekt_id'];
			}
			########## 2. if object has it's own permissions already loaded, use them
			elseif(sizeof($ownperm)>0){
				$objekt_id = $args['objekt_id'];
			}
			########## 3. if not section and no permissions set - use parent permissions
			else
			{
				if(!$args['parent_id']) { # NO parent ID given
					# GET parent: RARE CASE
					$sql = $site->db->prepare("SELECT parent_id FROM objekt_objekt WHERE objekt_id=? LIMIT 1",$args['objekt_id']);
					$sth = new SQL($sql);
					$parents = $sth->fetch();
					$args['parent_id'] = $parents['parent_id']; # first parent
	#print "<br>".$sql;
				}

				########## use parent section permissions if they exist in user permissions array			
				if($args['parent_id']) { # parent ID given
					if($site->user) { 
						$parentperm = $site->user->permissions[$args['parent_id']];
						# if parent permissions exist 
						if(sizeof($parentperm)>0) {
							$objekt_id = $args['parent_id']; # use them
							$perm_msg .= "get_obj_permission: ".$args['objekt_id']." => use parent ".$args['parent_id']." permissions";
						}
					} elseif($site->guest) { 
						$parentperm = $site->quest->permissions[$args['parent_id']];
						if(sizeof($parentperm)>0) {
							$objekt_id = $args['parent_id'];
							$perm_msg .= "get_obj_permission ".$args['objekt_id']." => use parent ".$args['parent_id']." permissions";
						}
					}

					############### parent permissions not found in permissions array => go on with searching
					# THIS IS RARE CASE. NORMALLY PARENT PERMSSIONS SHOULD ALREADY EXIST
					# this is always the case when performing a site seacrh
					if(sizeof($parentperm)<=0)
					{
						$perm_msg .= "get_obj_permission: ".$args['objekt_id']." => SEARCH RECURSIVELY"; 
						
						if($args['on_avaldatud'])
						{
							# published flag is inherited from parent
							$result = new SQL('select on_avaldatud from objekt where objekt_id = '.(int)$args['parent_id']);
							$args['on_avaldatud'] = $result->fetchsingle();
						}
						
						$perm = get_obj_permission(array(
							'objekt_id' => $args['parent_id'],
							'on_avaldatud' => $args['on_avaldatud'],
						));
						
						// parent can be published according to the database, but his parent may not be so use the calculated is_visible field to make the final desicion
						if(!$perm['is_visible']) $args['on_avaldatud'] = 0;
						
						$perm_msg .= '. Parent published: '.$args['on_avaldatud']; 
					}
				} 
				# / if parent ID given
				##################
				else {
					$objekt_id = $args['objekt_id'];			
				}

			} # if not section

			if(!$objekt_id){ # hopeless case
				$objekt_id = $args['objekt_id'];			
			}

			########## get permissions
			if($objekt_id && sizeof($perm)<=0){
				# (this function will also write additional debug messages about mask etc)
				$perm = get_user_permission(array(
					type => 'OBJ',
					objekt_id => $objekt_id,
					site => $site
				));
			}

		} # / if user is not superuser

		if(is_array($perm)){

			######## IF FOUND NEW PERMISSIONS FROM PARENT => ADD OBJECT PERM ALSO TO GLOBAL ARRAY
			if(sizeof($parentperm)>0) {
				if($site->user) { 
					$site->user->permissions[$args['objekt_id']] = $parentperm;
				} elseif($site->guest) { 
					$site->guest->permissions[$args['objekt_id']] = $parentperm;
				}
			}
		
		##################
		# set all useful properties

		# kas useril on objekti kohta ainult lugemis�igus? 1/0
		$perm['only_read'] = ($perm['R'] && !$perm['C'] && !$perm['U'] && !$perm['P'] && !$perm['D'] ? 1 : 0);

		# object is visible? 1/0
		# if (avaldatud AND Read) OR (not avaldatud AND Read AND in_editor/admin AND Create-or-Update-or-Publish-or-Delete) => VISIBLE

		# (removed by Bug #2182: if read AND (avaldatud OR (not avaldatud AND in_editor/admin) => visible )
		# (removed Bug #1985: && !$perm['only_read'] )
		if( ($args['on_avaldatud'] && $perm['R']) || 
			(!$args['on_avaldatud'] && $perm['R'] && ($site->in_editor || $site->in_admin) && ($perm['C'] || $perm['U'] || $perm['P'] || $perm['D']))
		) {
			$perm['is_visible'] = 1;
		} else {
			$perm['is_visible'] = 0;
		}

		# set mask, eg 01100
		$perm['mask'] = $perm['C'].$perm['R'].$perm['U'].$perm['P'].$perm['D'];

		$perm_msg .= " / is published: ".$args['on_avaldatud']. " is_visible: ".$perm['is_visible'];
		
		# / set all useful properties
		##################
		} # if permissions set
		}	# if object ID provided

		############## PRINT DEBUG MESSAGE
		if($site->user) { 
			$site->user->debug->msg($perm_msg); 
		} elseif($site->guest) { 
			$site->guest->debug->msg($perm_msg); 
		}

		return $perm;
}
