
/* the submit form */
var SCMSForm = window.parent.document.getElementById('frmEdit');

var BaseURL = SCMSForm.baseurl.value;
var WWWRoot = SCMSForm.wwwroot.value;

// the explorer window
var scms_explorer = 0;

// the filemanager window
var scms_filemanager = 0;


/* Save button */
var SCMSSave = function(name)
{
	this.Name = name;
}

SCMSSave.prototype.Execute = function()
{
	SCMSForm.op2.value='save';
	if(SCMSForm.pealkiri.value.length == 0)
	{
		alert(FCKLang.SCMSTitleIsMissing);
	}
	else
	{
		if(window.parent.saveForm)
		{
			var alias_value = SCMSForm.friendly_url;
			var alias = window.parent.document.getElementById('alias');
			
			if(!alias_value || (alias_value && alias && alias_value.value != alias.value))
			{
				window.parent.saveForm('save');
			}
			else
			{
				SCMSForm.submit();
			}
		}
		else
		{
			SCMSForm.submit();
		}
	}
	
	SCMSForm.pealkiri.focus();
}

SCMSSave.prototype.GetState = function()
{  
	return FCK_TRISTATE_OFF;  
}

FCKCommands.RegisterCommand('Save', new SCMSSave('Save'));

var oSCMSSaveItem = new FCKToolbarButton('Save', FCKLang.Save, null, null, true, null, 3);  

FCKToolbarItems.RegisterItem('Save', oSCMSSaveItem);  
/* / Save button */

/* SaveClose button */
var SCMSSaveClose = function(name)
{
	this.Name = name;
}

SCMSSaveClose.prototype.Execute = function()
{
	if(SCMSForm.pealkiri.value.length == 0)
	{
		alert(FCKLang.SCMSTitleIsMissing);
	}
	else
	{
		if(window.parent.saveForm)
		{
			var alias_value = SCMSForm.friendly_url;
			var alias = window.parent.document.getElementById('alias');
			
			if(!alias_value || (alias_value && alias && alias_value.value != alias.value))
			{
				window.parent.saveForm('saveclose');
			}
			else
			{
				SCMSForm.submit();
			}
		}
		else
		{
			SCMSForm.submit();
		}
	}
	
	SCMSForm.pealkiri.focus();
}

SCMSSaveClose.prototype.GetState = function()
{  
	return FCK_TRISTATE_OFF;  
}

FCKCommands.RegisterCommand('SCMSSaveClose', new SCMSSaveClose('SCMSSaveClose'));

var oSCMSSaveCloseItem = new FCKToolbarButton('SCMSSaveClose', FCKLang.SCMSSaveClose, null, FCK_TOOLBARITEM_ICONTEXT, true, null, 74);  

FCKToolbarItems.RegisterItem('SCMSSaveClose', oSCMSSaveCloseItem);  
/* /SaveClose button */

/* Insert image/file button */
var SCMSInsertImage = function(name)
{
	this.Name = name;
}

SCMSInsertImage.prototype.Execute = function()
{
	scms_filemanager = parent.openpopup('../../../admin/filemanager.php?setup=scms_wysiwyg_insert_file', 'filemanager', 980, 600);
}

SCMSInsertImage.prototype.GetState = function()
{  
	return FCK_TRISTATE_OFF;  
}

FCKCommands.RegisterCommand('SCMSInsertImage', new SCMSInsertImage('SCMSInsertImage'));

var oSCMSInsertImageItem = new FCKToolbarButton('SCMSInsertImage', FCKLang.SCMSInsertImage, null, null, null, null, 78);  

FCKToolbarItems.RegisterItem('SCMSInsertImage', oSCMSInsertImageItem);  
/* /Insert image/file button */

/* Insert new image/file button */
var SCMSInsertNewFile = function(name)
{
	this.Name = name;
}

SCMSInsertNewFile.prototype.Execute = function()
{
	scms_filemanager = parent.openpopup('../../../admin/edit.php?op=new&tyyp_id=21&dir=public&in_wysiwyg=1', 'editfile', 450, 430);
}

SCMSInsertNewFile.prototype.GetState = function()
{  
	return FCK_TRISTATE_OFF;  
}

FCKCommands.RegisterCommand('SCMSInsertNewFile', new SCMSInsertNewFile('SCMSInsertNewFile'));

var oSCMSInsertNewFileItem = new FCKToolbarButton('SCMSInsertNewFile', FCKLang.SCMSInsertNewFile, null, null, null, null, 77);  

FCKToolbarItems.RegisterItem('SCMSInsertNewFile', oSCMSInsertNewFileItem);  
/* /Insert new button */

/* Lead separator button */
var SCMSLead = function(name)
{
	this.Name = name;
}

SCMSLead.prototype.Execute = function()
{
	var oSCMSEditor = FCKeditorAPI.GetInstance('scms_article_editor') ;
	
	var leads = oSCMSEditor.EditorDocument.body.getElementsByTagName('hr');
	
	if(leads.length)
	{
		for(var i = 0; i <= leads.length; i++)
		{
			if(leads[i].className == 'scms_lead_body_separator') leads[i].parentNode.removeChild(leads[i]);
		}
	}
	
	var oItem = oSCMSEditor.EditorDocument.createElement( 'hr' ) ;
	oItem.className = 'scms_lead_body_separator';
	
	oSCMSEditor.InsertElement(oItem) ;
}

SCMSLead.prototype.GetState = function()
{  
	return FCK_TRISTATE_OFF;  
}

FCKCommands.RegisterCommand('SCMSLead', new SCMSLead('SCMSLead'));

var oSCMSLeadItem = new FCKToolbarButton('SCMSLead', FCKLang.SCMSLead, null, null, null, null, 75);  

FCKToolbarItems.RegisterItem('SCMSLead', oSCMSLeadItem);  
/* /Lead separator button */

// image/file/template insertion function
var file_source_window = 0;

function SCMSImageFileInsert(data)
{
	var oSCMSEditor = FCKeditorAPI.GetInstance('scms_article_editor') ;
	
	name = data.files[0].title;
	path = data.files[0].folder.replace(/^\//, '') + '/' + data.files[0].filename;
	if(/^shared/i.test(path))
	{
		path = 'file.php?' + data.files[0].objekt_id;
	}
	
	// text is selected create a link to the file
	var oItem = oSCMSEditor.CreateLink( BaseURL + path );
	
	// no text selected, insert
	if(oItem.length == 0)
	{
		// image
		if(/gif$/i.test(path) || /jpg$/i.test(path) || /jpeg$/i.test(path) || /png$/i.test(path) )
		{
			FCKDialog.OpenDialog( 'Image', FCKLang.DlgImgTitle, BaseURL + 'admin/fckeditor_dialog_image.php?dialog=dialog/scms_image.php&file_id=' + data.files[0].objekt_id, 450, 400);
		}
		// flash
		else if(/swf$/i.test(path))
		{
			var oItem = oSCMSEditor.EditorDocument.createElement( 'EMBED' ) ;
			var oFakeImage = null ;
			
			oItem.setAttribute('type', 'application/x-shockwave-flash', 0);
			oItem.setAttribute('pluginspage', 'http://www.macromedia.com/go/getflashplayer', 0);
			oItem.setAttribute('wmode', 'transparent', 0);
			oItem.src = BaseURL + path;
		
			oFakeImage = FCKDocumentProcessor_CreateFakeImage( 'FCK__Flash', oItem ) ;
			oFakeImage.setAttribute( '_fckflash', 'true', 0 ) ;
			oFakeImage = oSCMSEditor.InsertElement( oFakeImage );
		
			FCKEmbedAndObjectProcessor.RefreshView( oFakeImage, oItem ) ;
		}
		// flash or mp4 video
		else if(/flv$/i.test(path) || /mp4$/i.test(path))
		{
			var oItem = oSCMSEditor.EditorDocument.createElement( 'A' ) ;
			oItem.href = BaseURL + path;
			oItem.innerHTML = '&nbsp;';
			oItem.className = 'scms-flowplayer-anchor';
			var oFakeImage = null ;
										
			oFakeImage = FCKDocumentProcessor_CreateFakeImage( 'SCMS__FlashVideo', oItem ) ;
			oFakeImage.setAttribute( '_scmsflashvideo', 'true', 0 ) ;
			oFakeImage = oSCMSEditor.InsertElement( oFakeImage );
		
			FCKEmbedAndObjectProcessor.RefreshView( oFakeImage, oItem ) ;
		}
		else if(/html$/i.test(path) || /htm$/i.test(path))
		{
			file_source_window = parent.openpopup(BaseURL + 'admin/file_source.php?callback=window.opener.frames[0].insert_template&file=' + path, 'file_source', 1, 1);
		}
		// file
		else
		{
			var oItem = oSCMSEditor.EditorDocument.createElement( 'A' ) ;
			oItem.href = BaseURL + path;
			oItem.setAttribute('target', '_blank', 0);
			oItem.innerHTML = name;
			oSCMSEditor.InsertElement( oItem ) ;
		}
	}
	else
	{
		oItem[0].setAttribute('target', '_blank', 0);
	}
	
	if(scms_filemanager) scms_filemanager.close();
}

function insert_template(html)
{
	var oSCMSEditor = FCKeditorAPI.GetInstance('scms_article_editor') ;
	
	oSCMSEditor.InsertHtml(html);
	if(file_source_window) file_source_window.close();
}
// / image/file/template insertion function

/* InsertForm button */
var SCMSInsertForm = function(name)
{
	this.Name = name;
}

SCMSInsertForm.prototype.Execute = function()
{
	var oSCMSEditor = FCKeditorAPI.GetInstance('scms_article_editor') ;
	
	/* the form */
	var oForm = oSCMSEditor.EditorDocument.createElement( 'FORM' ) ;
	
	oForm = oSCMSEditor.InsertElementAndGetIt(oForm) ;
	
	oForm.setAttribute('name', FCKConfig.SCMSFormName, 0);
	oForm.setAttribute('action', FCKConfig.SCMSFormAction, 0);
	oForm.setAttribute('method', FCKConfig.SCMSFormMethod, 0);
	
	/* hidden systemfield */
	var oInput = oSCMSEditor.EditorDocument.createElement( 'INPUT' ) ;
	
	oInput.setAttribute('name', FCKConfig.SCMSFormHiddenName, 0);
	oInput.setAttribute('value', FCKConfig.SCMSFormHiddenString, 0);
	oInput.setAttribute('type', 'hidden', 0);
	
	oForm.appendChild(oInput);

	oFakeImage	= FCKDocumentProcessor_CreateFakeImage( 'FCK__InputHidden', oInput) ;
	oFakeImage.setAttribute( '_fckinputhidden', 'true', 0 ) ;
	oFakeImage	= oSCMSEditor.InsertElement( oFakeImage ) ;

	FCKEmbedAndObjectProcessor.RefreshView( oFakeImage, oInput) ;
	
	/* submit button */
	var oInput = oSCMSEditor.EditorDocument.createElement( 'INPUT' ) ;
	
	oInput.setAttribute('type', 'submit', 0);
	oInput.setAttribute('value', FCKLang.SCMSSend, 0);
	
	oForm.appendChild(oInput);
}

SCMSInsertForm.prototype.GetState = function()
{  
	return FCK_TRISTATE_OFF;  
}

FCKCommands.RegisterCommand('SCMSInsertForm', new SCMSInsertForm('SCMSInsertForm'));

var oSCMSInsertFormItem = new FCKToolbarButton('SCMSInsertForm', FCKLang.SCMSInsertForm, null, null, null, null, 79);  

FCKToolbarItems.RegisterItem('SCMSInsertForm', oSCMSInsertFormItem);  
/* /InsertForm button */

/* InsertSnippet button */
var SCMSInsertSnippet = function(name)
{
	this.Name = name;
}

SCMSInsertSnippet.prototype.Execute = function()
{
	var oSCMSEditor = FCKeditorAPI.GetInstance('scms_article_editor') ;
	
	oSCMSEditor.InsertHtml(html);
	
}

SCMSInsertSnippet.prototype.GetState = function()
{  
	return FCK_TRISTATE_OFF;  
}

FCKCommands.RegisterCommand('SCMSInsertSnippet', new FCKDialogCommand('SCMSInsertSnippet', FCKLang.DlgSCMSInsertSnippetTitle, 'dialog/scms_insert_snippet.php', 400, 350));

var oSCMSInsertSnippetItem = new FCKToolbarButton('SCMSInsertSnippet', FCKLang.SCMSInsertSnippet, null, null, null, null, 82);  

FCKToolbarItems.RegisterItem('SCMSInsertSnippet', oSCMSInsertSnippetItem);  
/* /InsertSnippet button */

// InsertSiteLink button
var SCMSInsertSiteLink = function(name)
{
	this.Name = name;
}

SCMSInsertSiteLink.prototype.Execute = function()
{
	scms_explorer = parent.openpopup(WWWRoot + 'admin/explorer.php?swk_setup=site_linking&editor=1&objekt_id=home', 'cms_explorer', '800','600');
}

function site_linking(nodes)
{
	var oSCMSEditor = FCKeditorAPI.GetInstance('scms_article_editor') ;
	
	if(nodes && scms_explorer)
	{
		var oItem = oSCMSEditor.CreateLink(BaseURL + 'index.php?id=' + nodes[0].objekt_id);
		if(oItem.length == 0)
		{
			var oItem = oSCMSEditor.EditorDocument.createElement( 'A' ) ;
			oItem.href = BaseURL + 'index.php?id=' + nodes[0].objekt_id;
			oItem.innerHTML = nodes[0].pealkiri;
			oSCMSEditor.InsertElement( oItem );
		}
		scms_explorer.close();
	}
}

SCMSInsertSiteLink.prototype.GetState = function()
{  
	return FCK_TRISTATE_OFF;  
}

FCKCommands.RegisterCommand('SCMSInsertSiteLink', new SCMSInsertSiteLink('SCMSInsertSiteLink'));

var SCMSInsertSiteLinkItem = new FCKToolbarButton('SCMSInsertSiteLink', FCKLang.SCMSInsertSiteLink, null, null, null, null, 76);  

FCKToolbarItems.RegisterItem('SCMSInsertSiteLink', SCMSInsertSiteLinkItem);  
// /InsertSiteLink button

// toolbar switcher
function SetToolbar( toolbarName )
{
	// Get the editor instance that we want to interact with.
	var oEditor = FCKeditorAPI.GetInstance('scms_article_editor') ;
	//var oEditor = FCKeditorAPI.GetInstance('FCKeditor1') ;
	var oToolbarSet = oEditor.ToolbarSet ;
	
	// Reset the state of all items in the current toolbar.
	for ( var i = 0 ; i < oToolbarSet.Toolbars.length ; i++ )
	{
		if ( oToolbarSet.Toolbars[i].Items ) 
		{
		for ( var j = 0 ; j < oToolbarSet.Toolbars[i].Items.length ; j++ )
			oToolbarSet.Toolbars[i].Items[j].State = FCK_UNKNOWN ;
		}
	}

	// Reset the array of toolbat items that are active only on WYSIWYG mode.
	oToolbarSet.ItemsWysiwygOnly = new Array() ;

	// Reset the array of toolbar items that are sensitive to the cursor position.
	oToolbarSet.ItemsContextSensitive = new Array() ;

	// Remove all items from the DOM.
	oToolbarSet.innerHTML = '' ;

	// Load the new toolbar.
	oToolbarSet.Name = toolbarName ;
	oToolbarSet.Load( toolbarName ) ;
}

// toggles between SCMS (advanced) and SCMS_simple (simple) toolbar
function toggleToolbars()
{
	// get current toolbar from cookie
	var currentToolbar = parent.getCookie('scms_toolbar');
	if(!currentToolbar) currentToolbar = '';
	
	// by default the SCMS_simple is used so the first change is to SCMS
	var changeToToolbar = 'SCMS';
	
	if(currentToolbar == 'SCMS')
	{
		changeToToolbar = 'SCMS_simple';
	}
	else if(currentToolbar == 'SCMS_simple')
	{
		changeToToolbar = 'SCMS';
	}
	else
	{
		changeToToolbar = 'SCMS';
	}
	
	// make to cookie expire date one year into to the future
	var cookieExpires = new Date();
	cookieExpires.setFullYear(cookieExpires.getFullYear() + 1);
	parent.setCookie('scms_toolbar', changeToToolbar, cookieExpires);
	
	SetToolbar(changeToToolbar);
}

/* Insert flashvideo button */
var SCMSFlashVideo = function(name)
{
	this.Name = name;
}

SCMSFlashVideo.prototype.Execute = function()
{

}

// Disable button toggling.
SCMSFlashVideo.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF;
}

/* Flash */
FCKCommands.RegisterCommand('SCMSFlashVideo', new FCKDialogCommand('SCMSFlashVideo', FCKLang.SCMSFlashVideoProp, 'dialog/scms_flashvideo.php', 450, 100));

// Add the button.
var oSCMSFlashVideoItem = new FCKToolbarButton('SCMSFlashVideo', FCKLang.SCMSFlashVideoInsert, null, null, null, null, 83);
FCKToolbarItems.RegisterItem('SCMSFlashVideo', oSCMSFlashVideoItem);

/* /Insert flashvideo button */

// Parse the document for product blocks
FCKDocumentProcessor.AppendNew().ProcessDocument = function( document )
{
	var matching = document.getElementsByTagName('a');

	var oMatch;
	var i = matching.length - 1;
	while ( i>= 0 && ( oMatch = matching[i--] ) )
	{
		// Find a tags with class player
		if(oMatch.className == 'scms-flowplayer-anchor')
		{
			// Create the new FakeImage 
			var oImg = FCKDocumentProcessor_CreateFakeImage('SCMS__FlashVideo', oMatch.cloneNode(true));
            oImg.setAttribute( '_scmsflashvideo', true, 0 ) ;

			// Place the FakeImage right where the a tag used to be
			oMatch.parentNode.insertBefore(oImg, oMatch);
			oMatch.parentNode.removeChild(oMatch);
		}
	}
}


var SCMSFlashVideoDeleteCommand = function(name)
{
    this.Name = name;
}

SCMSFlashVideoDeleteCommand.prototype.Execute = function()
{
    if (FCK.Selection.GetType()=='Control')
    {
        FCK.Selection.Delete();
    }
    else
    {
        var A=FCK.Selection.GetSelectedElement();
        
        if (A)
        {
          if (A.tagName=='IMG'&&A.getAttribute('_scmsflashvideo')) oFlashVideo=FCK.GetRealElement(A);
          else A=null;
        }
        
        if (!A)
        {
          oFlashVideo=FCK.Selection.MoveToAncestorNode('A');
          if (oFlashVideo) FCK.Selection.SelectNode(oFlashVideo);
        }
        
        if (oFlashVideo.href.length!=0)
        {
          oFlashVideo.removeAttribute('name');
          if (FCKBrowserInfo.IsIE) oFlashVideo.className=oFlashVideo.className.replace(FCKRegexLib.FCK_Class,'');
          return;
        }
        
        if (A)
        {
        	A.parentNode.removeChild(A);
        	return;
		}
		
        if (oFlashVideo.innerHTML.length==0)
        {
        	oFlashVideo.parentNode.removeChild(oFlashVideo);
        	return;
        }
        
        FCKTools.RemoveOuterTags(oFlashVideo);
    }
    
    if (FCKBrowserInfo.IsGecko) FCK.Selection.Collapse(true);
}

SCMSFlashVideoDeleteCommand.prototype.GetState = function()
{
    if (FCK.EditMode!=0) return -1;
    return FCK.GetNamedCommandState('Unlink');
}

FCKCommands.RegisterCommand('SCMSFlashVideoDelete', new SCMSFlashVideoDeleteCommand('SCMSFlashVideoDelete'));

FCK.ContextMenu.RegisterListener({
	AddItems:function(menu,tag,tagName)
	{
		if (tagName=='IMG'&&tag.getAttribute('_scmsflashvideo'))
		{
			menu.AddSeparator();
			menu.AddItem('SCMSFlashVideo',FCKLang.SCMSFlashVideoProp,48);
			menu.AddItem('SCMSFlashVideoDelete',FCKLang.SCMSFlashVideoDelete);
		}
	}
});


// SCMSAdvancedToolbar
var SCMSAdvancedToolbar = function(name)
{
	this.Name = name;
}

SCMSAdvancedToolbar.prototype.Execute = toggleToolbars;

SCMSAdvancedToolbar.prototype.GetState = function()
{  
	return FCK_TRISTATE_OFF;  
}

FCKCommands.RegisterCommand('SCMSAdvancedToolbar', new SCMSAdvancedToolbar('SCMSAdvancedToolbar'));

var oSCMSAdvancedToolbar = new FCKToolbarButton('SCMSAdvancedToolbar', FCKLang.SCMSAdvancedToolbar, null, null, null, null, 80);  

FCKToolbarItems.RegisterItem('SCMSAdvancedToolbar', oSCMSAdvancedToolbar);  
// /SCMSAdvancedToolbar

// SCMSSimpleToolbar
var SCMSSimpleToolbar = function(name)
{
	this.Name = name;
}

SCMSSimpleToolbar.prototype.Execute = toggleToolbars;

SCMSSimpleToolbar.prototype.GetState = function()
{  
	return FCK_TRISTATE_OFF;  
}


FCKCommands.RegisterCommand('SCMSSimpleToolbar', new SCMSSimpleToolbar('SCMSSimpleToolbar'));

var oSCMSSimpleToolbar = new FCKToolbarButton('SCMSSimpleToolbar', FCKLang.SCMSSimpleToolbar, null, null, null, null, 81);  

FCKToolbarItems.RegisterItem('SCMSSimpleToolbar', oSCMSSimpleToolbar);  
// /SCMSSimpleToolbar

/* FCK native Dialog overrides */

/* Image */
FCKCommands.RegisterCommand('Image', new FCKDialogCommand('Image', FCKLang.DlgImgTitle, 'dialog/scms_image.php', 450, 400));

/* Flash */
FCKCommands.RegisterCommand('Flash', new FCKDialogCommand('Flash', FCKLang.DlgFlashTitle, 'dialog/scms_flash.php', 450, 400));

/* Link */
FCKCommands.RegisterCommand('Link', new FCKDialogCommand('Link', FCKLang.DlgLnkWindowTitle, 'dialog/scms_link.php', 400, 330, FCK.GetNamedCommandState, 'CreateLink'));

/* Input text field */
FCKCommands.RegisterCommand('TextField', new FCKDialogCommand('TextField', FCKLang.TextField, 'dialog/scms_textfield.php', 380, 300));

/* Input hidden text field */
FCKCommands.RegisterCommand('HiddenField', new FCKDialogCommand('HiddenField', FCKLang.HiddenField, 'dialog/scms_hiddenfield.php', 380, 200));

/* Textarea field */
FCKCommands.RegisterCommand('Textarea', new FCKDialogCommand('Textarea', FCKLang.Textarea, 'dialog/scms_textarea.php', 380, 300));

/* FindAndReplace */
FCKCommands.RegisterCommand('Find', new FCKDialogCommand('Find', FCKLang.DlgFindAndReplace, 'dialog/scms_find_and_replace.php', 340, 232, null, null, 'Find'));


/* Paste */

FCKCommands.RegisterCommand('Paste', new FCKDialogCommand('FCKDialog_Paste', FCKLang.Paste, 'dialog/scms_pastetext.php', 400, 330));


/* Paste from Word */
FCKCommands.RegisterCommand('PasteWord', new FCKDialogCommand('FCKDialog_Paste', FCKLang.PasteFromWord, 'dialog/scms_pasteword.php', 400, 330));

/* PasteFromWord function */
FCK.PasteFromWord = function()
{
	FCKDialog.OpenDialog( 'FCKDialog_Paste', FCKLang.PasteFromWord, 'dialog/scms_pasteword.php', 400, 330, 'Word' ) ;
}

/* Paste as plain text*/
FCKCommands.RegisterCommand('PasteText', new FCKDialogCommand('FCKDialog_Paste', FCKLang.PasteAsText, 'dialog/scms_pastetext.php', 400, 330));

/* Anchor */
FCKCommands.RegisterCommand('Anchor', new FCKDialogCommand('Anchor', FCKLang.DlgAnchorTitle, 'dialog/scms_anchor.php', 370, 170));

/* Button */
FCKCommands.RegisterCommand('Button', new FCKDialogCommand('Button', FCKLang.Button, 'dialog/scms_button.php', 380, 230));

/* Checkbox */
FCKCommands.RegisterCommand('Checkbox', new FCKDialogCommand('Checkbox', FCKLang.Checkbox, 'dialog/scms_checkbox.php', 380, 230));

/* Select */
FCKCommands.RegisterCommand('Select', new FCKDialogCommand('Select', FCKLang.SelectionField, 'dialog/scms_select.php', 400, 380));

/* Table */
FCKCommands.RegisterCommand('Table', new FCKDialogCommand('Table', FCKLang.DlgTableTitle, 'dialog/scms_table.php', 400, 250));
FCKCommands.RegisterCommand('TableProp', new FCKDialogCommand('Table', FCKLang.DlgTableTitle, 'dialog/scms_table.php?Parent', 400, 250));

/* /Dialog overrides */

FCKCommands.RegisterCommand('TableProp', new FCKDialogCommand('Table', FCKLang.DlgTableTitle, 'dialog/scms_table.php?Parent', 400, 250));
