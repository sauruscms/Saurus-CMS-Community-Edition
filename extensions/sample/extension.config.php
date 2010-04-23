<?php

// This is a sample configuration file for the extension "sample" that ships with Saurus CMS installation.


// Extension unique ID, must be the same value as the current directory name. 
// Do not include spaces or special characters.
$EXTENSION['name'] = 'sample';

// Title, appears in the section editor and extensions mananger
$EXTENSION['title'] = 'Sample Extension';

// Short description, may include HTML tags
$EXTENSION['description'] = 'For explaining how stuff works';

// Author name, may include HTML tags
$EXTENSION['author'] = 'Saurus <a href="http://www.saurus.info" target="_blank">www.saurus.info</a>';

// Extension version number
$EXTENSION['version'] = '1.0';

// Version release date (yyyy-mm-dd)
$EXTENSION['version_date'] = '2006-05-18';

// Path to the icon image
$EXTENSION['icon_path'] = 'logo.gif';

// Dependency: the minimum Saurus version required
$EXTENSION['min_saurus_version'] = '4.2.0';

/* Smarty plugins directory */
//$EXTENSION['smarty_plugins'] = '../../../extensions/'.$EXTENSION['name'].'/smarty_plugins';

/* WYSIWYG configuration */
//URL
//$EXTENSION['url'] = $EXTENSION['protocol'].$EXTENSION['hostname'].$EXTENSION['wwwroot'].'/extensions/'.$EXTENSION['name'];

// WYSIWYG Editor config
// toolbars 
//$EXTENSION['wysiwyg_config']['ToolbarSets'] = array('SCMS', 'SCMS_simple');
//$EXTENSION['wysiwyg_config']['DefaultToolbarSet'] = 'SCMS_simple';

/*
 * $EXTENSION['wysiwyg_config']['Config'] for possible values see js/fckeditor/fckconfig.js
 * you can also set these values in js file defined by $EXTENSION['wysiwyg_config']['Config']['CustomConfigurationsPath']
 */

//$EXTENSION['wysiwyg_config']['Config']['CustomConfigurationsPath'] = $EXTENSION['url'].'/wysiwyg_config.js';
/* /WYSIWYG configuration */

// Array of admin-pages which are displayed under Extensions menu
#$EXTENSION['adminpages'][] = array(
#	"name" => "Sample adminpage",
#	"file" => "admin/custom_adminpage_sample.php"
#);

// The array of templates
#$EXTENSION['templates'][] = array(
#	"name" => "Sample extension",
#	"file" => "templates/sample.html",
#	"is_page" => 0,
# 	'is_visible' => 1,
#	'is_readonly' => 1,
#);

// Constants which can be later used in templates can be defined here
// For example $EXTENSION['config']['news_id'] = '123';
// Can be called as {$sample.config.new_id} in template code where "sample" is the extension name

#$EXTENSION['config']['constant_name'] = '';

?>