/* SCMS context menu actions */

function getWindowSize(objectType)
{
	var windowSize = { width: '450', height: '430' }; // default
	
	// articles
	if(objectType == '2')
	{
		windowSize.width = '880';
		windowSize.height = '660';
	};
	
	// sections
	if(objectType == '1')
	{
		windowSize.width = '512';
		windowSize.height = '251';
	};
	
	// albums
	if(objectType == '16')
	{
		windowSize.width = '512';
		windowSize.height = '281';
	};
	
	return windowSize;
}

// edit object action			
function scmsNewObject()
{
	var objectData = jQuery(this).data('objectData');
	
	var attrs = objectData.anchor.getAttributes(/^scms_/);
	
	avaaken(attrs['scms_url'] + 'admin/edit.php?op=new&keel=' + attrs['scms_object_lang_id'] + '&parent_id=' + attrs['scms_object_parent_id'] + '&kesk=' + attrs['scms_object_position'] + '&ttyyp_id=' + attrs['scms_object_template_id'] + '&tyyp_idlist=' + attrs['scms_object_type_list'] + '&profile_id=' + attrs['scms_object_profile_id'] + '&allow_comments=' + attrs['scms_object_allow_comments']+ '&publish=' + attrs['scms_object_publish'] + '&sorting=' + attrs['scms_object_sorting'], getWindowSize(attrs['scms_object_type_list']).width, getWindowSize(attrs['scms_object_type_list']).height);
	
	// return false to stop click propagation
	return false;
}

// edit object action			
function scmsEditObject()
{
	var objectData = jQuery(this).data('objectData');
	
	var attrs = objectData.anchor.getAttributes(/^scms_/);
	
	avaaken(attrs['scms_url'] + 'admin/edit.php?op=edit&id=' + attrs['scms_object_id'] + '&keel=' + attrs['scms_object_lang_id'] + '&parent_id=' + attrs['scms_object_parent_id'] + '&kesk=' + attrs['scms_object_position'] + '&tyyp_idlist=' + attrs['scms_object_type_list'], getWindowSize(attrs['scms_object_type_list']).width, getWindowSize(attrs['scms_object_type_list']).height);
	
	// return false to stop click propagation
	return false;
}

// move object up
function scmsMoveObjectUp()
{
	var objectData = jQuery(this).data('objectData');
	
	var attrs = objectData.anchor.getAttributes(/^scms_/);
	
	window.location.href = attrs['scms_url'] + 'admin/move.php?url=' + attrs['scms_self'] + '&op=up&id=' + attrs['scms_object_id'] + '&parent_id=' + attrs['scms_object_parent_id'] + '&kesk=' + attrs['scms_object_position'];
	
	// return false to stop click propagation
	return false;
}

// move object down
function scmsMoveObjectDown()
{
	var objectData = jQuery(this).data('objectData');
	
	var attrs = objectData.anchor.getAttributes(/^scms_/);
	
	window.location.href = attrs['scms_url'] + 'admin/move.php?url=' + attrs['scms_self'] + '&op=down&id=' + attrs['scms_object_id'] + '&parent_id=' + attrs['scms_object_parent_id'] + '&kesk=' + attrs['scms_object_position'];
	
	// return false to stop click propagation
	return false;
}

// change object publish status
function scmsToggleObjectPublishing()
{
	var objectData = jQuery(this).data('objectData');
	
	var attrs = objectData.anchor.getAttributes(/^scms_/);

	window.location.href = attrs['scms_url'] + 'admin/publish.php?url=' + attrs['scms_self'] + '&id=' + attrs['scms_object_id'] + '&op=' + (attrs['scms_object_is_published'] == 1 ? 'hide' : 'publish') + '&parent_id=' + attrs['scms_object_parent_id'] + '&kesk=' + attrs['scms_object_position'];
	
	// return false to stop click propagation
	return false;
}

// delete object
function scmsDeleteObject()
{
	var objectData = jQuery(this).data('objectData');
	
	var attrs = objectData.anchor.getAttributes(/^scms_/);

	avaaken(attrs['scms_url'] + 'admin/delete.php?id=' + attrs['scms_object_id'] + '&parent_id=' + attrs['scms_object_parent_id'], '413', '108', 'delete');
	
	// return false to stop click propagation
	return false;
}