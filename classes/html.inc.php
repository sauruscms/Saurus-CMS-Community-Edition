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





class vParents extends Parents {
# ---------------------------------------
# Parents, aga lisaks on 
# navi riba HTML'i koostamine
# ise kontrollib kas see riba peab olema n�htav
# kui mitte, siis tagastab t�hi HTML objekt
# ---------------------------------------
	var $html;
	var $block_print_text;
	var $on_navis_nahtav;
	var $on_custom;

	function vParents () {
		$args = func_get_arg(0);

		$this->Parents($args);
		$this->on_custom = $args[on_custom];

		if (!$this->site->dbstyle("hide_kusmaolen_riba","layout")) {
			$this->block_print_text = 0;
			$this->debug->msg("block print text = 0");
		} else {
			$this->block_print_text = 1;
			$this->debug->msg("block print text = 1");
		}

		if ($this->on_custom) {
			$this->block_print_text = 1;
		}



			# on vaja navigatsiooni n�idata

			$this->html = new HTML("");
			$this->debug->msg("Navi-riba on lubatud");
					
			# millised tyybid on n�htav?				
			$this->on_navis_nahtav = array();

			# -----------------------
			# HTML ON SIIN 
			# -----------------------
			if (!$this->site->dbstyle("hide_kusmaolen_riba","layout")) {				

			$this->html->add('
		  <table border="0" cellspacing="0" cellpadding="0" width="'.($args[on_custom] ? "100%":$this->site->dbstyle("hrz_menu_laius","layout")).'">
			<tr>
			  <td class="breadcrumb" height="'.$this->site->dbstyle("kusmaolen_riba_korgus","layout").'" nowrap align="'.$this->site->dbstyle("kusmaolen_riba_align","layout").'">');
			}
			  			  
#			$count = $this->size > 5 ? 5 : $this->size;
			$count = $this->size;
			$this->debug->msg("Navi-ribas on ".($count-1)." objekti");
			if ($count>=2) { 
				$this->html->add($this->site->sys_sona(array(sona => "navbar", tyyp=>"kujundus")).'&nbsp;');
			}
			$key = 0;
			for ($i = $count-2; $i>=0; $i--) {
				$obj = $this->get($i);
				if (in_array($obj->all[tyyp_id],$this->on_navis_nahtav) && (!$obj->all['is_hided_in_menu'] || $this->site->in_editor)) {
					if (1 || $key) { # make all links (by merle 04.03.2005)
					$this->html->add('&nbsp;'.$this->site->dbstyle("kusmaolen_riba_eraldaja","layout").'&nbsp;'); }
					$this->html->add('<a href="'.($this->site->self).'?id='.($obj->objekt_id).'" class="breadcrumb">'.($obj->pealkiri).'</a>');
#OLD					$this->html->add($i>0 ? '<a href="'.($this->site->self).'?id='.($obj->objekt_id).'" class="breadcrumb">'.($obj->pealkiri).'</a>' : $obj->pealkiri);
					$key++;
				}
			}

			if (!$this->site->dbstyle("hide_kusmaolen_riba","layout")) {
			$this->html->add('
				</td>
			</tr>
		  </table>');
			}

		$this->debug->msg($this->html->debug->get_msgs());
	} # constructor

	function print_text () {
		$this->debug->msg("PRINT: block print text = ".$this->block_print_text);

		if (!$this->block_print_text) {
			print $this->html->print_text();
		}
	}
}

/**
 * Used by {print_box} API function
 *
 */
function print_kastid () {
# -------------------------------
# kastid antud asukohaga
# print_kastid (array(
#	leht => $leht,
#	template => $template, (kas leht v�i template peab olema)
#	asukoht => 8,
#	on_td	=> 0/1,
# ))
# -------------------------------
		$args = func_get_arg(0);
		$custom_objs = Array();

		if ($args[template]) {
			$leht = &$args[template]->leht;
		} else {
			$leht = &$args[leht];
		}
		
		if ($args[parent_id]) {
			$parent_id = $args[parent_id];
		} elseif ($args[asukoht]==8) {
			$parent_id = $leht->parents->get(0);
		} else {
			$parent_id = $leht->parents->get(-2);
		}

		if ($args[is_custom]) {
			$is_custom = $args[is_custom];
		} else {
			$is_custom = 0;
		}


		$leht->debug->msg("Kastid asukoht = $args[asukoht], parent_id = ".(is_object($parent_id) ? $parent_id->objekt_id : $parent_id));

		$aken_alamlist = new Alamlist(array(
			parent => is_object($parent_id) ? $parent_id->objekt_id : $parent_id,
			asukoht	=> $args[asukoht],
		));

		if ($aken_alamlist->size || $leht->site->admin) {
			if ($args[on_td]) { ?><td width="<?=$leht->site->dbstyle("menyy_laius","layout")?>" align="center" class=box><? }

			# added 12.12.2003 by Dima Bug #744
			$sql = "SELECT COUNT(*) FROM obj_gallup WHERE on_avatud = '0'";
			$sth = new SQL($sql);
			$leht->site->debug->msg($sth->debug->get_msgs());
			$archive_link_on = $sth->fetchsingle() ? 1 : 0;
			# //

			include_once "kast.php";
			while ($kast = $aken_alamlist->next()) {
				$custom_objs[] = print_kast($kast,$is_custom,$archive_link_on);
				$was_printed = 1;
			}
			$is_not_empty = print_kast($aken_alamlist,$is_custom);
			if(!empty($is_not_empty[buttons])) {
				$custom_objs[] = $is_not_empty;
			}

		}
		if ($args[on_td]) {?></td><?}
		if($is_custom) {
			return $custom_objs;
		} else {
			return $was_printed;
		}
} # f-n print_kastid()

