
function Admin(addInstrumentURL, editInstrumentURL, removeInstrumentURL) {
	this.addInstrumentURL = addInstrumentURL;
	this.editInstrumentURL = editInstrumentURL;
	this.removeInstrumentURL = removeInstrumentURL;
}

Admin.prototype.addInstrument = function() {
	OW.ajaxFloatBox('SOMUSIC_CMP_MusicInstrument', {},
			{top:'calc(5vh)', width:'calc(50vw)', height:'calc(50vh)', iconClass: 'ow_ic_add', title: ''});
}

Admin.prototype.editInstrument = function(id) {
	OW.ajaxFloatBox('SOMUSIC_CMP_MusicInstrument', {"id":id},
			{top:'calc(5vh)', width:'calc(50vw)', height:'calc(50vh)', iconClass: 'ow_ic_add', title: ''});
}

Admin.prototype.removeInstrument = function(id) {
	var admin = this; 
	console.log(admin.removeInstrumentURL);
	$.ajax({
		type: 'post',
		url: admin.removeInstrumentURL,
		data: {"id": id},
		dataType: 'JSON',
		success: function(status) {
			if(status)
				setTimeout(function(){ location.reload(); }, 50);
		},
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		}
	});
}

Admin.prototype.removeClefToTable = function(tbody, tr) {
	if(tbody.children.length<=1)
		return;
	tbody.removeChild(tr);
	var index = 0;
	for(var i=1; i<tbody.children.length; i++) {
		tbody.children[i].children[1].children[0].value = index;
		index++;
	}
}

Admin.prototype.addClefToTable = function(tbody) {
	var lastChild = tbody.children[tbody.children.length-1];
	var newChild = lastChild.cloneNode(true);
	newChild.children[0].children[0].value = lastChild.children[0].children[0].value;
	newChild.children[1].children[0].value = lastChild.children[0].children[0].value+1;
	tbody.appendChild(newChild);
}

Admin.prototype.checkInstrumentGroup = function(otherDiv, value) {
	if(value==-1)
		otherDiv.style.display = "";
	else otherDiv.style.display = "none";
}

