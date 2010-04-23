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



class archive
{

	var $error_message;

	//Unpacks a zip archive to destination.

	function unzip($zipfile,$destination_dir = false,$delete_old_dir = false){
		
		//We check if the previous folder needs to be removed. If so, we do it with deltree() function that recursively deletes all files and then the empty folders from the target folder. if no destination_dir, then ignore this section. 

		if($destination_dir&&$delete_old_dir){

			if(file_exists($destination_dir)){
				if(!is_writable($destination_dir)){
					$this->create_error("Error in deleting the old directory. Missing write permissions in '".$destination_dir."'");
				}else{
					$this->deltree($destination_dir);
				}
			}

		}

		//if destination url, we need to check if it's there and writable, if now, we are going to make it. This script is meant to make the final directory, it will not create the necessary tree up the the folder. 

		if(!$this->error()&&$destination_dir){

			if(file_exists($destination_dir)){
				if(!is_writable($destination_dir)){
					$this->create_error("Missing write permissions in '".$destination_dir."'");
				}
			}else{
				if(!mkdir($destination_dir, 0775)){
					$this->create_error("Unable to create directory '".$destination_dir."'. Check writing permissions.");
				}
			}
		}

		//check if the archive file exists and is readable, then depending on destination_dir either just unpack it or unpack it into the destination_dir folder.

		if(!$this->error){
			if(file_exists($zipfile)){

				if(is_readable($zipfile)){
				 $archive = new PclZip($zipfile);
					 if($destination_dir){
						if ($archive->extract(PCLZIP_OPT_REPLACE_NEWER,PCLZIP_OPT_PATH, $destination_dir, 
											  PCLZIP_OPT_SET_CHMOD, 0777) == 0) {
							$this->create_error("Error : ".$archive->errorInfo(true));
						}else{
							return true;
						}
					 }else{
						if ($archive->extract(PCLZIP_OPT_SET_CHMOD, 0777) == 0) {
							$this->create_error("Error : ".$archive->errorInfo(true));
						}else{
							return true;
						}

					 }

				}else{
						$this->create_error("Unable to read ZIP file '".$zipfile."'. Check reading permissions.");
				}

			}else{
					$this->create_error("Missing ZIP file '.$zipfile.'");

			}
		}
		return $error;
	}

	//recursive folder delete. Checks each subfolder and deletes all the files. Will not follow symlinks as it can create some serious havoc with the wrong permissions.

	function deltree($path) {

		if (is_dir($path)) {
			if ($handle = opendir($path)) {
				while (false !== ($file = readdir($handle))) {
					if(!$this->error()){
						if($file != '.' && $file != '..') {
							if (is_dir($path."/".$file) && !is_link($path."/".$file)) {

									//found a folder, time to go in. 

									$this->deltree( $path . "/" . $file);

									//if no errors from the trip to the sub-folder, then it's time to remove it. 
									if(!$this->error()){
										if(!rmdir($path."/".$file)){
										$this->create_error("Error deleting directory '".$path."/".$file."'");
										}
									}
							}else{
									//removing the file
									if(!unlink($path."/".$file)){
										$this->create_error("Error deleting file '".$path."/".$file."'. Check file/parent folder write permissions");
									}
							}
						}
					}
				}
			closedir($handle);
			}
		}
	return;
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