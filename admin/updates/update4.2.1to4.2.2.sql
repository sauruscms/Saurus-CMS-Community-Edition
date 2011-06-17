###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################


###################################################
# TABLE CHANGES:
###################################################

ALTER TABLE license CHANGE type type BLOB;
ALTER TABLE moodulid CHANGE status status BLOB NOT NULL;

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################


###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, description) VALUES ('4.2.2', '2006-07-14', 'Zidane ruleZ');

