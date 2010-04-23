<?php

/**
 * Smarty plugin to reformat MySQL date to human readable string
 *
 * Modifies date in string format to text string:
 *		Today, Yesterday, Day before yesterday, 7 days ago, 7th May 2008
 *		Täna, Eile, Üleeile, 7 päeva tagasi, 7. mai 2008
 *  
 *	Example usage:
 *	{$article_details->fdate|date_to_string:$lang}
 *	- use fdate instead of date!
 *	- $lang is optional, defaults to current language
 *
 * @package Saurus4
 *
 * @param string $date
 * @param string $lang
 * @return string
 */
function smarty_modifier_mysql_date_to_string($date, $lang = '')
{
	global $site;

	$daynames = array(
		'et' => array('Täna', 'Eile', 'Üleeile', 'päeva tagasi', ),
		'en' => array('Today', 'Yesterday', 'Day before yesterday', 'days ago', ),
	);
	
	$months = array(
		'et' => array('jaanuar', 'veebruar', 'märts', 'aprill', 'mai', 'juuni', 'juuli', 'august', 'september', 'oktoober', 'november', 'detsember'),
	);
	
	if(!$lang) {
		// get current language
		$lang = $site->extension;
	}

	if ($lang == 'ee') $lang = 'et'; // Saurus backwards compatibility ee = et
	
	if (!in_array($lang, array_keys($daynames))) $lang = 'en';

	$days_ago = intval((time() - strtotime($date)) / (60 * 60 * 24));

	// today, yesterday, day before yest
	if ($days_ago >= 0 and $days_ago < 3) $string = $daynames[$lang][$days_ago];
	// 3 days ago .. 7 days ago
	else if ($days_ago >= 3 and $days_ago < 8) $string = $days_ago.' '.$daynames[$lang][3];
	// numeric date formats
	else {
		switch ($lang) {
			case 'et':
				$string = date('j', strtotime($date)).'. '.$months[$lang][date('n', strtotime($date)) - 1].' '.date('Y', strtotime($date));
				break;
			case 'en':
				$string = date('jS F Y', strtotime($date));
				break;
			default:
				$string = $site->db->MySQL_ee($date);
				break;
		} 
	}

	return $string;
}

?>