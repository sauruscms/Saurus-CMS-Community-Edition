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


#############################################
#	Siin asuvad k6ik lisa funktsioonid		#
#	(mis ei ole klassidega seotud)			#
#											#
#############################################

function calculate_formula($params, $formula = "") {
	extract($params);
	eval($formula);

	return $result;
}

function print_filesize ($filesize) {
	if ($filesize>1073741824) {
		$result = sprintf("%.2f&nbsp;GB",($filesize/1073741824));
	} elseif ($filesize>1048576) {
		$result = sprintf("%.1f&nbsp;MB",($filesize/1048576));
	} elseif ($filesize>1024) {
		$result = sprintf("%.1f&nbsp;KB",($filesize/1024));
	} else {
		$result = sprintf("%.0f&nbsp;B",$filesize);
	}
	return $result ? $result : $filesize;
}


#############################################
# Kasutatakse mallis, kus on fix ID-d sees
# Tehtud replikatsiioni jaoks
function rep_id($vana_id, $sitename){
	global $site;
	
	if ( !$vana_id || !$sitename ) {
		return 0;
	}
	if ( $site->CONF['hostname'] == $sitename ){
		return $vana_id;
	} else {
		$sql = $site->db->prepare("
			SELECT objekt_id FROM objekt WHERE related_objekt_id = ?",
			$vana_id
		);
		$sth = new SQL($sql);
		$id = $sth->fetchsingle();
		$site->debug->msg($sth->debug->get_msgs());
		return ($sth->rows ? $id : $vana_id);
	}
}


#########################################
#	F-on kontrollib isikukoodi 6igsus.
#	Tagastab: 
#	1 - kui isikukood korrektne
#	0 - kui isikukood on ebakorrektne

	function isikukoodkorrektne () { 

		$args = func_get_arg(0);
		if (is_array($args)) {
			$isikukood = $args["isikukood"];
		} else {
			$isikukood = $args;
		}

			$nr = trim($isikukood);

			# Kui ei ole number v6i vahem kui 11 symboli, siis isikukood vigane:
			if (!is_numeric($nr) || strlen($nr)!=11){return 0;};

			$kontroll=substr($nr,10,1); // kontrollj�rk on 11 number
			
			$gender=0;
			if(substr($nr,0,1)==3 || substr($nr,0,1)==4 || substr($nr,0,1)==5) { $gender=1; }
			
			for($i=0;$i<=9;$i++) { // eemaldame viimase numbri ja inf array'sse
				$num[]=substr($nr,$i,1);
			}
			
			# v�heke matemaatikat:			
			$k1 = ($num[0] + $num[1] * 2 + $num[2] * 3 + $num[3] * 4 + $num[4] * 5 + $num[5] * 6 + $num[6] * 7 + $num[7] * 8 + $num[8] * 9 + $num[9]) % 11;
			if($k1=="10") {
				$k1 = ($num[0] * 3 + $num[1] * 4 + $num[2] * 5 + $num[3] * 6 + $num[4] * 7 + $num[5] * 8 + $num[6] * 9 + $num[7] + $num[8] * 2 + $num[9] * 3) % 11;
				if($k1=="10") { $k1="0"; }
			}
			
			# kui k1=kontroll siis number �ige:
			if($k1==$kontroll && strlen($nr)==11 && $gender==1) {	
				return 1;
			} else { 
				return 0;
			}

		}

###########################################
# Returns or prints variable content
# Use:
# $myarr = Array("aaa","bbb"=>"ggg","ccc"=>1);
# echo printr($myarr); - simple mode
# echo printr($myarr,0,1); - advanced(detailed) mode
# mail("evgeny@saurus.ee","test variable",printr($myarr,1)); - send to mail


	function printr($var,$dont_print=0,$adv_mode=0){
			
			ob_start();
			echo "<hr size=1><pre><font size=2 face=Verdana><b>VARIABLE:</b><br>";
			if ($adv_mode){
				var_dump($var);
			} else {
				print_r($var);
			}
			echo "</font></pre><hr size=1>";
			$result = ob_get_contents();
			ob_end_clean(); 

		if ($dont_print){
			return $result;
		} else {
			echo $result;
		}
	}

###########################################
# Returns safe filename
# stripped out special chars
# spaces replaced with "_"
# Use:
# $filename = safe_filename("S�steemi fail");
function safe_filename($name) {

	/*
	$name = preg_replace("/[^\w\.]/","_",$name);
	$name = str_replace(array('�','�','�','�','�','�','�','�'), array('O','o','A','a','O','o','U','u'), $name);
	*/

	return safe_filename2($name);
}

function safe_filename2($name)
{	
	if(!function_exists('create_alias_from_string'))
	{
		global $class_path;
		include_once($class_path.'adminpage.inc.php');
	}
	
	$string = create_alias_from_string($name,true);
	return $string;
}

#######################################
# Check e-mail format and MX-records 
# returns "1" - if mail ok, "0" - otherwise
function check_mail_mx($mail) { 
	$mailok=0;
		if (eregi("^[_\.0-9a-z-]+@([0-9a-z][-0-9a-z\.]+)\.([a-z]{2,4}$)", $mail, $check)) {
			if (getmxrr($check[1].".".$check[2],$tmp)) {$mailok=1;}
		} 
	return $mailok;
}

#######################################
# Returns array
function get_array_tree($temp_tree) {
	global $current_level;
	if ( is_array($temp_tree) ) {
		foreach ($temp_tree as $key => $value) {
			if (!$temp_tree[$key]['parent']) {
				// all right, it's a root category
				$current_level = 0;

				$new_tree[] = array(
				'id' => $temp_tree[$key]['id'],
				'name' => $temp_tree[$key]['name'],
				'parent' => $temp_tree[$key]['parent'],
				'level' => $current_level
				);
				if ($branch = get_array_leafs($temp_tree, $temp_tree[$key]['id'])) {
					// merge the new array with the old array
					$new_tree = array_merge($new_tree, $branch);
				}
			}
		}
	}

	return (isset($new_tree) ? $new_tree : false);
}

function get_array_leafs($temp_tree, $id) {
	global $current_level;

	$current_level++;

	foreach ($temp_tree as $key => $value) {
	if ($temp_tree[$key]['parent'] == $id) {
	// all right, the parent id is a match
	$new_tree[] = array(
	'id' => $temp_tree[$key]['id'],
	'name' => $temp_tree[$key]['name'],
	'parent' => $temp_tree[$key]['parent'],
	'level' => $current_level
	);

	if ($branch = get_array_leafs($temp_tree, $temp_tree[$key]['id'])) {
	// merge the new array with the old array
	$new_tree = array_merge($new_tree, $branch);
	}

	$current_level--;
	}
	}

	return (isset($new_tree) ? $new_tree : false);
}

function get_array_branch($temp_tree, $id) {
	global $current_level;

	$current_level--;
	if( is_array($temp_tree) ){
	foreach ($temp_tree as $key => $value) {
	if ($temp_tree[$key]['id'] == $id) {
		// all right, the id is a match leaf
		$new_tree[] = array(
		'id' => $temp_tree[$key]['id'],
		'name' => $temp_tree[$key]['name'],
		'parent' => $temp_tree[$key]['parent'],
		'level' => $current_level
		);

		if ($branch = get_array_branch($temp_tree, $temp_tree[$key]['parent'])) {
			// merge the new array with the old array
			$new_tree = array_merge($new_tree, $branch);
		}

		$current_level++;
		}
	}
	}

	return (isset($new_tree) ? $new_tree : false);
}


##########################################################
# Converts string from 'windows-1251' to another encoding
#	$str - string to convert;
#	$target_encoding possible values:
#	k - 'koi8-r' 
#	w - 'windows-1251'
#	i - 'iso8859-5' 
#	a,d - 'x-cp866'
#	m - 'x-mac-cyrillic'
#	u - 'UTF8'


function convert_cyrillic($str,$target_encoding) {

	$allowed_convert_params = Array('k','i','a','d','m','u');
	$target_encoding = strtolower($target_encoding);

	if (in_array($target_encoding, $allowed_convert_params)){
		if ($target_encoding=='u'){ # UTF-8


			   for($i=0,$m=strlen($str);$i<$m;$i++) {
				  $c=ord($str[$i]);

					# Bug #1608: Vene keele on-the-fly konvertimine ei t��ta UTF-8 ja Mozilla korral
					if ($c<=127) {$out.=chr($c); continue; }
					if ($c>=192 && $c<=207) {$out.=chr(208).chr($c-48); continue; }
					if ($c>=208 && $c<=239) {$out.=chr(208).chr($c-48); continue; }
					if ($c>=240 && $c<=255) {$out.=chr(209).chr($c-112); continue; }
					if ($c==184) { $out.=chr(209).chr(145); continue; }; 
					if ($c==168) { $out.=chr(208).chr(129); continue; };

					/* OLD: example from http://ee.php.net/convert_cyr_string by german at artexpert dot ee, 04-May-2003 03:13
				   if ($c>127) {// convert only special chars
					   if     ($c==184) $out.=chr(209).chr(209); // small io
					   elseif ($c==168) $out.=chr(208).chr(129); // capital io
					   else             $out.=($c>239?chr(209):chr(208)).chr($c-48);
				   } else {
					   $out.=$str[$i];
				   }
					*/
			   }

		} else {
			$out = convert_cyr_string($str, 'w', $target_encoding);
		}
	} else {
		$out = $str;	
	}

   return $out;
}



###########################################
# Returns string 
# of all parents for this objekt_id. 
# ID is separated by ','
# Use:
# echo get_all_parents(objekt_id);
	function get_all_parents($obj_id) {

		$sql = "SELECT objekt_id, parent_id FROM objekt_objekt";
		$sth = new SQL($sql);
		while (list($objekt_id, $parent_id) = $sth->fetch()) {
			$obj[$objekt_id][] = $parent_id;
		}
		if (!is_numeric($obj_id)) {
			return 0;
		}
		$parents = get_parents(&$obj, $obj_id, 0);
		if ($parents) {
			$parents .= $obj_id;
		} else {
			$parents = "ALL";
		}
		return $parents;
		
	}

	function get_parents($obj, $obj_id, $level) {
		if (is_array($obj[$obj_id])){
			if ($level <= 10) {
				foreach ($obj[$obj_id] as $parent_id) {
					if ($parent_id > 0) {
						$result = get_parents(&$obj, $parent_id, ++$level);
						if (!is_numeric($result)) {
							$parents .= $result;
							$parents .= $parent_id.",";
						} else {
							return 0;
						}
					}
				}
			} else {
				return 0;
			}
		}
		return $parents;
	}
#
###########################################


###########################################
# Delete records from cache table
# Use:
# clear_cache("ALL"); - delete all cache table (for site, admin-pages will stay in touh)
# DEPRECATED: clear_cache(125); - simple objekt_id
# DEPRECATED: clear_cache(array); - array of objekt_id
# DEPRECATED: clear_cache("125,126,127"); - string of objekt_id
function clear_cache($params) {
	
	global $site;

	// site based cache emptying for object edit windows
	//if(preg_match('/edit(.*)\.php/i', $site->script_name))
	if($site->script_name == 'edit.php')
	{
		$site_id = $_SESSION['keel']['keel_id'];
		if(is_numeric($site_id))
		{
			$sql = "DELETE FROM cache WHERE url <> '' and site_id = ".$site_id;
		}
		else 
		{
			$sql = "DELETE FROM cache WHERE url <> ''";
		}
	}
	else 
	{
		$sql = "DELETE FROM cache WHERE url <> ''";
	}
	
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
}
#
###########################################

###########################################
# Returns array 
# array(
#	"pages" => full string with page numbers
#	"page_numbers" => only page numbers
#   "pagenumbers_count" => number of single pagenumbers, miminum possible is 1
#	"current_pagenumber" => current single pagenumber
#	"previous" => link to the previous page of pagenumbers
#	"next" => link to the next page of numbers
#	"start" => starting from row,
#	"limit" => count of rows,
#	"limit_sql" => SQL query LIMIT 0,1
# )
#
# Use:
# get_page_numbers(array(
#	"total" => total amount of records
#	"limit" => default : $site->CONF['komment_arv_lehel'],
#	"amount_of_comment_pages" => default : $site->CONF['kommentaaride_lehekulgede_arv'],
#	"p_text" => default : $site->sys_sona(array(sona => "Lehekulg", tyyp=>"kujundus")).": ",
#	"link_class" => default : "",
#	"numbers_style" => default : "",
#	"separator" => default : " |",
#	"next_chr" => default : "&gt;&gt;",
#	"prev_chr" => default : "&lt;&lt;",
#	"page_q" => default : "page"; uses for get page attribute from fdat[] array
#	"page_seq" => default : "0";
#		if page_seq=1 then page sequence in compact mode(1|2|3|...)
#		otherwise in full (1-10|11-20|...)
#
#	"url" => "&param_name=param_value"; default : "",
# ));

function get_page_numbers() {
	
	global $site;
	
	$args = func_get_arg(0);
	
	###########################
	#Default values
	$amount_of_comment_pages = $args['amount_of_comment_pages'] ? $args['amount_of_comment_pages'] : intval($site->CONF['kommentaaride_lehekulgede_arv']);
	$amount_of_comment_pages = $amount_of_comment_pages > 0 ? $amount_of_comment_pages : 1;
	
	$limit = $args['limit'] ? $args['limit'] : intval($site->CONF['komment_arv_lehel']);
	$p_text = $args['p_text'] ? $args['p_text'] : $site->sys_sona(array(sona => "Lehekulg", tyyp=>"kujundus")).": ";
	$link_class = $args['link_class'] ? $args['link_class'] : "";

	$separator = $args['separator'] ? $args['separator'] : " |";

	$next_chr = $args['next_chr'] ? $args['next_chr'] : "&gt;&gt;";

	$prev_chr = $args['prev_chr'] ? $args['prev_chr'] : "&lt;&lt;";

	$page_q = $args['page_q'] ? $args['page_q'] : "page";

	$page_seq = $args['page_seq'] ? $args['page_seq'] : 0;
	# /Default values
	############################
	$url = $args['url'];
	$id = $site->fdat['id'] ? "?id=".$site->fdat['id']."&" : "?";
	$numbers_style = $args['numbers_style'];
	$current_pagenumber = $site->fdat[$page_q];

	$site->fdat[$page_q] = $site->fdat[$page_q] ? $site->fdat[$page_q]-1 : 0;

	$start = $site->fdat[$page_q]*$limit;

	$naita = " LIMIT ".$start.", ".$limit;
	$kokku = intval($args['total']);

	$page_numbers = "";
	$tot_page = ceil($kokku/$limit)-1;
	if ( ($site->fdat[$page_q]+1)/$amount_of_comment_pages > 1 ) {
		$i = floor ( ($site->fdat[$page_q])/$amount_of_comment_pages )*$amount_of_comment_pages;
	} else {
		$i = 0;
	}
	$count = 0;
	$eelmised = $i>0 ? "<a href=\"".$id.$page_q."=".$i.($url ? $url : "")."\"".($link_class ? " ".$link_class : "").">".$prev_chr."</a>" : "";

	while ( $i<=$tot_page && $count<$amount_of_comment_pages ) {
		if ($count == ($amount_of_comment_pages-1) && $i < $tot_page) {
			$jargmised = " <a href=\"".$id.$page_q."=".($i+2).($url ? $url : "")."\"".($link_class ? " ".$link_class : "").">".$next_chr."</a>";
			$separator_str = "";
		} else {
			$separator_str = (($i+1)*$limit) >= $kokku ? "" : $separator;
		}
		$pagenum = $i + 1;
		if ($page_seq) {
			$lopp = $pagenum*$limit < $kokku ? $pagenum*$limit : $kokku;
			$algus = $pagenum*$limit - $limit + 1;
			$text = $algus."-".$lopp;
		} else {
			$text = $i+1;
		}
		if ( $i==$site->fdat[$page_q] ) {
		$page_numbers .= "  ".$text.$separator_str; 
		}
		else {
		$page_numbers .= "  <a href=\"".$id.$page_q."=".($i+1).($url ? $url : "")."\"".($numbers_style ? " ".$numbers_style : "").">".$text."</a>".$separator_str;
		}
		$count++;
		$i++;
	}
	if ( $i > 1 ) {
		$pages = $p_text.$eelmised.$page_numbers.$jargmised;
	} else {
		$page_numbers = "";
		$pages = "";
	}

	$result = array(
		"pages" => $pages,
		"pages_count" => $tot_page,
		"pagenumbers_count" => ($tot_page <= 0 ? 1 : $tot_page+1),
		"page_numbers" => $page_numbers,
		"current_pagenumber" => $current_pagenumber,
		"previous" => $eelmised,
		"next" => $jargmised,
		"start" => $start,
		"limit" => $limit,
		"limit_sql" => $naita,
	);
	
	return $result;
}
#
##################################################


##################################################
# Returns date of Monday by the week number.
# Example:
# date("d.m.Y", get_monday(42, 2003));   // it returns: "13.10.2003" 
#

function get_monday($week, $year=""){

		# check for PHP internal bug
		$phpbug = strtotime("next Monday", 1041372000);
		if ($phpbug == 1041804000){
			$bugfix = 0;
		} else {
			$bugfix = 1;
		}

		$first_date = strtotime("1 january ".($year ? $year : date("Y")));

		if(date("D", $first_date)=="Mon") {
			$monday = $first_date;
		} else {
			$monday = strtotime("next Monday", $first_date)-604800;
		}
		$plus_week = "+".($week-1-$bugfix)." week";

	return strtotime($plus_week, $monday);
}


# analog to get_monday(), but returns Sunday
function get_sunday($week, $year=""){
	return get_monday($week, $year)+604799;
}

##################################################
# Translates estonian CMS system strings to english
# (mostly used for giving out correct english terms in Saurus API)
# Example:
# $class = translate_en("rubriik");   // returns: "section" 
#

function translate_en($word){

	$translation = array();

	$word = strtolower($word);

	$translation['rubriik'] = 'section';
	$translation['artikkel'] = 'article';
	$translation['link'] = 'link';
	$translation['pilt'] = 'image';
	$translation['kommentaar'] = 'comment';
	$translation['gallup'] = 'poll';
	$translation['dokument'] = 'document';
	$translation['kogumik'] = "article's list";
	$translation['lingikast'] = 'link list';
	$translation['loginkast'] = 'login-box';
	$translation['teema'] = 'subject';
	$translation['album'] = 'album';
	$translation['iframekast'] = 'iframe-box';
	$translation['s�ndmus'] = 'event';
	$translation['eriobjekt'] = 'asset';

	$translation['kasutaja_id'] = 'user_id';
	$translation['kasutaja'] = 'user';
	$translation['eesnimi'] = 'name';
	$translation['perenimi'] = 'surname';
	$translation['tiitel'] = 'job title';
	$translation['aeg'] = 'time';
	$translation['on_lukus'] = 'is_locked';
	$translation['isikukood'] = 'id_code';
	$translation['postiaadress'] = 'address';
	$translation['postiindeks'] = 'post_code';
	$translation['telefon'] = 'phone';
	$translation['s�ndmus'] = 'event';
	$translation['tyhiotsing'] = 'no_search_results';
	
	// search params
	$translation['otsi'] = 'query';
	$translation['ilma'] = 'exclude';
	$translation['cat'] = 'section';

	/* op= translations */
	$translation['kaart'] = 'sitemap';
	$translation['tappisotsing'] = 'advsearch';
	$translation['saadaparool'] = 'remindpass';
	$translation['arhiiv'] = 'archive';
	$translation['gallup_arhiiv'] = 'poll_archive';
	
	if (isset($translation[$word])) {
		return $translation[$word];
	}
	else return $word;
}

##################################################
# Translates CMS system strings in english to estonian
# (mostly used for getting Saurus API parameters in english)
# Example:
# $class = translate_ee("section");   // returns: "rubriik" 
#

function translate_ee($word){

	$translation = array();

	$word = strtolower($word);

	$translation['section'] = 'rubriik';
	$translation['article'] = 'artikkel';
	$translation['link'] = 'link';
	$translation['image'] = 'pilt';
	$translation['comment'] = 'kommentaar';
	$translation['poll'] = 'gallup';
	$translation['document'] = 'dokument';
	$translation["article's list"] = 'kogumik';
	$translation['link list'] = 'lingikast';
	$translation['login-box'] = 'loginkast';
	$translation['subject'] = 'teema';
	$translation['album'] = 'album';
	$translation['iframe-box'] = 'iframekast';
	$translation['event'] = 's�ndmus';
	$translation['asset'] = 'eriobjekt';

	$translation['user_id'] = 'kasutaja_id';
	$translation['user'] = 'kasutaja';
	$translation['name'] = 'eesnimi';
	$translation['surname'] = 'perenimi';
	$translation['job title'] = 'tiitel';
	$translation['time'] = 'aeg';
	$translation['is_locked'] = 'on_lukus';
	$translation['id_code'] = 'isikukood';
	$translation['address'] = 'postiaadress';
	$translation['post_code'] = 'postiindeks';
	$translation['phone'] = 'telefon';
	$translation['event'] = 's�ndmus';
	$translation['no_search_results'] = 'tyhiotsing';
	
	/* op= translations */
	$translation['sitemap'] = 'kaart';
	$translation['advsearch'] = 'tappisotsing';
	$translation['remindpass'] = 'saadaparool';
	$translation['archive'] = 'arhiiv';
	$translation['poll_archive'] = 'gallup_arhiiv';

	if (isset($translation[$word])) {
		return $translation[$word];
	}
	else return $word;
}

function array_minus_array($a, $b) {
       $c=array_diff($a,$b);
       $c=array_intersect($c, $a);
       return $c;
}

####################################################
# Evgeny 16.04.2004
# Function changes current script-name with given name.
# It's need to put more, than one file under one admin privelege (in Admin area). In this case given filename must be name, defined in table 'admin_osa.file'.
# Then you use this function, don't use after it "$site->self" in your code, because it will be wrong.
#
# example: extend_admin_osa('change_meta.php'); // privileges for your file will be same, like for file 'change_meta.php'
#

function extend_admin_osa($filename){

		# lets check if web server is Apache or not
		if(preg_match("/apache/i", $_SERVER["SERVER_SOFTWARE"])){
			$script_name = $_SERVER["REQUEST_URI"]; # kui apache
		} else {
			$script_name = $_SERVER["SCRIPT_NAME"]; # kui muu (nt IIS)
		}

		$name_pos = strrpos($script_name, "/");
		$orig_file = substr($script_name, $name_pos+1);
		$result = str_replace($orig_file, $filename, $script_name);

		$_SERVER["REQUEST_URI"] = $result;
		$_SERVER["SCRIPT_NAME"] = $result;

	return 1;
}

############################################################
# Parameetriks on mingi tekst. 
# Funktsiooni resultaat: kui tekstis on olemas s�na, 
# mis algab http://, https:// voi ftp://-ga, 
# siis muudab funktsioon html-tagiks, kirjutab lingi 
# �mber <a>-tagi. Samuti toimimine e-posti aadressitega, 
# milliseid sisaldavad @-marki ning punkti. 
function add_html_tags($text = '') {
	return preg_replace(array("/(?:http|https|ftp):\/\/([\w\d\.\:\/\=\?\&\-]+)?(?:\/\S*)?/i", "/[\w\d]+([_\.-][\w\d]+)*@([\w\d]+([\.-][\w\d]+)*)+\\.[\w]{2,}/i"), array("<a href='\\0'>\\0</a>", "<a href='mailto:\\0'>\\0</a>"), $text);
}

#############################################################
# Parameeter teksti muutuja. Resultaat: tekst, 
# milles on muudetud emotikonid pildiks.
# -----------------------------------------------------------
# :D - icon_biggrin.gif, Very Happy
# :-D - icon_biggrin.gif, Very Happy
# :grin: - icon_biggrin.gif, Very Happy
# :) - icon_smile.gif, Smile
# :-) - icon_smile.gif, Smile
# :smile: - icon_smile.gif, Smile
# :( - icon_sad.gif, Sad
# :-( - icon_sad.gif, Sad
# :sad: - icon_sad.gif, Sad
# :o - icon_surprised.gif, Surprised
# :-o - icon_surprised.gif, Surprised
# :eek: - icon_surprised.gif, Surprised
# :shock: - icon_eek.gif, Shocked
# :? - icon_confused.gif, Confused
# :-? - icon_confused.gif, Confused
# :???: - icon_confused.gif, Confused
# 8) - icon_cool.gif, Cool
# 8-) - icon_cool.gif, Cool
# :cool: - icon_cool.gif, Cool
# :lol: - icon_lol.gif, Laughing
# :x - icon_mad.gif, Mad
# :-x - icon_mad.gif, Mad
# :mad: - icon_mad.gif, Mad
# :P - icon_razz.gif, Razz
# :-P - icon_razz.gif, Razz
# :razz: - icon_razz.gif, Razz
# :oops: - icon_redface.gif, Embarassed
# :cry: - icon_cry.gif, Crying or Very sad
# :evil: - icon_evil.gif, Evil or Very Mad
# :twisted: - icon_twisted.gif, Twisted Evil
# :roll: - icon_rolleyes.gif, Rolling Eyes
# :wink: - icon_wink.gif, Wink
# ;) - icon_wink.gif, Wink
# ;-) - icon_wink.gif, Wink
# :!: - icon_exclaim.gif, Exclamation
# :?: - icon_question.gif, Question
# :idea: - icon_idea.gif, Idea
# :arrow: - icon_arrow.gif, Arrow
# :| - icon_neutral.gif, Neutral
# :-| - icon_neutral.gif, Neutral
# :neutral: - icon_neutral.gif, Neutral
# :mrgreen: - icon_mrgreen.gif, Mr. Green

function do_smileys($text = '') {
	global $site;
	
	$emotion_path = $site->CONF['wwwroot'].$site->CONF['styles_path']."/gfx/emoticons/";
	$patterns = array(
		"/(?::D|:-D|:grin:)/", 
		"/(?::\)|:-\)|:smile:)/", 
		"/(?::\(|:-\(|:sad:)/", 
		"/(?::o|:-o|:eek:)/", 
		"/(?::shock:)/", 
		"/(?::\?|:-\?|:\?\?\?:)/", 
		"/(?:8\)|8-\)|:cool:)/", 
		"/(?::lol:)/", 
		"/(?::x|:-x|:mad:)/", 
		"/(?::P|:-P|:razz:)/", 
		"/(?::oops:)/", 
		"/(?::cry:)/", 
		"/(?::evil:)/", 
		"/(?::twisted:)/", 
		"/(?::roll:)/", 
		"/(?::wink:|;\)|;-\))/", 
		"/(?::!:)/", 
		"/(?::\?:)/", 
		"/(?::idea:)/", 
		"/(?::arrow:)/", 
		"/(?::\||:-\||:neutral:)/", 
		"/(?::mrgreen:)/"
	);

	$replace = array(
		"<img src=\"".$emotion_path."icon_biggrin.gif\" width=\"15\" height=\"15\" alt=\"Very Happy\">", 
		"<img src=\"".$emotion_path."icon_smile.gif\" width=\"15\" height=\"15\" alt=\"Smile\">", 
		"<img src=\"".$emotion_path."icon_sad.gif\" width=\"15\" height=\"15\" alt=\"Sad\">", 
		"<img src=\"".$emotion_path."icon_surprised.gif\" width=\"15\" height=\"15\" alt=\"Surprised\">", 
		"<img src=\"".$emotion_path."icon_eek.gif\" width=\"15\" height=\"15\" alt=\"Shocked\">", 
		"<img src=\"".$emotion_path."icon_confused.gif\" width=\"15\" height=\"15\" alt=\"Confused\">", 
		"<img src=\"".$emotion_path."icon_cool.gif\" width=\"15\" height=\"15\" alt=\"Cool\">", 
		"<img src=\"".$emotion_path."icon_lol.gif\" width=\"15\" height=\"15\" alt=\"Laughing\">", 
		"<img src=\"".$emotion_path."icon_mad.gif\" width=\"15\" height=\"15\" alt=\"Mad\">", 
		"<img src=\"".$emotion_path."icon_razz.gif\" width=\"15\" height=\"15\" alt=\"Razz\">", 
		"<img src=\"".$emotion_path."icon_redface.gif\" width=\"15\" height=\"15\" alt=\"Embarassed\">", 
		"<img src=\"".$emotion_path."icon_cry.gif\" width=\"15\" height=\"15\" alt=\"Crying or Very sad\">", 
		"<img src=\"".$emotion_path."icon_evil.gif\" width=\"15\" height=\"15\" alt=\"Evil or Very Mad\">", 
		"<img src=\"".$emotion_path."icon_twisted.gif\" width=\"15\" height=\"15\" alt=\"Twisted Evil\">", 
		"<img src=\"".$emotion_path."icon_rolleyes.gif\" width=\"15\" height=\"15\" alt=\"Rolling Eyes\">", 
		"<img src=\"".$emotion_path."icon_wink.gif\" width=\"15\" height=\"15\" alt=\"Wink\">", 
		"<img src=\"".$emotion_path."icon_exclaim.gif\" width=\"15\" height=\"15\" alt=\"Exclamation\">", 
		"<img src=\"".$emotion_path."icon_question.gif\" width=\"15\" height=\"15\" alt=\"Question\">", 
		"<img src=\"".$emotion_path."icon_idea.gif\" width=\"15\" height=\"15\" alt=\"Idea\">", 
		"<img src=\"".$emotion_path."icon_arrow.gif\" width=\"15\" height=\"15\" alt=\"Arrow\">", 
		"<img src=\"".$emotion_path."icon_neutral.gif\" width=\"15\" height=\"15\" alt=\"Neutral\">", 
		"<img src=\"".$emotion_path."icon_mrgreen.gif\" width=\"15\" height=\"15\" alt=\"Mr. Green\">"
	);

	return preg_replace($patterns, $replace, $text);
}

/**
 * Password crypting method used in CMS
 *
 * @param string $password
 * @return string
 */
function encrypt_password($password)
{
	return crypt($password, Chr(rand(65,91)).Chr(rand(65,91)));
}

/**
 * Tries to determine files mime type, return defaults to: application/octet-stream
 *
 * @param string $file file to acceess
 * @return string
 */
function get_file_mime_content_type($file)
{
	// use the mime_content_type
	if(function_exists('mime_content_type'))
	{
		$mime_content_type = mime_content_type($file);
	}
	// try the system
	elseif(function_exists('popen'))
	{
		$phandle = popen('file -bi '.$file, 'r');
		$output = fread ($phandle, 1024);
		pclose ($phandle);
		$mime_content_type = str_replace(array("\n", "\r\n"), '', $output);
	}
	
	return ($mime_content_type ? $mime_content_type : 'application/octet-stream');
}

/**
 * Replacement for fopen when opening url's behind basic HTTP authentication. Returns the response or false if fails.
 *
 * @param string $url
 * @param string $user
 * @param string $pass
 * @param string $agent
 * @return mixed
 */
function fopen_url_auth($url, $user, $pass, $agent)
{
	global $site, $CONF;

	$url_parsed = parse_url($url);
	$host = $url_parsed['host'];
	
	if (!empty($host)){
		$port = $url_parsed['port'];
		if ($port == 0) $port = 80;
		$path = $url_parsed['path'];
		if (empty($path)) $path = '/';
		if ($url_parsed['query'] != '')	$data_to_send = $url_parsed['query'];
	}

	if($CONF)
	{
		$configuration = $CONF;
	}
	else 
	{
		$configuration = $site->ReadConfDB();
	}

	if ($configuration["proxy_server"] != '') {
		if ($configuration["proxy_server_port"] == "" || $configuration["proxy_server_port"] == 0) {
			$configuration["proxy_server_port"] = 80;
		}
		$fp = @fsockopen($configuration["proxy_server"], $configuration["proxy_server_port"], $err_num, $err_msg, 30);
	} else {
		$fp = @fsockopen($host, $port, $err_num, $err_msg, 30);
	}
	if (!$fp)
	{
		return false;
	}
	else
	{
		$auth = $user.':'.$pass;
		$string = base64_encode($auth);
		
		$headers = '';

		$headers .= "GET ".$url." HTTP/1.1\r\n";
		$headers .= "Authorization: Basic ".$string."\r\n";
		$headers .= "User-Agent: ".$agent."\r\n";
		$headers .= "Host: $host\r\n";
		$headers .= "Content-type: application/x-www-form-urlencoded\r\n";
		$headers .= "Content-length: ".strlen($data_to_send)."\r\n";
		$headers .= "Connection: close\r\n\r\n";
		$headers .= $data_to_send;

		fputs($fp, $headers);

		$response = '';
		
		while (!feof($fp))
		{
		   $response .= fgets($fp, 128);
		}
		
		fclose($fp);
		
		// remove th HTTP header, headers are separated by a double CRLF/newline (\r\n\r\n)
		$response = explode("\r\n\r\n", $response);
		array_shift($response);
		$response = implode("\r\n\r\n", $response);

		return $response;
	}
}

/**
 * XSS Clean
 *
 * Sanitizes data so that Cross Site Scripting Hacks can be
 * prevented.� This function does a fair amount of work but
 * it is extremely thorough, designed to prevent even the
 * most obscure XSS attempts.� Nothing is ever 100% foolproof,
 * of course, but I haven't been able to get anything passed
 * the filter.
 *
 * Note: This function should only be used to deal with data
 * upon submission.� It's not something that should
 * be used for general runtime processing.
 *
 * This function was based in part on some code and ideas I
 * got from Bitflux: http://blog.bitflux.ch/wiki/XSS_Prevention
 *
 * To help develop this script I used this great list of
 * vulnerabilities along with a few other hacks I've 
 * harvested from examining vulnerabilities in other programs:
 * http://ha.ckers.org/xss.html
 *
 * @access	public
 * @param	string
 * @return	string
 */
function xss_clean($str)
{	
	$str = (string)$str;
	/*
	 * Remove Null Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 */
	$str = preg_replace('/\0+/', '', $str);
	$str = preg_replace('/(\\\\0)+/', '', $str);

	/*
	 * Validate standard character entites
	 *
	 * Add a semicolon if missing.  We do this to enable
	 * the conversion of entities to ASCII later.
	 *
	 */
	$str = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u',"\\1;",$str);
	
	/*
	 * Validate UTF16 two byte encodeing (x00) 
	 *
	 * Just as above, adds a semicolon if missing.
	 *
	 */
	$str = preg_replace('#(&\#x*)([0-9A-F]+);*#iu',"\\1\\2;",$str);

	/*
	 * URL Decode
	 *
	 * Just in case stuff like this is submitted:
	 *
	 * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
	 *
	 * Note: Normally urldecode() would be easier but it removes plus signs
	 *
	 */	
	$str = preg_replace("/%u0([a-z0-9]{3})/i", "&#x\\1;", $str);
	$str = preg_replace("/%([a-z0-9]{2})/i", "&#x\\1;", $str);        
    		
	/*
	 * Convert character entities to ASCII 
	 *
	 * This permits our tests below to work reliably
	 *
	 */
	$str = html_entity_decode($str, ENT_COMPAT);		

	/*
	 * Convert all tabs to spaces
	 *
	 * This prevents strings like this: ja	vascript
	 * Note: we deal with spaces between characters later.
	 *
	 */		
	$str = preg_replace("#\t+#", " ", $str);

	/*
	 * Makes PHP tags safe
	 *
	 *  Note: XML tags are inadvertently replaced too:
	 *
	 *	<?xml
	 *
	 * But it doesn't seem to pose a problem.
	 *
	 */		
	$str = str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);

	/*
	 * Compact any exploded words
	 *
	 * This corrects words like:  j a v a s c r i p t
	 * These words are compacted back to their correct state.
	 *
	 */		
	$words = array('javascript', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
	foreach ($words as $word)
	{
		$temp = '';
		for ($i = 0; $i < strlen($word); $i++)
		{
			$temp .= substr($word, $i, 1)."\s*";
		}
		
		$temp = substr($temp, 0, -3);
		$str = preg_replace('#'.$temp.'#is', $word, $str);
	}

	/*
	 * Remove disallowed Javascript in links or img tags
	 */		
	 $str = preg_replace("#<a.+?href=.*?(alert\(|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>.*?</a>#si", "", $str);
	 $str = preg_replace("#<img.+?src=.*?(alert\(|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>#si", "", $str);
	 $str = preg_replace("#<(script|xss).*?\>#si", "", $str);

	/*
	 * Remove JavaScript Event Handlers
	 *
	 * Note: This code is a little blunt.  It removes
	 * the event handler and anything upto the closing >, 
	 * but it's unlkely to be a problem.
	 *
	 */		
	 $str = preg_replace('#(<[^>]+.*?)(onblur|onchange|onclick|onfocus|onload|onmouseover|onmouseup|onmousedown|onselect|onsubmit|onunload|onkeypress|onkeydown|onkeyup|onresize)[^>]*>#iU',"\\1>",$str);

	/*
	 * Sanitize naughty HTML elements
	 *
	 * If a tag containing any of the words in the list 
	 * below is found, the tag gets converted to entities.
	 *
	 * So this: <blink>
	 * Becomes: &lt;blink&gt;
	 *
	 */		
	$str = preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "&lt;\\1\\2\\3&gt;", $str);
	
	/*
	 * Sanitize naughty scripting elements
	 *
	 * Similar to above, only instead of looking for
	 * tags it looks for PHP and JavaScript commands
	 * that are disallowed.  Rather than removing the
	 * code, it simply converts the parenthesis to entities
	 * rendering the code unexecutable.
	 *
	 * For example:	eval('some code')
	 * Becomes:		eval&#40;'some code'&#41;
	 *
	 */
	$str = preg_replace('#(alert|cmd|passthru|eval|exec|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);
					
	/*
	 * Final clean up
	 *
	 * This adds a bit of extra precaution in case
	 * something got through the above filters
	 *
	 */	
	$bad = array(
					'document.cookie'	=> '',
					'document.write'	=> '',
					'window.location'	=> '',
					"javascript\s*:"	=> '',
					"Redirect\s+302"	=> '',
					'<!--'				=> '&lt;!--',
					'-->'				=> '--&gt;'
				);

	foreach ($bad as $key => $val)
	{
		$str = preg_replace("#".$key."#i", $val, $str);   
	}
	
	return $str;
}
// END xss_clean()

/**
 * humand readable form of bytes
 *
 * @param integer $bytes
 * @return string
 */
function human_readable_file_size($bytes)
{
	$type = array(('B'), ('KB'), ('MB'), ('GB'), ('TB'), ('PB'), );
	
	$index = 0;
	
	while($bytes >= 1024)
	{
		$bytes /= 1024;
		$index++;
	}
	
	if($index == 0)
	{
		return sprintf('%.0f', ($bytes)).' '.$type[$index];
	}
	else 
	{
		return sprintf('%.2f', ($bytes)).' '.$type[$index];
	}
}

/**
 * convert local link to alias,
 * if given link is not local, returns the link unchanged
 *
 * @param string $bytes
 * @return string
 */
function convert_local_link_to_alias($link) {
	global $site;

	$objektUrl = $link;

	$queryArray = array();
	$idValue = '';

    # bug #2882
    if (preg_match("/^(.*:+(\/*))$/i", $objektUrl)) {
        # in case of invalid url, the url is returned unchanged
        return $objektUrl;
    }
    $urlArray = parse_url($objektUrl);

	$separator = (strpos($urlArray['query'], '&amp;') !== false ? '&amp;' : '&');
	foreach (explode($separator, $urlArray['query']) as $value) {
		$query = explode('=', $value);
		if ($query[0] != 'id') {
			$queryArray[] = $value;
		} else {
			$idValue = $query[1];
		}
	}
	
	if (count($queryArray) > 0) {
		$param = '?' . implode('&amp;', $queryArray);
	} else {
		$param = '';
	}

	// check if link is local and id parameter was given
	if (($urlArray['host'] == $_SERVER['SERVER_NAME'] || $urlArray['host'] == '') &&
		($urlArray['path'] == $site->wwwroot . '/' || $urlArray['path'] == '') && (is_numeric($idValue))) {

		$linkObj = new Objekt(array(
			objekt_id => $idValue,
		));

		// if http missing, add it
		if (!$urlArray['scheme']) {
			$urlArray['scheme'] = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
		}
		// replace link
		$objektUrl = $urlArray['scheme'] . '://' . $_SERVER['SERVER_NAME'] . $linkObj->get_object_href() . $param;
	}

	return $objektUrl;
}