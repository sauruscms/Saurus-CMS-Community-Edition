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



class Log 
{
	var $user_id;
	var $username;
	var $component; // Admin, Config, AD, External tables, PP, Site log, Modules, User groups, Profiles, Roles, Users, SSO, Mailinglist, Workflow, Files, Forms, Languages, Templates, Replication, Search & Replace, Stats, ADR, Recycle bin, XML, Extensions, ACL, NTLM, BankConnection, ID Card, Search
	var $type;
	var $action;
	var $message;
	var $objek_id;
	
	function Log($args = array())
	{
		global $site;
		if($site->CONF['save_site_log']==1){
			// set user_id, if none given use logged in / quest, 0 is for system
			$this->user_id = (isset($args['user_id']) ? $args['user_id'] : $site->user->user_id);
			
			// set user name
			$this->username = ($this->user_id == 0 ? 'system' : ($site->user->name ? $site->user->name : $site->user->username));
			
			// set type, defaults to 0 (message)
			$this->type = (int)array_search($args['type'], $this->getTypeArray());
			
			// set action, defaults to 0 (none)
			$this->action = (int)array_search($args['action'], $this->getActionsArray());
			
			$this->component = $args['component'];
			
			$this->message = $args['message'];
			
			$this->objek_id = $args['objekt_id'];
			
			$this->write();
		}
	}
	
	function write()
	{
		$sql = "insert into sitelog (date, user_id, objekt_id, username, component, type, action, message) values (now(), '".mysql_real_escape_string($this->user_id)."', '".mysql_real_escape_string($this->objek_id)."', '".mysql_real_escape_string($this->username)."', '".mysql_real_escape_string($this->component)."', '".mysql_real_escape_string($this->type)."', '".mysql_real_escape_string($this->action)."', '".mysql_real_escape_string($this->message)."')";
		new SQL($sql);
	}
	
	function getTypeArray()
	{
		return array(
			0 => 'message',
			1 => 'ERROR',
			2 => 'WARNING',
			3 => 'NOTICE',
		);
	}
	
	function getTypeCode($type)
	{
		// keep it static!
		return array_search($type, Log::getTypeArray());
	}
	
	function getActionsArray()
	{
		return array(
			0 => 'none',
			1 => 'log in',
			2 => 'log out',
			3 => 'create',
			4 => 'delete',
			5 => 'update',
			6 => 'import',
			7 => 'export',
			9 => 'connect',
			10 => 'sync',
			11 => 'lock',
			12 => 'unlock',
			13 => 'access',
			14 => 'cancel',
			15 => 'publish',
			16 => 'hide',
			17 => 'replace',
			18 => 'optimize',
			19 => 'send',
			20 => 'start',
			21 => 'end',
			22 => 'disable',
			23 => 'enable',
			23 => 'check',
		);
	}
}
