###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################

CREATE TABLE config_images (definition_id int(10) unsigned NOT NULL auto_increment, name text, value text, PRIMARY KEY  (definition_id));
###################################################
# TABLE CHANGES:
###################################################
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'search_result_excerpt_length','180','Search result excerpt length','0');

alter table objekt add column fulltext_keywords text   NULL after sisu_strip;
alter table objekt add fulltext fulltext_search (pealkiri_strip, sisu_strip);
alter table objekt add fulltext fulltext_keywords (fulltext_keywords);

DELETE FROM admin_osa  WHERE fail = 'atp_asutus.php';

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################
insert into admin_osa(id,parent_id,sorteering,nimetus,eng_nimetus,fail,moodul_id,extension) values ( '151','19','8','Piltide haldus','Image manipulation','images_config.php','0',NULL);

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, description) VALUES ('4.3.1', '2006-11-01', 'to search or not to search');

