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
# function init_calendar
#	name => "cal"
#	start_date => <today>
#	end_date => <today>
#	object_class => "event"
#	send_variables => 0/1
#	$hide_selectboxes => 0/1
#	$hide_weeknumbers => 0/1
#	$hide_month_link => 0/1
#	$hide_today_link => 0/1
#	$parent => default: <current page id>

/*
NB! Kļæ½ik kalendri stiilid on kirjas failis styles/calendar.css, peamised on siin selgitatud.
On mļæ½ttekas ka muuda px/cal_edasi.gif, px/cal_tagasi.gif (nooled), ja px.gif mis on ļæ½hepikseline "spacer".

Siin seletused:
	A.day:link {font-family: font-size: 7pt;text-decoration: none; color: black;}
	A.day:visited {font-family: font-size: 7pt;text-decoration: none; color: black;}
	A.day:active {font-family: font-size: 7pt;text-decoration: none; color: black;}
	A.day:hover {font-family: font-size: 7pt;text-decoration: none; color: #555555;}
	##### main link style, day links in caledar

	.cal { font-family:  Verdana, Tahoma, Arial, Helvetica; line-height: 12px; height: 18px; font-size: 12px; width: padding-bottom:8px; padding-right:7px; align: right; color: #ffffff; cursor: hand }
	##### some <td> styles in calendar
	
	.cal_actual { font-family:  Verdana, Tahoma, Arial, Helvetica; line-height: 12px; height: 18px; font-size: 12px; width: padding-bottom:8px; padding-right:7px; align: right; color: #ffff00; cursor: hand; background-color: #0089D2}
	##### actual day lightning in calendar, same as "cal" style, background-color differs

	.cal_nottoday { font-family:  Verdana, Tahoma, Arial, Helvetica; line-height: 12px; height: 18px; font-size: 12px; width: padding-bottom:8px; padding-right:7px; align: right; color: #ffffff; cursor: hand; background-color: #DDDDDD}
	##### today day lightning, if not today is selected, same as "cal" style, background-color differs
	
	.caltext { color: black; font-size: 12px;}
	##### some <td> styles in calendar
	
	.cal_maintable{border-style:solid; border-color:white; border-width:1px;}
	##### main <table> style in calendar

	.cal_selectbox_tr{background-color: #0089D2;}
	##### selectbox <tr> style

	.cal_weekday_tr{background-color: white;}
	##### weekday <tr> style, the one that goes above all days

	.cal_spacer_tr{background-color: black;}
	##### 1px spacer <tr> style

	.drd {background-color: #0089D2; color: #FFFFFF;}
	##### input style in calendar (selectboxes itself)

*/
function smarty_function_init_calendar ($params,&$smarty) {
	global $site, $leht, $template;

	$content_template = &$leht->content_template;



	if ($site->fdat['year']<100 && $site->fdat['year']){
		$site->fdat['year'] += 2000;
	}


	##############
	# default values

	extract($params);
    if(!isset($name)) { $name="cal"; }
    if(!isset($object_class)) { $object_class="artikkel"; }
	if(!isset($hide_selectboxes)) { $hide_selectboxes=0; }
	if(!isset($hide_weeknumbers)) { $hide_weeknumbers=0; }
	if(!isset($hide_month_link)) { $hide_month_link=0; }
	if(!isset($hide_today_link)) { $hide_today_link=0; }
	if(!isset($parent)) { $url_id=$leht->id; } else { $url_id=$parent; }

	# Generate hidden fields & url-parameters from $fdat :
	if ($send_variables==1){
		$skip_arr = Array('month','year','week','day','start_date','end_date','id');

		if (is_array($site->fdat)){
			foreach($site->fdat as $key => $val){
				if (!in_array($key,$skip_arr) && $val){
					if (is_array($val)){
						$val2 = array_unique($val);
						foreach ($val2 as $tmpval){
							$hid_var .= "<input type=hidden name=\"".$key."[]\" value=\"".$tmpval."\">\n";
							$link_var .= "&".$key."[]=".$tmpval;
						}
					} else { 
						$hid_var .= "<input type=hidden name=\"".$key."\" value=\"".$val."\">\n";
						$link_var .= "&".$key."=".$val;
					}
				}
			}
		}
	}

	$img_path = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF[hostname].$site->CONF[wwwroot].$site->CONF[img_path];

	#################
	# start_date, end_date

	# if parameter is given to function then change month and year values to correct ones
	if(isset($start_date)) { $isset_start_date = 1; }
	if(isset($site->fdat[start_date])) { $isset_url = 1; }
	# if start date or end date is not given by parameter in URL, take it from function parameter
	$start_date = ($site->fdat[start_date] ? $site->fdat[start_date] : $start_date);
	$end_date = ($site->fdat[end_date] ? $site->fdat[end_date] : $end_date);

	# take dd.mm.yyyy and split it
	list($sday,$smonth,$syear) = split ('\.',$start_date);
	$sday = intval($sday); $smonth = intval($smonth); $syear = intval($syear);
	if ($syear<100 && $syear){
		$syear += 2000;
	}

	list($eday,$emonth,$eyear) = split ('\.',$end_date);
	$eday = intval($eday); $emonth = intval($emonth); $eyear = intval($eyear);
	if ($eyear<100 && $eyear){
		$eyear += 2000;
	}


	##################
	# find values in such priority order:
	# 1.parameters from URL
	# 2.parameters from function
	# 3.today values

	$month = ($site->fdat['month'] ? $site->fdat['month'] : ($isset_start_date || $isset_url? $smonth : date("n")));
	$year = ($site->fdat['year'] ? $site->fdat['year'] : ($isset_start_date || $isset_url ? $syear : date("Y")));
	$day = $site->fdat['day'];
	$y = substr ($year,2);
	$selected_weeknumber = $site->fdat['week'];

	##################
	# today values

	$cur_month = date("n");
	$cur_year = date("Y");
	$cur_day = date("j");

	##################
	# find object counts foreach this month day

	$alamlistSQL = new AlamlistSQL(array(
		parent => $url_id,
		klass	=> $object_class,
		asukoht	=> 0,
	));

	$first_day = mktime(0, 0, 0, $month, 1, $year);
	$last_day = mktime(0, 0, 0, $month+1, 0, $year);

	if($object_class == 'artikkel'){
		$alamlistSQL->add_select("DAYOFMONTH(obj_artikkel.starttime) AS start_day");
		$alamlistSQL->add_select("DAYOFMONTH(obj_artikkel.endtime) AS end_day");
		$alamlistSQL->add_from("LEFT JOIN obj_artikkel on objekt.objekt_id=obj_artikkel.objekt_id");
		$alamlistSQL->add_where("(MONTH(obj_artikkel.starttime) = '".$month."' OR MONTH(obj_artikkel.endtime) = '".$month."') AND (YEAR(obj_artikkel.starttime) = '".$year."' OR YEAR(obj_artikkel.endtime) = '".$year."') ");
	}
	$alamlist = new Alamlist(array(
		alamlistSQL => $alamlistSQL,
	));
	$alamlist->debug->print_msg();

	$obj_exists = array();
	# loop this month events
	while ($obj = $alamlist->next()) {
		$i = 0;
		# if events start or end time matches with day
		# turn 'exists'-flag on for this day
		if($object_class == 'artikkel'){
			for($i=$obj->all[start_day]; $i<=$obj->all[end_day]; $i++) {
				$obj_exists[$i] = 1;
			}
		}
	}

	#################
	# javascript for changing month
	$html = '
	<SCRIPT LANGUAGE="JavaScript"><!--
	function prev() {
		if(document.cal.month.options.selectedIndex == 0) {
			document.cal.month.options.selectedIndex = 11;
			document.cal.year.options.selectedIndex = document.cal.year.options.selectedIndex-1;
		} else {
			document.cal.month.options.selectedIndex = document.cal.month.options.selectedIndex-1;
		}
		document.cal.submit();
		return false;
	}
	function next() {
		if(document.cal.month.options.selectedIndex == 11) {
			document.cal.month.options.selectedIndex = 0;
			document.cal.year.options.selectedIndex = document.cal.year.options.selectedIndex+1;
		} else {
			document.cal.month.options.selectedIndex = document.cal.month.options.selectedIndex+1;
		}
		document.cal.submit();
		return false;
	}
	//--></SCRIPT>
	';
	#################
	# form

	$html .= '
	<form name="cal" method="get" action="'.$site->self.'">
	'.$hid_var.'
	<input type=hidden name=id value="'.$url_id.'">
		  <table border="0" cellspacing="0" cellpadding="5" class="cal_maintable" width="170">';
			
	if($hide_selectboxes!=1){
				$html .= '<tr class="cal_selectbox_tr" align="center"> 
				<td style="padding-top: 5px; padding-bottom: 5px; padding-right: 3px; padding-left: 1px;"> 
				  <table border="0" cellspacing="0" cellpadding="0" width="100%">
					<tr> 
					  <td align="right" width="20"><a href="javascript:prev()"><img src="'.$img_path.'/cal_tagasi.gif" border="0"></a></td>
					  <td align="center" class="caltext" valign="top"> 
						<select class="drd" style="width: 90px" name="month" onChange="submit()">
	';
				##### month select-box ######
				for ($i=1; $i<=12; $i++) {
					$html .= '<option value="'.$i.'"';
					if ($i == $month) { $html .= "selected"; }
					$html .= '>'.$site->sys_sona(array(sona => "month".$i, tyyp=>"kalender")).'</option>';
				}
	$html .= '
						</select>
					  </td>
					  <td width="20"><a href="javascript:next()"><img src="'.$img_path.'/cal_edasi.gif" border="0"></a></td>
					  <td class="caltext" valign="top" align="right"> 
						<select name="year" class="drd" onChange="submit()">
	';
				##### year select-box ######
				for ($i=2000; $i<=$cur_year+3; $i++) { 
					$html .= '<option value="'.$i.'"';
					if ($i == $year) { $html .= "selected"; }
					$html .= '>'.$i.'</option>';
				}
	$html .= '
				</select>
			  </td>
			</tr>

		  </table>
		</td>
	  </tr>';
	} # if hide_selectboxes!=1

$html .= '<tr align="center" class="cal_weekday_tr"> 
				<td class="caltext"> 
				  <table width="100%" border="0" cellpadding="0" cellspacing="2">
					<tr nowrap>'; 
					if ($hide_weeknumbers!=1){$html .='
					  <td align="right" class="caltext"><img src="'.$img_path.'/px.gif" width="20" height="1"> 
					  </td>';
					}
					$html .='
					  <td align="right" class="caltext" style="padding-bottom:3px; padding-right:6px;" width="13%"><b>'.$site->sys_sona(array(sona => "weekday1", tyyp=>"kalender")).'</b></td>
					  <td align="right" class="caltext" style="padding-bottom:3px; padding-right:6px;" width="13%"><b>'.$site->sys_sona(array(sona => "weekday2", tyyp=>"kalender")).'</b></td>
					  <td align="right" class="caltext" style="padding-bottom:3px; padding-right:6px;" width="13%"><b>'.$site->sys_sona(array(sona => "weekday3", tyyp=>"kalender")).'</b></td>
					  <td align="right" class="caltext" style="padding-bottom:3px; padding-right:6px;" width="13%"><b>'.$site->sys_sona(array(sona => "weekday4", tyyp=>"kalender")).'</b></td>
					  <td align="right" class="caltext" style="padding-bottom:3px; padding-right:6px;" width="13%"><b>'.$site->sys_sona(array(sona => "weekday5", tyyp=>"kalender")).'</b></td>
					  <td align="right" class="caltext" style="padding-bottom:3px; padding-right:6px;" width="13%"><b>'.$site->sys_sona(array(sona => "weekday6", tyyp=>"kalender")).'</b></td>
					  <td align="right" class="caltext" style="padding-bottom:3px; padding-right:6px;" width="14%"><b>'.$site->sys_sona(array(sona => "weekday7", tyyp=>"kalender")).'</b></td>
					</tr>
					<tr class="cal_spacer_tr" nowrap> 
					  <td colspan="';
					if ($hide_weeknumbers!=1){$html .='8';
					} else {$html .='7';}
					$html .='"><img src="'.$img_path.'/px.gif" width="1" height="1"></td>
					</tr>
	';
		$weekday = date ("w", mktime(0,0,0,$month,1,$year));
		$weeknumber = date ("W", mktime(0,0,0,$month,1,$year));

		if ($weekday == 0) {
			$weekday = $weekday+7;	
		}
		$daysnum = date ("t", mktime(0,0,0,$month,1,$year));

		##################
		# 1st row start & weeknumber


		$weeklink = $site->self.
			"?id=".$url_id.
			"&start_date=".date("d.m.Y", get_monday($weeknumber, $year)).
			"&end_date=".date("d.m.Y", get_sunday($weeknumber, $year)).
			"&week=".$weeknumber.
			"&month=".$month.
			"&year=".$year.$link_var;
		$html .= '
		<tr align="center" nowrap>
		';	  	
		if ($hide_weeknumbers!=1){$html .='<td align="right" class="cal"><a href="'.$weeklink.'" class="week">'.$weeknumber.'.</a></td>';}

		# empty spaces
		for ($j=1; $j<$weekday; $j++) {
			$html .= '<td align="center" class="cal"></td>';
		}

		# paevade arv selles kuus:
		$days_qty = date("t", mktime(0,0,0,$month,1,$year));

		################
		# tsļæ½kkel ļæ½le pļæ½evade
		for ($i=1; $i<=$daysnum; $i++) {
			$is_selected = 0;
			$is_between = 0;

			# link for 1 day
			$link = $site->self."?id=".$url_id."&day=".$i."&month=".$month."&year=".$year;
			$link .= "&start_date=".date("d.m.Y", mktime(0,0,0,$month,$i,$year));
			$link .= "&end_date=".date("d.m.Y", mktime(0,0,0,$month,$i,$year));
			$link .= $link_var;

			# current week number
			$weeknumber = date ("W", mktime(0,0,0,$month,$i,$year));

			# set flag for convienence:
			# if current date is between startdate and endate
			if(	(mktime(0,0,0,$month,$i,$year) >= mktime(0,0,0,$smonth,$sday,$syear)) 
				&& (mktime(0,0,0,$month,$i,$year) <= mktime(0,0,0,$emonth,$eday,$eyear)) ) {
				$is_between = 1;
			}

			# RED: day is between start_date and end_date - make it red,
			# but if start/enddate is given as f-n parameter and some other selection is made, dont do so
			if ( $is_between && !$day && !$selected_weeknumber ) {
				$html .= '<td align="right" class="cal_actual"><a href="'.$link.'" class="today'.($obj_exists[$i] ? "_bd" : "").'">'.$i.'</a></td>';
				$is_selected = 1;
			} 
			# RED: 1 selected day
			else if ($i == $day) {
				$html .= '<td align="right" class="cal_actual"><a href="'.$link.'" class="today'.($obj_exists[$i] ? "_bd" : "").'">'.$i.'</a></td>';
				$is_selected = 1;
			} 
			# RED: if no day and no week is selected, 
			# and no default start date is given as function parameter
			# then show today selected
			else if (!$isset_start_date && !$day && !$selected_weeknumber && !$site->fdat[start_date] && !$site->fdat[end_date] && ($year == $cur_year && $month == $cur_month && $i == $cur_day)) {
				$html .= '<td align="right" class="cal_actual"><a href="'.$link.'" class="today'.($obj_exists[$i] ? "_bd" : "").'">'.$i.'</a></td>';
				$is_selected = 1;
			} 
			# RED: selected week
			else if($weeknumber == $selected_weeknumber) {
				$html .= '<td align="right" class="cal_actual"><a href="'.$link.'" class="today'.($obj_exists[$i] ? "_bd" : "").'">'.$i.'</a></td>';
				$is_selected = 1;
			}
			# GRAY: if current month, then show today as bold&gray
			else if ($i == $cur_day && $year == $cur_year && $month == $cur_month) {
				$html .= '<td align="right" class="cal_nottoday"><a href="'.$link.'" class="day'.($obj_exists[$i] ? "_bd" : "").'">'.$i.'</a></td>';
			} 
			# GRAY: usual day, if any event (or another object) exists for this day, then bold it
			else {
				$html .= '<td align="right" class="cal"><a href="'.$link.'" class="day'.($obj_exists[$i] ? "_bd" : "").'">'.$i.'</a></td>';
			}

			#############
			# find selected start & end date
			if ($is_selected) {
				if (!$selected_start_date){
					$selected_start_date =  (strlen($i)==1?"0":"").$i.".".(strlen($month)==1?"0":"").$month.".".$year;
#bugine					$selected_start_date =  $start_date;
					$selected_date = mktime(0,0,0,$month,$i,$year);
					$selection=1;
				}
				# if selection goes on
				if ($selection){
					$selected_end_date = (strlen($i)==1?"0":"").$i.".".(strlen($month)==1?"0":"").$month.".".$year;
#					$selected_end_date = $end_date;
				}

			}
			# if not selected, but AFTER some selection, tehen interrupt selection flag
			if (!$is_selected && isset($selected_date) && mktime(0,0,0,$month,$i,$year) >= $selected_date){
				$selection=0;
			}

			#############
			# end of row, new start & week number

			if (($weekday++)%7 == 0 && $i<$days_qty) {
				$weeknumber++;

				$weeklink = $site->self.
					"?id=".$url_id.
					"&start_date=".date("d.m.Y", get_monday($weeknumber+1, $year)).
					"&end_date=".date("d.m.Y", get_sunday($weeknumber+1, $year)).
					"&week=".$weeknumber.
					"&month=".$month.
					"&year=".$year.$link_var;

				$html .= '</tr>
				<tr align="center" nowrap>'; 
				if ($hide_weeknumbers!=1){$html .='<td align="right" class="cal"><a href="'.$weeklink.'" class="week">'.$weeknumber.'.</a></td>';}
			} # if

			# this month end date
			$month_end = $i.".".($month<10 ? "0".$month : $month).".".$year;
		} 
		# empty spaces
		
		$last_weekday = (date('w', mktime(0,0,0, $month, --$i, $year)));
		
		if($last_weekday)
		{
			# empty spaces
			for ($j = $last_weekday; $j < 7; $j++) {
				$html .= '<td align="center" class="cal"></td>';
			}
			//$html .= '</tr>'; // on seda vaja
		}
		
		# for ļæ½le pļæ½evade
		####################

		# this month start date
		$month_start = "01.".($month<10 ? "0".$month : $month).".".$year;

		####################
		# links 'whole month', 'today'
		$html .= '</tr>';

		if (!($hide_month_link==1 AND $hide_today_link==1)){# in case we don't have both hidden
		$html .= '<tr class="cal_spacer_tr" nowrap> 
			  <td colspan="'; 
		if ($hide_weeknumbers!=1){$html .='8';
		} else {$html .='7';}
		$html .='"><img src="'.$img_path.'/px.gif" width="1" height="1"></td>
			</tr>';
		$html .= '
			<tr><td colspan="'; 
		if ($hide_weeknumbers!=1){$html .='8';
		} else {$html .='7';}
		$html .='">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr nowrap> 
			  <td height="18" class="cal">';
				if ($hide_month_link!=1){$html .='<a class="day" href="'.$site->self.'?id='.$url_id.'&month='.$month.'&year='.$year.'&start_date='.$month_start.'&end_date='.$month_end.$link_var.'">'.$site->sys_sona(array(sona => "whole month", tyyp=>"kalender")).'</a>';
				} else {$html .='&nbsp;';}
			$html .= '
				</td>
			  <td align="right" class="cal" height="18">';
				if ($hide_today_link!=1){$html .= '<a class="day" href="'.$site->self.'?id='.$url_id.'&day='.$cur_day.'&month='.$cur_month.'&year='.$cur_year.$link_var.'">'.$site->sys_sona(array(sona => "today", tyyp=>"kalender")).'</a>';
				} else {$html .='&nbsp;';}
			$html .= '
			  </td>
			</tr>
		  </table>
			</td></tr>';
		}
		  $html .= '</table>
		</td>
	  </tr>
	</table>
	</form>
	';

	##############
	# assign to template variables

	$smarty->assign(array(
			$name.'_html' => $html,
			$name.'_start_date' => $selected_start_date,
			$name.'_end_date' => $selected_end_date
		));

} # function init_calendar
