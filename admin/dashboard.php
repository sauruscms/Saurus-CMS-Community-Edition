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
 * dashboard, opens as main page of the admin section
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

$menu_list = admin_menu_list();

//$adminpage_names = get_adminpage_name(array('script_name' => $site->script_name));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head> 	
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
		<title><?=$site->sys_sona(array('sona' => 'dashboard', 'tyyp' => 'Admin'));?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/default_admin_page.css" media="screen">
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/admin_menu.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/ie_position_fix.js"></script>
		<script type="text/javascript">
			window.onload = function()
			{
				//make_breadcrumb('<?=$adminpage_names['parent_pagename']?>', '<?=$adminpage_names['pagename']?>');
				make_breadcrumb('Dashboard', 'Dashboard');
				new ContentBox('dashboard_area', '10px', '0px', '10px', '0px');
			}
			
			var ContentBox = function(id, top, right, bottom, left)
			{
				this.box = document.getElementById(id);
				this.box.style.top = top;
				this.box.style.right = right;
				this.box.style.bottom = bottom;
				this.box.style.left = left;
			}
			
		</script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/yld.js"></script>
		<style type="text/css">
			div#dashboard_container {
				margin: 20px;
				font-family: "Tahoma";
				font-size: 13px;
			}
			
			a:link, a:visited, a:active {
				font-family: "Tahoma";
				font-size: 13px;
				color: #637FB1;
			}
			
			a:hover {
				color: #637FB1;
				text-decoration: underline; 
			}
			
			div#info_container h1 {
				font-size: 18px;
			}
			
			table.menu_items td {
				vertical-align: top;
				padding: 10px 10px 10px 0px;
				width: 150px;
			}
			
			table.menu_items h1 {
				font-size: 14px;
			}
			
			table.menu_items ul {
				margin: 0px;
				padding: 0px;
				list-style-type: none;
			}
			
			div.clearboth {
				clear: both;
			}
		</style>
	</head>
	<body>
		<div id="mainContainer">
		
			<div class="contentArea" id="dashboard_area">
				<div class="contentAreaContent">
					<div id="dashboard_container">
						<div id="info_container">
							<h1><?=$site->user->all['username'];?> (<?=$site->user->all['firstname'].' '.$site->user->all['lastname'];?>)</h1>
							<p>Last login: <?=$site->db->MySQL_ee($site->user->all['last_access_time']);?>, IP: <?=$site->user->all['last_ip'];?></p>
						</div>
						
						<table cellpadding="0" cellspacing="0" class="menu_items">
							<tr>
							<?php $i = 0; foreach($menu_list as $top_menu) { $i++; ?>
								<td>
									<h1><?=$top_menu['translated_name']?></h1>
									<ul>
									<?php foreach($top_menu['submenus'] as $menu_item) { ?>
										<li><a href="<?=$menu_item['fail']?>"><?=$menu_item['translated_name']?></a></li>
									<?php } ?>
									</ul>
								</td>
								<?php if(($i % 4) == 0) { ?>
									</tr><tr>
								<?php } ?>
							<?php } ?>
							</tr>
						</table>
					</div><!-- / dashboard_container -->
				</div><!-- / contentAreaContent -->
			</div><!-- / contentArea -->
			
		</div><!-- / mainContainer -->
	</body>
</html>
