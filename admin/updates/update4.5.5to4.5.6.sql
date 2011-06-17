###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################

alter table users change pass_expires pass_expires date  DEFAULT '2029-01-01' NOT NULL;
UPDATE users SET pass_expires = '2029-01-01' WHERE pass_expires = '2009-01-01';

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################


#E-payment module menus sections for admin menu bar. 

INSERT INTO admin_osa SET id=156, parent_id=1, sorteering=80, eng_nimetus='E-Commerce', moodul_id=35, show_in_editor=0;
INSERT INTO admin_osa SET id=157, parent_id=156, sorteering=50, eng_nimetus='Configuration', fail='change_config.php?group=2', moodul_id=35, show_in_editor=0;

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.5.6', '2008-10-03', now(), 'Don\'t be afraid, publish!');
