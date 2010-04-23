<?php

session_start();

?><html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=htmlspecialchars($_SESSION['keel']['encoding']);?>">
		<meta name="robots" content="noindex, nofollow" />
		<script src="common/fck_dialog_common.js" type="text/javascript"></script>
		<script type="text/javascript">

var oEditor		= window.parent.InnerDialogLoaded() ;
var FCK			= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;

window.onload = function()
{
	oEditor.FCKLanguageManager.TranslatePage( document ) ;
	SelectField( 'snippet_area' ) ;

	window.parent.SetAutoSize( true ) ;
	window.parent.SetOkButton( true ) ;
}

function Ok()
{
	html = GetE('snippet_area').value;
	
	if(html)
	{
		oEditor.FCKUndo.SaveUndoStep();
		
		// so the button would not be under the flash movie
		html = html.replace(/(<\s*embed)([^<]*<\s*\/embed\s*>)/gi, '$1 wmode="transparent" $2' ) ;
		html = html.replace( /<\/OBJECT>/gi, '<param name="wmode" value="transparent"></object>' ) ;
		
		FCK.InsertHtml(html);
		
		// refresh html so FCKeditor will create replacment holders for flash, not needed for IE6
		if(!oEditor.FCKBrowserInfo.IsIE)
		{
			FCK.SetData( FCK.GetXHTML( FCKConfig.FormatSource ), false ) ;
		}
	}

	//window.parent.Cancel( true ) ;
	return true ;
}


		</script>
</head>
<body style="overflow: hidden">
	<table width="100%" style="height: 100%">
		<tr>
			<td><span fcklang="DlgSCMS_Paste_your_HTML_snippet_below">Paste your HTML snippet below:</span></td>
		</tr>
		<tr>
			<td><textarea id="snippet_area" style="width: 370px; height: 200px;"></textarea></td>
		</tr>
	</table>
</body>
</html>
