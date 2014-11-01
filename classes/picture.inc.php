<?php


//DEPRECATE!!
function picture_resize( $key, $data, $args=array()) {
	set_time_limit(30);


	global $site;
	global $timer;

	# new by Pavel, image_mode 1 if imagemagick, 0 if gdi
	$mode=$site->CONF['image_mode'];

	$uploaded_file = $data['tmp_name'];
    if($args['file']) $uploaded_file=$args['file'];

	$uploaded_image = $uploaded_file."_image";
    if($args['image']) $uploaded_image=$args['image'];

    $uploaded_thumb = $uploaded_file."_thumb";
    if($args['thumb']) $uploaded_thumb=$args['thumb'];

	$thumb_width = $site->CONF['thumb_width'];
    if($args['thumb_width']) $thumb_width=$args['thumb_width'];

    $image_width = $site->CONF['image_width'];
    if($args['image_width']) $image_width=$args['image_width'];
	if($mode=="imagemagick") {


		$imagemagick_path = $site->CONF['imagemagick_path'];

		$imagemagick_path = trim($imagemagick_path);

		if ($imagemagick_path) {
			if (strstr( PHP_OS, "WIN")) {
				$imagemagick_path = "\"".$imagemagick_path."\"\\";
			} else {
				# slash l�ppu!
				if (!preg_match("/\/$/", $imagemagick_path)) { $imagemagick_path .= "/"; }
				$imagemagick_path = preg_replace("/\s+/", "_", $imagemagick_path);
			}
		}
		$ident_exec = $imagemagick_path."identify ".$uploaded_file;
		$ident_exec = strstr( PHP_OS, "WIN") ? $ident_exec : EscapeShellCmd($ident_exec);

		# old: $fileinfo = system($ident_exec);

		# new by Evgeny:
		ob_start();
		$tmp = system($ident_exec);
		$fileinfo = ob_get_contents();
		ob_end_clean();

		$site->debug->msg("Using Imagemagick for image proccessing.");
		$site->debug->msg("Path to identify executable: ".$imagemagick_path);
		$site->debug->msg("Identify: ".$ident_exec."; ".$fileinfo);
		$site->debug->msg("File info for image: ".$fileinfo."; Got: identify ".$uploaded_file);

		##############################
		# if file identified successfully

		if (preg_match("/\s(\d+)x(\d+)/i", $fileinfo, $matches)) {
			$cur_x = $matches[1];
			$cur_y = $matches[2];
			$site->debug->msg("Found: $cur_x x $cur_y");

			###############################
			# Image is larger than thumb measures - will resize

			if($cur_x>$thumb_width){

				############
				# find image size

				$image_rate = max($cur_x/$image_width,$cur_y/$image_width);
				$site->debug->msg("Image rate is $image_rate");

				$new_x = round($cur_x/$image_rate);
				$new_y = round($cur_y/$image_rate);

				############
				# convert to image size

				$image_exec = $imagemagick_path."convert -size ".$new_x."x".$new_y." $uploaded_file -geometry ".$new_x."x".$new_y." +profile '*' ".$uploaded_image;
				$image_exec = strstr( PHP_OS, "WIN") ? $image_exec : EscapeShellCmd($image_exec);

				$exec = system($image_exec);
				$site->debug->msg("Konverteeri: $image_exec: $exec");
	#print "<br>time after convert:".$timer->get_aeg()." <br>";

				############
				# read image
				if (file_exists($uploaded_image)) {
					$fd = fopen($uploaded_image, "rb");
					$cs_image = fread ($fd, filesize($uploaded_image));
					fclose ($fd);
					#Is unlinked below
					#unlink("$uploaded_file_image");
				} else {$cs_image="";}
				$site->debug->msg("image size: ".strlen($cs_image));

				############
				# find thumb size

				$thumb_rate = max($cur_x/$thumb_width,$cur_y/$thumb_width);
				$site->debug->msg("Thumb rate is $thumb_rate");

				$new_x = round($cur_x/$thumb_rate);
				$new_y = round($cur_y/$thumb_rate);

				############
				# convert to thumbnail size

				if (file_exists($uploaded_image)) {
					//Hack kuna imagemagik konverteerib m�nikord v�iksed pildid valesti
					$thumb_exec = $imagemagick_path."convert -size ".$new_x."x".$new_y." ".$uploaded_image." -geometry ".$new_x."x".$new_y." +profile '*' ".$uploaded_thumb;
					$thumb_exec = strstr( PHP_OS, "WIN") ? $thumb_exec : EscapeShellCmd($thumb_exec);

					$exec = system($thumb_exec);
					//if(!$args['no_unlink']) unlink($uploaded_image);
                    unlink($uploaded_image);
				} else {
					//vana kood
					$thumb_exec = $imagemagick_path."convert -size ".$new_x."x".$new_y." $uploaded_file -geometry ".$new_x."x".$new_y." +profile '*' ".$uploaded_thumb;
					$thumb_exec = strstr( PHP_OS, "WIN") ? $thumb_exec : EscapeShellCmd($thumb_exec);

					$exec = system($thumb_exec);
				}
				$site->debug->msg("$thumb_exec: $exec");

				############
				# read thumb

				if (file_exists($uploaded_thumb)) {
					$fd = fopen($uploaded_thumb, "rb");
					$cs_thumb = fread ($fd, filesize($uploaded_thumb));
					fclose ($fd);
					//if(!$args['no_unlink']) unlink($uploaded_thumb);
                    unlink($uploaded_thumb);
				} else {$cs_thumb="";}
				$site->debug->msg("thumb size: ".strlen($cs_thumb));

			}
			###############################
			# if image is smaller than thumb measures (will not resize)
			else {
				$site->debug->msg("Thumb not resized: ");
				//Read file
				if (file_exists("$uploaded_file")) {
					$fd = fopen("$uploaded_file", "rb");
					$cs_thumb = fread ($fd, filesize("$uploaded_file"));
					fclose ($fd);
					$cs_image = $cs_thumb;
				} else {
					$cs_thumb="";
					$cs_image="";
				}
				$site->debug->msg("If identyfy");
			}
			$site->debug->msg("If data");

			###############################
			# save also original file, if needed

			if ( $site->CONF['original_picture_saved'] == 1 ) {
				############
				# read original image
				if (file_exists($uploaded_file)) {
					$fd = fopen($uploaded_file, "rb");
					$cs_orig = fread ($fd, filesize($uploaded_file));
					fclose ($fd);
				}
			}
			# else set orignal file to empty
			else {
				$cs_orig = "";
			}

			###############################
			# delete uploaded file

			if(!$args['no_unlink']) unlink($uploaded_file);

			###############################
			# return image data

			$img_array = array (
				"thumb" => $cs_thumb,
				"image" => $cs_image,
				"orig" => $cs_orig
			);

			return $img_array;
		}
		##############################
		# if file couldn't be identified

		else {
			$site->debug->msg("File not identified");
			if(!$args['no_unlink']) unlink($uploaded_file);
		}
		return 0;
	} 
	###############################
	# new by Pavel
	# else if we have gdi mode, skip all imagemagic part
		else if ($mode=="gd lib") {

		##############################
		# if file identified successfully
		$imagesize=getimagesize($uploaded_file);

		# if we have JPEG pic and GD functions are ok - proceed
		if ($imagesize[2]==2 AND (function_exists('getimagesize') AND function_exists('imageCreateTrueColor') AND function_exists('imagecreatefromjpeg') AND function_exists('imagecopyresampled') AND function_exists('imagesx') AND function_exists('imagesy'))) {

			$site->debug->msg("Using GD for image proccessing.");

			$cur_x = $imagesize[0];
			$cur_y = $imagesize[1];
			$site->debug->msg("Found: $cur_x x $cur_y");
			
			###############################
			# Image is larger than thumb measures - will resize

			if($cur_x>$thumb_width){
			
				############
				# find image size

				$image_rate = max($cur_x/$image_width,$cur_y/$image_width);
				$site->debug->msg("Image rate is $image_rate");
				
				$new_x = round($cur_x/$image_rate);
				$new_y = round($cur_y/$image_rate);


				############
				# convert to image size
			
				$src_img=imagecreatefromjpeg($uploaded_file);
				$dst_img=imageCreateTrueColor($new_x, $new_y);
				
				imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_x, $new_y, imagesx($src_img), imagesy($src_img));
				imagejpeg($dst_img,$uploaded_image);
				#echo $uploaded_image; exit;
				
				############
				# read image
				if (file_exists($uploaded_image)) {
					#echo "yeahhhoo!";
					$fd = fopen($uploaded_image, "rb");
					$cs_image = fread ($fd, filesize($uploaded_image));
					fclose ($fd);

					#Is unlinked below
					#unlink("$uploaded_file_image");
				} else {$cs_image="";}
				


				$site->debug->msg("Image created: $dst_img");
				$site->debug->msg("image size: ".strlen($cs_image));

				imagedestroy($dst_img);
				imagedestroy($src_img);

				############
				# find thumb size

				$thumb_rate = max($cur_x/$thumb_width,$cur_y/$thumb_width);
				$site->debug->msg("Thumb rate is $thumb_rate");

				$new_x = round($cur_x/$thumb_rate);
				$new_y = round($cur_y/$thumb_rate);

				############
				# convert to thumbnail size
				$src_img=imagecreatefromjpeg($uploaded_file);
				$dst_img=imageCreateTrueColor($new_x, $new_y);

				imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_x, $new_y, imagesx($src_img), imagesy($src_img));

				$site->debug->msg("Thumbnail created: $dst_img");

				imagejpeg($dst_img,$uploaded_thumb);

				############
				# read image
				if (file_exists($uploaded_thumb)) {
					$fd = fopen($uploaded_thumb, "rb");
					$cs_thumb = fread ($fd, filesize($uploaded_thumb));
					fclose ($fd);
					#Is unlinked below
					#unlink("$uploaded_file_image");
				} else {$cs_thumb="";}



				$site->debug->msg("thumb size: ".strlen($cs_thumb));
				imagedestroy($dst_img);
				imagedestroy($src_img);
			} 
			###############################
			# if image is smaller than thumb measures (will not resize)
			else {
				$site->debug->msg("Thumb not resized: ");
				//Read file
				if (file_exists("$uploaded_file")) {
					$fd = fopen("$uploaded_file", "rb");
					$cs_thumb = fread ($fd, filesize("$uploaded_file"));
					fclose ($fd);
					$cs_image = $cs_thumb;
				} else {
					$cs_thumb="";
					$cs_image="";
				}
				$site->debug->msg("If identyfy");
			}
			$site->debug->msg("If data");

			###############################
			# save also original file, if needed

			if ( $site->CONF['original_picture_saved'] == 1 ) {
				
				############
				# read original image
				if (file_exists($uploaded_file)) {
					$fd = fopen($uploaded_file, "rb");
					$cs_orig = fread ($fd, filesize($uploaded_file));
					fclose ($fd);
				}
			}
			# else set orignal file to empty
			else {
				$cs_orig = "";
			}

			###############################
			# delete uploaded file

			if(!$args['no_unlink']) unlink($uploaded_file);

			###############################
			# return image data 

			$img_array = array (
				"thumb" => $cs_thumb,
				"image" => $cs_image,
				"orig" => $cs_orig
			);
			return $img_array;
		}
		##############################
		# if GD couldn't be identified or is not JPEG

		else {
			$site->debug->msg("GD-library not supported or is not JPEG file");
			if(!$args['no_unlink']) unlink($uploaded_file);
		}
		return 0;

	}
}

function get_images($path,$view_path,$get_type=null)
{
	global $site;
	#echo $get_type;
    if(!is_dir($path) )
    {
    	//ei ole kataloog
        return false;
    }
    if(!is_dir($path.'/.gallery_thumbnails'))
    {
        //ei ole thumbnailide katalooma
        return false;
    }
    if(!is_dir($path.'/.gallery_pictures'))
    {
        //ei ole piltide katalooma
        return false;
    }

	//võta folder
	$sql = $site->db->prepare('SELECT objekt_id FROM obj_folder WHERE relative_path LIKE ?', '/'.$view_path);
    $result = new SQL($sql);
	
	//folderi objekti id
    $folder_objekt_id = $result->fetchsingle();
	
	// !TODO only public images are visible to public

	$sql = 'SELECT obj_file.objekt_id, obj_file.filename 
			FROM objekt_objekt, obj_file, objekt 
			WHERE objekt_objekt.objekt_id = obj_file.objekt_id 
			AND objekt_objekt.objekt_id = objekt.objekt_id
			AND objekt_objekt.parent_id = '.(int)$folder_objekt_id.' 
			ORDER BY objekt_objekt.sorteering DESC';
	$result = new SQL($sql);

    //on kataloog ja .gallery_thumbnails ja .gallery_thumbnails on olemas
    $filenames = array();
    while($row = $result->fetch('ASSOC')){
    	$objekt_id = $row['objekt_id'];
		$filenames[$objekt_id] = $row['filename'];
	}

    $images=array();
    $i=0;
    
    
    
    foreach($filenames as $file){
    
        /* if thumbnail and picture file exists and is not 0-bytes count as an image */
    	if(file_exists($path.'/.gallery_thumbnails/'.$file) && filesize($path.'/.gallery_thumbnails/'.$file) && file_exists($path.'/.gallery_pictures/'.$file) && filesize($path.'/.gallery_pictures/'.$file))
        {
	        $images[$i]['thumb']=$view_path.'/.gallery_thumbnails/'.$file;
		    $img_dat = getimagesize($path.'/.gallery_thumbnails/'.$file);
	        $images[$i]['thumb_height']=$img_dat[1];
	        $images[$i]['thumb_width']=$img_dat[0];
	
	        if($get_type == 'first') break;
	
	        $images[$i]['actual_image']=$view_path.'/'.$file;
	        $img_dat=getimagesize($path.'/'.$file);
	        $images[$i]['actual_image_height']=$img_dat[1];
	        $images[$i]['actual_image_width']=$img_dat[0];
	
	        $images[$i]['image']=$view_path.'/.gallery_pictures/'.$file;
		    $img_dat = getimagesize($path.'/.gallery_pictures/'.$file);
	        $images[$i]['image_height']=$img_dat[1];
	        $images[$i]['image_width']=$img_dat[0];
	
	        $images[$i]['filename']=$file;
	
	        $i++;
        }
    }
    
    if($get_type == 'random') return $images[rand(0,sizeof($images)-1)]['thumb'];
    if($get_type == 'first') return $images[0]['thumb'];

    return $images;
}

function generate_images($path,$thumb_width,$pic_width)
{
	global $site;
	
	if(!is_dir($path))
    {
        //ei ole kataloog
        //printr('//ei ole kataloog');
        return false;
    }
    if(!is_dir($path.'/.gallery_thumbnails'))
    {
        if(!mkdir($path.'/.gallery_thumbnails'))
        {
			new Log(array(
				'action' => 'create',
				'type' => 'ERROR',
				'component' => 'Gallery',
				'message' => 'Could not create folder for thumbnails in '.str_replace($site->absolute_path, '', $path),
			));
            return false;
        }
    }
    if(!is_dir($path.'/.gallery_pictures'))
    {
        if(!mkdir($path.'/.gallery_pictures'))
        {
			new Log(array(
				'action' => 'create',
				'type' => 'ERROR',
				'component' => 'Gallery',
				'message' => 'Could not create folder for pictures in '.str_replace($site->absolute_path, '', $path),
			));
            return false;
        }
    }

    //on kataloog ja .gallery_thumbnails ja .gallery_pictures on olemas
    $filenames=array();
    $dir_handle=opendir($path);
    while (false !== ($filename=readdir($dir_handle)))
    {
       if($filename == '.' || $filename == '..') {} else
       {
            if(!is_dir($path.'/'.$filename)) $filenames[]=$filename;
       }
	}
    
    foreach($filenames as $file)
    {
       if(file_exists($path.'/.gallery_thumbnails/'.$file))
       {
       		if (filesize($path.'/.gallery_thumbnails/'.$file))
			{
	           $thumb_data=getimagesize($path.'/.gallery_thumbnails/'.$file);
	           if($thumb_data[0] != $thumb_width) $gen_thumb=true; else $gen_thumb=false;
			}
	      	else $gen_thumb=true;
       }
       elseif(getimagesize($path.'/'.$file)) $gen_thumb=true;
       else $gen_thumb = false;
       
       if(file_exists($path.'/.gallery_pictures/'.$file))
       {
           if (filesize($path.'/.gallery_pictures/'.$file))
           {
	           $image_data=getimagesize($path.'/.gallery_pictures/'.$file);
	           if($image_data[0] != $pic_width) $gen_pic=true; else $gen_pic=false;
	       }
	       else $gen_pic=true;
       }
       elseif(getimagesize($path.'/'.$file)) $gen_pic = true;
       else $gen_pic = false;
       
       //$gen_thumb = true;
       //$gen_pic = true;

       if($gen_pic || $gen_thumb)
       {
            //recreate thumbnail
            $args=array();
            $args['file']=$path.'/'.$file;
            if($gen_thumb)
            {
                $args['thumb']=$path.'/.gallery_thumbnails/'.$file;
                $args['thumb_width']=$thumb_width;
            }
            if($gen_pic)
            {
                $args['image']=$path.'/.gallery_pictures/'.$file;
                $args['image_width']=$pic_width;
            }
            $args['no_unlink']=true;

            //$image_data=picture_resize(0,array(),$args);

            if($gen_thumb)
            {
				$image = new ImageShopper(str_replace($site->absolute_path, '',  $args['file']));
				
				$cur_x = $image->image_src_x;
				$cur_y = $image->image_src_y;
				
				$image_rate = max($cur_x/$args['thumb_width'],$cur_y/$args['thumb_width']);
				
				$new_x = round($cur_x/$image_rate);
				$new_y = round($cur_y/$image_rate);
				
				$image->image_resize = true;
				$image->image_x = $new_x;
				$image->image_ratio_y = true;
				$image->file_auto_rename = false;
				$image->file_overwrite = true;
				$image->process($path.'/.gallery_thumbnails');
				
				//printr($image->log);
				
				if(!file_exists($image->file_dst_pathname))
				{
					new Log(array(
						'action' => 'create',
						'type' => 'ERROR',
						'component' => 'Gallery',
						'message' => 'Could not create thumbnail for '.$path.'/'.$file,
					));
				}
				
				unset($image);
            }
            
            if($gen_pic)
            {
				$image = new ImageShopper(str_replace($site->absolute_path, '',  $args['file']));
				
				$cur_x = $image->image_src_x;
				$cur_y = $image->image_src_y;
				
				$image_rate = max($cur_x/$args['image_width'],$cur_y/$args['image_width']);
				
				$new_x = round($cur_x/$image_rate);
				$new_y = round($cur_y/$image_rate);
				
				$image->image_resize = ($image_rate > 1 ? true : false);
				$image->image_x = $new_x;
				$image->image_ratio_y = true;
				$image->file_auto_rename = false;
				$image->file_overwrite = true;
				$image->process($path.'/.gallery_pictures');
				
				//printr($image->log);
				
				if(!file_exists($image->file_dst_pathname))
				{
					new Log(array(
						'action' => 'create',
						'type' => 'ERROR',
						'component' => 'Gallery',
						'message' => 'Could not create thumbnail for '.$path.'/'.$file,
					));
				}
				
				unset($image);
            }
        }
    }
    return true;

}

/**
 * Class ImageShopper
 *
 * renamed from upload
 * 
 * @version   0.20
 * @author    Colin Verot <colin@verot.net>
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Colin Verot
 */

/**
 * Documentaion is here: http://www.verot.net/res/sources/class.upload.html
 */
class ImageShopper {

    
    /**
     * Uploaded file name
     *
     * @access public
     * @var string
     */
    var $file_src_name;

    /**
     * Uploaded file name body (i.e. without extension)
     *
     * @access public
     * @var string
     */
    var $file_src_name_body;

    /**
     * Uploaded file name extension
     *
     * @access public
     * @var string
     */
    var $file_src_name_ext;

    /**
     * Uploaded file MIME type
     *
     * @access public
     * @var string
     */
    var $file_src_mime;

    /**
     * Uploaded file size, in bytes
     *
     * @access public
     * @var double
     */
    var $file_src_size;

    /**
     * Holds eventual PHP error code from $_FILES
     *
     * @access public
     * @var string
     */
    var $file_src_error;

    /**
     * Uloaded file name, including server path
     *
     * @access private
     * @var string
     */
    var $file_src_pathname;

    /**
     * Destination file name
     *
     * @access private
     * @var string
     */
    var $file_dst_path;

    /**
     * Destination file name
     *
     * @access public
     * @var string
     */
    var $file_dst_name;

    /**
     * Destination file name body (i.e. without extension)
     *
     * @access public
     * @var string
     */
    var $file_dst_name_body;

    /**
     * Destination file extension
     *
     * @access public
     * @var string
     */
    var $file_dst_name_ext;

    /**
     * Destination file name, including path
     *
     * @access private
     * @var string
     */
    var $file_dst_pathname;

    /**
     * Source image width
     *
     * @access private
     * @var integer
     */
    var $image_src_x;

    /**
     * Source image height
     *
     * @access private
     * @var integer
     */
    var $image_src_y;

    /**
     * Destination image width
     *
     * @access private
     * @var integer
     */
    var $image_dst_x;

    /**
     * Destination image height
     *
     * @access private
     * @var integer
     */
    var $image_dst_y;

    /**
     * Flag set after instanciating the class
     *
     * Indicates if the file has been uploaded properly
     *
     * @access public
     * @var bool
     */
    var $file_exists;

    /**
     * Flag stopping PHP upload checks
     *
     * Indicates whether we instanciated the class with a filename, in which case
     * we will not check on the validity of the PHP *upload*
     *
     * This flag is automatically set to true when working on a local file
     *
     * Warning: for uploads, this flag MUST be set to false for security reason
     *
     * @access public
     * @var bool
     */
    var $no_upload_check;

    /**
     * Flag set after calling a process
     *
     * Indicates if the processing, and copy of the resulting file went OK
     *
     * @access public
     * @var bool
     */
    var $processed;

    /**
     * Holds eventual error message in plain english
     *
     * @access public
     * @var string
     */
    var $error;

    /**
     * Holds an HTML formatted log
     *
     * @access public
     * @var string
     */
    var $log;


    // overiddable processing variables
    
    
    /**
     * Set this variable to replace the name body (i.e. without extension)
     *
     * @access public
     * @var string
     */
    var $file_new_name_body;

    /**
     * Set this variable to add a string to the faile name body
     *
     * @access public
     * @var string
     */
    var $file_name_body_add;

    /**
     * Set this variable to change the file extension
     *
     * @access public
     * @var string
     */
    var $file_new_name_ext;

    /**
     * Set this variable to format the filename (spaces changed to _)
     *
     * @access public
     * @var boolean
     */
    var $file_safe_name = false;

    /**
     * Set this variable to true if you want to check the MIME type against a mime_magic file
     *
     * This variable is set to false by default as many systems don't have mime_magic installed or properly set
     *
     * @access public
     * @var boolean
     */
    var $mime_magic_check;

    /**
     * Set this variable to false if you don't want to turn dangerous scripts into simple text files
     *
     * @access public
     * @var boolean
     */
    var $no_script;

    /**
     * Set this variable to true to allow automatic renaming of the file
     * if the file already exists
     *
     * Default value is true
     *
     * For instance, on uploading foo.ext,<br>
     * if foo.ext already exists, upload will be renamed foo_1.ext<br>
     * and if foo_1.ext already exists, upload will be renamed foo_2.ext<br>
     *
     * @access public
     * @var bool
     */
    var $file_auto_rename;

    /**
     * Set this variable to true to allow automatic creation of the destination 
     * directory if it is missing (works recursively)
     *
     * Default value is true
     *
     * @access public
     * @var bool
     */
    var $dir_auto_create;

    /**
     * Set this variable to true to allow automatic chmod of the destination 
     * directory if it is not writeable
     *
     * Default value is true
     *
     * @access public
     * @var bool
     */
    var $dir_auto_chmod;

     /**
     * Set this variable to the default chmod you want the class to use
     * when creating directories, or attempting to write in a directory
     *
     * Default value is 0777 (without quotes)
     *
     * @access public
     * @var bool
     */
    var $dir_chmod;

    /**
     * Set this variable tu true to allow overwriting of an existing file
     *
     * Default value is false, so no files will be overwritten
     *
     * @access public
     * @var bool
     */
    var $file_overwrite;

    /**
     * Set this variable to change the maximum size in bytes for an uploaded file
     *
     * Default value is the value <i>upload_max_filesize</i> from php.ini
     *
     * @access public
     * @var double
     */
    var $file_max_size;

    /**
     * Set this variable to true to resize the file if it is an image
     *
     * You will probably want to set {@link image_x} and {@link image_y}, and maybe one of the ratio variables
     *
     * Default value is false (no resizing)
     *
     * @access public
     * @var bool
     */
    var $image_resize;

    /**
     * Set this variable to convert the file if it is an image
     *
     * Possibles values are : ''; 'png'; 'jpeg'; 'gif'
     *
     * Default value is '' (no conversion)<br>
     * If {@link resize} is true, {@link convert} will be set to the source file extension 
     *
     * @access public
     * @var string
     */
    var $image_convert;

    /**
     * Set this variable to the wanted (or maximum/minimum) width for the processed image, in pixels
     *
     * Default value is 150
     *
     * @access public
     * @var integer
     */
    var $image_x;

    /**
     * Set this variable to the wanted (or maximum/minimum) height for the processed image, in pixels
     *
     * Default value is 150
     *
     * @access public
     * @var integer
     */
    var $image_y;

    /**
     * Set this variable to keep the original size ratio to fit within {@link image_x} x {@link image_y}
     *
     * Default value is false
     *
     * @access public
     * @var bool
     */
    var $image_ratio;

    /**
     * Set this variable to keep the original size ratio to fit within {@link image_x} x {@link image_y}, 
     * but only if original image is bigger
     *
     * Default value is false
     *
     * @access public
     * @var bool
     */
    var $image_ratio_no_zoom_in;

    /**
     * Set this variable to keep the original size ratio to fit within {@link image_x} x {@link image_y}, 
     * but only if original image is smaller
     *
     * Default value is false
     *
     * @access public
     * @var bool
     */
    var $image_ratio_no_zoom_out;

    /**
     * Set this variable to calculate {@link image_x} automatically , using {@link image_y} and conserving ratio
     *
     * Default value is false
     *
     * @access public
     * @var bool
     */
    var $image_ratio_x;

    /**
     * Set this variable to calculate {@link image_y} automatically , using {@link image_x} and conserving ratio
     *
     * Default value is false
     *
     * @access public
     * @var bool
     */
    var $image_ratio_y;

    /**
     * Quality of JPEG created/converted destination image
     *
     * Default value is 75
     *
     * @access public
     * @var integer;
     */
    var $jpeg_quality;

    /**
     * Determines the quality of the JPG image to fit a desired file size
     *
     * Value is in bytes. The JPG quality will be set between 1 and 100%
     * The calculations are approximations.
     *
     * Default value is NULL (no calculations)
     *
     * @access public
     * @var integer;
     */
    var $jpeg_size;

    /**
     * Preserve transparency when resizing or converting an image (experimental)
     *
     * Default value is false
     *
     * Currently works only when resizing GIFs or converting transparent GIF to PNG<br>
     * It has problems with transparent PNG
     *
     * @access public
     * @var integer;
     */
    var $preserve_transparency;
    
    /**
     * Corrects the image brightness
     *
     * Value can range between -127 and 127
     *
     * Default value is NULL
     *
     * @access public
     * @var integer;
     */
    var $image_brightness;

    /**
     * Corrects the image contrast
     *
     * Value can range between -127 and 127
     *
     * Default value is NULL
     *
     * @access public
     * @var integer;
     */
    var $image_contrast;
    
    /**
     * Applies threshold filter
     *
     * Value can range between -127 and 127
     *
     * Default value is NULL
     *
     * @access public
     * @var integer;
     */
    var $image_threshold;

    /**
     * Applies a tint on the image
     *
     * Value is an hexadecimal color, such as #FFFFFF
     *
     * Default value is NULL
     *
     * @access public
     * @var string;
     */
    var $image_tint_color;

    /**
     * Applies a colored overlay on the image
     *
     * Value is an hexadecimal color, such as #FFFFFF
     *
     * To use with {@link image_overlay_percent}
     *
     * Default value is NULL
     *
     * @access public
     * @var string;
     */
    var $image_overlay_color;

    /**
     * Sets the percentage for the colored overlay
     *
     * Value is a percentage, as an integer between 0 and 100
     *
     * Unless used with {@link image_overlay_color}, this setting has no effect
     *
     * Default value is 50
     *
     * @access public
     * @var integer;
     */
    var $image_overlay_percent;

    /**
     * Inverts the color of an image
     *
     * Default value is FALSE
     *
     * @access public
     * @var boolean;
     */
    var $image_negative;
    
    /**
     * Turns the image into greyscale
     *
     * Default value is FALSE
     *
     * @access public
     * @var boolean;
     */
    var $image_greyscale;

    /**
     * Adds a text label on the image
     *
     * Value is a string, any text. Beware that the text won't wordwrap
     *
     * If set, this setting allow the use of all other settings starting with image_text_
     *
     * Default value is NULL
     *
     * @access public
     * @var string;
     */
    var $image_text;

    /**
     * Sets the text direction for the text label
     *
     * Value is either 'h' or 'v', as in horizontal and vertical
     *
     * Default value is h (horizontal)
     *
     * @access public
     * @var string;
     */
    var $image_text_direction;

    /**
     * Sets the text color for the text label
     *
     * Value is an hexadecimal color, such as #FFFFFF
     *
     * Default value is #FFFFFF (white)
     *
     * @access public
     * @var string;
     */
    var $image_text_color;

    /**
     * Sets the text visibility in the text label
     *
     * Value is a percentage, as an integer between 0 and 100
     *
     * Default value is 100
     *
     * @access public
     * @var integer;
     */
    var $image_text_percent;

    /**
     * Sets the text background color for the text label
     *
     * Value is an hexadecimal color, such as #FFFFFF
     *
     * Default value is NULL (no background)
     *
     * @access public
     * @var string;
     */
    var $image_text_background;

    /**
     * Sets the text background visibility in the text label
     *
     * Value is a percentage, as an integer between 0 and 100
     *
     * Default value is 100
     *
     * @access public
     * @var integer;
     */
    var $image_text_background_percent;

    /**
     * Sets the text font in the text label
     *
     * Value is a an integer between 1 and 5
     *
     * These fonts are built-in on your system. 1 is the smallest font, 5 the biggest
     *
     * Default value is 5
     *
     * @access public
     * @var integer;
     */
    var $image_text_font;

    /**
     * Sets the text label position within the image
     *
     * Value is one or two out of 'TBLR' (top, bottom, left, right)
     *
     * The positions are as following:   
     * <pre>
     *                        TL  T  TR
     *                        L       R
     *                        BL  B  BR
     * </pre>
     *
     * Default value is NULL (centered, horizontal and vertical)
     *
     * Note that is {@link image_text_x} and {@link image_text_y} are used, this setting has no effect
     *
     * @access public
     * @var string;
     */
    var $image_text_position;

    /**
     * Sets the text label absolute X position within the image
     *
     * Value is in pixels, representing the distance between the left of the image and the label
     * If a negative value is used, it will represent the distance between the right of the image and the label    
     *     
     * Default value is NULL (so {@link image_text_position} is used)
     *
     * @access public
     * @var integer;
     */
    var $image_text_x;

    /**
     * Sets the text label absolute Y position within the image
     *
     * Value is in pixels, representing the distance between the top of the image and the label
     * If a negative value is used, it will represent the distance between the bottom of the image and the label    
     *     
     * Default value is NULL (so {@link image_text_position} is used)
     *
     * @access public
     * @var integer;
     */
    var $image_text_y;

    /**
     * Sets the text label padding
     *
     * Value is in pixels, representing the distance between the text and the label background border
     *     
     * Default value is 0
     *
     * This setting can be overriden by {@link image_text_padding_x} and {@link image_text_padding_y}
     *
     * @access public
     * @var integer;
     */
    var $image_text_padding;

    /**
     * Sets the text label horizontal padding
     *
     * Value is in pixels, representing the distance between the text and the left and right label background borders
     *     
     * Default value is NULL
     *
     * If set, this setting overrides the horizontal part of {@link image_text_padding}
     *
     * @access public
     * @var integer;
     */
    var $image_text_padding_x;

    /**
     * Sets the text label vertical padding
     *
     * Value is in pixels, representing the distance between the text and the top and bottom label background borders
     *     
     * Default value is NULL
     *
     * If set, his setting overrides the vertical part of {@link image_text_padding}
     *
     * @access public
     * @var integer;
     */
    var $image_text_padding_y;

    /**
     * Flips the image vertically or horizontally
     *
     * Value is either 'h' or 'v', as in horizontal and vertical
     *
     * Default value is NULL (no flip)
     *
     * @access public
     * @var string;
     */
    var $image_flip;

    /**
     * Rotates the image by increments of 45 degrees
     *
     * Value is either 90, 180 or 270
     *
     * Default value is NULL (no rotation)
     *
     * @access public
     * @var string;
     */
    var $image_rotate;

    /**
     * Crops an image
     *
     * Values are four dimensions, or two, or one (CSS style)
     * They represent the amount cropped top, right, bottom and left.
     * These values can either be in an array, or a space separated string.
     * Each value can be in pixels (with or without 'px'), or percentage (of the source image)
     *
     * For instance, are valid:
     * <pre>
     * $foo->image_crop = 20                  OR array(20);
     * $foo->image_crop = '20px'              OR array('20px');
     * $foo->image_crop = '20 40'             OR array('20', 40);
     * $foo->image_crop = '-20 25%'           OR array(-20, '25%');
     * $foo->image_crop = '20px 25%'          OR array('20px', '25%');
     * $foo->image_crop = '20% 25%'           OR array('20%', '25%');
     * $foo->image_crop = '20% 25% 10% 30%'   OR array('20%', '25%', '10%', '30%');
     * $foo->image_crop = '20px 25px 2px 2px' OR array('20px', '25%px', '2px', '2px');
     * $foo->image_crop = '20 25% 40px 10%'   OR array(20, '25%', '40px', '10%');
     * </pre>
     *
     * If a value is negative, the image will be expanded, and the extra parts will be filled with black
     *
     * Default value is NULL (no cropping)
     *
     * @access public
     * @var string OR array;
     */
    var $image_crop;

    /**
     * Adds a bevel border on the image
     *
     * Value is a positive integer, representing the thickness of the bevel
     *
     * If the bevel colors are the same as the background, it makes a fade out effect
     *
     * Default value is NULL (no bevel)
     *
     * @access public
     * @var integer;
     */
    var $image_bevel;

    /**
     * Top and left bevel color
     *
     * Value is a color, in hexadecimal format
     * This setting is used only if {@link image_bevel} is set
     *
     * Default value is #FFFFFF
     *
     * @access public
     * @var string;
     */
    var $image_bevel_color1;

    /**
     * Right and bottom bevel color
     *
     * Value is a color, in hexadecimal format
     * This setting is used only if {@link image_bevel} is set
     *
     * Default value is #000000
     *
     * @access public
     * @var string;
     */
    var $image_bevel_color2;


    /**
     * Adds a single-color border on the outer of the image
     *
     * Values are four dimensions, or two, or one (CSS style)
     * They represent the border thickness top, right, bottom and left.
     * These values can either be in an array, or a space separated string.
     * Each value can be in pixels (with or without 'px'), or percentage (of the source image)
     *
     * See {@link image_crop} for valid formats
     *
     * If a value is negative, the image will be cropped. 
     * Note that the dimensions of the picture will be increased by the borders' thickness
     *
     * Default value is NULL (no border)
     *
     * @access public
     * @var integer;
     */
    var $image_border;

    /**
     * Border color
     *
     * Value is a color, in hexadecimal format. 
     * This setting is used only if {@link image_border} is set
     *
     * Default value is #FFFFFF
     *
     * @access public
     * @var string;
     */
    var $image_border_color;

    /**
     * Adds a multi-color frame on the outer of the image
     *
     * Value is an integer. Two values are possible for now:
     * 1 for flat border, meaning that the frame is mirrored horizontally and vertically
     * 2 for crossed border, meaning that the frame will be inversed, as in a bevel effect
     *
     * The frame will be composed of colored lines set in {@link image_frame_colors}
     *
     * Note that the dimensions of the picture will be increased by the borders' thickness
     *
     * Default value is NULL (no frame)
     *
     * @access public
     * @var integer;
     */
    var $image_frame;

    /**
     * Sets the colors used to draw a frame
     *
     * Values is a list of n colors in hexadecimal format.
     * These values can either be in an array, or a space separated string.
     *
     * The colors are listed in the following order: from the outset of the image to its center
     * 
     * For instance, are valid:
     * <pre>
     * $foo->image_frame_colors = '#FFFFFF #999999 #666666 #000000';
     * $foo->image_frame_colors = array('#FFFFFF', '#999999', '#666666', '#000000');
     * </pre>
     *
     * This setting is used only if {@link image_frame} is set
     *
     * Default value is '#FFFFFF #999999 #666666 #000000'
     *
     * @access public
     * @var string OR array;
     */
    var $image_frame_colors;

    /**
     * Adds a watermark on the image
     *
     * Value is a local image filename, relative or absolute. GIF, JPG and PNG are supported, as well as PNG alpha.
     *
     * If set, this setting allow the use of all other settings starting with image_watermark_
     *
     * Default value is NULL
     *
     * @access public
     * @var string;
     */
    var $image_watermark;

    /**
     * Sets the watermarkposition within the image
     *
     * Value is one or two out of 'TBLR' (top, bottom, left, right)
     *
     * The positions are as following:   TL  T  TR
     *                                   L       R
     *                                   BL  B  BR
     *
     * Default value is NULL (centered, horizontal and vertical)
     *
     * Note that is {@link image_watermark_x} and {@link image_watermark_y} are used, this setting has no effect
     *
     * @access public
     * @var string;
     */
    var $image_watermark_position;

    /**
     * Sets the watermark absolute X position within the image
     *
     * Value is in pixels, representing the distance between the top of the image and the watermark
     * If a negative value is used, it will represent the distance between the bottom of the image and the watermark    
     *     
     * Default value is NULL (so {@link image_watermark_position} is used)
     *
     * @access public
     * @var integer;
     */
    var $image_watermark_x;

    /**
     * Sets the twatermark absolute Y position within the image
     *
     * Value is in pixels, representing the distance between the left of the image and the watermark
     * If a negative value is used, it will represent the distance between the right of the image and the watermark    
     *     
     * Default value is NULL (so {@link image_watermark_position} is used)
     *
     * @access public
     * @var integer;
     */
    var $image_watermark_y;

    /**
     * Allowed MIME types
     *
     * Default is a selection of safe mime-types, but you might want to change it
     *
     * @access public
     * @var integer
     */
    var $allowed;
    

    /**
     * Init or re-init all the processing variables to their default values
     *
     * This function is called in the constructor, and after each call of {@link process}
     *
     * @access private
     */
    function init() {

        // overiddable variables
        $this->file_new_name_body       = '';       // replace the name body
        $this->file_name_body_add       = '';       // append to the name body
        $this->file_new_name_ext        = '';       // replace the file extension
        $this->file_safe_name           = false;     // format safely the filename
        $this->file_overwrite           = false;    // allows overwritting if the file already exists
        $this->file_auto_rename         = true;     // auto-rename if the file already exists
        $this->dir_auto_create          = true;     // auto-creates directory if missing
        $this->dir_auto_chmod           = true;     // auto-chmod directory if not writeable
        $this->dir_chmod                = 0777;     // default chmod to use
        
        $this->mime_magic_check         = false;    // don't double check the MIME type with mime_magic
        $this->no_script                = true;     // turns scripts into test files 
        
        // TODO: this should be calculated by allowed memory usage
        $this->file_max_size = 1000000000000000000;   
        
        $this->image_resize             = false;    // resize the image
        $this->image_convert            = '';       // convert. values :''; 'png'; 'jpeg'; 'gif'

        $this->image_x                  = 150;
        $this->image_y                  = 150;
        $this->image_ratio              = false;
        $this->image_ratio_no_zoom_in   = false;
        $this->image_ratio_no_zoom_out  = false;
        $this->image_ratio_x            = false;    // calculate the $image_x if true
        $this->image_ratio_y            = false;    // calculate the $image_y if true
        $this->jpeg_quality             = 75;
        $this->jpeg_size                = NULL;
        $this->preserve_transparency    = true;
        
        $this->image_brightness         = NULL; 
        $this->image_contrast           = NULL;
        $this->image_threshold          = NULL;
        $this->image_tint_color         = NULL;
        $this->image_overlay_color      = NULL;
        $this->image_overlay_percent    = NULL;
        $this->image_negative           = false;
        $this->image_greyscale          = false;

        $this->image_text               = NULL;
        $this->image_text_direction     = NULL;
        $this->image_text_color         = '#FFFFFF';
        $this->image_text_percent       = 100;
        $this->image_text_background    = NULL;
        $this->image_text_background_percent = 100; 
        $this->image_text_font          = 5;
        $this->image_text_x             = NULL;
        $this->image_text_y             = NULL;
        $this->image_text_position      = NULL; 
        $this->image_text_padding       = 0;
        $this->image_text_padding_x     = NULL;
        $this->image_text_padding_y     = NULL;
        
        $this->image_watermark          = NULL;
        $this->image_watermark_x        = NULL;
        $this->image_watermark_y        = NULL;
        $this->image_watermark_position = NULL; 

        $this->image_flip               = NULL; 
        $this->image_rotate             = NULL;   
        $this->image_crop               = NULL;

        $this->image_bevel              = NULL;
        $this->image_bevel_color1       = '#FFFFFF';
        $this->image_bevel_color2       = '#000000';      
        $this->image_border             = NULL;
        $this->image_border_color       = '#FFFFFF';
        $this->image_frame              = NULL;
        $this->image_frame_colors       = '#FFFFFF #999999 #666666 #000000';

        $this->allowed = array(
			'image/bmp',
			'image/gif',
			'image/jpeg',
			'image/pjpeg',
			'image/png',
			'image/tiff',
			'image/x-tiff',
			'image/x-windows-bmp',
           );
    }

    /**
     * Constructor. Checks if the file has been uploaded
     *
     * The constructor takes $_FILES['form_field'] array as argument
     * where form_field is the form field name
     *
     * The constructor will check if the file has been uploaded in its temporary location, and
     * accordingly will set {@link uploaded} (and {@link error} is an error occurred)
     *
     * If the file has been uploaded, the constructor will populate all the variables holding the upload 
     * information (none of the processing class variables are used here).
     * You can have access to information about the file (name, size, MIME type...).
     *
     *
     * Alternatively, you can set the first argument to be a local filename (string)
     * and the second argument to be a MIME type (string) (second argument optional if mime_magic is installed)
     * This allows processing of a local file, as if the file was uploaded
     *
     * @access private
     * @param  array  $file $_FILES['form_field']
     *    or   string $file Local filename
     */
    function ImageShopper($file) {

       	global $site;

        $this->file_src_name      = '';
        $this->file_src_name_body = '';
        $this->file_src_name_ext  = '';
        $this->file_src_mime      = '';
        $this->file_src_size      = '';
        $this->file_src_error     = '';
        $this->file_src_pathname  = '';

        $this->file_dst_path      = '';
        $this->file_dst_name      = '';
        $this->file_dst_name_body = '';
        $this->file_dst_name_ext  = '';
        $this->file_dst_pathname  = '';

        $this->image_src_x        = 0;
        $this->image_src_y        = 0;
        $this->image_dst_type     = '';
        $this->image_dst_x        = 0;
        $this->image_dst_y        = 0;

        $this->file_exists           = true;
        $this->no_upload_check    = false;
        $this->processed          = true;
        $this->error              = '';
        $this->log                = '';        
        $this->allowed            = array();
        $this->init();

        if (!$file) {
            $this->file_exists = false;
            $this->error = _("File error. Please try again");
        }

        // check if we sent a local filename rather than a $_FILE element
        if (!is_array($file)) {
            if (empty($file)) {
                $this->file_exists = false;
                $this->error = _("File error. Please try again");
            } else {
                
            	// local file add sites path to the file
            	$file = $site->absolute_path.$file;
                
            	$this->no_upload_check = true;
                // this is a local filename, i.e.not uploaded
                $this->log .= '<b>' . _("source is a local file") . ' ' . $file . '</b><br />';

                if ($this->file_exists && !file_exists($file)) {
                    $this->file_exists = false;
                    $this->error = _("Local file doesn't exist");
                }
        
                if ($this->file_exists && !is_readable($file)) {
                    $this->file_exists = false;
                    $this->error = _("Local file is not readable");
                }

                if ($this->file_exists) {
                    $this->file_src_pathname   = $file;
                    $this->file_src_name       = basename($file);
                    $this->log .= '- ' . _("local file name OK") . '<br />';
                    ereg('\.([^\.]*$)', $this->file_src_name, $extension);
                    if (is_array($extension)) {
                        //why lowercase??? whyyyyyyyyyy??
                    	//$this->file_src_name_ext      = strtolower($extension[1]);
                        $this->file_src_name_ext      = $extension[1];
                        $this->file_src_name_body     = substr($this->file_src_name, 0, ((strlen($this->file_src_name) - strlen($this->file_src_name_ext)))-1);
                    } else {
                        $this->file_src_name_ext      = '';
                        $this->file_src_name_body     = $this->file_src_name;
                    }
                    $this->file_src_size = (file_exists($file) ? filesize($file) : 0);
                    // we try to retrieve the MIME type
                    $info = getimagesize($this->file_src_pathname);
                    $this->image_src_x = $info[0];
                    $this->image_src_y = $info[1];

                    $this->file_src_mime = (array_key_exists('mime', $info) ? $info['mime'] : NULL); 
                    // if we don't have a MIME type, we attempt to retrieve it the old way
                    if (empty($this->file_src_mime)) {
                        $mime = (array_key_exists(2, $info) ? $info[2] : NULL); // 1 = GIF, 2 = JPG, 3 = PNG
                        $this->file_src_mime = ($mime==1 ? 'image/gif' : ($mime==2 ? 'image/jpeg' : ($mime==3 ? 'image/png' : NULL)));
                    }
                    // if we still don't have a MIME type, we attempt to retrieve it otherwise
                    if (empty($this->file_src_mime) && function_exists('mime_content_type')) {
                        $this->file_src_mime = mime_content_type($this->file_src_pathname);
                    }                     
                    $this->file_src_error = 0; 
                }                
                
            }
        } else {
            // this is an element from $_FILE, i.e. an uploaded file
            $this->log .= '<b>' . _("source is an uploaded file") . '</b><br />';
            if ($this->file_exists) {
                $this->file_src_error         = $file['error'];
                switch($this->file_src_error) {
                    case 0:
                        // all is OK
                        $this->log .= '- ' . _("upload OK") . '<br />';
                        break;
                    case 1:
                        $this->file_exists = false;
                        $this->error = _("File upload error (the uploaded file exceeds the upload_max_filesize directive in php.ini)");
                        break;
                    case 2:
                        $this->file_exists = false;
                        $this->error = _("File upload error (the uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form)");
                        break;
                    case 3:
                        $this->file_exists = false;
                        $this->error = _("File upload error (the uploaded file was only partially uploaded)");
                        break;
                    case 4:
                        $this->file_exists = false;
                        $this->error = _("File upload error (no file was uploaded)");
                        break;
                    default:
                        $this->file_exists = false;
                        $this->error = _("File upload error (unknown error code)");
                }
            }
    
            if ($this->file_exists) {
                $this->file_src_pathname   = $file['tmp_name'];
                $this->file_src_name       = $file['name'];
                if ($this->file_src_name == '') {
                    $this->file_exists = false;
                    $this->error = _("File upload error. Please try again");
                }
            }

            if ($this->file_exists) {
                $this->log .= '- ' . _("file name OK") . '<br />';
                ereg('\.([^\.]*$)', $this->file_src_name, $extension);
                if (is_array($extension)) {
                    // whats with the lower case????
                	//$this->file_src_name_ext      = strtolower($extension[1]);
                    $this->file_src_name_ext      = $extension[1];
                    $this->file_src_name_body     = substr($this->file_src_name, 0, ((strlen($this->file_src_name) - strlen($this->file_src_name_ext)))-1);
                } else {
                    $this->file_src_name_ext      = '';
                    $this->file_src_name_body     = $this->file_src_name;
                }
                $this->file_src_size = $file['size'];
                $this->file_src_mime = $file['type'];
            }
        }

        $this->log .= '- ' . _("source variables") . '<br />';
        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_name         : ' . $this->file_src_name . '<br />';
        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_name_body    : ' . $this->file_src_name_body . '<br />';
        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_name_ext     : ' . $this->file_src_name_ext . '<br />';
        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_pathname     : ' . $this->file_src_pathname . '<br />';
        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_mime         : ' . $this->file_src_mime . '<br />';
        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_size         : ' . $this->file_src_size . ' (max= ' . $this->file_max_size . ')<br />';
        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_src_error        : ' . $this->file_src_error . '<br />';
    }

    /**
     * Returns the version of GD
     *
     * This function is copyright Justin Greer, and has been found on php.net
     *
     * @access public
     */
    function gd_version() {
        static $gd_version_number = null;
        if ($gd_version_number === null) {
            ob_start();
            phpinfo(8);
            $module_info = ob_get_contents();
            ob_end_clean();
            if (preg_match("/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i", $module_info,$matches)) {
                $gd_version_number = $matches[1];
            } else {
                $gd_version_number = 0;
            }
        }
        return $gd_version_number;
    } 

    /**
     * Actually uploads the file, and act on it according to the set processing class variables
     *
     * This function copies the uploaded file to the given location, eventually performing actions on it.
     * Typically, you can call {@link process} several times for the same file,
     * for instance to create a resized image and a thumbnail of the same file.
     * The original uploaded file remains intact in its temporary location, so you can use {@link process} several times.
     * You will be able to delete the uploaded file with {@link clean} when you have finished all your {@link process} calls.
     *
     * According to the processing class variables set in the calling file, the file can be renamed,
     * and if it is an image, can be resized or converted.
     *
     * When the processing is completed, and the file copied to its new location, the
     * processing class variables will be reset to their default value.
     * This allows you to set new properties, and perform another {@link process} on the same uploaded file
     *
     * It will set {@link processed} (and {@link error} is an error occurred)
     *
     * @access public
     * @param  string $server_path Path location of the uploaded file, with an ending slash
     */
    function process($server_path) {
		set_time_limit(30);
        global $site;
    	
    	$this->error        = '';
        $this->processed    = true;

        if (substr($server_path, -1, 1) != '/') $server_path = $server_path . '/';
        $this->log .= '<b>' . _("process file to") . ' '  . $server_path . '</b><br />';

        // checks file size and mine type
        if ($this->file_exists) {

            if ($this->file_src_size > $this->file_max_size ) {
                $this->processed = false;
                $this->error = _("File too big");
            } else {
                $this->log .= '- ' . _("file size OK") . '<br />';
            }

            // turn dangerous scripts into text files
            if ($this->no_script) {
                if (((substr($this->file_src_mime, 0, 5) == 'text/' || strpos($this->file_src_mime, 'javascript') !== false)  && (substr($this->file_src_name, -4) != '.txt')) 
                    || preg_match('/\.(php|pl|py|cgi|asp)$/i', $this->file_src_name) || empty($this->file_src_name_ext)) {
                    $this->file_src_mime = 'text/plain';
                    $this->log .= '- ' . _("script") . ' '  . $this->file_src_name . ' ' . _("renamed as") . ' ' . $this->file_src_name . '.txt!<br />';
                    $this->file_src_name_ext .= (empty($this->file_src_name_ext) ? 'txt' : '.txt');
                } 
            }

            // checks MIME type with mime_magic
            if ($this->mime_magic_check && function_exists('mime_content_type')) {
                $detected_mime = mime_content_type($this->file_src_pathname);
                if ($this->file_src_mime != $detected_mime) {
                    $this->log .= '- ' . _("MIME type detected as") . ' ' . $detected_mime . ' ' . _("but given as") . ' ' . $this->file_src_mime . '!<br />';
                    $this->file_src_mime = $detected_mime;
                }
            } 
 
            if (!empty($this->file_src_mime) && !array_key_exists($this->file_src_mime, array_flip($this->allowed))) {
                $this->processed = false;
                $this->error = _("Incorrect type of file");
            } else {
                $this->log .= '- ' . _("file mime OK") . ' : ' . $this->file_src_mime . '<br />';
            }
        } else {
            $this->error = _("File not uploaded. Can't carry on a process");
            $this->processed = false;
        }

        if ($this->processed) {
            $this->file_dst_path        = $server_path;

            // repopulate dst variables from src
            $this->file_dst_name        = $this->file_src_name;
            $this->file_dst_name_body   = $this->file_src_name_body;
            $this->file_dst_name_ext    = $this->file_src_name_ext;


            if ($this->file_new_name_body != '') { // rename file body
                $this->file_dst_name_body = $this->file_new_name_body;
                $this->log .= '- ' . _("new file name body") . ' : ' . $this->file_new_name_body . '<br />';
            }
            if ($this->file_new_name_ext != '') { // rename file ext
                $this->file_dst_name_ext  = $this->file_new_name_ext;
                $this->log .= '- ' . _("new file name ext") . ' : ' . $this->file_new_name_ext . '<br />';
            }
               if ($this->file_name_body_add != '') { // append a bit to the name
                $this->file_dst_name_body  = $this->file_dst_name_body . $this->file_name_body_add;
                $this->log .= '- ' . _("file name body add") . ' : ' . $this->file_name_body_add . '<br />';
            }
            if ($this->file_safe_name) { // formats the name
                $this->file_dst_name_body = str_replace(array(' ', '-'), array('_','_'), $this->file_dst_name_body) ;
                $this->file_dst_name_body = ereg_replace('[^A-Za-z0-9_]', '_', $this->file_dst_name_body) ;
                $this->log .= '- ' . _("file name safe format") . '<br />';
            }

            $this->log .= '- ' . _("destination variables") . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_path         : ' . $this->file_dst_path . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name_body    : ' . $this->file_dst_name_body . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name_ext     : ' . $this->file_dst_name_ext . '<br />';

            // do we do some image manipulation?
            $image_manipulation  = ($this->image_resize 
                                 || $this->image_convert != '' 
                                 || is_numeric($this->image_brightness) 
                                 || is_numeric($this->image_contrast) 
                                 || is_numeric($this->image_threshold) 
                                 || !empty($this->image_tint_color) 
                                 || !empty($this->image_overlay_color) 
                                 || !empty($this->image_text)
                                 || $this->image_greyscale
                                 || $this->image_negative
                                 || !empty($this->image_watermark)
                                 || is_numeric($this->image_rotate)
                                 || is_numeric($this->jpeg_size)
                                 || !empty($this->image_flip)
                                 || !empty($this->image_crop)
                                 || !empty($this->image_border)
                                 || $this->image_frame > 0
                                 || $this->image_bevel > 0);

            if ($image_manipulation) {
                if ($this->image_convert=='') {
                    $this->file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');
                    $this->log .= '- ' . _("image operation, keep extension") . '<br />';
                } else {
                    $this->file_dst_name = $this->file_dst_name_body . '.' . $this->image_convert;
                    $this->log .= '- ' . _("image operation, change extension for conversion type") . '<br />';
                }
            } else {
                $this->file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');
                $this->log .= '- ' . _("no image operation, keep extension") . '<br />';
            }
            
            if (!$this->file_auto_rename) {
                $this->log .= '- ' . _("no auto_rename if same filename exists") . '<br />';
                $this->file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
            } else {
                $this->log .= '- ' . _("checking for auto_rename") . '<br />';
                $this->file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                $body     = $this->file_dst_name_body;
                $cpt = 1;
                while (file_exists($this->file_dst_pathname)) {
                    $this->file_dst_name_body = $body . '_' . $cpt;
                    $this->file_dst_name = $this->file_dst_name_body . (!empty($this->file_dst_name_ext) ? '.' . $this->file_dst_name_ext : '');
                    $cpt++;
                    $this->file_dst_pathname = $this->file_dst_path . $this->file_dst_name;
                }               
                if ($cpt>1) $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("auto_rename to") . ' ' . $this->file_dst_name . '<br />';
            }
            
            $this->log .= '- ' . _("destination file details") . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_name         : ' . $this->file_dst_name . '<br />';
            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;file_dst_pathname     : ' . $this->file_dst_pathname . '<br />';

            if ($this->file_overwrite) {
                 $this->log .= '- ' . _("no overwrite checking") . '<br />';
            } else {
                if (file_exists($this->file_dst_pathname)) {
                    $this->processed = false;
                    $this->error = $this->file_dst_name . ' ' . _("already exists. Please change the file name");
                } else {
                    $this->log .= '- ' . $this->file_dst_name . ' '  . _("doesn't exist already") . '<br />';
                }
            }
        } else {
                $this->processed = false;
        }

        if (!$this->no_upload_check && !is_uploaded_file($this->file_src_pathname)) {
            $this->processed = false;
            $this->error = _("No correct source file. Can't carry on a process");
        }

        if ($this->processed && !file_exists($this->file_src_pathname)) {
            $this->processed = false;
            $this->error = _("No source file. Can't carry on a process");
        }
    
        // checks if the destination directory is writeable, and attempt to make it writeable      
        if ($this->processed && !isReadable($this->file_src_pathname)) {
            $this->processed = false;
            if (is_readable($this->file_src_pathname)) {
                $this->error = _("Source file is not readable. open_basedir restriction in place?");
            } else {
                $this->error = _("Source file is not readable. Can't carry on a process");
            }
        }

        // checks if the destination directory exists, and attempt to create it        
        if ($this->processed && !file_exists($this->file_dst_path)) {
            if ($this->dir_auto_create) {
                $this->log .= '- ' . $this->file_dst_path . ' '  . _("doesn't exist. Attempting creation:");
                if (!function_exists('recursiveMkdir')) {
                    function recursiveMkdir($strPath, $mode = 0777) {
                       return is_dir($strPath) or ( recursiveMkdir(dirname($strPath), $mode) and mkdir($strPath, $mode) );
                    }
                }
                if (!recursiveMkdir($this->file_dst_path, $this->dir_chmod)) {
                    $this->log .= ' ' . _("failed") . '<br />';
                    $this->processed = false;
                    $this->error = _("Destination directory can't be created. Can't carry on a process");
                } else {
                    $this->log .= ' ' . _("success") . '<br />';
                }
            } else {
                $this->error = _("Destination directory doesn't exist. Can't carry on a process");
            }
        }

        if ($this->processed && !is_dir($this->file_dst_path)) {
            $this->processed = false;
            $this->error = _("Destination path is not a directory. Can't carry on a process");
        }

        // checks if the destination directory is writeable, and attempt to make it writeable      
        if ($this->processed && !isWriteable($this->file_dst_pathname)) {
            if ($this->dir_auto_chmod) {
                $this->log .= '- ' . $this->file_dst_path . ' '  . _("is not writeable. Attempting chmod:");
                if (!chmod($this->file_dst_path, $this->dir_chmod)) {
                    $this->log .= ' ' . _("failed") . '<br />';
                    $this->processed = false;
                    $this->error = _("Destination directory can't be made writeable. Can't carry on a process");
                } else {
                    $this->log .= ' ' . _("success") . '<br />';
                    if (!isWriteable($this->file_dst_pathname)) { // we re-check
                        $this->processed = false;
                        $this->error = _("Destination directory is still not writeable. Can't carry on a process");
                    }
                }                
            } else {
                $this->processed = false;
                $this->error = _("Destination path is not a writeable. Can't carry on a process");
            }
        }

        if ($this->processed) {

            if ($image_manipulation) {
             
                $this->log .= '- ' . _("image resizing or conversion wanted") . '<br />';
                if ($this->gd_version()) {
                    switch($this->file_src_mime) {
                        case 'image/pjpeg':
                        case 'image/jpeg':
                        case 'image/jpg':
                            if (!function_exists('imagecreatefromjpeg')) {
                                $this->processed = false;
                                $this->error = _("No create from JPEG support");
                            } else {
                                $image_src = imagecreatefromjpeg($this->file_src_pathname);
                                if (!$image_src) {
                                    $this->processed = false;
                                    $this->error = _("No JPEG read support");
                                } else {
                                    $this->log .= '- ' . _("source image is JPEG") . '<br />';
                                }
                            }
                            break;
                        case 'image/png':
                            if (!function_exists('imagecreatefrompng')) {
                                $this->processed = false;
                                $this->error = _("No create from PNG support");
                            } else {
                                $image_src = imagecreatefrompng($this->file_src_pathname);
                                if (!$image_src) {
                                    $this->processed = false;
                                    $this->error = _("No PNG read support");
                                } else {
                                    $this->log .= '- ' . _("source image is PNG") . '<br />';
                                }
                            }
                            break;
                        case 'image/gif':
                            if (!function_exists('imagecreatefromgif')) {
                                $this->processed = false;
                                $this->error = _("No create from GIF support");
                            } else {
                                $image_src = imagecreatefromgif($this->file_src_pathname);
                                if (!$image_src) {
                                    $this->processed = false;
                                    $this->error = _("No GIF read support");
                                } else {
                                    $this->log .= '- ' . _("source image is GIF") . '<br />';
                                }
                            }
                            break;
                        default:
                            $this->processed = false;
                            $this->error = _("Can't read image source. not an image?");
                    }
                } else {
                    $this->processed = false;
                    $this->error = _("GD doesn't seem to be present");
                }

                if ($this->processed && $image_src) {

                    $this->image_src_x = imagesx($image_src);
                    $this->image_src_y = imagesy($image_src);
                    $this->image_dst_x = $this->image_src_x;
                    $this->image_dst_y = $this->image_src_y;
                    $gd_version = $this->gd_version();
                    
                    if ($this->image_resize) {
                        $this->log .= '- ' . _("resizing...") . '<br />';
 
                        if ($this->image_ratio_x) {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("calculate x size") . '<br />';
                            $this->image_dst_x = round(($this->image_src_x * $this->image_y) / $this->image_src_y);
                            $this->image_dst_y = $this->image_y;
                        } else if ($this->image_ratio_y) {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("calculate y size") . '<br />';
                            $this->image_dst_x = $this->image_x;
                            $this->image_dst_y = round(($this->image_src_y * $this->image_x) / $this->image_src_x);
                        } else if ($this->image_ratio || $this->image_ratio_no_zoom_in || $this->image_ratio_no_zoom_out) {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("check x/y sizes") . '<br />';
                            if ((!$this->image_ratio_no_zoom_in && !$this->image_ratio_no_zoom_out)
                                 || ($this->image_ratio_no_zoom_in && ($this->image_src_x > $this->image_x || $this->image_src_y > $this->image_y))
                                 || ($this->image_ratio_no_zoom_out && $this->image_src_x < $this->image_x && $this->image_src_y < $this->image_y)) {
                                $this->image_dst_x = $this->image_x;
                                $this->image_dst_y = $this->image_y;
                                if (($this->image_src_x/$this->image_x) > ($this->image_src_y/$this->image_y)) {
                                    $this->image_dst_x = $this->image_x;
                                    $this->image_dst_y = intval($this->image_src_y*($this->image_x / $this->image_src_x));
                                } else {
                                    $this->image_dst_y = $this->image_y;
                                    $this->image_dst_x = intval($this->image_src_x*($this->image_y / $this->image_src_y));
                                }
                            } else {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("doesn't calculate x/y sizes") . '<br />';
                                $this->image_dst_x = $this->image_src_x;
                                $this->image_dst_y = $this->image_src_y;
                            }
                        } else {
                            $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("use plain sizes") . '<br />';
                            $this->image_dst_x = $this->image_x;
                            $this->image_dst_y = $this->image_y;
                        }

                        if ($this->preserve_transparency && $this->file_src_mime != 'image/gif' && $this->file_src_mime != 'image/png') $this->preserve_transparency = false;        

                        if ($gd_version >= 2 && !$this->preserve_transparency) {
                            $image_dst = imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        } else {
                            $image_dst = imagecreate($this->image_dst_x, $this->image_dst_y);
                        }
        
                        if ($this->preserve_transparency) {        
                            $this->log .= '- ' . _("preserve transparency") . '<br />';
                            $transparent_color = imagecolortransparent($image_src);
                            imagepalettecopy($image_dst, $image_src);
                            imagefill($image_dst, 0, 0, $transparent_color);
                            imagecolortransparent($image_dst, $transparent_color);
                        }

                        if ($gd_version >= 2 && !$this->preserve_transparency) {
                            $res = imagecopyresampled($image_dst, $image_src, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, $this->image_src_x, $this->image_src_y);
                        } else {
                            $res = imagecopyresized($image_dst, $image_src, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, $this->image_src_x, $this->image_src_y);
                        }

                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("resized image object created") . '<br />';
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;image_src_x y        : ' . $this->image_src_x . ' x ' . $this->image_src_y . '<br />';
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;image_dst_x y        : ' . $this->image_dst_x . ' x ' . $this->image_dst_y . '<br />';

                    } else {
                        // we only convert, so we link the dst image to the src image
                        $image_dst = & $image_src;
                    }

                    // we have to set image_convert if it is not already
                    if (empty($this->image_convert)) {
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("setting destination file type to") . ' ' . $this->file_src_name_ext . '<br />';
                        $this->image_convert = strtolower($this->file_src_name_ext);
                    }

                    // crop image
                    if ($gd_version >= 2 && !empty($this->image_crop)) {
                        if (is_array($this->image_crop)) {
                            $vars = $this->image_crop;
                            $this->log .= '- ' . _("crop image") . ' : ' . implode(' ', $this->image_crop) . '<br />';
                        } else {
                            $this->log .= '- ' . _("crop image") . ' : ' . $this->image_crop . '<br />';
                            $vars = explode(' ', $this->image_crop);
                        }
                        if (sizeof($vars) == 4) {
                            $ct = $vars[0]; $cr = $vars[1]; $cb = $vars[2]; $cl = $vars[3];
                        } else if (sizeof($vars) == 2) {
                            $ct = $vars[0]; $cr = $vars[1]; $cb = $vars[0]; $cl = $vars[1];
                        } else {
                            $ct = $vars[0]; $cr = $vars[0]; $cb = $vars[0]; $cl = $vars[0];
                        } 
                        if (strpos($ct, '%')>0) $ct = $this->image_dst_y * (str_replace('%','',$ct) / 100);
                        if (strpos($cr, '%')>0) $cr = $this->image_dst_x * (str_replace('%','',$cr) / 100);
                        if (strpos($cb, '%')>0) $cb = $this->image_dst_y * (str_replace('%','',$cb) / 100);
                        if (strpos($cl, '%')>0) $cl = $this->image_dst_x * (str_replace('%','',$cl) / 100);
                        if (strpos($ct, 'px')>0) $ct = str_replace('px','',$ct);
                        if (strpos($cr, 'px')>0) $cr = str_replace('px','',$cr);
                        if (strpos($cb, 'px')>0) $cb = str_replace('px','',$cb);
                        if (strpos($cl, 'px')>0) $cl = str_replace('px','',$cl);
                        $ct = (int) $ct;
                        $cr = (int) $cr;
                        $cb = (int) $cb;
                        $cl = (int) $cl;
                        $this->image_dst_x = $this->image_dst_x - $cl - $cr;
                        $this->image_dst_y = $this->image_dst_y - $ct - $cb;
                        if ($this->image_dst_x < 1) $this->image_dst_x = 1;
                        if ($this->image_dst_y < 1) $this->image_dst_y = 1;

                        $tmp=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        imagecopy($tmp, $image_dst, 0, 0, $cl, $ct, $this->image_dst_x, $this->image_dst_y);

                        // we transfert tmp into image_dst
                        imagedestroy($image_dst);     
                        $image_dst=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        imagecopy($image_dst,$tmp,0,0,0,0,$this->image_dst_x,$this->image_dst_y);
                        imagedestroy($tmp);      
                    }
                    
                    
                    // flip image
                    if ($gd_version >= 2 && !empty($this->image_flip)) {
                        $this->image_flip = strtolower($this->image_flip);
                        $this->log .= '- ' . _("flip image") . ' : ' . $this->image_flip . '<br />';
                        $tmp=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        for ($x = 0; $x < $this->image_dst_x; $x++) {
                            for ($y = 0; $y < $this->image_dst_y; $y++){
                                if (strpos($this->image_flip, 'v') !== false) {
                                    imagecopy($tmp, $image_dst, $this->image_dst_x - $x - 1, $y, $x, $y, 1, 1);
                                } else {
                                    imagecopy($tmp, $image_dst, $x, $this->image_dst_y - $y - 1, $x, $y, 1, 1);
                                }
                            }
                        }

                        // we transfert tmp into image_dst
                        imagedestroy($image_dst);     
                        $image_dst=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        imagecopy($image_dst,$tmp,0,0,0,0,$this->image_dst_x,$this->image_dst_y);
                        imagedestroy($tmp);      
                    }


                    // rotate image
                    if ($gd_version >= 2 && is_numeric($this->image_rotate)) {
                        if (!in_array($this->image_rotate, array(0, 90, 180, 270))) $this->image_rotate = 0;  
                        if ($this->image_rotate != 0) {
                            if ($this->image_rotate == 90 || $this->image_rotate == 270) {
                                $tmp=imagecreatetruecolor($this->image_dst_y, $this->image_dst_x);
                            } else {
                                $tmp=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                            }
                            $this->log .= '- ' . _("rotate image") . ' : ' . $this->image_rotate . '<br />';
                            for ($x = 0; $x < $this->image_dst_x; $x++) {
                                for ($y = 0; $y < $this->image_dst_y; $y++){
                                    if ($this->image_rotate == 90) {
                                        imagecopy($tmp, $image_dst, $y, $x, $x, $this->image_dst_y - $y - 1, 1, 1);
                                    } else if ($this->image_rotate == 180) {
                                        imagecopy($tmp, $image_dst, $x, $y, $this->image_dst_x - $x - 1, $this->image_dst_y - $y - 1, 1, 1);
                                    } else if ($this->image_rotate == 270) {
                                        imagecopy($tmp, $image_dst, $y, $x, $this->image_dst_x - $x - 1, $y, 1, 1);
                                    } else {
                                        imagecopy($tmp, $image_dst, $x, $y, $x, $y, 1, 1);
                                    }
                                }
                            }
                            if ($this->image_rotate == 90 || $this->image_rotate == 270) {
                                $t = $this->image_dst_y;
                                $this->image_dst_y = $this->image_dst_x;
                                $this->image_dst_x = $t;
                            }
                            
                            // we transfert tmp into image_dst
                            imagedestroy($image_dst);     
                            $image_dst=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                            imagecopy($image_dst,$tmp,0,0,0,0,$this->image_dst_x,$this->image_dst_y);
                            imagedestroy($tmp);      
                        }                        
                    }

                    // add color overlay
                   if ($gd_version >= 2 && (is_numeric($this->image_overlay_percent) && !empty($this->image_overlay_color))) {
                        $this->log .= '- ' . _("apply color overlay") . '<br />';
                        sscanf($this->image_overlay_color, "#%2x%2x%2x", $red, $green, $blue);
                        $filter=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        $color=imagecolorallocate($filter, $red, $green, $blue);
                        imagefilledrectangle($filter, 0, 0, $this->image_dst_x, $this->image_dst_y, $color);
                        imagecopymerge($image_dst, $filter, 0, 0, 0, 0, $this->image_dst_x, $this->image_dst_y, $this->image_overlay_percent);
                        imagedestroy($filter);
                    }

                    // add brightness, contrast and tint, turns to greyscale and inverts colors
                    if ($gd_version >= 2 && ($this->image_negative || $this->image_greyscale || is_numeric($this->image_threshold)|| is_numeric($this->image_brightness) || is_numeric($this->image_contrast) || !empty($this->image_tint_color))) {
                        $this->log .= '- ' . _("apply tint, light, contrast correction, negative, greyscale and threshold") . '<br />';

                        if (!empty($this->image_tint_color)) sscanf($this->image_tint_color, "#%2x%2x%2x", $red, $green, $blue);
                        $background = imagecolorallocatealpha($image_dst, 255, 255, 255, 0);
                        imagefill($image_dst, 0, 0, $background);
                        imagealphablending($image_dst, TRUE);

                        for($y=0; $y < $this->image_dst_y; $y++) {
                            for($x=0; $x < $this->image_dst_x; $x++) {
                                if ($this->image_greyscale) {
                                    $rgb = imagecolorat($image_dst, $x, $y);           
                                    $pixel = imagecolorsforindex($image_dst, $rgb);
                                    $r = $g = $b = round((0.2125 * $pixel['red']) + (0.7154 * $pixel['green']) + (0.0721 * $pixel['blue']));
                                    $a = $pixel['alpha'];           
                                    $pixelcolor = imagecolorallocatealpha($image_dst, $r, $g, $b, $a);
                                    imagesetpixel($image_dst, $x, $y, $pixelcolor);
                                }      
                                if (is_numeric($this->image_threshold)) {
                                    $rgb = imagecolorat($image_dst, $x, $y);           
                                    $pixel = imagecolorsforindex($image_dst, $rgb);
                                    $c = (round($pixel['red']+$pixel['green']+$pixel['blue'])/3) - 127;
                                    $r = $g = $b = ($c > $this->image_threshold ? 255 : 0);
                                    $a = $pixel['alpha'];           
                                    $pixelcolor = imagecolorallocatealpha($image_dst, $r, $g, $b, $a);
                                    imagesetpixel($image_dst, $x, $y, $pixelcolor);
                                }
                                if (is_numeric($this->image_brightness)) {
                                    $rgb = imagecolorat($image_dst, $x, $y);           
                                    $pixel = imagecolorsforindex($image_dst, $rgb);
                                    $r = max(min(round($pixel['red']+(($this->image_brightness*2))),255),0);
                                    $g = max(min(round($pixel['green']+(($this->image_brightness*2))),255),0);
                                    $b = max(min(round($pixel['blue']+(($this->image_brightness*2))),255),0);
                                    $a = $pixel['alpha'];           
                                    $pixelcolor = imagecolorallocatealpha($image_dst, $r, $g, $b, $a);
                                    imagesetpixel($image_dst, $x, $y, $pixelcolor);
                                }
                                if (is_numeric($this->image_contrast)) {
                                    $rgb = imagecolorat($image_dst, $x, $y);           
                                    $pixel = imagecolorsforindex($image_dst, $rgb);
                                    $r = max(min(round(($this->image_contrast+128)*$pixel['red']/128),255),0);
                                    $g = max(min(round(($this->image_contrast+128)*$pixel['green']/128),255),0);
                                    $b = max(min(round(($this->image_contrast+128)*$pixel['blue']/128),255),0);
                                    $a = $pixel['alpha'];           
                                    $pixelcolor = imagecolorallocatealpha($image_dst, $r, $g, $b, $a);
                                    imagesetpixel($image_dst, $x, $y, $pixelcolor);
                                }
                                if (!empty($this->image_tint_color)) {
                                    $rgb = imagecolorat($image_dst, $x, $y);           
                                    $pixel = imagecolorsforindex($image_dst, $rgb);
                                    $r = min(round($red*$pixel['red']/169),255);
                                    $g = min(round($green*$pixel['green']/169),255);
                                    $b = min(round($blue*$pixel['blue']/169),255);
                                    $a = $pixel['alpha'];           
                                    $pixelcolor = imagecolorallocatealpha($image_dst, $r, $g, $b, $a);
                                    imagesetpixel($image_dst, $x, $y, $pixelcolor);
                                }                                
                                if (!empty($this->image_negative)) {
                                    $rgb = imagecolorat($image_dst, $x, $y);           
                                    $pixel = imagecolorsforindex($image_dst, $rgb);
                                    $r = round(255-$pixel['red']);
                                    $g = round(255-$pixel['green']);
                                    $b = round(255-$pixel['blue']);
                                    $a = $pixel['alpha'];           
                                    $pixelcolor = imagecolorallocatealpha($image_dst, $r, $g, $b, $a);
                                    imagesetpixel($image_dst, $x, $y, $pixelcolor);
                                }                                
                            }
                        }
                    }

                    // adds a border
                    if ($gd_version >= 2 && !empty($this->image_border)) {
                        if (is_array($this->image_border)) {
                            $vars = $this->image_border;
                            $this->log .= '- ' . _("add border") . ' : ' . implode(' ', $this->image_border) . '<br />';
                        } else {
                            $this->log .= '- ' . _("add border") . ' : ' . $this->image_border . '<br />';
                            $vars = explode(' ', $this->image_border);
                        }
                        if (sizeof($vars) == 4) {
                            $ct = $vars[0]; $cr = $vars[1]; $cb = $vars[2]; $cl = $vars[3];
                        } else if (sizeof($vars) == 2) {
                            $ct = $vars[0]; $cr = $vars[1]; $cb = $vars[0]; $cl = $vars[1];
                        } else {
                            $ct = $vars[0]; $cr = $vars[0]; $cb = $vars[0]; $cl = $vars[0];
                        } 
                        if (strpos($ct, '%')>0) $ct = $this->image_dst_y * (str_replace('%','',$ct) / 100);
                        if (strpos($cr, '%')>0) $cr = $this->image_dst_x * (str_replace('%','',$cr) / 100);
                        if (strpos($cb, '%')>0) $cb = $this->image_dst_y * (str_replace('%','',$cb) / 100);
                        if (strpos($cl, '%')>0) $cl = $this->image_dst_x * (str_replace('%','',$cl) / 100);
                        if (strpos($ct, 'px')>0) $ct = str_replace('px','',$ct);
                        if (strpos($cr, 'px')>0) $cr = str_replace('px','',$cr);
                        if (strpos($cb, 'px')>0) $cb = str_replace('px','',$cb);
                        if (strpos($cl, 'px')>0) $cl = str_replace('px','',$cl);
                        $ct = (int) $ct;
                        $cr = (int) $cr;
                        $cb = (int) $cb;
                        $cl = (int) $cl;
                        $this->image_dst_x = $this->image_dst_x + $cl + $cr;
                        $this->image_dst_y = $this->image_dst_y + $ct + $cb;
                        if (!empty($this->image_border_color)) sscanf($this->image_border_color, "#%2x%2x%2x", $red, $green, $blue);

                        $tmp=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        $background = imagecolorallocatealpha($tmp, $red, $green, $blue, 0);
                        imagefill($tmp, 0, 0, $background);
                        imagecopy($tmp, $image_dst, $cl, $ct, 0, 0, $this->image_dst_x - $cr - $cl, $this->image_dst_y - $cb - $ct);
                        
                        // we transfert tmp into image_dst
                        imagedestroy($image_dst);     
                        $image_dst=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        imagecopy($image_dst,$tmp,0,0,0,0,$this->image_dst_x,$this->image_dst_y);
                        imagedestroy($tmp);      
                    }
                    
                    // add frame border
                    if (is_numeric($this->image_frame)) {
                        if (is_array($this->image_frame_colors)) {
                            $vars = $this->image_frame_colors;
                            $this->log .= '- ' . _("add frame") . ' : ' . implode(' ', $this->image_frame_colors) . '<br />';
                        } else {
                            $this->log .= '- ' . _("add frame") . ' : ' . $this->image_frame_colors . '<br />';
                            $vars = explode(' ', $this->image_frame_colors);
                        }

                        $nb = sizeof($vars);
                        $this->image_dst_x = $this->image_dst_x + ($nb * 2);
                        $this->image_dst_y = $this->image_dst_y + ($nb * 2);
                        $tmp=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        imagecopy($tmp, $image_dst, $nb, $nb, 0, 0, $this->image_dst_x - ($nb * 2), $this->image_dst_y - ($nb * 2));
                        
                        for ($i=0; $i<$nb; $i++) {
                            sscanf($vars[$i], "#%2x%2x%2x", $red, $green, $blue);
                            $c = imagecolorallocate($tmp, $red, $green, $blue);
                            if ($this->image_frame == 1) {
                                imageline($tmp, $i, $i, $this->image_dst_x - $i -1, $i, $c);
                                imageline($tmp, $this->image_dst_x - $i -1, $this->image_dst_y - $i -1, $this->image_dst_x - $i -1, $i, $c);
                                imageline($tmp, $this->image_dst_x - $i -1, $this->image_dst_y - $i -1, $i, $this->image_dst_y - $i -1, $c);
                                imageline($tmp, $i, $i, $i, $this->image_dst_y - $i -1, $c);
                            } else {
                                imageline($tmp, $i, $i, $this->image_dst_x - $i -1, $i, $c);
                                imageline($tmp, $this->image_dst_x - $nb + $i, $this->image_dst_y - $nb + $i, $this->image_dst_x - $nb + $i, $nb - $i, $c);
                                imageline($tmp, $this->image_dst_x - $nb + $i, $this->image_dst_y - $nb + $i, $nb - $i, $this->image_dst_y - $nb + $i, $c);
                                imageline($tmp, $i, $i, $i, $this->image_dst_y - $i -1, $c);
                            }
                        }
                        
                        // we transfert tmp into image_dst
                        imagedestroy($image_dst);     
                        $image_dst=imagecreatetruecolor($this->image_dst_x, $this->image_dst_y);
                        imagecopy($image_dst,$tmp,0,0,0,0,$this->image_dst_x,$this->image_dst_y);
                        imagedestroy($tmp);  
                    }

                    // add bevel border
                    if ($this->image_bevel > 0) {
                        if (empty($this->image_bevel_color1)) $this->image_bevel_color1 = '#FFFFFF'; 
                        if (empty($this->image_bevel_color2)) $this->image_bevel_color2 = '#000000'; 
                        sscanf($this->image_bevel_color1, "#%2x%2x%2x", $red1, $green1, $blue1);
                        sscanf($this->image_bevel_color2, "#%2x%2x%2x", $red2, $green2, $blue2);
                        imagealphablending($image_dst, true);
                        for ($i=0; $i<$this->image_bevel; $i++) {
                            $alpha = round(($i / $this->image_bevel) * 127);
                            $c1 = imagecolorallocatealpha($image_dst, $red1, $green1, $blue1, $alpha);
                            $c2 = imagecolorallocatealpha($image_dst, $red2, $green2, $blue2, $alpha);
                            imageline($image_dst, $i, $i, $this->image_dst_x - $i -1, $i, $c1);
                            imageline($image_dst, $this->image_dst_x - $i -1, $this->image_dst_y - $i, $this->image_dst_x - $i -1, $i, $c2);
                            imageline($image_dst, $this->image_dst_x - $i -1, $this->image_dst_y - $i -1, $i, $this->image_dst_y - $i -1, $c2);
                            imageline($image_dst, $i, $i, $i, $this->image_dst_y - $i -1, $c1);
                        }
                    }

                    // add watermark image
                    if ($this->image_watermark!='' && file_exists($site->absolute_path.$this->image_watermark)) {
                        $this->log .= '- ' . _("add watermark") . '<br />';
                        $this->image_watermark_position = strtolower($this->image_watermark_position);
                        
                        $watermark_info = getimagesize($this->image_watermark);
                        $watermark_type = (array_key_exists(2, $watermark_info) ? $watermark_info[2] : NULL); // 1 = GIF, 2 = JPG, 3 = PNG
                        $watermark_checked = false;

                        if ($watermark_type == 1) {
                            if (!function_exists('imagecreatefromgif')) {
                                $this->error = _("No create from GIF support, can't read watermark");
                            } else {
                                $filter = imagecreatefromgif($this->image_watermark);
                                if (!$filter) {
                                    $this->error = _("No GIF read support, can't create watermark");
                                } else {
                                    $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("watermark source image is GIF") . '<br />';
                                    $watermark_checked = true;
                                }
                            }
                        } else if ($watermark_type == 2) {
                            if (!function_exists('imagecreatefromjpeg')) {
                                $this->error = _("No create from JPG support, can't read watermark");
                            } else {
                                $filter = imagecreatefromjpeg($this->image_watermark);
                                if (!$filter) {
                                    $this->error = _("No JPG read support, can't create watermark");
                                } else {
                                    $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("watermark source image is JPG") . '<br />';
                                    $watermark_checked = true;
                                }
                            }
                        } else if ($watermark_type == 3) {
                            if (!function_exists('imagecreatefrompng')) {
                                $this->error = _("No create from PNG support, can't read watermark");
                            } else {
                                $filter = imagecreatefrompng($this->image_watermark);
                                if (!$filter) {
                                    $this->error = _("No PNG read support, can't create watermark");
                                } else {
                                    $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("watermark source image is PNG") . '<br />';
                                    $watermark_checked = true;
                                }
                            }
                        }
                        if ($watermark_checked) {
                            $watermark_width = imagesx($filter);
                            $watermark_height = imagesy($filter);
                            $watermark_x = 0;
                            $watermark_y = 0;
                            if (is_numeric($this->image_watermark_x)) {
                                if ($this->image_watermark_x < 0) {
                                    $watermark_x = $this->image_dst_x - $watermark_width + $this->image_watermark_x;
                                } else {
                                    $watermark_x = $this->image_watermark_x;
                                }
                            } else {
                                if (strpos($this->image_watermark_position, 'r') !== false) {
                                    $watermark_x = $this->image_dst_x - $watermark_width;
                                } else if (strpos($this->image_watermark_position, 'l') !== false) {
                                    $watermark_x = 0;
                                } else {
                                    $watermark_x = ($this->image_dst_x - $watermark_width) / 2;
                                }
                            }
         
                            if (is_numeric($this->image_watermark_y)) {
                                if ($this->image_watermark_y < 0) {
                                    $watermark_y = $this->image_dst_y - $watermark_height + $this->image_watermark_y;
                                } else {
                                    $watermark_y = $this->image_watermark_y;
                                }
                            } else {
                                if (strpos($this->image_watermark_position, 'b') !== false) {
                                    $watermark_y = $this->image_dst_y - $watermark_height;
                                } else if (strpos($this->image_watermark_position, 't') !== false) {
                                    $watermark_y = 0;
                                } else {
                                    $watermark_y = ($this->image_dst_y - $watermark_height) / 2;
                                }
                            }
                            imagecopyresampled ($image_dst, $filter, $watermark_x, $watermark_y, 0, 0, $watermark_width, $watermark_height, $watermark_width, $watermark_height);
                        
                        } else {
                            $this->error = _("Watermark image is of unknown type");
                        }                        
                    }

                    // add text
                    if (!empty($this->image_text)) {
                        $this->log .= '- ' . _("add text") . '<br />';
                  
                        if (!is_numeric($this->image_text_padding)) $this->image_text_padding = 0;
                        if (!is_numeric($this->image_text_padding_x)) $this->image_text_padding_x = $this->image_text_padding;
                        if (!is_numeric($this->image_text_padding_y)) $this->image_text_padding_y = $this->image_text_padding;
                        $this->image_text_position = strtolower($this->image_text_position);
                        $this->image_text_direction = strtolower($this->image_text_direction);
                        
                        if ($this->image_text_direction == 'v') {
                            $text_height = (ImageFontWidth($this->image_text_font) * strlen($this->image_text)) + (2 * $this->image_text_padding_y);
                            $text_width = ImageFontHeight($this->image_text_font) + (2 * $this->image_text_padding_x);                    
                        } else {
                            $text_width = (ImageFontWidth($this->image_text_font) * strlen($this->image_text)) + (2 * $this->image_text_padding_x);
                            $text_height = ImageFontHeight($this->image_text_font) + (2 * $this->image_text_padding_y);                    
                        }
                        $text_x = 0;
                        $text_y = 0;
                        if (is_numeric($this->image_text_x)) {
                            if ($this->image_text_x < 0) {
                                $text_x = $this->image_dst_x - $text_width + $this->image_text_x;
                            } else {
                                $text_x = $this->image_text_x;
                            }
                        } else {
                            if (strpos($this->image_text_position, 'r') !== false) {
                                $text_x = $this->image_dst_x - $text_width;
                            } else if (strpos($this->image_text_position, 'l') !== false) {
                                $text_x = 0;
                            } else {
                                $text_x = ($this->image_dst_x - $text_width) / 2;
                            }
                        }
     
                        if (is_numeric($this->image_text_y)) {
                            if ($this->image_text_y < 0) {
                                $text_y = $this->image_dst_y - $text_height + $this->image_text_y;
                            } else {
                                $text_y = $this->image_text_y;
                            }
                        } else {
                            if (strpos($this->image_text_position, 'b') !== false) {
                                $text_y = $this->image_dst_y - $text_height;
                            } else if (strpos($this->image_text_position, 't') !== false) {
                                $text_y = 0;
                            } else {
                                $text_y = ($this->image_dst_y - $text_height) / 2;
                            }
                        }
        
                        // add a background, maybe transparent
                        if (!empty($this->image_text_background)) {
                            sscanf($this->image_text_background, "#%2x%2x%2x", $red, $green, $blue);
                            if ($gd_version >= 2 && (is_numeric($this->image_text_background_percent)) && $this->image_text_background_percent >= 0 && $this->image_text_background_percent <= 100) {
                                $filter=imagecreatetruecolor($text_width, $text_height);
                                $background_color=imagecolorallocate($filter, $red, $green, $blue);
                                imagefilledrectangle($filter, 0, 0, $text_width, $text_height, $background_color);
                                imagecopymerge($image_dst, $filter, $text_x, $text_y, 0, 0, $text_width, $text_height, $this->image_text_background_percent);
                                imagedestroy($filter);
                            } else {
                                $background_color = imageColorAllocate($image_dst ,$red, $green, $blue);
                                imagefilledrectangle($image_dst, $text_x, $text_y, $text_x + $text_width, $text_y + $text_height, $background_color);
                            }
                        }

                        $text_x += $this->image_text_padding_x;
                        $text_y += $this->image_text_padding_y;
                        
                        sscanf($this->image_text_color, "#%2x%2x%2x", $red, $green, $blue);


                        // add the text, maybe transparent
                        if ($gd_version >= 2 && (is_numeric($this->image_text_percent)) && $this->image_text_percent >= 0 && $this->image_text_percent <= 100) {
                            $t_width = $text_width - (2 * $this->image_text_padding_x);
                            $t_height = $text_height - (2 * $this->image_text_padding_y);                            
                            if ($t_width < 0) $t_width = 0;
                            if ($t_height < 0) $t_height = 0;
                            $filter=imagecreatetruecolor($t_width, $t_height);
                            $color = imagecolorallocate($filter, 0, 0, 0);
                            $text_color = imageColorAllocate($filter ,$red, $green, $blue);
                            imagecolortransparent($filter, $color);
                            if ($this->image_text_direction == 'v') {
                                imagestringup($filter, $this->image_text_font, 0, $text_height - (2 * $this->image_text_padding_y), $this->image_text, $text_color);
                            } else {
                                imagestring($filter, $this->image_text_font, 0, 0, $this->image_text, $text_color);
                            }
                            imagecopymerge($image_dst, $filter, $text_x, $text_y, 0, 0, $t_width, $t_height, $this->image_text_percent);
                            imagedestroy($filter);
                        } else {
                            $text_color = imageColorAllocate($image_dst ,$red, $green, $blue);
                            if ($this->image_text_direction == 'v') {
                                imagestringup($image_dst, $this->image_text_font, $text_x, $text_y + $text_height - (2 * $this->image_text_padding_y), $this->image_text, $text_color);
                            } else {
                                imagestring($image_dst, $this->image_text_font, $text_x, $text_y, $this->image_text, $text_color);
                            }
                        }

                    }
        
        
        
                    if (is_numeric($this->jpeg_size) && $this->jpeg_size > 0 && ($this->image_convert == 'jpeg' || $this->image_convert == 'jpg')) {
                        // based on: JPEGReducer class version 1, 25 November 2004, Author: Huda M ElMatsani, justhuda at netscape dot net
                        $this->log .= '- ' . _("JPEG desired file size") . ' : ' . $this->jpeg_size . '<br />';
                        //calculate size of each image. 75%, 50%, and 25% quality
                        ob_start(); imagejpeg($image_dst,'',75);  $buffer = ob_get_contents(); ob_end_clean();
                        $size75 = strlen($buffer);
                        ob_start(); imagejpeg($image_dst,'',50);  $buffer = ob_get_contents(); ob_end_clean();
                        $size50 = strlen($buffer);
                        ob_start(); imagejpeg($image_dst,'',25);  $buffer = ob_get_contents(); ob_end_clean();
                        $size25 = strlen($buffer);
                
                        //calculate gradient of size reduction by quality
                        $mgrad1 = 25/($size50-$size25);
                        $mgrad2 = 25/($size75-$size50);
                        $mgrad3 = 50/($size75-$size25);
                        $mgrad  = ($mgrad1+$mgrad2+$mgrad3)/3;
                        //result of approx. quality factor for expected size
                        $q_factor=round($mgrad*($this->jpeg_size-$size50)+50);
                
                        if ($q_factor<1) {
                            $this->jpeg_quality=1;
                        } elseif ($q_factor>100) {
                            $this->jpeg_quality=100;
                        } else {
                            $this->jpeg_quality=$q_factor;
                        }
                        $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("JPEG quality factor set to") . ' ' . $this->jpeg_quality . '<br />';
                    }



                    // outputs image
                    $this->log .= '- ' . _("converting..") . '<br />';
                    switch($this->image_convert) {
                        case 'jpeg':
                        case 'jpg':
                            $result = imagejpeg ($image_dst, $this->file_dst_pathname, $this->jpeg_quality);
                            if (!$result) {
                                $this->processed = false;
                                $this->error = _("No JPEG create support");
                            } else {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("JPEG image created") . '<br />';
                            }
                            break;
                        case 'png':
                            $result = imagepng ($image_dst, $this->file_dst_pathname);
                            if (!$result) {
                                $this->processed = false;
                                $this->error = _("No PNG create support");
                            } else {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("PNG image created") . '<br />';
                            }
                            break;
                        case 'gif':
                            $result = imagegif ($image_dst, $this->file_dst_pathname);
                            if (!$result) {
                                $this->processed = false;
                                $this->error = _("No GIF create support");
                            } else {
                                $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("GIF image created") . '<br />';
                            }
                            break;
                        default:
                            $this->processed = false;
                            $this->error = _("No convertion type defined");
                    }
                    if (is_resource($image_src)) imagedestroy($image_src);
                    if (is_resource($image_dst)) imagedestroy($image_dst);
                    $this->log .= '&nbsp;&nbsp;&nbsp;&nbsp;' . _("image objects destroyed") . '<br />';
                }

            } else {
                $this->log .= '- ' . _("no image processing wanted") . '<br />';

                if (!$this->no_upload_check) {
                    $result = is_uploaded_file($this->file_src_pathname);
                } else {
                    $result = TRUE;
                }
                if ($result) {
                    $result = file_exists($this->file_src_pathname);
                    if ($result) {
                        $result = copy($this->file_src_pathname, $this->file_dst_pathname);
                        if (!$result) {
                            $this->processed = false;
                            $this->error = _("Error copying file on the server. Copy failed");
                        }
                    } else {
                        $this->processed = false;
                        $this->error = _("Error copying file on the server. Missing source file");
                    }
                } else {
                    $this->processed = false;
                    $this->error = _("Error copying file on the server. Incorrect source file");
                }


                //$result = move_uploaded_file($this->file_src_pathname, $this->file_dst_pathname);
                //if (!$result) {
                //    $this->processed = false;
                //    $this->error = _("Error copying file on the server");
                //}
            }

        }

        if ($this->processed) {
            $this->log .= '- <b>' . _("process OK") . '</b><br />';

        }
        // we reinit all the var
        // no we don't $this->init();

    }

    /**
     * Deletes the uploaded file from its temporary location
     *
     * When PHP uploads a file, it stores it in a temporary location.
     * When you {@link process} the file, you actually copy the resulting file to the given location, it doesn't alter the original file.
     * Once you have processed the file as many times as you wanted, you can delete the uploaded file.
     *
     * You might want not to use this function if you work on local files, as it will delete the source file
     *
     * @access public
     */
    function clean() {
        unlink($this->file_src_pathname);
    }

}

// we need a special function as is_writeable() is not good enough
if (!function_exists('isReadable')) {
    function isReadable($strPath) {
        if (!($f = fopen($strPath, 'r'))) return false;
        fclose($f); return true;
    }
}

// we need a special function as is_writeable() is not good enough
if (!function_exists('isWriteable')) {
    function isWriteable($strPath) {
        if (!($f = fopen($strPath, 'w+'))) return false;
        fclose($f); unlink($strPath); return true;
    }
}

// i18n gettext compatibility
if (!function_exists("_")) {
  function _($str) {
    return $str;
  }
} 

