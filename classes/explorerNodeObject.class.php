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



class NodeObject
{
	var $all = array();
	
	function NodeObject($all)
	{
		$this->fill($all);
	}
	
	function fill($data)
	{
		global $site;
		
		$data = (array)$data;
		
		//translate values
		if($data['klass']) $data['klass'] = strtolower($site->sys_sona(array('sona' => 'tyyp_'.$data['klass'], 'tyyp' => 'System')));
		
		//convert dates
		if($data['aeg']) $data['aeg'] = $site->db->MySQL_ee($data['aeg']);
		
		//don't overwrite whole all array, just the given values.
		$this->all = array_merge($this->all, $data);
	}
	
	function get($field)
	{
		if(array_key_exists($field, $this->all)) return $this->all[$field];
		else return null;
	}
	
	function getAll()
	{
		return $this->all;
	}
}

