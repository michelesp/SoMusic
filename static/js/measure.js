function Measure(index, beatNum, beatValue, keySign, instrumentsUsed) {
	this.index = index;
	this.notes = [];
	this.staves = [];
	this.beatNum = beatNum;
	this.beatValue = beatValue;
	this.keySign = keySign;
	this.instrumentsUsed = instrumentsUsed;
	this.instrumentsName = this.getInstrumentsName(this.instrumentsUsed);
	this.instruments = [];
	for(var i=0; i<this.instrumentsName.length; i++) {
		this.notes[this.instrumentsName[i]] = [[]];
		this.instruments[this.instrumentsName[i]]=[
			new Vex.Flow.Voice({
				num_beats: this.beatNum, beat_value: this.beatValue,
				resolution: Vex.Flow.RESOLUTION
			})
		];
		this.instruments[this.instrumentsName[i]][0].setMode(3);
		/*setMode(3) allows to insert notes inside the measure even if the measure is not complete, but
	     throws an exception if the duration of the inserted notes exceeds the time signature*/
	}
	this.width;
	this.computeScale();
}

Measure.prototype.getInstrumentsName = function(instrumentsUsed) {
	var toReturn = [];
	for(var i=0; i<instrumentsUsed.length; i++) {
		var instrument = instrumentsUsed[i];
		var label = instrument.labelName;
		for(var j=0; j<instrument.scoresClef.length; j++)
			toReturn.push(label+"#score"+j);
	}
	return toReturn;
}

/*adds a note in the measure
 in case adding the note generates an error (the new inserted note exceeds the time signature),
 the voice is restored to the previous state*/
Measure.prototype.addNote = function (note, instrumentName, index, nVoice) {
	while(this.notes[instrumentName].length<=nVoice) {
		this.notes[instrumentName].push([]);
		this.instruments[instrumentName].push(new Vex.Flow.Voice({
				num_beats: this.beatNum, beat_value: this.beatValue,
				resolution: Vex.Flow.RESOLUTION}));
	}
	this.notes[instrumentName][nVoice].splice(index, 0, note);
	try {
		this.instruments[instrumentName][nVoice] = new Vex.Flow.Voice({
			num_beats: this.beatNum, beat_value: this.beatValue,
			resolution: Vex.Flow.RESOLUTION
		}).setMode(3);
		this.instruments[instrumentName][nVoice].addTickables(this.notes[instrumentName][nVoice]);
	}
	catch (err) {
		console.log(err);
		this.notes[instrumentName].splice(index, 1);
		this.instruments[instrumentName] = new Vex.Flow.Voice({
			num_beats: this.beatNum, beat_value: this.beatValue,
			resolution: Vex.Flow.RESOLUTION
		}).setMode(3);
		this.instruments[instrumentName].addTickables(this.notes[instrumentName]);
	}
}

//Renderer the measure. the x param is the start of the previous measure
Measure.prototype.render = function (ctx, x) {
	this.computeScale();
	var k=0;
	this.staves = [];
	var braces = [];
	var lines = [];
	for(var i=0; i<this.instrumentsUsed.length; i++){
		var inst = this.instrumentsUsed[i];
		var start = k*80+130;
		var end = start;
		for(var j=0; j<inst.scoresClef.length; j++, k++) {
			end = k*80;
			var stave = new Vex.Flow.Stave(x, end, this.width);
			if(this.index==0)
				stave.addClef(inst.scoresClef[j])
						.addTimeSignature(this.beatNum+"/"+this.beatValue)
						.addKeySignature(this.keySign);
			this.staves.push(stave);
		}
		if(this.index==0){
			ctx.fillText(inst.labelName, 10, (start+end)/2);
			if(typeof inst.braces !=="undefined")
				for(var j=0; j<inst.braces.length; j++)
					braces.push(new Vex.Flow.StaveConnector(this.staves[k-inst.scoresClef.length+parseInt(inst.braces[j][0])],
							this.staves[k-inst.scoresClef.length+parseInt(inst.braces[j][1])]).setType(3));
		}
	}
	this.staves[0].setContext(ctx).draw();
	for(var i=1; i< this.staves.length; i++) {
		this.staves[i].setContext(ctx).draw();
		lines.push(new Vex.Flow.StaveConnector(this.staves[i-1], this.staves[i]).setType(1));
	}
	for(var i=0; i<braces.length; i++)
		braces[i].setContext(ctx).draw();
	for(var i=0; i<lines.length; i++)
		lines[i].setContext(ctx).draw();
}

Measure.prototype.renderEndLine = function (ctx) {
	new Vex.Flow.StaveConnector(this.staves[0], this.staves[0]).setType(6).setContext(ctx).draw();
	for(var i=1; i<this.staves.length; i++) 
		new Vex.Flow.StaveConnector(this.staves[i-1], this.staves[i]).setType(6).setContext(ctx).draw();
}

Measure.prototype.computeScale = function () {
	var widths = [];
	for (var instrumentName in this.notes)
		widths[instrumentName] = 70;
	for (var instrumentName in this.notes) {
		for (var i=0; i<this.notes[instrumentName].length; i++) {
			var width = 0;
			for(var j=0; j<this.notes[instrumentName][i].length; j++) {
				var noteDuration = this.notes[instrumentName][i][j].duration;
				if(isNaN(noteDuration.charAt(noteDuration.length-1)))
					noteDuration = parseInt(noteDuration.substring(0, noteDuration.length-1));
				else noteDuration = parseInt(noteDuration);
				//width += (noteDuration>8?noteDuration:2*noteDuration);
				width += (noteDuration<16?noteDuration:noteDuration/2);
				var noteModifiers = this.notes[instrumentName][i][j].modifiers;
				for(var k=0; k<noteModifiers.length; k++)
					width += noteModifiers[k].width;
			}
			if(widths[instrumentName]<(width+70))
				widths[instrumentName] = width+70;
		}
	}
	this.width = 80;
	for (var instrumentName in this.notes)
		if(this.width<widths[instrumentName])
			this.width = widths[instrumentName];
	if(this.index==0)
		this.width += 60+this.getArmatureAlterations()*20;
}

Measure.prototype.getEndX = function () {
	return this.staves[0].getX() + this.staves[0].getWidth();
}

Measure.prototype.drawNotes = function (ctx) {
	this.computeScale();
	for (var instrumentName in this.instruments) {
		for(var i=0; i<this.instruments[instrumentName].length; i++) {
			var fillStyles = [];
			for(var j=0; j<this.instruments[instrumentName][i].tickables.length; j++) {
				var note = this.instruments[instrumentName][i].tickables[j];
				fillStyles[j] = [];
				for(var k=0; k<note.note_heads.length; k++)
					fillStyles[j][k] = (typeof note.note_heads[k].style!=="undefined"?note.note_heads[k].style:"");
			}
			var beams = Vex.Flow.Beam.generateBeams(this.instruments[instrumentName][i].tickables);
			for(var j=0; j<this.instruments[instrumentName][i].tickables.length; j++) {
				for(var k=0; k<this.instruments[instrumentName][i].tickables[j].note_heads.length; k++)
					this.instruments[instrumentName][i].tickables[j].note_heads[k].style = fillStyles[j][k];
			}
		}
		//Vex.Flow.Formatter.FormatAndDraw(ctx,  this.getStaveToDraw(instrumentName), this.instruments[instrumentName][0].tickables);
		var width = this.width;
		if(this.index==0)
			width-=(60+this.getArmatureAlterations()*20);
		width -= 20;
		var formatter = new Vex.Flow.Formatter().joinVoices(this.instruments[instrumentName]).format(this.instruments[instrumentName], width);
		var measure = this;
		this.instruments[instrumentName].forEach(function(v) { v.draw(ctx, measure.getStaveToDraw(instrumentName)); })
		
		beams.forEach(function(b) { b.setContext(ctx).draw(); });
	}
}

Measure.prototype.getStaveToDraw = function (instrumentName) {
	var str = instrumentName.split("#score");
	var n = 0;
	for(var i=0; i<this.instrumentsUsed.length; i++) {
		var instrument = this.instrumentsUsed[i];
		if(str[0]==instrument.labelName)
			return this.staves[n+parseInt(str[1])];
		n += instrument.scoresClef.length;
	}
}

Measure.prototype.getStaveIndex = function (height) {
	var scoreClose = -1;
	var scoreCloseDist = Number.POSITIVE_INFINITY;
	for(var i=0; i<this.staves.length; i++){
		var stave = this.staves[i];
		var dist = Math.abs(stave.getYForLine(2)-height);
		if(dist<scoreCloseDist) {
			scoreCloseDist = dist;
			scoreClose = i;
		}
	}
	return scoreClose;
}

Measure.prototype.getArmatureAlterations = function () {
	switch(this.keySign) {
	case "C":
	case "Am":
		return 0;
	case "G":
	case "Em":
	case "F":
	case "Dm":
		return 1;
	case "D":
	case "Bm":
	case "Bb":
	case "Gm":
		return 2;
	case "A":
	case "F#m":
	case "Eb":
	case "Cm":
		return 3;
	case "E":
	case "C#m":
	case "Ab":
	case "Fm":
		return 4;
	case "B":
	case "G#m":
	case "Db":
	case "Bbm":
		return 5;
	case "F#":
	case "D#m":
	case "Gb":
	case "Ebm":
		return 6;
	case "C#":
	case "A#m":
	case "Cb":
	case "Abm":
		return 7;
	}
}


