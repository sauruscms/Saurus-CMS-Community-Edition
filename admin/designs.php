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
 * Designs
 *
 */

global $site;

$class_path = '../classes/';
include($class_path.'port.inc.php');
include($class_path.'adminpage.inc.php');
require_once($class_path."archive.class.php");
require_once($class_path."lgpl/pclzip.class.php");


$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1 : 0),
	'on_admin_keel' => 1,
));

if (!$site->user->allowed_adminpage()) {
	exit;
}

// is this in editor mode?
if($site->fdat['keepThis'])
{
	$editor_mode = true;
}
else 
{
	$editor_mode = false;
}

include_once($class_path.'extension.class.php');

$adminpage_names = get_adminpage_name(array('script_name' => $site->script_name));

// change the extension
if($site->fdat['extension'] && $site->fdat['activate'])
{
	verify_form_token();
	
	$extension = get_extensions('DB', false, $site->fdat['extension']);
	$ext_path = $extension[$site->fdat['extension']]['path'];
	$extension = load_extension_config($extension[$site->fdat['extension']]);
	
	$ext_templates = array();
	$sql = $site->db->prepare('select ttyyp_id, templ_fail from templ_tyyp where extension = ?', $site->fdat['extension']);
	$result = new SQL($sql);
	while($row = $result->fetch('ASSOC'))
	{
		$ext_templates[$row['templ_fail']] = $row['ttyyp_id'];
	}
	
	if(sizeof($ext_templates) && sizeof($extension['templates']))
	{
		// active languages
		$languages = array();
		$sql = 'select keel_id from keel where on_kasutusel = 1';
		$result = new SQL($sql);
		while($language = $result->fetch('ASSOC'))
		{
			$languages[] = $language['keel_id'];	
		}
		
		$flag = false;
		foreach($extension['templates'] as $template)
		{
			// change the default content and page templates for each active language
			if($template['is_default'] && $ext_templates['../../../'.$ext_path.$template['file']])
			{
				foreach($languages as $language_id)
				{
					change_default_template($language_id, $ext_templates['../../../'.$ext_path.$template['file']], ($template['is_page'] ? 'page' : 'content'));
				}
				
				// chnage all other default pages templates to inactive state, do this only once
				if($template['is_page'] && !$flag)
				{
					$sql = "update templ_tyyp set on_nahtav = '0' where is_default = 1 and on_page_templ = '1' and ttyyp_id <> ".$ext_templates['../../../'.$ext_path.$template['file']];
					new SQL($sql);

					$sql = "update templ_tyyp set on_nahtav = '1' where is_default = 1 and on_page_templ = '1' and ttyyp_id = ".$ext_templates['../../../'.$ext_path.$template['file']];
					new SQL($sql);

					$flag = true;
				}
			}
			
			// change the template op's
			if($template['op'] && $ext_templates['../../../'.$ext_path.$template['file']])
			{
				change_op_template($template['op'], $ext_templates['../../../'.$ext_path.$template['file']]);
			}
		}
		
		clear_template_cache($site->absolute_path.'classes/smarty/templates_c/');
		
		clear_cache("ALL");
	}
	
	if($site->fdat['direct'] && !$editor_mode)
	{
		unset($site->fdat['extension']);
		unset($site->fdat['activate']);
		unset($site->fdat['direct']);
	}
	elseif ($site->fdat['direct'] && $editor_mode)
	{
		?>
		<script type="text/javascript">
			window.parent.location.href = window.parent.location.href.replace(/#$/, '');
			window.close();
		</script>
		<?php
		exit;
	}
	else 
	{
		// if in editor mode refresh the original window and close the admin-popup
		if($editor_mode)
		{
			?>
			<script type="text/javascript">
				window.opener.parent.location.href = window.opener.parent.location.href.replace(/#$/, '');
				window.opener.close();
				window.close();
			</script>
			<?php
			exit;
		}
		else 
		{
			?>
			<script type="text/javascript">
				window.opener.location.href = window.opener.location.href.replace(/#$/, '');
				window.close();
			</script>
			<?php
			exit;
		}
	}
}

// get the extensions which have a default page template defined

// active template
$sql = "select page_ttyyp_id from keel where on_default = '1'";
$result = new SQL($sql);
$active_template_id = $result->fetchsingle();

// extensions with page templates
$extensions = array();
if($site->fdat['extension']) $where = $site->db->prepare('and templ_tyyp.extension = ?', $site->fdat['extension']);
$sql = "select * from templ_tyyp where on_page_templ = '1' and is_default = 1 $where";
$result = new SQL($sql);
while($template = $result->fetch('ASSOC')) if($template['extension'])
{
	$extension = get_extensions('DB', false, $template['extension']);
	$extension = $extension[$template['extension']];
	
	// for sorting
	$extension_titles[$extension['extension_id']] = $extension['title'];
	
	// preview
	if($template['preview'])
	{
		if(strpos($template['preview'], 'http') !== 0)
		{
			// relative, add site url
			$template['preview'] = $site->CONF['wwwroot'].'/'.$extension['path'].$template['preview'];
		}
	}
	else 
	{
		$template['preview'] = $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/general/no_preview.gif';
	}
	
	// preview thumb
	if($template['preview_thumb'])
	{
		if(strpos($template['preview_thumb'], 'http') !== 0)
		{
			// relative, add site url
			$template['preview_thumb'] = $site->CONF['wwwroot'].'/'.$extension['path'].$template['preview_thumb'];
		}
	}
	else 
	{
		$template['preview_thumb'] = $site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/general/no_thumbnail.gif';
	}
	
	$extension['default_page_template'] = $template;
	$extensions[$extension['extension_id']] = $extension;
	if($template['ttyyp_id'] == $active_template_id) $active_extension = $extension;
}

// sort extensions by title, this will break with non-ascii symbols no doubt
if(sizeof($extension_titles) > 1)
{
	asort($extension_titles, SORT_STRING);
	
	$temp = array();
	
	foreach($extension_titles as $ext_id => $ext_title)
	{
		$temp[$ext_id] = $extensions[$ext_id];
	}
	$extensions = $temp;
	
	// clean up
	unset($temp);
	unset($extension_titles);
}

// uploading
if($site->fdat['op'] == "upload"){
	$ext = new extension_upload();
}

if($site->fdat['upload']){

	verify_form_token();
	$ext->tmp_location=$site->absolute_path."shared/".time()."_".rand(1,837838);
	$ext->extensions_folder=$site->absolute_path."extensions";
	$ext->overwrite_extension=$site->fdat['overwrite'];
	$ext->unpack_extension('extension'); // variable is the array name in $_FILES where the file resides.
	$ext->find_file('extension.config.php');
	if($ext->validate_extension()){
		sync_extensions();
		$synced = 1;
	}
	$zip= new archive();
	$zip->deltree($ext->tmp_location);
	@rmdir($ext->tmp_location);
}


if($site->fdat['op'] == "upload"){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head> 	
		<title><?=$site->sys_sona(array('sona' => 'extension_upload', 'tyyp' => 'Admin'));?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/default_admin_page.css" media="screen" />
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/yld.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/ie_position_fix.js"></script>
<?php if($synced == 1){?>
	<script type="text/javascript">
		window.opener.location.href = window.opener.location='designs.php';
		window.close();
	</script>
<?php }?>
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
		<form name="upload_extension" method="POST" enctype="multipart/form-data">
		<?php create_form_token('upload-extension'); ?>
		<div id="mainContainer">
			<div class="titleArea">
				<?=$site->sys_sona(array('sona' => "extension_upload", 'tyyp' => 'admin'))?>
			</div><!-- / titleArea -->
			<div class="contentArea" id="content_box">
				<div class="contentAreaContent">

							<table cellspacing="0" cellpadding="10" width="100%">
								<tr>
									<td>
<?php 
if($ext->error()){
	echo "<strong><span style='color:red'>".$site->sys_sona(array('sona' => "extension_upload_error", 'tyyp' => 'admin'))."</span></strong>";
}
?>
									</td>
								</tr>
								<tr>
									<td>
									<input type="hidden" name="op" value="upload">
									<input type="file" name="extension" style="width:250px"><br>
									<input type="checkbox" name="overwrite" id="overwrite" value="yes" 
									<?php 
	if($site->fdat['upload']){
		if($site->fdat['overwrite']){
		echo "checked";
		}
	}else{
		echo "checked";
	}
	?>><label for="overwrite"><?=$site->sys_sona(array('sona' => "overwrite", 'tyyp' => 'admin'))?></label>
									</td>
								</tr>
							</table>

				</div><!-- / contentAreaContent -->
			</div><!-- / contentArea -->
			<div class="footerArea">
				<div class="actionButtonsArea">

						<input type="submit" value="<?=$site->sys_sona(array('sona' => "upload", 'tyyp' => 'admin'))?>" name="upload">
						<input type="button" value="<?=$site->sys_sona(array('sona' => "close", 'tyyp' =>"editor"))?>" onclick="window.close();" />

				</div>
			</div class="footerArea"><!-- / footerContainer -->
		</div><!-- / mainContainer -->
		</form>
	</body>
</html>


<?php } else {

// single extension popup
if($site->fdat['extension'])
{
	$extension = array_pop($extensions);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head> 	
		<title><?=$extension['title'];?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/default_admin_page.css" media="screen" />
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/yld.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/ie_position_fix.js"></script>
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
				<?=$extension['title'];?>
			</div><!-- / titleArea -->
			<div class="contentArea" id="content_box">
				<div class="contentAreaContent">
					<a href="#" onclick="window.close();"><img src="<?=$extension['default_page_template']['preview'];?>" width="640" height="480" alt="<?=$extension['title'];?>" title="<?=$extension['title'];?>" /></a>
				</div><!-- / contentAreaContent -->
			</div><!-- / contentArea -->
			<div class="footerArea">
				<div class="actionButtonsArea">
					<form id="installForm" action="<?=$_SERVER['PHP_SELF']?>" method="POST">
						<?php create_form_token('install-extension'); ?>
						<input type="hidden" name="activate" value="1" />
						<?php if($editor_mode) { ?>
						<input type="hidden" name="keepThis" value="true" />
						<?php } ?>
						<input type="hidden" name="extension" value="<?=$extension['name'];?>" />
						<input type="button" value="<?=$site->sys_sona(array('sona' => 'apply', 'tyyp' => 'editor'))?>" onclick="this.form.submit();" />
						<input type="button" value="<?=$site->sys_sona(array('sona' => 'close', 'tyyp' => 'editor'))?>" onclick="window.close();" />
					</form>
				</div>
			</div class="footerArea"><!-- / footerContainer -->
		</div><!-- / mainContainer -->
	</body>
</html>
	
<?php

}
else 
{

// extension list
?><html>
	<head> 	
		<title><?=$site->sys_sona(array('sona' => 'design', 'tyyp' => 'Admin'));?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/default_admin_page.css" media="screen">
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/admin_menu.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/yld.js"></script>
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
			
			function showLinks(layer_id)
			{
				links = document.getElementById(layer_id);
				links.style.display = 'block';
			}
			
			function hideLinks(layer_id)
			{
				links = document.getElementById(layer_id);
				links.style.display = 'none';
			}
			
			function applyExtension(name)
			{
				var form = document.getElementById('installForm');
				form.extension.value = name;
				form.submit();
			}
		</script>
		
		<style>
		div.division_bar table {
			width: 100%;
		}
		
		div.division_bar td {
			white-space: nowrap;
			vertical-align: middle;
			padding: 3px;
		}
		
		div.division_bar td div {
			border-bottom: 1px solid #8aa2d2;
		}
		
		div.division_bar {
			clear: both;
		}
		
		div.extension {
			float: left;
			margin: 0 10px 20px 0;
		}
		
		div.extension td {
			text-align: center;
			padding: 4px;
		}
		
		div.extension td.thumbnail {
		}
		
		div.extension td.thumbnail div {
			position: absolute;
			z-index: 100;
			display: none;
			width: 128px;
			height: 98px;
			_height: 128px; /* IE 6 fix */
			padding-top: 30px;
			background-color: #fff;
			opacity: 0.85;
			filter: alpha(opacity=85);
			font-weight: bold;
		}
		
		div.extension table.active {
			background-color: #D2DFFA;
		}
		</style>
	</head>

	<body>
	    <table cellpadding="0" cellpadding="0" class="s_Body_container">
	        <tr>
	            <td class="s_Header_container">
	        		<form name="toolbar_form" id="toolbar_form" method="POST" action="<?=$_SERVER['PHP_SELF'];?>">
		            	<div class="s_Toolbar_container">
		            		<div class="s_Toolbar_content">
			            		<ul class="s_Buttons_container">
			            			<li><a href="#"  onclick="openpopup('designs.php?op=upload', 'extension_upload', '350','200');" id="button_upload" class="button_upload"><?=$site->sys_sona(array('sona' => 'upload' , 'tyyp' => 'admin'));?></a></li>
			            		</ul>

								<?php /* Grupeerimise valiku dropdown, implementeerimata:									
				    			<table cellpadding="0" cellspacing="0" align="right">
				    				<tr>
				    					<td>
						            		<ul class="s_Buttons_container">
							           			<li><span><?=$site->sys_sona(array('sona' => 'show' , 'tyyp' => 'admin'));?>: <select name="" onchange="">
							           					<option value="all">- <?=$site->sys_sona(array('sona' => 'koik', 'tyyp' => 'editor'));?> -</option>
							           					<option value="author"><?=$site->sys_sona(array('sona' => 'by_author', 'tyyp' => 'admin'));?></option>
							           				</select></span></li>
						            		</ul>
		            					</td>
		            				</tr>
		            			</table>
								*/ ?>

		            		</div><!-- s_Toolbar_content -->
		            	</div><!-- s_Toolbar_container -->
		            	<div class="s_Page_title_bar">
							<table cellpadding="0" cellspacing="0">
								<tr>
									<!--<td class="icon" width="16" style="padding-right: 3px;"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/history.png" width="16" height="16"></td>-->
									<td class="title"><?=$site->sys_sona(array('sona' => 'design', 'tyyp' => 'admin'));?><?=($active_extension ? ': '.$active_extension['title'] : '')?></td>
								</tr>
							</table>
	            		</div><!-- s_Page_title_bar -->
		    		</form>
	            </td>
	        </tr>
	        <tr>
	            <td class="s_Page_container">
	                <div id="s_Content_container">
	                	<div class="division_bar">
	                		<!--<table cellpadding="0" cellspacing="0">
	                			<tr>
	                				<td>Autori nimi</td>
	                				<td width="100%"><div></div></td>
	                			</tr>
	                		</table>-->
                		</div><!-- / division_bar -->
	                	<div class="extensions_list">
							
		                	<form id="installForm" action="<?=$_SERVER['PHP_SELF']?>" method="POST">
								<?php create_form_token('install-extension'); ?>
								<input type="hidden" name="activate" value="1">
								<input type="hidden" name="direct" value="1">
								<?php if($editor_mode) { ?>
								<input type="hidden" name="keepThis" value="true">
								<?php } ?>
								<input type="hidden" name="extension">
							</form>
							
							<?php foreach($extensions as $extension) { ?>
	                		<div class="extension">
	                			<table cellpadding="0" cellspacing="0"<?=($extension['default_page_template']['ttyyp_id'] == $active_template_id ? ' class="active"' : '');?>>
	                				<tr>
	                					<td class="thumbnail" onmouseover="showLinks('links_<?=$extension['name'];?>');" onmouseout="hideLinks('links_<?=$extension['name'];?>');">
	                						<div id="links_<?=$extension['name'];?>"><a href="javascript:void(0);" onclick="openpopup('designs.php?extension=<?=$extension['name'];?><?=($editor_mode ? '&keepThis=true' : '');?>', 'choose_design', 660, 570);"><?=$site->sys_sona(array('sona' => 'view', 'tyyp' => 'editor'));?></a><br><br><a href="javascript:applyExtension('<?=$extension['name'];?>');"><?=$site->sys_sona(array('sona' => 'apply', 'tyyp' => 'editor'));?></a><?php if ($extension['is_downloadable'] == 1) { ?><br><br><a href="<?=$site->CONF['wwwroot'].'/admin/extensions.php?download='.$extension['extension_id'];?>"><?=$site->sys_sona(array('sona' => 'download', 'tyyp' => 'editor'));?></a><?php } ?></div>
                							<img src="<?=$extension['default_page_template']['preview_thumb'];?>" width="128" height="128" alt="<?=$extension['title'];?>" title="<?=$extension['title'];?>">
	                					</td>
	                				</tr>
	                				<tr>
	                					<td><a href="javascript:void(0);" onclick="openpopup('designs.php?extension=<?=$extension['name'];?><?=($editor_mode ? '&keepThis=true' : '');?>', 'choose_design', 660, 570);"><?=$extension['title'];?></a></td>
	                				</tr>
	                			</table>
	                		</div><!-- / extension -->
							<?php } // end foreach $extensions ?>
	                	</div><!-- / extensions_list -->
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

<?php

}
}
