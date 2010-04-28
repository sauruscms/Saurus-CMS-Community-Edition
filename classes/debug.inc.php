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


###########################
# DEBUG
###########################

if (!class_exists('Debug')){
class Debug {
	var $on_debug;
	var $colors;
	var $msg_array;

	function Debug() {
		$this->colors = Array("FAFAFA","EEEEEE");
		$this->msg_array = Array();
		$this->on_debug = 1;
	}

	function hash_get ($ary,$strings_only,$ary_name) {
		# tagastame hash vormis key => value

		$result = "<table><tr><td colspan=2><b>ARRAY $ary_name</b></td></tr>";
		if (is_array($ary)) {
			reset ($ary);
			while (list ($key, $val) = each ($ary)) {
				if ($strings_only && preg_match("/^\d+$/",$key)) {continue;}
				$result .= "<tr bgcolor=#".$this->colors[++$i%2]."><td align=right><b>$key:</b></td><td><i>$val</i></td></tr>\n";
			}
			$result.="</table>";
		} else {
			$result = "scalar: $ary";
		}
		return $result;
	}

	function get_msgs () {
		return $this->msg_array;
	}	

	function fetch_msgs () {
		$result = $this->msg_array;
		$this->msg_reset();
		return $result;
	}	

	function print_hash ($ary,$strings_only,$ary_name) {
		if(show_debug()) print $this->hash_get($ary,$strings_only,$ary_name);
	}

	function msg ($message) {
		if (is_array($message)) {
			reset($message);
			while (list($key,$msg)=each($message)) {
				array_push($this->msg_array,$msg);
			}
		} else {
			array_push($this->msg_array,$message);
		}
	}	
	
	function msg_reset () {
		$this->msg_array = Array();
	}	

	function print_msg() {
		if(show_debug()) print $this->get_msg();
	}

	function get_msg() {
		$result = "<hr><table><tr><td colspan=3 class=txt><b>Debug Messages</b></td></tr>";
		#reset ($msg_array);

		for ($i=1;$i<sizeof($this->msg_array)+1;$i++) {
			$result .= "<tr valign=top bgcolor=".$this->colors[$i%2]."><td bgcolor=#FFFFFF class=txt>&nbsp;</td><td align=right class=txt><b>$i:</b></td><td class=txt>".htmlspecialchars($this->msg_array[$i-1])."</td></tr>\n";
		}
		$result .= "</table>";
		$this->msg_reset();
		
		return $result;
	}
}


	function print_vars($obj) {
		$arr = get_object_vars($obj);
		while (list($prop, $val) = each($arr))
			echo "::: $prop = $val<br>";
	}

	function print_methods($obj) {
		$arr = get_class_methods(get_class($obj));
		foreach ($arr as $method)
			echo "::: function $method()<br>";
	}

	function class_parentage($obj, $class) {
		global $$obj;
		if (is_subclass_of($$obj, $class)) {
			echo "Object $obj belongs to class ".get_class($$obj);
			echo " a subclass of $class\n";
		} else {
			echo "Object $obj does not belong to a subclass of $class\n";
		}
	}
}
