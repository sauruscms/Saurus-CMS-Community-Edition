<?php

class Update
{
	public $lastUpdate = 479;
	public $updateTo = 0;
	private $updates = 0;
	private $ln = NULL;
	private $cli = false;
	
	public function __construct()
	{
		$sql = "SELECT description FROM version WHERE version_nr = '4.7.FINAL'";
		$result = new SQL($sql);
		$this->lastUpdate = (int)$result->fetchsingle();
		
		if(!$this->lastUpdate) $this->lastUpdate = 479;
		
		$this->cli = php_sapi_name() == 'cli' ? true : false;
		$this->ln = $this->cli == 'cli' ? "\n" : '<br>';
	}
	
	public function scanUpdates()
	{
		global $site;
		
		$this->updates = glob($site->absolute_path.'admin/updates/update_*.php');

		foreach ($this->updates as $update)
		{
			$update_nr = str_replace($site->absolute_path.'admin/updates/update_', '', $update);
			$update_nr = str_replace('.php', '', $update_nr);
			
			if($update_nr > $this->updateTo) $this->updateTo = $update_nr;
			
			include_once($update);
		}
	}
	
	public function runUpdates()
	{
		$this->scanUpdates();
		
		$from = $from ? $from : $this->lastUpdate + 1;
		
		if($from > $this->updateTo)
		{
			$this->str('No new updates.');
			return;
		}
		else 
		{
			$this->str('Running updates '.$from.' to '.$this->updateTo.' ...');
		}
		
		for($i = $from; $i <= $this->updateTo; $i++)
		{
			$update_function = 'up_'.$i;
			
			if(function_exists($update_function))
			{
				$update_function();
			}
			else 
			{
				$this->str('Update '.$i.' function not found');
			}
			
			new Log(array(
				'action' => 'update',
				'message' => 'Update '.$i.' done.',
			));
			
			if($this->lastUpdate == 479)
			{
				$this->lastUpdate = 480;
				
				$sql = "SELECT description FROM version WHERE version_nr = '4.7.FINAL'";
				$result = new SQL($sql);
				
				if($result->rows)
				{
					$sql = "UPDATE version SET install_date = NOW(), description = ".(int)$this->lastUpdate." WHERE version_nr = '4.7.FINAL'";
					$result = new SQL($sql);
				}
				else 
				{
					$sql = "INSERT INTO version SET version_nr = '4.7.FINAL', release_date = '2011-05-20', install_date = NOW(), description = 480";
					$result = new SQL($sql);
				}
			}
			else 
			{
				$this->lastUpdate = $i;
				$sql = "UPDATE version SET install_date = NOW(), description = ".(int)$this->lastUpdate." WHERE version_nr = '4.7.FINAL'";
				$result = new SQL($sql);
			}
			
			$this->str('Update '.$i.' done.');
		}
	}
	
	public function runVersionUpdates($updates)
	{
		global $class_path, $site;
		
		include_once($class_path.'install.inc.php');
		
		global $CONF, $FDAT, $conn, $default_data_files, $install, $skip_html;
		
		$default_data_files = $updates;
		$install = 0;
		$CONF = ReadConf();
		$FDAT = $site->fdat;
		$skip_html = $this->cli;
		$conn = $site->db;
		
		run_dumpfile();
	}
	
	public function importGlossaries()
	{
		global $class_path, $site;
		
		include_once($class_path.'install.inc.php');
		
		###############
		# lang file import: importida ainult need keeled, mis saidis aktiivsed
	
		# get languages in use
		$sqlK = "select distinct b.glossary_id as keel_id, b.encoding as encoding from keel as a left join keel as b on a.keel_id = b.glossary_id where b.on_kasutusel = '1'";
		$sthK = new SQL($sqlK);
	
		###############
		# loop over active languages
		while ($keel = $sthK->fetch())
		{
			# get site encoding, default is UTF-8 if not set
			$lang_encoding = $keel['encoding'] ? strtoupper($keel['encoding']) : "UTF-8";
	
			# file = admin/updates/language0.csv	
			$file = "admin/updates/".$lang_encoding."/language".$keel['keel_id'].".csv";
	
			# kui leidub selle keele keelefail
			if(file_exists($file)) {
				$this->str($file."'...");
	
				$one_lang_error = import_langfile($file,$keel);
				if(!$one_lang_error) { # import OK
					$lang_error .= $one_lang_error;
				}
			} 	
		}
		# /  loop over active languages
		###############		
	}
	
	public function synchroniseExtensions()
	{
		global $class_path;
		
		include_once($class_path.'extension.class.php');
		
		sync_extensions();
	}
	
	public function clearCaches()
	{
		global $class_path;
		include_once($class_path.'adminpage.inc.php');
		
		new SQL("DELETE FROM cache WHERE url <> ''");
		
		clear_template_cache($class_path.'smarty/templates_c/');
	}
	
	private function str($string)
	{
		echo $string.$this->ln; 
		flush();
	}
}
