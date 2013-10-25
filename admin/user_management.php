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
 * Main page for user management
 * 
 * Page is divided into 3 parts:
 * LEFT: group tree, MIDDLE: users list, RIGHT: detail view of selected item.
 * Allows add, modify, duplicate, delete, etc all groups & users data.
 * 
 * @param int $group_id selected group ID
 * @param int $user_id selected user ID
 * @param string $group_search group search string
 * @param string $user_search user search string
 * @param boolean $search_subtree expand user search to subtree
 * 
 */

$class_path = "../classes/";
include_once($class_path."port.inc.php");
include_once($class_path."adminpage.inc.php");
include_once($class_path."user_html.inc.php");

$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));
if (!$site->user->allowed_adminpage()) {
	exit;
}

######### get adminpage name
$adminpage_names = get_adminpage_name(array("script_name" => $site->script_name));
$parent_pagename = $adminpage_names['parent_pagename'];
$pagename = $adminpage_names['pagename'];

#################
# GET GROUP INFO
if($site->fdat['group_id']) {
	$group = new Group(array(
		group_id => $site->fdat['group_id'],
	));
	$breadcrumb_focus_str = ",'".$group->all['name']."'";
}

#################
# GET user INFO
if($site->fdat['user_id']) {
	$user = new User(array(
		user_id => $site->fdat['user_id'],
	));
	$breadcrumb_focus_str = ",'".$user->all['firstname']." ".$user->all['lastname']."'";
}
###############
# VIEW cookie
# 1) if fdat value not set then get cookie value (default value: 'scms_um_view=overview_true')
if(!isset($site->fdat['view'])) { 
	$site->fdat['view'] = ($_COOKIE["scms_um_view"]=="overview_true") ? "overview_true":"overview_false"; 
}
# 2) save cookie
setcookie("scms_um_view", $site->fdat['view']);
# / VIEW cookie
##############

##### defaults
$site->fdat['user_id'] = isset($site->fdat['user_id']) ? $site->fdat['user_id'] : '';
$site->fdat['user_prev_id'] = isset($site->fdat['user_prev_id']) ? $site->fdat['user_prev_id'] : '';
$site->fdat['user_next_id'] = isset($site->fdat['user_next_id']) ? $site->fdat['user_next_id'] : '';

#echo 'user_search:'.$site->fdat['user_search'];
#echo "prev user: ".$site->fdat['user_prev_id'];
#echo "next user: ".$site->fdat['user_next_id'];

/*
 * SAVE bookmark
*/
if($site->fdat['bookmark'] == 1) {
	if(is_numeric($site->fdat['user_id']) && is_numeric($site->fdat['group_id'])) {
		$site->user->toggle_favorite(array(
					user_id => $site->fdat['user_id']
				));
	} else if(is_numeric($site->fdat['group_id'])) {
		$site->user->toggle_favorite(array(
					group_id => $site->fdat['group_id']
				));
	}
	$site->fdat['bookmark'] = 0;
}

/*
 * Get favorites stuff
*/
$site->user->load_favorites(true);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
<title><?=$site->title?> <?= $site->admin->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/users.js"></SCRIPT>

<SCRIPT LANGUAGE="JavaScript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>' <?=$breadcrumb_focus_str?>);
//-->
</SCRIPT>
</head>

<body style="overflow-y: auto; overflow-x: auto;">

<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">

<form name="selectform" action="<?=$site->self?>" method="GET">
<?php 
######## gather all fdat values into hidden fields
foreach($site->fdat as $fdat_field=>$fdat_value) {
	$fdat_value = htmlspecialchars(xss_clean($fdat_value));
	$fdat_field = htmlspecialchars(xss_clean($fdat_field)); 
	if($fdat_field != 'selected_devices'){
		echo '<input type=hidden id="selectform_'.$fdat_field.'" name="'.$fdat_field.'" value="'.$fdat_value.'">';
	} 
} 
?>
</form>
  
  <!-- Toolbar -->
<?php print_users_toolbar(); ?>
  <!-- //Toolbar -->

  <!-- Content area -->
  <tr valign="top"> 
    <td >

<?php 
###################
# USERS TABLE
print_users_table(array(
	"is_browse" =>0
));

?>

	
	
	</td>
  </tr>

  <!-- // Content area -->
</table>
<?php 
############ debug
# user debug:
if($site->on_debug) { 
	print  '<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%"><tr heigth="30"><td >'; 
	if($site->user) { $site->user->debug->print_msg();  }
	# guest debug: 
	if($site->guest) { 	$site->guest->debug->print_msg(); }
	$site->debug->print_msg();
} # debug
?>

</body>
</html>