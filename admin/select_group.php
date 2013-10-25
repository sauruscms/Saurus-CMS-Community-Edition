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
 * Popup page for selecting group(s) or user(s)
 * 
 * @param string op - action name
 * @param string op2 - step 2 action name
 * @param boolean select_one - if requesting page saves 1 or multiple user(group) ID values. Default is 0, page saves multiple values.
 * @param string paste2box - opener input box name where to send result ID value
 * @param string pastename2box - opener input box name where to send result NAME value
 * @param string show_checkboxes = 0/1, if we need to select multiple groups, put here 1. Default is 0
 * 
 */

global $site;

$class_path = "../classes/";
include($class_path."port.inc.php");
include($class_path."adminpage.inc.php");
include($class_path."user_html.inc.php");

$site = new Site(array(
	on_debug => ($_COOKIE["debug"] ? 1:0),
	on_admin_keel => 1
));

$op = $site->fdat['op'];
$op2 = $site->fdat['op2'];



$site->fdat['group_id'] = (int)($site->fdat['group_id'] ? $site->fdat['group_id'] : get_topparent_group(array("site"=>$site)));


#################
# GET GROUP INFO
if($site->fdat['group_id']) {
	$group = new Group(array(
		group_id => $site->fdat[group_id],
	));
	$breadcrumb_focus_str = ",'".$group->all['name']."'";
}

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



#################
# STEP2: SEND SELECTED DATA BACK TO OPENER
if($op2) {

	##############
	# refresh opener and close popup
	?>
	<SCRIPT language="javascript"><!--
	
<?php 
	// 0) If we choose multiple values of groups and if 'paste2box' parameter was given then past result into that input box:
		if($site->fdat['paste2box'] && $site->fdat['show_checkboxes']) {
?>
			if(window.opener.document.getElementById('<?= $site->fdat[paste2box] ?>')) {
				window.opener.document.getElementById('<?= $site->fdat[paste2box] ?>').value= '<?=$site->fdat['selgroups']?>';

				<?php if ($site->fdat['run_opener_function']){ ?>
					window.opener.<?=$site->fdat['run_opener_function']?>();	
				<?php } ?>
				window.close();
			}
			//-->
			<?="</scr"."ipt>" ?>
<?php exit; } ?>









	// 1) if 'paste2box' parameter was given then past result into that input box
	if('<?=$site->fdat[paste2box]?>'!='') {
		<?php 
		if (is_numeric($site->fdat['user_id'])){
			$user = new User(array(
				user_id => $site->fdat['user_id'],
			));	
		}


		?>
			if(window.opener.document.getElementById('<?= $site->fdat[paste2box] ?>')) {

//alert('<?=$site->fdat[paste2box]?>=<?=$site->fdat[user_id]?>');

				var path_obj = window.opener.document.getElementById('<?= $site->fdat[paste2box] ?>');
				var path_nameobj = window.opener.document.getElementById('<?= $site->fdat[pastename2box] ?>');
				var path_contactnameobj = window.opener.document.getElementById('<?= $site->fdat[pastecontactname2box]?>');
				var path_contactinfoobj = window.opener.document.getElementById('<?= $site->fdat[pastecontactinfo2box]?>');



		//If we need to return person's parent group
		<?php if ($site->fdat['pasteparent2box']) { ?>

			//  if person selected:
			<?php if ($site->fdat['user_id']){ ?>
			<?php 
				$parent_group = new Group(array(
					group_id => $user->all['group_id'],
				));

			?>

				if(path_obj) {
					path_obj.value = 'group_id:<?=$user->all['group_id']?>';
				}
				if(path_nameobj) {
					path_nameobj.value = '<?=$parent_group->all['name']?>';
				}
				if(path_contactnameobj) {
					if (path_contactnameobj.value=='') {path_contactnameobj.value = '<?=$user->name?>';}
				}
				
<?php 

###################
# If contact fields came in url, fill in contact info:

if ($site->fdat["USER_CONTACT_FIELDS"]){
	$contact_info = array();
	$usercontactfields = explode(",", $site->fdat["USER_CONTACT_FIELDS"]);
	foreach ($usercontactfields as $ucfld){
		$ucfld = trim($ucfld);
		if ($user->all[$ucfld]){
			$contact_info[] = $user->all[$ucfld];
		}
	}
	$contact_info = join(", ", $contact_info);
}

###################
# If person hasn't any contacts, then contact_info = Group contacts :

if (!$contact_info && $site->fdat["GROUP_CONTACT_FIELDS"]){
	$contact_info = array();
	$groupcontactfields = explode(",", $site->fdat["GROUP_CONTACT_FIELDS"]);
	foreach ($groupcontactfields as $gcfld){
		$gcfld = trim($gcfld);
		if ($parent_group->all[$gcfld]){
			$contact_info[] = $parent_group->all[$gcfld];
		}
	}
	$contact_info = join(", ", $contact_info);
}

###################
# if contact info found, pass this data to external text-box:

if ($contact_info){ ?>


				if(path_contactinfoobj) {
					if (path_contactinfoobj.value=='') {path_contactinfoobj.value = '<?=$contact_info?>';}
				}

<?php } //if ($contact_info)  ?>


				


			//  if group selected:
			<?php } else { ?>


				if(path_obj) {
					 path_obj.value = 'group_id:<?=$site->fdat[group_id]?>';
				}
				if(path_nameobj) {
					path_nameobj.value = '<?=$group->name?>';
				}
/*
Not need for groups:
				if(path_contactnameobj) {
					if (path_contactinfoobj.value) {path_contactnameobj.value = '<?=$group->name?>';}
				}
				if(path_contactinfoobj) {
				// XXX here we need to take parameter names from CONF:
					if (path_contactinfoobj.value) {path_contactinfoobj.value = '<?=$group->all['address_town'].", ".$group->all['address_street'].", ".$group->all['email'].", tel:".$group->all['phone'] ?>';}
				}
*/

			<?php } ?>


		<?php } else { ?>

				if(path_obj) {
					// group is selected
					if('<?=$site->fdat[user_id]?>'=='') { path_obj.value = 'group_id:<?=$site->fdat[group_id]?>'; }
					// user is selected
					else { path_obj.value = 'user_id:<?=$site->fdat[user_id]?>'; }
				}
				if(path_nameobj) {
					// group is selected
					if('<?=$site->fdat[user_id]?>'=='') { path_nameobj.value = '<?=$group->name?>'; }
					// user is selected
					else { path_nameobj.value = '<?=$user->name?>'; }
				}

		<?php } ?>



			}	
	}
	// 2) if no 'paste2box' parameter given then return selection result as URL
	else {
		var userframe = opener.window.top.window.document.getElementById('profile');
		if(userframe) {
			/* This has been added */
			<?php 
				if($site->fdat[user_id] > 0) {
			?>
			userframe.src = userframe.src + '&add=1&user=<?=$site->fdat[user_id]?>';
			<?php 
				} else 
				if($site->fdat[group_id] > 0) {
			?>
			userframe.src = userframe.src + '&add=1&group=<?=$site->fdat[group_id]?>';
			<?php 
				}
			?>
		} else {
			/* This is the URL part */
			var oldurl = window.opener.location.toString();
			//Needed for filemanager
			var FileManager = window.opener.document.getElementById('isFileManager');
			if(FileManager) {
				var FileManagerUrl = FileManager.value;
			} else {
				var FileManagerUrl = '';
			}
			oldurl = oldurl.replace(/\&remove_group_id=(\d+)/g, "");
			oldurl = oldurl.replace(/\&remove_user_id=(\d+)/g, "");

			// group is selected
			if('<?=$site->fdat[user_id]?>'=='') {
				var re = new RegExp("\&selected_groups=");
				// 1) default: several group ID values are allowed on requesting page
				if('<?=$site->fdat[select_one]?>'!='1') {
					// if match found in opener URL then replace it
					if (oldurl.match(re)) {
						newurl = oldurl.replace(/\&selected_groups=(\d+)/g, "\&selected_groups=<?=$site->fdat[group_id]?>,$1");
					} else { // else add it to the end
						newurl = oldurl + FileManagerUrl + "&selected_groups=<?=$site->fdat[group_id]?>";
					}
				}
				// 2) only one group ID is allowed on requesting page
				else {
					// delete both old parameters and add new parameter
					newurl = oldurl.replace(/\&selected_groups=(\d+)/g, "");
					newurl = newurl.replace(/\&selected_users=(\d+)/g, "");
					newurl = newurl + FileManagerUrl + "&selected_groups=<?=$site->fdat[group_id]?>";
				} // select_one 1/0
			}
			// user is selected
			else {
				var re = new RegExp("\&selected_users=");
				// 1) default: several user ID values are allowed on requesting page
				if('<?=$site->fdat[select_one]?>'!='1') {
					// if match found in opener URL then replace it
					if (oldurl.match(re)) {
						newurl = oldurl.replace(/\&selected_users=(\d+)/g, "\&selected_users=<?=$site->fdat[user_id]?>,$1");
					} else { // else add it to the end
						newurl = oldurl + FileManagerUrl + "&selected_users=<?=$site->fdat[user_id]?>";
					}
				}
				// 2) only one user ID is allowed on requesting page
				else {
					// if match found in opener URL then delete both old parameters and add new parameter
					newurl = oldurl.replace(/\&selected_groups=(\d+)/g, "");
					newurl = newurl.replace(/\&selected_users=(\d+)/g, "");
					newurl = newurl + FileManagerUrl + "&selected_users=<?=$site->fdat[user_id]?>";
				} // select_one 1/0
			}
			window.opener.location=newurl;
		} //if
	} // where to send result

	window.close();
	// --></SCRIPT>
	<?php 
	exit;

}
# / STEP2: SEND SELECTED DATA BACK TO OPENER
#################


##################
# POPUP HTML

##### defaults
$site->fdat['user_id'] = isset($site->fdat['user_id']) ? $site->fdat['user_id'] : '';
$site->fdat['user_prev_id'] = isset($site->fdat['user_prev_id']) ? $site->fdat['user_prev_id'] : '';
$site->fdat['user_next_id'] = isset($site->fdat['user_next_id']) ? $site->fdat['user_next_id'] : '';

$site->fdat['search_subtree'] = isset($site->fdat['search_subtree']) ? $site->fdat['search_subtree'] : "1";
$site->fdat['user_search'] = isset($site->fdat['user_search']) ? $site->fdat['user_search'] : "1";
$site->fdat['group_search'] = isset($site->fdat['group_search']) ? $site->fdat['group_search'] : "1";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<meta http-equiv="Cache-Control" content="no-cache">
<link rel="stylesheet" href="<?=$site->CONF[wwwroot].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/users.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>

<script language="JavaScript">
<!--
var sel_groups = new Array();


	function sel_box_group(gr_id, mybox){
		if (mybox.checked==true){
			sel_groups[gr_id] = gr_id;
		} else {
			sel_groups[gr_id] = 0;
		}
		//alert(sel_groups);
	}


function send_box_values() {

	var selected_groups = '';
	for (i = 0; i < sel_groups.length; i++){
		if (sel_groups[i] == i){
			selected_groups = selected_groups+','+i;
		}
	}
	//alert(selected_groups);	
	document.getElementById('selectform_selgroups').value=selected_groups;
	document.forms['selectform'].submit();
}


//-->
</script>

</head>

<body class="popup_body" onload="make_breadcrumb('<?=$site->sys_sona(array(sona => "groups", tyyp=>"kasutaja"))?>' <?=$breadcrumb_focus_str?>);window.focus();">
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<SCRIPT LANGUAGE="JavaScript">
	<!--
		function make_breadcrumb() {
			var ar = arguments;
			var html = '';
			for (i = 0; i < ar.length; i++) {
				if(i != 0) {
					html += '<img src="../styles/default/gfx/header/breadcrumb_arrow.gif" width="15" height="9">';
				}
				html +=  '<a href="#" class="scms_breadcrumb">' + ar[i] + '</a>';
			}
			var header_breadcrumb = document.getElementById("header_breadcrumb");
			var header_title = document.getElementById("header_title");
			if(header_title) header_title.innerHTML = ar[(ar.length-1)];
			if(header_breadcrumb) header_breadcrumb.innerHTML = html;
		}
	//-->
	</SCRIPT>

  <tr>
    <td height="60" class="scms_header">
	
      <table width="100%" border="0" cellspacing="10" cellpadding="0">
        <tr>
          <td class="scms_header_title"><div id="header_title" style="display:inline">&nbsp;</div><br>
            <div id="header_breadcrumb" style="display:inline"><a href="#" class="scms_breadcrumb">&nbsp;</a></div></td>
        </tr>
      </table>
	
    </td>
  </tr>

<form name="selectform" action="<?=$site->self?>" method="GET">
<?php 
# op2 must exist!
$site->fdat['op2'] = isset($site->fdat['op2']) ? $site->fdat['op2'] : "";

# if multiple_select, 'selgroups' must esist!
if ($site->fdat['show_checkboxes'] && !$site->fdat['selgroups']){
	$site->fdat['selgroups'] = "";
}


######## gather all fdat values into hidden fields
foreach($site->fdat as $fdat_field=>$fdat_value) { 
	if($fdat_field != 'selected_devices'){
		$fdat_value = htmlspecialchars(xss_clean($fdat_value));
		$fdat_field = htmlspecialchars(xss_clean($fdat_field)); 
		echo '<input type=hidden id="selectform_'.$fdat_field.'" name="'.$fdat_field.'" value="'.$fdat_value.'">
		';
	} 
} 
?>
</form>

  <?php 
  ######### TOOLBAR
  print_users_toolbar(); ?>

  <!-- Content area -->
  <tr valign="top" height=100%> 
    <td>
		<?php 
		###################
		# USERS TABLE
		print_users_table(array(
			"is_browse" => 1,
			"expand_all" => $site->fdat['show_checkboxes'],
			"show_checkboxes" => $site->fdat['show_checkboxes']
		));
		?>
	
	</td>
  </tr>
  <!-- // Content area -->
</table>


<?php $site->debug->print_msg(); ?>
</body>
</html>