###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################


###################################################
# TABLE CHANGES:
###################################################
update config set  nimi='check_for_captcha',  sisu='0',  kirjeldus='Use CAPTCHA verification for comments and forums (requires CAPTCHA extension).',  on_nahtav='1' where nimi='check_for_captcha' ;
update config set  nimi='SAMPO_url',  sisu='https://www.sampo.ee/cgi-bin/pizza',  on_nahtav='0' where nimi='SAMPO_url' ;

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################
insert into config ( nimi, sisu, kirjeldus, on_nahtav ) values (  'feedbackform_action',  'form.php',  'Default form action',  '1' );
insert into config ( nimi, sisu, kirjeldus, on_nahtav ) values (  'feedbackform_form_name',  'SCMSForm',  'Default form name',  '1' );
insert into config ( nimi, sisu, kirjeldus, on_nahtav ) values (  'feedbackform_method',  'POST',  'Default form method',  '1' );
insert into config ( nimi, sisu, kirjeldus, on_nahtav ) values (  'feedbackform_check_for_captcha',  '0',  'Use CAPTCHA verification for feedback forms (requires CAPTCHA extension).',  '1' );

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, description) VALUES ('4.2.4', '2006-09-08', 'this is how we do it!');

