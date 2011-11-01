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
# Processes feedback form and sends e-mail
# : is FORM action value for feedback adding forms
# : is independent script, not for including, new Site is generated
##############################

function strpos_arr($haystack, $needle) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $what) {
        if(($pos = strpos($haystack, $what))!==false) return $pos;
    }
    return false;
}

global $site;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";
include($class_path."port.inc.php");

$debug = $_COOKIE["debug"] ? 1:0;

$site = new Site(array(
	on_debug=>$debug
));
$errors = array();

if(!isset($_SESSION['keel']))
{
	//no session started, prolly a bot, exit
	header('Location: index.php');
	exit;
}

if($site->CONF['feedbackform_check_for_captcha'] == 1)
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
	$errors[] = 'CAPTHCA';
}

$fdat = sizeof($_POST) ? $_POST : $_GET;

foreach ($fdat as $key=>$value) {
	if(is_array($value)) $value = implode(', ', $value);
	$site->debug->msg("$key: $value");

	list($prefix, $field) = split("_",$key,2);

	if (!$field) {
		$field = $prefix;
		$prefix = "000";
	}

	if (preg_match("/^_/",$field)) {
		# kui välja nimi algab __ -ga, 
		# siis ignoreerime seda
		$site->debug->msg("skipped");
		next;
	} 
	elseif ($field == 'id') {
		# if fieldname is "id" => ignore it (Bug #927)
		$site->debug->msg("skipped");
		next;
	} 
	else {

	# ----------------------------------------
	# Data check
	# ----------------------------------------
		
		
		$value = trim($value); //bug #2390
		# ----------------------------------------
		# required field
		# ----------------------------------------
		if(strlen($prefix) <= 3 && strpos_arr(strtolower($prefix), array('a', 'b', 'c', 'd', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',)) === false)
		{
			if (preg_match("/r/i",$prefix) && $value==='') {
				$site->debug->msg("error: required");
				$errors[] = $field;
			}

			# ----------------------------------------
			# email field
			# ----------------------------------------
			if (preg_match("/e/i",$prefix) && $value!=='' && !preg_match("/^[\w\-\&\.]+\@[\w\-\&\.]+$/i",$value)) {
				$site->debug->msg("error: not a email");
				$errors[] = $field;
			}

			# ----------------------------------------
			# number with dot field
			# ----------------------------------------
			if (preg_match("/f/i",$prefix) && $value!=='' && !preg_match("/^[\d\.\,]+$/",$value)) {
				$site->debug->msg("error: not a number");
				$errors[] = $field;
			}
		}
		else
		{
			$field = $key;
		}

		if ($field == "systemfield") {
			$site->debug->msg("SYSTEM");
			list($tomail,$bad_url,$ok_url,$subject) = split('\|\|\|', $value);

			// bug #2388  Tagaside vormist eemaldada e-maili aadress, tagasiasendus
			$result = new SQL($site->db->prepare('select mail from allowed_mails where id = ?;', $tomail));
			if($result->rows == 1)	$tomail = $result->fetchsingle(); 

			$site->debug->msg("to: $tomail");
			$site->debug->msg("OK: $ok_url");
			$site->debug->msg("ERR: $bad_url");
		} else if ($field!="keel" && $field!="op") {
			$output .= preg_replace("/((re)|(er)|(rf)|(fr)|(e)|(r)|(f))_/", "", $key).": $value\n";
		}
	}
}

if ($subject) {
	foreach ($fdat as $key=>$value) {
		list($prefix, $field) = split("_",$key,2);
		$subject = str_replace("[".$field."]", $value, $subject);
	}
}
# ----------------------------------------
# some enviroment variables
# ----------------------------------------
#??????????????
#map {$out{$_} = $ENV{$_}} split(/ /, $$CONF{env_var});

# ----------------------------------------
# view allowed_mails table
# ----------------------------------------

# added 08.12.2003 by merle: can use multiple e-mails, separated by comma

$sql = $site->db->prepare("SELECT * FROM allowed_mails WHERE FIND_IN_SET(mail,?)",$tomail);
$sth = new SQL($sql);

$sql1 = $site->db->prepare("SELECT * FROM users WHERE FIND_IN_SET(email,?)", $tomail);
$sth1 = new SQL($sql1);

$test = $sth->rows + $sth1->rows;

if ( !$test ) { $errors[] = "Error! Receiver e-mail in mail form has been changed by unauthorized persons."; }

if (sizeof($errors)==0) {
# -------------------
# Send email message
# -------------------

#print "<pre>$output</pre>";
	$output  = "The following information was submitted by ".$_SERVER["REMOTE_ADDR"]."\nfrom ".$_SERVER["HTTP_REFERER"]."\non ".date("d.m.Y T")."\n\n".$output;

	include_once($class_path.'mail.class.php');

	$mail = new email(array(
  		'subject' => ($subject ? $subject : $site->CONF['subject']),
  		'message' => $output,
  		'charset' => $site->encoding,
  	));
  	
  	$send_status = $mail->send_mail(array(
  		'to' => $tomail,
  		'from' => $site->CONF['from_email'],
  	));

	//$send_status = mail($tomail, ($subject ? $subject : $site->CONF["subject"]), $output, "From: ".$site->CONF[from_email]."\nContent-Type: text/plain; charset=".$site->encoding);
	
	# kui mail OK
	if ($send_status) {
		header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF[hostname].$site->CONF[wwwroot].($site->in_editor  ? "/editor" : "")."/".$ok_url);
	# kirjuta error logi
	} else {
		new Log(array(
			'component' => 'Feedback forms',
			'type' => 'ERROR',
			'message' => "Error occurred during sending form feedback e-mail: ".$send_status." (From: ".htmlspecialchars($site->CONF[from_email])." To: ".htmlspecialchars($tomail).")",
		));
	
		$errors[] = "Error occurred during sending form feedback e-mail!"; 
	}
}

if (sizeof($errors)) {
# -------------------
# Error handling
# -------------------
#	$http_headers_out{'Location'} = "$bad_url\&errors=".join("",@errors)."\n\n" unless $fdat{debug};	
	# Bug #2221: [error] väljastab vigaste väljade nimed ühes jorus
	header("Location: ".(empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF[hostname].$site->CONF[wwwroot].($site->in_editor ? "/editor" : "")."/".$bad_url."&op=error&fields=".join(",",$errors));

}

$site->debug->print_msg();
