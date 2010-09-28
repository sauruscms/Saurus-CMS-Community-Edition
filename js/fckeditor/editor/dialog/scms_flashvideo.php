<?php

session_start();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<!--
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2009 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Anchor dialog window.
-->
<html>
	<head>
		<title>Flash Video Properties</title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=htmlspecialchars($_SESSION['keel']['encoding']);?>">
		<meta content="noindex, nofollow" name="robots">
		<script src="common/fck_dialog_common.js" type="text/javascript"></script>
		<script type="text/javascript">

var dialog			= parent ;
var oEditor			= dialog.InnerDialogLoaded() ;

var FCK				= oEditor.FCK ;
var FCKBrowserInfo	= oEditor.FCKBrowserInfo ;
var FCKTools		= oEditor.FCKTools ;
var FCKRegexLib		= oEditor.FCKRegexLib ;

var oDOM			= FCK.EditorDocument ;

var oFakeImage = dialog.Selection.GetSelectedElement() ;

var oFlashVideo ;

if ( oFakeImage )
{
	if ( oFakeImage.tagName == 'IMG' && oFakeImage.getAttribute('_scmsflashvideo') )
		oFlashVideo = FCK.GetRealElement( oFakeImage ) ;
	else
		oFakeImage = null ;
}

//Search for a real Flash Video
if ( !oFakeImage )
{
	oFlashVideo = FCK.Selection.MoveToAncestorNode( 'A' ) ;
	if ( oFlashVideo )
		FCK.Selection.SelectNode( oFlashVideo ) ;
}

window.onload = function()
{
	// First of all, translate the dialog box texts
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	if ( oFlashVideo )
		GetE('txtUrl').value = oFlashVideo.href ;
	else
		oFlashVideo = null ;

	window.parent.SetOkButton( true ) ;
	window.parent.SetAutoSize( true ) ;

	SelectField( 'txtUrl' ) ;
}

function Ok()
{
    var sNewHref = GetE('txtUrl').value ;

    if ( GetE('txtUrl').value.length == 0 )
	{
		if ( oFlashVideo ) {
           	// Removes the current Flash Video from the document using the new command
			FCK.Commands.GetCommand( 'SCMSFlashVideoDelete' ).Execute() ;
			return true ;

        }else{
			GetE('txtUrl').focus() ;
			alert( oEditor.FCKLang.DlgAlertUrl ) ;
	
			return false ;
        }
	}

	oEditor.FCKUndo.SaveUndoStep() ;
	
	if ( !oFlashVideo )
	{
        oFlashVideo = oEditor.FCK.EditorDocument.createElement( 'A' ) ;
        oFlashVideo.href = sNewHref;
        oFlashVideo.innerHTML = '&nbsp;';
        oFlashVideo.className = 'scms-flowplayer-anchor';
	    oFakeImage  = null ;
	}
	
	oFlashVideo.href = sNewHref;
	SetAttribute( oFlashVideo, '_fcksavedurl', sNewHref );
	
	if ( !oFakeImage )
	{
		oFakeImage	= oEditor.FCKDocumentProcessor_CreateFakeImage( 'SCMS__FlashVideo', oFlashVideo ) ;
		oFakeImage.setAttribute( '_scmsflashvideo', 'true', 0 ) ;
		oFakeImage	= FCK.InsertElement( oFakeImage ) ;
	}
	
	oEditor.FCKEmbedAndObjectProcessor.RefreshView( oFakeImage, oFlashVideo ) ;

	return true ;
}

	</script>
	</head>
	<body style="overflow: hidden">
		<table height="100%" width="100%">
			<tr>
				<td align="center">
					<table border="0" cellpadding="0" cellspacing="0" width="80%">
						<tr>
							<td>
								<span>Flash Video Url</span><BR>
								<input id="txtUrl" style="width: 100%" type="text">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
</html>