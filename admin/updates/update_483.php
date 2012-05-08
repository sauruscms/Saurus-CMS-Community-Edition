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
 * @package 	SaurusCMS
 * @copyright 	2000-2010 Saurused Ltd (http://www.saurus.info/)
 * @license		Mozilla Public License 1.1 (http://www.opensource.org/licenses/mozilla1.1.php)
 * 
 */

function up_483()
{
  global $site;
  
  // init_documents can be now saved to the filesystem
  $sql = $site->db->prepare('INSERT INTO config (nimi, sisu) VALUES ("documents_directory", ?) ON DUPLICATE KEY UPDATE sisu = VALUES(sisu)', '/shared/documents');
  new SQL($sql);

  $sql = 'INSERT INTO config (nimi, sisu, kirjeldus, on_nahtav) VALUES ("documents_in_filesystem", "1", "If this is 1 and documents_directory has a value, then {init_documents} saves documents to the filesystem", "1") ON DUPLICATE KEY UPDATE sisu = VALUES(sisu), kirjeldus = VALUES(kirjeldus), on_nahtav = VALUES(on_nahtav)';
  new SQL($sql);

  $sql = 'INSERT INTO config (nimi, sisu, kirjeldus, on_nahtav) VALUES ("documents_mod_xsendfile", "1", "If this is 1 and documents_in_filesystem is 1 and documents_directory has a value and mod_xsendfile Apache mod has been installed, then mod_xsendfile will serve the files", "1") ON DUPLICATE KEY UPDATE sisu = VALUES(sisu), kirjeldus = VALUES(kirjeldus), on_nahtav = VALUES(on_nahtav)';
  new SQL($sql);
}
