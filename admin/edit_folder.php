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
 * Popup page for editing folder
 *
 */

global $site, $class_path;

####################################
# FUNCTION edit_objekt

function edit_objekt()
{
	global $site;
	global $objekt;
	global $keel;
	global $class_path;
	
	echo '<input type="hidden" name="callback" value="'.$site->fdat['callback'].'">';
}
# / FUNCTION edit_objekt
####################################


####################################
# FUNCTION salvesta_objekt

function salvesta_objekt () {
	global $site;
	global $objekt;
	global $class_path;
	
    if ($objekt->objekt_id) {

		if ($objekt->on_sisu_olemas)
		{
			# -------------------------------
			# Objekti uuendamine andmebaasis    
			# -------------------------------
			
			rename_folder($site->fdat['pealkiri'], $objekt->objekt_id);
			
		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------
			
			
		}
    }
  
}
# / FUNCTION salvesta_objekt
####################################
