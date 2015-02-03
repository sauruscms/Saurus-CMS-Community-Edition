/*
 * config file for Saurus CMS article editor
*/

FCKConfig.Keystrokes = [
	[ CTRL + 65 /*A*/, true ],
	[ CTRL + 67 /*C*/, true ],
	[ CTRL + 70 /*F*/, true ],
	[ CTRL + 83 /*S*/, 'Save' ],
	[ CTRL + 84 /*T*/, true ],
	[ CTRL + 88 /*X*/, true ],
	[ CTRL + 86 /*V*/, 'Paste' ],
	[ CTRL + 45 /*INS*/, true ],
	[ SHIFT + 45 /*INS*/, 'Paste' ],
	[ CTRL + 88 /*X*/, 'Cut' ],
	[ SHIFT + 46 /*DEL*/, 'Cut' ],
	[ CTRL + 90 /*Z*/, 'Undo' ],
	[ CTRL + 89 /*Y*/, 'Redo' ],
	[ CTRL + SHIFT + 90 /*Z*/, 'Redo' ],
	[ CTRL + 76 /*L*/, 'Link' ],
	[ CTRL + 66 /*B*/, 'Bold' ],
	[ CTRL + 73 /*I*/, 'Italic' ],
	[ CTRL + 85 /*U*/, 'Underline' ],
	[ CTRL + SHIFT + 83 /*S*/, 'Save' ],
	[ CTRL + ALT + 13 /*ENTER*/, 'FitWindow' ],
	[ SHIFT + 32 /*SPACE*/, 'Nbsp' ]
] ;

FCKConfig.CleanWordKeepsStructure = true ;

FCKConfig.Plugins.Add('scms_actions', 'en,et');

FCKConfig.Plugins.Add('nodepath');

FCKConfig.ToolbarSets["SCMS"] = [
	['Save',  'SCMSSaveClose', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteWord', '-', 'Undo', 'Redo', '-', 'Find', '-', 'Source', 'ShowBlocks', '-', 'FitWindow', 'SCMSSimpleToolbar'],
	'/',
	['Link', 'SCMSInsertSiteLink', 'Unlink', 'Anchor', '-', 'SCMSInsertImage',  'SCMSInsertNewFile', 'SCMSInsertSnippet', '-', 'SCMSLead', 'Rule', '-', 'Table', '-',  'SCMSInsertForm', 'TextField', 'HiddenField', 'Textarea', 'Select', 'Radio', 'Checkbox', 'Button', '-',  'SpecialChar'],
	'/',
	['Style', 'Bold', 'Italic', 'Underline', 'StrikeThrough', '-', 'Subscript', 'Superscript', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull', '-', 'UnorderedList', 'OrderedList', 'Outdent', 'Indent', '-', 'TextColor', 'BGColor', 'RemoveFormat']
];

FCKConfig.ToolbarSets["SCMS_simple"] = [
	['Save', 'SCMSSaveClose', '-', 'Copy', 'PasteText','PasteWord', '-', 'Link', 'SCMSInsertSiteLink', 'SCMSInsertImage', 'SCMSInsertSnippet', 'SCMSLead', 'Table', 'SCMSAdvancedToolbar'],
	'/',
	['Style', 'Bold', 'Italic', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull', '-', 'UnorderedList', 'OrderedList', 'Outdent', 'Indent']
];
