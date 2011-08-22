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
 * Collection of installation related functions in CMS installation and upgrade processes.
 * Some functions are also used in Extension Manager.
 */

/**
 * Translate a multiple query separated by ; to single queries
 *
 * @param string $sql Input data, eg all SQL file content
 * @param string $delimiter SQL delimiter
 * @access public
 * @return array Array of SQL queries
 *
 */
function split_sql_file($sql, $delimiter) {

	$sql               = trim($sql);
    $char              = '';
    $last_char         = '';
    $ret               = array();
    $string_start      = '';
    $in_string         = FALSE;
    $escaped_backslash = FALSE;

#	$ret = split("\s*;[\n\r]",$sql);

    for ($i = 0; $i < strlen($sql); ++$i) {
        $char = $sql[$i];

        // if delimiter found, add the parsed part to the returned array
        if ($char == $delimiter && !$in_string) {
            $ret[]     = substr($sql, 0, $i);
            $sql       = substr($sql, $i + 1);
            $i         = 0;
            $last_char = '';
        }

        if ($in_string) {
            // We are in a string, first check for escaped backslashes
            if ($char == '\\') {
                if ($last_char != '\\') {
                    $escaped_backslash = FALSE;
                } else {
                    $escaped_backslash = !$escaped_backslash;
                }
            }
            // then check for not escaped end of strings
            if (($char == $string_start)
                && !(($last_char == '\\') && !$escaped_backslash)) {
                $in_string    = FALSE;
                $string_start = '';
            }
        } else {
            // we are not in a string, check for start of strings
            if (($char == '"') || ($char == '\'') || ($char == '`')) {
                $in_string    = TRUE;
                $string_start = $char;
            }
        }
        $last_char = $char;
    } // end for

    // add any rest to the returned array
    if (!empty($sql)) {
        $ret[] = $sql;
    }
    return $ret;
} 

/***********************************/
/* READCONF                        */
/* is copy-paste from classes/site.class.php */
/***********************************/
function ReadConf() {

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

	# check if file config.php exists at all
	if( !file_exists($file)) { 
		print "<font color=red>Error: can't write config.php, create this file and make sure it is writable by the webserver.</font>";
		exit;
	} 
	$CONFIG = fopen ($file, "r");
	$config = new CONFIG(fread($CONFIG, 1024*1024));
	fclose($CONFIG);
	return $config->CONF;
}


/***********************************/
/* READCONFDB                      */
/* is copy-paste from classes/site.class.php */
/* (except 1 line, see below)      */
/***********************************/
function ReadConfDB() {

// NB! exception in copy-paste: this 1 line must be added
	global $conn;

	$sql = "SELECT nimi,sisu FROM config";
	$sth = new SQL($sql);
	if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font>"; }
	while ($conf_rida = $sth->fetch()) {
		$conf[$conf_rida[nimi]] = $conf_rida[sisu];
	}
	return $conf;
}

function set_hostname_wwwroot($url)
{
	// cut off http:// from the beginning
	if (!strcasecmp("http://", substr($url, 0, 7))) {
		$url = substr($url, 7);
	}

	// add / to the end if missing and not empty
	if (strcasecmp("/", substr($url, -1,1)) && strlen($url)>0) {
		$url = $url."/";
	}

	######################
	# find hostname & wwwroot from url
	#
	# this->hostname: serveri nimi, nt dino.saurus.ee 
	# this->wwwroot: URL ilma scriptinimega, nt /port
	#    on tühistring kui saidil oma virtuaalhost ja dns-kirje.

	$self = $_SERVER["REQUEST_URI"]; # kui apache
	# failinimi lõpust maha
	if (preg_match("/^[^\?]*\//", $self, $matches)) {
		$path = $matches[0];
	} else {
		$path = $self;
	}
	# slash lõppu!
	if (!preg_match("/\/$/",$path)) {$path .= "/"; }

	$wwwroot = $path;

	# slash lõpust maha!
	$wwwroot = preg_replace("/\/$/","",$wwwroot);

	# find hostname from url
	$hostname = $_SERVER["HTTP_HOST"];

	# update database
	$sql2 = "UPDATE config SET sisu='".$hostname."' WHERE nimi='hostname'";
	$sth2 = new SQL($sql2);

	# update database
	$sql2 = "UPDATE config SET sisu='".$wwwroot."' WHERE nimi='wwwroot'";
	$sth2 = new SQL($sql2);
}

/***********************************/
/* CURRENT_VERSION                 */
/* check for installed version     */
/***********************************/
function current_version() {

	global $conn, $CONF;

	$db_found = check_db();

	if ($db_found) {
		$sql = "SELECT version_nr FROM version ORDER BY release_date DESC LIMIT 1";

		$sth = new SQL($sql);
		$cms_version = $sth->fetchsingle();
		return $cms_version;
	}
	else {
		return 0;
	}
}

/***********************************/
/* DBCONNECT                       */
/* Connect to Database             */
/***********************************/
function dbconnect($root_access, $rootuser, $rootpasswd, $db)
{
	global $conn, $CONF, $class_path;

	// include database independent API functions
	include_once($class_path.$CONF[dbtype].".inc.php");
	
	// connect to database
	// if connect using root access,
	// then use param values instead of config values
	$user = ($root_access?$rootuser:$CONF["user"]);
	$passwd = ($root_access?$rootpasswd:$CONF["passwd"]);
	$db = ($db?$db:($root_access?"":$CONF["db"]));
	$conn = new DB(array(
		host	=> $CONF["dbhost"],
		port	=> $CONF["dbport"],
		dbname	=> $db,
		user	=> $user,
		pass	=> $passwd,
		'mysql_set_names' => $CONF["mysql_set_names"],
	));
};


/***********************************/
/* MAKE_DB                         */
/* creates the DB on new installs  */
/***********************************/
function make_db()
{
	global $CONF, $conn;
	// check if database exists

	$sql = "SHOW DATABASES ";
	$sth = new SQL($sql);
	if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font>"; exit;}
	while ($rec = $sth->fetch()) {
		if ($rec[0] == $CONF["db"]) {
			$db_exists = 1;
		}
	}

	// create database or return error message
	if ($db_exists) {
		return 0;
	}
	else {
		$sql = "CREATE DATABASE ".$CONF["db"];
		$sth = new SQL ($sql);
		if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font>"; exit; }
		return 1;
	}
}

/***********************************/
/* MAKE_USER                       */
/* creates the user for DB on new installs  */
/***********************************/
function make_user()
{
	global $CONF, $conn;

	// check if user exists for this database
/* 
todo: kasutaja chekkimine tuleb panna funktsioonina mysql.inc.php-sse. 
*/
	$sql = "use mysql";
	$sth = new SQL ($sql);
	if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font>"; exit;}

	$sql = "select User from user";
	$sth = new SQL($sql);
	if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font>"; exit;}
	while ($rec = $sth->fetch()) {
		if ($rec[0] == $CONF["user"]) {
			$user_exists = 1;
		}
	}
	// create user 

	if ($user_exists) {
		return 0;
	}
	else {
/* 
todo: kasutaja loomine tuleb panna funktsioonina mysql.inc.php-sse. 
*/
		$sql = "GRANT ALL PRIVILEGES ON ".$CONF["db"].".* TO ".$CONF["user"]."@".$CONF["dbhost"]." IDENTIFIED BY  '".$CONF["passwd"]."'";
		$sth = new SQL ($sql);
		if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font>"; exit;}

		$sql = "FLUSH PRIVILEGES;";
		$sth = new SQL ($sql);
		if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font>"; }

		return 1;
	}
}

/***********************************/
/* CHECK_DB                        */
/* checks if DB exists             */
/***********************************/
function check_db(){
	global $CONF, $conn;

	// connect to database 
	dbconnect(0, '', '', ''); 
	
	print $conn->error;

	// check if database exists

	if(!$conn->error) {
		$sql = "SHOW DATABASES";

		$sth = new SQL($sql);
		//if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font>"; }

		while ($rec = $sth->fetch()) {
			if ($rec[0] == $CONF["db"]) {
				return 1;
			}
		}
	} # if conn
	// if database doesn't exist or error in connection, return 0

	return 0;
}
/***********************************/
/* CHECK_allpriv                        */
/* checks if DB exists             */
/***********************************/
function check_allpriv($user) {
	global $CONF, $conn;

	// create
	$sql = "CREATE TABLE tempinstall (temp int(10))";
	$sth = new SQL($sql);
	if ($sth->error) { 
		$error .= " CREATE ";
		return "Error! User ".$user." doesn't have privilege ".$error. " for database ".$CONF["db"];
	}

	// select
	$sql = "SELECT temp FROM tempinstall";
	$sth = new SQL($sql);
	if ($sth->error) { 
		$error .= " SELECT ";
		return "Error! User ".$user." doesn't have privilege ".$error. " for database ".$CONF["db"];
	}
# POOLELI
	return 0;
}

/***********************************/
/* RUN_DUMPFILE                   */
/* run database dump file with sql querys */
/***********************************/
function run_dumpfile(){
	global $CONF, $FDAT, $conn, $default_data_files, $install, $skip_html;

	# connect to database based on
	# connect data given in form
	if ($_POST["update_user"] != '' || $_POST["update_passwd"] != '') {
# veakontroll : juurde error
		$conn = 0;
		dbconnect(1, $_POST["update_user"], $_POST["update_passwd"], $CONF["db"]);
	} 
	### use CONF values
	elseif($CONF["user"] != '' || $CONF["passwd"] != '') {
		$conn = 0;
		dbconnect(1, $CONF["user"], $CONF["passwd"], $CONF["db"]);
	}
	else {
		print "<font color=red>Error: DB user name and password were empty!</font><br />";
		exit;
	}

	# if dump-file is given, upload it

	$uploadfile = $_FILES["data_file"];

	if($uploadfile["name"] != ''){
		$default_data_files = array();
		array_push($default_data_files, $uploadfile["tmp_name"]);
#		$filename = $uploadfile["name"];
	}

	foreach($default_data_files as $file) {
		if($uploadfile["name"] != ''){ $filename = $uploadfile["name"]; }
		else { $filename = $file; }

		if (file_exists($file)) {
			if ($fd = fopen($file, "r")) {
				$sql = fread ($fd, filesize($file));
				fclose ($fd);
			} 
			else {
				echo "<font color=red>Can't open data file \"<b>".$filename."</b>\" - access denied</font><br />";
				$error = 1;
			}
		}
		else {
			echo "<font color=red>Can't open data file \"<b>".$filename."</b>\" - not found</font><br />";
			$error = 1;
		}

		############
		# if there is smth in file
		if($sql) {

			$pieces = split_sql_file( $sql,';' );

			// now $sql is an array of all sql directives to launch
			$i = 1;
		
			if( ! $skip_html) { # display HTML output			

				echo "Running SQL file '".$file."'...<br>";
				echo "<script>document.getElementById('listing').scrollTop = document.getElementById('listing').scrollHeight - 500;</script>";
				flush(); usleep(500000);

			} # if display HTML output 
		
			foreach ($pieces as $query)	{
				if( ! $skip_html) { # display HTML output			

					if ($i%1000 == 0 || $i == 1) {
						flush(); usleep(500000);
						$tbl_is_begun = 1;
					}
					# print 1 dot for each 10 queries - only for install
					if ($i%10 == 0 && $install) {
						print ". ";
						flush();
					}
				} # if display HTML output 
				
				$sth = new SQL($query);
				if ($sth->error) 
					{print "<font color=red>Error: ".$sth->error."</font><br>"; $error=1;}

				$i++;
			}
//			if( ! $skip_html) { # display HTML output			
//			} # if display HTML output 

		} # if sql

		#################
		# if update, find php-script file name and run it
		if(!$install) {
			$script_filename = substr($filename,0,-4).".php";
			$error = run_scriptfile($script_filename);
		}


	} # foreach

	return $error;
}

/***********************************/
/* RUN_SCRIPTFILE                   */
/* run php-script file (converters etc)  */
/* NOT IN USE YET */
/***********************************/
function run_scriptfile($file) {

	global $CONF, $conn;
		$filename = $file;

		if (file_exists($file)) {
			echo "Running PHP script file '".$file."'...<br>";
			echo "<script>document.getElementById('listing').scrollTop = document.getElementById('listing').scrollHeight - 500;</script>";
			flush(); usleep(500000);

			include_once($file);
		}
	
	return $error;
}

/***********************************/
/* STORE_ADMIN_DATA                      */
/* saves admin data into admin tables in db */
/***********************************/
function store_admin_data()
{
	global $CONF, $conn, $FDAT;

	// connect to database 
	if (!$conn) { dbconnect(0, '', '', ''); }

	// check if admin password matches with password confirmation
	if ($FDAT["adminpasswd"] != $FDAT["adminpasswd_check"]) {
		return "Passwords don't match! Please go back and try again.";
	}
	// check if admin password is not empty
	if (trim($FDAT["adminpasswd"]) == '') {
		return "Please go back and set default password for administrator login!";
	}
	// check if admin password is not default "saurus"
	if ($FDAT["adminpasswd"] == 'saurus') {
		return "Please go back and change default password for administrator login!";
	}

    if (empty($FDAT["adminemail"]) || !filter_var($FDAT["adminemail"], FILTER_VALIDATE_EMAIL)) {
		return "Please go back and set correct e-mail address for administrator!";
    }

	// write admin user data db
	if ($FDAT["adminpasswd"]) {
		$pass_sql = $conn->prepare(", password=? ", crypt($FDAT["adminpasswd"], Chr(rand(65,91)).Chr(rand(65,91))));
		$pass = crypt($FDAT["adminpasswd"], Chr(rand(65,91)).Chr(rand(65,91)));
	}

	// check if exists default admin, if yes, then update, if no, then insert

	$sql = "SELECT COUNT(*) FROM users where username='admin'";
	$sth = new SQL($sql);
	$exists = $sth->fetchsingle();

	if ($exists) {
		$sql = $conn->prepare(
			"UPDATE users SET firstname=?, username=?, email=?, group_id=? $pass_sql where username='admin'",
			$FDAT["adminname"],
			$FDAT["admin"],
            $FDAT["adminemail"],
			1
		);
		$sth = new SQL($sql);
	} else {
		$sql = $conn->prepare(
			"INSERT INTO users (firstname, username, email, group_id, password) VALUES (?, ?, ?, ?, ?)",
			$FDAT["adminname"],
			$FDAT["admin"],
            $FDAT["adminemail"],
			1,
			$pass
		);
		$sth = new SQL($sql);

		$sql = "SELECT last_insert_id()";
		$sth = new SQL($sql);
		$admin_id = $sth->fetchsingle();
	}
	return 0;
}
/***********************************/
/* STORE_CONFIG_DATA                      */
/* saves data into config table in db */
/***********************************/
function store_config_data(){
	global $CONF, $conn;

	// write settings into db

	if (!$_POST[cff_only_regusers_comment]) $_POST[cff_only_regusers_comment] = "0";
	if (!$_POST[cff_default_comments]) $_POST[cff_default_comments] = "0";
	if (!$_POST[cff_users_can_register]) $_POST[cff_users_can_register] = "0";

	foreach ($_POST as $key=>$value) {
		
		if ( substr ($key, 0, 4) == "cff_" ) {			
			$sql = $conn->prepare("UPDATE config SET sisu=? WHERE nimi=?", $value, substr ($key, 4)); 
			$sth = new SQL($sql);
			if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font><br />"; }
		}
	}	

}
/***********************************/
/* FIX_PATH                        */
/* puts directory paths into correct format */
/***********************************/
function fix_path($path)
{
	// add / to the beginning if missing
	if (strcasecmp("/", substr($path, 0, 1))) {
		$path = "/".$path;
	}

	// cut off / from the end
	if (!strcasecmp("/", substr($path, -1,1))) {
		$path = substr($path, 0,strlen($path)-1);
	}
	return $path;
}
/***********************************/
/* FIX_HOSTNAME                    */
/* puts hostname into correct format */
/***********************************/
function fix_hostname($path)
{
	// cut off http:// from the beginning
	if (!strcasecmp("http://", substr($path, 0, 7))) {
		$path = substr($path, 7);
	}
	// cut off / from the end
	if (!strcasecmp("/", substr($path, -1,1))) {
		$path = substr($path, 0,strlen($path)-1);
	}
	return $path;
}

/***********************************/
/* MODIFY_FILE                     */
/* modify config file              */
/***********************************/

function modify_file($src, $reg_src, $reg_rep)
{
	$buffers = array();
	
	$out = @fopen($src, "r");
    if ($out)
    {
	    while (!feof($out))
	    {
	        $buffers[] = fgets($out, 4096);
	    }
    }

    if(sizeof($buffers) < 2)
    {
    	$buffers = array(
			'<? /*'."\n",
			"\n",
			'########################'."\n",
			'# database connect'."\n",
			"\n",
			'dbhost = localhost'."\n",
			'dbport = 3306'."\n",
			'db = sauruscms'."\n",
			'user = sauruscms'."\n",
			'passwd = nopasswdset'."\n",
			'dbtype = mysql'."\n",
			"\n",
			'# run MySQL SET NAMES query'."\n",
			'# mysql_set_names = utf8'."\n",
			"\n",
			'# Enable/disable polling Saurus server for live site statistics collecting'."\n",
			'disable_site_polling = 0'."\n",
			"\n",
			'# Allow using PHP-tags in Smarty templates'."\n",
			'allow_php_tags = 0'."\n",
			"\n",
			'*/?>',
		);
	}
    
    $out = @fopen($src, "w");
    if (!$out)
    {
		if(php_sapi_name() == 'cli')
			echo 'Can\'t write config.php, no permissions'."\n";
		else
			echo "<font color=red>Error: can't write config.php, make sure it is writable by the webserver.</font>";
		exit;
    }

    $lines = 0; // Keep track of the number of lines changed
    
    foreach ($buffers as $buffer)
    {
        $new = preg_replace($reg_src, $reg_rep, $buffer);
        if ($new != $buffer)
        {
            $lines++;
        }
        
        fputs($out, $new);
    }
    
    fclose($out);
    
    // make the config.php read-only for others
    @chmod($src, 0644);
    
    if ($lines == 0) 
    {
        // Skip the rest - no lines changed
        return "$src";
    }
    
     // Success!
    return "$src updated with $lines lines of changes";
}

// Two global arrays
$reg_src = array();
$reg_rep = array();

// Setup various searches and replaces

function add_src_rep($key, $rep) 
{
    global $reg_src, $reg_rep;
    
	if($rep == '') { # Bug: when config value was empty then linebreak was deleted and config file got messy
		$rep = "\n";
	}
    $reg_src[] = "/ $key \s* = \s* (.*) /x";
    $reg_rep[] = "$key = $rep";
}

function show_error_info()
{
echo <<< EOT
        <b>Write error</b> unable to update your "config.php"  file <br/>
EOT;

}

/***********************************/
/* UPDATE_CONFIG_PHP               */
/***********************************/

function update_config_php($dbhost, $dbport, $db, $user, $passwd)
{
    global $reg_src, $reg_rep;
 
    add_src_rep("dbhost", $dbhost);
    add_src_rep("dbport", $dbport);
    add_src_rep("db", $db);
    add_src_rep("user", $user);
    add_src_rep("passwd", $passwd);

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
	
	$ret = modify_file($file, $reg_src, $reg_rep);

	return $ret;

}
/***********************************/
/* SITE_URL                        */
/***********************************/

function site_url() {

	# leiame url-i ilma hostname'ta

		##Kontrollime kas server jooksetab apachet või mitte
		if(preg_match("/apache/i", $_SERVER["SERVER_SOFTWARE"]) || preg_match("/apache/i", $_SERVER["SERVER_SOFTWARE"])){
			$self = $_SERVER["REQUEST_URI"]; # kui apache
		} else {
			$self = $_SERVER["SCRIPT_NAME"]; # kui muu (nt IIS)
		}
		if (preg_match("/^[^\?]*\//", $self, $matches)) {
			$path = $matches[0];
		} else {
			$path = $self;
		}
		# slash lõppu!
		if (!preg_match("/\/$/",$path)) {$path .= "/"; }

	# Bug #2303: fixing port ($_SERVER["HTTP_HOST"] already includes port info):
	### =>REMOVED: add port to the URL if it's not default http port (80) or https port (443)
	#$port = ($_SERVER["SERVER_PORT"] && $_SERVER["SERVER_PORT"]!='80' && $_SERVER["SERVER_PORT"]!='443'?':'.$_SERVER["SERVER_PORT"]:'');

	# Bug #2271: Tuumas ja installiskriptis leitakse hostname erinevate serveri muutujate põhjal	
	$url = $_SERVER["HTTP_HOST"].$path; 
	return $url;
}
/***********************************/
/* IMPORT_LANG_FILE                */
# function is copy-paste from admin/lang_file.php
/***********************************/

function import_langfile($local_file_name,$dbkeel) {

	global $CONF, $conn, $site;
	$site->db = &$conn;

	# extract language from file name
	preg_match("/(.*)language(\d+)\.csv/", $local_file_name, $match);

	$keel_id = $match[2];

	# check if file exists
	if (!file_exists($local_file_name)) {
		$error = "Can't open language file \"<b>".$local_file_name."</b>\" - not found";
		return $error;
	}

	include_once('classes/lang_functions.inc.php');
	###### Import files from CVS
	if(import_dict_from_file(
		$cvs_file = $local_file_name,
		$overwrite_user_translations = true,
		$delete_old_data = false,
		$write_log = false)
	) return;
	else return 'Language file import failed.';
}
# / function
########################

/**
 * Delete given file. 
 *
 * @param string $filename Relative filename to delete
 * @access private
 * @return boolean $success Returns true if file was successfully deleted, false otherwise
 *
 */
function delete_file($filename) {
	if(is_file($filename)) {
		$success = unlink($filename);
	}
	return $success;
}

function files_folders_permissions()
{
	// if not exists create templates, templates_c
	if(!is_dir('classes/smarty/templates'))
	{
		@mkdir('classes/smarty/templates');
	}
	@chmod('classes/smarty/templates', 0777);
	
	if(!is_dir('classes/smarty/templates_c'))
	{
		@mkdir('classes/smarty/templates_c');
	}
	@chmod('classes/smarty/templates_c', 0777);
	
	//  permissions for public, shared, extensions
	if(is_dir('public'))
	{
		@chmod('public', 0777);
	}
	
	if(is_dir('shared'))
	{
		@chmod('shared', 0777);
	}
	
	if(is_dir('extensions'))
	{
		@chmod('extensions', 0777);
	}
}
