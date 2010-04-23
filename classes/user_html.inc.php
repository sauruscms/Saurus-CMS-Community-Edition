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
# FUNCTION print_users_table
function print_users_table(){

	global $site;
	global $class_path;
	
	global $group; # selected 1 group info
	global $user; # selected 1 user info

	$args = func_get_arg(0);

	include_once($class_path."adminpage.inc.php");
	include_once($class_path."custom.inc.php");

	###########
	# PERMISSIONS CHECK - get read-allowed group ID-s for current user
	$read_allowed_groups = get_allowed_groups();
	#echo printr($read_allowed_groups);

?>


<?
############################
# CONTENT TABLE
?>
	<TABLE class="scms_content_area" width="100%" height="100%" border=0 cellspacing=0 cellpadding=0>
	<TR>
		<!-- Left column -->
		<TD class="scms_left">
			<TABLE border=0 width="100%" height="100%" cellspacing=0 cellpadding=0>
			<!-- Search -->
			<TR>
				<TD id="search">
					<?
					#################
					# SEARCH BOX
					print_search_box(array(
						"hide_end_form_tag" => $args['show_checkboxes']	// if we need to show checkboxes, don't print end form tag
					));
					?>				
				</TD>
			</TR>
			<!-- //Search -->
			<TR>
				<TD valign=top>
					<div id=navigation class="scms_left_div">
					<TABLE width="100%" border="0" cellpadding="0" cellspacing="0">
					<?
						/*
						 * Get favorites 
						*/
						$favorites = $site->user->get_favorites(array(
							fetch_user_favorits => 1,
							fetch_group_favorits => 1,
						));
						if(count($favorites)>0) {
					?>
						<!-- Favorites -->
						<tr>
							<td class="scms_groupheader">
								<IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/bookmark.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle>&nbsp;&nbsp;<?=$site->sys_sona(array(sona => "Favorites", tyyp=>"admin"))?>
							</td>
						</tr>
						<TR>
							<TD>
								<table border=0>
								<?
									/*
									* Display favorites
									*/

									foreach($favorites as $favorite_data) {
								?>
									<tr>
										<td style="padding-right:4px;padding-left:16px"><img src="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/gfx/icons/16x16/users/<?= (strlen($favorite_data['group_id'])>0?'group':($favorite_data['is_superuser']?'superuser':'user')) ?>.png" width="16" height="16"></td>
										<td><a href="javascript:<?= strlen($favorite_data['group_id'])>0?"select_group('".$favorite_data['group_id']."')":"document.getElementById('selectform_user_id').value='".$favorite_data['user_id']."';document.getElementById('selectform_group_id').value='".$favorite_data['user_group_id']."';document.forms['selectform'].submit();"; ?>"><?= strlen($favorite_data['group_id'])>0?$favorite_data['name']:$favorite_data['firstname'].' '.$favorite_data['lastname'] ?></a></td>
									</tr>
								<?
									} //foreach
								?>
								</table>
							</TD>
						</TR>
						<!-- //Favorites -->
						<?
							} //if favorites
						?>
						<!-- Menu tree -->
						<TR>
							<TD>
								<?
								#################
								# GROUPS TREE: 2 views - tree & search result list

								#### GROUP WHERE
								if($site->user->is_superuser) { 
									$group_where_str = " 1=1 "; 
								}
								else { 
									# $group_where_str = $site->db->prepare(" FIND_IN_SET(group_id,?) ", join(",",$read_allowed_groups));
									$group_where_str = " group_id IN('".join("','", $read_allowed_groups)."') ";

								}

								###### GET tree html
								$tree_html = get_grouptree_html(array(
									 "where_str" => $group_where_str,
									 "expand_all"  => $args['expand_all'],
									 "show_checkboxes" => $args['show_checkboxes']
								));
								###### PRINT tree html
								if ($args['show_checkboxes']){
									echo "</form>";
								}
								?>
								
								<?echo $tree_html; ?>          
								
								<?
								# / GROUPS TREE
								#################
								?>
							</TD>
						</TR>
						<!-- //Menu tree -->
					</TABLE>
					</div>
				</TD>
			</TR>
			</TABLE>
		<!-- // Left column -->
		</TD>
	<?
	# / LEFT COLUMN
	##################
?>

<?

# if not browse window
if($args['is_browse']) { $site->fdat['view']='overview_false'; }

############################
# MIDDLE LIST

	#################
	# USERS WHERE

	$where_sql[] = $group_where_str;

	# group filter
	if($site->fdat['group_id'] && !$site->fdat['flt_role']) {
		# 1. if search criteria is defined, then search from all current open subtree
		if($site->fdat['search'] && $site->fdat['user_search'] && $site->fdat['search_subtree']) {
			########## a) user search AND group search BOTH	=>  use group search result
			if($site->fdat['group_search']) {
#				$where_sql[] = $site->db->prepare("FIND_IN_SET(group_id,?)",join(',',$group_search_result));
			}
			########## b) user search AND NOT group search => don't use group filter at all
			else {
				/*
				# variant 2 => use group menu
				$menu->get_full_subtree(array("parent_id" => $site->fdat['group_id']));
				# $menu->full_subtree is variable from group tree and is all ID-s of group children
				$tree_with_children = $menu->full_subtree;
				#echo printr($tree_with_children);
				$where_sql[] = $site->db->prepare("FIND_IN_SET(group_id,?)",join(',',$tree_with_children));
				*/
			}
		}
		# 2. if no search criteria, then show only users belonging to current group
		else {
			$where_sql[] = $site->db->prepare("group_id=?",$site->fdat['group_id']);
		}
	}
	# user search filter, search from fixed fields ()
	if($site->fdat['search'] && $site->fdat['user_search']) {
		$where_sql[] = $site->db->prepare("(username LIKE ? OR firstname LIKE ? OR lastname LIKE ? OR email LIKE ?)",
			$site->fdat['search'].'%',
			$site->fdat['search'].'%',
			$site->fdat['search'].'%',
			$site->fdat['search'].'%'
		);
	}
	# user role filter, search from user_roles
	if($site->fdat['flt_role']) {
		$where_sql[] = $site->db->prepare("(user_roles.role_id = ?)",
			$site->fdat['flt_role']
		);
		### extra JOIN needed
		$join = " LEFT JOIN user_roles ON user_roles.user_id = users.user_id ";
	}

	$where = is_array($where_sql) ? " WHERE ".join(" AND ",$where_sql) : '';

	### if search is used then set page to null
	if(!isset($site->fdat['page']) && ($site->fdat['search'] || $site->fdat['flt_role']) ) { $site->fdat['page'] = 0;}

	# / USERS WHERE
	#################

?>
<!-- Middle column -->
<TD class="scms_middle<?=($site->fdat['view']=='overview_true' ? "" : "_dialogwindow")?>" >

	<TABLE style="width: 98%" height="100%" border="0" cellspacing="0" cellpadding="0">
		<!-- Table title -->
		<TR height=25>
		<TD>

			<?######### grey title header ########?>

				<table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr class="scms_pane_header"> 
                     <td>			
					  <IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/users/user.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle> <?=$site->sys_sona(array(sona => "users", tyyp=>"admin"));?>
					 </td>
					 <td align="right">
					 </td>
					 <td style="width:120px">
						<!-- Paging -->
						<?
						#################
						# pagenumbers table
						$sql = "SELECT COUNT(*) FROM users ".$join.$where;
						$sth = new SQL($sql);
						$total_count = $sth->fetchsingle();
						
						$pagenumbers = print_pagenumbers(array(
							"total_count" => $total_count,
							"rows_count" => 20,
						));
						# / pagenumbers 
						#################
						?>			
						<!-- //Paging -->
					 </td>
                    </tr>
                 </table>
			<?######### / grey title header ########?>


		</TD>
	</TR>
	<!-- // Table title -->

		 
 <?
	#################
	# table column names
	?>
	<!-- Table heading -->
	<TR height=25>
	<TD class="scms_tableheader">

	<?
	####### get assoc.array of visible fieldnames and translations
	$visible_fields = get_visible_fields(array(
		"prefpage_name" => ($args['is_browse'] ?'select_group':'user_management_fields'),
		"sst_name" => 'custom,kasutaja',
	));
#printr($visible_fields);
	####### print column headers table
	print_column_headers(array(
		"visible_fields" => $visible_fields,
		"page_prefs_url" => '&name='.($args['is_browse'] ?'select_group':'user_management_fields').'&sst_name=custom,kasutaja'
	));
	##### td width: calculate percents
	$td_width = intval((100/sizeof(array_keys($visible_fields)))).'%';

	?>
	</TD>
	</TR>
	<!-- //Table heading -->

	<?
	# / COLUMN NAMES
	#################
	?>
		<!-- Table data -->
		<TR>
			<TD valign=top>
					<!-- Scrollable area -->
					<div id=listing class="scms_middle_div">
						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="scms_table">
<?
	#################
	# users list

	########### ORDER
	$order = " ORDER BY ".$site->fdat['sortby']." ".$site->fdat['sort'];

	########### SQL

 	$sql = $site->db->prepare("SELECT users.* FROM users ");
	$sql .= $join;
	$sql .= $where;
	$sql .= $order;
	$sql .= $pagenumbers['limit_sql'];

#print $sql;
	$sth = new SQL($sql);
	$listusers = array();
	while($tmp = $sth->fetch()){
		$listusers[] = $tmp;
	}
		#################
		# loop over users
		$i = 0;
		foreach($listusers as $listuser){
			$next_id = 	$listusers[$i+1]['user_id'];
			$prev_id = 	$listusers[$i-1]['user_id'];
			
			
#			$href = "?group_id=".$listuser[group_id]."&user_id=".$listuser[user_id];
#			$href .= "&page=".$site->fdat['page'];
$href = "javascript:document.getElementById('selectform_user_id').value='".$listuser[user_id]."';document.getElementById('selectform_group_id').value='".$listuser[group_id]."';document.getElementById('selectform_user_next_id').value='".$next_id."';document.getElementById('selectform_user_prev_id').value='".$prev_id."';document.forms['selectform'].submit();";

			# user is spueruser
			if($listuser['is_predefined']) {$icongif = "superuser.png"; }
			# ordinary user, not Everybody group member 
			else { $icongif = "user.png"; }
		?>
          <tr id="<?=$listuser[user_id]?>" <?=($site->fdat['user_id'] == $listuser[user_id] ? ' class="scms_activerow"' : '')?>>
			<?##### icon ######?>
			<td width="20"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/users/<?=$icongif?>" width="16" height="16"></td>

			<?# loop over visible fields
			foreach(array_keys($visible_fields) as $key=>$field){ 
				if($field=='fullname'){ $listuser[$field] = $listuser['firstname'].' '.$listuser['lastname']; }

				?>
				<td width="<?=$td_width?>" ondblclick="javascript:void(openpopup('<?=$site->CONF['wwwroot'].$site->CONF['adm_path']?>/edit_user.php?user_id=<?=$listuser[user_id]?>&tab=user&op=edit','user','366','450'))"><a href="<?=$href?>"><?=($listuser[$field] ? $listuser[$field] : '&nbsp;')?></a></td>
			<? } # foreach ?>
			<?##### delete ######?>
<!--			<td width="16" align="right">&nbsp;</td>-->
		
			</tr>
		<?
			$i++;
		}
		# / loop over users
		#################
		?>
					  </table>
					</div>
					<!-- //Scrollable area -->
                </TD>
              </TR>
			<!-- //Table data -->

            </TABLE>
		</TD>
<?
# / MIDDLE LIST
############################
?>



		</TD>
  </TR>
		<?
		###################
		# select buttons
		if($args['is_browse']){
		?>
        <tr align="right" height=30> 
          <td valign="top" colspan="2" style="padding-top: 10px; padding-right:10px" > 

<? if ($args['show_checkboxes']){ ?>
			<input type="button" value="<?=$site->sys_sona(array(sona => "vali", tyyp=>"admin")) ?>" onclick="javascript:selectform.op2.value='selectclose'; send_box_values();" style="width: 60px">
<? } else { ?>
            <input type="button" value="<?=$site->sys_sona(array(sona => "vali", tyyp=>"admin")) ?>" onclick="javascript:selectform.op2.value='selectclose';selectform.submit();" style="width: 60px">
<? } ?>
			<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();" style="width: 60px"> 
          </td>
        </tr>
		<?}?>
</TABLE>

<?
}
# / FUNCTION print_users_table
##############################

##############################
# FUNCTION print_users_toolbar
function print_users_toolbar(){

	global $site;
	global $class_path;
	
	global $group; # selected 1 group info
	global $user; # selected 1 user info

?>

  <!-- Toolbar -->
  <tr>
	<td class="scms_toolbar">
	<?
	################################
	# FUNCTION BAR TABLE
	?>
	<TABLE cellpadding=0 cellspacing=0 border=0>
	  <?
		############# detail buttons activity
		# when no group is selected then buttons are in inactive mode (non-clickable)
			if(!$site->fdat['group_id'] && !$site->fdat['user_id']) {
				$in_active = '_inactive';			
			} else {
				$in_active = '';			
			}
		  # popup window name
		  if($site->fdat['user_id']) {
			  $user_selected = 1;
			  $popup_href = $site->CONF['wwwroot'].$site->CONF['adm_path']."/edit_user.php?user_id=".$site->fdat['user_id']."&tab=user";
			  $popup_name = 'user';
		  }
		  else {
			  $user_selected = 0;
			  $popup_href = $site->CONF['wwwroot'].$site->CONF['adm_path']."/edit_group.php?group_id=".$site->fdat['group_id']."&tab=group";
			  $popup_name = 'group';
		  }
		  # if everybody group is selected, make delete button inactive
		  if ($site->fdat['group_id']==get_topparent_group(array("site" => &$site)) && !$site->fdat['user_id']) {
			$everybody_group = 1;
		  }

		  # if superuser is selected, make delete button inactive
		  if ($site->fdat['user_id'] && $user->all['is_predefined']) {
			$is_superuser = 1;
		  }

	  ############# / detail buttons activity
	  ?>
        <TR> 
			<?############ new buttons ###########?>

				<TD nowrap><a href="javascript:void(openpopup('<?=$site->CONF['wwwroot'].$site->CONF['adm_path']?>/edit_user.php?tab=user&op=new&group_id=<?=$site->fdat['group_id']?>','user','366','450'))" ><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/users/user.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id="pt">&nbsp;<?=$site->sys_sona(array(sona => "user", tyyp=>"kasutaja"))?></A></TD>
				<TD  nowrap><a href="javascript:void(openpopup('<?=$site->CONF['wwwroot'].$site->CONF['adm_path']?>/edit_group.php?tab=group&op=new&group_id=<?=$site->fdat['group_id']?>','group','366','450'))"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/users/group.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id="pt"><?=$site->sys_sona(array(sona => "group", tyyp=>"kasutaja"))?></A></TD>
			<?############ edit button ###########?>

				<TD nowrap><?if(!$in_active){?><a href="javascript:void(openpopup('<?=$popup_href?>&op=edit','<?=$popup_name?>','366','450'))"><?}?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/edit.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id="pt"> <?=$site->sys_sona(array(sona => "muuda", tyyp=>"editor"))?><?if(!$in_active){?></a><?}?></TD>

			<?############ delete button (inactive for Everybody group)###########?>
				<TD><?if(!$in_active && !$everybody_group)	{?><a href="javascript:void(openpopup('<?=$popup_href?>&op=delete','<?=$popup_name?>','413','108'))"><?} else{?>&nbsp;<?}?><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete<?=($in_active || $everybody_group ? '_inactive' : '')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id="po"><?if(!$in_active && !$everybody_group){?></a><?}?></TD>

			<?############ duplicate button : inactive for everybody group ###########?>
				<TD><?if(!$in_active && !$everybody_group){?><a href="javascript:void(openpopup('<?=$popup_href?>&op=copy','<?=$popup_name?>','413','108'))"><?} else{?>&nbsp;<?}?><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/editcopy<?=($in_active || $everybody_group ? '_inactive' : '')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id="po"><?if(!$in_active && !$everybody_group){?></a><?}?></TD>

				<TD><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/s_toolbar_divider.gif" WIDTH="14" HEIGHT="20" BORDER="0" ALT="" id="po"></TD>

			<?############ save as CSV button ###########?>
				<TD nowrap><a href="export2csv.php?op=users"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/filesave.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id="pt"> <?=$site->sys_sona(array(sona => "salvesta", tyyp=>"editor"))?> CSV</a></TD>
				<TD><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/s_toolbar_divider.gif" WIDTH="14" HEIGHT="20" BORDER="0" ALT="" id="po"></TD>
				
			<?############ print button ###########?>
			<!--
				<TD><a href="#"><IMG 
				SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/fileprint.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id="po"></a></TD>
			-->

			<?############ lock button : only for users ###########?>

			<TD><?if(!$in_active && $site->fdat['user_id']){?><a href="javascript:void(openpopup('<?=$site->CONF['wwwroot'].$site->CONF['adm_path']?>/edit_user.php?user_id=<?=$site->fdat['user_id']?>&op=lock','lock','413','108'))"><?}?><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/<?=(!$site->fdat['user_id'] || $user->all['is_locked']?'lock':'unlock')?><?=($in_active || !$site->fdat['user_id'] ? "_inactive" :'')?>.png" alt="<?=($user->all['is_locked']?'Unlock':'Lock')?>" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id="po"><?if(!$in_active && $site->fdat['user_id']){?></a><?}?></TD>

			<?############ view ###########?>

				<?
					/*
					 * Check if is favorite 
					*/
					if($site->fdat['user_id']) {
						$is_favorite = $site->user->is_favorite(array(
							user_id => $site->fdat['user_id'],
						));
					} else {
						$is_favorite = $site->user->is_favorite(array(
							group_id => $site->fdat['group_id'],
						));
					}
				?>
				<TD><a href="<?= $site->self ?>?user_id=<?= $site->fdat['user_id'] ?>&group_id=<?= $site->fdat['group_id'] ?>&bookmark=1" class="scms_button_img"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/bookmark<?= ($is_favorite?'':'_inactive') ?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align=absmiddle></a></TD>
				<td width="100%">&nbsp;</td>
				<td><?=$site->sys_sona(array(sona=>'Role', tyyp=>'kasutaja'))?>:&nbsp;</td>
				<td style="padding-right: 10px;">
						<!-- Role filter -->
						<?############### ROLE selectbox 
						$sqltmp = $site->db->prepare("SELECT * FROM roles ORDER BY name");
						$sthtmp = new SQL($sqltmp);				
						?>
						<SELECT NAME="tmp_flt_role" class="scms_flex_input" style="width:160px" onchange="javascript:document.getElementById('searchbox').value='';document.getElementById('searchform_flt_role').value=this.options[this.selectedIndex].value;document.searchform.submit();">
						<option value=""> -- <?=$site->sys_sona(array(sona => "vali", tyyp=>"admin"))?> -- </option>
						<?	while($role = $sthtmp->fetch() ){ ?>
							<option value="<?=$role['role_id']?>" <?=($site->fdat['flt_role']==$role['role_id']?' selected':'')?>><?=$role['name']?></option>
						<?} ?>

						</SELECT>
						<!-- //Role filter -->
				</td>
			</TR>
            </TR>
          </TABLE>
	<?
	# / FUNCTION BAR TABLE
	################################
	?>	  
		  </td>
      </tr>
  <!-- //Toolbar -->
<?
}
# / FUNCTION print_users_toolbar
##############################




##############################
# FUNCTION get_allowed_groups
/*
* get_allowed_groups
* 
* Returns array of read-allowed group ID-s for current user (logged in user or guest)
* 
* @package CMS
* 
* usage:  $read_allowed_groups = get_allowed_groups();
*/
function get_allowed_groups(){

	global $site;
	global $class_path;
	
	###########
	# PERMISSIONS CHECK - get group permissions for current user
	# load user permissions
	if ($site->user->user_id){
		if(!isset($site->user->aclpermissions)) { 
			$site->user->aclpermissions = $site->user->load_aclpermissions();
		}
		$aclpermissions = &$site->user->aclpermissions;
	} 
	elseif($site->guest) {
		if(!isset($site->guest->aclpermissions)) { 
			$site->guest->aclpermissions = $site->guest->load_aclpermissions();
		}
		$aclpermissions = &$site->guest->aclpermissions;
	}
	#echo printr($aclpermissions);

	# save read-allowed groups ID-s
	$read_allowed_groups = array();
	foreach($aclpermissions as $perm_group_id => $perm){
		if($perm['R']){ $read_allowed_groups[] = $perm_group_id; }
	}
	#echo printr($read_allowed_groups);

	return $read_allowed_groups;

}
# / FUNCTION get_allowed_groups
##############################

##############################
# FUNCTION 3.01.2008
/*
* print_search_box
* 
* Prints search table html
* 
* @package CMS
* 
* usage:  print_search_box(array(
*	'exclude_hidden_fields' => array('myotsing', 'myfield'), // default ''
*	'hide_output' => 0/1,		// default '',
*	'hide_end_form_tag' => 0/1	// default '' 
*	));
*/
function print_search_box(){

	global $site;
	global $class_path;

	if (func_num_args()>0){
		$args = func_get_arg(0);
	}



	######### SEARCH
	$search_str = $site->sys_sona(array(sona => "otsi", tyyp=>"editor"));

	####### default values
	$site->fdat['search_subtree'] = isset($site->fdat['search_subtree']) ? $site->fdat['search_subtree'] : "1";
	$site->fdat['user_search'] = isset($site->fdat['user_search']) ? $site->fdat['user_search'] : "1";
	$site->fdat['group_search'] = isset($site->fdat['group_search']) ? $site->fdat['group_search'] : "1";

	$out = '
			<!-- Search -->
						<TABLE width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor=white style="padding-left:4; padding-right:4; padding-top:2">
	  <form name="searchform" action="'.$site->self.'" method="GET">
								<TR>
									<TD width="24" nowrap><a href="javascript:void(0)" id="top6" onclick="show_menu(\'sub6\')"><IMG SRC="'.$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/menu/search.gif" BORDER="0" ALT=""><br></a>
									<!-- Dropdown -->
										<div id="sub6" z-index: 1; class="scms_dropdown_div" style="padding:0;">
											<TABLE cellpadding=0 cellspacing=0 border=0 width=100% class="scms_dropdown">
											<TR>
												<td width=32><INPUT TYPE="checkbox" NAME="tmpuser_search" id="tmpuser_search" value=1 '.($site->fdat['user_search'] ? ' checked' : '').' onclick="check_user_search(this);"></td><TD><label for="user_search">'.$site->sys_sona(array(sona => "user", tyyp=>"kasutaja")).'</label></TD>
											</TR>
											<TR>
												<td width=32><INPUT TYPE="checkbox" NAME="tmpgroup_search" id="tmpgroup_search" value=1 '.($site->fdat['group_search'] ? ' checked' : '').' onclick="check_group_search(this);"></td><TD><label for="group_search">'.$site->sys_sona(array(sona => "group", tyyp=>"kasutaja")).'</label></TD>
											</TR>
											<tr>
											<td colspan=2 class="scms_dropdown_divider">
											</tr>
											<TR>
												<td width=32><input type=checkbox id="tmpsearch_subtree" name="tmpsearch_subtree" value="1" onclick="check_search_subtree(this);"  '.($site->fdat['search_subtree']!='1' ? ' ' : ' checked').'></td><TD><label for="search_s">'.$site->sys_sona(array(sona => "search subtree", tyyp=>"otsing")).'</label></TD>
											</TR>
											</TABLE>
										</div>
									<!-- Dropdown -->
									
									</TD>
									<TD> <input name="search" id="searchbox" type="text" class="scms_flex_input" value="'.($site->fdat['search']? $site->fdat['search'] : $search_str.':').'" onFocus="if(this.value==\''.$search_str.':\') this.value=\'\';" onBlur="if(this.value==\'\')this.value=\''.$search_str.':\';"></TD>
								</TR>';
	######## hidden ########

	$out .= '
	<input type=hidden id="searchform_user_search" name="user_search" value="'.$site->fdat['user_search'].'">
	<input type=hidden id="searchform_group_search" name="group_search" value="'.$site->fdat['group_search'].'">
	<input type=hidden id="searchform_group_search" name="group_search" value="'.$site->fdat['group_search'].'">
	<input type=hidden id="searchform_flt_role" name="flt_role" value="">';
	
	$exclude_hidden_fields = array('tmpuser_search', 'tmpgroup_search', 'tmpsearch_subtree', 'user_search', 'group_search', 'flt_role', 'search_subtree', 'search');

	if (is_array($args['exclude_hidden_fields'])){
		$exclude_hidden_fields = array_merge($exclude_hidden_fields, $args['exclude_hidden_fields']);
	}

	foreach($site->fdat as $fdat_field=>$fdat_value) { 
		if(!in_array($fdat_field, $exclude_hidden_fields))	{ 
			$out .= '<input type=hidden id="searchform_'.$fdat_field.'" name="'.$fdat_field.'" value="'.$fdat_value.'">
			';
		}
	}
	if (!$args['hide_end_form_tag']){
		$out .= '</form>';
	}

	$out .= '			</TABLE>
				<!-- //Search -->
							';


		if ($args['hide_output']){
			return $out;
		} else {
			echo $out;
		}
}
# / FUNCTION print_search_box
##############################


##############################
# FUNCTION get_grouptree_html
/*
* get_grouptree_html
* 
* Returns group tree html, data is inside table
* 2 different views: tree and search result
* 
* @package CMS
* 
* usage: $tree_html = get_grouptree_html(array(
*     "where_str" => $group_where_str
* ));
*/
function get_grouptree_html(){

	global $site;
	global $class_path;

	$args = func_get_arg(0);
	$group_where_str = $args['where_str'];

	# default values (hidden fields must exist for javascript)
	$site->fdat['user_id'] = isset($site->fdat['user_id']) ? $site->fdat['user_id'] : '';

	  #####################
	  # 1. TREE: gather data 

	  # if not search result
	  if(!($site->fdat['group_search'] && 
		($site->fdat['search'] && $site->fdat['search'] != $site->sys_sona(array(sona => "otsi", tyyp=>"editor")))		  
	  )) { 

		####### SQL with permissions check: get only groups, which are read-allowed to user
  		$sql = $site->db->prepare("SELECT group_id AS id, parent_group_id AS parent, name FROM groups ");
		$sql .= " WHERE ".$group_where_str;
		$sql .= " ORDER BY name";
		#print $sql;
		$sth = new SQL($sql);
		while ($data = $sth->fetch()){
			$temp_tree[] = $data;		
		}

		############# generate tree html
		require_once($class_path.'menu.class.php');
		$menu = new Menu(array(
			expand_all  => $args['expand_all'], // expand all branches in the tree
			show_checkboxes => $args['show_checkboxes'],  // shows checkboxes near sections
			tree => $temp_tree,
			datatype => "group",
			selectform => 1,
			tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/users/group.png',
			params => ($args['is_browse'] ? "&select_one=".$site->fdat['select_one'] : ''),
			
		));
		############# make tree html
		$tree_html = $menu->source;
	}
	  #####################
	  # 2. SEARCH RESULT: plain list of groups, no tree
	else {
  		$sql = $site->db->prepare("SELECT group_id AS id, parent_group_id AS parent, name FROM groups ");
		$sql .= " WHERE ".$group_where_str;
		$sql .= $site->db->prepare(" AND name LIKE ? ORDER BY name", '%'.$site->fdat['search'].'%');
#print $sql;
		$sth = new SQL($sql);

#		$group_search_result = array();
		$tree_html = '<ul class="scms_tree_menu">';
		while ($data = $sth->fetch()){ 
#			$group_search_result[] = $data['id'];
			$href = "javascript:select_group('".$data['id']."')";
			$js_event = "ondblclick=\"javascript:void(openpopup('".$site->CONF['wwwroot'].$site->CONF['adm_path']."/edit_group.php?group_id=".$data['id']."&tab=group&op=edit','group','366','450'))\"";
			# selected group or profile 
			$span_class = ""; $span_end = ""; 
			if($data['id'] == $site->fdat['group_id']) { 
				$span_class = '<span class="scms_selected">';  $span_end = '</span>';
			}

			# Show cheboxes, if need:
			if ($args['show_checkboxes']){
				$checkbox = '<input type=checkbox onClick="sel_box_group('.$obj['id'].', this)" name="selgroup_'.$data['id'].'" id="selgroup_'.$data['id'].'">';
			} else {
				$checkbox = '';
			}		

			##### html
			$tree_html .= '<li class="scms_plain"><a href="'.$href.'" '.$js_event.'>'.$span_class.$checkbox.$data['name'].$span_end.'</a></li>';
			
		} # while groups 
		$tree_html .= '</ul>';
	} # tree or search result

	########## FINAL HTML
	$finaltree_html = '<table width="100%" height="100%"  border="0" cellpadding="0" cellspacing="0">';
	$finaltree_html .= $menu->title; # title row

	$finaltree_html .= '<tr height="100%">
					<td valign=top>';
	$finaltree_html .= $tree_html;
	$finaltree_html .= '</td>
			</tr>			
		</table>';

	return $finaltree_html;
}
# / FUNCTION get_grouptree_html
##############################



#####################
# FUNCTION get_my_group
# returns group ID (input can be group or user)
#
# $group_id = get_my_group(array("who" => 'user_id:5'));
function get_my_group() {
	global $site;
	$args = func_get_arg(0);

	# parameter is in format "user_id:22" or "group_id:14"
	list($sel_type,$sel_id) = split(":",$args['who']);

	if($sel_type == 'group_id') {
		$group_id = $sel_id;
	} # if group ID
	elseif($sel_type == 'user_id') {
		$user = new User(array(user_id => $sel_id));
		$group_id = $user->group_id;
	} # if user ID

	return $group_id;
}
# / FUNCTION get_my_group
#####################
