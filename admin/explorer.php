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
 * Admin page template
 *
 */

global $class_path;

$class_path = '../classes/';

include($class_path.'port.inc.php');
include($class_path.'adminpage.inc.php');

$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1 : 0),
	'on_admin_keel' => 1,
));

$site->user->adminpermissions = $site->user->load_adminpermissions();			

if ($_GET['editor'] != 1 && !$site->user->allowed_adminpage()) exit;

include($class_path.'explorerNode.class.php');
include($class_path.'explorerNodeObject.class.php');
include($class_path.'explorerTraversalActions.classes.php');
include($class_path.'explorerHelpers.classes.php');

include($class_path.'explorer_functions.inc.php');

$timer = new SingleTimer();
if($timer_debug = (int)$_GET['timer'])
{
	$timer = new SingleTimer();
}

if($timer_debug) $timer->printTime('explorer_start', ($timer_debug == 1 ? true : false)); 

// params & defaults

// control set
$swk_setup = (string)$_GET['swk_setup'];
if(!$swk_setup) $_GET['swk_setup'] = $swk_setup = 'swk_setup';

//callback, javascript callback function name, takes one argument an array of selected nodes.
$callback = $_SESSION[$swk_setup]['callback'];

//selectable (0 - not, 1 - single, 2 - multi)
$selectable = $_SESSION[$swk_setup]['selectable'];
if(empty($selectable)) $selectable = 0;

//pre_selected
$pre_selected = $_GET['pre_selected'];
if(empty($pre_selected)) $pre_selected = array();
else $pre_selected = explode(',', $pre_selected);

foreach($pre_selected as $i => $pre_select)
{
	$pre_selected[$i] = trim($pre_select);
}

// remove some objects with subtree from tree
$remove_objects = $_GET['remove_objects'];

if(empty($remove_objects)) $remove_objects = array();
else $remove_objects = explode(',', $remove_objects);

foreach($remove_objects as $i => $remove_object)
{
	$remove_objects[$i] = trim($remove_object);
}

// object classes
$classes = ($_SESSION[$swk_setup]['mem_classes'] ? $_SESSION[$swk_setup]['mem_classes'] : $_SESSION[$swk_setup]['classes']);
if(empty($classes)) $classes = array('rubriik', 'artikkel', );

// fields to pull from db
$fields = $_SESSION[$swk_setup]['db_fields'];
if(empty($fields)) $fields = array('select_checkbox', 'objekt_id', 'pealkiri', 'tyyp_id', );

// required db fields
//$fields = array_merge(array('objekt_id', 'pealkiri', 'tyyp_id', ),  $fields);

// fields to display
$display_fields = $_SESSION[$swk_setup]['display_fields'];
if(empty($display_fields)) $display_fields = array('select_checkbox', 'pealkiri',);

// language
$language_id = (int)$_GET['lang'];
if(!isset($_GET['lang']))
{
	$language_id = (isset($_SESSION['keel']['keel_id']) ? $_SESSION['keel']['keel_id'] : $site->keel);
}
else 
{
	$language_id = (int)$_GET['lang'];
}

if($_SESSION[$swk_setup]['hide_language_selection'] != 1){  //we create an array

$languages = array();
$sql = 'select nimi, keel_id from keel where on_kasutusel = 1 order by nimi;';
$result = new SQL($sql);
while ($row = $result->fetch('ASSOC'))
{
	$languages[] = $row;
}
}
// tree trunk
// can be alias
$trunk_id = $site->alias(array('key' => $_GET['objekt_id'], 'keel' => $language_id));
if(!$trunk_id) 
{
	$trunk_id = (int)$_GET['objekt_id'];
}

if(empty($trunk_id))
{
	$trunk = new NodeObject(array('objekt_id' => 0, 'pealkiri' => 'CMS', 'select_checkbox' => 0, 'tyyp_id' => 0, 'klass' => '', 'on_avaldatud' => '', 'parent_id' => '', 'sys_alias' => '', 'friendly_url' => '', 'ttyyp_id' => '', 'page_ttyyp_id' => '', 'kesk' => '',  'aeg' => '', ));
	$trunk_id = 0;
}
else
{
	$trunk = new Objekt(array('objekt_id' => $trunk_id));
	if($trunk->objekt_id)
	{
		foreach($fields as $field)
		{
			$trunk_fields[$field] = $trunk->all[$field];
		}
		$trunk_fields['select_checkbox'] = 0; 
		$trunk = new NodeObject($trunk_fields); 
	}
	else 
	{
		exit;
	}
}
// /tree trunk

//open objects
$unfolded = array();
if($_COOKIE['swk_unfolded_ids'])
{
	$unfolded = explode(',' , $_COOKIE['swk_unfolded_ids']);
}
if(empty($unfolded)) $unfolded = array($trunk_id);
// /params & defaults

$tree = new Node($trunk);
if($timer_debug) $timer->printTime('populate tree', ($timer_debug == 1 ? true : false));
$tree->populateTree($remove_objects);
if($timer_debug) $timer->printTime('end populate tree', ($timer_debug == 1 ? true : false));

$opArray = new ObjectParentArray($tree->object_parent_array);

//search
$search = 0;
if($_POST['form_action'] == 'search')
{
	if($timer_debug) $timer->printTime('start search', ($timer_debug == 1 ? true : false));
	$searches = array();
	$classes_filter = array();
	
	foreach($_POST as $field => $keyword)
	{
		if($field == 'classes')
		{
			$classes_filter = explode(',', $keyword);
			foreach($classes_filter as $i => $class)
			{
				if(!in_array($class, $classes))
				{
					unset($classes_filter[$i]);
				}
			}
			
			$_SESSION[$swk_setup]['mem_classes'] = $classes;
			$_SESSION[$swk_setup]['classes'] = $classes = (sizeof($classes_filter) > 0 ? $classes_filter : $classes);
		}
		elseif(in_array($field, $fields) && $keyword != '')
		{
			$searches[$field] = $keyword;
		}
		
	}
	if(count($searches))
	{
		// forget pre selection
		$pre_selected = array();
		
		//fold the tree
		$unfolded = array();
		
		$search = new TreeSearch($searches, $classes, $language_id);
		//add search result to be open in trees
		foreach($search->getResults() as $object_id)
		{
			while($object_id !== null)
			{
				$object_id = $opArray->find_parent($object_id);
				if($object_id !== null && !in_array($object_id, $unfolded)) $unfolded[] = $object_id;
			}
		}
	}
	if($timer_debug) $timer->printTime('end search', ($timer_debug == 1 ? true : false));
}
else 
{
	$_SESSION[$swk_setup]['classes'] = $classes = $_SESSION[$swk_setup]['mem_classes'];	
}
// /search

// unfold pre selected parents
foreach($pre_selected as $object_id)
{
	$object_id = $opArray->find_parent($object_id);
	if($object_id !== null && !in_array($object_id, $unfolded)) $unfolded[] = $object_id;
}

// / unfold pre selected parents

if($timer_debug) $timer->printTime('traverse with InitTree', ($timer_debug == 1 ? true : false));
$tree->traverse(new InitTree($opArray, $unfolded, $fields, $classes, $language_id));
if($timer_debug) $timer->printTime('end traverse with InitTree', ($timer_debug == 1 ? true : false));

if($timer_debug) $timer->printTime('traverse with JavaScriptTree', ($timer_debug == 1 ? true : false));
$jstree = new JavaScriptTree(array('traverse', 'traverseLoaded', 'traverseLoadedStop', 'setContainer',), ($search ? $search->getResults() : null), $pre_selected);
$tree->traverse($jstree);
if($timer_debug) $timer->printTime('end traverse with JavaScriptTree', ($timer_debug == 1 ? true : false));

#printr($tree);

$adminpage_names = get_adminpage_name(array('script_name' => $site->script_name));

?><html>
	<head>
		<title><?=$site->title;?> <?=$site->cms_version;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/default_admin_page.css" media="screen">
		<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path'];?>/swk_explorer.css" media="screen">
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path'];?>/admin_menu.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/prototype.js"></script>
		<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/swk_explorer.js"></script>
		<script type="text/javascript">
			var isIE = navigator.appVersion.match(/MSIE/); // assume gecko on false

			function callback_wrapper()
			{
				<?php if($callback) { ?>
				var selection = new GetSelected();
				tree.traverseLoaded(selection)
				<?=$callback;?>(selection.nodes<?php if($pre_selected){echo ",'".$pre_selected[0]."'";}?>);
				<?php } ?>
			}
			
			function contentDimController(elem_id)
			{
				elem = document.getElementById(elem_id);
			    elem.style.display = 'none';
			    elem.style.height = elem.parentNode.offsetHeight + 'px';
			    elem.style.display = 'block';
			}
			
			window.onresize = function()
			{
				contentDimController('s_Content_container');
			}
			
			//globals
			//this is the main culprit, used globaly in many standalone functions
			var tree = <?=$jstree->getScript();?>; //the object tree
			//this global is used for loading
			var loaded = new Array();
			//this global is used to keep track of the node thats being loaded
			var requestedNode = 0;
			//fields to render
			var loadFields = new Array('<?=implode("','", $display_fields);?>');
			//select mode
			var select_mode = <?=$selectable;?>;
			//language_id
			var language_id = <?=$language_id;?>;
			//swk_setup
			var swk_setup = '<?=$swk_setup;?>';
			//cms styles path
			var styles_path = '<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>';
			//cms wwwroot
			var wwwroot = '<?=$site->CONF['wwwroot']?>';
			// /globals
			
			window.onload = function()
			{
				contentDimController('s_Content_container');
				
				make_breadcrumb('<?=$adminpage_names['parent_pagename'];?>','<?=$adminpage_names['pagename'];?>');
				
				//init tree
				tree.traverseLoaded(new InitTree('tree_content', loadFields));
				tree.traverseLoaded(new InitTreeDisplay('tree_content'));
				if(document.getElementById('pealkiri')) document.getElementById('pealkiri').focus();
			}
		</script>
	</head>
	<body>
	    <table cellpadding="0" cellpadding="0" class="s_Body_container">
	        <tr>
	            <td class="s_Header_container">
	            	<div class="s_Toolbar_container">
	            		<div class="s_Toolbar_content">
	            			<table cellpadding="0" cellspacing="0" align="right">
	            				<tr>
	            					<td>
									<?php if(is_array($languages) && sizeof($languages) > 1){?>
					            		<ul class="s_Buttons_container">
					            			<li><span><select name="lang" class="select" onchange="changeLang(this.value);">
					            				<?php foreach($languages as $language) { ?>
					            					<option value="<?=$language['keel_id'];?>"<?=($language['keel_id'] == $language_id ? ' selected="selected"' : '');?>><?=$language['nimi'];?></option>
					            				<?php } ?>
 					            				</select></span></li>
					            		</ul>
									<?php }else{?>
										<input type="hidden" name="lang" id="lang" value="<?=$language_id;?>">
									<?php }?>
	            					</td>
	            				</tr>
	            			</table>
	            		</div>
	            	</div>
	            	<div class="s_Page_title_bar">
						<table cellpadding="0" cellspacing="0">
							<tr>
								<td class="icon" width="16" style="padding-right: 3px;"><img src="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/gfx/icons/16x16/actions/swk_explorer.png" width="16" height="16"></td>
								<td class="title"><span><?=$site->sys_sona(array('sona' => 'explorer', 'tyyp' => 'admin'));?><?=($swk_setup == 'general_site_explorer' ? ' (beta)' : '');?></span></td>
							</tr>
						</table>
            		</div><!-- s_Page_title_bar -->
	            </td>
	        </tr>
	        <tr>
	            <td class="s_Page_container">
	                <div id="s_Content_container">
	                	<table cellpadding="0" cellspacing="0" class="tree">
	                		<thead>
	                			<tr>
	                				<?php foreach ($display_fields as $field)
	                				{
	                					$swk_function_name = 'swk_title_row_'.$field;
	                					
	                					if(function_exists($swk_function_name))
	                					{
	                						$swk_function_name($name);
	                					}
	                					else 
	                					{
	                						swk_title_row_default($field);
	                					}
									} // /foreach 
									
									// search buttons 
									?><td class="search_buttons"></td>
		                			<?php // /search buttons ?>
			                	</tr>
	                			<tr>
	                				<form name="filters" id="filters" method="POST"><!-- no action defined to preserve GET params -->
		                				<?php foreach ($display_fields as $field)
		                				{
		                					$swk_function_name = 'swk_title_search_'.$field;
		                					
		                					if($field == 'klass')
		                					{
		                						swk_title_search_klass($name, implode(',', $classes));
		                					}
		                					elseif(function_exists($swk_function_name))
		                					{
		                						$swk_function_name($name, $searches[$field]);
		                					}
		                					else 
		                					{
		                						swk_title_search_default($field, $searches[$field]);
		                					}
										} // /foreach 
										
										// search buttons
										?><th class="search_buttons">
						            		<ul class="s_Buttons_container">
						            			<li><a href="javascript:submitFilters();" id="button_search" class="button_search_nolabel"></a></li>
						            			<li><a href="javascript:clearFilters();" id="button_search_reset" class="button_search_reset_nolabel"></a></li>
						            		</ul>
		                				</th>
			                			<?php // /search buttons ?>
			                			
			                			<input type="hidden" name="form_action" value="search">
		                			</form>
		                		</tr>
	                		</thead>
	                		<tbody id="tree_content"></tbody>
	                	</table>
	                </div><!-- /s_Content_container -->
	            </td>
	        </tr>
	        <tr>
	            <td class="s_Footer_container">
	            <?php if($selectable) { ?>
        			<table cellpadding="0" cellspacing="0" align="right" class="choose_buttons">
        				<tr>
        					<td>
								<input type="button" class="button" value="<?=$site->sys_sona(array('sona' => 'Vali', 'tyyp' => 'admin'));?>" id="choose_button" onclick="callback_wrapper()" disabled="disabled">
								<input type="button" class="button" value="<?=$site->sys_sona(array('sona' => 'Close', 'tyyp' => 'editor')) ?>" onclick="javascript:window.close();">
        					</td>
        				</tr>
        			</table>
	            <?php } ?>
	            </td>
	        </tr>
	    </table>
	</body>
</html>
<?php

if($timer_debug) $timer->printTime('explorer_end', ($timer_debug == 1 ? true : false));
