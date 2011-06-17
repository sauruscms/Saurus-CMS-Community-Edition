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
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'alias_trail_format','0','Alias format','1');
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'alias_language_format','0','Alias language format','1');

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.5.1', '2008-05-08', now(), 'Less is more');
