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


##############################
# Shows calendar in popup window
# : is usually target for selecting dates links/buttons in forms
# : writes selected date into form field (given by parameters "vorm", "lahter")
# : is independent script, not for including, new Site is generated
##############################

global $site;

preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";
include($class_path."port.inc.php");

$site = new Site(array(
	on_debug=>0,
	on_admin_keel => 1
));

$form = $site->fdat['form'] ? $site->fdat['form'] : $site->fdat['vorm'];
$form = preg_replace('/[^A-Za-z0-9 ]/', '', $form);

$form_field = $site->fdat['form_field'] ? $site->fdat['form_field'] : $site->fdat['lahter'];
$form_field = preg_replace('/[^A-Za-z0-9 ]/', '', $form_field);


?>
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/datepicker.css">
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/yld.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/jquery.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/datepicker.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'];?>/common.js.php"></script>

<script type="text/javascript">
// Handle click of OK link 
function handleOK(selected_date) {
	if (opener && !opener.closed) {
		opener.document.<?php echo $form; ?>.<?php echo $form_field; ?>.value=selected_date; 
		opener.document.<?php echo $form; ?>.<?php echo $form_field; ?>.focus(); 
	} else {
		alert('You have closed the main window.\n\nNo action will be taken on the choices in this dialog box.');
	}
	window.close();
	return false;
}
</script>

</head>

<body onLoad="if (opener) opener.blockEvents()" onUnload="if (opener) opener.unblockEvents()">
<?php

if(is_numeric($site->fdat['month']) && ($site->fdat['month'] >=1 && $site->fdat['month'] <= 12))
{
	$month = $site->fdat['month'];
}else{
	$month = date("m");
}

if(is_numeric($site->fdat['year'])){
	$year = $site->fdat['year'];
}else{ 
	$year = date("Y");
}

if(is_numeric($site->fdat['day'])){
	$day = $site->fdat['day'];
}else{
	$day = date("d");
}

?>
<script type="text/javascript">
	jQuery(function($){
		load_datepicker_settings();
		$('#inlinekalender').datepicker({
			defaultDate: new Date(<?=$year;?>, <?=$month;?> - 1, <?=$day;?>),
			onSelect: function(date) {
				handleOK(date)
			}
		});
	});
</script>

<table align="center" border="0">
	<tr>
	<td>
	<span id="inlinekalender"></span> 
	</td>
	</tr>
</table>
 


</body>
</html>