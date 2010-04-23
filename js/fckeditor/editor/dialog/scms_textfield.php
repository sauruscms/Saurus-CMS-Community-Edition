<?php

session_start();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<!--
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
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
 * Text field dialog window.
-->
<html>
	<head>
		<title>Text Field Properties</title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=htmlspecialchars($_SESSION['keel']['encoding']);?>">
		<meta content="noindex, nofollow" name="robots">
		<script src="common/fck_dialog_common.js" type="text/javascript"></script>
		<script type="text/javascript">

var oEditor = window.parent.InnerDialogLoaded() ;

// Gets the document DOM
var oDOM = oEditor.FCK.EditorDocument ;

var oActiveEl = oEditor.FCKSelection.GetSelectedElement() ;

window.onload = function()
{
	// First of all, translate the dialog box texts
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	if ( oActiveEl && oActiveEl.tagName == 'INPUT' && ( oActiveEl.type == 'text' || oActiveEl.type == 'password' ) )
	{
		var num = oActiveEl.name.indexOf('_',0);
		
		if (num != -1)
		{
			var prefix = oActiveEl.name.substring(0, num);
			
			if(prefix.length <= 3 && !prefix.match(/a/i) && !prefix.match(/b/i) && !prefix.match(/c/i) && !prefix.match(/d/i) && !prefix.match(/g/i) && !prefix.match(/h/i) && !prefix.match(/i/i) && !prefix.match(/j/i) && !prefix.match(/k/i) && !prefix.match(/l/i) && !prefix.match(/m/i) && !prefix.match(/o/i) && !prefix.match(/p/i) && !prefix.match(/q/i) && !prefix.match(/s/i) && !prefix.match(/t/i) && !prefix.match(/u/i) && !prefix.match(/v/i) && !prefix.match(/w/i) && !prefix.match(/x/i) && !prefix.match(/y/i) && !prefix.match(/z/i) && !prefix.match(/0-9/))
			{
				if(/r/.test(prefix)) {
					GetE('txtRequired').checked	= true ;
				} 
				if(/e/.test(prefix))	{
					GetE('txtValidate').value	= 'email' ;
				} 
				if(/f/.test(prefix))	{
					GetE('txtValidate').value	= 'numeric' ;
				}

				GetE('txtName').value	= oActiveEl.name.substring(num + 1, oActiveEl.name.length) ;
			}
			else
			{
				GetE('txtName').value	= oActiveEl.name;
			}
		}
		else
		{
			GetE('txtName').value	= oActiveEl.name ;
		}
		
		GetE('txtValue').value	= oActiveEl.value ;
		GetE('txtSize').value	= GetAttribute( oActiveEl, 'size' ) ;
		GetE('txtMax').value	= GetAttribute( oActiveEl, 'maxLength' ) ;
		GetE('txtType').value	= oActiveEl.type ;

		GetE('txtType').disabled = true ;
	}
	else
		oActiveEl = null ;

	window.parent.SetOkButton( true ) ;
	window.parent.SetAutoSize( true ) ;
}

function Ok()
{
	if ( isNaN( GetE('txtMax').value ) || GetE('txtMax').value < 0 )
	{
		//alert( "Maximum characters must be a positive number." ) ;
		alert( FCKLang.SCMSMaxCharsPos ) ;
		GetE('txtMax').focus() ;
		return false ;
	}
	else if( isNaN( GetE('txtSize').value ) || GetE('txtSize').value < 0 )
	{
		//alert( "Width must be a positive number." ) ;
		alert( FCKLang.SCMSWidthPos ) ;
		GetE('txtSize').focus() ;
		return false ;
	}

	if ( !oActiveEl )
	{
		oActiveEl = oEditor.FCK.EditorDocument.createElement( 'INPUT' ) ;
		oActiveEl.type = GetE('txtType').value ;
		oEditor.FCKUndo.SaveUndoStep() ;
		oActiveEl = oEditor.FCK.InsertElement( oActiveEl ) ;
	}

	var prefix = '';
	
	if(/email/.test(GetE('txtValidate').value))
	{
		prefix = 'e';	
	}
	
	if(/numeric/.test(GetE('txtValidate').value))
	{
		prefix = 'f';	
	}
	

	if(GetE('txtRequired').checked)
	{
		prefix += 'r';
	}

	
	if(prefix)
	{
		prefix += '_';
	}
	
	oActiveEl.name = prefix + GetE('txtName').value ;
	SetAttribute( oActiveEl, 'value'	, GetE('txtValue').value ) ;
	SetAttribute( oActiveEl, 'size'		, GetE('txtSize').value ) ;
	SetAttribute( oActiveEl, 'maxlength', GetE('txtMax').value ) ;

	return true ;
}

	</script>
</head>
<body style="OVERFLOW: hidden" scroll="no">
	<table height="100%" width="100%">
		<tr>
			<td align="center">
				<table cellSpacing="2" cellPadding="2" border="0">
					<tr>
						<td>
							<span fckLang="DlgTextName">Name</span><br>
							<input id="txtName" type="text" size="20">
						</td>
						<td>
							<span fckLang="DlgTextValue">Value</span><br>
							<input id="txtValue" type="text" size="25">
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" id="txtRequired"><label for="txtRequired" fckLang="DlgSCMSRequired">Required</label>
						</td>
						<td>
							<span fckLang="DlgSCMSValidate">Validate</span><br>
							<select id="txtValidate">
								<option></option>
								<option value="email" fckLang="DlgSCMSEmail">E-mail</option>
								<option value="numeric" fckLang="DlgSCMSNumeric">Numeric</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<span fckLang="DlgTextCharWidth">Character Width</span><br>
							<input id="txtSize" type="text" size="5">
						</td>
						<td>
							<span fckLang="DlgTextMaxChars">Maximum Characters</span><br>
							<input id="txtMax" type="text" size="5">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span fckLang="DlgTextType">Type</span><br>
							<select id="txtType">
								<option value="text" selected fckLang="DlgTextTypeText">Text</option>
								<option value="password" fckLang="DlgTextTypePass">Password</option>
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>