###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################

# remove statistic tables
DROP TABLE IF EXISTS `stat_agents`;
DROP TABLE IF EXISTS `stat_logs`;
DROP TABLE IF EXISTS `stat_domains`;
DROP TABLE IF EXISTS `stat_summary`;
DROP TABLE IF EXISTS `stat_visitors`;
DROP TABLE IF EXISTS `stats_objects`;

# remove Spam table
DROP TABLE IF EXISTS `spam`;

# drop Mapped sections table
DROP TABLE IF EXISTS `lang_mapping`;

# remove v3 PHP templates
DROP TABLE IF EXISTS `ext_isik`;
DROP TABLE IF EXISTS `ext_komand`;
DROP TABLE IF EXISTS `ext_yksus`;

# remove the E-shop data tables
DROP TABLE IF EXISTS `product`;
DROP TABLE IF EXISTS `product_category`;
DROP TABLE IF EXISTS `product_category_description`;
DROP TABLE IF EXISTS `product_description`;
DROP TABLE IF EXISTS `product_manufacturer`;
DROP TABLE IF EXISTS `product_profiles`;
DROP TABLE IF EXISTS `shop_cart`;
DROP TABLE IF EXISTS `shop_order`;

DROP TABLE IF EXISTS `_tmp_sonad`;

# remove v3 styles
DROP TABLE IF EXISTS `styles`;

# misc removals
DROP TABLE IF EXISTS `xdb`;
DROP TABLE IF EXISTS `objekt_ext_parents`;

###################################################
# TABLE CHANGES:
###################################################

# remove statistics

# remove statistics module
DELETE FROM `moodulid` WHERE `moodul_id`='26';

# remove the admin page
DELETE FROM `admin_osa` WHERE `id`='84';

# remove statistic configuration options
DELETE FROM `config` WHERE `nimi`='stats_own_IP';
DELETE FROM `config` WHERE `nimi`='stats_enabled';
DELETE FROM `config` WHERE `nimi`='stats_last_calculate';
DELETE FROM `config` WHERE `nimi`='stats_own_referers';
DELETE FROM `config` WHERE `nimi`='stats_store_days';

# remove Personell module
DELETE FROM `moodulid` WHERE `moodul_id`='7';

# remove ServIT admin pages
DELETE FROM `admin_osa` WHERE `id`='61';
DELETE FROM `admin_osa` WHERE `id`='50';

# remove Spam module
DELETE FROM `moodulid` WHERE `moodul_id`='16';

# remove Spam admin page
DELETE FROM `admin_osa` WHERE `id`='52';

# remove Mapped sections admin page
DELETE FROM `admin_osa` WHERE `id`='53';

# remove Mapped sections module
DELETE FROM `moodulid` WHERE `moodul_id`='18';

# remove metadata
DELETE FROM `moodulid` WHERE `moodul_id`='11';
ALTER TABLE `objekt` DROP COLUMN `metadata_tyyp_id`;
ALTER TABLE `objekt` DROP COLUMN `metadata_strip`;
DROP TABLE `metadata`;
DROP TABLE `metadata_tyyp`;

# remove Plotting
DELETE FROM `moodulid` WHERE `moodul_id`='22';

# remove ADR 2
DELETE FROM `moodulid` WHERE `moodul_id`='27';

# remove Resources
DELETE FROM objekt_objekt WHERE objekt_id IN (SELECT objekt_id FROM objekt WHERE tyyp_id = 23);
DELETE FROM objekt_objekt WHERE objekt_id IN (SELECT objekt_id FROM obj_resource);
DELETE FROM objekt WHERE objekt_id IN (SELECT objekt_id FROM obj_resource);
DELETE FROM objekt WHERE tyyp_id = 23;
DROP TABLE `obj_resource`;

DELETE FROM `tyyp` WHERE `tyyp_id`='23';

DELETE FROM `moodulid` WHERE `moodul_id`='34';

DELETE FROM `admin_osa` WHERE `id`='82';

# remove event bindings
DROP TABLE `event_bindings`;

# remove event type
DELETE FROM `tyyp` WHERE `tyyp_id`='18';

# remove Saurus API module
DELETE FROM `moodulid` WHERE `moodul_id`='13';
UPDATE admin_osa SET moodul_id = 0 WHERE moodul_id = 13;

# remove Alias module
DELETE FROM `moodulid` WHERE `moodul_id`='14';

# remove v3 PHP templates
DELETE FROM `templ_tyyp` WHERE `ttyyp_id`='41';
DELETE FROM `templ_tyyp` WHERE `ttyyp_id`='42';
DELETE FROM `templ_tyyp` WHERE `ttyyp_id`='43';
DELETE FROM `templ_tyyp` WHERE `ttyyp_id`='46';

# switch PHP templates to API templates
# default templates for sites
UPDATE keel SET page_ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/page_templates/modern_page_template.html') WHERE (page_ttyyp_id = 0 AND on_kasutusel = 1) OR page_ttyyp_id = 1;
UPDATE keel SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/content_templates/articles.html') WHERE (ttyyp_id = 0 AND on_kasutusel = 1) OR ttyyp_id = 1;

# default templates for objects
UPDATE objekt SET page_ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/page_templates/modern_page_template.html') WHERE page_ttyyp_id = 1;
UPDATE objekt SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/content_templates/articles.html') WHERE ttyyp_id = 1;

# match v3 templates to v4
# articles in two columns
UPDATE objekt SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/content_templates/articles_2_columns.html') WHERE ttyyp_id = 12;
# news
UPDATE objekt SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/content_templates/news_list.html') WHERE ttyyp_id = 13;
# headlines
UPDATE objekt SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/content_templates/content_templates/article_list.html') WHERE ttyyp_id = 45;
# forum
UPDATE objekt SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/content_templates/forum.html') WHERE ttyyp_id = 40;
# adv search
UPDATE objekt SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/content_templates/advanced_search.html') WHERE ttyyp_id = 27;
# documents
UPDATE objekt SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/content_templates/documents.html') WHERE ttyyp_id = 11;
# gallery
UPDATE objekt SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/content_templates/gallery_list.html') WHERE ttyyp_id = 39;

# default the rest
UPDATE objekt SET ttyyp_id = 0 WHERE ttyyp_id < 100;
UPDATE objekt SET page_ttyyp_id = 0 WHERE page_ttyyp_id < 100;

# object templates
# image
UPDATE tyyp SET ttyyp_id = 0 WHERE ttyyp_id = 39 AND tyyp_id = 12;
# document
UPDATE tyyp SET ttyyp_id = 0 WHERE ttyyp_id = 11 AND tyyp_id = 7;
# album
UPDATE tyyp SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/object_templates/gallery.html') WHERE ttyyp_id = 39 AND tyyp_id = 16;
# forum topic
UPDATE tyyp SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/object_templates/forum_topic.html') WHERE ttyyp_id = 40 AND tyyp_id = 15;
# forum message
UPDATE tyyp SET ttyyp_id = (SELECT ttyyp_id FROM templ_tyyp WHERE templ_fail = '../../../extensions/saurus4/object_templates/forum_message.html') WHERE ttyyp_id = 40 AND tyyp_id = 14;

# remove PHP template columns
ALTER TABLE `templ_tyyp` DROP COLUMN `moodul_id`, DROP COLUMN `on_objekt_only`, DROP COLUMN `eri_params`, DROP COLUMN `on_aeg`, DROP COLUMN `on_konfigureeritav`, DROP COLUMN `pilt`, DROP COLUMN `kirjeldus`;

# remove PHP template columns
ALTER TABLE `tyyp` DROP COLUMN `bgcolor`, DROP COLUMN `on_kaardil_nahtav`, DROP COLUMN `on_navis_nahtav`, DROP COLUMN `on_konfigureeritav`, DROP COLUMN `nupude_prefix`, DROP COLUMN `win_width`, DROP COLUMN `win_height`;

# remove content restriction
DELETE FROM `moodulid` WHERE `moodul_id`='38';

# remove E-shop
# remove the module
DELETE FROM `moodulid` WHERE `moodul_id`='19';

# remove the admin-pages
DELETE FROM `admin_osa` WHERE `id`='55';
DELETE FROM `admin_osa` WHERE `id`='56';
DELETE FROM `admin_osa` WHERE `id`='57';
DELETE FROM `admin_osa` WHERE `id`='64';
DELETE FROM `admin_osa` WHERE `id`='70';

# remove scheduled publishing module
DELETE FROM `moodulid` WHERE `moodul_id`='36';

# remove cache module
DELETE FROM `moodulid` WHERE `moodul_id`='21';

# remove SEO module
DELETE FROM `moodulid` WHERE `moodul_id`='30';

# remove ACL module
DELETE FROM `moodulid` WHERE `moodul_id`='5';
UPDATE `admin_osa` SET `moodul_id`='0' WHERE `moodul_id`='5';

# remove Custom assets module
DELETE FROM `moodulid` WHERE `moodul_id`='25';

# remove Profiles module
DELETE FROM `moodulid` WHERE `moodul_id`='33';
UPDATE `admin_osa` SET `moodul_id`='0' WHERE `moodul_id`='33';

###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################

# remove session life configuration
DELETE FROM `config` WHERE `nimi`='session_lifetime';

# remove gzip compression configuration
DELETE FROM `config` WHERE `nimi`='compress_level';

# remove e-shop configuration
DELETE FROM `config` WHERE `nimi`='product_img_path';
DELETE FROM `config` WHERE `nimi`='product_add_for_all_languages';
DELETE FROM `config` WHERE `nimi`='shop_vat_percent';
DELETE FROM `config` WHERE `nimi`='shop_order_email';
DELETE FROM `config` WHERE `nimi`='shop_currency';
DELETE FROM `config` WHERE `nimi`='shoppingcart_vat_formula';
DELETE FROM `config` WHERE `nimi`='ignore_special_price';

# remove v3 template config
DELETE FROM `admin_osa` WHERE `id`='10';

# remove master templates page (the functionality is in "Sites" page)
DELETE FROM `admin_osa` WHERE `id`='49';

# clean up mailinglists
ALTER TABLE `user_mailinglist` DROP COLUMN `ext_info`;
ALTER TABLE `user_mailinglist` DROP COLUMN `is_sms`;
ALTER TABLE `user_mailinglist` DROP COLUMN `is_email`;

# clean up objekt
ALTER TABLE `objekt` DROP COLUMN `pilt`;
ALTER TABLE `objekt` DROP COLUMN `pilt_aktiivne`;
ALTER TABLE `objekt` DROP COLUMN `banner`;
ALTER TABLE `objekt` DROP COLUMN `laius`;
ALTER TABLE `objekt` DROP COLUMN `on_kustutatud`;
ALTER TABLE `objekt` DROP COLUMN `admin_id`;
ALTER TABLE `objekt` DROP COLUMN `added_time`;
ALTER TABLE `objekt` DROP COLUMN `ext_id`;
 
# clean up extensions
ALTER TABLE `extensions` DROP COLUMN `parent_id`;

# clean up comments
ALTER TABLE `obj_kommentaar` DROP COLUMN `parent_topic_id`;

###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.6.6', '2010-03-31', now(), 'On our way to open fields');
