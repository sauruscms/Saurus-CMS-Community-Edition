//node object methods
//tree method, traverses whole tree, recursive
function traverse(action)
{
	action.execute(this);
	for(var i = 0; i < this.children.length; i++)
	{
		this.children[i].traverse(action);
	}
}

//tree method, traverses only loaded nodes of the tree, recursive
function traverseLoaded(action)
{
	action.execute(this);
	for(var i = 0; i < this.children.length; i++)
	{
		if(this.children[i].pealkiri) this.children[i].traverseLoaded(action);
	}
}

//tree method, traverses only loaded nodes of the tree, the action object may stop traversal, recursive
function traverseLoadedStop(action)
{
	action.execute(this);
	for(var i = 0; i < this.children.length; i++)
	{
		if(this.children[i].pealkiri && !action.stop) this.children[i].traverseLoadedStop(action);
	}
}
// /node object methods

// classes for tree traversal actions
// class to remove subtree id's from loaded cookie
var ClearCookie = function (cookie_name)
{
	this.execCount = 0;
	this.execute = function (node)
	{
		this.execCount++;
		deleteIdFromCookie(node.objekt_id.value, cookie_name)
	}
}

// class to add open node ids to cookie
var AddCookie = function ()
{
	this.execCount = 0;
	this.execute = function (node)
	{
		this.execCount++;
		if(node.folded == 0) addIdToCookie(node.objekt_id.value, 'swk_unfolded_ids');
	}
}

// class to hide tree nodes
var HideNodes = function ()
{
	this.execCount = 0;
	this.execute = function (node)
	{
		this.execCount++;
		if(node.container)
		{
			node.container.style.display = 'none';
			var checkbox = document.getElementById('node_checkbox_' + node.objekt_id.value);
			if(checkbox)
			{
				checkbox.checked = false;
				node.container.className = node.className;
			}
		}
	}
}

// class to display tree nodes
var DisplayNodes = function ()
{
	this.execCount = 0;
	this.execute = function (node)
	{
		this.execCount++;
		if(node.container && !node.folded)
		{
			if(node.inSearch) node.container.className = 'searched';
			for(var i = 0; i < node.children.length; i++)
			{
				if(node.children[i].container)
				{
					node.children[i].container.style.display = (isIE ? 'block' : 'table-row');
				}
			}
		}
	}
}

//class for tree initialisation
var InitTree = function (container_id, fields)
{
	this.execCount = 0;
	this.container = 0;
	this.parentNodes = new Array();
	this.execute = function (node)
	{
		this.execCount++;
		if(!this.container) this.container = document.getElementById(container_id);
		//create the row
		if(!node.container)
		{
			var row = this.container.insertRow(-1);
			node.setContainer(row);
		}
		//by default hide all nodes
		node.container.style.display = 'none';
		//for each field to show
		for(var i = 0; i < fields.length; i++)
		{
			var field = fields[i];
			//create the field cell
			var cell = node.container.insertCell(-1);
			node[field].setContainer(cell);
			//if the field object has init method run it
			if(node[field].init) node[field].init(node);
			//render html
			node[field].setHTML();
		}
		if(node.inSearch)
		{
			node.container.className = 'searched';
		}
		if(node.selected)
		{
			toggleSelectById(node.objekt_id.value);
		}
		if(checkIdFromCookie(node.objekt_id.value, 'swk_unfolded_ids'))
		{
			node.folded = 0;
		}
		
		if(!node.parent_id)
		{
			node.parent_id = new parent_id(0);
			this.parentNodes[node.objekt_id.value] = node;
			node.parent = null;
		}
		else
		{
			node.parent = this.parentNodes[node.parent_id.value];
			this.parentNodes[node.objekt_id.value] = node;
		}
	}
}

//class to show loaded nodes
var InitTreeDisplay = function (container_id)
{
	this.execCount = 0;
	this.execute = function (node)
	{
		this.execCount++;
		if(node.pealkiri)
		{
			node.container.style.display = (isIE ? 'block' : 'table-row');
			var unfolder = document.getElementById('node_unfolder_' + node.objekt_id.value)
			var folder = document.getElementById('node_folder_' + node.objekt_id.value)
			//hide unfold element
			if(node.folded == 0 && unfolder) unfolder.style.display = 'none';
			//hide fold element
			else if(node.folded == 1 && folder) folder.style.display = 'none';
		}
	}
}

//class for searching tree node using objekt id
var FindNode = function (objekt_id)
{
	this.node = 0;
	this.stop = 0;
	this.execCount = 0;
	this.execute = function (node)
	{
		this.execCount++;
		if(node.objekt_id.value == objekt_id)
		{
			this.stop = 1;
			this.node = node;
		}
	}
}

// class for counting selected nodes
var CountSelected = function ()
{
	this.selected = 0;
	this.execCount = 0;
	this.execute = function (node)
	{
		this.execCount++;
		var checkbox = document.getElementById('node_checkbox_' + node.objekt_id.value);
		if(checkbox && checkbox.checked) this.selected++;
	}
}

// class for getting selected nodes
var GetSelected = function ()
{
	this.nodes = new Array();
	this.execCount = 0;
	this.execute = function (node)
	{
		this.execCount++;
		var checkbox = document.getElementById('node_checkbox_' + node.objekt_id.value);
		if(checkbox && checkbox.checked)
		{
			if(node.parent){
				ascend = new ToTop(node);
				ascend.reverse(ascend);
			}else{
				var ascend = new Array();
			}
				ascend.push({objekt_id: node.objekt_id.value, pealkiri: node.pealkiri.value});
			this.nodes.push({objekt_id: node.objekt_id.value, pealkiri: node.pealkiri.value, trail: ascend});
		}
	}
}

// we get a path from current node to the top. 
var ToTop = function (node)
{
		var parent = node.parent;
		var i = true;
		var trail = new Array();

				while(i){
					trail.push({objekt_id: parent.objekt_id.value, pealkiri: parent.pealkiri.value});
						if(parent.parent){
						parent=parent.parent;
						}else{
						i = false;
						}
				}
				
		return trail;
}


// class for unselecting with exception id
var UnSelectExcept = function (id)
{
	this.execCount = 0;
	this.execute = function (node)
	{
		this.execCount++;
		var checkbox = document.getElementById('node_checkbox_' + node.objekt_id.value);
		if(checkbox && checkbox.checked && node.objekt_id.value != id)
		{
			checkbox.checked = false;
			toggleNodeSelectCheckBox(node);
		}
	}
}
// /classes for tree traversal actions

//node properties as classes
//common functions
function setHTML(html)
{
	if(html) this.HTML = html;
	if(this.container)
	{
		this.container.innerHTML = this.HTML;
		this.container.className = this.className;
	}
}

//NB! used in property classes *and* node class
function setContainer(dom_object)
{
	this.container = dom_object;
}
// /common functions
//classes, as there is no easy way to make functional inheritance, these objects should follow dummy_class definition
/*
var dummy_class = function (value)
{
	this.value = value;
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
}
*/
var objekt_id = function (value)
{
	this.value = value;
	this.className = 'objekt_id';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
	this.init = function(node)
	{
		this.HTML = (node.objekt_id.value == 0 ? '' : node.objekt_id.value);
	}
}

var parent_id = function (value)
{
	this.value = value;
	this.className = 'parent_id';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
}

var level = function (value)
{
	this.value = value;
	this.className = 'level';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
}

var on_avaldatud = function (value)
{
	this.value = value;
	this.className = 'on_avaldatud';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
	this.init = function(node)
	{
		this.HTML = (node.on_avaldatud.value == 0 ? '' : '<img src="'+ styles_path +'/gfx/icons/16x16/actions/check.png">');
	}
}

var pealkiri = function (value)
{
	this.value = value;
	this.className = 'pealkiri';
	this.HTML = this.value;
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
	this.init = function(node)
	{
		//tree padding
		this.container.style.paddingLeft = node.level.value * 14;
		//creates the html
		this.HTML = (node.children.length ? '<a href="javascript:unfold(' + node.objekt_id.value + ');" id="node_unfolder_' + node.objekt_id.value + '" class="node_unfold_button"><img src="' + styles_path + '/gfx/swk_plus.gif"></a><a href="javascript:fold(' + node.objekt_id.value + ');" id="node_folder_' + node.objekt_id.value + '" class="node_fold_button"><img src="' + styles_path + '/gfx/swk_minus.gif"></a>' : '<img src="' + styles_path + '/gfx/px.gif" height="11" width="11">') + '<span class="title_text"' + (select_mode ? ' onclick="toggleSelectById(' + node.objekt_id.value + ');"' : '') + '>' + (select_mode == 0 && (node.tyyp_id.value == '1' || node.tyyp_id.value == '2') ? '<a href="' + wwwroot + '/editor/?id=' + node.objekt_id.value + '" target="_blank">' : '') + this.value + (select_mode == 0 && (node.tyyp_id.value == '1' || node.tyyp_id.value == '2') ? '</a>' : '') + '</span>';
	}
}

var select_checkbox = function (value)
{
	this.value = value;
	this.className = 'select_checkbox';
	this.HTML = '';	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
	this.init = function(node)
	{
		//creates the html
		if(node.objekt_id.value != 0)
		{
			this.HTML = '<input type="checkbox" name="selection[]" id="node_checkbox_' + node.objekt_id.value + '" onclick="toggleNodeSelectById(' + node.objekt_id.value + ');">';
		}
		else
		{
			this.HTML = '';
		}
	}
}

var klass = function (value)
{
	this.value = value;
	this.className = 'klass';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
}

var sys_alias = function (value)
{
	this.value = value;
	this.className = 'sys_alias';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
}

var friendly_url = function (value)
{
	this.value = value;
	this.className = 'friendly_url';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
}

var ttyyp_id = function (value)
{
	this.value = value;
	this.className = 'ttyyp_id';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
	this.init = function(node)
	{
		this.HTML = (node.ttyyp_id.value == 0 ? '' : node.ttyyp_id.value);
	}
}

var page_ttyyp_id = function (value)
{
	this.value = value;
	this.className = 'page_ttyyp_id';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
	this.init = function(node)
	{
		this.HTML = (node.page_ttyyp_id.value == 0 ? '' : node.page_ttyyp_id.value);
	}
}

var kesk = function (value)
{
	this.value = value;
	this.className = 'kesk';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
	this.init = function(node)
	{
		this.HTML = (node.kesk.value == 0 ? '' : node.kesk.value);
	}
}

var aeg = function (value)
{
	this.value = value;
	this.className = 'aeg';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
	this.init = function(node)
	{
		this.HTML = (node.aeg.value == '00.00.0000' || node.aeg.value == '00.00.0000 00:00' ? '' : node.aeg.value);
	}
}

var tyyp_id = function (value)
{
	this.value = value;
	this.className = 'tyyp_id';
	this.HTML = this.value;	
	this.container = 0;
	this.setHTML = setHTML;
	this.setContainer = setContainer;
}

// classes
// /node properties as javascript classes

// functions for managing comma separated id strings in cookies
function deleteIdFromCookie(id, cookie_name)
{
	var id_string = getCookie(cookie_name);
	if(id_string)
	{
		var ids = id_string.split(',');
		id_string = '';
		for(var i = 0; i < ids.length; i++)
		{
			if(id != ids[i]) id_string += ids[i] + ',';
		}
		id_string = id_string.replace(/,$/, '');
		setCookie(cookie_name, id_string);
	}
}

function addIdToCookie(id, cookie_name)
{
	var id_string = getCookie(cookie_name);
	if(id_string)
	{
		var ids = id_string.split(',');
		var flag = 1;
		for(var i = 0; i < ids.length; i++)
		{
			if(id == ids[i]) flag = 0;
		}
		if(flag) setCookie(cookie_name, id_string + ',' + id);
	}
	else
	{
		setCookie(cookie_name, id);
	}
}

function checkIdFromCookie(id, cookie_name)
{
	var id_string = getCookie(cookie_name);
	if(id_string)
	{
		var ids = id_string.split(',');
		for(var i = 0; i < ids.length; i++)
		{
			if(id == ids[i]) return 1;
		}
	}
	return 0;
}
// / functions for managing comma separated id strings in cookies

// standalone functions
// unfold subtree
function unfold(id)
{
	loaded = 0;
	var search = new FindNode(id);
	tree.traverseLoadedStop(search);
	search.node;
	if(search.node)
	{
		//set fold status
		search.node.folded = 0;
		// add to cookie
		search.node.traverseLoaded(new AddCookie());
		//load next level objects if not loaded
		if(search.node.children.length && !search.node.children[0].pealkiri)
		{
			var flag = 1;
			for(var i = 0; i <search.node.children.length; i++)
			{
				if(search.node.children[i].pealkiri) flag = 0;
			}
			if(flag)
			{
				var url = 'explorer_ajax_pull_objects.php';
				var pars = 'parent_id=' + search.node.objekt_id.value + '&lang=' + language_id + '&swk_setup=' + swk_setup + '&rand = ' + Math.random(9999);
				//globally :'( (IE & prototype.js aren't getting along so well)
				requestedNode = search.node;
				var request = new Ajax.Request(url, {method: 'get', parameters: pars, onLoading: onNodeLoad, onComplete: nodeLoadResponse});
			}
		}
		//unfold subtree
		search.node.traverseLoaded(new DisplayNodes());
		//show fold element
		document.getElementById('node_folder_' + search.node.objekt_id.value).style.display = 'inline';
		//hide unfold element
		document.getElementById('node_unfolder_' + search.node.objekt_id.value).style.display = 'none';
	};
}
// /unfold subtree

// on loading subtree
function onNodeLoad ()
{
	requestedNode.className = requestedNode.container.className;
	requestedNode.container.className = 'loading';
}
// /on loading subtree

// loading subtree
function nodeLoadResponse (originalRequest)
{
	requestedNode.container.className = requestedNode.className;
	if(!loaded.length)
	{
		requestedNode.children = 0;
		//delete fold element
		var folder = document.getElementById('node_folder_' + requestedNode.objekt_id.value);
		if(folder) folder.parentNode.removeChild(folder);
		//delete unfold element
		var unfolder = document.getElementById('node_unfolder_' + requestedNode.objekt_id.value);
		if(unfolder) unfolder.parentNode.removeChild(unfolder);
		requestedNode.pealkiri.setHTML('<img src="' + styles_path + '/gfx/px.gif" height="11" width="11">' + requestedNode.pealkiri.container.innerHTML);
		deleteIdFromCookie(requestedNode.objekt_id.value, 'swk_unfolded_ids');
	}
	else
	{
		var r = -1;
		for(var i = 0; i < loaded.length; i++)
		{
			//search for the node in children
			if(requestedNode.children.length) for(var k = 0; k <  requestedNode.children.length; k++)
			{	
				if(requestedNode.children[k].objekt_id.value == loaded[i].objekt_id.value)
				{
					// attache all loaded fields
					for(var field in loaded[i])
					{
						requestedNode.children[k][field] = loaded[i][field];
					}
					var container = document.getElementById('tree_content');
					//create the row
					r++;
					var row = container.insertRow( requestedNode.container.rowIndex - 1 + r);
					requestedNode.children[k].setContainer(row);
					//for each field to show
					for(var l = 0; l < loadFields.length; l++)
					{
						var field = loadFields[l];
						//create the field cell
						var cell =  requestedNode.children[k].container.insertCell(-1);
						requestedNode.children[k][field].setContainer(cell);
						//if the field object has init method run it
						if( requestedNode.children[k][field].init) requestedNode.children[k][field].init(requestedNode.children[k]);
						//render html
						requestedNode.children[k][field].setHTML();
					}
					
					//hide fold element
					if(document.getElementById('node_folder_' + requestedNode.children[k].objekt_id.value))
					{
						document.getElementById('node_folder_' + requestedNode.children[k].objekt_id.value).style.display = 'none';
					}
					//show unfold element
					if(document.getElementById('node_unfolder_' + requestedNode.children[k].objekt_id.value)) document.getElementById('node_unfolder_' + requestedNode.children[k].objekt_id.value).style.display = 'inline';

					requestedNode.children[k].parent = requestedNode;
				}
			}
		}
	}
}
// /loading subtree

// folding subtree
function fold(id)
{
	var search = new FindNode(id);
	tree.traverseLoadedStop(search);
	if(search.node && search.node.children.length)
	{
		// delete from cookie
		search.node.traverseLoaded(new ClearCookie('swk_unfolded_ids'));
		//set fold status
		search.node.folded = 1;
		for(var i = 0; i < search.node.children.length; i++)
		{
			search.node.children[i].traverseLoaded(new HideNodes());
		}
		//hide fold element
		document.getElementById('node_folder_' + search.node.objekt_id.value).style.display = 'none';
		//show unfold element
		document.getElementById('node_unfolder_' + search.node.objekt_id.value).style.display = 'inline';
	}
}
// /folding subtree

function toggleChooseButton()
{
	var choose_button = document.getElementById('choose_button');
	if(choose_button)
	{
		var counter = new CountSelected();
		tree.traverseLoaded(counter);
		
		//single selection
		if(select_mode == 1 && counter.selected == 1)
			choose_button.disabled = false;
		//multi selection
		else if (select_mode == 2 && counter.selected > 0)
			choose_button.disabled = false;
		else
			choose_button.disabled = true;
	}
}

function toggleSelectById(id)
{
	var checkbox = document.getElementById('node_checkbox_' + id);
	if(checkbox.checked)
	{
		checkbox.checked = false;
	}
	else
	{
		checkbox.checked = true;
	}
	toggleNodeSelectById(id);
}

function toggleNodeSelectById(id)
{
	if(select_mode == 1) tree.traverseLoaded(new UnSelectExcept(id));
	var search = new FindNode(id);
	tree.traverseLoadedStop(search);
	if(search.node) toggleNodeSelectCheckBox(search.node);
	toggleChooseButton();
}

function toggleNodeSelectCheckBox(node)
{
	var checkbox = document.getElementById('node_checkbox_' + node.objekt_id.value);
	if(checkbox.checked)
	{
		node.className = node.container.className;
		node.container.className = 'selected';
	}
	else
	{
		node.container.className = node.className;
	}
}

//search form functions
function clearFilters()
{
	var form = document.getElementById('filters');
	for(var i = 0; i < form.elements.length; i++)
	{
		form.elements[i].value = '';
	}
	form.submit();
}

function submitFilters()
{
	var form = document.getElementById('filters');
	form.submit();
}

function changeLang(lang)
{
	setCookie('swk_unfolded_ids', 0);
	var href = window.location.href.split('?');
	if(href[1])
	{
		if(href[1].match(/lang=([0-9]*)/))
		{
			href[1] = href[1].replace(/lang=([0-9]*)/, 'lang=' + lang);
		}
		else
		{
			href[1] += '&lang=' + lang;
		}
		window.location.href = href[0] + '?' + href[1];
	}
	else
	{
		window.location.href = '?lang='+lang;
	}
}
