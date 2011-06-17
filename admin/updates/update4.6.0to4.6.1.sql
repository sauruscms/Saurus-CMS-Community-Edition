###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################

# auto-publsihing index cardinality change
alter table `objekt` drop key `avaldatud`, add index `avaldatud` (`avaldamisaeg_algus`, `avaldamisaeg_lopp`);

# statistics UA string longer
alter table `stat_agents` drop index `ind_agt`;
alter table `stat_agents` change `agent` `agent` text(1000) NULL;
alter table `stat_agents` add unique `agents` (`agent`(250));

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.6.1', '2009-06-01', now(), 'The editor button is dead! Long live the editor button!');
