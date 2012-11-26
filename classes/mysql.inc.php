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


##########################################
# Database-independent, function-based API
# This file is for MySQL
##########################################

error_reporting(7);

class DB {
	var $dbh;
	var $sql_count;
	var $sql_aeg;
	var $connection;
	var $dbname;
	var $fields_info;
#	var $debug;
	var $error_msg;
	var $error_no;
	var $error;


	function DB() {
		$args = func_get_arg(0);

		global $site;
		$this->site = &$site;

		$this->debug = new Debug();
		$this->timer = new Timer();

		$this->dbname = $args["dbname"];
		$this->port = $args[port] ? $args[port] : "3306";
		$this->fields_info = array();

		#FAQ:Setting the database port to anything else than 3306 doesn't work!
		#First be sure you have Saurus CMS version later than 3.1.29. If it doesn't help, then if you are #using an mysql server on a different port e.g. 3307 instead of 3306, using "localhost" as #"dbhost" parameter will override the "dbport" value and use the default port 3306. Specify the #computer host or domain name instead. This is a bug in the mysql client. 

		if (($this->connection = @mysql_connect($args[host].":".$this->port,$args[user],$args[pass])) && $this->dbname) {
			if (!$this->dbh = mysql_select_db($this->dbname,$this->connection)){
				$this->debug->msg(mysql_error($this->connection));
				#$this->error(); # commented out, Bug #2468
			}
		} elseif (!$this->dbname) {
			$this->connection = @mysql_connect($args[host].":".$this->port,$args[user],$args[pass]);
		} else {
			$this->debug->msg(mysql_error($this->connection));
			#$this->error(); # commented out, Bug #2468
		}
		$this->error_no = mysql_errno($this->connection);
		$this->error = mysql_error($this->connection);
		if(!$this->connection && !$this->dbname)
		{
			$this->error = "Access denied for user ".$args[user]."@".$args[host].":".$this->port;
		}
		elseif(strpos(mysql_get_server_info($this->connection), '4.0') !== 0) // sql_mode was introduced in 4.1
		{
			// disable strict mode
			new SQL("set session sql_mode=''");
		}
		
		if($args['mysql_set_names'])
		{
			new SQL("set names ". $args['mysql_set_names']);
		}

		$this->get_timezone();
	}

	function get_timezone(){
		global $site;
		static $k=0;
		if($k===0){
			
		$sql="select * from config where nimi = 'time_zone' limit 1";
		$sth = new SQL($sql);
			if($data = $sth->fetch()){
				if(is_numeric($data['sisu'])){
					if(!@putenv('TZ='))
					{
						$k = 1;
						return;
					}

					$sql_tz="select * from ext_timezones where id='".$data['sisu']."'";
					$sth_tz = new SQL($sql_tz);
						if($data_tz = $sth_tz->fetch()){

							new SQL("set time_zone='+0:00'");
							$sql_gmt="select unix_timestamp() as ut";
							$sth_gmt = new SQL($sql_gmt);
							$d_gmt = $sth_gmt->fetch("ASSOC");
							$gmt_timestamp=$d_gmt['ut'];
#							echo $gmt_timestamp."<br>";
							$gmt=date("Y-m-d H:i:s",$gmt_timestamp);


							//we go into the head of our new timezone and become IT.

							putenv("TZ=".trim($data_tz['php_variable']));
							$time=date("Y")."-".date("m")."-".date("d")." ".date("H").":".date("i").":".date("s");

							$sql_time = "SELECT unix_timestamp('".$time."') as ts";
							$sth_time = new SQL($sql_time);
								if($d2 = $sth_time->fetch("ASSOC"))
								{
									$timestamp=$d2['ts'];
								}
							//we find out what the difference is.

							$difference=$timestamp-$gmt_timestamp;

							if($difference<0){
								$dif=str_replace("-","",$difference);
							}else{
								$dif=$difference;
							}

							$hours=$dif/3600;

							if(ereg("\.",$hours)){

								$f=explode(".",$hours);
								$koef="0.".$f[1];
								$min=round($koef*60);
								if($min == 60){
									$min="00";
									$f[0]+=1;
								}
								$dif=$f[0].":".$min;
							}else{
								$dif=$hours.":00";
							}

							if($difference<0){
							$dif="-".$dif;
							}else{
							$dif="+".$dif;
							}

							new SQL("set time_zone='".$dif."'");
							$k=1;

						}
				}
			}
		}
	}


	function quoteStr ($s) {
		# ekraneerib ohtlikud sümbolid stringis
		$s = Addslashes($s);
		#$s = preg_replace ("/(\')/", "\\\'", $s);
		#$s = preg_replace ("/(_)/", "\\_", $s);
		return $s;
	}

	function quote ($s) {
		# ekraneerib ohtlikud sümbolid inputi sültuvalt:
		# - iga massiivi elemendis 
		# - stringis
		if (is_array($s)) {
			reset ($s);
			while (list ($key, $val) = each ($s)) {		
				$s[$key] = $this->quoteStr($val);
			}
		} else {
			$s = $this->quoteStr($s);		
		}
		return $s;
	}

	######### 1) DATE FORMAT FUNCTIONS - BASE
	

	###### Base function: MySQL => unix timestamp
	function MySQL_unixtime($date){
		# input: yyyy-mm-dd hh:mm:ss
		# returns: timestamp or 0

		list($part1, $part2) = explode(" ", trim($date)); # get data
		list($year, $month, $day) = explode("-", $part1); # get data
		list($hour, $min, $sec) = explode(":", $part2); # get data
		if (!$year || !$month || !$day){
			return 0;
		}	
		$result = mktime($hour, $min, $sec, $month, $day, $year);
		if ($result==-1){
			return 0;
		}
		return $result;
	}



	###### Base function: MySQL => given format
	function MySQL_date($date,$format='dd.mm.yyyy'){
		# input: yyyy-mm-dd
		# returns: date with different format
		# default format is dd.mm.yyyy
		if($date){
			if(!trim($format)) { $format='dd.mm.yyyy'; }
			list($year,$month,$day) = explode("-",$date); # get data
			$format = str_replace('dd',$day,$format);
			$format = str_replace('mm',$month,$format);
			if(strstr($format, 'yyyy')) { # if 4-digit year
				$format = str_replace('yyyy',$year,$format);
			}
			else { # if 2-digit year
				$format = str_replace('yy',substr($year,-2),$format);
			}
			return $format;
		} # if date
	}

	###### Base function: given format => MySQL
	function date_MySQL($str, $format='dd.mm.yyyy') {
		# input: date with whatever format 
		# returns: yyyy-mm-dd
		# default format is dd.mm.yyyy
		if($str && !(strpos($str, '-') == 2 || strpos($str, '-') == 4)){
			if(!trim($format)) { $format='dd.mm.yyyy'; }
			$month = substr($str,strpos($format, 'mm'),2);
			$day = substr($str,strpos($format, 'dd'),2);
			if(strstr($format, 'yyyy')) { $year = substr($str,strpos($format, 'yyyy'),4); } 
			else { $year = "20".substr($str,strpos($format, 'yy'),2); }
			
			return $year."-".$month."-".$day;
		} # if str
		else return $str;
	}
	######### / 1) DATE FORMAT FUNCTIONS - BASE

	######### 2) DATE FORMAT FUNCTIONS - ADDITIONAL, based on base functions

	######### MySQL => another date format 
	function MySQL_ee($aeg_mysql) {
		# from yyyy-mm-dd
		# to dd.mm.yyyy (or given format)
		global $site;
		# split: maybe long input was passed
		$arr1 = split (" ",$aeg_mysql);
		if(sizeof($arr1)>1) { # if long format was given => return long format also
			return $this->MySQL_ee_long($aeg_mysql);
		}
		else { # default: short format was given => use only first part
			$aeg_mysql = $arr1[0];
			return $this->MySQL_date($aeg_mysql,$site->CONF['date_format']);
		}
		#OLD,fixed format: $aeg_mysql =  preg_replace("/^(\d\d\d\d)\-(\d?\d)-(\d?\d)/","\\3.\\2.\\1",$aeg_mysql);
	}
	function MySQL_ee_short($aeg_mysql) {
		global $site;
		# from yyyy-mm-dd (hh:mm) 
		# to dd.mm.yy (or given format)
		$arr1 = split (" ",$aeg_mysql);		
		return $this->MySQL_date($arr1[0],$site->CONF['date_format']);
	}
	function MySQL_ee_long($aeg_mysql) {
		global $site;
		# from yyyy-mm-dd hh:mm:ss
		# to dd.mm.yyyy hh:mm  (or given format)
		$arr1 = split (" ",$aeg_mysql);		
		$long_date = $this->MySQL_date($arr1[0],$site->CONF['date_format']);
		if (count($arr1)>0){$long_date .= " ".substr($arr1[1],0,5);} # omit seconds
		return $long_date;
	}
	######### / MySQL => another date format

	######### another date format => MySQL 
	function ee_MySQL($aeg_ee) {
		global $site;
		# from dd.mm.yyyy (or given format)
	# to yyyy-mm-dd 
		if($aeg_ee) {
			return $this->date_MySQL($aeg_ee,$site->CONF['date_format']);
		}
	}

	function ee_MySQL_long($aeg_ee0) {
		global $site;
		# from dd.mm.yyyy hh:mm (or given format)
		# to yyyy-mm-dd hh:mm
		if($aeg_ee0){
			$pos = strpos($aeg_ee0, " ");
			if ($pos>0) {
				$aeg_ee = substr($aeg_ee0, 0, $pos);
				$time_ee = trim(substr($aeg_ee0, $pos));
			} else {
				$aeg_ee = $aeg_ee0;
			}
			
			return $this->date_MySQL($aeg_ee,$site->CONF['date_format']).($time_ee ? " ".$time_ee : "");	
		}
	}
	######### / another date format => MySQL 

	######### / 2) DATE FORMAT FUNCTIONS - ADDITIONAL, based on base functions

	###### DB_DATE function: returns date in database-ready format
	function db_date($time) {
		# input: time() in unix timestamp format
		# returns: yyyy-mm-dd

		# adodb compatible:	return $this->dbh->DBDate($time); 
		if($time){
			return "'".date("Y-m-d",$time)."'";
		} else {
			return "'".date("Y-m-d")."'";	# return now
		}
	}
	###### UNIX2DB_DATETIME function: returns datetime in database-ready format
	function unix2db_datetime($time) {
		# input: time() in unix timestamp format
		# returns: yyyy-mm-dd hh:mm:ss

		# adodb compatible:	return $this->dbh->DBTimeStamp($time); 
		if($time){
			return "'".date("Y-m-d H:i:s",$time)."'";
		} else {
			return "'".date("Y-m-d H:i:s")."'";	# return now
		}
	}


	######### SQL FUNCTIONS

	function prepare() {
	# võtab sql lause ?-ga ja parameetrid, tagastab quoted sql lause
		$args = func_get_args();
		$sql = $args[0];
			$args = $this->quote($args);
		$i=0;
#		$sql = preg_replace("/\?/",'#¤#%?#¤#%',$sql,50);
		$sql = preg_replace("/\?/e", '"\'".$args[++$i]."\'"', $sql, 50); // ilmselt on võimalik aeglasemaid regexp'e teha aga see hea kandidaat
		//$sql = str_replace('?', '\''.$args[++$i].'\'', $sql);
		return $sql;
	}

	function get_fields() {
	# tagastab tabeli väljad kujul "a,b,c"
	# get_fields(array(
	#	table/tabel => 'aabel',
	#   [no_cache => 1]
	#))
		$args = func_get_arg(0);

		$fields_ary = array();

		$table = $args[table].$args[tabel];
				
		if ($table) {

			if ($this->fields_info[$table] && !$args[no_cache]) {
				$this->debug->msg("Väljade info leitud cache'is, võti = ".$table);
				return $this->fields_info[$table];
			} else {
				$fields = mysql_list_fields($this->dbname, $table, $this->connection);
				$columns = mysql_num_fields($fields);
				for ($i = 0; $i < $columns; $i++) {
						array_push($fields_ary, mysql_field_name($fields, $i));
					}
				$this->debug->msg("Väljade info genereeritud");
				$result = join(",",$fields_ary);
				$this->fields_info[$table] = $result;
				return $result;
			}
		} else {
			$this->debug->msg("Tabeli nimi puudub, $table");
		}
	}

	function error() {
		# 
		$message = "<table><tr><td colspan=2><b><font color=red>Database error</font></b></td></tr>";
		$message .= "<tr valign=top><td><b>SQL</b>:</td><td>".$this->sql."</td></tr>";
		$message .= "<tr valign=top><td><b>Error</b>:</td><td>".mysql_error($this->connection)."</td></tr>";
		$message .= "</table>";
		$this->debug->msg($message);
		$this->debug->print_msg();


		if (ini_get('display_errors')){
			$message = "<table><tr><td colspan=2><b><font color=red>Database error</font></b></td></tr>";
			$message .= "<tr valign=top><td><b>SQL</b>:</td><td>".htmlspecialchars($this->sql)."</td></tr>";
			$message .= "<tr valign=top><td><b>Error</b>:</td> <td>".htmlspecialchars(mysql_error($this->connection))."</td></tr>";
			$message .= "</table>";
			echo $message;
			unset($message);
		}

		$fdat = $_POST ? $_POST : $_GET;
		if ($fdat){
			$serialized_fdat = serialize($fdat);
		}

		if (!defined("SAVE_ERROR_LOG")){

			$res = @mysql_query("SELECT sisu FROM config WHERE nimi='save_error_log'", $this->connection);
			if ($res){
				list($tmp) = @mysql_fetch_array($res);
			}
			define("SAVE_ERROR_LOG", ($tmp ? 1:0));
		}

		if (SAVE_ERROR_LOG){
			@mysql_query("INSERT INTO error_log (time_of_error, source, err_text, err_type, domain, referrer, fdat_scope, ip) VALUES (NOW(), '".addslashes($this->sql)."', '".addslashes($error_text)."', 'SQL', '".addslashes($_SERVER['HTTP_HOST'])."', '".addslashes($_SERVER['REQUEST_URI'])."', '".addslashes($serialized_fdat)."', '".$_SERVER['REMOTE_ADDR']."')", $this->connection);
		}


//		$this->site->debug->print_msg();
/*
		$this->site->kirjuta_log(array(
			on_fataalne_error => 1,
			on_error => 1,
			text => mysql_error(),
			sql_text => $this->sql
		));
*/
	}
	######### / SQL FUNCTIONS

}

class SQL {

	var $sql;
#	var $debug;
	var $result;
	var $rows;
	var $num_fields;
	var $error_no;
	var $error;
	var $i; # row number
	var $insert_id;

	function SQL ($sql) {
#old		$this->BaasObjekt();

		global $site;
		$this->site = &$site;

		$this->debug = new Debug();
		$this->timer = new Timer();

		$this->sql = $sql;
		$this->i = 0;
		
		$this->debug->msg($this->sql);
		$errdsp = ini_get('display_errors');
		ini_set('display_errors', 0);
		if (is_resource($this->site->db->connection)) {
			$this->result = @mysql_query($this->sql, $this->site->db->connection);
		} else {
			$this->result = @mysql_query($this->sql);
		}
		ini_set('display_errors', $errdsp);

		$aeg = $this->timer->get_aeg();
		if (!empty($this->site->db)) {
			$this->site->db->sql_count++;
			$this->site->db->sql_aeg += $aeg;
		}

		if ($this->site->db->debug) {
			$this->site->db->debug->msg($this->site->db->sql_count.". ".$this->sql);
			$this->site->db->debug->msg($this->site->db->sql_count.". Aeg: ".$aeg. " ; Koguaeg: ". $this->site->db->sql_aeg. ($aeg>0.01?" SLOW QUERY (>0.01)":""));
		}

		if (is_resource($this->site->db->connection)) {
			$this->error_no = mysql_errno($this->site->db->connection);
			$this->error = mysql_error($this->site->db->connection);
		} else {
			$this->error_no = mysql_errno();
			$this->error = mysql_error();
		}

		if ($this->error_no) {	
			#$this->debug->msg("VIGA: ".$sql."<br>".mysql_error());
			$this->error();
			$this->result ="";
		} else {
			$errdsp = ini_get('display_errors');
			ini_set('display_errors', 0);
#not tested enough			if($this->result != 1) { ##### line added because of major bug in some server environments: http://bugs.php.net/bug.php?id=24720
				$this->num_fields = @mysql_num_fields($this->result);
#not tested enough			}

			if (is_resource($this->site->db->connection)) {
				$this->rows = preg_match("/^\s*(select|show)/i", $this->sql) ? @mysql_num_rows($this->result) : @mysql_affected_rows($this->site->db->connection);
			} else {
				$this->rows = preg_match("/^\s*(select|show)/i", $this->sql) ? @mysql_num_rows($this->result) : @mysql_affected_rows();
			}

			if (preg_match("/^\s*(insert)/i", $this->sql)) {
				if (is_resource($this->site->db->connection)) {
					$this->insert_id = @mysql_insert_id($this->site->db->connection);
				} else {
					$this->insert_id = @mysql_insert_id();
				}
				$this->debug->msg("ID: ".$this->insert_id);
			}
			$this->debug->msg("affected rows: ".$this->rows);
			ini_set('display_errors', $errdsp);
		}		
	}


	function fetch($result_type='BOTH') {
		$this->i++;
		if($result_type == 'ASSOC'){
			$result_type = MYSQL_ASSOC;
		} else if ($result_type == 'NUM'){
			$result_type = MYSQL_NUM;
		} else {
			$result_type = MYSQL_BOTH;
		}
		return $this->error_no ? "" : @mysql_fetch_array($this->result, $result_type);
	}
	
	function fetchsingle() {
		#$this->i++;	23.05.03 Evgeny comments. It makes fetch()
		$ary = $this->error_no ? "" : $this->fetch();
		return $ary[0];
	}

	function fetchrow() {
		$this->i++;
		return $this->error_no ? "" : @mysql_fetch_row($this->result);
	}

	function field_name($number_of_field) {
		return $this->error_no ? "" : @mysql_field_name($this->result,$number_of_field);
	}


	function error() {
		
		if (is_resource($this->site->db->connection)) {
			$error_text = mysql_error($this->site->db->connection);
		} else {
			$error_text = mysql_error();
		}

		$message = "<table><tr><td colspan=2><b><font color=red>Database error</font></b></td></tr>";
		$message .= "<tr valign=top><td><b>SQL</b>:</td><td>".$this->sql."</td></tr>";
		$message .= "<tr valign=top><td><b>Error</b>:</td><td>".$error_text."</td></tr>";
		$message .= "</table>";
		$this->debug->msg($message);
		$this->debug->print_msg();

		if (ini_get('display_errors')){
			$message = "<table><tr><td colspan=2><b><font color=red>Database error</font></b></td></tr>";
			$message .= "<tr valign=top><td><b>SQL</b>:</td><td>".htmlspecialchars($this->sql)."</td></tr>";
			$message .= "<tr valign=top><td><b>Error</b>:</td> <td>".htmlspecialchars($error_text)."</td></tr>";
			$message .= "</table>";
			echo $message;
			unset($message);
		}

		$fdat = $_POST ? $_POST : $_GET;
		if ($fdat){
			$serialized_fdat = serialize($fdat);
		}

		if (!defined("SAVE_ERROR_LOG")){
			$query = "SELECT sisu FROM config WHERE nimi='save_error_log'";

			if (is_resource($this->site->db->connection)) {
				$res = @mysql_query($query, $this->site->db->connection);
			} else {
				$res = @mysql_query($query);
			}

			if ($res){
				list($tmp) = @mysql_fetch_array($res);
			}
			define("SAVE_ERROR_LOG", ($tmp ? 1:0));
		}

		if (SAVE_ERROR_LOG){
			$query = "INSERT INTO error_log (time_of_error, source, err_text, err_type, domain, referrer, fdat_scope, ip) VALUES (NOW(), '".addslashes($this->sql)."', '".addslashes($error_text)."', 'SQL', '".addslashes($_SERVER['HTTP_HOST'])."', '".addslashes($_SERVER['REQUEST_URI'])."', '".addslashes($serialized_fdat)."', '".$_SERVER['REMOTE_ADDR']."')";

			if (is_resource($this->site->db->connection)) {
				@mysql_query($query, $this->site->db->connection);
			} else {
				@mysql_query($query);
			}
		}

	}



}
