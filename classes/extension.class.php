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
 * extension handling functions
 * 
 */


/**
 * extension class
 * 
 * All extension handling functions - extension info, permissions, install/update etc
 * 
 * @package CMS
 * 
 * @param int $name 
 *
 * $extension = new extension(array(
 *	name => CODENAME   # eg "mylibrary"
 *	[path => $dir_relative_path] # path, eg "extension/mylibrary/"
 *	[site => &$this],  # pointer to site isntance
 * ));
 * 
 */
class extension extends BaasObjekt {

	# NB! keep constructor small and use public functions to get additional info for extension,
	# mimimum extension instance will return only extension data from table 'extensions', nothing else

	function extension ($args) {
		$this->BaasObjekt($args);
		
		$this->args = $args;
		
		# if new extension instance is called in the middle of site class,
		# then current site instance is passed as parameter, otherwise usual way is used
		if ($args['site']) {
			$this->site = &$args['site'];
		}

		##################
		# GET extension NAME

		# 1. name as parameter
		if($args['name']) {
			$this->name = $args['name'];
		}
		# 2. path as parameter
		elseif($args['path']) {
			$tmp_arr = explode("/",$args['path']);
			$this->name = $tmp_arr[1];
		}
		
		####################
		# CHECK name, at this point must name exist, but if not
		# then exit and dont create extension instance
		if(!$this->name)	{
			$this->site->debug->msg("extension not found => exit");
			$this->name = '';
			$this->id = '';
			return;
		}

		###################
		# GET ALL extension DATA:
		$sql = $this->site->db->prepare("SELECT extensions.*, DATE_FORMAT(version_date,'%d.%m.%Y') AS fversion_date FROM extensions ");

		if($this->name){ $sql .= $this->site->db->prepare("WHERE name=?",$this->name); }
##		elseif($this->path){ $sql .= $this->site->db->prepare("WHERE path=?",$this->path); }
		#print $sql;
		$sth = new SQL($sql);
		# if extension found
		if ($sth->rows) {
			$this->all = $sth->fetch('ASSOC');

			# find extension TEMPLATES: array of template names


			# common properties:
			$this->name = $this->all['name'];
			$this->id = $this->all['extension_id'];
			$this->path = $this->all['path'];
			$this->absolute_path = $this->site->absolute_path.$this->all['path'];
		}
		##################
		# IF extension NOT FOUND IN DATABASE
		else {
			$this->name = 0;
			return 0;
		}

	} # constructor extension
	###################

/**
* install (private)
* 
* 
* 
*
* 
* @package CMS
* 
*/
function install(){

	$args = $this->args;

}
# / install
##########################



/**
* update (private)
* 
* 
* 
*
* 
* @package CMS
* 
*/
function update(){

	$args = $this->args;

}
# / update
##########################


/**
* uninstall (private)
* 
* 
* 
*
* 
* @package CMS
* 
*/
function uninstall(){

	$args = $this->args;

	if($this->name) { # sanity check

		############# DELETE TEMPLATES
		$sql = $this->site->db->prepare("DELETE FROM templ_tyyp WHERE extension=?",$this->name);
		$sth = new SQL($sql);
		#print "<br>".$sql;
		$this->site->debug->msg($sth->debug->get_msgs());

		############# DELETE ADMIN-PAGES

		$sql = $this->site->db->prepare("DELETE FROM admin_osa WHERE extension=?",$this->name);
		$sth = new SQL($sql);
		#print "<br>".$sql;
		$this->site->debug->msg($sth->debug->get_msgs());

		############# DELETE RECORD
		$sql = $this->site->db->prepare("DELETE FROM extensions WHERE name=?",$this->name);
		$sth = new SQL($sql);
		#print "<br>".$sql;
		$this->site->debug->msg($sth->debug->get_msgs());

		############# DELETE EXT DIR
		if(is_dir($this->absolute_path)) {
		$dir_deleted = deldir($this->absolute_path);
		}

		############# DELETE GLOSSARY
		$sql = $this->site->db->prepare("SELECT sst_id FROM sys_sona_tyyp WHERE extension=? AND sst_id >= 100", $this->name); 
		$sth = new SQL($sql);
		$sst_id = $sth->fetchsingle();

		$sql = $this->site->db->prepare("DELETE FROM sys_sona_tyyp WHERE sst_id=?",$sst_id);
		$sth = new SQL($sql);
		$this->site->debug->msg($sth->debug->get_msgs());

		$sql = $this->site->db->prepare("DELETE FROM sys_sonad WHERE sst_id=?",$sst_id);
		$sth = new SQL($sql);
		$this->site->debug->msg($sth->debug->get_msgs());

		$sql = $this->site->db->prepare("DELETE FROM sys_sonad_kirjeldus WHERE sst_id=?",$sst_id);
		$sth = new SQL($sql);
		$this->site->debug->msg($sth->debug->get_msgs());


		####### write log
		new Log(array(
			'action' => 'delete',
			'component' => 'Extensions',
			'message' => "Extension '".$this->name."' uninstalled. Directory '".$this->absolute_path."' ".($dir_deleted?'deleted':'not deleted - <font color=red>permission denied</font>'),
		));
	} # sanity check
}
# / uninstall
##########################

/**
* check_dependencies (private)
* 
* 1. Version check: If current CMS version is smaller than minimum saurus version requirement
* then show error message and change extension activity to false. Return 0
* 2. Modules check: If required saurus modules not found 
* then show error message and change extension activity to false. Return 0
*
* 
* @package CMS
* 
*/
function check_dependencies(){

	$args = $this->args;

	# 1. If current version is smaller than minimum saurus version requirement
	# then show error message and change activity to false.
	if(version_compare($this->site->cms_version, $this->all["min_saurus_version"]) < 0){ # first < second

		if($this->all['is_active']){
			$this->set_inactive();
			####### write log
			new Log(array(
				'action' => 'disable',
				'component' => 'Extensions',
				'message' => "Extension '".$this->name."' changed to inactive. (check version dependencies)",
			));
		}
		return 0;
	}

	return 1; ## dependencies are OK
}
# / check_dependencies
##########################

/**
* set_inactive (private)
* 
* Change extension activity to false.
*
* 
* @package CMS
* 
*/
function set_inactive(){

	$args = $this->args;

	$sql = $this->site->db->prepare("UPDATE extensions SET is_active=? WHERE name=?",
		'0',
		$this->name
	);
	$sth = new SQL($sql);
	#print($sql);
	$this->site->debug->msg($sth->debug->get_msgs());	

}
# / set_inactive
##########################

/**
* get_templates (private)
* 
* 
* 
*
* 
* @package CMS
* 
*/
function get_templates(){

	$args = $this->args;

	$templ_arr = array();

	$sql = $this->site->db->prepare("SELECT ttyyp_id, nimi, templ_fail, on_page_templ FROM templ_tyyp WHERE extension=?",$this->name);
	$sth = new SQL($sql);
	while($templ = $sth->fetch() ){
		$templ_arr[] = $templ;
	}

	return $templ_arr;
}
# / get_templates
##########################

/**
* get_adminpages (private)
* 
* 
* 
*
* 
* @package CMS
* 
*/
function get_adminpages(){

	$args = $this->args;

}
# / get_adminpages
##########################


/**
* load_extension_config (private)
* 
* Searches for file "extension.config.php" and includes the file, 
* reading all variables into the array: $this->CONF.
* Returns 1 if config file found, 0 if not.
* 
* @package CMS
*
* @param name - extension name
*
* $conf_found = $extension->load_extension_config();
*/
function load_extension_config(){

	if(!is_array($this->site->cash(array('klass' => 'GET_EXTENSIONS', 'kood' => $this->site->absolute_path.$this->path)))){

		$file = $this->site->absolute_path.$this->path.'extension.config.php';

		if(file_exists($file)) {

			include($file);
			$this->site->cash(array(klass => 'GET_EXTENSIONS', 'kood' => $this->site->absolute_path.$this->path, 'sisu' => $EXTENSION));
			$conf_found = 1;
			$this->CONF = $EXTENSION;

		}else { 

			$conf_found = 0;

		}
	}else{

		//When in memory, re read it from the array
		$this->CONF = $this->site->cash(array('klass' => 'GET_EXTENSIONS', 'kood' => $this->site->absolute_path.$this->path));
		$conf_found = 1;
	}

	return $conf_found;
}
# / load_extension_config
##########################



}

###################################### Standalone and public extension functions ########################


/**
* sync_extensions (public)
* 
* 1) Reads directory "extensions/" and adds new record into table 'extension' for each found directory
* 2) searches for file "extension.config.php" and reads the values in that file into the extension record
*   - add new admin-pages automatically, if needed
*   - add new templates automatically, if needed
*	- add new system word group, if needed
*   - import dictionary from language files
*   - run install/update SQL files
*   - check dependencies
* 
* @package CMS
* 
*/
function sync_extensions(){
	global $site, $class_path;
	
	include_once($class_path.'lang_functions.inc.php');
	include_once($class_path.'install.inc.php');

	$ext_path = $site->absolute_path.'extensions/';
	$handle = opendir($ext_path);
	while (false !== ($dir = readdir($handle))) {
		if (is_dir($ext_path.$dir) && $dir != '.' && $dir != '..' && $dir != 'CVS') {
			$dirlist[] = $dir."/";
		} # if
	} # while
	closedir($handle);
	# if no dirs found => do nothing & return
	if(!count($dirlist)) {
		return;
	}	
	sort($dirlist);	

	############ loop over extension directories
	foreach ($dirlist as $dir) {

		$is_install = false; # true, if found new extension 

		$dir_absolute_path = $ext_path.$dir;
		$dir_relative_path = 'extensions/'.$dir;
		$dir_name = substr($dir,0,-1);

#		printr($dir_absolute_path);
#		printr($dir_relative_path);

		####### check if extension exists
		$extension = new extension(array(
			name => $dir_name
		));

		###### 1. extension not found in database => INSERT it
		if(!$extension->name) {
			$is_install = true;

			$sql = $site->db->prepare("INSERT INTO extensions (name,path,is_active) VALUES (?,?,?)",$dir_name,$dir_relative_path,'0');
			$sth = new SQL($sql);
			#print($sql);
			$site->debug->msg($sth->debug->get_msgs());

			####### write log
			new Log(array(
				'action' => 'create',
				'component' => 'Extensions',
				'message' => "New extension '".$dir_name."' inserted. (sync)",
			));

			# reload extension:
			$extension = new extension(array(
				name => $dir_name
			));
			$no_delete_extension[]=$extension->id;
		} # INSERT
		else {
			$no_delete_extension[]=$extension->id;
			####### WRITE LOG
			new Log(array(
				'action' => 'update',
				'component' => 'Extensions',
				'message' => "New extension '".$dir_name."' updated. (sync)",
			));
		}

		####### 2. search for CONFIG FILE

		$conf_found = $extension->load_extension_config();

		# now all config variables are in array $extension->CONF
		#printr($conf_found);

		####### 3. UPDATE extension record
		# 3.A config file found => we have official ext, overwrite all record values with config file values
		if($conf_found) {
			$sql = $site->db->prepare("UPDATE extensions SET path=?, is_official=?, title=?, description=?, author=?, version=?, version_date=?, icon_path=?, min_saurus_version=?, min_saurus_modules=?, is_downloadable=? WHERE name=?",
				$dir_relative_path,
				'1',
				$extension->CONF['title'],
				$extension->CONF['description'],
				$extension->CONF['author'],
				$extension->CONF['version'],
				$extension->CONF['version_date'],
				$extension->CONF['icon_path'],
				$extension->CONF['min_saurus_version'],
				$extension->CONF['min_saurus_modules'],
				($extension->CONF['is_downloadable']=='1'?'1':'0'),
				$extension->name
			);
		}
		# 3.B config file NOT found => we have custom ext, dont overwrite user defined record values
		else {
			$sql = $site->db->prepare("UPDATE extensions SET path=?, is_official=? WHERE name=?",
				$dir_relative_path,
				'0',
				$extension->name
			);
		} # official or custom ext
		$sth = new SQL($sql);
		#print($sql);
		$site->debug->msg($sth->debug->get_msgs());


		####### 4. CREATE ADMIN-PAGES
		if(count($extension->CONF['adminpages'])>0){
			#printr($extension->CONF['adminpages']);

			## get minimum sorteering from main menu "Extensions"
			$sql = $site->db->prepare("SELECT MIN(sorteering) AS min_sorteering FROM admin_osa WHERE parent_id=?", '86');
			$sth = new SQL($sql);
			#print($sql);
			$site->debug->msg($sth->debug->get_msgs());
			$min_sorteering = $sth->fetchsingle();
			$min_sorteering = intval($min_sorteering)-1;

			## find new ID, must be 1000...-> 
			$sql = $site->db->prepare("SELECT MAX(id) FROM admin_osa WHERE id >= 1000"); 
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			$max_id = $sth->fetchsingle();
			if($max_id) {
				$max_id++;
			}
			else {
				$max_id = 1000;
			}


			foreach($extension->CONF['adminpages'] as $adminpage) {
				## parent ID is hardcoded "86": Extensions
				#check if adminpage exists:

				$sql = $site->db->prepare("SELECT id FROM admin_osa WHERE eng_nimetus=? AND parent_id=? AND extension=?", $adminpage["name"], '86', $extension->name);
				$sth = new SQL($sql);
				$adminpage_id = $sth->fetchsingle();

				## if not found => INSERT
				if(!$adminpage_id) {
					$sql = $site->db->prepare("INSERT INTO admin_osa (id, parent_id, sorteering, eng_nimetus, fail, moodul_id, extension) VALUES (?, ?, ?, ?, ?, ?, ?)",
						$max_id,
						86,
						$min_sorteering,
						$adminpage["name"],
						$site->CONF['wwwroot'].'/'.$extension->path.$adminpage["file"],
						0,
						$extension->name
					);
					$max_id++;
					#######write log
					new Log(array(
						'action' => 'create',
						'component' => 'Extensions',
						'message' => "Extension '".$extension->name."': new admin-page '".$adminpage["name"]."' inserted (sync)",
					));
				}
				## if found => UPDATE
				else {
					$sql = $site->db->prepare("UPDATE admin_osa SET eng_nimetus=?, fail=?, extension=?  WHERE id=?",
						$adminpage["name"],
						$site->CONF['wwwroot'].'/'.$extension->path.$adminpage["file"],
						$extension->name,
						$adminpage_id
					);
					$no_delete_list[]=$adminpage_id;
				}
				$sth = new SQL($sql);
				if(!$adminpage_id){
					$no_delete_list[]=$sth->insert_id;
				}
				#print($sql);
				$site->debug->msg($sth->debug->get_msgs());

				#######################
				# save system word to group "admin":
				include_once($class_path.'adminpage.inc.php');
				
				// get admin section key (should always be 12, but in any case)
				$sql = "select sst_id from sys_sona_tyyp where voti = 'admin'";
				$result = new SQL($sql);
				$sst_id = $result->fetchsingle();
				
				// insert the same translation for every active language
				$sql = 'select distinct glossary_id as keel_id from keel where on_kasutusel = 1';
				$result = new SQL($sql);
				while($row = $result->fetch('ASSOC'))
				{
					save_systemword(array(
						'sysword' => $adminpage['name'],
						'translation' => $adminpage['name'],
						'lang_id' => $row['keel_id'],
						'sst_id' => $sst_id,
					));
				}
			} # loop over adminpages

			if(!empty($extension->name)){
				new sql("delete from admin_osa where extension='".$extension->name."' and id not in (".implode(",",$no_delete_list).")");
			}
		} # if adminpages found

		####### 5. CREATE TEMPLATES
		
		if(count($extension->CONF['templates'])>0){
			#printr($extension->CONF['templates']);

			$sql = $site->db->prepare("SELECT max(ttyyp_id) FROM templ_tyyp WHERE ttyyp_id >= 1000 AND ttyyp_id < 2000 OR ttyyp_id >= 2100"	); 
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			$max_id = $sth->fetchsingle();
			if($max_id) { 	$max_id++;	}
			else { 	$max_id = 1000; }

			############## loop over templates
			foreach($extension->CONF['templates'] as $template) {
				#check if template exists:
				
				$template['op'] = translate_ee($template['op']);
				
				/* get op: dont overwrite existing values */
				$sql=$site->db->prepare("SELECT op FROM templ_tyyp WHERE op=? AND nimi<>?;", $template['op'], $template['name']);
				$sth = new SQL($sql);
				$op_found = $sth->fetchsingle();
				if($op_found) {
					$template['op'] = ''; # dont overwrite
				}
				$sql = $site->db->prepare("SELECT ttyyp_id FROM templ_tyyp WHERE nimi=? AND extension=?", $template["name"], $extension->name);
				$sth = new SQL($sql);
				$template_id = $sth->fetchsingle();

				## if not found => INSERT
				if(!$template_id) {
					$sql = $site->db->prepare("INSERT INTO templ_tyyp (ttyyp_id, nimi, templ_fail, on_page_templ, on_nahtav, extension, op, is_readonly, is_default, preview, preview_thumb) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
						$max_id,
						$template['name'],
						'../../../'.$extension->path.$template['file'],
						($template['is_page'] ? 1 : 0),
						($template['is_visible'] ? 1 : 0),
						$extension->name,
						$template['op'],
						($template['is_readonly'] ? 1 : 0),
						($template['is_default'] ? 1 : 0),
						$template['preview'],
						$template['preview_thumb']
					);
					$max_id++;
					#######write log
					new Log(array(
						'action' => 'create',
						'component' => 'Extensions',
						'message' => "Extension '".$extension->name."': new template '".$template["name"]."' inserted (sync)",
					));
				}
				## if found => UPDATE
				else {
					$sql = $site->db->prepare("UPDATE templ_tyyp SET nimi=?, templ_fail=?, on_page_templ=?, on_nahtav=?, extension=?, op=".($template['op'] ? "'".mysql_real_escape_string($template['op'])."'" : 'op' ).", is_readonly=?, is_default = ?, preview = ?, preview_thumb = ? WHERE ttyyp_id=?",
						$template['name'],
						'../../../'.$extension->path.$template['file'],
						($template['is_page'] ? 1 : 0),
						($template['is_visible'] ? 1 : 0),
						$extension->name,
						($template['is_readonly'] ? 1 : 0),
						($template['is_default'] ? 1 : 0),
						$template['preview'],
						$template['preview_thumb'],
						$template_id
					);
				}
				$sth = new SQL($sql);
				#print($sql.'<br />');
				$site->debug->msg($sth->debug->get_msgs());

			} # loop over templates

		} # if templates found

		####### 6. CREATE SYSTEMWORD GROUP in GLOSSARY

		# check if systemword group with that name exists
		$sql = $site->db->prepare("SELECT sst_id FROM sys_sona_tyyp WHERE voti=?", $extension->name); 
		$sth = new SQL($sql);
		$sst_id = $sth->fetchsingle();

		# UPDATE glossary group name
		if($sst_id) {
			$sql = $site->db->prepare("UPDATE sys_sona_tyyp SET voti=?, nimi=?, extension=? WHERE sst_id=?", 
				$extension->name,
				($extension->CONF['title'] ? $extension->CONF['title'] : $extension->name),
				$extension->name,
				$sst_id
			);
			$sth = new SQL($sql);
		}
		# INSERT new glossary group
		else {
			# find new sst ID (must be >= 100; 0...100 are reserved for Saurus internal use)
			$sql = $site->db->prepare("SELECT MAX(sst_id) FROM sys_sona_tyyp"); 
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());	
			$max_id = $sth->fetchsingle();
			if($max_id >= 100) { 	$max_id++;	}
			else { 	$max_id = 100; }

			$sql = $site->db->prepare("INSERT INTO sys_sona_tyyp (sst_id, voti, nimi, extension) VALUES (?,?,?,?)", 
				$max_id,
				$extension->name,
				($extension->CONF['title'] ? $extension->CONF['title'] : $extension->name),
				$extension->name
			);
			$sth = new SQL($sql);

			####### write log
			new Log(array(
				'action' => 'create',
				'component' => 'Extensions',
				'message' => "Extension '".$extension->name."': new glossary group '".$extension->CONF['title']."' inserted (sync)",
			));
		}
		########## 7. import dictionary from language files
		if($extension->name) 
		{
			/* get site's languages and encodings */
			$languages = array();
			
			$sql = 'select distinct glossary_id as keel_id, encoding from keel where on_kasutusel = 1;';
			$result = new SQL($sql);
			while($lang = $result->fetch('ASSOC'))
			{
				if(file_exists($site->absolute_path.'extensions/'.$extension->name.'/lang/'.$lang['encoding'].'/language'.$lang['keel_id'].'.csv')) import_dict_from_file($site->absolute_path.'extensions/'.$extension->name.'/lang/'.$lang['encoding'].'/language'.$lang['keel_id'].'.csv');	
			}
		}

		####### 4. RUN INSTALL/UPDATE SQL FILES
		# 8A. if INSTALLING new extension then run all *.sql files in extension folder "install/"
		if($is_install){

			$ext_install_path = $dir_absolute_path.'install/';
			if(is_dir($ext_install_path)){ # if install/ exists, Bug #2442
			$handle = opendir($ext_install_path);
			while (false !== ($dir = readdir($handle))) {
				if (is_file($file = $ext_install_path.$dir) && $dir != '.' && $dir != '..' && $dir != 'CVS') {
					$tmp_parts = pathinfo($ext_install_path.$dir);
					## if file extension is "sql" (case insensitive) => run sql files
					if(strtoupper($tmp_parts['extension']) == 'SQL') { 
						# 
						if ($fd = fopen($file, "r")) {
							$sql = fread ($fd, filesize($file));
							fclose ($fd);

							# if there is smth in file
							if($sql) {
								$pieces = split_sql_file( $sql,';' );
								// now $pieces is an array of all sql directives to launch
								foreach ($pieces as $query)	{
									$sth = new SQL($query);
									if ($sth->error) { print "<font color=red>Error: ".$sth->error."</font><br />";}
									$i++;
								}
							} # data found
						} # open SQL file
						else {
							echo "<font color=red>Can't open data file \"<b>".$filename."</b>\" - access denied</font><br />";
						} # cant open sql file
					} # if sql file
				} # file
			} # while
			closedir($handle);
			} # if dir exists		

		} # is install
		# 8B. if UPDATING existing extension then run all *.sql files in extension folder "install/updates/"
		else {
		
		} # is update


		############# 9. CHECK DEPENDENCIES
		$extension->check_dependencies();

	}

	// Delete non-existing extensions

	if(is_array($no_delete_extension)){

		$sth = new SQL("select name from extensions where extension_id not in (".implode(",",$no_delete_extension).")");
			while($r = $sth->fetch("ASSOC")){
				$extension = new extension(array(
					name => $r['name']
				));
				$extension->uninstall();
			}
	}

	############ / loop over extension directories


}
# / sync_extensions
##########################

##########################
# FUNCTION deldir

# $dir_deleted = deldir($dir);
function deldir($dir) {
   $dh=opendir($dir);
   while ($file=readdir($dh)) {
       if($file!="." && $file!="..") {
           $fullpath=$dir."/".$file;
           if(!is_dir($fullpath)) {
               unlink($fullpath);
           } else {
               deldir($fullpath);
           }
       }
   }
   closedir($dh);
  
   if(rmdir($dir)) {
       return true;
   } else {
       return false;
   }
}
# / FUNCTION deldir
##########################

##########################
# FUNCTION get_extensions
function get_extensions($mode = 'DB', $is_active = false, $by_name = '')
{
	global $site;
	static $ext_mem = array();
	
	$extensions = array();
	
	/* check if extensions are cached */
	if($by_name || !is_array($site->cash(array('klass' => 'GET_EXTENSIONS', 'kood' => 'ALL_EXTENSIONS_INFO'))))
	{
		if($by_name && $ext_mem[$by_name])
		{
			return array($by_name => $ext_mem[$by_name]);
		}
		
		/* method 1: get from database */
		if($mode == 'DB')
		{
			$where = 'where 1';
			($is_active ? $where .= ' and is_active = 1' : '');
			($by_name ? $where .= $site->db->prepare(' and name = ?', $by_name) : '');
			$extensions_in_db = new SQL('select * from extensions '.$where.';');
			while($extension = $extensions_in_db->fetch('ASSOC'))
			{
				$extensions[$extension['name']] = $extension;
				$extensions[$extension['name']]['fullpath'] = $site->absolute_path.$extension['path'];
				
				if($by_name)
				{
					$ext_mem[$by_name] = $extensions[$extension['name']];
				}
			}
		}
		/* method 2: get from filesystem */
		elseif($mode == 'FS')
		{
			/* TODO */
		}
		/* cache all extensions */
		$site->cash(array(klass => 'GET_EXTENSIONS', 'kood' => 'ALL_EXTENSIONS_INFO', 'sisu' => $extensions));
	}
	/* the extensions are already loaded */
	else 
	{
		$extensions = $site->cash(array('klass' => 'GET_EXTENSIONS', 'kood' => 'ALL_EXTENSIONS_INFO'));
		if($is_active)
		{
			foreach($extensions as $key => $extension) if($extension['is_active'] === 0) unset($extensions[$key]);
		}
	}

	return $extensions; 
}

# /FUNCTION get_extensions
##########################

function &load_extension_config(&$extension)
{
	global $site;
	$EXTENSION = array();

	//IF the extension is not in memory, we read it in there
	if(!is_array($site->cash(array('klass' => 'GET_EXTENSIONS', 'kood' => $extension['fullpath'])))){

		if(file_exists($extension['fullpath'].'/extension.config.php'))
		{
			include($extension['fullpath'].'/extension.config.php');
			$site->cash(array(klass => 'GET_EXTENSIONS', 'kood' => $extension['fullpath'], 'sisu' => $EXTENSION));
		}

	}else{
		//When in memory, re read it from the array
		$EXTENSION = $site->cash(array('klass' => 'GET_EXTENSIONS', 'kood' => $extension['fullpath']));

	}

	/* add some shtuff */
	$EXTENSION['wwwroot'] = $site->CONF['wwwroot'];
	$EXTENSION['hostname'] = $site->CONF['hostname'];
	$EXTENSION['protocol'] = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://');

	return $EXTENSION;
}

/**
 * Returns structured array of extension templates
 *
 * @param string $sql
 * @return array
 */
function get_extension_templates($sql)
{
	global $site;
	
	$template_data = array();

	$sth = new SQL ($sql);

	# if found templates
	if($sth->rows)
	{
		####### gahter templates info
		while ($templ=$sth->fetch())
		{
			$ext_templ_arr[$templ['extension']][] = $templ;
		}

		#####################################
		# load all EXTENSION CONFIGS
		$ext_path = $site->absolute_path.'extensions/';
		$handle = opendir($ext_path);
		while (false !== ($dir = readdir($handle)))
		{
			if (is_dir($ext_path.$dir) && $dir != '.' && $dir != '..' && $dir != 'CVS')
			{
				$ext_dir = $dir."/";
				$file = $ext_path.$ext_dir.'extension.config.php';
				$ext_name = substr($ext_dir,0,-1);

				if(file_exists($file))
				{
					include($file);
					
					# if found templates for this extension => show extension templates group
					if(sizeof($ext_templ_arr[$ext_name]) > 0) { 
						foreach($ext_templ_arr[$ext_name] as $templ){
							$template_data[$EXTENSION['title']][$templ['ttyyp_id']] = $templ;
						}
					} # if found templates for this extension
				} # if config file exists
			} # if
		} # while
		closedir($handle);
		# / load all EXTENSION CONFIGS
		#####################################

	} # if found templates

	return $template_data;
}

/**
* print_extension_templates (public)
* 
* Reads directory "extensions/" and displays template groups for each one.
* Prints out selectbox content: <option .. > rows
* 
* Return record data array of curretly selected template
*
* @package CMS
* 
*/
function print_extension_templates($sql,$selected_value){
	global $site;
	
		$ext_templ_arr = get_extension_templates($sql);
		
		foreach($ext_templ_arr as $ext_title => $extension)
		{ 
			print '<optgroup label="'.$ext_title.'">';

			foreach($ext_templ_arr[$ext_title] as $templ_id =>  $templ){
				if ($templ['ttyyp_id'] == $selected_value) {$ttyyp = $templ;}
				print "<option value=\"".$templ['ttyyp_id']."\"".($selected_value==$templ['ttyyp_id']?" selected":"").($templ['ttyyp_id'] == $objekt->all['ttyyp_id'] || $templ['ttyyp_id'] == $objekt->all['page_ttyyp_id'] ? " style=\"color: #a7a6aa;\"" : "").">";
				print $templ['nimi'];
				print "</option>\n";
			}
			print '</optgroup>';
		} # if found templates for this extension

	### return selected template array
	return $ttyyp;
}
# / FUNCTION print_extension_templates
####################################


//unpacks a specified zip file and looks for specified config file within its folders. If found one it will check if it's possible to add it into CMS by either adding it or changing the old extension and proceeds to do so if there are no errors. 

class extension_upload
{

	var $error_message;
	var $tmp_location;
	var $extension_folder;
	var $extension_name;
	var $extension_dir_structure;
	var $config_location;
	var $extensions_folder;
	var $overwrite_extension;
	var $file_chmod;
	var $dir_chmod;

	//we give few variables predefined values. 

	function extension_upload(){
		$this->overwrite_extension = false;
		$this->dir_chmod=0775;
		$this->files_chmod=0775;
	}

	//We check if there is file  in the $farray_name named array in $_FILES superglobal. If so, we unpack the zip into a temp folder.

	function unpack_extension($farray_name){

		if(file_exists($_FILES[$farray_name]['tmp_name'])&&$_FILES[$farray_name]['error']==0&&$_FILES[$farray_name]['size']>0){


			//we unpack the extension to a temp folder.

			$zip = new archive();
			$zip->unzip($_FILES[$farray_name]['tmp_name'],$this->tmp_location,false);
			if($zip->error()){
				$this->create_error($zip->error());
			}

		}else{
				$this->create_error("No file found under \$_FILES['".$farray_name."']!");

		}
	}

	/*
	We are looking for a file in the folder . This script looks for for 2 variations of extension folders.
	First one is where the extension is inside a folder. 

	extension_name
		content_templates
		page_templates
		extension.config.php


	Second one is where there is no folder where they are in:

	content_templates
	page_templates
	extension.config.php
	*/

	function find_file($file_name){

		if(!$this->error()){

		$path=$this->tmp_location;

			if (is_dir($path)) {
				if ($handle = opendir($path)) {

					while (false !== ($file = readdir($handle))) {
						if(!$this->error()){
							if($file != '.' && $file != '..') {

								if (is_dir($path."/".$file) && !is_link($path."/".$file)) {

									$this->extension_folder=$path."/".$file;
									//we count the number of folders. If we have a first option we only want to see 1 folder under which the extension belongs. 
									$counter++;
								}elseif(is_file($path."/".$file)){

								//If we have a match here then the extension is packed together using the second method.

								if($file == $file_name){
									$this->config_location=$path."/".$file;
									$this->extension_dir_structure = 2; // 2 means that the extension  is not inside one folder, but just packed together
								}

								}
						}
						}
					}

					//no config location means that the second method was not used and we have to go in a folder. There should only be one of it. After going in we just look for files (we dont go into directories) with the specific name. If we dont find any, then the archive is of unspecified structure and so we fail.
					if($this->config_location == ""){
						
						if($counter == 1){

							if ($handle = opendir($this->extension_folder)) {
								while (false !== ($file = readdir($handle))) {

									if(!$this->error()){

										if($file != '.' && $file != '..') {


											if(is_file($this->extension_folder."/".$file)){
												if($file == $file_name){

													$this->config_location = $this->extension_folder."/".$file;
													$this->extension_dir_structure = 1; // 1 means that the extension is contained inside a folder

												}

											}

										}

									}

								}

								if($this->config_location == ""){
									$this->create_error("Unknown extension folder structure. Not option 2.");
								}
							}

						
						}else{

							$this->create_error("Unknown extension folder structure. Not option 1.");

						}

					}
				}
			}
		}

		if(!$this->error()){

			return $this->config_location;
		}else{
			return false;
		}

	}

	//We check if the configuration file is where it is supposed to be and if so, include it, so the variable $EXTENSION will be initialized. After that we check that the name variable is valid, if so, we move on to check if there is already a folder with the said name unders extensions. If no folder, we create and copy the content of our tmp folder into the new extension folder. If there already is an extension by that name we check the overwrite rights and either copy it or fail with an error message. 


	function validate_extension(){
		if(!$this->error()){
			if(!empty($this->config_location)&&file_exists($this->config_location)){
				include($this->config_location);

				if(preg_replace("/[^a-zA-Z0-9\-._]+/","_",$EXTENSION['name']) != $EXTENSION['name']){
					$this->create_error("Extension name is not valid. Your extension.config.php \$EXTENSION['name'] variable is '".$EXTENSION['name']."' while closest validated name would be '".preg_replace("/[^a-zA-Z0-9\-._]+/","_",$EXTENSION['name'])."'");
				}else{
					$this->extension_name=$EXTENSION['name'];

					//We check if the folder already exists
					$path=$this->extensions_folder."/".$this->extension_name;

					if(is_dir($path)){

						//do we have permission to overwrite?

						if($this->overwrite_extension){

							if(is_writable($path)){
								if($this->extension_dir_structure == 1){
									$this->full_copy($this->extension_folder,$path);
								}else{
									$this->full_copy($this->tmp_location,$path);
								}
							}else{
								$this->create_error("There is already a folder called '".$this->extension_name."' under the extension folder, but it has no write permissions.");
							}

						}else{

							$this->create_error("There is already a folder called '".$this->extension_name."' under the extensions folder and you gave no permission to overwrite it.");
						}

					}else{
						//No extension directory means we need to make one.

						if(mkdir( $path)){
							chmod($path,$this->dir_chmod);
								if($this->extension_dir_structure == 1){
									$this->full_copy($this->extension_folder,$path);
								}else{
									$this->full_copy($this->tmp_location,$path);
								}
						}else{
							$this->create_error("Problem creating folder '".$path."'. Please check writing permissions on the parent folder");
						}

					}

				}

			}else{

				$this->create_error("Problems finding the extension configration file at '".$this->config_location."'.");

			}

			if(!$this->error()){
				return true;
			}else{
				return false;
			}
		}
	}


	//If there is an error to store, we do it here. 

	function create_error($error_description){

		if(!empty($error_description)){

			$this->error_message = $error_description;

			new Log(array(
				'component' => 'Extensions',
				'type' => 'ERROR',
				'message' => $error_description,
			));

		}

	}


	//function to check for errors, if no errors, it returns false. If error, then returs the description

	function error(){

		if($this->error_message !=""){
			return $this->error_message;
		}else{
			return false;
		}
	}


	//does a recursive source to target file copy. creates all necessary directories. Is supposed to skip links and stick to folders and files.
	
	function full_copy( $source, $target )
	{
		if ( is_dir( $source ) && !is_link($source))
		{
			if(!is_dir($target)){
				if(mkdir( $target )){
					chmod($target,$this->dir_chmod);
				}else{
					$this->create_error("Unable to create '".$target."'. Please check parent folder write permissions");
				}
			}
		
			$d = dir( $source );
		
			while ( FALSE !== ( $entry = $d->read() ) )
			{
				if ( $entry == '.' || $entry == '..' )
				{
					continue;
				}
			
				$Entry = $source . '/' . $entry;		   
				if ( is_dir( $Entry ) )
				{
					$this->full_copy( $Entry, $target . '/' . $entry );
					continue;
				}
				// unlink existing files before copying
				if(file_exists($target . '/' . $entry)) unlink($target . '/' . $entry);
				if(copy( $Entry, $target . '/' . $entry )){
				chmod($target. '/' . $entry,$this->files_chmod);
				}else{
				$this->create_error("Unable to create '".$target.'/' . $entry."'. Please check folder write permissions");
				}
			}
	
			$d->close();
		}elseif(!is_link($source))
		{
			// unlink existing files before copying
			if(file_exists($target . '/' . $entry)) unlink($target . '/' . $entry);
			if(copy( $source, $target )){
			chmod($target,$this->files_chmod);
			}else{
				$this->create_error("Unable to create '".$target."'. Please check folder write permissions");
			}
		}
	}

}


//After confirming that the extension is downloadable, packs the extension into a zip archive and offers it for download.

class extension_download
{

	var $error_message;
	var $web_folder;
	var $extensionInfo;
	var $extensionID;


	function extension_download($id){
		$this->extensionID=$id;
	}


	//we check from the database if the extension is downloadable

	function validate_download(){

		//check if the ID exists

		if(is_numeric($this->extensionID)){
		$sql = "SELECT * FROM extensions where extension_id='".$this->extensionID."'";

		$sth = new SQL($sql);

		//if there are results, make sure is_downladable is set to 1 then store the extension data, otherwise fail.

			if($sth->rows == 1){

				$r=$sth->fetch('ASSOC');

					//We get the absolute path to the config file and if it's there and readable, we include it and check one of it's variables. 

					$conf_file=$this->web_folder.$r['path']."extension.config.php";
					if(is_file($conf_file)&&is_readable($conf_file)){
						
						include_once($this->web_folder.$r['path']."extension.config.php");

						if($EXTENSION['is_downloadable']=='1'){
							$this->extensionInfo = $r;
						}else{
							$this->create_error('Extension with the specified ID is not meant to be downloadable.');
						}

					}else{
						$this->create_error('Unable to find an extension.config.php in the folder specified to the extension');
					}


			}else{
				$this->create_error('Unable to find an extension with specified ID');
			}
		}else{
				$this->create_error('Incorrect extension ID');
		}
	}


	//we download the extension

	function download_extension(){
	global $site;
		//we make sure there are no errors

		if(!$this->error()){
			//we verify that there is extension information we can use
			if(is_array($this->extensionInfo)){


				//check if the folder exists
				if(is_dir($this->web_folder.$this->extensionInfo['path'])){

					//create a random temporary file name

					$tmpzip=$site->absolute_path."shared/pclzip_".time()."_".rand(1,837838).".zip";
					$folder_to_zip=$this->web_folder.$this->extensionInfo['path'];

					//we zip the file together, PCLZIP_OPT_REMOVE_PATH is meant to remove the un-necessary path names


					  $archive = new PclZip($tmpzip);
					  $v_list = $archive->create($this->web_folder.$this->extensionInfo['path'],
												  PCLZIP_OPT_REMOVE_PATH, $this->web_folder."extensions/");
					  if ($v_list == 0) {
						die("Error : ".$archive->errorInfo(true));
					  }

					//we create a zip name from the title and pass the file to the user after which we delete it. 

					$zipname=preg_replace("/[^a-zA-Z0-9\-._]+/","_",$this->extensionInfo['title']).".zip";


						header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Length: ' . filesize($tmpzip));
						header('Content-Disposition: attachment; filename=' . basename($zipname));
						readfile($tmpzip); 
						unlink($tmpzip);
						exit;
						
				}else{
					$this->create_error("Extension with ID '".$this->extensionID."' is missing");
				}

			}else{
				$this->create_error("Missing extension details.");
			}
		}
	}


	//If there is an error to store, we do it here. 

	function create_error($error_description){

		if(!empty($error_description)){

			$this->error_message = $error_description;

			new Log(array(
				'component' => 'Extensions',
				'type' => 'ERROR',
				'message' => $error_description,
			));

		}

	}


	//function to check for errors, if no errors, it returns false. If error, then returs the description

	function error(){

		if($this->error_message !=""){
			return $this->error_message;
		}else{
			return false;
		}
	}

}
