###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################


###################################################
# TABLE CHANGES:
###################################################
alter table sys_sonad_kirjeldus change sst_id sst_id smallint (6) UNSIGNED  DEFAULT '0' NOT NULL;

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################
insert into config ( nimi, sisu, kirjeldus, on_nahtav ) values (  'allow_commenting',  '1',  'Allow commenting',  '1' );
insert into config ( nimi, sisu, kirjeldus, on_nahtav ) values (  'check_for_captcha',  '0',  'Use CAPTCHA verification for comments and feedback forms (requires CAPTCHA extension).',  '1' );

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, description) VALUES ('4.2.3', '2006-08-11', 'gotchacaptcha');

