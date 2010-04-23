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


/**
 * Saurus CMS user privilege checking for custom admin-pages. 
 * This file must be included at the top of the custom admin-page,
 * if access is not granted to the current user then page load will be terminated and login-page is displayed.
 * 
 */
global $site;

if(!isset($class_path)) { $class_path = "../classes/"; }

include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");

$site = new Site(array(
	on_debug=> ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));

# if user is not logged in, show loginpage (Bug #2679)
if(!$site->user) {
	include_once($class_path."login_html.inc.php");
	admin_login_form(array("site" => $site, "auth_error" => 0));
	exit;
}

# load  *admin pages* permissions
$site->user->adminpermissions = $site->user->load_adminpermissions();			

if (!$site->user->allowed_adminpage()) {
	exit;
}
