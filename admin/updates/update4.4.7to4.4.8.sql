###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################
insert into config (nimi, sisu, kirjeldus, on_nahtav) values('mailinglist_sending_option','0','Mailinglist send type','1');

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.4.8', '2008-01-23', now(), 'Pick your date');

