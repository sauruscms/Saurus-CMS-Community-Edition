###################################################
# NEW TABLES: (drop & create, without data)
#
###################################################
         
###################################################
# TABLE CHANGES:
###################################################

ALTER TABLE keel CHANGE locale locale VARCHAR(20)  NOT NULL;


###################################################
# NEW DATA TO SYSTEM TABLES: (NB! must ALWAYS FOLLOW all other sections)
###################################################
INSERT INTO config (nimi, sisu, kirjeldus, on_nahtav) VALUES ('NORDEA_MAC_key','','Nordea MAC signature key','0');
insert into config (nimi,sisu,kirjeldus,on_nahtav) values ( 'NORDEA_url','https://solo3.nordea.fi/cgi-bin/SOLOPM01','URL for connecting to the Nordea Solo payment system','0');
insert into config (nimi,sisu,kirjeldus,on_nahtav) values ( 'NORDEA_account',NULL,'ACCOUNT to use for the Nordea Solo payment system','0');
insert into config(nimi,sisu,kirjeldus,on_nahtav) values ( 'NORDEA_username',NULL,'USERNAME for the Nordea Solo payment system','0');


UPDATE keel SET locale="et_EE" WHERE nimi="Estonian";
UPDATE keel SET locale="en_GB" WHERE nimi="English";
UPDATE keel SET locale="ru_RU" WHERE nimi="Russian";
UPDATE keel SET locale="fi_FI" WHERE nimi="Finnish";
UPDATE keel SET locale="af_ZA" WHERE nimi="Afrikaans";
UPDATE keel SET locale="sq_AL" WHERE nimi="Albanian";
UPDATE keel SET locale="am_ET" WHERE nimi="Amharic";
UPDATE keel SET locale="ar_SA" WHERE nimi="Arabic";
UPDATE keel SET locale="hy_AM" WHERE nimi="Armenian";
UPDATE keel SET locale="as_IN" WHERE nimi="Assamese";
UPDATE keel SET locale="ast_ES" WHERE nimi="Asturian";
UPDATE keel SET locale="az_AZ" WHERE nimi="Azerbaijani";
UPDATE keel SET locale="ba_RU" WHERE nimi="Bashkir";
UPDATE keel SET locale="eu_ES" WHERE nimi="Basque";
UPDATE keel SET locale="bn_IN" WHERE nimi="Bengali (Bangla)";
UPDATE keel SET locale="bo_BT" WHERE nimi="Bhutani";
UPDATE keel SET locale="br_FR" WHERE nimi="Breton";
UPDATE keel SET locale="bg_BG" WHERE nimi="Bulgarian";
UPDATE keel SET locale="ca_ES" WHERE nimi="Catalan";
UPDATE keel SET locale="zh_CN" WHERE nimi="Chinese";
UPDATE keel SET locale="zh_TW" WHERE nimi="Chinese of taiwan";
UPDATE keel SET locale="co_FR" WHERE nimi="Corsican";
UPDATE keel SET locale="hr_HR" WHERE nimi="Croatian";
UPDATE keel SET locale="cs_CZ" WHERE nimi="Czech";
UPDATE keel SET locale="da_DK" WHERE nimi="Danish";
UPDATE keel SET locale="nl_NL" WHERE nimi="Dutch";
UPDATE keel SET locale="fo_FO" WHERE nimi="Faeroese";
UPDATE keel SET locale="fr_FR" WHERE nimi="French";
UPDATE keel SET locale="fy_NL" WHERE nimi="Frisian";
UPDATE keel SET locale="gl_ES" WHERE nimi="Galician";
UPDATE keel SET locale="ka_GE" WHERE nimi="Georgian";
UPDATE keel SET locale="de_DE" WHERE nimi="German";
UPDATE keel SET locale="el_GR" WHERE nimi="Greek";
UPDATE keel SET locale="kl_GL" WHERE nimi="Greenlandic";
UPDATE keel SET locale="gu_IN" WHERE nimi="Gujarati";
UPDATE keel SET locale="ha_NG" WHERE nimi="Hausa";
UPDATE keel SET locale="he_IL" WHERE nimi="Hebrew";
UPDATE keel SET locale="hi_IN" WHERE nimi="Hindi";
UPDATE keel SET locale="hu_HU" WHERE nimi="Hungarian";
UPDATE keel SET locale="is_IS" WHERE nimi="Icelandic";
UPDATE keel SET locale="id_ID" WHERE nimi="Indonesian";
UPDATE keel SET locale="ga_IE" WHERE nimi="Irish";
UPDATE keel SET locale="it_IT" WHERE nimi="Italian";
UPDATE keel SET locale="ja_JP" WHERE nimi="Japanese";
UPDATE keel SET locale="kn_IN" WHERE nimi="Kannada";
UPDATE keel SET locale="kk_KZ" WHERE nimi="Kazakh";
UPDATE keel SET locale="rw_RW" WHERE nimi="Kinyarwanda";
UPDATE keel SET locale="ky_KG" WHERE nimi="Kirghiz";
UPDATE keel SET locale="ko_KR" WHERE nimi="Korean";
UPDATE keel SET locale="lv_LV" WHERE nimi="Latvian (Lettish)";
UPDATE keel SET locale="lt_LT" WHERE nimi="Lithuanian";
UPDATE keel SET locale="mk_MK" WHERE nimi="Macedonian";
UPDATE keel SET locale="ms_MY" WHERE nimi="Malay";
UPDATE keel SET locale="ml_IN" WHERE nimi="Malayalam";
UPDATE keel SET locale="mt_MT" WHERE nimi="Maltese";
UPDATE keel SET locale="mi_NZ" WHERE nimi="Maori";
UPDATE keel SET locale="arn_CL" WHERE nimi="Mapudungun";
UPDATE keel SET locale="mr_IN" WHERE nimi="Marathi";
UPDATE keel SET locale="mn_MN" WHERE nimi="Mongolian";
UPDATE keel SET locale="ne_NP" WHERE nimi="Nepali";
UPDATE keel SET locale="no_NO" WHERE nimi="Norwegian";
UPDATE keel SET locale="oc_FR" WHERE nimi="Occitan";
UPDATE keel SET locale="or_IN" WHERE nimi="Oriya";
UPDATE keel SET locale="ps_AF" WHERE nimi="Pastho (Pustho)";
UPDATE keel SET locale="fa_IR" WHERE nimi="Persian";
UPDATE keel SET locale="pl_PL" WHERE nimi="Polish";
UPDATE keel SET locale="pt_PT" WHERE nimi="Portuguese";
UPDATE keel SET locale="pa_IN" WHERE nimi="Punjabi";
UPDATE keel SET locale="quz_EC" WHERE nimi="Quechua";
UPDATE keel SET locale="sa_IN" WHERE nimi="Sanskrit";
UPDATE keel SET locale="sr_SP" WHERE nimi="Serbian";
UPDATE keel SET locale="ns_ZA" WHERE nimi="Sesotho";
UPDATE keel SET locale="tn_ZA" WHERE nimi="Setswana";
UPDATE keel SET locale="sk_SK" WHERE nimi="Slovak";
UPDATE keel SET locale="sl_SI" WHERE nimi="Slovenian";
UPDATE keel SET locale="es_ES" WHERE nimi="Spanish";
UPDATE keel SET locale="sw_KE" WHERE nimi="Swahili";
UPDATE keel SET locale="sv_SE" WHERE nimi="Swedish";
UPDATE keel SET locale="tg_TJ" WHERE nimi="Tajik";
UPDATE keel SET locale="ta_IN" WHERE nimi="Tamil";
UPDATE keel SET locale="tt_RU" WHERE nimi="Tatar";
UPDATE keel SET locale="te_IN" WHERE nimi="Telugu";
UPDATE keel SET locale="th_TH" WHERE nimi="Thai";
UPDATE keel SET locale="tr_TR" WHERE nimi="Turkish";
UPDATE keel SET locale="tk_TM" WHERE nimi="Turkmen";
UPDATE keel SET locale="uk_UA" WHERE nimi="Ukrainian";
UPDATE keel SET locale="ur_PK" WHERE nimi="Urdu";
UPDATE keel SET locale="uz_UZ" WHERE nimi="Uzbek";
UPDATE keel SET locale="vi_VN" WHERE nimi="Vietnamese";
UPDATE keel SET locale="cy_GB" WHERE nimi="Welsh";
UPDATE keel SET locale="wo_SN" WHERE nimi="Wolof";
UPDATE keel SET locale="xh_ZA" WHERE nimi="Xhosa";
UPDATE keel SET locale="yo_NG" WHERE nimi="Yoruba";
UPDATE keel SET locale="zu_ZA" WHERE nimi="Zulu";

INSERT INTO keel (keel_id, nimi, encoding, extension, on_default, on_default_admin, on_kasutusel, site_url, page_ttyyp_id, ttyyp_id, locale) VALUES (157, 'Romanian', 'UTF-8', NULL, 0, 0, 0, NULL, 0, 0, 'ro_RO');
INSERT INTO keel (keel_id, nimi, encoding, extension, on_default, on_default_admin, on_kasutusel, site_url, page_ttyyp_id, ttyyp_id, locale) VALUES (158, 'Belarusian', 'UTF-8', NULL, 0, 0, 0, NULL, 0, 0, 'be_BY');
INSERT INTO keel (keel_id, nimi, encoding, extension, on_default, on_default_admin, on_kasutusel, site_url, page_ttyyp_id, ttyyp_id, locale) VALUES (159, 'Bosnian', 'UTF-8', NULL, 0, 0, 0, NULL, 0, 0, 'bs_BA');
INSERT INTO keel (keel_id, nimi, encoding, extension, on_default, on_default_admin, on_kasutusel, site_url, page_ttyyp_id, ttyyp_id, locale) VALUES (160, 'Filipino (Philippines)', 'UTF-8', NULL, 0, 0, 0, NULL, 0, 0, 'fil_PH');
INSERT INTO keel (keel_id, nimi, encoding, extension, on_default, on_default_admin, on_kasutusel, site_url, page_ttyyp_id, ttyyp_id, locale) VALUES (161, 'Dari', 'UTF-8', NULL, 0, 0, 0, NULL, 0, 0, 'gbz_AF');
INSERT INTO keel (keel_id, nimi, encoding, extension, on_default, on_default_admin, on_kasutusel, site_url, page_ttyyp_id, ttyyp_id, locale) VALUES (162, 'Luxembourgish', 'UTF-8', NULL, 0, 0, 0, NULL, 0, 0, 'lb_LU');
INSERT INTO keel (keel_id, nimi, encoding, extension, on_default, on_default_admin, on_kasutusel, site_url, page_ttyyp_id, ttyyp_id, locale) VALUES (163, 'Romansh', 'UTF-8', NULL, 0, 0, 0, NULL, 0, 0, 'rm_CH');
INSERT INTO keel (keel_id, nimi, encoding, extension, on_default, on_default_admin, on_kasutusel, site_url, page_ttyyp_id, ttyyp_id, locale) VALUES (164, 'Syriac', 'UTF-8', NULL, 0, 0, 0, NULL, 0, 0, 'syr_SY');


UPDATE objekt SET pealkiri= 'Sait' WHERE keel = 0 AND sys_alias = 'home';
UPDATE objekt SET pealkiri= 'Site' WHERE keel = 1 AND sys_alias = 'home';
UPDATE objekt SET pealkiri= 'Süsteem' WHERE keel = 0 AND sys_alias = 'system';
UPDATE objekt SET pealkiri= 'System' WHERE keel = 1 AND sys_alias = 'system';



###################################################
# VERSION CHANGE:
###################################################

INSERT INTO version (version_nr, release_date, install_date, description) VALUES ('4.4.3', '2007-09-05', now(), 'Rene\'s Birthday Edition');

