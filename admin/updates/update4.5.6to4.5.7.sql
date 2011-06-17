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


###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.5.7', '2009-01-07', now(), 'Action button purtyifaction');
