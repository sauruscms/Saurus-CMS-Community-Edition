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



error_reporting(7);

class CONFIG {
// config faili reeglidega formateeritud 
// andmete lugemine/salvestamine
	var $CONF;

	function CONFIG ($text) {
#old	$this->BaasObjekt();	

		$this->site = &$GLOBALS{site};
		$this->debug = new Debug();
		
		$this->CONF = array();
		$this->Import($text);
		$this->debug->msg("CONF ".sizeof($this->CONF));		
	} # function CONFIG

	function Import($text) {
	# ---------------------
	# config texti parsimine
	# ---------------------
		$this->debug->msg("IMPORT: $text");
		
		foreach (split("[\n\r]+",$text) as $rida) {
			$this->debug->msg("rida: $rida");
			if (preg_match('/^\s*([^#=]+?)\s*=\s*([^#]+)\s*(?:#.*)?$/',$rida,$matches)) {
				$this->CONF[trim($matches[1])] = trim($matches[2]);
				$this->debug->msg("SET $matches[1] = $matches[2]");
			};
		}
	} # function Import

	function get($key) {
		$result = $this->CONF["$key"];
		$this->debug->msg("GET '$key' tagastas '$result'");
		return $result;
	}# function get

	function put($key,$value) {
		$this->CONF[$key] = $value;
	} #function put

	function Export() {
		$delim="\n";
		foreach ($this->CONF as $key=>$value) {
			$result .= "$key = $value".$delim;
		}
		return $result;
	} #function Export
} # class CONFIG
