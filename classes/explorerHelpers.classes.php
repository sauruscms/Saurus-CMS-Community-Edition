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



class TreeSearch
{
	var $found_object_ids = array();
	var $classes;
	
	function TreeSearch($searches, $classes, $language_id = 0)
	{
		global $site;
		
		$this->classes = (array)$classes;
		
		foreach($this->classes as $i => $class)
		{
			$this->classes[$i] = "'".mysql_real_escape_string($class)."'";
		}
		$sql = 'select tyyp_id from tyyp where klass in ('.implode(',', $this->classes).');';
		$result = new SQL($sql);
		$this->classes = array();
		while($row = $result->fetch('ASSOC'))
		{
			$this->classes[] = $row['tyyp_id'];
		}
		
		$where = 'tyyp_id in ('.implode(',', $this->classes).') and keel = '.mysql_real_escape_string($language_id).' ';
		foreach($searches as $field => $keyword)
		{
			$where .= ' and '.mysql_real_escape_string($field);
			if($field == 'objekt_id' || $field == 'ttyyp_id' || $field == 'page_tyyp_id' || $field == 'kesk')
			{
				$where .= ' = '.(int)$keyword;
			}
			else 
			{
				$where .= " like '%".mysql_real_escape_string($keyword)."%' ";
			}
		}
		$sql = 'select objekt_id from objekt where '.$where.';';
		//printr($sql);
		$result = new SQL($sql);
		while($row = $result->fetch('ASSOC'))
		{
			$this->found_object_ids[] = $row['objekt_id'];
		}
	}
	
	function getResults()
	{
		return $this->found_object_ids;
	}
}

class ObjectParentArray
{
	var $object_parent_array;
	
	function ObjectParentArray($object_parent_array)
	{
		$object_parent_array = (array)$object_parent_array;
		foreach($object_parent_array as $relation)
		{
			$this->object_parent_array[$relation['objekt_id']] = $relation;
		}
	}
	
	function find_parent($object_id)
	{
		return $this->object_parent_array[$object_id]['parent_id'];
	}
}

class Singleton
{

	function Singleton()
	{
		// static associative array containing the real objects, key is classname
		static $instances = array();

		// get classname
		$class = get_class($this);

		if (!array_key_exists($class, $instances))
		{
			// does not yet exist, save in array
			$instances[$class] = $this;
		}

		// PHP doesn't allow us to assign a reference to $this, so we do this
		// little trick and fill our new object with references to the original
		// class' variables:
		foreach (get_class_vars($class) as $var => $value)
		{
			$this->$var =& $instances[$class]->$var;
		}
	}
}

class SingleTimer extends Singleton 
{
	var $start;
	var $stop;
	
	function SingleTimer()
	{
		parent::Singleton();
		
		$this->start = $this->getMicroTime();
	}
	
	function getMicroTime()
	{ 
		list($usec, $sec) = explode(" ",microtime()); 
	    return ((float)$usec + (float)$sec); 
	}
	
	function stop()
	{
		$this->stop = $this->getMicroTime();
		return $this->getTime();
	}
	
	function getTime()
	{
		$time = ($this->stop ? $this->stop : $this->getMicroTime());
		return $time - $this->start;
	}
	
	function printTime($message, $as_html_comment = false)
	{
		if($as_html_comment) echo '<!-- Timer: ';
		($as_html_comment ? print($message.' '.$this->getTime()) : printr($message.' '.$this->getTime()));
		if($as_html_comment) echo ' -->';
	}
}
