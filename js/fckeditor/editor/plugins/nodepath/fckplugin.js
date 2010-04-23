/*
 * File Name: nodePath\fckPlugin.js
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * 	Plugin for FCKeditor that shows a status bar with the path to the current selected node.
 *
 *  version 0.1 12/11/2005
 *		-initial release, just a nice status bar.
 *  version 0.2 21/03/2006 (Holger Hees)
 *		-extended release, with the ability to select elements from notelist.
 *  version 0.3 20/05/2006 (Holger Hees)
 *		-extended release, multi-editor ability
 *  
 * 
 * File Authors:
 * 		Alfonso Martínez de Lizarrondo (Uritec) alfonso -at- uritec dot net
 *      Holger Hees hhees -at- systemconcept dot de
 */

var StatusBar = function (editorInstance) {
	var td=document.createElement('TD');
	td.id='statusBar';
	//td.appendChild(document.createTextNode(" "));
	td.innerHTML='&nbsp;';
	//td.style.font='menu';
	td.className='WYSIWYGPfad';
	var tr=document.createElement('TR');
	tr.appendChild(td);
	editorInstance.EditingArea.TargetElement.parentNode.parentNode.appendChild(tr);
	this.Bar = td;
	this.Instance = editorInstance;
}

var sbNode=new Array();
//it should be part of the StatusBar object, but then I can't manage to get the event properly.
createStatusBar = function(oStatusBar) {
	var node;
	var text='&nbsp;'; // + FCKSelection.GetType() + " " + new Date();

    var firstNode=false;
	//the selected node (not valid if nothing is selected)
	node=oStatusBar.Instance.Selection.GetSelectedElement();
	if (node) {
		text=getNodeName(node) + text;
        firstNode=true;
	}

    var i=0;
	//parent, up to the body
	node=oStatusBar.Instance.Selection.GetParentElement();
    sbNode[oStatusBar.Instance.Name]=node;
	while (node && node.nodeName!='BODY') {
        if(firstNode) text = '&nbsp;&raquo;&nbsp;' + text;
		text='<a href="javascript:selectSBElement(\''+oStatusBar.Instance.Name+'\','+i+')">'+getNodeName(node)+'</a>' + text;
		node=node.parentNode;
        firstNode=true;
        i++;
	}

	//update the text
	oStatusBar.Bar.innerHTML='html:&nbsp;'+text;
}

function selectSBElement(instanceName,pos){
    if(sbNode[instanceName]){
        node=sbNode[instanceName];
        for(i=0;i<pos;i++){
            node=node.parentNode;
        }
		statusBars[instanceName].Instance.Selection.SelectNode(node);
        FCK.Events.FireEvent( "OnSelectionChange" )
    }
}

//auxiliary function to get the real name of the tag.
//It could be further improved.
getNodeName = function(oTag) {
	var oRealTag;
	
	if (!oTag) 
		return "";
		
	if (!oTag.getAttribute) 
		return "";
		
	if (oTag.getAttribute('_fckfakelement') )
		oRealTag = FCK.GetRealElement( oTag ) ;
	else 
		oRealTag=oTag;

	var sTagName=oRealTag.nodeName.toLowerCase();

	var name=sTagName;

	return name;
}

var statusBars = new Array();
function updateStatusBar(editorInstance){
	if(!statusBars[editorInstance.Name]){
		statusBars[editorInstance.Name]=new StatusBar(editorInstance);
	}
	else{
		createStatusBar(statusBars[editorInstance.Name]);
	}
}

FCK.Events.AttachEvent('OnSelectionChange', updateStatusBar ) ;
FCK.Events.AttachEvent('OnStatusChange',updateStatusBar);