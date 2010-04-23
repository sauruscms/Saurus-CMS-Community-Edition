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



global $class_path;

include($class_path.'explorerHelpers.classes.php');

class FulltextSearch 
{
	var $search_words = array();
	var $keywords = array();
	var $search_query = '';
	var $search_count = 0;
	var $show_exec_times = false;
	var $result_objects = array();
	var $section_id = array(); //search root
	var $query_sql;
	var $ok = false;
	var $use_fulltext = false;
	
	function FulltextSearch($search_string, $section_id, $classes, $use_fulltext = false, $keel)
	{
		global $site;
		
		$this->use_fulltext = $use_fulltext;
		
		if($section_id){
			$this->section_id[]=$section_id;
		}else{
			foreach(explode(",",$keel) as $v){
					$this->section_id[]=$site->alias(array('key' => 'rub_home_id', 'keel' => $v));
			}
		}

		foreach(explode(' ', $search_string) as $search_word)
		{
			$this->search_words[] = mysql_escape_string($search_word);
			$this->keywords[] = mysql_escape_string($search_word);
		}
		
		if($this->show_exec_times) printr($site->timer->get_aeg().' - alamlist SQL');
		$this->query_sql = new AlamlistSQL (array(
			'klass' => (is_array($classes) ? implode(',', $classes) : ''),
			'order' => ($this->use_fulltext ? 'fulltext_keywords desc, fulltext_score desc, objekt.aeg desc' : 'fulltext_keywords desc, objekt.aeg desc'),
		));
		$this->query_sql->add_where('keel in  ('.$keel.')');

		
		if($use_fulltext)
		{
			$this->query_sql->add_where("(match (pealkiri_strip, sisu_strip) against ('".implode(' ', $this->search_words)."') or match (fulltext_keywords) against ('".implode(' ',$this->keywords)."'))");
			$this->query_sql->add_select("match (pealkiri_strip, sisu_strip) against ('".implode(' ', $this->search_words)."') as fulltext_score");
		}
		else 
		{
			$sql = '0 ';
			foreach ($this->search_words as $search_word) if (strlen($search_word) > 0) # Bug 2831
			{
				$sql .= "or pealkiri_strip like '%$search_word%' ";
				$sql .= "or sisu_strip like '%$search_word%' ";
				$sql .= "or fulltext_keywords like '%$search_word%' ";
			}
			$this->query_sql->add_where("($sql)");
		}
		
		if(count($this->search_words))
			$this->ok = true;
	}
	
	function execSearch()
	{
		global $site;
		
		if($this->ok)
		{
			$this->show_exec_times = ($_COOKIE['debug'] ? true : false);
			
			// bug #2477, hidden objects must be excluded
			$this->query_sql->add_where("objekt.is_hided_in_menu = '0'");
			
			if($this->show_exec_times) printr($site->timer->get_aeg().' - alamlist');
			$query_result = new Alamlist(array(
				'alamlistSQL' => $this->query_sql,
			));
			if($this->show_exec_times) $query_result->debug->print_msg();
			if($this->show_exec_times) printr($site->timer->get_aeg().' - done, get tree');
			
			$object_parent_array = array();
			$sql = 'select objekt_id, parent_id from objekt_objekt;';
			$result = new SQL($sql);
			while($row =  $result->fetch('ASSOC'))	$object_parent_array[] = $row;
			$objArray = new ObjectParentArray($object_parent_array);
			
			if($this->show_exec_times) printr($site->timer->get_aeg().' - done, start exclude all but homesection');
	
			while($object = $query_result->next())
			{
				$parent_id = $objArray->find_parent((string)$object->objekt_id);
				$loop_guard = 0;
				$loop_ids = array();
				while($parent_id)
				{

					$loop_guard++;
					if(!in_array($parent_id, $loop_ids)) $loop_ids[] = $parent_id;
					if($loop_guard > 100)
					{
						new Log(array(
							'component' => 'Search',
							'type' => 'ERROR',
							'message' => 'Neverending loop! ID list: '.implode(',', $loop_ids),
							'user_id' => 0,
						));
						exit;
					}

					if(in_array($parent_id,$this->section_id))
					{

						$this->result_objects[$object->all['klass']][] = $object;
						$this->search_count++;
						break;
					}
					$parent_id = $objArray->find_parent($parent_id);
				}
			}
			if($this->show_exec_times) printr($site->timer->get_aeg().' - done, ');
		}
	}
	
	function getResults()
	{
		return $this->result_objects;
	}
}

class FulltextSearchBoolean extends FulltextSearch 
{
	var $exclude_words = array();
	
	function FulltextSearchBoolean($search_string, $exclude_words, $boolean_mode, $last_change, $order, $section_id, $classes, $keel)
	{
		global $site;

		if($section_id){
			$this->section_id[]=$section_id;
		}else{
			foreach(explode(",",$keel) as $v){
					$this->section_id[]=$site->alias(array('key' => 'rub_home_id', 'keel' => $v));
			}
		}


		foreach(explode(' ', $search_string) as $search_word)
		{
			switch (strtoupper($boolean_mode))
			{
				case 'AND':	$this->search_words[] = '+'.mysql_real_escape_string($search_word); break;
				case 'PHRASE': $this->search_words = array('"'.mysql_real_escape_string($search_string).'"'); break;
				default: $this->search_words[] = mysql_real_escape_string($search_word); break;
			}
			// $this->keywords[] = mysql_escape_string($search_word);
		}
		
		if($exclude_words) foreach(explode(' ', $exclude_words) as $exclude_word)
		{
			$this->exclude_words[] = '-'.mysql_real_escape_string($exclude_word);
		}
		
		switch($order)
		{
			case 'relevance' : $order = 'fulltext_search_score desc'; break;
			case 'aeg' :
			case 'date' : $order = 'objekt.aeg desc'; break;
			case 'pealkiri' :
			case 'title' : $order = 'objekt.pealkiri'; break;
			default: $order = 'objekt.aeg desc'; break;
		}
		
		if($this->show_exec_times) printr($site->timer->get_aeg().' - alamlist SQL');
		$this->query_sql = new AlamlistSQL (array(
			'klass' => (is_array($classes) ? implode(',', $classes) : ''),
			'order' => $order,
		));

		//$this->query_sql->add_where('keel = '.(int)$site->keel);
		$this->query_sql->add_where('keel in ('.$keel.')');


		if(count($this->search_words) && count($this->exclude_words))
		{
			$boolean_mode_where = "(match (pealkiri_strip, sisu_strip) against ('".implode(' ', $this->search_words)." ".implode(' ', $this->exclude_words)."' in boolean mode) or match (fulltext_keywords) against ('".implode(' ',$this->search_words)."' in boolean mode))";
			$boolean_mode_select = "match (pealkiri_strip, sisu_strip) against ('".implode(' ', $this->search_words)." ".implode(' ', $this->exclude_words)."' in boolean mode) as fulltext_search_score";
		}
		elseif(count($this->search_words))
		{
			$boolean_mode_where =  "(match (pealkiri_strip, sisu_strip) against ('".implode(' ', $this->search_words)."' in boolean mode) or match (fulltext_keywords) against ('".implode(' ',$this->search_words)."' in boolean mode))";
			$boolean_mode_select =  "match (pealkiri_strip, sisu_strip) against ('".implode(' ', $this->search_words)."' in boolean mode) as fulltext_search_score";
		}
		elseif(count($this->exclude_words))
		{
			$boolean_mode_where = "(match (pealkiri_strip, sisu_strip) against ('".implode(' ', $this->exclude_words)."' in boolean mode))";
			$boolean_mode_select = "match (pealkiri_strip, sisu_strip) against ('".implode(' ', $this->exclude_words)."' in boolean mode) as fulltext_search_score";
		}

		$this->query_sql->add_select($boolean_mode_select);
		$this->query_sql->add_where($boolean_mode_where);
		
		$intervals = array('0','1 DAY','7 DAY','1 MONTH','3 MONTH','6 MONTH','1 YEAR');
		switch((int)$last_change)
		{
			case 1 : $this->query_sql->add_where('aeg >= subdate(now(), interval 1 day)'); break;
			case 2 : $this->query_sql->add_where('aeg >= subdate(now(), interval 7 day)'); break;
			case 3 : $this->query_sql->add_where('aeg >= subdate(now(), interval 1 month)'); break;
			case 4 : $this->query_sql->add_where('aeg >= subdate(now(), interval 3 month)'); break;
			case 5 : $this->query_sql->add_where('aeg >= subdate(now(), interval 6 month)'); break;
			case 6 : $this->query_sql->add_where('aeg >= subdate(now(), interval 1 year)'); break;
			
			default: break;
		}
		
		if(count($this->search_words) || count($this->exclude_words))
		{
			$this->ok = true;
		}
	}
}

class AdvancedSearch extends FulltextSearch 
{
	var $exclude_words = array();
	
	function AdvancedSearch($search_string, $exclude_words, $boolean_mode, $last_change, $order, $section_id, $classes, $keel)
	{
		global $site;
		
		if($section_id){
			$this->section_id[]=$section_id;
		}else{
			foreach(explode(",",$keel) as $v){
					$this->section_id[]=$site->alias(array('key' => 'rub_home_id', 'keel' => $v));
			}
		}
		
		switch($order)
		{
			case 'aeg' :
			case 'date' : $order = 'objekt.aeg desc'; break;
			case 'pealkiri' :
			case 'title' : $order = 'objekt.pealkiri'; break;
			default: $order = 'objekt.aeg desc'; break;
		}
		
		if($this->show_exec_times) printr($site->timer->get_aeg().' - alamlist SQL');
		$this->query_sql = new AlamlistSQL (array(
			'klass' => (is_array($classes) ? implode(',', $classes) : ''),
			'order' => $order,
		));
		
		$this->query_sql->add_where('keel in  ('.$keel.')');
		
		foreach(explode(' ', $search_string) as $search_word)
		{
			switch (strtoupper($boolean_mode))
			{
				case 'AND':
					$this->search_words[] = mysql_real_escape_string($search_word);
					$this->query_sql->add_where('('.$this->prepareSearchQuery('AND').')');
					break;
				case 'PHRASE':
					$this->search_words = array(mysql_real_escape_string($search_string));
					$this->query_sql->add_where('('.$this->prepareSearchQuery('PHRASE').')');
					break;
				default:
					$this->search_words[] = mysql_real_escape_string($search_word);
					$this->query_sql->add_where('('.$this->prepareSearchQuery('OR').')');
					break;
			}
		}
		
		if($exclude_words) foreach(explode(' ', $exclude_words) as $exclude_word)
		{
			$this->exclude_words[] = mysql_real_escape_string($exclude_word);
		}
		
		$sql = '0 ';
		foreach ($this->search_words as $search_word) if (strlen($search_word) > 3)
		{
			$sql .= "or pealkiri_strip not like '%$search_word%' ";
			$sql .= "or sisu_strip not like '%$search_word%' ";
		}
		$this->query_sql->add_where("($sql)");
		
		$intervals = array('0','1 DAY','7 DAY','1 MONTH','3 MONTH','6 MONTH','1 YEAR');
		switch((int)$last_change)
		{
			case 1 : $this->query_sql->add_where('aeg >= subdate(now(), interval 1 day)'); break;
			case 2 : $this->query_sql->add_where('aeg >= subdate(now(), interval 7 day)'); break;
			case 3 : $this->query_sql->add_where('aeg >= subdate(now(), interval 1 month)'); break;
			case 4 : $this->query_sql->add_where('aeg >= subdate(now(), interval 3 month)'); break;
			case 5 : $this->query_sql->add_where('aeg >= subdate(now(), interval 6 month)'); break;
			case 6 : $this->query_sql->add_where('aeg >= subdate(now(), interval 1 year)'); break;
			
			default: break;
		}
		
		if(count($this->search_words) || count($this->exclude_words))
		{
			$this->ok = true;
		}
	}
	
	function prepareSearchQuery($mode = 'OR')
	{
		switch ($mode)
		{
			case 'OR' :
			case 'PHRASE' :
				$sql = '0 ';
				break;
			case 'AND' :
				$sql = '1 ';
				break;
			default:
				$sql = '0 ';
				break;
		}
		foreach ($this->search_words as $search_word)
		{
			switch ($mode)
			{
				case 'OR' :
				case 'PHRASE' :
					$sql .= "or pealkiri_strip like '%$search_word%' ";
					$sql .= "or sisu_strip like '%$search_word%' ";
					$sql .= "or fulltext_keywords like '%$search_word%' ";
					break;
				case 'AND' :
					$sql .= "and (pealkiri_strip like '%$search_word%' ";
					$sql .= "or sisu_strip like '%$search_word%' ";
					$sql .= "or fulltext_keywords like '%$search_word%') ";
					break;
				default:
					$sql .= "or pealkiri_strip like '%$search_word%' ";
					$sql .= "or sisu_strip like '%$search_word%' ";
					$sql .= "or fulltext_keywords like '%$search_word%' ";
					break;
			}
		}
		
		return $sql;
	}
}
