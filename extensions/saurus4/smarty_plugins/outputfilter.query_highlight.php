<?php

function smarty_outputfilter_query_highlight($source, &$smarty)
{
	global $site;
	
	$highlight_words = array();
	
	if ($site->fdat['highlight'])
	{
		$highlight_words = explode(',', trim($site->fdat['highlight']));
	}
	
	if ($site->fdat['query'] || $site->fdat['otsi'])
	{
		$query_words = explode(' ', trim(($site->fdat['query'] ? $site->fdat['query'] : $site->fdat['otsi'])));
		foreach ($query_words as $key => $word)
		{
			if(!preg_match('/\*$/', preg_quote($word, '/')) && strlen($word) < 4) unset($query_words[$key]);
		}
		
		$highlight_words = array_merge($highlight_words, $query_words);
	}
	
	if(!count($highlight_words))
	{
		return $source;
	}
	
	$source = str_replace('$', '<!-- [[[-----HIGHLIGH_REPLACE_dollar-----]]] -->', $source);
	
	preg_match("/\<(body([^>]+)|body)\>.*?\<\/body\>/is", $source, $match);
	if($match[0])
	{
		$replaceable_html = $match[0];
	}
	else 
	{
		$replaceable_html = $source;
	}
	
	foreach($highlight_words as $word)
	{
		$word = preg_quote(str_replace('*', '', trim($word)), '/');
		if($word)
		{
			$replaceable_html = preg_replace("/(?!<.*?)($word)(?![^<>]*?>)/is", '<span class="highlight">$1</span>', $replaceable_html);
		}
	}
	
	$source = preg_replace("/\<(body([^>]+)|body)\>.*?\<\/body\>/is", $replaceable_html, $source);

	$source = str_replace('<!-- [[[-----HIGHLIGH_REPLACE_dollar-----]]] -->', '$', $source);
	
	return $source;
} 

?>