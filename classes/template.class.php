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



class Template extends HTML {
# ---------------------------------------
# koostab malli kas lehe v�i objekti j�rgi
#
# constructor new Template ($leht[, $obj])
# ---------------------------------------
	
	var $ttyyp_id;
	var $all;
	var $fail;
	var $leht;

	function Template($leht, $obj='', $ttyyp_id='') {

		$this->leht = &$leht;
		
		$ttyyp_id = (int)$ttyyp_id;
		
		$this->HTML();
		global $class_path;

			# erijuht: kui leiame malli otse malli id j�rgi:
			if($ttyyp_id) {
				$this->ttyyp_id = $ttyyp_id;
				$this->debug->msg("Leiame malli ID j�rgi (".$ttyyp_id.")");		
			}

			# erijuht: kui leiame malli etteantud objekti j�rgi:
			else if($obj) {
				$this->ttyyp_id = $obj->all[ttyyp_id];
				$this->debug->msg("Leiame malli objekti j�rgi");		

			}
			# erijuht: leiame selle URL-il oleva "tpl" v��rtuse j�rgi
			else if ($leht->site->fdat['tpl']) { 
				$this->debug->msg("Leiame malli URL-il oleva tpl-i v��rtuse  j�rgi");		
				$this->ttyyp_id = (int)$leht->site->fdat['tpl'];
			} 
			# tavajuht: leiame selle lehe j�rgi: tehakse index.php-s esimese asjana
			else if ($leht->objekt->all[ttyyp_id]) { 
				$this->ttyyp_id = $leht->objekt->all[ttyyp_id];
			} 
			else
			{
				$this->is_default = 1;
				$this->debug->msg("Mall pole m��ratud, kasutame default-malli");		
			}
			
			##############
			# kui eritemplate JA pole malli tegemine ID j�rgi,
			# siis on p�ring tabelist juba leht-classis tehtud
			if ($leht->eritemplate && !$obj && !$ttyyp_id) {
				$this->fail = $leht->eritemplate;
				$this->ttyyp_id = $leht->template[ttyyp_id];
				$this->all = &$leht->template;
				$this->debug->msg("Eritemplate");		
			}

			##############
			# tavajuht: teha p�ring tabelist malli ID j�rgi
			else {
				$sql = $this->leht->site->db->prepare("select * from templ_tyyp where ttyyp_id = ?", $this->ttyyp_id);
				$sth = new SQL($sql);
				$this->debug->msg($sth->debug->get_msgs());
				$this->all = $sth->fetch();
				$this->fail = $this->all[templ_fail];
			}
			$this->on_page_templ = $this->all[on_page_templ];

			$this->debug->msg("Template tyyp ID = ".$this->ttyyp_id.", type on ".($this->on_page_templ ? "PAGE" : "CONTENT"));

			# otsustada, kas tegemist on fixeeritud .php-malliga
			# v�i d�naamilise html-malliga

			# kui d�n. mall, siis luua uus smarty mall
			if($this->ttyyp_id >= 1000) {
				# teha require ainult siis kui tegemist d�naamilise malliga, muidu mitte
				define(SMARTY_DIR,$class_path.'smarty/lib/');
				require_once(SMARTY_DIR.'Smarty.class.php');
				require_once($class_path.'smarty.inc.php');

				# new instance of smarty template
				$this->smarty = new Smarty;
				# smarty kataloogide teed
				$this->smarty->template_dir = $class_path.'smarty/templates/';
				$this->smarty->compile_dir = $class_path.'smarty/templates_c/';
				$this->smarty->config_dir = $class_path.'smarty/configs/';
				$this->smarty->cache_dir = $class_path.'smarty/cache/';
				
				// add SAPI plugins
				$this->smarty->plugins_dir[] = $class_path.'sapi/';

				/* extensions feature: add smarty plugins path */
				include_once($class_path.'extension.class.php');
				foreach(get_extensions() as $extension) /* TODO: all or only active? */
				{
					$EXTENSION = load_extension_config($extension);

					if(is_string($EXTENSION['smarty_plugins']))
					{
						$this->smarty->plugins_dir[] = $class_path.'smarty/lib/'.$EXTENSION['smarty_plugins'];
					}
				}

				# asuvad failis 'smarty.inc.php';
				$this->smarty->register_compiler_function ("procedure", "sm_function",false);
				$this->smarty->register_compiler_function ("/procedure", "sm_function_close",false);
				
			}
			# if smarty mall v�i fix.php-mall

		$this->debug->msg("Template on loodud: '".$this->all[nimi]."' (".$this->fail.", ".($this->smarty ? "d�naamiline html" : "fiks. php").")");
	
	}
		
	function print_text() {

		# kui tavaline fikseeritud .php-mall
		if(empty($this->smarty)) {
			if (file_exists($this->fail)) {
				$this->debug->msg("Kasutame ".$this->fail." php-malli");
				include_once $this->fail;
			} 
			elseif (file_exists("../".$this->fail)) {
				$this->fail = "../".$this->fail; # being in editor
				$this->debug->msg("Kasutame ".$this->fail." php-malli");
				include_once $this->fail;
			} 
			else {
				$this->debug->msg("Kasutame t�hja malli, kuna fail ".$this->fail." ei eksisteeri");
				$this->editor_debug->msg("ERROR! File '".$this->fail."' not found, default template is used instead");
				include_once "templ1.php";
			}

			if (function_exists("print_me")) {
				print_me($this);
			} else {
#				$this->debug->msg("VIGA: ei saa faili lugeda  ".$this->fail);
			}
		}
		# kui d�naamiline tag-langiga html-mall
		else {
			if (file_exists($this->smarty->template_dir.$this->fail)) {
				$this->debug->msg("Kasutame ".$this->fail." html-malli");

				# omistatakse �ra globaalsed muutujad
				if(!function_exists('smarty_function_init_page')){
					require_once $this->smarty->_get_plugin_filepath('function', 'init_page');
				}
				smarty_function_init_page($this->smarty, array('on_page_templ' => $this->on_page_templ));
				#init_page($this->smarty, array('on_page_templ' => $this->on_page_templ));
				# n�idatakse malli
				$this->smarty->display($this->fail);
			} else {
				$this->debug->msg("VIGA: ei eksisteeri faili ".$this->smarty->template_dir.$this->fail);
				$this->editor_debug->msg("ERROR! File '".$this->smarty->template_dir.$this->fail."' not found");
			}
		} # if fix mall or dynamic mall


	}

}