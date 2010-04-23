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
 * edit_object_metadata.php
 * 
 * used by SEO Module to modify sections title, meta-description and meta-keywords
 * 
 */

function edit_objekt_metadata () {
	global $site;
	$objekt=new Objekt(array('objekt_id'=>$site->fdat['id'],'no_cache'=>'1'))
?>

<form name="frmEdit" action="<?=$site->self?>" method="POST">
<input type=hidden name=tab value="<?=$site->fdat['tab']?>">
<input type=hidden name=id value="<?=$site->fdat['id']?>">
<input type=hidden name=op value="<?=$site->fdat['op']?>">
<input type=hidden name=op2 value="">

<tr> 
	<td valign="top" width="100%" class="scms_dialog_area" height="100%"> 
		<div class="scms_scrolltable_border"> 
			<div style="width:100%;" class="scms_scrolltable_header">
				<table width="100%" cellpadding="0" cellspacing="0">
				</table>
			</div>
			<div id="scrolltableDiv" class="scms_scrolltable" style="height:290px"> 
				<table width="100%"  border="0" cellspacing="0" cellpadding="3">
<?
		//peaks siis otsima millisel esivanemal on SEO meta't
		function getSeoData($id,$clear=false)
		{
			static $seo_meta=array();
			$objekt=new Objekt(array('objekt_id'=>$id));
			if($clear) $seo_meta=array();

			if($objekt->all['meta_title'] && empty($seo_meta['meta_title'])) $seo_meta['meta_title']=$objekt->all['meta_title'];
			if($objekt->all['meta_keywords'] && empty($seo_meta['meta_keywords'])) $seo_meta['meta_keywords']=$objekt->all['meta_keywords'];
			if($objekt->all['meta_description'] && empty($seo_meta['meta_description'])) $seo_meta['meta_description']=$objekt->all['meta_description'];
			
			//if we have all seo data or the the tree trunk (id=0) then end else recurse
			if(($seo_meta['meta_title'] && $seo_meta['meta_keywords'] && $seo_meta['meta_description']) || $id == 0) return $seo_meta;
			else return getSeoData($objekt->all['parent_id']);
		}

		if($objekt->all['objekt_id'])
		{
			$seo_meta=getSeoData($objekt->all['objekt_id']);
			$higher_seo_meta=getSeoData($objekt->all['parent_id'],true);
		}
		else
		{
			$seo_meta=getSeoData($site->fdat['parent_id']);
			$higher_seo_meta=$seo_meta;
		}
		
		?>
		<script type="text/javascript">
			
			var metas = new Array();
			metas['meta_title'] = '<?=str_replace(array("\r", "\n"), ' ', addslashes($higher_seo_meta['meta_title']));?>';
			metas['meta_keywords'] = '<?=str_replace(array("\r", "\n"), ' ', addslashes($higher_seo_meta['meta_keywords']));?>';
			metas['meta_description'] = '<?=str_replace(array("\r", "\n"), ' ', addslashes($higher_seo_meta['meta_description']));?>';

			function toggle(id)
			{
				var switcher=document.getElementById(id);
				if(switcher)
				{
					if(switcher.disabled) switcher.disabled=false;
					else 
					{
						switcher.disabled=true;
						switcher.value=metas[id];
					}
				}
			}
			
		</script>
		<tr> 
            <td class="txt" width="0" valign="top" align=right nowrap><label for="meta_title_toggler"><?=$site->sys_sona(array(sona => "Saidi tiitel", tyyp=>"editor"))?></label> <input id="meta_title_toggler" onclick="toggle('meta_title');" type="checkbox"<?=($objekt->all['meta_title']?' checked="checked"':' ')?>/>:</td>
			<td class="txt" width="100%" nowrap valign="top">
				<input name="meta_title" id="meta_title" class="frm" style="width: 100%" value="<?=htmlspecialchars($seo_meta['meta_title'])?>"<?=(empty($objekt->all['meta_title'])?' disabled="disabled"':' ')?>/> 
			</td>
		</tr>	
		<tr> 
            <td class="txt" width="0" valign="top"  align=right nowrap><label for="meta_desc_toggler">Meta-description</label> <input id="meta_desc_toggler" onclick="toggle('meta_description');" type="checkbox"<?=($objekt->all['meta_description']?' checked="checked"':' ')?>/>:</td>
			<td class="txt" width="100%" nowrap valign="top">
				<textarea name="meta_description" id="meta_description" rows="5" style="width: 100%; padding: 2px; font-family: Tahoma, Verdana; font-size: 11px; color: #000;" value="<?=htmlspecialchars($seo_meta['meta_description'])?>"<?=(empty($objekt->all['meta_description'])?' disabled="disabled"':' ')?>><?=htmlspecialchars($seo_meta['meta_description'])?></textarea> 
			</td>
		</tr>	
		<tr> 
            <td class="txt" width="0" valign="top" align=right nowrap><label for="meta_keywords_toggler">Meta-keywords</label> <input id="meta_keywords_toggler" onclick="toggle('meta_keywords');" type="checkbox"<?=($objekt->all['meta_keywords']?' checked="checked"':' ')?>/>:</td>
			<td class="txt" width="100%" nowrap valign="top">
				<textarea name="meta_keywords" id="meta_keywords" rows="5" style="width: 100%; padding: 2px; font-family: Tahoma, Verdana; font-size: 11px; color: #000;" value="<?=htmlspecialchars($seo_meta['meta_keywords'])?>"<?=(empty($objekt->all['meta_keywords'])?' disabled="disabled"':' ')?>><?=htmlspecialchars($seo_meta['meta_keywords'])?></textarea> 
			</td>
		</tr>	
		<?
	# SEO
	####################################
?>
				</table>
			</div>
		</div>
	</td>
</tr>
<tr> 
	<td align="right" valign="top" class="scms_dialog_area_bottom"> 
		<input type="button" value="<?=$site->sys_sona(array(sona => "apply", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='save';this.form.submit();">
		<input type="button" value="<?=$site->sys_sona(array(sona => "Salvesta", tyyp=>"editor")) ?>" onclick="javascript:frmEdit.op2.value='saveclose';this.form.submit();">
		<input type="button" value="<?=$site->sys_sona(array(sona => "close", tyyp=>"editor")) ?>" onclick="javascript:window.close();"> 
	</td>
</tr>

</form>

<?

}

function salvesta_objekt_metadata () {
	global $site;

	$class_path = "../classes/";

	$objekt=new Objekt(array('objekt_id'=>$site->fdat['id']));
	if ($objekt->all['objekt_id'])
	{
		# -------------------------------
		# Objekti uuendamine andmebaasis    
		# -------------------------------
		$sql = $site->db->prepare("update objekt set meta_title=?, meta_keywords=?, meta_description=? WHERE objekt_id=?",
			$site->fdat['meta_title'],
			$site->fdat['meta_keywords'],
			$site->fdat['meta_description'],
			$objekt->objekt_id
		);
		$sth = new SQL($sql);
		$site->debug->msg($sth->debug->get_msgs());
	}
	else
	{
		$site->debug->msg("sisu pole salvestatud kuna objekt_id puudub");
	}
}
