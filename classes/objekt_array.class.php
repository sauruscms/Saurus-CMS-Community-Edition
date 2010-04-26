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



class ObjektArray extends BaasObjekt {

	var $size;
	var $index;
	var $list;
	var $objekts;

	function ObjektArray () {
		#$args = func_get_arg(0);
		$this->BaasObjekt($args);	
			
		$this->list = Array();
		$this->objekts = Array();		
		$this->set_size();
		$this->index = -1;
	}

	function set_size () {
		# private
		# kirjutab objekti omadus suurus
		$this->size = sizeof($this->list);
	}

	function reset () {
		# h�ppab algusele
		if ($this->size>0) {
			$this->index = -1; # muutsin 0 => -1. t��tab valesti muidu ju. (peale reset-k�sku alustatakse tavaliselt uuesti while ts�klit ->next k�su abil ja siis ollakse kohe index 0 pealt j�rgmise indeksi (1) peal. esimest ignoreeritakse nii �ldse.)
			$this->debug->msg("index = -1");
		} else {
			$this->index = -1;
			$this->debug->msg($this->size."List on t�hi: index = -1");
		};
	}

	function add ($objekt) {
		# lisab objekti objektide ahela l�ppu
		# juhul kui objekt ei ole kasutajale vaatamiseks (R) keelatud
		# JA pole juba listis olemas
		## kui objekt juba listis leidub, siiss �ra lisa topelt (porting MS SQL)

		## POOLELI: Suure objektide arvu (nt �le ~10 000) korral on siin t�sine kiiruse jama, tekib "Fatal error: Maximum execution time of 30 seconds exceeded in", sest massiiv "$this->objekts" on liiga suur:
#print('; '.sizeof($this->objekts));

		if( in_array($objekt->objekt_id, $this->objekts) ){
			#printr("AGA ma olen juba olemas:".$objekt->objekt_id);
			return;
		}

		############### new ver4 ACL permissions

		if(!isset($objekt->permission)){ # load permissions	if not loaded yet
			$objekt->permission = $objekt->get_permission();
		}
		$perm = $objekt->permission;

		############# find object permissions
		
		################# USE PERMISSIONS
		# at this point object permissions must exist (either user,guest or default permissions)

		############ add object if it-s visible

		if ($objekt->objekt_id && $perm['is_visible'] ) {
			array_push($this->list,$objekt);
			array_push($this->objekts,$objekt->objekt_id);
			$debug_msg .= $objekt->objekt_id." => object added.";
			$this->set_size();
		} 
		# if NOT allowed then dont add object 
		else {
			# ifnot visible
			if(!$perm['is_visible']) {
				$this->debug->msg("Objekt on vaatamiseks keelatud! Ei lisa teda objektide listi.");
				$debug_msg = " => skip object (Read is denied)";
			}
			# if missing ID
			elseif(!$objekt->objekt_id) {
				$this->debug->msg("Objekt pole loodud");
				$debug_msg = " => skip object (object not found)";
			}
			# if unknown
			else {
				$this->debug->msg("Objekt missing");
				$debug_msg = " => skip object (reason unknown)";
			}
			unset($objekt);

		}	
	
		########### debug msg
		if($this->site->user->user_id) {
			$this->site->user->debug->msg($debug_msg);
		} elseif($this->site->guest) {
			$this->site->guest->debug->msg($debug_msg);
		}


	} # function add

	function on_viimane () {
		# 1 kui pointer on viimase objekti peal, 
		# 0 kui mitte
		return $this->index+1 == $this->size;
	}

	function on_parent ($objekt) {
		# 1 kui antud id on parentide hulgas, 
		# 0 kui mitte

		if (!strcasecmp(get_class($objekt),"Objekt")) {
			# input on objekt
			$objekt_id = $objekt->objekt_id;
		} elseif (preg_match("/^\d+$/",$objekt)) {
			# input on objekti ID
			$objekt_id = $objekt;
		} else {
			# input on vigane
			$this->debug->msg("vigased algandmed: \"$objekt\" pole objekt ega objekti ID");
		}
		return in_array($objekt_id, $this->objekts);
	}

	function get_current () {
		# tagastab objekt, mis on hetkel aktiivne
		if ($this->index>=0 and $this->index<$this->size) {
#			$this->debug->msg("tagastan ".($this->index+1).". objekt ".$this->size."-st");
			return $this->list[$this->index];
		}
	}

	function get ($index) {
		# tagastab objekt, $index-i j�rgi
		# $index = 1 - objekt number 2 (0,1,2...)
		# $index = -1 - esimene objekt l�pust

		###### positive index or 0 (get from start01.11.05)
		if ($index>=0 and $index<$this->size) {
#			$this->debug->msg("tagastan ".($index+1).". objekt ".$this->size."-st (index=$index)");
			return $this->list[$index];
		} 
		###### negative index (get from end)
		else if ($index<0 and abs($index)<=$this->size) {
#			$this->debug->msg("tagastan ".($this->size+$index+1).". objekt ".$this->size."-st (index=$index)");
			return $this->list[$this->size+$index];
		} else {
#			$this->debug->msg("vale index $index, loetelus on ".$this->size." objekti");
		}
	}

	function set_index ($index) {
		# positsioneerib objekt, $index-i j�rgi
		# $index = 1 - objekt number 2 (0,1,2...)
		# $index = -1 - esimene objekt l�pust

		if ($index>=0 and $index<$this->size) {
			$this->debug->msg("aktiveerin ".($index+1).". objekti (koguarv=".$this->size.", index=$index)");
			$this->index = $index;
		} else if ($index<0 and abs($index)<=$this->size) {
			$this->debug->msg("aktiveerin ".($this->size+$index+1).". objekti (koguarv=".$this->size.", index=$index)");
			$this->index = $this->size+$index;
		} else {
			$this->debug->msg("vale index $index, loetelus on ".$this->size." objekti");
		}
	}

	function next() {
		# teeb samm edasi ja tagastab objekt
		if ($this->index+1 < $this->size) {
			++$this->index;
			return $this->get_current();
		} else {
			$this->debug->msg("objektid on otsas");
		};
	}

	function prev() {
		# teeb samm tagasi ja tagastab objekt
		if ($this->index-1 >=0) {
			--$this->index;
			return $this->get_current();
		} else {
			$this->debug->msg("objektid on otsas");
		};
	}
}


class Parents extends ObjektArray {	
# ---------------------------------------
# ahel antud objektist k�ige �lema objektini
#
# constructor new Parents(array(
#	"parent"		=> Objekt
# ));
# ---------------------------------------
#
#
	var $parent_id;
	var $meta;
	var $on_peida_vmenyy;
	var $aktiivne_id;

	function Parents () {
		$args = func_get_arg(0);
		$this->ObjektArray();
		$this->meta = Array();
		
		if (!strcasecmp(get_class($args["parent"]),"Objekt")) {
			# parent on objekt
			$this->parent_id = $args["parent"]->objekt_id;
			$this->debug->msg("Parents. Antud on objekt: parent_id = ".$this->parent_id);
		} elseif (preg_match("/^\d+$/",$args["parent"])) {
			# parent on objekti ID
			$this->parent_id = $args["parent"];
			$this->debug->msg("Parents. Antud on objekti ID: parent_id = ".$this->parent_id);
		} else {
			# parent on vigane
			$this->debug->msg("vigased algandmed: \"$args[parent]\" pole objekt ega objekti ID");
		}

		# merle h�mar kommentaar "lisa_objekt"-ile: mulle tundub, et see on vajalik situatsioonis, 
		# kus URL-i peal id puudub JA tegu on vana fiks. op-malliga: siis "on meil eriobjekt"
		# ja oleks vaja seda op-malli n�idata HOME rubriigi all ja seep�rast lisatakse parentsi l�ppu objekt HOME.

		if ($args["lisa_objekt"]) { 
			$this->add($args["lisa_objekt"]);
		}


		if ($this->parent_id) {
			$id = $this->parent_id;

			$this->debug->msg("Parents. Alguses parent_id = ".$this->parent_id);

			$idid = Array();

			# juhul kui HOME, on vaja �he sammu v�rra alla minna
			if ($args["on_esileht"]){
				$this->debug->msg("Antud HOME rubriik, h�ppame �he taseme v�rra alla");
				$alamlist = new Alamlist(array(
					"parent"	=> $id,
					"start"	=> 0,
					"limit"	=> 1,
					"klass"	=> 'rubriik', // #2525
					"order" => "objekt.kesk asc, sorteering DESC", # removed kesk(position), Bug #2418 // reversed
				));
				$this->debug->msg($alamlist->debug->get_msgs());
				if ($alamlist->size) {
					$obj = $alamlist->get(0);
					$id = $obj->objekt_id;
					$this->debug->msg("Uus ID = $id");
				} else {
					$this->debug->msg("Kahjuks alla h�pata ei saa... Pole sobivat kohta");
				}
			};

			$this->aktiivne_id=$id;

			#############################
			# allah�ppamine

			$this->debug->msg("JUMP: ================START================");

			$this->debug->msg("Jump down? ". ($this->site->in_editor || $this->site->in_admin ? "We are in editor-area or admin-area => abort mission" : "We are in public area => start mission"));

			# We are in public area => start missio:
			if ( !($this->site->in_editor || $this->site->in_admin) ) {

			do {
				$last_id = $id;

				$obj = new Objekt (array(
					"objekt_id" => $id,
				));
				$this->debug->msg($obj->debug->get_msgs());

				#####################
				# kui objekt on rubriik JA talle pole ei lehe- ega sisumalli m��ratud 
				# siis tuleb objekti auto avanemine ise otsustada.
				# variante on 2:

				if($obj->all[klass] == "rubriik" && 
					!$obj->all["page_ttyyp_id"] && !$obj->all["ttyyp_id"]) {

					$obj->all["on_auto_avanev"] = $this->site->master_tpl["on_auto_avanev"];
					$this->debug->msg("JUMP: Auto avanemise m��rab saidi p�himall (ID=".$this->site->master_tpl[ttyyp_id].")".$this->site->master_tpl["on_auto_avanev"]);
				}
				# kui objekt on rubriik JA talle pole malli m��ratud 
				#####################

				#####################
				# kui objektile pole m��ratud sisumalli aga on m��ratud lehemall,
				# siis v�tta autoavanemise v��rtus lehemalli k�ljest
				elseif($obj->all["page_ttyyp_id"] && !$obj->all["ttyyp_id"]) {
					$sql = $this->site->db->prepare("SELECT on_auto_avanev FROM templ_tyyp WHERE ttyyp_id=?",
						$obj->all["page_ttyyp_id"]
						);
					$sth = new SQL($sql);
					$obj->all["on_auto_avanev"] = $sth->fetchsingle();
					
				}

				$this->debug->msg("JUMP: Tulemus: objekt ".$obj->objekt_id. " ".($obj->all["on_auto_avanev"]? "ON" : "EI OLE")." auto avanev ");


				# juhul, kui malli on_auto_avanev = 1,
				# siis hakka pihta

				if ($obj->all["on_auto_avanev"]) {
			
					# kontrollime objektide olemasolu
					$alamlist = new Alamlist(array(
						"parent" => $obj->objekt_id,
#						 "asukoht"	=> "0,6",  #removed 19.03.03 by merle - doesnt work with SAPI
						"on_counter"=> 1,
						# changed classes 01.04.03 by merle - 
						# varem kontrolliti, et leiduks artikleid, n��d otsitakse k�iki v.a. rubriike (JA loginkaste - added 15.05.03 by merle)
						# - JA v.a Uudistekogu-sid (added 16.05.03 by merle)
						# - JA v.a Lingid (added 20.01.04 by merle) / kui men��s on lingid, peab olema k�ik OK
						"not_klass"		=> "rubriik,loginkast,kogumik,link",
						"order" => "sorteering DESC", # removed kesk(position), Bug #2418
					));
					$this->debug->msg($alamlist->debug->get_msgs());
					
					# alamlist on tyhi
					if ($alamlist->rows==0) {
						$this->debug->msg("JUMP: Otsime esimest alamrubriiki, kuhu v�iks h�pata");
						$alamlist = new Alamlist(array(
							"parent" => $obj->objekt_id,
							"start"		=> 0,
							"klass"		=> "rubriik",
							"not_tyyp_nimi"		=> "Lingikast", 
						));
						$this->debug->msg($alamlist->debug->get_msgs());
						# kui leiti alamrubriik vaata talle otsa ja p��a teda lisada
						if ($alamlist->rows>0) {
							$obj = $alamlist->next();
							$this->debug->msg($alamlist->debug->get_msgs());							
							# kui ei �nnestunud objekti korralikult k�tte saada (polnud �iguseid)
							# siis nendi fakti ja �ra h�ppa alla
							if(!$obj->objekt_id){
								$this->debug->msg("JUMP: Objekti ei tehtud, ei h�ppa alla");
							}
							# v�ib alla h�pata, sest objekt on tibens:
							else {
								# JUMP REALLY DOWN here: 
								$this->debug->msg("JUMP: H�ppasime alla ja n��d on aktiivne id = ".$obj->objekt_id);
								$id = $obj->objekt_id;
							} # kas objekt on vaatamiseks tibens
						} # kas leiti alamrubriike
						else {
							$this->debug->msg("JUMP: Ei h�ppa alla, sest polnud rubriiki, kuhu h�pata");
						}
					} else {
						$this->debug->msg("JUMP: Ei h�ppa alla, sest leiti alamobjekte!");
					
					}				
				}
			} while ($last_id !== $id);

			} # to jump or not to jump

			$this->aktiivne_id=$id;
			$this->debug->msg("JUMP: ================END================");

			# / allah�ppamine
			#############################

			#############################
			# loop over parents
			$first = 1;
			$i = 0;
			while ($id) {

			# hakkame antud objektist �lesse minna
				#######################
				# if current object, do extra checks:
				# 1) decide which parent to use from now on
				# 2) check if object's language matches with site language

				if($first || $i==1) { # if first or second (if we have sub-article as current object, Bug #1955)
					$obj_parent = ""; 

					# 1. ja 2. objekti p�ritakse 2 korda (pole ilus lahendus, hetkel h�davajadus):
					# 1. kord selleks, et teada saada tema klass
					$obj = new Objekt (array(
						"objekt_id" => $id,
						"no_cache" => 1,
					));
					#################
					# 1) if current object is article, then start searching parents (we have to find correct parent)
					if($obj->all["klass"] == "artikkel") {
						$this->debug->msg("Current object".($i==1?"'s parent":"")." is article. Start doing extra check.");
						# find all parent id-s of this object

						#####################
						# 1a. if found more than 1 parent => go on and find right parent
						if($obj->all['parents_count'] > 1) {

							$all_parents = $obj->get_obj_all_parents($obj->objekt_id);

							# v6ttame maha prygikasti rubriik parenti listist:
							if ($this->site->alias("trash")){
								if (in_array($this->site->alias("trash"), $all_parents)){
									$all_parents = array_diff($all_parents, Array($this->site->alias("trash")));
								};
							}
							$this->debug->msg("Object ".$obj->objekt_id." has ".sizeof($all_parents)." parents: ".join(",",$all_parents));

							# get cookie with previous page current section value
							#$cookie_parent = $this->site->sess_get("current_section");
							$cookie_parent = $_COOKIE["current_section"];						

							###################
							# parent_id in URL (Bug #538)
							# new feature: parameter "parent_id" in URL, it overrides "current_section" cookie settings

							if($this->site->fdat['parent_id']) { 
								$obj_parent = $this->site->fdat['parent_id'];
								$this->debug->msg("Parent found in URL. Parent set to: ".$obj_parent);
							}
							elseif(is_array($_SESSION['alias'])){
								#Alias being used and a direct path to the object is being shown. 

								foreach($_SESSION['alias'] as $cuuki){
									if($cuuki['objekt_id']==$obj->objekt_id){
										$obj_parent=$cuuki['parent_id'];
									}


								}


							}
							###################
							# if cookie has value, go on
							if($cookie_parent && $obj_parent == "") {
								$this->debug->msg("Found current_section cookie: ".$cookie_parent);
								# if 1 object parent is same as cookie, take this for parent
								if(in_array($cookie_parent,$all_parents)) {
									$obj_parent = $cookie_parent;
									$this->debug->msg("Parent set to:".$obj_parent);
								}
								# if not match, go 1 step up, and check 2nd level
								else {
									$this->debug->msg("Cookie doesn't match. Searching match from parents...");
									foreach($all_parents as $par) {
										# find all parents for parent
										$all_parents = $obj->get_obj_all_parents($par);

										# if 1 object parent is same as cookie, take this for parent
										if(in_array($cookie_parent,$all_parents)) {
			
											$obj_parent = $par;
											$this->debug->msg("Parent set to:".$obj_parent);
											break;
										}
									} # foreach
									if(!$obj_parent) {
										$this->debug->msg("No match found in parents. Parent not set.");
									}
								} # if not match
							}
							# / if parent_id / cookie has value
							###################
							###################
							# just pick first parent - we have no info which one to prefer
							else {
								$this->debug->msg("I have no idea, which parent to prefer => choosing just the first one");						
							}
							# / just pick first parent - we have no info which one to prefer
							###################
						}
						# / if found more than 1 parent, go on
						#####################

					}
					# / if current object is article, then start searching parents
					#################

					# 2) check if object's language matches with site language
					# bug #2398 : skip language check and automatic langchange for folders, files.
					# bug #2661 : Sisuobjekti detailvaate lingile lisatud ?lang=en parameeter peab alati m�juma (site classis v�etakse fdat->keel m�lemast parameetrist juba)

					if($obj->objekt_id && ! in_array($obj->all['tyyp_id'], array(21,22)) && ! isset($this->site->fdat['keel'])) {

						$this->debug->msg("Language check: current object (ID=".$obj->objekt_id.") language is: ".$obj->all[keel]."; site language is: ".$this->site->keel);
						
						# if they differ, change site language
						if($obj->all[keel] != $this->site->keel) {
							$this->site->change_keel($obj->all[keel]);
							$this->debug->msg("Site language set to: ".$obj->all[keel]);
						}

					}
					$no_cache = 1;
				}# if current object

				# if not current object:
				else { 
					$obj_parent = ""; 
					$no_cache = 0; 
				}
				# / if current object, decide which parent to use from now on
				#######################

				###################
				# create object
				$obj = new Objekt (array(
					"objekt_id" => $id,
					"parent_id" =>  $obj_parent,
					"no_cache" => $no_cache,
				));
				$this->debug->msg($obj->debug->get_msgs());

				# if creating object fails (because of wrong parent), do it without parent
				if(!$obj->objekt_id) {
					$obj = new Objekt (array(
						"objekt_id" => $id,
						"no_cache" => 1,
					));
					$this->debug->msg($obj->debug->get_msgs());
				}
				############# if creating object still fails then QUIT because PARENT IS FORBIDDEN
				if(!$obj->objekt_id) {
					$this->debug->msg("PARENTS: Kuna �ks parentitest on keelatud siis l�peta kogu t�� ja reseti parents");
					$this->list = Array();
					$this->objekts = Array();		
					$this->set_size();
					$this->index = -1;
					return;
				}

				############ IF OK
				elseif (!$idid["id".$id]) {

					# viimases rubriigis vaatame on_peida_vmenyy v��rtus
					if ($obj->all["klass"] == "rubriik" && !isset($on_peida_vmenyy)) {
						$obj->load_sisu();
						$on_peida_vmenyy = $obj->all["on_peida_vmenyy"];
						$this->debug->msg("Aktiivse rubriigi on_peida_vmenyy is: ".$obj->all["on_peida_vmenyy"]);
					}

					# lisame objekt
					$this->add($obj);
					
					# meta
					if ($this->meta["keywords"] == "" && $obj->all["meta_keywords"] != "") {
						$this->meta["keywords"] = $obj->all["meta_keywords"];
						$this->debug->msg("meta keyword = ".$this->meta["keywords"]);
					}
					if ($this->meta["description"] == "" && $obj->all["meta_description"] != "") {
						$this->meta["description"] = $obj->all["meta_description"];
						$this->debug->msg("meta description = ".$this->meta["description"]);
					}
					if ($this->meta["title"] == "" && $obj->all["meta_title"] != "") {
						$this->meta["title"] = $obj->all["meta_title"];
						$this->debug->msg("meta title = ".$this->meta["title"]);
					}

					$idid["id".$id]=1;

					############################
					# set next ID

					$id=$obj->parent_id;

					############################
					# set next ID exception: 18.05.03 by merle
					# force another parent for system article:

					# if object is system article, then dont proceed with its real parent (system section)
					# but force its parent to be first page in the site
					if($first && $obj->parent_id == $this->site->alias("system") && $obj->all["klass"] == "artikkel") {
						$this->debug->msg("Current object is system article: ".$obj->all[sys_alias]);
						$home_alamlist = new Alamlist(array(
							"parent"	=> $this->site->alias("rub_home_id"),
							"start"	=> 0,
							"limit"	=> 1,
							"tyyp"	=> 'rubriik',
						));
						if ($home_alamlist->size) {
							$home_obj = $home_alamlist->get(0);
							$id = $home_obj->objekt_id;
							$this->debug->msg("Because its system article, parent is forced to be: $id");
						} else {
							$this->debug->msg("Setting new parent for system article failed - not found any section");
						}
					}
					# force another parent for system article:
					############################

				} else {
					$idid["id".$id]=1;
					$id='';
				}
				$i++;
				$first = 0;

			} # while obj
			# / loop over parents
			####################

			$this->on_peida_vmenyy = $on_peida_vmenyy; #defined("on_peida_vmenyy") ? constant("on_peida_vmenyy"):0;
		} # if parent
	} # function Parents
} # class Parents







class AlamlistSQL extends BaasObjekt {
	var $from;
	var $where;
	var $group;
	var $select_adds;
	var $order;

	var $parent_id;
	var $meta;
	var $rows;
	var $asukoht;
	var $klass;

	function AlamlistSQL () {
		$args = func_get_arg(0);
		$this->BaasObjekt();
		$this->meta = Array();
		$this->debug->msg("asukoht=".$args["asukoht"]);
		
		$args["asukoht"] = (strcmp($args["asukoht"],'') ? $args["asukoht"] : $args["kesk"]);
		$this->klass = (strcmp($args["tyyp_nimi"],'') ? $args["tyyp_nimi"] : $args["klass"]);
		$this->not_klass = $args["not_klass"];
		$this->not_tyyp_nimi = $args["not_tyyp_nimi"];
		$this->asukoht = $args["asukoht"];
		$this->order = $args["order"] ? " ORDER BY ".$args["order"]." " : "";

		if (!strcasecmp(get_class($args["parent"]),"Objekt")) {
			# parent on objekt
			$this->parent_id = $args["parent"]->objekt_id;
		#} elseif (preg_match("/^\d+$/",$args["parent"])) {
		} else if ((is_numeric($args["parent"]) && $args["parent"]==0) || $args["parent"]) {
			# parent on objekti ID
			$this->parent_id = $args["parent"];
		} else {
			# parent on vigane
			$this->debug->msg("vigased algandmed: \"$args[parent]\" pole objekt ega objekti ID");
		}
		
		$this->debug->msg("parent_id=".$this->parent_id);

		$ary = array();
		$args["tyyp_nimi"] = trim($args["tyyp_nimi"]);

		# valmistame sql-i p�hiosa (v.a. select)
		/*
		# JOIN changed for optimize, 02.03.03 by merle
		$this->from = "FROM objekt 
				LEFT JOIN tyyp on objekt.tyyp_id = tyyp.tyyp_id 
				LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id ";
		*/
		# JOIN changed for optimize, leave out join with tyyp table
		/*
		$this->from = "FROM objekt_objekt 
				LEFT JOIN objekt on objekt.objekt_id=objekt_objekt.objekt_id 
				LEFT JOIN tyyp on objekt.tyyp_id = tyyp.tyyp_id ";
		*/
		############ FROM
		$this->from = "FROM objekt_objekt 
				LEFT JOIN objekt on objekt.objekt_id=objekt_objekt.objekt_id ";

		########### on_alampuu_kontroll => JOIN 
		if ($args["on_alampuu_kontroll"]) {
			# kontrollime alampuu olemasolu
			$this->debug->msg("Alampuu kontroll ON");

			$this->from .= " LEFT JOIN objekt_objekt as children on objekt.objekt_id=children.parent_id ";
			$this->from .= " LEFT JOIN objekt as children_objekt on children.objekt_id=children_objekt.objekt_id ";
			####### VIGA uue ver4 ACLiga - POOLELI::
			## added 1 || to get by
			# kui useril POLE �igust n�ha avaldamata objekte:
#				if (!$this->site->user->is_superuser) {
#					$this->select_adds = ", sum((children_objekt.tyyp_id=$args[on_alampuu_kontroll])*(children_objekt.on_avaldatud=1)) as on_alampuu ";
#				} 
			# k�si alati k�ik objektid (ka avaldamata),
			# sest n�itamise kontroll tehakse add() funktsioonis


			############ GROUP BY

			$this->select_adds = ", SUM(children_objekt.tyyp_id=$args[on_alampuu_kontroll]) AS on_alampuu ";

			$this->group = " GROUP BY objekt.objekt_id ";

		} ############ / on_alampuu_kontroll => JOIN 

		$this->debug->msg("FROM: ".$this->from);

			
		############ WHERE
		if(isset($this->parent_id))
		{
			$parents = explode(',', $this->parent_id);
			foreach($parents as $key => $parent)
			{
				$parents[$key] = "'".mysql_real_escape_string(trim($parent))."'";
			}
			$this->where = " WHERE objekt_objekt.parent_id IN (".implode(',', $parents).")";
		}
		else $this->where = 'WHERE 1';

		# kui pole toimetamiskeskkonnas, siis k�si vaid avaldatud objektid (Bug #2205);
		# objekti n�itamise kontroll tehakse add() funktsioonis hiljem niikuinii.
		if (!$this->site->in_editor && !$this->site->in_admin) { $this->where .= " AND objekt.on_avaldatud=1 "; }
					
		# klassid, $this->klass - comma separated list
		if (trim($this->klass)!=''){ 
			//no more tyyp table in query
			//$klass_arr = split(",",$this->klass);
			//$this->where .= " AND tyyp.klass IN('".join("','",$klass_arr)."') " ; 
			if(join(',', array_keys(array_intersect($this->site->object_tyyp_id_klass, split(',', $this->klass))))==''){
			$this->where .= " AND objekt.tyyp_id IN('') "; 
			}else{
			$this->where .= " AND objekt.tyyp_id IN(".join(',', array_keys(array_intersect($this->site->object_tyyp_id_klass, split(',', $this->klass)))).") "; 
			}
		}

		if (trim($this->not_klass)!=''){ 
			//no more tyyp table in query
			//$not_klass_arr = split(",",$this->not_klass);
			//$this->where .= " AND tyyp.klass NOT IN('".join("','",$not_klass_arr)."') " ; 
			
			$this->where .= " AND objekt.tyyp_id NOT IN(".join(',', array_keys(array_intersect($this->site->object_tyyp_id_klass, split(',', $this->not_klass)))).") "; 
		}

		if (trim($this->not_tyyp_nimi)!=''){ 
			//no more tyyp table in query
			//$not_tyyp_nimi_arr = split(",",$this->not_tyyp_nimi);
			//$this->where .= " AND tyyp.nimi NOT IN('".join("','",$not_tyyp_nimi_arr)."') " ; 
			
			$this->where .= " AND objekt.tyyp_id NOT IN(".join(',', array_keys(array_intersect($this->site->object_tyyp_id_nimi, split(',', $this->not_tyyp_nimi)))).") "; 
		}


		if (strcmp($args["asukoht"],'')) {
			$asukoht_arr = split(",",$args[asukoht]);
			$this->where .= " AND objekt.kesk IN('". join("','", $asukoht_arr) ."') ";
		}
		if ($args["max_vanus"]) {$this->where .= " AND objekt.aeg >= subdate(now(), INTERVAL $args[max_vanus]) ";}

		if($args[where]) {
			$this->where .= $this->add_where($args[where]);
		}			
		############ / WHERE

		############ additional FROM
		if($args[from]) {
			$this->from .= $this->add_from($args[from]);
		}

		$this->debug->msg("WHERE: ".$this->where);
		$this->debug->msg("GROUP: ".$this->group);
	}
	######## / function AlamlistSQL 
	
	function add_where($where) {
		$this->where .= " AND $where ";
	}

	function add_from($from) {
		$this->from .= " $from ";
	}

	function add_select($select) {
		$this->select_adds .= ", $select ";
	}

	function add_group($group) {
		$this->group .= " $group ";
	}

	function make_sql() {
		return $this->from." ".$this->where." ".$this->group;
	}
}

class Alamlist extends ObjektArray {
# ---------------------------------------
# antud objekti alamobjektide ahel
#
# constructor new Alamlist(array(
#	"parent"		=> Objekt v�i objekt_id *
#	"tyyp/klass"		=> "rubriik" 
#	kesk/"asukoht"=> 1
#	"start"		=> 10
#	"limit"		=> 5
#	"max_vanus"	=> 7 DAYS
#	"on_counter"	=> 1
#   "not_klass"	=> 
# ));
# * - vajalik parameeter
# ---------------------------------------
#
# 2do: kasutajate piirangud, s.h. objekti avalikus
#

	var $parent_id;
	var $meta;
	var $rows;
	var $asukoht;
	var $klass;

	var $sql;

	function Alamlist () {
		$args = func_get_arg(0);
		$this->ObjektArray();

		if (is_object($args["alamlistSQL"])) {
			$this->debug->msg("AlamlistSQL on antud");
			$this->sql = $args["alamlistSQL"];
		} else {
			$this->debug->msg("AlamlistSQLi pole - teen ise");
			$this->sql = new AlamlistSQL($args);
		}
		
		$this->parent_id = $this->sql->parent_id;
		$this->klass = $this->sql->klass;
		$this->asukoht = $this->sql->asukoht;
		$this->select_strip_fields = $args["select_strip_fields"];

		$this->debug->msg("klass = ".$this->klass. "; not_klass = ".$this->sql->not_klass. "; not_tyyp_nimi = ".$this->sql->not_tyyp_nimi);

		$this->parent_id = $this->sql->parent_id;

		if ($args["on_counter"]) {
			
			# bug #2174 workaround
			($this->site->in_editor ? 0 : $this->sql->add_where('objekt.on_avaldatud = 1'));
			
			# ainult loendmae palju objektid on
			$sql = $this->site->db->prepare("SELECT COUNT(objekt.objekt_id) ".$this->sql->make_sql());
			$sth = new SQL($sql);
			$this->rows = $sth->fetchsingle();
			$this->debug->msg($sth->debug->get_msgs());
			$this->debug->msg("Leitud ".$this->rows." alamobjekti");
		} else {

			# valmistame objektide loetelu 
			// tyyp case laused
			$case_statement_tyyp_tabel = $case_statement_tyyp_klass = 'case objekt.tyyp_id';
			foreach($this->site->object_classes as $object_class)
			{
				$case_statement_tyyp_tabel .= ' when '.$object_class['tyyp_id'].' then \''.$object_class['tabel'].'\'';
				$case_statement_tyyp_klass .= ' when '.$object_class['tyyp_id'].' then \''.$object_class['klass'].'\'';
			}
			$case_statement_tyyp_tabel .= ' end';
			$case_statement_tyyp_klass .= ' end';
			
			$sql = "
				SELECT objekt.objekt_id, objekt.pealkiri, objekt.on_pealkiri,
				objekt.on_avaldatud, objekt.keel, objekt.kesk,
				objekt.ttyyp_id, objekt.page_ttyyp_id, objekt.on_foorum, objekt.aeg, objekt.meta_keywords, objekt.friendly_url, objekt.meta_title,
				objekt.meta_description, objekt.sys_alias, objekt.ttyyp_params, objekt.author, objekt.is_hided_in_menu, objekt.last_modified,
				objekt.created_user_id, objekt.created_user_name, objekt.changed_user_id, objekt.changed_user_name, objekt.created_time, objekt.changed_time, 
				objekt_objekt.parent_id, objekt_objekt.sorteering, objekt.last_commented_time, objekt.comment_count, objekt.on_saadetud,
				objekt.tyyp_id, ".$case_statement_tyyp_tabel." as tabel, ".$case_statement_tyyp_klass." as klass ";
			if($this->select_strip_fields){ # used for search operations (dont include by default, optim.)
				$sql .= " ,objekt.pealkiri_strip, objekt.sisu_strip ";
			}
			$sql .= $this->sql->select_adds." ".$this->sql->make_sql();
			# grouping
			if (!$this->sql->group)
			{
				$sql .= " group by objekt_objekt.objekt_id ";				
			}
			# ORDER
			if ($this->sql->order) {
				$sql .= $this->sql->order;
			} else {
				$sql .= " ORDER BY objekt_objekt.sorteering DESC ";				
			}
			# LIMIT
			if (is_numeric(trim($args["start"])) && is_numeric(trim($args["limit"]))) { 
				$sql .= " LIMIT $args[start],$args[limit]"; 
			} elseif ( !is_numeric($args[start]) && is_numeric(trim($args["limit"])) ) {
				$sql .= " LIMIT $args[limit]"; 
			}
			$sth = new SQL($this->site->db->prepare($sql));
			$this->rows = $sth->rows; ### NB!
			$this->debug->msg("Leitud ".$this->rows." alamobjekti");
			while ($ary = $sth->fetch()) {
				$this->debug->msg("Objekt leitud: $ary[objekt_id]. ".$ary["pealkiri"]);

				$this->add(
					new Objekt(array(
						"ary" => $ary
					))
				);

			} # while 
			$this->debug->msg($sth->debug->get_msgs());
		} # if on_counter
	} # function Alamlist ()


	function edit_buttons () {
		$args = func_num_args()==1 ? func_get_arg(0) : func_get_args();

		$args["ainult_kui_tyhi"] = strcmp($args["ainult_kui_tyhi"],'') ? $args["ainult_kui_tyhi"]:1;
		
		if (!($args["ainult_kui_tyhi"] && $this->size)) {
			print $this->get_edit_buttons($args); 
		}
	}

	function get_edit_buttons () {
		$args = func_get_arg(0);

		# EDITOR-AREA CHECK: print buttons only for editor-area, else return nothing
		if(!$this->site->in_editor && !$this->site->in_admin && !$args['button_always_visble']) {
			return "";
		}

		$self = $this->site->safeURI;
		$parent_id = $this->parent_id;
		$kesk = $this->asukoht;
		$keel = is_numeric($args["keel"]) ? $args["keel"] : $this->site->keel;

		# get parent permissions
		$perm = get_obj_permission(array(
			"objekt_id" => $this->parent_id,
		));

		######### if user has C or U or P or D  permission (see also Bug #1985)
		# then show buttons, otherwise show nothing
		if( !($perm['C'] || $perm['U'] || $perm['P'] || $perm['D']) ) {
			return "";
		}

		######### nuppude v�rvid

		if (preg_match("/^\d+$/",$args[tyyp_idlist])) {
			$type = $args[tyyp_idlist];
		} else {
			$type = $this->klass;
		}

		$result='';

		$admpath = $this->site->CONF["wwwroot"].$this->site->CONF["adm_path"];
		$imgpath = $this->site->CONF["wwwroot"].$this->site->CONF["adm_img_path"];


		# juhul kui t��p on asset, siis PEAB alati kaasas olema ka profiili ID (muidu ei oma custom asset m�tet)
		# igal juhul lisada nupu urlile ka 'profile_id' (by merle 19.11.2004)
		if(stristr($args[tyyp_idlist], "20") ) {
			$profile = "&profile_id=".$args[profile_id];
		}
		# NEW nupp:
		if (!$args["only_edit"]) {
			
			if (!$args["peida_text"]) {
				# klassi nime pole => kirjuta 'uus'
				$sona = $this->site->sys_sona(array("sona" => "new", "tyyp"=>"editor")).'...';
				# kui ette ei antud komadega t��pide list vaid anti 1 klass, kirjuta ka klassi nimi
				if (!stristr($this->klass,',')) {
					$sona = $this->site->sys_sona(array('sona' => 'new', 'tyyp' => 'editor', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])).' '.strtolower($this->site->sys_sona(array('sona' => 'tyyp_'.$this->klass, 'tyyp' => 'System', 'lang_id' => $_SESSION['keel_admin']['glossary_id'])));
				}
			}

			if($perm['C']){
				
				// set different sizes for article editor and section editor
				// articles
				if($args['tyyp_idlist']=="2") $new_object_popupsize = "880,660";
				// edit section
				else if($args['tyyp_idlist']=="1" && id) $new_object_popupsize = "512,201";
				// new section
				else if($args['tyyp_idlist']=="1" && !id) $new_object_popupsize = "512,182";
				//default
				else $new_object_popupsize = "450,430";				
				
				$result = '<a class="scms_new_object" href="javascript:void(0);" onclick="javascript:avaaken(\''.(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$this->site->CONF['hostname'].$this->site->CONF['wwwroot'].'/admin/edit.php?op=new&keel='.$keel.'&parent_id='.$parent_id.'&kesk='.$kesk.'&ttyyp_id='.$ttyyp_id.'&tyyp_idlist='.$args['tyyp_idlist'].'&profile_id='.$args[profile_id].'&publish='.$args['publish'].($args['tyyp_idlist'] == 2 ? '&allow_comments='.$args['allow_comments'] : '').($args['sys_alias'] ? '&sys_alias='.$args['sys_alias'] : '').'\','.$new_object_popupsize.')">'.$sona.'</a>';
			}

		}
		return $result;
	}
}
