###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################

# add site id to cache
alter table `cache` add column `site_id` int(10) UNSIGNED NULL after `user_id`;

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.6.5', '2010-01-14', now(), 'moar free stuff pls!');
