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
 * 
 *
 * @param unknown_type $params
 * @param unknown_type $smarty
 */
function smarty_function_print_flowplayer($params, &$smarty)
{
   	global $site;
    ?>
    
<script type="text/javascript">
	var noConflict = false;
	if (typeof jQuery == 'undefined')
	{
		noConflict = true;
		document.write('<script src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path']; ?>/jquery.js" type="text/javascript"><\/script>');
	}
</script>
<script type="text/javascript">
	if(noConflict) jQuery.noConflict();
 	var wwwroot = '<?php echo $site->CONF['wwwroot'];?>';
	if (jQuery('.scms-flowplayer-anchor').length && typeof flowplayer == 'undefined')
	{
		document.write('<script type="text/javascript" src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path']; ?>/flowplayer/flowplayer.min.js"><\/script>');
		document.write('<script type="text/javascript" src="<?php echo $site->CONF['wwwroot'].$site->CONF['js_path']; ?>/scms_flowplayer.js"><\/script>');
	}
</script>

<?php
}
