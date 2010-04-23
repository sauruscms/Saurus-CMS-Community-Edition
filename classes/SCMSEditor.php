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

include_once('..'.$site->CONF['js_path'].'/fckeditor/fckeditor_php4.php');

/**
 * SCMSEditor class
 * 
 * Extends the FCKeditor, adds a new function browser() to determine the users browser
 * 
 * @package CMS
 * @version	1.0
 * @author saurus@saurus.info
 * @link	http://www.saurus.info/
 * 
 *
 */
class SCMSEditor extends FCKeditor 
{
	/**
	 * Constructor function for PHP4.
	 *
	 * @param string $instanceName
	 * @access public
	 */
	function SCMSEditor( $instanceName )
	{
		$this->FCKeditor( $instanceName ) ;
	}

	function IsCompatible()
	{
		return 1;
	}
	
	/**
	 * Function for determining the browser type
	 *
	 * @access public
	 * @return string Browser type [MSIE|Gecko|incompatible]
	 */
	function browser()
	{
		global $HTTP_USER_AGENT ;

		if ( isset( $HTTP_USER_AGENT ) )
			$sAgent = $HTTP_USER_AGENT ;
		else
			$sAgent = $_SERVER['HTTP_USER_AGENT'] ;

		if ( strpos($sAgent, 'MSIE') !== false && strpos($sAgent, 'mac') === false && strpos($sAgent, 'Opera') === false )
		{
			return 'MSIE';
		}
		else if ( strpos($sAgent, 'Gecko/') !== false )
		{
			return 'Gecko';
		}
		else
			return 'incompatible';
	}
	
}
