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


########################################
# All automated functions,
# called via cron job or page load
#
# function auto_maillist()
# function auto_publishing()
# 
########################################

	
########################################
# funktsioon auto_maillist
########################################
# author: merle
# description: automaatse meilinglisti saatmiseks
# Tagastab: -

function auto_maillist($output_visible, $test_run, $is_custom=0, $is_pageloaded = 0) {

	global $class_path;
	global $site;
	
	$exec_timer = new Timer();

	# ignore 'STOP' button 
	ignore_user_abort(true); 

	# set script execution time to CONF["php_max_execution_time"] min only if general value is smaller
	if ( intval(ini_get('max_execution_time')) < intval($site->CONF["php_max_execution_time"]) ) {
		if (ini_get('safe_mode')!=true){
			set_time_limit ( intval($site->CONF["php_max_execution_time"]) );
		}
	}

	
	# sets the maximum amount of memory in CONF["php_memory_limit"] Mbytes 
	# that a script is allowed to allocate
	# if general value is smaller
	if ( intval(ini_get('memory_limit')) < intval($site->CONF["php_memory_limit"]) ) {
		ini_set ( "memory_limit", $site->CONF["php_memory_limit"]."M" );
	}

	# et hoida siinset funktsiooni väikesena,
	# includitakse class ainult siis kui toimub väljakutse
	require_once $class_path."mail.class.php";

	$meilirub = array();
	$artiklid = array();
	$mailing_list = array();
	//$url = $site->CONF["hostname"].$site->CONF["wwwroot"]."/";
	$url = $site->db_hostname.$site->db_wwwroot.'/'; /* bug #2285 force the using of DB values */
	
	//$file_path = $site->CONF["protocol"].$site->CONF["hostname"].$site->CONF["wwwroot"].$site->CONF["file_path"];
	$file_path = $site->CONF["protocol"].$site->db_hostname.$site->db_wwwroot;

	$sql_keel = "select keel_id,encoding,extension, glossary_id from keel where on_kasutusel = '1'";
	$sth_keel = new SQL($sql_keel);
	$site->debug->msg($sth_keel->debug->get_msgs());
	while ($keel = $sth_keel->fetch()) {
	
		$section_headlines = array();
		$meilirub = array();
		$artiklid = array();
		$meilid = array();
		$msg = array();
		$rubmsg = array();
		$subjects = array();
		$subject = array();
		$clear_to = array();
		$rubmsg_array = array();
		$subjects_array = array();
		##########################
		# leida rubriigid, millel on meililisti linnuke püsti
		$sql =  $site->db->prepare("
			SELECT obj_rubriik.objekt_id, objekt.pealkiri
			FROM obj_rubriik
			LEFT JOIN objekt ON obj_rubriik.objekt_id=objekt.objekt_id
			WHERE obj_rubriik.on_meilinglist='1'
			AND objekt.keel=?",
			$keel['keel_id']
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		############
		# tsükkel üle rubriikide, panna id-d massiivi
		while ($rub = $sth->fetch()) {
			$mailing_list['section_headlines'][$rub['objekt_id']] = $section_headlines[$rub['objekt_id']] = $rub['pealkiri'];
			$meilirub[] = $rub['objekt_id'];
			
			$list[$rub['objekt_id']]['title'] = $rub['pealkiri'];
		}

		##########################
		# 1. ARTIKLID 
		# leida kõik meilirubriikide alamartiklid, mis on avaldatud ja meililistiga saatmata
		if (count($meilirub)) {
			$sql = $site->db->prepare("
				SELECT objekt.objekt_id, objekt.pealkiri, objekt.aeg AS date, obj_artikkel.lyhi, obj_artikkel.sisu, objekt_objekt.parent_id, objekt.created_user_name
				FROM objekt
				LEFT JOIN objekt_objekt ON objekt_objekt.objekt_id=objekt.objekt_id
				LEFT JOIN obj_artikkel ON obj_artikkel.objekt_id=objekt.objekt_id
				WHERE objekt_objekt.parent_id IN (".join(',',$meilirub).")
				AND objekt.on_saadetud='0'
				AND objekt.on_avaldatud='1'
				AND objekt.tyyp_id=2
				AND objekt.aeg>? 
				ORDER BY objekt_objekt.sorteering DESC",
				$site->db->ee_MySQL($site->CONF['maillist_send_newer_than'])
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			############
			# tsükkel üle saatmist vajavate artiklite
			while ($art = $sth->fetch()) {

				$art['date'] = $site->db->MySQL_date($art['date']);
				$artiklid[] = $art['objekt_id'];

				$msg[$art['objekt_id']] = "<p><b>".($site->CONF['maillist_article_title'] ? $site->sys_sona(array(sona => "Aeg", tyyp=>"kasutaja", lang_id=>$keel['glossary_id']))." ".$art['date'] : $art['pealkiri'])."</b></p>";
				if ($art['lyhi']) {
					$art['lyhi'] = preg_replace("/[\n\s\r]+$/","",$art['lyhi']);
					$art['lyhi'] = trim($art['lyhi']);
					$art['lyhi'] = str_replace("##saurus649code##", $file_path, $art['lyhi']);
					# commented out, bug #423:
					# $art[lyhi] = preg_replace("/\<.*?\>/","",$art[lyhi]);
				} 
				if ($art['sisu']) {
					$art['sisu'] = preg_replace("/[\n\s\r]+$/","",$art['sisu']);
					$art['sisu'] = trim($art['sisu']);
					$art['sisu'] = str_replace("##saurus649code##", $file_path, $art['sisu']);
				}
				if ($site->CONF['maillist_article_content']) {
					if ($art['lyhi']) {
						$msg[$art['objekt_id']] .= '<p>'.$art['lyhi'].'</p>';
					}
					if ($art['sisu']) {
						$msg[$art['objekt_id']] .= '<p>'.$art['sisu'].'</p>';
					}
				} else {
					$msg[$art['objekt_id']] .= '<p>'.$art['lyhi'].'</p>';
					$link = $site->CONF['protocol'].$url."?id=".$art['objekt_id'];
					$msg[$art['objekt_id']] .= '<p>'.$site->sys_sona(array(sona => "Loe edasi", tyyp=>"kujundus", lang_id=>$keel['glossary_id'])).": <a href=\"".$link."\">".$link."</a>".'</p>';
				}
				
				#$msg[$art['objekt_id']] .= "<br>";
				$rubmsg[$art['parent_id']] .= $msg[$art['objekt_id']];
				
				$list[$art['parent_id']]['articles'][$art['objekt_id']]['title'] = $art['pealkiri'];
				$list[$art['parent_id']]['articles'][$art['objekt_id']]['content'] = $msg[$art['objekt_id']];

				$rubmsg_array[$art['parent_id']][$art['objekt_id']] = $msg[$art['objekt_id']];
				$subjects_array[$art['parent_id']][$art['objekt_id']] = $art['pealkiri'];

				if ($site->CONF["maillist_subject"] == 1) {
					$subjects[$art['parent_id']] = $section_headlines[$art['parent_id']].": ".$art['pealkiri'];
				} elseif ($site->CONF["maillist_subject"] == 2) {
					$subjects[$art['parent_id']] = $section_headlines[$art['parent_id']];
				} elseif ($site->CONF["maillist_subject"] == 3) {
					$subjects[$art['parent_id']] = $art['pealkiri'];
				} else {
					$subjects[$art['parent_id']] = $site->sys_sona(array(sona => "Mailinglists: e-mail subject", tyyp=>"kasutaja", lang_id=>$keel['glossary_id']));
					$subjects[$art['parent_id']] = str_replace("[Website name]",$site->CONF["site_name"], $subjects[$art['parent_id']]);
				}

				### additional values:
				$created_user_names[$art['objekt_id']] = $art['created_user_name'];
				$titles[$art['objekt_id']] = $art['pealkiri'];
			} # while art
		}
		# / 1. ARTIKLID
		######################

		##########################
		# 2. ASSETID
		# leida kõik meilirubriikide custom assetid (eriobjektid), 
		# mis on avaldatud ja meililistiga saatmata

		if (count($meilirub)) {
			$sql = $site->db->prepare("
				SELECT objekt.objekt_id, objekt.pealkiri, objekt.aeg AS date, obj_asset.*, objekt_objekt.parent_id
				FROM objekt
				LEFT JOIN objekt_objekt ON objekt_objekt.objekt_id=objekt.objekt_id
				LEFT JOIN obj_asset ON obj_asset.objekt_id=objekt.objekt_id
				WHERE objekt_objekt.parent_id IN (".join(',',$meilirub).")
				AND objekt.on_saadetud='0'
				AND objekt.on_avaldatud='1'
				AND objekt.tyyp_id=20
				AND objekt.aeg>? 
				ORDER BY objekt_objekt.sorteering DESC",
				$site->db->ee_MySQL($site->CONF['maillist_send_newer_than'])
			);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());

			############
			# tsükkel üle saatmist vajavate assetite
			while ($art = $sth->fetch()) {
				
				$artiklid[] = $art['objekt_id'];
				$msg[$art['objekt_id']] = "<b>".($site->CONF['maillist_article_title'] ? $site->sys_sona(array(sona => "Aeg", tyyp=>"kasutaja", lang_id=>$keel['glossary_id']))." ".$art['date'] : $art['pealkiri'])."</b><br><br>";
				#########
				# profile data
				$profile_data = Array();
				$selectlist = array();
				$need_change_names = array();
				$asset_names = array();

				$profile = $site->get_profile(array(id=>$art['profile_id']));

				# sanity check: kui ei leitud sellise id-ga profiili, siis ära edasi mine
				# (siis e-maili saadetakse vaid asseti pealkiri)
				if($profile[profile_id]) {
					# attributes
					if($profile_def = unserialize($profile[data])) {				
						foreach($profile_def as $key => $data) {
							$profile_data[$data[name]] = $art[$key];
							##############
							# save selectlists for later
							# here values are asset object ID-s
							if($data[type]=='SELECT' || $data[type]=='MULTIPLE SELECT' || $data[type]=='RADIO') {
								# add only if value is not empty
								if($art[$key]) {
									# value can be comma-separated list of ID-s, split it
									$values = split(",",$art[$key]);
									foreach($values as $value){
										$selectlist[] = $value;
									}
									# remember object: values must be changed later
									$need_change_names[] = $data[name];
								} # if value not epmty
							} # if select				
						} # foeach profile def row
						###################
						# get selectlist values - 1 extra sql per function; sql is fast
						if(is_array($selectlist)) {
							$selectlist = array_unique($selectlist);
						}
						#echo printr($selectlist);
						if( sizeof($selectlist)>0 ) {
							###############
							# get names of asset objects
							$sql2 = $site->db->prepare("SELECT objekt.pealkiri,objekt.objekt_id	FROM objekt WHERE FIND_IN_SET(objekt.objekt_id,?)",join(",",$selectlist)	);
							$sth2 = new SQL ($sql2);	
							while($tmp_names = $sth2->fetch()) {
								$asset_names[$tmp_names[objekt_id]] = $tmp_names[pealkiri];
							}
							########## loop over existing data
							foreach($profile_data as $name=>$data){
								# if this data type needs changing
								if(in_array($name,$need_change_names)){
									# change attribute from asset ID => asset NAME
									# value can be comma-separated list of ID-s, split it
									$ids = split(",",$data);
									$new_value = $data;
									foreach($ids as $id){
										$new_value = str_replace($id,$asset_names[$id],$new_value);
									}
									$profile_data[$name] = $new_value;
								}
							}
							#echo printr($profile_data);
						}
					} # attributes

					########## loop over data
					foreach($profile_data as $name=>$data){
						$msg[$art['objekt_id']] .= $site->sys_sona(array(sona => $name, tyyp=>"custom", lang_id=>$keel['glossary_id'])).": ".$data."<br>";
					}
					$msg[$art['objekt_id']] .= "<br>";

				} # if profile exists
				# / profile data
				#########

				# assetitel panna lingiks parenti e rubriigi ID
				$link = $site->CONF['protocol'].$url."?id=".$art['parent_id'];

				$msg[$art['objekt_id']] .= $site->sys_sona(array(sona => "Loe edasi", tyyp=>"kujundus", lang_id=>$keel['glossary_id'])).": <a href=\"".$link."\">".$link."</a><br>";
				$msg[$art['objekt_id']] .= "<br><br><br>";
				$rubmsg[$art['parent_id']] .= $msg[$art['objekt_id']];

				$rubmsg_array[$art['parent_id']][$art['objekt_id']] = $msg[$art['objekt_id']];
				$subjects_array[$art['parent_id']][$art['objekt_id']] = $art['pealkiri'];

				if ($site->CONF["maillist_subject"] == 1) {
					$subjects[$art['parent_id']] = $section_headlines[$art['parent_id']].": ".$art['pealkiri'];
				} elseif ($site->CONF["maillist_subject"] == 2) {
					$subjects[$art['parent_id']] = $section_headlines[$art['parent_id']];
				} elseif ($site->CONF["maillist_subject"] == 3) {
					$subjects[$art['parent_id']] = $section_headlines[$art['parent_id']];
				} else {
					$subjects[$art['parent_id']] = $site->sys_sona(array(sona => "Mailinglists: e-mail subject", tyyp=>"kasutaja", lang_id=>$keel['glossary_id']));
					$subjects[$art['parent_id']] = str_replace("[Website name]",$site->CONF["site_name"], $subjects[$art['parent_id']]);
				}
			}
		}
		# / 2. ASSETID
		######################

		######################
		# meili üldine osa

		if ($site->CONF["maillist_header"]) {
			$header = str_replace("\n", "<br>", $site->CONF["maillist_header"]);
		} else {
			$header = "<br>";
		}
		
		if ($site->CONF["maillist_footer"]) {
			$footer = str_replace("\n", "<br>", $site->CONF["maillist_footer"]);
		} else {
			$footer = "<br>____________________________________<br>".$site->CONF["site_name"]."<br".$site->CONF['protocol'].$url;
		}


		if ($site->CONF["maillist_sender_address"]) {
			$from = $site->CONF["maillist_sender_address"];
		} else {
			$from = $site->CONF["from_email"];
		}
		
		//printr($list);
		
		######################
		# leia kasutajate nimed ja e-mailid, kes on meilinglisti tellinud

		$sql = "SELECT users.user_id, users.firstname,users.lastname, users.email, user_mailinglist.objekt_id
		FROM user_mailinglist
			LEFT JOIN users ON users.user_id = user_mailinglist.user_id
		WHERE user_mailinglist.user_id<>0 AND (users.is_locked='0' OR ISNULL(users.is_locked)) AND users.email<>''
		";
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());

		######################
		# tsükkel üle kasutajate
		$subscribed = array();
		while ($kasutaja = $sth->fetch()) {

			$to = $kasutaja['lastname'].' '.$kasutaja['firstname'].' <'.$kasutaja['email'].'>';
			// subscribed sections
			$subscribed[$to][] = $kasutaja['objekt_id'];
			################### 1. STANDARD mailinglist
			if (!$is_custom) {

				$meilid[$to] .= $rubmsg[$kasutaja['objekt_id']];
				if ($subjects[$kasutaja['objekt_id']]) {
					$subject[$to] = $subjects[$kasutaja['objekt_id']];
				}
				$clear_to[$to] = $kasutaja['email'];
			}

			################### 2. CUSTOM mailinglist (GOD, it must be done better. several clients already using.)
			else {




			#### E-MAIL
				$to = $kasutaja['lastname']." ".$kasutaja['firstname']." <".$kasutaja['email'].">";
				if ($is_custom) {
					if (is_array($subjects_array[$kasutaja['objekt_id']])) {
						foreach ($subjects_array[$kasutaja['objekt_id']] as $art_id => $art_subject) {
							$art_content = $rubmsg_array[$kasutaja['objekt_id']][$art_id];
							$mailing_list['email'][$kasutaja['objekt_id']]['articles'][$art_id] = array(
								'subject' => $art_subject,
								'message' => $art_content,
								'created_user_name' => $created_user_names[$art_id],
								'title' => $titles[$art_id]
							);
							$mailing_list['email'][$kasutaja['objekt_id']]['users'][$kasutaja['user_id']] = array(
								'to' => $to,
								'email' => $kasutaja['email'],
							);
							$mailing_list['email'][$kasutaja['objekt_id']]['headline'] = $section_headlines[$kasutaja['objekt_id']];
							$mailing_list['email'][$kasutaja['objekt_id']]['encoding'] = $keel['encoding'];
						}
					}
				} else { # standard mailinglist
					if ($rubmsg[$kasutaja['objekt_id']]) {
						$meilid[$to] .= $rubmsg[$kasutaja['objekt_id']];
						$clear_to[$to] = $kasutaja['email'];
					}
					if ($subjects[$kasutaja['objekt_id']]) {
						$subject[$to] = $subjects[$kasutaja['objekt_id']];
					}
				}

			}
			############ / 2. CUSTOM mailinglist

		} # while kasutaja

		$report_message = "<hr size=1>Sending date: ".date("d.m.Y H:i:s");
		$report_message .= "<hr size=1>Server IP: ".getenv ("SERVER_ADDR")."; Remote IP: ".getenv ("REMOTE_ADDR");
		$report_message .= "<hr size=1>Articles/assets in the list: ".join(',',$artiklid)."<hr size=1>";
		$report_message .= "Next mailing list: ".date("d.m.Y H:i",$site->CONF['next_mailinglist'])."<hr size=1>";
		$report_message .= "Will send articles/assets newer than: ".$site->CONF['maillist_send_newer_than']."<hr size=1>";

		if (!$test_run) {
			######################
			# panna artiklitel "on_saadetud" linnuke püsti
			if (count($artiklid)) {
				$sql = "UPDATE objekt SET on_saadetud='1' WHERE objekt_id IN (".join(',',$artiklid).")";
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());
			}
		}

		#######################################################
		# Test-run shows, which letters will be sent: 
		if (count($rubmsg)){

			$testrun_msg_arr2 = Array();
			$testrun_msg_arr = Array();
			foreach($meilid as $to => $body) {
				if ($to!='' && $body!='') {
					$testrun_msg_arr2[$body] = 1;
				}
			}
			unset($to); unset($body);

			$msg_cnt=1;			
			foreach ($testrun_msg_arr2 as $key => $value){
					$testrun_msg_arr[$key] = $msg_cnt;
				$report_message .= "<table border=1 width=700 cellpadding=0 cellspacing=0><tr><td bgcolor=#CCCCCC valign=middle align=center><b>Message[".$msg_cnt."]</b></td></tr><tr><td><font size=2>".$header.$key.$footer."</font></td></tr></table><br><br>";
				#$testrun_msg_arr[$msg_parent_id] = $msg_cnt;

				$msg_cnt++;
			}
		}



		######################
		# meilide saatmine

		$mail_ok_count = 0;
		$mail_error_count = 0;
		$mail_count = 0;
		if(sizeof($meilid) > 0) {
			$report_message .= "<b>Send news to:<br></b>";
			foreach($meilid as $to => $body) {
				$send_status = 0;

				if ($to!='' && $body!='') {

					$send_status = check_mail_mx($clear_to[$to]);

					if (!$test_run && $send_status) {

						// send mail for each section
						if ($site->CONF['mailinglist_sending_option'] == 1)
						{
							foreach ($list as $section_id => $section) if(in_array($section_id, $subscribed[$to]) && sizeof($section['articles']))
							{
								$body = '';
								foreach ($section['articles'] as $article)
								{
									$body .= $article['content'];
								}
								
								if ($site->CONF['maillist_subject'] == 1) {
									$subject = $section['title'].': '.$article['title'];
								} elseif ($site->CONF['maillist_subject'] == 2) {
									$subject = $section['title'];
								} elseif ($site->CONF['maillist_subject'] == 3) {
									$subject = $article['title'];
								} else {
									$subject = $site->sys_sona(array(sona => 'Mailinglists: e-mail subject', tyyp=>'kasutaja', lang_id=>$keel['glossary_id']));
									$subject = str_replace('[Website name]',$site->CONF['site_name'], $subject);
								}
								
								$send_status = send_mailinglist_message($header, $body, $footer, $keel['encoding'], $subject, $to, $from);
							}
						}
						// send one mail per article
						elseif ($site->CONF['mailinglist_sending_option'] == 2)
						{
							foreach($list as $section_id => $section) if(in_array($section_id, $subscribed[$to]) && sizeof($section['articles']))
							{
								foreach ($section['articles'] as $article)
								{
									if ($site->CONF['maillist_subject'] == 1) {
										$subject = $section['title'].': '.$article['title'];
									} elseif ($site->CONF['maillist_subject'] == 2) {
										$subject = $section['title'];
									} elseif ($site->CONF['maillist_subject'] == 3) {
										$subject = $article['title'];
									} else {
										$subject = $site->sys_sona(array(sona => 'Mailinglists: e-mail subject', tyyp=>'kasutaja', lang_id=>$keel['glossary_id']));
										$subject = str_replace('[Website name]',$site->CONF['site_name'], $subject);
									}
									
									$send_status = send_mailinglist_message($header, $article['content'], $footer, $keel['encoding'], $subject, $to, $from);
								}
							}
						}
						// send all articles in one mail
						else 
						{
							$send_status = send_mailinglist_message($header, $body, $footer, $keel['encoding'], $subject[$to], $to, $from);
						}
					}
					
					$report_message .= "<b>[".$testrun_msg_arr[$body]."]</b> ";
					$report_message .= htmlspecialchars($to).($send_status ? "" : " <font color=red><b>bad mail</b></font>")."<br>\n";

					$mail_count++;

				if ($send_status) { $mail_ok_count++; } 
				else  { $mail_error_count++; } 
				}

			}
			$report_message .= "<hr size=1><b>DEBUG ONLY: Mailinglists sent to ".$mail_count." users. Successfully sent: ".$mail_ok_count.", Error occurred during sending: ".$mail_error_count.", Execution time: ".$exec_timer->get_aeg().".</b>";

			if ($test_run) {
				echo $report_message;
			} elseif ($mail_count) {
				$reporter_address = $site->CONF["maillist_reporter_address"];
				if ($reporter_address) {
					$headers  = "MIME-Version: 1.0\r\n";
					$headers .= "Content-type: text/html; charset=".$keel['encoding']."\r\n";
					$headers .= "From: ".$site->CONF["from_email"]."\r\n";

					mail($reporter_address, "Mailinglist report from site: ".$site->CONF["site_name"], wordwrap($report_message,70), $headers);
				}
			}
		} # kui on, mida saata
		# / meilide saatmine
		######################


		######################
		# kirjuta log lõpetamisest
		# ainult siis, kui mõni meil üldse saadeti

		if($mail_count && !$test_run) {
			new Log(array(
				'action' => 'send',
				'component' => 'Mailinglist',
				'user_id' => ($is_pageloaded ? 0 : $site->user->id),
				'message' => "Mailinglists sent to ".$mail_count." users. Successfully sent: ".$mail_ok_count.", Error occurred during sending: ".$mail_error_count.", Execution time: ".$exec_timer->get_aeg(),
			));
		}
	} # while keel
	if ($is_custom) {
		$mailing_list['header'] = $header;
		$mailing_list['footer'] = $footer;
		$mailing_list['from'] = $from;

		return $mailing_list;
	}
} # function

# / funktsioon meilinglisti saatmiseks
########################################


#####################
# function auto_publishing
# 
# objektide automaatseks avaldamiseks/peitmiseks
########################################

function auto_publishing($is_pageloaded = 0) {
	
	global $site;

	########################################
	# leida objektid, mis vajavad avaldamist JA mis ei kuulu prügikasti 

	$sql = "SELECT DISTINCT objekt.objekt_id FROM objekt LEFT JOIN objekt_objekt ON objekt_objekt.objekt_id=objekt.objekt_id "; # bug 2817
	# pole praegu avaldatud JA ei ole prügikatis (Bug #1373) JA..
	$sql .= " WHERE on_avaldatud=0 AND objekt_objekt.parent_id<>'".$site->alias("trash")."' ";
	# ..alguskuupäev täidetud, varasem tänasest ja lõppkuupäev täidetud/tühi, hilisem tänasest VÕI..
	$sql .= " AND ( (avaldamisaeg_algus>0 AND avaldamisaeg_algus <= ".$site->db->unix2db_datetime(time())." AND (avaldamisaeg_lopp>=".$site->db->unix2db_datetime(time())." OR NOT avaldamisaeg_lopp>0)) ";
	# ..alguskuupäev tühi ja lõppkuupäev täidetud, hilisem tänasest
	$sql .= " OR (avaldamisaeg_lopp>=".$site->db->unix2db_datetime(time())." AND NOT avaldamisaeg_algus>0) )";
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
	//print_r($sql);			
	while($objekt_id = $sth->fetchsingle()) {

		# tee objekt. ja tee seda superuserina, Bug #805 ( muidu on nii, et kui pageloadi ajal püütakse avaldada objekti, millele sellel juhuslikul useril õiguseid ei ole, siis seda ka autom. avaldada ei suudeta)
		$obj = new Objekt(array("id"=>$objekt_id, "superuser"=>1));
		
		$sql = $site->db->prepare("UPDATE objekt SET on_avaldatud=1 WHERE objekt_id=?", $objekt_id);
		
		$sth2 = new SQL($sql);
		$site->debug->msg($sth2->debug->get_msgs());	
		new Log(array(
			'action' => 'publish',
			'objekt_id' => $obj->objekt_id,
			'user_id' => ($is_pageloaded ? 0 : $site->user->id),
			'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($obj->all[klass])), $obj->pealkiri(), $obj->objekt_id, "published"),
		));
	}

	########################################
	# leida objektid, mis vajavad peitmist

	$sql = "SELECT DISTINCT objekt.objekt_id FROM objekt LEFT JOIN objekt_objekt ON objekt_objekt.objekt_id=objekt.objekt_id "; # bug 2817
	# on praegu avaldatud JA ei ole prügikatis (Bug #1373) JA..
	$sql .= " WHERE on_avaldatud=1  AND objekt_objekt.parent_id<>'".$site->alias("trash")."' ";
	# ..alguskuupäev täidetud ja hilisem praegusest VÕI.
	$sql .= " AND (avaldamisaeg_algus > ".$site->db->unix2db_datetime(time())." ";
	# ..lõppkuupäev täidetud ja varasem praegusest
	$sql .= " OR (avaldamisaeg_lopp>0 AND avaldamisaeg_lopp < ".$site->db->unix2db_datetime(time()).")) ";

	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());			
	
	while($objekt_id = $sth->fetchsingle()) {

		# tee objekt. ja tee seda superuserina, Bug #805 ( muidu on nii, et kui pageloadi ajal püütakse avaldada objekti, millele sellel juhuslikul useril õiguseid ei ole, siis seda ka autom. avaldada ei suudeta)
		$obj = new Objekt(array("objekt_id"=>$objekt_id, "superuser"=>1));
		$sql = $site->db->prepare("UPDATE objekt SET on_avaldatud=0 WHERE objekt_id=?", $objekt_id);
		$sth2 = new SQL($sql);
		$site->debug->msg($sth2->debug->get_msgs());	

		new Log(array(
			'action' => 'hide',
			'objekt_id' => $obj->objekt_id,
			'user_id' => ($is_pageloaded ? 0 : $site->user->id),
			'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($obj->all[klass])), $obj->pealkiri(), $obj->objekt_id, "hided"),
		));
	} # while
}
# / function auto_publishing
#####################

// error notifications
function auto_error_notifications($is_pageloaded = 0)
{
	global $class_path;
	global $site;

	// do this only if there are emails to send to and only if sending is not set to inactive (0)
	if($site->CONF['send_error_notifiations_setting'] && ($site->CONF['send_error_notifiations_to_superusers'] || $site->CONF['send_error_notifiations_to']))
	{
		// emails
		$emails = array();
		
		if($site->CONF['send_error_notifiations_to'])
		{
			$emails = explode(',', $site->CONF['send_error_notifiations_to']);
			foreach($emails as $i => $email)
			{
				$emails[$i] = trim($email);
			}
		}
		
		// get superuser emails
		if($site->CONF['send_error_notifiations_to_superusers'])
		{
			// skip locked superusers
			$result = new SQL("select email from users where is_predefined = 1 and is_locked <> 1 and email <> ''");
			while($email = $result->fetchsingle())
			{
				$emails[] = $email;
			}
		}
		
		$emails = array_unique($emails);
		
		// exit function if no emails present
		if(!sizeof($emails))
		{
			new Log(array(
				'action' => 'send',
				'component' => 'Error notification',
				'type' => 'ERROR',
				'user_id' => ($is_pageloaded ? 0 : $site->user->id),
				'message' => 'Error notification sending failed: no recipients.',
			));
			//so the last run wouldn't get updated
			return;
		}
		
		# ignore 'STOP' button 
		ignore_user_abort(true); 
		
		$error_types = Log::getTypeArray();
		$log_actions = Log::getActionsArray();
		
		$last_run = $site->CONF['send_error_notifiations_last_run'];
		
		// select all ERROR type log entries from this point to send_error_notifiations_last_run
		$sql = "select date, component, message from sitelog where date > '".$last_run."' and type = ".Log::getTypeCode('ERROR');
		$result = new SQL($sql);
		
		// if any errors found, send emails out
		if($result->rows)
		{
			$messages = array();
			$i = 0;
			while($row = $result->fetch('ASSOC'))
			{
				$i++;
				$messages[] = $i.'. '.$row['date'].($row['component'] ? ' ['.$row['component'].']' : '')."\n".$row['message']."\n";
			}
			
			$messages = implode("\n", $messages);
			
			// notice header
			$notice =
				'Website: '.$site->CONF['protocol'].$site->CONF['hostname'].$site->CONF['wwwroot']."\n\n";
			
			// notice content
			$notice .=  strip_tags($messages)."\n\n";
			
			// notice footer
			$notice .=
				'Time: '.date("d.m.Y H:i T")."\n";
			
			include_once($class_path.'mail.class.php');
			
			$error_notice = new email(array(
				'subject' => $site->CONF['site_name'].': Website error report',
				'message' => $notice,
				'charset' => $site->encoding,
			));
			
			if($error_notice->send_mail(array(
				'to' => implode(',', $emails),
				'from' => $site->CONF['from_email'],
			)))
			{
				new Log(array(
					'action' => 'send',
					'component' => 'Error notification',
					'user_id' => ($is_pageloaded ? 0 : $site->user->id),
					'message' => 'Error notifications sent to: '.implode(',', $emails),
				));
			}
			else 
			{
				new Log(array(
					'action' => 'send',
					'component' => 'Error notification',
					'type' => 'ERROR',
					'user_id' => ($is_pageloaded ? 0 : $site->user->id),
					'message' => 'Error notification sending failed: e-mail not accepted for delivery.',
				));
				//so the last run wouldn't get updated
				return;
			}
		}
		
		//update last run
		new SQL("update config set sisu = now() where nimi = 'send_error_notifiations_last_run'");
		//new SQL("update config set sisu = 0 where nimi = 'send_error_notifiations_last_run'");
	}
}

function send_mailinglist_message($header, $body, $footer, $encoding, $subject, $to, $from)
{
	global $site;
	
	//printr(func_get_args());
	
	if ($site->CONF["maillist_format"]) {
		# strip HTML tags for plain text message
		$message = $header.$body.$footer;
		$message = str_replace("<br>", "\n", $message);
		$message = strip_tags($message);
	}
	if ($site->CONF["maillist_format"] != 1) {
		$html = $header.$body.$footer;
		$html = str_replace("\n", "<br>", $html);

		$html_h = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
		$html_h .= "<html>\n";
		$html_h .= "<head>\n";
		$html_h .= "  <meta content=\"text/html;".$encoding."\" http-equiv=\"Content-Type\">\n";
		$html_h .= "  <title></title>\n";
		$html_h .= "</head>\n";
		$html_h .= "<body>\n";

		$html_f = "\n</body>\n";
		$html_f .= "</html>";

		$html = $html_h.$html.$html_f;
	}

	$email = new email(array(
		'subject' => $subject,
		'message' => wordwrap($message,70),
		'charset' => $encoding,
		'html' => $html,
	));
		
	return $email->send_mail(array(
		'to' => $to,
		'from' => $from,
	));
}
