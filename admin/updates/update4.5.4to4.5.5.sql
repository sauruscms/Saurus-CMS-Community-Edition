###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################

alter table users change pass_expires pass_expires date  DEFAULT '2029-01-01' NOT NULL;
UPDATE users SET pass_expires = '2029-01-01' WHERE pass_expires = '2009-01-01';

# indexes for replication
alter table objekt add index repl_site_key (repl_site_key(4));
alter table objekt add index related_objekt_id (related_objekt_id);

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.5.5', '2008-08-28', now(), 'Insert snippets and snuppets');
