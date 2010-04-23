	/*
	 * User related JS - used in SAPI templates etc
	*/

function check_user_search(checkbox) {
	if(checkbox.checked){ document.getElementById('searchform_user_search').value='1'; }
	else{ document.getElementById('searchform_user_search').value='0'; }
	document.forms['searchform'].submit();		
}

function check_group_search(checkbox) {
	if(checkbox.checked){ document.getElementById('searchform_group_search').value='1'; }
	else{ document.getElementById('searchform_group_search').value='0'; }
	document.forms['searchform'].submit();		
}

function check_search_subtree(checkbox) {
	if(checkbox.checked){ document.getElementById('searchform_search_subtree').value='1'; }
	else{ document.getElementById('searchform_search_subtree').value='0'; }
	document.forms['searchform'].submit();		
}

function select_group(id) {
	if(document.getElementById('selectform_user_id')){
		document.getElementById('selectform_user_id').value='';
	}
	document.getElementById('selectform_group_id').value=id;
	document.forms['selectform'].submit();
}