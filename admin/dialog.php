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



session_start();

// control set
$dialog_setup = (string)$_GET['dialog_setup'];
if(!$dialog_setup) $_GET['dialog_setup'] = $dialog_setup = 'dialog_setup';
$dialog_setup = $_SESSION['dialogs'][$dialog_setup];

// encoding
if(!isset($dialog_setup['encoding'])) $dialog_setup['encoding'] = 'UTF-8';

// buttons
if(!sizeof($dialog_setup['buttons']))
{
	$dialog_setup['buttons']['close'] = array(
		'type' => 'button',
		'value' => 'X',
		'onclick' => 'window.close();',
	);
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head> 	
		<title><?=$dialog_setup['window_title'];?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$dialog_setup['encoding'];?>" />
		<link rel="stylesheet" href="../styles/default/default_admin_page.css" media="screen" />
		<script type="text/javascript" src="../js/ie_position_fix.js"></script>
		<script type="text/javascript">
			window.onload = function()
			{
				new contentBox('content_box', '40px', '0px', '40px', '0px');
			}
			
			var contentBox = function(id, top, right, bottom, left)
			{
				this.box = document.getElementById(id);
				this.box.style.top = top;
				this.box.style.right = right;
				this.box.style.bottom = bottom;
				this.box.style.left = left;
			}
		</script>
	</head>
	<body id="popup">
		<div id="mainContainer">
			<div class="titleArea">
				<?=$dialog_setup['dialog_title'];?>
			</div><!-- / titleArea -->
			<div class="contentArea" id="content_box">
				<div class="contentAreaContent">
					<?=$dialog_setup['content'];?>
				</div><!-- / contentAreaContent -->
			</div><!-- / contentArea -->
			<div class="footerArea">
				<div class="actionButtonsArea">
					<?php foreach($dialog_setup['buttons'] as $button) { ?>
					<input <?php foreach($button as $attribute => $value) echo $attribute.'="'.$value.'" ';?>/>
					<?php } ?>
				</div>
			</div class="footerArea"><!-- / footerContainer -->
		</div><!-- / mainContainer -->
	</body>
</html>