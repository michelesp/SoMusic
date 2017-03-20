function Preview(keySig, scoreDiv) {
	this.keySig = keySig;
	Object.keys(Vex.Flow.keySignature.keySpecs).forEach(function (key) {
		var option = document.createElement("option");
		option.text = key;
		keySig.add(option);
	});
	this.renderer = new Vex.Flow.Renderer(scoreDiv, Vex.Flow.Renderer.Backends.SVG);
	this.tab = document.getElementById('tabInstrumentsBody');
	this.timeSign = getRadioSelected("time");
	this.update();
}

Preview.prototype.update = function(e) {
	var ctx = this.renderer.getContext();
	var keySign = this.keySig.options[this.keySig.selectedIndex].text;
	var instrumentsUsed = this.getInstrumentsUsed();
	var instruments = instrumentsUsed.instruments;
	var totNScores= instrumentsUsed.totNScores;
	var staves = [];
	var braces = [];
	var lines = [];
	if(typeof e != "undefined")
		this.timeSign = e.target.children[0].id;
	this.renderer.resize(400, (totNScores*70>400?totNScores*75:400));
	ctx.clear();
	for(var i=0, j=0; i<instruments.length; i++) {
		var inst = instruments[i];
		var start = j*70+130;
		var end = start;
		for(var k=0; k<inst.scoresClef.length; k++){
			end = j*70;
			staves.push(new Vex.Flow.Stave(100, end, 200));
			staves[j].addClef(inst.scoresClef[k]).addTimeSignature(this.timeSign).addKeySignature(keySign).setContext(ctx).draw();
			j++;
			if(j>1){
				lines.push(new Vex.Flow.StaveConnector(staves[j-2], staves[j-1]).setType(1))
				lines.push(new Vex.Flow.StaveConnector(staves[j-2], staves[j-1]).setType(6))
			}
		}
		for(var k=0; k<inst.braces.length; k++)
			braces.push(new Vex.Flow.StaveConnector(staves[j-inst.scoresClef.length+inst.braces[k][0]], staves[j-inst.scoresClef.length+inst.braces[k][1]]).setType(3));
		var y = (start+end)/2;
		ctx.fillText(instruments[i].labelName,10,y);
	}
	for(var i=0; i<braces.length; i++)
		braces[i].setContext(ctx).draw();
	for(var i=0; i<lines.length; i++)
		lines[i].setContext(ctx).draw();
}

Preview.prototype.getInstrumentsUsed = function() {
	var instrumentsUsed = [];
	var totNScores = 0;
	for(var i=0; i<this.tab.children.length; i++) {
		var tr = this.tab.children[i];
		var labelName=null, name=null;
		for(var j=0; j<tr.children.length; j++) {
			var td = tr.children[j];
			for(var k=0; k<td.children.length; k++){
				if(td.children[k].nodeName=="INPUT")
					labelName=td.children[k].value;
				if(td.children[k].nodeName=="SELECT")
					name=td.children[k].value;
			}
		}
		instrumentsUsed.push({"labelName":labelName, "name":name, "scoresClef":instruments[name]["scoresClef"], "braces":instruments[name]["braces"]});
		totNScores+=instrumentsUsed[instrumentsUsed.length-1].scoresClef.length;
	}
	return {instruments: instrumentsUsed, totNScores: totNScores };
}

Preview.prototype.updateInstrumentsLabel = function() {
	var toRename = [];
	var readed = [];
	for(var i=0; i<this.tab.children.length; i++) {
		var tr = this.tab.children[i];
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
	for(var i=0; i<this.tab.children.length; i++) {
		var tr = this.tab.children[i];
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

Preview.prototype.changeType = function(tr, value) {
	for (var i = 0; i < tr.children.length; i++) {
		var td = tr.children[i];
		for (var j = 0; j < td.childNodes.length; j++)
			if (td.childNodes[j].nodeName == "INPUT")
				td.childNodes[j].value = this.titleCase(value.replace("_", " "));
	}
	this.updateInstrumentsLabel();
	this.update();
}

Preview.prototype.deleteInstrument = function(e) {
	if(e.parentNode.children.length>1)
		e.parentNode.removeChild(e);
	this.updateInstrumentsLabel();
	this.update();
}

Preview.prototype.titleCase = function(str) {
	var splitStr = str.toLowerCase().split(' ');
	for (var i = 0; i < splitStr.length; i++) {
		splitStr[i] = splitStr[i].charAt(0).toUpperCase()
		+ splitStr[i].substring(1);
	}
	return splitStr.join(' ');
}


