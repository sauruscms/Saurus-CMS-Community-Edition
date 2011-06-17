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

function up_480()
{
	new SQL("TRUNCATE TABLE `sso`");
	new SQL("TRUNCATE TABLE `kasutaja_sso`");
	new SQL("TRUNCATE TABLE `ldap_map`");
	new SQL("TRUNCATE TABLE `ldap_servers`");
	new SQL("TRUNCATE TABLE `replicator`");
	new SQL("TRUNCATE TABLE `xml`");
	new SQL("TRUNCATE TABLE `xml_dtd`");
	new SQL("TRUNCATE TABLE `xml_map`");
	
	new SQL("DELETE FROM `admin_osa` WHERE `id`='37'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='38'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='47'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='88'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='155'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='156'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='157'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='48'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='54'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='63'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='65'");
	new SQL("DELETE FROM `admin_osa` WHERE `id`='66'");
	new SQL("DELETE FROM `admin_osa` where `id`='41'");
	new SQL("DELETE FROM `admin_osa` where `id`='45'");
	new SQL("DELETE FROM `admin_osa` where `id`='36'");
	
	new SQL("UPDATE groups SET auth_type = 'CMS'");
	new SQL("UPDATE users SET autologin_ip = ''");

	new SQL('TRUNCATE TABLE `moodulid`');
	new SQL('TRUNCATE TABLE `license`');

	// Issue #25 Poll's are not working
	new SQL("INSERT INTO templ_tyyp (ttyyp_id, op, nimi, templ_fail, on_nahtav, on_auto_avanev, sst_id, on_page_templ, is_readonly, is_default) VALUES (30, 'vote', 'Poll', 'templ_poll.php', '0', 1, 0, '0', 1, 0)");
	new SQL("INSERT INTO templ_tyyp (ttyyp_id, op, nimi, templ_fail, on_nahtav, on_auto_avanev, sst_id, on_page_templ, is_readonly, is_default) VALUES (38, 'gallup_arhiiv', 'Poll archive', 'templ_pollarchive.php', '0', 1, 0, '0', 1, 0)");
}
