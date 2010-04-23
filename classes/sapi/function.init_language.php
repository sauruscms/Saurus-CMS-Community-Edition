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
# DEPRECATED since version 4.6.4
#
# function init_language
#	name => default: "language"
# Returns array of site languges
function smarty_function_init_language($params,&$smarty) {
	global $site, $leht;

	$content_template = &$leht->content_template;
	
	##################
	# default values

	extract($params);

	if(!isset($name)) { $name = "language"; }

	# / default values
	###################

	$sql = $site->db->prepare("SELECT keel_id AS id, nimi AS name, extension FROM keel WHERE on_kasutusel");
	$sth = new SQL($sql);
	$sth->debug->msg($sth->debug->get_msgs());

	while ($result = $sth->fetch()) {
		
		// if not in editor and use aliases has been enabled
		if(!$site->in_editor && $site->CONF['use_aliases'])
			$result['href'] = $site->CONF['wwwroot'].'/'.$result['extension'];
		else
			$result['href'] = $site->CONF['wwwroot'].($site->in_editor ? '/editor' : '').'/?lang='.$result['extension'];

		$result['home_id'] = $site->alias((array('key' => 'rub_home_id', 'keel'=>$result['id'])));

		$language[] = $result;
	}

	##############
	# assign to template variables

	$smarty->assign(array(
		$name => $language
	));

}