###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################
alter table `stat_agents` add unique `agents` (`agent`(250));

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

# Saurus CMS homepage into help menu
insert into `admin_osa`(`id`,`parent_id`,`sorteering`,`nimetus`,`eng_nimetus`,`fail`,`moodul_id`,`extension`,`show_in_editor`) values ( '158','78','100',NULL,'Saurus CMS homepage','http://www.saurus.info/\" target=\"_blank','0',NULL,'0');

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.6.2', '2009-07-03', now(), 'There\'s something about albums');
