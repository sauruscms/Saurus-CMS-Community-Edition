###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################
alter table objekt change aeg aeg datetime  DEFAULT '0000-00-00' NOT NULL;

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ('users_require_safe_password','0','Check for password difficulty','1');
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'bank_connection_logfile','','The log file for Bank Connection','0');

# Tools menu re-sorting
UPDATE admin_osa SET sorteering= 10 WHERE id=85;
UPDATE admin_osa SET sorteering= 20 WHERE id=15;
UPDATE admin_osa SET sorteering= 30 WHERE id=41;
UPDATE admin_osa SET sorteering= 40 WHERE id=84;
UPDATE admin_osa SET sorteering= 45 WHERE id=63;
UPDATE admin_osa SET sorteering= 50 WHERE id=66;
UPDATE admin_osa SET sorteering= 55 WHERE id=150;
UPDATE admin_osa SET sorteering= 60 WHERE id=62;
UPDATE admin_osa SET sorteering= 65 WHERE id=77;

insert into admin_osa(id,parent_id,sorteering,nimetus,eng_nimetus,fail,moodul_id,extension) values ( '152','69','70','Explorer','Explorer','site_explorer.php','0',NULL);

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.4.1', '2007-06-28', now(), 'Your password is weak, short and bald!');

