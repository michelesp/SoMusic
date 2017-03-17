
function deleteInstrument(e) {
	var nTR = 0;
	for (var i = 0; i < e.parentNode.childNodes.length; i++)
		if (e.parentNode.childNodes[i].nodeName == "TR")
			nTR++;
	if(nTR>1)
		e.parentNode.removeChild(e);
	updateInstrumentsLabel();
	VISUALMELODY.preview();
}

function changeType(tr, value) {
	for (var i = 0; i < tr.childNodes.length; i++) {
		if (tr.childNodes[i].nodeName == "TD") {
			var td = tr.childNodes[i];
			for (var j = 0; j < td.childNodes.length; j++) {
				if (td.childNodes[j].nodeName == "INPUT")
					td.childNodes[j].value = titleCase(value.replace("_",
					" "));
			}
		}
	}
	updateInstrumentsLabel();
	VISUALMELODY.preview();
}

function titleCase(str) {
	var splitStr = str.toLowerCase().split(' ');
	for (var i = 0; i < splitStr.length; i++) {
		splitStr[i] = splitStr[i].charAt(0).toUpperCase()
		+ splitStr[i].substring(1);
	}
	return splitStr.join(' ');
}

function updateInstrumentsLabel() {
	var tab = document.getElementById('tabInstrumentsBody');
	var toRename = [];
	var readed = [];
	for(var i=0; i<tab.children.length; i++) {
		var tr = tab.children[i];
		var value = tr.firstElementChild.firstElementChild.value
		var str = value.split(" ");
		console.log(str[str.length-1]);
		if(str.length>1 && !Number.isNaN(str[str.length-1])){
			str.pop();
			value = str.join(" ");
		}
		if(readed.indexOf(value)>=0) {
			if(typeof toRename[value] === "undefined")
				toRename[value] = 0;
		}
		else readed.push(value);
	}
	for(var i=0; i<tab.children.length; i++) {
		var tr = tab.children[i];
		var value = tr.firstElementChild.firstElementChild.value
		var str = value.split(" ");
		if(str.length>1 && !Number.isNaN(str[str.length-1])){
			str.pop();
			value = str.join(" ");
		}
		if(readed.indexOf(value)>=0) {
			if(typeof toRename[value] !== "undefined") {
				toRename[value]++;
				tr.firstElementChild.firstElementChild.value = value+" "+toRename[value];
			}
		}
		else readed.push(value);
	}
}


