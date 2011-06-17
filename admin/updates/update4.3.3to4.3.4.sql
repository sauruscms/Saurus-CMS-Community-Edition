###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
CREATE TABLE sitelog (                                   
           site_log_id int(10) unsigned NOT NULL auto_increment,  
           date datetime NOT NULL default '0000-00-00 00:00:00',  
           user_id int(10) unsigned NOT NULL default '0',         
           objekt_id int(10) unsigned NOT NULL default '0',       
           username varchar(255) NOT NULL default '',       
           component varchar(255) NOT NULL default '',         
           type tinyint(3) unsigned NOT NULL default '0',         
           action tinyint(3) unsigned NOT NULL default '0',       
           message text NOT NULL,                                 
           PRIMARY KEY  (site_log_id),                            
           KEY user_id (user_id),                               
           KEY objekt_id (objekt_id)                            
);
         
###################################################
# TABLE CHANGES:
###################################################
alter table version add column install_date date  DEFAULT '0000-00-00' NOT NULL  after release_date;

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'lock_inactive_user_after_x_days','0','Lock inactive user after x days','1');
insert into sitelog (date, objekt_id, username, type, action, message) select aeg, objekt_id, sisestaja, if(on_error or on_fataalne_error or is_error, 1, 0), if(on_import or on_eksport, if(on_import, 6, 7), 0), text from logi;

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.3.4', '2007-01-24', now(), 'logalicius');

