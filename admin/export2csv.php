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
 * Page for exporin data to CSV format
 * 
 * Open's save as dialog box and saves comma separated file
 * 
 * @param string op - which data to export
 * 
 */


$class_path = "../classes/";
include_once($class_path."port.inc.php");
include_once($class_path."adminpage.inc.php");

$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));


########## allowed referring adminpages, deny export for all unauthorized requests
# id=73 user_managament.php
if (!$site->user->allowed_adminpage(array(
		'adminpage_id' => 73
	))) {
	exit;
}


#################################
# CSV export
# Feature #739
	if ($site->fdat['op']=='users'){

		########## POOLELI
		$sql = "SELECT * FROM user_mailinglist ORDER BY objekt_id ASC ";
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
		while ($data = $sth->fetch()){
			$news_arr[$data['user_id']][] = $data['objekt_id'];
		}


		$sql = "SELECT *, DATE_FORMAT(created_date,'%d.%m.%Y') as f_created_date, DATE_FORMAT(pass_expires,'%d.%m.%Y') as f_pass_expires, DATE_FORMAT(last_access_time,'%d.%m.%Y %H:%i:%S') as f_last_access_time FROM users ORDER BY user_id DESC";
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());	
		$csv_arr[] = "user_id;username;email;firstname;lastname;title;reg_date;last_access_time;blocked;pass_expires;autologin_ip;last_used_ip;news_sections;idcode;tel;account_nr;reference_nr;address;zip;city;country;delivery_address;delivery_city;delivery_zip;delivery_country;contact_phone;contact_person;";
		while ($d = $sth->fetch()){
			$csv_arr[] = $d['user_id'].";".
			$d['username'].";".
			$d['email'].";".
			str_replace(";",",",$d['firstname']).";".
			str_replace(";",",",$d['lastname']).";".
			str_replace(";",",",$d['title']).";".
			$d['f_created_date'].";".
			($d['last_access_time']=='0000-00-00 00:00:00' ? 0:$d['f_last_access_time']).";".
			($d['is_locked'] ? "Y":"N").";".
			$d['f_pass_expires'].";".
			$d['autologin_ip'].";".
			$d['last_ip'].";".
			(is_array($news_arr[$d['user_id']]) ? join(",",$news_arr[$d['user_id']]):0).";".
			$d['idcode'].";".
			str_replace(";",",",$d['tel']).";".
			str_replace(";",",",$d['account_nr']).";".
			str_replace(";",",",$d['reference_nr']).";".
			str_replace(";",",",$d['address']).";".
			str_replace(";",",",$d['postalcode']).";".
			str_replace(";",",",$d['city']).";".
			str_replace(";",",",$d['country']).";".
			str_replace(";",",",$d['delivery_address']).";".
			str_replace(";",",",$d['delivery_city']).";".
			str_replace(";",",",$d['delivery_zip']).";".
			str_replace(";",",",$d['delivery_country']).";".
			str_replace(";",",",$d['contact_phone']).";".
			str_replace(";",",",$d['contactperson']).";";
		}

		header("Content-Disposition: attachment; filename=\"users_".date('Ymd_Hi').".csv\"");
		header("Content-Type: plain/text");
		header("cache-control: nocache");
		echo join("\n", $csv_arr);
		exit;
	}
# / CSV export
#################################
