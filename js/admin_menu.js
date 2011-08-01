function make_breadcrumb()
{
	var ar = arguments;
	
	if(top.window.setAdminPageTitle) top.window.setAdminPageTitle(ar);
}

var current_element = null;
var is_gecko = (navigator.product == "Gecko");

function show_menu(menu,color) {
	var sub = document.getElementById(menu);
	
	if(current_element != null) {
		var a = document.getElementById(current_element.id.replace(/sub/gi,"top"));
		a.style.backgroundColor = "";
		a.style.color = "";

	}

	var a = document.getElementById(menu.replace(/sub/gi,"top"));
	if(!color) color = "#D2DFFA";
	a.style.backgroundColor = color;
	a.style.color = "#466AB7";


	var other_divs = document.getElementsByTagName("div");
	for(d=0;d<other_divs.length;d++) {
		if(other_divs[d].className == "scms_dropdown_div") other_divs[d].style.visibility = "hidden";
	}

	sub.onmousemove=myMouseHandler
	current_element = sub;
	if(sub) sub.style.visibility = "visible";
}

function show_menu_onover(menu,color) {
	if(current_element != null) {
		show_menu(menu,color);
	}
}

window.document.onmousemove=myMouseHandler

function myMouseHandler(e) {

	if(current_element != null) {
		if(is_gecko == true) {
			if(e.pageX < (current_element.offsetLeft+3)) current_element.style.visibility = 'hidden';
			if(e.pageX > (current_element.offsetLeft+current_element.offsetWidth-3)) current_element.style.visibility = 'hidden';
			if(e.pageY > (current_element.offsetTop+current_element.offsetHeight-10)) current_element.style.visibility = 'hidden';
		} else {
			if(event.clientX < (current_element.offsetLeft+3)) current_element.style.visibility = 'hidden';
			if(event.clientX > (current_element.offsetLeft+current_element.offsetWidth-3)) current_element.style.visibility = 'hidden';
			if(event.clientY > (current_element.offsetTop+current_element.offsetHeight-3)) current_element.style.visibility = 'hidden';
		}

		if(current_element.style.visibility == 'hidden') {
			var a = document.getElementById(current_element.id.replace(/sub/gi,"top"));
			a.style.backgroundColor = "";
			a.style.color = "";
		}

	}
	return true
}

function close_menu() {
	var a = document.getElementById(current_element.id.replace(/sub/gi,"top"));
	a.style.backgroundColor = "";
	a.style.color = "";
	
	current_element.style.visibility='hidden';
	current_element=null;
}

// adminpage functions
if(typeof jQuery != 'undefined' && typeof SCMS != 'undefined') {
	
	$(function() {
	
		setMainDimensions();
		$(window).resize(setMainDimensions);
		
		if(SCMS.variables.adminpage_names) make_breadcrumb(SCMS.variables.adminpage_names.parent_pagename, SCMS.variables.adminpage_names.pagename);
		
		messageBox.init();
		alertBox.init();
	});
	
	function setMainDimensions() {
		$('#main').height($(window).height() - $('header').height() - $('footer').height() - 30);
	}
	
	var pageCover = {
		$cover: $('#page-cover'),
		show: function () {
			pageCover.$cover.removeClass('hidden');
		},
		hide: function () {
			pageCover.$cover.addClass('hidden');
		}
	}
	
	var messageBox = {
		
		show: function (message) {
			$('#message-box').html(message).removeClass('hidden');
			
			var delayTimer;
			
			if(delayTimer) {
				clearTimeout(delayTimer);
				delayTimer = null;
			}
			
			delayTimer = setTimeout(messageBox.hide, 4000);	
		},
		
		hide: function () {
			$('#message-box').addClass('hidden');
		},
		
		init: function() {
			$('#message-box').click(messageBox.hide);
		}
	}
	
	var alertBox = {
		showOK: function(message, callback) {
			alertBox.show(message, callback, 'OK');
		},
		
		showOKCancel: function(message, callback) {
			alertBox.show(message, callback, 'OKCancel');
		},
		
		show: function (message, callback, type) {
			$('#alert-box-message').html(message);
			
			$('#alert-box-button-ok').click(function () {
				alertBox.hide();
				callback();
			});
			
			if(type == 'OKCancel') {
				$('#alert-box-button-cancel').removeClass('hidden');
			}
			
			pageCover.show();
			$('#alert-box').removeClass('hidden');
		},
		
		hide: function () {
			$('#alert-box-button-ok').unbind('click');
			$('#alert-box-button-cancel').addClass('hidden');
			$('#alert-box').addClass('hidden');
			pageCover.hide();
		},
		
		init: function() {
			$('#alert-box-button-cancel').click(function () {
				alertBox.hide();
			});
		}
	}
}
