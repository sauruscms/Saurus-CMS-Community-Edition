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
* Saurus CMS default triggers.
* This file contains triggers depending on Saurus CMS object-related actions.
*
* NB! Don't modify this file, it may be overwritten by version upgrades. 
* For custom changes create a new file named as "actions_custom.inc.php" and use this file as a template.
* 
* @package CMS
* 
*
* Events are:
* onBeforeObjectLoad()
* onBeforeObjectSave()
* onObjectSave()
* onObjEventAccept()
* onObjEventDecline()
* onObjEventAssignToUser()
* onObjEventAssignToGroup()
* onObjEventAssignToResource()
*
*
*
* Results of the functions are passed to a global variable with the same index. Sample:
* $site->globals['onBeforeObjectSave'] = onBeforeObjectSave($myparams_array);
*  - you can access results of f-on onBeforeObjectSave() in global var $site->globals['onBeforeObjectSave']
*
*/
global $site, $objekt;
define('CUSTOM_ACTIONS_INCLUDED', 1); // it helps sometimes to optimize code in CMS.


/**
*	
* When "new/edit" button was pressed and object is not loaded into the edit-window yet. You can set default values using this event, save them into $site->fdat[''] array.
*
*/
function onBeforeObjectLoad ($params){
	global $site;

#	$obj = &$params->all;

	# set default value:
	$site->fdat['author'] = $site->user->name;

	return $result;
}



/**
*	
* When "save" button was pressed, but object is not saved yet. Last chance to retreive old object's data.
*
*/
function onBeforeObjectSave ($params){
/*
	global $site;

	$obj = &$params->all;

	return $result;
*/
}



/**
*	
* If object was realy saved (no errors)
*
*/
function onObjectSave ($params){
/*
	global $site;

	$obj = &$params->all;


	return 1;
*/
}






/**
*	
* If object 'event' was accepted
*
*/
function onObjEventAccept ($params){
/*
	global $site;

	# $params['user_id']
	# $params['event_id']


	# Object 'event'
	if ($params['event_id']){

	}

	return 1;
*/
}


/**
*	
* If object 'event' was declined
*
*/
function onObjEventDecline ($params){
/*
	global $site;

# $params['user_id']
# $params['event_id']

	# Object 'event'
	if ($params['event_id']){


	}

	return 1;
*/
}



/**
*	
* If object 'event' was assigned to user
*
*/
function onObjEventAssignToUser ($params){
/*
	global $site;

# $params['user_id']
# $params['event_id']


	return 1;
*/
}








/**
*	
* If object 'event' was assigned to group
*
*/
function onObjEventAssignToGroup ($params){
/*
	global $site;

# $params['group_id']
# $params['event_id']

	return 1;
*/
}



/**
*	
* If object 'event' was assigned to resource
*
*/
function onObjEventAssignToResource ($params){
/*
	global $site;

# $params['resource_id']
# $params['event_id']

	return 1;
*/
}




?>