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


##############################
# Prints an box object
# : is called from "classes/html.inc.php"
# : is script for including
##############################

function print_kast($kast,$is_custom=0,$archive_link_on=1) {

#Muutujad mis hoiab custom stringid
$custom_buttons = '';
$custom_title = '';
$custom_contents = '';

if (get_class($kast)=="Objekt" || is_subclass_of($kast,"Objekt")) {
	# ----------------------------
	# Uudiste kogumik
	# ----------------------------
	if ($kast->all[klass] == "kogumik") {
		$kast->load_sisu();

		if(!$is_custom) {
?>
				<table width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" border="0" cellspacing="0" cellpadding="1">
				  <tr> 
					<td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" class="boxhead" height="24">
					&nbsp;&nbsp;<?=$kast->pealkiri() ?><?php $kast->edit_buttons(array(
						tyyp_idlist => "8,2,6,9,13,17",
					)); ?></td>
				  </tr>
				  <tr> 
					<td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" bgcolor="<?=$kast->site->dbstyle("menyy_border","color")? $kast->site->dbstyle("menyy_border","color") :"#CCCCCC" ?>"> 
					  <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="<?=$kast->site->dbstyle("menyy_taust","color")? $kast->site->dbstyle("menyy_taust","color") :"#FAFAFA" ?>">
						<tr> 
						  <td align="right" valign="top"><img src="<?=$kast->site->img_path ?>/px.gif" width="20" height="10"></td>
						  <td><img src="<?=$kast->site->img_path ?>/px.gif" width="<?=$kast->site->dbstyle("menyy_laius","layout")-42?>" height="10"></td>
						  <td><img src="<?=$kast->site->img_path ?>/px.gif" width="20" height="10"></td>
						</tr>
<?php 
		} else {
		//Custom print out
			
			ob_start();
			$kast->edit_buttons(array(
						tyyp_idlist => "8,2,6,9,13,17",
			));
			$custom_buttons .= ob_get_contents();
			ob_end_clean();
			
			$custom_title .= $kast->pealkiri();
			$custom_contents .= "<ul class=\"boxlist\">";
		}//if is_custom
						# rubriigid kus uudised otsida
						$sql = "SELECT objekt.objekt_id FROM objekt LEFT JOIN objekt_objekt ON objekt_objekt.objekt_id=objekt.objekt_id WHERE objekt_objekt.parent_id=".$kast->objekt_id." AND (objekt.kesk = 0 or objekt.kesk = 5 or objekt.kesk = 9) AND objekt.tyyp_id=1";
						####### POOLELI
						if (!$kast->site->in_editor) {$sql .= " AND objekt.on_avaldatud=1";}
						if (!$kast->site->in_editor) {$sql .= " AND !FIND_IN_SET(objekt.objekt_id, '".join(",",$kast->site->noaccess_hash)."')";}

						$sth = new SQL($sql);			
						while ($rid=$sth->fetchsingle()) {
							$news_rubrics .= ",".$rid;
						};
						$kast->debug->msg($sth->debug->get_msgs());
						$kast->debug->msg("Rubriigid: $news_rubrics");

						$sql = "
							SELECT objekt.objekt_id, objekt.pealkiri, objekt.aeg, objekt.on_avaldatud, objekt_objekt.parent_id 
							FROM objekt 
							LEFT JOIN objekt_objekt ON objekt_objekt.objekt_id=objekt.objekt_id 
							WHERE find_in_set(objekt_objekt.parent_id,'$news_rubrics') AND (objekt.kesk=0 OR objekt.kesk=6) AND (objekt.tyyp_id=2 OR objekt.tyyp_id=15)";
						if (!$kast->site->in_editor) {$sql .= " AND objekt.on_avaldatud=1  ";}
						$sql .= " ORDER BY objekt.aeg DESC, objekt_objekt.sorteering DESC limit 0,".($kast->all[art_arv] ? $kast->all[art_arv] : 5);

						$kast->debug->msg($sth->debug->get_msgs());

						$sth = new SQL($sql);
						$kast->debug->msg("Leitud ".$sth->rows." alamobjekte");
						$esimene = 1;
						while ($ary = $sth->fetch()) {
							$kast->debug->msg("Objekt leitud: $ary[objekt_id]. ".$ary[pealkiri]);
							$obj = new Objekt(array(
								ary => $ary
							));
							if(!$is_custom) {
							if (!$esimene) {
								# eraldaja
?>
								<tr valign="top"> 
								  <td align="right" valign="top"><img src="<?=$kast->site->img_path ?>/px.gif" width="20" height="10"></td>
								  <td background="<?=$kast->site->img_path ?>/stripe1.gif"><img src="<?=$kast->site->img_path ?>/px.gif" width="1" height="10"></td>
								  <td><img src="<?=$kast->site->img_path ?>/px.gif" width="20" height="10"></td>
								</tr>
<?php 
							}
							if ($obj->site->in_editor) {
?>								
								<!--tr valign="top"> 
									<td colspan="3" align=left>&nbsp; &nbsp;
									
									</td>
								</tr-->
<?php 
							} # if in_editor
?>
								<tr valign="top"> 
								  <td align="right" valign="top"><img src="<?=$kast->site->img_path ?>/nupp1.gif" width="10" height="10" align="texttop"></td>
								  <td><a href="<?=$kast->site->self?>?id=<?=$obj->objekt_id?>" class="navi2_on"><?=$obj->pealkiri()?></a><?=$kast->all[on_kp_nahtav] ? "<br><font class=txt><font class=date>".$obj->aeg()."</font></font>" : ""?><?php $obj->edit_buttons(array(
										tyyp_idlist	=> 3,
										only_edit	=> 1,
									)); ?></td>
								  <td><img src="<?=$kast->site->img_path ?>/px.gif" width="20" height="10"></td>
								</tr>
<?php 						
							$esimene = 0;
						} else {
						//Custom print out
							if ($obj->site->in_editor) {
									ob_start();
									$obj->edit_buttons(array(
										tyyp_idlist	=> 3,
										only_edit	=> 1,
									));
									$custom_contents .= ob_get_contents();
									ob_end_clean();
							}
							if($kast->all[on_kp_nahtav]) {
								$cu_date = '&nbsp;&nbsp;<font class=date>'.$obj->aeg().' </font>';
							} else {
								$cu_date = '';
							}
							$custom_contents .= '<li class="list"><a href="'.$kast->site->self.'?id='.$obj->objekt_id.'" class="navi2_on">'.$obj->pealkiri().$cu_date.'</a>'.'</li>'.(($obj->site->in_editor)?"<br clear=all>":"");

						}//if is_custom
						} # while 

			if(!$is_custom) {
?>
						<tr> 
						  <td colspan="3"><img src="<?=$kast->site->img_path ?>/px.gif" width="1" height="10"></td>
						</tr>
					  </table>
					</td>
				  </tr>
				</table>
							  <br>
<?php 		
			} else {
				$custom_contents .= "</ul>";
			}//if is_custom
		
	} else if ($kast->all[klass] == "rubriik") {
	# ----------------------------
	# Lingide kast
	# ----------------------------

	if(!$is_custom) {
?>
        <table width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" border="0" cellspacing="0" cellpadding="1">
          <tr> 
            <td width="100%" class="boxhead" height="24">
		&nbsp;&nbsp;<?=$kast->pealkiri() ?><?php $kast->edit_buttons(array(
			tyyp_idlist => "8,2,6,9,13,17",
			
		)); ?></td>
          </tr>
          <tr> 
            <td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" bgcolor="<?=$kast->site->dbstyle("menyy_border","color")? $kast->site->dbstyle("menyy_border","color") :"#CCCCCC" ?>"> 
              <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="<?=$kast->site->dbstyle("menyy_taust","color")? $kast->site->dbstyle("menyy_taust","color") :"#FAFAFA" ?>">
				<tr> 
                  <td align="right" valign="top"><img src="<?=$kast->site->img_path ?>/px.gif" width="20" height="10"></td>
                  <td><img src="<?=$kast->site->img_path ?>/px.gif" width="<?=$kast->site->dbstyle("menyy_laius","layout")-52 ?>" height="10" border=0></td>

                  <td><img src="<?=$kast->site->img_path ?>/px.gif" width="20" height="10"></td>
                </tr>
<?php 
	
	} else {
	//Custom print out
		
		$custom_contents .= '<div class="linkbox">';

		ob_start();
		$kast->edit_buttons(array(
			tyyp_idlist => "8,2,6,9,13,17",
		));
		$custom_buttons .= ob_get_contents();
		ob_end_clean();
		$custom_title .= $kast->pealkiri();

	}//if is_custom

				$lingi_alamlist = new Alamlist(array(
					parent	=> $kast->objekt_id,
					klass	=> "link",
					asukoht	=> $kast->all[kesk],
				));

				$esimene = 1;
				while ($viit = $lingi_alamlist->next()) {
					if(!$is_custom) {
					if (!$esimene) {
						# eraldaja
?>
                <tr valign="top"> 
                  <td align="right" valign="top"><img src="<?=$kast->site->img_path ?>/px.gif" width="20" height="10"></td>
                  <td background="<?=$kast->site->img_path ?>/stripe1.gif"><img src="<?=$kast->site->img_path ?>/px.gif" width="1" height="10"></td>
                  <td><img src="<?=$kast->site->img_path ?>/px.gif" width="20" height="10"></td>
                </tr>
<?php 
					} # if !esimene

					$viit->load_sisu();
					if ($viit->site->in_editor) {

?>
		        <!--tr valign="top"> 
					<td colspan="3" align=left> &nbsp; &nbsp;

					</td>
                </tr-->
<?php 
				  } # if in_editor
?>
                <tr valign="top"> 
                  <td align="right" valign="top"><img src="<?=$kast->site->img_path ?>/nupp1.gif" width="10" height="10" align="texttop"></td>
                  <td><a href="<?=$viit->all[url] ?>" target="<?=$viit->all[on_uusaken] ? "_blank" : "_self" ?>" class="navi2_on"><?=$viit->pealkiri()?></a><?php $viit->edit_buttons(array(
						tyyp_idlist	=> 3
					)); ?></td>
                  <td><img src="<?=$kast->site->img_path ?>/px.gif" width="20" height="10"></td>
                </tr>
<?php     
				 $esimene=0;
				} else {
				//Custom print out
					$viit->load_sisu();
					if ($viit->site->in_editor) {
							ob_start();
							$viit->edit_buttons(array(
								tyyp_idlist	=> 3
							));
							$custom_contents .=  ob_get_contents();
							ob_end_clean();
					}
					$custom_contents .= '<a href="'.$viit->all[url].'" target="'.($viit->all[on_uusaken] ? "_blank" : "_self").'" class="navi2_on">'.$viit->pealkiri().'</a><br>';
				}//if is_custom
				} # while next()

			if(!$is_custom) {
				if ($lingi_alamlist->size==0) {
				
?>
				<tr> 
                  <td colspan="3">
					<?php $lingi_alamlist->edit_buttons(array(
						tyyp_idlist	=> 3
					)); ?></td>
                </tr>
<?php 
				}
?>

                <tr> 
                  <td colspan="3"><img src="<?=$kast->site->img_path ?>/px.gif" width="1" height="10"></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
					<br>
<?php 	
			} else {
				//Custom print out
					if ($lingi_alamlist->size==0) {
						ob_start();
						$lingi_alamlist->edit_buttons(array(
							tyyp_idlist	=> 3
						));
						$custom_contents .= ob_get_contents();
						ob_end_clean();
					}
				$custom_contents .= '</div>';
			}//if is_custom
	} else if ($kast->all[klass] == "loginkast") {
	# ----------------------------
	# Login kast
	# ----------------------------
		if ($kast->all[on_pealkiri] || $kast->site->in_editor) {
			if(!$is_custom) {
?>
		<table width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" border="0" cellspacing="0" cellpadding="1">
			<form action="<?=$kast->site->self?>" method=post>
			<tr>
				<td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" class="boxhead" height="24">&nbsp;&nbsp;<?=$kast->site->user->user_id ? $kast->site->sys_sona(array(sona => "tere", tyyp=>"kasutaja"))." ".$kast->site->user->all['username'] : $kast->pealkiri() ?><?php 
				$kast->edit_buttons(array(
					tyyp_idlist => "8,2,6,9,13,17",
				)); ?></td>
			</tr>
			<tr>
				<td width="100%" bgcolor="<?=$kast->site->dbstyle("menyy_border","color")? $kast->site->dbstyle("menyy_border","color") :"#CCCCCC" ?>">
					<table width="100%" border="0" cellspacing="0" cellpadding="11" bgcolor="<?=$kast->site->dbstyle("menyy_taust","color")? $kast->site->dbstyle("menyy_taust","color") :"#FAFAFA" ?>">
					<tr>
						<td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>">
<?php 
			} else {
			//Custom print out

				$custom_contents .= '<div class="loginbox">';
				
				ob_start();
				$kast->edit_buttons(array(
					tyyp_idlist => "8,2,6,9,13,17",
				));
				
				$custom_buttons .= ob_get_contents();
				ob_end_clean();
				$custom_title .= $kast->site->user->user_id ? $kast->site->sys_sona(array(sona => "tere", tyyp=>"kasutaja"))." ".$kast->site->user->all['username'] : $kast->pealkiri();
			}//if is_custom
		} # pealkiri

		if($is_custom) {
			ob_start();
		}//if is_custom

?>
				<font class=<?=($kast->site->agent ? "txt" : "txt1")?>>
<?php 
					# kasutaja login form
					# vüi tema andmed ja lingid
					if ($kast->site->user->user_id) {
?>
								<table  width="100%" border="0" cellspacing="0" cellpadding="0">
								<tr valign="top"> 
									<td align="right"><img src="<?=$kast->site->img_path ?>/nupp1.gif" width="10" height="10" align="texttop"></td>
									<td colspan=2><a href="<?=$kast->site->self?>?id=<?=$kast->objekt_id?>&op=register" class="navi2_on"><?=$kast->site->sys_sona(array(sona => "Muuda oma andmeid", tyyp=>"kasutaja"))?></a></td>
								</tr>
								<tr valign="top"> 
									<td align="right"><img src="<?=$kast->site->img_path ?>/nupp1.gif" width="10" height="10" align="texttop"></td>
									<td colspan=2><a href="<?=$kast->site->self?>?id=<?=$kast->objekt_id?>&op=logout&url=<?=$kast->site->safeURI?>" class="navi2_on"><?=$kast->site->sys_sona(array(sona => "Logi valja", tyyp=>"kasutaja"))?></a></td>
								</tr>
								</table>
<?php 
					} else {
						# ----------------
						# login kast
						# ----------------
?>								<form action="<?=$kast->site->self?>" method=post>
								<input type=hidden name="op" value="login">
								<input type=hidden name="url" value="<?=$kast->site->safeURI?>">
								<input type=hidden name="id" value="<?=$kast->objekt_id?>">
								<table  width="100%" border="0" cellspacing="0" cellpadding="0">
								<tr>
									<td width="1%"><img src="<?=$kast->site->img_path ?>/px.gif" width="13" height="1"></td>
									<td><img src="<?=$kast->site->img_path ?>/px.gif" width="1" height="1"></td>
									<td><img src="<?=$kast->site->img_path ?>/px.gif" width="62" height="1"></td>
								</tr>
								<tr>
									<td colspan=2 align="right"><font class=txt1><?=$kast->site->sys_sona(array(sona => "Login", tyyp=>"kasutaja"))?>:&nbsp;</font></td>
									<td>
										<input type=text class=searchbox size=3 name=user style="width:60">
									</td>
								</tr>
								<tr>
									<td colspan=2 align="right"  width="1%"><font class=txt1><?=$kast->site->sys_sona(array(sona => "Password", tyyp=>"kasutaja"))?>:&nbsp;</font></td>
									<td>
										<input type=password class=searchbox size=3 name=pass style="width:60">
									</td>
								</tr>
								<tr>
									<td colspan=3 align=center height="34"> 
										<INPUT class=searchbtn type=submit value="<?=$kast->site->sys_sona(array(sona => "nupp login", tyyp=>"kasutaja"))?>">
									</td>
								</tr>
							<?php if ($kast->site->CONF['allow_forgot_password']) { ?>
								<tr valign="top"> 
									<td align="right"><img src="<?=$kast->site->img_path ?>/nupp1.gif" width="10" height="10" align="texttop"></td>
									<td colspan=2><a href="<?=$kast->site->self?>?id=<?=$kast->objekt_id?>&op=remindpass" class="navi2_on"><?=$kast->site->sys_sona(array(sona => "Unustasid parooli", tyyp=>"kasutaja"))?></a></td>
								</tr>
							<?php } ?>
<?php if ($kast->site->CONF[users_can_register]==1) { ?>
								<tr valign="top"> 
									<td align="right"><img src="<?=$kast->site->img_path ?>/nupp1.gif" width="10" height="10" align="texttop"></td>
									<td colspan=2><a href="<?=$kast->site->self?>?id=<?=$kast->objekt_id?>&op=register" class="navi2_on"><?=$kast->site->sys_sona(array(sona => "Registeeru", tyyp=>"kasutaja"))?></a></td>
								</tr>
<?php } ?>
								</table>
								</form>
<?php 
					}
#					$kast->print_text(); 
?>
							</font>
<?php 
		if($is_custom) {
			$custom_contents .= ob_get_contents();
			ob_end_clean();
			$custom_contents .= '</div>';
		}//if is_custom

		if ($kast->all[on_pealkiri] || $kast->site->in_editor) {

		if(!$is_custom) {
?>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			</form>
		</table>
	<br>
<?php 
		}//if is_custom
		 } else {
			if(!$is_custom) {
				echo "<br>";
			}//if is_custom
		 }
	} else if ($kast->all[klass] == "artikkel") {
	# ----------------------------
	# Artikkel kastis
	# ----------------------------
		if ($kast->all[on_pealkiri] || $kast->site->in_editor) {
			if(!$is_custom) {
?>
		<table width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" border="0" cellspacing="0" cellpadding="1">
			<tr>
				<td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" class="boxhead" height="24">&nbsp;&nbsp;<?=$kast->pealkiri() ?><?php 
				$kast->edit_buttons(array(
					tyyp_idlist => "8,2,6,9,13,17",
				)); ?></td>
			</tr>
			<tr>
			<td width="100%" bgcolor="<?=$kast->site->dbstyle("menyy_border","color")? $kast->site->dbstyle("menyy_border","color") :"#CCCCCC" ?>">
				 <table width="100%" border="0" cellspacing="0" cellpadding="11" bgcolor="<?=$kast->site->dbstyle("menyy_taust","color")? $kast->site->dbstyle("menyy_taust","color") :"#FAFAFA" ?>">                
				 <tr>                   
					 <td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>">
<?php 
			} else {
			//Custom print out

				$custom_contents .= '<div class="articlebox">';

				ob_start();
				$kast->edit_buttons(array(
					tyyp_idlist => "8,2,6,9,13,17",
				));
				$custom_buttons .= ob_get_contents();
				ob_end_clean();
				$custom_title .= $kast->pealkiri();
			}//if is_custom
		} # pealkiri

		if(!$is_custom) {
?>
					<font class=<?=($kast->site->agent ? "txt" : "txt1")?>>
					<?php $kast->print_text(); ?>
					</font>
<?php 
		if ($kast->all[on_pealkiri] || $kast->site->in_editor) {
?>
					</td>
				 </tr>              
				 </table>
			 </td>
		 </tr>        
		 </table>
	<br>
<?php 
		 } else {
		    echo "<br>";
		 }

	} else {
	//Custom print out
		ob_start();
		echo "<font class=".($kast->site->agent ? "txt" : "txt1").">".$kast->print_text()."</font>";
		$custom_contents .= ob_get_contents();
		ob_end_clean();

		$custom_contents .= '</div>';

	}//if is_custom

	############## GALLUP
	} else if ($kast->all[klass] == "gallup") {
		$kast->load_sisu();

		######### HEADER

		##### 1) default html
		if(!$is_custom) {
?>
		<table width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" border="0" cellspacing="0" cellpadding="1">
		<tr>             
		 <td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" class="boxhead" height="24">
			&nbsp;&nbsp;<?=$kast->site->sys_sona(array(sona => 'Gallup', tyyp => "kujundus")) ?><?php $kast->edit_buttons(array(
					tyyp_idlist => "8,2,6,9,13,17",
				));
			?></td>
		 </tr> 
		 <tr>  
		 <td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" bgcolor="<?=$kast->site->dbstyle("menyy_border","color")? $kast->site->dbstyle("menyy_border","color") :"#CCCCCC" ?>"> 
		 <table width="100%" border="0" cellspacing="0" cellpadding="11" bgcolor="<?=$kast->site->dbstyle("menyy_taust","color")? $kast->site->dbstyle("menyy_taust","color") :"#FAFAFA" ?>">
		 <tr> 
						  
		 <td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>"><font class="<?=($kast->site->agent ? "txt" : "txt1" )?>"><?=$kast->pealkiri() ?></font> <br>
		 <table width="100%" border="0" cellspacing="0" cellpadding="2">
		 <tr>
		 <td valign="top" colspan="2"><img src="<?=$kast->site->img_path ?>/px.gif" width="1" height="3"></td>
		 </tr>
<?php 
		} 
		###### 2) custom html		 
		else {
			$custom_contents .= '<div class="gallupbox">';

			ob_start();
			$kast->edit_buttons(array(
				tyyp_idlist => "8,2,6,9,13,17",
			));
			$custom_buttons .= ob_get_contents();
			ob_end_clean();
			$custom_title .= '&nbsp;&nbsp;'.$kast->site->sys_sona(array(sona => 'Gallup', tyyp => "kujundus"));
			$custom_contents .= '<font class="'.($kast->site->agent ? "txt" : "txt1" ).'">'.$kast->pealkiri().'</font><br>';
		}//if is_custom

		######### / HEADER

		######### CHECK voting
		# 1) IP-based gallup
		if ($kast->site->CONF[gallup_ip_check]==1){
			$sql = $kast->site->db->prepare("SELECT COUNT(gi_id) FROM gallup_ip WHERE objekt_id=? AND ip LIKE ?",$kast->objekt_id, $_SERVER["REMOTE_ADDR"] );
			$sth = new SQL($sql);
			$count = $sth->fetchsingle();
			$kast->debug->msg($sth->debug->get_msgs());
		} 
		# 2) cookie based gallup
		else if ($kast->site->CONF[gallup_ip_check]==2 && $kast->site->cookie["gallup"][$kast->objekt_id]==1){
			$count = 1;
		} 
		# 3) user based gallup (only logged in users)
		else if ($kast->site->CONF[gallup_ip_check]==3){
			$sql = $kast->site->db->prepare("SELECT COUNT(gi_id) FROM gallup_ip WHERE objekt_id=? AND user_id=?",$kast->objekt_id,$kast->site->user->user_id);
			$sth = new SQL($sql);
			# count=1: not logged in users are not allowed to vote:
			$count = $kast->site->user->user_id ? $sth->fetchsingle() : 1;
			$kast->debug->msg($sth->debug->get_msgs());
		} else { 
			$count = 0;
		}
		######### / CHECK voting

		######### GET VOTES (SUMS)
		$sql = $kast->site->db->prepare("SELECT * FROM gallup_vastus WHERE objekt_id=?",$kast->objekt_id);
		$sth = new SQL($sql);

		if($is_custom) { # custom html
			ob_start();
			print '<table  width="100%" border="0" cellspacing="0" cellpadding="0">';
		}//if is_custom

		#################### 1. SHOW FORM & radio buttons
		if (!$count && !$kast->site->fdat[results] && !$kast->site->in_editor) {
?>				
			<SCRIPT LANGUAGE="JavaScript"><!--
				//See script on keerulisem kui see peaks olema
				//kuna muidu see ei tööta IE peal
				function do_it(vorm) {
					if (vorm.java_check.value==1) {
						return true
					} else {
						return false
					}
				}
			//--></SCRIPT>

			<form action="<?=$kast->site->self?>" method=get>
			<input type=hidden name="uri" value="<?=$kast->site->URI ?>">
			<input type=hidden name="gallup_id" value="<?=$kast->objekt_id ?>">
			<input type=hidden name="op" value="vote">

<?php 		####### loop over VASTUS (votes sum)
			while ($vastus = $sth->fetch()) { ?>
				 <tr>
				 <td valign="top" width="15">
				 <input type=radio id="vastus_<?=$vastus[gv_id]?>" name=vastus value="<?=$vastus[gv_id]?>" onclick="javascript:if(this.checked){this.form.java_check.value=1;};">
				 </td>
				 <td valign="top" class="<?=($kast->site->agent ? "txt" : "txt1") ?>"><label for="vastus_<?=$vastus[gv_id]?>"><?=$vastus[vastus] ?></label></td>
				 </tr>
<?php 		} # while vastus ?> 

				<?php ######## submit-button ?>
				<tr align="right">
					<input type="hidden" name="java_check" value="0">
					<td valign="top" colspan="2"><input type="submit" name="haaleta" value="<?=$kast->site->sys_sona(array(sona => 'haaleta', tyyp => "kujundus")) ?>" onclick="javascript:return do_it(this.form);" class="searchbtn"></td>
				</tr>
			  </form>
			 </table>
<?php 			
			$kast->debug->msg($sth->debug->get_msgs());
		} 
		#################### / 1. SHOW FORM & radio buttons
		
		#################### 2. SHOW RESULTS
		else {
			$sql = $kast->site->db->prepare("SELECT SUM(count) AS kokku, MAX(count) AS maksi FROM gallup_vastus WHERE objekt_id=? ",$kast->objekt_id);
			
			$sth_c = new SQL($sql);
			$stat = $sth_c->fetch();
			$kast->debug->msg("kokku = $stat[kokku], maks = $stat[maksi]");
			$kast->debug->msg($sth_c->debug->get_msgs());

		###### voters vount:
?>
		 <tr>
			 <td valign="top" class="<?=($kast->site->agent ? "txt" : "txt1")?>"><?=$kast->site->sys_sona(array(sona =>"vastajaid", tyyp=> "kujundus"))?>: <b><?=$stat[kokku] ?></b></td>
			 </tr>
			 <tr>
			 <td valign="top"><img src="<?=$kast->site->img_path ?>/px.gif" width="1" height="3"></td>
		 </tr>
<?php 	###### one colored row
		while ($vastus = $sth->fetch()) {
			$percent = $stat[kokku] ? sprintf('%2.0f',100*($vastus[count])/$stat[kokku]) : 0;
?>				
		 <tr>
			 <td valign="top" class="<?=($kast->site->agent ? "txt" : "txt1")?>"><?=$vastus[vastus] ?></td>
		 </tr>
		 <tr>
			 <td valign="top"><b><font class="<?=($kast->site->agent ? "txt" : "txt1")?>">- <?=$percent ?>%</font></b> <img src="<?=$kast->site->img_path ?>/gallup_bar<?=(($stat[maksi]==$vastus[count] && $vastus[count])? "2":"1") ?>.gif" width="<?= 110 * ($percent/100) ?>" height=8 border="1"></td>
		 </tr>
<?php 
			} # while vastus
		
			############## archive link     # added 12.12.2003 by Dima Bug #744
			if ($archive_link_on) { ?>
			 <tr>
				 <td valign="top"><img src="<?=$kast->site->img_path ?>/px.gif" width="1" height="3"></td>
				 </tr>
				 <tr align="right">
				 <td valign="top"><a href="<?=$kast->site->self ?>?op=gallup_arhiiv" class="navi2_on"><?=$kast->site->sys_sona(array(sona => 'Arhiiv', tyyp => "kujundus")) ?></a></td>
			 </tr>
			<?php }?>
			 </table>
<?php 	}
		#################### / 2. SHOW RESULTS

		##### 1) default html
		if(!$is_custom) { ?>
			</td>
		 </tr>              
		 </table>
		 </td>
		 </tr>        
		 </table>
		<br>
<?php 	}//if is_custom

		##### 2) custom html			
		if($is_custom) {
			$custom_contents .= ob_get_contents();
			ob_end_clean();
			$custom_contents .= '</div>';
		}//if is_custom

	} 
	############## / GALLUP	

	else if ($kast->all[klass] == "iframekast")	{
		$kast->load_sisu();

		$conf = new CONFIG($kast->all[ttyyp_params]);
		$src_file = $conf->get("src_file");
		$predefined = $conf->get("predefined");
		$height = $conf->get("height");

		# kui tegemist saidi sisese failiga, panna id juurde
		if (trim($predefined) != '') {
			$src_file .= "&id=".($kast->site->fdat[id] ? $kast->site->fdat[id] : $kast->site->alias("rub_home_id"));
		}

	if(!$is_custom) {
?>
  <table width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" border="0" cellspacing="0" cellpadding="2">
                
  <tr> 
                  
  <td class="boxhead" height="24">
	  &nbsp;&nbsp;<?=$kast->pealkiri() ?><?php $kast->edit_buttons(array(
					tyyp_idlist => "8,2,6,9,13,17",
				));
	?></td>
  </tr>
                
  <tr valign="top"> 
                  
  <td class=box><?php if(strlen(trim($src_file))>0) {?><iframe name="iifreim" src="<?=$src_file?>" width="<?= ($is_custom)?"100%":$kast->site->dbstyle("menyy_laius","layout");?>" frameborder=0 height="<?=$height?>" ></iframe><?php }?></td>
  </tr>
  
              
  </table>
	  <br>


<?php 
	} else {
	//Custom print out

		$custom_contents .= '<div class="iframebox">';

		ob_start();
		$kast->edit_buttons(array(
			tyyp_idlist => "8,2,6,9,13,17",
		));
		$custom_buttons .= ob_get_contents();
		ob_end_clean();
		$custom_title .= $kast->pealkiri();
		if(strlen(trim($src_file))>0) {
			$custom_contents .= '<iframe name="iifreim" src="'.$src_file.'" width="100%" frameborder=0 height="'.$height.'" ></iframe>';
		}

		$custom_contents .= '</div>';

	}//if is_custom

	}
###########################################################

	} else if (get_class($kast)=="Alamlist" || is_subclass_of($kast,"Alamlist")) {
	# ----------------------
	# kui parameetrina on alamlist, 
	# siis teeme "uus kast" nupp
	# ----------------------
		if (!($kast->size>0)) {
			if(!$is_custom) {
?>
<table width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" border="0" cellspacing="0" cellpadding="1">          
 <tr>             
 <td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" class="boxhead" height="24">
&nbsp; &nbsp;
<?=$kast->site->sys_sona(array(sona => 'new', tyyp =>"editor"))?> 
<?=$kast->edit_buttons(array(
	tyyp_idlist => "8,2,6,9,13,17",
	no_br => 1
))?>	
</nobr></td>
 </tr>
 <tr>             
 <td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" bgcolor="<?=$kast->site->dbstyle("menyy_border","color")? $kast->site->dbstyle("menyy_border","color") :"#CCCCCC" ?>"> 
              
 <table width="<?=$kast->site->dbstyle("menyy_laius","layout")?>" border="0" cellspacing="0" cellpadding="11" bgcolor="<?=$kast->site->dbstyle("menyy_taust","color")? $kast->site->dbstyle("menyy_taust","color") :"#FAFAFA" ?>">                
 <tr>
	<td width="<?=$kast->site->dbstyle("menyy_laius","layout")?>">&nbsp;</td>
 </tr>              
 </table>

 </td>
 </tr>        
 </table>
	 <br>
<?php 
			} else {
			//Custom print out
				ob_start();
				$kast->edit_buttons(array(
					tyyp_idlist => "8,2,6,9,13,17",
					no_br => 1
				));
				$custom_buttons .= ob_get_contents();
				ob_end_clean();
				$custom_title .= $kast->site->sys_sona(array(sona => 'new', tyyp =>"editor"));
			}//if is_custom
		} # if ! size > 0
	} else {
		$GLOBALS{site}->debug->msg("print_kast() argument \"kast\" on vale");
	}
	
#print "<br>";

	return array('buttons'=>$custom_buttons,'title'=>$custom_title,'contents'=>$custom_contents);

}
