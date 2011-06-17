###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################
alter table users change pass_expires pass_expires date  DEFAULT '2029-01-01' NOT NULL;
UPDATE users SET pass_expires = '2029-01-01' WHERE pass_expires = '2009-01-01';

alter table templ_tyyp add column is_default tinyint (1)UNSIGNED  DEFAULT '0' NOT NULL  after is_readonly;
alter table templ_tyyp add column preview text   NULL  after is_default, add column preview_thumb text   NULL  after preview;
alter table extensions add column is_downloadable tinyint (1)UNSIGNED  DEFAULT '0' NOT NULL  after is_active;
alter table users add column is_readonly tinyint (1)UNSIGNED  DEFAULT '0' NOT NULL  after is_predefined;
ALTER TABLE admin_osa CHANGE COLUMN nimetus nimetus varchar(255) NULL;
ALTER TABLE admin_osa ADD COLUMN show_in_editor tinyint(1) unsigned NOT NULL DEFAULT '0';

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################
insert into moodulid(moodul_id,nimi,on_aktiivne,is_invisible,status) values ( 36,'Scheduled publishing','1','0','');
insert into moodulid(moodul_id,nimi,on_aktiivne,is_invisible,status) values ( 37,'SaaS Pack1','1','1','');
insert into config set nimi='save_site_log', sisu='1', kirjeldus='Enable site log', on_nahtav='1';
insert into config set nimi='time_zone', sisu='', kirjeldus='GMT time zone the website is located in', on_nahtav='1';

UPDATE config SET kirjeldus= 'Only authenticated users may add comments' WHERE nimi='only_regusers_comment';
UPDATE config SET kirjeldus= 'Enable mailing lists' WHERE nimi='enable_mailing_list';
UPDATE config SET kirjeldus= 'Send articles that are newer than (dd.mm.yyyy)' WHERE nimi='maillist_send_newer_than';
UPDATE config SET kirjeldus= 'Send reports about mailinglist postings to (e-mail address)' WHERE nimi='maillist_reporter_address';
UPDATE config SET kirjeldus= '\"From\" e-mail address' WHERE nimi='from_email';
UPDATE config SET kirjeldus= '\"To\" e-mail address' WHERE nimi='default_mail';
UPDATE config SET kirjeldus= 'E-mail \"Subject\"' WHERE nimi='subject';
UPDATE config SET kirjeldus= 'Form action' WHERE nimi='feedbackform_action';
UPDATE config SET kirjeldus= 'Form name' WHERE nimi='feedbackform_form_name';
UPDATE config SET kirjeldus= 'Form method' WHERE nimi='feedbackform_method';
UPDATE config SET kirjeldus= 'Cache expires (hours). 0 = cache is not used' WHERE nimi='cache_expired';
UPDATE config SET kirjeldus= 'Cache will be skipped for ID\'s (coma separated list)' WHERE nimi='dont_cache_objects';
UPDATE config SET kirjeldus= 'GZIP compress level (values 1 to 9) 2 = recommended, 0 = not used' WHERE nimi='compress_level';
UPDATE config SET kirjeldus= 'Send error notifications to all superusers' WHERE nimi='send_error_notifiations_to_superusers';
UPDATE config SET kirjeldus= 'Method for error notifications activation' WHERE nimi='send_error_notifiations_setting';
UPDATE config SET kirjeldus= 'Session lifetime (minutes)' WHERE nimi='session_lifetime';
UPDATE config SET kirjeldus= 'Maximum time a script is allowed to run (seconds)' WHERE nimi='php_max_execution_time';
UPDATE config SET kirjeldus= 'Maximum amount of memory a script is allowed to allocate (Mbytes)' WHERE nimi='php_memory_limit';
UPDATE config SET kirjeldus= 'Display errors only to these IP addresses (semicolon separated)' WHERE nimi='display_errors_ip';
UPDATE config SET kirjeldus= 'Use CAPTCHA verification for comments and forums' WHERE nimi='check_for_captcha';
UPDATE config SET kirjeldus= 'Use CAPTCHA verification for feedback forms' WHERE nimi='feedbackform_check_for_captcha';
update config set kirjeldus='Prevent multiple votes' where nimi='gallup_ip_check';

#NEW ADMIN MENU STRUCTURE

update admin_osa set nimetus='';
UPDATE admin_osa SET sorteering=100, show_in_editor=1 WHERE id=69;
UPDATE admin_osa SET sorteering=90, show_in_editor=1 WHERE id=19;
UPDATE admin_osa SET sorteering=80 WHERE id=54;
UPDATE admin_osa SET sorteering=70 WHERE id=86;
UPDATE admin_osa SET sorteering=60 WHERE id=32;
UPDATE admin_osa SET sorteering=50, eng_nimetus='Presentation' WHERE id=34;
UPDATE admin_osa SET sorteering=40, eng_nimetus='Data' WHERE id=2;
UPDATE admin_osa SET sorteering=30 WHERE id=36;
UPDATE admin_osa SET sorteering=20 WHERE id=5;
UPDATE admin_osa SET sorteering=10 WHERE id=78;

#Tools menu
UPDATE admin_osa SET sorteering=100, show_in_editor=1 WHERE id=77;
UPDATE admin_osa SET parent_id=69, show_in_editor=1, sorteering=90 WHERE id=73;
UPDATE admin_osa SET sorteering=80, show_in_editor=1 WHERE id=62;
UPDATE admin_osa SET sorteering=70, show_in_editor=1 WHERE id=63;
UPDATE admin_osa SET sorteering=60, show_in_editor=1 WHERE id=150;
UPDATE admin_osa SET sorteering=50, show_in_editor=1 WHERE id=66;
UPDATE admin_osa SET sorteering=40 WHERE id=84;
UPDATE admin_osa SET sorteering=30, show_in_editor=1 WHERE id=15;
UPDATE admin_osa SET parent_id=69, sorteering=20 WHERE id=52;
UPDATE admin_osa SET parent_id=69, sorteering=10 WHERE id=82;

#Settings menu

UPDATE admin_osa SET sorteering=100, show_in_editor=1 WHERE id=60;
INSERT INTO admin_osa SET id=153, parent_id=19, sorteering=90, nimetus='', eng_nimetus='Site Design', fail='designs.php';
UPDATE admin_osa SET parent_id=19, sorteering=80 WHERE id=83;
UPDATE admin_osa SET sorteering=70 WHERE id=151;

#E-commerce

UPDATE admin_osa SET sorteering=100 WHERE id=64;
UPDATE admin_osa SET sorteering=90 WHERE id=55;
UPDATE admin_osa SET sorteering=80 WHERE id=70;
UPDATE admin_osa SET sorteering=50 WHERE id=65;

#Extensions needs no change

#Languages
UPDATE admin_osa SET sorteering=90 WHERE id=9;
UPDATE admin_osa SET sorteering=80 WHERE id=17;
UPDATE admin_osa SET sorteering=70 WHERE id=53;


#Presentation

UPDATE admin_osa SET sorteering=100 WHERE id=49;
UPDATE admin_osa SET parent_id=34 WHERE id=39;
UPDATE admin_osa SET sorteering=80 WHERE id=59;
UPDATE admin_osa SET sorteering=70 WHERE id=58;
UPDATE admin_osa SET sorteering=60 WHERE id=71;
UPDATE admin_osa SET sorteering=50 WHERE id=10;

#Data

UPDATE admin_osa SET parent_id=2, sorteering=100 WHERE id=152;
UPDATE admin_osa SET parent_id=2, sorteering=90 WHERE id=74;
UPDATE admin_osa SET parent_id=2, sorteering=80 WHERE id=75;

#System

UPDATE admin_osa SET sorteering=100 WHERE id=68;
UPDATE admin_osa SET parent_id=5, sorteering=90 WHERE id=41;
UPDATE admin_osa SET parent_id=5, sorteering=80 WHERE id=85;
UPDATE admin_osa SET sorteering=70 WHERE id=42;

#Move sub-menus from the deletable one main menu and one submenu
UPDATE admin_osa set parent_id=86 where parent_id=3;
DELETE FROM admin_osa WHERE id=3;
DELETE FROM admin_osa WHERE id=20;

UPDATE config SET on_nahtav='1' WHERE nimi='cache_expired';
UPDATE config SET on_nahtav='1' WHERE nimi='compress_level';
UPDATE config SET on_nahtav='1' WHERE nimi='dont_cache_objects';
UPDATE config SET on_nahtav='1' WHERE nimi='mailinglist_sending_option';
UPDATE config SET on_nahtav='1' WHERE nimi='gallup_ip_check';

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.5.0', '2008-04-04', now(), 'Fallout');
