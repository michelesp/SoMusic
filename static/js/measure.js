function Measure(index, beatNum, beatValue, keySign, instrumentsUsed) {
	this.index = index;
	this.notesArr = [];
	this.staves = [];
	this.beatNum = beatNum;
	this.beatValue = beatValue;
	this.keySign = keySign;
	this.instrumentsUsed = instrumentsUsed;
	/*setMode(3) allows to insert notes inside the measure even if the measure is not complete, but
     throws an exception if the duration of the inserted notes exceeds the time signature*/
	this.voicesName = this.getVoicesName(this.instrumentsUsed);
	this.voices = [];
	for(var i=0; i<this.voicesName.length; i++) {
		this.notesArr[this.voicesName[i]] = [];
		this.voices[this.voicesName[i]]=new Vex.Flow.Voice({
			num_beats: this.beatNum, beat_value: this.beatValue,
			resolution: Vex.Flow.RESOLUTION
		});
		this.voices[this.voicesName[i]].setMode(3);
	}
	//array of ties inside the measure
	this.minNote = 1; //1 is w, 2 is h, 3 is q, 4 is 8, 5 is 16
	this.width;
	this.computeScale();
}

Measure.prototype.getNoteIndex = function(voice, note) {
	var notesArr = this.notesArr[voice];
	for(var i=0; i<notesArr.length; i++)
		if(notesArr[i]==note)
			return i;
}

Measure.prototype.getVoicesName = function(instrumentsUsed) {
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
Measure.prototype.addNote = function (note, voiceName, index) {
	this.notesArr[voiceName].splice(index, 0, note);
	var toReturn = 'success';
	try {
		//if (voiceName == "basso" || voiceName == "alto")
		//	note.setStemDirection(-1);
		this.voices[voiceName] = new Vex.Flow.Voice({
			num_beats: this.beatNum, beat_value: this.beatValue,
			resolution: Vex.Flow.RESOLUTION
		}).setMode(3);
		this.voices[voiceName].addTickables(this.notesArr[voiceName]);
	}
	catch (err) {
		this.notesArr[voiceName].splice(index, 1);
		this.voices[voiceName] = new Vex.Flow.Voice({
			num_beats: this.beatNum, beat_value: this.beatValue,
			resolution: Vex.Flow.RESOLUTION
		}).setMode(3);
		this.voices[voiceName].addTickables(this.notesArr[voiceName]);
		toReturn = 'err';
	}
	finally {
		return toReturn;
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
				stave.addClef(inst.scoresClef[j]).addTimeSignature(this.beatNum+"/"+this.beatValue).addKeySignature(this.keySign);
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
	for(var i=1; i<this.staves.length; i++) 
		new Vex.Flow.StaveConnector(this.staves[i-1], this.staves[i]).setType(6).setContext(ctx).draw();
}

//calculate the width of the stave based on the note with the minimum duration
Measure.prototype.computeScale = function () {
	this.restoreVoices();
	var widths = [];
	for (var voiceName in this.notesArr)
		widths[voiceName] = 70;
	for (var voiceName in this.notesArr) {
		for (var i = 0; i < this.notesArr[voiceName].length; i++) {
			var noteDuration = this.notesArr[voiceName][i].duration;
			if(isNaN(noteDuration.charAt(noteDuration.length-1)))
				noteDuration = parseInt(noteDuration.substring(0, noteDuration.length-1));
			else noteDuration = parseInt(noteDuration);
			widths[voiceName] += 10*noteDuration;
			if (noteDuration > this.minNote)
				this.minNote = noteDuration;
		}
	}
	this.width = 70;
	for (var voiceName in this.notesArr)
		if(this.width<widths[voiceName])
			this.width = widths[voiceName];
	if(this.index==0)
		this.width+=40;
}

//check if the given voice is full or not
Measure.prototype.isComplete = function (voiceName) {
	for (var i in this.voices[voiceName].getTickables())
		if (this.voices[voiceName].getTickables()[i] instanceof Vex.Flow.GhostNote)
			return false;
}

Measure.prototype.getEndX = function () {
	return this.staves[0].getX() + this.staves[0].getWidth();
}

//draw the notes on the staves
Measure.prototype.drawNotes = function (ctx) {
	for (var voice in this.voices) {
		var fillStyles = [];
		for(var i=0; i<this.voices[voice].tickables.length; i++) {
			var note = this.voices[voice].tickables[i];
			fillStyles[i] = [];
			for(var j=0; j<note.note_heads.length; j++)
				fillStyles[i][j] = (typeof note.note_heads[j].style!=="undefined"?note.note_heads[j].style:"");
		}
		var beams = Vex.Flow.Beam.generateBeams(this.voices[voice].tickables);
		for(var i=0; i<this.voices[voice].tickables.length; i++) {
			for(var j=0; j<this.voices[voice].tickables[i].note_heads.length; j++)
				this.voices[voice].tickables[i].note_heads[j].style = fillStyles[i][j];
		}
		Vex.Flow.Formatter.FormatAndDraw(ctx,  this.getStaveToDraw(voice), this.voices[voice].tickables);
		beams.forEach(function(b) { b.setContext(ctx).draw(); });
		//this.voices[voice].draw(ctx, this.getStaveToDraw(voice));
	}
}

Measure.prototype.getStaveToDraw = function (voice) {
	var str = voice.split("#score");
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

//add ghostNotes to the voice until it's complete (allows proper formatting)
Measure.prototype.completeVoices = function () {
	for (var voice in this.voices)
		while (!this.voices[voice].isComplete())
			this.voices[voice].addTickable(new Vex.Flow.GhostNote({clef: "bass", keys: ["e/2"], duration: "16"}));
}

//remove ghostNotes from the voices
Measure.prototype.restoreVoices = function () {
	for (var voice in this.voices) {
		this.voices[voice] = new Vex.Flow.Voice({
			num_beats: this.beatNum, beat_value: this.beatValue,
			resolution: Vex.Flow.RESOLUTION
		}).setMode(3);
		this.voices[voice].addTickables(this.notesArr[voice]);
	}
}

