
function InstrumentsTable(addURL, deleteURL, getURL, changeNameURL, changeTypeURL, changeUserURL) {
	this.addURL = addURL;
	this.deleteURL = deleteURL;
	this.getURL = getURL;
	this.changeNameURL = changeNameURL;
	this.changeTypeURL = changeTypeURL;
	this.changeUserURL = changeUserURL;
	this.toUpdate = [];
	this.inUpdate = false;
	this.lastEditIndex = -1;
	this.lastEditPosition = -1;
	this.loadTable();
}

InstrumentsTable.prototype.loadTable = function() {
	this.ajaxRequest(this.getURL, {});
}

InstrumentsTable.prototype.addInstrument = function() {
	this.ajaxRequest(this.addURL, {});
}


InstrumentsTable.prototype.deleteInstrument = function(index) {
	this.ajaxRequest(this.deleteURL, {'index': index});
}

InstrumentsTable.prototype.updateTable = function(data) {
	jQuery('#instrumentsTableDiv').replaceWith(data["html"]);
	this.instrumentsUsed = data["instrumentsUsed"];
	this.totNScores = data["totNScores"];
	var lastInput = document.getElementsByName("instrumentName")[this.lastEditIndex];
	if(this.lastEditIndex>=0) {
		lastInput.focus();
		lastInput.setSelectionRange(this.lastEditPosition, this.lastEditPosition);
	}
	this.toUpdate.forEach(function(elm, index){
		elm.update();
	});
	this.inUpdate = false;
}

InstrumentsTable.prototype.changeType = function(index, value) {
	this.ajaxRequest(this.changeTypeURL, {'index': index, 'value': value});
}

InstrumentsTable.prototype.changeUser = function(index, id) {
	this.ajaxRequest(this.changeUserURL, {'index': index, 'id': id});
}

InstrumentsTable.prototype.changeName = function(index, value) {
	if(this.inUpdate)
		return;
	if(value.length>0) {
		document.getElementsByName("instrumentName")[index].readOnly = true;
		this.ajaxRequest(this.changeNameURL, {'index': index, 'value': value});
	}
	this.isUpdate = true;
}

InstrumentsTable.prototype.textEdited = function(index, value) {
	if (typeof this.activeTimeout !== 'undefined')
		clearTimeout(this.activeTimeout);
	this.lastEditIndex = index;
	this.lastEditPosition = document.getElementsByName("instrumentName")[index].selectionStart;
	var instrumentsTable = this;
	this.activeTimeout = setTimeout(function() { instrumentsTable.changeName(index, value) }, 1000);
}

InstrumentsTable.prototype.checkNameLength = function(e) {
	if(e.keyCode==8 || e.keyCode==37 || e.keyCode==39)
		return true;
	return e.target.value.length<12;
}

InstrumentsTable.prototype.ajaxRequest = function(url, data) {
	var instrumentsTable = this;
	$.ajax({
		type: 'post',
		url: url,
		data: data,
		dataType: 'JSON',
		success: function(data) { instrumentsTable.updateTable(data); },
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		}
	});
}
