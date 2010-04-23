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



function smarty_function_init_events ($params, &$smarty)
{
	global $site;
	
	extract($params);
	
	if(!$name) { $name="eventlist"; }
	if(!$order) { $order="starttime DESC"; }
	if(!$start_time) $start_time = date('d.m.Y');
	if(!$end_time) $end_time = date('d.m.Y',strtotime(substr($start_time,6,4).'-'.substr($start_time,3,2).'-'.substr($start_time,0,2)));
	

	if(!function_exists('smarty_function_init_articles'))
	{
		require_once $smarty->_get_plugin_filepath('function', 'init_articles');
	}
	
	if(!function_exists('smarty_function_init_article'))
	{
		require_once $smarty->_get_plugin_filepath('function', 'init_article');
	}

	if($start_time == $end_time)
	{
		$selection = $site->db->prepare('((obj_artikkel.starttime between obj_artikkel.starttime and ?) and (obj_artikkel.endtime between ? and obj_artikkel.endtime))', $site->db->ee_MySQL($start_time));
	}
	else 
	{
		$selection = $site->db->prepare('((obj_artikkel.starttime between ? and ?) or (obj_artikkel.endtime between ? and ?))', $site->db->ee_MySQL($start_time), $site->db->ee_MySQL($end_time), $site->db->ee_MySQL($start_time), $site->db->ee_MySQL($end_time));		
	}
	
	// replace events with articles
	smarty_function_init_articles(array(
		'name' => $name,
		'parent' => $params['parent'],
		'position' => $params['position'],
		'buttons' => $params['buttons'],
		'on_create' => $params['on_create'],
		'profile' => 'converted_event',
		'where' => ($params['where'] ? '('.$params['where'].') AND ' : '').$selection,
		'start' => $params['start'],
		'limit' => $params['limit'],
		'order' => $order,
	),
	&$smarty);
	
	$events = $smarty->get_template_vars($name);
	
	foreach($events as $i => $event)
	{
		smarty_function_init_article(array(
			'name' => md5($i),
			'id' => $event->id,
		),
		&$smarty);
		
		$article = $smarty->get_template_vars(md5($i));
		
		// add article content as the event description
		$events[$i]->description = $article->lead.$article->body;
		
		// add start and end attributes
		$start_date = explode(' ', $site->db->MySQL_ee_long($event->starttime));
		$events[$i]->start_date = $start_date['0'];
		$events[$i]->start_time = $start_date['1'];
		
		$end_date = explode(' ', $site->db->MySQL_ee_long($event->endtime));
		$events[$i]->end_date = $end_date['0'];
		$events[$i]->end_time = $end_date['1'];
	}
	
	$smarty->assign($name, $events);

}
