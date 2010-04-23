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


class Timer {
# -------------------------
# kui vaja operatsiooni aega teada,
# siis timer on hea lahendus
# 
# $timer = new Timer(); - alustame mõõtmine
# $timer->get_aeg(); tagastab aeg
# $timer->stop(); peatub timerit ja tagastab aeg
# -------------------------

	var $time_start;
	var $time_end;

	function Timer () {
		$this->time_start = $this->getmicrotime();
	} # function Timer

	function getmicrotime(){ 
		list($usec, $sec) = explode(" ",microtime()); 
	    return ((float)$usec + (float)$sec); 
	} # function getmicrotime

	function timer_stop () {
		$this->time_end = $this->getmicrotime();
		return $this->get_aeg();
	} # function timer_stop 
	
	function get_aeg() {
		$time = $this->time_end ? $this->time_end : $this->getmicrotime();
		return $time - $this->time_start;
	} # function get_aeg

} # class Timer

