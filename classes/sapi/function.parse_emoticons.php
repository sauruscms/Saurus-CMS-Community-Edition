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


#################################
# function parse_emoticons
#	text => <text to parse>
#	[name => template variable the output will be assigned to]
# Replace pre-defined emoticons by graphic icons
#
# DEPRECATED it's now a Smarty modifier

function smarty_function_parse_emoticons ($params,&$smarty) {
	global $site, $leht, $template;

	extract($params);
	if(!isset($text)) { return; }
	
	$text = do_smileys($text); ### add emoticons

	if(isset($name)){ # if template variable is set, then assign it:
		$smarty->assign(array(
			$name => $text
		));
	}
	else { # if no template variable is set then print text out:
		print $text;
	}
}
