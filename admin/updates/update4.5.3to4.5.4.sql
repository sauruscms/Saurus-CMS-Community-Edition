###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################

alter table users change pass_expires pass_expires date  DEFAULT '2029-01-01' NOT NULL;
UPDATE users SET pass_expires = '2029-01-01' WHERE pass_expires = '2009-01-01';

# replace links to the public files in articles (body) with the new path '##saurus649code##' => '##saurus649##code/public'
update obj_artikkel, config
set obj_artikkel.sisu = replace(obj_artikkel.sisu, '##saurus649code##',concat('##saurus649code##',config.sisu))
where config.nimi = 'file_path'
and instr(obj_artikkel.sisu,concat('##saurus649code##',config.sisu))=0;

# replace links to the public files in articles (lead) with the new path '##saurus649code##' => '##saurus649##code/public'
update obj_artikkel, config
set obj_artikkel.lyhi = replace(obj_artikkel.lyhi, '##saurus649code##',concat('##saurus649code##',config.sisu))
where config.nimi = 'file_path'
and instr(obj_artikkel.lyhi,concat('##saurus649code##',config.sisu))=0;

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

# Report a bug menu item
insert into admin_osa(id,parent_id,sorteering,nimetus,eng_nimetus,fail,moodul_id,extension,show_in_editor) values ( '155','78','20','','Report a bug','send_feedback.php','0',NULL,'0');

# Report a bug e-mail address
insert into config (nimi, sisu, kirjeldus, on_nahtav) values('send_feedback_email','feedback@saurus.info','E-mail address where bug, feature request and other feedback are sent','0');

# XML export did not use encoding_to field before
UPDATE xml SET encoding_to=encoding WHERE direction = 'E';

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.5.4', '2008-08-01', now(), 'All your feedback are belong to us!');
