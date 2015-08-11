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


error_reporting(isset($_GET["error_reporting"]) ? $_GET["error_reporting"] : "7");

global $site;
global $class_path;
$class_path = "../classes/";
include($class_path."port.inc.php");


error_reporting(isset($_GET["error_reporting"]) ? E_ALL : 7);


$debug = $_COOKIE["debug"] ? 1:0;





$site = new Site(array(
	on_debug=> ($debug ? 1 : 0),
	on_admin_keel => 1,
));

require_once($class_path."auto.inc.php");

if($site->fdat['test']) {

	if (!$site->CONF['enable_mailing_list']){
		echo "<b><font color=red>Mailinglist is not allowed in system configuration! Anyway show debug information:</font></b><br>";
	}

	echo "<b>THIS IS TEST RUN: no actual e-mails will be send and no data modified.</b><br>";
	#if(!$site->fdat['test']) {echo "<a href=\"?test=1\">Run test</a><br>";}
	flush();
	auto_maillist(1, $site->fdat['test']);

} else if ($site->CONF['enable_mailing_list']) {

	if ($site->fdat['manual']){
		str_repeat(" ", 300);
		?>
		<script language="JavaScript">
		<!--
			window.close();
		//-->
		</script>
		<?php 
		flush();
	}
	auto_maillist(1,0);

}


###########################
# debug info

$site->debug->msg("SQL päringute arv = ".$site->db->sql_count."; aeg = ".$site->db->sql_aeg);
$site->debug->msg("TÖÖAEG = ".$site->timer->get_aeg());
$site->debug->print_msg();
