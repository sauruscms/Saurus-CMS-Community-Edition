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

function dump_full_database()
{

// Table structure for table `admin_osa`

new SQL("DROP TABLE IF EXISTS `admin_osa`"); echo '. '; flush();
new SQL("CREATE TABLE `admin_osa` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `parent_id` int(11) unsigned NOT NULL default '0',
  `sorteering` int(11) unsigned NOT NULL default '0',
  `nimetus` varchar(255) default NULL,
  `eng_nimetus` varchar(255) default NULL,
  `fail` varchar(255) default NULL,
  `moodul_id` int(11) unsigned NOT NULL default '0',
  `extension` varchar(100) default NULL,
  `show_in_editor` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`,`parent_id`),
  KEY `id_2` (`id`)
)"); echo '. '; flush();

// Dumping data for table `admin_osa`

new SQL("INSERT INTO `admin_osa` VALUES (2,1,40,'','Data','',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (5,1,20,'','System','',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (36,1,30,'','Integration',NULL,6,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (78,1,10,'','Help',NULL,0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (9,32,90,'','Translations','sys_sonad_loetelu.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (77,69,100,'','Files','filemanager.php',0,NULL,1)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (76,5,20,'','External tables','db_data.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (37,36,40,'','Directories','xml_dir.php',6,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (15,69,30,'','Log','log.php',0,NULL,1)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (17,32,80,'','System articles','sys_alias.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (34,1,50,'','Presentation',NULL,0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (19,1,90,'','Properties','',0,NULL,1)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (38,36,30,'','Mapping','xml_map.php',6,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (32,1,60,'','Languages','',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (33,32,100,'','Languages','keeled.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (39,34,90,'','Custom Style Sheet','custom_css.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (41,5,90,'','IP filter','ip_filter.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (42,5,70,'','Configuration','change_config.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (75,2,80,'','Profile data','profile_data.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (45,36,20,'','sso_applications','sso.php',8,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (151,19,70,'','Image manipulation','images_config.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (47,36,10,'','Replication','replication.php',10,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (48,36,9,'','LDAP','ldap.php',12,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (54,1,80,'','E-Commerce','',19,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (58,34,70,'','Content templates','content_templates.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (59,34,80,'','Page templates','page_templates.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (60,19,100,'','Site properties','change_config.php?group=1',0,NULL,1)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (65,54,50,'','Configuration','change_config.php?group=2',19,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (63,69,70,'','Link verifier','find_broken_links.php',23,NULL,1)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (62,69,80,'','Recycle Bin','trash.php',0,NULL,1)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (66,69,50,'','Search & Replace','search_replace.php',24,NULL,1)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (83,19,80,'','Permissions','permissions.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (68,5,100,'','System info','sys_info.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (69,1,100,'','Tools','',0,NULL,1)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (71,34,60,'','Object templates','object_template_map.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (73,69,90,'','People','user_management.php',0,NULL,1)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (74,2,90,'','Profiles','profiles.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (85,5,80,'','Error log','error_log.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (81,78,10,'','About','javascript:void(openpopup(\'about.php\',\'about\',\'450\',\'300\'))',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (86,1,70,'','Extensions','',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (87,86,110,'','Extensions','extensions.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (88,36,5,'','Configuration','change_config.php?group=3',6,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (150,69,60,'','Feedback forms','feedbackforms_handler.php',0,NULL,1)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (152,2,100,'','Explorer','site_explorer.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (153,19,90,'','Site Design','designs.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (155,78,20,'','Report a bug','send_feedback.php',0,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (156,1,80,NULL,'E-Commerce',NULL,35,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (157,156,50,NULL,'Configuration','change_config.php?group=2',35,NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `admin_osa` VALUES (158,78,100,NULL,'Saurus CMS homepage','http://www.saurus.info/\" target=\"_blank',0,NULL,0)"); echo '. '; flush();

// Table structure for table `allowed_mails`

new SQL("DROP TABLE IF EXISTS `allowed_mails`"); echo '. '; flush();
new SQL("CREATE TABLE `allowed_mails` (
  `id` int(11) NOT NULL auto_increment,
  `mail` varchar(255) default '0',
  `objekt_id_list` text,
  PRIMARY KEY  (`id`)
)"); echo '. '; flush();

// Dumping data for table `allowed_mails`


// Table structure for table `cache`

new SQL("DROP TABLE IF EXISTS `cache`"); echo '. '; flush();
new SQL("CREATE TABLE `cache` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `objekt_id` int(10) unsigned NOT NULL default '0',
  `aeg` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `checksum` varchar(20) NOT NULL default '',
  `requests` int(11) unsigned NOT NULL default '0',
  `sisu` longtext,
  `url` text NOT NULL,
  `user_id` bigint(20) unsigned default NULL,
  `site_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `objekt_id` (`objekt_id`),
  KEY `url` (`url`(200))
)"); echo '. '; flush();

// Dumping data for table `cache`


// Table structure for table `config`

new SQL("DROP TABLE IF EXISTS `config`"); echo '. '; flush();
new SQL("CREATE TABLE `config` (
  `nimi` varchar(255) NOT NULL default '',
  `sisu` varchar(255) default NULL,
  `kirjeldus` text,
  `on_nahtav` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`nimi`)
)"); echo '. '; flush();

// Dumping data for table `config`

new SQL("INSERT INTO `config` VALUES ('styles_path','/styles/default','Application skin','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('img_path','/px','piltide asukoht','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('js_path','/js','javascriptide asukoht','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('adm_img_path','/admin/images','administraatori osas','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('file_path','/public','failide asukoht','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('next_hour','1305285327','Millal järgmine auto_publishing  toimub','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('adm_path','/admin','admini juur','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('site_name','Saurus CMS','Website name, used on printer-friendly pages','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('hostname','www.yourdomain.com','Website domain e.g \"your.site.com\" (Caution! Affects whole website!)','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('wwwroot','','Website root URL (if any) e.g \"/mysite\" (Caution! Affects whole website!)','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('kasutaja_voti','deprecated','reg.kasutaja parooli kodeerimisel kasutatav võti','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('artiklite_arv_arhiivis','20','Number of articles on archive page in \"News\" template','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('otsingu_tulemuste_arv','20','Number of articles on search result page','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('default_mail','your@yourdomain.com','Default \"To\" e-mail address','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('from_email','Website <your@yourdomain.com>','Default \"From\" e-mail address','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('subject','Data from website','E-mail \"Subject\"','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('gallup_ip_check','2','Prevent multiple votes','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('image_width','600','Maximum size of picture in gallery (in pixels)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('thumb_width','150','Maximum size of thumbnail in gallery (in pixels)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('user_ip_filter','0','User IP access: 0 - luba k6ik, valja arvatud ... / 1 - keela k6ik, valja arvatud ...','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('admin_ip_filter','0','User IP access: 0 - luba k6ik, valja arvatud ... / 1 - keela k6ik, valja arvatud ...','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('regusers_access_enabled','0','1 - enabled / 0 - disabled','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('kasuta_ip_filter','0','0 - don\'t use; 1 - users only; 2 - admins only; 3 - both admins and users','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('only_regusers_comment','0','Only authenticated users may add comments','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('default_comments','0','Adding comments is allowed for objects by default','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('default_pass_expire_days','365','Registered users password expiration time (in days)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('komment_arv_lehel','10','Number of comments on 1 comments page','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('kommentaaride_lehekulgede_arv','10','Number of comments pages','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('users_can_register','0','Site visitors are allowed to register','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('original_picture_saved','0','Allow original file download in image gallery\r\n','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('alamartiklid_paises','0','Subarticles alignment','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('protocol','http://','Protocol of the public website','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('custom_img_path','/px_custom','custom piltide jaoks, mis ei kuulu toote komplekti','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('goto_user_details','0:1:details.php?user=[user_id]','Hidden feature: kui sisselülitatud, siis kommentaari juures ei näidata mitte autori nime ja emaili,<br> vaid kasutajanime ja linki valislehele.','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('comment_max_chars','120','Maximum length of word in the comment. Longer words will cause text wrapping.','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('next_week','1305886527','Time for next week run','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('allow_autologin_from_ip','0','Automatic login based on user IP-address','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('php_max_execution_time','1300','Maximum time a script is allowed to run (seconds)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('php_memory_limit','64','Maximum amount of memory a script is allowed to allocate (Mbytes)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_interval','1','Mailing list interval (in hour)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('next_mailinglist','1305285327','Millal järgmine auto_maillist  toimub','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('editor_intra_link','0','If \"intranet\" hyperlinks are allowed in the editor','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('enable_mailing_list','1','Enable mailing lists','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_send_newer_than','01.09.2004','Send articles that are newer than (dd.mm.yyyy)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('xml_max_filecount','100','The maximum number of XML import files that are processed at once','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('allow_change_position','0','Allow to change position for the objects','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('imagemagick_path','','Imagemagick binaries path. Fill only if Imagemagick is used for image \nmanipulation and it is not found in system path','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('cache_expired','0','Cache expires (hours). 0 = cache is not used','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('dont_cache_objects','','Cache will be skipped for ID\'s (coma separated list)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('cyr_convert_encoding','','Cyrillic conversation type. Value=(k,i,a,d,m,u) or empty','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('allow_forgot_password','1','Allow \"Forgot password\" feature','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('next_day','1305368127','Time for next day run','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('trash_expires','10','Number of days content objects are preserved in recycle bin. <br>If \"0\", objects will be deleted immediately.','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('UPOS_url','https://unet.eyp.ee/cgi-bin/ws.sh/u-pos.w','URL for connecting to the UPOS payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('HANZA_url','https://www.hanza.net/cgi-bin/hanza/pangalink.jsp','URL for connecting to the HANZA.NET payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('IPOS_url','https://unet.eyp.ee/cgi-bin/ws.sh/u-commerce.w','URL for connecting to the IPOS payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('SAMPO_url','https://www2.sampo.ee/cgi-bin/pizza','URL for connecting to the SAMPO payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('UPOS_signature','','SIGNATURE path or text for the UPOS payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('HANZA_signature','','SIGNATURE path or text for the HANZA.NET payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('IPOS_signature','','SIGNATURE path or text for the IPOS payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('SAMPO_signature','','SIGNATURE path or text for the SAMPO payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('UPOS_account','','ACCOUNT to use for the UPOS payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('HANZA_account','','ACCOUNT to use for the HANZA.NET payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('IPOS_account','','ACCOUNT to use for the IPOS payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('SAMPO_account','','ACCOUNT to use for the SAMPO payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('payment_secret_key','','Secret key to use for the payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('notification_about_new_user_enabled','0','Notify administrator about new registered users by e-mail Y/N','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('toolbar_allowed_on_print_page','0','Standard buttons allowed on the Print Page','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('start_date_of_objects_counting','01.09.2004','[dd.mm.yyyy ] Start counting from that date','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('unix_userpasswd','0','Use UNIX crypt for user login','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('eshop_key','','Key used to sign data in the shoppingcart (Not the same as the bank key!)','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('prog_path','','Path to the \'kontrolli\' and \'signeeri\' programs','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('company_name','','Company name to display when paying','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('UPOS_username','','USERNAME for the UPOS payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('HANZA_username','','USERNAME for the HANZA.NET payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('IPOS_username','','USERNAME for the IPOS payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('SAMPO_username','','USERNAME for the SAMPO payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('documents_directory','/public/documents','Documents directory','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('secure_file_path','/shared','File path for non public files','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('save_error_log','1','Save PHP and MySQL errors into the database','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('display_errors_ip','','Display errors only to these IP addresses (semicolon separated)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('add_new_user_to_mailinglists','0','Add new users to all mailinglists by default','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_sending_after_publishing','0','Run mailinglists after each publishing (otherwise run on pageload)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_format','0','Mailinglists format','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_sender_address','','Sender address','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_article_content','0','Article content','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_header','','Custom header (will be added prior articles)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_footer','','Custom footer (will be added after articles)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_subject','0','Mailinglist subject','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_article_title','0','Article title','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('maillist_reporter_address','','Send reports about mailinglist postings to (e-mail address)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('date_format','dd.mm.yyyy','System date format','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('new_user_password','','Password string for new users (default is empty)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('image_mode','gd lib','Image manipulation is handed by','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('max_login_attempts','5','Maximum number of login attempts','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('login_locked_time','20','How long user is locked after failed login attempts (in minutes)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('login_duration_time','5','Time within the login attempts are considered as one (in minutes)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('active_users','0:0','Visitors count on the site (guests:users)','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('next_counting_active_users','0','Next calculation time for the visitors count','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('users_can_delete_comment','1','Users are allowed to delete their last comment','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('next_10min','1305282327','Time for next 10 minutes interval run','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('proxy_server','','Proxy server address','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('proxy_server_port','','Proxy server port','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('transport_vat','0.18','Transport V.A.T. value in percent','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ad_server','','Active Directory server address','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ad_server_port','389','Active Directory server port','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ad_base_dn','OU=Company,DC=example,DC=com','Base DN of the AD server','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ad_readall_username','','Username of the AD user with read access to the Base DN (fill only if anonymous access is denied)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ad_readall_password','','Password of the AD user with read access to the Base DN (fill only if anonymous access is denied)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ad_create_cms_users','0','Create user if not found in CMS? (Y/N)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ad_create_cms_groups','0','Create group if not found in CMS? (Y/N)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ad_auth_users','0','Use Active Directory authentication for users (Y/N)','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('use_ntlm_auth','0','Use NTLM authentication for users? (Y/N)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('use_aliases','0','Use human friendly URLs','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('allow_commenting','1','Allow commenting','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('check_for_captcha','1','Use CAPTCHA verification for comments and forums','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('feedbackform_action','form.php','Form action','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('feedbackform_form_name','SCMSForm','Form name','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('feedbackform_method','post','Form method','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('feedbackform_check_for_captcha','0','Use CAPTCHA verification for feedback forms','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('search_result_excerpt_length','180','Search result excerpt length','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('KREP_account','','ACCOUNT to use for the KREP payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('KREP_signature','','SIGNATURE path or text for the KREP payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('KREP_url','https://i-pank.krediidipank.ee/','URL for connecting to the KREP payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('KREP_username','','USERNAME for the KREP payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('lock_inactive_user_after_x_days','0','Lock inactive user after x days','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('force_https_for_editing','0','Force HTTPS for editor environment','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('force_https_for_admin','0','Force HTTPS for admin environment','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('users_require_safe_password','0','Check for password difficulty','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('bank_connection_logfile','','The log file for Bank Connection','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('NORDEA_MAC_key','','Nordea MAC signature key','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('NORDEA_url','https://solo3.nordea.fi/cgi-bin/SOLOPM01','URL for connecting to the Nordea Solo payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('NORDEA_account','','ACCOUNT to use for the Nordea Solo payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('NORDEA_username','','USERNAME for the Nordea Solo payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ESTCARD_url','https://pos.estcard.ee/test-pos/servlet/iPAYServlet','URL for connecting to ESTCARD payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ESTCARD_signature','','SIGNATURE path or text for the ESTCARD payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('ESTCARD_id','','ID in the ESTCARD payment system','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('send_error_notifiations_to_superusers','0','Send error notifications to all superusers','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('send_error_notifiations_to','','E-mail addresses to send error notifications (comma separated)','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('send_error_notifiations_setting','0','Method for error notifications activation','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('send_error_notifiations_last_run','2008-02-24 17:27:46','Last time when error notifications were sent','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('mailinglist_sending_option','0','Mailinglist send type','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('save_site_log','1','Enable site log','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('time_zone','','GMT time zone the website is located in','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('alias_trail_format','0','Alias format','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('alias_language_format','0','Alias prefix format','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('send_feedback_email','feedback@saurus.info','E-mail address where bug, feature request and other feedback are sent','0')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('fm_allow_multiple_upload','1','Allow for multiple file uploading','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('context_menu_open_event','click','Context menu open event','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('id_card_create_cms_users','0','Create user if not found in CMS? (Y/N)','')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('id_card_default_cms_group','0','Default group for created users','')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('redirect_to_alias','1','Redirect links containing object ID to object alias','1')"); echo '. '; flush();
new SQL("INSERT INTO `config` VALUES ('replace_links_with_alias','0','Replace local links in content with their respective aliases','1')"); echo '. '; flush();

// Table structure for table `config_images`

new SQL("DROP TABLE IF EXISTS `config_images`"); echo '. '; flush();
new SQL("CREATE TABLE `config_images` (
  `definition_id` int(10) unsigned NOT NULL auto_increment,
  `name` text,
  `value` text,
  PRIMARY KEY  (`definition_id`)
)"); echo '. '; flush();

// Dumping data for table `config_images`

new SQL("INSERT INTO `config_images` VALUES (2,'Content width','520')"); echo '. '; flush();
new SQL("INSERT INTO `config_images` VALUES (3,'Half of content width','260')"); echo '. '; flush();

// Table structure for table `css`

new SQL("DROP TABLE IF EXISTS `css`"); echo '. '; flush();
new SQL("CREATE TABLE `css` (
  `css_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `data` blob,
  `is_active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`css_id`),
  UNIQUE KEY `name` (`name`)
)"); echo '. '; flush();

// Dumping data for table `css`

new SQL("INSERT INTO `css` VALUES (1,'custom_css','@import url(extensions/saurus4/css/main.css);\r\n@import url(extensions/saurus4/css/content.css);\r\n\r\nbody, td {\r\n	font-family: \"Lucida Sans Unicode\", Verdana, sans-serif;\r\n	font-size: 13px;\r\n	color: #495B76;\r\n}\r\n\r\na {\r\n	color: #85A5EF;\r\n}\r\n\r\nh1 {\r\n	margin: 0px;\r\n	padding-top: 10px;\r\n	font-size: 24px;\r\n	font-weight: normal;\r\n}\r\n\r\nh2 {\r\n	margin: 0px;\r\n	padding-top: 10px;\r\n	font-size: 20px;\r\n	font-weight: normal;\r\n}\r\n\r\nh3 {\r\n	margin: 0px;\r\n	padding-top: 10px;\r\n	font-size: 16px;\r\n	font-weight: normal;\r\n}\r\n\r\np.qoute {\r\n	margin: 10px 0px 10px 0px;\r\n	padding-left: 15px;\r\n	font-style: italic;\r\n	border-left: 3px solid #85A5EF;\r\n}\r\n\r\nspan.Date {\r\n	font-size: 11px;\r\n	color: #85A5EF;\r\n}\r\n',1)"); echo '. '; flush();
new SQL("INSERT INTO `css` VALUES (2,'wysiwyg_css','h1 {\r\n	margin: 0px;\r\n	padding-top: 10px;\r\n	font-size: 24px !important;\r\n	font-weight: normal;\r\n}\r\n\r\nh2 {\r\n	margin: 0px;\r\n	padding-top: 10px;\r\n	font-size: 20px !important;\r\n	font-weight: normal;\r\n}\r\n\r\nh3 {\r\n	margin: 0px;\r\n	padding-top: 10px;\r\n	font-size: 16px !important;\r\n	font-weight: normal;\r\n} \r\n\r\np.qoute {\r\n	margin: 10px 0px 10px 0px;\r\n	padding-left: 15px;\r\n	font-style: italic;\r\n	border-left: 3px solid #85A5EF;\r\n}\r\n\r\npre {\r\n}\r\n\r\nspan.Date {\r\n	font-size: 11px;\r\n	color: #85A5EF;\r\n}\r\n',1)"); echo '. '; flush();
new SQL("INSERT INTO `css` VALUES (3,'wysiwyg_css_general','body, td {\r\n	padding: 5px 5px 5px 5px;\r\n	margin: 0px;\r\n	background-color: #ffffff;\r\n	font-family: \"Lucida Sans Unicode\", Verdana, sans-serif;\r\n	font-size: 13px;\r\n	color: #495B76;\r\n}\r\n\r\na {\r\n	color: #85A5EF !important;\r\n}\r\n',1)"); echo '. '; flush();

// Table structure for table `document_parts`

new SQL("DROP TABLE IF EXISTS `document_parts`"); echo '. '; flush();
new SQL("CREATE TABLE `document_parts` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  `content` longblob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_objekt_id` (`objekt_id`,`id`)
)"); echo '. '; flush();

// Dumping data for table `document_parts`


// Table structure for table `error_log`

new SQL("DROP TABLE IF EXISTS `error_log`"); echo '. '; flush();
new SQL("CREATE TABLE `error_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time_of_error` datetime NOT NULL default '0000-00-00 00:00:00',
  `source` text NOT NULL,
  `err_text` text NOT NULL,
  `err_type` enum('PHP','SQL','CMS') NOT NULL default 'CMS',
  `domain` varchar(255) NOT NULL default '',
  `referrer` varchar(255) NOT NULL default '',
  `fdat_scope` text NOT NULL,
  `ip` varchar(15) NOT NULL default '',
  `remote_user` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `err_type` (`err_type`)
)"); echo '. '; flush();

// Dumping data for table `error_log`


// Table structure for table `ext_country`

new SQL("DROP TABLE IF EXISTS `ext_country`"); echo '. '; flush();
new SQL("CREATE TABLE `ext_country` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `profile_id` int(4) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `profile_id` (`profile_id`)
)"); echo '. '; flush();

// Dumping data for table `ext_country`

new SQL("INSERT INTO `ext_country` VALUES (2,135,'Afghanistan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (3,135,'Albania')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (4,135,'Algeria')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (5,135,'American Samoa')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (6,135,'Andorra')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (7,135,'Angola')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (8,135,'Anguilla')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (9,135,'Antartica')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (10,135,'Antigua and Barbuda')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (11,135,'Argentina')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (12,135,'Armenia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (13,135,'Aruba')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (14,135,'Ascension Island')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (15,135,'Australia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (16,135,'Azerbaijan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (17,135,'Bahamas')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (18,135,'Bahrain')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (19,135,'Bangladesh')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (20,135,'Barbados')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (21,135,'Belarus')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (22,135,'Belgium')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (23,135,'Belize')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (24,135,'Benin')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (25,135,'Bermuda')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (26,135,'Bhutan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (27,135,'Bolivia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (28,135,'Bosnia and Herzegovina')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (29,135,'Botswana')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (30,135,'Bouvet Island')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (31,135,'Brazil')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (32,135,'British Indian Ocean Territory')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (33,135,'Brunei Darussalam')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (34,135,'Bulgaria')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (35,135,'Burkina Faso')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (36,135,'Burundi')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (37,135,'Cambodia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (38,135,'Cameroon')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (39,135,'Canada')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (40,135,'Cap Verde')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (41,135,'Cayman Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (42,135,'Central African Republic')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (43,135,'Chad')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (44,135,'Chile')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (45,135,'China')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (46,135,'Christmas Island')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (47,135,'Cocos (Keeling) Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (48,135,'Colombia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (49,135,'Comoros')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (50,135,'Croatia/Hrvatska')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (51,135,'Cuba')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (52,135,'Cyprus')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (53,135,'Czech Republic')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (54,135,'Denmark')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (55,135,'Djibouti')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (56,135,'Dominica')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (57,135,'Dominican Republic')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (58,135,'East Timor')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (59,135,'Ecuador')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (60,135,'Egypt')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (61,135,'El Salvador')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (62,135,'Equatorial Guinea')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (63,135,'Eritrea')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (64,135,'Estonia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (65,135,'Ethiopia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (66,135,'Falkland Islands (Malvina)')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (67,135,'Faroe Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (68,135,'Fiji')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (69,135,'Finland')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (70,135,'France')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (71,135,'French Guiana')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (72,135,'French Polynesia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (73,135,'French Southern Territories')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (74,135,'Gabon')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (75,135,'Gambia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (76,135,'Georgia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (77,135,'Germany')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (78,135,'Ghana')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (79,135,'Gibraltar')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (80,135,'Greece')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (81,135,'Greenland')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (82,135,'Grenada')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (83,135,'Guadeloupe')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (84,135,'Guam')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (85,135,'Guatemala')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (86,135,'Guernsey')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (87,135,'Guinea')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (88,135,'Guinea-Bissau')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (89,135,'Guyana')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (90,135,'Haiti')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (91,135,'Heard and McDonald Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (92,135,'Holy See (City Vatican State)')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (93,135,'Honduras')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (94,135,'Hong Kong')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (95,135,'Hungary')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (96,135,'Iceland')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (97,135,'India')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (98,135,'Indonesia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (99,135,'Iran (Islamic Republic of)')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (100,135,'Iraq')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (101,135,'Ireland')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (102,135,'Isle of Man')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (103,135,'Israel')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (104,135,'Italy')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (105,135,'Jamaica')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (106,135,'Japan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (107,135,'Jersey')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (108,135,'Jordan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (109,135,'Kazakhstan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (110,135,'Kenya')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (111,135,'Kiribati')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (112,135,'Latvia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (113,135,'Lebanon')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (114,135,'Lesotho')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (115,135,'Liberia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (116,135,'Libyan Arab Jamahiriya')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (117,135,'Liechtenstein')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (118,135,'Lithuania')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (119,135,'Luxembourg')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (120,135,'Macau')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (121,135,'Macedonia, Former Yugoslav Republic')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (122,135,'Madagascar')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (123,135,'Malawi')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (124,135,'Malaysia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (125,135,'Maldives')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (126,135,'Mali')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (127,135,'Malta')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (128,135,'Marshall Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (129,135,'Martinique')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (130,135,'Mauritania')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (131,135,'Mauritius')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (132,135,'Mayotte')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (133,135,'Mexico')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (134,135,'Micronesia, Federal State of')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (135,135,'Moldova, Republic of')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (136,135,'Monaco')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (137,135,'Mongolia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (138,135,'Montserrat')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (139,135,'Morocco')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (140,135,'Mozambique')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (141,135,'Myanmar')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (142,135,'Namibia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (143,135,'Nauru')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (144,135,'Nepal')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (145,135,'Netherlands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (146,135,'Netherlands Antilles')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (147,135,'New Caledonia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (148,135,'New Zealand')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (149,135,'Nicaragua')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (150,135,'Niger')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (151,135,'Nigeria')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (152,135,'Niue')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (153,135,'Norfolk Island')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (154,135,'Northern Mariana Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (155,135,'Norway')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (156,135,'Oman')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (157,135,'Pakistan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (158,135,'Palau')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (159,135,'Panama')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (160,135,'Papua New Guinea')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (161,135,'Paraguay')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (162,135,'Peru')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (163,135,'Philippines')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (164,135,'Pitcairn Island')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (165,135,'Poland')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (166,135,'Portugal')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (167,135,'Puerto Rico')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (168,135,'Qatar')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (169,135,'Reunion Island')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (170,135,'Romania')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (171,135,'Russian Federation')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (172,135,'Rwanda')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (173,135,'Saint Kitts and Nevis')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (174,135,'Saint Lucia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (175,135,'Saint Vincent and the Grenadines')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (176,135,'San Marino')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (177,135,'Sao Tome and Principe')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (178,135,'Saudi Arabia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (179,135,'Senegal')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (180,135,'Seychelles')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (181,135,'Sierra Leone')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (182,135,'Singapore')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (183,135,'Slovak Republic')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (184,135,'Slovenia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (185,135,'Solomon Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (186,135,'Somalia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (187,135,'South Africa')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (188,135,'South Georgia and the South Sandwich Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (189,135,'Spain')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (190,135,'Sri Lanka')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (191,135,'St. Helena')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (192,135,'St. Pierre and Miquelon')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (193,135,'Sudan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (194,135,'Suriname')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (195,135,'Svalbard and Jan Mayen Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (196,135,'Swaziland')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (197,135,'Sweden')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (198,135,'Switzerland')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (199,135,'Syrian Arab Republic')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (200,135,'Taiwan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (201,135,'Tajikistan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (202,135,'Tanzania')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (203,135,'Thailand')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (204,135,'Togo')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (205,135,'Tokelau')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (206,135,'Tonga')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (207,135,'Trinidad and Tobago')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (208,135,'Tunisia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (209,135,'Turkey')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (210,135,'Turkmenistan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (211,135,'Turks and Ciacos Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (212,135,'Tuvalu')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (213,135,'Uganda')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (214,135,'Ukraine')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (215,135,'United Arab Emirates')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (216,135,'United Kingdom')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (217,135,'United Kingdom')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (218,135,'United States')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (219,135,'Uruguay')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (220,135,'US Minor Outlying Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (221,135,'Uzbekistan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (222,135,'Vanuatu')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (223,135,'Venezuela')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (224,135,'Vietnam')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (225,135,'Virgin Islands (British)')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (226,135,'Virgin Islands (USA)')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (227,135,'Wallis and Futuna Islands')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (228,135,'Western Sahara')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (229,135,'Western Samoa')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (230,135,'Yemen')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (231,135,'Yugoslavia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (232,135,'Zaire')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (233,135,'Zambia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_country` VALUES (234,135,'Zimbabwe')"); echo '. '; flush();

// Table structure for table `ext_timezones`

new SQL("DROP TABLE IF EXISTS `ext_timezones`"); echo '. '; flush();
new SQL("CREATE TABLE `ext_timezones` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `profile_id` int(4) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  `UTC_dif` float(7,2) default NULL,
  `php_variable` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
)"); echo '. '; flush();

// Dumping data for table `ext_timezones`

new SQL("INSERT INTO `ext_timezones` VALUES (1,138,'(GMT -11) Midway Island,Samoa',-11.00,'Pacific/Midway')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (2,138,'(GMT -10) Hawaii',-10.00,'Pacific/Honolulu')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (3,138,'(GMT -9) Alaska',-9.00,'America/Adak')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (4,138,'(GMT -8) Pacific Time (US & Canada),Tijuana',-8.00,'America/Tijuana')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (5,138,'(GMT -7) Arizona',-7.00,'America/Phoenix')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (6,138,'(GMT -7) Chihuahua, La Paz, Mazatlan',-7.00,'America/Mazatlan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (7,138,'(GMT -7) Mountain Time (US & Canada)',-7.00,'America/Dawson_Creek')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (8,138,'(GMT -6) Central America',-6.00,'America/Mexico_City')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (9,138,'(GMT -6) Central Time (US & Canada)',-6.00,'America/Regina')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (10,138,'(GMT -6) Guadalajara, Mexico City, Monterrey',-6.00,'America/Mexico_City')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (11,138,'(GMT -5) Bogota, Lime, Quito',-5.00,'America/Bogota')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (12,138,'(GMT -5) Eastern Time (US & Canada)',-5.00,'America/Indianapolis')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (13,138,'(GMT -5) Indiana (East)',-5.00,'America/Indianapolis')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (14,138,'(GMT -4) Atlantic Time (Canada)',-4.00,'America/Halifax')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (15,138,'(GMT -4) Caracas, La Paz',-4.00,'America/Caracas')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (16,138,'(GMT -4) Santiago',-4.00,'America/Santiago')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (17,138,'(GMT -3:30) Newfoundland',-3.50,'America/St_Johns')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (18,138,'(GMT -3) Buenos Aires, Georgetown',-3.00,'America/Buenos_Aires')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (19,138,'(GMT -3) Greenland',-3.00,'America/Thule')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (20,138,'(GMT -2) Mid-Atlantic',-2.00,'Atlantic/South_Georgia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (21,138,'(GMT -1) Azores',-1.00,'Atlantic/Azores')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (22,138,'(GMT -1) Cape Verde Is.',-1.00,'Atlantic/Cape_Verde')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (23,138,'(GMT) Casablanca, Monrovia',0.00,'Africa/Monrovia')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (24,138,'(GMT) Greenwich Mean Time - Dublin, Edinburgh, Lisbon, London',0.00,'Europe/London')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (25,138,'(GMT +1) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna',1.00,'Europe/Vienna')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (26,138,'(GMT +1) Belgrade, Bratislava, Budapest, Ljubljana, Prague',1.00,'Europe/Prague')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (27,138,'(GMT +1) Brussels, Copenhagen, Madrid, Paris',1.00,'Europe/Paris')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (28,138,'(GMT +1) Sarajevo, Skopje, Warsaw, Zagreb',1.00,'Europe/Zagreb')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (29,138,'(GMT +1) West Central Africa',1.00,'Africa/Kinshasa')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (30,138,'(GMT +2) Athens, Istanbul, Minsk',2.00,'Europe/Minsk')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (31,138,'(GMT +2) Bucharest',2.00,'Europe/Bucharest')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (32,138,'(GMT +2) Cairo',2.00,'Africa/Cairo')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (33,138,'(GMT +2) Harare, Pretoria',2.00,'Africa/Harare')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (34,138,'(GMT +2) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius',2.00,'Europe/Tallinn')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (35,138,'(GMT +2) Jerusalem',2.00,'Asia/Jerusalem')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (36,138,'(GMT +3) Baghdad',3.00,'Asia/Baghdad')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (37,138,'(GMT +3) Kuwait, Riyadh',3.00,'Asia/Kuwait')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (38,138,'(GMT +3) Moscow, St. Petersburg, Volgograd',3.00,'Europe/Moscow')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (39,138,'(GMT +3) Nairobi',3.00,'Africa/Nairobi')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (40,138,'(GMT +3:30) Tehran',3.50,'Asia/Tehran')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (41,138,'(GMT +4) Abu Dhabi, Muscat',4.00,'Asia/Muscat')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (42,138,'(GMT +4) Baku, Tbilisi, Yerevan',4.00,'Asia/Tbilisi')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (43,138,'(GMT +4:30) Kabul',4.00,'Asia/Kabul')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (44,138,'(GMT +5) Ekaterinburg',5.00,'Asia/Yekaterinburg')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (45,138,'(GMT +5) Islamabad, Karachi, Tashkent',5.00,'Asia/Karachi')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (46,138,'(GMT +5:30) Chennai, Kolkata, Mumbai, New Delhi',5.50,'Asia/Calcutta')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (47,138,'(GMT +5:45) Kathmandu',5.75,'Asia/Katmandu')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (48,138,'(GMT +6) Almaty, Novosibirsk',6.00,'Asia/Novosibirsk')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (49,138,'(GMT +6) Astana, Dhaka',6.00,'Asia/Dhaka')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (50,138,'(GMT +6:30) Rangoon',6.50,'Asia/Rangoon')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (51,138,'(GMT +7) Bangkok, Hanoi, Jakarta',7.00,'Asia/Bangkok')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (52,138,'(GMT +7) Krasnoyarsk',7.00,'Asia/Krasnoyarsk')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (53,138,'(GMT +8) Beijing, Chongging, Hong Kong, Urumgi',8.00,'Asia/Hong_Kong')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (54,138,'(GMT +8) Irkutsk, Ulaan Bataar',8.00,'Asia/Irkutsk')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (55,138,'(GMT +8) Kuala Lumpur, Singapore',8.00,'Asia/Kuala_Lumpur')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (56,138,'(GMT +8) Perth',8.00,'Australia/Perth')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (57,138,'(GMT +8) Taipei',8.00,'Asia/Taipei')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (58,138,'(GMT +9) Osaka, Sapporo, Tokyo',9.00,'Asia/Tokyo')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (59,138,'(GMT +9) Seoul',9.00,'Asia/Seoul')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (60,138,'(GMT +9) Yakutsk',9.00,'Asia/Yakutsk')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (61,138,'(GMT +9:30) Adelaide',9.50,'Australia/Adelaide')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (62,138,'(GMT +9:30) Darwin',9.50,'Australia/Darwin')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (63,138,'(GMT +10) Brisbane',10.00,'Australia/Brisbane')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (64,138,'(GMT +10) Canberra, Melbourne, Sydney',10.00,'Australia/Sydney')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (65,138,'(GMT +10) Guam, Port Moresby',10.00,'Pacific/Guam')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (66,138,'(GMT +10) Hobart',10.00,'Australia/Hobart')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (67,138,'(GMT +10) Vladivostok',10.00,'Asia/Vladivostok')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (68,138,'(GMT +11) Magadan, Solomon Is., New Caledonia',11.00,'Asia/Magadan')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (69,138,'(GMT +12) Auckland, Wellington',12.00,'Pacific/Auckland')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (70,138,'(GMT +12) Figi, Kamchatka, Marshall Is.',12.00,'Asia/Kamchatka')"); echo '. '; flush();
new SQL("INSERT INTO `ext_timezones` VALUES (71,138,'(GMT +13) Nuku\'alofa',13.00,'Pacific/Enderbury')"); echo '. '; flush();

// Table structure for table `extensions`

new SQL("DROP TABLE IF EXISTS `extensions`"); echo '. '; flush();
new SQL("CREATE TABLE `extensions` (
  `extension_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `title` varchar(255) default NULL,
  `description` text,
  `path` varchar(255) default NULL,
  `author` varchar(255) default NULL,
  `icon_path` varchar(255) default NULL,
  `version` varchar(15) default NULL,
  `version_date` date NOT NULL default '0000-00-00',
  `min_saurus_version` varchar(15) default NULL,
  `min_saurus_modules` varchar(255) default NULL,
  `is_official` tinyint(1) unsigned NOT NULL default '1',
  `is_active` tinyint(1) unsigned NOT NULL default '0',
  `is_downloadable` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`extension_id`),
  UNIQUE KEY `name` (`name`)
)"); echo '. '; flush();

// Dumping data for table `extensions`

new SQL("INSERT INTO `extensions` VALUES (1,'saurus4','Saurus 4','These templates ship with Saurus CMS 4 installation.','extensions/saurus4/','Saurus <a href=\"http://www.saurus.info\" target=\"_blank\">www.saurus.info</a>','logo.gif','1.8','2009-11-19','4.6.4','',1,0,1)"); echo '. '; flush();
new SQL("INSERT INTO `extensions` VALUES (4,'sample','Sample Extension','For explaining how stuff works','extensions/sample/','Saurus <a href=\"http://www.saurus.info\" target=\"_blank\">www.saurus.info</a>','logo.gif','1.0','2006-05-18','4.2.0','',1,0,0)"); echo '. '; flush();

// Table structure for table `favorites`

new SQL("DROP TABLE IF EXISTS `favorites`"); echo '. '; flush();
new SQL("CREATE TABLE `favorites` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `fav_objekt_id` bigint(20) unsigned default NULL,
  `fav_user` bigint(20) unsigned default NULL,
  `fav_group` bigint(20) unsigned default NULL,
  `is_selected` tinyint(1) unsigned default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
)"); echo '. '; flush();

// Dumping data for table `favorites`


// Table structure for table `forms`

new SQL("DROP TABLE IF EXISTS `forms`"); echo '. '; flush();
new SQL("CREATE TABLE `forms` (
  `form_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `profile_id` int(10) unsigned default '0',
  `source_table` varchar(50) default NULL,
  PRIMARY KEY  (`form_id`)
)"); echo '. '; flush();

// Dumping data for table `forms`


// Table structure for table `gallup_ip`

new SQL("DROP TABLE IF EXISTS `gallup_ip`"); echo '. '; flush();
new SQL("CREATE TABLE `gallup_ip` (
  `gi_id` bigint(21) NOT NULL auto_increment,
  `objekt_id` bigint(20) NOT NULL default '0',
  `ip` char(15) default NULL,
  `user_id` bigint(20) NOT NULL default '0',
  `vote_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `gv_id` bigint(21) unsigned NOT NULL default '0',
  PRIMARY KEY  (`gi_id`),
  KEY `gallup_id` (`objekt_id`)
)"); echo '. '; flush();

// Dumping data for table `gallup_ip`


// Table structure for table `gallup_vastus`

new SQL("DROP TABLE IF EXISTS `gallup_vastus`"); echo '. '; flush();
new SQL("CREATE TABLE `gallup_vastus` (
  `gv_id` bigint(21) NOT NULL auto_increment,
  `objekt_id` bigint(20) NOT NULL default '0',
  `vastus` varchar(255) NOT NULL default '',
  `count` int(10) default '0',
  PRIMARY KEY  (`gv_id`),
  KEY `gallup_id` (`objekt_id`)
)"); echo '. '; flush();

// Dumping data for table `gallup_vastus`

new SQL("INSERT INTO `gallup_vastus` VALUES (1,10350,'Sed mollis dui vel',11)"); echo '. '; flush();
new SQL("INSERT INTO `gallup_vastus` VALUES (2,10350,'Nam blandit neque',7)"); echo '. '; flush();
new SQL("INSERT INTO `gallup_vastus` VALUES (3,10350,'Morbi metus',2)"); echo '. '; flush();
new SQL("INSERT INTO `gallup_vastus` VALUES (4,10350,'Aenean congue',1)"); echo '. '; flush();
new SQL("INSERT INTO `gallup_vastus` VALUES (5,10350,'Nunc commodo',14)"); echo '. '; flush();
new SQL("INSERT INTO `gallup_vastus` VALUES (6,10351,'Sed mollis dui vel',21)"); echo '. '; flush();
new SQL("INSERT INTO `gallup_vastus` VALUES (7,10351,'Nam blandit neque et',9)"); echo '. '; flush();
new SQL("INSERT INTO `gallup_vastus` VALUES (8,10351,'Morbi metus',3)"); echo '. '; flush();
new SQL("INSERT INTO `gallup_vastus` VALUES (9,10351,'Aenean congue',4)"); echo '. '; flush();
new SQL("INSERT INTO `gallup_vastus` VALUES (10,10351,'Nunc commodo',0)"); echo '. '; flush();

// Table structure for table `groups`

new SQL("DROP TABLE IF EXISTS `groups`"); echo '. '; flush();
new SQL("CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `parent_group_id` int(11) NOT NULL default '0',
  `is_predefined` char(1) default '0',
  `description` varchar(255) default NULL,
  `auth_type` enum('CMS','LDAP','AD') NOT NULL default 'CMS',
  `auth_params` varchar(100) default NULL,
  `profile_id` int(4) unsigned default NULL,
  `email` varchar(255) default NULL,
  `phone` varchar(255) default NULL,
  `address` text,
  `website` varchar(255) default NULL,
  `notes` text,
  PRIMARY KEY  (`group_id`),
  KEY `parent_group_id` (`parent_group_id`),
  KEY `profile_id` (`profile_id`),
  KEY `name` (`name`)
)"); echo '. '; flush();

// Dumping data for table `groups`

new SQL("INSERT INTO `groups` VALUES (1,'Everybody',0,'1','','CMS',NULL,0,NULL,NULL,NULL,NULL,NULL)"); echo '. '; flush();

// Table structure for table `ip_filter`

new SQL("DROP TABLE IF EXISTS `ip_filter`"); echo '. '; flush();
new SQL("CREATE TABLE `ip_filter` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ip` varchar(15) NOT NULL default '127.0.0.1',
  `type` enum('user','admin') NOT NULL default 'user',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`),
  KEY `ip` (`ip`,`type`)
)"); echo '. '; flush();

// Dumping data for table `ip_filter`


// Table structure for table `kasutaja_sso`

new SQL("DROP TABLE IF EXISTS `kasutaja_sso`"); echo '. '; flush();
new SQL("CREATE TABLE `kasutaja_sso` (
  `kasutaja_id` bigint(20) unsigned NOT NULL default '0',
  `sso_id` int(3) unsigned NOT NULL default '0',
  `kgrupp_id` int(3) unsigned NOT NULL default '0',
  `user_value` varchar(100) default NULL,
  `pwd_value` varchar(100) default NULL,
  UNIQUE KEY `kasutaja` (`kasutaja_id`,`sso_id`,`kgrupp_id`)
)"); echo '. '; flush();

// Dumping data for table `kasutaja_sso`


// Table structure for table `keel`

new SQL("DROP TABLE IF EXISTS `keel`"); echo '. '; flush();
new SQL("CREATE TABLE `keel` (
  `keel_id` int(10) unsigned NOT NULL auto_increment,
  `nimi` varchar(32) NOT NULL default '',
  `encoding` varchar(255) default NULL,
  `glossary_id` int(11) unsigned NOT NULL default '0',
  `extension` varchar(255) default NULL,
  `on_default` tinyint(1) unsigned NOT NULL default '0',
  `on_default_admin` tinyint(1) unsigned NOT NULL default '0',
  `on_kasutusel` tinyint(1) unsigned NOT NULL default '0',
  `site_url` varchar(100) default NULL,
  `page_ttyyp_id` int(11) unsigned NOT NULL default '0',
  `ttyyp_id` int(10) unsigned NOT NULL default '0',
  `locale` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`keel_id`),
  KEY `on_kasutusel` (`on_kasutusel`),
  KEY `nimi` (`nimi`)
)"); echo '. '; flush();

// Dumping data for table `keel`

new SQL("INSERT INTO `keel` VALUES (1,'English','UTF-8',1,'en',1,1,1,'',1060,1040,'en_GB')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (2,'Russian','UTF-8',2,'',0,0,0,'',0,0,'ru_RU')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (3,'Finnish','UTF-8',3,'',0,0,0,'',0,0,'fi_FI')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (4,'Afan (Oromo)','UTF-8',4,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (5,'Abkhazian','UTF-8',5,'',0,0,0,'',0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (6,'Afar','UTF-8',6,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (7,'Afrikaans','UTF-8',7,'',0,0,0,NULL,0,0,'af_ZA')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (8,'Albanian','UTF-8',8,'',0,0,0,NULL,0,0,'sq_AL')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (9,'Algerian darja','UTF-8',9,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (10,'Amharic','UTF-8',10,'',0,0,0,NULL,0,0,'am_ET')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (11,'Arabic','UTF-8',11,'',0,0,0,NULL,0,0,'ar_SA')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (12,'Armenian','UTF-8',12,'',0,0,0,NULL,0,0,'hy_AM')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (13,'Assamese','UTF-8',13,'',0,0,0,NULL,0,0,'as_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (14,'Asturian','UTF-8',14,'',0,0,0,NULL,0,0,'ast_ES')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (15,'Aymara','UTF-8',15,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (16,'Azerbaijani','UTF-8',16,'',0,0,0,NULL,0,0,'az_AZ')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (17,'Bantu','UTF-8',17,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (18,'Bashkir','UTF-8',18,'',0,0,0,NULL,0,0,'ba_RU')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (19,'Basque','UTF-8',19,'',0,0,0,NULL,0,0,'eu_ES')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (20,'Bengali (Bangla)','UTF-8',20,'',0,0,0,NULL,0,0,'bn_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (21,'Bhutani','UTF-8',21,'',0,0,0,NULL,0,0,'bo_BT')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (22,'Bihari','UTF-8',22,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (23,'Bislama','UTF-8',23,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (24,'Bosniac','UTF-8',24,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (25,'Braille','UTF-8',25,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (26,'Brazilian portuguese','UTF-8',26,'',0,0,0,'',0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (27,'Breton','UTF-8',27,'',0,0,0,NULL,0,0,'br_FR')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (28,'Bulgarian','UTF-8',28,'',0,0,0,NULL,0,0,'bg_BG')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (29,'Burmese','UTF-8',29,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (30,'Byelorussian','UTF-8',30,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (31,'Cambodian','UTF-8',31,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (32,'Camuno','UTF-8',32,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (33,'Canadian french','UTF-8',33,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (34,'Catalan','UTF-8',34,'',0,0,0,NULL,0,0,'ca_ES')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (35,'Chinese','UTF-8',35,'',0,0,0,NULL,0,0,'zh_CN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (36,'Chinese of taiwan','UTF-8',36,'',0,0,0,NULL,0,0,'zh_TW')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (37,'Corsican','UTF-8',37,'',0,0,0,NULL,0,0,'co_FR')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (38,'Cree','UTF-8',38,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (39,'Croatian','UTF-8',39,'',0,0,0,NULL,0,0,'hr_HR')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (40,'Czech','UTF-8',40,'',0,0,0,NULL,0,0,'cs_CZ')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (41,'Danish','UTF-8',41,'',0,0,0,NULL,0,0,'da_DK')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (42,'Dioula','UTF-8',42,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (43,'Dutch','UTF-8',43,'',0,0,0,NULL,0,0,'nl_NL')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (44,'Esperanto','UTF-8',44,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (45,'Faeroese','UTF-8',45,'',0,0,0,NULL,0,0,'fo_FO')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (46,'Fiji','UTF-8',46,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (47,'French','UTF-8',47,'',0,0,0,NULL,0,0,'fr_FR')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (48,'Frisian','UTF-8',48,'',0,0,0,NULL,0,0,'fy_NL')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (49,'Furlan','UTF-8',49,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (50,'Galician','UTF-8',50,'',0,0,0,NULL,0,0,'gl_ES')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (51,'Georgian','UTF-8',51,'',0,0,0,NULL,0,0,'ka_GE')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (52,'German','UTF-8',52,'',0,0,0,NULL,0,0,'de_DE')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (53,'Greek','UTF-8',53,'',0,0,0,NULL,0,0,'el_GR')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (54,'Greenlandic','UTF-8',54,'',0,0,0,NULL,0,0,'kl_GL')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (55,'Guarani','UTF-8',55,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (56,'Gujarati','UTF-8',56,'',0,0,0,NULL,0,0,'gu_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (57,'Hausa','UTF-8',57,'',0,0,0,NULL,0,0,'ha_NG')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (58,'Hebrew','UTF-8',58,'',0,0,0,NULL,0,0,'he_IL')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (59,'Hindi','UTF-8',59,'',0,0,0,NULL,0,0,'hi_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (60,'Hungarian','UTF-8',60,'',0,0,0,NULL,0,0,'hu_HU')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (61,'Icelandic','UTF-8',61,'',0,0,0,NULL,0,0,'is_IS')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (62,'Iluko','UTF-8',62,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (63,'Indonesian','UTF-8',63,'',0,0,0,NULL,0,0,'id_ID')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (64,'Interlingue','UTF-8',64,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (65,'Inupiak','UTF-8',65,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (66,'Irish','UTF-8',66,'',0,0,0,NULL,0,0,'ga_IE')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (67,'Italian','UTF-8',67,'',0,0,0,NULL,0,0,'it_IT')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (68,'Japanese','UTF-8',68,'',0,0,0,NULL,0,0,'ja_JP')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (69,'Javanese','UTF-8',69,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (70,'Kannada','UTF-8',70,'',0,0,0,NULL,0,0,'kn_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (71,'Kashmiri','UTF-8',71,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (72,'Kawesqar (Alacalufe)','UTF-8',72,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (73,'Kazakh','UTF-8',73,'',0,0,0,NULL,0,0,'kk_KZ')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (74,'Kinyarwanda','UTF-8',74,'',0,0,0,NULL,0,0,'rw_RW')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (75,'Kirghiz','UTF-8',75,'',0,0,0,NULL,0,0,'ky_KG')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (76,'Kirundi','UTF-8',76,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (77,'Korean','UTF-8',77,'',0,0,0,NULL,0,0,'ko_KR')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (78,'Kurdish','UTF-8',78,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (79,'Laothian','UTF-8',79,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (80,'Latin','UTF-8',80,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (81,'Latvian (Lettish)','UTF-8',81,'',0,0,0,NULL,0,0,'lv_LV')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (82,'Lingala','UTF-8',82,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (83,'Lithuanian','UTF-8',83,'',0,0,0,NULL,0,0,'lt_LT')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (84,'Macedonian','UTF-8',84,'',0,0,0,NULL,0,0,'mk_MK')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (85,'Malagasy','UTF-8',85,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (86,'Malay','UTF-8',86,'',0,0,0,NULL,0,0,'ms_MY')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (87,'Malayalam','UTF-8',87,'',0,0,0,NULL,0,0,'ml_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (88,'Maltese','UTF-8',88,'',0,0,0,NULL,0,0,'mt_MT')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (89,'Maori','UTF-8',89,'',0,0,0,NULL,0,0,'mi_NZ')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (90,'Mapudungun','UTF-8',90,'',0,0,0,'',1051,0,'arn_CL')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (91,'Marathi','UTF-8',91,'',0,0,0,NULL,0,0,'mr_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (92,'Maya','UTF-8',92,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (93,'Mayangna','UTF-8',93,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (94,'Miskitu','UTF-8',94,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (95,'Moldavian','UTF-8',95,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (96,'Mongolian','UTF-8',96,'',0,0,0,NULL,0,0,'mn_MN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (97,'Nauru','UTF-8',97,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (98,'Nepali','UTF-8',98,'',0,0,0,NULL,0,0,'ne_NP')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (99,'Norwegian','UTF-8',99,'',0,0,0,NULL,0,0,'no_NO')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (100,'Occitan','UTF-8',100,'',0,0,0,NULL,0,0,'oc_FR')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (101,'Old greek','UTF-8',101,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (102,'Oriya','UTF-8',102,'',0,0,0,NULL,0,0,'or_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (103,'Pastho (Pustho)','UTF-8',103,'',0,0,0,NULL,0,0,'ps_AF')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (104,'Persian','UTF-8',104,'',0,0,0,NULL,0,0,'fa_IR')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (105,'Polish','UTF-8',105,'',0,0,0,NULL,0,0,'pl_PL')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (106,'Portuguese','UTF-8',106,'',0,0,0,NULL,0,0,'pt_PT')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (107,'Provensal','UTF-8',107,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (108,'Punjabi','UTF-8',108,'',0,0,0,NULL,0,0,'pa_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (109,'Quechua','UTF-8',109,'',0,0,0,NULL,0,0,'quz_EC')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (110,'haeto-romance','UTF-8',110,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (111,'omanian','UTF-8',111,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (112,'omansh','UTF-8',112,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (113,'Samoan','UTF-8',113,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (114,'Sangro','UTF-8',114,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (115,'Sanskrit','UTF-8',115,'',0,0,0,NULL,0,0,'sa_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (116,'Sardinian','UTF-8',116,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (117,'Scots gaelic','UTF-8',117,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (118,'Serbian','UTF-8',118,'',0,0,0,NULL,0,0,'sr_SP')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (119,'Sesotho','UTF-8',119,'',0,0,0,NULL,0,0,'ns_ZA')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (120,'Setswana','UTF-8',120,'',0,0,0,NULL,0,0,'tn_ZA')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (121,'Shona','UTF-8',121,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (122,'Sindhi','UTF-8',122,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (123,'Singhalese','UTF-8',123,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (124,'Siswati','UTF-8',124,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (125,'Slovak','UTF-8',125,'',0,0,0,NULL,0,0,'sk_SK')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (126,'Slovenian','UTF-8',126,'',0,0,0,NULL,0,0,'sl_SI')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (127,'Somali','UTF-8',127,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (128,'Spanish','UTF-8',128,'',0,0,0,NULL,0,0,'es_ES')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (129,'Sundanese','UTF-8',129,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (130,'Swahili','UTF-8',130,'',0,0,0,NULL,0,0,'sw_KE')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (131,'Swedish','UTF-8',131,'',0,0,0,NULL,0,0,'sv_SE')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (132,'Tagalog','UTF-8',132,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (133,'Tajik','UTF-8',133,'',0,0,0,NULL,0,0,'tg_TJ')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (134,'Tamil','UTF-8',134,'',0,0,0,NULL,0,0,'ta_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (135,'Tatar','UTF-8',135,'',0,0,0,NULL,0,0,'tt_RU')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (136,'Telugu','UTF-8',136,'',0,0,0,NULL,0,0,'te_IN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (137,'Thai','UTF-8',137,'',0,0,0,NULL,0,0,'th_TH')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (138,'Tibetan','UTF-8',138,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (139,'Tigrinya','UTF-8',139,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (140,'Tonga','UTF-8',140,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (141,'Tsonga','UTF-8',141,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (142,'Turkish','UTF-8',142,'',0,0,0,NULL,0,0,'tr_TR')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (143,'Turkmen','UTF-8',143,'',0,0,0,NULL,0,0,'tk_TM')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (144,'Twi','UTF-8',144,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (145,'Ukrainian','UTF-8',145,'',0,0,0,NULL,0,0,'uk_UA')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (146,'Urdu','UTF-8',146,'',0,0,0,NULL,0,0,'ur_PK')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (147,'Uzbek','UTF-8',147,'',0,0,0,NULL,0,0,'uz_UZ')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (148,'Valencian','UTF-8',148,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (149,'Vietnamese','UTF-8',149,'',0,0,0,'',0,0,'vi_VN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (150,'Volapuk','UTF-8',150,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (151,'Welsh','UTF-8',151,'',0,0,0,NULL,0,0,'cy_GB')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (152,'Wolof','UTF-8',152,'',0,0,0,NULL,0,0,'wo_SN')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (153,'Xhosa','UTF-8',153,'',0,0,0,NULL,0,0,'xh_ZA')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (154,'Yiddish','UTF-8',154,'',0,0,0,NULL,0,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (155,'Yoruba','UTF-8',155,'',0,0,0,NULL,0,0,'yo_NG')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (156,'Zulu','UTF-8',156,'',0,0,0,'',0,0,'zu_ZA')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (500,'_Custom language','UTF-8',500,'',0,0,0,'',1021,1027,'')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (157,'Romanian','UTF-8',157,NULL,0,0,0,NULL,0,0,'ro_RO')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (158,'Belarusian','UTF-8',158,NULL,0,0,0,NULL,0,0,'be_BY')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (159,'Bosnian','UTF-8',159,NULL,0,0,0,NULL,0,0,'bs_BA')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (160,'Filipino (Philippines)','UTF-8',160,NULL,0,0,0,NULL,0,0,'fil_PH')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (161,'Dari','UTF-8',161,NULL,0,0,0,NULL,0,0,'gbz_AF')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (162,'Luxembourgish','UTF-8',162,NULL,0,0,0,NULL,0,0,'lb_LU')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (163,'Romansh','UTF-8',163,NULL,0,0,0,NULL,0,0,'rm_CH')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (164,'Syriac','UTF-8',164,NULL,0,0,0,NULL,0,0,'syr_SY')"); echo '. '; flush();
new SQL("INSERT INTO `keel` VALUES (165,'Estonian','UTF-8',0,'et',0,0,1,'',1060,1040,'et_EE')"); echo '. '; flush();
new SQL("UPDATE `keel` SET `keel_id` = 0 WHERE `keel_id` = 165"); echo '. '; flush();

// Table structure for table `ldap_map`

new SQL("DROP TABLE IF EXISTS `ldap_map`"); echo '. '; flush();
new SQL("CREATE TABLE `ldap_map` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `element_name` varchar(100) NOT NULL default '',
  `parameters` tinytext,
  `return_fields` tinytext NOT NULL,
  `server_id` int(10) unsigned NOT NULL default '0',
  `type` enum('SEARCH','EDITOR','USER') NOT NULL default 'SEARCH',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
)"); echo '. '; flush();

// Dumping data for table `ldap_map`


// Table structure for table `ldap_servers`

new SQL("DROP TABLE IF EXISTS `ldap_servers`"); echo '. '; flush();
new SQL("CREATE TABLE `ldap_servers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `serverURL` varchar(255) NOT NULL default '',
  `port` varchar(4) NOT NULL default '',
  `baseDN` varchar(100) default NULL,
  `bindDN` varchar(100) default NULL,
  `password` varchar(100) default NULL,
  `only_bind` tinyint(1) unsigned default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
)"); echo '. '; flush();

// Dumping data for table `ldap_servers`


// Table structure for table `license`

new SQL("DROP TABLE IF EXISTS `license`"); echo '. '; flush();
new SQL("CREATE TABLE `license` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `URL` varchar(255) NOT NULL default '',
  `license_key` varchar(100) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `activation_key` varchar(100) NOT NULL default '',
  `status` varchar(255) NOT NULL default 'not verified',
  `type` blob,
  PRIMARY KEY  (`id`)
)"); echo '. '; flush();

// Dumping data for table `license`


// Table structure for table `moodulid`

new SQL("DROP TABLE IF EXISTS `moodulid`"); echo '. '; flush();
new SQL("CREATE TABLE `moodulid` (
  `moodul_id` int(10) unsigned NOT NULL auto_increment,
  `nimi` varchar(255) NOT NULL default '',
  `on_aktiivne` tinyint(1) unsigned default '0',
  `is_invisible` tinyint(1) unsigned NOT NULL default '0',
  `status` blob NOT NULL,
  PRIMARY KEY  (`moodul_id`),
  UNIQUE KEY `moodul_id` (`moodul_id`,`nimi`),
  KEY `moodul_id_2` (`moodul_id`)
)"); echo '. '; flush();

// Dumping data for table `moodulid`

// Table structure for table `notifications`

new SQL("DROP TABLE IF EXISTS `notifications`"); echo '. '; flush();
new SQL("CREATE TABLE `notifications` (
  `notification_id` tinyint(3) unsigned NOT NULL default '0',
  `type` varchar(255) NOT NULL default '0',
  `name` varchar(255) default '0',
  `value` varchar(255) default '0',
  `value_type` enum('active','run','send','misc','mails') default NULL,
  PRIMARY KEY  (`notification_id`)
)"); echo '. '; flush();

// Dumping data for table `notifications`


// Table structure for table `obj_artikkel`

new SQL("DROP TABLE IF EXISTS `obj_artikkel`"); echo '. '; flush();
new SQL("CREATE TABLE `obj_artikkel` (
  `lyhi` mediumtext,
  `sisu` mediumtext,
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  `algus_aeg` date NOT NULL default '0000-00-00',
  `lopp_aeg` date NOT NULL default '0000-00-00',
  `profile_id` int(4) unsigned default NULL,
  `starttime` datetime default NULL,
  `endtime` datetime default NULL,
  PRIMARY KEY  (`objekt_id`),
  KEY `profile_id` (`profile_id`)
)"); echo '. '; flush();

// Dumping data for table `obj_artikkel`

new SQL("INSERT INTO `obj_artikkel` VALUES ('','<strong>Palun kontrolli järgmiste väljade õigsust:</strong> <p>&nbsp;</p><p><strong><font color=\"#cc0000\">[error]</font></strong></p>',23,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Teie andmed on saadetud!',24,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Palun muutke otsingukriteeriumit või kasutage  täppisotsingut.',25,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Palun kontrollige andmeid.',428,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Curabitur enim. Ut urna enim, congue dapibus, ultricies nec, ultricies ut, ligula. Ut urna. In massa. Vivamus semper massa vitae nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In pharetra, lacus feugiat vestibulum volutpat, nisl dui ultrices leo, suscipit mollis urna justo sed elit. Duis sed lorem. In nonummy odio fermentum ligula. </p><p>Praesent blandit risus id tortor. Pellentesque nulla. Nam diam mauris, vulputate eget, suscipit ut, cursus at, eros. Integer blandit dignissim purus. Aenean ornare auctor ante. Proin metus tortor, luctus a, facilisis id, elementum quis, augue. Quisque sed lorem vel pede rutrum volutpat. Morbi imperdiet eros vel nisi. Ut condimentum pellentesque tortor. Nulla magna. </p></div>',10225,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Meie andmetel oled sa juba sellele küsitlusele vastanud.',389,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Täname, et registreerisite ennast veebisaidi kasutajaks.',418,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Sinu andmed on edukalt muudetud.',419,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Parool on saadetud teile e-posti teel.',424,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Did not&nbsp;find any articles or documents matching your search query, please try to refine your searh.',426,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('',' <font class=\"Alampealkiri\"> <p align=\"center\">     </p></font>Sinu kasutajanimi on blokeeritud.',427,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('',' Sinu IP aadressilt ei ole lubatud siseneda.',694,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('',' <strong>Please check the  following fields:</strong>  <p /> <p><strong><font color=\"#cc0000\">[error]</font></strong></p>',6245,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('',' According to our records you have already voted.',6247,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','  <p align=\"left\">This user has been  blocked by site  administrator.</p>',6248,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('',' Thank you for registering at our site!',6249,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('',' Your data has been successfully updated!',6250,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','  Please check your username and password.',6251,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('',' Your data has been successfully submitted.',6252,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('',' You should receive an e-mail with your password  shortly.',6253,'0000-00-00','0000-00-00',NULL,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Your installation of Saurus <a title=\"Saurus CMS web content management system\" target=\"_blank\" href=\"http://www.saurus.info\">web content management system</a> is completed successfully. What you see is a sample website with some content to get you started. Click&#160;on&#160;the menu links at right for available pre-defined content layouts.</p> <h2>Edit content</h2> <p>To start editing web content, type /editor after your site address e.g. sitename.com/editor or sitename.com/folder/editor.</p> <h2>Administer</h2> <p>To use site administration tools, type /admin after your site address e.g. sitename.com/admin or sitename.com/folder/admin.</p> <h2>Develop</h2> <p>Visit our <a title=\"Saurus CMS support site\" target=\"_blank\" href=\"http://www.saurus.info/support\">support site</a>&#160;for technical documentation, API reference and code samples. <br /> <br /> <br /> We hope you enjoy your copy of Saurus CMS!</p>',10032,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Aliquam porta quam nec lorem. Suspendisse eros nibh, sollicitudin et, placerat vel, euismod vestibulum, ipsum. Maecenas sollicitudin. Nullam vulputate auctor urna. Sed rutrum. Proin tincidunt. Vestibulum mattis iaculis sapien. Mauris ullamcorper purus et nulla. Morbi nunc ligula, tempor non, convallis vitae, laoreet eu, tellus. </p><p>Mauris urna odio, rhoncus et, ornare elementum, varius quis, erat. Maecenas commodo, nulla nec gravida mattis, magna pede feugiat dui, nec consequat orci ligula eu neque. Morbi sit amet elit. In pellentesque mattis lorem. Maecenas hendrerit tincidunt dolor. Pellentesque nonummy, orci a placerat auctor, augue leo consequat nibh, sit amet viverra urna mauris a sapien. Maecenas cursus lorem eu dolor. Nulla sagittis pharetra orci. Nullam risus elit, tempus eu, vestibulum a, tincidunt vel, mauris. Donec a nisl eget dui lacinia sollicitudin. Nunc mauris. In eu sem. Quisque vitae libero sit amet velit venenatis lobortis. Aliquam erat volutpat. Aenean nulla massa, feugiat at, rhoncus nec, aliquam sed, lorem. Mauris quis purus sit amet lacus accumsan sodales. Suspendisse euismod ornare ipsum. Praesent varius tincidunt arcu. Morbi interdum tellus sed erat. </p>',10083,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Integer laoreet, pede in pretium congue, erat mi nonummy orci, nec pharetra lacus elit nec lectus. In eget ipsum. Aliquam adipiscing placerat erat. Ut posuere diam quis metus. Praesent facilisis congue arcu. Etiam iaculis mi ut ipsum. Aliquam non turpis. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse ante. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nunc lorem mi, aliquam et, sagittis non, gravida vel, urna. </p><p>Aliquam erat volutpat. Aliquam sit amet tellus sit amet erat cursus ullamcorper. Nam eget sem at arcu dignissim dapibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean nisi eros, feugiat sed, tempus vel, luctus id, tortor. In elementum urna vitae pede. Sed nec arcu ut elit tempor semper. Aenean volutpat leo id dui. Nulla lacinia, leo sit amet vehicula vestibulum, nisi lectus pulvinar odio, in interdum libero pede a nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Donec suscipit gravida massa. Morbi eget enim vitae erat molestie porttitor. Sed commodo venenatis massa. </p></div>',10084,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Nullam tempor, nibh et volutpat placerat, lectus leo dignissim libero, blandit venenatis enim magna vitae mauris. Nullam pellentesque magna. Nullam tempor turpis eu elit. Aliquam erat volutpat. Aliquam magna. Sed iaculis, urna non accumsan ornare, neque dolor porttitor velit, id interdum metus dolor at dui. Curabitur tincidunt magna id eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Vestibulum eu neque sed odio tristique cursus. Nunc est est, placerat sed, rhoncus vitae, tempor at, felis. Fusce sem magna, iaculis sit amet, imperdiet nec, suscipit nec, eros. Aenean aliquam mauris sit amet augue. Ut in nunc nec neque sagittis nonummy. Praesent est. Duis nec massa ac sapien luctus mattis. Phasellus tortor sapien, scelerisque vitae, gravida id, imperdiet quis, justo. Mauris mauris velit, adipiscing dignissim, euismod vitae, tincidunt ut, libero. Cras ut nisl. Sed iaculis nunc fringilla erat. Morbi fringilla quam eu mi. </p><p>Sed gravida consectetuer nisi. Pellentesque consectetuer tempus justo. Quisque gravida. Praesent vel quam. Vivamus blandit dignissim risus. Donec libero. Nunc quis purus at magna suscipit scelerisque. Fusce sit amet justo vel diam iaculis ultricies. Ut felis diam, iaculis non, imperdiet tempor, rhoncus vitae, sem. Suspendisse potenti. Maecenas fermentum nisi eget pede. Duis sit amet sem vel dui faucibus condimentum. Curabitur dictum arcu. Morbi consectetuer dignissim orci. Aenean lectus pede, imperdiet eu, venenatis a, luctus nec, libero. Aenean sapien quam, faucibus non, placerat id, bibendum ac, eros. Nullam rutrum, nibh a suscipit lacinia, massa est pellentesque mi, eu mattis nisl ipsum sed diam. </p></div>',10085,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','The current section \"Hidden section\" is published but marked with \"Hide in navigation menu\" which makes the section name to disappear from menus and sitemap. You can tell the hidden status by the yellow colour of the v-shaped action button. The contents of the section are still visible if you know the direct link or use site search.',10094,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','This article is unpublished and cannot be seen by any of the site visitors. You can tell the unpublished status by the red colour of the v-shaped action button. <br />',10096,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','This article has the option \"Allow comments\" ticked. <p>Suspendisse nec elit at lacus pulvinar elementum. Nam egestas. Vivamus gravida arcu sit amet tortor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Donec lacinia dui eget nulla. Nam vitae libero ut metus molestie rutrum. Aenean congue cursus erat. Nam commodo consectetuer ante. Curabitur sodales. Donec semper ipsum quis elit. Pellentesque a tellus.</p>',10098,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Nulla dignissim nibh id felis. Vestibulum elit urna, lobortis id, sagittis at, scelerisque at, tortor. Etiam odio nisi, tempus eu, pulvinar non, ullamcorper non, lectus. Duis orci orci, rutrum nec, elementum et, feugiat non, risus.',10121,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('Article can be split into lead and body. The current template \"Articles: 1 column\" then displays only lead with a link to the full article text. ','Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Aenean ultrices libero nec felis. Sed imperdiet. Aenean et lacus. Etiam vel tortor. Mauris euismod. Nam ipsum diam, pharetra id, hendrerit quis, vulputate ut, augue. Duis tempor sodales nisi. Etiam euismod, leo non adipiscing placerat, lorem turpis gravida mi, sit amet condimentum velit dolor molestie nibh. Praesent elit est, tempus eleifend, porta sed, rhoncus eget, lacus. Nullam eleifend interdum augue. Quisque sed felis quis sem malesuada pulvinar. Donec placerat. Praesent arcu. Maecenas tortor. Curabitur purus pede, mattis eu, condimentum quis, tristique sit amet, ipsum. Suspendisse orci. Vestibulum vulputate.',10151,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Nunc varius ante. Proin vitae magna at quam suscipit vehicula. Mauris sollicitudin urna id erat. Donec nec nisl ut urna sollicitudin faucibus:<ol><li>Pellentesque pellentesque. Aliquam iaculis congue erat.</li><li>Donec ullamcorper. Fusce lacus magna, pretium vel.</li><li>Mauris ultricies ipsum ut eros. Fusce blandit accumsan risus. Nulla aliquet. Duis sollicitudin orci id purus.</li></ol><p>Donec pretium laoreet erat. Nulla lectus. Cras suscipit nisi convallis lectus. Vestibulum magna urna, euismod nec, rutrum at.</p>',10152,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Sed ornare dolor in leo. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Fusce feugiat tortor. Pellentesque consequat leo volutpat lectus. Fusce rhoncus, sem id rhoncus aliquet, erat lorem viverra erat, et molestie massa urna et mauris. Quisque adipiscing lacus placerat nibh. Ut mattis nulla sit amet diam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nunc dapibus vulputate metus. </p><p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Integer vel ligula id enim commodo condimentum. Vivamus convallis ante a purus. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Phasellus vehicula, nibh nec tristique tincidunt, diam turpis commodo purus, ac pretium dui nisl ac nulla. Maecenas pede mauris, luctus a, rhoncus nec, interdum ut, pede. Duis quis augue dapibus dolor ultrices convallis. Proin ut sapien. Ut sit amet mi a urna aliquet dignissim. Maecenas placerat. Phasellus tempus risus vel est. Fusce venenatis dui a ipsum. Aliquam ut libero. Praesent tincidunt, tortor ornare vulputate dapibus, dui mauris euismod lectus, ut viverra tortor tortor in tortor. </p></div>',10167,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Ut adipiscing mi et dui. Pellentesque mi justo, congue eget, malesuada ac, vehicula ut, mi. Nulla pellentesque. </p><p>Curabitur sollicitudin ipsum. Nulla et orci. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam congue diam et felis vehicula pellentesque. Mauris vestibulum sollicitudin est. Nam bibendum magna quis urna.</p>',10153,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Aliquam eu mauris. Suspendisse sed augue. Aliquam orci neque, adipiscing a, fringilla quis, lacinia vel, nisi. Aliquam luctus, odio non faucibus imperdiet, justo tortor pharetra lacus, bibendum accumsan velit nisl pellentesque nisl. Aenean pharetra erat ullamcorper mi. Phasellus blandit, nisl a molestie scelerisque, justo nibh consectetuer pede, at facilisis sem lectus vitae lorem. Quisque nibh. Maecenas massa. Suspendisse porta dignissim dolor. </p><p>Suspendisse pede nulla, consequat et, imperdiet at, vulputate vitae, massa. Etiam dolor odio, tincidunt id, consequat nonummy, aliquam eget, massa. Nulla facilisi. Sed non erat nec velit eleifend ultricies. Curabitur quis diam nec erat ultrices scelerisque. Quisque aliquet tincidunt sapien. Praesent quis ligula blandit urna adipiscing semper. Praesent massa. Sed fermentum ornare lorem. Nulla facilisi. </p></div>',10166,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Aliquam eu mauris. Suspendisse sed augue. Aliquam orci neque, adipiscing a, fringilla quis, lacinia vel, nisi. Aliquam luctus, odio non faucibus imperdiet, justo tortor pharetra lacus, bibendum accumsan velit nisl pellentesque nisl. Aenean pharetra erat ullamcorper mi. Phasellus blandit, nisl a molestie scelerisque, justo nibh consectetuer pede, at facilisis sem lectus vitae lorem. Quisque nibh. Maecenas massa. Suspendisse porta dignissim dolor. </p><p>Suspendisse pede nulla, consequat et, imperdiet at, vulputate vitae, massa. Etiam dolor odio, tincidunt id, consequat nonummy, aliquam eget, massa. Nulla facilisi. Sed non erat nec velit eleifend ultricies. Curabitur quis diam nec erat ultrices scelerisque. Quisque aliquet tincidunt sapien. Praesent quis ligula blandit urna adipiscing semper. Praesent massa. Sed fermentum ornare lorem. Nulla facilisi. </p></div>',10168,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Curabitur enim. Ut urna enim, congue dapibus, ultricies nec, ultricies ut, ligula. Ut urna. In massa. Vivamus semper massa vitae nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In pharetra, lacus feugiat vestibulum volutpat, nisl dui ultrices leo, suscipit mollis urna justo sed elit. Duis sed lorem. In nonummy odio fermentum ligula. </p><p>Praesent blandit risus id tortor. Pellentesque nulla. Nam diam mauris, vulputate eget, suscipit ut, cursus at, eros. Integer blandit dignissim purus. Aenean ornare auctor ante. Proin metus tortor, luctus a, facilisis id, elementum quis, augue. Quisque sed lorem vel pede rutrum volutpat. Morbi imperdiet eros vel nisi. Ut condimentum pellentesque tortor. Nulla magna. </p></div>',10169,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nullam sit amet diam at sapien posuere suscipit. Praesent dictum eros et sapien. Donec volutpat, purus a malesuada molestie, orci velit euismod massa, eget sagittis felis magna elementum augue. Ut vitae pede. Curabitur eu velit. Mauris ac velit. Donec viverra nunc ac mauris. Ut pretium. Morbi velit augue, aliquam nec, tristique a, egestas imperdiet, nisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Ut et nisi. Vivamus venenatis suscipit ipsum. Duis vel felis vel turpis vulputate accumsan. Vivamus eleifend, massa ut aliquam tincidunt, est lectus aliquam ante, vel faucibus magna purus vitae magna. Ut condimentum luctus nisi. In justo elit, blandit vel, semper nec, rhoncus quis, risus. Praesent ut leo. Proin blandit urna vitae elit.',10170,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Morbi nec odio. Sed at ante. Suspendisse orci mauris, tempus at, hendrerit sit amet, sollicitudin sed, mi. Nullam tincidunt tincidunt tortor. Fusce augue enim, convallis sit amet, porttitor vitae, interdum sed, felis. Nulla condimentum. Praesent egestas venenatis dolor. Fusce tortor neque, dictum ut, feugiat a, adipiscing in, odio. Aenean eget est. Nam odio tellus, vehicula quis, tempor nec, auctor non, turpis. Aenean ac lacus. Etiam adipiscing nunc a erat. Nam vehicula tempus eros. Duis vitae nisl. Nunc quis mi. Etiam interdum, purus id vestibulum egestas, felis sapien ornare nulla, vel condimentum mi lectus a odio. Proin in ipsum ut nibh pellentesque sagittis. In ante. Nulla quam. Nulla orci pede, commodo id, commodo id, sollicitudin et, nibh. </p>',10171,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Oled edukalt paigaldanud Saurus CMS <a href=\"http://www.saurus.ee\" target=\"_blank\" title=\"Saurus CMS sisuhaldustarkvara\">sisuhaldustarkvara</a>. Näed hetkel lihtsat veebisaiti mille oleme ette valmistanud et saaksid alustada oma saidi sisu toimetamist või kujundusega sidumist. Paremal menüüs klikkides leiad näidised erinevatest tootega kaasas olevatest sisumallidest.</p> <h2>Toimeta sisu</h2> <p>Veebi sisu toimetamiseks lisa oma veebisaidi aadressile /editor: näiteks firma.ee/editor või firma.ee/kataloog/editor.</p> <h2>Administreeri</h2> <p>Administreerimisvahendid leiad /admin osast. Täienda vastavalt oma veebi aadressi: firma.ee/admin või firma.ee/kataloog/admin.</p> <h2>Arenda</h2> <p>Oma kujunduse sidumiseks ja paigalduskomplektiga kaasasoleva funktsionaalsuse täiendamiseks uuri administreerimisvahendeid ja külasta inglisekeelset <a href=\"http://www.saurus.info/support\" target=\"_blank\" title=\"Saurus CMS tugiveeb\">tugiveebi</a> kust leiad tehnilise dokumentatsiooni, keelesüntaksi ja koodinäidised.</p> <p>Edukat toimetamist!</p>',10197,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Aliquam porta quam nec lorem. Suspendisse eros nibh, sollicitudin et, placerat vel, euismod vestibulum, ipsum. Maecenas sollicitudin. Nullam vulputate auctor urna. Sed rutrum. Proin tincidunt. Vestibulum mattis iaculis sapien. Mauris ullamcorper purus et nulla. Morbi nunc ligula, tempor non, convallis vitae, laoreet eu, tellus. </p><p>Mauris urna odio, rhoncus et, ornare elementum, varius quis, erat. Maecenas commodo, nulla nec gravida mattis, magna pede feugiat dui, nec consequat orci ligula eu neque. Morbi sit amet elit. In pellentesque mattis lorem. Maecenas hendrerit tincidunt dolor. Pellentesque nonummy, orci a placerat auctor, augue leo consequat nibh, sit amet viverra urna mauris a sapien. Maecenas cursus lorem eu dolor. Nulla sagittis pharetra orci. Nullam risus elit, tempus eu, vestibulum a, tincidunt vel, mauris. Donec a nisl eget dui lacinia sollicitudin. Nunc mauris. In eu sem. Quisque vitae libero sit amet velit venenatis lobortis. Aliquam erat volutpat. Aenean nulla massa, feugiat at, rhoncus nec, aliquam sed, lorem. Mauris quis purus sit amet lacus accumsan sodales. Suspendisse euismod ornare ipsum. Praesent varius tincidunt arcu. Morbi interdum tellus sed erat. </p>',10208,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Integer laoreet, pede in pretium congue, erat mi nonummy orci, nec pharetra lacus elit nec lectus. In eget ipsum. Aliquam adipiscing placerat erat. Ut posuere diam quis metus. Praesent facilisis congue arcu. Etiam iaculis mi ut ipsum. Aliquam non turpis. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse ante. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nunc lorem mi, aliquam et, sagittis non, gravida vel, urna. </p><p>Aliquam erat volutpat. Aliquam sit amet tellus sit amet erat cursus ullamcorper. Nam eget sem at arcu dignissim dapibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean nisi eros, feugiat sed, tempus vel, luctus id, tortor. In elementum urna vitae pede. Sed nec arcu ut elit tempor semper. Aenean volutpat leo id dui. Nulla lacinia, leo sit amet vehicula vestibulum, nisi lectus pulvinar odio, in interdum libero pede a nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Donec suscipit gravida massa. Morbi eget enim vitae erat molestie porttitor. Sed commodo venenatis massa. </p></div>',10209,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Nullam tempor, nibh et volutpat placerat, lectus leo dignissim libero, blandit venenatis enim magna vitae mauris. Nullam pellentesque magna. Nullam tempor turpis eu elit. Aliquam erat volutpat. Aliquam magna. Sed iaculis, urna non accumsan ornare, neque dolor porttitor velit, id interdum metus dolor at dui. Curabitur tincidunt magna id eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Vestibulum eu neque sed odio tristique cursus. Nunc est est, placerat sed, rhoncus vitae, tempor at, felis. Fusce sem magna, iaculis sit amet, imperdiet nec, suscipit nec, eros. Aenean aliquam mauris sit amet augue. Ut in nunc nec neque sagittis nonummy. Praesent est. Duis nec massa ac sapien luctus mattis. Phasellus tortor sapien, scelerisque vitae, gravida id, imperdiet quis, justo. Mauris mauris velit, adipiscing dignissim, euismod vitae, tincidunt ut, libero. Cras ut nisl. Sed iaculis nunc fringilla erat. Morbi fringilla quam eu mi. </p><p>Sed gravida consectetuer nisi. Pellentesque consectetuer tempus justo. Quisque gravida. Praesent vel quam. Vivamus blandit dignissim risus. Donec libero. Nunc quis purus at magna suscipit scelerisque. Fusce sit amet justo vel diam iaculis ultricies. Ut felis diam, iaculis non, imperdiet tempor, rhoncus vitae, sem. Suspendisse potenti. Maecenas fermentum nisi eget pede. Duis sit amet sem vel dui faucibus condimentum. Curabitur dictum arcu. Morbi consectetuer dignissim orci. Aenean lectus pede, imperdiet eu, venenatis a, luctus nec, libero. Aenean sapien quam, faucibus non, placerat id, bibendum ac, eros. Nullam rutrum, nibh a suscipit lacinia, massa est pellentesque mi, eu mattis nisl ipsum sed diam. </p></div>',10210,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','See veebi rubriik on küll avalikustatud, kuid märgitud linnukesega \"Peida menüüs\" mille tulemusena ei ilmu ta avalikus osas menüüdes ega sisukaardil. Peidetud staatust märgib ka v-kujulise käsunupu kollane värv. Ehkki menüü ei ole külastajatele nähtav, saavad nad siiski selle sisuga tutvuda kui teavad otselinki või kasutavad saidi otsingut. ',10214,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','See artikkel on avaldamata ja pole seega veebi külastajatele nähtav. Avaldamata staatust märgib v-kujulise käsunupu punane värv. <br />',10215,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('<p>Artikli võib jagada sissejuhatuseks ja sisuks. Siin kasutusel olev sisumall \"Articles: 1 column\" kuvab sellisel juhul nimekirjas sissejuhatuse koos lingiga artikli täielikule tekstile. <br />','Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Aenean ultrices libero nec felis. Sed imperdiet. Aenean et lacus. Etiam vel tortor. Mauris euismod. Nam ipsum diam, pharetra id, hendrerit quis, vulputate ut, augue. Duis tempor sodales nisi. Etiam euismod, leo non adipiscing placerat, lorem turpis gravida mi, sit amet condimentum velit dolor molestie nibh. Praesent elit est, tempus eleifend, porta sed, rhoncus eget, lacus. Nullam eleifend interdum augue. Quisque sed felis quis sem malesuada pulvinar. Donec placerat. Praesent arcu. Maecenas tortor. Curabitur purus pede, mattis eu, condimentum quis, tristique sit amet, ipsum. Suspendisse orci. Vestibulum vulputate.</p>',10216,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Nulla dignissim nibh id felis. Vestibulum elit urna, lobortis id, sagittis at, scelerisque at, tortor. Etiam odio nisi, tempus eu, pulvinar non, ullamcorper non, lectus. Duis orci orci, rutrum nec, elementum et, feugiat non, risus.',10217,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Sellel artiklil on aktiivne märgend \"Kommentaarid lubatud\".</p><p>Suspendisse nec elit at lacus pulvinar elementum. Nam egestas. Vivamus gravida arcu sit amet tortor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Donec lacinia dui eget nulla. Nam vitae libero ut metus molestie rutrum. Aenean congue cursus erat. Nam commodo consectetuer ante. Curabitur sodales. Donec semper ipsum quis elit. Pellentesque a tellus.</p>',10219,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Nunc varius ante. Proin vitae magna at quam suscipit vehicula. Mauris sollicitudin urna id erat. Donec nec nisl ut urna sollicitudin faucibus:<ol><li>Pellentesque pellentesque. Aliquam iaculis congue erat.</li><li>Donec ullamcorper. Fusce lacus magna, pretium vel.</li><li>Mauris ultricies ipsum ut eros. Fusce blandit accumsan risus. Nulla aliquet. Duis sollicitudin orci id purus.</li></ol><p>Donec pretium laoreet erat. Nulla lectus. Cras suscipit nisi convallis lectus. Vestibulum magna urna, euismod nec, rutrum at.</p>',10220,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Ut adipiscing mi et dui. Pellentesque mi justo, congue eget, malesuada ac, vehicula ut, mi. Nulla pellentesque. </p><p>Curabitur sollicitudin ipsum. Nulla et orci. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam congue diam et felis vehicula pellentesque. Mauris vestibulum sollicitudin est. Nam bibendum magna quis urna.</p>',10221,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Aliquam eu mauris. Suspendisse sed augue. Aliquam orci neque, adipiscing a, fringilla quis, lacinia vel, nisi. Aliquam luctus, odio non faucibus imperdiet, justo tortor pharetra lacus, bibendum accumsan velit nisl pellentesque nisl. Aenean pharetra erat ullamcorper mi. Phasellus blandit, nisl a molestie scelerisque, justo nibh consectetuer pede, at facilisis sem lectus vitae lorem. Quisque nibh. Maecenas massa. Suspendisse porta dignissim dolor. </p><p>Suspendisse pede nulla, consequat et, imperdiet at, vulputate vitae, massa. Etiam dolor odio, tincidunt id, consequat nonummy, aliquam eget, massa. Nulla facilisi. Sed non erat nec velit eleifend ultricies. Curabitur quis diam nec erat ultrices scelerisque. Quisque aliquet tincidunt sapien. Praesent quis ligula blandit urna adipiscing semper. Praesent massa. Sed fermentum ornare lorem. Nulla facilisi. </p></div>',10222,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Sed ornare dolor in leo. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Fusce feugiat tortor. Pellentesque consequat leo volutpat lectus. Fusce rhoncus, sem id rhoncus aliquet, erat lorem viverra erat, et molestie massa urna et mauris. Quisque adipiscing lacus placerat nibh. Ut mattis nulla sit amet diam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nunc dapibus vulputate metus. </p><p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Integer vel ligula id enim commodo condimentum. Vivamus convallis ante a purus. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Phasellus vehicula, nibh nec tristique tincidunt, diam turpis commodo purus, ac pretium dui nisl ac nulla. Maecenas pede mauris, luctus a, rhoncus nec, interdum ut, pede. Duis quis augue dapibus dolor ultrices convallis. Proin ut sapien. Ut sit amet mi a urna aliquet dignissim. Maecenas placerat. Phasellus tempus risus vel est. Fusce venenatis dui a ipsum. Aliquam ut libero. Praesent tincidunt, tortor ornare vulputate dapibus, dui mauris euismod lectus, ut viverra tortor tortor in tortor. </p></div>',10223,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<div id=\"lipsum\"><p>Aliquam eu mauris. Suspendisse sed augue. Aliquam orci neque, adipiscing a, fringilla quis, lacinia vel, nisi. Aliquam luctus, odio non faucibus imperdiet, justo tortor pharetra lacus, bibendum accumsan velit nisl pellentesque nisl. Aenean pharetra erat ullamcorper mi. Phasellus blandit, nisl a molestie scelerisque, justo nibh consectetuer pede, at facilisis sem lectus vitae lorem. Quisque nibh. Maecenas massa. Suspendisse porta dignissim dolor. </p><p>Suspendisse pede nulla, consequat et, imperdiet at, vulputate vitae, massa. Etiam dolor odio, tincidunt id, consequat nonummy, aliquam eget, massa. Nulla facilisi. Sed non erat nec velit eleifend ultricies. Curabitur quis diam nec erat ultrices scelerisque. Quisque aliquet tincidunt sapien. Praesent quis ligula blandit urna adipiscing semper. Praesent massa. Sed fermentum ornare lorem. Nulla facilisi. </p></div>',10224,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nullam sit amet diam at sapien posuere suscipit. Praesent dictum eros et sapien. Donec volutpat, purus a malesuada molestie, orci velit euismod massa, eget sagittis felis magna elementum augue. Ut vitae pede. Curabitur eu velit. Mauris ac velit. Donec viverra nunc ac mauris. Ut pretium. Morbi velit augue, aliquam nec, tristique a, egestas imperdiet, nisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Ut et nisi. Vivamus venenatis suscipit ipsum. Duis vel felis vel turpis vulputate accumsan. Vivamus eleifend, massa ut aliquam tincidunt, est lectus aliquam ante, vel faucibus magna purus vitae magna. Ut condimentum luctus nisi. In justo elit, blandit vel, semper nec, rhoncus quis, risus. Praesent ut leo. Proin blandit urna vitae elit.',10226,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Morbi nec odio. Sed at ante. Suspendisse orci mauris, tempus at, hendrerit sit amet, sollicitudin sed, mi. Nullam tincidunt tincidunt tortor. Fusce augue enim, convallis sit amet, porttitor vitae, interdum sed, felis. Nulla condimentum. Praesent egestas venenatis dolor. Fusce tortor neque, dictum ut, feugiat a, adipiscing in, odio. Aenean eget est. Nam odio tellus, vehicula quis, tempor nec, auctor non, turpis. Aenean ac lacus. Etiam adipiscing nunc a erat. Nam vehicula tempus eros. Duis vitae nisl. Nunc quis mi. Etiam interdum, purus id vestibulum egestas, felis sapien ornare nulla, vel condimentum mi lectus a odio. Proin in ipsum ut nibh pellentesque sagittis. In ante. Nulla quam. Nulla orci pede, commodo id, commodo id, sollicitudin et, nibh. </p>',10227,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','The page you are looking for has not been found.',10245,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Your IP address is blocked.',10246,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"> <tbody> <tr> <td align=\"center\" class=\"boxhead\" colspan=\"2\"><strong>The item has been added to the cart!</strong></td></tr> <tr> <td valign=\"center\" align=\"center\" class=\"txt\"><a href=\"javascript:window.close()\">Back</a></td> <td valign=\"center\" align=\"center\" class=\"txt\"><a href=\"javascript:to_cart()\">View cart</a></td></tr></tbody></table>',10247,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<br />&lt;table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"&gt; &lt;tbody&gt; &lt;tr&gt; &lt;td align=\"center\" class=\"boxhead\"&gt;&lt;strong&gt;The cart has been saved!&lt;/strong&gt;&lt;/td&gt;&lt;/tr&gt; &lt;tr&gt; &lt;td valign=\"center\" align=\"center\" class=\"txt\"&gt;&lt;a href=\"javascript:window.close()\"&gt;Back&lt;/a&gt;&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;',10248,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','Seda lehekülge ei leitud.',10249,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"> <tbody> <tr> <td align=\"center\" colspan=\"2\" class=\"boxhead\"><strong>Toode on lisatud ostukorvi!</strong></td></tr> <tr> <td valign=\"center\" align=\"center\" class=\"txt\"><a href=\"javascript:window.close()\">Tagasi</a></td> <td valign=\"center\" align=\"center\" class=\"txt\"><a href=\"javascript:to_cart()\">Edasi</a></td></tr></tbody></table>',10250,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"> <tbody> <tr> <td align=\"center\" class=\"boxhead\"><strong>Teie ostukorv on salvestatud!</strong></td></tr> <tr> <td valign=\"center\" align=\"center\" class=\"txt\"><a href=\"javascript:window.close()\">Tagasi</a><a href=\"javascript:to_cart()\"></a></td></tr></tbody></table>',10251,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Powered by <a href=\"http://www.saurus.info\" target=\"_blank\" title=\"Sisuhaldustarkvara\">Saurus CMS</a> | <a href=\"?op=sitemap\" title=\"Sisukaart\">Sitemap</a></p>',10568,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_artikkel` VALUES ('','<p>Saiti jooksutab <a title=\"Sisuhaldustarkvara\" target=\"_blank\" href=\"http://www.saurus.ee\">Saurus CMS</a> | <a title=\"Sisukaart\" href=\"?op=sitemap\">Sisukaart</a></p>',10462,'0000-00-00','0000-00-00',137,NULL,NULL)"); echo '. '; flush();

// Table structure for table `obj_asset`

new SQL("DROP TABLE IF EXISTS `obj_asset`"); echo '. '; flush();
new SQL("CREATE TABLE `obj_asset` (
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  `profile_id` int(4) unsigned default NULL,
  PRIMARY KEY  (`objekt_id`),
  UNIQUE KEY `objekt_profile` (`objekt_id`,`profile_id`),
  KEY `profile_id` (`profile_id`)
)"); echo '. '; flush();

// Dumping data for table `obj_asset`


// Table structure for table `obj_dokument`

new SQL("DROP TABLE IF EXISTS `obj_dokument`"); echo '. '; flush();
new SQL("CREATE TABLE `obj_dokument` (
  `fail` varchar(255) NOT NULL default '',
  `kirjeldus` text,
  `autor` varchar(255) default NULL,
  `size` int(10) unsigned NOT NULL default '0',
  `tyyp` varchar(10) default NULL,
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  `mime_tyyp` varchar(255) default NULL,
  `sisu_blob` longblob,
  `profile_id` int(4) unsigned NOT NULL default '0',
  `repl_last_modified` int(11) unsigned NOT NULL default '0',
  `download_type` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`objekt_id`),
  KEY `autor` (`autor`),
  KEY `profile_id` (`profile_id`)
)"); echo '. '; flush();

// Dumping data for table `obj_dokument`


// Table structure for table `obj_file`

new SQL("DROP TABLE IF EXISTS `obj_file`"); echo '. '; flush();
new SQL("CREATE TABLE `obj_file` (
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  `fullpath` tinytext NOT NULL,
  `relative_path` tinytext,
  `filename` tinytext NOT NULL,
  `mimetype` varchar(80) default NULL,
  `size` float(12,0) default NULL,
  `lastmodified` datetime default NULL,
  `is_deleted` tinyint(1) unsigned NOT NULL default '0',
  `profile_id` int(4) unsigned NOT NULL default '0',
  `author` varchar(255) default NULL,
  `notes` text,
  `kirjeldus` text,
  PRIMARY KEY  (`objekt_id`),
  UNIQUE KEY `objekt_id` (`objekt_id`),
  KEY `objekt_id_2` (`objekt_id`)
)"); echo '. '; flush();

// Dumping data for table `obj_file`


// Table structure for table `obj_folder`

new SQL("DROP TABLE IF EXISTS `obj_folder`"); echo '. '; flush();
new SQL("CREATE TABLE `obj_folder` (
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  `fullpath` tinytext NOT NULL,
  `relative_path` tinytext,
  PRIMARY KEY  (`objekt_id`),
  UNIQUE KEY `id` (`objekt_id`),
  KEY `id_2` (`objekt_id`)
)"); echo '. '; flush();

// Dumping data for table `obj_folder`

new SQL("INSERT INTO `obj_folder` VALUES (10506,'','/public')"); echo '. '; flush();
new SQL("INSERT INTO `obj_folder` VALUES (10507,'','/shared')"); echo '. '; flush();

// Table structure for table `obj_gallup`

new SQL("DROP TABLE IF EXISTS `obj_gallup`"); echo '. '; flush();
new SQL("CREATE TABLE `obj_gallup` (
  `on_avatud` tinyint(1) unsigned NOT NULL default '1',
  `orig_parent_id` int(11) NOT NULL default '0',
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  `expires` date default NULL,
  `is_anonymous` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`objekt_id`),
  KEY `on_avatud` (`on_avatud`)
)"); echo '. '; flush();

// Dumping data for table `obj_gallup`

new SQL("INSERT INTO `obj_gallup` VALUES (1,10029,10350,'2010-04-01',1)"); echo '. '; flush();
new SQL("INSERT INTO `obj_gallup` VALUES (1,10194,10351,'0000-00-00',1)"); echo '. '; flush();

// Table structure for table `obj_kommentaar`

new SQL("DROP TABLE IF EXISTS `obj_kommentaar`"); echo '. '; flush();
new SQL("CREATE TABLE `obj_kommentaar` (
  `objekt_id` bigint(20) unsigned NOT NULL auto_increment,
  `nimi` varchar(100) NOT NULL default '',
  `email` varchar(100) default NULL,
  `on_saada_email` tinyint(1) unsigned NOT NULL default '0',
  `on_peida_email` tinyint(1) unsigned NOT NULL default '0',
  `ip` varchar(15) default NULL,
  `text` text,
  `kasutaja_id` bigint(20) default NULL,
  `url` varchar(100) default NULL,
  PRIMARY KEY  (`objekt_id`)
)"); echo '. '; flush();

// Dumping data for table `obj_kommentaar`

new SQL("INSERT INTO `obj_kommentaar` VALUES (10120,'Tom','',0,0,'192.168.0.16','Morbi augue purus, scelerisque accumsan, tincidunt ut, tincidunt vitae, est. Proin id nunc. Mauris felis elit, scelerisque sit amet, tincidunt id, pretium at, purus. Nam quis tortor non ipsum porta mattis. Morbi nisl. Aliquam sapien nunc, tempus quis, faucibus et, sagittis in, velit. Proin quis nisi eget lacus sollicitudin dapibus.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10239,'Daniel','dan@smidth.com',0,0,'192.168.0.16','Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque rhoncus, erat eu ullamcorper elementum, nibh purus tincidunt ligula, ut dictum sapien erat ac urna. Etiam tempor sollicitudin leo. Aliquam erat volutpat. Sed sed nulla. Donec sollicitudin, ipsum quis adipiscing malesuada, dui augue posuere leo, ut tristique erat ipsum id nibh. Nunc mauris nulla, blandit lobortis, aliquet ac, viverra id, eros. Nullam mauris. Phasellus mattis. Nunc imperdiet sapien vel lacus.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10118,'Mary','',0,0,'192.168.0.16','Aliquam magna lacus, gravida vel, fermentum vitae, commodo vitae, massa. Quisque tristique euismod turpis. Integer vitae sapien vel quam egestas euismod.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10119,'Wane','',0,0,'192.168.0.16','Etiam orci neque, porta id, vehicula id, vulputate a, magna. Vestibulum eu mi. In iaculis, nisi quis egestas tincidunt, dui magna scelerisque dolor, ut imperdiet sem sapien a leo.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10238,'John Smidth','',0,0,'192.168.0.16','Ut sem magna, pellentesque a, tincidunt quis, adipiscing a, tortor. Sed orci :). Nullam nec lacus sed nunc porttitor tristique. Etiam eu nisi. Mauris enim erat, interdum a, tincidunt sed, auctor condimentum, lectus. Suspendisse diam. Cras et enim. Nam in elit eget quam venenatis facilisis. Suspendisse leo massa, laoreet eget, condimentum accumsan, semper a, nunc.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10117,'Jane','',0,0,'192.168.0.16','Quisque a arcu. Aenean consequat, leo id mollis sagittis, ligula magna adipiscing quam, ac aliquet nisl justo nec dolor. Sed porta nulla eget odio. Sed convallis sapien eu mauris. Fusce sem leo, ultrices sed, rutrum quis, vestibulum id, mauris. Phasellus hendrerit velit eget erat. Sed nec mauris ac justo lobortis facilisis. Duis et velit sit amet tortor consequat rhoncus. Etiam magna. Suspendisse id turpis et leo sodales mattis. Phasellus a risus quis sapien vehicula auctor. Nam accumsan, mi eget tincidunt tincidunt, est quam adipiscing tortor, ut consequat nisl metus sed mauris. Vivamus semper tellus a lorem. Aliquam erat volutpat. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Curabitur et dolor id turpis pharetra blandit. ',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10116,'Gulliver','',0,0,'192.168.0.16','Quisque sit amet purus ac quam viverra malesuada. Sed arcu. Quisque velit lectus, bibendum nec, tristique eu, sagittis eget, massa. Donec laoreet odio a augue. http://www.saurus.info',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10114,'Daniel','dan@smidth.com',0,0,'192.168.0.16','Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque rhoncus, erat eu ullamcorper elementum, nibh purus tincidunt ligula, ut dictum sapien erat ac urna. Etiam tempor sollicitudin leo. Aliquam erat volutpat. Sed sed nulla. Donec sollicitudin, ipsum quis adipiscing malesuada, dui augue posuere leo, ut tristique erat ipsum id nibh. Nunc mauris nulla, blandit lobortis, aliquet ac, viverra id, eros. Nullam mauris. Phasellus mattis. Nunc imperdiet sapien vel lacus.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10113,'Xena','',0,0,'192.168.0.16','Ut porttitor tortor ut dui. Pellentesque varius felis ac libero hendrerit iaculis. Nunc ipsum. Aliquam tellus lacus, pulvinar at, tempor ac, facilisis quis, velit. Fusce tortor ante, semper ut, suscipit vel, dignissim id, enim. Donec nunc nisl, semper quis, tempor at, porttitor vel, felis. :)',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10111,'Kati','',0,0,'192.168.0.16','Suspendisse potenti. Aliquam erat volutpat. Cras aliquet, urna vel semper fringilla, tortor ligula adipiscing diam, id elementum magna elit non ligula. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque dictum. Integer malesuada lorem vel elit. Ut ut tellus eget ante interdum venenatis. Duis et nulla.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10108,'Amadeus','',0,0,'192.168.0.16','Phasellus ultrices rutrum leo!',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10109,'Humbert','',0,0,'192.168.0.16','Quisque enim augue, pharetra in, iaculis et, rhoncus a, lorem. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Sed volutpat magna non tellus. Maecenas semper nibh tincidunt nunc. Morbi tellus ipsum, tincidunt non, posuere sit amet, cursus nec, augue. Vestibulum egestas arcu eu mauris. Sed vitae enim ac lacus eleifend lobortis.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10107,'John Smidth','',0,0,'192.168.0.16','Ut sem magna, pellentesque a, tincidunt quis, adipiscing a, tortor. Sed orci :). Nullam nec lacus sed nunc porttitor tristique. Etiam eu nisi. Mauris enim erat, interdum a, tincidunt sed, auctor condimentum, lectus. Suspendisse diam. Cras et enim. Nam in elit eget quam venenatis facilisis. Suspendisse leo massa, laoreet eget, condimentum accumsan, semper a, nunc.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10101,'Don','',0,0,'192.168.0.16','Urabitur porttitor risus a ligula. Donec gravida auctor lorem. Vestibulum justo lorem, eleifend ac, semper ac, varius id, risus. In leo enim, gravida mattis, pretium sit amet, ornare ut, ligula. Donec pulvinar.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10102,'Jessica Tim','some@body.com',0,0,'192.168.0.16','Mauris iaculis tellus eget pede accumsan hendrerit: http://www.saurus.info. Etiam elit. Quisque sem nisl, consequat eget, porta at, porta non, dui. In nec lacus. Sed eget lacus. Pellentesque tempus massa nec velit. Cras elit justo, accumsan sit amet, mattis a, nonummy a, velit.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10100,'Margaret','',0,0,'192.168.0.16','Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Ut justo ligula, venenatis sit amet, suscipit nec, hendrerit quis, diam. Etiam sit amet justo. In hac habitasse platea dictumst.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10099,'Jim','',0,0,'192.168.0.16','Curabitur aliquet purus et nulla. Nam aliquet ullamcorper enim. Donec commodo viverra dui. Praesent sodales malesuada turpis. Aliquam ultricies, mi eu eleifend tempor, augue dui adipiscing ante, sed malesuada odio risus in tellus. Mauris scelerisque.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10240,'Gulliver','',0,0,'192.168.0.16','Quisque sit amet purus ac quam viverra malesuada. Sed arcu. Quisque velit lectus, bibendum nec, tristique eu, sagittis eget, massa. Donec laoreet odio a augue. http://www.saurus.info',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10241,'Mary','',0,0,'192.168.0.16','Aliquam magna lacus, gravida vel, fermentum vitae, commodo vitae, massa. Quisque tristique euismod turpis. Integer vitae sapien vel quam egestas euismod.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10242,'Wane','',0,0,'192.168.0.16','Etiam orci neque, porta id, vehicula id, vulputate a, magna. Vestibulum eu mi. In iaculis, nisi quis egestas tincidunt, dui magna scelerisque dolor, ut imperdiet sem sapien a leo.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10243,'Tom','',0,0,'192.168.0.16','Morbi augue purus, scelerisque accumsan, tincidunt ut, tincidunt vitae, est. Proin id nunc. Mauris felis elit, scelerisque sit amet, tincidunt id, pretium at, purus. Nam quis tortor non ipsum porta mattis. Morbi nisl. Aliquam sapien nunc, tempus quis, faucibus et, sagittis in, velit. Proin quis nisi eget lacus sollicitudin dapibus.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10244,'Amadeus','',0,0,'192.168.0.16','Phasellus ultrices rutrum leo!',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10228,'Jim','',0,0,'192.168.0.16','Curabitur aliquet purus et nulla. Nam aliquet ullamcorper enim. Donec commodo viverra dui. Praesent sodales malesuada turpis. Aliquam ultricies, mi eu eleifend tempor, augue dui adipiscing ante, sed malesuada odio risus in tellus. Mauris scelerisque.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10229,'Jessica Tim','some@body.com',0,0,'192.168.0.16','Mauris iaculis tellus eget pede accumsan hendrerit: http://www.saurus.info. Etiam elit. Quisque sem nisl, consequat eget, porta at, porta non, dui. In nec lacus. Sed eget lacus. Pellentesque tempus massa nec velit. Cras elit justo, accumsan sit amet, mattis a, nonummy a, velit.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10230,'Humbert','',0,0,'192.168.0.16','Quisque enim augue, pharetra in, iaculis et, rhoncus a, lorem. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Sed volutpat magna non tellus. Maecenas semper nibh tincidunt nunc. Morbi tellus ipsum, tincidunt non, posuere sit amet, cursus nec, augue. Vestibulum egestas arcu eu mauris. Sed vitae enim ac lacus eleifend lobortis.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10231,'Kati','',0,0,'192.168.0.16','Suspendisse potenti. Aliquam erat volutpat. Cras aliquet, urna vel semper fringilla, tortor ligula adipiscing diam, id elementum magna elit non ligula. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque dictum. Integer malesuada lorem vel elit. Ut ut tellus eget ante interdum venenatis. Duis et nulla.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10232,'Xena','',0,0,'192.168.0.16','Ut porttitor tortor ut dui. Pellentesque varius felis ac libero hendrerit iaculis. Nunc ipsum. Aliquam tellus lacus, pulvinar at, tempor ac, facilisis quis, velit. Fusce tortor ante, semper ut, suscipit vel, dignissim id, enim. Donec nunc nisl, semper quis, tempor at, porttitor vel, felis. :)',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10233,'Jane','',0,0,'192.168.0.16','Quisque a arcu. Aenean consequat, leo id mollis sagittis, ligula magna adipiscing quam, ac aliquet nisl justo nec dolor. Sed porta nulla eget odio. Sed convallis sapien eu mauris. Fusce sem leo, ultrices sed, rutrum quis, vestibulum id, mauris. Phasellus hendrerit velit eget erat. Sed nec mauris ac justo lobortis facilisis. Duis et velit sit amet tortor consequat rhoncus. Etiam magna. Suspendisse id turpis et leo sodales mattis. Phasellus a risus quis sapien vehicula auctor. Nam accumsan, mi eget tincidunt tincidunt, est quam adipiscing tortor, ut consequat nisl metus sed mauris. Vivamus semper tellus a lorem. Aliquam erat volutpat. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Curabitur et dolor id turpis pharetra blandit. ',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10234,'Claire','',0,0,'192.168.0.16','Cras fermentum bibendum est. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Etiam orci risus, sollicitudin at, luctus ut, volutpat at, elit.',19,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10235,'Ruth','',0,0,'192.168.0.16','Vivamus facilisis pellentesque arcu: http://www.saurus.info Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Maecenas convallis accumsan ante. Nullam in dolor. Curabitur felis turpis, varius ut, sollicitudin vitae, consequat sed, mi. Etiam id felis sed neque nonummy congue. Duis vitae augue.  :)',19,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10236,'Margaret','',0,0,'192.168.0.16','Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Ut justo ligula, venenatis sit amet, suscipit nec, hendrerit quis, diam. Etiam sit amet justo. In hac habitasse platea dictumst.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10237,'Don','',0,0,'192.168.0.16','Urabitur porttitor risus a ligula. Donec gravida auctor lorem. Vestibulum justo lorem, eleifend ac, semper ac, varius id, risus. In leo enim, gravida mattis, pretium sit amet, ornare ut, ligula. Donec pulvinar.',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10147,'Claire','',0,0,'192.168.0.16','Cras fermentum bibendum est. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Etiam orci risus, sollicitudin at, luctus ut, volutpat at, elit.',19,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `obj_kommentaar` VALUES (10149,'Ruth','',0,0,'192.168.0.16','Vivamus facilisis pellentesque arcu: http://www.saurus.info Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Maecenas convallis accumsan ante. Nullam in dolor. Curabitur felis turpis, varius ut, sollicitudin vitae, consequat sed, mi. Etiam id felis sed neque nonummy congue. Duis vitae augue.  :)',19,NULL)"); echo '. '; flush();

// Table structure for table `obj_link`

new SQL("DROP TABLE IF EXISTS `obj_link`"); echo '. '; flush();
new SQL("CREATE TABLE `obj_link` (
  `url` varchar(255) default NULL,
  `on_uusaken` tinyint(1) unsigned NOT NULL default '0',
  `tiitel` text,
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`objekt_id`)
)"); echo '. '; flush();

// Dumping data for table `obj_link`

new SQL("INSERT INTO `obj_link` VALUES ('http://www.saurus.info',1,NULL,10042)"); echo '. '; flush();
new SQL("INSERT INTO `obj_link` VALUES ('http://www.sauropol.com',1,NULL,10564)"); echo '. '; flush();
new SQL("INSERT INTO `obj_link` VALUES ('http://www.sauropol.com',1,NULL,10565)"); echo '. '; flush();
new SQL("INSERT INTO `obj_link` VALUES ('http://www.saurus.ee',1,'',10205)"); echo '. '; flush();

// Table structure for table `obj_pilt`

new SQL("DROP TABLE IF EXISTS `obj_pilt`"); echo '. '; flush();
new SQL("CREATE TABLE `obj_pilt` (
  `fail` varchar(255) NOT NULL default '',
  `kirjeldus` text,
  `autor` varchar(255) default NULL,
  `size` int(10) unsigned NOT NULL default '0',
  `tyyp` varchar(10) default NULL,
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  `mime_tyyp` varchar(255) default NULL,
  `sisu_blob` longblob,
  `vaike_blob` blob,
  PRIMARY KEY  (`objekt_id`),
  KEY `autor` (`autor`)
)"); echo '. '; flush();

// Dumping data for table `obj_pilt`


// Table structure for table `obj_rubriik`

new SQL("DROP TABLE IF EXISTS `obj_rubriik`"); echo '. '; flush();
new SQL("CREATE TABLE `obj_rubriik` (
  `kirjeldus` text,
  `on_peida_vmenyy` tinyint(1) unsigned default '0',
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  `on_kp_nahtav` tinyint(1) unsigned NOT NULL default '0',
  `art_arv` smallint(5) unsigned NOT NULL default '0',
  `on_printlink` enum('0','1') NOT NULL default '0',
  `on_meilinglist` enum('0','1') NOT NULL default '0',
  `on_alamartiklid` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`objekt_id`)
)"); echo '. '; flush();

// Dumping data for table `obj_rubriik`

new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,1,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,13,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,44,0,0,'1','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,6246,0,0,'1','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,385,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,382,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,10095,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,10093,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,10088,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,10089,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,10090,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,10091,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,6256,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,10029,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,10033,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,10044,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES (NULL,0,10087,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES ('',0,10193,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES ('',0,10194,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES ('',0,10198,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES ('',0,10200,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES ('',0,10201,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES ('',0,10203,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES ('',0,10204,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES ('',0,10211,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES ('',0,10212,0,0,'0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `obj_rubriik` VALUES ('',0,10213,0,0,'0','0','0')"); echo '. '; flush();

// Table structure for table `object_profiles`

new SQL("DROP TABLE IF EXISTS `object_profiles`"); echo '. '; flush();
new SQL("CREATE TABLE `object_profiles` (
  `profile_id` int(4) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `data` text,
  `source_table` varchar(50) default NULL,
  `is_predefined` char(1) default NULL,
  `is_default` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`profile_id`),
  UNIQUE KEY `name` (`name`),
  KEY `source_table` (`source_table`)
)"); echo '. '; flush();

// Dumping data for table `object_profiles`

new SQL("INSERT INTO `object_profiles` VALUES (124,'Room','a:3:{s:6:\"number\";a:7:{s:4:\"name\";s:6:\"number\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:5:\"floor\";a:7:{s:4:\"name\";s:5:\"floor\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:5:\"notes\";a:7:{s:4:\"name\";s:5:\"notes\";s:4:\"type\";s:8:\"TEXTAREA\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:4:\"text\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}}','obj_resource',NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `object_profiles` VALUES (127,'Company','a:5:{s:4:\"name\";a:9:{s:4:\"name\";s:4:\"name\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:0;}s:7:\"website\";a:7:{s:4:\"name\";s:7:\"website\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:7:\"address\";a:7:{s:4:\"name\";s:7:\"address\";s:4:\"type\";s:8:\"TEXTAREA\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:4:\"text\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:5:\"phone\";a:7:{s:4:\"name\";s:5:\"phone\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:5:\"notes\";a:9:{s:4:\"name\";s:5:\"notes\";s:4:\"type\";s:8:\"TEXTAREA\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:4:\"text\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:0;}}','groups',NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `object_profiles` VALUES (80,'Files','a:4:{s:3:\"aeg\";a:9:{s:4:\"name\";s:3:\"aeg\";s:4:\"type\";s:8:\"DATETIME\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:8:\"datetime\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:1;}s:6:\"author\";a:9:{s:4:\"name\";s:6:\"author\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:0;}s:5:\"notes\";a:9:{s:4:\"name\";s:5:\"notes\";s:4:\"type\";s:8:\"TEXTAREA\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:4:\"text\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:0;}s:8:\"mimetype\";a:9:{s:4:\"name\";s:8:\"mimetype\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:0;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:0;}}','obj_file','1',1)"); echo '. '; flush();
new SQL("INSERT INTO `object_profiles` VALUES (135,'country','a:1:{s:4:\"name\";a:7:{s:4:\"name\";s:4:\"name\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}}','ext_country',NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `object_profiles` VALUES (136,'device','a:2:{s:6:\"number\";a:7:{s:4:\"name\";s:6:\"number\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:5:\"notes\";a:7:{s:4:\"name\";s:5:\"notes\";s:4:\"type\";s:8:\"TEXTAREA\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:4:\"text\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}}','obj_resource',NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `object_profiles` VALUES (55,'Document',NULL,'obj_dokument','1',1)"); echo '. '; flush();
new SQL("INSERT INTO `object_profiles` VALUES (38,'Contact','a:12:{s:3:\"tel\";a:7:{s:4:\"name\";s:3:\"tel\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:6:\"mobile\";a:7:{s:4:\"name\";s:6:\"mobile\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:9:\"birthdate\";a:7:{s:4:\"name\";s:9:\"birthdate\";s:4:\"type\";s:4:\"DATE\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:4:\"date\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:5:\"title\";a:7:{s:4:\"name\";s:5:\"title\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:7:\"country\";a:7:{s:4:\"name\";s:7:\"country\";s:4:\"type\";s:6:\"SELECT\";s:13:\"source_object\";s:3:\"135\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:4:\"city\";a:7:{s:4:\"name\";s:4:\"city\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:7:\"address\";a:7:{s:4:\"name\";s:7:\"address\";s:4:\"type\";s:8:\"TEXTAREA\";s:13:\"source_object\";s:0:\"\";s:7:\"db_type\";s:4:\"text\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;}s:10:\"postalcode\";a:9:{s:4:\"name\";s:10:\"postalcode\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;s:10:\"is_general\";i:0;}s:5:\"notes\";a:9:{s:4:\"name\";s:5:\"notes\";s:4:\"type\";s:8:\"TEXTAREA\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:4:\"text\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;s:10:\"is_general\";i:0;}s:8:\"username\";a:9:{s:4:\"name\";s:8:\"username\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:0;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:0;}s:9:\"firstname\";a:9:{s:4:\"name\";s:9:\"firstname\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:0;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:0;}s:8:\"lastname\";a:9:{s:4:\"name\";s:8:\"lastname\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:0;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:0;}}','users','1',1)"); echo '. '; flush();
new SQL("INSERT INTO `object_profiles` VALUES (137,'Article','a:5:{s:6:\"author\";a:9:{s:4:\"name\";s:6:\"author\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:1;}s:3:\"aeg\";a:9:{s:4:\"name\";s:3:\"aeg\";s:4:\"type\";s:8:\"DATETIME\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:8:\"datetime\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:1;}s:18:\"avaldamisaeg_algus\";a:9:{s:4:\"name\";s:18:\"avaldamisaeg_algus\";s:4:\"type\";s:8:\"DATETIME\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:8:\"datetime\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:1;}s:17:\"avaldamisaeg_lopp\";a:9:{s:4:\"name\";s:17:\"avaldamisaeg_lopp\";s:4:\"type\";s:8:\"DATETIME\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:8:\"datetime\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:1;}s:17:\"fulltext_keywords\";a:9:{s:4:\"name\";s:17:\"fulltext_keywords\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:4:\"text\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:0;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:1;}}','obj_artikkel','1',1)"); echo '. '; flush();
new SQL("INSERT INTO `object_profiles` VALUES (138,'Timezones','a:3:{s:4:\"name\";a:9:{s:4:\"name\";s:4:\"name\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;s:10:\"is_general\";i:0;}s:7:\"UTC_dif\";a:9:{s:4:\"name\";s:7:\"UTC_dif\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:5:\"float\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;s:10:\"is_general\";i:0;}s:12:\"php_variable\";a:9:{s:4:\"name\";s:12:\"php_variable\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;s:10:\"is_general\";i:0;}}','ext_timezones',NULL,0)"); echo '. '; flush();
new SQL("INSERT INTO `object_profiles` VALUES (139,'converted_event','a:6:{s:6:\"author\";a:9:{s:4:\"name\";s:6:\"author\";s:4:\"type\";s:4:\"TEXT\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:7:\"varchar\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:1;}s:3:\"aeg\";a:9:{s:4:\"name\";s:3:\"aeg\";s:4:\"type\";s:8:\"DATETIME\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:8:\"datetime\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:1;}s:18:\"avaldamisaeg_algus\";a:9:{s:4:\"name\";s:18:\"avaldamisaeg_algus\";s:4:\"type\";s:8:\"DATETIME\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:8:\"datetime\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:1;}s:17:\"avaldamisaeg_lopp\";a:9:{s:4:\"name\";s:17:\"avaldamisaeg_lopp\";s:4:\"type\";s:8:\"DATETIME\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:8:\"datetime\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:1;s:10:\"is_general\";i:1;}s:9:\"starttime\";a:9:{s:4:\"name\";s:9:\"starttime\";s:4:\"type\";s:8:\"DATETIME\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:8:\"datetime\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;s:10:\"is_general\";i:0;}s:7:\"endtime\";a:9:{s:4:\"name\";s:7:\"endtime\";s:4:\"type\";s:8:\"DATETIME\";s:13:\"source_object\";s:0:\"\";s:13:\"default_value\";s:0:\"\";s:7:\"db_type\";s:8:\"datetime\";s:11:\"is_required\";i:0;s:9:\"is_active\";i:1;s:13:\"is_predefined\";i:0;s:10:\"is_general\";i:0;}}','obj_artikkel',NULL,0)"); echo '. '; flush();

// Table structure for table `objekt`

new SQL("DROP TABLE IF EXISTS `objekt`"); echo '. '; flush();
new SQL("CREATE TABLE `objekt` (
  `objekt_id` bigint(11) NOT NULL auto_increment,
  `pealkiri` varchar(255) NOT NULL default '',
  `on_pealkiri` tinyint(1) default '1',
  `tyyp_id` int(3) NOT NULL default '0',
  `author` varchar(100) default NULL,
  `on_avaldatud` tinyint(1) NOT NULL default '0',
  `keel` smallint(1) NOT NULL default '0',
  `kesk` smallint(5) unsigned NOT NULL default '0',
  `ttyyp_id` smallint(5) unsigned default NULL,
  `pealkiri_strip` varchar(255) default NULL,
  `sisu_strip` mediumtext,
  `fulltext_keywords` text,
  `on_foorum` tinyint(1) unsigned default NULL,
  `aeg` datetime NOT NULL default '0000-00-00 00:00:00',
  `meta_keywords` text,
  `meta_title` varchar(255) default NULL,
  `meta_description` text,
  `count` bigint(20) unsigned NOT NULL default '0',
  `sys_alias` varchar(50) default NULL,
  `ttyyp_params` text,
  `avaldamisaeg_algus` datetime default NULL,
  `avaldamisaeg_lopp` datetime default NULL,
  `on_saadetud` tinyint(1) unsigned NOT NULL default '0',
  `page_ttyyp_id` smallint(5) unsigned NOT NULL default '0',
  `last_modified` int(11) unsigned default NULL,
  `related_objekt_id` bigint(11) unsigned NOT NULL default '0',
  `friendly_url` varchar(255) default NULL,
  `is_hided_in_menu` enum('0','1') NOT NULL default '0',
  `check_in` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `check_in_admin_id` int(10) unsigned NOT NULL default '0',
  `created_user_id` bigint(20) unsigned NOT NULL default '0',
  `created_user_name` varchar(255) NOT NULL default '',
  `changed_user_id` bigint(20) unsigned NOT NULL default '0',
  `changed_user_name` varchar(255) NOT NULL default '',
  `created_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `changed_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_commented_time` datetime default NULL,
  `comment_count` int(10) unsigned default '0',
  `repl_site_key` varchar(4) default NULL,
  PRIMARY KEY  (`objekt_id`),
  KEY `pealkiri` (`pealkiri`),
  KEY `tyyp_id` (`tyyp_id`),
  KEY `kesk` (`kesk`),
  KEY `aeg` (`aeg`),
  KEY `on_saadetud` (`on_saadetud`),
  KEY `friendly_url` (`friendly_url`),
  KEY `keel_avaldatud` (`keel`,`on_avaldatud`),
  KEY `repl_site_key` (`repl_site_key`),
  KEY `related_objekt_id` (`related_objekt_id`),
  KEY `avaldatud` (`avaldamisaeg_algus`,`avaldamisaeg_lopp`),
  FULLTEXT KEY `fulltext_search` (`pealkiri_strip`,`sisu_strip`),
  FULLTEXT KEY `fulltext_keywords` (`fulltext_keywords`)
) ENGINE=MyISAM"); echo '. '; flush();

// Dumping data for table `objekt`

new SQL("INSERT INTO `objekt` VALUES (1,'Sait',1,1,'',1,0,0,0,'Sait','',NULL,0,'2006-01-01 00:00:00','Saurus CMS sisuhaldustarkvara\r\nwww.saurus.ee','Saurus CMS sisuhaldustarkvara','',115,'home','site_name = ShowTime\nslogan = Saurus CMS out-of-the-box experience\npage_end_html = \n','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1187180404,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2999-12-31 00:00:00','2007-08-15 15:20:04',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (13,'Süsteem',1,1,'',1,0,0,0,'Süsteem','',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,4,'system','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1246363122,0,'susteem','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2999-12-31 00:00:00','2009-06-30 11:58:42',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (25,'Ei leitud ühtegi tulemust',1,2,'',1,0,0,0,'Ei leitud ühtegi tulemust','Palun muutke otsingukriteeriumit või kasutage  täppisotsingut.','',0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'tyhiotsing','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1176300153,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2001-11-07 00:00:00','2007-04-11 17:02:33',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (23,'Viga andmete sisestamisel',1,2,'',1,0,0,0,'Viga andmete sisestamisel','Palun kontrolli järgmiste väljade õigsust:  [error]','',0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'error_page','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1176300225,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2001-11-07 00:00:00','2007-04-11 17:03:45',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (24,'Aitäh',1,2,'',1,0,0,0,'Aitäh','Teie andmed on saadetud!','',0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'ok_page','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1176300209,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2001-11-07 00:00:00','2007-04-11 17:03:29',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (44,'Gallupi arhiiv',1,1,'',1,0,0,0,'Gallupi arhiiv',' ',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,3,'gallup_arhiiv','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1055169020,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2999-12-31 00:00:00','2003-06-09 17:30:20',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (428,'Vale kasutajanimi või parool',1,2,'',1,0,0,0,'Vale kasutajanimi või parool','Palun kontrollige andmeid.','',0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'login_incorrect','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1176300168,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2002-04-23 00:00:00','2007-04-11 17:02:48',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (382,'Site',1,1,'',1,1,0,0,'Site','',NULL,0,'2006-01-01 00:00:00','Saurus CMS web publishing\r\nwww.saurus.info','Saurus CMS content management system','',111,'home','site_name = ShowTime\nslogan = Saurus CMS out-of-the-box experience\npage_end_html = \n','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1187180375,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2999-12-31 00:00:00','2007-08-15 15:19:35',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (385,'System',1,1,'',1,1,0,0,'System','',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,8,'system','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1187180390,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2999-12-31 00:00:00','2007-08-15 15:19:50',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (389,'Sa oled juba korra hääletanud!',1,2,'',1,0,0,0,'Sa oled juba korra hääletanud!','Meie andmetel oled sa juba sellele küsitlusele vastanud.','',0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'gallup_ip_olemas','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1176300094,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2002-02-27 00:00:00','2007-04-11 17:01:34',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (424,'Parool saadetud',1,2,'',1,0,0,0,'Parool saadetud',' Parool on saadetud teile e-posti teel.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'unustatud_parool_saadetud','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098527457,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-04-22 00:00:00','2004-10-23 13:30:57',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (418,'Registreerimine õnnestus!',1,2,'',1,0,0,0,'Registreerimine õnnestus!','Täname, et registreerisite ennast veebisaidi kasutajaks.','',0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'kasutaja_registreeritud','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1176300130,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2002-04-19 00:00:00','2007-04-11 17:02:10',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (419,'Kasutaja andmed salvestatud',1,2,'',1,0,0,0,'Kasutaja andmed salvestatud',' Sinu andmed on edukalt muudetud.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'kasutaja_uuendatud','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098527356,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-04-19 00:00:00','2004-10-23 13:29:16',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10245,'Page not found',1,2,'Default Administrator',1,1,0,0,'Page not found',' The page you are looking for has not been found.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'404error','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145623718,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-21 15:48:15','2006-04-21 15:48:38',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (426,'No results',1,2,'',1,1,0,0,'No results','Did not find any articles or documents matching your search query, please try to refine your searh.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'tyhiotsing','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1164275033,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','2002-04-22 00:00:00','2006-11-23 11:43:53',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (427,'Kasutaja blokeeritud',1,2,'',1,0,0,0,'Kasutaja blokeeritud','        Sinu kasutajanimi on blokeeritud.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'kasutaja_locked','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098527267,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-04-23 00:00:00','2004-10-23 13:27:47',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (694,'Saiti sisenemine keelatud!',1,2,'',1,0,0,0,'Saiti sisenemine keelatud!','  Sinu IP aadressilt ei ole lubatud siseneda.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'your_IP_disabled','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098527219,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-07-24 00:00:00','2004-10-23 13:26:59',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6246,'Poll\'s archive',1,1,NULL,1,1,0,0,'Poll\'s archive',' ',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,3,'gallup_arhiiv','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','2999-12-31 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6245,'Error!',1,2,'',1,1,0,0,'Error!','  Please check the  following fields:   [error]',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,1,'error_page','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098528850,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-10-28 00:00:00','2004-10-23 13:54:10',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6247,'You have already voted!',1,2,'',1,1,0,0,'You have already voted!','  According to our records you have already voted.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'gallup_ip_olemas','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098528171,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-10-28 00:00:00','2004-10-23 13:42:51',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6248,'User blocked',1,2,'',1,1,0,0,'User blocked','   This user has been  blocked by site  administrator.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'kasutaja_locked','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098528226,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-10-28 00:00:00','2004-10-23 13:43:46',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6249,'Thank you',1,2,'',1,1,0,0,'Thank you','  Thank you for registering at our site!',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'kasutaja_registreeritud','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098528309,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-10-28 00:00:00','2004-10-23 13:45:09',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6250,'User profile has been changed!',1,2,'',1,1,0,0,'User profile has been changed!','  Your data has been successfully updated!',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'kasutaja_uuendatud','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098528339,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-10-28 00:00:00','2004-10-23 13:45:39',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6251,'Login incorrect',1,2,'',1,1,0,0,'Login incorrect','   Please check your username and password.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'login_incorrect','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098528600,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-10-28 00:00:00','2004-10-23 13:50:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6252,'Thank you!',1,2,'',1,1,0,0,'Thank you!','  Your data has been successfully submitted.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'ok_page','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098528829,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-10-28 00:00:00','2004-10-23 13:53:49',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6253,'Account data sent',1,2,'',1,1,0,0,'Account data sent','  You should receive an e-mail with your password  shortly.',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,0,'unustatud_parool_saadetud','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1098528558,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','2002-10-28 00:00:00','2004-10-23 13:49:18',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6256,'Recycle Bin',1,1,NULL,1,1,0,NULL,NULL,NULL,NULL,NULL,'2006-04-18 00:00:00',NULL,NULL,NULL,0,'trash',NULL,NULL,NULL,0,0,NULL,0,NULL,'0','0000-00-00 00:00:00',0,0,'Default Administrator',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','2009-06-30 14:08:46',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10208,'Sed varius elementum risus',1,2,'Default Administrator',1,0,0,0,'',' Aliquam porta quam nec lorem. Suspendisse eros nibh, sollicitudin et, placerat vel, euismod vestibulum, ipsum. Maecenas sollicitudin. Nullam vulputate auctor urna. Sed rutrum. Proin tincidunt. Vestibulum mattis iaculis sapien. Mauris ullamcorper purus et nulla. Morbi nunc ligula, tempor non, convallis vitae, laoreet eu, tellus. Mauris urna odio, rhoncus et, ornare elementum, varius quis, erat. Maecenas commodo, nulla nec gravida mattis, magna pede feugiat dui, nec consequat orci ligula eu neque. Morbi sit amet elit. In pellentesque mattis lorem. Maecenas hendrerit tincidunt dolor. Pellentesque nonummy, orci a placerat auctor, augue leo consequat nibh, sit amet viverra urna mauris a sapien. Maecenas cursus lorem eu dolor. Nulla sagittis pharetra orci. Nullam risus elit, tempus eu, vestibulum a, tincidunt vel, mauris. Donec a nisl eget dui lacinia sollicitudin. Nunc mauris. In eu sem. Quisque vitae libero sit amet velit venenatis lobortis. Aliquam erat volutpat. Aenean nulla massa, feugiat at, rhoncus nec, aliquam sed, lorem. Mauris quis purus sit amet lacus accumsan sodales. Suspendisse euismod ornare ipsum. Praesent varius tincidunt arcu. Morbi interdum tellus sed erat. ',NULL,0,'2005-08-05 00:00:00','','','',2,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145534029,0,'sed-varius-elementum-risus','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 14:53:49','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10029,'Homepage',1,1,'',1,1,0,0,'Homepage',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,560,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145451608,0,'homepage','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-19 16:00:08','0000-00-00 00:00:00','2009-07-01 11:02:09',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10032,'Welcome to Saurus CMS!',1,2,'Default Administrator',1,1,0,0,'Welcome to Saurus CMS!','Your installation of Saurus web content management system is completed successfully. What you see is a sample website with some content to get you started. Click&#160;on&#160;the menu links at right for available pre-defined content layouts. Edit content To start editing web content, type /editor after your site address e.g. sitename.com/editor or sitename.com/folder/editor. Administer To use site administration tools, type /admin after your site address e.g. sitename.com/admin or sitename.com/folder/admin. Develop Visit our support site&#160;for technical documentation, API reference and code samples.       We hope you enjoy your copy of Saurus CMS!',NULL,0,'2006-04-20 00:00:00',NULL,NULL,NULL,56,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1305282046,0,'welcome-to-saurus-cms','0','0000-00-00 00:00:00',19,19,'Default Administrator',19,'Default Administrator ','2006-04-20 09:37:40','2011-05-13 13:20:46','2006-04-20 17:09:32',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10033,'Sample templates',1,1,'',1,1,0,0,'Sample templates',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,81,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145515478,0,'sample-templates','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 09:44:38','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10044,'News with archive',1,1,'',1,1,0,1056,'News with archive',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,249,'','open_news = 1\ntotal_news = 3\n','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145540673,0,'news-with-archive','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 14:46:29','2006-04-20 16:44:33',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10042,'Saurus CMS',1,3,'',1,1,0,0,'Saurus CMS',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145629000,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 12:55:24','2006-04-21 17:16:40',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10205,'Saurus CMS',1,3,'',1,0,0,0,'Saurus CMS','',NULL,0,'0000-00-00 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1246446190,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 12:55:24','2009-07-01 11:03:10','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10204,'Artiklid',1,1,'',1,0,0,1040,'Artiklid',' ',NULL,0,'0000-00-00 00:00:00','','','',81,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145627621,0,'artiklid','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 15:23:38','2006-04-21 16:53:41','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10203,'Artiklid 2 veerus',1,1,'',1,0,0,1041,'Artiklid 2 veerus',' ',NULL,0,'0000-00-00 00:00:00','','','',45,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145627597,0,'artiklid-2-veerus','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 15:16:21','2006-04-21 16:53:17','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10201,'Uudised arhiiviga',1,1,'',1,0,0,1056,'Uudised arhiiviga',' ',NULL,0,'0000-00-00 00:00:00','','','',238,'','open_news = 1\ntotal_news = 3\n','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145626827,0,'uudised-arhiiviga','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 14:46:29','2006-04-21 16:40:27','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10200,'Peidetud rubriik',1,1,'',1,0,0,0,'Peidetud rubriik',' ',NULL,0,'0000-00-00 00:00:00','','','',32,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145626204,0,'peidetud-rubriik','1','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 15:17:40','2006-04-21 16:30:04','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10198,'Foorum',1,1,'',1,0,0,1045,'Foorum',' ',NULL,0,'0000-00-00 00:00:00','','','',65,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145626037,0,'foorum','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 15:12:09','2006-04-21 16:27:17','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10197,'Tere tulemast!',1,2,'Default Administrator',1,0,0,0,'Tere tulemast!','Oled edukalt paigaldanud Saurus CMS sisuhaldustarkvara. Näed hetkel lihtsat veebisaiti mille oleme ette valmistanud et saaksid alustada oma saidi sisu toimetamist või kujundusega sidumist. Paremal menüüs klikkides leiad näidised erinevatest tootega kaasas olevatest sisumallidest. Toimeta sisu Veebi sisu toimetamiseks lisa oma veebisaidi aadressile /editor: näiteks firma.ee/editor või firma.ee/kataloog/editor. Administreeri Administreerimisvahendid leiad /admin osast. Täienda vastavalt oma veebi aadressi: firma.ee/admin või firma.ee/kataloog/admin. Arenda Oma kujunduse sidumiseks ja paigalduskomplektiga kaasasoleva funktsionaalsuse täiendamiseks uuri administreerimisvahendeid ja külasta inglisekeelset tugiveebi kust leiad tehnilise dokumentatsiooni, keelesüntaksi ja koodinäidised. Edukat toimetamist!',NULL,0,'2006-04-20 00:00:00','','','',56,'','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1305282120,0,'tere-tulemast','0','0000-00-00 00:00:00',19,19,'Default Administrator',19,'Default Administrator ','2006-04-20 09:37:40','2011-05-13 13:22:00','2006-04-20 17:09:32',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10565,'Sauropol',1,3,'',1,0,0,0,'Sauropol','',NULL,0,'2009-07-01 11:03:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1246446218,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2009-07-01 11:03:38','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10194,'Avalehekülg',1,1,'',1,0,0,0,'Avalehekülg','',NULL,0,'0000-00-00 00:00:00','','','',394,'','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1165508603,0,'avalehekulg','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-19 16:00:08','2006-12-07 18:23:23','2009-07-01 11:03:23',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10193,'Sisumallid',1,1,'',1,0,0,0,'Sisumallid',' ',NULL,0,'0000-00-00 00:00:00','','','',69,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145626024,0,'sisumallid','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 09:44:38','2006-04-21 16:27:04','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10083,'Sed varius elementum risus',1,2,'Default Administrator',1,1,0,0,'Sed varius elementum risus',' Aliquam porta quam nec lorem. Suspendisse eros nibh, sollicitudin et, placerat vel, euismod vestibulum, ipsum. Maecenas sollicitudin. Nullam vulputate auctor urna. Sed rutrum. Proin tincidunt. Vestibulum mattis iaculis sapien. Mauris ullamcorper purus et nulla. Morbi nunc ligula, tempor non, convallis vitae, laoreet eu, tellus. Mauris urna odio, rhoncus et, ornare elementum, varius quis, erat. Maecenas commodo, nulla nec gravida mattis, magna pede feugiat dui, nec consequat orci ligula eu neque. Morbi sit amet elit. In pellentesque mattis lorem. Maecenas hendrerit tincidunt dolor. Pellentesque nonummy, orci a placerat auctor, augue leo consequat nibh, sit amet viverra urna mauris a sapien. Maecenas cursus lorem eu dolor. Nulla sagittis pharetra orci. Nullam risus elit, tempus eu, vestibulum a, tincidunt vel, mauris. Donec a nisl eget dui lacinia sollicitudin. Nunc mauris. In eu sem. Quisque vitae libero sit amet velit venenatis lobortis. Aliquam erat volutpat. Aenean nulla massa, feugiat at, rhoncus nec, aliquam sed, lorem. Mauris quis purus sit amet lacus accumsan sodales. Suspendisse euismod ornare ipsum. Praesent varius tincidunt arcu. Morbi interdum tellus sed erat. ',NULL,0,'2005-08-05 00:00:00',NULL,NULL,NULL,2,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145534029,0,'sed-varius-elementum-risus','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 14:53:49','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10084,'Aliquam sit amet tellus sit amet erat cursus ullamcorper',1,2,'Default Administrator',1,1,0,0,'Aliquam sit amet tellus sit amet erat cursus ullamcorper',' Integer laoreet, pede in pretium congue, erat mi nonummy orci, nec pharetra lacus elit nec lectus. In eget ipsum. Aliquam adipiscing placerat erat. Ut posuere diam quis metus. Praesent facilisis congue arcu. Etiam iaculis mi ut ipsum. Aliquam non turpis. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse ante. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nunc lorem mi, aliquam et, sagittis non, gravida vel, urna. Aliquam erat volutpat. Aliquam sit amet tellus sit amet erat cursus ullamcorper. Nam eget sem at arcu dignissim dapibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean nisi eros, feugiat sed, tempus vel, luctus id, tortor. In elementum urna vitae pede. Sed nec arcu ut elit tempor semper. Aenean volutpat leo id dui. Nulla lacinia, leo sit amet vehicula vestibulum, nisi lectus pulvinar odio, in interdum libero pede a nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Donec suscipit gravida massa. Morbi eget enim vitae erat molestie porttitor. Sed commodo venenatis massa. ',NULL,0,'2005-09-01 00:00:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145534065,0,'aliquam-sit-amet-tellus-sit-amet-erat-cursus-ullamcorper','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 14:54:25','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10085,'Sed gravida consectetuer nisi',1,2,'Default Administrator',1,1,0,0,'Sed gravida consectetuer nisi',' Nullam tempor, nibh et volutpat placerat, lectus leo dignissim libero, blandit venenatis enim magna vitae mauris. Nullam pellentesque magna. Nullam tempor turpis eu elit. Aliquam erat volutpat. Aliquam magna. Sed iaculis, urna non accumsan ornare, neque dolor porttitor velit, id interdum metus dolor at dui. Curabitur tincidunt magna id eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Vestibulum eu neque sed odio tristique cursus. Nunc est est, placerat sed, rhoncus vitae, tempor at, felis. Fusce sem magna, iaculis sit amet, imperdiet nec, suscipit nec, eros. Aenean aliquam mauris sit amet augue. Ut in nunc nec neque sagittis nonummy. Praesent est. Duis nec massa ac sapien luctus mattis. Phasellus tortor sapien, scelerisque vitae, gravida id, imperdiet quis, justo. Mauris mauris velit, adipiscing dignissim, euismod vitae, tincidunt ut, libero. Cras ut nisl. Sed iaculis nunc fringilla erat. Morbi fringilla quam eu mi. Sed gravida consectetuer nisi. Pellentesque consectetuer tempus justo. Quisque gravida. Praesent vel quam. Vivamus blandit dignissim risus. Donec libero. Nunc quis purus at magna suscipit scelerisque. Fusce sit amet justo vel diam iaculis ultricies. Ut felis diam, iaculis non, imperdiet tempor, rhoncus vitae, sem. Suspendisse potenti. Maecenas fermentum nisi eget pede. Duis sit amet sem vel dui faucibus condimentum. Curabitur dictum arcu. Morbi consectetuer dignissim orci. Aenean lectus pede, imperdiet eu, venenatis a, luctus nec, libero. Aenean sapien quam, faucibus non, placerat id, bibendum ac, eros. Nullam rutrum, nibh a suscipit lacinia, massa est pellentesque mi, eu mattis nisl ipsum sed diam. ',NULL,0,'2005-12-16 00:00:00',NULL,NULL,NULL,1,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145534096,0,'sed-gravida-consectetuer-nisi','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 14:54:56','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10087,'Forum',1,1,'',1,1,0,1045,'Forum',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,69,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145535129,0,'forum','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:12:09','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10088,'Aliquam sollicitudin',1,15,'',1,1,0,0,'Aliquam sollicitudin',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,8,'','email = \non_saada_email = 0\n','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145535282,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:14:42','0000-00-00 00:00:00','2006-04-20 15:55:53',3,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10089,'Integer et libero a magna fermentum bibendum',1,15,'',1,1,0,0,'Integer et libero a magna fermentum bibendum',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,9,'','email = \non_saada_email = 0\n','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145535298,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:14:58','0000-00-00 00:00:00','2006-04-20 15:49:30',1,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10090,'Pellentesque a diam',1,15,'',1,1,0,0,'Pellentesque a diam',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,26,'','email = \non_saada_email = 0\n','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145535312,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:15:12','0000-00-00 00:00:00','2006-04-20 15:45:31',2,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10091,'Articles in 2 columns',1,1,'',1,1,0,1041,'Articles in 2 columns',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,48,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145535437,0,'articles-in-2-columns','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 15:16:21','2006-04-20 15:17:17',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10093,'Hidden section',1,1,'',1,1,0,0,'Hidden section',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,26,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145535460,0,'hidden-section','1','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:17:40','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10094,'Hidden section',1,2,'Default Administrator',1,1,0,0,'Hidden section',' The current section \"Hidden section\" is published but marked with \"Hide in navigation menu\" which makes the section name to disappear from menus and sitemap. You can tell the hidden status by the yellow colour of the v-shaped action button. The contents of the section are still visible if you know the direct link or use site search.',NULL,0,'2006-04-20 00:00:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145536370,0,'hidden-section-2','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 15:20:11','2006-04-20 15:32:50',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10095,'Article list',1,1,'',1,1,0,1040,'Article list',' ',NULL,0,'0000-00-00 00:00:00',NULL,NULL,NULL,73,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145535818,0,'article-list','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:23:38','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10096,'Unpublished article',1,2,'Default Administrator',0,1,0,0,'Unpublished article',' This article is unpublished and cannot be seen by any of the site visitors. You can tell the unpublished status by the red colour of the v-shaped action button.  ',NULL,0,'2006-04-20 00:00:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145536456,0,'unpublished-article','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:34:16','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10098,'Article with comments',1,2,'Default Administrator',1,1,0,0,'Article with comments',' This article has the option \"Allow comments\" ticked. Suspendisse nec elit at lacus pulvinar elementum. Nam egestas. Vivamus gravida arcu sit amet tortor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Donec lacinia dui eget nulla. Nam vitae libero ut metus molestie rutrum. Aenean congue cursus erat. Nam commodo consectetuer ante. Curabitur sodales. Donec semper ipsum quis elit. Pellentesque a tellus.',NULL,1,'2006-04-20 00:00:00',NULL,NULL,NULL,13,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145539654,0,'article-with-comments','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 15:36:06','2006-04-20 16:27:34','2006-04-20 16:29:17',2,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10099,'Curabitur aliquet purus et nulla.',1,14,NULL,1,1,0,NULL,'Curabitur aliquet purus et nulla.','Curabitur aliquet purus et nulla. Nam aliquet ullamcorper enim. Donec commodo viverra dui. Praesent sodales malesuada turpis. Aliquam ultricies, mi eu eleifend tempor, augue dui adipiscing ante, sed malesuada odio risus in tellus. Mauris scelerisque.',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,4,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','2006-04-20 15:44:30',2,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10100,'Re: Curabitur aliquet purus et nulla.',1,14,NULL,1,1,0,NULL,'Re: Curabitur aliquet purus et nulla.','Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Ut justo ligula, venenatis sit amet, suscipit nec, hendrerit quis, diam. Etiam sit amet justo. In hac habitasse platea dictumst.',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10101,'Re: Curabitur aliquet purus et nulla.',1,14,NULL,1,1,0,NULL,'Re: Curabitur aliquet purus et nulla.','Urabitur porttitor risus a ligula. Donec gravida auctor lorem. Vestibulum justo lorem, eleifend ac, semper ac, varius id, risus. In leo enim, gravida mattis, pretium sit amet, ornare ut, ligula. Donec pulvinar.',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10102,'Aliquam gravida',1,14,NULL,1,1,0,NULL,'Aliquam gravida','Mauris iaculis tellus eget pede accumsan hendrerit: http://www.saurus.info. Etiam elit. Quisque sem nisl, consequat eget, porta at, porta non, dui. In nec lacus. Sed eget lacus. Pellentesque tempus massa nec velit. Cras elit justo, accumsan sit amet, mattis a, nonummy a, velit.',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,23,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','2006-04-20 15:46:22',1,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10111,'Aenean vulputate fermentum nunc',1,14,NULL,1,1,0,NULL,'Aenean vulputate fermentum nunc','Suspendisse potenti. Aliquam erat volutpat. Cras aliquet, urna vel semper fringilla, tortor ligula adipiscing diam, id elementum magna elit non ligula. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque dictum. Integer malesuada lorem vel elit. Ut ut tellus eget ante interdum venenatis. Duis et nulla.',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (6257,'Recycle Bin',1,1,NULL,1,0,0,NULL,NULL,NULL,NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,0,'trash',NULL,NULL,NULL,0,0,NULL,0,NULL,'0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10107,'Re: Aliquam gravida',1,14,'',1,1,0,0,'Re: Aliquam gravida','Ut sem magna, pellentesque a, tincidunt quis, adipiscing a, tortor. Sed orci :). Nullam nec lacus sed nunc porttitor tristique. Etiam eu nisi. Mauris enim erat, interdum a, tincidunt sed, auctor condimentum, lectus. Suspendisse diam. Cras et enim. Nam in elit eget quam venenatis facilisis. Suspendisse leo massa, laoreet eget, condimentum accumsan, semper a, nunc.',NULL,0,'2006-04-20 00:00:00',NULL,NULL,NULL,11,NULL,'','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145608229,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','0000-00-00 00:00:00','2006-04-21 11:30:29','2006-04-20 15:47:46',1,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10171,'Pellentesque aliquam, leo tincidunt euismod tempo',1,2,'Default Administrator',1,1,0,0,'Pellentesque aliquam, leo tincidunt euismod tempo',' Morbi nec odio. Sed at ante. Suspendisse orci mauris, tempus at, hendrerit sit amet, sollicitudin sed, mi. Nullam tincidunt tincidunt tortor. Fusce augue enim, convallis sit amet, porttitor vitae, interdum sed, felis. Nulla condimentum. Praesent egestas venenatis dolor. Fusce tortor neque, dictum ut, feugiat a, adipiscing in, odio. Aenean eget est. Nam odio tellus, vehicula quis, tempor nec, auctor non, turpis. Aenean ac lacus. Etiam adipiscing nunc a erat. Nam vehicula tempus eros. Duis vitae nisl. Nunc quis mi. Etiam interdum, purus id vestibulum egestas, felis sapien ornare nulla, vel condimentum mi lectus a odio. Proin in ipsum ut nibh pellentesque sagittis. In ante. Nulla quam. Nulla orci pede, commodo id, commodo id, sollicitudin et, nibh. ',NULL,0,'2006-03-25 00:00:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145608771,0,'pellentesque-aliquam-leo-tincidunt-euismod-tempo','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-21 11:39:31','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10108,'Re: Re: Aliquam gravida',1,14,NULL,1,1,0,NULL,'Re: Re: Aliquam gravida','Phasellus ultrices rutrum leo!',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10109,'Donec dapibus',1,14,NULL,1,1,0,NULL,'Donec dapibus','Quisque enim augue, pharetra in, iaculis et, rhoncus a, lorem. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Sed volutpat magna non tellus. Maecenas semper nibh tincidunt nunc. Morbi tellus ipsum, tincidunt non, posuere sit amet, cursus nec, augue. Vestibulum egestas arcu eu mauris. Sed vitae enim ac lacus eleifend lobortis.',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10113,'Nunc in metus',1,14,NULL,1,1,0,NULL,'Nunc in metus','Ut porttitor tortor ut dui. Pellentesque varius felis ac libero hendrerit iaculis. Nunc ipsum. Aliquam tellus lacus, pulvinar at, tempor ac, facilisis quis, velit. Fusce tortor ante, semper ut, suscipit vel, dignissim id, enim. Donec nunc nisl, semper quis, tempor at, porttitor vel, felis. :)',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,3,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','2006-04-20 15:54:38',2,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10114,'Re: Nunc in metus',1,14,NULL,1,1,0,NULL,'Re: Nunc in metus','Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque rhoncus, erat eu ullamcorper elementum, nibh purus tincidunt ligula, ut dictum sapien erat ac urna. Etiam tempor sollicitudin leo. Aliquam erat volutpat. Sed sed nulla. Donec sollicitudin, ipsum quis adipiscing malesuada, dui augue posuere leo, ut tristique erat ipsum id nibh. Nunc mauris nulla, blandit lobortis, aliquet ac, viverra id, eros. Nullam mauris. Phasellus mattis. Nunc imperdiet sapien vel lacus.',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10116,'Re: Nunc in metus',1,14,NULL,1,1,0,NULL,'Re: Nunc in metus','Quisque sit amet purus ac quam viverra malesuada. Sed arcu. Quisque velit lectus, bibendum nec, tristique eu, sagittis eget, massa. Donec laoreet odio a augue. http://www.saurus.info',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10117,'Fusce lorem urna',1,14,NULL,1,1,0,NULL,'Fusce lorem urna','Quisque a arcu. Aenean consequat, leo id mollis sagittis, ligula magna adipiscing quam, ac aliquet nisl justo nec dolor. Sed porta nulla eget odio. Sed convallis sapien eu mauris. Fusce sem leo, ultrices sed, rutrum quis, vestibulum id, mauris. Phasellus hendrerit velit eget erat. Sed nec mauris ac justo lobortis facilisis. Duis et velit sit amet tortor consequat rhoncus. Etiam magna. Suspendisse id turpis et leo sodales mattis. Phasellus a risus quis sapien vehicula auctor. Nam accumsan, mi eget tincidunt tincidunt, est quam adipiscing tortor, ut consequat nisl metus sed mauris. Vivamus semper tellus a lorem. Aliquam erat volutpat. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Curabitur et dolor id turpis pharetra blandit. ',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,6,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','2006-04-20 15:57:03',3,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10118,'Re: Fusce lorem urna',1,14,NULL,1,1,0,NULL,'Re: Fusce lorem urna','Aliquam magna lacus, gravida vel, fermentum vitae, commodo vitae, massa. Quisque tristique euismod turpis. Integer vitae sapien vel quam egestas euismod.',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10119,'Re: Fusce lorem urna',1,14,NULL,1,1,0,NULL,'Re: Fusce lorem urna','Etiam orci neque, porta id, vehicula id, vulputate a, magna. Vestibulum eu mi. In iaculis, nisi quis egestas tincidunt, dui magna scelerisque dolor, ut imperdiet sem sapien a leo.',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10120,'Re: Fusce lorem urna',1,14,NULL,1,1,0,NULL,'Re: Fusce lorem urna','Morbi augue purus, scelerisque accumsan, tincidunt ut, tincidunt vitae, est. Proin id nunc. Mauris felis elit, scelerisque sit amet, tincidunt id, pretium at, purus. Nam quis tortor non ipsum porta mattis. Morbi nisl. Aliquam sapien nunc, tempus quis, faucibus et, sagittis in, velit. Proin quis nisi eget lacus sollicitudin dapibus.',NULL,NULL,'2006-04-20 00:00:00',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,0,0,0,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10121,'Forum',1,2,'Default Administrator',1,1,0,0,'Forum',' Nulla dignissim nibh id felis. Vestibulum elit urna, lobortis id, sagittis at, scelerisque at, tortor. Etiam odio nisi, tempus eu, pulvinar non, ullamcorper non, lectus. Duis orci orci, rutrum nec, elementum et, feugiat non, risus.',NULL,0,'2006-04-20 00:00:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145537947,0,'forum-2','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:59:07','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10246,'IP blocked',1,2,'Default Administrator',1,1,0,0,'IP blocked',' Your IP address is blocked.',NULL,0,'2006-04-21 00:00:00',NULL,NULL,NULL,0,'your_IP_disabled','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145623751,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-21 15:49:11','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10247,'Item added to the cart',1,2,'Default Administrator',1,1,0,0,'Item added to the cart','    The item has been added to the cart!  Back View cart',NULL,0,'2006-04-21 00:00:00',NULL,NULL,NULL,0,'add_cart','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145623788,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-21 15:49:48','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10248,'The cart has been saved',1,2,'Default Administrator',1,1,0,0,'The cart has been saved','  &lt;table width=\"100%\" cellspacing=\"0\" cellpadding=\"10\" border=\"0\"&gt; &lt;tbody&gt; &lt;tr&gt; &lt;td align=\"center\" class=\"boxhead\"&gt;&lt;strong&gt;The cart has been saved!&lt;/strong&gt;&lt;/td&gt;&lt;/tr&gt; &lt;tr&gt; &lt;td valign=\"center\" align=\"center\" class=\"txt\"&gt;&lt;a href=\"javascript:window.close()\"&gt;Back&lt;/a&gt;&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;',NULL,0,'2006-04-21 00:00:00',NULL,NULL,NULL,0,'save_cart','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145623815,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-21 15:50:15','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10147,'',1,14,'',1,1,0,0,'','Cras fermentum bibendum est. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Etiam orci risus, sollicitudin at, luctus ut, volutpat at, elit.',NULL,0,'2006-04-20 00:00:00',NULL,NULL,NULL,0,NULL,'','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145539700,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','0000-00-00 00:00:00','2006-04-20 16:28:20',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10149,'',1,14,'',1,1,0,0,'','Vivamus facilisis pellentesque arcu: http://www.saurus.info Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Maecenas convallis accumsan ante. Nullam in dolor. Curabitur felis turpis, varius ut, sollicitudin vitae, consequat sed, mi. Etiam id felis sed neque nonummy congue. Duis vitae augue.  :)',NULL,0,'2006-04-20 00:00:00',NULL,NULL,NULL,0,NULL,'','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145539792,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','0000-00-00 00:00:00','2006-04-20 16:29:52',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10151,'Lead & body',1,2,'Default Administrator',1,1,0,0,'Lead & body','Article can be split into lead and body. The current template â€œArticles: 1 columnâ€ then displays only lead with a link to the full article text.  Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Aenean ultrices libero nec felis. Sed imperdiet. Aenean et lacus. Etiam vel tortor. Mauris euismod. Nam ipsum diam, pharetra id, hendrerit quis, vulputate ut, augue. Duis tempor sodales nisi. Etiam euismod, leo non adipiscing placerat, lorem turpis gravida mi, sit amet condimentum velit dolor molestie nibh. Praesent elit est, tempus eleifend, porta sed, rhoncus eget, lacus. Nullam eleifend interdum augue. Quisque sed felis quis sem malesuada pulvinar. Donec placerat. Praesent arcu. Maecenas tortor. Curabitur purus pede, mattis eu, condimentum quis, tristique sit amet, ipsum. Suspendisse orci. Vestibulum vulputate.',NULL,0,'2006-04-20 00:00:00',NULL,NULL,NULL,3,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145541595,0,'lead-body','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 16:59:37','2006-04-20 16:59:55',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10152,'Suspendisse potenti',1,2,'Default Administrator',1,1,0,0,'Suspendisse potenti',' Nunc varius ante. Proin vitae magna at quam suscipit vehicula. Mauris sollicitudin urna id erat. Donec nec nisl ut urna sollicitudin faucibus:Pellentesque pellentesque. Aliquam iaculis congue erat.Donec ullamcorper. Fusce lacus magna, pretium vel.Mauris ultricies ipsum ut eros. Fusce blandit accumsan risus. Nulla aliquet. Duis sollicitudin orci id purus.Donec pretium laoreet erat. Nulla lectus. Cras suscipit nisi convallis lectus. Vestibulum magna urna, euismod nec, rutrum at.',NULL,0,'2006-04-20 00:00:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145541996,0,'suspendisse-potenti','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 17:03:40','2006-04-20 17:06:36',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10153,'Curabitur sollicitudin',1,2,'Default Administrator',1,1,6,0,'Curabitur sollicitudin',' Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Ut adipiscing mi et dui. Pellentesque mi justo, congue eget, malesuada ac, vehicula ut, mi. Nulla pellentesque. Curabitur sollicitudin ipsum. Nulla et orci. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam congue diam et felis vehicula pellentesque. Mauris vestibulum sollicitudin est. Nam bibendum magna quis urna.',NULL,1,'2006-04-20 00:00:00',NULL,NULL,NULL,3,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145541919,0,'curabitur-sollicitudin','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 17:04:53','2006-04-20 17:05:19',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10564,'Sauropol',1,3,'',1,1,0,0,'Sauropol','',NULL,0,'2009-07-01 11:02:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1246446148,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2009-07-01 11:02:28','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10250,'Toode on lisatud ostukorvi',1,2,'Default Administrator',1,0,0,0,'Toode on lisatud ostukorvi','    Toode on lisatud ostukorvi!  Tagasi Edasi',NULL,0,'2006-04-21 00:00:00',NULL,NULL,NULL,0,'add_cart','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145623927,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-21 15:51:26','2006-04-21 15:52:07',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10251,'Ostukorv on salvestatud',1,2,'Default Administrator',1,0,0,0,'Ostukorv on salvestatud','    Teie ostukorv on salvestatud!  Tagasi',NULL,0,'2006-04-21 00:00:00',NULL,NULL,NULL,0,'save_cart','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145623913,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-21 15:51:53','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10249,'Viga',1,2,'Default Administrator',1,0,0,0,'Viga','Seda lehekülge ei leitud.','',0,'2006-04-21 00:00:00',NULL,NULL,NULL,9,'404error','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1176300105,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-21 15:51:02','2007-04-11 17:01:45',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10166,'Morbi feugiat condimentum libero',1,2,'Default Administrator',1,1,0,0,'Morbi feugiat condimentum libero',' Aliquam eu mauris. Suspendisse sed augue. Aliquam orci neque, adipiscing a, fringilla quis, lacinia vel, nisi. Aliquam luctus, odio non faucibus imperdiet, justo tortor pharetra lacus, bibendum accumsan velit nisl pellentesque nisl. Aenean pharetra erat ullamcorper mi. Phasellus blandit, nisl a molestie scelerisque, justo nibh consectetuer pede, at facilisis sem lectus vitae lorem. Quisque nibh. Maecenas massa. Suspendisse porta dignissim dolor. Suspendisse pede nulla, consequat et, imperdiet at, vulputate vitae, massa. Etiam dolor odio, tincidunt id, consequat nonummy, aliquam eget, massa. Nulla facilisi. Sed non erat nec velit eleifend ultricies. Curabitur quis diam nec erat ultrices scelerisque. Quisque aliquet tincidunt sapien. Praesent quis ligula blandit urna adipiscing semper. Praesent massa. Sed fermentum ornare lorem. Nulla facilisi. ',NULL,0,'2006-02-01 00:00:00',NULL,NULL,NULL,1,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145546021,0,'morbi-feugiat-condimentum-libero','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 18:13:41','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10167,'Aenean metus',1,2,'Default Administrator',1,1,0,0,'Aenean metus',' Sed ornare dolor in leo. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Fusce feugiat tortor. Pellentesque consequat leo volutpat lectus. Fusce rhoncus, sem id rhoncus aliquet, erat lorem viverra erat, et molestie massa urna et mauris. Quisque adipiscing lacus placerat nibh. Ut mattis nulla sit amet diam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nunc dapibus vulputate metus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Integer vel ligula id enim commodo condimentum. Vivamus convallis ante a purus. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Phasellus vehicula, nibh nec tristique tincidunt, diam turpis commodo purus, ac pretium dui nisl ac nulla. Maecenas pede mauris, luctus a, rhoncus nec, interdum ut, pede. Duis quis augue dapibus dolor ultrices convallis. Proin ut sapien. Ut sit amet mi a urna aliquet dignissim. Maecenas placerat. Phasellus tempus risus vel est. Fusce venenatis dui a ipsum. Aliquam ut libero. Praesent tincidunt, tortor ornare vulputate dapibus, dui mauris euismod lectus, ut viverra tortor tortor in tortor. ',NULL,0,'2006-01-01 00:00:00',NULL,NULL,NULL,1,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145546138,0,'aenean-metus','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 18:15:38','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10168,'Morbi feugiat condimentum libero',1,2,'Default Administrator',1,1,0,0,'Morbi feugiat condimentum libero',' Aliquam eu mauris. Suspendisse sed augue. Aliquam orci neque, adipiscing a, fringilla quis, lacinia vel, nisi. Aliquam luctus, odio non faucibus imperdiet, justo tortor pharetra lacus, bibendum accumsan velit nisl pellentesque nisl. Aenean pharetra erat ullamcorper mi. Phasellus blandit, nisl a molestie scelerisque, justo nibh consectetuer pede, at facilisis sem lectus vitae lorem. Quisque nibh. Maecenas massa. Suspendisse porta dignissim dolor. Suspendisse pede nulla, consequat et, imperdiet at, vulputate vitae, massa. Etiam dolor odio, tincidunt id, consequat nonummy, aliquam eget, massa. Nulla facilisi. Sed non erat nec velit eleifend ultricies. Curabitur quis diam nec erat ultrices scelerisque. Quisque aliquet tincidunt sapien. Praesent quis ligula blandit urna adipiscing semper. Praesent massa. Sed fermentum ornare lorem. Nulla facilisi. ',NULL,0,'2006-03-16 00:00:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145546168,0,'morbi-feugiat-condimentum-libero-2','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 18:15:41','2006-04-20 18:16:08',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10169,'Curabitur eu nulla',1,2,'Default Administrator',1,1,0,0,'Curabitur eu nulla',' Curabitur enim. Ut urna enim, congue dapibus, ultricies nec, ultricies ut, ligula. Ut urna. In massa. Vivamus semper massa vitae nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In pharetra, lacus feugiat vestibulum volutpat, nisl dui ultrices leo, suscipit mollis urna justo sed elit. Duis sed lorem. In nonummy odio fermentum ligula. Praesent blandit risus id tortor. Pellentesque nulla. Nam diam mauris, vulputate eget, suscipit ut, cursus at, eros. Integer blandit dignissim purus. Aenean ornare auctor ante. Proin metus tortor, luctus a, facilisis id, elementum quis, augue. Quisque sed lorem vel pede rutrum volutpat. Morbi imperdiet eros vel nisi. Ut condimentum pellentesque tortor. Nulla magna. ',NULL,0,'2004-05-20 00:00:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145546248,0,'curabitur-eu-nulla','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 18:17:28','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10170,'Duis interdum quam',1,2,'Default Administrator',1,1,0,0,'Duis interdum quam',' Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nullam sit amet diam at sapien posuere suscipit. Praesent dictum eros et sapien. Donec volutpat, purus a malesuada molestie, orci velit euismod massa, eget sagittis felis magna elementum augue. Ut vitae pede. Curabitur eu velit. Mauris ac velit. Donec viverra nunc ac mauris. Ut pretium. Morbi velit augue, aliquam nec, tristique a, egestas imperdiet, nisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Ut et nisi. Vivamus venenatis suscipit ipsum. Duis vel felis vel turpis vulputate accumsan. Vivamus eleifend, massa ut aliquam tincidunt, est lectus aliquam ante, vel faucibus magna purus vitae magna. Ut condimentum luctus nisi. In justo elit, blandit vel, semper nec, rhoncus quis, risus. Praesent ut leo. Proin blandit urna vitae elit.',NULL,0,'2006-03-01 00:00:00',NULL,NULL,NULL,0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145608707,0,'duis-interdum-quam','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-21 11:38:27','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10209,'Aliquam sit amet tellus sit amet erat cursus ullamcorper',1,2,'Default Administrator',1,0,0,0,'',' Integer laoreet, pede in pretium congue, erat mi nonummy orci, nec pharetra lacus elit nec lectus. In eget ipsum. Aliquam adipiscing placerat erat. Ut posuere diam quis metus. Praesent facilisis congue arcu. Etiam iaculis mi ut ipsum. Aliquam non turpis. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse ante. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nunc lorem mi, aliquam et, sagittis non, gravida vel, urna. Aliquam erat volutpat. Aliquam sit amet tellus sit amet erat cursus ullamcorper. Nam eget sem at arcu dignissim dapibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean nisi eros, feugiat sed, tempus vel, luctus id, tortor. In elementum urna vitae pede. Sed nec arcu ut elit tempor semper. Aenean volutpat leo id dui. Nulla lacinia, leo sit amet vehicula vestibulum, nisi lectus pulvinar odio, in interdum libero pede a nisl. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Donec suscipit gravida massa. Morbi eget enim vitae erat molestie porttitor. Sed commodo venenatis massa. ',NULL,0,'2005-09-01 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145534065,0,'aliquam-sit-amet-tellus-sit-amet-erat-cursus-ullamcorper','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 14:54:25','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10210,'Sed gravida consectetuer nisi',1,2,'Default Administrator',1,0,0,0,'',' Nullam tempor, nibh et volutpat placerat, lectus leo dignissim libero, blandit venenatis enim magna vitae mauris. Nullam pellentesque magna. Nullam tempor turpis eu elit. Aliquam erat volutpat. Aliquam magna. Sed iaculis, urna non accumsan ornare, neque dolor porttitor velit, id interdum metus dolor at dui. Curabitur tincidunt magna id eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Vestibulum eu neque sed odio tristique cursus. Nunc est est, placerat sed, rhoncus vitae, tempor at, felis. Fusce sem magna, iaculis sit amet, imperdiet nec, suscipit nec, eros. Aenean aliquam mauris sit amet augue. Ut in nunc nec neque sagittis nonummy. Praesent est. Duis nec massa ac sapien luctus mattis. Phasellus tortor sapien, scelerisque vitae, gravida id, imperdiet quis, justo. Mauris mauris velit, adipiscing dignissim, euismod vitae, tincidunt ut, libero. Cras ut nisl. Sed iaculis nunc fringilla erat. Morbi fringilla quam eu mi. Sed gravida consectetuer nisi. Pellentesque consectetuer tempus justo. Quisque gravida. Praesent vel quam. Vivamus blandit dignissim risus. Donec libero. Nunc quis purus at magna suscipit scelerisque. Fusce sit amet justo vel diam iaculis ultricies. Ut felis diam, iaculis non, imperdiet tempor, rhoncus vitae, sem. Suspendisse potenti. Maecenas fermentum nisi eget pede. Duis sit amet sem vel dui faucibus condimentum. Curabitur dictum arcu. Morbi consectetuer dignissim orci. Aenean lectus pede, imperdiet eu, venenatis a, luctus nec, libero. Aenean sapien quam, faucibus non, placerat id, bibendum ac, eros. Nullam rutrum, nibh a suscipit lacinia, massa est pellentesque mi, eu mattis nisl ipsum sed diam. ',NULL,0,'2005-12-16 00:00:00','','','',1,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145534096,0,'sed-gravida-consectetuer-nisi','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 14:54:56','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10211,'Aliquam sollicitudin',1,15,'',1,0,0,0,'',' ',NULL,0,'0000-00-00 00:00:00','','','',7,'','email = \non_saada_email = 0\n','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145535282,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:14:42','0000-00-00 00:00:00','2006-04-20 15:55:53',3,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10212,'Integer et libero a magna fermentum bibendum',1,15,'',1,0,0,0,'',' ',NULL,0,'0000-00-00 00:00:00','','','',7,'','email = \non_saada_email = 0\n','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145535298,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:14:58','0000-00-00 00:00:00','2006-04-20 15:49:30',1,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10213,'Pellentesque a diam',1,15,'',1,0,0,0,'',' ',NULL,0,'0000-00-00 00:00:00','','','',19,'','email = \non_saada_email = 0\n','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145535312,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:15:12','0000-00-00 00:00:00','2006-04-20 15:45:31',2,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10214,'Peidetud rubriik',1,2,'Default Administrator',1,0,0,0,'Peidetud rubriik','See veebi rubriik on küll avalikustatud, kuid märgitud linnukesega \"Peida menüüs\" mille tulemusena ei ilmu ta avalikus osas menüüdes ega sisukaardil. Peidetud staatust märgib ka v-kujulise käsunupu kollane värv. Ehkki menüü ei ole külastajatele nähtav, saavad nad siiski selle sisuga tutvuda kui teavad otselinki või kasutavad saidi otsingut. ',NULL,0,'2006-04-20 00:00:00','','','',0,'','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1165573792,0,'peidetud-rubriik-2','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 15:20:11','2006-12-08 12:29:52','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10215,'Avaldamata artikkel',1,2,'Default Administrator',0,0,0,0,'Avaldamata artikkel','See artikkel on avaldamata ja pole seega veebi külastajatele nähtav. Avaldamata staatust märgib v-kujulise käsunupu punane värv.  ',NULL,0,'2006-04-20 00:00:00','','','',0,'','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1165573707,0,'avaldamata-artikkel','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 15:34:16','2006-12-08 12:28:27','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10216,'Sissejuhatus ja sisu',1,2,'Default Administrator',1,0,0,0,'Sissejuhatus ja sisu','Artikli võib jagada sissejuhatuseks ja sisuks. Siin kasutusel olev sisumall \"Articles: 1 column\" kuvab sellisel juhul nimekirjas sissejuhatuse koos lingiga artikli täielikule tekstile.  Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Aenean ultrices libero nec felis. Sed imperdiet. Aenean et lacus. Etiam vel tortor. Mauris euismod. Nam ipsum diam, pharetra id, hendrerit quis, vulputate ut, augue. Duis tempor sodales nisi. Etiam euismod, leo non adipiscing placerat, lorem turpis gravida mi, sit amet condimentum velit dolor molestie nibh. Praesent elit est, tempus eleifend, porta sed, rhoncus eget, lacus. Nullam eleifend interdum augue. Quisque sed felis quis sem malesuada pulvinar. Donec placerat. Praesent arcu. Maecenas tortor. Curabitur purus pede, mattis eu, condimentum quis, tristique sit amet, ipsum. Suspendisse orci. Vestibulum vulputate.',NULL,0,'2006-04-20 00:00:00','','','',7,'','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1246446365,0,'sissejuhatus-ja-sisu','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 16:59:37','2009-07-01 11:06:05','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10217,'Forum',1,2,'Default Administrator',1,0,0,0,'',' Nulla dignissim nibh id felis. Vestibulum elit urna, lobortis id, sagittis at, scelerisque at, tortor. Etiam odio nisi, tempus eu, pulvinar non, ullamcorper non, lectus. Duis orci orci, rutrum nec, elementum et, feugiat non, risus.',NULL,0,'2006-04-20 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145537947,0,'forum','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 15:59:07','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10219,'Kommentaaridega artikkel',1,2,'Default Administrator',1,0,0,0,'Kommentaaridega artikkel','Sellel artiklil on aktiivne märgend \"Kommentaarid lubatud\".Suspendisse nec elit at lacus pulvinar elementum. Nam egestas. Vivamus gravida arcu sit amet tortor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Donec lacinia dui eget nulla. Nam vitae libero ut metus molestie rutrum. Aenean congue cursus erat. Nam commodo consectetuer ante. Curabitur sodales. Donec semper ipsum quis elit. Pellentesque a tellus.',NULL,1,'2006-04-20 00:00:00','','','',16,'','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1246446353,0,'kommentaaridega-artikkel','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 15:36:06','2009-07-01 11:05:53','2006-04-20 16:29:17',2,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10220,'Suspendisse potenti',1,2,'Default Administrator',1,0,0,0,'',' Nunc varius ante. Proin vitae magna at quam suscipit vehicula. Mauris sollicitudin urna id erat. Donec nec nisl ut urna sollicitudin faucibus:Pellentesque pellentesque. Aliquam iaculis congue erat.Donec ullamcorper. Fusce lacus magna, pretium vel.Mauris ultricies ipsum ut eros. Fusce blandit accumsan risus. Nulla aliquet. Duis sollicitudin orci id purus.Donec pretium laoreet erat. Nulla lectus. Cras suscipit nisi convallis lectus. Vestibulum magna urna, euismod nec, rutrum at.',NULL,0,'2006-04-20 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145541996,0,'suspendisse-potenti','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 17:03:40','2006-04-20 17:06:36','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10221,'Curabitur sollicitudin',1,2,'Default Administrator',1,0,6,0,'',' Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Ut adipiscing mi et dui. Pellentesque mi justo, congue eget, malesuada ac, vehicula ut, mi. Nulla pellentesque. Curabitur sollicitudin ipsum. Nulla et orci. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam congue diam et felis vehicula pellentesque. Mauris vestibulum sollicitudin est. Nam bibendum magna quis urna.',NULL,1,'2006-04-20 00:00:00','','','',3,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145541919,0,'curabitur-sollicitudin','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 17:04:53','2006-04-20 17:05:19','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10222,'Morbi feugiat condimentum libero',1,2,'Default Administrator',1,0,0,0,'',' Aliquam eu mauris. Suspendisse sed augue. Aliquam orci neque, adipiscing a, fringilla quis, lacinia vel, nisi. Aliquam luctus, odio non faucibus imperdiet, justo tortor pharetra lacus, bibendum accumsan velit nisl pellentesque nisl. Aenean pharetra erat ullamcorper mi. Phasellus blandit, nisl a molestie scelerisque, justo nibh consectetuer pede, at facilisis sem lectus vitae lorem. Quisque nibh. Maecenas massa. Suspendisse porta dignissim dolor. Suspendisse pede nulla, consequat et, imperdiet at, vulputate vitae, massa. Etiam dolor odio, tincidunt id, consequat nonummy, aliquam eget, massa. Nulla facilisi. Sed non erat nec velit eleifend ultricies. Curabitur quis diam nec erat ultrices scelerisque. Quisque aliquet tincidunt sapien. Praesent quis ligula blandit urna adipiscing semper. Praesent massa. Sed fermentum ornare lorem. Nulla facilisi. ',NULL,0,'2006-02-01 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145546021,0,'morbi-feugiat-condimentum-libero','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 18:13:41','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10223,'Aenean metus',1,2,'Default Administrator',1,0,0,0,'',' Sed ornare dolor in leo. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Fusce feugiat tortor. Pellentesque consequat leo volutpat lectus. Fusce rhoncus, sem id rhoncus aliquet, erat lorem viverra erat, et molestie massa urna et mauris. Quisque adipiscing lacus placerat nibh. Ut mattis nulla sit amet diam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nunc dapibus vulputate metus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Integer vel ligula id enim commodo condimentum. Vivamus convallis ante a purus. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Phasellus vehicula, nibh nec tristique tincidunt, diam turpis commodo purus, ac pretium dui nisl ac nulla. Maecenas pede mauris, luctus a, rhoncus nec, interdum ut, pede. Duis quis augue dapibus dolor ultrices convallis. Proin ut sapien. Ut sit amet mi a urna aliquet dignissim. Maecenas placerat. Phasellus tempus risus vel est. Fusce venenatis dui a ipsum. Aliquam ut libero. Praesent tincidunt, tortor ornare vulputate dapibus, dui mauris euismod lectus, ut viverra tortor tortor in tortor. ',NULL,0,'2006-01-01 00:00:00','','','',1,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145546138,0,'aenean-metus','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 18:15:38','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10224,'Morbi feugiat condimentum libero',1,2,'Default Administrator',1,0,0,0,'',' Aliquam eu mauris. Suspendisse sed augue. Aliquam orci neque, adipiscing a, fringilla quis, lacinia vel, nisi. Aliquam luctus, odio non faucibus imperdiet, justo tortor pharetra lacus, bibendum accumsan velit nisl pellentesque nisl. Aenean pharetra erat ullamcorper mi. Phasellus blandit, nisl a molestie scelerisque, justo nibh consectetuer pede, at facilisis sem lectus vitae lorem. Quisque nibh. Maecenas massa. Suspendisse porta dignissim dolor. Suspendisse pede nulla, consequat et, imperdiet at, vulputate vitae, massa. Etiam dolor odio, tincidunt id, consequat nonummy, aliquam eget, massa. Nulla facilisi. Sed non erat nec velit eleifend ultricies. Curabitur quis diam nec erat ultrices scelerisque. Quisque aliquet tincidunt sapien. Praesent quis ligula blandit urna adipiscing semper. Praesent massa. Sed fermentum ornare lorem. Nulla facilisi. ',NULL,0,'2006-03-16 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145546168,0,'morbi-feugiat-condimentum-libero-2','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2006-04-20 18:15:41','2006-04-20 18:16:08','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10225,'Curabitur eu nulla',1,2,'Default Administrator',1,0,0,0,'',' Curabitur enim. Ut urna enim, congue dapibus, ultricies nec, ultricies ut, ligula. Ut urna. In massa. Vivamus semper massa vitae nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In pharetra, lacus feugiat vestibulum volutpat, nisl dui ultrices leo, suscipit mollis urna justo sed elit. Duis sed lorem. In nonummy odio fermentum ligula. Praesent blandit risus id tortor. Pellentesque nulla. Nam diam mauris, vulputate eget, suscipit ut, cursus at, eros. Integer blandit dignissim purus. Aenean ornare auctor ante. Proin metus tortor, luctus a, facilisis id, elementum quis, augue. Quisque sed lorem vel pede rutrum volutpat. Morbi imperdiet eros vel nisi. Ut condimentum pellentesque tortor. Nulla magna. ',NULL,0,'2004-05-20 00:00:00','','','',1,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145546248,0,'curabitur-eu-nulla','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-20 18:17:28','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10226,'Duis interdum quam',1,2,'Default Administrator',1,0,0,0,'',' Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nullam sit amet diam at sapien posuere suscipit. Praesent dictum eros et sapien. Donec volutpat, purus a malesuada molestie, orci velit euismod massa, eget sagittis felis magna elementum augue. Ut vitae pede. Curabitur eu velit. Mauris ac velit. Donec viverra nunc ac mauris. Ut pretium. Morbi velit augue, aliquam nec, tristique a, egestas imperdiet, nisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Ut et nisi. Vivamus venenatis suscipit ipsum. Duis vel felis vel turpis vulputate accumsan. Vivamus eleifend, massa ut aliquam tincidunt, est lectus aliquam ante, vel faucibus magna purus vitae magna. Ut condimentum luctus nisi. In justo elit, blandit vel, semper nec, rhoncus quis, risus. Praesent ut leo. Proin blandit urna vitae elit.',NULL,0,'2006-03-01 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145608707,0,'duis-interdum-quam','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-21 11:38:27','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10227,'Pellentesque aliquam, leo tincidunt euismod tempo',1,2,'Default Administrator',1,0,0,0,'',' Morbi nec odio. Sed at ante. Suspendisse orci mauris, tempus at, hendrerit sit amet, sollicitudin sed, mi. Nullam tincidunt tincidunt tortor. Fusce augue enim, convallis sit amet, porttitor vitae, interdum sed, felis. Nulla condimentum. Praesent egestas venenatis dolor. Fusce tortor neque, dictum ut, feugiat a, adipiscing in, odio. Aenean eget est. Nam odio tellus, vehicula quis, tempor nec, auctor non, turpis. Aenean ac lacus. Etiam adipiscing nunc a erat. Nam vehicula tempus eros. Duis vitae nisl. Nunc quis mi. Etiam interdum, purus id vestibulum egestas, felis sapien ornare nulla, vel condimentum mi lectus a odio. Proin in ipsum ut nibh pellentesque sagittis. In ante. Nulla quam. Nulla orci pede, commodo id, commodo id, sollicitudin et, nibh. ',NULL,0,'2006-03-25 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,1145608771,0,'pellentesque-aliquam-leo-tincidunt-euismod-tempo','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2006-04-21 11:39:31','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10228,'Curabitur aliquet purus et nulla.',1,14,'',1,0,0,0,'','Curabitur aliquet purus et nulla. Nam aliquet ullamcorper enim. Donec commodo viverra dui. Praesent sodales malesuada turpis. Aliquam ultricies, mi eu eleifend tempor, augue dui adipiscing ante, sed malesuada odio risus in tellus. Mauris scelerisque.',NULL,0,'2006-04-20 00:00:00','','','',4,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','2006-04-20 15:44:30',2,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10229,'Aliquam gravida',1,14,'',1,0,0,0,'','Mauris iaculis tellus eget pede accumsan hendrerit: http://www.saurus.info. Etiam elit. Quisque sem nisl, consequat eget, porta at, porta non, dui. In nec lacus. Sed eget lacus. Pellentesque tempus massa nec velit. Cras elit justo, accumsan sit amet, mattis a, nonummy a, velit.',NULL,0,'2006-04-20 00:00:00','','','',19,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','2006-04-20 15:46:22',1,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10230,'Donec dapibus',1,14,'',1,0,0,0,'','Quisque enim augue, pharetra in, iaculis et, rhoncus a, lorem. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Sed volutpat magna non tellus. Maecenas semper nibh tincidunt nunc. Morbi tellus ipsum, tincidunt non, posuere sit amet, cursus nec, augue. Vestibulum egestas arcu eu mauris. Sed vitae enim ac lacus eleifend lobortis.',NULL,0,'2006-04-20 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10231,'Aenean vulputate fermentum nunc',1,14,'',1,0,0,0,'','Suspendisse potenti. Aliquam erat volutpat. Cras aliquet, urna vel semper fringilla, tortor ligula adipiscing diam, id elementum magna elit non ligula. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque dictum. Integer malesuada lorem vel elit. Ut ut tellus eget ante interdum venenatis. Duis et nulla.',NULL,0,'2006-04-20 00:00:00','','','',1,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10232,'Nunc in metus',1,14,'',1,0,0,0,'','Ut porttitor tortor ut dui. Pellentesque varius felis ac libero hendrerit iaculis. Nunc ipsum. Aliquam tellus lacus, pulvinar at, tempor ac, facilisis quis, velit. Fusce tortor ante, semper ut, suscipit vel, dignissim id, enim. Donec nunc nisl, semper quis, tempor at, porttitor vel, felis. :)',NULL,0,'2006-04-20 00:00:00','','','',3,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','2006-04-20 15:54:38',2,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10233,'Fusce lorem urna',1,14,'',1,0,0,0,'','Quisque a arcu. Aenean consequat, leo id mollis sagittis, ligula magna adipiscing quam, ac aliquet nisl justo nec dolor. Sed porta nulla eget odio. Sed convallis sapien eu mauris. Fusce sem leo, ultrices sed, rutrum quis, vestibulum id, mauris. Phasellus hendrerit velit eget erat. Sed nec mauris ac justo lobortis facilisis. Duis et velit sit amet tortor consequat rhoncus. Etiam magna. Suspendisse id turpis et leo sodales mattis. Phasellus a risus quis sapien vehicula auctor. Nam accumsan, mi eget tincidunt tincidunt, est quam adipiscing tortor, ut consequat nisl metus sed mauris. Vivamus semper tellus a lorem. Aliquam erat volutpat. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Curabitur et dolor id turpis pharetra blandit. ',NULL,0,'2006-04-20 00:00:00','','','',5,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','2006-04-20 15:57:03',3,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10234,'',1,14,'',1,0,0,0,'','Cras fermentum bibendum est. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Etiam orci risus, sollicitudin at, luctus ut, volutpat at, elit.',NULL,0,'2006-04-20 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145539700,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','0000-00-00 00:00:00','2006-04-20 16:28:20','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10235,'',1,14,'',1,0,0,0,'','Vivamus facilisis pellentesque arcu: http://www.saurus.info Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Maecenas convallis accumsan ante. Nullam in dolor. Curabitur felis turpis, varius ut, sollicitudin vitae, consequat sed, mi. Etiam id felis sed neque nonummy congue. Duis vitae augue.  :)',NULL,0,'2006-04-20 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145539792,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','0000-00-00 00:00:00','2006-04-20 16:29:52','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10236,'Re: Curabitur aliquet purus et nulla.',1,14,'',1,0,0,0,'','Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Ut justo ligula, venenatis sit amet, suscipit nec, hendrerit quis, diam. Etiam sit amet justo. In hac habitasse platea dictumst.',NULL,0,'2006-04-20 00:00:00','','','',2,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10237,'Re: Curabitur aliquet purus et nulla.',1,14,'',1,0,0,0,'','Urabitur porttitor risus a ligula. Donec gravida auctor lorem. Vestibulum justo lorem, eleifend ac, semper ac, varius id, risus. In leo enim, gravida mattis, pretium sit amet, ornare ut, ligula. Donec pulvinar.',NULL,0,'2006-04-20 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10238,'Re: Aliquam gravida',1,14,'',1,0,0,0,'','Ut sem magna, pellentesque a, tincidunt quis, adipiscing a, tortor. Sed orci :). Nullam nec lacus sed nunc porttitor tristique. Etiam eu nisi. Mauris enim erat, interdum a, tincidunt sed, auctor condimentum, lectus. Suspendisse diam. Cras et enim. Nam in elit eget quam venenatis facilisis. Suspendisse leo massa, laoreet eget, condimentum accumsan, semper a, nunc.',NULL,0,'2006-04-20 00:00:00','','','',9,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1145608229,0,'','0','0000-00-00 00:00:00',0,0,'',19,'Default Administrator','0000-00-00 00:00:00','2006-04-21 11:30:29','2006-04-20 15:47:46',1,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10239,'Re: Nunc in metus',1,14,'',1,0,0,0,'','Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque rhoncus, erat eu ullamcorper elementum, nibh purus tincidunt ligula, ut dictum sapien erat ac urna. Etiam tempor sollicitudin leo. Aliquam erat volutpat. Sed sed nulla. Donec sollicitudin, ipsum quis adipiscing malesuada, dui augue posuere leo, ut tristique erat ipsum id nibh. Nunc mauris nulla, blandit lobortis, aliquet ac, viverra id, eros. Nullam mauris. Phasellus mattis. Nunc imperdiet sapien vel lacus.',NULL,0,'2006-04-20 00:00:00','','','',1,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10240,'Re: Nunc in metus',1,14,'',1,0,0,0,'','Quisque sit amet purus ac quam viverra malesuada. Sed arcu. Quisque velit lectus, bibendum nec, tristique eu, sagittis eget, massa. Donec laoreet odio a augue. http://www.saurus.info',NULL,0,'2006-04-20 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10241,'Re: Fusce lorem urna',1,14,'',1,0,0,0,'','Aliquam magna lacus, gravida vel, fermentum vitae, commodo vitae, massa. Quisque tristique euismod turpis. Integer vitae sapien vel quam egestas euismod.',NULL,0,'2006-04-20 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10242,'Re: Fusce lorem urna',1,14,'',1,0,0,0,'','Etiam orci neque, porta id, vehicula id, vulputate a, magna. Vestibulum eu mi. In iaculis, nisi quis egestas tincidunt, dui magna scelerisque dolor, ut imperdiet sem sapien a leo.',NULL,0,'2006-04-20 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10243,'Re: Fusce lorem urna',1,14,'',1,0,0,0,'','Morbi augue purus, scelerisque accumsan, tincidunt ut, tincidunt vitae, est. Proin id nunc. Mauris felis elit, scelerisque sit amet, tincidunt id, pretium at, purus. Nam quis tortor non ipsum porta mattis. Morbi nisl. Aliquam sapien nunc, tempus quis, faucibus et, sagittis in, velit. Proin quis nisi eget lacus sollicitudin dapibus.',NULL,0,'2006-04-20 00:00:00','','','',1,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10244,'Re: Re: Aliquam gravida',1,14,'',1,0,0,0,'','Phasellus ultrices rutrum leo!',NULL,0,'2006-04-20 00:00:00','','','',0,'','','0000-00-00 00:00:00','0000-00-00 00:00:00',0,0,0,0,'','0','0000-00-00 00:00:00',0,0,'',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','0000-00-00 00:00:00',0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10350,'Quisque porttitor viverra erat?',1,6,'',1,1,0,0,'Quisque porttitor viverra erat?','',NULL,0,'2007-04-11 00:00:00',NULL,NULL,NULL,0,'','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1207236091,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2007-04-11 15:53:29','2008-04-03 18:21:31',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10351,'Quisque porttitor viverra erat?',1,6,'',1,0,0,0,'Quisque porttitor viverra erat?','',NULL,0,'2007-04-11 00:00:00',NULL,NULL,NULL,17,'','ttyyp_params','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1207236136,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2007-04-11 16:11:46','2008-04-03 18:22:16',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10568,'Site footer',1,2,'Default Administrator',1,1,0,0,'Site footer','Powered by Saurus CMS | Sitemap',NULL,0,'2009-07-01 11:58:00',NULL,NULL,NULL,0,'footer','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1246449560,0,'site-footer','0','0000-00-00 00:00:00',0,19,'Default Administrator',0,'','2009-07-01 11:59:20','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10462,'Saidi jalus',1,2,'Default Administrator',1,0,0,0,'Saidi jalus','Saiti jooksutab Saurus CMS | Sisukaart',NULL,0,'2008-02-28 17:40:00',NULL,NULL,NULL,0,'footer','','0000-00-00 00:00:00','0000-00-00 00:00:00',1,0,1246446463,0,'','0','0000-00-00 00:00:00',0,19,'Default Administrator',19,'Default Administrator','2008-02-28 17:42:12','2009-07-01 11:07:43',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10506,'public',1,22,NULL,1,1,0,NULL,'public',NULL,NULL,NULL,'2009-06-30 12:18:45',NULL,NULL,NULL,0,'public',NULL,NULL,NULL,0,0,NULL,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','2009-06-30 12:18:45','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();
new SQL("INSERT INTO `objekt` VALUES (10507,'shared',1,22,NULL,1,1,0,NULL,'shared',NULL,NULL,NULL,'2009-06-30 12:18:45',NULL,NULL,NULL,0,'shared',NULL,NULL,NULL,0,0,NULL,0,NULL,'0','0000-00-00 00:00:00',0,0,'',0,'','2009-06-30 12:18:45','0000-00-00 00:00:00',NULL,0,NULL)"); echo '. '; flush();

// Table structure for table `objekt_objekt`

new SQL("DROP TABLE IF EXISTS `objekt_objekt`"); echo '. '; flush();
new SQL("CREATE TABLE `objekt_objekt` (
  `objekt_id` bigint(20) NOT NULL default '0',
  `parent_id` bigint(20) NOT NULL default '0',
  `sorteering` bigint(20) unsigned default '0',
  PRIMARY KEY  (`objekt_id`,`parent_id`),
  KEY `sorteering` (`sorteering`),
  KEY `parent_id` (`parent_id`),
  KEY `objekt_id` (`objekt_id`)
)"); echo '. '; flush();

// Dumping data for table `objekt_objekt`

new SQL("INSERT INTO `objekt_objekt` VALUES (1,0,1)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (13,0,13)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (25,13,23)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (23,13,21)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (24,13,22)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (44,13,36)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6253,385,5409)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6252,385,5408)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6251,385,5407)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10245,385,5548)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (419,13,332)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6250,385,5406)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6249,385,5405)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6248,385,5404)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6247,385,5403)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (428,13,343)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (418,13,331)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (427,13,342)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (426,385,341)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (424,13,339)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6246,385,5402)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6245,385,5401)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (382,0,5047)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (385,0,308)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (389,13,312)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (694,13,677)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10118,10117,5464)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10117,10088,5463)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10116,10113,5462)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10114,10113,5460)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10113,10088,5459)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10109,10089,5455)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10108,10107,5454)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10107,10102,5453)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10111,10088,5457)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10102,10090,5448)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10101,10099,5447)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10100,10099,5446)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10099,10090,5445)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10098,10095,5476)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10121,10087,5467)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10120,10117,5466)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10119,10117,5465)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10096,10095,5442)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10095,10033,5441)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10094,10093,5440)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10093,10033,5432)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10091,10033,5439)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10090,10087,5436)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10089,10087,5435)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10088,10087,5434)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10087,10033,5427)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10244,10238,5547)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10243,10233,5546)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10242,10233,5545)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10241,10233,5544)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10205,10194,5556)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10240,10232,5543)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10239,10232,5542)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10238,10229,5541)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10237,10228,5540)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10236,10228,5539)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10235,10219,5538)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10234,10219,5537)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10233,10211,5536)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10232,10211,5535)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10231,10211,5534)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10230,10212,5533)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10229,10213,5532)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10228,10213,5531)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10227,10201,5530)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10226,10201,5529)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10225,10201,5528)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10224,10201,5527)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10223,10201,5526)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10222,10201,5525)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10221,10203,5524)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10220,10203,5523)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10219,10204,5522)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10217,10198,5520)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10216,10204,5519)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10215,10204,5518)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10214,10200,5517)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10213,10198,5516)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10212,10198,5515)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10204,10193,5507)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10203,10193,5506)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10211,10198,5514)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10210,10201,5513)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10209,10201,5512)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10208,10201,5511)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10170,10044,5494)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10169,10044,5493)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10168,10044,5492)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10167,10044,5491)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10166,10044,5490)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10251,13,5554)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10250,13,5553)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10249,13,5552)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10248,385,5551)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10247,385,5550)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10564,10029,5426)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10153,10091,5478)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10152,10091,5477)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10151,10095,5444)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10149,10098,5474)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10147,10098,5472)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10085,10044,5431)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10084,10044,5430)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10083,10044,5429)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10201,10193,5504)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10042,10029,5555)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10246,385,5549)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10033,382,5417)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10032,10029,5479)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10029,382,5420)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10044,10033,5433)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10193,1,5496)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10194,1,5497)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10565,10194,5500)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10197,10194,5508)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10198,10193,5501)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6256,385,5414)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (6257,13,1)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10200,10193,5503)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10171,10044,5495)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10350,10029,5586)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10351,10194,5587)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10568,385,5590)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10462,13,5558)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10506,0,2)"); echo '. '; flush();
new SQL("INSERT INTO `objekt_objekt` VALUES (10507,0,1)"); echo '. '; flush();

// Table structure for table `permissions`

new SQL("DROP TABLE IF EXISTS `permissions`"); echo '. '; flush();
new SQL("CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `type` enum('OBJ','ADMIN','ACL','EXT') NOT NULL default 'OBJ',
  `source_id` bigint(20) unsigned NOT NULL default '0',
  `role_id` bigint(20) unsigned NOT NULL default '0',
  `group_id` bigint(20) unsigned default '0',
  `user_id` bigint(20) unsigned default '0',
  `C` enum('1','0') NOT NULL default '0',
  `R` enum('1','0') NOT NULL default '0',
  `U` enum('1','0') NOT NULL default '0',
  `P` enum('1','0') NOT NULL default '0',
  `D` enum('1','0') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uni_perm` (`group_id`,`source_id`,`user_id`,`type`,`role_id`),
  KEY `permission` (`type`,`source_id`)
)"); echo '. '; flush();

// Dumping data for table `permissions`

new SQL("INSERT INTO `permissions` VALUES (3,'OBJ',1,0,1,0,'0','1','0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (4,'OBJ',382,0,1,0,'0','1','0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (5,'ACL',1,0,1,0,'0','1','0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (273,'OBJ',10044,0,1,0,'0','1','0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (281,'OBJ',10095,0,1,0,'0','1','0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (280,'OBJ',10093,0,1,0,'0','1','0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (268,'OBJ',10029,0,1,0,'0','1','0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (279,'OBJ',10091,0,1,0,'0','1','0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (278,'OBJ',10087,0,1,0,'0','1','0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (270,'OBJ',10033,0,1,0,'0','1','0','0','0')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (401,'OBJ',10506,0,1,0,'1','1','1','1','1')"); echo '. '; flush();
new SQL("INSERT INTO `permissions` VALUES (402,'OBJ',10507,0,1,0,'0','0','0','0','0')"); echo '. '; flush();

// Table structure for table `preferences`

new SQL("DROP TABLE IF EXISTS `preferences`"); echo '. '; flush();
new SQL("CREATE TABLE `preferences` (
  `pref_id` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `data` text NOT NULL,
  PRIMARY KEY  (`pref_id`),
  UNIQUE KEY `name` (`name`)
)"); echo '. '; flush();

// Dumping data for table `preferences`

new SQL("INSERT INTO `preferences` VALUES (1,'user_management_fields','a:24:{s:8:\"fullname\";a:2:{s:9:\"fieldname\";s:8:\"fullname\";s:10:\"is_visible\";i:1;}s:5:\"email\";a:2:{s:9:\"fieldname\";s:5:\"email\";s:10:\"is_visible\";i:1;}s:3:\"tel\";a:2:{s:9:\"fieldname\";s:3:\"tel\";s:10:\"is_visible\";i:1;}s:6:\"mobile\";a:2:{s:9:\"fieldname\";s:6:\"mobile\";s:10:\"is_visible\";i:1;}s:5:\"title\";a:2:{s:9:\"fieldname\";s:5:\"title\";s:10:\"is_visible\";i:1;}s:8:\"username\";a:2:{s:9:\"fieldname\";s:8:\"username\";s:10:\"is_visible\";i:1;}s:8:\"lastname\";a:2:{s:9:\"fieldname\";s:8:\"lastname\";s:10:\"is_visible\";i:0;}s:9:\"firstname\";a:2:{s:9:\"fieldname\";s:9:\"firstname\";s:10:\"is_visible\";i:0;}s:7:\"address\";a:2:{s:9:\"fieldname\";s:7:\"address\";s:10:\"is_visible\";i:0;}s:10:\"postalcode\";a:2:{s:9:\"fieldname\";s:10:\"postalcode\";s:10:\"is_visible\";i:0;}s:12:\"autologin_ip\";a:2:{s:9:\"fieldname\";s:12:\"autologin_ip\";s:10:\"is_visible\";i:0;}s:7:\"last_ip\";a:2:{s:9:\"fieldname\";s:7:\"last_ip\";s:10:\"is_visible\";i:0;}s:10:\"account_nr\";a:2:{s:9:\"fieldname\";s:10:\"account_nr\";s:10:\"is_visible\";i:0;}s:12:\"reference_nr\";a:2:{s:9:\"fieldname\";s:12:\"reference_nr\";s:10:\"is_visible\";i:0;}s:4:\"city\";a:2:{s:9:\"fieldname\";s:4:\"city\";s:10:\"is_visible\";i:0;}s:7:\"country\";a:2:{s:9:\"fieldname\";s:7:\"country\";s:10:\"is_visible\";i:0;}s:16:\"delivery_address\";a:2:{s:9:\"fieldname\";s:16:\"delivery_address\";s:10:\"is_visible\";i:0;}s:13:\"delivery_city\";a:2:{s:9:\"fieldname\";s:13:\"delivery_city\";s:10:\"is_visible\";i:0;}s:12:\"delivery_zip\";a:2:{s:9:\"fieldname\";s:12:\"delivery_zip\";s:10:\"is_visible\";i:0;}s:16:\"delivery_country\";a:2:{s:9:\"fieldname\";s:16:\"delivery_country\";s:10:\"is_visible\";i:0;}s:13:\"contactperson\";a:2:{s:9:\"fieldname\";s:13:\"contactperson\";s:10:\"is_visible\";i:0;}s:13:\"contact_phone\";a:2:{s:9:\"fieldname\";s:13:\"contact_phone\";s:10:\"is_visible\";i:0;}s:9:\"birthdate\";a:2:{s:9:\"fieldname\";s:9:\"birthdate\";s:10:\"is_visible\";i:0;}s:5:\"notes\";a:2:{s:9:\"fieldname\";s:5:\"notes\";s:10:\"is_visible\";i:0;}}')"); echo '. '; flush();
new SQL("INSERT INTO `preferences` VALUES (2,'xml_dir_fields','a:24:{s:9:\"direction\";a:2:{s:9:\"fieldname\";s:9:\"direction\";s:10:\"is_visible\";i:1;}s:6:\"dtd_id\";a:2:{s:9:\"fieldname\";s:6:\"dtd_id\";s:10:\"is_visible\";i:1;}s:8:\"dir_path\";a:2:{s:9:\"fieldname\";s:8:\"dir_path\";s:10:\"is_visible\";i:1;}s:15:\"delete_old_data\";a:2:{s:9:\"fieldname\";s:15:\"delete_old_data\";s:10:\"is_visible\";i:0;}s:18:\"delete_match_field\";a:2:{s:9:\"fieldname\";s:18:\"delete_match_field\";s:10:\"is_visible\";i:0;}s:8:\"el_start\";a:2:{s:9:\"fieldname\";s:8:\"el_start\";s:10:\"is_visible\";i:1;}s:12:\"reverse_data\";a:2:{s:9:\"fieldname\";s:12:\"reverse_data\";s:10:\"is_visible\";i:0;}s:9:\"is_active\";a:2:{s:9:\"fieldname\";s:9:\"is_active\";s:10:\"is_visible\";i:1;}s:7:\"is_cron\";a:2:{s:9:\"fieldname\";s:7:\"is_cron\";s:10:\"is_visible\";i:0;}s:11:\"delete_file\";a:2:{s:9:\"fieldname\";s:11:\"delete_file\";s:10:\"is_visible\";i:0;}s:13:\"spec_dataproc\";a:2:{s:9:\"fieldname\";s:13:\"spec_dataproc\";s:10:\"is_visible\";i:0;}s:8:\"encoding\";a:2:{s:9:\"fieldname\";s:8:\"encoding\";s:10:\"is_visible\";i:0;}s:6:\"jrk_nr\";a:2:{s:9:\"fieldname\";s:6:\"jrk_nr\";s:10:\"is_visible\";i:0;}s:16:\"delete_condition\";a:2:{s:9:\"fieldname\";s:16:\"delete_condition\";s:10:\"is_visible\";i:0;}s:10:\"root_begin\";a:2:{s:9:\"fieldname\";s:10:\"root_begin\";s:10:\"is_visible\";i:0;}s:8:\"root_end\";a:2:{s:9:\"fieldname\";s:8:\"root_end\";s:10:\"is_visible\";i:0;}s:14:\"result_tagname\";a:2:{s:9:\"fieldname\";s:14:\"result_tagname\";s:10:\"is_visible\";i:0;}s:16:\"export_file_name\";a:2:{s:9:\"fieldname\";s:16:\"export_file_name\";s:10:\"is_visible\";i:0;}s:17:\"is_addtime_tofile\";a:2:{s:9:\"fieldname\";s:17:\"is_addtime_tofile\";s:10:\"is_visible\";i:0;}s:11:\"source_data\";a:2:{s:9:\"fieldname\";s:11:\"source_data\";s:10:\"is_visible\";i:0;}s:11:\"export_type\";a:2:{s:9:\"fieldname\";s:11:\"export_type\";s:10:\"is_visible\";i:0;}s:12:\"dtd_new_name\";a:2:{s:9:\"fieldname\";s:12:\"dtd_new_name\";s:10:\"is_visible\";i:0;}s:12:\"export_email\";a:2:{s:9:\"fieldname\";s:12:\"export_email\";s:10:\"is_visible\";i:0;}s:11:\"import_type\";a:2:{s:9:\"fieldname\";s:11:\"import_type\";s:10:\"is_visible\";i:0;}}')"); echo '. '; flush();
new SQL("INSERT INTO `preferences` VALUES (3,'select_group','a:36:{s:5:\"email\";a:2:{s:9:\"fieldname\";s:5:\"email\";s:10:\"is_visible\";i:0;}s:8:\"username\";a:2:{s:9:\"fieldname\";s:8:\"username\";s:10:\"is_visible\";i:0;}s:8:\"lastname\";a:2:{s:9:\"fieldname\";s:8:\"lastname\";s:10:\"is_visible\";i:0;}s:9:\"firstname\";a:2:{s:9:\"fieldname\";s:9:\"firstname\";s:10:\"is_visible\";i:1;}s:5:\"title\";a:2:{s:9:\"fieldname\";s:5:\"title\";s:10:\"is_visible\";i:0;}s:7:\"address\";a:2:{s:9:\"fieldname\";s:7:\"address\";s:10:\"is_visible\";i:0;}s:10:\"postalcode\";a:2:{s:9:\"fieldname\";s:10:\"postalcode\";s:10:\"is_visible\";i:0;}s:3:\"tel\";a:2:{s:9:\"fieldname\";s:3:\"tel\";s:10:\"is_visible\";i:0;}s:12:\"autologin_ip\";a:2:{s:9:\"fieldname\";s:12:\"autologin_ip\";s:10:\"is_visible\";i:0;}s:7:\"last_ip\";a:2:{s:9:\"fieldname\";s:7:\"last_ip\";s:10:\"is_visible\";i:0;}s:10:\"account_nr\";a:2:{s:9:\"fieldname\";s:10:\"account_nr\";s:10:\"is_visible\";i:0;}s:12:\"reference_nr\";a:2:{s:9:\"fieldname\";s:12:\"reference_nr\";s:10:\"is_visible\";i:0;}s:4:\"city\";a:2:{s:9:\"fieldname\";s:4:\"city\";s:10:\"is_visible\";i:0;}s:7:\"country\";a:2:{s:9:\"fieldname\";s:7:\"country\";s:10:\"is_visible\";i:0;}s:16:\"delivery_address\";a:2:{s:9:\"fieldname\";s:16:\"delivery_address\";s:10:\"is_visible\";i:0;}s:13:\"delivery_city\";a:2:{s:9:\"fieldname\";s:13:\"delivery_city\";s:10:\"is_visible\";i:0;}s:12:\"delivery_zip\";a:2:{s:9:\"fieldname\";s:12:\"delivery_zip\";s:10:\"is_visible\";i:0;}s:16:\"delivery_country\";a:2:{s:9:\"fieldname\";s:16:\"delivery_country\";s:10:\"is_visible\";i:0;}s:13:\"contact_phone\";a:2:{s:9:\"fieldname\";s:13:\"contact_phone\";s:10:\"is_visible\";i:0;}s:13:\"contactperson\";a:2:{s:9:\"fieldname\";s:13:\"contactperson\";s:10:\"is_visible\";i:0;}s:1:\"a\";a:2:{s:9:\"fieldname\";s:1:\"a\";s:10:\"is_visible\";i:0;}s:3:\"a_2\";a:2:{s:9:\"fieldname\";s:3:\"a_2\";s:10:\"is_visible\";i:0;}s:4:\"palk\";a:2:{s:9:\"fieldname\";s:4:\"palk\";s:10:\"is_visible\";i:0;}s:8:\"tsekboks\";a:2:{s:9:\"fieldname\";s:8:\"tsekboks\";s:10:\"is_visible\";i:0;}s:6:\"selekt\";a:2:{s:9:\"fieldname\";s:6:\"selekt\";s:10:\"is_visible\";i:0;}s:6:\"raadio\";a:2:{s:9:\"fieldname\";s:6:\"raadio\";s:10:\"is_visible\";i:0;}s:4:\"fail\";a:2:{s:9:\"fieldname\";s:4:\"fail\";s:10:\"is_visible\";i:0;}s:8:\"Multiple\";a:2:{s:9:\"fieldname\";s:8:\"Multiple\";s:10:\"is_visible\";i:0;}s:7:\"boolean\";a:2:{s:9:\"fieldname\";s:7:\"boolean\";s:10:\"is_visible\";i:0;}s:8:\"karvkate\";a:2:{s:9:\"fieldname\";s:8:\"karvkate\";s:10:\"is_visible\";i:0;}s:9:\"birthtime\";a:2:{s:9:\"fieldname\";s:9:\"birthtime\";s:10:\"is_visible\";i:0;}s:9:\"birthdate\";a:2:{s:9:\"fieldname\";s:9:\"birthdate\";s:10:\"is_visible\";i:0;}s:8:\"fullname\";a:2:{s:9:\"fieldname\";s:8:\"fullname\";s:10:\"is_visible\";i:0;}s:10:\"test_radio\";a:2:{s:9:\"fieldname\";s:10:\"test_radio\";s:10:\"is_visible\";i:0;}s:10:\"veel_radio\";a:2:{s:9:\"fieldname\";s:10:\"veel_radio\";s:10:\"is_visible\";i:0;}s:11:\"Testin_Veel\";a:2:{s:9:\"fieldname\";s:11:\"Testin_Veel\";s:10:\"is_visible\";i:0;}}')"); echo '. '; flush();
new SQL("INSERT INTO `preferences` VALUES (4,'keeled_fields','a:6:{s:4:\"nimi\";a:2:{s:9:\"fieldname\";s:4:\"nimi\";s:10:\"is_visible\";i:1;}s:9:\"extension\";a:2:{s:9:\"fieldname\";s:9:\"extension\";s:10:\"is_visible\";i:1;}s:8:\"encoding\";a:2:{s:9:\"fieldname\";s:8:\"encoding\";s:10:\"is_visible\";i:1;}s:8:\"site_url\";a:2:{s:9:\"fieldname\";s:8:\"site_url\";s:10:\"is_visible\";i:1;}s:10:\"on_default\";a:2:{s:9:\"fieldname\";s:10:\"on_default\";s:10:\"is_visible\";i:1;}s:16:\"on_default_admin\";a:2:{s:9:\"fieldname\";s:16:\"on_default_admin\";s:10:\"is_visible\";i:1;}}')"); echo '. '; flush();
new SQL("INSERT INTO `preferences` VALUES (5,'pagetemplates_fields','a:12:{s:4:\"nimi\";a:2:{s:9:\"fieldname\";s:4:\"nimi\";s:10:\"is_visible\";i:1;}s:10:\"templ_fail\";a:2:{s:9:\"fieldname\";s:10:\"templ_fail\";s:10:\"is_visible\";i:1;}s:9:\"kirjeldus\";a:2:{s:9:\"fieldname\";s:9:\"kirjeldus\";s:10:\"is_visible\";i:0;}s:4:\"pilt\";a:2:{s:9:\"fieldname\";s:4:\"pilt\";s:10:\"is_visible\";i:0;}s:9:\"on_nahtav\";a:2:{s:9:\"fieldname\";s:9:\"on_nahtav\";s:10:\"is_visible\";i:1;}s:18:\"on_konfigureeritav\";a:2:{s:9:\"fieldname\";s:18:\"on_konfigureeritav\";s:10:\"is_visible\";i:0;}s:14:\"on_auto_avanev\";a:2:{s:9:\"fieldname\";s:14:\"on_auto_avanev\";s:10:\"is_visible\";i:0;}s:6:\"on_aeg\";a:2:{s:9:\"fieldname\";s:6:\"on_aeg\";s:10:\"is_visible\";i:0;}s:10:\"eri_params\";a:2:{s:9:\"fieldname\";s:10:\"eri_params\";s:10:\"is_visible\";i:0;}s:14:\"on_objekt_only\";a:2:{s:9:\"fieldname\";s:14:\"on_objekt_only\";s:10:\"is_visible\";i:0;}s:13:\"on_page_templ\";a:2:{s:9:\"fieldname\";s:13:\"on_page_templ\";s:10:\"is_visible\";i:0;}s:9:\"moodul_id\";a:2:{s:9:\"fieldname\";s:9:\"moodul_id\";s:10:\"is_visible\";i:0;}}')"); echo '. '; flush();
new SQL("INSERT INTO `preferences` VALUES (6,'contenttemplates_fields','a:12:{s:4:\"nimi\";a:2:{s:9:\"fieldname\";s:4:\"nimi\";s:10:\"is_visible\";i:1;}s:10:\"templ_fail\";a:2:{s:9:\"fieldname\";s:10:\"templ_fail\";s:10:\"is_visible\";i:1;}s:9:\"kirjeldus\";a:2:{s:9:\"fieldname\";s:9:\"kirjeldus\";s:10:\"is_visible\";i:0;}s:4:\"pilt\";a:2:{s:9:\"fieldname\";s:4:\"pilt\";s:10:\"is_visible\";i:0;}s:9:\"on_nahtav\";a:2:{s:9:\"fieldname\";s:9:\"on_nahtav\";s:10:\"is_visible\";i:1;}s:18:\"on_konfigureeritav\";a:2:{s:9:\"fieldname\";s:18:\"on_konfigureeritav\";s:10:\"is_visible\";i:0;}s:14:\"on_auto_avanev\";a:2:{s:9:\"fieldname\";s:14:\"on_auto_avanev\";s:10:\"is_visible\";i:0;}s:6:\"on_aeg\";a:2:{s:9:\"fieldname\";s:6:\"on_aeg\";s:10:\"is_visible\";i:0;}s:10:\"eri_params\";a:2:{s:9:\"fieldname\";s:10:\"eri_params\";s:10:\"is_visible\";i:0;}s:14:\"on_objekt_only\";a:2:{s:9:\"fieldname\";s:14:\"on_objekt_only\";s:10:\"is_visible\";i:0;}s:13:\"on_page_templ\";a:2:{s:9:\"fieldname\";s:13:\"on_page_templ\";s:10:\"is_visible\";i:0;}s:9:\"moodul_id\";a:2:{s:9:\"fieldname\";s:9:\"moodul_id\";s:10:\"is_visible\";i:0;}}')"); echo '. '; flush();
new SQL("INSERT INTO `preferences` VALUES (7,'log_fields','a:11:{s:2:\"id\";a:2:{s:9:\"fieldname\";s:2:\"id\";s:10:\"is_visible\";i:0;}s:3:\"aeg\";a:2:{s:9:\"fieldname\";s:3:\"aeg\";s:10:\"is_visible\";i:1;}s:8:\"on_error\";a:2:{s:9:\"fieldname\";s:8:\"on_error\";s:10:\"is_visible\";i:0;}s:17:\"on_fataalne_error\";a:2:{s:9:\"fieldname\";s:17:\"on_fataalne_error\";s:10:\"is_visible\";i:0;}s:6:\"on_sql\";a:2:{s:9:\"fieldname\";s:6:\"on_sql\";s:10:\"is_visible\";i:0;}s:9:\"on_import\";a:2:{s:9:\"fieldname\";s:9:\"on_import\";s:10:\"is_visible\";i:0;}s:10:\"on_eksport\";a:2:{s:9:\"fieldname\";s:10:\"on_eksport\";s:10:\"is_visible\";i:0;}s:9:\"asutus_id\";a:2:{s:9:\"fieldname\";s:9:\"asutus_id\";s:10:\"is_visible\";i:0;}s:9:\"sisestaja\";a:2:{s:9:\"fieldname\";s:9:\"sisestaja\";s:10:\"is_visible\";i:1;}s:4:\"text\";a:2:{s:9:\"fieldname\";s:4:\"text\";s:10:\"is_visible\";i:1;}s:3:\"sql\";a:2:{s:9:\"fieldname\";s:3:\"sql\";s:10:\"is_visible\";i:0;}}')"); echo '. '; flush();
new SQL("INSERT INTO `preferences` VALUES (12,'file_management_fields','a:10:{s:8:\"pealkiri\";a:2:{s:9:\"fieldname\";s:8:\"pealkiri\";s:10:\"is_visible\";i:1;}s:8:\"filename\";a:2:{s:9:\"fieldname\";s:8:\"filename\";s:10:\"is_visible\";i:1;}s:12:\"last_changed\";a:2:{s:9:\"fieldname\";s:12:\"last_changed\";s:10:\"is_visible\";i:0;}s:4:\"size\";a:2:{s:9:\"fieldname\";s:4:\"size\";s:10:\"is_visible\";i:1;}s:8:\"mimetype\";a:2:{s:9:\"fieldname\";s:8:\"mimetype\";s:10:\"is_visible\";i:1;}s:12:\"lastmodified\";a:2:{s:9:\"fieldname\";s:12:\"lastmodified\";s:10:\"is_visible\";i:0;}s:10:\"profile_id\";a:2:{s:9:\"fieldname\";s:10:\"profile_id\";s:10:\"is_visible\";i:0;}s:6:\"author\";a:2:{s:9:\"fieldname\";s:6:\"author\";s:10:\"is_visible\";i:1;}s:5:\"notes\";a:2:{s:9:\"fieldname\";s:5:\"notes\";s:10:\"is_visible\";i:1;}s:9:\"kirjeldus\";a:2:{s:9:\"fieldname\";s:9:\"kirjeldus\";s:10:\"is_visible\";i:0;}}')"); echo '. '; flush();
new SQL("INSERT INTO `preferences` VALUES (16,'profiles','a:5:{s:10:\"profile_id\";a:2:{s:9:\"fieldname\";s:10:\"profile_id\";s:10:\"is_visible\";i:0;}s:4:\"name\";a:2:{s:9:\"fieldname\";s:4:\"name\";s:10:\"is_visible\";i:1;}s:4:\"data\";a:2:{s:9:\"fieldname\";s:4:\"data\";s:10:\"is_visible\";i:1;}s:12:\"source_table\";a:2:{s:9:\"fieldname\";s:12:\"source_table\";s:10:\"is_visible\";i:1;}s:13:\"is_predefined\";a:2:{s:9:\"fieldname\";s:13:\"is_predefined\";s:10:\"is_visible\";i:1;}}')"); echo '. '; flush();
new SQL("INSERT INTO `preferences` VALUES (17,'resource_fields','a:4:{s:4:\"name\";a:2:{s:9:\"fieldname\";s:4:\"name\";s:10:\"is_visible\";i:0;}s:6:\"number\";a:2:{s:9:\"fieldname\";s:6:\"number\";s:10:\"is_visible\";i:1;}s:5:\"notes\";a:2:{s:9:\"fieldname\";s:5:\"notes\";s:10:\"is_visible\";i:1;}s:5:\"floor\";a:2:{s:9:\"fieldname\";s:5:\"floor\";s:10:\"is_visible\";i:0;}}')"); echo '. '; flush();

// Table structure for table `replicator`

new SQL("DROP TABLE IF EXISTS `replicator`"); echo '. '; flush();
new SQL("CREATE TABLE `replicator` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `direction` enum('I','E') NOT NULL default 'I',
  `name` varchar(255) default NULL,
  `scope` tinyint(3) unsigned NOT NULL default '0',
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  `is_subtree` tinyint(1) NOT NULL default '1',
  `file_type` tinyint(3) unsigned default NULL,
  `file` varchar(255) default NULL,
  `oi_format` tinyint(3) unsigned default '0',
  `time_interval` int(10) unsigned default '0',
  `is_active` enum('0','1') NOT NULL default '0',
  `on_avaldatud` tinyint(1) unsigned NOT NULL default '0',
  `on_lukus` tinyint(1) unsigned NOT NULL default '0',
  `last_replication` int(11) unsigned NOT NULL default '0',
  `jrk_nr` int(10) unsigned NOT NULL default '0',
  `obj_types` varchar(255) NOT NULL default '',
  `is_insert` tinyint(1) unsigned NOT NULL default '1',
  `is_update` tinyint(1) unsigned NOT NULL default '1',
  `is_publish` tinyint(1) unsigned NOT NULL default '1',
  `is_delete` tinyint(1) unsigned NOT NULL default '1',
  `sql_select` varchar(255) default NULL,
  `sql_from` varchar(255) default NULL,
  `sql_where` varchar(255) default NULL,
  `sql_order` varchar(255) default NULL,
  `method` enum('FILE','HTTP') default 'FILE',
  PRIMARY KEY  (`id`),
  KEY `time_interval` (`time_interval`),
  KEY `is_active` (`is_active`),
  KEY `on_lukus` (`on_lukus`),
  KEY `jrk_nr` (`jrk_nr`)
)"); echo '. '; flush();

// Dumping data for table `replicator`


// Table structure for table `roles`

new SQL("DROP TABLE IF EXISTS `roles`"); echo '. '; flush();
new SQL("CREATE TABLE `roles` (
  `role_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`role_id`),
  KEY `name` (`name`)
)"); echo '. '; flush();

// Dumping data for table `roles`


// Table structure for table `session`

new SQL("DROP TABLE IF EXISTS `session`"); echo '. '; flush();
new SQL("CREATE TABLE `session` (
  `sess_id` varchar(32) NOT NULL default '',
  `update_time` int(10) unsigned NOT NULL default '0',
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `ip` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`sess_id`),
  KEY `update_time` (`update_time`),
  KEY `user_id` (`user_id`)
)"); echo '. '; flush();

// Dumping data for table `session`


// Table structure for table `sitelog`

new SQL("DROP TABLE IF EXISTS `sitelog`"); echo '. '; flush();
new SQL("CREATE TABLE `sitelog` (
  `site_log_id` int(10) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_id` int(10) unsigned NOT NULL default '0',
  `objekt_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(255) NOT NULL default '',
  `component` varchar(255) NOT NULL default '',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `action` tinyint(3) unsigned NOT NULL default '0',
  `message` text NOT NULL,
  PRIMARY KEY  (`site_log_id`),
  KEY `user_id` (`user_id`),
  KEY `objekt_id` (`objekt_id`)
)"); echo '. '; flush();

// Dumping data for table `sitelog`


// Table structure for table `sso`

new SQL("DROP TABLE IF EXISTS `sso`"); echo '. '; flush();
new SQL("CREATE TABLE `sso` (
  `sso_id` int(3) unsigned NOT NULL auto_increment,
  `app_name` varchar(255) NOT NULL default '',
  `login_url` varchar(255) default NULL,
  `user_fieldname` varchar(100) default NULL,
  `pwd_fieldname` varchar(100) default NULL,
  `request_method` enum('GET','POST') NOT NULL default 'POST',
  `keel` int(11) unsigned NOT NULL default '0',
  `additional_fields` text,
  PRIMARY KEY  (`sso_id`),
  KEY `app_name` (`app_name`),
  KEY `keel` (`keel`)
)"); echo '. '; flush();

// Dumping data for table `sso`


// Table structure for table `sys_sona_tyyp`

new SQL("DROP TABLE IF EXISTS `sys_sona_tyyp`"); echo '. '; flush();
new SQL("CREATE TABLE `sys_sona_tyyp` (
  `sst_id` tinyint(3) unsigned NOT NULL auto_increment,
  `voti` varchar(100) NOT NULL default '',
  `nimi` varchar(255) default NULL,
  `moodul_id` int(10) unsigned NOT NULL default '0',
  `extension` varchar(100) default NULL,
  PRIMARY KEY  (`sst_id`),
  UNIQUE KEY `voti` (`voti`),
  KEY `moodul_id` (`moodul_id`)
)"); echo '. '; flush();

// Dumping data for table `sys_sona_tyyp`

new SQL("INSERT INTO `sys_sona_tyyp` VALUES (23,'custom','Custom',0,NULL)"); echo '. '; flush();

// Table structure for table `sys_sonad`

new SQL("DROP TABLE IF EXISTS `sys_sonad`"); echo '. '; flush();
new SQL("CREATE TABLE `sys_sonad` (
  `id` int(11) NOT NULL auto_increment,
  `sys_sona` varchar(255) NOT NULL default '',
  `keel` int(11) NOT NULL default '0',
  `sona` text,
  `origin_sona` text,
  `sst_id` tinyint(1) unsigned NOT NULL default '2',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `sona` (`sys_sona`,`keel`,`sst_id`),
  KEY `sys_sona` (`sys_sona`),
  KEY `keel` (`keel`)
)"); echo '. '; flush();

// Dumping data for table `sys_sonad`

new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('date', 1, 'Date', NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('firstname', 0, 'Eesnimi', 'Eesnimi', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Files', 0, 'Fail', 'Failid', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('fax', 0, 'Faks', 'Faks', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Document', 0, 'Dokument', 'Dokument', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('tel', 1, 'Phone', 'Phone', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('email', 0, 'E-post', 'E-post', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Room', 1, 'Room', 'Room', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('postalcode', 1, 'Zip code', 'PO Box', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('phone', 1, 'Phone', 'Phone', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('number', 1, 'Number', 'Number', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('notes', 1, 'Description', 'Notes', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('name', 1, 'Name', 'Name', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('mobile', 1, 'Mobile', 'Mobile', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('mimetype', 1, 'MIME type', 'MIME type', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('lastname', 1, 'Lastname', 'Last name', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Images', 1, 'Images', 'Images', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image', 1, 'Image', 'Image', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('firstname', 1, 'Firstname', 'First name', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Files', 1, 'File', 'Files', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('fax', 1, 'Fax', 'Fax', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('email', 1, 'E-mail', 'E-mail', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Document', 1, 'Document', 'Document', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Country-short', 1, 'Country-short', 'Country-short', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('country', 1, 'Country', 'Country', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Contact', 1, 'Contact', 'Contact', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('city', 1, 'City', 'City', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('birthdate', 1, 'Birthdate', 'Birthdate', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('author', 1, 'Author', 'Author', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('address', 1, 'Address', 'Address', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('country', 0, 'Riik', 'Riik', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Contact', 0, 'Kontakt', 'Kontakt', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('city', 0, 'Linn', 'Linn', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('birthdate', 0, 'Sünnipäev', 'Sünnipäev', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('author', 0, 'Autor', 'Autor', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('address', 0, 'Aadress', 'Aadress', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('title', 1, 'Title', 'Title', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('username', 1, 'Username', 'Username', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('website', 1, 'Web site', 'Web site', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image', 0, 'Pilt', 'Pilt', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('lastname', 0, 'Perekonnanimi', 'Perekonnanimi', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('mimetype', 0, 'MIME tüüp', 'MIME tüüp', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('mobile', 0, 'Mobiil', 'Mobiil', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('name', 0, 'Nimi', 'Nimi', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('notes', 0, 'Kirjeldus', 'Märkmed', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('number', 0, 'Number', 'Number', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('phone', 0, 'Telefon', 'Telefon', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('postalcode', 0, 'Postiindeks', 'Postiindeks', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Room', 0, 'Ruum', 'Ruum', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('tel', 0, 'Telefon', 'Telefon', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('title', 0, 'Amet', 'Amet', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('username', 0, 'Kasutajanimi', 'Kasutajanimi', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('website', 0, 'Veeb', 'Veeb', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('aeg', 1, 'Time', 'Time', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Article', 1, 'Article', 'Article', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('avaldamisaeg_algus', 1, 'Publish', 'Publish', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('avaldamisaeg_lopp', 1, 'Until', 'Until', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('aeg', 0, 'Aeg', NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Article', 0, 'Artikkel', NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('avaldamisaeg_algus', 0, 'Avaldatud', NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('avaldamisaeg_lopp', 0, 'Kuni', NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('fulltext_keywords', 0, 'Keywords', 'Keywords', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('fulltext_keywords', 1, 'Keywords', 'Keywords', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image_definitions_Sisu laius', 1, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image_definitions_Sisu laius', 0, 'Sisu laius', 'Sisu laius', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image_definitions_Content width', 1, 'Content width', 'Content width', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image_definitions_Content width', 0, 'Sisu laius', 'Sisu laius', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image_definitions_Half of content width', 1, 'Half of content width', 'Half of content width', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image_definitions_Half of content width', 0, 'Pool sisu laiust', 'Pool sisu laiust', 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('address', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('aeg', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Article', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('author', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('avaldamisaeg_algus', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('avaldamisaeg_lopp', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('birthdate', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('city', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Contact', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('country', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Country-short', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('currency', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('date', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Document', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('email', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('fax', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Files', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('firstname', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('fulltext_keywords', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Images', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image_definitions_Content width', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image_definitions_Half of content width', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('image_definitions_Sisu laius', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('lastname', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('mimetype', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('mobile', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('name', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('notes', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('number', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('phone', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('postalcode', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('price', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('Room', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('tel', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('title', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('username', 3, NULL, NULL, 23)"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad` (`sys_sona`, `keel`, `sona`, `origin_sona`, `sst_id`) VALUES ('website', 3, NULL, NULL, 23)"); echo '. '; flush();

// Table structure for table `sys_sonad_kirjeldus`

new SQL("DROP TABLE IF EXISTS `sys_sonad_kirjeldus`"); echo '. '; flush();
new SQL("CREATE TABLE `sys_sonad_kirjeldus` (
  `sst_id` smallint(6) unsigned NOT NULL default '0',
  `sys_sona` varchar(255) NOT NULL default '',
  `sona` varchar(255) default NULL,
  `last_update` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`sst_id`,`sys_sona`)
)"); echo '. '; flush();

// Dumping data for table `sys_sonad_kirjeldus`

new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'address', 'address', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'aeg', 'aeg', '2005-07-12 12:48:36')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'Article', 'Article', '2005-07-12 12:48:36')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'author', 'author', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'avaldamisaeg_algus', 'avaldamisaeg_algus', '2005-07-12 12:48:36')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'avaldamisaeg_lopp', 'avaldamisaeg_lopp', '2005-07-12 12:48:36')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'birthdate', 'birthdate', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'city', 'city', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'Contact', 'Contact', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'country', 'country', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'Country-short', 'Country-short', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'date', 'date', '2006-04-06 11:59:16')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'Document', 'Document', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'email', 'email', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'fax', 'fax', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'Files', 'Files', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'firstname', 'firstname', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'fulltext_keywords', 'Keywords', '2007-04-11 15:12:13')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'image', 'image', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'Images', 'Images', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'image_definitions_Content width', NULL, '0000-00-00 00:00:00')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'image_definitions_Half of content width', NULL, '0000-00-00 00:00:00')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'image_definitions_Sisu laius', NULL, '0000-00-00 00:00:00')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'lastname', 'lastname', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'mimetype', 'mimetype', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'mobile', 'mobile', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'name', 'name', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'notes', 'notes', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'number', 'number', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'phone', 'phone', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'postalcode', 'postalcode', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'Room', 'Room', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'tel', 'tel', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'username', 'username', '2005-07-12 12:46:33')"); echo '. '; flush();
new SQL("INSERT INTO `sys_sonad_kirjeldus` (`sst_id`, `sys_sona`, `sona`, `last_update`) VALUES (23, 'website', 'website', '2005-07-12 12:46:33')"); echo '. '; flush();

// Table structure for table `tbl`

new SQL("DROP TABLE IF EXISTS `tbl`"); echo '. '; flush();
new SQL("CREATE TABLE `tbl` (
  `tbl_id` int(10) unsigned NOT NULL auto_increment,
  `tbl` varchar(50) default NULL,
  `field` varchar(50) default NULL,
  `field_label` varchar(100) default NULL,
  `on_nahtav` tinyint(1) unsigned NOT NULL default '0',
  `jrk_nr` int(10) unsigned NOT NULL default '0',
  `on_kp` tinyint(1) unsigned NOT NULL default '0',
  `on_select` tinyint(1) unsigned NOT NULL default '0',
  `ttyyp_id` int(10) unsigned default '0',
  `on_noutud` tinyint(1) unsigned NOT NULL default '0',
  `pp_dok_liik` text NOT NULL,
  PRIMARY KEY  (`tbl_id`),
  KEY `kompl` (`field`,`tbl`,`ttyyp_id`)
)"); echo '. '; flush();

// Dumping data for table `tbl`

new SQL("INSERT INTO `tbl` VALUES (95,'kasutaja','email','E-post',1,5,0,0,18,1,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (460,'kasutaja','perenimi','Perenimi',1,25,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (97,'kasutaja','pass','Pass',1,4,0,0,18,1,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (458,'kasutaja','eesnimi','Eesnimi',1,23,0,0,18,1,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (124,'obj_dokument','fail','fail',0,6,0,0,11,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (459,'kasutaja','user','User',1,24,0,0,18,1,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (426,'obj_dokument','tyyp','type',0,7,0,0,11,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (116,'objekt','aeg','Aeg',1,1,1,0,11,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (117,'obj_dokument','size','Suurus',1,4,0,0,11,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (119,'objekt','pealkiri','Pealkiri',1,2,0,0,11,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (118,'obj_dokument','autor','Autor',0,3,0,0,11,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (120,'obj_dokument','kirjeldus','Kirjeldus',1,5,0,0,11,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (125,'kasutaja','tiitel','tiitel',0,6,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (130,'kasutaja','telefon','telefon',0,10,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (129,'kasutaja','postiaadress','postiaadress',1,9,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (485,'kasutaja','isikukood','Isikukood',0,35,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (184,'kasutaja','kasutaja_id','Kasutaja_id',0,11,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (185,'kasutaja','aeg','Aeg',0,12,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (186,'kasutaja','session_id','Session_id',0,13,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (187,'kasutaja','last_access_time','Last_access_time',0,14,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (188,'kasutaja','on_lukus','On_lukus',0,15,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (189,'kasutaja','postiindeks','Postiindeks',0,16,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (193,'kasutaja','pass_expires','Pass_expires',0,17,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (423,'kasutaja','autologin_ip','Autologin_ip',0,18,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (424,'kasutaja','last_ip','Last_ip',0,19,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (457,'kasutaja','account_nr','Account_nr',0,22,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (456,'kasutaja','reference_nr','Reference_nr',0,21,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (477,'kasutaja','city','City',1,27,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (478,'kasutaja','country','Country',1,28,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (479,'kasutaja','delivery_address','Delivery_address',0,29,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (480,'kasutaja','delivery_city','Delivery_city',0,30,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (481,'kasutaja','delivery_zip','Delivery_zip',0,31,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (482,'kasutaja','delivery_country','Delivery_country',0,32,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (483,'kasutaja','contact_phone','Contact_phone',0,33,0,0,18,0,'')"); echo '. '; flush();
new SQL("INSERT INTO `tbl` VALUES (484,'kasutaja','contactperson','Contactperson',0,34,0,0,18,0,'')"); echo '. '; flush();

// Table structure for table `templ_tyyp`

new SQL("DROP TABLE IF EXISTS `templ_tyyp`"); echo '. '; flush();
new SQL("CREATE TABLE `templ_tyyp` (
  `ttyyp_id` int(11) NOT NULL auto_increment,
  `op` varchar(25) NOT NULL default '',
  `nimi` varchar(100) default NULL,
  `templ_fail` varchar(100) default NULL,
  `on_nahtav` enum('0','1') NOT NULL default '0',
  `on_auto_avanev` tinyint(1) NOT NULL default '1',
  `sst_id` int(11) NOT NULL default '0',
  `on_page_templ` enum('0','1') NOT NULL default '0',
  `tbl` varchar(100) default NULL,
  `smarty_prefilter` text,
  `smarty_postfilter` text,
  `extension` varchar(100) default NULL,
  `is_readonly` tinyint(1) unsigned NOT NULL default '0',
  `is_default` tinyint(1) unsigned NOT NULL default '0',
  `preview` text,
  `preview_thumb` text,
  PRIMARY KEY  (`ttyyp_id`),
  KEY `nimi` (`nimi`)
)"); echo '. '; flush();

// Dumping data for table `templ_tyyp`

new SQL("INSERT INTO `templ_tyyp` VALUES (1059,'register','User registration','../../../extensions/saurus4/content_templates/register.html','0',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1039,'','Page template','../../../extensions/saurus4/page_templates/default_page_template.html','0',1,0,'1',NULL,NULL,NULL,'saurus4',1,0,'images/page_template_preview.jpg','images/page_template_thumbnail.jpg')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1040,'','Articles: 1 column','../../../extensions/saurus4/content_templates/articles.html','1',1,0,'0',NULL,NULL,NULL,'saurus4',1,1,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1041,'','Articles: 2 columns','../../../extensions/saurus4/content_templates/articles_2_columns.html','1',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1043,'','Articles: bulleted list','../../../extensions/saurus4/content_templates/article_list.html','1',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1058,'arhiiv','Articles: news archive','../../../extensions/saurus4/content_templates/news_archive.html','0',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1045,'','Forum','../../../extensions/saurus4/content_templates/forum.html','1',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1046,'search','Search: results','../../../extensions/saurus4/content_templates/search_results.html','0',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1047,'tappisotsing','Search: advanced','../../../extensions/saurus4/content_templates/advanced_search.html','0',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1048,'','Articles: detail view','../../../extensions/saurus4/object_templates/article.html','0',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1049,'','Forum: topic view','../../../extensions/saurus4/object_templates/forum_topic.html','0',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1050,'','Forum: message view','../../../extensions/saurus4/object_templates/forum_message.html','0',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1051,'','Documents','../../../extensions/saurus4/content_templates/documents.html','1',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1052,'kaart','Sitemap','../../../extensions/saurus4/content_templates/sitemap.html','0',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1055,'','Gallery: detail view','../../../extensions/saurus4/object_templates/gallery.html','0',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1056,'','Articles: news with archive','../../../extensions/saurus4/content_templates/news_list.html','1',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1057,'','Gallery','../../../extensions/saurus4/content_templates/gallery_list.html','1',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1060,'','Modern page template','../../../extensions/saurus4/page_templates/modern_page_template.html','1',1,0,'1',NULL,NULL,NULL,'saurus4',1,1,'images/page_template_preview.jpg','images/page_template_thumbnail.jpg')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1061,'rss','RSS feed of a section','../../../extensions/saurus4/page_templates/section_rss.html','0',1,0,'1',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();
new SQL("INSERT INTO `templ_tyyp` VALUES (1062,'','Blog','../../../extensions/saurus4/content_templates/blog.html','1',1,0,'0',NULL,NULL,NULL,'saurus4',1,0,'','')"); echo '. '; flush();

// Table structure for table `tyyp`

new SQL("DROP TABLE IF EXISTS `tyyp`"); echo '. '; flush();
new SQL("CREATE TABLE `tyyp` (
  `tyyp_id` int(3) NOT NULL auto_increment,
  `nimi` varchar(50) NOT NULL default '',
  `klass` varchar(20) NOT NULL default '',
  `tabel` varchar(20) default NULL,
  `on_alampuu_kustutamine` enum('0','1') NOT NULL default '0',
  `on_kujundusmall` enum('0','1') NOT NULL default '0',
  `on_kast` enum('0','1') NOT NULL default '0',
  `on_otsingus` enum('0','1','2') NOT NULL default '0',
  `use_trash` enum('0','1') NOT NULL default '0',
  `ttyyp_id` int(11) unsigned default NULL,
  PRIMARY KEY  (`tyyp_id`),
  UNIQUE KEY `nimi` (`nimi`),
  KEY `klass` (`klass`)
)"); echo '. '; flush();

// Dumping data for table `tyyp`

new SQL("INSERT INTO `tyyp` VALUES (1,'Rubriik','rubriik','obj_rubriik','1','1','0','1','1',NULL)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (2,'Artikkel','artikkel','obj_artikkel','1','0','0','1','1',1048)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (3,'Valine link','link','obj_link','1','0','1','1','0',NULL)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (12,'Pilt','pilt','obj_pilt','1','0','0','1','0',0)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (14,'Kommentaar','kommentaar','obj_kommentaar','1','0','0','1','0',1050)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (6,'Gallup','gallup','obj_gallup','1','0','0','0','1',0)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (7,'Dokument','dokument','obj_dokument','1','0','0','1','1',0)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (8,'Lingikast','rubriik','obj_rubriik','1','0','1','0','1',NULL)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (9,'Uudistekogu','kogumik','obj_rubriik','0','0','1','0','0',NULL)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (13,'Login-kast','loginkast','obj_artikkel','1','0','1','0','0',NULL)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (15,'Teema','teema','obj_rubriik','1','1','0','2','0',1049)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (16,'Album','album','obj_rubriik','1','0','0','2','1',1055)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (17,'Iframe-kast','iframekast','obj_artikkel','1','0','1','0','0',NULL)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (19,'Product Category','productcategory','obj_rubriik','1','0','1','1','0',0)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (20,'Asset','asset','obj_asset','1','0','0','1','1',0)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (21,'File','file','obj_file','1','0','0','1','0',0)"); echo '. '; flush();
new SQL("INSERT INTO `tyyp` VALUES (22,'Folder','folder','obj_folder','1','0','0','1','0',0)"); echo '. '; flush();

// Table structure for table `user_mailinglist`

new SQL("DROP TABLE IF EXISTS `user_mailinglist`"); echo '. '; flush();
new SQL("CREATE TABLE `user_mailinglist` (
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `objekt_id` bigint(20) unsigned NOT NULL default '0',
  KEY `user_id` (`user_id`),
  KEY `objekt_id` (`objekt_id`)
)"); echo '. '; flush();

// Dumping data for table `user_mailinglist`


// Table structure for table `user_roles`

new SQL("DROP TABLE IF EXISTS `user_roles`"); echo '. '; flush();
new SQL("CREATE TABLE `user_roles` (
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `role_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`role_id`)
)"); echo '. '; flush();

// Dumping data for table `user_roles`


// Table structure for table `users`

new SQL("DROP TABLE IF EXISTS `users`"); echo '. '; flush();
new SQL("CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL default '0',
  `email` varchar(255) default NULL,
  `is_predefined` char(1) default NULL,
  `is_readonly` tinyint(1) unsigned NOT NULL default '0',
  `profile_id` int(4) unsigned NOT NULL default '0',
  `username` varchar(50) default NULL,
  `password` blob,
  `firstname` varchar(255) default NULL,
  `lastname` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `image` tinytext,
  `created_date` date NOT NULL default '0000-00-00',
  `session_id` varchar(255) NOT NULL default '',
  `last_access_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_locked` tinyint(1) unsigned NOT NULL default '0',
  `idcode` varchar(11) default NULL,
  `address` text,
  `postalcode` varchar(20) default NULL,
  `tel` varchar(20) default NULL,
  `pass_expires` date NOT NULL default '2029-01-01',
  `autologin_ip` varchar(20) default NULL,
  `last_ip` varchar(20) default NULL,
  `account_nr` varchar(50) default NULL,
  `reference_nr` varchar(50) default NULL,
  `city` varchar(255) default NULL,
  `country` varchar(255) default NULL,
  `delivery_address` varchar(255) default NULL,
  `delivery_city` varchar(255) default NULL,
  `delivery_zip` varchar(255) default NULL,
  `delivery_country` varchar(255) default NULL,
  `contact_phone` varchar(255) default NULL,
  `contactperson` varchar(255) default NULL,
  `mobile` varchar(255) default NULL,
  `birthdate` date default NULL,
  `notes` text,
  `failed_logins` tinyint(1) unsigned NOT NULL default '0',
  `first_failed_login` int(10) unsigned NOT NULL default '0',
  `last_failed_login` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `created_date` (`created_date`),
  KEY `session_id` (`session_id`),
  KEY `group_id` (`group_id`),
  KEY `email` (`email`),
  KEY `profile_id` (`profile_id`)
)"); echo '. '; flush();

// Dumping data for table `users`

new SQL("INSERT INTO `users` VALUES (1,1,'user@site.com','1',0,38,'admin','random','Default Administrator','','','','0000-00-00','','2011-05-13 13:15:34',0,NULL,'','','','2029-01-01','','192.168.1.254',NULL,NULL,'','',NULL,NULL,NULL,NULL,NULL,NULL,'','0000-00-00','',0,0,0)"); echo '. '; flush();

// Table structure for table `version`

new SQL("DROP TABLE IF EXISTS `version`"); echo '. '; flush();
new SQL("CREATE TABLE `version` (
  `version_nr` varchar(15) NOT NULL default '0',
  `release_date` date NOT NULL default '0000-00-00',
  `install_date` date NOT NULL default '0000-00-00',
  `description` text,
  PRIMARY KEY  (`version_nr`),
  UNIQUE KEY `version_nr` (`version_nr`)
)"); echo '. '; flush();

// Dumping data for table `version`

new SQL("INSERT INTO `version` VALUES ('4.7.FINAL','2011-05-17',NOW(),'479')"); echo '. '; flush();

// Table structure for table `xml`

new SQL("DROP TABLE IF EXISTS `xml`"); echo '. '; flush();
new SQL("CREATE TABLE `xml` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dir_path` varchar(255) default NULL,
  `direction` enum('I','E') NOT NULL default 'I',
  `dtd_id` int(11) NOT NULL default '0',
  `delete_old_data` tinyint(1) unsigned NOT NULL default '0',
  `delete_match_field` varchar(255) default NULL,
  `el_start` varchar(255) NOT NULL default '',
  `reverse_data` tinyint(1) unsigned NOT NULL default '0',
  `keel` int(11) unsigned NOT NULL default '0',
  `is_active` tinyint(1) unsigned NOT NULL default '0',
  `is_cron` tinyint(1) unsigned NOT NULL default '0',
  `delete_file` tinyint(1) unsigned NOT NULL default '0',
  `spec_dataproc` varchar(255) NOT NULL default '-',
  `encoding` varchar(255) NOT NULL default 'ISO-8859-1',
  `jrk_nr` int(10) unsigned NOT NULL default '0',
  `delete_condition` varchar(255) default NULL,
  `root_begin` text,
  `root_end` varchar(255) default NULL,
  `result_tagname` varchar(255) default NULL,
  `export_file_name` varchar(255) default NULL,
  `is_addtime_tofile` tinyint(3) unsigned NOT NULL default '0',
  `source_data` text,
  `export_type` enum('table','query','objects') default NULL,
  `dtd_new_name` varchar(100) NOT NULL default '',
  `export_email` varchar(100) default NULL,
  `import_type` enum('default','php script') default 'default',
  `is_locked` tinyint(1) unsigned NOT NULL default '0',
  `encoding_to` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `keel` (`keel`),
  KEY `dtd` (`dtd_id`)
)"); echo '. '; flush();

// Dumping data for table `xml`


// Table structure for table `xml_dtd`

new SQL("DROP TABLE IF EXISTS `xml_dtd`"); echo '. '; flush();
new SQL("CREATE TABLE `xml_dtd` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dtd_name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
)"); echo '. '; flush();

// Dumping data for table `xml_dtd`


// Table structure for table `xml_map`

new SQL("DROP TABLE IF EXISTS `xml_map`"); echo '. '; flush();
new SQL("CREATE TABLE `xml_map` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dtd_element_name` varchar(100) NOT NULL default '',
  `db_field_name` varchar(100) NOT NULL default '',
  `dtd_id` int(11) NOT NULL default '0',
  `tbl_name` varchar(50) NOT NULL default '',
  `type` enum('TEXT','DATE','BINARY','HTML') NOT NULL default 'TEXT',
  `objekt_id` int(10) unsigned default NULL,
  `date_format` varchar(50) default 'dd.mm.yyyy',
  `fixed_value` text,
  `xml_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `dtd_name` (`dtd_id`)
)"); echo '. '; flush();

// Dumping data for table `xml_map`

}
