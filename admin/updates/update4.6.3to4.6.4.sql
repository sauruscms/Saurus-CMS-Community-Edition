###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################

alter table `stat_agents` add unique `agents` (`agent`(250));

# GD is the only choice in image manipulation 
update `config` set `sisu`='gd lib' where `nimi`='image_mode';

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

# redirect to alias configuration parameter
INSERT INTO `config`(`nimi`,`sisu`,`kirjeldus`,`on_nahtav`) VALUES ( 'redirect_to_alias','1','Redirect links containing object ID to object alias','1');

# replace links with alias in content configuration parameter
INSERT INTO `config`(`nimi`,`sisu`,`kirjeldus`,`on_nahtav`) VALUES ( 'replace_links_with_alias','0','Replace local links in content with their respective aliases','1');

# change alias language format description
update `config` set `kirjeldus`='Alias prefix format' where `nimi`='alias_language_format';

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.6.4', '2009-11-23', now(), 'All of your aliases will be searched');
