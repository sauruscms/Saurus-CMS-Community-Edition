/*
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
 * Scripts related to the Image dialog window (see fck_image.html).
 */

var oEditor		= window.parent.InnerDialogLoaded() ;
var FCK			= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;
var FCKDebug	= oEditor.FCKDebug ;

var bImageButton = ( document.location.search.length > 0 && document.location.search.substr(1) == 'ImageButton' ) ;

//#### Dialog Tabs

// Set the dialog tabs.
window.parent.AddTab( 'Info', FCKLang.DlgImgInfoTab ) ;

if ( !bImageButton && !FCKConfig.ImageDlgHideLink )
	window.parent.AddTab( 'Link', FCKLang.DlgImgLinkTab ) ;

if ( !FCKConfig.ImageDlgHideAdvanced )
	window.parent.AddTab( 'Advanced', FCKLang.DlgAdvancedTag ) ;

// Function called when a dialog tag is selected.
function OnDialogTabChange( tabCode )
{
	ShowE('divInfo'		, ( tabCode == 'Info' ) ) ;
	ShowE('divLink'		, ( tabCode == 'Link' ) ) ;
	ShowE('divAdvanced'	, ( tabCode == 'Advanced' ) ) ;
}

// Get the selected image (if available).
var oImage = FCK.Selection.GetSelectedElement() ;

if ( oImage && oImage.tagName != 'IMG' && !( oImage.tagName == 'INPUT' && oImage.type == 'image' ) )
	oImage = null ;

// Get the active link.
var oLink = FCK.Selection.MoveToAncestorNode( 'A' ) ;

var oImageOriginal ;

function UpdateOriginal( resetSize )
{
	if ( !eImgPreview )
		return ;

	if ( GetE('txtUrl').value.length == 0 )
	{
		oImageOriginal = null ;
		return ;
	}

	oImageOriginal = document.createElement( 'IMG' ) ;	// new Image() ;

	if ( resetSize )
	{
		oImageOriginal.onload = function()
		{
			this.onload = null ;
			ResetSizes() ;
		}
	}

	oImageOriginal.src = eImgPreview.src ;
}

var bPreviewInitialized ;

window.onload = function()
{
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	GetE('btnLockSizes').title = FCKLang.DlgImgLockRatio ;
	GetE('btnResetSize').title = FCKLang.DlgBtnResetSize ;

	// Load the selected element information (if any).
	LoadSelection() ;

	UpdateOriginal() ;

	window.parent.SetAutoSize( true ) ;

	// Activate the "OK" button.
	window.parent.SetOkButton( true ) ;
}

function LoadSelection()
{
	if ( ! oImage ) return ;

	var sUrl = oImage.getAttribute( '_fcksavedurl' ) ;
	if ( sUrl == null )
		sUrl = GetAttribute( oImage, 'src', '' ) ;

	// TODO: Wait stable version and remove the following commented lines.
//	if ( sUrl.startsWith( FCK.BaseUrl ) )
//		sUrl = sUrl.remove( 0, FCK.BaseUrl.length ) ;

	GetE('txtUrl').value    = sUrl ;
	GetE('txtAlt').value    = GetAttribute( oImage, 'alt', '' ) ;
	GetE('txtVSpace').value	= GetAttribute( oImage, 'vspace', '' ) ;
	GetE('txtHSpace').value	= GetAttribute( oImage, 'hspace', '' ) ;
	GetE('txtBorder').value	= GetAttribute( oImage, 'border', '' ) ;
	GetE('cmbAlign').value	= GetAttribute( oImage, 'align', '' ) ;

	var iWidth, iHeight ;

	var regexSize = /^\s*(\d+)px\s*$/i ;

	if ( oImage.style.width )
	{
		var aMatchW  = oImage.style.width.match( regexSize ) ;
		if ( aMatchW )
		{
			iWidth = aMatchW[1] ;
			oImage.style.width = '' ;
			SetAttribute( oImage, 'width' , iWidth ) ;
		}
	}

	if ( oImage.style.height )
	{
		var aMatchH  = oImage.style.height.match( regexSize ) ;
		if ( aMatchH )
		{
			iHeight = aMatchH[1] ;
			oImage.style.height = '' ;
			SetAttribute( oImage, 'height', iHeight ) ;
		}
	}

	GetE('txtWidth').value	= iWidth ? iWidth : GetAttribute( oImage, "width", '' ) ;
	GetE('txtHeight').value	= iHeight ? iHeight : GetAttribute( oImage, "height", '' ) ;

	// Get Advances Attributes
	GetE('txtAttId').value			= oImage.id ;
	GetE('cmbAttLangDir').value		= oImage.dir ;
	GetE('txtAttLangCode').value	= oImage.lang ;
	GetE('txtAttTitle').value		= oImage.title ;
	GetE('txtLongDesc').value		= oImage.longDesc ;

	if ( oEditor.FCKBrowserInfo.IsIE )
	{
		GetE('txtAttClasses').value = oImage.className || '' ;
		GetE('txtAttStyle').value = oImage.style.cssText ;
	}
	else
	{
		GetE('txtAttClasses').value = oImage.getAttribute('class',2) || '' ;
		GetE('txtAttStyle').value = oImage.getAttribute('style',2) ;
	}

	if ( oLink )
	{
		var sLinkUrl = oLink.getAttribute( '_fcksavedurl' ) ;
		if ( sLinkUrl == null )
			sLinkUrl = oLink.getAttribute('href',2) ;

		GetE('txtLnkUrl').value		= sLinkUrl ;
		GetE('cmbLnkTarget').value	= oLink.target ;
	}

	UpdatePreview() ;
}

//#### The OK button was hit.
function Ok()
{
	if ( GetE('txtUrl').value.length == 0 )
	{
		window.parent.SetSelectedTab( 'Info' ) ;
		GetE('txtUrl').focus() ;

		alert( FCKLang.DlgImgAlertUrl ) ;

		return false ;
	}

	var bHasImage = ( oImage != null ) ;

	if ( bHasImage && bImageButton && oImage.tagName == 'IMG' )
	{
		if ( confirm( 'Do you want to transform the selected image on a image button?' ) )
			oImage = null ;
	}
	else if ( bHasImage && !bImageButton && oImage.tagName == 'INPUT' )
	{
		if ( confirm( 'Do you want to transform the selected image button on a simple image?' ) )
			oImage = null ;
	}

	oEditor.FCKUndo.SaveUndoStep() ;
	if ( !bHasImage )
	{
		if ( bImageButton )
		{
			oImage = FCK.EditorDocument.createElement( 'input' ) ;
			oImage.type = 'image' ;
			oImage = FCK.InsertElement( oImage ) ;
		}
		else
			oImage = FCK.InsertElement( 'img' ) ;
	}

	UpdateImage( oImage ) ;

	var sLnkUrl = GetE('txtLnkUrl').value.Trim() ;

	if ( sLnkUrl.length == 0 )
	{
		if ( oLink )
			FCK.ExecuteNamedCommand( 'Unlink' ) ;
	}
	else
	{
		if ( oLink )	// Modifying an existent link.
			oLink.href = sLnkUrl ;
		else			// Creating a new link.
		{
			if ( !bHasImage )
				oEditor.FCKSelection.SelectNode( oImage ) ;

			oLink = oEditor.FCK.CreateLink( sLnkUrl )[0] ;

			if ( !bHasImage )
			{
				oEditor.FCKSelection.SelectNode( oLink ) ;
				oEditor.FCKSelection.Collapse( false ) ;
			}
		}

		SetAttribute( oLink, '_fcksavedurl', sLnkUrl ) ;
		SetAttribute( oLink, 'target', GetE('cmbLnkTarget').value ) ;
	}

	return true ;
}

function UpdateImage( e, skipId )
{
	e.src = GetE('txtUrl').value ;
	SetAttribute( e, "_fcksavedurl", GetE('txtUrl').value ) ;
	SetAttribute( e, "alt"   , GetE('txtAlt').value ) ;
	SetAttribute( e, "width" , GetE('txtWidth').value ) ;
	SetAttribute( e, "height", GetE('txtHeight').value ) ;
	SetAttribute( e, "vspace", GetE('txtVSpace').value ) ;
	SetAttribute( e, "hspace", GetE('txtHSpace').value ) ;
	SetAttribute( e, "border", GetE('txtBorder').value ) ;
	SetAttribute( e, "align" , GetE('cmbAlign').value ) ;

	// Advances Attributes

	if ( ! skipId )
		SetAttribute( e, 'id', GetE('txtAttId').value ) ;

	SetAttribute( e, 'dir'		, GetE('cmbAttLangDir').value ) ;
	SetAttribute( e, 'lang'		, GetE('txtAttLangCode').value ) ;
	SetAttribute( e, 'title'	, GetE('txtAttTitle').value ) ;
	SetAttribute( e, 'longDesc'	, GetE('txtLongDesc').value ) ;

	if ( oEditor.FCKBrowserInfo.IsIE )
	{
		e.className = GetE('txtAttClasses').value ;
		e.style.cssText = GetE('txtAttStyle').value ;
	}
	else
	{
		SetAttribute( e, 'class'	, GetE('txtAttClasses').value ) ;
		SetAttribute( e, 'style', GetE('txtAttStyle').value ) ;
	}
}

var eImgPreview ;
var eImgPreviewLink ;

function SetPreviewElements( imageElement, linkElement )
{
	eImgPreview = imageElement ;
	eImgPreviewLink = linkElement ;

	UpdatePreview() ;
	UpdateOriginal() ;

	bPreviewInitialized = true ;
}

function UpdatePreview()
{
	if ( !eImgPreview || !eImgPreviewLink )
		return ;

	if ( GetE('txtUrl').value.length == 0 )
		eImgPreviewLink.style.display = 'none' ;
	else
	{
		UpdateImage( eImgPreview, true ) ;

		if ( GetE('txtLnkUrl').value.Trim().length > 0 )
			eImgPreviewLink.href = 'javascript:void(null);' ;
		else
			SetAttribute( eImgPreviewLink, 'href', '' ) ;

		eImgPreviewLink.style.display = '' ;
	}
}

var bLockRatio = true ;

function SwitchLock( lockButton )
{
	bLockRatio = !bLockRatio ;
	lockButton.className = bLockRatio ? 'BtnLocked' : 'BtnUnlocked' ;
	lockButton.title = bLockRatio ? 'Lock sizes' : 'Unlock sizes' ;

	if ( bLockRatio )
	{
		if ( GetE('txtWidth').value.length > 0 )
			OnSizeChanged( 'Width', GetE('txtWidth').value ) ;
		else
			OnSizeChanged( 'Height', GetE('txtHeight').value ) ;
	}
}

// Fired when the width or height input texts change
function OnSizeChanged( dimension, value )
{
	// Verifies if the aspect ration has to be maintained
	if ( oImageOriginal && bLockRatio )
	{
		var e = dimension == 'Width' ? GetE('txtHeight') : GetE('txtWidth') ;

		if ( value.length == 0 || isNaN( value ) )
		{
			e.value = '' ;
			return ;
		}

		if ( dimension == 'Width' )
			value = value == 0 ? 0 : Math.round( oImageOriginal.height * ( value  / oImageOriginal.width ) ) ;
		else
			value = value == 0 ? 0 : Math.round( oImageOriginal.width  * ( value / oImageOriginal.height ) ) ;

		if ( !isNaN( value ) )
			e.value = value ;
	}

	UpdatePreview() ;
}

// Fired when the Reset Size button is clicked
function ResetSizes()
{
	if ( ! oImageOriginal ) return ;

	GetE('txtWidth').value  = oImageOriginal.width ;
	GetE('txtHeight').value = oImageOriginal.height ;

	UpdatePreview() ;
}

function SetUrl( url, width, height, alt )
{
	if ( sActualBrowser == 'Link' )
	{
		GetE('txtLnkUrl').value = url ;
		UpdatePreview() ;
	}
	else
	{
		GetE('txtUrl').value = url ;
		GetE('txtWidth').value = width ? width : '' ;
		GetE('txtHeight').value = height ? height : '' ;

		if ( alt )
			GetE('txtAlt').value = alt;

		UpdatePreview() ;
		UpdateOriginal( true ) ;
	}

	window.parent.SetSelectedTab( 'Info' ) ;
}

function ChangeImageURL(URL)
{
	GetE('txtUrl').value = URL;
	UpdatePreview() ;
	UpdateOriginal( true ) ;
}

function toggleOpenOriginal(elem)
{
	if(elem.checked)	
	{
		GetE('txtLnkUrl').value = elem.value;
		GetE('cmbLnkTarget').value = '_blank';
	}
	else
	{
		GetE('txtLnkUrl').value = '';
		GetE('cmbLnkTarget').value = null;
	}
}
