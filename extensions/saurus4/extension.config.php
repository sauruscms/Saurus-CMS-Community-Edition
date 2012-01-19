<?php

// Extension unique ID, must be the same value as the current directory name. 
// Do not include spaces or special characters.
$EXTENSION['name'] = 'saurus4';

// Title, appears in the section editor and extensions mananger
$EXTENSION['title'] = 'Saurus 4';

// Short description, may include HTML tags
$EXTENSION['description'] = 'These templates ship with Saurus CMS 4 installation.';

// Author name, may include HTML tags
$EXTENSION['author'] = 'Saurus <a href="http://www.saurus.info" target="_blank">www.saurus.info</a>';

// Extension version number
$EXTENSION['version'] = '1.8';

// Version release date (yyyy-mm-dd)
$EXTENSION['version_date'] = '2009-11-19';

// Path to the icon image
$EXTENSION['icon_path'] = 'logo.gif';

// Dependency: the minimum Saurus version required
$EXTENSION['min_saurus_version'] = '4.6.4';

// Can be downloaded via CMS admin interface?
$EXTENSION['is_downloadable'] = '1';

// Array of admin-pages which are displayed under Extensions menu
#$EXTENSION['adminpages'][] = array(
#	"name" => "Sample adminpage",
#	"file" => "admin/custom_adminpage_sample.php"
#);

// The array of templates

// page templates
$EXTENSION['templates'][] = array(
	'name' => 'Page template',
	'file' => 'page_templates/default_page_template.html',
	'is_page' => 1,
 	'is_visible' => 0,
	'is_readonly' => 1,
	'is_default' => 0,
	'preview_thumb' => 'images/page_template_thumbnail.jpg',
	'preview' => 'images/page_template_preview.jpg',
);

$EXTENSION['templates'][] = array(
	'name' => 'Modern page template',
	'file' => 'page_templates/modern_page_template.html',
	'is_page' => 1,
 	'is_visible' => 1,
	'is_readonly' => 1,
	'is_default' => 1,
	'preview_thumb' => 'images/page_template_thumbnail.jpg',
	'preview' => 'images/page_template_preview.jpg',
);

$EXTENSION['templates'][] = array(
	'name' => 'RSS feed of a section',
	'file' => 'page_templates/section_rss.html',
	'is_page' => 1,
 	'is_visible' => 0,
	'is_readonly' => 1,
	'is_default' => 0,
	'op' => 'rss',
);

// content templates
$EXTENSION['templates'][] = array(
	'name' => 'Articles: 1 column',
	'file' => 'content_templates/articles.html',
	'is_page' => 0,
 	'is_visible' => 1,
	'is_readonly' => 1,
	'is_default' => 1,
);

$EXTENSION['templates'][] = array(
	'name' => 'Articles: 2 columns',
	'file' => 'content_templates/articles_2_columns.html',
	'is_page' => 0,
 	'is_visible' => 1,
	'is_readonly' => 1,
);

$EXTENSION['templates'][] = array(
	'name' => 'Articles: news with archive',
	'file' => 'content_templates/news_list.html',
	'is_page' => 0,
 	'is_visible' => 1,
	'is_readonly' => 1,
);

$EXTENSION['templates'][] = array(
	'name' => 'Articles: news archive',
	'file' => 'content_templates/news_archive.html',
	'is_page' => 0,
 	'is_visible' => 0,
	'is_readonly' => 1,
	'op' => 'archive',
);

$EXTENSION['templates'][] = array(
	'name' => 'Articles: bulleted list',
	'file' => 'content_templates/article_list.html',
	'is_page' => 0,
 	'is_visible' => 1,
	'is_readonly' => 1,
);

$EXTENSION['templates'][] = array(
	'name' => 'Forum',
	'file' => 'content_templates/forum.html',
	'is_page' => 0,
 	'is_visible' => 1,
	'is_readonly' => 1,
);

$EXTENSION['templates'][] = array(
	'name' => 'Search: results',
	'file' => 'content_templates/search_results.html',
	'is_page' => 0,
 	'is_visible' => 0,
	'is_readonly' => 1,
	'op' => 'search',
);

$EXTENSION['templates'][] = array(
	'name' => 'Search: advanced',
	'file' => 'content_templates/advanced_search.html',
	'is_page' => 0,
 	'is_visible' => 0,
	'is_readonly' => 1,
	'op' => 'advsearch',
);

$EXTENSION['templates'][] = array(
	'name' => 'User registration',
	'file' => 'content_templates/register.html',
	'is_page' => 0,
 	'is_visible' => 0,
	'is_readonly' => 1,
	'op' => 'register',
);

$EXTENSION['templates'][] = array(
	'name' => 'Documents',
	'file' => 'content_templates/documents.html',
	'is_page' => 0,
 	'is_visible' => 1,
	'is_readonly' => 1,
);

$EXTENSION['templates'][] = array(
	'name' => 'Gallery',
	'file' => 'content_templates/gallery_list.html',
	'is_page' => 0,
 	'is_visible' => 1,
	'is_readonly' => 1,
);

$EXTENSION['templates'][] = array(
	'name' => 'Sitemap',
	'file' => 'content_templates/sitemap.html',
	'is_page' => 0,
 	'is_visible' => 0,
	'is_readonly' => 1,
	'op' => 'sitemap',
);

$EXTENSION['templates'][] = array(
	'name' => 'Blog',
	'file' => 'content_templates/blog.html',
	'is_page' => 0,
 	'is_visible' => 1,
	'is_readonly' => 1,
);

// object/detail templates
$EXTENSION['templates'][] = array(
	'name' => 'Articles: detail view',
	'file' => 'object_templates/article.html',
	'is_page' => 0,
 	'is_visible' => 0,
	'is_readonly' => 1,
);

$EXTENSION['templates'][] = array(
	'name' => 'Articles: feedback errors',
	'file' => 'object_templates/feedback_error.html',
	'is_page' => 0,
 	'is_visible' => 0,
	'is_readonly' => 1,
	'op' => 'error',
);

$EXTENSION['templates'][] = array(
	'name' => 'Forum: topic view',
	'file' => 'object_templates/forum_topic.html',
	'is_page' => 0,
 	'is_visible' => 0,
	'is_readonly' => 1,
);

$EXTENSION['templates'][] = array(
	'name' => 'Forum: message view',
	'file' => 'object_templates/forum_message.html',
	'is_page' => 0,
 	'is_visible' => 0,
	'is_readonly' => 1,
);

$EXTENSION['templates'][] = array(
	'name' => 'Gallery: detail view',
	'file' => 'object_templates/gallery.html',
	'is_page' => 0,
 	'is_visible' => 0,
	'is_readonly' => 1,
);

// Smarty plugins directory
$EXTENSION['smarty_plugins'] = '../../../extensions/'.$EXTENSION['name'].'/smarty_plugins';

// Smarty filters
$EXTENSION['smarty_filters'] = array(
	'all' => array(
    	'output' => array('query_highlight', 'obfuscate_email', ),
    ),
);
// /Smarty filters

// default capthca definition
// image format gif, jpg, png
$EXTENSION['captchas']['default']['image_type'] = 'gif';
// image width
$EXTENSION['captchas']['default']['image_width'] = 120;
// image height
$EXTENSION['captchas']['default']['image_height'] = 40;
// text string to render on the image
$EXTENSION['captchas']['default']['text_to_verify'] = substr(md5(uniqid(rand(), 1)), rand(0, (strlen(md5(uniqid(rand(), 1)))-6)), rand(3,4));
//image effects
$EXTENSION['captchas']['default']['effects'] = array(
	array(
		'name' => 'GotchaGradientEffect',
		'args' => array(),
	),
	array(
		'name' => 'GotchaGridEffect',
		'args' => array(
				'size' => 2,
			),
	),
	array(
		'name' => 'GotchaDotEffect',
		'args' => array(),
	),
	array(
		'name' => 'GotchaTextEffect',
		'args' => array(
				'text' => $EXTENSION['captchas']['default']['text_to_verify'], //text to render
				'size' => 20, //font size
				'depth' => 3, //font depth/shadow
				'fonts' => array( //array of fonts to use
					'fonts/arialbd.ttf',
				),
			),
	),
	array(
		'name' => 'GotchaDotEffect',
		'args' => array(),
	),
);

// These constants are used by sample page template and point to first 
// menu items in each language.  
$EXTENSION['constants']['sticky_links']['en'] = 10029;
$EXTENSION['constants']['sticky_links']['ee'] = 25974;
