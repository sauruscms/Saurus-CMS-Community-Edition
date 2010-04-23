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



class Node
{
	var $children = array();
	var $container; // NodeObject 
	var $object_parent_array = array();
	var $parentNode;
	
	function Node($data)
	{
		$this->container = $data;
	}
	
	function &addChild($data)
	{
		$this->children[] = new Node($data);
		$this->children[count($this->children) - 1]->parentNode =& $this;
		return $this->children[count($this->children) - 1];
	}
	
	function traverse(&$action, $order = 'BEFORE')
	{
		$action->beforeExec($this);
		
		if($order == 'BEFORE') $action->execute($this);

		foreach($this->children as $child)
		{
			$child->traverse($action);
		}
		
		if($order == 'AFTER') $action->execute($this);
		
		$action->afterExec($this);
	}
	
	function populateTree($remove_objects = array())
	{
		$objects = array();
		$clip = array();
		
		$this->object_parent_array = array();
		if(sizeof($remove_objects)>=1)
		{
			$sql_where=' where objekt_id not in ('.implode(",",$remove_objects).') ';
		}else{
			$sql_where='';
		}

		$sql = 'select objekt_id, parent_id from objekt_objekt '.$sql_where.' order by parent_id, sorteering desc';
		$result = new SQL($sql);
		while($row = $result->fetch('ASSOC'))
		{
			if($row['parent_id'] == $this->container->get('objekt_id'))
			{
				$clip[] =& $this->addChild(new NodeObject($row));
				$this->object_parent_array[] = $row;
			}
			else $objects[$row['parent_id']][] = $row;
		}
		
		for($i = 0; $i < count($clip); $i++)
		{
			$parent_id = $clip[$i]->container->get('objekt_id');
			if(array_key_exists($parent_id, $objects))
			{
				foreach($objects[$parent_id] as $object)
				{
					$clip[] =& $clip[$i]->addChild(new NodeObject($object));
					$this->object_parent_array[] = $object;
				}
				
				unset($objects[$parent_id]);
			}
		}
	}
	
	function countChildren()
	{
		return count($this->children);
	}
}

