
/* Set focus to headline in the edit-popups*/
function setHeadlineFocus(){
	eval('if(typeof(document.frmEdit)!="undefined"){if(typeof(document.frmEdit.pealkiri)!="undefined"){document.frmEdit.pealkiri.focus();};}');
}

/* changes profile-block visibility if profile is changed in selectbox */
function changeProfile(obj) {
	var divs = document.getElementsByTagName('div');
	for( var i in divs) {
		if(/div/i.test(divs[i].tagName) && /profile_\d+/i.test(divs[i].id) ) {
			divs[i].style.display = 'none';
		}
	}
	if (obj.value) document.getElementById('profile_' + obj.value).style.display = 'block';
}

// section/album editor functions

// resizes window to specified dimensions
function resizeWindowTo(setw,seth)
{
	return window.resizeTo(setw,seth),window.resizeTo(setw*2-((typeof window.innerWidth ==
	'undefined')?document.body.clientWidth:window.innerWidth),seth*2-((typeof window.innerHeight ==
	'undefined')?document.body.clientHeight:window.innerHeight));
}

// resizes window height
function resizeDocumentHeightTo(seth)
{
	var setw = ((typeof window.innerWidth == 'undefined') ? document.body.clientWidth : window.innerWidth);
	resizeDocumentTo(setw, seth);
}

// toggle advanced/profile etc panels
function togglePanel(panel)
{
	var panel_link_state = document.getElementById(panel + '_panel_link_state');
	var panel_state = document.getElementById(panel + '_panel_state');
	var panel = document.getElementById(panel + '_panel');
	
	var height = panel.offsetHeight;
	
	if(!panel.style.display || panel.style.display.match('none'))
	{
		panel_link_state.innerHTML = '&laquo;';
		panel.style.display = 'block';
		window.resizeBy(0, panel.offsetHeight);
		panel_state.value = 1;
	}
	else
	{
		panel_link_state.innerHTML = '&raquo;';
		panel.style.display = 'none';
		window.resizeBy(0, -height);
		panel_state.value = 0;
	}
}

// refreshes form, used when select values are changed
function refreshForm()
{
	var form = document.getElementById('editForm');
	form.refresh.value = 1;
	form.submit();
}

function togglePublishing(state)
{
	var form = document.getElementById('editForm');
	
	if(state)
	{
		form.publish.value = 1;
	}
	else
	{
		form.publish.value = 0;
	}
}
