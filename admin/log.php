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
 * Log viewer
 *
 */

global $site;

$class_path = '../classes/';
include($class_path.'port.inc.php');
include($class_path.'adminpage.inc.php');

$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1 : 0),
	'on_admin_keel' => 1,
));

if (!$site->user->allowed_adminpage()) {
	exit;
}

// get users
$users = array();

$sql = "select distinct(sitelog.user_id), users.username, concat(users.firstname,' ',users.lastname) as fullname from sitelog left join users using (user_id)";
$result = new SQL($sql);
while($row = $result->fetch('ASSOC'))
{
	if($row['user_id'])
	{
		$users[$row['user_id']] = $row;
	}
	else 
	{
		$users[$row['user_id']] = array('user_id' => 0, 'username' => 'system', 'fullname' => 'system');
	}
}
// / get users

// dates
// begin date
if($site->fdat['date_begin'])
{
	$date_begin = htmlspecialchars($site->fdat['date_begin']);
}
else
{
	$date_begin = date('d.m.Y', time() - 60 * 60 * 24 * 7);
}

// end date
if($site->fdat['date_end'])
{
	$date_end = htmlspecialchars($site->fdat['date_end']);
}
else
{
	$date_end = date('d.m.Y', time() + 60 * 60 * 24);
}
$sql_where_date = $site->db->prepare('and date >= ? and date <= ?', $site->db->ee_MySQL($date_begin), $site->db->ee_MySQL($date_end));
// / dates

// user filter
$sql_selected_user = '';
if(isset($site->fdat['user_id']) && $site->fdat['user_id'] != 'all')
{
	$sql_selected_user = 'and user_id = '.(int)$site->fdat['user_id'];
}
// / user filter

// search
$sql_search = '';
if($site->fdat['search'])
{
	$sql_search = "and message like '%".mysql_real_escape_string($site->fdat['search'])."%'";
}
// /search

// pages
$page_items = 20;
// get totalpages
$sql = "select count(site_log_id) from sitelog where 1 $sql_selected_user $sql_where_date $sql_search";
$result = new SQL($sql);
$total_pages = ceil(($total_items = $result->fetchsingle()) / $page_items);
// current page
$page = (int)$site->fdat['page'];
if(!$page) $page = 1;
if ($total_pages && $page > $total_pages) $page = $total_pages;
// / pages

// log types
$log_types = Log::getTypeArray();

// log actions
$log_actions = Log::getActionsArray();

// selected user
// get log records
$log_records = array();
$sql = $site->db->prepare("select * from sitelog where 1 $sql_selected_user $sql_where_date $sql_search order by date desc limit ".($page - 1) * (int)$page_items.", ".(int)$page_items);
//printr($sql);
$result = new SQL($sql);
while($row = $result->fetch('ASSOC'))
{
	$log_records[] = array(
		'date' => $site->db->MySQL_ee_long($row['date']),
		'username' => $users[$row['user_id']]['username'],
		'objekt_id' => $row['objekt_id'],
		'component' => $row['component'],
		'type' => $log_types[$row['type']],
		'action' => $log_actions[$row['action']],
		'message' => $row['message'],
	);
}

$adminpage_names = get_adminpage_name(array('script_name' => $site->script_name));

?><html>
	<head> 	
		<title><?=$site->sys_sona(array('sona' => 'Log', 'tyyp' => 'Admin'));?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/default_admin_page.css" media="screen">
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/admin_menu.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/yld.js"></script>
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/datepicker.css">
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/jquery.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'] ?>/datepicker.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot']?>/common.js.php"></script>
		<script type="text/javascript">
			var isIE = navigator.appVersion.match(/MSIE/); // assume gecko on false
			
			function contentDimController(elem_id)
			{
				elem = document.getElementById(elem_id);
			    elem.style.display = 'none';
			    elem.style.height = elem.parentNode.offsetHeight + 'px';
			    elem.style.display = 'block';
			}
			
			window.onload = function()
			{
				contentDimController('s_Content_container');
				make_breadcrumb('<?=$adminpage_names['parent_pagename'];?>','<?=$adminpage_names['pagename'];?>');

			}
			
			window.onresize = function()
			{
				contentDimController('s_Content_container');
			}
			
			function nextPage()
			{
				var form = document.toolbar_form;
				form.page.value++;
				form.submit();
			}
			
			function previousPage()
			{
				var form = document.toolbar_form;
				if(form.page.value > 1)
				{
					form.page.value--;
					form.submit();
				}
			}


		</script>
	</head>

	<body>
	    <table cellpadding="0" cellpadding="0" class="s_Body_container">
	        <tr>
	            <td class="s_Header_container">
	        		<form name="toolbar_form" id="toolbar_form" method="POST" action="<?=$_SERVER['PHP_SELF'];?>">
		            	<div class="s_Toolbar_container">
		            		<div class="s_Toolbar_content">
			            		<ul class="s_Buttons_container">
			            			<li><a href="#" onclick="javascript:void(avaaken('delete_log.php','366','450','log'));" id="button_delete" class="button_delete"><?=$site->sys_sona(array('sona' => 'kustuta' , 'tyyp' => 'editor'));?></a></li>
			            			<li><a href="#" onclick="document.toolbar_form.submit();" id="button_refresh" class="button_refresh"><?=$site->sys_sona(array('sona' => 'refresh' , 'tyyp' => 'admin'));?></a></li>
			            		</ul>
				    			<table cellpadding="0" cellspacing="0" align="right">
				    				<tr>
				    					<td>
						            		<ul class="s_Buttons_container">
							           			<li><span><?=$site->sys_sona(array('sona' => 'otsi', 'tyyp' => 'editor'));?>: <input type="text" name="search" class="text" value="<?=htmlspecialchars($site->fdat['search']);?>" onkeypress="if(event.keyCode == 13) { this.form.page.value = 1; this.form.submit(); }"></span></li>
							           			<li><span><input type="text" id="date_begin" name="date_begin" value="<?=$date_begin;?>" onkeypress="if(event.keyCode == 13) { this.form.submit(); }" class="text_date"></span></li>
												<li><a href="#" onclick="init_datepicker('date_begin','date_begin','date_end')" class="button_calendar"></a></li>
							           			<li><span><input type="text" id="date_end" name="date_end" value="<?=$date_end;?>" onkeypress="if(event.keyCode == 13) { this.form.submit(); }" class="text_date"></span></li>
												<li><a href="#" onclick="init_datepicker('date_end','date_begin','date_end')" class="button_calendar"></a></li>
							           			<li><span><select name="user_id" onchange="this.form.page.value = 1; this.form.submit();">
							           					<option value="all">- <?=$site->sys_sona(array('sona' => 'koik', 'tyyp' => 'editor'));?> -</option>
							           					<?php foreach ($users as $user) { ?>
							           					<option value="<?=$user['user_id'];?>"<?=($site->fdat['user_id'] != '' && $site->fdat['user_id'] != 'all' && $user['user_id'] == $site->fdat['user_id'] ? ' selected="selected"' : '');?>><?=$user['fullname'];?></option>
							           					<?php } ?>
							           				</select></span></li>
						            		</ul>
		            					</td>
		            				</tr>
		            			</table>
		            		</div><!-- s_Toolbar_content -->
		            	</div><!-- s_Toolbar_container -->
		            	<div class="s_Page_title_bar">
	            			<table cellpadding="0" cellspacing="0" align="right">
	            				<tr>
	            					<td>
					            		<ul class="s_Buttons_container">
					            			<li><span><strong><?=$site->sys_sona(array('sona' => 'found', 'tyyp' => 'Admin'));?> <?=$total_items;?></strong></span></li>
			    				   			<li><a href="#" onclick="previousPage();" class="button_left"></a></li>
					            			<li><span><input type="text" id="results_page" name="page" value="<?=$page;?>" onkeypress="if(event.keyCode == 13) { this.form.submit(); }" class="text_small"></span></li>
					            			<li><span>/</span></li>
					            			<li><span><strong><?=$total_pages;?></strong></span></li>
			    				   			<li><a href="#" onclick="nextPage();" class="button_right"></a></li>
					            		</ul>
	            					</td>
	            				</tr>
	            			</table>
							<table cellpadding="0" cellspacing="0">
								<tr>
									<td class="icon" width="16" style="padding-right: 3px;"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/history.png" width="16" height="16"></td>
									<td class="title"><?=$site->sys_sona(array('sona' => 'Log', 'tyyp' => 'Admin'));?></td>
								</tr>
							</table>
	            		</div><!-- s_Page_title_bar -->
		    		</form>
	            </td>
	        </tr>
	        <tr>
	            <td class="s_Page_container">
	                <div id="s_Content_container">
						<table cellpadding="0" cellspacing="0" class="data_table" width="100%">
						<!-- table header -->
						<thead>
							<tr>
								<td><?=$site->sys_sona(array('sona' => 'Aeg', 'tyyp' => 'editor'));?></td>
								<td><?=$site->sys_sona(array('sona' => 'Autor', 'tyyp' => 'editor'));?></td>
								<td><?=$site->sys_sona(array('sona' => 'actions', 'tyyp' => 'admin'));?></td>
								<td><?=$site->sys_sona(array('sona' => 'Objekt', 'tyyp' => 'editor'));?> ID</td>
							</tr>
						</thead>
						
						<!-- table content -->
						<tbody>
						<?php foreach ($log_records as $log_record) { //printr($log_record); ?>
							<tr <?=($log_record['type'] == 'ERROR' ? 'class="red"' : '');?>>
								<td><?=$log_record['date'];?></td>
								<td><?=($log_record['username'] ? $log_record['username'] : '&nbsp;');?></td>
								<td><?=$log_record['message'];?></td>
								<td><?=($log_record['objekt_id'] ? $log_record['objekt_id'] : '&nbsp;');?></td>
							</tr>
						<?php } ?>
						</tbody>
						
						</table><!-- /data_table -->
	                </div><!-- /s_Content_container -->
	            </td>
	        </tr>
	        <tr>
	            <td class="s_Footer_container">
	            </td>
	        </tr>
	    </table>
	</body>
</html>