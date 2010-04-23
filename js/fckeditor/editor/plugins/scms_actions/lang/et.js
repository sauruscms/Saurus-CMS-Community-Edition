/*
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
 * File Name: et.js
 * 	Estonian language file.
 * 
 * Original translations:
 * 		Kristjan Kivikangur (kristjan@ttrk.ee)
 *
 * Customized translations for Saurus CMS:
 * 		Saurus (www.saurus.info)
 */

var FCKLang =
{
// Language direction : "ltr" (left to right) or "rtl" (right to left).
Dir					: "ltr",

ToolbarCollapse		: "Voldi tööriistariba",
ToolbarExpand		: "Laienda tööriistariba",

// Toolbar Items and Context Menu
Save				: "Uuenda",
NewPage				: "Uus leht",
Preview				: "Eelvaade",
Cut					: "Lõika",
Copy				: "Kopeeri",
Paste				: "Kleebi",
PasteText			: "Kleebi tekstina",
PasteWord			: "Kleebi Wordist",
Print				: "Prindi",
SelectAll			: "Vali kõik",
RemoveFormat		: "Eemalda vorming",
InsertLinkLbl		: "Link",
InsertLink			: "Link",VisitLink			: "Ava link",
RemoveLink			: "Eemalda link",
Anchor				: "Ankur",
AnchorDelete		: "Eemalda ankur",
InsertImageLbl		: "Pilt",
InsertImage			: "Pilt või fail",
InsertFlashLbl		: "Flash",
InsertFlash			: "Lisa Flash",
InsertTableLbl		: "Tabel",
InsertTable			: "Sisesta/Muuda tabel",
InsertLineLbl		: "Joon",
InsertLine			: "Horisontaaljoon",
InsertSpecialCharLbl: "Sümbol",
InsertSpecialChar	: "Sümbol",
InsertSmileyLbl		: "Smiley",
InsertSmiley		: "Sisesta Smiley",
About				: "Info FCKeditor-ist",
Bold				: "Jäme",
Italic				: "Kursiiv",
Underline			: "Allajoonitud",
StrikeThrough		: "Läbijoonitud",
Subscript			: "Allindeks",
Superscript			: "Ülaindeks",
LeftJustify			: "Joonda vasakule",
CenterJustify		: "Joonda keskele",
RightJustify		: "Joonda paremale",
BlockJustify		: "Rööpjoondus",
DecreaseIndent		: "Vähenda taanet",
IncreaseIndent		: "Suurenda taanet",
Blockquote			: "Blokktsitaat",
Undo				: "Taasta",
Redo				: "Korda tegevust",
NumberedListLbl		: "Nummerdatud loetelu",
NumberedList		: "Nummerdatud loetelu",
BulletedListLbl		: "Täpitud loetelu",
BulletedList		: "Täpitud loetelu",
ShowTableBorders	: "Näita tabeli jooni",
ShowDetails			: "Näita üksikasju",
Style				: "Laad",
FontFormat			: "Vorming",
Font				: "Font",
FontSize			: "Suurus",
TextColor			: "Teksti värv",
BGColor				: "Tausta värv",
Source				: "HTML",
FindAndReplace			: "Otsi ja asenda",
SpellCheck			: "Õigekirja kontroll",
UniversalKeyboard	: "Klaviatuur",
PageBreakLbl		: "Lehepiir",
PageBreak			: "Sisesta lehevahetuskoht",

Form			: "Vorm",	
Checkbox		: "Märkeruut",
RadioButton		: "Raadionupp",	
TextField		: "Tekstiväli",	
Textarea		: "Tekstiala",	
HiddenField		: "Peidetud väli",	
Button			: "Nupp",	
SelectionField	: "Rippvalik",
ImageButton		: "Image Button",

FitWindow		: "Maksimeeri redaktori mõõtmed",
ShowBlocks		: "Näita blokke",

// Context Menu
EditLink			: "Muuda linki",
CellCM				: "Lahter",
RowCM				: "Rida",
ColumnCM			: "Veerg",
InsertRowAfter		: "Uus rida alla",
InsertRowBefore		: "Uus rida üles",
DeleteRows			: "Eemalda read",
InsertColumnAfter	: "Uus veerg paremale",
InsertColumnBefore	: "Uus veerg vasakule",
DeleteColumns		: "Eemalda veerud",
InsertCellAfter		: "Uus lahter paremale",
InsertCellBefore	: "Uus lahter vasakule",
DeleteCells			: "Eemalda lahter",
MergeCells			: "Ühenda lahtrid",
MergeRight			: "Ühenda paremale",
MergeDown			: "Ühenda alla",
HorizontalSplitCell	: "Poolita lahter horisontaalselt",
VerticalSplitCell	: "Poolita lahter vertikaalselt",
TableDelete			: "Kustuta tabel",
CellProperties		: "Lahtri atribuudid",
TableProperties		: "Tabeli atribuudid",
ImageProperties		: "Pildi atribuudid",
FlashProperties		: "Flashi atribuudid",	

AnchorProp			: "Ankru atribuudid",
ButtonProp			: "Nupu atribuudid",
CheckboxProp		: "Märkeruudu atribuudid",	
HiddenFieldProp		: "Peidetud välja atribuudid",	
RadioButtonProp		: "Raadionupu atribuudid",	
ImageButtonProp		: "Piltnupu atribuudid",
TextFieldProp		: "Tekstivälja atribuudid",	
SelectionFieldProp	: "Rippvaliku atribuudid",	
TextareaProp		: "Tekstiala atribuudid",	
FormProp			: "Vormi atribuudid",	

FontFormats			: "Tavaline;Vormindatud;Aadress;Pealkiri 1;Pealkiri 2;Pealkiri 3;Pealkiri 4;Pealkiri 5;Pealkiri 6",

// Alerts and Messages
ProcessingXHTML		: "Töötlen XHTML-i. Palun oota...",
Done				: "Valmis",
PasteWordConfirm	: "Tekst, mida soovid lisada paistab pärinevat Wordist. Kas soovid seda enne kleepimist puhastada?",
NotCompatiblePaste	: "See toiming on saadaval ainult Internet Explorer versioon 5.5 või uuema puhul. Kas soovid kleepida ilma puhastamata?",
UnknownToolbarItem	: "Tundmatu tööriistariba üksus \"%1\"",
UnknownCommand		: "Tundmatu käsunimi \"%1\"",
NotImplemented		: "Käsku ei täidetud",
UnknownToolbarSet	: "Tööriistariba \"%1\" ei eksisteeri",
NoActiveX			: "Sinu veebisirvija turvalisuse seaded võivad limiteerida mõningaid tekstirdaktori kasutusvõimalusi. Sa peaksid võimaldama valiku \"Run ActiveX controls and plug-ins\" oma veebisirvija seadetes. Muidu võid sa täheldada vigu tekstiredaktori töös ja märgata puuduvaid funktsioone.",
BrowseServerBlocked : "Ressursside sirvija avamine ebaõnnestus. Võimalda pop-up akende avamine.",
DialogBlocked		: "Dialoogiakent ei avatud. Palun kontrolli et hüpiakende tõrjuja oleks välja lülitatud.",	

// Dialogs
DlgBtnOK			: "Salvesta",
DlgBtnCancel		: "Katkesta",
DlgBtnClose			: "Sulge",
DlgBtnBrowseServer	: "Sirvi serverit",
DlgAdvancedTag		: "Täpsemalt",
DlgOpOther			: "<Teine>",
DlgInfoTab			: "Info",
DlgAlertUrl			: "Palun sisesta aadress",

// General Dialogs Labels
DlgGenNotSet		: "<määramata>",
DlgGenId			: "Id",
DlgGenLangDir		: "Keele suund",
DlgGenLangDirLtr	: "Vasakult paremale (LTR)",
DlgGenLangDirRtl	: "Paremalt vasakule (RTL)",
DlgGenLangCode		: "Keele kood",
DlgGenAccessKey		: "Juurdepääsu võti",
DlgGenName			: "Nimi",
DlgGenTabIndex		: "Tabulaatori järjekord",
DlgGenLongDescr		: "Pikk kirjeldus URL",
DlgGenClass			: "Stiiliklassid",
DlgGenTitle			: "Juhendav tiitel",
DlgGenContType		: "Juhendava sisu tüüp",
DlgGenLinkCharset	: "Lingitud ressurssi märgistik",
DlgGenStyle			: "Laad",

// Image Dialog
DlgImgTitle			: "Pilt",
DlgImgInfoTab		: "Pildi",
DlgImgBtnUpload		: "Saada serverile",
DlgImgURL			: "URL",
DlgImgUpload		: "Lae üles",
DlgImgAlt			: "Alternatiivne tekst",
DlgImgWidth			: "Laius",
DlgImgHeight		: "Kõrgus",
DlgImgLockRatio		: "Lukusta kuvasuhe",
DlgBtnResetSize		: "Lähtesta suurus",
DlgImgBorder		: "Raam",
DlgImgHSpace		: "HSpace",
DlgImgVSpace		: "VSpace",
DlgImgAlign			: "Joondus",
DlgImgAlignLeft		: "Vasak",
DlgImgAlignAbsBottom: "Abs alla",
DlgImgAlignAbsMiddle: "Abs keskele",
DlgImgAlignBaseline	: "Baasjoonele",
DlgImgAlignBottom	: "Alla",
DlgImgAlignMiddle	: "Keskele",
DlgImgAlignRight	: "Paremale",
DlgImgAlignTextTop	: "Teksti üles",
DlgImgAlignTop		: "Üles",
DlgImgPreview		: "Eelvaade",
DlgImgAlertUrl		: "Palun kirjuta pildi URL",
DlgImgLinkTab		: "Link",	

// Flash Dialog
DlgFlashTitle		: "Flash omadused",
DlgFlashChkPlay		: "Automaatne start ",
DlgFlashChkLoop		: "Korduv",
DlgFlashChkMenu		: "Võimalda flash menüü",
DlgFlashScale		: "Mastaap",
DlgFlashScaleAll	: "Näita kõike",
DlgFlashScaleNoBorder	: "Äärist ei ole",
DlgFlashScaleFit	: "Täpne sobivus",

// Link Dialog
DlgLnkWindowTitle	: "Link",
DlgLnkInfoTab		: "Link",
DlgLnkTargetTab		: "Sihtkoht",

DlgLnkType			: "Lingi tüüp",
DlgLnkTypeURL		: "Veebiaadress",
DlgLnkTypeAnchor	: "Ankur sellel lehel",
DlgLnkTypeEMail		: "E-post",
DlgLnkProto			: "Protokoll",
DlgLnkProtoOther	: "<muu>",
DlgLnkURL			: "URL",
DlgLnkAnchorSel		: "Vali ankur",
DlgLnkAnchorByName	: "Ankru nime järgi",
DlgLnkAnchorById	: "Elemendi Id järgi",
DlgLnkNoAnchors		: "<Selles dokumendis ei ole ankruid>",
DlgLnkEMail			: "E-posti aadress",
DlgLnkEMailSubject	: "Kirja teema",
DlgLnkEMailBody		: "Kirja tekst",
DlgLnkUpload		: "Lae üles",
DlgLnkBtnUpload		: "Saada serverile",

DlgLnkTarget		: "Sihtkoht",
DlgLnkTargetFrame	: "<raam>",
DlgLnkTargetPopup	: "<hüpikaken>",
DlgLnkTargetBlank	: "Uus aken (_blank)",
DlgLnkTargetParent	: "Vanem aken (_parent)",
DlgLnkTargetSelf	: "Sama aken (_self)",
DlgLnkTargetTop		: "Pealmine aken (_top)",
DlgLnkTargetFrameName	: "Sihtmärk raami nimi",
DlgLnkPopWinName	: "Hüpikakna nimi",
DlgLnkPopWinFeat	: "Hüpikakna omadused",
DlgLnkPopResize		: "Suurendatav",
DlgLnkPopLocation	: "Aadressiriba",
DlgLnkPopMenu		: "Menüüriba",
DlgLnkPopScroll		: "Kerimisribad",
DlgLnkPopStatus		: "Olekuriba",
DlgLnkPopToolbar	: "Tööriistariba",
DlgLnkPopFullScrn	: "Täisekraan (IE)",
DlgLnkPopDependent	: "Sõltuv (Netscape)",
DlgLnkPopWidth		: "Laius",
DlgLnkPopHeight		: "Kõrgus",
DlgLnkPopLeft		: "Vasak asukoht",
DlgLnkPopTop		: "Ülemine asukoht",

DlnLnkMsgNoUrl		: "Palun sisesta lingi aadress",
DlnLnkMsgNoEMail	: "Palun sisesta e-posti aadress",
DlnLnkMsgNoAnchor	: "Palun vali ankur",
DlnLnkMsgInvPopName	: "Hüpikakna nimi peab algama alfabeetilise tähega ja ei tohi sisaldada tühikuid",

// Color Dialog
DlgColorTitle		: "Värv",
DlgColorBtnClear	: "Tühjenda",
DlgColorHighlight	: "Märgi",
DlgColorSelected	: "Valitud",

// Smiley Dialog
DlgSmileyTitle		: "Sisesta Smiley",

// Special Character Dialog
DlgSpecialCharTitle	: "Sümbol",

// Table Dialog
DlgTableTitle		: "Tabel",
DlgTableRows		: "Ridu",
DlgTableColumns		: "Veerge",
DlgTableBorder		: "Joone paksus",
DlgTableAlign		: "Joondus",
DlgTableAlignNotSet	: "<Määramata>",
DlgTableAlignLeft	: "Vasak",
DlgTableAlignCenter	: "Kesk",
DlgTableAlignRight	: "Parem",
DlgTableWidth		: "Laius",
DlgTableWidthPx		: "pikslit",
DlgTableWidthPc		: "protsenti",
DlgTableHeight		: "Kõrgus",
DlgTableCellSpace	: "Lahtri vahe",
DlgTableCellPad		: "Lahtri täidis",
DlgTableCaption		: "Pealkiri",
DlgTableSummary		: "Kokkuvõte",	

// Table Cell Dialog
DlgCellTitle		: "Lahter",
DlgCellWidth		: "Laius",
DlgCellWidthPx		: "pikslit",
DlgCellWidthPc		: "protsenti",
DlgCellHeight		: "Kõrgus",
DlgCellWordWrap		: "Murra ridu",
DlgCellWordWrapNotSet	: "<Määramata>",
DlgCellWordWrapYes	: "Jah",
DlgCellWordWrapNo	: "Ei",
DlgCellHorAlign		: "Horisontaaljoondus",
DlgCellHorAlignNotSet	: "<Määramata>",
DlgCellHorAlignLeft	: "Vasak",
DlgCellHorAlignCenter	: "Kesk",
DlgCellHorAlignRight: "Parem",
DlgCellVerAlign		: "Vertikaaljoondus",
DlgCellVerAlignNotSet	: "<Määramata>",
DlgCellVerAlignTop	: "Üles",
DlgCellVerAlignMiddle	: "Keskele",
DlgCellVerAlignBottom	: "Alla",
DlgCellVerAlignBaseline	: "Baasjoonele",
DlgCellRowSpan		: "Reaulatus",
DlgCellCollSpan		: "Veeruulatus",
DlgCellBackColor	: "Tausta värv",
DlgCellBorderColor	: "Joone värv",
DlgCellBtnSelect	: "Vali...",

// Find and Replace Dialog
DlgFindAndReplace	: "Otsi ja asenda",

// Find Dialog
DlgFindTitle		: "Otsi",DlgFindandReplaceTitle	: "Otsi ja asenda",

DlgFindFindBtn		: "Otsi",
DlgFindNotFoundMsg	: "Valitud teksti ei leitud.",

// Replace Dialog
DlgReplaceTitle			: "Asenda",
DlgReplaceFindLbl		: "Leia:",
DlgReplaceReplaceLbl	: "Asenda:",
DlgReplaceCaseChk		: "Erista suurtähti",
DlgReplaceReplaceBtn	: "Asenda",
DlgReplaceReplAllBtn	: "Asenda kõik",
DlgReplaceWordChk		: "Otsi terveid sõnu",

// Paste Operations / Dialog
PasteErrorPaste	: "Sinu brauseri turvaseaded ei luba redaktoril automaatselt kleepida. Palun kasuta selleks klaviatuuri [Ctrl+V].",
PasteErrorCut	: "Sinu brauseri turvaseaded ei luba redaktoril automaatselt lõigata. Palun kasuta selleks klaviatuuri [Ctrl+X].",
PasteErrorCopy	: "Sinu brauseri turvaseaded ei luba redaktoril automaatselt kopeerida. Palun kasuta selleks klaviatuuri [Ctrl+C].",

PasteAsText		: "Kleebi tekstina",
PasteFromWord	: "Kleebi Wordist",

DlgPasteMsg2	: "Kleebi tekst siia kasutades klaviatuuri [Ctrl+V].",	
DlgPasteSec		: "Sinu veebisirvija turvaseadete tõttu, ei oma redaktor otsest ligipääsu lõikelaua andmetele. Sa pead kleepima need uuesti siia aknasse.",
DlgPasteIgnoreFont		: "Ingoreeri fondikirjeldusi",	
DlgPasteRemoveStyles	: "Eemalda stiilid",	
DlgPasteCleanBox		: "Puhasta",	

// Color Picker
ColorAutomatic	: "Automaatne",
ColorMoreColors	: "Rohkem värve...",

// Document Properties
DocProps		: "Dokumendi atribuudid",	

// Anchor Dialog
DlgAnchorTitle		: "Ankur",	
DlgAnchorName		: "Nimi",	
DlgAnchorErrorName	: "Palun sisesta ankru nimi",	

// Speller Pages Dialog
DlgSpellNotInDic		: "Puudub sõnastikust",
DlgSpellChangeTo		: "Muuda",
DlgSpellBtnIgnore		: "Ignoreeri",
DlgSpellBtnIgnoreAll	: "Ignoreeri kõiki",
DlgSpellBtnReplace		: "Asenda",
DlgSpellBtnReplaceAll	: "Asenda kõik",
DlgSpellBtnUndo			: "Võta tagasi",
DlgSpellNoSuggestions	: "- Soovitused puuduvad -",
DlgSpellProgress		: "Toimub õigekirja kontroll...",
DlgSpellNoMispell		: "Õigekirja kontroll sooritatud: õigekirjuvigu ei leitud",
DlgSpellNoChanges		: "Õigekirja kontroll sooritatud: ühtegi sõna ei muudetud",
DlgSpellOneChange		: "Õigekirja kontroll sooritatud: üks sõna muudeti",
DlgSpellManyChanges		: "Õigekirja kontroll sooritatud: %1 sõna muudetud",

IeSpellDownload			: "Õigekirja kontrollija ei ole installeeritud. Soovid sa selle alla laadida?",

// Button Dialog
DlgButtonText	: "Tekst",	
DlgButtonType	: "Tüüp",	
DlgButtonTypeBtn	: "Nupp",
DlgButtonTypeSbm	: "Saada",
DlgButtonTypeRst	: "Lähtesta",

// Checkbox and Radio Button Dialogs
DlgCheckboxName		: "Nimi",	
DlgCheckboxValue	: "Väärtus",
DlgCheckboxSelected	: "Valitud",

// Form Dialog
DlgFormName		: "Nimi",	
DlgFormAction	: "Action",	
DlgFormMethod	: "Meetod",	

// Select Field Dialog
DlgSelectName		: "Nimi",	
DlgSelectValue		: "Vaikeväärtus",
DlgSelectSize		: "Suurus",	
DlgSelectLines		: "rida",	
DlgSelectChkMulti	: "Luba valida mitut",	
DlgSelectOpAvail	: "Väärtused",	
DlgSelectOpText		: "Tekst",
DlgSelectOpValue	: "Väärtus",
DlgSelectBtnAdd		: "Lisa",	
DlgSelectBtnModify	: "Muuda",	
DlgSelectBtnUp		: "Üles",	
DlgSelectBtnDown	: "Alla",	
DlgSelectBtnSetValue : "Muuda vaikeväärtuseks",	
DlgSelectBtnDelete	: "Kustuta",	

// Textarea Dialog
DlgTextareaName	: "Nimi",
DlgTextareaCols	: "Veerge",
DlgTextareaRows	: "Ridu",	

// Text Field Dialog
DlgTextName			: "Nimi",	
DlgTextValue		: "Vaikeväärtus",	
DlgTextCharWidth	: "Pikkus tähemärkides",	
DlgTextMaxChars		: "Maksimaalselt tähemärke",	
DlgTextType			: "Tüüp",	
DlgTextTypeText		: "Tekst",	
DlgTextTypePass		: "Parool",	

// Hidden Field Dialog
DlgHiddenName	: "Nimi",	
DlgHiddenValue	: "Väärtus",	

// Bulleted List Dialog
BulletedListProp	: "Loetelu atribuudid",	
NumberedListProp	: "Loetelu atribuudid",	
DlgLstStart			: "Alusta",
DlgLstType			: "Tüüp",
DlgLstTypeCircle	: "Ring",
DlgLstTypeDisc		: "Ketas",
DlgLstTypeSquare	: "Ruut",
DlgLstTypeNumbers	: "Numbrid (1, 2, 3)",
DlgLstTypeLCase		: "Väiketähed (a, b, c)",
DlgLstTypeUCase		: "Suurtähed (A, B, C)",	
DlgLstTypeSRoman	: "Väikesed rooma numbrid (i, ii, iii)",	
DlgLstTypeLRoman	: "Suured rooma numbrid (I, II, III)",	

// Document Properties Dialog
DlgDocGeneralTab	: "Üldine",
DlgDocBackTab		: "Taust",
DlgDocColorsTab		: "Värvid ja veerised",
DlgDocMetaTab		: "Meta andmed",

DlgDocPageTitle		: "Lehekülje tiitel",
DlgDocLangDir		: "Kirja suund",
DlgDocLangDirLTR	: "Vasakult paremale (LTR)",
DlgDocLangDirRTL	: "Paremalt vasakule (RTL)",
DlgDocLangCode		: "Keele kood",
DlgDocCharSet		: "Märgistiku kodeering",
DlgDocCharSetCE		: "Kesk-Euroopa",
DlgDocCharSetCT		: "Hiina traditsiooniline (Big5)",
DlgDocCharSetCR		: "Kirillitsa",
DlgDocCharSetGR		: "Kreeka",
DlgDocCharSetJP		: "Jaapani",
DlgDocCharSetKR		: "Korea",
DlgDocCharSetTR		: "Türgi",
DlgDocCharSetUN		: "Unicode (UTF-8)",
DlgDocCharSetWE		: "Lääne-Euroopa",
DlgDocCharSetOther	: "Ülejäänud märgistike kodeeringud",

DlgDocDocType		: "Dokumendi tüüppäis",
DlgDocDocTypeOther	: "Teised dokumendi tüüppäised",
DlgDocIncXHTML		: "Arva kaasa XHTML deklaratsioonid",
DlgDocBgColor		: "Taustavärv",
DlgDocBgImage		: "Taustapildi URL",
DlgDocBgNoScroll	: "Mittekeritav tagataust",
DlgDocCText			: "Tekst",
DlgDocCLink			: "Link",
DlgDocCVisited		: "Külastatud link",
DlgDocCActive		: "Aktiivne link",
DlgDocMargins		: "Lehekülje äärised",
DlgDocMaTop			: "Ülaserv",
DlgDocMaLeft		: "Vasakserv",
DlgDocMaRight		: "Paremserv",
DlgDocMaBottom		: "Alaserv",
DlgDocMeIndex		: "Dokumendi võtmesõnad (eraldatud komadega)",
DlgDocMeDescr		: "Dokumendi kirjeldus",
DlgDocMeAuthor		: "Autor",
DlgDocMeCopy		: "Autoriõigus",
DlgDocPreview		: "Eelvaade",

// Templates Dialog
Templates			: "Šabloon",
DlgTemplatesTitle	: "Sisu šabloonid",
DlgTemplatesSelMsg	: "Palun vali šabloon, et avada see redaktoris<br />(praegune sisu läheb kaotsi):",
DlgTemplatesLoading	: "Laen šabloonide nimekirja. Palun oota...",
DlgTemplatesNoTpl	: "(Ühtegi šablooni ei ole defineeritud)",
DlgTemplatesReplace	: "Asenda tegelik sisu",

// About Dialog
DlgAboutAboutTab	: "Teave",
DlgAboutBrowserInfoTab	: "Veebisirvija info",
DlgAboutLicenseTab	: "Litsents",
DlgAboutVersion		: "versioon",
DlgAboutInfo		: "Täpsema info saamiseks mine"
}

/* SCMS Specific translations */

FCKLang.SCMSSaveClose		= 'Salvesta' ;
FCKLang.SCMSInsertImage		= 'Pilt või fail' ;
FCKLang.SCMSInsertNewFile	= 'Lisa uus pilt või fail';
FCKLang.SCMSTitleIsMissing	= 'Palun täitke pealkiri!';
FCKLang.SCMSInsertForm		= 'Vorm';
FCKLang.SCMSLead			= 'Sissejuhatuse eraldaja';
FCKLang.SCMSSend			= 'Saada';
FCKLang.SCMSMaxCharsPos		= 'Maximum characters must be a positive number';
FCKLang.SCMSWidthPos		= 'Width must be a positive number';
FCKLang.SCMSInsertSiteLink	= 'Saidisisene link';
FCKLang.SCMSAdvancedToolbar	= 'Rohkem tööriistu' ;
FCKLang.SCMSSimpleToolbar	= 'Vähem tööriistu' ;
FCKLang.SCMSInsertSnippet	= 'Lisa HTML\'i (nt Youtube video)' ;

FCKLang.DlgSCMSRequired		= 'Kohustuslik';
FCKLang.DlgSCMSValidate		= 'Valideeri';
FCKLang.DlgSCMSChoose		= 'Vali';
FCKLang.DlgSCMSEmail		= 'E-post';
FCKLang.DlgSCMSNumeric		= 'Number';
FCKLang.DlgSCMSsysmail		= 'Saaja e-posti aadress';
FCKLang.DlgSCMSsysbadurl	= 'Vealehe aadress (URL)';
FCKLang.DlgSCMSsysokurl		= 'OK-lehe aadress (URL)';
FCKLang.DlgSCMSsubject		= 'E-kirja subjekt';
FCKLang.DlgSCMSPopIt		= 'Ava uues aknas';
FCKLang.DlgImgSize			= 'Suurus';
FCKLang.DlgImgOpenOriginal	= 'Ava originaal hüpikaknas';
FCKLang.DlgSCMSInsertSnippetTitle	= 'Lisa HTML\'i lõik';
FCKLang.DlgSCMS_Paste_your_HTML_snippet_below	= 'Kleebi HTML siia:';

/* /SCMS Specific translations */

FCKLang.FitWindow	= 'Muuda vaadet';	