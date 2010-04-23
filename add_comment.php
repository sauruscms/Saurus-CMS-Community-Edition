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


##############################
# Add a comment into database
# : is FORM action value for comment forms
# : will redirect back to the calling page
# : is independent script, not for including, new Site is generated
##############################

global $site, $leht;

##############################
# function big_string_remove
function big_string_remove( $input ) {

	global $site;

	$limit = $site->CONF['comment_max_chars'] ? $site->CONF['comment_max_chars'] : 50;

	$output = "";
	$sybol = array ("(","{","[","]","}",")");
	for ( $i=0; $i<=strlen($input); $i++) {
		if ($input[$i]!= " ") {
			$y++;
		} else {
			$y=0;
		}

		if (in_array ($input[$i], $sybol)){
			$x++;
		} else {
			$x=0;
		}

		$output .= $input[$i];
		if ( $y >= $limit ) {
			$y=0;
			$output .= " ";
		}

		if ( $x >= $limit ) {
			$x=0;
			$output .= "\n";
		}
	}
	return $output;
}
# / function big_string_remove
##############################

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";
include($class_path."port.inc.php");
include($class_path."mail.class.php"); # for f-n encodeHeader()

$site = new Site(array(
	on_debug=>0,
));

if(!isset($_SESSION['keel']))
{
	//no session started, prolly a bot, exit
	header('Location: index.php');
	exit;
}

if($site->CONF['allow_commenting'] == 0)
{
	header('Location: index.php');
	exit;
}


if($site->CONF['check_for_captcha'] == 1)
{
	if(isset($_SESSION['scms_captcha']) && is_array($_SESSION['scms_captcha']))
	{
		$captcha = array_keys($_SESSION['scms_captcha']);
		$captcha['name'] = $captcha[0];
		$captcha['text'] = $_SESSION['scms_captcha'][$captcha['name']];
		
		if(strtolower($_POST['captcha_'.$captcha['name']]) == strtolower($captcha['text']))
		{
			$capthca_check_failed = false;
		}
		else 
		{
			$capthca_check_failed = true;
		}
 	}
	else 
	{
		$capthca_check_failed = true;
	}
}

unset($_SESSION['scms_captcha']);

if($capthca_check_failed)
{
	// let's save data from form to cookie if there is captcha error
	$error_data = $site->fdat['nimi'].'|'.$site->fdat['email'].'|'.$site->fdat['url'].'|'.$site->fdat['text'].'|'.$site->fdat['pealkiri'];
	setcookie("addcomment_captcha_error", $error_data);
	
	// or I know: to the session!
	$_SESSION['scms_last_comment'] = $site->fdat;
	
	if ($site->fdat['redirect_url'])
	{
		header('Location: '.urldecode(preg_replace("!\r|\n.*!s", "", $_POST['redirect_url'])).'&lisa_alert=2');
		exit;
	}
	else
	{
		//protocol check ...
		header('Location: '.(empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].($site->in_editor?'/editor':'').'/?'.(($site->fdat['tpl'] || $site->fdat['c_tpl'])&&!$site->fdat['inserted_id']&&!$site->fdat['jump_to_parent']?'tpl='.$site->fdat['tpl'].'&c_tpl='.$site->fdat['c_tpl'].'&':'').'id='.$site->fdat['id'].'&lisa_alert=2');
		exit;
	}
}

$tyyp_id = 14;
$site->debug->print_hash($site->fdat,1,"FDAT");

$leht = new Leht(array(
	id => $site->fdat['id'] ? $site->fdat['id'] : $site->alias("rub_home_id"),
));

$objekt = new Objekt(array(
	objekt_id => $site->fdat['id'],
	on_sisu=>1,
));

if(!$objekt->objekt_id)
{
	//redirect 404 lehele
	header('Location: index.php?id='.$site->alias(array('key' => '404error')));
	exit;
}

$obj_conf = new CONFIG($objekt->all['ttyyp_params']);

if ($site->fdat['output_device'] == 'pda') {
	if (strlen($site->fdat['text']) < 2 || strlen($site->fdat['nimi']) < 2) {
		myRedirect($site->fdat['redirect_url']);
		exit;
	}
	$name = trim($site->user->all['firstname'] . ' ' . $site->user->all['lastname']);
	$nimi = trim($site->fdat['nimi']);
	if ($name != $nimi)
		$site->fdat['nimi'] .= ' (nimi muudetud)';
}

$already = 0;

############ get all parent object: trail
$trail_objs = $leht->parents->list;


#oldfor ($y=-1;$y>-10;$y--){
$i = 0;
foreach ($trail_objs as $i => $myobj)	{
	# skip the first array element - itself
	//if($i == 0) { continue; } 

	if (($myobj->all[ttyyp_id]==40 || $myobj->all[ttyyp_id]>1000) && !$already){
		$already=1; 
		$par_rubobj = $myobj; # get parent section object

		############################
		# CONFIGURATION PARAMETERS - reading parameters values of object

			$leht->debug->msg("PARAMS ".$par_rubobj->all[ttyyp_params]);
			$conf = new CONFIG($par_rubobj->all[ttyyp_params]);
			$faq_mode = ($conf->get("faq_mode") ? 1:0);
			$conf->debug->print_msg();

			if (!$faq_mode) {
				$leht->debug->msg("set default forum view");
			} else {
				$leht->debug->msg("set forum view to FAQ-mode");
			}
		# / CONFIGURATION  PARAMETERS
		############################

		};
}
########################
# if article then check if commenting is allowed for this article;
# allow unlimited commenting for all other content objects  (Bug #2656)

if (($objekt->all[klass] == "artikkel" && $objekt->all['on_foorum']) || $objekt->all[klass] != "artikkel") {

	########################
	# if access is allowed
	# Bug #2133
	if (!($objekt->all[klass] == "kommentaar" && $faq_mode && !$site->in_editor)){

		# kui FAQ-mode ja pole editor, siis pane avaldatud=NO (Bug #2133)
		if ($faq_mode && !$site->in_editor){
			$publish=0;
		} else {$publish=1;}

		# Kui admin vastab kirjale, siis teeme parent avaldatud:
		if ($faq_mode && $site->in_editor && $objekt->all[klass] == "kommentaar" && is_numeric($site->fdat['id'])){
			$sql = $site->db->prepare(
				"UPDATE objekt SET on_avaldatud=?, last_modified=? WHERE objekt_id=?",
				1,
				time(),
				$site->fdat['id']
				);
			$sth = new SQL ($sql);
			$site->debug->msg($sth->debug->get_msgs());
		}

		#####################
		# insert into objekt:	
		$sql = $site->db->prepare("INSERT INTO objekt (pealkiri, tyyp_id, on_avaldatud, keel, kesk, pealkiri_strip, sisu_strip, aeg, check_in, last_modified, created_user_id, created_user_name, created_time) values (?, ?, ?, ?, ?, ?, ?, ".$site->db->unix2db_datetime(time()).", ?, ?, ?, ?, ?)",
			big_string_remove(strip_tags($site->fdat['pealkiri'])),
			$tyyp_id,
			$publish,
			$site->keel,
			0,
			big_string_remove(strip_tags($site->fdat['pealkiri'])),
			big_string_remove(strip_tags($site->fdat['text'])),
			time(),
			0,
			$site->user->id,
			$site->user->name,
			date("Y-m-d H:i:s")
		);
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());
		
		$id = $sth->insert_id;
		
		#####################
		# insert into objekt_objekt:	
		$sql = "SELECT MAX(sorteering) FROM objekt_objekt";
		$sth = new SQL ($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$sorteering=$sth->fetchsingle();

		$sql = $site->db->prepare("INSERT INTO objekt_objekt (objekt_id, parent_id, sorteering) VALUES (?,?,?)",
			$id,
			$site->fdat['id'],
			$sorteering+1
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		
		$name = big_string_remove(strip_tags($site->fdat['nimi']));
		$email = strip_tags($site->fdat['email']);
		$blog_url = strip_tags($site->fdat['url']);
		
		#####################
		# insert into obj_kommentaar:	
		$sql = $site->db->prepare("INSERT INTO obj_kommentaar (objekt_id, nimi, email, on_saada_email, on_peida_email, ip, text, kasutaja_id, url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
			$id,
			$name,
			$email,
			$site->fdat['on_saada_email'] ? 1 : 0,
			$site->fdat['on_peida_email'] ? 1 : 0,	
			$_SERVER["REMOTE_ADDR"],
			big_string_remove(strip_tags($site->fdat['text'])),
			$site->user->user_id,
			$blog_url
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$comment_inserted = $sth->rows;

		#####################
		# UPDATE LAST_COMMENTED_TIME, COMMENT_COUNT
		
		# get comment count for object:
		$alamlist_count = new Alamlist(array(
				parent => $site->fdat['id'],
				klass	=> "kommentaar",
				asukoht	=> 0,
				on_counter => 1	
			));
		$comment_count = $alamlist_count->rows;

		$sql = $site->db->prepare("UPDATE objekt SET last_commented_time=".$site->db->unix2db_datetime(time()).", comment_count=? WHERE objekt_id=?",
			$comment_count,
			$site->fdat['id']
		);		
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());


		#################
		# kui kommentaar edukalt tabelisse lisatud
		if ($comment_inserted){
			####### find TO e-mail saved in topic's editor or in template configuration
			if(is_object($obj_conf) && $obj_conf->get('email')){
				$conf_email = $obj_conf->get('email');
			} elseif(is_object($conf)){
				$conf_email = $conf->get("email"); 
			}

			#####################
			# kui e-maili vaja saata ja e-maili formaat OK
			if (($objekt->all[on_saada_email]==1 && 	preg_match("/^[\w\d\-\&\.]+\@[\w\d\-\&\.]+$/",$objekt->all[email])) || ($conf_email != '' &&  preg_match("/^[\w\d\-\&\.]+\@[\w\d\-\&\.]+$/",$conf_email))
				){
			
				if (preg_match("/^[\w\d\-\&\.]+\@[\w\d\-\&\.]+$/",$site->fdat[email])){
					$from = $site->fdat['email'];
				} else {
					$from = $site->CONF['from_email'];
				};
	
				$url = "/?".($site->fdat[tpl]?"tpl=".$site->fdat[tpl]."&":"").($site->fdat[c_tpl]?"c_tpl=".$site->fdat[c_tpl]."&":"")."id=".($site->fdat['inserted_id'] ? $id : $site->fdat[id]);

				$messagebody  = ($site->fdat['message_text'] ? str_replace("\\n", "\n", strip_tags($site->fdat['message_text'])) : strip_tags($site->fdat['text']))."\n\n\nURL: ".(empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF[hostname].$site->CONF[wwwroot].($site->fdat['mail_to_admin'] || ($faq_mode && $publish == 0) ? "/editor" : "")."/?".($site->fdat[tpl]?"tpl=".$site->fdat[tpl]."&":"").($site->fdat[c_tpl]?"c_tpl=".$site->fdat[c_tpl]."&":"")."id=".($id ? $id : $site->fdat[id]);
				mail(
					email::encodeHeader(($objekt->all[email] ? $objekt->all[email] : $conf_email), $site->encoding), 
					email::encodeHeader(strip_tags($site->fdat['pealkiri']), $site->encoding),
					$messagebody,
					"From: ". email::encodeHeader($from, $site->encoding) .(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? "\r\n" : "\n").
					"MIME-Version: 1.0" .(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? "\r\n" : "\n").
					"Content-Type: text/plain; charset=\"".$site->encoding."\"" .(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? "\r\n" : "\n").   # Bug #2121
					"Content-Transfer-Encoding: 8bit".(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? "\r\n" : "\n")
				);

				if ($site->on_debug){
					echo "<hr>Saadan meil siia:".($objekt->all[email] ? $objekt->all[email] : $conf_email).", from: ".$from;
				};			

			};
			# / kui e-maili vaja saata ja e-maili formaat OK
			#####################

			# ------------------------
			# Kustutame chache-ist
			# ------------------------
			clear_cache("ALL");
		}
		# / kui kommentaar edukalt tabelisse lisatud
		#################

		#########################
		# debug info
		$site->debug->print_msg();

		#########################
		# redirect

		if (!$site->on_debug){
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); //To fool old browsers
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			# show javascript message "Forum alert: Your question has been sent"
			if ($faq_mode && !$site->in_editor){$tmp_lisa_alert="&lisa_alert=1";}  # Bug #2133

		if ($site->fdat['redirect_url']){
			header("Location: ".urldecode($site->fdat['redirect_url']));
		} else { # Bug #1953
			header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF[hostname].$site->CONF[wwwroot].($site->in_editor?"/editor":"")."/?".(($site->fdat[tpl] || $site->fdat[c_tpl])&&!$site->fdat['inserted_id']&&!$site->fdat['jump_to_parent']?"tpl=".$site->fdat[tpl]."&c_tpl=".$site->fdat[c_tpl]."&":"")."id=".($site->fdat['jump_to_parent'] ? $objekt->parent_id : $objekt->objekt_id).$tmp_lisa_alert);
		}
		} # not debug
	}
	else { 
		echo "<font size=2>Access denied.</font>";
	}
	# / if access is allowed
	########################
}
else {
	$site->debug->msg("Object adding denied - not correct class:".$objekt->all[klass]);
	$site->debug->print_msg();
}

# / double check object class: is it correct?
########################

function myRedirect($url) {
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); //To fool old browsers
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Location: " . urldecode($url));
}
