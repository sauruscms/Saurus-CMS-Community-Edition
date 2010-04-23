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
 * Takes CSS rules and creates XML file for FCKeditor style dropdown menu
 * 
 */

global $site;

$class_path = '../classes/';
include_once($class_path.'port.inc.php');
include($class_path.'adminpage.inc.php');

$site = new Site(array(
	'on_debug' => 0,
	'on_admin_keel' => 1,
));

header('Content-type: text/xml');

print('<?xml version="1.0" encoding="utf-8" ?>'."\n");
print('<Styles>'."\n");

echo '<Style name="Normal" element="p" />'."\n";

$sql = "SELECT * FROM css WHERE name='wysiwyg_css'";
$sth = new SQL($sql);
$css = $sth->fetch();
$css = $css['data'];

$css = str_replace("\n", '',$css);
$css = str_replace("\r", '',$css);

$css = explode('}', $css);

foreach($css as $rule)
{
	$rule .= '}';
	if(preg_match('/(\w*)?(\s*)\.*\s*([\w|\-|\_|\d]+)?(\s*){(.*)?}/', $rule, $matches)) /* Merle 'A Good Thing(tm)' RegExp(tm) */
	{
		//printr($matches);
		$element = $matches[1];
		$classname = $matches[3];
		//$rules = $matches[5];
		
		echo '<Style name="'.($classname ? $classname : $element).'" element="'.($element ? $element : 'span').'">'."\n"; /* default element span, or should it be font for backwards comp? */
		echo ($classname ? '<Attribute name="class" value="'.$classname.'" />'."\n" : null);
		echo '</Style>'."\n";
	}
}

/* The original XML file content
<Styles>
	<Style name="Image on Left" element="img">
		<Attribute name="style" value="padding: 5px; margin-right: 5px" />
		<Attribute name="border" value="2" />
		<Attribute name="align" value="left" />
	</Style>
	<Style name="Image on Right" element="img">
		<Attribute name="style" value="padding: 5px; margin-left: 5px" />
		<Attribute name="border" value="2" />
		<Attribute name="align" value="right" />
	</Style>
	<Style name="Custom Bold" element="span">
		<Attribute name="style" value="font-weight: bold;" />
	</Style>
	<Style name="Custom Italic" element="em" />
	<Style name="Title" element="span">
		<Attribute name="class" value="Title" />
	</Style>
	<Style name="Code" element="span">
		<Attribute name="class" value="Code" />
	</Style>
	<Style name="Title H3" element="h3" />
	<Style name="Custom Ruler" element="hr">
		<Attribute name="size" value="1" />
		<Attribute name="color" value="#ff0000" />
	</Style>
</Styles>
*/

print('</Styles>'."\n");
