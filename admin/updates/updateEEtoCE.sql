###################################################
# Remove licensing and commercial modules
###################################################

TRUNCATE TABLE `license`;
TRUNCATE TABLE `moodulid`;
TRUNCATE TABLE `sso`;
TRUNCATE TABLE `kasutaja_sso`;
TRUNCATE TABLE `ldap_map`;
TRUNCATE TABLE `ldap_servers`;
TRUNCATE TABLE `replicator`;
TRUNCATE TABLE `xml`;
TRUNCATE TABLE `xml_dtd`;
TRUNCATE TABLE `xml_map`;

DELETE FROM `admin_osa` WHERE `id`='37';
DELETE FROM `admin_osa` WHERE `id`='38';
DELETE FROM `admin_osa` WHERE `id`='47';
DELETE FROM `admin_osa` WHERE `id`='88';
DELETE FROM `admin_osa` WHERE `id`='155';
DELETE FROM `admin_osa` WHERE `id`='156';
DELETE FROM `admin_osa` WHERE `id`='157';
DELETE FROM `admin_osa` WHERE `id`='48';
DELETE FROM `admin_osa` WHERE `id`='54';
DELETE FROM `admin_osa` WHERE `id`='63';
DELETE FROM `admin_osa` WHERE `id`='65';
DELETE FROM `admin_osa` WHERE `id`='66';
DELETE FROM `admin_osa` where `id`='41';
DELETE FROM `admin_osa` where `id`='45';
DELETE FROM `admin_osa` where `id`='36';

UPDATE groups SET auth_type = 'CMS';
UPDATE users SET autologin_ip = '';
