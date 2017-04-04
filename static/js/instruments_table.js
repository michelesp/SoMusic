
function InstrumentsTable(addURL, deleteURL, getURL, commitChangeURL, changeTypeURL) {
	this.addURL = addURL;
	this.deleteURL = deleteURL;
	this.getURL = getURL;
	this.commitChangeURL = commitChangeURL;
	this.changeTypeURL = changeTypeURL;
	this.toUpdate = [];
	this.lastEditTimeout = -1;
	this.lastEditIndex = -1;
	this.buffer = "";
	this.loadTable();
}

InstrumentsTable.prototype.loadTable = function() {
	var instrumentsTable = this;
	$.ajax({
		type: 'post',
		url: instrumentsTable.getURL,
		data: {},
		dataType: 'json',
		success: function(data) { instrumentsTable.updateTable(data); },
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		},
		complete: function(){ }
	});
}

InstrumentsTable.prototype.addInstrument = function() {
	var instrumentsTable = this;
	$.ajax({
		type: 'post',
		url: instrumentsTable.addURL,
		data: {'addInstrument': true,
			'instruments': instrumentsTable.instrumentsToSend()},
			dataType: 'json',
			success: function(data) { instrumentsTable.updateTable(data); },
			error: function( XMLHttpRequest, textStatus, errorThrown ){
				OW.error(textStatus);
			},
			complete: function(){ }
	});
}


InstrumentsTable.prototype.deleteInstrument = function(index) {
	var instrumentsTable = this;
	$.ajax({
		type: 'post',
		url: instrumentsTable.deleteURL,
		data: {'deleteInstrument': index,
			'instruments': instrumentsTable.instrumentsToSend()},
			dataType: 'json',
			success: function(data) { instrumentsTable.updateTable(data); },
			error: function( XMLHttpRequest, textStatus, errorThrown ){
				OW.error(textStatus);
			},
			complete: function(){ }
	});
}

InstrumentsTable.prototype.instrumentsToSend = function () {
	var instrumentName = document.getElementsByName("instrumentName");
	var instrumentType = document.getElementsByName("instrumentType");
	var instrumentUser = document.getElementsByName("instrumentUser");
	var instruments = [];
	for(var i=0; i<instrumentName.length; i++) 
		instruments.push({"name": instrumentName[i].value,
			"type": instrumentType[i].value,
			"user": instrumentUser[i].value});
	return instruments;
}

InstrumentsTable.prototype.updateTable = function(data) {
	jQuery('#instrumentsTableDiv').replaceWith(data["html"]);
	this.instrumentsUsed = data["instrumentsUsed"];
	this.totNScores = data["totNScores"];
	var lastInput = document.getElementsByName("instrumentName")[this.lastEditIndex];
	if(this.buffer.length>0) {
		lastInput.value = this.buffer;
		this.textEdited(this.lastEditIndex, this.buffer);
	}
	if(this.lastEditIndex>=0) {
		lastInput.focus();
	}
	this.toUpdate.forEach(function(elm, index){
		elm.update();
	});
}

InstrumentsTable.prototype.changeType = function(index, value) {
	var instrumentsTable = this;
	$.ajax({
		type: 'post',
		url: this.changeTypeURL,
		data: {'index': index, 'value': value},
		dataType: 'json',
		success: function(data) { instrumentsTable.updateTable(data); },
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		},
		complete: function(){ }
	});
}

InstrumentsTable.prototype.textEdited = function(index, value) {
	this.buffer = value;
	this.lastEditTimeout = Date.now();
	this.lastEditIndex = index;
	var instrumentsTable = this;
	if (typeof this.activeTimeout !== 'undefined')
		clearTimeout(this.activeTimeout);
	this.activeTimeout = setTimeout(function() { instrumentsTable.commitChange() }, 500);
}

InstrumentsTable.prototype.commitChange = function() {
	if(this.lastEditTimeout==-1)
		return;
	var instrumentsTable = this;
	this.buffer = "";
	$.ajax({
		type: 'post',
		url: instrumentsTable.commitChangeURL,
		data: {'instruments': instrumentsTable.instrumentsToSend()},
		dataType: 'json',
		success: function(data) { instrumentsTable.updateTable(data); },
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		},
		complete: function(){
			this.lastEditTimeout = -1;
		}
	});
}


