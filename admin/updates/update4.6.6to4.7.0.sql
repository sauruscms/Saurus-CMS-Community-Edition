###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################

UPDATE `config` SET `kirjeldus`='Default \"To\" e-mail address' WHERE `nimi`='default_mail';
UPDATE `config` SET `kirjeldus`='Default \"From\" e-mail address' WHERE `nimi`='from_email';
UPDATE `config` set `kirjeldus`='Use human friendly URLs' WHERE `nimi`='use_aliases';

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.7.0', '2010-04-01', now(), 'The open field');
