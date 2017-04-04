
function Preview(floatBox, timeSignature, keySignature, scoreDiv, nextButton, instrumentsTable) {
	this.floatBox = floatBox;
	this.timeSignature = timeSignature;
	this.keySignature = keySignature;
	this.scoreDiv = scoreDiv;
	this.nextButton = nextButton;
	this.instrumentsTable = instrumentsTable;
	this.renderer = new Vex.Flow.Renderer(scoreDiv, Vex.Flow.Renderer.Backends.SVG);
	this.instrumentsTable.toUpdate.push(this);
	var preview = this;
	this.timeSignature.addEventListener("change", function() { preview.update(); }, false);
	this.keySignature.addEventListener("change", function() { preview.update(); }, false);
	this.nextButton.addEventListener("click", function() { preview.commitPreview(); }, false);
}

Preview.prototype.update = function() {
	var ctx = this.renderer.getContext();
	var timeSignature = this.timeSignature.value;
	var keySignature = this.keySignature.value;
	var instrumentsUsed = this.instrumentsTable.instrumentsUsed;
	var totNScores = this.instrumentsTable.totNScores;
	var staves = [];
	var braces = [];
	var lines = [];
	this.renderer.resize(400, (totNScores*70>400?totNScores*75:400));
	ctx.clear();
	for(var i=0, j=0; i<instrumentsUsed.length; i++) {
		var inst = instrumentsUsed[i];
		var start = j*70+130;
		var end = start;
		for(var k=0; k<inst.scoresClef.length; k++){
			end = j*70;
			staves.push(new Vex.Flow.Stave(100, end, 200));
			staves[j].addClef(inst.scoresClef[k]).addTimeSignature(timeSignature).addKeySignature(keySignature).setContext(ctx).draw();
			j++;
			if(j>1){
				lines.push(new Vex.Flow.StaveConnector(staves[j-2], staves[j-1]).setType(1))
				lines.push(new Vex.Flow.StaveConnector(staves[j-2], staves[j-1]).setType(6))
			}
		}
		for(var k=0; k<inst.braces.length; k++)
			braces.push(new Vex.Flow.StaveConnector(staves[j-inst.scoresClef.length+inst.braces[k][0]], staves[j-inst.scoresClef.length+inst.braces[k][1]]).setType(3));
		var y = (start+end)/2;
		ctx.fillText(instrumentsUsed[i].labelName,10,y);
	}
	for(var i=0; i<braces.length; i++)
		braces[i].setContext(ctx).draw();
	for(var i=0; i<lines.length; i++)
		lines[i].setContext(ctx).draw();
}

Preview.prototype.commitPreview = function() {
	this.floatBox.close();
	SoMusic.floatBox = OW.ajaxFloatBox('SOMUSIC_CMP_Editor',
			{"timeSignature": this.timeSignature.value, "keySignature": this.keySignature.value, "instrumentsUsed": this.instrumentsTable.instrumentsUsed},
			{top:'calc(5vh)', width:'calc(80vw)', height:'calc(85vh)', iconClass: 'ow_ic_add', title: ''});
}
