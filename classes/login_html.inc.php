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
 * Login forms, required browser, etc html printing functions;
 * Usually an entire page html is printed to output.
 * 
 */

############################
# FUNCTION admin_login_form
/**
 * admin_login_form
 * 
 * prints admin-area login page html
 *
 * 
 * @package CMS
 * 
 * usage:	include_once($class_path."login_html.inc.php");
 *			admin_login_form(array("site" => $this, "auth_error" => 1));
 */
function admin_login_form() {
	$args = func_get_arg(0);
	$site = &$args['site']; # pointer to site instance
	$auth_error = $args['auth_error']; # 1/0, 1 kui sisselogimine ebaõnnestus, 2 kui kasutaja lukustatud

	##### if auth_error parameter not provided, try to find out it: 
	if($site->fdat["op"] == 'login' && $site->fdat["url"]){
		# POOLELI
	}

	#################
	# language selectbox data
	$sql = "select distinct keel.keel_id, keel.keel_id as keel, keel.nimi, keel.on_default_admin from keel left join sys_sonad on keel.keel_id = sys_sonad.keel where sys_sonad.keel is not null and keel.keel_id < 500 order by keel.nimi";
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());	
	$lang_count = $sth->rows;
	####### loop over in use languages
	while ($lang = $sth->fetch()) {
		$reserv_output .= "<option value=\"".$lang['keel']."\" ".($lang['on_default_admin'] ? 'selected':'').">".$lang['nimi']."</option>\n";

		# ARVUTA TÕLGITUD sõnade arv:  nii, et kui näiteks teed somaalia keele saiti ja admin osa stringe pole tõlgitud, siis ei näidata keele valikuna seda sisselogimise juures
		$sql2 = $site->db->prepare("
			SELECT COUNT(sona) AS cnt_sona, COUNT(origin_sona) AS cnt_origin_sona 
			FROM sys_sonad
			WHERE sst_id = 12 AND keel = ?", 
			$lang[keel]
		);
		$sth2 = new SQL($sql2);
		$site->debug->msg($sth2->debug->get_msgs());
		$tmp_rec =  $sth2->fetchrow();
		$translated = ($tmp_rec['cnt_sona'] > 30 || $tmp_rec['cnt_origin_sona'] > 30) ? 1 : 0;
		
		# Naitame valikus ainult keeled, mis juba t6lkitud.
		if ($translated){
			$output .= "<option value=\"".$lang['keel']."\" ".($lang['on_default_admin'] ? 'selected':'').">".$lang['nimi']."</option>\n";
			$naidatud = 1;
		}

		if (!$naidatud){ $output = $reserv_output; };

	} # / loop over in use languages
	# / language selectbox data
	#################

	#################
	# get default admin language
	$sql = $site->db->prepare("SELECT glossary_id FROM keel WHERE keel.on_default_admin='1' LIMIT 1");
	$sth = new SQL($sql);
	$default_admin_lang = $sth->fetchsingle();

	#################
	# get site metadata
	# metadata is saved in the HOME SECTION object
	$home_id = $site->alias(array(
		'key' => 'rub_home_id',
		'keel' => $site->keel,
	));
	# can't use "new Objekt" here, beacuse site is not fully loaded yet.
	$sql = $site->db->prepare("SELECT objekt_id, meta_title FROM objekt WHERE objekt_id=?",	$home_id);
	$sth = new SQL($sql);
	$home_objekt = $sth->fetch();
	$meta_title = $home_objekt['meta_title'];

	# / get site metadata
	#################
	
	$url = str_replace(array('?op=logout', '&op=logout', urlencode('?op=logout'), urlencode('&op=logout')), '', $site->safeURI);
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title><?=$meta_title?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding?>">
	<link rel="stylesheet" type="text/css" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'] ?>/loginscreen.css">
</head>

<body style="overflow-y: auto; overflow-x: auto;" onLoad="document.forms['loginform'].user.focus()">
	<?########### FORM ?>
	  <form method="post" name="loginform" action="<?=$site->wwwroot?><?if($site->in_admin){echo "/admin";}if($site->in_editor){echo "/editor";}?>/index.php">
	<?
	foreach ($site->fdat as $key=>$value) {
		if (!is_array($value) && $key!="user" && $key!="pass" && !($key == 'op' && $value == 'logout')) {
	?>
		<input type="hidden" name="<?php echo htmlspecialchars(xss_clean($key)); ?>" value="<?php echo htmlspecialchars(xss_clean($value)); ?>">
	<?
		}
	}
	?>
	<input type=hidden name="op" value="login">
	<input type=hidden name="url" value="<?php echo $url ?>">

	<table width="100%" height="99%" cellspacing=0 cellpadding=0 border=0>
		<tr>
			<td valign=middle align=center>
				
				<table class="shadow_box_wrapper" cellspacing="0" cellpadding="0">
					<tr><td class="tl"></td><td class="tc"></td><td class="tr"></td></tr>
					<tr>
						<td class="ml"></td>
						<td> <!-- shadow_box_wrapper content -->
							
							<div id="loginbox">
								<div id="loginhead">
									<h1><?=$site->sys_sona(array(sona => "Admin login", tyyp=>"Admin")) ?></h1>
									<a href="<?=$site->wwwroot?>" title="<?=$meta_title?>"><?=strlen($meta_title)>50?substr($meta_title,0,50).'..':$meta_title?></a>
								</div>
								<div id="loginmain">
								<?######## error #######?>
									<? if ($auth_error == 1) { ?>
										<div class="errormessage"><?=$site->sys_sona(array(sona => "Unauthorized access", tyyp=>"Admin")) ?></div>
									<? } elseif ($auth_error == 2) { ?>
										<div class="errormessage"><?=str_replace("[minutes]",$site->CONF['login_locked_time'], $site->sys_sona(array(sona => "Maximum logins error", tyyp=>"Admin"))) ?></div>

									<? } ?>
								<?### / error ####?>
									<div></div> <?## IE7 bug - needs this to show errormessage, otherwise it will dissapera#?>
									<table>
								<?######## username #######?>
									<?php
										$username = '';
									?>
										<tr>
											<td class="label"><?=$site->sys_sona(array(sona => "Username", tyyp=>"Admin")) ?>:</td>
											<td><input type="text" name="user" value="<?=xss_clean($username);?>"></td>
										</tr>
							<?######## password #######?>
										<tr>
											<td class="label"><?=$site->sys_sona(array(sona => "Password", tyyp=>"Admin")) ?>:</td>
											<td><input type="password" name="pass"></td>
										</tr>
							<?######## language selectbox:  #######?>
							<? # show only if more than one language found
								if($lang_count > 1) {
										?>
										<tr>
											<td class="label"><?=$site->sys_sona(array(sona => "translations", tyyp=>"Admin")) ?>:</td>
											<td><select name="keel"><?=$output?></select></td>
										</tr>
									<? }
								# otherwise display hidden field with default lang ID value (Bug #2460)		
								else {	?>
										<input type="hidden" name="keel" value="<?=$default_admin_lang?>">
							<?} ?>
										<tr>
											<td colspan="2"><div class="separator"></div></td>
										</tr>
										<tr id="bottomrow">
											<td></td>
											<td>
												<input id="loginbutton" type="submit" name="Submit" value="<?=$site->sys_sona(array(sona => "Login", tyyp=>"Admin")) ?>">
												<? if($site->CONF['allow_forgot_password']){ ?>
													<a href="?op=remindpass"><?=$site->sys_sona(array(sona => "Unustasid parooli", tyyp=>"kasutaja"))?></a>
												<?}?>
											</td>
										</tr>
									</table>
								</div>
							</div>
							
						</td> <!-- shadow_box_wrapper content -->
						<td class="mr"></td>
					</tr>
					<tr><td class="bl"></td><td class="bc"></td><td class="br"></td></tr>
				</table> <!-- shadow_box_wrapper -->
				<div id="logindisclaimer">Saurus CMS <a href="http://www.saurus.info/" title="Web content management software Saurus CMS">www.saurus.info</a></div>
			</td>
		</tr>
	</table>	

	</form>
	<?########### / FORM ?>

	</body>
	</html>
	<?
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }

$site->debug->print_msg();

	exit();
}
# / FUNCTION admin_login_form
############################


############################
# FUNCTION print_remindpass_form
/**
* print_remindpass_form
* 
* prints forgotten password page html (entire page),
* uses array $site->fdat['form_error'] if needed.
* 
* @package CMS
* 
* usage:	include_once($class_path."login_html.inc.php");
*			print_remindpass_form(array("site" => $this));
*/
function print_remindpass_form() {
	$args = func_get_arg(0);
	$site = &$args['site']; # pointer to site instance
	
	# check if feature is allowed: 
	if(!$site->CONF['allow_forgot_password']){ return; }

	############ STEP 1 => FORM

	# decide if we are in this form first time or second time but with errors 
	if($site->fdat['op2'] == 'send' && !is_array($site->fdat['form_error'])) {
		$pass_is_sent = 1;
	} else { $pass_is_sent = 0; }
	
	#################
	# get site metadata
	# metadata is saved in the HOME SECTION object
	$home_id = $site->alias(array(
		'key' => 'rub_home_id',
		'keel' => $site->keel,
	));
	# can't use "new Objekt" here, beacuse site is not fully loaded yet.
	$sql = $site->db->prepare("SELECT objekt_id, meta_title FROM objekt WHERE objekt_id=?",	$home_id);
	$sth = new SQL($sql);
	$home_objekt = $sth->fetch();
	$meta_title = $home_objekt['meta_title'];

	# / get site metadata
	#################


######## lisatud header kuna muidu tekib gzip-i compressi kasutamisel esimesel korral valge tühi leht
header("Content-type: text/html;charset=".$site->encoding);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$meta_title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding?>">
	<link rel="stylesheet" type="text/css" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'] ?>/loginscreen.css">
</head>

<body style="overflow-y: auto; overflow-x: auto;" <?if(!$pass_is_sent){?>onLoad="document.forms['loginform'].email.focus()"<?}?>>

	<?########### FORM ?>
	  <form method="POST" name="loginform" action="<?=$site->wwwroot?><?if($site->in_admin){echo "/admin";}if($site->in_editor){echo "/editor";}?>/index.php">
	<?
	#added by Dima 05.05.2003
	foreach ($site->fdat as $key=>$value) {
		if (!is_array($value) && $key!="email" && $key!="op2") {
	?>
		<input type="hidden" name="<?=$key?>" value="<?=$value?>">
	<?
		}
	}
	?>
	<input type=hidden name=op2 value="send">

	<table width="100%" style="height: 99%;" cellspacing=0 cellpadding=0 border=0>
		<tr>
			<td valign=middle align=center>
				
				<table class="shadow_box_wrapper" cellspacing="0" cellpadding="0">
					<tr><td class="tl"></td><td class="tc"></td><td class="tr"></td></tr>
					<tr>
						<td class="ml"></td>
						<td> <!-- shadow_box_wrapper content -->
				
							<div id="loginbox">
								<div id="loginhead">
									<h1><?=$site->sys_sona(array(sona => "Admin login", tyyp=>"Admin")) ?></h1>
									<a href="<?=$site->wwwroot?>" title="<?=$meta_title?>"><?=strlen($meta_title)>50?substr($meta_title,0,50).'..':$meta_title?></a>
								</div>
								<div id="loginmain">
									<?######## error OR ok message #######?>
									<? if ($site->fdat['form_error']['email'] || $pass_is_sent) { ?>
			
										<?if($site->fdat['form_error']['email']){?>
											<div class="errormessage"><?=$site->fdat['form_error']['email']?></div>
										<?}elseif($pass_is_sent){?>
											<div class="okmessage"><?=$site->sys_sona(array(sona => "unustatud_parool_saadetud", tyyp=>"system"))?></div>
										<?}?>
									<? } ?>						
									<div></div> <?## IE7 bug - needs this to show errormessage, otherwise it will dissapera#?>
									<table cellspacing="0" cellpadding="0" border="0">
								<?if(!$pass_is_sent){?>
											<tr><td colspan=2 class="label"><?=$site->sys_sona(array(sona => "Unustatud parooli saatmine", tyyp=>"kujundus"))?></td></tr>
										<?}?>
									
								<?if(!$pass_is_sent){?>
								<?######## e-mail #######?>
										<tr>
											<td class="label"><?=$site->sys_sona(array(sona => "Email", tyyp=>"kasutaja"))?>:</td>
											<td><input type="text" name="email"></td>
										</tr>
								<?}?>
										<tr>
											<td colspan="2"><div class="separator"></div></td>
										</tr>					
										<tr id="bottomrow">
											<td></td>
											<td>
												<?if(!$pass_is_sent){?>
													<input id="loginbutton" type="submit" name="Submit" value="<?=$site->sys_sona(array(sona => "Saada", tyyp=>"kasutaja")) ?>">
												<?}?>
												<a href="index.php?id=<?=$site->fdat['id']?>"><?=$site->sys_sona(array(sona => "tagasi", tyyp=>"editor"))?></a>
											</td>
										</tr>
										</table>
								
								</div> <!-- #loginmain -->
								
								
							</div> <!-- #loginbox -->
						</td> <!-- shadow_box_wrapper content -->
						<td class="mr"></td>
					</tr>
					<tr><td class="bl"></td><td class="bc"></td><td class="br"></td></tr>
				</table> <!-- shadow_box_wrapper -->
				<div id="logindisclaimer">Saurus CMS <a href="http://www.saurus.info/" title="Web content management software Saurus CMS">www.saurus.info</a></div>
			</td>
		</tr>
	</table>	

	</form>
	<?########### / FORM ?>

	</body>
	</html>
<?
if($site->user) { $site->user->debug->print_msg(); }
# guest debug: 
if($site->guest) { 	$site->guest->debug->print_msg(); }

$site->debug->print_msg();

	return;
}
# / FUNCTION print_remindpass_form
############################


############################
# FUNCTION send_remindpass
/**
* send_remindpass
* 
* sends an e-mail to the user with new generated password or
* if errors occurred then saves errors to the $site->fdat['form_error'] array.
* Requires: GET/POST parameter "op2" must be "send", is step 2 after #remind password# form
* 
* @package CMS
* 
* usage:	include_once($class_path."login_html.inc.php");
*			send_remindpass(array("site" => $this));
*/
function send_remindpass() {
	$args = func_get_arg(0);
	$site = &$args['site']; # pointer to site instance
	# check if feature is allowed: 
	if(!$site->CONF['allow_forgot_password']){ return; }

	#########################
	# STEP 2 => SEND E-MAIL
	if($site->fdat['op2'] == 'send') {

	##### emaili formaadi kontroll
	if (!preg_match("/^[\w\-\&\.\d]+\@[\w\-\&\.\d]+$/", $site->fdat['email'])) {
		$op2_status = "error";
		$site->fdat['form_error']['email'] = $site->sys_sona(array(sona => "wrong email format", tyyp=>"kasutaja"));
	}
	#### if no errors
	if ($op2_status != "error") {

		###### check if user exists
		$sql = $site->db->prepare("SELECT user_id, firstname,lastname,username,email,is_readonly FROM users WHERE email LIKE ? ", $site->fdat['email']);
#		print $sql;
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		$user = $sth->fetch();	
#		printr($user);
#		exit;

		##### exactly 1 user found => OK
		if ($sth->rows == 1 && $user['is_readonly']!=1) {
			# data sanity: if account info exists => OK
			if($user['username']){ 
	
			######## always GENERATE NEW PASSWORD
			$new_pass = genpassword(8); # length 8 char
			# then encrypt password
			$enc_new_pass = crypt($new_pass, Chr(rand(65,91)).Chr(rand(65,91)));
		
			########## CHANGE password
			$sql = $site->db->prepare("UPDATE users SET password=? WHERE user_id=? ", $enc_new_pass, $user['user_id']);
#			print $sql;
			$sth = new SQL($sql);		

			########## SEND email
			$header = "<br>";
			$footer = "<br>____________________________________<br>
			".$site->CONF["site_name"]."<br>
			".(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF["hostname"].$site->CONF["wwwroot"]."/";

			/*
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=".$site->encoding."\r\n";
			$headers .= "From: ".$site->CONF["from_email"]."\r\n";
			*/

$message .= "
".$site->sys_sona(array(sona => "Name", tyyp=>"Admin")).": ".$user['firstname']." ".$user['lastname']."<br>
".$site->sys_sona(array(sona => "Username", tyyp=>"Admin")).": ".$user['username']."<br>
".$site->sys_sona(array(sona => "Password", tyyp=>"Admin")).": ".$new_pass."<br>
";

$message .= '<br>'.$site->sys_sona(array(sona => "forgotten password: mail body", tyyp=>"kasutaja")).'<br>';

			global $class_path;
			include_once($class_path.'mail.class.php');

			$mail = new email(array(
		  		'subject' => $site->sys_sona(array('sona' => 'unustatud parool: subject', 'tyyp' => 'kasutaja')),
		  		'message' => strip_tags($header.$message.$footer),
		  		'html' => $header.$message.$footer,
		  		'charset' => $site->encoding,
		  	));
		  	
		  	$send_status = $mail->send_mail(array(
		  		'to' => $user['email'],
		  		'from' => $site->CONF['from_email'],
		  	));

			//$send_status = mail ($user['email'],$site->sys_sona(array(sona => "unustatud parool: subject", tyyp=>"kasutaja")), $header.$message.$footer, $headers);

			######## MAIL OK
			if ($send_status) { 
				new Log(array(
					'action' => 'send',
					'component' => 'Users',
					'message' => "Password reminder: e-mail sent to '".$user['email']."'.",
				));
				$op2_status = "ok";			
			}
			######## MAIL ERROR
			else  { 
				new Log(array(
					'action' => 'send',
					'component' => 'Users',
					'type' => 'ERROR',
					'message' => "Password reminder error: can't send e-mail to '".$user['email']."'.",
				));
				$op2_status = "error";
				$site->fdat['form_error']['email'] = $site->sys_sona(array(sona => "viga", tyyp=>"kujundus"));			
			} 

			} # if account info exists
			# if no username found => error
			else {
				new Log(array(
					'action' => 'send',
					'component' => 'Users',
					'type' => 'ERROR',
					'message' => "Password reminder error: user with e-mail '".$site->fdat['email']."' doesn't have username.",
				));
				$op2_status = "error";
				$site->fdat['form_error']['email'] = $site->sys_sona(array(sona => "email not found", tyyp=>"kasutaja"));	
			}
		} # exactly 1 user found 
		else {
				# 0) the User is flagged is_readonly => write log message
			if($user['is_readonly']==1){
					new Log(array(
						'action' => 'send',
						'component' => 'Users',
						'type' => 'ERROR',
						'message' => "Password reminder error: the email '".$site->fdat['email']."' belongs to a is_readonly flagged user, so no password was sent.",
					));
			}else{
				# 1) if more than 1 users found => write log message
				if($sth->rows > 1) { 
					new Log(array(
						'action' => 'send',
						'component' => 'Users',
						'type' => 'ERROR',
						'message' => "Password reminder error: more than 1 user found with  e-mail '".$site->fdat['email']."'.",
					));
				}
				# 2) if no users found => write log message and give error message
				else {
					new Log(array(
						'action' => 'send',
						'component' => 'Users',
						'type' => 'ERROR',
						'message' => "Password reminder error: no user found with e-mail '".$site->fdat['email']."'.",
					));
				}
			}
			$op2_status = "error";
			$site->fdat['form_error']['email'] = $site->sys_sona(array(sona => "email not found", tyyp=>"kasutaja"));	
		} # how many users found
	} # email is ok
	} # op2
	# / STEP 2 => SEND
	#########################

	return $site->fdat['form_error'];
}
# / FUNCTION send_remindpass
############################


###################
# Generates a somewhat random pronounceable password $length letters long
function genpassword($length){

    srand((double)microtime()*1000000);

    $vowels = array("a", "e", "i", "o", "u");
    $cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr", "cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl");
    $numbers = array("1", "2", "3", "4", "5", "6", "7", "8", "9");

	$password = '';

    $num_vowels = count($vowels);
    $num_cons = count($cons);
    $num_numbers = count($numbers);

    for($i = 0; $i < $length; $i++){
        $password .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)] ;
    }
    $password = $numbers[rand(0, $num_numbers - 1)].$password;

    return substr($password, 0, $length);
}