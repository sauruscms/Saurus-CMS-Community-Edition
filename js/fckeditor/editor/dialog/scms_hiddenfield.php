<?php

session_start();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<!--
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * "Support Open Source software. What about a donation today?"
 * 
 * File Name: fck_hiddenfield.html
 * 	Hidden Field dialog window.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Hidden Field Properties</title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=htmlspecialchars($_SESSION['keel']['encoding']);?>" />
	<meta content="noindex, nofollow" name="robots" />
	<script src="common/fck_dialog_common.js" type="text/javascript"></script>
	<script type="text/javascript">

var oEditor = window.parent.InnerDialogLoaded() ;
var FCK = oEditor.FCK ;
var FCKConfig	= oEditor.FCKConfig ;

// Gets the document DOM
var oDOM = FCK.EditorDocument ;

// Get the selected flash embed (if available).
var oFakeImage = FCK.Selection.GetSelectedElement() ;
var oActiveEl ;

if ( oFakeImage )
{
	if ( oFakeImage.tagName == 'IMG' && oFakeImage.getAttribute('_fckinputhidden') )
		oActiveEl = FCK.GetRealElement( oFakeImage ) ;
	else
		oFakeImage = null ;
}

window.onload = function()
{
	// First of all, translate the dialog box texts
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	if ( oActiveEl )
	{
		if ( oActiveEl.name == FCKConfig.SCMSFormHiddenName ) 
		{
			GetE('txtName').disabled = true;
			window.parent.SetAutoSize( true ) ;

/*
			if(oEditor.FCKBrowserInfo.IsIE) {
				// window.parent.window.dialogWidth = '380px';
				// window.parent.window.dialogHeight = '220px';
				window.parent.resizeTo(380, 420);
				window.resizeTo(360,280);
			} else {
				// window.parent.window.resizeTo(380, 420);

			}

*/			
			var split = oActiveEl.value.split("|||");
			
			GetE('txtsysmail').value = split[0];
			GetE('txtsysbadurl').value = split[1];
			GetE('txtsysokurl').value = split[2];
			GetE('txtsubject').value = split[3];

			GetE('txtValue').disabled = true ;
		
		}
		else
		{
			GetE('txtName').focus();
		}
		GetE('txtName').value		= oActiveEl.name ;
		GetE('txtValue').value		= oActiveEl.value ;
	}

	window.parent.SetOkButton( true ) ;
}


function Ok()
{
	oEditor.FCKUndo.SaveUndoStep() ;

	oActiveEl = CreateNamedElement( oEditor, oActiveEl, 'INPUT', {name: GetE('txtName').value, type: 'hidden' } ) ;

	if ( oActiveEl.name == FCKConfig.SCMSFormHiddenName ) 
	{
		SetAttribute( oActiveEl, 'value', GetE('txtsysmail').value + '|||' + GetE('txtsysbadurl').value + '|||' + GetE('txtsysokurl').value + '|||' +GetE('txtsubject').value) ;
	}
	else
	{
		SetAttribute( oActiveEl, 'value', GetE('txtValue').value ) ;
	}

	if ( !oFakeImage )
	{
		oFakeImage	= oEditor.FCKDocumentProcessor_CreateFakeImage( 'FCK__InputHidden', oActiveEl ) ;
		oFakeImage.setAttribute( '_fckinputhidden', 'true', 0 ) ;

		oActiveEl.parentNode.insertBefore( oFakeImage, oActiveEl ) ;
		oActiveEl.parentNode.removeChild( oActiveEl ) ;
	}
	else
		oEditor.FCKUndo.SaveUndoStep() ;

	return true ;

}


	</script>
</head>
<body style="overflow: hidden" scroll="no">
	<table height="100%" width="100%">
		<tr>
			<td align="center">
				<table border="0" class="inhoud" cellpadding="0" cellspacing="0" width="80%">
					<tr>
						<td>
							<span fcklang="DlgHiddenName">Name</span><br />
							<input type="text" size="20" id="txtName" style="width: 273px" />
						</td>
					</tr>
					<tr>
						<td>
							<span fcklang="DlgHiddenValue">Value</span><br />
							<input type="text" size="30" id="txtValue"  style="width: 273px" />
						</td>
					</tr>
					<tr>
						<td><br><br><br></td>
					</tr>
					<tr>
						<td>
							<span fckLang="DlgSCMSsysmail">E-mail</span><br>
							<input type="text" size="50" id="txtsysmail">
						</td>
					</tr>
					<tr>
						<td>
							<span fckLang="DlgSCMSsubject">E-mail subject</span><br>
							<input type="text" size="50" id="txtsubject">
						</td>
					</tr>
					<tr>
						<td>
							<span fckLang="DlgSCMSsysbadurl">Error URL</span><br>
							<input type="text" size="50" id="txtsysbadurl">
						</td>
					</tr>
					<tr>
						<td>
							<span fckLang="DlgSCMSsysokurl">OK URL</span><br>
							<input type="text" size="50" id="txtsysokurl">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>