//check username and password while login
function checkLoginPassword(){
	var userName = document.getElementById('szUserName');
	var password = document.getElementById('szPassword');
	if(userName.value == ''){
		userName.select();
		userName.style.background="#ff9999";
		alert('Username cannot be empty!'); 
		return false;
	}
	if(password.value == ''){
		password.select();
		password.style.background="#ff9999";
		alert('Password cannot be empty!'); 
		return false;
	}
}

//Select all entries in a view
function toggleSelectAllItems(toggleCheckBox){
	var checkBoxesList =  document.getElementsByName("entityId[]");
	for(var i=0; i < checkBoxesList.length; i++){
		if(toggleCheckBox.checked)
			checkBoxesList[i].checked = true;
		else
			checkBoxesList[i].checked = false;
	}
}

//Delete function for Views
function deleteEntries(){
	//Check if there are selected entries to delete
	var checkBoxesList =  document.getElementsByName("entityId[]");
	for(var i=0; i < checkBoxesList.length; i++){
		if(checkBoxesList[i].checked){
			if(confirm('Are you sure you want to delete these entries?'))
				return true;
			else
				return false
		}
	}
	return false;
}

//Delete function for Forms
function deleteEntry(entityId, entityName, szForward){
	if(confirm('Are you sure you want to delete this entry?'))
		window.location.href='deleteEntities.php?entityId[0]='+entityId+'&entityName='+entityName+'&szForward='+szForward+'&closewindow=yes';
	else
		return false;
}

function switchView(page, filterValue, itemsPerPage){
	window.location.href=page+'?view='+filterValue+'&items='+itemsPerPage;
}

function switchItemsNumber(page, viewFilterValue, itemsPerPage, sortAttributeName, sortDirection, szSearchString){
	window.location.href=page+'?items='+itemsPerPage+'&view='+viewFilterValue+'&sort='+sortAttributeName+'&sortDirection='+sortDirection+'&search='+szSearchString;
}

function lookup(entity, inputid, labelid){
	window.open("lookuprecords.php?entity=" + entity+"&inputid="+inputid+"&labelid="+labelid, "_blank", "location=no,menubar=no,toolbar=no,resizable=yes,scrollbars=yes,width=600,height=500");
}

function selectLookupValue(inputid, labelid, removeValue){
	if(removeValue == true){
		self.opener.document.getElementById(inputid).value = '';
		self.opener.document.getElementById(labelid).innerHTML = '';
		window.close();			
	}else{
		var radios = document.getElementsByName('entityId[]');
		for(var i=0; i < radios.length; i++){
			if(radios[i].checked){
				var value = radios[i].value;
				var checkedRadioLabelID = radios[i].id;
				var label = document.getElementById(checkedRadioLabelID+'_label').innerHTML;
				self.opener.document.getElementById(inputid).value = value;
				self.opener.document.getElementById(labelid).innerHTML = label;
				window.close();			
			}
		}
	}
}

function checkMandatoryFileds(){
	var mandatoryFields = document.getElementsByName('aMandatory[]');
	for(var i=0; i < mandatoryFields.length; i++){
		var elt = document.getElementById(mandatoryFields[i].value);
		if(elt.tagName.toLowerCase() == 'input'){
			if(elt.value == ''){
				elt.select();
				elt.style.backgroundColor = "#ff9999";
				alert('All mandatory fields must be filled!'); 
				return false;
			}
		}else if(elt.tagName.toLowerCase() == 'select'){
			if(elt.selectedIndex == 0){
				elt.focus();
				elt.style.backgroundColor = "#ff9999";
				alert('All mandatory fields must be filled!'); 
				return false;
			}
		}
	}
}