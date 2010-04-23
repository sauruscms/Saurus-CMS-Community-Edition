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
# function init_sites
#	name => default: "sites"
# Returns array of subsites
function smarty_function_init_sites($params,&$smarty) {
	global $site, $leht;

	$content_template = &$leht->content_template;
	
	##################
	# default values

	extract($params);

	if (!isset($name)) {
		$name = "sites";
	}

	# / default values
	###################

	unset($sites);

	$sql = $site->db->prepare("SELECT a.nimi AS glossary, a.locale AS locale, b.glossary_id AS glossary_id, b.keel_id AS id, b.nimi AS name, b.extension AS extension, b.on_default AS is_default FROM keel AS a LEFT JOIN keel AS b ON a.keel_id = b.glossary_id WHERE b.on_kasutusel = '1' ORDER BY b.nimi");
	$sth = new SQL($sql);
	$sth->debug->msg($sth->debug->get_msgs());

	while ($result = $sth->fetch()) {
		
		unset($subsite);

		$subsite->id = $result["id"];
		$subsite->name = $result["name"];
		$subsite->extension = $result["extension"];
		$subsite->is_default = $result["is_default"];

		$subsite->glossary = $result["glossary"];
		$subsite->glossary_id = $result["glossary_id"];
		$subsite->locale = $result["locale"];

		// if not in editor and use aliases has been enabled
		if (!$site->in_editor && $site->CONF['use_aliases']) {
			$subsite->href = $site->CONF['wwwroot'] . '/' . $result['extension'] . '/';
		} else {
			$subsite->href = $site->CONF['wwwroot'] . ($site->in_editor ? '/editor' : '') . '/?lang=' . $result['extension'];
		}

		$subsite->home_id = $site->alias((array('key' => 'rub_home_id', 'keel' => $result['id'])));

		$sites[] = $subsite;
	}

	##############
	# assign to template variables

	$smarty->assign(array(
		$name => $sites
	));

	}
