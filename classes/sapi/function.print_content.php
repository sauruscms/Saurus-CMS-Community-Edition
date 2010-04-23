<?php
/**
 * This source file is is part of Saurus CMS content management software.
 * It is licensed under MPL 1.1 (http://www.opensource.org/licenses/mozilla1.1.php).
 * Copyright (C) 2000-2010 Saurused Ltd (http://www.saurus.info/).
 * Redistribution of this file must retain the above copyright notice.
 * 
 * Please note that the original authors never thought this would turn out
 * such a great piece of software when the work started using Perl in year 2000.
 * Due to organic growth, you may find parts of the software being
 * a bit (well maybe more than a bit) old fashioned and here's where you can help.
 * Good luck and keep your open source minds open!
 * 
 * @package		SaurusCMS
 * @copyright	2000-2010 Saurused Ltd (http://www.saurus.info/)
 * @license		Mozilla Public License 1.1 (http://www.opensource.org/licenses/mozilla1.1.php)
 * 
 */


#################################
# function print_content
#	tpl => default: 1 column
#	var1 => value1
#	var2 => value2
#	...
#
# prints fixed template in content area
# 1. Possible parameters for template name (tpl) are:
#	1 column
#	2 column
#	news
#	headlines
#	documents
#	gallery
#	forum
#   <HTML-based template name>
#
# if no parameter is given, will print template which is set for this object
# 
# 2. Variable list can be send as parameters to pass page template variables to the content template.

##################
# sisumalli leidmise PRIORITEETIDE Jļæ½RJEKORD
# 1. kas mļæ½ļæ½ratud {print_content}-i parameeter "tpl" ?
# 2. kas URL-is on antud ette sisumall "c_tpl" parameetriga ?
# 3. kas edit-aknas on mļæ½ļæ½ratud sisumall ?
# 4. kasutame default malli (master sisumalli vļæ½i default fix-malli)

function smarty_function_print_content ($params) {
	global $site, $leht, $template;
	
	static $print_content_count = 0;
	
	$content_template = $leht->content_template; #(removed pointer by Bug #1597)
	# Mļæ½rkus: $leht->content_template on f-ni alguses mļæ½ļæ½ratud ainult siis
	# kui rubriigi edit-aknas on pandud mingi sisumall,
	# juhul kui see on tļæ½hi, on ka muutuja tļæ½hi ja vļæ½ļæ½rtustatakse alles selle f-ni lļæ½pus

	# Kui rubriigi edit-aknas on objektile mļæ½ļæ½ratud sisumall AGA seni pole leht->ontnet_template-i loodud
	# (miks ei ole, on hea kļæ½simus), siis loo siin saidi sisumall.
	# (See fiksab BUGi #2259: Parameetri tpl= kasutamisel kaotatakse ļæ½ra rubriigi sisumall)
	if (!$leht->content_template->fail && $leht->objekt->all['ttyyp_id']) { 
		$leht->content_template = new Template($leht, '', $leht->objekt->all['ttyyp_id']);
	}
	##############
	# default values

	extract($params);


	#printr("objekti enda content_template=".$leht->content_template->fail." => print_content_count=".$print_content_count. '/ tpl='.$tpl. "; c_tpl=".$site->fdat[c_tpl]);

	# internal variable: remember if parameter "tpl" was provided or not
	if(isset($tpl)) { $is_tpl_param = true; } else { $is_tpl_param = false;  }

	##### KAS OBJEKTIL ENDAL MALL vļæ½i mitte: (Bug #1597)
	# juhul kui pole URL-i parameetrit "c_tpl" JA pole {print_content}-i parameetrit "tpl", 
	# siis peame kasutama objekti enda sisumalli.
	if(!isset($tpl) && !$site->fdat[c_tpl]) {
		$tpl = $leht->content_template->all['nimi'];
		$fail = $leht->content_template->fail;
	}

	##################
	# UNSET TEMPLATE
	# juhul kui SAPI-s on kasutatud mallis mitut {print_content} tagi,
	# siis nullida vahepeal muutuja 

	$print_content_count++;
	if($print_content_count > 1) {
		unset($content_template);
	}
	
	##############
	# IF DEFAULT TEMPLATE
	# kui tag-i parameetrina tpl-i pole kaasas 
	# JA ( objektile pole edit-aknas sisumalli mļæ½ļæ½ratud Vļæ½I tegu on master content template-ga )
	# JA pole eritemplate
	# => siis kasuta default-i (default vļæ½ib olla php fix vļæ½i html-mall)

	# Bug #2276: Objekti detailvaates ei arvestata c_tpl URL-i parameetriga
    if(!isset($tpl) && !$site->fdat[c_tpl]
		&& (!$leht->objekt->all[ttyyp_id] || $leht->content_template->is_default) 
		&& !$leht->eritemplate
	) { 
		# 4.1 kui aktiivse objekti klassile on mļæ½ļæ½ratud oma pļæ½himall,
		# siis kasuta kļæ½igepealt seda (malli ID asub tabelis tyyp.ttyyp_id)
		# ($site->objtype_tpl on massiiv kujul $this->objtype_tpl[objekti tļæ½ļæ½p] => malli ID)
		if( $site->objtype_tpl[$leht->objekt->all[tyyp_id]] ) {
			$objtype_tpl_id = $site->objtype_tpl[$leht->objekt->all[tyyp_id]];
			$use_objtype_tpl = 1;
		}
		# 4.2 kui on mļæ½ļæ½ratud nii saidi pļæ½himall JA saidi default sisumall
		# siis vļæ½tta default malliks saidi sisumall
		elseif($site->master_tpl[ttyyp_id] && $site->master_cont_tpl[ttyyp_id]) {
			$tpl = $site->master_cont_tpl[nimi];
			$fail = $site->master_cont_tpl['templ_fail'];
			$use_master_cont_tpl = 1;
		}

		# 4.3 default
		else 
		{
			$sql = "select templ_fail, nimi from templ_tyyp where templ_fail = '../../../extensions/saurus4/content_templates/articles.html' limit 1";
			$result = new SQL($sql);
			if($result->rows)
			{
				$row = $result->fetch('ASSOC');
				$fail = $row['templ_fail'];
				$tpl = $row['nimi'];
			}
			else 
			{
				echo 'No content template found.';
				exit;
			}
		}
	}

	##############
	# IF TEMPLATE IS ALREADY CREATED 
	# juhul kui sisumall on edit-aknas mļæ½ļæ½ratud (st pole vaikimisi mall) 
	# Vļæ½i on tegu erimalliga (tema mall on ka teada),
	# siis on tema malli fail juba teada ja ei pea pļæ½ringuga seda otsima
	if(isset($leht->content_template) && 
		(!$leht->content_template->is_default || $site->fdat["op"] != '' || $site->fdat[otsi])
		) {
		$fail = $leht->content_template->fail;
	}

	####################
	# IF PREDEFINED CONTENT TEMPLATE
	# kui on tegemist predefined html-sisumalliga (mis edit-aknas mļæ½ļæ½ratud) 
	# JA PARAMEETRINA POLE TPL KAASA ANTUD
	# siis tema fail ei ole teada ja tuleb teha pļæ½ring selle leidmiseks, 
	# seepļæ½rast omista siin $tpl malli nimega 
	
	if(!$tpl && isset($leht->content_template) && !$leht->content_template->is_default && $leht->content_template->ttyyp_id >= 1000 ) {
		$tpl =  $leht->content_template->all[nimi];
	}

	###################
	# 1. IF TEMPLATE IS SET IN URL (c_tpl) e ANTUD MALLI ID AND parameter "tpl" NOT set(erijuht)
	# siis leia malli nimi (kui sellist malli pole, lļæ½heb default kļæ½ik edasi)
	if($site->fdat[c_tpl] && !$is_tpl_param) {
		# find template id by parameter c_tpl (= template ID)
		$sql = $site->db->prepare("SELECT ttyyp_id, templ_fail FROM templ_tyyp WHERE ttyyp_id = ? AND ttyyp_id >= '1000' LIMIT 1", $site->fdat[c_tpl]);
		$sth = new SQL($sql);
		$t = $sth->fetch();
		if($t[templ_fail]) {
			$filename[$tpl] = $template->smarty->template_dir.$t[templ_fail];
			$is_html = 1;
		}
	}

	###################
	# 2. IF TEMPLATE ON OBJEKTITļæ½ļæ½BI MALL e ANTUD MALLI ID
	# siis leia malli nimi (kui sellist malli pole, lļæ½heb default kļæ½ik edasi)
	if($use_objtype_tpl) {
		# find template id by parameter c_tpl (= template ID)
		$sql = $site->db->prepare("SELECT ttyyp_id, templ_fail FROM templ_tyyp WHERE ttyyp_id = ? AND ttyyp_id >= '1000' LIMIT 1", $objtype_tpl_id);
		$sth = new SQL($sql);
		$t = $sth->fetch();
		if($t[templ_fail]) {
			$filename[$tpl] = $template->smarty->template_dir.$t[templ_fail];
			$is_html = 1;
		}
	}

	###################
	# 3. IF TEMPLATE IS HTML ja meil on olemas MALLI NIMI (tavajuht)
	# kui otsida on vaja dļæ½n-malli faili ja meil on olemas malli NIMI, teha pļæ½ring malli faili leidmiseks

	### SELLE KOHAGA ON OLNUD YYRATULT JAMASID 4.4.0st alates (vt kurikuulus Bug #2475)
	### TÖÖTAMA peaks asi lõpuks nii (at least i hope):
	# kõigepealt leida mall(id) välja "nimi" järgi. Nüüd võib juhtuda, et leitakse MITU malli sama nimega, sest nad asuvad eri extensionites. 
	# Nüüd teha kontroll et kui leitakse mitu, siis tuleks võrrelda var $fail ja välja "templ_fail" väärtust ja võtta $fail väärtusega klappiv kirje.

	if( isset($tpl) && !isset($filename[$tpl]) ) {

		# find template id by parameter tpl (= template name)

	$sql = $site->db->prepare("SELECT ttyyp_id, templ_fail FROM templ_tyyp WHERE nimi = ? AND ttyyp_id >= '1000' ", $tpl);
		$sth = new SQL($sql);
		
		if(isset($fail) && $sth->rows > 1) { # kui leiti sama nimega malle rohkem kui üks (eri extensionitest)
			## siis tee veel üks päring, seekord failinime järgi
			$sql = $site->db->prepare("SELECT ttyyp_id, templ_fail FROM templ_tyyp WHERE templ_fail = ? AND ttyyp_id >= '1000' LIMIT 1", $fail);
			$sth = new SQL($sql);
		}

		$t = $sth->fetch();
		if($t['templ_fail']) {
			$filename[$tpl] = $template->smarty->template_dir.$t[templ_fail];
			$is_html = 1;
		}
	}
	###################
	# IF TEMPLATE IS WITH OP-PARAMETER HTML 
	# kui mall on eritemplate (?op=..) JA pole php-fix mall (laiend<>php), siis pane html-linnuke pļæ½sti

	if($leht->eritemplate && substr($leht->eritemplate, -4) != '.php') {
		$filename[$tpl] = $leht->eritemplate;
		$is_html = 1;
	}

	###################
	# and last if print_content has tpl param
	if(!isset($fail) && $is_tpl_param && $tpl && !isset($filename[$tpl]) ) {
		# find template id by parameter tpl (= template name)
		$sql = $site->db->prepare("SELECT ttyyp_id, templ_fail FROM templ_tyyp WHERE nimi = ? AND ttyyp_id >= '1000' LIMIT 1", $tpl);
		$sth = new SQL($sql);
		$t = $sth->fetch();
		if($t[templ_fail]) {
			$filename[$tpl] = $template->smarty->template_dir.$t[templ_fail];
			$is_html = 1;
		}
	}
	
	##################
	# PRIORITIES
	# leitud malli arvestamise jļæ½rjekord:
	# 1. kas on mļæ½ļæ½ratud rubriigi edit-aknas ($fail)
	# 2. kas on leitud dļæ½naamilise malli fail
	$fail = $fail ? $fail : $filename[$tpl];
	
	##############
	# not print left & right column in default templates 
	$template->only_content = 1;

	#############
	# print html-template
	if($is_html) {

		### tee uus sisumall, mida trļæ½kkida

		$content_template = new Template($leht, '', $t[ttyyp_id]);

		# BUG  #1597: siin oli omistamisel viga: juhul kui ma siin rikun ļæ½ra vļæ½ļæ½rtuse, mis oli edit-aknas mļæ½ļæ½ratud, siis kasut.edapsidi seda malli puhta {print_content}-i trļæ½kiks
		# aga juhul kui ma ei omista, siis on tulevikus $leht->content_template jļæ½lle vale.
		# Kompromiss: omista ainult siis kui tegu default sisumalliga - puhas {print_content}.

		#OLD $leht->content_template = &$content_template;

		if(!$is_tpl_param){ # kui pole kaasas "tpl" parameetrit vļæ½i on URL-il "c_tpl"
			$leht->content_template = &$content_template;
		}

		if($use_master_cont_tpl) {
			$content_template->debug->msg("Sisumalliks vļæ½eti saidi default sisumall '".$site->master_cont_tpl[nimi]."' (ID=".$site->master_cont_tpl[ttyyp_id].")");
		}
		if($use_objtype_tpl) {
			$content_template->debug->msg("Sisumalliks vļæ½eti objektitļæ½ļæ½bi sisumall '".$filename[$tpl]."' (ID=".$objtype_tpl_id.")");
		}

		########### Save parameters as content template variables
		foreach($params as $param=>$value){
			if($param == 'tpl') {continue;} # dont override {$tpl} var
			$content_template->smarty->assign($param,$value);
		}
		########### / Save parameters as content template variables

		$content_template->print_text();
		$content_template->debug->print_msg();

	}
	#############
	# print php-template
	else {
		if (file_exists($fail)) {
			include_once $fail;
		} 
		else {
			if($leht->content_template) {
				$leht->content_template->debug->msg("VIGA: ei saa faili lugeda: '".$fail."' (tpl name=".$tpl."). Kasutame default malli.");
			} else {
				$template->debug->msg("VIGA: ei saa faili lugeda: '".$fail."' (tpl name=".$tpl."). Kasutame default malli.");
			}
			include_once "templ1.php";
		}
		
		if (function_exists("print_me")) {
			print_me($template);
		} else {
			$template->debug->msg("VIGA: ei saa $faili lugeda  ".$fail);
		}
	}
}
