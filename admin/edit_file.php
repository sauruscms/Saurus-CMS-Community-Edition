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
 * Popup page for editing file data
 *
 */

global $site, $class_path;

####################################
# FUNCTION edit_objekt

function edit_objekt()
{
	global $site;
	global $objekt;
	global $keel;
	global $class_path;
	
	if($objekt->parent_id)
	{
		$parent_folder = new Objekt(array('objekt_id' => $objekt->parent_id, 'on_sisu' => 1));
	}
	elseif($site->fdat['parent_id'])
	{
		$parent_folder = new Objekt(array('objekt_id' => $site->fdat['parent_id'], 'on_sisu' => 1));
	}
	elseif($site->fdat['dir'])
	{
		$sql = $site->db->prepare('select objekt_id from obj_folder where relative_path = ?', '/'.$site->fdat['dir']);
		$result = new SQL($sql);
		
		$parent_folder = new Objekt(array('objekt_id' => $result->fetchsingle(), 'on_sisu' => 1));
	}
	else 
	{
		//screwed, dont know where to put the object
		exit;
	}

?>
<script type="text/javascript">

var form = document.frmEdit;

// save the submit() function
form.actual_submit = form.submit;

// overwrite submit() function
form.submit = function()
{
	if(this.fileupload.value != '')
	{
		var filename = (this.fileupload.value.match(/\\/) ? this.fileupload.value.split(/\\/) : this.fileupload.value.split('/'));
		filename = filename[filename.length - 1];
		
		$.ajax({
		    url: 'ajax_response.php?rand=' + Math.random(9999),
		    data: {op: 'check_file', name: '<?=$parent_folder->all['relative_path'];?>/' + filename},
		    type: 'POST',
		    dataType: 'json',
		    timeout: 10000,
		    error: function(){
		    },
		    success: function(response){
				if(response.file_exists && confirm('<?=$site->sys_sona(array('sona' => 'overwrite_file', 'tyyp' => 'Admin'));?>'))
				{
					// use the original submit function
					form.actual_submit();
				}
				else if(!response.file_exists)
				{
					// use the original submit function
					form.actual_submit();
				}
				else
				{
					document.body.style.cursor = 'default';
				}
		    }
		});
	}
	else
	{
		// use the original submit function
		form.actual_submit();
	}
}

function setPealkiri (strPealkiri) {
	var algus=0;
	var saved_algus=0;
	var p_elem = document.getElementById('pealkiri');
	var filename = document.getElementById('filename');
	filename.value = strPealkiri;
	
	// \ to /
	strPealkiri = strPealkiri.replace(/\\/g, '/');
	// strip out file extension
	strPealkiri = strPealkiri.replace(/\.[^\.]*$/g, '');
	// strip out file path
	strPealkiri = strPealkiri.replace(/^(.*)\//g, '');
	
	p_elem.value = strPealkiri;
}
</script>

	<input type="hidden" name="file" value="<?= $site->fdat['file'] ?>">
	<input type="hidden" name="dir" value="<?= $site->fdat['dir'] ?>">
	<input type="hidden" name="parent_id" value="<?php echo $parent_folder->objekt_id; ?>">
	<input type="hidden" name="in_wysiwyg" value="<?php echo ($site->fdat['dir'] ? 1 : 0) ?>">
	<input type="hidden" name="publish" value="1"> <?php ### Bug #2321 ?>

    <input type="hidden" name="sorting" value="<?=$site->fdat['sorting'];?>">

	<input type="hidden" name="callback" value="<?=$site->fdat['callback'];?>">

	<?php ################ upload file ?>

		<tr>
          <td width="20%" nowrap><?= $site->sys_sona(array(sona => "Upload", tyyp=>"files")) ?>:</td>
		  <input type="hidden" name="filename" id="filename" value="">
          <td width="80%" nowrap>
            <input name="fileupload" id="fileupload" onChange="setPealkiri(this.value)" type="file" class="scms_flex_input" style="border:0;">
          </td>
			<?php #################### thumbnail?>
			<td rowspan="2" align="center" valign="middle" style="padding-right:15px">
				<?php 
				if($objekt->objekt_id && $objekt->all['relative_path'])
				{
					//Find the correct thumbnail
					$thumbnail_file = str_replace($objekt->all['filename'], '.thumbnails/'.$objekt->all['filename'], $objekt->all['relative_path']);
					$thumbnail_path = preg_replace('#/$#', '', $site->absolute_path).$thumbnail_file;
					$thumbnail_url = $site->CONF['wwwroot'].$thumbnail_file;
					
					$thumb = $thumbnail_url;

					$img_href = $site->CONF['wwwroot'].'/file.php?'.$objekt->objekt_id;
					$i_width = 10;
					$i_height = 10;
				?>
                <!-- Thumbnail -->
				<a href="javascript:void(openpopup('<?= $img_href ?>', 'popup',<?= $i_width ?>, <?= $i_height ?>, 'yes'))"><IMG SRC="<?= $thumb ?>" BORDER="0" ALT=""></a>
				<!-- // Thumbnail -->   
				<?php } ?>
               </td>
        </tr>


	<?php ################ filename ?>
	  <tr>
			<td width="20%" nowrap valign="top"><?= $site->sys_sona(array(sona => "filename", tyyp=>"files")) ?>:</td>
			<td width="100%" valign="top">
				<?= $objekt->all['filename'] ?>
			</td>
		</tr>

	</table>

    <!-- Profile fields -->
	<?php ############################ PROFILE TABLES 

  			$sql = $site->db->prepare("SELECT profile_id AS id, source_table AS parent, name FROM object_profiles WHERE source_table=? ORDER BY name",'obj_file');
			$sth = new SQL($sql);

			# get object profile
			if($objekt->all['profile_id']) {
				$profile_def = $site->get_profile(array("id"=>$objekt->all['profile_id']));
			}
			elseif($site->fdat['profile_id'])
			{
				$profile_def = $site->get_profile(array("id"=>$site->fdat['profile_id']));
			}
			# if still not found then use default profile for this class
			if(!$profile_def['profile_id']) {
				$site->fdat['profile_id'] = $site->get_default_profile_id(array(source_table => 'obj_file'));
				$profile_def = $site->get_profile(array("id"=>$site->fdat['profile_id']));
			}
			$site->fdat['profile_id'] = $profile_def['profile_id'];
		?>
		<br />
		<table width="100%"  border="0" cellspacing="3" cellpadding="0" class="scms_borderbox">

		<tr>
			<td colspan="2">
            <div style="position:relative">
              <div class="scms_borderbox_label">

              <SELECT onchange="changeProfile(this)" NAME="profile_id" class="scms_flex_input" style="width:120px">
				<?php 
				$all_profiles_hash = array();
				while ($profile_data = $sth->fetch()){
					$all_profiles_hash[] = $profile_data['id'];

					print "<option value='".$profile_data['id']."' ".($profile_data['id']==$site->fdat['profile_id'] ? '  selected':'').">".$site->sys_sona(array(sona => $profile_data['name'], tyyp=>"custom"))."</option>";
				} ?>
				</SELECT>
			</div>
	<?php ##### hidden field "profile_id" if selectbox is disabled OR module "Profile" is not allowed
	if($site->fdat['profile_locked']) {?>
		<input type="hidden" name="profile_id" value="<?=$site->fdat['profile_id']?>">
	<?php }?>
            </div>
          </td>
        </tr>
	<?php ###### profile fields row ?>
		<tr>
			<td valign=top colspan="2" style="height:130px">
	<?php 
	#################
	# Loop throug all profiles

	foreach($all_profiles_hash as $profile_id) {
	?>
	<!-- Scrollable area -->
	<div id="profile_<?= $profile_id ?>" class="scms_scroll_div" style="display: <?= ($site->fdat['profile_id']==$profile_id?'block':'none') ?>; height: 130px">

		<table width="90%" border=0 cellspacing=0 cellpadding=0>
			<tr><td colspan=2>&nbsp;</td></tr>
		<?php 
		$profile_def = $site->get_profile(array("id"=>$profile_id));
		$profile_fields = unserialize($profile_def['data']);

		# if profile fields exist
		if(is_array($profile_fields) && sizeof($profile_fields)>0){

			## add suffix for each field, to get unique id-s
			foreach($profile_fields as $key=>$tmp_prof){
				$profile_fields[$key]['html_fieldname'] = $profile_fields[$key]['name']."_".$profile_id;
			}
			#printr($profile_fields);

			###################
			# print profile fields rows
			print_profile_fields(array(
				'profile_fields' => $profile_fields,
				'field_values' => $objekt->all,
				'fields_width' => '300px',
			));

		} # if profile fields exist

		?>
		</table>
	</div>
	<?php 
		} //foreach
	?>
	</td>
	</tr>
	<!-- //Profile fields -->
	<?php 
		
	############################ / PROFILE TABLES 
}
# / FUNCTION edit_objekt
####################################


####################################
# FUNCTION salvesta_objekt

function salvesta_objekt () {
	global $site;
	global $objekt;
	global $class_path;

    if ($objekt->objekt_id) {

		if ($objekt->on_sisu_olemas)
		{
			# -------------------------------
			# Objekti uuendamine andmebaasis    
			# -------------------------------

			$parent_folder = new Objekt(array('objekt_id' => $objekt->parent_id, 'on_sisu' => 1));

			$fileupload = $_FILES['fileupload'];

			if($fileupload['name'] && $parent_folder->all['relative_path'])
			{
				$fileupload['name'] = safe_filename2($fileupload['name']);
				
				$upload_path = preg_replace('#/$#', '', $site->absolute_path).$parent_folder->all['relative_path'];
				$upload = upload_file_to_folder($fileupload, $upload_path);
				
				if($upload === true)
				{
					create_file_thumbnail($upload_path.'/'.$fileupload['name']);
	
					$pealkiri = ($site->fdat['pealkiri'] ? $site->fdat['pealkiri'] : $fileupload['name']);
	
					$mimetype = get_file_mime_content_type($upload_path.'/'.$fileupload['name']);
					$pathinfo = pathinfo($upload_path.'/'.$fileupload['name']);
	
					############ 1) update record in object content table:
					$sql = $site->db->prepare("update obj_file set relative_path = ?, filename = ?, mimetype = ?, size = ? where objekt_id = ?;",
						$parent_folder->all['relative_path'].'/'.$fileupload['name'],
						$pathinfo['basename'],
						$mimetype,
						filesize($upload_path.'/'.$fileupload['name']),
						$objekt->objekt_id
					);
					$sth = new SQL($sql);
					
					//if($site->fdat['dir']) refresh_gallery_images($objekt, $site->fdat['dir']);
					
				}//if fullpath
				
			}//if file size

			############ 1) create always record in object content table:
			$sql = $site->db->prepare("UPDATE obj_file SET profile_id=? WHERE objekt_id=?",$site->fdat['profile_id'],$objekt->objekt_id);
			$sth = new SQL($sql);
			$site->debug->msg($sth->debug->get_msgs());
			
			if($site->fdat['in_wysiwyg'] == 1)
			{
				$objekt->all['in_wysiwyg_filename'] = $pathinfo['basename']; // very ugly workaround for bug #2269 
				//printr($objekt->all['in_wysiwyg_filename']);
			}

		} else {
			# -------------------------------
			# Objekti loomine andmebaasis    
			# -------------------------------

			/*
			 * Upload file data and make thumbnail
			*/

			/*
			# old usage of "dir" - when files where not content objects as should,
			# find "dir" value from "parent_id" (parent_id overrules any "dir" value):
			if($site->fdat['parent_id']){
				$sql = $site->db->prepare("SELECT * FROM obj_folder  WHERE objekt_id = ?", $site->fdat['parent_id']);
				$sth = new SQL($sql);
				$parent_folder = $sth->fetch();
			}
			*/
			
			if($objekt->parent_id)
			{
				$parent_folder = new Objekt(array('objekt_id' => $objekt->parent_id, 'on_sisu' => 1));
			}
			elseif($site->fdat['parent_id'])
			{
				$parent_folder = new Objekt(array('objekt_id' => $site->fdat['parent_id'], 'on_sisu' => 1));
			}
			elseif($site->fdat['dir'])
			{
				$sql = $site->db->prepare('select objekt_id from obj_folder where relative_path = ?', '/'.$site->fdat['dir']);
				$result = new SQL($sql);
				
				$parent_folder = new Objekt(array('objekt_id' => $result->fetchsingle(), 'on_sisu' => 1));
			}
			else 
			{
				//screwed, dont know where to put the object
				exit;
			}
			
			$site->fdat['dir'] = preg_replace('#^/#', '', $parent_folder->all['relative_path']);
			
			$fileupload = $_FILES ['fileupload'];

			if ($fileupload['name'] && $parent_folder->all['relative_path']) {
				
				$fileupload['name'] = safe_filename2($fileupload['name']);
				
				$upload_path = preg_replace('#/$#', '', $site->absolute_path).$parent_folder->all['relative_path'];
				$upload = upload_file_to_folder($fileupload, $upload_path);
				
				$fullpath = $upload_path.'/'.$fileupload['name'];
				
				if($upload === true)
				{
					create_file_thumbnail($fullpath);

					$pealkiri = $site->fdat['pealkiri']?$site->fdat['pealkiri']:$fileupload['name'];
	
					$mimetype = get_file_mime_content_type($fullpath);
					$pathinfo = pathinfo($fullpath);
	
					############ 1) create always record in object content table:
					$sql = $site->db->prepare("INSERT INTO obj_file (objekt_id, relative_path, filename, mimetype, size, profile_id) VALUES (?,?,?,?,?,?)",
						$objekt->objekt_id,
						$parent_folder->all['relative_path'].'/'.$pathinfo['basename'],
						$pathinfo['basename'],
						$mimetype,
						filesize($fullpath),
						$site->fdat['profile_id']
					);
					#print $sql."<hr>";
					$sth = new SQL($sql);
					
					if($site->fdat['in_wysiwyg'] == 1)
					{
						$objekt->all['in_wysiwyg_filename'] = $pathinfo['basename']; // very ugly workaround for bug #2269 
					}
					
					if($site->fdat['dir']) refresh_gallery_images($objekt, $site->fdat['dir']);
				}//if fullpath
			}//if file size

		}//if update or insert

	} else {
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}
}
# / FUNCTION salvesta_objekt
####################################

if(!function_exists('refresh_gallery_images'))
{
	function refresh_gallery_images(&$objekt, $dir)
	{
		global $site, $class_path;
		
		include_once($class_path.'picture.inc.php');
		
		// find album objects where the parent folder of the file is used
		$sql = "select ttyyp_params from objekt where tyyp_id = 16 and ttyyp_params like '%path = ".mysql_real_escape_string($dir)."\n%' limit 1";
		
		$result = new SQL($sql);
		while($ttyyp_params = $result->fetchsingle())
		{
			$conf = new CONFIG($ttyyp_params);
			$conf->get('');
			
			generate_images($site->absolute_path.$conf->get('path'), $conf->get('tn_size'), $conf->get('pic_size'));
		}
	}
}
