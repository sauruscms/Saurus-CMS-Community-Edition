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


#########################################################
# Stuff for redirects with mod_rewrite and without it
#
# Note: this file replaced file "map" starting from 4.3.0
# Note: rewrite for long and language aliases from 4.5.0
#

error_reporting(0);
Global $site, $class_path, $forceRedirect;

// if alias redirecting is on - in case id is given in url, force redirecting in index.php
if ($_GET['id']) {
	$forceRedirect = true;
}

# l�pust /admin|editor|classes/ maha
if (preg_match("/(.*)\/(admin|editor|classes|temp)\/$/", getcwd(), $matches)){
	$cls_pth=realpath("./classes/");
}else{
	$cls_pth=realpath("./classes/");
}

//ini_set('session.cache_limiter', 'private'); // cant be done, messes up reg users view and log in ifno is not available
session_start();

include_once($cls_pth."/custom.inc.php");

$_SESSION['alias']="";
$uri = $_SERVER['REQUEST_URI'] = str_replace($_GET['cmd'], '', $_SERVER['REQUEST_URI']);

#echo "<br><br>".$_SERVER['REQUEST_URI']."<br><br>";
if (substr_count($uri,'/px')){
	exit;
}

$tmp_cmd=explode("/",$_GET['cmd']);


if($_SERVER['SERVER_PORT']!=80){
$server_port = ":".$_SERVER['SERVER_PORT'];
}

$fn=explode("/",$_SERVER["SCRIPT_FILENAME"]);
$folder_name=$fn[sizeof($fn)-2];

$process=0;
foreach($tmp_cmd as $t){

	// if the there is a .php in the URL then don't use aliases go directly to that file
	if(preg_match('/\.php$/i', $t) && file_exists($t) && !preg_match("#^\.\./#", $t))
	{
		$_SERVER['SCRIPT_NAME'] = str_replace('map.php', $t, $_SERVER['SCRIPT_NAME']);
		
		// if alias redirecting is on, force redirecting in index.php
		$forceRedirect = true;
	
		include($t);
		exit;
	}
	
	if($process == 1&&!empty($t)){
		$ncmd[]=$t;
	}
	if($t==$folder_name){
		$process=1;

	}

}

//Now if we found no folder name, then the case is that they are all aliases. (web page is located in a xxx.domain.yyy)

if($process!=1){
	foreach($tmp_cmd as $t){
		if(!empty($t)){
			$ncmd[]=$t;
		}
	}
}




//We create the necessary variable array just in case we are going to need it here

$variable=array();

	foreach($_GET as $k=>$v){
		if($k!="mod_rewrite" && $k!="cmd"){
			if($k=="id" && is_numeric($v)){
			$special_case=true;
			}
			$variable[]=$k."=".$v;
		}
	}


if(!$special_case){
$new_url = array();

function find_alias($name,$parent,$lang,$strict){
global $conn, $forceRedirect;
	//the name may be either just an ID of the menu or a friendly_url.

if($parent==""){
		$sql = $conn->prepare("SELECT objekt_id, keel FROM objekt WHERE friendly_url=? and keel=? LIMIT 1",$name,$lang);
}else{
		$sql = $conn->prepare("SELECT t1.objekt_id, t1.keel FROM objekt as t1, objekt_objekt as t2 WHERE t1.friendly_url=? and t1.objekt_id=t2.objekt_id and t2.parent_id=? LIMIT 1",$name,$parent);

}

		$sth = new SQL($sql);
		if($data = $sth->fetch()){

			$objects_arr['objekt_id'] = $data['objekt_id'];
			$objects_arr['lang_id'] = $data['keel'];
			$objects_arr['parent_id'] = $parent;

			return $objects_arr;

		}elseif(!$strict){

			if($parent==""){
					$sql = $conn->prepare("SELECT t1.objekt_id, t1.keel, t2.extension FROM objekt as t1, keel as t2 WHERE t1.friendly_url=? and t1.keel=t2.keel_id order by t2.on_default desc LIMIT 1",$name,$lang);
			}else{
					$sql = $conn->prepare("SELECT t1.objekt_id, t1.keel, t3.extension FROM objekt as t1, objekt_objekt as t2 WHERE t1.friendly_url=? and t1.objekt_id=t2.objekt_id and t2.parent_id=? and t1.keel=t3.keel_id order by t3.on_default desc LIMIT 1",$name,$parent);
			}

			$sth = new SQL($sql);
				if($data = $sth->fetch()){

					$objects_arr['objekt_id'] = $data['objekt_id'];
					$objects_arr['lang_id'] = $data['keel'];
					$objects_arr['lang_ext'] = $data['extension'];
					$objects_arr['parent_id'] = $parent;

					return $objects_arr;

				}elseif(is_numeric($name)){

					$forceRedirect = true;

					if($parent==""){
							$sql = $conn->prepare("SELECT objekt_id, keel FROM objekt WHERE objekt_id=? LIMIT 1",$name);
					}else{
							$sql = $conn->prepare("SELECT t1.objekt_id, t1.keel FROM objekt as t1, objekt_objekt as t2 WHERE t1.objekt_id=? and t1.objekt_id=t2.objekt_id and t2.parent_id=? LIMIT 1",$name,$parent);
					}
							$sth = new SQL($sql);
							if($data = $sth->fetch()){

								$objects_arr['objekt_id'] = $data['objekt_id'];
								$objects_arr['lang_id'] = $data['keel'];
								$objects_arr['parent_id'] = $parent;

								return $objects_arr;
							}


				}else{
					
					return false;

				}


		}elseif(is_numeric($name)){

			$forceRedirect = true;

			if($parent==""){
					$sql = $conn->prepare("SELECT objekt_id, keel FROM objekt WHERE objekt_id=? and keel=? LIMIT 1",$name,$lang);
			}else{
					$sql = $conn->prepare("SELECT t1.objekt_id, t1.keel FROM objekt as t1, objekt_objekt as t2 WHERE t1.objekt_id=? and t1.keel=? and t1.objekt_id=t2.objekt_id and t2.parent_id=? LIMIT 1",$name,$lang,$parent);
			}
		$sth = new SQL($sql);
		if($data = $sth->fetch()){

			$objects_arr['objekt_id'] = $data['objekt_id'];
			$objects_arr['lang_id'] = $data['keel'];
			$objects_arr['parent_id'] = $parent;

			return $objects_arr;
		}

		}else{

			return false;

		}
}


####################################################################
# If qry string not empty, this means, file was ran from RewriteEngine
$QUERY_STRING = $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : $_SERVER['QUERY_STRING'];

$map_debug = $_COOKIE["debug"] ? 1:0;

####################################################
# sona urils. Peab olema sama, kui selle failinimi
# $sona = "map"; // kommenteerime ja teeme universaalseks:
$name_pos = strrpos($_SERVER['SCRIPT_NAME'],"/");
$sona = substr($_SERVER['SCRIPT_NAME'],$name_pos+1);

if ($map_debug){
	echo "<pre>";
	echo "Otsime skripti nimi muutujast SCRIPT_NAME: ".$_SERVER['SCRIPT_NAME']."<br>";
	echo "Skripti nimi: <b>$sona</b> <br>";
	if($QUERY_STRING && substr_count($QUERY_STRING,"mod_rewrite")){echo "RewriteEngine used: QUERY_STRING={$QUERY_STRING}<br>";}
	echo "</pre>";
}

# kui kasutusel mod_rewrite:
if ($QUERY_STRING && substr_count($QUERY_STRING,"mod_rewrite")){
	parse_str($QUERY_STRING);
	$pos = (strlen($mod_rewrite)==1 ? 0:strlen($mod_rewrite));
	$sona = "";
	$mod_rewrite_used = 1;
	unset($_SERVER['QUERY_STRING']);

} else {
	$pos = strpos($uri, $sona);
}
$kysi_pos = strpos($uri, "?");


	preg_match('/\/(admin|editor|temp)\//i', $_SERVER["REQUEST_URI"], $matches);
	$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";


	#####################
	# Classes include:
	include_once($class_path."timer.class.php");

	if ($_GET["debug"]) {
		include_once($class_path."debug.inc.php");
	} else {
		include_once($class_path."nodebug.inc.php");
	}
	include_once($class_path."config.class.php");


	#####################
	# Read config-file:
	######## get absolute path of website root
	$absolute_path = getcwd().'/';
	# strip /admin|editor|classes/ from the end
	if (preg_match("/(.*)\/(admin|editor|classes|temp|test)\/$/", $absolute_path, $matches) || preg_match("/(.*)\\\(admin|editor|classes|temp|test)\/$/", $absolute_path, $matches)) {
		$absolute_path = $matches[1];
	}
	# add slash to the end
	if (!preg_match("/\/$/",$absolute_path)) {$absolute_path .= "/"; }

	####### read config.php
	$file = $absolute_path."config.php";

	$fp = fopen($file, "r");
	# check if file config.php exists at all
	if(!file_exists($file)) { 
		print "<font color=red>Error: file \"$file\" not found!</font>";
		exit;
	} 
	$config = new CONFIG(fread($fp, 1024*1024));
	fclose($fp);
	$CONF = $config->CONF;

		
	#############################################
	# include database independent API functions:
	include_once($class_path.$CONF["dbtype"].".inc.php");

	$conn = new DB(array(
		host	=> $CONF["dbhost"],
		port	=> $CONF["dbport"],
		dbname	=> $CONF["db"],
		user	=> $CONF["user"],
		pass	=> $CONF["passwd"],
		'mysql_set_names' => $CONF["mysql_set_names"],
	));	


########## if empty alias: do nothing (Bug #2164)

if(!is_array($ncmd)){

	if ($map_debug){
	echo "<pre>ALIAS NOT GIVEN, do nothing";
	echo "</pre>";
	}


}else{




				//If the user changes language via URL, we need to take that into account. 

				if (!empty($_GET['lang'])){

						if($_GET['lang'] == 'ee' || $_GET['lang'] == 'et')
						{
							$sql1 = $conn->prepare("SELECT keel_id,extension,site_url FROM keel WHERE on_kasutusel='1' AND extension in ('ee', 'et') LIMIT 1");
						}
						else 
						{
							$sql1 = $conn->prepare("SELECT keel_id,extension,site_url FROM keel WHERE on_kasutusel='1' AND extension=? LIMIT 1", $_GET['lang']);
						}

						$sth1 = new SQL($sql1);
						if($mytmp = $sth1->fetch("ASSOC")){
						$lang = $mytmp['extension'];
						$lang_id = $mytmp['keel_id'];
						$site_url = $mytmp['site_url'];
						$language_first=1;
#						unset($ncmd);  //necessary because when there language is changed the old way it's best he went to the homepage of that language. A lot less hassle. 


						//We redirect the query to the URL while not taking the aliases with us. It created problems when there was an URL/alias/?lang=xx combination and there was no specified alias under that language. And the user probably just wanted to redirect to another language, but missed the alias in the url. 

						$cu=trim($_SERVER['SERVER_NAME'].$server_port.preg_replace("/\/$/","",$_SERVER["REQUEST_URI"]));

						if(empty($site_url)){
						$site_url = $cu;	
						}


						if(is_array($variable)&&sizeof($variable)>0){
							$qs="?".implode("&",$variable);
						}
						Header( "HTTP/1.1 301 Moved Permanently" );
						header ("Location: " . (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $site_url.$qs);
						exit;
						}
						




				}


			if (!$lang || !is_numeric($lang_id)){

			//here is ONE exception to the alias rule. if the first alias is "et" or "ee" then the language can be "ee" or "et"
			if(preg_match("/^et$/i",$ncmd[0]) || preg_match("/^ee$/i",$ncmd[0])){
				$sql1 = $conn->prepare("SELECT keel_id,extension,site_url FROM keel WHERE on_kasutusel='1' AND (extension=? or extension=?) LIMIT 1", 'ee', 'et');
			}
			else 
			{
				$sql1 = $conn->prepare("SELECT keel_id,extension,site_url FROM keel WHERE on_kasutusel='1' AND extension=? LIMIT 1", $ncmd[0]);
			}

			$sth1 = new SQL($sql1);
			$mytmp = $sth1->fetch();
			$lang = $mytmp['extension'];
			$lang_id = $mytmp['keel_id'];
			$site_url = $mytmp['site_url'];

				if ($lang && is_numeric($lang_id)){
					unset($ncmd[0]);
					$language_first=1;
				}

			}

			if (!$lang || !is_numeric($lang_id)){


				//If no url language link we go and get the necessary language id from session

						if(is_numeric($_SESSION['keel']['keel_id']) && !is_numeric($lang_id)){
						$lang_id = $_SESSION['keel']['keel_id'];

						}else{



				//Not in session either may mean a first visit to the page, so we find out what the default language is.


				//First we find all the active languages


						$sql1 = $conn->prepare("SELECT keel_id,extension,site_url,on_default FROM keel WHERE on_kasutusel='1'");
						$sth1 = new SQL($sql1);
						while($mytmp = $sth1->fetch("ASSOC")){
							$tmplangs[]=$mytmp;
						}
				/*
				There is a possibility that the same site runs under several different domains. estonian site might end with .ee, english .info, finnish .fi so while the site may have one fixed default language, we will first compare the site_url variable with the _SERVER["SERVER_NAME"] variable. If we have a match, we will use that language variables, otherwise we go with the default language the site has.
				*/


						foreach($tmplangs as $tmpl){

							if(strtolower(trim($tmpl['site_url'])) == strtolower($_SERVER["SERVER_NAME"])){

									$lang = $tmpl['extension'];
									$lang_id = $tmpl['keel_id'];
									$site_url = $tmpl['site_url'];
									$lang_matched = true;
									break;

							}

						}


						//If site_url did not match with the domain name the next step is to look for the default language

						if(!$lang_matched){

							foreach($tmplangs as $tmpl){
								if($tmpl['on_default'] == '1'){
										$lang = $tmpl['extension'];
										$lang_id = $tmpl['keel_id'];
										$site_url = $tmpl['site_url'];
										$lang_matched = true;
										break;
								}
							}


						}

						//IF there still is no language then index.php will handle it. 

				}


			}

if(!empty($site_url)){

	$cu=trim($_SERVER['SERVER_NAME'].$server_port.preg_replace("/\/$/","",$_SERVER["REQUEST_URI"]));

	if(!eregi($cu,$site_url)){
		if(is_array($variable)&&sizeof($variable)>0){
			$qs="?".implode("&",$variable);
		}
		Header( "HTTP/1.1 301 Moved Permanently" );
		header ("Location: " . (empty($_SERVER['HTTPS']) ? 'http://' :
'https://') .$site_url."/".implode("/",$ncmd).$qs);
		exit;
	}


}


// IF the language is not defined we need to keep tabs on several different alias hierarchies under different languages. 

if($language_first){

	foreach($ncmd as $k=>$v){


			$x=find_alias($v,$temp_tree[$lang_id][sizeof($temp_tree[$lang_id])-1]['objekt_id'],$lang_id,$language_first);

			if($x){

				$temp_tree[$lang_id][]=$x;
			}else{

				unset($temp_tree[$lang_id]);
				break;
			}
	}

}else{

		//We are going to look for all the languages that contain the alias or ID, as we don't know which language we need to look in.

	$tmp = array();
	$sql1 = $conn->prepare("SELECT keel from objekt WHERE friendly_url = ?",current($ncmd));
	$sth1 = new SQL($sql1);
		while($mytmp = $sth1->fetch()){
			$tmp[$mytmp['keel']]=1;
		}

	if(is_numeric(current($ncmd))){
		$sql1 = $conn->prepare("SELECT keel from objekt WHERE objekt_id = ?",current($ncmd));
		$sth1 = new SQL($sql1);
			while($mytmp = $sth1->fetch()){
				$tmp[$mytmp['keel']]=1;
			}
	}

	reset($ncmd);

	foreach($tmp as $key=>$value){

		foreach($ncmd as $url_value){
			$x=find_alias($url_value,$temp_tree[$key][sizeof($temp_tree[$key])-1]['objekt_id'],$key,1);

			if($x){

				$temp_tree[$key][]=$x;
			}else{

				unset($temp_tree[$key]);
				break;
			}
		}
	}

}


	if(is_array($temp_tree)&&sizeof($temp_tree)>=1){


		if(is_array($temp_tree[$lang_id])){
			$_SESSION['alias']=$temp_tree[$lang_id];
		}else{

			//if the language has changed we need to make another check if the new language does not require a redirect to another domain.
				$sql1 = $conn->prepare("SELECT * from keel WHERE keel_id = ?", $key);
				$sth1 = new SQL($sql1);
					if($mytmp = $sth1->fetch()){

						if(!empty($mytmp['site_url'])){

							$cu=trim($_SERVER['SERVER_NAME'].$server_port.preg_replace("/\/$/","",$_SERVER["REQUEST_URI"]));

							if(!eregi($cu,$mytmp['site_url'])){
								if(is_array($variable)&&sizeof($variable)>0){
									$qs="?".implode("&",$variable);
								}
								Header( "HTTP/1.1 301 Moved Permanently" );
								header ("Location: ". (empty($_SERVER['HTTPS']) ? 'http://' :
'https://') . $mytmp['site_url']."/".implode("/",$ncmd).$qs);
								exit;
							}
						}
						$lang = $mytmp['extension'];
						$lang_id = $mytmp['keel_id'];

					}

			$_SESSION['alias']=current($temp_tree);

		}
	}else{

		if(is_array($ncmd)&&!empty($ncmd)){
			$alias_error=true;
		}
	}

}

//If there was an alias error we cant transmit the variables as well. 

if($alias_error){
$variable=array();
}

if($alias_error){

	$_GET['id'] = $_GET['id'] = 100000000000;
	$new_url[]="id=100000000000";

}else{
	if(!empty($_SESSION['alias'])) {
		$latest_alias=end($_SESSION['alias']);
		$id=$latest_alias['objekt_id'];
		$_GET['id'] = $id;
	}
}

$_GET['lang'] = $lang;
if(is_numeric($id)){
	$new_url[]="id=".$id;
}
if($lang){
	$new_url[]="lang=".$lang;
}

$qstring=array_merge($new_url,$variable);

$QUERY_STRING = $_SERVER['QUERY_STRING'] = $_ENV['QUERY_STRING'] = $_SERVER['QUERY_STRING'] = implode("&",$qstring);
$_SERVER['REQUEST_URI'].="?".implode("&",$qstring);


}else{  //if special case (?id=xxxx) then we just redirect to that and ignore all the aliases. 


	$QUERY_STRING = $_SERVER['QUERY_STRING'] = $_ENV['QUERY_STRING'] = $_SERVER['QUERY_STRING'] = implode("&",$variable);
	$_SERVER['REQUEST_URI'].="?".implode("&",$variable);
}

// for site object let's pretend this is index.php #2690
$_SERVER['SCRIPT_NAME'] = str_replace('map.php', 'index.php', $_SERVER['SCRIPT_NAME']);

include('index.php');
