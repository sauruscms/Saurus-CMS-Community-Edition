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

$classes = array();
foreach($site->object_classes as $key => $value)
{
	if($value['use_trash'] == 1) $classes[] = $value['klass'];
}
$classes = implode(',', $classes);

$languages = array();
$sql = 'select nimi, keel_id, on_default from keel where on_kasutusel = 1 order by nimi;';
$result = new SQL($sql);
while ($row = $result->fetch('ASSOC'))
{
	$languages[] = $row;
	if($row['on_default'])
	{
		$def_lang_id = $row['keel_id'];
	}
}

// selected language, default 0 (estonian)
$language_id = (isset($site->fdat['language']) ? (int)$site->fdat['language'] : $def_lang_id);

// trash id
$trash_id = $site->alias(array('key' => 'trash', 'keel' => $language_id));
if(!$trash_id)
{
	echo "Error! this language doesn't have Recycle bin!";
	exit;
}

// sort by, default title
switch ($site->fdat['sort_by'])
{
	case 'pealkiri' : $sort_by = 'pealkiri'; break;
	case 'changed_time' : $sort_by = 'changed_time'; break;
	case 'changed_user_name' : $sort_by = 'changed_user_name'; break;
	default: $sort_by = 'changed_time'; break;
}

// sort direction, default ascending
switch ($site->fdat['sort_dir'])
{
	case 'asc' : $sort_dir = 'asc'; break;
	case 'desc' : $sort_dir = 'desc'; break;
	default: $sort_dir = 'desc'; break;
}


if($site->fdat['delete_all'] && count($site->fdat['objects']))
{
	verify_form_token();
	
	//empty trash
	for($i = count($site->fdat['objects']) - 1; $i >= 0; $i--)
	{
		//printr($site->fdat['objects'][$i]);
		foreach($site->fdat['objects'][$i] as $object_id => $values) if($object_id)
		{
			$delete_objs = new Alamlist(array(
				'parent' => $object_id,
				'klass' => $classes,
			));
			
			while($object = $delete_objs->next())
			{
				$object->del();
				//printr($object->objekt_id.' deleted');
				new Log(array(
					'action' => 'delete',
					'component' => 'Recycle bin',
					'objekt_id' => $objekt_id->objekt_id,
					'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($object->all['klass'])), $object->pealkiri(), $object->objekt_id, ' removed from Recycle Bin '),
				));
			}
		}
		
	}

	$delete_objs = new Alamlist(array(
		'parent' => $trash_id,
		'klass' => $classes,
	));
	
	while($object = $delete_objs->next())
	{
		$object->del();
		//printr($object->objekt_id.' deleted');
		new Log(array(
			'action' => 'delete',
			'component' => 'Recycle bin',
			'objekt_id' => $objekt_id->objekt_id,
			'message' => sprintf("%s '%s' (ID = %s) %s" , ucfirst(translate_en($object->all['klass'])), $object->pealkiri(), $object->objekt_id, ' removed from Recycle Bin '),
		));
	}

	new Log(array(
		'action' => 'delete',
		'component' => 'Recycle bin',
		'message' => 'Recycle Bin emptied',
	));
}

$root = new Alamlist(array(
	'parent' => $trash_id,
	'klass' => $classes,
	'order' => $sort_by.' '.$sort_dir,
));

$untraveled = array(); //stack
$periferal = array(); //misc data for other table cells

while($item = $root->next())
{
	$untraveled[] = array('level' => 0, 'object' => $item);
	$periferal[] = $item;
}

$untraveled = array_reverse($untraveled);

$adminpage_names = get_adminpage_name(array('script_name' => $site->script_name));

?><html>
	<head> 	
		<title><?=$site->sys_sona(array('sona' => 'recycle bin', 'tyyp' => 'Admin'));?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/default_admin_page.css" media="screen">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/scms_dropdown.css" media="screen">
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
			
			function askConfirmation(confirm_phrase, form)
			{
				var form = document.getElementById(form);
				if(form && confirm(confirm_phrase))
				{
					form.delete_all.value = 1;
					form.submit();
				}
			}
			
			function changeOrdering(column)
			{
				var form = document.getElementById('actionForm');
				
				if(form.sort_by.value == column)
				{
					(form.sort_dir.value == 'asc' ? form.sort_dir.value = 'desc' : form.sort_dir.value = 'asc'); 
				}
				else
				{
					form.sort_dir.value = 'asc';
					form.sort_by.value = column;
				}
				
				form.delete_all.value = 0;
				form.submit();
			}
		</script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/yld.js"></script>
<?php print_context_button_init(); ?>
	</head>

	<body>
	    <table cellpadding="0" cellpadding="0" class="s_Body_container">
	        <tr>
	            <td class="s_Header_container">
		            	<div class="s_Toolbar_container">
		            		<div class="s_Toolbar_content">
				        		<form name="toolbar_form" id="toolbar_form" method="POST" action="<?=$_SERVER['PHP_SELF'];?>">
			        				<?php create_form_token('trash-toolbar'); ?>
					    			<table cellpadding="0" cellspacing="0" width="100%">
					    				<tr>
					    					<td>
							            		<ul class="s_Buttons_container">
							            			<li><a href="javascript:askConfirmation('<?=$site->sys_sona(array('sona' => 'recycle bin', 'tyyp' => 'admin')).': '.$site->sys_sona(array('sona' => 'empty', 'tyyp' => 'admin'))."? ";?>','actionForm')" id="button_delete" class="button_delete"><?=$site->sys_sona(array('sona' => 'Empty' , 'tyyp' => 'admin'));?></a></li>
							            		</ul>
					    					</td>
				        					<td>
												<?php if(is_array($languages) && sizeof($languages) > 1) { ?>
							            		<ul class="s_Buttons_container" style="float: right;">
							            			<li><span><?=$site->sys_sona(array('sona' => 'Language', 'tyyp' => 'Admin'));?>:  <select name="language" class="select" onchange="this.form.submit();">
							            				<?php foreach($languages as $language) { ?>
							            					<option value="<?=$language['keel_id'];?>"<?=($language['keel_id'] == $language_id ? ' selected="selected"' : '');?>><?=$language['nimi'];?></option>
							            				<?php } ?>
								            				</select></span></li>
							            		</ul>
												<?php } else { ?>
													<input type="hidden" name="language" id="language" value="<?=$language_id;?>">
												<?php } ?>
				        					</td>
					    				</tr>
					    			</table>
					    		</form>
		            		</div><!-- s_Toolbar_content -->
		            	</div><!-- s_Toolbar_container -->
		            	<div class="s_Page_title_bar">
							<table cellpadding="0" cellspacing="0">
								<tr>
									<td class="icon" width="16" style="padding-right: 3px;"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/edittrash.png" width="16" height="16"></td>
									<td class="title"><?=$site->sys_sona(array('sona' => 'recycle bin', 'tyyp' => 'Admin'));?></td>
								</tr>
							</table>
	            		</div><!-- s_Page_title_bar -->
	            </td>
	        </tr>
	        <tr>
	            <td class="s_Page_container">
	                <div id="s_Content_container">
					<form name="actionForm" id="actionForm" method="POST">
       				<?php create_form_token('trash-actions'); ?>

					<input type="hidden" name="delete_all" value="0">

					<input type="hidden" name="language" value="<?=$language_id;?>">
					<input type="hidden" name="sort_by" value="<?=$sort_by;?>">
					<input type="hidden" name="sort_dir" value="<?=$sort_dir;?>">
					
					<table cellpadding="0" cellspacing="0" class="data_table">
					<!-- table header -->
					<thead>
						<tr>
							<td>
								<a href="#" onclick="changeOrdering('pealkiri');"><?=$site->sys_sona(array('sona' => 'title', 'tyyp' => 'saurus4'));?></a><?php if($sort_by == 'pealkiri') echo '<img src="'.$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/general/sort_'.($sort_dir == 'asc' ? 'down' : 'up' ).'.gif">'; ?>
							</td>
							<td>
								<a href="#" onclick="changeOrdering('changed_user_name');"><?=$site->sys_sona(array('sona' => 'who', 'tyyp' => 'admin'));?></a><?php if($sort_by == 'changed_user_name') echo '<img src="'.$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/general/sort_'.($sort_dir == 'asc' ? 'down' : 'up' ).'.gif">'; ?>
							</td>
							<td>
								<a href="#" onclick="changeOrdering('changed_time');"><?=$site->sys_sona(array('sona' => 'when', 'tyyp' => 'admin'));?></a><?php if($sort_by == 'changed_time') echo '<img src="'.$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/general/sort_'.($sort_dir == 'asc' ? 'down' : 'up' ).'.gif">'; ?>
							</td>
						</tr>
					</thead>
					
					<!-- table content -->
					<tbody>
					<tr>
					<td>
					
					<ul>
					<?php
					
					//printr($site->fdat);
					if(count($untraveled))
					{
						echo '<input type="hidden" name="objects[0]['.$untraveled[count($untraveled) - 1]['object']->objekt_id.'][completed]" value="1" />';
						echo '<input type="hidden" name="objects[0]['.$untraveled[count($untraveled) - 1]['object']->objekt_id.'][parent_id]" value="'.$untraveled[count($untraveled) - 1]['object']->parent_id.'" />';
					}
					
					while($item = array_pop($untraveled))
					{
						$next = $untraveled[count($untraveled) - 1];
						
						echo '<li>';
						echo $item['object']->get_edit_buttons(array('nupud' => array('edit', 'delete'), 'tyyp_idlist' => $item['object']->all['tyyp_id'], ));
						//echo $item['object']->objekt_id;
						echo $item['object']->all['pealkiri'];
						$children = new Alamlist(array(
							'parent' => $item['object']->objekt_id,
							'klass' => $classes,
						));
						if($children->rows)
						{
							echo '<ul>';
							echo '<input type="hidden" name="objects['.$item['level'].']['.$item['object']->objekt_id.'][completed]" value="1" />';
							echo '<input type="hidden" name="objects['.$item['level'].']['.$item['object']->objekt_id.'][parent_id]" value="'.$item['object']->parent_id.'" />';
						}
						if(!$children->rows && $next && $next['level'] < $item['level'])
							for($i = $next['level']; $i < $item['level']; $i++)
								if($i == ($item['level'] - 1) && $next['level'] == 0)
								{
									$perifery = array_shift($periferal);
									//periferal info
									?>
											</ul>
										</td>
										<td>
											<?=$perifery->all['changed_user_name'];?>
										</td>
										<td>
											<?=date('d.m.Y h:i', strtotime($perifery->all['changed_time']));?>
										</td>
									</tr>
									<tr>
										<td>
											<ul>
									<?php
								}
								else echo '</ul>';
						elseif(!$children->rows && $next && $next['level'] == 0 && $item['level'] == 0)
						{
							$perifery = array_shift($periferal);
							//periferal info
							?>
									</ul>
								</td>
								<td>
									<?=$perifery->all['changed_user_name'];?>
								</td>
								<td>
									<?=date('d.m.Y h:i', strtotime($perifery->all['changed_time']));?>
								</td>
							</tr>
							<tr>
								<td>
									<ul>
							<?php
						}
						 
						$temp = array_reverse($children->list);
						foreach($temp as $obj)
						{
							$untraveled[] = array('level' => $item['level'] + 1, 'object' => $obj);
						}
						
						echo '</li>';
						//break;
					}
					
						$perifery = array_shift($periferal);
						//periferal info
						?>
								</ul>
							</td>
							<td>
								<?=($perifery ? $perifery->all['changed_user_name'] : '');?>
							</td>
							<td>
								<?=($perifery ? date('d.m.Y h:i', strtotime($perifery->all['changed_time'])) : '');?>
							</td>
						</tr>
					</tbody>
					
					</table>
					
					</form><!-- / actionForm -->
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