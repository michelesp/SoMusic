//index represents the position of the measure inside the stave
function Measure(index, ctx, beatNum, beatValue, keySign, timeSign, instrumentsUsed) {
	this.index = index;
	this.notesArr = [];
	this.staves = [];
	this.beatNum = beatNum;
	this.beatValue = beatValue;
	this.keySign = keySign;
	this.timeSign = timeSign;
	this.instrumentsUsed = instrumentsUsed;
	/*setMode(3) allows to insert notes inside the measure even if the measure is not complete, but
     throws an exception if the duration of the inserted notes exceeds the time signature*/
	/*this.voices = {
        "basso": new Vex.Flow.Voice({
            num_beats: this.beatNum, beat_value: this.beatValue,
            resolution: Vex.Flow.RESOLUTION
        }).setMode(3),
        "tenore": new Vex.Flow.Voice({
            num_beats: this.beatNum, beat_value: this.beatValue,
            resolution: Vex.Flow.RESOLUTION
        }).setMode(3),
        "alto": new Vex.Flow.Voice({
            num_beats: this.beatNum, beat_value: this.beatValue,
            resolution: Vex.Flow.RESOLUTION
        }).setMode(3),
        "soprano": new Vex.Flow.Voice({
            num_beats: this.beatNum, beat_value: this.beatValue,
            resolution: Vex.Flow.RESOLUTION
        }).setMode(3)
    };*/
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
	console.log(this.voices);
	//array of ties inside the measure
	this.ctx = ctx;
	this.ties = [];
	this.formatter = new Vex.Flow.Formatter();
	this.minNote = 1; //1 is w, 2 is h, 3 is q, 4 is 8, 5 is 16
	this.width;
	this.computeScale();
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

Measure.prototype.getIndex = function () {
	return this.index;
}

Measure.prototype.getVoiceName = function (scoreIndex) {
	console.log("scoreIndex");
	console.log(scoreIndex);
	console.log(this.voicesName);
	return this.voicesName[scoreIndex];
}

/*adds a note in the measure
 in case adding the note generates an error (the new inserted note exceeds the time signature),
 the voice is restored to the previous state*/
Measure.prototype.addNote = function (note, voiceName, index) {
	console.log(this.notesArr);
	console.log(voiceName);
	console.log(this.notesArr[voiceName]);
	this.notesArr[voiceName].splice(index, 0, note);
	var toReturn = 'success';
	try {
		if (voiceName == "basso" || voiceName == "alto")
			note.setStemDirection(-1);
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
Measure.prototype.render = function (x) {
	this.computeScale();
	var instrumentsUsed = getInstrumentsUsed();
	var k=0;
	this.staves = [];
	var braces = [];
	var lines = [];
	for(var i=0; i<instrumentsUsed.instruments.length; i++){
		var inst = instrumentsUsed.instruments[i];
		console.log(inst);
		var start = k*80+130;
		var end = start;
		for(var j=0; j<inst.scoresClef.length; j++, k++) {
			end = k*80;
			var stave = new Vex.Flow.Stave(x, end, this.width);
			if(this.index==0)
				stave.addClef(inst.scoresClef[j]).addTimeSignature(this.timeSign).addKeySignature(this.keySign);
			this.staves.push(stave);
		}
		if(this.index==0){
			this.ctx.fillText(inst.labelName, 10, (start+end)/2);
			for(var j=0; j<inst.braces.length; j++)
				braces.push(new Vex.Flow.StaveConnector(this.staves[k-inst.scoresClef.length+inst.braces[j][0]], this.staves[k-inst.scoresClef.length+inst.braces[j][1]]).setType(3));
		}
	}
	// for(var i=0; i<instrumentsUsed.totNScores; i++)
	//	this.scores.push(new Vex.Flow.Stave(x, i*70, this.width).addClef(instruments[]).addTimeSignature(this.timeSign).addKeySignature(this.keySign));
	/*this.trebleStave = new Vex.Flow.Stave(x, 20, this.width);
    this.bassStave = new Vex.Flow.Stave(x, this.trebleStave.getBottomLineY() + 10, this.width);
    if (this.index == 0) {
    	this.trebleStave.addClef("treble").addTimeSignature(this.timeSign);
        this.bassStave.addClef("bass").addTimeSignature(this.timeSign);
        this.trebleStave.addKeySignature(this.keySign);
        this.bassStave.addKeySignature(this.keySign);
        this.bassStave.setNoteStartX(this.trebleStave.getNoteStartX());
        this.bassStave.setWidth(this.bassStave.getNoteStartX()
            - this.bassStave.getX() + this.width);
        this.trebleStave.setWidth(this.trebleStave.getNoteStartX() - this.trebleStave.getX() + this.width);
    }
    this.trebleStave.setContext(this.ctx).draw();
    this.bassStave.setContext(this.ctx).draw();*/
	this.staves[0].setContext(this.ctx).draw();
	for(var i=1; i< this.staves.length; i++) {
		this.staves[i].setContext(this.ctx).draw();
		lines.push(new Vex.Flow.StaveConnector(this.staves[i-1], this.staves[i]).setType(1));
		//lines.push(new Vex.Flow.StaveConnector(this.staves[i-1], this.staves[i]).setType(6));
	}
	for(var i=0; i<braces.length; i++)
		braces[i].setContext(this.ctx).draw();
	for(var i=0; i<lines.length; i++)
		lines[i].setContext(this.ctx).draw();
}

Measure.prototype.renderEndLine = function () {
	for(var i=1; i<this.staves.length; i++) 
		new Vex.Flow.StaveConnector(this.staves[i-1], this.staves[i]).setType(6).setContext(this.ctx).draw();
}

//calculate the width of the stave based on the note with the minimum duration
Measure.prototype.computeScale = function () {
	this.restoreVoices();
	var notes = {"w": 1, "h": 2, "q": 4, "8": 8, "16": 16, "wr": 1, "hr": 2, "qr": 4, "8r": 8, "16r": 16};
	for (var voiceName in this.notesArr) {
		for (var i = 0; i < this.notesArr[voiceName].length; i++) {
			var noteDuration = this.notesArr[voiceName][i].duration;
			if (notes[noteDuration] > this.minNote)
				this.minNote = notes[noteDuration];
		}
	}
	this.width = 85 * this.minNote;
}

//check if the given voice is full or not
Measure.prototype.isComplete = function (voiceName) {
	/*this.restoreVoices();
     return this.voices[voiceName].isComplete();*/
	for (var i in this.voices[voiceName].getTickables())
		if (this.voices[voiceName].getTickables()[i] instanceof Vex.Flow.GhostNote)
			return false;
	return this.voices[voiceName].isComplete();
}

//check if the note is the first of the stave(used for tiesBetweenMeasures)
Measure.prototype.isFirstNote = function (voiceName, note) {
	var cont = 0;
	for (var i in this.notesArr[voiceName]) {
		if (this.notesArr[voiceName][i] == note)
			return cont == 0;
		cont++;
	}
}

//check if the note is the last of the stave (used for tiesBetweenMeasures)
Measure.prototype.isLastNote = function (voiceName, note) {
	var cont = 0;
	for (var i in this.notesArr[voiceName]) {
		cont++;
		if (this.notesArr[voiceName][i] == note)
			return cont == this.notesArr[voiceName].length;
	}
}

Measure.prototype.getEndX = function () {
	//return this.trebleStave.getX() + this.trebleStave.getWidth();
	return this.staves[0].getX() + this.staves[0].getWidth();
}

//draw the notes on the staves
Measure.prototype.drawNotes = function () {
	this.completeVoices();
	var toFormat = [];
	for (var voice in this.voices)
		toFormat.push(this.voices[voice]);
	this.formatter.format(toFormat, this.width);
	for (var voice in this.voices) {
		/*if (voice == "basso" || voice == "tenore")
            this.voices[voice].draw(this.ctx, this.bassStave);
        else
            this.voices[voice].draw(this.ctx, this.trebleStave);*/
		this.voices[voice].draw(this.ctx, this.getStaveToDraw(voice));
	}
}

Measure.prototype.getStaveToDraw = function (voice) {
	var str = voice.split("#score");
	for(var i=0; i<this.instrumentsUsed.length; i++) {
		var instrument = this.instrumentsUsed[i];
		if(str[0]==instrument.labelName)
			return this.staves[i+parseInt(str[1])];
	}
}

//Renderer the ties inside the measure
Measure.prototype.renderTies = function () {
	for (var i = 0; i < this.ties.length; i++) {
		var hasFirst = false;
		var hasLast = false;
		var cont = 0;
		loop:
			for (var voiceName in this.notesArr) {
				for (var j in this.notesArr[voiceName]) {
					if (hasFirst)
						cont++;
					if (this.notesArr[voiceName][j] === this.ties[i][0].first_note)
						hasFirst = true;
					if (this.notesArr[voiceName][j] === this.ties[i][0].last_note) {
						hasLast = true;
						if (!hasFirst || cont > 1) {
							this.ties.splice(Number(i), 1);
							i--;
						}
						break loop;
					}
				}
			}
		if (!hasLast || !hasFirst) {
			this.ties.splice(Number(i), 1);
			i--;
		}
	}
	var ctx = this.ctx;
	this.ties.forEach(function (t) {
		t[0].setContext(ctx).draw()
	})
}

Measure.prototype.getStaveIndex = function (height) {		//individuare posizione spartito
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

Measure.prototype.getStaveBottom = function (stave) {
	switch (stave) {
	case "bass":
		return this.bassStave.getBottomLineY();
	case "treble":
		return this.trebleStave.getBottomLineY();
	}
}

Measure.prototype.getWidth = function () {
	return this.trebleStave.getWidth();
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

//check if the measure is empty
Measure.prototype.isEmpty = function () {
	for (var voiceName in this.notesArr)
		if (this.notesArr[voiceName].length > 0)
			return false;
	return true;
}

Measure.prototype.updateTiesIndex = function () {
	for (var i in this.ties) {
		loop:
			for (var voiceName in this.notesArr) {
				for (var j in this.notesArr[voiceName]) {
					if (this.ties[i][0].first_note == this.notesArr[voiceName][j]) {
						this.ties[i][2] = j;
						break loop;
					}
				}
			}
	}
}
