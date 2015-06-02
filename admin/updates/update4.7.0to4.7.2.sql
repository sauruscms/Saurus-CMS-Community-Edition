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
INSERT INTO `config` (`nimi`, `sisu`, `kirjeldus`, `on_nahtav`) VALUES ('allow_onsite_translation', '1', 'Allow on-site translation (syswords)', '1');


###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.8.0', '2015-01-01', now(), 'Big Bang Theory');

