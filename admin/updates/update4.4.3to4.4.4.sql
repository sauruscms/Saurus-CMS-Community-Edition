###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################

ALTER TABLE `tbl` CHANGE `tbl` `tbl` VARCHAR(50);
ALTER TABLE `tbl` CHANGE `field` `field` VARCHAR(50);
ALTER TABLE `tbl` DROP INDEX kompl, ADD INDEX kompl (field,tbl,ttyyp_id);

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'ESTCARD_url','https://pos.estcard.ee/test-pos/servlet/iPAYServlet','URL for connecting to ESTCARD payment system','0');
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'ESTCARD_signature',NULL,'SIGNATURE path or text for the ESTCARD payment system','0');
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'ESTCARD_id','','ID in the ESTCARD payment system','0');

INSERT INTO moodulid (moodul_id, nimi, on_aktiivne, is_invisible, status) VALUES (35, 'E-payments', 1, 0, '');

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.4.4', '2007-09-25', now(), 'I has fixed bug, kthnxbai');

