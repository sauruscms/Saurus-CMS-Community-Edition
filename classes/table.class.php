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


class Table extends HTML {
# ---------------------------------------
# constructor new Table(array(
#	from => "from ... left join ...",
#	where => "where ... ",
#	group => " group by ... ",
#	tulemuste_arv => "20",
#	leht => 2,
#  order => "user_desc", # column->id + "_desc"
# ))
# ---------------------------------------

	var $leht;
	var $tulemuste_arv;
	var $lehed;

	var $sth;
	var $columns;
	var $hidden_columns;

	var $leht_count;
	var $count;
	var $rows;
	
	var $from;
	var $from_counter;
	var $where;
	var $group;
	var $group_counter;
	var $order;
	var $order_col;
	var $order_desc;
	
	var $td_template;
	var $tr_template;
	var $tr_on_exec;
	var $leht_aktiivne_html;
	var $leht_passiivne_html;
	var $leht_separator_html;

	var $table_tag;
	
	function Table () {
	# init
		$args = func_get_arg(0);
		$this->HTML();	
		
		$this->columns = array();
		$this->hidden_columns = array();
		$this->tulemuste_arv = $args[tulemuste_arv] ? $args[tulemuste_arv] : 20;
		$this->leht = $args[leht] ? $args[leht] : 1;

		$this->from = $args[from];
		$this->where = $args[where];
		$this->group = $args[group];

		$this->from_counter = $args[from_counter];
#		$this->where = $args[where];
		$this->group_counter = $args[group_counter];

		$this->order = $args[order];
		
		$this->tr_template = $args[tr_template] ? $args[tr_template] : "<tr>[td]</tr>";
		# kui tr_on_exec=1, siis teeb eval-i
		$this->tr_on_exec = $args[tr_on_exec];
		$this->td_template = $args[td_template] ? $args[td_template] : "<td>[value]</td>";
		$this->th_template = $args[th_template] ? $args[th_template] : "<th>[nool] [pealkiri]</th>";
		$this->thead_template = $args[thead_template] ? $args[thead_template] : "<thead>[th]</thead>";

		$this->table_tag = $args[table_tag];

		$this->leht_aktiivne_html = $args[leht_aktiivne_html];
		$this->leht_passiivne_html = $args[leht_passiivne_html];
		$this->leht_separator_html = $args[leht_separator_html];

		$this->debug->msg("tabel loodud");
		
	}
	
	function AddColumn ($column) { /* PUBLIC */
		# lisab veerg tabelisse
		if (!strcasecmp(get_class($column),"Column")) {
			$this->debug->msg("Veerg ".$column->id." lisatud");
			if (preg_match("/^".addslashes($column->id)."(_desc)?/i",$this->order,$matches)) {
				$this->order_col = $column->id;
				$this->order_desc = $matches[1];
				$this->debug->msg("Set order ".$column->id.$matches[1]);
				$column->set_order($matches[1]);
			}
			if ($column->pealkiri) {
				array_push($this->columns, $column);
				$this->debug->msg("column added \"".$column->pealkiri."\"");
				$this->debug->msg("columns count ".sizeof($this->columns));
			} else {
				array_push($this->hidden_columns, $column);
				$this->debug->msg("column added");
				$this->debug->msg("columns count ".sizeof($this->hidden_columns));
			}
			
		} else {
			$this->debug->msg("Vale objekti tüüp: ".get_class($column));
		}
	}

	function get_value ($col, $val) {
		return $col->all[$val];	
	}

	function get_text() { /* PRIVATE */
		# HTML-i valmistamine

		$this->debug->msg("get_text, visible columns: ".sizeof($this->columns));
				
		$result = $this->table_tag ? $this->table_tag : "<table border=0 cellpadding=2 cellspacing=1>";
		$col_num=0;
		foreach($this->columns as $col) {
			$th .= preg_replace('/\[(\w+)\]/e', '$this->get_value($col,$1)', $col->th_template ? $col->th_template : $this->th_template);
			$col_num++;
		}
		$this->debug->msg("columns created $col_num ".sizeof($this->columns));
		$result .= preg_replace('/\[th\]/', $th, $this->thead_template);
		
		while ($record = $this->sth->fetch()) {

			# Bug #1857: Vaikimis dokumendimallis hidden ei kaota ekraanilt rida ära avalikus vaates
			# (kuna dok. mallis ei tehta korralikult "Alamlist" objekti,
			# siis ei läbita ka korralikult permissionite kontrolli => workaround: tee seda ise siin.

			if($record['objekt_id']) { # kui tegu objektide tabeliga
				$obj = new Objekt(array("objekt_id" => $record['objekt_id'])); 
				## if object IS NOT visible => go to next row
				if(!$obj->permission['is_visible']){
					continue;
				}
			} # kui tegu objektide tabeliga

			$td="";
			for ($i=0;$i<$col_num;$i++) {				
				$record[value] = $record[$i] !=='' ? $record[$i] : "&nbsp;";
				$record[value] = str_replace('"', '\"',$record[value]);

				$td_single = preg_replace('/\[(\w+)\]/e', '$record[\\1]', $this->columns[$i]->td_template ? $this->columns[$i]->td_template : $this->td_template);
				if ($this->columns[$i]->on_exec) {
					#$this->debug->msg(htmlspecialchars("td eval: $td_single"));
					$this->debug->msg("td eval: $td_single");
					$td_single =  eval($td_single);
				}
				$td .= $td_single;
				#$this->debug->msg("cell added".htmlspecialchars($td_single));
				$this->debug->msg("cell added ".$td_single);
			}
			$record["td"] = $td;
			$record["bgcolor"] = ($this->sth->i % 2 ? "ffffff" : "efefef");
			
			$tr = preg_replace('/\[(\w+)\]/e', '$record[\\1]', $this->tr_template);
			if ($this->tr_on_exec) {
				#$this->debug->msg(htmlspecialchars("EVAL: $tr"));
				$this->debug->msg("EVAL: $tr");
				$tr =  eval($tr);
			}
			$result .= $tr;
			#$this->debug->msg("row added".htmlspecialchars($tr));
			$this->debug->msg("row added".$tr);
		}
		
		$result .= "</table>";
		$this->debug->msg("text prepared");

		return $result;
	}
	
	function print_text () {
		if (!$this->block_print_text) {
			print $this->get_text();
		}
	}
	
	function Execute() { /* PUBLIC */
	# SQL päring
	
		$sql_count = "";	
		if ($this->order_col) {
			$order = "ORDER BY ".$this->order_col.($this->order_desc ? " DESC" : "");
		}
		
		$fields = array();			
		foreach($this->columns as $col) {
			array_push($fields, $col->source);
		}
		foreach($this->hidden_columns as $col) {
			array_push($fields, $col->source);
		}
		if (sizeof($fields)==0) { array_push($fields,"*"); }
						
		$sql_count = "select count(*) ".(strcmp($this->from_counter,'') ? $this->from_counter : $this->from)." ".$this->where." ".(strcmp($this->group_counter,'') ? $this->group_counter : $this->group);
		$sth = new SQL($sql_count);
		$this->count = $sth->fetchsingle();
		$this->debug->msg($sth->debug->get_msgs());
		$this->debug->msg("Tulemuste arv: ".$this->count);
		
		if ($this->leht>0) {
			$algus = ($this->leht-1)*$this->tulemuste_arv;
			if ($algus<0) {$algus=0;}
			$limit = " limit $algus,".$this->tulemuste_arv;
		} else {
			$limit = "";	
		}
			
		$sql = "select ".join(",",$fields)." ".$this->from." ".$this->where." ".$this->group." ".$order." ".$limit;
		
		$this->sth = new SQL($sql);
		$this->debug->msg($this->sth->debug->get_msgs());
		$this->rows = $this->rows;	
		
		$this->lehed = new Lehed(array(
			count => $this->count,
			leht => $this->leht,
			tulemuste_arv => $this->tulemuste_arv,
			aktiivne_html => $this->leht_aktiivne_html,
			passiivne_html => $this->leht_passiivne_html,
			separator_html => $this->leht_separator_html,
		));
#			aktiivne_html => ;
#			var $passiivne_html;
#			var $separator_html;

	}
}

# / class Table
#################################

class Column extends BaasObjekt {
# constructor new column(array(
#	id => "user",
#	source => kasutaja.user,
#	pealkiri => "Kasutajanimi",
#	order => 1, 
#  order_default => "asc/desc"
# ));
	
	var $id;
	var $source;
	var $pealkiri;
	var $order; # (0-none, 1->asc, 2->desc, -1 -> default)
	var $order_default; 
	var $all;
	var $nool;
	var $on_exec;
			
	var $td_template;
	var $th_template;
	
	function Column() {
	# init
		$args = func_get_arg(0);
		$this->BaasObjekt($args);
		$this->all = array();
		if ($args[nool_invert]) {
			$this->nool = array(0=>"unsorted",2=>"sorted_desc",1=>"sorted");
		} else {
			$this->nool = array(0=>"unsorted",1=>"sorted_desc",2=>"sorted");
		}

		$this->td_template = $args[td_template] ? $args[td_template] : "<td>[value]</td>";
		$this->th_template = $args[th_template] ? $args[th_template] : "";
		$this->on_exec = $args[on_exec];

		if ($args[source] && $args[id]) {
			$this->id = $args[id];
			$this->source = $args[source];
			$this->pealkiri = $args[pealkiri];						
			$this->order_default = ($args[order_default]=="desc" ? 1 : 2);
			$this->order = $args[order] ? $args[order] : 0;
			//if ($this->order<0) { $this->order = $this->order_default; }
			
			if ($args[pealkiri]) {
				$this->all[pealkiri] = $args[pealkiri];			
				$this->apply_order();
			} else {
				$this->td_template="";
			}
			
			/*$this->all[this_order] = $this->id.(($this->order ? $this->order : $this->order_default)==1 ? "_desc" : "");
			$this->all[nool] = "<img src=\"".$this->site->img_path."/".$this->nool[$this->order].".gif\" align=\"texttop\" border=0>";*/
			
		} else {
			$this->debug->msg("Andmed puuduvad: source = $args[source], pealkiri = $args[pealkiri], id = $args[id]");
		}
	}
	
	function set_order($is_desc) { /* PUBLIC */		
	# sorteering selle veeru järgi
		$this->order = $is_desc ? 2 : 1;
		$this->apply_order ();			
	}
	
	function apply_order () { /* PRIVATE */
	# sorteeringu rakendamine
		if ($this->order<0) { $this->order = $this->order_default; }			
		$this->all[this_order] = $this->id.(($this->order ? $this->order : $this->order_default)==1 ? "_desc" : "");
		$this->all[nool] = "<img src=\"".$this->site->img_path."/".$this->nool[$this->order].".gif\" align=\"texttop\" border=0>";	
	}
}




class Lehed extends HTML {

	var $count;
	var $leht;
	var $tulemuste_arv;
	
	var $aktiivne_html;
	var $passiivne_html;
	var $separator_html;
		
	function Lehed () {
		$args = func_get_arg(0);
		
		$this->HTML('');
		
		$this->count = $args[count];
		$this->leht = $args[leht] ? $args[leht] : 1;
		$this->tulemuste_arv = $args[tulemuste_arv] ? $args[tulemuste_arv] : 20;
		
		$this->aktiivne_html = $args[aktiivne_html] ? $args[aktiivne_html] : "<b>[algus] - [lopp]</b>";
		$this->passiivne_html = $args[passiivne_html] ? $args[passiivne_html] : "[algus] - [lopp]";
		$this->separator_html = $args[separator_html] ? $args[separator_html] : " | ";
		
		$pagenum=0;
		$results = $this->tulemuste_arv;
		$page = $this->leht;
		$count = $this->count;
		$data = array();
		
		if ($count >= $results) {
			while (($pagenum++*$results)<$count) {
		  		$data[lopp] = $pagenum*$results < $count ? $pagenum*$results : $count;
				$data[algus] = $pagenum*$results - $results + 1;
				$data[pagenum] = $pagenum;
				if ($pagenum == $page) {
					$this->add(preg_replace("/\[(\w+)\]/e",'$data[\\1]',$this->aktiivne_html));
				} else {
					$this->add(preg_replace("/\[(\w+)\]/e",'$data[\\1]',$this->passiivne_html));
					
					#$this->add("<b><a href=\"javascript:document.vorm.page.value='$pagenum';document.vorm.submit();\">$algus-$lopp</a></b>");
				}
		 	 	$this->add( $pagenum*$results < $count ? $this->separator_html : "" );
			} # / while pagenum
		} # / if count
	}
	
} # class Lehed
