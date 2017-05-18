
function Editor(notesInput, restsInput, accidentalsInput, canvas, addButton,
		composition, deteleNotesURL, addTieURL, addNoteURL,getCompositionURL,
		accidentalUpdateURL, closeURL, removeInstrumentURL, exportURL) {
	var editor = this;
	this.canvas = canvas;
	this.deleteNotesURL = deteleNotesURL;
	this.addTieURL = addTieURL;
	this.addNoteURL = addNoteURL;
	this.getCompositionURL = getCompositionURL;
	this.accidentalUpdateURL = accidentalUpdateURL;
	this.closeURL = closeURL;
	this.removeInstrumentURL = removeInstrumentURL;
	this.exportURL = exportURL;
	this.lastUpdate = Date.now();
	this.interval = setInterval(() => {
		if(Date.now()>editor.lastUpdate-5000 && this.renderer.selectedNotes.length==0)
			editor.ajaxRequest(editor.getCompositionURL, {}, false);
	}, 10000);
	notesInput.forEach(function(element, index){
		element.addEventListener("click", function(){
			var rest = document.querySelector("input[name='rests']:checked");
			if(rest!=null)
				rest.checked = false;
		});
	});
	restsInput.forEach(function(element, index){
		element.addEventListener("click", function(){
			var note = document.querySelector("input[name='notes']:checked");
			if(note!=null)
				note.checked = false;
		});
	});
	accidentalsInput.forEach(function(element, index){
		element.addEventListener("click", function(){
			editor.accidentalUpdate(this.value);
		});
	});
	this.renderer = new Renderer(this.canvas, composition.instrumentsUsed);
	this.canvas.addEventListener("click", function(e) {editor.processClick(e);}, false);
	document.getElementById("del").addEventListener("click", function (e) {
		editor.delNotes(e);
	}, false);
	document.getElementById("tie").parentNode.addEventListener("click", function (e) {
		editor.tie(e);
	}, false);
	addButton.addEventListener("click", function() {
		SoMusic.save(editor.renderer.composition);
		var fb = SoMusic.floatBox.pop();
		fb.floatBox.close();
	}, false);
	notesInput[2].click();
	accidentalsInput[0].click();
	this.renderer.updateComposition(composition);
}


Editor.prototype.processClick = function (e) {
	var rect = this.canvas.getBoundingClientRect();
	var x = e.clientX - rect.left;
	var y = e.clientY - rect.top;
	var i = this.getMeasureIndex(x);
	var found = false; //set to true if a note is clicked
	var staveIndex = this.renderer.measures[0].getStaveIndex(y);
	var measureIndex = this.getMeasureIndex(x);
	var voice = this.renderer.measures[measureIndex].voicesName[staveIndex];
	var notes = this.renderer.measures[measureIndex].notesArr[voice];
	var noteIndex = this.getNoteIndex(x, notes);
	var pitch = this.calculatePitch(e);
	var found = false;
	var note = notes[noteIndex];
	if(note.noteType=="n"){
		for(var j=0; j<note.keys.length; j++) {
			if(pitch==note.keys[j]) {
				found = true;
				var isSelected = false;
				for(var k=0; k<this.renderer.selectedNotes.length && !isSelected; k++) {
					var sn = this.renderer.selectedNotes[k];
					if(sn.note==note) {
						this.renderer.selectedNotes.splice(k, 1);
						note.setStyle({fillStyle: "balck"});
						isSelected = true;
					}
				}
				if(!isSelected) {
					this.renderer.selectedNotes.push({"note": note,
						"voiceName": voice,
						"index": staveIndex,
						"measureIndex": measureIndex,
						"noteIndex": noteIndex});
					note.setStyle({fillStyle: "red"});
				}
			}
		}
	}
	if(!found)
		this.addNote(e, staveIndex, measureIndex, noteIndex);
	this.renderer.renderAndDraw();
}

Editor.prototype.getNoteIndex = function (x, notes) {
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
Editor.prototype.getMeasureIndex = function (x) {
	for (var i = 0; i < this.renderer.measures.length; i++)
		if (x >= this.renderer.measures[i].staves[0].getX() && x <= this.renderer.measures[i].staves[0].getNoteEndX())
			return i;
}

//delete the selected notes
Editor.prototype.delNotes = function (e) {
	if(this.renderer.selectedNotes.length == 0)
		this.shakeScore('No note selected');
	var toRemove = [];
	for(var i=0; i<this.renderer.selectedNotes.length; i++) 
		toRemove.push({
			staveIndex: this.renderer.selectedNotes[i].index,
			measureIndex: this.renderer.selectedNotes[i].measureIndex,
			noteIndex: this.renderer.selectedNotes[i].noteIndex
		});
	this.ajaxRequest(this.deleteNotesURL, {"toRemove":toRemove}, true);
}

Editor.prototype.tie = function (e) {
	if(this.renderer.selectedNotes.length==0) {
		this.shakeScore('Tie error');
		return;
	}
	var toTie = [];
	for(var i=0; i<this.renderer.selectedNotes.length; i++) 
		toTie.push({
			voiceName: this.renderer.selectedNotes[i].voiceName,
			staveIndex: this.renderer.selectedNotes[i].index,
			measureIndex: this.renderer.selectedNotes[i].measureIndex,
			noteIndex: this.renderer.selectedNotes[i].noteIndex
		});
	this.ajaxRequest(this.addTieURL, {"toTie":toTie}, true);
}

//TODO pass x and y from processClick
//add the note to the stave
Editor.prototype.addNote = function (e, staveIndex, measureIndex, noteIndex) {
	var duration = -1;
	var note = document.querySelector("input[name='notes']:checked");
	if(note != null)
		duration = note.value;
	else duration = document.querySelector("input[name='rests']:checked").value;
	var accidental = document.querySelector("input[name='accidentals']:checked").value
	var pitch = this.calculatePitch(e);
	var noteLength = {"1":1, "2":2, "4":4, "8":8, "16":16, "32":32, "64":64,
			"1r":1, "2r":2, "4r":4, "8r":8, "16r":16, "32r":32, "64r":64};
	this.ajaxRequest(this.addNoteURL, {
		staveIndex: staveIndex,
		measureIndex: measureIndex,
		noteIndex: noteIndex,
		newNote: pitch,
		duration: noteLength[duration],
		isPause: duration.indexOf("r")>=0,
		accidental: document.querySelector("input[name='accidentals']:checked").value
	});
}

Editor.prototype.getYFromClickEvent = function (e) {
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
Editor.prototype.calculatePitch = function (e) {
	var y = this.getYFromClickEvent(e);
	return this.getNote(y, this.renderer.measures[0].staves[this.renderer.measures[0].getStaveIndex(y)]);
}

Editor.prototype.getNote = function (y, stave) {
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

Editor.prototype.accidentalUpdate = function (type) {
	if(this.renderer.selectedNotes.length==0)
		return;
	var toUpdate = [];
	for(var i=0; i<this.renderer.selectedNotes.length; i++) 
		toUpdate.push({
			staveIndex: this.renderer.selectedNotes[i].index,
			measureIndex: this.renderer.selectedNotes[i].measureIndex,
			noteIndex: this.renderer.selectedNotes[i].noteIndex
		});
	this.ajaxRequest(this.accidentalUpdateURL, {"toUpdate":toUpdate, "accidental":type}, true);
}

Editor.prototype.close = function() {
	clearInterval(this.interval);
	this.ajaxRequest(this.closeURL, {}, false);
}

Editor.prototype.update = function() {
	this.ajaxRequest(this.getCompositionURL, {}, false);
}

Editor.prototype.removeCompositionInstrument = function(row, index){
	var editor = this;
	$.ajax({
		type: 'post',
		url: this.removeInstrumentURL,
		data: {"index": index},
		dataType: 'JSON',
		success: function(data){
			console.log(data);
			if(data) {
				row.parentNode.removeChild(row);
				SoMusic.editor.update();
			}
		},
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		}
	});
}

Editor.prototype.exportMusicXML = function() {
	var a = document.createElement('a');
	a.setAttribute('download', 'music.xml');
	a.href = this.exportURL;
	a.style.display = 'none';
	document.body.appendChild(a);
	a.click();
}

Editor.prototype.shakeScore = function(err){
	var sc = document.getElementById('score');
	sc.classList.remove("animated");
	sc.classList.remove("shake");
	void sc.offsetWidth;
	sc.classList.add("animated");
	sc.classList.add("shake");
	sweetAlert('Oops...', err, 'error');
}

Editor.prototype.ajaxRequest = function(url, data, cleanVars) {
	var editor = this;
	$.ajax({
		type: 'post',
		url: url,
		data: data,
		dataType: 'JSON',
		success: function(data) { editor.lastUpdate=Date.now(); editor.renderer.updateComposition(data, cleanVars); },
		error: function( XMLHttpRequest, textStatus, errorThrown ){
			OW.error(textStatus);
		},
		complete: function(){
		}
	});
}

