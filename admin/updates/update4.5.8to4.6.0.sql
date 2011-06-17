###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################
alter table obj_file add column relative_path tinytext NULL after fullpath;
alter table obj_folder add column relative_path tinytext NULL after fullpath;

update objekt set keel = 1 where tyyp_id in (21,22);

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

insert into moodulid(moodul_id,nimi,on_aktiivne,is_invisible,status) values ( '38','Content Restriction 1','0','1','');
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'fm_allow_multiple_upload','1','Allow for multiple file uploading','1');

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.6.0', '2009-04-29', now(), '');
