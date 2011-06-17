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

UPDATE admin_osa SET moodul_id= 9 WHERE fail='tabelid.php';

insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'send_error_notifiations_to_superusers','0','Send error notifications to all superusers (Y/N)','1');
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'send_error_notifiations_to','','E-mail addresses to send error notifications (comma separated)','1');
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'send_error_notifiations_setting','0','Method for error notifications activation (inactive, pageload, cronjob)','1');
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'send_error_notifiations_last_run',now(),'Last time when error notifications were sent','0');

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.4.5', '2007-10-26', now(), 'I demand you show me your friendly URL!');

