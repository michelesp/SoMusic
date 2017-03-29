function Renderer(canvasId, scoreDivId, vmCanvasId) {
	this.canvas = document.getElementById(canvasId);
	this.VFRenderer = new Vex.Flow.Renderer(this.canvas, Vex.Flow.Renderer.Backends.CANVAS);
	this.ctx = this.VFRenderer.getContext();
	this.selectedNotes = [];
	this.measures = [];
	this.ties = [];
}

Renderer.prototype.init = function (instrumentsUsed, totNScores, keySign, reload) {
	this.timeSign = getRadioSelected("time");
	this.beatNum = this.timeSign.split("/")[0];
	this.beatValue = this.timeSign.split("/")[1];
	this.keySign = keySign.options[keySign.selectedIndex].text;
	this.instrumentsUsed = instrumentsUsed;
	this.totNScores = totNScores;
	this.measures.push(new Measure(0, this.ctx, this.beatNum, this.beatValue, this.keySign, this.timeSign, instrumentsUsed));
	this.renderMeasures();
	var r = this;
	this.canvas.addEventListener("click", function(e) {r.processClick(e);}, false);
	document.getElementById("del").addEventListener("click", function (e) {
		r.delNotes(e);
	}, false);
	document.getElementById("tie").addEventListener("click", function (e) {
		r.tie(e);
	}, false);
	if(typeof reload == "boolean" && !reload)
		this.ajaxRequest("http://127.0.0.1/%7Emichele/SoMusic/somusic/Editor/initEditor", {
			instrumentsUsed: instrumentsUsed,
			timeSign: r.timeSign,
			keySign: r.keySign
		});
	else this.ajaxRequest("http://127.0.0.1/%7Emichele/SoMusic/somusic/Editor/getComposition", {id:reload});
}

//renders all the measures
Renderer.prototype.renderMeasures = function () {
	var size = 0;
	for (var i = 0; i < this.measures.length; i++) {
		this.measures[i].computeScale();
		size += this.measures[i].width;
	}
	this.VFRenderer.resize(size + 1500, (this.totNScores*80>250?this.totNScores*83:250));
	this.VFRenderer.getContext().clear();
	this.measures[0].render(100);
	for (var i = 1; i < this.measures.length; i++) 
		this.measures[i].render(this.measures[i - 1].getEndX());
	this.measures[this.measures.length-1].renderEndLine();
}

Renderer.prototype.processClick = function (e) {
	var rect = this.canvas.getBoundingClientRect();
	var x = e.clientX - rect.left;
	var y = e.clientY - rect.top;
	var i = this.getMeasureIndex(x);
	var found = false; //set to true if a note is clicked
	var staveIndex = this.measures[0].getStaveIndex(y);
	var measureIndex = this.getMeasureIndex(x);
	var voice = this.measures[measureIndex].voicesName[staveIndex];
	var notes = this.measures[measureIndex].notesArr[voice];
	var noteIndex = this.getNoteIndex(x, notes);
	var pitch = this.calculatePitch(e);
	var found = false;
	var note = notes[noteIndex];
	if(note.noteType=="n"){
		for(var j=0; j<note.keys.length; j++) {
			if(pitch==note.keys[j]) {
				found = true;
				var isSelected = false;
				for(var k=0; k<this.selectedNotes.length && !isSelected; k++) {
					var sn = this.selectedNotes[k];
					if(sn.note==note) {
						this.selectedNotes.splice(k, 1);
						note.setStyle({fillStyle: "balck"});
						isSelected = true;
					}
				}
				if(!isSelected) {
					this.selectedNotes.push({"note": note, "voiceName": voice, "index": staveIndex, "measureIndex": measureIndex, "noteIndex": noteIndex});
					note.setStyle({fillStyle: "red"});
				}
			}
		}
	}
	if(!found)
		this.addNote(e, staveIndex, measureIndex, noteIndex);
	this.renderAndDraw();
}

Renderer.prototype.getNoteIndex = function (x, notes) {
	var index = 0;
	var difIndex = Math.abs(notes[index].getAbsoluteX()-x);
	for(var i=1; i<notes.length; i++) {
		var dif = Math.abs(notes[i].getAbsoluteX()-x);
		if(dif<difIndex) {
			index = i;
			difIndex = dif;
		}
	}
	return index;
}

//return the index of the measure clicked
Renderer.prototype.getMeasureIndex = function (x) {
	for (var i = 0; i < this.measures.length; i++)
		if (x >= this.measures[i].staves[0].getX() && x <= this.measures[i].staves[0].getNoteEndX())
			return i;
}

//delete the selected notes
Renderer.prototype.delNotes = function (e) {
	if(this.selectedNotes.length == 0)
		this.shakeScore('No note selected');
	var r = this;
	var toRemove = [];
	for(var i=0; i<this.selectedNotes.length; i++) 
		toRemove.push({
			staveIndex: this.selectedNotes[i].index,
			measureIndex: this.selectedNotes[i].measureIndex,
			noteIndex: this.selectedNotes[i].noteIndex
		});
	this.ajaxRequest("http://127.0.0.1/%7Emichele/SoMusic/somusic/Editor/deleteNotes", {"toRemove":toRemove});
}

Renderer.prototype.tie = function (e) {
	if(this.selectedNotes.length==0) {
		this.shakeScore('Tie error');
		return;
	}
	var r = this;
	var toTie = [];
	for(var i=0; i<this.selectedNotes.length; i++) 
		toTie.push({
			voiceName: this.selectedNotes[i].voiceName,
			staveIndex: this.selectedNotes[i].index,
			measureIndex: this.selectedNotes[i].measureIndex,
			noteIndex: this.selectedNotes[i].noteIndex
		});
	console.log(this.selectedNotes);
	this.ajaxRequest("http://127.0.0.1/%7Emichele/SoMusic/somusic/Editor/addTie", {"toTie":toTie});
}

//TODO pass x and y from processClick
//add the note to the stave
Renderer.prototype.addNote = function (e, staveIndex, measureIndex, noteIndex) {
	var duration = getRadioSelected("notes");
	var accidental = getRadioSelected("accidental");
	var pitch = this.calculatePitch(e);
	var noteLength = {"w":1, "h":2, "q":4, "8":8, "16":16, "32":32, "64":64, "wr":1, "hr":2, "qr":4, "8r":8, "16r":16, "32r":32, "64r":64};
	this.ajaxRequest("http://127.0.0.1/%7Emichele/SoMusic/somusic/Editor/addNote", {
		staveIndex: staveIndex,
		measureIndex: measureIndex,
		noteIndex: noteIndex,
		newNote: pitch,
		duration: noteLength[duration],
		isPause: duration.indexOf("r")>=0
	});
}

Renderer.prototype.updateComposition = function(data) {
	console.log(data);
	var instrumentsScore = data.instrumentsScore;
	this.measures = [];
	this.ties = [];
	this.selectedNotes = [];
	for(var i=0; i<instrumentsScore[0].measures.length; i++) {
		var m = new Measure(i, this.ctx, this.beatNum, this.beatValue, this.keySign, this.timeSign, this.instrumentsUsed);
		for(var j=0; j<instrumentsScore.length; j++) {
			var m1 = instrumentsScore[j].measures[i];
			var voice = m1.voices[0];
			for(var k=0; k<voice.length; k++) {
				var note = voice[k];
				var keys = [];
				for(var l=0; l<note.step.length; l++)
					keys[l] = note.step[l]+"/"+note.octave[l];
				if(note.step.length==0) {
					if(m1.clef=="treble")
						keys[0]= "b/4";
					else if(m1.clef=="bass")
						keys[0] = "d/3";
					else if(m1.clef=="alto")
						keys[0] = "c/4";
				}
				m.addNote(new Vex.Flow.StaveNote({clef: m1.clef, keys: keys, duration: (64/note.duration)+(note.isRest?"r":"") }), instrumentsScore[j].name, k);
			}
		}
		this.measures.push(m);
	}
	for(var i=0; i<instrumentsScore.length; i++) {
		var instrumentScore = instrumentsScore[i];
		for(var j=0; j<instrumentScore.ties.length; j++) {
			var tie = instrumentScore.ties[j];
			this.ties.push([new Vex.Flow.StaveTie({
				first_note: this.measures[tie.firstMeasure].notesArr[instrumentScore.name][tie.firstNote],
				last_note: this.measures[tie.lastMeasure].notesArr[instrumentScore.name][tie.lastNote]
			}), instrumentScore.name, tie.firstMeasure, tie.firstNote, tie.lastMeasure, tie.lastNote]);
		}
	}
	this.renderAndDraw();
}

Renderer.prototype.renderAndDraw = function () {
	this.ctx.clear();
	this.renderMeasures();
	for (var i = 0; i < this.measures.length; i++) 
		this.measures[i].drawNotes();
	var r = this;
	this.ties.forEach(function (t) {
		t[0].setContext(r.ctx).draw()
	});
}

Renderer.prototype.getYFromClickEvent = function (e) {
	var rect = this.canvas.getBoundingClientRect();
	var y = e.clientY - rect.top;
	y = y.toFixed();
	var diff = y % 5;
	if (diff <= 2)
		y = y - diff;
	else
		y = y * 1 + (5 - diff);
	return y;
}

//calculate the pitch based on the mouse y position
Renderer.prototype.calculatePitch = function (e) {
	var y = this.getYFromClickEvent(e);
	return this.getNote(y, this.measures[0].staves[this.measures[0].getStaveIndex(y)]);
}

Renderer.prototype.getNote = function (y, stave) {
	var octave;
	var note;
	var bottom;
	/*var diff = y % 5;
    if (diff <= 2)
        y = y - diff;
    else
        y += Number((5 - diff));*/
	if (stave.clef == "treble") {
		bottom = stave.getBottomLineY() + 15;
		note = 4; //c is 0, b is 6
		octave = 3;
	}
	else if (stave.clef == "bass") {
		bottom = stave.getBottomLineY();
		note = 2; //c is 0, b is 6
		octave = 2;
	}
	else if (stave.clef == "alto") {
		bottom = stave.getBottomLineY() + 15;
		note = 5; //c is 0, b is 6
		octave = 2;
	}
	for (i = bottom; i >= bottom - 80; i -= 5) {
		if (i == y)
			break;
		if (note == 6) {
			note = 0;
			octave++;
		}
		else
			note++;
	}
	var notes = {0: 'c', 1: 'd', 2: 'e', 3: 'f', 4: 'g', 5: 'a', 6: 'b'};
	return notes[note] + '/' + octave;
}


Renderer.prototype.saveData = function () {
	var data = new EditorData(this.keySign, this.timeSign, this.instrumentsUsed);
	for (var i in this.ties) {
		var t = this.ties[i];
		data.ties.push(new TieData(t[1], t[2], t[3], t[4], t[5]));
	}
	for (var i in this.measures) {
		var measure = new MeasureData(i, this.measures[0].voicesName);
		for (var voiceName in this.measures[i].notesArr) {
			for (var j in this.measures[i].notesArr[voiceName]) {
				var note = this.measures[i].notesArr[voiceName][j];
				var accidental = null;
				if (note.modifiers.length > 0)
					accidental = note.modifiers[0].type;
				var noteData = new NoteData(note.duration, note.isRest() == undefined ? false : true, note.keys, accidental);
				measure.notesArr[voiceName].push(noteData);
			}
		}
		for (var k in this.measures[i].ties) {
			var t = this.measures[i].ties[k];
			measure.ties.push(new TieData(t[1], t[2], t[3], t[4], t[5]));
		}
		data.measures.push(measure);
	}
	return data;
}

Renderer.prototype.restoreData = function (data, instruments, isUsed) {
	this.instrumentsUsed = [];
	if(!isUsed) {
		this.instrumentsUsed.push({
			labelName: data.instrumentsScore[0].name.split("#score")[0],
			name: data.instrumentsScore[0].instrument,
			braces: instruments[data.instrumentsScore[0].instrument]["braces"],
			scoresClef: instruments[data.instrumentsScore[0].instrument]["scoresClef"]
		});
		for(var i=1; i<data.instrumentsScore.length; i++) {
			var label = data.instrumentsScore[i].name.split("#score")[0];
			if(label != this.instrumentsUsed[this.instrumentsUsed.length-1].labelName)
				this.instrumentsUsed.push({
					labelName: label,
					name: data.instrumentsScore[i].instrument,
					braces: instruments[data.instrumentsScore[i].instrument]["braces"],
					scoresClef: instruments[data.instrumentsScore[i].instrument]["scoresClef"]
				});
		}
	}
	else this.instrumentsUsed = instruments;
	this.keySign = data.instrumentsScore[0].measures[0].keySignature;
	this.timeSign = data.instrumentsScore[0].measures[0].timeSignature;
	this.beatNum = this.timeSign.split("/")[0];
	this.beatValue = this.timeSign.split("/")[1];
	this.updateComposition(data);
}

Renderer.prototype.shakeScore = function(err){
	var sc = document.getElementById('score');
	sc.classList.remove("animated");
	sc.classList.remove("shake");
	void sc.offsetWidth;
	sc.classList.add("animated");
	sc.classList.add("shake");
	sweetAlert('Oops...', err, 'error');
}

Renderer.prototype.ajaxRequest = function(url, data) {
	var r = this;
	$.ajax({
		type: 'post',
		url: url,
		data: data,
		dataType: 'JSON',
		success: function(data) { r.updateComposition(data); },
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		},
		complete: function(){
		}
	});
}

//return the radio element selected with the given name
function getRadioSelected(name) {
	var elements = document.getElementsByName(name);
	for (var i = 0; i < elements.length; i++) {
		if (elements[i].checked)
			return elements[i].id;
	}
}

