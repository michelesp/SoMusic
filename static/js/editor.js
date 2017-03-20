function Renderer(canvasId, scoreDivId, vmCanvasId) {
	this.canvas = document.getElementById(canvasId);
	//this.scoreDiv = document.getElementById(scoreDivId);
	//this.vmCanvas = document.getElementById(vmCanvasId);
	this.VFRenderer = new Vex.Flow.Renderer(this.canvas, Vex.Flow.Renderer.Backends.CANVAS);
	this.ctx = this.VFRenderer.getContext();
	this.selectedNotes = [];
	this.measures = []; //save
	//this.vmRenderer = new vmRenderer(this.measures, this.canvas, document.getElementById(vmCanvasId));
	//save
	this.ties = []; //array of ties that connect notes belonging to different staves
	//save $("#ks :selected").text() too
	//this.connection = new FireBaseConnection();
	//this.user;
	//this.size;
}

Renderer.prototype.init = function (instrumentsUsed, totNScores, keySign) {
	this.timeSign = getRadioSelected("time"); //save
	this.beatNum = this.timeSign.split("/")[0];
	this.beatValue = this.timeSign.split("/")[1];
	this.keySign = keySign.options[keySign.selectedIndex].text;
	this.instrumentsUsed = instrumentsUsed;
	this.totNScores = totNScores;
	this.measures.push(new Measure(0, this.ctx, this.beatNum, this.beatValue, this.keySign, this.timeSign, instrumentsUsed));
	this.measures.push(new Measure(1, this.ctx, this.beatNum, this.beatValue, this.keySign, this.timeSign, instrumentsUsed));
	this.measures.push(new Measure(2, this.ctx, this.beatNum, this.beatValue, this.keySign, this.timeSign, instrumentsUsed));
	this.measures.push(new Measure(3, this.ctx, this.beatNum, this.beatValue, this.keySign, this.timeSign, instrumentsUsed));
	this.renderMeasures();
	//this.vmRenderer.update(); //notify the observers that the measures array has changed
	var r = this;
	this.canvas.addEventListener("click", function(e) {r.processClick(e);}, false);
	document.getElementById("del").addEventListener("click", function (e) {
		r.delNotes(e);
	}, false);
	document.getElementById("tie").addEventListener("click", function (e) {
		r.tie(e);
	}, false);
	/*document.getElementById("visualMelody").addEventListener("click", function(e) {
        this.vmResize(e);
    }, false);*/
}

//renders all the measures
Renderer.prototype.renderMeasures = function () {
	var size = 0;
	for (var i = 0; i < this.measures.length; i++)
		size += this.measures[i].width;
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
	if (this.measures[i].isEmpty())
		this.addNote(e);
	else {
		loop:
			for (var voiceName in this.measures[i].voices) {
				for (var note in this.measures[i].voices[voiceName].getTickables()) {
					if (this.measures[i].voices[voiceName].getTickables()[note] instanceof Vex.Flow.StaveNote &&
							this.isSelected(this.measures[i].voices[voiceName].getTickables()[note], x, y, voiceName)) {
						found = true; //the user clicked on a note
						var foundNote = this.measures[i].voices[voiceName].getTickables()[note];
						for (var n in this.selectedNotes) {
							if (foundNote == this.selectedNotes[n]["note"]) {
								//if the note was already selected, color it black and
								//remove from the selected notes array
								this.colorNote(foundNote, i, voiceName, "black");
								this.selectedNotes.splice(Number(n), 1);
								break loop;
							}
						}
						//if the note was not selected color it red and add it to the selected notes array
						this.selectedNotes.push({"note": foundNote, "voiceName": voiceName, "index": i});
						this.colorNote(foundNote, i, voiceName, "red");
						break loop;
					}
				}
			}
	//if the user didn't click on a note, add a new one
	if (!found)
		this.addNote(e);
	}
}

//color the note red
//index = the measure index
Renderer.prototype.colorNote = function (note, index, voiceName, color) {
	for (var n in this.measures[index].notesArr[voiceName]) {
		if (this.measures[index].notesArr[voiceName][n] == note) {
			//note.setStyle({strokeStyle: color, stemStyle: color, fillStyle: color});
			note.setStyle({fillStyle: color});
			this.measures[index].notesArr[voiceName][n] = note;
			this.renderAndDraw();
			break;
		}
	}
}

//check if the mouse has clicked the given note
Renderer.prototype.isSelected = function isSelected(note, x, y, voiceName) {
	var bb = note.getBoundingBox();
	var offset = 0;
	if (this.measures[0].voicesName.indexOf(voiceName)>=0) //if the stem is up the height must be lowered by 30
		if (note.duration != "w" && !note.isRest())
			offset = 30;
		else if (note.isRest() && note.duration == "q")
			offset = 10;
		else if (note.isRest() && note.duration == "h")
			offset = -10;
		else if (note.isRest() && note.duration == "16")
			offset = 5;
	if (x >= bb.getX() && x <= bb.getX() + bb.getW())
		if (y >= bb.getY() + offset && y <= bb.getY() + 10 + offset)
			return true;
	return false;
}

//return the index of the measure clicked
Renderer.prototype.getMeasureIndex = function (x) {
	for (var i = 0; i < this.measures.length; i++)
		if (x >= this.measures[i].staves[0].getX() && x <= this.measures[i].staves[0].getNoteEndX())
			return i;
}

//return the index of the new note
Renderer.prototype.calcNoteIndex = function (index, voiceName, x) {
	var notes = this.measures[index].voices[voiceName].getTickables();
	var tmp = [];
	for (var i in notes)
		if (notes[i] instanceof Vex.Flow.StaveNote)
			tmp.push(notes[i]);
	for (var i = 0; i < tmp.length; i++) {
		if (x < tmp[i].getBoundingBox().getX())
			return i;
	}
	return i++;
}

//delete the selected notes
Renderer.prototype.delNotes = function (e) {
	if(this.selectedNotes.length == 0)
		this.shakeScore('No note selected');
	for (var i in this.selectedNotes) {
		var notes = this.measures[this.selectedNotes[i]["index"]].notesArr[this.selectedNotes[i]["voiceName"]];
		for (var j in notes)
			if (notes[j] == this.selectedNotes[i]["note"]){
				for(var k in this.ties) {
					if(this.ties[k].first_note==notes[j]){
						if(this.ties[k].last_note==notes[j+1])
							this.ties.splice(k, 1);
						else this.ties[k].setNotes({
							first_note: notes[parseInt(j)+1],
							last_note: this.ties[k].last_note
						});
					}
					if(this.ties[k].last_note==notes[j]){
						if(this.ties[k].first_note==notes[j-1])
							this.ties.splice(k, 1);
						else this.ties[k].setNotes({
							first_note: this.ties[k].first_note,
							last_note: notes[j-1]
						});
					}
				}
				notes.splice(Number(j), 1);
				j--;
			}
		this.measures[this.selectedNotes[i]["index"]].minNote = 1; //reset the min note to resize the measure properly
	}
	var toUpdate = [];
	for (var k in this.selectedNotes)
		if (!(toUpdate.includes(this.selectedNotes[k]["index"])))
			toUpdate.push(this.selectedNotes[k]["index"]);
	for (var i in toUpdate)
		this.measures[toUpdate[i]].updateTiesIndex();
//	after deleting empty the selectedNotes array
	this.selectedNotes.splice(0, this.selectedNotes.length)
	this.renderAndDraw();
}

Renderer.prototype.tie = function (e) {
	if(this.selectedNotes.length==0) {
		this.shakeScore('Tie error');
		return;
	}
	var voice = this.selectedNotes[0]["voiceName"];
	for(var i=1; i<this.selectedNotes.length; i++){
		if(this.selectedNotes[i]["voiceName"] != voice) {
			this.shakeScore('Tie error');
			return;
		}
	}
	var firstIndex = Number.POSITIVE_INFINITY, lastIndex = Number.NEGATIVE_INFINITY;
	var firstNote, lastNote, firstNotePos, lastNotePos;
	for(var i=0; i<this.selectedNotes.length; i++) {
		if(this.selectedNotes[i]["index"]<firstIndex) {
			firstIndex = this.selectedNotes[i]["index"];
			firstNote = this.selectedNotes[i]["note"];
			firstNotePos = this.measures[firstIndex].getNoteIndex(voice, firstNote);
		}
		else if(this.selectedNotes[i]["index"]==firstIndex) {
			var pos = this.measures[firstIndex].getNoteIndex(voice, this.selectedNotes[i]["note"]);
			if(pos<firstNotePos) {
				firstNote = this.selectedNotes[i]["note"];
				firstNotePos = pos;
			}
		}
		if(this.selectedNotes[i]["index"]>lastIndex) {
			lastIndex = this.selectedNotes[i]["index"];
			lastNote = this.selectedNotes[i]["note"];
			lastNotePos = this.measures[lastIndex].getNoteIndex(voice, lastNote);
		}
		else if(this.selectedNotes[i]["index"]==lastIndex) {
			var pos = this.measures[lastIndex].getNoteIndex(voice, this.selectedNotes[i]["note"]);
			if(pos>lastNotePos) {
				lastNote = this.selectedNotes[i]["note"];
				lastNotePos = pos;
			}
		}
	}
	if (!(this.areTied(firstNote, lastNote))[0])		//if the notes aren't tied yet
		this.ties.push([new Vex.Flow.StaveTie({
			first_note: firstNote,
			last_note: lastNote
		}), this.selectedNotes[0]["voiceName"], firstIndex, firstNotePos, lastIndex, lastNotePos]);
	else { //otherwise remove the tie
		var index = this.areTied(firstNote, lastNote)[1];
		this.ties.splice(index, 1);
	}
	for(var i=0; i<this.selectedNotes.length; i++)
		this.colorNote(this.selectedNotes[i]["note"], this.selectedNotes[i]["index"], voice, "black");
	this.selectedNotes = [];
	this.renderAndDraw();
}

//the sameMeasure variable is set to true when firstNote and secondNote belong to the same measure
//return an array containing a boolean value and the index of the tie inside the ties array, if the tie exists.
Renderer.prototype.areTied = function (firstNote, lastNote) {
	for (var i in this.ties)
		if (this.ties[i][0].first_note == firstNote && this.ties[i][0].last_note == lastNote)
			return [true, i];
	return [false, null];
}

//TODO pass x and y from processClick
//add the note to the stave
Renderer.prototype.addNote = function (e) {
	var duration = getRadioSelected("notes");
	var accidental = getRadioSelected("accidental");
	var pitch = this.calculatePitch(e);
	var staveIndex = this.measures[0].getStaveIndex(this.getYFromClickEvent(e));
	var newNote = new Vex.Flow.StaveNote({clef: this.measures[0].staves[staveIndex].clef, keys: [pitch], duration: duration});
	if (accidental != "clear" && !newNote.isRest())
		newNote.addAccidental(0, new Vex.Flow.Accidental(accidental));
	var i = this.getMeasureIndex(e.clientX - this.canvas.getBoundingClientRect().left);
	var voice = this.measures[i].voicesName[staveIndex];
	if (this.measures[i].isEmpty()) {
		var message = this.measures[i].addNote(newNote, this.measures[i].getVoiceName(staveIndex), 0);
		if (message == 'err')
			this.shakeScore('Measure duration exceeded!');
	}
	else {
		var pos = this.calcNoteIndex(i, voice, e.clientX - this.canvas.getBoundingClientRect().left);
		if(this.measures[i].addNote(newNote, voice, pos) == 'err')
			this.shakeScore('Measure duration exceeded!');
	}
	//add new measures
	if (i >= this.measures.length - 2)
		this.measures.push(new Measure(i + 2, this.ctx, this.beatNum, this.beatValue, this.keySign, this.timeSign, this.instrumentsUsed));
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
	//this.vmRenderer.update(); //notify the observers that the measures array has changed
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

Renderer.prototype.restoreData = function (data) {
	this.timeSign = data["timeSign"];
	this.beatNum = this.timeSign.split("/")[0];
	this.beatValue = this.timeSign.split("/")[1];
	this.keySign = data["keySign"];
	this.measures.splice(0, this.measures.length);
	this.ties.splice(0, this.ties.length);
	this.selectedNotes.splice(0, this.selectedNotes.length);
	this.instrumentsUsed = data.instrumentsUsed;
	this.totNScores = 0;
	for(var i=0; i<this.instrumentsUsed.length; i++)
		this.totNScores += this.instrumentsUsed[i].scoresClef.length;
	for (var i in data["measures"]) {
		var measure = data["measures"][i];
		this.measures.push(new Measure(measure["index"], this.ctx, this.beatNum, this.beatValue, this.keySign, this.timeSign, data["instrumentsUsed"]));
		for(var j=0; j<data["instrumentsUsed"].length; j++) {
			var inst = data["instrumentsUsed"][j];
			for(var k=0; k<inst.scoresClef.length; k++) {
				var voiceName = inst.labelName+"#score"+k;
				for (var l in measure["notesArr"][voiceName]) {
					var note = measure["notesArr"][voiceName][l];
					var vexNote, duration = note["duration"];
					vexNote = new Vex.Flow.StaveNote({clef: inst.scoresClef[k], keys: [note["keys"][0]], duration: duration});
					if (note["accidental"] != undefined)
						vexNote.addAccidental(0, new Vex.Flow.Accidental(note["accidental"]));
					this.measures[i].addNote(vexNote, voiceName, l);
					this.measures[i].computeScale();
				}
			}
		}
	}
	if (data["ties"] != undefined) {
		for (var i in data["ties"]) {
			var tie = data["ties"][i];
			this.ties.push([new Vex.Flow.StaveTie({
				first_note: this.measures[tie["firstIndex"]].notesArr[tie["voiceName"]][tie["firstNote"]],
				last_note: this.measures[tie["lastIndex"]].notesArr[tie["voiceName"]][tie["lastNote"]]
			}), tie["voiceName"], tie["firstIndex"], tie["firstNote"], tie["lastIndex"], tie["lastNote"]
			]);
		}
	}
	this.renderAndDraw();
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

//return the radio element selected with the given name
function getRadioSelected(name) {
	var elements = document.getElementsByName(name);
	for (var i = 0; i < elements.length; i++) {
		if (elements[i].checked)
			return elements[i].id;
	}
}
