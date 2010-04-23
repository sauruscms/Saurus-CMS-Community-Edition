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


#################################
# function login_button
#	boxstyle => default: class=searchbtn
#	fontstyle => default: class=navi2_on
#	value => default: ($user ?  <system word "Logi valja"> :  <system word "nupp login">)
#	targeturl => "?id=666" (default: current url) saab ette anda kuhu ID-le suunduda peale sisselogimist

function smarty_function_login_button ($params) {
	global $site, $leht;

	##############
	# default values

	extract($params);

	if(isset($targeturl)) {
		$targeturl = urlencode($site->CONF[wwwroot].'/'.$targeturl);
	}
	# default value is the current url
	else {
		$targeturl = $site->safeURI;
	}

	##########################
	# reg.user 

	if ($site->kasutaja) {
		$value = $site->sys_sona(array(sona => "Logi valja", tyyp=>"kasutaja"));

		if(!isset($fontstyle)) {  
			$login_button = '<a href="'.$site->self.'?id='.$leht->id.'&op=logout&url='.$site->safeURI.'" class="navi2_on">'.$value.'</a>';
		} 
		else {
			$login_button = '<a href="'.$site->self.'?id='.$leht->id.'&op=logout&url='.$site->safeURI.'"><font '.$fontstyle.'>'.$value.'</font></a>';
		}
	}
	else {
		if(!isset($value)) {  
			$value = $site->sys_sona(array(sona => "nupp login", tyyp=>"kasutaja"));
		}

		if(!isset($boxstyle)) { 
			$boxstyle = 'class=searchbtn';
		}
		$login_button = '<input type=hidden name="op" value="login"><input type=hidden name="url" value="'.$targeturl.'"><input type=hidden name="id" value="'.$leht->id.'"><INPUT type=submit value="'.$value.'" '.$boxstyle.'>';
	
	}
	print $login_button;
}
