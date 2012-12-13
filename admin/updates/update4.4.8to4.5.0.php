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
 * @package 	SaurusCMS
 * @copyright 	2000-2010 Saurused Ltd (http://www.saurus.info/)
 * @license		Mozilla Public License 1.1 (http://www.opensource.org/licenses/mozilla1.1.php)
 * 
 */

global $site;

global $class_path;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == 'editor' ? '../classes/' : './classes/';

include_once($class_path.'port.inc.php');

$site = new Site(array(
	'on_debug' => 0,
));


/*---------------------------	Code Begin	------------------------------------------*/

// add default values for $site_title and $site_slogan for all active languages

$sql = "select keel_id from keel where on_kasutusel = 1";
$result = new SQL($sql);
while($row = $result->fetch('ASSOC'))
{
	$curr_objekt = new Objekt(array(
		'objekt_id' => $site->alias(array(
			'key' => 'rub_home_id',
			'keel' => $row['keel_id'],
		)),
		'parent_id' => 0,
		'no_cache' => 1,
	));
	
	$conf = new CONFIG($curr_objekt->all['ttyyp_params']);
	
	if($curr_objekt->all['sys_alias'] == 'home')
	{
		$conf = new CONFIG($curr_objekt->all['ttyyp_params']);
		$conf->put('site_name', 'ShowTime');	
		$conf->put('slogan', 'Saurus CMS out-of-the-box experience');	
	
		$sql = $site->db->prepare(
			"UPDATE objekt SET ttyyp_params=? WHERE objekt_id=?", 
			$conf->Export(), $curr_objekt->objekt_id
		);
	
		$sth = new SQL($sql);
	}
}

// add timezone support to the CMS. 

//We start by checking if there is not already a table by the name of 'ext_timezones'.

$sql="SHOW TABLES LIKE 'ext_timezones'"; 
$result = new SQL($sql);

	if($result->rows >=1){

		echo "<br><font color=red>You already have a table called 'ext_timezones' in your database.</font>";

	}else{


//We create the table.

$create_sql="
CREATE TABLE ext_timezones (
  id int(10) unsigned NOT NULL auto_increment,
  profile_id int(4) unsigned NOT NULL default '0',
  name varchar(255) default NULL,
  UTC_dif float(7,2) default NULL,
  php_variable varchar(255) default NULL,
  PRIMARY KEY  (id)
)";
$create_result = new SQL($create_sql);

//Now we check if there is no Timezones profile in the CMS, if there is, time to fail again. 

$sql_check="select profile_id from object_profiles where name='Timezones'";
$check_result = new SQL($sql_check);
	if($check_result->rows >=1){
		echo "<br><font color=red>there already exists a profile called 'Timezones'</font>";
	}else{


		$sql = "insert into object_profiles set name='Timezones', data='a:3:{s:4:\"name\";a:9:{s:4:\"name\";s:4:\"name\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;s:10:\"is_general\";i:0;}s:7:\"UTC_dif\";a:9:{s:4:\"name\";s:7:\"UTC_dif\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:5:\"float\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;s:10:\"is_general\";i:0;}s:12:\"php_variable\";a:9:{s:4:\"name\";s:12:\"php_variable\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;s:10:\"is_general\";i:0;}}', source_table='ext_timezones'";
		$result = new SQL($sql);
		$profile_id=$result->insert_id;

		if(is_numeric($profile_id)){


$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-11', name='(GMT -11) Midway Island,Samoa', php_variable='Pacific/Midway'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-10', name='(GMT -10) Hawaii', php_variable='Pacific/Honolulu'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-9', name='(GMT -9) Alaska', php_variable='America/Adak'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-8', name='(GMT -8) Pacific Time (US & Canada),Tijuana', php_variable='America/Tijuana'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-7', name='(GMT -7) Arizona', php_variable='America/Phoenix'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-7', name='(GMT -7) Chihuahua, La Paz, Mazatlan', php_variable='America/Mazatlan'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-7', name='(GMT -7) Mountain Time (US & Canada)', php_variable='America/Dawson_Creek'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-6', name='(GMT -6) Central America', php_variable='America/Mexico_City'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-6', name='(GMT -6) Central Time (US & Canada)', php_variable='America/Regina'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-6', name='(GMT -6) Guadalajara, Mexico City, Monterrey', php_variable='America/Mexico_City'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-5', name='(GMT -5) Bogota, Lime, Quito', php_variable='America/Bogota'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-5', name='(GMT -5) Eastern Time (US & Canada)', php_variable='America/Indianapolis'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-5', name='(GMT -5) Indiana (East)', php_variable='America/Indianapolis'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-4', name='(GMT -4) Atlantic Time (Canada)', php_variable='America/Halifax'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-4', name='(GMT -4) Caracas, La Paz', php_variable='America/Caracas'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-4', name='(GMT -4) Santiago', php_variable='America/Santiago'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-3.5', name='(GMT -3:30) Newfoundland', php_variable='America/St_Johns'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-3', name='(GMT -3) Buenos Aires, Georgetown', php_variable='America/Buenos_Aires'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-3', name='(GMT -3) Greenland', php_variable='America/Thule'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-2', name='(GMT -2) Mid-Atlantic', php_variable='Atlantic/South_Georgia'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-1', name='(GMT -1) Azores', php_variable='Atlantic/Azores'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='-1', name='(GMT -1) Cape Verde Is.', php_variable='Atlantic/Cape_Verde'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='0', name='(GMT) Casablanca, Monrovia', php_variable='Africa/Monrovia'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='0', name='(GMT) Greenwich Mean Time - Dublin, Edinburgh, Lisbon, London', php_variable='Europe/London'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+1', name='(GMT +1) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna', php_variable='Europe/Vienna'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+1', name='(GMT +1) Belgrade, Bratislava, Budapest, Ljubljana, Prague', php_variable='Europe/Prague'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+1', name='(GMT +1) Brussels, Copenhagen, Madrid, Paris', php_variable='Europe/Paris'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+1', name='(GMT +1) Sarajevo, Skopje, Warsaw, Zagreb', php_variable='Europe/Zagreb'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+1', name='(GMT +1) West Central Africa', php_variable='Africa/Kinshasa'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+2', name='(GMT +2) Athens, Istanbul, Minsk', php_variable='Europe/Minsk'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+2', name='(GMT +2) Bucharest', php_variable='Europe/Bucharest'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+2', name='(GMT +2) Cairo', php_variable='Africa/Cairo'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+2', name='(GMT +2) Harare, Pretoria', php_variable='Africa/Harare'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+2', name='(GMT +2) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius', php_variable='Europe/Tallinn'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+2', name='(GMT +2) Jerusalem', php_variable='Asia/Jerusalem'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+3', name='(GMT +3) Baghdad', php_variable='Asia/Baghdad'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+3', name='(GMT +3) Kuwait, Riyadh', php_variable='Asia/Kuwait'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+3', name='(GMT +3) Moscow, St. Petersburg, Volgograd', php_variable='Europe/Moscow'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+3', name='(GMT +3) Nairobi', php_variable='Africa/Nairobi'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+3.5', name='(GMT +3:30) Tehran', php_variable='Asia/Tehran'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+4', name='(GMT +4) Abu Dhabi, Muscat', php_variable='Asia/Muscat'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+4', name='(GMT +4) Baku, Tbilisi, Yerevan', php_variable='Asia/Tbilisi'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+4', name='(GMT +4:30) Kabul', php_variable='Asia/Kabul'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+5', name='(GMT +5) Ekaterinburg', php_variable='Asia/Yekaterinburg'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+5', name='(GMT +5) Islamabad, Karachi, Tashkent', php_variable='Asia/Karachi'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+5.5', name='(GMT +5:30) Chennai, Kolkata, Mumbai, New Delhi', php_variable='Asia/Calcutta'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+5.75', name='(GMT +5:45) Kathmandu', php_variable='Asia/Katmandu'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+6', name='(GMT +6) Almaty, Novosibirsk', php_variable='Asia/Novosibirsk'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+6', name='(GMT +6) Astana, Dhaka', php_variable='Asia/Dhaka'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+6.5', name='(GMT +6:30) Rangoon', php_variable='Asia/Rangoon'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+7', name='(GMT +7) Bangkok, Hanoi, Jakarta', php_variable='Asia/Bangkok'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+7', name='(GMT +7) Krasnoyarsk', php_variable='Asia/Krasnoyarsk'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+8', name='(GMT +8) Beijing, Chongging, Hong Kong, Urumgi', php_variable='Asia/Hong_Kong'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+8', name='(GMT +8) Irkutsk, Ulaan Bataar', php_variable='Asia/Irkutsk'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+8', name='(GMT +8) Kuala Lumpur, Singapore', php_variable='Asia/Kuala_Lumpur'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+8', name='(GMT +8) Perth', php_variable='Australia/Perth'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+8', name='(GMT +8) Taipei', php_variable='Asia/Taipei'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+9', name='(GMT +9) Osaka, Sapporo, Tokyo', php_variable='Asia/Tokyo'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+9', name='(GMT +9) Seoul', php_variable='Asia/Seoul'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+9', name='(GMT +9) Yakutsk', php_variable='Asia/Yakutsk'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+9.5', name='(GMT +9:30) Adelaide', php_variable='Australia/Adelaide'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+9.5', name='(GMT +9:30) Darwin', php_variable='Australia/Darwin'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+10', name='(GMT +10) Brisbane', php_variable='Australia/Brisbane'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+10', name='(GMT +10) Canberra, Melbourne, Sydney', php_variable='Australia/Sydney'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+10', name='(GMT +10) Guam, Port Moresby', php_variable='Pacific/Guam'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+10', name='(GMT +10) Hobart', php_variable='Australia/Hobart'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+10', name='(GMT +10) Vladivostok', php_variable='Asia/Vladivostok'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+11', name='(GMT +11) Magadan, Solomon Is., New Caledonia', php_variable='Asia/Magadan'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+12', name='(GMT +12) Auckland, Wellington', php_variable='Pacific/Auckland'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+12', name='(GMT +12) Figi, Kamchatka, Marshall Is.', php_variable='Asia/Kamchatka'";
$update[]="insert into ext_timezones set profile_id='".$profile_id."', UTC_dif='+13', name='(GMT +13) Nuku\'alofa', php_variable='Pacific/Enderbury'";


foreach($update as $u){
	new SQL($u);
}


		}

	}
	}

// empty template cache, because the SAPI function are now in Smarty plugin format
// copy-paste from admin/clear_templ_cache.php (its nice that this is not a stand-alone fuction ...)

$templ_cache_path = $site->absolute_path.'classes/smarty/templates_c/';

function delete_templates_directory($file) { 
	chmod($file,0777); 
	if (is_dir($file)) { 
		$handle = opendir($file); 
		//while($filename = readdir($handle)) { 
		while (false !== ($file = readdir($handle))) { 
			if ($filename != "." && $filename != "..") { 
				delete_templates_directory($file."/".$filename); 
			} 
		} #while
		closedir($handle); 
		if (@rmdir($file)){return 1;}; 
	} else { 
		if(@unlink($file)) return 1; 
	} 
} 

if ($DIR = @opendir($templ_cache_path)) {


	############################
	# tsükkel üle failide
	while (false !== ($file = readdir($DIR))) { 
		if ($file != "." && $file != "..") { 
			if (!@delete_templates_directory($templ_cache_path.$file)){
				$err_catalogs[] = $templ_cache_path.$file;
			};
		} # ./..
	}
	# / tsükkel üle failide
	############################

	if (count($err_catalogs)){
		$error .= "<br><br><font color=red><b>Error! Make sure that directories:</b><br><br>";
		$error .= join("<br>", $err_catalogs);
		$error .=  "<br><br><b>have write permissions for the web server.</b><br></font>";
	}
	closedir($DIR); 
	clear_cache("ALL");
}
# kui kataloogi ei saa avada, kirjutada logisse veateade
else {
	print "<br><font color=red>Error! Can't open directory '".$templ_cache_path."'</font>";
}


/*---------------------------	Code End	------------------------------------------*/

if ($site->on_debug){

	$site->debug->msg('SQL pÃ¤ringute arv = '.$site->db->sql_count.'; aeg = '.$site->db->sql_aeg);
	$site->debug->msg('TÃ–Ã–AEG = '.$site->timer->get_aeg());
#	$site->debug->print_msg();

}
