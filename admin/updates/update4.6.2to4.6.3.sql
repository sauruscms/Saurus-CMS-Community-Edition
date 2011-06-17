###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################
alter table `stat_agents` add unique `agents` (`agent`(250));

# add glossary column
alter table `keel` add column `glossary_id` int(11) UNSIGNED DEFAULT '0' NOT NULL after `encoding`;

alter table `keel` drop PRIMARY key, add PRIMARY key (`keel_id`);
# 'nimi' is now non-unique
alter table `keel` drop key `nimi`;
alter table `keel` add index `nimi` (`nimi`);
alter table `keel` drop key `nimi_2`;

# 'keel_id' is now auto incremented
alter table `keel` change `keel_id` `keel_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, auto_increment=501;
update keel set keel_id = 0 where nimi = 'Estonian';
update keel set keel_id = 0 where keel_id = 501 or keel_id = 502;

alter table `keel` change `extension` `extension` varchar(255) NULL ;

# set existing glossaries
update keel set glossary_id = keel_id where keel_id < 501;

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

# context menu open event switcher
insert into `config`(`nimi`,`sisu`,`kirjeldus`,`on_nahtav`) values ( 'context_menu_open_event','click','Context menu open event','1');

# Estonian ID card user creation configuration parameters
insert into config set nimi='id_card_create_cms_users', sisu=0, kirjeldus='Create user if not found in CMS? (Y/N)', on_nahtav=0;
insert into config set nimi='id_card_default_cms_group', sisu=0, kirjeldus='Default group for created users', on_nahtav=0;

# unlock all objects
UPDATE objekt SET check_in = 0, check_in_admin_id = 0;

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.6.3', '2009-09-24', now(), 'I speak in many glossaries');
