###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################


###################################################
# TABLE CHANGES:
###################################################
update config set  nimi='SAMPO_url',  sisu='https://www2.sampo.ee/cgi-bin/pizza',  on_nahtav='0' where nimi='SAMPO_url' ;
alter table allowed_mails add column objekt_id_list text   NULL  after mail;
insert into admin_osa(id,parent_id,sorteering,nimetus,eng_nimetus,fail,moodul_id,extension) values ( '150','69','16','Feedback forms','Feedback forms','feedbackforms_handler.php','0',NULL);
alter table objekt change kesk kesk smallint UNSIGNED  DEFAULT '0' NOT NULL;
###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, description) VALUES ('4.3.0', '2006-09-29', 'Explorer frenzy');

