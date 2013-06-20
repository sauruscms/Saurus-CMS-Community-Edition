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
 * Retrieves file source and calls callback function with source as an argunment
 *
 * @param	file		the file, has to be sync'ed file
 */

global $class_path;

$class_path = '../classes/';

include($class_path.'port.inc.php');
include($class_path.'adminpage.inc.php');

$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1 : 0),
	'on_admin_keel' => 1,
));

$file = (string)$_GET['file'];
if(!$file)
{
	exit;
}

// get fullpath from db
$file = preg_replace('#^/#', '', $file);
$file = preg_replace('#/$#', '', $file);

//$file = $site->absolute_path.$file;
$sql = $site->db->prepare('select objekt_id, relative_path from obj_file where relative_path = ? limit 1', '/'.$file);

$result = new SQL($sql);
$result = $result->fetch('ASSOC');
$objekt_id = $result['objekt_id'];

//create the file objekt, this is for permissions
$objekt = new Objekt(array('objekt_id' => $objekt_id, ));

if($objekt->objekt_id && $objekt->all['tyyp_id'] == 21)
{
	if($file)
	{
		$file = $site->absolute_path.$file;
		
		if(file_exists($file))
		{
			if($fp = fopen($file, 'r'))
			{
				//read the contents and make callback
				$fcontent = fread($fp, filesize($file));
				fclose($fp);
				?>
				<script type="text/javascript">
					window.opener.frames[0].insert_template("<?=str_replace(array('"', "\n", "\r"), array('\"', '\n', '\r'), $fcontent);?>");
				</script>
				<?php
				exit;
			}
			else 
			{
				// could not read file
				exit;
			}
		}
		else 
		{
			// no such file in fs
			exit;
		}
	}
	else 
	{
		// no such file in db
		exit;
	}
}
else 
{
	// no permissions
	exit;
}
