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



class TraversalAction //interface
{
	function beforeExec(&$node) {}
	function execute(&$node) {}
	function afterExec(&$node) {}
}

class JavaScriptTree extends TraversalAction 
{
	var $jstring;
	var $jmethods;
	var $mark_searched;
	
	function JavaScriptTree($methods = array(), $mark_searched = array(), $mark_selected = array())
	{
		$this->jstring = '';
		$this->jmethods = (array)$methods;
		$this->mark_searched = (array)$mark_searched;
		$this->mark_selected = (array)$mark_selected;
	}
	
	function getScript()
	{
		//strip unneeded commas (IE won't work otherwise) and empty array's
		$this->jstring = str_replace(array('[]',',}',',]',), array(0, '}', ']',), $this->jstring);
		//strip last comma
		$this->jstring = ereg_replace(',$', '', $this->jstring);
		return $this->jstring;
	}
	
	function beforeExec(&$node)
	{
		/* @var $node Node */
		//object start bracket
		$this->jstring .= '{';
		//add properties
		$this->jstring .= 'container: 0,';
		$this->jstring .= 'folded: 1,';
		$this->jstring .= 'className: \'\',';
		$this->jstring .= 'inSearch: '.(in_array($node->container->get('objekt_id'), $this->mark_searched) ? '1' : '0').',';
		$this->jstring .= 'selected: '.(in_array($node->container->get('objekt_id'), $this->mark_selected) ? '1' : '0').',';
		$this->jstring .= 'parent: null,';
		//add methods
		foreach($this->jmethods as $method)
		{
			$this->jstring .= $method.':'.$method.',';
		}
		//object children
		$this->jstring .= 'children:[';
	}
	
	function execute(&$node)
	{
		/* @var $node Node */
	}
	
	function afterExec(&$node)
	{
		/* @var $node Node */
		//close children array
		$this->jstring .= '],';
		//make all array keys into property objects with corresponding values
		foreach($node->container->getAll() as $prop => $value)
		{
			$this->jstring .= $prop.':new '.$prop.'(\''.str_replace("'", "\'", $value).'\'),';
		}
		//close object bracket
		$this->jstring .= '},';
	}
}

class InitTree extends TraversalAction 
{
	var $opArray;
	var $open_objects = array();
	var $load_object_fields = array();
	var $objects = array();
	var $classes = array();
	var $language_id;
	
	function InitTree(&$opArray, $open_objects, $load_object_fields, $classes, $language_id = 0)
	{
		global $site;
		//global $timer;
		//echo '<!-- start InitTree: '.$timer->get_aeg().' -->';
		
		$this->opArray =& $opArray;
		$this->open_objects = (array)$open_objects;
		$this->load_object_fields = (array)$load_object_fields;
		$this->classes = (array)$classes;
		$this->language_id = $language_id;
		
		$langs= array();
		
		if(count($this->load_object_fields) == 0) $this->load_object_fields = array('objekt_id', 'pealkiri', 'on_avaldatud', 'tyyp_id', );
		
		//echo '<!-- start load open objects: '.$timer->get_aeg().' -->';
		//init open objects
		foreach($this->open_objects as $i => $object_id)
		{
			if($object_id) //do not to discard root object if it's 0
			{
				$object = new Objekt(array('objekt_id' => $object_id,));
				//not an object or no permissions
				if($object->objekt_id && $object->all['keel'] == $this->language_id)
				{
					$this->addObject($object->all);
					$this->objects[$object->objekt_id]['select_checkbox'] = 0;
				}
				else 
				{
					unset($this->open_objects[$i]);
				}
			}
		}
		
		//set open object id's into cookie
		setcookie('swk_unfolded_ids', implode(',', $this->open_objects));
		//echo '<!-- end load open objects: '.$timer->get_aeg().' -->';
		// /init open objects
															//printr($this->objects);
		// init open objects children
		//echo '<!-- start load open objects children: '.$timer->get_aeg().' -->';
		foreach($this->open_objects as $object_id)
		{
			$objects_sql = new AlamlistSQL (array(
				'parent' => $object_id,
				'klass' => implode(',', $this->classes),
				'where' => 'keel = '.mysql_escape_string($language_id),
			));
			//$objects_sql->add_select('objekt.pealkiri');
			
			$objects_list = new Alamlist (array(
				'alamlistSQL' => $objects_sql,
			));
			
			$objects = array();
			while($object = $objects_list->next())
			{
				$this->addObject($object->all);
				$this->objects[$object->objekt_id]['select_checkbox'] = 0;
			}
		}
		// /init open objects children
		//echo '<!-- end InitTree: '.$timer->get_aeg().' -->';
	}
	
	function execute(&$node)
	{
		global $site;
		global $timer;
		
		/* @var $node Node */
		//mark object levels
		$level = -1;
		$object_id = $node->container->get('objekt_id');
		//echo '<!-- execute() find parent start: '.$timer->get_aeg().' -->';
		$loop_guard = 0;
		$loop_ids = array();
		while($object_id !== null)
		{
			$loop_guard++;
			if(!in_array($object_id, $loop_ids)) $loop_ids[] = $object_id;
			if($loop_guard > 100)
			{
				new Log(array(
					'type' => 'ERROR',
					'message' => 'Neverending loop! ID list: '.implode(',', $loop_ids),
					'user_id' => 0,
				));
				exit;
			}
			$object_id = $this->opArray->find_parent($object_id);
			$level++;
		}
		//echo '<!-- execute() find parent end  : '.$timer->get_aeg().' -->';
		$node->container->fill(array('level' => $level,));
		// /mark object levels
		
		$object_id = $node->container->get('objekt_id');
		
		//fill objects
		if(array_key_exists($object_id, $this->objects))
		{
			$node->container->fill($this->objects[$object_id]);
		}
	}
	
	function addObject($all)
	{
		foreach($this->load_object_fields as $field)
		{
			$this->objects[$all['objekt_id']][$field] = $all[$field]; 
		}
	}
}

