###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################

###################################################
# TABLE CHANGES:
###################################################

ALTER TABLE `obj_kommentaar` ADD `url` VARCHAR(100);
alter table shop_order change sum sum float (9,2)  NULL , change vat vat float (9,2)  NULL , change transport transport float (9,2)  NULL , change transport_vat transport_vat float (9,2)  NULL; 
ALTER TABLE `objekt` CHANGE `pealkiri_strip` `pealkiri_strip` VARCHAR(255);

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

INSERT INTO config VALUES ('KREP_account','','ACCOUNT to use for the KREP payment system','0');
INSERT INTO config VALUES ('KREP_signature','','SIGNATURE path or text for the KREP payment system','0');
INSERT INTO config VALUES ('KREP_url','https://i-pank.krediidipank.ee/','URL for connecting to the KREP payment system','0');
INSERT INTO config VALUES ('KREP_username','','USERNAME for the KREP payment system','0');


###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, description) VALUES ('4.3.2', '2006-11-23', 'PÖFF has arrived!');

