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
# Generates general javascript
# : js functions is mixed with CMS variables and cant be in "js/yld.js"
# : is independent script, not for including, new Site is generated
##############################

global $site;
global $class_path;


preg_match('/\/(admin|editor)\//i', $_SERVER["REQUEST_URI"], $matches);
$class_path = $matches[1] == "editor" ? "../classes/" : "./classes/";

$include_once = true;
include($class_path."port.inc.php");

$site = new Site(array(
	on_debug=>0

));


?>
function checkForumFields(variant){
// variant=1 => check message body, autor, headline
// variant=2 => check message body, headline

	if (variant==1){autor = document.forumFrm.nimi.value;}
	headline = document.forumFrm.pealkiri.value;
	message = document.forumFrm.text.value;

	if (variant==1){
		if (autor.length < 2){
			alert('<?=trim($site->sys_sona(array(sona => "Forum alert: Enter your name!", tyyp=>"kujundus"))) ?>'); return false;
		}
	}
	if (headline.length < 2){
		alert('<?=trim($site->sys_sona(array(sona => "Forum alert: Please fill in the subject!", tyyp=>"kujundus"))) ?>'); return false;
	} else if (message.length < 2){
		alert('<?=trim($site->sys_sona(array(sona => "Forum alert: Please fill in the message body!", tyyp=>"kujundus"))) ?>'); return false;
	} else {
		return true;
	};

}

function load_datepicker_settings()
{
	if(window.jQuery){

		jQuery(function($){

			$.datepicker.regional['ee'] = {clearText: '<?=$site->sys_sona(array('sona' => 'clear', 'tyyp' =>'kalender'))?>', 
				clearStatus: '',
				closeText: '<?=$site->sys_sona(array('sona' => 'close', 'tyyp' =>'kalender'))?>', 
				closeStatus: '',
				prevText: '<?=$site->sys_sona(array('sona' => 'previous', 'tyyp' =>'kalender'))?>',  
				prevStatus: '',
				nextText: '<?=$site->sys_sona(array('sona' => 'next', 'tyyp' =>'kalender'))?>', 
				nextStatus: '',
				currentText: '<?=$site->sys_sona(array('sona' => 'today', 'tyyp' =>'kalender'))?>', 
				currentStatus: '',
				monthNames: ['<?=$site->sys_sona(array('sona' => 'month1', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'month2', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'month3', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'month4', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'month5', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'month6', 'tyyp' =>'kalender'))?>', 
				'<?=$site->sys_sona(array('sona' => 'month7', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'month8', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'month9', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'month10', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'month11', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'month12', 'tyyp' =>'kalender'))?>'],
				monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun', 
				'Jul','Aug','Sep','Okt','Nov','Dec'],
				monthStatus: '', 
				yearStatus: '',
				weekHeader: 'Ve', 
				weekStatus: '',
				dayNamesShort: ['<?=$site->sys_sona(array('sona' => 'weekday7', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday1', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday2', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday3', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday4', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday5', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday6', 'tyyp' =>'kalender'))?>'],
				dayNames: ['<?=$site->sys_sona(array('sona' => 'weekday7_long', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday1_long', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday2_long', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday3_long', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday4_long', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday5_long', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday6_long', 'tyyp' =>'kalender'))?>'],
				dayNamesMin: ['<?=$site->sys_sona(array('sona' => 'weekday7', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday1', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday2', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday3', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday4', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday5', 'tyyp' =>'kalender'))?>','<?=$site->sys_sona(array('sona' => 'weekday6', 'tyyp' =>'kalender'))?>'],
				dayStatus: 'DD', 
				dateStatus: 'D, M d',
				dateFormat: 'dd.mm.yy', 
				firstDay: 1, 
				initStatus: '', 
				showOtherMonths: true,
				speed: '',
				isRTL: false};

			});
		$.datepicker.setDefaults($.datepicker.regional['ee']);
	}

	return true;
}