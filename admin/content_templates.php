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


#
# Dünaamiliste user-defined html-mallide haldamine
#
global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");

$templ_path = $class_path."smarty/templates/";

$site = new Site(array(
	on_debug=>$_COOKIE["debug"] ? 1:0,
	on_admin_keel => 1
));
if (!$site->user->allowed_adminpage()) {
	exit;
}

######### get adminpage name
$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
$parent_pagename = $adminpage_names['parent_pagename'];
$pagename = $adminpage_names['pagename'];

$op = $site->fdat[op];

################
# type: page/content
$type = "content";

include_once("templates.php");

$site->debug->print_msg();
?>
</body>
</html>
