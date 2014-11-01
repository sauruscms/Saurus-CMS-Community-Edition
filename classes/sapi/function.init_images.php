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
# function init_images
#	"name" => default: images
#	"parent" => id, default: <current page id>
#	"start" => <starting from row>
#	"limit" => <count of rows>
#	"where" => where clause (sql)
# ));
function smarty_function_init_images($params,&$smarty)
{
    if (!function_exists('search_obj_array'))
    {
    	function search_obj_array($needle,$field,$array=array())
	    {
	    	if($array)
	        {
	            foreach($array as $key => $data)
	            {
	                if($data->all[$field] == $needle)
	                {
	                	return $key;
	                }
	            }
	        }
	        return false;
	    }
    }
    global $class_path, $site, $leht;

    extract($params);
	if(!isset($name)) { $name='images'; }
	if(!isset($parent)) {
		$parent = $leht->id;
	}
    $album=new Objekt(array(
        'objekt_id' => $parent,
    ));
    
	$conf = new CONFIG($album->all['ttyyp_params']);

    //$alamlist->debug->print_msg();
    $files = array();
    if($conf->get('path'))
    {
		$path = (string)$conf->get('path');
    	
    	$path = preg_replace('#^/#', '', $path);
		$path = preg_replace('#/$#', '', $path);
		
        $sql = $site->db->prepare('select objekt_id from obj_folder where relative_path = ?', '/'.$path);
        $result = new SQL($sql);
        $folder_id = $result->fetchsingle();
        if($folder_id)
        {
        	$alamlistSQL = new AlamlistSQL(array(
	    		'parent' => $folder_id,
	    		'klass'	=> 'file',
	    		'order' => ' filename ',
	    		'where' => $where,
	    	));
	        $alamlistSQL->add_select(" obj_file.filename, obj_file.size, obj_file.kirjeldus ");
		    $alamlistSQL->add_from("LEFT JOIN obj_file ON objekt.objekt_id=obj_file.objekt_id");
	        $alamlist = new Alamlist(array(
	        	'alamlistSQL' => $alamlistSQL,
	        	//'start' => $start, bug #2564
	        	//'limit' => $limit,
	        ));
	        
	        $files=array();
	        
			$new_button = $alamlist->get_edit_buttons(array(
				'tyyp_idlist' => '21',
				//profile_id => join(",",$profile_ids), # new nupule anda edasi kļæ½ik profiili ID-d
				'publish' => 1, // images are always published
			));
			
	        while ($obj = $alamlist->next())
	        {
	        	$obj->buttons = $obj->get_edit_buttons(array(
	            	'tyyp_idlist'=> 21,
	                'nupud' => array('edit', 'delete', 'new'),
	            ));
	            $files[]=$obj;
        	}
        }
        
    	$path=$site->absolute_path.$path;
        include_once($class_path.'picture.inc.php');
        $imgs=get_images($path, $conf->get('path'));
    }
    else
    {
        //veateade et path pole paika pandud or something ...
    }
    $start_from=0;
    if($limit) $end_at=$limit; else $end_at=sizeof($imgs);
    if($start)
    {
        $total_pages=ceil(sizeof($imgs)/$limit);
        $start_from=$start;
        $end_at=$start_from+$limit;
    }
    
    if($end_at > sizeof($imgs)) $end_at = sizeof($imgs);

    $j=0;
    $images=array();
    
    for($i=$start_from;$i<$end_at;$i++)
    {
        $images[$j] = new stdClass();
        
        $images[$j]->thumb_path=$site->CONF['wwwroot'].'/'.$imgs[$i]['thumb']; # relative path
        $images[$j]->thumb_height=$imgs[$i]['thumb_height']; # in pixels
        $images[$j]->thumb_width=$imgs[$i]['thumb_width'];

        $images[$j]->image_path=$site->CONF['wwwroot'].'/'.$imgs[$i]['image'];
        $images[$j]->image_height=$imgs[$i]['image_height'];
        $images[$j]->image_width=$imgs[$i]['image_width'];

        $images[$j]->actual_image_path=$site->CONF['wwwroot'].'/'.$imgs[$i]['actual_image'];
        $images[$j]->actual_image_height=$imgs[$i]['actual_image_height'];
        $images[$j]->actual_image_width=$imgs[$i]['actual_image_width'];

        $images[$j]->actual_image_size=&$images[$j]->size; # original
        $images[$j]->filename=$imgs[$i]['filename'];

        $key = search_obj_array($imgs[$i]['filename'],'filename',$files);
        
        if($key !== false)
        {
        	$images[$j]->id = $files[$key]->all['objekt_id'];
	        $images[$j]->title = $files[$key]->pealkiri;
	        $images[$j]->description = $files[$key]->all['kirjeldus'];
	        $images[$j]->size = $files[$key]->all['size']; # final display
	        $images[$j]->buttons = $files[$key]->buttons;
        }
        
        $j++;
    }
    //printr($images);

	$smarty->assign(array(
		$name => $images,
		$name.'_newbutton' => $new_button,
		$name.'_title' => $album->pealkiri, // TODO: deprecate, parent gallery should have it's own tag
		$name.'_first_image' => $images[0]->image_path, # relative path to first image
		$name.'_last_image' => $images[sizeof($images)-1]->image_path,
		$name.'_count' => sizeof($images),
		$name.'_counttotal' => sizeof($imgs),
	));
}
