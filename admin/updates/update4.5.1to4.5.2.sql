###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################
alter table objekt change comment_count comment_count int (10)UNSIGNED  DEFAULT '0' NULL;

# convert old log messages into new table
insert into sitelog (date, objekt_id, username, type, action, message)
select aeg, objekt_id, sisestaja, if(is_error or on_error, 1, 0), if(on_import, 6, if(on_eksport, 7 , 0)), text from logi;

# drop old log table
drop table logi;

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.5.2', '2008-06-05', now(), 'Panem et Alias\'s');
