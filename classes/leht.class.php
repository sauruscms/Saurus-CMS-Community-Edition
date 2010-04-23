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



class Leht extends BaasObjekt {
# ---------------------------------------
# koostab lehek�lg ID j�rgi
#
# constructor new Leht (array(
#	"id"	=> 234234
# ));
# ---------------------------------------

	var $id;
	var $parents;
	var $meta;
	var $logo;
	var $footer;
	var $topmeny;
	var $on_esileht;
	var $eritemplate;
	var $template;
	
	var $objekt; # see on objekt, mis on avatud, tavaliselt antud ID-ga

	var $fdat;

	function Leht() {

		$args = func_get_arg(0);
		$this->BaasObjekt();
		$this->id = $args[id];
		$fdat = Array();

		# Get full path to the class folder. added by Dima 19.03.2004
		$path_parts = pathinfo($_SERVER["SCRIPT_FILENAME"]);
		$class_path = $path_parts["dirname"];

		# bugfix #1393, by merle 15.10.2004
		# if path ends with "/editor", cut it off
		if(substr($path_parts["dirname"],-7) == '/editor'){
			$class_path = substr($path_parts["dirname"],0,-7);
		}
		# if path ends with "/admin", cut it off
		if(substr($path_parts["dirname"],-6) == '/admin'){
			$class_path = substr($path_parts["dirname"],0,-6);
		}
		$class_path .= "/classes/";

		//testing:
		//require_once($class_path."auto.inc.php");
		//auto_error_notifications(1);
		//auto_maillist(0, 0, 0 , 1);
		#################################################
		# run mailinglist in CONF[maillist_interval] hour
		if ( $this->site->CONF['next_mailinglist']<time() && $this->site->CONF['maillist_interval'] && $this->site->CONF['enable_mailing_list'] ) {
			# set next run
			$sql = $this->site->db->prepare("
				update config set sisu = ? where nimi='next_mailinglist'",
				(time()+(intval($this->site->CONF['maillist_interval'])*3600))
			);
			$sth = new SQL($sql);
			$this->site->debug->msg($sth->debug->get_msgs());

			require_once($class_path."auto.inc.php");
			auto_maillist(0, 0, 0 , 1);
		}
		########################
		# run in every 10 minutes
		if ($this->site->CONF['next_10min']<time()) {
			# set next run
			$sql = $this->site->db->prepare("update config set sisu = ? where nimi='next_10min'",time()+600);
			$sth = new SQL($sql);
			$this->site->debug->msg($sth->debug->get_msgs());			

			require_once($class_path."auto.inc.php");
			auto_publishing(1);
		}

		########################
		# run in every hour
		if ($this->site->CONF['next_hour']<time()) {
			# set next run
			$sql = $this->site->db->prepare("update config set sisu = ? where nimi='next_hour'",time()+3600);
			$sth = new SQL($sql);
			$this->site->debug->msg($sth->debug->get_msgs());			

			require_once($class_path."auto.inc.php");

			# delete from cache old content:
			if (is_numeric($this->site->CONF['cache_expired']))
			{			
				## delete cache by interval
				$cache_expired = time() + $this->site->CONF['cache_expired']*60*60; # now + interval in seconds
				$sql = $this->site->db->prepare("DELETE FROM cache WHERE aeg < ".$this->site->db->unix2db_datetime($cache_expired)." AND objekt_id != ?", 0);
				$sth = new SQL($sql);
				$this->site->debug->msg($sth->debug->get_msgs());
			}

			// error notifications, only if setting is marked as pageload
			if($this->site->CONF['send_error_notifiations_setting'] == 1)
			{
				auto_error_notifications(1);
			}

		}
		
		#################################################
		# We don't want to dublicate code, so will write it here:
		if ($this->site->admin && $this->site->fdat['empty_recycle_bin']){
			$do_empty = 1;
		}


		#################################################
		# run every day
		if ($this->site->CONF['next_day']<time() || $do_empty) {

			if (!$do_empty){
				# set next run
				$sql = $this->site->db->prepare("update config set sisu = ? where nimi='next_day'",time()+86400);
				$sth = new SQL($sql);
				$this->site->debug->msg($sth->debug->get_msgs());
			}


			#################################################
			# empty Recycle Bin
			if ($this->site->CONF['trash_expires'] || $do_empty) {

				$sql = "SELECT keel_id FROM keel WHERE on_kasutusel = '1'";
				$sth503 = new SQL($sql);
				$this->site->debug->msg($sth503->debug->get_msgs());

			while ($tmp_keel = $sth503->fetch()){
				$trash_id = $this->site->alias(array('key' => 'trash', 'keel'=>$tmp_keel['keel_id']));
				if ($trash_id){
				
				# find objects which changed_time + trash.expires.in.days < NOW (Bug #2602)
				$sql502 = $this->site->db->prepare("SELECT objekt_objekt.* FROM objekt_objekt LEFT JOIN objekt ON objekt.objekt_id=objekt_objekt.objekt_id WHERE objekt_objekt.parent_id=? AND  DATE_ADD(objekt.changed_time,INTERVAL ? DAY) < NOW() ", $trash_id, $this->site->CONF['trash_expires']);
				$sth502 = new SQL($sql502);
				
				$this->site->debug->msg($sth502->debug->get_msgs());
				while ($ttmp = $sth502->fetch()){

					$this->site->debug->msg('leht.class.php : Trying to remove object '.$ttmp['objekt_id'].' from Recycle Bin...');

					$del_objekt = new Objekt(array(
						'objekt_id' => $ttmp['objekt_id'],
						'superuser' => 1
					));

					if ($del_objekt->objekt_id && $del_objekt->parent_id==$trash_id){
					
						$del_objekt->del();
						new Log(array(
							'action' => 'delete',
							'component' => 'Recycle bin',
							'objekt_id' => $del_objekt->objekt_id,
							'user_id' => 0,
							'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($del_objekt->all['klass'])), $del_objekt->pealkiri(), $del_objekt->objekt_id, " removed from Recycle Bin "),
						));
					} else {
						new Log(array(
							'action' => 'delete',
							'component' => 'Recycle bin',
							'objekt_id' => $del_objekt->objekt_id,
							'user_id' => 0,
							'type' => 'ERROR',
							'message' => "Couldn't remove object ID = '".$ttmp['objekt_id']."' from Recycle Bin (Parent ID of this object ='".$del_objekt->parent_id."')",
						));
					}

				}
				}
			}

			}
			# / empty Recycle Bin
			#################################################
			
			#################################################
			# lock inactive users
			
			$this->site->CONF['lock_inactive_user_after_x_days'] = (int)$this->site->CONF['lock_inactive_user_after_x_days'];
			 
			if($this->site->CONF['lock_inactive_user_after_x_days'])
			{
				$sql = 'select user_id, username, last_access_time, is_predefined, firstname, lastname, username from users where is_locked = 0 and date_sub(curdate(), interval '.$this->site->CONF['lock_inactive_user_after_x_days'].' day) > last_access_time';
				$result = new SQL($sql);
				while($row = $result->fetch('ASSOC'))
				{
					// dont lock the last supersuser
					if($row['is_predefined'] == 1)
					{
						$sql = 'select user_id from users where user_id <> '.$row['user_id'].' and is_predefined = 1 and is_locked = 0 limit 1';
						$_result = new SQL($sql);
						if($_result->rows)
						{
							$lockuser = new user(array(
								'user_id' => $row['user_id'], 
								'skip_last_access_time_update' => 1, 
							));
							$lockuser->lock('Superuser '.htmlspecialchars(xss_clean($row['firstname'])).' '.htmlspecialchars(xss_clean($row['lastname'])).' ('.htmlspecialchars(xss_clean($row['username'])).') locked due to inactivity. Last access time: '.($row['last_access_time'] != '0000-00-00 00:00:00' ? date('d.m.Y h:i', strtotime($row['last_access_time'])) : 'never'), 0);
						}
					}
					else 
					{
						$lockuser = new user(array(
							'user_id' => $row['user_id'], 
							'skip_last_access_time_update' => 1, 
						));
						$lockuser->lock('User '.htmlspecialchars(xss_clean($row['firstname'])).' '.htmlspecialchars(xss_clean($row['lastname'])).' ('.htmlspecialchars(xss_clean($row['username'])).') locked due to inactivity. Last access time: '.($row['last_access_time'] != '0000-00-00 00:00:00' ? date('d.m.Y h:i', strtotime($row['last_access_time'])) : 'never'), 0);
					}
				}
			}
			
			# / lock inactive users
			#################################################

		}
		# / run every day
		#################################################

		########################
		# run every week - for alive site statistics (can be turned off from config.php by defining: disable_site_polling = 1)
		if ($this->site->CONF['next_week'] < time() && !$this->site->CONF['disable_site_polling'])
		{
			# set next week run
			$sql = $this->site->db->prepare("update config set sisu = ? where nimi='next_week'",time()+604800);
			$sth = new SQL($sql);
			$this->site->debug->msg($sth->debug->get_msgs());		
			
			$accessed_by = 1; # "CMS weekly"
			$latest_ver = $this->site->site_polling($accessed_by);
		}

		########################
		# eriobjekt: op=...

		$eriobjekt = array(
			"objekt_id" => $this->site->alias("rub_home_id"),
			"parent_id" => $this->site->alias("rub_home_id"),
			"on_avaldatud" => 1,
		);

		if (isset($this->site->fdat['otsi']) && !$this->site->fdat['op'])
		{ # Bug #1828: even if empty parameter "otsi" set in URL => go to search results page
			
			$sql = "select * from templ_tyyp where op='search'";
			$sth = new SQL($sql);
			$this->debug->msg($sth->debug->get_msgs());
			if ($this->template = $sth->fetch())
			{
				$this->eritemplate = $this->template['templ_fail'];
			}
			/*
			$this->eritemplate = "templ_searchres.php";
			$eriobjekt[pealkiri] = $this->site->sys_sona(array("sona" => 'Otsing', "tyyp"=>"kujundus"));
			*/
		} elseif ($this->site->fdat['op']) {
			#  op v�ib olla ka mitme v��rtusega - nt "cart,saveorder"
			$sql = "SELECT * FROM templ_tyyp WHERE op IN('".str_replace(",","','", mysql_real_escape_string($this->site->fdat[op]))."')";
			$sth = new SQL($sql);
			$this->debug->msg($sth->debug->get_msgs());
			if ($this->template = $sth->fetch()) {
				$this->eritemplate = $this->template[templ_fail];
				# eriobjekt ehk vana fiks.op-mall (millel pole �ldse aimu, mis tema parent on)
				# on siis kui URL-il pole id-d antud (fixing Bug #1962,#1924)
				if(!$this->site->fdat['id']) {
					$eriobjekt[pealkiri] = $this->site->sys_sona(array("sona" => $this->template[nimi], "tyyp"=>"kujundus"));
				}
			} # found op-template
		} # op
		
		########################
		# kui id = home id-ga, siis h�pata alla

		# condition "!$this->eritemplate" removed in 3.1.24 by bugfix #486
		#	if (!$this->eritemplate && $this->id == $this->site->alias("rub_home_id")) {

		if ($this->id == $this->site->alias("rub_home_id")) {

			$this->on_esileht = 1;
		}

		
		if (preg_match("/^\d+$/",$this->id)) {

			####### PARENTS
			$this->parents = new vParents(array(
				"parent"	=> $this->id,
				"on_esileht"	=> $this->on_esileht,
				"lisa_objekt" => $eriobjekt[pealkiri] && !$this->on_esileht ? new Objekt(array("ary" => $eriobjekt)) : 0,
				"on_custom" => 0,
			));
			if($this->parents->denied) {
#				$this->parents ;
			}
			$this->id = $this->parents->aktiivne_id;
			$this->objekt = $this->parents->get(0);

			$this->meta = &$this->parents->meta;
			$this->debug->msg($this->parents->debug->get_msgs());		

		} else { # if id
			$this->debug->msg("Vale ID $args[id]");
		} # if id
	}
	# / FUNCTION Leht
	##########################
}
# class Leht