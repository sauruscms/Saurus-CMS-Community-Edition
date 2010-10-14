$(document).ready(function()
{
	initFolders(folder_tree);
	
	initFiles(files);
	
	setCreateFolderDialogueActions();
	
	$('a.all_files_selector').click(selectAllFiles);
	$('a.all_files_deselector').click(deSelectAllFiles);
	
	$('a#file_multi_delete').click(deleteSelectedFiles);
	
	$('a#file_multi_move').click(moveSelectedFiles);
	
	initFavorites();
	
	$('a#switch_to_thumbs').click(toggleFileView);
	$('a#switch_to_list').click(toggleFileView);
	
	$('a#switch_to_list').removeClass('hidden');
	
	$('div#search_start').click(function ()
	{
		$('input#search_text').focus();
		searchFiles();
	});

	$('div#search_clear').click(function ()
	{
		clearSearch();
	});

	$('input#search_text').focus(function ()
	{
		if(this.value == translations.search_files + ': ') this.value = '';
	});
	
	$('input#search_text').blur(function ()
	{
		if(this.value == '') this.value = translations.search_files + ': ';
	});
	
	$('input#search_text').keypress(function (event)
	{
		$('div#search_start').removeClass('hidden');
		$('div#search_clear').addClass('hidden');
		
		// enter
		if(event.which == 13)
		{
			searchFiles();
		}
		// esc = 0
	});
	
	$('input#upload_new_file').click(function ()
	{
		openpopup('edit.php?op=new&tyyp_id=21&parent_id=' + open_folder_id + '&callback=window.opener.fileUpdated', 'editfile', 450 , 430);		
	});
	
	$('input#paging_text').keypress(function (event)
	{
		// enter
		if(event.which == 13)
		{
			changeFilePage(this.value);
		}
	});
	
	$('a#paging_previous').click(function ()
	{
		changeFilePage(file_page - 1);
	});
	
	$('a#paging_next').click(function ()
	{
		changeFilePage(file_page + 1);
	});
	
	liveActions();
	
	if(selected_file)
	{
		scrollToFile(selected_file);
		selectFileById(selected_file);
	}
	
	setContentDimensions();
	
	$(window).resize(function()
	{
		setContentDimensions();
	});
});

function customActionTrigger()
{
	var data = {files: [], folders: []};
	
	if(settings.select_mode == 1) // single file
	{
		var selected_files = getSelectedFiles();
		data.files.push(files[open_folder_id].files[file_page][selected_files[0]]);
	}
	else if(settings.select_mode == 2) // single folder
	{
		data.folders.push(folder_tree[open_folder_id]);
	}
	
	settings.callbackHandler(data);
}

function changeFilePage(page)
{
	page = Number(page);
	
	if(page)
	{
		if(page < 1) return;
		if(page > Math.ceil(files[open_folder_id].total_files / 100)) return;
		if(page == file_page) return;
		
		file_page = page;
		$('input#paging_text').attr('value', file_page);
		
		deSelectAllFiles();
		
		if($('div#folder_thumbnails_' + open_folder_id).children('div#folder_thumbs_page_' + file_page).length > 0)
		{
			if(view_mode == 'thumbs')
			{
				$('div#folder_thumbnails_' + open_folder_id).children('div').addClass('hidden');
				$('div#folder_thumbnails_' + open_folder_id).children('div#folder_thumbs_page_' + file_page).removeClass('hidden');
			}
			else
			{
				$('table#folder_files_' + open_folder_id).children('tbody').addClass('hidden');
				$('table#folder_files_' + open_folder_id).children('tbody#folder_list_page_' + file_page).removeClass('hidden');
			}
			
			$('div#scms_listing_contents').scrollTo(0);
		}
		else
		{
			// load new page of files
			disableFilemanager();
			
			if(open_folder_id == 1)
			{
				var data = { op: 'search_files', keyword: $('input#search_text').attr('value'), sort_by: sorting_column, sort_dir: sorting_direction, page: file_page};
			}
			else
			{
				var data = { op: 'get_folder_files', folder_id: open_folder_id, sort_by: sorting_column, sort_dir: sorting_direction, page: file_page };
			}
			
			$.ajax({
			    url: site_url + '/admin/ajax_response.php',
			    cache: false,
			    data: data,
			    type: 'POST',
			    dataType: 'json',
			    timeout: ajax_timeout,
			    error: function(XMLHttpRequest, textStatus, errorThrown)
			    {
			    	alert(textStatus);
					enableFilemanager();
			    },
			    success: function(response, textStatus)
			    {
			    	if(!response.error)
			    	{
			    		addToFiles(response.files, response.folder_id);
						
			    		files[response.folder_id].total_files = response.files.total_files;
						
			    		files[response.folder_id].files[file_page] = response.files.files[file_page];
			    		
			    		showThumbnails(folder_tree[open_folder_id]);
			    		showFileList(folder_tree[open_folder_id]);
			    		
						$('div#scms_listing_contents').scrollTo(0);
						
						enableFilemanager();
			    	}
			    	else
			    	{
						messageDialog(response.error_message);
			    	}
			    }
			});
		}
	}
}

function addToFiles(files_to_add, folder_id)
{
	files[folder_id].total_files = files_to_add.total_files;
	
	files[folder_id].files[file_page] = files_to_add.files[file_page];
}

function scrollToFile(file_id)
{
	if(view_mode == 'thumbs')
	{
		$('div#scms_listing_contents').scrollTo('div#file_thumbnail_container_' + file_id);
	}
}

function initFavorites()
{
	if(favorites)
	{
		for(var i in favorites)
		{
			$('table#scms_favorites_table').append(getFavoriteHTML(favorites[i]));
		}
		
		addFavoriteHovers();
		
		$('div#scms_favorites').removeClass('hidden');
	}
}

function getFavoriteHTML(favorite)
{
	return '<tr class="' + favorite.klass + '"><td class="link_cell"><div class="icon"><a class="favorite_link" rel="' + favorite.klass + '" id="favorite_' + favorite.objekt_id + '" href="javascript:void(0);" title="' + favorite.title + '">' + favorite.title + '</a></div></td><td class="context_button_cell"><img class="context_button_delete invisible" id="favorite_delete_' + favorite.objekt_id + '" src="' + site_url + '/styles/default/gfx/filemanager/delete.png" width="16" height="16" /></td></tr>';
}

function toggleFavorite(objekt_id)
{
	disableFilemanager();
	
	$('tr#single_file_favorite_row_' + objekt_id).toggleClass('hidden');
	
	$.ajax({
	    url: site_url + '/admin/ajax_response.php',
	    data: { op: 'toggle_favorite', objekt_id: objekt_id },
	    cache: false,
	    type: 'POST',
	    dataType: 'json',
	    timeout: ajax_timeout,
	    error: function(XMLHttpRequest, textStatus, errorThrown)
	    {
	    	alert(textStatus);
			enableFilemanager();
	    },
	    success: function(response, textStatus)
	    {
	    	$('table#scms_favorites_table').empty();
	    	
	    	if(response.favorites)
	    	{
				for(var i in response.favorites)
				{
					$('table#scms_favorites_table').append(getFavoriteHTML(response.favorites[i]));
				}
				
				addFavoriteHovers();
	    		setFavorites(response.favorites);
	    		
	    		$('div#scms_favorites').removeClass('hidden');
	    	}
	    	else
	    	{
	    		$('div#scms_favorites').addClass('hidden');
	    		setFavorites([]);
	    	}
	    	
	    	enableFilemanager();
	    }
	});
}

function deleteFavorite()
{
	var objekt_id = $(this).attr('id').replace('favorite_delete_', '');
	
	$('img.context_button_anchor').addClass('invisible');
	$('div.context_button_container').remove();
	
	toggleFavorite(objekt_id);
}

function setFavorites(favs)
{
	favorites = favs;
}

function clearSearch()
{
	delete files[1];
	$('div#folder_thumbnails_' + 1).remove();
	$('table#folder_files_' + 1).remove();
	
	file_page = 1;
	
	open_folder_id = open_folder_id_save;
	
	toggleFolder(open_folder_id);
	
	$('input#search_text').attr('value', translations.search_files + ': ');
	
	$('div#search_start').removeClass('hidden');
	$('div#search_clear').addClass('hidden');
	
	$('div#scms_no_search_results').addClass('hidden');
	
	$('table#scms_file_and_folder_tools').removeClass('hidden');
	$('div#scms_left_pane_cover').addClass('hidden');
}

function searchFiles(search_page)
{
	var keyword = $('input#search_text').attr('value');
	
	if(keyword.length < 1)
	{
		return;
	}
	
	if(search_page)
	{
		file_page = search_page;
	}
	else
	{
		file_page = 1;
	}
	
	disableFilemanager();

	for(var objekt_id in folder_tree) delete files[objekt_id];
	$('div#scms_file_list').empty();
	$('div#scms_file_thumbnails').empty();
	
	// load
	$.ajax({
	    url: site_url + '/admin/ajax_response.php',
	    cache: false,
	    data: { op: 'search_files', keyword: keyword, sort_by: sorting_column, sort_dir: sorting_direction, page: file_page },
	    type: 'POST',
	    dataType: 'json',
	    timeout: ajax_timeout,
	    error: function(XMLHttpRequest, textStatus, errorThrown)
	    {
	    	alert(textStatus);
			enableFilemanager();
	    },
	    success: function(response, textStatus)
	    {
	    	if(!response.error)
	    	{
	    		resetFiles(response.files, 1);
				
				files[1] = response.files;
		    	
				if(open_folder_id != 1) open_folder_id_save = open_folder_id;
				open_folder_id = 1;
				
				if(response.files.total_files == 0)
				{
					noFilesToDisplay();
					
					$('table#scms_file_and_folder_tools').addClass('hidden');
					$('div#scms_left_pane_cover').removeClass('hidden');
					
					$('div#search_start').addClass('hidden');
					$('div#search_clear').removeClass('hidden');
				}
				else
				{
					initFiles(files);
					
					$('div#search_start').addClass('hidden');
					$('div#search_clear').removeClass('hidden');
					
					$('table#scms_file_and_folder_tools').addClass('hidden');
					$('div#scms_left_pane_cover').removeClass('hidden');
				}
	    	}
	    	else
	    	{
				messageDialog(response.error_message);
				$('div#scms_file_thumbnails').addClass('hidden');
	    	}
	    	
			enableFilemanager();
	    }
	});		
}

function noFilesToDisplay()
{
	$('div#scms_file_thumbnails').addClass('hidden');
	$('div#scms_file_list').addClass('hidden');
	$('div#scms_files_info').addClass('hidden');
	$('div#scms_listing_left_actions_bar').addClass('hidden');
	$('div#scms_paging').addClass('hidden');
	if(settings.select_mode == 2 && open_folder_id != 1)
	{
		$('span#custom_action_text').addClass('hidden');
		$('a#custom_action').removeClass('hidden');
		$('div#custom_actions').removeClass('hidden');
		$('div#scms_listing_actions_bar').removeClass('hidden');
		$('div#files_are_selected').addClass('hidden');
		$('div#no_files_are_selected').addClass('hidden');
	}
	else
	{
		$('div#scms_listing_actions_bar').addClass('hidden');
		$('div#custom_actions').addClass('hidden');
	}
	
	if(open_folder_id == 1)
	{
		$('div#scms_no_search_results').removeClass('hidden');
		$('div#scms_no_files').addClass('hidden');
	}
	else
	{
		$('div#scms_no_files').removeClass('hidden');
		$('div#scms_no_search_results').addClass('hidden');
	}
}

function toggleSorting(column)
{
	if(sorting_column == column)
	{
		if(sorting_direction == 'asc')
		{
			sorting_direction = 'desc';
		}
		else if(sorting_direction == 'desc')
		{
			sorting_direction = 'asc';
		}
	}
	
	sorting_column = column;
	
	sortFiles();
}

function sortFiles()
{
	disableFilemanager();
	
	var selected_files = getSelectedFiles();

	for(var objekt_id in folder_tree) delete files[objekt_id];
	$('div#scms_file_list').empty();
	$('div#scms_file_thumbnails').empty();
	
	if(open_folder_id == 1)
	{
		var data = { op: 'search_files', keyword: $('input#search_text').attr('value'), sort_by: sorting_column, sort_dir: sorting_direction, page: file_page };
	}
	else
	{
		var data = { op: 'get_folder_files', folder_id: open_folder_id, sort_by: sorting_column, sort_dir: sorting_direction, page: file_page };
	}
	
	// load
	$.ajax({
	    url: site_url + '/admin/ajax_response.php',
	    cache: false,
	    data: data,
	    type: 'POST',
	    dataType: 'json',
	    timeout: ajax_timeout,
	    error: function(XMLHttpRequest, textStatus, errorThrown)
	    {
	    	alert(textStatus);
			enableFilemanager();
	    },
	    success: function(response, textStatus)
	    {
	    	if(!response.error)
	    	{
				resetFiles(response.files, response.folder_id);
				
				files[response.folder_id] = response.files;
		    	
				if(response.files.total_files == 0)
				{
					noFilesToDisplay();
				}
				else
				{
					initFiles(files);
				}
				
				for(var i in selected_files)
				{
					selectFile($('input#file_selector_' + selected_files[i]));
				}
	    	}
	    	else
	    	{
				messageDialog(response.error_message);
				$('div#scms_file_thumbnails').addClass('hidden');
	    	}
	    	
			enableFilemanager();
	    }
	});	
}

function toggleFileView()
{
	if(view_mode == 'thumbs')
	{
		view_mode = 'list';
		
		$('a#switch_to_list').addClass('hidden');
		$('a#switch_to_thumbs').removeClass('hidden');
	}
	else if(view_mode == 'list')
	{
		view_mode = 'thumbs';
		
		$('a#switch_to_thumbs').addClass('hidden');
		$('a#switch_to_list').removeClass('hidden');
	}
	
	var cookie_exp = new Date();
	cookie_exp.setFullYear(cookie_exp.getFullYear() + 1);
	setCookie('scms_filemanager_view_mode', view_mode, cookie_exp);
	
	initFiles();
}

function fileUpdated(file)
{
	delete files[file.parent_id];
	
	$('table#folder_files_' + file.parent_id).remove();
	$('div#folder_thumbnails_' + file.parent_id).remove();
	
	if(open_folder_id == 1)
	{
		// redo the search
		searchFiles(file_page);
	}
	else
	{
		toggleFolder(file.parent_id);
	}
}

function getSelectedFiles()
{
	var files = [];
	
	$('input.file_selector:checked').each(function()
	{
		var file_id = $(this).attr('value');
		var add = true;
		
		for(var i in files) if(files[i] == file_id)
		{
			add = false;
			break;
		}
		
		//files[$(this).attr('value')] = $(this).attr('value');
		if(add) files.push($(this).attr('value'));
	});
	
	return files;
}

function deleteSelectedFiles()
{
	var files = getSelectedFiles();
	
	if(files.length)
	{
		confirmDialog(translations.files_delete_confirmation, function () { deleteFiles(files) });
	}
}

function moveSelectedFiles()
{
	var files = getSelectedFiles();
	
	if(files.length)
	{
		moveFilesDialog(files);
	}
}

function moveFilesDialog(files)
{
	files_to_move = files;
	folder_selection_window = openpopup(site_url + '/admin/explorer.php?swk_setup=folder_selection&editor=1&objekt_id=0&lang=1', 'cms_explorer', 600, 450);
}

function moveFilesHandler(objects)
{
	folder_selection_window.close();
	window.focus();
	moveFiles(objects[0].objekt_id, files_to_move);
}

function moveFiles(to_folder_id, files_to_move)
{
	var parents_and_files = [];
	
	for(var objekt_id in files_to_move)
	{
		for(var folder_id in files)
		{
			if(files[folder_id].files[file_page][files_to_move[objekt_id]] != undefined)
			{
				if(parents_and_files[files[folder_id].files[file_page][files_to_move[objekt_id]].parent_id] == undefined) parents_and_files[files[folder_id].files[file_page][files_to_move[objekt_id]].parent_id] = [];
				parents_and_files[files[folder_id].files[file_page][files_to_move[objekt_id]].parent_id].push(files_to_move[objekt_id]);
			}
		}
	}
	
	for(var folder_id in parents_and_files)
	{
		disableFilemanager();
	
		if(folder_id == to_folder_id) if(open_folder_id == 1)
		{
	    	for(var i in parents_and_files[folder_id])
	    	{
	    		removeFile(parents_and_files[folder_id][i]);
	    	}
	    	
	    	delete files[to_folder_id];
	    	resetFolderFiles(to_folder_id);
	    	
	    	delete files[open_folder_id];
	    	resetFolderFiles(open_folder_id);
	    	
			toggleFolder(open_folder_id);
	    	
	    	enableFilemanager();
			
	    	continue;
		}
		
		$.ajax({
		    url: site_url + '/admin/ajax_response.php',
		    data: {op: 'move_files', from_folder_id: folder_id, to_folder_id: to_folder_id, files: parents_and_files[folder_id].join(',')},
		    cache: false,
		    type: 'POST',
		    dataType: 'json',
		    timeout: ajax_timeout,
		    error: function(XMLHttpRequest, textStatus, errorThrown)
		    {
		    	alert(textStatus);
		    	enableFilemanager();
		    },
		    success: function(response, textStatus)
		    {
		    	if(response.error && response.error < 3)
		    	{
					messageDialog(response.error_message);
		    	}
		    	else if(response.error)
		    	{
					messageDialog(translations.unable_to_move_files);
		    	}
		    	
		    	for(var i in response.moved_files)
		    	{
		    		removeFile(response.moved_files[i]);
		    	}
		    	
		    	delete files[to_folder_id];
		    	resetFolderFiles(to_folder_id);

		    	if(open_folder_id != 1)
		    	{
			    	delete files[open_folder_id];
			    	resetFolderFiles(open_folder_id);
			    	
					toggleFolder(open_folder_id);
		    	}
		    	
		    	enableFilemanager();
		    }
		});
	}
}

function resetFolderFiles(folder_id)
{
	delete files[folder_id];
	$('table#folder_files_' + folder_id).remove();
	$('div#folder_thumbnails_' + folder_id).remove();
}

function messageDialog(message)
{
	disableFilemanager();
	
	$('div#scms_dialog, input#message_ok_button').removeClass('hidden');
	
	$('td#message_cell').text(message);
	
	$('input#message_ok_button').click(function ()
	{
		$('div#scms_dialog, input#message_ok_button').addClass('hidden');
		
		$('input#message_ok_button').unbind('click');
		
		enableFilemanager();
	});
}

function confirmDialog(question, ok_handler)
{
	disableFilemanager();
	
	$('div#scms_dialog, input#message_ok_button, input#message_cancel_button').removeClass('hidden');
	
	$('td#message_cell').text(question);
	
	$('input#message_ok_button').click(function ()
	{
		hideConfirmDialog();
		ok_handler();
	});
	
	$('input#message_cancel_button').click(hideConfirmDialog);
}

function hideConfirmDialog()
{
	$('div#scms_dialog, input#message_ok_button, input#message_cancel_button').addClass('hidden');
	
	$('input#message_ok_button, input#message_cancel_button').unbind('click');
	
	enableFilemanager();
}

function selectAllFiles()
{
	if(files[open_folder_id] != undefined)
	{
		for(var objekt_id in files[open_folder_id].files[file_page])
    	{
    		if(objekt_id != open_folder_id)
    		{
    			if(view_mode == 'list')
    			{
	    			var checkbox = $('input#file_selector_' + objekt_id);
    			}
    			else
    			{
	    			var checkbox = $('input#file_thumb_selector_' + objekt_id);
    			}
    			
				var file_id = $(checkbox).attr('value');
				
				$('input#file_thumb_selector_' + objekt_id).attr('checked', 'checked');
				$('input#file_selector_' + objekt_id).attr('checked', 'checked');
				
				$('tr#file_data_container_' + file_id).addClass('selected');
				$('td#file_thumb_cell_' + file_id).addClass('selected');
    		}
    	}
		
    	toggleSelectionActions();
	}
	else
	{
		alert('no files here');
	}
}

function deSelectAllFiles()
{
	if(files[open_folder_id] != undefined)
	{
		for(var objekt_id in files[open_folder_id].files[file_page])
    	{
    		if(objekt_id != open_folder_id)
    		{
    			if(view_mode == 'list')
    			{
	    			var checkbox = $('input#file_selector_' + objekt_id);
    			}
    			else
    			{
	    			var checkbox = $('input#file_thumb_selector_' + objekt_id);
    			}
    			
				var file_id = $(checkbox).attr('value');
				
				$('input#file_thumb_selector_' + objekt_id).removeAttr('checked');
				$('input#file_selector_' + objekt_id).removeAttr('checked');
				
				$('tr#file_data_container_' + file_id).removeClass('selected');
				$('td#file_thumb_cell_' + file_id).removeClass('selected');
    		}
    	}
		
    	toggleSelectionActions();
	}
}

function editFolderPermissions(folder_id)
{
	openpopup('edit.php?op=edit&id=' + folder_id + '&tab=permissions&callback=window.opener.folderUpdated', 'editfolder', 450 , 430);
}

function folderUpdated(folder)
{
	// do nothing on callback
}

function liveActions()
{
	// file and thumbnail selections
	$('input.file_selector').live('click', function ()
	{
		if(this.checked)
		{
			selectFile($(this));
		}
		else
		{
			deSelectFile($(this));
		}
	});
	
	// thumbnail hover in
	$('.thumbnail_cell').live('mouseover',	function()
	{
		$(this).children('div.thumbnail_links').removeClass('hidden');
	});
	
	// thumbnail hover out
	$('.thumbnail_cell').live('mouseout',	function()
	{
		$(this).children('div.thumbnail_links').addClass('hidden');
	});
	
	//thumbnail edit single
	$('a.single_file_edit').live('click', function ()
	{
		var file_id = $(this).attr('id').replace('file_edit_', '');
		openpopup('edit.php?op=edit&id=' + file_id + '&callback=window.opener.fileUpdated', 'editfile', 450 , 430);
	});

	//thumbnail edit single
	if(settings.callback && settings.select_mode == 1)
	{
		$('a.single_file_custom_action').live('click', function ()
		{
			var file_id = $(this).attr('id').replace('file_single_file_custom_action_', '');
			selectFileById(file_id);
			customActionTrigger();
		});
	}

	//thumbnail view link
	$('a.single_file_view').live('click', function ()
	{
		var file_id = $(this).attr('id').replace('file_view_', '');
		openpopup(site_url + '/file.php?' + file_id, 'popup', 10, 10, 'yes');
	});

	//thumbnail move single
	$('a.single_file_move').live('click', function ()
	{
		var file_id = $(this).attr('id').replace('file_move_', '');
		
		moveFilesDialog([file_id]);
	});

	//delete single
	$('a.single_file_delete').live('click', function ()
	{
		var file_id = $(this).attr('id').replace('file_delete_', '');
		
		confirmDialog(translations.file_delete_confirmation + ': ' + files[open_folder_id].files[file_page][file_id].filename + '?', function () { deleteFiles([file_id]) });
	});

	//file add favorite
	$('a.single_file_favorite').live('click', function ()
	{
		var file_id = $(this).attr('id').replace('file_favorite_', '');
		
		toggleFavorite(file_id);
	});

	var delay; // hover thumbnails delay function
	
	$('td.filename_cell').live('mouseover', function ()
	{
		$('div.preview_thumbnail').addClass('hidden');
		
		var thumbnail = $(this).children('div.preview_thumbnail');
		
		if(!$(thumbnail).css('top') == 0)
		{
			$(thumbnail).css('top', $(this).offset().top + 20);
			$(thumbnail).css('left', $(this).offset().left + 20);
		}
		
		if(delay)
		{
            clearTimeout(delay);
		}
		
		delay = setTimeout(function()
		{
			$(thumbnail).removeClass('hidden');
        }, 750);
	});
	
	$('td.filename_cell').live('mouseout', function ()
	{
        clearTimeout(delay);
        
		var thumbnail = $(this).children('div.preview_thumbnail');
		
		$(thumbnail).addClass('hidden');
	});
	
	// file list thumbnail hide
	$('div.preview_thumbnail').live('mouseover', function ()
	{
			$(this).addClass('hidden');
	});
	
	// favorite folder go to
	$('a.favorite_link').live('click', function()
	{
		var objekt_id = $(this).attr('id').replace('favorite_', '');
		
		var href = document.location.href;
		
		if($(this).attr('rel') == 'folder' && open_folder_id != objekt_id)
		{
			for (var i in favorites) if(favorites[i].objekt_id == objekt_id)
			{
				break;
			}
			
			if(href.match(/folder_id[0..9]*(?:=[^&]*)/))
			{
				href = href.replace(/folder_id[0..9]*(?:=[^&]*)/, 'folder_id=' + objekt_id);
			}
			else
			{
				href += (href.match(/\?/) ? '&' : '?') + 'folder_id=' + objekt_id;
			}
			
			if(href.match(/file_id[0..9]*(?:=[^&]*)/))
			{
				href = href.replace(/file_id[0..9]*(?:=[^&]*)/, '');
			}
			
			// clean up
			href = href.replace(/&$/, '');
			href = href.replace(/&&/, '');
			
			if(favorites[i].objekt_id != open_folder_id) window.location.replace(href);
		}
		
		if ($(this).attr('rel') == 'file')
		{
			for (var i in favorites) if(favorites[i].objekt_id == objekt_id)
			{
				break;
			}
			
			if(favorites[i].parent_id != open_folder_id)
			{
				if(href.match(/folder_id[0..9]*(?:=[^&]*)/))
				{
					href = href.replace(/folder_id[0..9]*(?:=[^&]*)/, 'folder_id=' + favorites[i].parent_id);
				}
				else
				{
					href += (href.match(/\?/) ? '&' : '?') + 'folder_id=' + favorites[i].parent_id;
				}
				
				if(href.match(/file_id[0..9]*(?:=[^&]*)/))
				{
					href = href.replace(/file_id[0..9]*(?:=[^&]*)/, 'file_id=' + objekt_id);
				}
				else
				{
					href += (href.match(/\?/) ? '&' : '?') + 'file_id=' + objekt_id;
				}
			}
			else
			{
				deSelectAllFiles();
				scrollToFile(objekt_id);
				selectFileById(objekt_id);
			}
			
			if(favorites[i].parent_id != open_folder_id) window.location.replace(href);
		}
		
	});
	
	// favorite delete
	$('img.context_button_delete').live('click', deleteFavorite);
	
	// sorting actions
	$('a.sort_by_filename').live('click', function () { toggleSorting('filename')});
	$('a.sort_by_date').live('click', function () { toggleSorting('date')});
	$('a.sort_by_size').live('click', function () { toggleSorting('size')});
	$('a.sort_by_folder').live('click', function () { toggleSorting('folder')});
}

function showThumbnails(folder)
{
	$('div#scms_file_thumbnails').children('div').addClass('hidden');
	
	if($('div#folder_thumbnails_' + folder.objekt_id).length < 1)
	{
		$('div#scms_file_thumbnails').append('<div id="folder_thumbnails_' + folder.objekt_id + '" class="hidden"></div>');
	}
	
	if($('div#folder_thumbnails_' + folder.objekt_id).children('div#folder_thumbs_page_' + file_page).length < 1)
	{
		$('div#folder_thumbnails_' + folder.objekt_id).children('div').addClass('hidden');
		
		$('div#folder_thumbnails_' + folder.objekt_id).append('<div id="folder_thumbs_page_' + file_page + '"></div>');
		
		var HTML = '';
		
		for(var objekt_id in files[folder.objekt_id].files[file_page]) if(objekt_id != folder.objekt_id)
		{
			HTML += getFileThumbnailHTML(files[folder.objekt_id].files[file_page][objekt_id]);
		}
		
		$('div#folder_thumbnails_' + folder.objekt_id).children('div#folder_thumbs_page_' + file_page).append(HTML);
	}
	
	$('div#folder_thumbnails_' + folder.objekt_id).removeClass('hidden');
}

function getFileRowHTML(file)
{
	return '<tr id="file_data_container_' + file.objekt_id + '"><td class="selectbox_cell"><input id="file_selector_' + file.objekt_id + '" class="file_selector" type="checkbox" value="' + file.objekt_id + '" /></td><td id="filename_cell_' + file.objekt_id + '" class="filename_cell">' + ( file.permissions.U == 1 ? '<a class="single_file_edit" id="file_edit_' + file.objekt_id + '" href="javascript:void(0);">' : '' ) + file.filename + (file.permissions.C == 1 ? '</a>' : '') + (file.extension == 'jpg' || file.extension == 'jpeg' || file.extension == 'gif' || file.extension == 'png' ? '<div class="preview_thumbnail hidden"><table cellpadding="0" cellspacing="0"><tr><td><img src="' + file.thumbnail + '" /></td></tr></table></div></div>' : '') + '</td><td class="folder_cell">' + file.folder + '</td><td class="size_cell">' + file.hr_size + '</td><td class="date_cell">' + file.hr_date + '</td></tr>';
}


function showFileList(folder)
{
	$('div#scms_file_list').children('table').addClass('hidden');
	
	if($('table#folder_files_' + folder.objekt_id).length < 1)
	{
		$('div#scms_file_list').append('<table id="folder_files_' + folder.objekt_id + '" cellpadding="0" cellspacing="0" class="scms_file_listing hidden"><thead><tr><td></td><td><a href="javascript:void(0);" class="sort_by_filename' + (sorting_column == 'filename' ? ' sort_' + sorting_direction : '') + '">' + translations.filename + '</a></td><td><a href="javascript:void(0);" class="sort_by_folder' + (sorting_column == 'folder' ? ' sort_' + sorting_direction : '') + '">' + translations.folder_path + '</a></td><td><a href="javascript:void(0);" class="sort_by_size' + (sorting_column == 'size' ? ' sort_' + sorting_direction : '') + '">' + translations.size + '</a></td><td><a href="javascript:void(0);" class="sort_by_date' + (sorting_column == 'date' ? ' sort_' + sorting_direction : '') + '">' + translations.file_date + '</a></td></tr></thead></table>');
	}
	
	if($('table#folder_files_' + folder.objekt_id).children('tbody#folder_list_page_' + file_page).length < 1)
	{
		$('table#folder_files_' + folder.objekt_id).children('tbody').addClass('hidden');
		
		$('table#folder_files_' + folder.objekt_id).append('<tbody id="folder_list_page_' + file_page + '"></tbody>');
		
		var HTML = '';
		
		for(var objekt_id in files[folder.objekt_id].files[file_page]) if(objekt_id != folder.objekt_id)
		{
    		HTML += getFileRowHTML(files[folder.objekt_id].files[file_page][objekt_id]);
		}
	
		$('table#folder_files_' + folder.objekt_id).children('tbody#folder_list_page_' + file_page).append(HTML);
	}
	
	$('table#folder_files_' + folder.objekt_id).removeClass('hidden');
}

function initFiles()
{
	if(files[open_folder_id] != undefined)
	{
    	$('div#scms_file_list').addClass('hidden');
    	$('div#scms_file_thumbnails').addClass('hidden');
    	
		showFileList(folder_tree[open_folder_id]);
		showThumbnails(folder_tree[open_folder_id]);
		
    	setFilesCounter();
    	
		toggleSelectionActions();
		
		$('span#paging_total_pages').html(Math.ceil(files[open_folder_id].total_files / 100));
		$('input#paging_text').attr('value', file_page);
		
    	if(view_mode == 'list')
    	{
	    	$('div#scms_file_list').removeClass('hidden');
    	}
    	else
    	{
	    	$('div#scms_file_thumbnails').removeClass('hidden');
    	}
    	
    	if(files[open_folder_id].total_files > 0)
    	{
    		showFileActions();
    	}
    	else
    	{
    		noFilesToDisplay();
    	}
	}
	else
	{
		alert('no files here');
	}
}

function showFileActions()
{
	$('div#scms_files_info').removeClass('hidden');
	$('div#paging_previous').removeClass('hidden');
	$('div#scms_listing_actions_bar').removeClass('hidden');
	$('div#scms_listing_left_actions_bar').removeClass('hidden');
	
	if(Math.floor(files[open_folder_id].total_files / 100))
	{
		$('div#scms_paging').removeClass('hidden');
	}
	
	if(settings.callback)
	{
		$('div#custom_actions').removeClass('hidden');
	}
	
	if($('input.file_selector:checked').length > 0)
	{
		if(settings.callback && settings.select_mode == 1)
		{
			$('span#custom_action_text').addClass('hidden');
			$('a#custom_action').removeClass('hidden');
		}
		else
		{
			$('div#files_are_selected').removeClass('hidden');
			$('div#no_files_are_selected').addClass('hidden');
		}
	}
	else
	{
		if(settings.callback && settings.select_mode == 1)
		{
			$('a#custom_action').addClass('hidden');
			$('span#custom_action_text').removeClass('hidden');
		}
		else if(settings.callback && settings.select_mode == 2 && open_folder_id != 1)
		{
			$('span#custom_action_text').addClass('hidden');
			$('a#custom_action').removeClass('hidden');
		}
		else
		{
			$('a#custom_action').addClass('hidden');
			$('div#files_are_selected').addClass('hidden');
			$('div#no_files_are_selected').removeClass('hidden');
		}
	}
	
	$('div#scms_no_search_results').addClass('hidden');
	$('div#scms_no_files').addClass('hidden');
}

function setFilesCounter()
{
	var files_counter = files[open_folder_id].total_files;
	
	$('span#files_counter').html(files_counter + ' ' + (files_counter == 1 ? translations.file : translations.files));
}

function deleteFiles(files_to_delete)
{
	var deletetable_files = [];
	var no_permissions = false;
	
	for(var i in files_to_delete) if(files[open_folder_id].files[file_page][files_to_delete[i]].permissions.D == 1)
	{
		deletetable_files.push(files_to_delete[i]);
	}
	else
	{
		no_permissions = true;
	}
	
	if(no_permissions)
	{
		messageDialog(translations.no_permissions_to_delete_some_files);
	}
	
	if(deletetable_files.length == 0) return;
	
	disableFilemanager();
	
	$.ajax({
	    url: site_url + '/admin/ajax_response.php',
	    data: {op: 'delete_files', files: deletetable_files.join(',')},
	    cache: false,
	    type: 'POST',
	    dataType: 'json',
	    timeout: ajax_timeout,
	    error: function(XMLHttpRequest, textStatus, errorThrown)
	    {
	    	alert(textStatus);
	    	enableFilemanager();
	    },
	    success: function(response, textStatus)
	    {
	    	if(response.error)
	    	{
				messageDialog(translations.some_files_could_not_be_deleted);
	    	}
	    	
	    	for(var i in response.deleted_files)
	    	{
	    		removeFile(response.deleted_files[i]);
	    	}
	    	
	    	if(open_folder_id != 1)
	    	{
		    	delete files[open_folder_id];
		    	resetFolderFiles(open_folder_id);
		    	
				toggleFolder(open_folder_id);
	    	}
	    	
	    	enableFilemanager();
	    }
	});
}

function removeFile(file_id)
{
	delete files[open_folder_id].files[file_page][file_id];
		
	$('tr#file_data_container_' + file_id).remove();
	$('div#file_thumbnail_container_' + file_id).remove();
}

function toggleSelectionActions()
{
	if(files[open_folder_id].total_files != 0)
	{
		if($('input.file_selector:checked').length == $((view_mode == 'list' ? 'table#folder_files_' : 'div#folder_thumbnails_') + open_folder_id + ' input.file_selector').length * 2)
		{
	    	$('a.all_files_selector').addClass('hidden');
	    	$('a.all_files_deselector').removeClass('hidden');
		}
		else
		{
			$('a.all_files_selector').removeClass('hidden');
			$('a.all_files_deselector').addClass('hidden');
		}
		
		if($('input.file_selector:checked').length == 0)
		{
			if(settings.callback && settings.select_mode == 1)
			{
				$('a#custom_action').addClass('hidden');
				$('span#custom_action_text').removeClass('hidden');
			}
			else if(settings.callback && settings.select_mode == 2 && open_folder_id != 1)
			{
				$('a#custom_action').removeClass('hidden');
				$('div#files_are_selected').addClass('hidden');
			}
			else
			{
				$('div#no_files_are_selected').removeClass('hidden');
				$('div#files_are_selected').addClass('hidden');
			}
		}
		else
		{
			if(settings.callback && settings.select_mode == 1)
			{
				$('span#custom_action_text').addClass('hidden');
				$('a#custom_action').removeClass('hidden');
			}
			else if(settings.callback && settings.select_mode == 2 && open_folder_id != 1)
			{
				$('div#files_are_selected').removeClass('hidden');
				$('a#custom_action').addClass('hidden');
			}
			else
			{
				$('div#no_files_are_selected').addClass('hidden');
				$('div#files_are_selected').removeClass('hidden');
			}
		}
	}
}

function selectFile(checkbox)
{
	var file_id = $(checkbox).attr('value');
	
	selectFileById(file_id);
}

function selectFileById(id)
{
	if(settings.select_mode == 1) deSelectAllFiles();
	
	$('input#file_thumb_selector_' + id).attr('checked', 'checked');
	$('input#file_selector_' + id).attr('checked', 'checked');
				
	$('tr#file_data_container_' + id).addClass('selected');
	$('td#file_thumb_cell_' + id).addClass('selected');
	
	toggleSelectionActions();
}

function deSelectFile(checkbox)
{
	var file_id = $(checkbox).attr('value');
	
	$('input#file_thumb_selector_' + file_id).removeAttr('checked');
	$('input#file_selector_' + file_id).removeAttr('checked');
	
	$('tr#file_data_container_' + file_id).removeClass('selected');
	$('td#file_thumb_cell_' + file_id).removeClass('selected');
	
	toggleSelectionActions();
}

function isFavorite(objekt_id)
{
	for(var i in favorites) if(favorites[i].objekt_id == objekt_id)
	{
		return true;
	}
	
	return false;
}

function getFileThumbnailHTML(file)
{
	// the container
	var HTML = '<div class="thumbnail" id="file_thumbnail_container_' + file.objekt_id + '"><table cellpadding="0" cellspacing="0"><tr><td class="thumbnail_cell" id="file_thumb_cell_' + file.objekt_id + '" colspan="2"><div class="thumbnail_links hidden"><table cellpadding="0" cellspacing="0"><tr><td><table cellpadding="0" cellspacing="0">';
	
	// actions
	
	// custom action
	if(settings.callback && settings.select_mode == 1) HTML += '<tr><td class="single_file_custom_action_cell"><a class="single_file_custom_action" id="file_single_file_custom_action_' + file.objekt_id + '" href="javascript:void(0);">' + settings.action_trigger + '</a></td></tr>';
	
	//view
	HTML += '<tr><td><a class="single_file_view' + (settings.callback && settings.select_mode == 1 ? '' : ' bold') + '" id="file_view_' + file.objekt_id + '" href="javascript:void(0);">' + translations.view_file + '</a></td></tr>';
	
	//edit
	if(file.permissions.U == 1) HTML += '<tr><td><a class="single_file_edit' + (settings.callback && settings.select_mode == 1 ? '' : ' bold') + '" id="file_edit_' + file.objekt_id + '" href="javascript:void(0);">' + translations.edit_file + '</a></td></tr>';
	
	if(!settings.callback)
	{
		// move
		//if(file.permissions.U == 1) HTML += '<tr><td><a class="single_file_move" id="file_move_' + file.objekt_id + '" href="javascript:void(0);">' + translations.move_file + '</a></td></tr>';
		
		// delete
		//if(file.permissions.D == 1) HTML += '<tr><td><a class="single_file_delete" id="file_delete_' + file.objekt_id + '" href="javascript:void(0);">' + translations.delete_file + '</a></td></tr>';
		
		// empty row
		HTML += '<tr><td>&nbsp;</td></tr>';
		
		// add to favorites
		HTML += '<tr id="single_file_favorite_row_' + file.objekt_id + '"' + (isFavorite(file.objekt_id) ? 'class="hidden"' : '') + '><td><a class="single_file_favorite" id="file_favorite_' + file.objekt_id + '" href="javascript:void(0);">' + translations.add_file_favorite + '</a></td></tr>';
	}
	
	// container close
	HTML += '</table></td></tr></table></div><div class="thumbnail_image"><table cellpadding="0" cellspacing="0"><tr><td><img src="' + file.thumbnail + '" /></td></tr></table></div></td></tr><tr><td><input type="checkbox" class="file_selector" value="' + file.objekt_id + '" id="file_thumb_selector_' + file.objekt_id + '" /></td><td><label for="file_thumb_selector_' + file.objekt_id + '" title="' + file.filename + '">' + (file.filename.length > 11 ? file.filename.substring(0, 11) + '..' : file.filename) + '</label></td></tr></table></div>'
	
	return HTML;
}

function createFolder()
{
	var parent_id = $('input#save_folder_parent_id').attr('value');
	if(!parent_id) parent_id = open_folder_id;
	
	var folder_name = $('input#save_folder_name').attr('value');
	
	if(folder_tree[parent_id].permissions.C != 1)
	{
		messageDialog(translations.no_permissions_to_create_folder + ': ' + folder_tree[parent_id].title);
	}
	else if(!folder_tree[parent_id].is_writeable)
	{
		messageDialog(folder_tree[parent_id].title + ' ' + translations.folder_has_no_fs_permissions);
	}
	else if(folder_name.length)
	{
		disableFolderDialogue();
		
		$.ajax({
		    url: site_url + '/admin/ajax_response.php',
		    cache: false,
		    data: {op: 'create_folder', name: folder_name, parent_id: parent_id},
		    type: 'POST',
		    dataType: 'json',
		    timeout: ajax_timeout,
		    error: function(XMLHttpRequest, textStatus, errorThrown)
		    {
		    	alert(textStatus);
		    	enableFolderDialogue();
		    },
		    success: function(response, textStatus)
		    {
		    	if(!response.error)
		    	{
		    		// delete previous subtree
		    		deleteFolderSubtree(folder_tree[parent_id]);
		    		
		    		folder_tree[parent_id].has_children = 1;
		    		folder_tree[parent_id].open = 1;
		    		
		    		toggleFolderStateIcon(folder_tree[parent_id]);

		    		// add previous folders + new
		    		addFolderSubtree(folder_tree[parent_id], response.folders);
			    	
			    	hideFolderDialogue();
			    	enableFolderDialogue();
			    	
			    	toggleFolder(response.folder_id);
		    	}
		    	else
		    	{
					messageDialog(response.error_message);
		    		
					enableFolderDialogue();
		    	}
		    }
		});
	}
}

function addFolderSubtree(folder, folders)
{
	var previous_folder_id = folder.objekt_id;
	
	for(var objekt_id in folders) if(objekt_id != folder.objekt_id)
	{
		addToFolderTree(folders[objekt_id]);
			
		$('tr#folder_row_' + previous_folder_id).after(getFolderHTML(folders[objekt_id]));
		toggleFolderStateIcon(folders[objekt_id]);
		
		addFolderHover(folders[objekt_id]);
		addFolderAction(folders[objekt_id]);
			
		previous_folder_id = objekt_id;
	}
}

function deleteFolderSubtree(folder)
{
	if(folder_tree[folder.objekt_id].has_children)
	{
		for(var objekt_id in folder_tree)
		{
			if(folder_tree[objekt_id].parent_id == folder.objekt_id)
			{
				$('tr#folder_row_' + objekt_id).remove();
			}
		}
	}
}

function saveFolder()
{
	var folder_id = $('input#save_folder_id').attr('value');
	
	var folder_name = $('input#save_folder_name').attr('value');

	if(folder_name.length)
	{
		disableFolderDialogue();
		
		$.ajax({
		    url: site_url + '/admin/ajax_response.php',
		    cache: false,
		    data: {op: 'edit_folder', name: folder_name, folder_id: folder_id},
		    type: 'POST',
		    dataType: 'json',
		    timeout: ajax_timeout,
		    error: function(XMLHttpRequest, textStatus, errorThrown)
		    {
		    	alert(textStatus);
		    	enableFolderDialogue();
		    },
		    success: function(response, textStatus)
		    {
		    	hideFolderDialogue();
		    	enableFolderDialogue();
		    	
		    	if(!response.error)
		    	{
		    		// delete previous subtree
		    		deleteFolderSubtree(folder_tree[folder_tree[response.folder_id].parent_id]);
		    		
		    		// add folders back
		    		addFolderSubtree(folder_tree[folder_tree[response.folder_id].parent_id], response.folders);
			    	
			    	hideFolderDialogue();
			    	enableFolderDialogue();
			    	
			    	toggleFolder(response.folder_id);
		    	}
		    	else
		    	{
					messageDialog(response.error_message);
					
			    	enableFolderDialogue();
		    	}
		    		
		    }
		});
	}
}

function toggleFolderStateIcon(folder)
{
	$('tr#folder_row_' + folder.objekt_id).children('td:first').removeClass('open closed no_subfolders');
	$('tr#folder_row_' + folder.objekt_id).children('td:first').addClass((folder.open ? (folder.has_children ? 'open' : 'no_subfolders') : (folder.has_children ? 'closed' : 'no_subfolders')));
}

function disableFolderDialogue()
{
	$('input#save_folder_button, input#create_folder_button, input#cancel_save_folder_button, input#save_folder_name').attr('disabled', 'disabled');
}

function enableFolderDialogue()
{
	$('input#save_folder_button, input#create_folder_button, input#cancel_save_folder_button, input#save_folder_name').removeAttr('disabled');
}

function disableFilemanagerViews()
{
	$('div#scms_fm_body_cover').removeClass('hidden');
}

function enableFilemanagerViews()
{
	$('div#scms_fm_body_cover').addClass('hidden');
}

function disableFilemanager()
{
	$('div#scms_content_cover').removeClass('hidden');
}

function enableFilemanager()
{
	$('div#scms_content_cover').addClass('hidden');
}

function setCreateFolderDialogueActions()
{
	$('input#show_create_folder_button').click(showCreateFolderDialogue);
	$('input#create_folder_button').click(createFolder);
	$('input#save_folder_button').click(saveFolder);
	$('input#cancel_save_folder_button').click(hideFolderDialogue);
}

function showCreateFolderDialogue(parent_id)
{
	disableFilemanagerViews();
	
	if(parent_id > 0) $('input#save_folder_parent_id').attr('value', parent_id); else $('input#save_folder_parent_id').attr('value', '');
	
	$('input#show_create_folder_button, div#scms_search_tools, td#scms_file_upload').addClass('hidden');
	$('input#create_folder_button, input#cancel_save_folder_button, input#save_folder_name').parent().removeClass('hidden');
	
	$('input#save_folder_name').attr('value', '');
	$('input#save_folder_name').focus();
	
	$('input#save_folder_name').unbind('keypress');
	$('input#save_folder_name').keypress(function (event)
	{
		if(event.which == 13) createFolder();
	});
}

function showEditFolderDialogue(folder_id)
{
	disableFilemanagerViews();
	
	$('input#save_folder_id').attr('value', folder_id);
	
	$('input#show_create_folder_button, div#scms_search_tools, td#scms_file_upload').addClass('hidden');
	$('input#save_folder_button, input#cancel_save_folder_button, input#save_folder_name').parent().removeClass('hidden');
	
	$('input#save_folder_name').attr('value', folder_tree[folder_id].title);
	$('input#save_folder_name').focus();
	
	$('input#save_folder_name').unbind('keypress');
	$('input#save_folder_name').keypress(function (event)
	{
		if(event.which == 13) saveFolder();
	});
}

function hideFolderDialogue()
{
	$('input#show_create_folder_button, div#scms_search_tools, td#scms_file_upload').removeClass('hidden');
	$('input#create_folder_button, input#save_folder_button, input#cancel_save_folder_button, input#save_folder_name').parent().addClass('hidden');
	
	enableFilemanagerViews();
}

function initFolders(folder_tree)
{
	for(var objekt_id in folder_tree) if(objekt_id != 1)
	{
		$('table#scms_folder_tree_table').append(getFolderHTML(folder_tree[objekt_id]));
		toggleFolderStateIcon(folder_tree[objekt_id]);
	}
	
	addFolderHovers();
	addFolderActions();
}

function showFolderContextMenu()
{
	var selected_id = this.id.replace('folder_button_', '');
	
	$('div.context_button_container').remove();
	
	$(this).before('<div class="context_button_container"><ul><li class="subfolder' + (1 || folder_tree[selected_id].permissions.C != 1 ? ' hidden' : '') + '"><a id="folder_create_' + selected_id + '" href="javascript:void(0);">' + translations.create_subfolder + '</a></li></ul></div>');
	
	// add subfolder action
	if(folder_tree[selected_id].permissions.C == 1) $('div.context_button_container li.subfolder a').click(function ()
	{
		$('img.context_button_anchor').addClass('invisible');
		$('div.context_button_container').remove();
		
		var folder_id = this.id.replace('folder_create_', '');
		
		if(folder_tree[folder_id].is_writeable)
		{
			showCreateFolderDialogue(folder_id);
		}
		else
		{
			messageDialog(folder_tree[folder_id].title + ' ' + translations.folder_has_no_fs_permissions);
		}
	});
	
	// add delete action item
	if(folder_tree[selected_id].permissions.D == 1 && folder_tree[selected_id].has_children == 0 && folder_tree[selected_id].level != 1 && (files[selected_id] == undefined || files[selected_id].total_files == 0))
	{
		$('div.context_button_container li.subfolder').after('<li class="delete"><a id="folder_delete_' + selected_id + '" href="javascript:void(0);">' + translations.delete_folder + '</a></li>');
		
		// delete action
		$('div.context_button_container li.delete a').click(function()
		{
			$('img.context_button_anchor').addClass('invisible');
			$('div.context_button_container').remove();
			
			var folder_id = this.id.replace('folder_delete_', '');
			
			confirmDialog(translations.folder_delete_confirmation + ': ' + folder_tree[folder_id].title + '?', function ()
			{
				disableFilemanager();
				
				$('img.context_button_anchor').addClass('invisible');
				$('div.context_button_container').remove();
				
				$.ajax({
				    url: site_url + '/admin/ajax_response.php',
				    cache: false,
				    data: { op: 'delete_folder', folder_id: folder_id },
				    type: 'POST',
				    dataType: 'json',
				    timeout: ajax_timeout,
				    error: function(XMLHttpRequest, textStatus, errorThrown)
				    {
				    	alert(textStatus);
						enableFilemanager();
				    },
				    success: function(response, textStatus)
				    {
				    	if(response.error)
				    	{
				    		messageDialog(response.error_message);
				    	}
				    	else
				    	{
				    		removeFolder(folder_id);
					    	enableFilemanager();
				    	}
				    }
				});
			});
		});
	}
	
	// add permissions item
	if(folder_tree[selected_id].permissions.U == 1)
	{
		$('div.context_button_container ul li.subfolder').after('<li class="permissions"><a id="folder_permissions_' + selected_id + '" href="javascript:void(0);">' + translations.folder_permissions + '</a></li>');
		
		// edit action
		$('div.context_button_container li.permissions a').click(function()
		{
			var folder_id = this.id.replace('folder_permissions_', '');
			
			$('img.context_button_anchor').addClass('invisible');
			$('div.context_button_container').remove();
			
			editFolderPermissions(folder_id);
		});
	}
	
	// add edit action item
	if(folder_tree[selected_id].level != 1 && folder_tree[selected_id].permissions.U == 1)
	{
		$('div.context_button_container ul li.subfolder').after('<li class="edit"><a id="folder_edit_' + selected_id + '" href="javascript:void(0);">' + translations.rename_folder + '</a></li>');
		
		// edit action
		$('div.context_button_container li.edit a').click(function()
		{
			var folder_id = this.id.replace('folder_edit_', '');
			
			$('img.context_button_anchor').addClass('invisible');
			$('div.context_button_container').remove();
			
			showEditFolderDialogue(folder_id);
		});
	}
	
	// add synchro action item
	$('div.context_button_container ul li.subfolder').after('<li class="synchronise"><a id="folder_synchronise_' + selected_id + '" href="javascript:void(0);">' + translations.synchronise_folder + '</a></li>');
	
	// synchro action
	$('div.context_button_container li.synchronise a').click(function()
	{
		var folder_id = this.id.replace('folder_synchronise_', '');
		
		$('img.context_button_anchor').addClass('invisible');
		$('div.context_button_container').remove();
		
		disableFilemanager();
		
		file_page = 1;
		
		$.ajax({
		    url: site_url + '/admin/ajax_response.php',
		    data: { op: 'synchronise_folder', folder_id: folder_id, sort_by: sorting_column, sort_dir: sorting_direction },
		    cache: false,
		    type: 'POST',
		    dataType: 'json',
		    timeout: ajax_timeout,
		    error: function(XMLHttpRequest, textStatus, errorThrown)
		    {
		    	alert(textStatus);
				enableFilemanager();
		    },
		    success: function(response, textStatus)
		    {
		    	if(response.error)
		    	{
					messageDialog(response.error_message);
		    	}
		    	else
		    	{
		    		// delete previous subtree
		    		deleteFolderSubtree(folder_tree[response.folder_id]);
					
		    		for(var objekt_id in folder_tree)
			    	{
		    			if(folder_tree[objekt_id].parent_id == response.folder_id) delete folder_tree[objekt_id];
			    	}
			    	
		    		var has_children = 0;
		    		
			    	for(var objekt_id in response.folders)
			    	{
			    		has_children = 1;
			    		addToFolderTree(response.folders[objekt_id]);
						folder_tree[objekt_id] = response.folders[objekt_id];
			    	}
					
			    	folder_tree[response.folder_id].has_children = has_children;
			    	addToFolderTree(folder_tree[response.folder_id]);
		    		
			    	toggleFolderStateIcon(folder_tree[response.folder_id]);
		    		
		    		// add folders back
		    		addFolderSubtree(folder_tree[response.folder_id], response.folders);
					
			    	delete files[response.folder_id];
			    	resetFolderFiles(response.folder_id);
			    	
			    	resetFiles(response.files, response.folder_id);
			    	files[response.folder_id] = response.files;
			    	
			    	toggleFolder(response.folder_id);
			    	
			    	enableFilemanager();
		    	}
		    }
		});
	});
	
	if(!isFavorite(selected_id))
	{
		// add favorites action item
		$('div.context_button_container ul li.subfolder').after('<li class="favorite"><a id="folder_favorite_' + selected_id + '" href="javascript:void(0);">' + translations.add_folder_favorite + '</a></li>');
		
		// favorites action
		$('div.context_button_container li.favorite a').click(function()
		{
			var folder_id = this.id.replace('folder_favorite_', '');
			
			$('img.context_button_anchor').addClass('invisible');
			$('div.context_button_container').remove();
			
			toggleFavorite(folder_id);
		});
	}
	
	$('div.context_button_container').hover(function () {}, function ()
	{
		$(this).remove();
	});
}

function removeFolder(folder_id)
{
	// remove from HTML
	$('tr#folder_row_' + folder_id).remove();
	
	var parent_id = folder_tree[folder_id].parent_id;
	
	delete folder_tree[folder_id]
	
	var parent_has_children = false;
	
	for(var objekt_id in folder_tree)
	{
		if(folder_tree[objekt_id].parent_id == parent_id)
		{
			parent_has_children = true;
			break;
		}
	}
	
	if(!parent_has_children) folder_tree[parent_id].has_children = 0;
	
	toggleFolderStateIcon(folder_tree[parent_id]);
	toggleFolder(parent_id);
}

function addFolderActions()
{
	$('table#scms_folder_tree_table a.folder_link').click(toggleFolder);
	$('table#scms_folder_tree_table img.context_button_anchor').click(showFolderContextMenu);
}

function getFolderHTML(folder)
{
	return '<tr id="folder_row_' + folder.objekt_id + '" class="level_' + folder.level + (folder.objekt_id == open_folder_id ? ' selected' : '') + '"><td class=""><a class="folder_link" title="' + folder.title + '" id="folder_link_' + folder.objekt_id + '" href="javascript:void(0);">' + folder.title + '</a></td><td class="context_button_cell"><img id="folder_button_' + folder.objekt_id + '" class="context_button_anchor invisible" src="' + site_url + '/styles/default/gfx/filemanager/context_button_anchor.gif" width="13" height="13" /></td></tr>';
}

function toggleFolder(id)
{
	if(id > 0)
	{
		var selected_id = id;
	}
	else
	{
		var selected_id = this.id.replace('folder_link_', '');
	}
	
	//if(open_folder_id != selected_id)
	{
		deSelectAllFiles();
		
		if(open_folder_id != selected_id) file_page = 1;
		
		// close all other folders
		
		// get parentage
		var parents = new Array();
		var objekt_id = selected_id;
		parents[objekt_id] = 1;
		
		while(objekt_id > 0)
		{
			parents[folder_tree[objekt_id].parent_id] = 1;
			objekt_id = folder_tree[objekt_id].parent_id;
		}
		
		// close folders not in parent list
		for(var objekt_id in folder_tree)
		{
			if(!parents[folder_tree[objekt_id].parent_id])
			{
				folder_tree[objekt_id].open = 0;
				folder_tree[folder_tree[objekt_id].parent_id].open = 0;
				
				$('tr#folder_row_' + objekt_id).addClass('hidden');
				
				$('tr#folder_row_' + folder_tree[objekt_id].parent_id).children('td:first').removeClass('open closed no_subfolders');
				$('tr#folder_row_' + folder_tree[objekt_id].parent_id).children('td:first').addClass((folder_tree[folder_tree[objekt_id].parent_id].open ? (folder_tree[folder_tree[objekt_id].parent_id].has_children ? 'open' : 'no_subfolders') : (folder_tree[folder_tree[objekt_id].parent_id].has_children ? 'closed' : 'no_subfolders')));
			}
		}
		
		$('tr#folder_row_' + open_folder_id).removeClass('selected');
		
		open_folder_id = selected_id;
		
		$('tr#folder_row_' + open_folder_id).addClass('selected');
		
		// show contents
		if(folder_tree[selected_id].open == 0)
		{
			folder_tree[selected_id].open = 1;
			
			$('tr#folder_row_' + selected_id).children('td:first').removeClass('open closed no_subfolders');
			$('tr#folder_row_' + selected_id).children('td:first').addClass((folder_tree[selected_id].open ? (folder_tree[selected_id].has_children ? 'open' : 'no_subfolders') : (folder_tree[selected_id].has_children ? 'closed' : 'no_subfolders')));
			
			var found_children = false;
			
			for(var objekt_id in folder_tree) if(folder_tree[objekt_id].parent_id == selected_id)
			{
				found_children = true;
				$('tr#folder_row_' + objekt_id).removeClass('hidden');
			}
			
			// load children
			if(folder_tree[selected_id].has_children && !found_children)
			{
				$.ajax({
				    url: site_url + '/admin/ajax_response.php',
				    cache: false,
				    data: { op: 'get_folders', parent_id: selected_id },
				    type: 'POST',
				    dataType: 'json',
				    timeout: ajax_timeout,
				    error: function(XMLHttpRequest, textStatus, errorThrown)
				    {
				    	alert(textStatus);
				    },
				    success: function(response, textStatus)
				    {
				    	var previous_folder_id = selected_id;
				    	
				    	for(var objekt_id in response.folders)
				    	{
				    		addToFolderTree(response.folders[objekt_id]);
								
				    		$('tr#folder_row_' + previous_folder_id).after(getFolderHTML(response.folders[objekt_id]));
				    		toggleFolderStateIcon(response.folders[objekt_id]);
				    		
				    		addFolderHover(response.folders[objekt_id]);
				    		addFolderAction(response.folders[objekt_id]);
								
				    		previous_folder_id = objekt_id;
				    	}
				    }
				});
			}
		}
		
		var cookie_exp = new Date();
		cookie_exp.setFullYear(cookie_exp.getFullYear() + 1);
		setCookie('scms_filemanager_open_folder_id', selected_id, cookie_exp);
		
		displayFiles(selected_id);
	}
}

function displayFiles(folder_id)
{
	// show files
	if(files[folder_id] == undefined)
	{
		disableFilemanager();
		
		// load
		$.ajax({
		    url: site_url + '/admin/ajax_response.php',
		    cache: false,
		    data: { op: 'get_folder_files', folder_id: folder_id, sort_by: sorting_column, sort_dir: sorting_direction, page: file_page },
		    type: 'POST',
		    dataType: 'json',
		    timeout: ajax_timeout,
		    error: function(XMLHttpRequest, textStatus, errorThrown)
		    {
		    	alert(textStatus);
				enableFilemanager();
		    },
		    success: function(response, textStatus)
		    {
		    	if(!response.error)
		    	{
					resetFiles(response.files, response.folder_id);
					
					files[response.folder_id] = response.files;
			    	
					if(response.files.total_files == 0)
					{
						noFilesToDisplay();
					}
					else
					{
						initFiles(files);
					}
			    	
					enableFilemanager();
		    	}
		    	else
		    	{
					messageDialog(response.error_message);
					$('div#scms_file_thumbnails').addClass('hidden');
		    	}
		    }
		});
	}
	else
	{
		if(files[folder_id].total_files == 0)
		{
			noFilesToDisplay();
		}
		else
		{
			initFiles(files);
		}
	}	
}

function resetFiles(files, folder_id)
{
	files[folder_id] = files;
}

function addToFolderTree(folder)
{
	folder_tree[folder.objekt_id] = folder;
}

function addFavoriteHovers()
{
	$('table#scms_favorites_table tr').hover(
		function()
		{
			$(this).children('td.context_button_cell').children('img.context_button_delete').removeClass('invisible');
		},
		function ()
		{
			$(this).children('td.context_button_cell').children('img.context_button_delete').addClass('invisible');
		}
	);
}

function addFolderAction(folder)
{
	$('tr#folder_row_' + folder.objekt_id + ' a.folder_link').click(toggleFolder);
	$('tr#folder_row_' + folder.objekt_id + ' img.context_button_anchor').click(showFolderContextMenu);
}

function addFolderHover(folder)
{
	$('tr#folder_row_' + folder.objekt_id).hover(folderHoverIn, folderHoverOut);
}

function folderHoverIn()
{
	$(this).addClass('hover');
	$(this).children('td.context_button_cell').children('img.context_button_anchor').removeClass('invisible');
}

function folderHoverOut()
{
	$(this).removeClass('hover');
	$(this).children('td.context_button_cell').children('img.context_button_anchor').addClass('invisible');
}

function addFolderHovers()
{
	$('table#scms_folder_tree_table tr').hover(folderHoverIn, folderHoverOut);
}

function setContentDimensions()
{
	// set content height
	$('div#scms_content_cover').height($(window).height());
	
	// set content height
	$('div#scms_fm_body').height($(window).height() - $('div#scms_header_bar').height() - $('div#scms_footer_bar').height() - (8 + 2 + 2)); // paddings and borders need to be added
	
	// set left pane cover height
	$('div#scms_left_pane_cover').height($('div#scms_fm_body').height());
	
	// set cover content height
	$('div#scms_fm_body_cover').height($(window).height() - $('div#scms_header_bar').height() - (8 + 2 + 2)); // paddings and borders need to be added
	
	// set folder tree height
	$('div#scms_folder_tree').height($('div#scms_fm_body').height() - $('div#scms_favorites').height());
	
	// set listing area height
	$('div#scms_listing_contents').height($('div#scms_fm_body').height() - $('div#scms_listing_taskbar').height());
}

// SWFupload handler functions
function fileQueued(file) {
	try {
	} catch (ex) {
		this.debug(ex);
	}
}

function fileQueueError(file, errorCode, message) {
	try {
		
		$('td#scms_folder_tools').removeClass('hidden');
		$('div#scms_search_tools').removeClass('hidden');
		$('div#scms_fm_body_cover').addClass('hidden');
		$('td#scms_upload_progress').addClass('hidden');
		$('td#scms_upload_text').addClass('hidden');
		$('td#scms_upload_cancel').addClass('hidden');
		
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			messageDialog(translations.upload_queue_limit + ': ' + this.settings.file_queue_limit);
			return;
		}
	
		if (errorCode === SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT) {
			messageDialog(file.name + ' ' + translations.upload_limit_size + ' ' + this.settings.file_size_limit);
			return;
		}

		switch (errorCode) {
		default:
			if (file !== null) {
				messageDialog("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			}
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		
		if(numFilesQueued > 0)
		{
			if(folder_tree[open_folder_id].is_writeable)
			{
				this.setButtonDisabled(true);
				this.setButtonDimensions(1, 1);
				this.numFilesQueued = numFilesQueued;
				
				$('td#scms_folder_tools').addClass('hidden');
				$('div#scms_search_tools').addClass('hidden');
				$('div#scms_fm_body_cover').removeClass('hidden');
				$('td#scms_upload_progress').removeClass('hidden');
				$('td#scms_upload_text').removeClass('hidden');
				$('td#scms_upload_cancel').removeClass('hidden');
				
				$('div#upload_progress_grow').width(0);
				
				this.addPostParam('folder_id', open_folder_id);
				this.startUpload();
			}
			else
			{
				messageDialog(folder_tree[open_folder_id].title + ' ' + translations.folder_has_no_fs_permissions);
			}
		}
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadStart(file) {
	try {
		
		$('td#scms_upload_text').html(file.name + ' <span id="percent_placeholder">0</span>%');
		this.progressBarWidth = $('div#upload_progress_grow').width();
		
	} catch (ex)  {
        this.debug(ex);
	}
	
	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var percent = Math.round((bytesLoaded / bytesTotal) * 100);
		$('div#upload_progress_grow').width(this.progressBarWidth + Math.round(($('div#upload_progress_bar').width() / this.numFilesQueued * percent) / 100));
		
		$('span#percent_placeholder').html(percent);
		
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	try {
		
		$('td#scms_folder_tools').removeClass('hidden');
		$('div#scms_search_tools').removeClass('hidden');
		$('div#scms_fm_body_cover').addClass('hidden');
		$('td#scms_upload_progress').addClass('hidden');
		$('td#scms_upload_text').addClass('hidden');
		$('td#scms_upload_cancel').addClass('hidden');
		
		switch (errorCode) {
			case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
				// upload canceled
			break;
			
			case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
				messageDialog('Error occured while trying to connect.');
			break;
			
			default:
				messageDialog("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
	
	$('td#scms_upload_text').empty();
	
	if (this.getStats().files_queued === 0) {
		
		// all files are finished
		$('td#scms_upload_progress').addClass('hidden');
		$('td#scms_upload_text').addClass('hidden');
		
		$('td#scms_folder_tools').removeClass('hidden');
		$('div#scms_search_tools').removeClass('hidden');
		$('div#scms_fm_body_cover').addClass('hidden');
		$('td#scms_upload_cancel').addClass('hidden');
		
		this.setButtonDisabled(false);
		this.setButtonDimensions(131, 25);
		
		delete files[open_folder_id];
		
		$('table#folder_files_' + open_folder_id).remove();
		$('div#folder_thumbnails_' + open_folder_id).remove();
		
		toggleFolder(open_folder_id);
	}
}

function swfuLoaded()
{
	$('input#upload_new_file').addClass('hidden');
}
