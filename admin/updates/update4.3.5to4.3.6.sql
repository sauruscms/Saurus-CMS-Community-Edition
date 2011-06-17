###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################
update config set nimi='protocol',kirjeldus='Protocol of the public website' where nimi='protocol';

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'force_https_for_editing','0','Force HTTPS for editor environment','1');
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'force_https_for_admin','0','Force HTTPS for admin environment','1');

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.3.6', '2007-03-28', now(), 'and the Loop Guard said: Hold! State your business!');

