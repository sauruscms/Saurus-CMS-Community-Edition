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
# function sysword
#	word => word
#	type => type
#	name => template variable the output will be assigned to
#	[load_all => 0/1] default 0
#	[skip_convert => 0/1] default 0
#	[show_missing => 0/1] default 0; 1: if translation not found then print out an input word
# prints sysword

function smarty_function_sysword ($params, &$smarty) {
	global $site;

	extract($params);

	$original_word = $word; # remember original word for "show_missing" feature
	
	if( isset($word) && isset($type) ) { # if required param OK
		$word = trim($word);
		$word = preg_replace("/\s+/","_",$word);
		$word = strtolower($word);
		$type = preg_replace("/\s+/","_",$type);
		$type = strtolower($type);

		# Retrieve word from cache:
		$cash_value = $site->cash(array(klass => "smarty_syswords", kood => $word."_".$type."_".$type_id));

		# if exists:
		if ($cash_value) {
			echo $cash_value; return; //1; Bug #1921

		} else {

			$is_types_cached = $site->cash(array(klass => "smarty_syswords", kood => "SYSWORD_TYPES_LOAD_ALL"));

			# If word's types already cached, return them from cache:
			if ($is_types_cached){
				
				$type_arr = &$site->cash(array(klass => "smarty_syswords", kood => "SYSWORD_TYPES_ARRAY"));
				$type_synonim_arr = &$site->cash(array(klass => "smarty_syswords", kood => "SYSWORD_TYPES_SYNONIM_ARRAY"));
				$type = $type_synonim_arr[$type];

			# If word's types are not in cache, then load them from DB and put into cache:
			} else {

				$sth = new SQL("SELECT sst_id, voti, nimi FROM sys_sona_tyyp");
				$site->debug->msg($sth->debug->get_msgs());

				while($data = $sth->fetch()){
					$data['voti'] = strtolower($data['voti']);
					$data['nimi'] = strtolower($data['nimi']);
					$type_arr[$data['voti']] = $data['sst_id'];
					$data['nimi'] = preg_replace("/\s+/", "_", $data['nimi']);
					$type_arr[$data['nimi']] = $data['sst_id'];
					$type_synonim_arr[$data['nimi']] = $data['voti'];
					$type_synonim_arr[$data['voti']] = $data['voti'];
				}

				# put into cache:
				$site->cash(array(klass => "smarty_syswords", kood => "SYSWORD_TYPES_ARRAY", sisu => $type_arr));	
				$site->cash(array(klass => "smarty_syswords", kood => "SYSWORD_TYPES_SYNONIM_ARRAY", sisu => $type_synonim_arr));
				$site->cash(array(klass => "smarty_syswords", kood => "SYSWORD_TYPES_LOAD_ALL", sisu => 1));
				$type = $type_synonim_arr[$type];

				# for debug:
				$site->debug->msg("SMARTY SYSWORD: Put all sys. word's types into cache. Total: ".$sth->rows);
			}

			# if syssona type id found, search for sys_sona:
			if (is_numeric($type_arr[$type])){

				# If load all, then check, if word's descriptions already in cache.
				# If not, put them from DB into cache:
				if ($load_all && !$site->cash(array(klass => "smarty_syswords", kood => $type."_". "SYSWORD_DESCRIPTIONS_LOAD_ALL"))){

					$sql = $site->db->prepare("SELECT sst_id, sys_sona, sona FROM sys_sonad_kirjeldus WHERE sst_id=?", $type_arr[$type]);								
					$sth = new SQL($sql);
					$site->debug->msg($sth->debug->get_msgs());		
					while ($data = $sth->fetch()){
						$data['sys_sona'] = strtolower($data['sys_sona']);
						$data['sona'] = strtolower($data['sona']);

						$word_arr[preg_replace("/\s+/", "_", $data['sys_sona'])] = $data['sys_sona'];
						$word_arr[preg_replace("/\s+/", "_", $data['sona'])] = $data['sys_sona'];				
					}					
					
					$site->cash(array('klass' => "smarty_syswords", 'kood' => "words_".$type_arr[$type], 'sisu' => $word_arr));
					$site->cash(array('klass' => "smarty_syswords", 'kood' => $type."_". "SYSWORD_DESCRIPTIONS_LOAD_ALL", 'sisu' => 1));

					$final_sys_sona = $word_arr[$word];
				
				# if sysword already in cache:
				} else if ($site->cash(array(klass => "smarty_syswords", kood => $type."_". "SYSWORD_DESCRIPTIONS_LOAD_ALL"))) {

					# retrieve from cache:
					$word_arr = &$site->cash(array('klass' => "smarty_syswords", 'kood' => "words_".$type_arr[$type]));
					$final_sys_sona = $word_arr[$word];

				# 
				} else {			

				#################
				# find word
				$sql = $site->db->prepare("SELECT sys_sona FROM sys_sonad_kirjeldus WHERE sst_id=? AND (sys_sona LIKE ? OR sona LIKE ?) LIMIT 1", $type_arr[$type], $word, $word); 
				$sth = new SQL($sql);
				$site->debug->msg($sth->debug->get_msgs());
				$final_sys_sona = $sth->fetchsingle();
				}

				#################
				# print 
				$edit_link_start='';$edit_link_end=''; # Bug #2426
				$new_link_start='';$new_link_end='';
				
				if ($final_sys_sona){
										
					if($site->in_editor){

						######### sysword edit-link
                        $sysword_sql = $site->db->prepare("SELECT IF(LENGTH(sona)>0,sona,origin_sona) AS sona, id, sys_sona
                        FROM sys_sona_tyyp, sys_sonad 
                        WHERE sys_sonad.sst_id = sys_sona_tyyp.sst_id AND sys_sonad.sst_id=? AND keel=? 
                        AND UCASE(sys_sona) LIKE UCASE(?) ", $type_arr[$type], $site->keel, $final_sys_sona);
                        
                        $sysword_sth = new SQL($sysword_sql);
                        $sysword_res = $sysword_sth->fetch();

						#### if translation is missing then display sysword edit-popup link
						
						if($sysword_res['sys_sona'] == '' && $sysword_res['id']){
							$sysword_href = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/admin/edit_translation.php?op=new&type=popup&sst_id='.$type_arr[$type];
							
							$edit_button = '<a class="syswordpopup" href="javascript:void(avapopup(\''.$sysword_href.'\',\'glossary\',\'600\',\'400\',\'no\'))">
								<img src="http://'.$site->CONF['hostname'].$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/translate.png">
							</a>';
                        }else{
                        	$sysword_href = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/admin/edit_translation.php?op=edit&type=popup&sst_id='.$type_arr[$type].'&word_id='.$sysword_res['id'];

							if($params['hide_button'] != 1 || $params['hide_buttons'] != 1){
								$edit_button = '<a class="syswordpopup" href="javascript:void(avapopup(\''.$sysword_href.'\',\'glossary\',\'600\',\'400\',\'no\'))">
									<img src="http://'.$site->CONF['hostname'].$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/translate.png">
								</a>';
							}
						}
                        
						######### / sysword edit-link
                    //\Changed by Alexei
                    }
                    
                    if($site->CONF['allow_onsite_translation'] != 1) $edit_button = "";
                    
					if ($name) { # assign word to template variable
						
						if($params['hide_button'] == 1 || $params['hide_buttons'] == 1){
							$smarty->assign(array(
								$name => $site->sys_sona(array('sona' => $final_sys_sona, 'tyyp'=> $type_synonim_arr[$type], 'load_all' => $load_all, 'skip_convert' => $skip_convert))
							));
						}else{
							$smarty->assign(array(
								$name => $site->sys_sona(array('sona' => $final_sys_sona, 'tyyp'=> $type_synonim_arr[$type], 'load_all' => $load_all, 'skip_convert' => $skip_convert)).$edit_button
							));
						}
					} else { # echo word
						if($params['hide_button'] == 1 || $params['hide_buttons'] == 1){
							echo $site->sys_sona(array('sona' => $final_sys_sona, 'tyyp'=> $type_synonim_arr[$type], 'load_all' => $load_all, 'skip_convert' => $skip_convert));
						}else{
							echo $site->sys_sona(array('sona' => $final_sys_sona, 'tyyp'=> $type_synonim_arr[$type], 'load_all' => $load_all, 'skip_convert' => $skip_convert)).$edit_button;
						}
					}
					return; //1; Bug #1921
				} 
				# if word translation not found AND in editor-area => print "[missingword]"
				else if($site->on_debug || $site->in_editor) { 
					######### sysword new-link

                    # open popup admin/sys_sonad_loetelu.php?lisa=1&sst_id=121&flt_keel=1
                    $edit_button = '<a class="syswordpopup" href="javascript:javascript:void(avapopup(\''.(empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'].'/admin/edit_translation.php?op=new&type=popup&sst_id='.$type_arr[$type].'&sys_word='.($show_missing?$original_word:$word).'\',\'glossary\',\'600\',\'400\',\'no\'))">
                    	<img src="http://'.$site->CONF['hostname'].$site->CONF['wwwroot'].$site->CONF['styles_path'].'/gfx/translate.png">
                    </a>';

					######### / sysword new-link
                    if($site->CONF['allow_onsite_translation'] != 1) $edit_button = "";
                    
					if ($name) { # save word to template var
						if($params['hide_button'] == 1 || $params['hide_buttons'] == 1){
							$smarty->assign(array(
								$name => ($show_missing?$original_word:$word)
							));
						}else{
							$smarty->assign(array(	
								$name => "[".($show_missing?$original_word:$word)."]".$edit_button
							));
						}
					} 
					else {
						if($params['hide_button'] == 1 || $params['hide_buttons'] == 1){
							echo ($show_missing?$original_word:$word); 
						}else{
							echo "[".($show_missing?$original_word:$word)."]".$edit_button; 
						}
					}
					return; // 1; Bug #1921
				} 
				# if translation not found and parameter "show_missing"=1 => print out an input word (Bug #1961)
				else if($show_missing) { 
					if ($name) { # save word to template var
						$smarty->assign(array(
							$name => $original_word
						));
					} 
					else { 
						echo $original_word; 
					}
					return;
				} # if word translation not found
			} # if syssona type id found
			else{
				return; // 0; Bug #1921
			}
		}# if cash value not found => search for word
	} # if required param OK
}
