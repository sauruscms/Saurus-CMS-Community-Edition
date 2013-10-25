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
 * Page for managing profile data of assets
 * 
 * Page is divided into 2 parts:
 * LEFT: profile types
 * RIGHT: Allows add, modify, delete, etc all profile instances
 * 
 * @param int $profile_id selected profile ID
 * @param int $objekt_id selected asset ID
 * @param string $profile_search profile search string
 * @param string $data_search user search string
 * 
 */


$class_path = "../classes/";
include_once($class_path."port.inc.php");
include_once($class_path."adminpage.inc.php");

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

#temporary:
error_reporting(7);

$site->fdat['profile_id'] = (int)$site->fdat['profile_id'];

#################
# GET profile INFO
if($site->fdat['profile_id']) {
	$site->fdat['profile_id'] = (int)$site->fdat['profile_id'];
	$profile_def = $site->get_profile(array(id=>$site->fdat['profile_id'])); 
	$breadcrumb_focus_str = ",'".$site->sys_sona(array(sona => $profile_def['name'], tyyp=>"custom"))."'";

	# sanity check: kui ei leitud sellise nimega profiili, anda toimetajale veateade ja v�ljuda:
	if(!$profile_def['profile_id']) {
		if($site->in_admin && $site->fdat['profile_id']) {
			print "<font color=red><b>Profile '".$site->fdat['profile_id']."' not found!</b></font>";
		}
		exit;
	}

	######### EXTERNAL TABLE ? 
	if(substr($profile_def['source_table'],0,4) == 'ext_'){
		$external_table = $profile_def['source_table'];
	}

}

#################
# GET objekt INFO
if($site->fdat['objekt_id']) {
	$objekt = new Objekt(array(
		objekt_id => $site->fdat['objekt_id'],
	));
}
# get parent for new button
if($objekt->parent_id) { $parent_id = $objekt->parent_id; }
else { $parent_id = $site->alias("system");}

?>
<html>
<head>
<title><?=$site->title?> <?= $site->cms_version ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding ?>">
<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF[styles_path]?>/scms_general.css">
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF['wwwroot'].$site->CONF[js_path]?>/yld.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript" SRC="<?=$site->CONF[wwwroot].$site->CONF[js_path]?>/admin_menu.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
<!--
	make_breadcrumb('<?= $parent_pagename ?>','<?= $pagename ?>' <?=$breadcrumb_focus_str?>);
//-->
</SCRIPT>
<?php print_context_button_init(); ?>
</head>

<body style="overflow-y: auto; overflow-x: auto;">

<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
<?php 
################################
# FUNCTION BAR
?>
<!-- Toolbar -->
<TR>
<TD class="scms_toolbar">

	<?php ######### PROFILE FUNCTION BAR ############?>
      <table border="0" cellpadding="0" cellspacing="0">
        <tr> 
		  <?php ############ new profile instance button ###########?>
			<?php 
			if( $profile_def['source_table'] == 'obj_asset'){ # ASSET
				$href = $site->CONF['wwwroot'].$site->CONF['adm_path']."/edit.php?op=new&keel=".$site->keel."&parent_id=".$parent_id."&tyyp_idlist=20&profile_id=".$site->fdat['profile_id'];
			}
			elseif($external_table){
				$href = $site->CONF['wwwroot'].$site->CONF['adm_path']."/edit_table.php?op=new&profile_id=".$site->fdat['profile_id']."&external_table=".$external_table;
			
			}	
			?>
            <td nowrap><?php if($site->fdat['profile_id']){?><a href="javascript:avaaken('<?=$href?>', 450, 430);"><?php }?><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/filenew<?=(!$site->fdat['profile_id'] ? '_inactive' : '')?>.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" id=pt></td>

		<?php ###### wide middle cell ######?>
		<td width="100%"></td>
		  
        </tr>
      </table>
</TD>
</TR>

<?php 
# / FUNCTION BAR
################################
?>
  <!-- //Toolbar -->
  <!-- Content area -->

  <tr valign="top"> 
<?php 
############################
# PROFILE TYPES MENU
?>
<td >
	<!-- content table -->	
	<TABLE class="scms_content_area" border=0 cellspacing=0 cellpadding=0>
	<TR>
		<!-- Left column -->
		<TD class="scms_left">

			<div id=navigation class="scms_left_div">
				<table style="width:100%;height:100%"  border="0" cellpadding="0" cellspacing="0">
			<!-- Search -->
					<tr>
						<td valign=top>
			<?php 
			#################
			# SEARCH BOX
			?>
			<?php $search_str = $site->sys_sona(array(sona => "otsi", tyyp=>"editor")); ?>
						<TABLE width="20%" border="0" cellpadding="0" cellspacing="0" bgcolor=white style="padding-left:4; padding-right:4; padding-top:2">
	  <form name="datasearchform" action="<?=$site->self?>" method="GET">
								<TR>
									<TD width="24" nowrap><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/menu/search.gif" BORDER="0" ALT="">

									</TD>
									<TD><input name="data_search" type="text" class="scms_flex_input" value="<?=$site->fdat['data_search']? $site->fdat['data_search'] : $search_str.':'?>" onFocus="if(this.value=='<?=$search_str?>:') this.value='';" onBlur="if(this.value=='')this.value='<?=$search_str?>:';" style="width:140px"></TD>
									<?php ###### wide middle cell ######?>
									<td width="100%"></td>

								</TR>
		<?php ######## hidden ########?>
		<input type=hidden name=profile_search value="<?=$site->fdat['profile_search']?>">
		<input type=hidden name=profile_id value="<?=$site->fdat['profile_id']?>">
		</form>
						</TABLE>
			<!-- //Search -->
			<br />
			</td>
			</tr>
	
			<!-- Menu tree -->

					<!-- I grupp -->
					<tr>
						<td valign=top>
	<?php 
	  #####################
	  # TREE
		require_once($class_path.'menu.class.php');

		######## CUSTOM OBJECT TREE
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		'obj_asset');
		$sth = new SQL($sql);
		$temp_tree = array();
		while ($data = $sth->fetch()){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			$temp_tree[] = $data;		
		}
		$menu = new Menu(array(
			width=> "100%",
			tree => $temp_tree,
			datatype => "profile",
			tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/mime/asset.png',
			tree_title => '<a href="'.$site->self.'?source_table=obj_asset">'.$site->sys_sona(array(sona => "asset", tyyp=>"editor")).'</a>'
		));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr >
						<td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;
?>
						</td>
					</tr>
					<!-- //III grupp -->
					<!-- IV grupp -->
					<tr>
						<td valign=top>
<?php 

		######## FILEMANAGER TREE
  		$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
		'obj_file');
		$sth = new SQL($sql);
		$temp_tree = array();
		while ($data = $sth->fetch()){
			### change technical profile name to translation in current language:
			$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
			$temp_tree[] = $data;		
		}
		$menu = new Menu(array(
			width=> "100%",
			tree => $temp_tree,
			datatype => "profile",
			tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/mime/folder_open.png',
			tree_title => '<a href="'.$site->self.'?source_table=obj_file">'.$site->sys_sona(array(sona => "file manager", tyyp=>"admin")).'</a>'
		));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr >
						<td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;
?>
						</td>
					</tr>
					<!-- //III grupp -->
					<!-- IV grupp -->
					<tr height=100%>
						<td valign=top>

<?php 
		########### EXTERNAL TABLES TREES
		$sql = $site->db->prepare("show tables");
		$sth = new SQL($sql);
		while ($tbl_data = $sth->fetchsingle()){
			$tables[] = $tbl_data;
		}
#printr($tables);

		$ext_tables = array();
		foreach($tables as $table){
			# add table name to array if this has right external prefix
			if(substr($table,0,4)=='ext_'){
				$ext_tables[] = $table;
			} # if correct prefix
		}
		##### loop over external tables
		foreach($ext_tables as $ext_table) {
			############ PRINT TREE
			$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",
			$ext_table);
			$sth = new SQL($sql);
			$temp_tree = array();
			while ($data = $sth->fetch()){
				### change technical profile name to translation in current language:
				$data['name'] = $site->sys_sona(array(sona => $data['name'], tyyp=>"custom"));
				$temp_tree[] = $data;		
			}
			$menu = new Menu(array(
				width=> "100%",
				tree => $temp_tree,
				datatype => "profile",
				tree_icon => $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/icons/16x16/mime/object.png',
				tree_title => '<a href="'.$site->self.'?source_table='.$ext_table.'">'.substr($ext_table,4).'</a>'
			));
		############# make tree html
		$tree_html = $menu->source;

		$finaltree_html = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		$finaltree_html .= $menu->title; # title row
		$finaltree_html .= '<tr >
						<td valign=top>';
		$finaltree_html .= $tree_html;
		$finaltree_html .= '</td>
				</tr>			
			</table>';
		# print tree
		echo $finaltree_html;
		}
		##### / loop over external tables
	  ?>          

						</td>
					</tr>
					<!-- //IV grupp -->
							
				</table>


</DIV>
</TD>

<?php 
# / PROFILE TYPES MENU
############################
?>

<?php 
############################
# MIDDLE LIST

#############
# if profile selected
if($site->fdat['profile_id']) {

	########### FROM
	if( $profile_def['source_table'] == 'obj_asset'){
		$from_sql = " FROM obj_asset LEFT JOIN objekt on objekt.objekt_id=obj_asset.objekt_id LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id ";
	}
	elseif( $profile_def['source_table'] == 'obj_file'){
		$from_sql = " FROM obj_file LEFT JOIN objekt on objekt.objekt_id=obj_file.objekt_id LEFT JOIN objekt_objekt on objekt.objekt_id=objekt_objekt.objekt_id ";
	}
	elseif($external_table){
		$from_sql = " FROM ".$external_table;
	}

	############# get fields
	$profile_data = $profile_def['data'];
	$profile_data = unserialize($profile_data);	# profile_data is now array of ALL fields, indexes are table fieldnames
?>
		<!-- Middle column -->
		<TD class="scms_middle_dialogwindow">

<?php ######  pages table ######?>

				<table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr class="scms_pane_header"> 
                     <td nowrap>			
					  <IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/mime/files.png" WIDTH="16" HEIGHT="16" BORDER="0" ALT="" align="absmiddle">&nbsp; <?=$site->sys_sona(array(sona => "profile_data", tyyp=>"admin"))?>
					  </td>
	<?php 
	#################
	# WHERE

	if( $profile_def['source_table'] == 'obj_asset'){
		# assets: exclude object in trash 
		$where_sql[] = $site->db->prepare("objekt_objekt.parent_id<>?",$site->alias("trash"));
	}

	# profile filter
	if($site->fdat['profile_id']) {
		$where_sql[] = $site->db->prepare("profile_id=?",$site->fdat['profile_id']);
	}
	# data search filter, search from all possible fields
	if($site->fdat['data_search']) {
		foreach($profile_data as $field => $value) { 
			$whereparts_sql[] = $site->db->prepare(" ".$field." LIKE ? ",							$site->fdat['data_search'].'%'
			);
		}
		# assets 6 files: searsh from title also
		if( $profile_def['source_table'] == 'obj_asset' || $profile_def['source_table'] == 'obj_file'){
			$whereparts_sql[] = $site->db->prepare(" objekt.pealkiri LIKE ? ",									$site->fdat['data_search'].'%'
			);
		} # assets
		$where_sql[] = "(". join(" OR ",$whereparts_sql). ") ";

	}
	$where = is_array($where_sql) ? " WHERE ".join(" AND ",$where_sql) : '';

	# / obj_asset WHERE
	#################


	#################
	# pagenumbers 
	$sql = "SELECT COUNT(*) ".$from_sql.$where;
	$sth = new SQL($sql);
	$total_count = $sth->fetchsingle();
	?>
	<td>
	<?php ######### print pagenumbers table
	$pagenumbers = print_pagenumbers(array(
		"total_count" => $total_count,
		"rows_count" => 20,
	));
	?>
	</td>
	<?php 
	# / pagenumbers 
	#################
	?>		
					
					</tr>
                 </table>
<?php ######  // pages table ######?>
		
			<table style="width:100%;height:100%" border="0" cellspacing="0" cellpadding="0">
		   <!-- Table header -->	
			  <tr height=10> 
                <td valign="top" class="scms_tableheader">



	<?php 
	#################
	# table column names

	# set sort base link, viska vana parameeter lingist v�lja:
	$sort_baselink = $site->URI;
	$sort_baselink = preg_replace("/\&sortby=(\w+)/i","",$sort_baselink); # field to sort by
	$sort_baselink = preg_replace("/\&sort=(\w+)/i","",$sort_baselink); # sort direction: desc/asc
	$sort_baselink = preg_replace("/\?sortby=(\w+)/i","?",$sort_baselink); # field to sort by
	$sort_baselink = preg_replace("/\?sort=(\w+)/i","?",$sort_baselink); # sort direction: desc/asc
	# add & or ? to the end of URL if not found:
	$sort_baselink = $sort_baselink.(substr($sort_baselink,-1)!='&' && substr($sort_baselink,-1)!='?'?($_SERVER["QUERY_STRING"]?"&":"?"):'');

	# if no sorting set - sort by sorteering
	if(!$site->fdat['sortby']) {
		if( $profile_def['source_table'] == 'obj_asset'){ # assets
			$site->fdat['sortby'] = "objekt_objekt.sorteering";
			$site->fdat['sort'] = 'desc';
		}
	}
	$site->fdat['sort'] = $site->fdat['sort'] == 'asc' ? 'asc': 'desc';
	##### td width: calculate percents
	if( is_array($profile_data) && sizeof(array_keys($profile_data)) > 0 ) {
		$td_width = intval((100/(sizeof(array_keys($profile_data))+1))).'%';
	}
	else {$td_width = '100%';}
	?>


		<table width="100%"  border="0" cellspacing="0" cellpadding="3">
		   <tr id="headerrow">

			<?php ########## asset name ?>
			<?php if( $profile_def['source_table'] == 'obj_asset'){ ?>
			<?php $field= "pealkiri"; 
			$href = $sort_baselink.'sort='.($site->fdat['sortby']==$field && $site->fdat['sort']=='asc'?'desc':'asc').'&sortby='.$field;
			?>
			<td  width="<?=$td_width?>" nowrap  onClick="document.location='<?=$href?>'" <?=($site->fdat['sortby']==$field ? 'class="scms_tableheader_active"' : '')?>><a href="<?=$href?>"><?=$site->sys_sona(array(sona => "name", tyyp=> "admin"))?></a></td>
			<?php } # asset 'pealkiri' cell?>

			<?php 
			if (is_array($profile_data)) { # if is array

			#########################
			# loop over fields
			foreach($profile_data as $field => $value) { 
				# if field is active
				if( $value['is_active'] ) {
				$href = $sort_baselink.'sort='.($site->fdat['sortby']==$field && $site->fdat['sort']=='asc'?'desc':'asc').'&sortby='.$field;
					
			?>
				<td width="<?=$td_width?>" onClick="document.location='<?=$href?>'" <?=($site->fdat['sortby']==$field ? 'class="scms_tableheader_active"' : '')?>><a href="<?=$href?>"><?=$site->sys_sona(array(sona => $value['name'], tyyp=> "custom"))?></a></td>
			<?php 
				} # if field is active
			} # foreach 
			# / loop over asset fields
			#########################
			}  # if is array
			?>
						  <td width="16" align="right"><IMG SRC="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/general/px.gif" WIDTH="16" HEIGHT="1" BORDER="0" ALT=""></td>

		</tr></table>

				</td>
			</tr>
			<!-- // Table header -->

	<?php 
	# / COLUMN NAMES
	#################
	?>
			<tr>
				<td valign=top >
					<!-- Scrollable area -->
					<div id=listing class="scms_middle_div">

				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="scms_table">

<?php 
	#################
	# LIST

	########### ORDER
	if($site->fdat['sortby']){
		$order = " ORDER BY ".mysql_real_escape_string($site->fdat['sortby'])." ".$site->fdat['sort'];
	}

	########### SQL
	unset($sql);
	if( $profile_def['source_table'] == 'obj_asset'){ # assets
	 	$sql = "SELECT obj_asset.*, objekt.pealkiri ";
	}
	elseif($external_table){
	 	$sql = "SELECT * ";
	}
	if($sql){
		$sql .= $from_sql;
		$sql .= $where;
		$sql .= $order;
		$sql .= $pagenumbers['limit_sql'];
		$sth = new SQL($sql);
	}
#print $sql;

	if($sth->rows){
		#################
		# loop over obj_asset
		while($asset = $sth->fetch()){
			$baselink = $site->URI;
			$baselink = preg_replace("/\&objekt_id=(\d+)/i","",$baselink); 
			##### href & is_active
			if( $profile_def['source_table'] == 'obj_asset'){
				###### create object, to get buttons
				$objekt = new Objekt (array(
					"objekt_id" => $asset['objekt_id'],
				));
				$buttons = $objekt->get_edit_buttons(array(
					"tyyp_idlist" => $objekt->all[tyyp_id],
					"profile_id" => $site->fdat['profile_id']
				));

				$href =  "javascript:document.location='".$baselink."&objekt_id=".$asset['objekt_id']."'; avaaken('".$site->CONF['wwwroot'].$site->CONF['adm_path']."/edit.php?op=edit&id=".$asset['objekt_id']."', 450, 430);";
				
				$is_active = $site->fdat['objekt_id'] == $asset['objekt_id']? 1 : 0;

				$delete_href = $site->CONF['wwwroot'].$site->CONF['adm_path']."/delete.php?id=".$asset['objekt_id']."&parent_id=".$parent_id;
			}
			elseif($external_table){
				$href =  "javascript:document.location='".$baselink."&id=".$asset['id']."'; avaaken('".$site->CONF['wwwroot'].$site->CONF['adm_path']."/edit_table.php?tab=edit&op=edit&external_table=".$external_table."&id=".$asset['id']."&profile_id=".$asset['profile_id']."', 450, 430);";
				$is_active = $site->fdat['id'] == $asset['id']? 1 : 0;

				$delete_href = $site->CONF['wwwroot'].$site->CONF['adm_path']."/edit_table.php?op=delete&external_table=".$external_table."&id=".$asset['id'];
			}
		?>
          <tr <?=($is_active ? ' class="scms_activerow"' : '')?>>
			<?php ########## asset name ?>
			<?php if( $profile_def['source_table'] == 'obj_asset'){ ?>
			<td  width="<?=$td_width?>" class="scms_table_row" nowrap><a href="<?=$href?>"><?=($asset['pealkiri'] ? $asset['pealkiri'] : '&nbsp;')?><?=$buttons?></a></td>
			<?php } # asset 'pealkiri' cell?>
			<?php 
			if (is_array($profile_data)) { # if is array

			######### get field values in right format
			$formatted_values = format_profile_values(array(
				"profile_data" => &$profile_data,
				"data" => &$asset,
			));

			#########################
			# loop over asset fields
			foreach($profile_data as $field => $value) { 
				# if field is active
				if( $value['is_active'] ) {				

					########## field value
					$field_value = $formatted_values[$field];
					if (strlen($field_value)>50) { #  strip if necessary
						$field_value = substr($field_value,0,50)."...";
					}
				# Bug #2567: FILE v�li ajab html-i katki
			?>
				<td  width="<?=$td_width?>" class="scms_table_row"><a href="<?=$href?>"><?=( $field_value ? htmlspecialchars($field_value) : '&nbsp;') ?></a></td>
			<?php 
				} # field active
			} # foreach 
			}  # if is array
			?>

			<?php ##### delete (dont show for assets, they have action-button already)######?>
			<?php if( $profile_def['source_table'] != 'obj_asset'){ ?>
			<td  width="16" align="right"><a href="javascript:void(openpopup('<?=$delete_href?>','delete','413','108'));"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/delete.png" width="16" height="16"  border=0></a></td>
			<?php }?>
          </tr>
		<?php 
		}
		# / loop over obj_asset
		#################
	}
		?>
              </table>
            </div>
		<!-- //Scrollable area -->

	</td>
     </tr>
	
	</table>
	
	
</TD>
<?php 
}
# /if profile selected
#############
?>


<?php 
# / MIDDLE LIST
############################
?>
	</TR>
	</TABLE>
	<!-- content table -->	





</body>

</html>
