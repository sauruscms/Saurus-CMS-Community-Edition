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



global $class_path;

$class_path = '../classes/';

include($class_path.'port.inc.php');
include($class_path.'adminpage.inc.php');
include($class_path.'explorerHelpers.classes.php');

$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1 : 0),
	'on_admin_keel' => 1,
));

$site->user->adminpermissions = $site->user->load_adminpermissions();			

if (!$site->user->allowed_adminpage()) exit;

$languages = array(array('nimi' => '', 'keel_id' => 'all', ));
$sql = 'select nimi, keel_id from keel where on_kasutusel = 1 order by nimi;';
$result = new SQL($sql);
while ($row = $result->fetch('ASSOC')) { $languages[] = $row; }

$mails_table = array();

$sql = 'select mail, objekt_id_list from allowed_mails;';
$result = new SQL($sql);
while ($row = $result->fetch('ASSOC'))
{
	if($row['objekt_id_list']) $row['objekt_id_list'] = explode(',', $row['objekt_id_list']);
	else $row['objekt_id_list'] = array();

	foreach($row['objekt_id_list'] as $i => $article_id)
	{
		$row['objekt_id_list'][$i] = ereg_replace('_(.*)$', '', $article_id);
	}
	
	if($row['mail']) $row['mail'] = explode(',', $row['mail']);
	else $row['mail'] = array();

	foreach($row['mail'] as $i => $mail)
	{
		$row['mail'][$i] = trim($mail);
	}
	
	$mails_table[] = $row;
}

$articles = array();
foreach($mails_table as $row)
{
	foreach($row['objekt_id_list'] as $article_id)
	{
		if(!in_array($article_id, array_keys($articles))) $articles[$article_id]['emails'] = array();
		foreach ($row['mail'] as $email)
		{
			if(!in_array($email, $articles[$article_id]['emails'])) $articles[$article_id]['emails'][] = $email;
		}
	}
}

$object_parent_array = array();
$sql = 'select objekt_id, parent_id from objekt_objekt;';
$result = new SQL($sql);
while($row =  $result->fetch('ASSOC'))
{
	$object_parent_array[] = $row;
}

$objArray = new ObjectParentArray($object_parent_array);

$parent_ids = array();
foreach($articles as $article_id => $article)
{
	$parent_id = $objArray->find_parent((string)$article_id);
	while($parent_id)
	{
		$articles[$article_id]['parents'][$parent_id] = array();
		if(!in_array($parent_id, $parent_ids)) $parent_ids[] = $parent_id;
		$parent_id = $objArray->find_parent($parent_id);
	}
}

$parents = array();
$select_ids = array_merge(array_keys($articles), $parent_ids);

if($select_ids)
{
	$sql = 'select objekt_id, pealkiri, keel from objekt where objekt_id in ('.implode(',', $select_ids).');';
	$result = new SQL($sql);
	while($row = $result->fetch('ASSOC'))
	{
		if(in_array($row['objekt_id'], array_keys($articles)))
		{
			$articles[$row['objekt_id']]['pealkiri'] = $row['pealkiri'];
			$articles[$row['objekt_id']]['keel'] = $row['keel'];
		}
		else $parents[$row['objekt_id']] = $row['pealkiri'];
	}
}

foreach($articles as $article_id => $article)
{
	if(!isset($article['pealkiri']))
	{
		unset($articles[$article_id]);
		continue;
	}
	foreach($article['parents'] as $parent_id => $parent)
	{
		$articles[$article_id]['parents'][$parent_id] = $parents[$parent_id];
	}
	
	if(isset($_POST['lang']) && $_POST['lang'] != 'all')
	{
		if($article['keel'] != $_POST['lang'])
		{
			unset($articles[$article_id]);
			continue;
		}
	}
	
	if($_POST['search'])
	{
		if(strpos(strtolower($article['pealkiri']), strtolower($_POST['search'])) !== false || strpos(strtolower(current($articles[$article_id]['parents'])), strtolower($_POST['search'])) !== false || strpos(strtolower(implode(' ', $article['emails'])), strtolower($_POST['search'])) !== false) {}
		else 
		{
			unset($articles[$article_id]);
			continue;
		}
	}
	
	$articles[$article_id]['parents'] = array_reverse($articles[$article_id]['parents'], true);
}
$adminpage_names = get_adminpage_name(array('script_name' => $site->script_name));


?>
<html>
	<head> 	
		<title><?=$site->sys_sona(array('sona' => 'feedbackforms_properties', 'tyyp' => 'Admin'));?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/default_admin_page.css" media="screen">
		<!-- custom css <link rel="stylesheet" href="../styles/screen.css" media="screen">-->
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/admin_menu.js"></script>
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
		</script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/yld.js"></script>
		<script type="text/javascript">
			function submitFilters()
			{
				var form = document.getElementById('filter_form');
				form.submit();
			}
			
			function clearFilters()
			{
				var form = document.getElementById('filter_form');
				form.lang.value = 'all';
				form.search.value = '';
				form.submit();
			}
		</script>
	</head>

	<body>
	    <table cellpadding="0" cellpadding="0" class="s_Body_container">
	        <tr>
	            <td class="s_Header_container">
	            	<div class="s_Toolbar_container">
	            		<div class="s_Toolbar_content">
		            		<form name="filter_form" id="filter_form" method="POST" action="<?=$_SERVER['PHP_SELF'];?>">
		            			<table cellpadding="0" cellspacing="0" align="right">
		            				<tr>
		            					<td>
						            		<ul class="s_Buttons_container">
						            			<li><span><?=$site->sys_sona(array('sona' => 'search', 'tyyp' => 'saurus4'));?>: <input type="text" name="search" value="<?=htmlspecialchars($_POST['search']);?>" class="text"></span></li>
						            			<li><span><?=$site->sys_sona(array('sona' => 'Language', 'tyyp' => 'Admin'));?>:  <select name="lang" class="select" onchange="this.form.submit();">
						            				<?php foreach($languages as $language) { ?>
						            					<option value="<?=$language['keel_id'];?>"<?=($language['keel_id'] == $_POST['lang'] ? ' selected="selected"' : '');?>><?=$language['nimi'];?></option>
						            				<?php } ?>
	 					            				</select></span></li>
			        				   			<li><a href="javascript:submitFilters();" class="button_search"><?=$site->sys_sona(array('sona' => 'search', 'tyyp' => 'saurus4'));?></a></li>
			        				   			<li><a href="javascript:clearFilters();" class="button"><?=$site->sys_sona(array('sona' => 'reset', 'tyyp' => 'Admin'));?></a></li>
						            		</ul>
		            					</td>
		            				</tr>
		            			</table>
			            	</form><!-- /from banner_filters -->
	            		</div>
	            	</div>
	            	<div class="s_Page_title_bar">
            			<table cellpadding="0" cellspacing="0" align="right">
            				<tr>
            					<td>
				            		<strong><?=$site->sys_sona(array('sona' => 'total items', 'tyyp' => 'Admin'));?>: <?=count($articles);?></strong>
            					</td>
            				</tr>
            			</table>
	            		<span><?=$site->sys_sona(array('sona' => 'feedbackforms_properties', 'tyyp' => 'Admin'));?></span>
	            	</div>
	            </td>
	        </tr>
	        <tr>
	            <td class="s_Page_container">
	                <div id="s_Content_container">
	                	<table cellpadding="0" cellspacing="0" class="data_table" width="100%">
	                		<thead>
	                			<tr>
	                				<td><?=$site->sys_sona(array('sona' => 'Form', 'tyyp' => 'Admin'));?></td>
	                				<td><?=$site->sys_sona(array('sona' => 'Recievers', 'tyyp' => 'Admin'));?></td>
	                			</tr>
	                		</thead>
	                		<tbody>
	                		<?php foreach($articles as $article_id => $article) { ?>
	                			<tr>
	                				<td>
	                					<strong><a href="javascript:avaaken('<?=$site->CONF['wwwroot'];?>/admin/edit.php?op=edit&id=<?=$article_id;?>&parent_id=<?=current(array_keys($article['parents']));?>&kesk=0&tyyp_idlist=2',880,660);"><?=$article['pealkiri'];?></a></strong><br>
	                					<a href="<?=$site->CONF['wwwroot'].'/editor/?id='.$article_id;?>" target="_blank"><?=implode(' > ', $article['parents'])?></a>
	                				</td>
	                				<td>
	                					<?php foreach($article['emails'] as $email) { ?>
	                					<a href="mailto:<?=$email;?>"><?=$email;?></a>  
	                					<?php } ?>
	                				</td>
	                			</tr>
	                		<?php } ?>
	                		</tbody>
	                	</table>
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