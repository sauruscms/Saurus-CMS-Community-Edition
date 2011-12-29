//---------------------------------------------------------
//                      window opening functions
//---------------------------------------------------------

function avapopup(mypage, myname, w, h, scroll) {
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',notresizable'
	win = window.open(mypage, myname, winprops)
	if (win.opener == null) { win.opener = self }
	if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
}

// new, "saurus 4 style compatible" popup function:
function openpopup(mypage, myname, w, h) {
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars=no,resizable'
	win = window.open(mypage, myname, winprops)
	if (win.opener == null) { win.opener = self }
	if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
	return win;
}

function avaaken(link, x, y, nimi) {
	var winl = (screen.width - x) / 2;
	var wint = (screen.height - y) / 2;
	editWindow = window.open(link, nimi,'width='+x+',height='+y+',top='+wint+',left='+winl+',toolbar=0,directories=0,menubar=0,status=0,resizable=1,location=0,scrollbars=yes,copyhistory=0');
	if (editWindow.opener == null) { editWindow.opener = self }
	if (parseInt(navigator.appVersion) >= 4) { editWindow.window.focus(); }
}

function avaaken2(link, x, y, doScroll) {
	var winl = (screen.width-x)/2;
	var wint = (screen.height-y)/2;

	var scr = (doScroll)? 'yes' : 'no'

	var newWin = window.open(link, 'Fail','width='+x+',height='+y+',toolbar=0,directories=0,menubar=0,status=0,resizable=1,location=0,scrollbars=' + scr + ',copyhistory=0,left=' + winl + ',top=' + wint);
	newWin.focus();
}
function avaaken3(winname,link, x, y, doScroll) {
	var winl = (screen.width-x)/2;
	var wint = (screen.height-y)/2;

	var scr = (doScroll)? 'yes' : 'no'
	var newWin = window.open(link, winname,'width='+x+',height='+y+',toolbar=0,directories=0,menubar=0,status=0,resizable=1,location=0,scrollbars=' + scr + ',copyhistory=0,left=' + winl + ',top=' + wint);
	newWin.focus();
}

function avaprintaken(link, x, y, nimi, toolbar) {
	var winl = (screen.width - x) / 2;
	var wint = (screen.height - y) / 2;
	editWindow = window.open(link, nimi,'width='+x+',height='+y+',top='+wint+',left='+winl+',toolbar='+toolbar+',directories=0,menubar=1,status=0,resizable=1,location=0,scrollbars=yes,copyhistory=0');
	if (editWindow.opener == null) { editWindow.opener = self }
}

function gallupaken(link, x, y, nimi) {
	var winl = (screen.width - x) / 2;
	var wint = (screen.height - y) / 2;
	editWindow = window.open(link, nimi,'width='+x+',height='+y+',top='+wint+',left='+winl+',toolbar=0,directories=0,menubar=0,status=0,resizable=1,location=0,scrollbars=no,copyhistory=0');
	if (editWindow.opener == null) { editWindow.opener = self }
}

//---------------------------------------------------------
//      dialog window functions (works both in IE and NN)
//---------------------------------------------------------

// Global for brower version branching.
var Nav4 = ((navigator.appName == "Netscape") && (parseInt(navigator.appVersion) == 4))

// One object tracks the current modal dialog opened from this window.
var dialogWin = new Object()

// Generate a modal dialog.
// Parameters:
//    url -- URL of the page/frameset to be loaded into dialog
//    width -- pixel width of the dialog window
//    height -- pixel height of the dialog window
//    returnFunc -- reference to the function (on this page)
//                  that is to act on the data returned from the dialog
//    args -- [optional] any data you need to pass to the dialog
function openDialog(url, width, height, returnFunc, args) {
	if (!dialogWin.win || (dialogWin.win && dialogWin.win.closed)) {
		// Initialize properties of the modal dialog object.
		dialogWin.returnFunc = returnFunc
		dialogWin.returnedValue = ""
		dialogWin.args = args
		dialogWin.url = url
		dialogWin.width = width
		dialogWin.height = height
		// Keep name unique so Navigator doesn't overwrite an existing dialog.
		dialogWin.name = (new Date()).getSeconds().toString()
		// Assemble window attributes and try to center the dialog.
		if (Nav4) {
			// Center on the main window.
			dialogWin.left = window.screenX + 
			   ((window.outerWidth - dialogWin.width) / 2)
			dialogWin.top = window.screenY + 
			   ((window.outerHeight - dialogWin.height) / 2)
			var attr = "screenX=" + dialogWin.left + 
			   ",screenY=" + dialogWin.top + ",resizable=no,width=" + 
			   dialogWin.width + ",height=" + dialogWin.height
		} else {
			// The best we can do is center in screen.
			dialogWin.left = (screen.width - dialogWin.width) / 2
			dialogWin.top = (screen.height - dialogWin.height) / 2
			var attr = "left=" + dialogWin.left + ",top=" + 
			   dialogWin.top + ",resizable=no,width=" + dialogWin.width + 
			   ",height=" + dialogWin.height
		}
		
		// Generate the dialog and make sure it has focus.
		dialogWin.win=window.open(dialogWin.url, dialogWin.name, attr)
		dialogWin.win.focus()
	} else {
		dialogWin.win.focus()
	}
}

// Event handler to inhibit Navigator form element 
// and IE link activity when dialog window is active.
function deadend() {
	if (dialogWin.win && !dialogWin.win.closed) {
		dialogWin.win.focus()
		return false
	}
}

// Since links in IE4 cannot be disabled, preserve 
// IE link onclick event handlers while they're "disabled."
// Restore when re-enabling the main window.
var IELinkClicks

// Disable form elements and links in all frames for IE.
function disableForms() {
	IELinkClicks = new Array()
	for (var h = 0; h < frames.length; h++) {
		for (var i = 0; i < frames[h].document.forms.length; i++) {
			for (var j = 0; j < frames[h].document.forms[i].elements.length; j++) {
				frames[h].document.forms[i].elements[j].disabled = true
			}
		}
		IELinkClicks[h] = new Array()
		for (i = 0; i < frames[h].document.links.length; i++) {
			IELinkClicks[h][i] = frames[h].document.links[i].onclick
			frames[h].document.links[i].onclick = deadend
		}
	}
}

// Restore IE form elements and links to normal behavior.
function enableForms() {
	for (var h = 0; h < frames.length; h++) {
		for (var i = 0; i < frames[h].document.forms.length; i++) {
			for (var j = 0; j < frames[h].document.forms[i].elements.length; j++) {
				frames[h].document.forms[i].elements[j].disabled = false
			}
		}
		for (i = 0; i < frames[h].document.links.length; i++) {
			frames[h].document.links[i].onclick = IELinkClicks[h][i]
		}
	}
}

// Grab all Navigator events that might get through to form
// elements while dialog is open. For IE, disable form elements.
function blockEvents() {
	if (Nav4) {
		window.captureEvents(Event.CLICK | Event.MOUSEDOWN | Event.MOUSEUP | Event.FOCUS)
		window.onclick = deadend
	} else {
		disableForms()
	}
	window.onfocus = checkModal
}
// As dialog closes, restore the main window's original
// event mechanisms.
function unblockEvents() {
	if (Nav4) {
		window.releaseEvents(Event.CLICK | Event.MOUSEDOWN | Event.MOUSEUP | Event.FOCUS)
		window.onclick = null
		window.onfocus = null
	} else {
		enableForms()
	}
}

// Invoked by onFocus event handler of EVERY frame,
// return focus to dialog window if it's open.
function checkModal() {
	if (dialogWin.win && !dialogWin.win.closed) {
		dialogWin.win.focus()	
	}
}

function replace(string,text,by) {
// Replaces text with by in string
    var strLength = string.length, txtLength = text.length;
    if ((strLength == 0) || (txtLength == 0)) return string;
    var i = string.indexOf(text);
    if ((!i) && (text != string.substring(0,txtLength))) return string;
    if (i == -1) return string;
    var newstr = string.substring(0,i) + by;
    if (i+txtLength < strLength)
        newstr += replace(string.substring(i+txtLength,strLength),text,by);
    return newstr;
}

//---------------------------------------------------------
//                      Form Check
//---------------------------------------------------------

var whitespace = " \t\n\r";

function isEmpty(s)
{   return ((s == null) || (s.length == 0))
}

function isWhitespace (s) {
	var i;

    if (isEmpty(s)) return true;

    for (i = 0; i < s.length; i++)
    {   
        var c = s.charAt(i);

        if (whitespace.indexOf(c) == -1) return false;
    }

    return true;
}

function check_string (theField,alert_txt) {
	  if (isWhitespace(theField.value)) {
		if(!isWhitespace(alert_txt)) {
			alert(alert_txt);
		}
		//Trick to fool stupid browsers
		if(theField.focus()){};
		return false;
	  } else {
		return true;
	  }
	}

function check_email (theField,alert_txt){
	 var s = theField.value;
    // is s whitespace?
    if (isWhitespace(s)) {
		if(!isWhitespace(alert_txt)) {
			alert(alert_txt);
		}
		//Trick to fool stupid browsers
		if(theField.focus()){};
		return false;
	}
    
    // there must be >= 1 character before @, so we
    // start looking at character position 1 
    // (i.e. second character)
    var i = 1;
    var sLength = s.length;

    // look for @
    while ((i < sLength) && (s.charAt(i) != "@"))
    { i++
    }

    if ((i >= sLength) || (s.charAt(i) != "@")) {
		if(!isWhitespace(alert_txt)) {
			alert(alert_txt);
		}
		//Trick to fool stupid browsers
		if(theField.focus()){};
		return false; 
	} else {
		i += 2;
	}

    // look for .
    while ((i < sLength) && (s.charAt(i) != "."))
    { i++
    }

    // there must be at least one character after the .
    if ((i >= sLength - 1) || (s.charAt(i) != ".")) {
		if(!isWhitespace(alert_txt)) {
			alert(alert_txt);
		}
		//Trick to fool stupid browsers
		if(theField.focus()){};
		return false;
	} else {
		return true;
	}
}

//---------------------------------------------------------
//              Shopping cart functionality
//---------------------------------------------------------

function add_to_cart(formname,page,prod) {
	var qty;

	qty = eval('document.forms["'+formname+'"].qty'+prod+'.value');

	if (isEmpty(qty)) {
		qty = 1;
	}
	if (isWhitespace(qty)) {
		qty = 1;
	}
	
	avapopup(page + '?add=' + prod + '&qty=' + qty, 'add2cart', 250, 150, 'no');
}

function del_from_cart(formname,page,prod) {
	window.location.replace(page + '&del=' + prod);
}

function to_cart() {
	if(window.name=='add2cart' && window.opener) {
		window.opener.location.replace('./index.php?op=cart');
		window.close();
	} else {
		window.location.replace('./index.php?op=cart');
	}
}

function save_cart(page) {
	avapopup(page + '?op=save', 'savecart', 250, 100, 'no');
}

function setCookie(name, value, expire,path) {
	document.cookie = name + "=" + escape(value)+((expire == null)?"":(";expires="+expire.toGMTString()))
}

function getCookie(Name) {
	var search = Name + "="
	if (document.cookie.length > 0) { // if there are any cookies
	  offset = document.cookie.indexOf(search)
	  if (offset != -1) { // if cookie exists
	    offset += search.length
	    // set index of beginning of value
	    end = document.cookie.indexOf(";", offset)
	    // set index of end of cookie value
	      if (end == -1)
	      end = document.cookie.length
	      return unescape(document.cookie.substring(offset, end))
	      }
	}
}

function changeCookie(data,path) {
	var today = new Date();
	var expires = new Date();
	expires.setTime(today.getTime() + 3600*24*31);
	setCookie("saurus_shoppingcart", data, expires,path);
}

// Returns safe filename
// strips out special chars & spaces, replaces with "_"
// Usage: safename = safe_filename("Süsteemi fail");
function safe_filename(name) {
	var safename = name.toString();
	var re = new RegExp("(\\W+)", "g");
	safename = safename.replace(re, "_");
	return safename;
}

//Datepicker initialization.
//field name - the ID that is used for the calendar
//min_date - the ID that contains the earliest possible date to select
//max_date - the ID that contains the latest possible date  to select

function init_datepicker(field_name,min_date,max_date){
	
	if ( window.jQuery && $ && $.datepicker && load_datepicker_settings()){

		jQuery(function($){

			if(min_date && max_date){

				function customRange(input) {
					return {minDate: (input.id == max_date ? $.datepicker.getDateFor('#' + min_date) : null),
						maxDate: (input.id == min_date ? $.datepicker.getDateFor('#' + max_date) : null)};
				}
				$('#' + field_name).datepicker({beforeShow: customRange,showOn: '', speed: '',  onSelect: function(dateText) { 
					document.getElementById(field_name).focus();$.datepicker.hideDatepicker();}});


			}else{

			
				$('#' + field_name).datepicker({showOn: '', speed: '',  onSelect: function(dateText) { 
					document.getElementById(field_name).focus();$.datepicker.hideDatepicker();}
				});

			}
		});

		$.datepicker.showFor('#' + field_name); 

	}else{

		openDialog('kalender.php?vorm=frmEdit&lahter=' + field_name + '&longyear=1','200','167');

	}
}