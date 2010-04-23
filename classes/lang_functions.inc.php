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



/**
 * import into CMS dictionary from a CSV text  file, returns true on success, false if failes
 *
 * @param	string	$cvs_file
 * @param	boolean	$overwrite_user_translations	default false
 * @param	boolean	$delete_old_data				default false
 * @param	boolean	$write_log						default true
 * @return	boolean
 */
function import_dict_from_file($cvs_file, $overwrite_user_translations = false, $delete_old_data = false, $write_log = true)
{
	global $site;
	
	//printr($overwrite_user_translations);
	//printr($delete_old_data);
	
	if($filep = @fopen($cvs_file, 'r'))
	{
		// read file contents into a string
		$cvs_file = fread($filep, filesize($cvs_file));
		// chop the strings into lines
		$cvs_file = str_replace("\r\n", "\n", $cvs_file); // bug #2397, change the windows linebreaks
		$cvs_file = explode("\n", $cvs_file);
			
		$cvs_checksum = null;
		$cvs_date = null;
		$cvs_encoding = null;
		$sys_words = array();
		$k = null;
		
		/*
			create an array($sys_words) of translation
		*/
		// for each line in the file
		foreach($cvs_file as $i => $cvs_line)
		{
			//lines with starting with [
			if($cvs_line[0] == '[')
			{
				//chekcsum
				if(ereg('^\[CHECKSUM', $cvs_line))
				{
					$cvs_checksum = str_replace(array('[CHECKSUM=',']'), '', $cvs_line);	
					$cvs_checksum = explode(':', $cvs_checksum);
					$cvs_checksum['lang_id'] = &$cvs_checksum[0];
					$cvs_checksum['types'] = &$cvs_checksum[1];
					$cvs_checksum['words'] = &$cvs_checksum[2];
					
					unset($cvs_file[$i]);
				}
				//date (mysql datetime)
				elseif(ereg('^\[DATE', $cvs_line))
				{
					$cvs_date = str_replace(array('[DATE=',']'), '', $cvs_line);	
					
					unset($cvs_file[$i]);
				}
				//encoding
				elseif(ereg('^\[ENCODING', $cvs_line))
				{
					$cvs_encoding = trim(str_replace(array('[ENCODING=',']'), '', $cvs_line));	
					
					unset($cvs_file[$i]);
				}
				//types
				else 
				{
					$k = array_push($sys_words, explode(':', str_replace(array('[',']'), '', $cvs_line)));
					$k--;
					$sys_words[$k]['key'] = &$sys_words[$k][0];
					$sys_words[$k]['sst_id'] = &$sys_words[$k][1];
					$sys_words[$k]['name'] = &$sys_words[$k][2];
				}
			}
			//comment lines, skip them
			elseif($cvs_line[0] == '#')
			{
				unset($cvs_file[$i]);
			}
			//empty lines, skip them
			elseif(empty($cvs_line))
			{
				unset($cvs_file[$i]);
			}
			//translation line
			elseif($k !== null)
			{
				$cvs_line = explode(';', $cvs_line);
				$sys_words[$k]['words'][] = array(
						'sys_word' => $cvs_line[0],
						'description' => $cvs_line[1],
						'translation' => $cvs_line[2],
					);
			}
		}

		## check if CHECKSUM line was successfully detected, if not, return error and stop (Bug #2566)
		if(trim($cvs_checksum['lang_id']) == '' ){
			new Log(array(
				'action' => 'import',
				'component' => 'Languages',
				'type' => 'ERROR',
				'message' => 'Dictionary import: invalid language file - CHECKSUM line not detected!',
			));
			return false;		
		}

		/*
		// check if the dict file is the same encoding as the language
		// no longer neccesssary, because langaugaes and glossaries are different things
		$sql = $site->db->prepare('select nimi, encoding from keel where keel_id = ?;', $cvs_checksum['lang_id']);
		$result = new SQL($sql);
		$result = $result->fetch('ASSOC');
		$lang_name = $result['nimi'];
		if($write_log && $result['encoding'] != $cvs_encoding)
		{
			new Log(array(
				'action' => 'import',
				'component' => 'Languages',
				'type' => 'ERROR',
				'message' => 'Dictionary import: CSV file encoding does not match language encoding. CSV = "'.$cvs_encoding.'" '.$lang_name.' = "'.$result['encoding'].'".',
			));
			return false;
		}
		*/
		
		// update glossary encoding from the language file
		$sql = $site->db->prepare("update keel set encoding = ? where keel_id = ?", $cvs_encoding, $cvs_checksum['lang_id']);
		$result = new SQL($sql);
		
		if($result->error)
		{
			new Log(array(
				'action' => 'import',
				'component' => 'Languages',
				'type' => 'ERROR',
				'message' => 'Dictionary import: Could not update glossary encoding. CSV = "'.$cvs_encoding.'" '.$lang_name.'.',
			));
			return false;
		}
		
		//printr($sys_words);	
		//printr($cvs_checksum);	
		
		foreach($sys_words as $sys_word)
		{
			$sql = $site->db->prepare('select sst_id from sys_sona_tyyp where voti = ?', $sys_word['key']);
			$result = new SQL($sql);
			$sys_word['sst_id'] = $result->fetchsingle();
			
			if($result->rows == 0)
			{
				// create sys_word type
				$sql = $site->db->prepare('insert into sys_sona_tyyp (voti, nimi) values (?, ?);', $sys_word['key'], $sys_word['name']);
				$result = new SQL($sql);
				$sys_word['sst_id'] = $result->insert_id;
			}
			else 
			{
				// update sys_word type
				$sql = $site->db->prepare('update sys_sona_tyyp set voti = ?, nimi = ? where sst_id = ?;', $sys_word['key'], $sys_word['name'], $sys_word['sst_id']);
				new SQL($sql);
			}
			
			if($delete_old_data)
			{
				$sql = $site->db->prepare('delete from sys_sonad where sst_id = ? and keel = ?', $sys_word['sst_id'], $cvs_checksum['lang_id']);
				new SQL($sql);
			}
			
			if(isset($sys_word['words'])) foreach($sys_word['words'] as $word)
			{
				$sql = $site->db->prepare('select 1 from sys_sonad_kirjeldus where sst_id = ? and sys_sona = ?', $sys_word['sst_id'], $word['sys_word']);
				$result = new SQL($sql);
				
				if($result->rows == 0)
				{
					// insert new 
					$sql = $site->db->prepare('insert into sys_sonad_kirjeldus (sst_id, sys_sona, sona, last_update) values (?, ?, ? , now());', $sys_word['sst_id'], $word['sys_word'], $word['description']);
					new SQL($sql);
				}
				else 
				{
					// update only last_update
					$sql = $site->db->prepare('update sys_sonad_kirjeldus set sona = ?, last_update = now() where sst_id = ? and sys_sona = ?;', $word['description'], $sys_word['sst_id'] ,$word['sys_word']);
					new SQL($sql);
				}
				
				$sql = $site->db->prepare('select id from sys_sonad where sst_id = ? and sys_sona = ? and keel = ?', $sys_word['sst_id'], $word['sys_word'], $cvs_checksum['lang_id']);
				$result = new SQL($sql);
				
				if($result->rows == 0)
				{
					// insert new 
					$sql = $site->db->prepare('insert into sys_sonad (sys_sona, keel, sona, origin_sona, sst_id) values (?, ?, ? , ?, ?);', $word['sys_word'], $cvs_checksum['lang_id'], $word['translation'], $word['translation'], $sys_word['sst_id']);
					new SQL($sql);
				}
				elseif($overwrite_user_translations)
				{
					// update 
					$sql = $site->db->prepare('update sys_sonad set sona = ?, origin_sona = ? where id = ?;', $word['translation'], $word['translation'], $result->fetchsingle());
					new SQL($sql);
				}
				else 
				{
					// update 
					$sql = $site->db->prepare('update sys_sonad set origin_sona = ? where id = ?;', $word['translation'], $result->fetchsingle());
					new SQL($sql);
				}
			}
		}
			new Log(array(
				'action' => 'import',
				'component' => 'Languages',
				'type' => 'NOTICE',
				'message' => 'Dictionary import successful. Language: '.$lang_name,
			));

		return true;
	}
	else
	{
		if($write_log)
		{
			new Log(array(
				'action' => 'import',
				'component' => 'Languages',
				'type' => 'ERROR',
				'message' => 'Dictionary import: could not open CSV file "'.$cvs_file.'.',
			));
		}
		return false;
	}
		
}
