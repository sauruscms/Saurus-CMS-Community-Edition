###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################


###################################################
# TABLE CHANGES:
###################################################

### converter 3.5.0 => 4.2.1

ALTER TABLE objekt ADD repl_site_key CHAR(4);
ALTER TABLE logi ADD COLUMN is_error TINYINT(3) UNSIGNED DEFAULT NULL;
ALTER TABLE logi ADD COLUMN data_type VARCHAR(255) DEFAULT NULL;
ALTER TABLE logi ADD COLUMN xml_files TEXT NOT NULL;
ALTER TABLE ldap_servers ADD COLUMN only_bind TINYINT(1) UNSIGNED DEFAULT '0';
ALTER TABLE tbl ADD pp_dok_liik TEXT NOT NULL;
ALTER TABLE tbl DROP INDEX field;
ALTER TABLE tbl ADD INDEX kompl (field,tbl,pp_dok_liik(255),ttyyp_id);

ALTER TABLE shop_cart MODIFY COLUMN date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE shop_cart ADD cart_session VARCHAR(32); 
ALTER TABLE shop_cart ADD is_saved TINYINT(1) UNSIGNED DEFAULT "0"; 
ALTER TABLE shop_cart CHANGE user user_id BIGINT(20) NOT NULL; 
ALTER TABLE shop_cart ADD UNIQUE cart_session (cart_session,user_id);

ALTER TABLE shop_order ADD transport_vat FLOAT(5,2)  AFTER transport; 

ALTER TABLE product MODIFY COLUMN category_id INTEGER(11) UNSIGNED DEFAULT '0';
ALTER TABLE product MODIFY COLUMN is_published TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE product MODIFY COLUMN datetime_1 DATETIME DEFAULT NULL;
ALTER TABLE product_category MODIFY COLUMN parent_id INTEGER(11) UNSIGNED DEFAULT NULL;
ALTER TABLE product_category_description MODIFY COLUMN name VARCHAR(255) DEFAULT NULL;

CREATE TABLE notifications (
  notification_id tinyint(3) unsigned NOT NULL auto_increment,
  type varchar(255) NOT NULL default '0',
  name varchar(255) default '0',
  value varchar(255) default '0',
  value_type enum('active','run','send','misc','mails') default NULL,
  PRIMARY KEY  (notification_id)
);

### / converter 3.5.0 => 4.2.1

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################


###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, description) VALUES ('4.2.1', '2006-06-09', 'the summer is magic!');

