
function Editor(notesInput, restsInput, accidentalsInput, canvas, addButton, composition, noteColor,
		deteleNotesURL, addTieURL, addNoteURL,getCompositionURL, accidentalUpdateURL, 
		closeURL, removeInstrumentURL, exportURL, dotsUpdateURL, moveNotesURL,
		setNoteAnnotationTextURL, changeNoteDurationURL) {
	var editor = this;
	this.canvas = canvas;
	this.noteColor = noteColor;
	this.deleteNotesURL = deteleNotesURL;
	this.addTieURL = addTieURL;
	this.addNoteURL = addNoteURL;
	this.getCompositionURL = getCompositionURL;
	this.accidentalUpdateURL = accidentalUpdateURL;
	this.closeURL = closeURL;
	this.removeInstrumentURL = removeInstrumentURL;
	this.exportURL = exportURL;
	this.dotsUpdateURL = dotsUpdateURL;
	this.moveNotesURL = moveNotesURL;
	this.setNoteAnnotationTextURL = setNoteAnnotationTextURL;
	this.changeNoteDurationURL = changeNoteDurationURL;
	this.lastUpdate = Date.now();
	this.selectedNotes = [];
	this.voiceIndex = 0;
	this.interval = setInterval(() => {
		if(Date.now()>editor.lastUpdate-5000 && this.selectedNotes.length==0)
			editor.ajaxRequest(editor.getCompositionURL, {});
	}, 10000);
	notesInput.forEach(function(element, index){
		element.addEventListener("click", function(){
			var rest = document.querySelector("input[name='rests']:checked");
			if(rest!=null)
				rest.checked = false;
			if(editor.selectedNotes.length>0)
				editor.changeNoteDuration(element.value);
		});
	});
	restsInput.forEach(function(element, index){
		element.addEventListener("click", function(){
			var note = document.querySelector("input[name='notes']:checked");
			if(note!=null)
				note.checked = false;
			if(editor.selectedNotes.length>0)
				editor.changeNoteDuration(element.value);
		});
	});
	accidentalsInput.forEach(function(element, index){
		element.addEventListener("click", function(){
			editor.accidentalUpdate(this.value);
		});
	});
	/*
	 * voiceButton.forEach(function(element, index){
	 * element.addEventListener("click", function(){
	 * editor.changeVoice(this.value); }); });
	 */
	this.renderer = new Renderer(this.canvas, composition.instrumentsUsed);
	this.canvas.addEventListener("click", function(e) {editor.processClick(e);}, false);
	document.getElementById("delete").parentNode.addEventListener("click", function (e) {
		editor.delNotes(e);
	}, false);
	document.getElementById("tie").parentNode.addEventListener("click", function (e) {
		editor.tie(e);
	}, false);
	document.getElementById("dot").parentNode.addEventListener("click", function (e) {
		editor.dotsUpdate(1);
	}, false);
	document.getElementById("double-dot").parentNode.addEventListener("click", function (e) {
		editor.dotsUpdate(2);
	}, false);
	addButton.addEventListener("click", function() {
		SoMusic.save(editor.renderer.composition);
		var fb = SoMusic.floatBox.pop();
		fb.floatBox.close();
	}, false);
	notesInput[2].click();
	accidentalsInput[0].click();
	// voiceButton[0].click();
	window.onkeyup = function(e) {
	    var key = e.keyCode ? e.keyCode : e.which;
	    if(key == 37)
	    	editor.changeNoteSelection(-1);
	    else if(key == 38)
	    	editor.moveNotes(1);
	    else if(key == 39)
	    	editor.changeNoteSelection(1);
	    else if (key == 40)
	    	editor.moveNotes(-1);
	    else if((key>=65 && key<=90) || key==32 || key==13)
	    	editor.addAnnotationLetter(e.key);
	}
	this.renderer.updateComposition(composition);
}


Editor.prototype.processClick = function (e) {
	var rect = this.canvas.getBoundingClientRect();
	var x = e.clientX - rect.left;
	var y = e.clientY - rect.top;
	var i = this.getMeasureIndex(x);
	var found = false; // set to true if a note is clicked
	var staveIndex = this.renderer.measures[0].getStaveIndex(y);
	var measureIndex = this.getMeasureIndex(x);
	var instrumentName = this.renderer.measures[measureIndex].instrumentsName[staveIndex];
	var notes = this.renderer.measures[measureIndex].notes[instrumentName][this.voiceIndex];
	var noteIndex = this.getNoteIndex(x, notes);
	var pitch = this.calculatePitch(e);
	var found = false;
	var note = notes[noteIndex];
	if(note.noteType=="n"){
		if(typeof SoMusic.assignmentManager!=="undefined" && SoMusic.assignmentManager.isAdmin==0 && note.note_heads[0].style.fillStyle=="red")
			return;
		for(var j=0; j<note.keys.length; j++) {
			if(pitch==note.keys[j]) {
				found = true;
				var isSelected = false;
				for(var k=0; k<this.selectedNotes.length && !isSelected; k++) {
					var sn = this.selectedNotes[k];
					if(sn.note==note) {
						this.selectedNotes.splice(k, 1);
						note.setStyle({fillStyle: (typeof note.oldStyle!=="undefined"?note.oldStyle:"black")});
						isSelected = true;
					}
				}
				if(!isSelected) {
					this.selectedNotes.push({"note": note,
						"voiceName": instrumentName,
						"index": staveIndex,
						"measureIndex": measureIndex,
						"noteIndex": noteIndex});
					note.oldStyle = note.note_heads[0].style.fillStyle;
					note.setStyle({fillStyle: "blue"});
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

// return the index of the measure clicked
Editor.prototype.getMeasureIndex = function (x) {
	for (var i = 0; i < this.renderer.measures.length; i++)
		if (x >= this.renderer.measures[i].staves[0].getX() && x <= this.renderer.measures[i].staves[0].getNoteEndX())
			return i;
}

// delete the selected notes
Editor.prototype.delNotes = function (e) {
	if(this.selectedNotes.length == 0)
		this.shakeScore('No note selected');
	var toRemove = [];
	for(var i=0; i<this.selectedNotes.length; i++) 
		toRemove.push({
			staveIndex: this.selectedNotes[i].index,
			measureIndex: this.selectedNotes[i].measureIndex,
			noteIndex: this.selectedNotes[i].noteIndex
		});
	this.ajaxRequest(this.deleteNotesURL, {"toRemove":toRemove});
}

Editor.prototype.tie = function (e) {
	if(this.selectedNotes.length==0) {
		this.shakeScore('Tie error');
		return;
	}
	var toTie = [];
	for(var i=0; i<this.selectedNotes.length; i++) 
		toTie.push({
			voiceName: this.selectedNotes[i].voiceName,
			staveIndex: this.selectedNotes[i].index,
			measureIndex: this.selectedNotes[i].measureIndex,
			noteIndex: this.selectedNotes[i].noteIndex
		});
	this.ajaxRequest(this.addTieURL, {"toTie":toTie});
}

// TODO pass x and y from processClick
// add the note to the stave
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
		accidental: document.querySelector("input[name='accidentals']:checked").value,
		color: this.noteColor
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

// calculate the pitch based on the mouse y position
Editor.prototype.calculatePitch = function (e) {
	var y = this.getYFromClickEvent(e);
	return this.getNote(y, this.renderer.measures[0].staves[this.renderer.measures[0].getStaveIndex(y)]);
}

Editor.prototype.getNote = function (y, stave) {
	var octave;
	var note;
	var bottom;
	/*
	 * var diff = y % 5; if (diff <= 2) y = y - diff; else y += Number((5 -
	 * diff));
	 */
	if (stave.clef == "treble") {
		bottom = stave.getBottomLineY() + 15;
		note = 4; // c is 0, b is 6
		octave = 3;
	}
	else if (stave.clef == "bass") {
		bottom = stave.getBottomLineY();
		note = 2; // c is 0, b is 6
		octave = 2;
	}
	else if (stave.clef == "alto") {
		bottom = stave.getBottomLineY() + 15;
		note = 5; // c is 0, b is 6
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
	if(this.selectedNotes.length==0)
		return;
	var toUpdate = [];
	for(var i=0; i<this.selectedNotes.length; i++) 
		toUpdate.push({
			staveIndex: this.selectedNotes[i].index,
			measureIndex: this.selectedNotes[i].measureIndex,
			noteIndex: this.selectedNotes[i].noteIndex
		});
	this.ajaxRequest(this.accidentalUpdateURL, {"toUpdate":toUpdate, "accidental":type});
}

Editor.prototype.dotsUpdate = function (value) {
	if(this.selectedNotes.length==0)
		return;
	var toUpdate = [];
	for(var i=0; i<this.selectedNotes.length; i++) 
		toUpdate.push({
			staveIndex: this.selectedNotes[i].index,
			measureIndex: this.selectedNotes[i].measureIndex,
			noteIndex: this.selectedNotes[i].noteIndex
		});
	this.ajaxRequest(this.dotsUpdateURL, {"toUpdate":toUpdate, "dotValue":value});
}

Editor.prototype.close = function() {
	clearInterval(this.interval);
	this.ajaxRequest(this.closeURL, {});
}

Editor.prototype.update = function() {
	this.ajaxRequest(this.getCompositionURL, {});
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

Editor.prototype.moveNotes = function(value) {
	if(this.selectedNotes.length==0)
		return;
	var editor = this;
	var toUpdate = [];
	var selected = this.selectedNotes;
	for(var i=0; i<this.selectedNotes.length; i++) 
		toUpdate.push({
			staveIndex: this.selectedNotes[i].index,
			measureIndex: this.selectedNotes[i].measureIndex,
			noteIndex: this.selectedNotes[i].noteIndex
		});
	this.ajaxRequest(this.moveNotesURL, {"toUpdate":toUpdate, "value":value}, function(data) {
		editor.lastUpdate=Date.now();
		editor.renderer.updateComposition(data);
		selected.forEach(function(item, index, array){
			editor.selectedNotes.push(item);
			note = editor.renderer.measures[item.measureIndex].notes[item.voiceName][0][item.noteIndex];
			//note.oldStyle = note.note_heads[0].style.fillStyle;
			note.oldStyle = editor.noteColor;
			note.setStyle({fillStyle: "blue"});
		});
		editor.renderer.renderAndDraw();
		console.log(selected);
	});
}


Editor.prototype.changeNoteSelection = function(value) {
	var editor = this;
	var selectedNotes = [];
	this.selectedNotes.forEach(function(item, index){
		var notes = editor.renderer.measures[item.measureIndex].notes[item.voiceName][0];
		notes[item.noteIndex].setStyle({fillStyle: (typeof notes[item.noteIndex].oldStyle!=="undefined"?notes[item.noteIndex].oldStyle:"black")});
		var notePos = item;
		do {
			notePos = editor.getCloseNote(notePos, value);
		}while(notePos.measureIndex>=0 && notePos.measureIndex<editor.renderer.measures.length && notePos.noteIndex>=0 && 
				notePos.noteIndex<=editor.renderer.measures[notePos.measureIndex].notes[item.voiceName][0].length && editor.renderer.measures[notePos.measureIndex].notes[item.voiceName][0][notePos.noteIndex].note_heads[0].style.fillStyle=="red");
		if(notePos.measureIndex>=0 && notePos.measureIndex<editor.renderer.measures.length && 
				notePos.noteIndex>=0 && notePos.noteIndex<=editor.renderer.measures[notePos.measureIndex].notes[item.voiceName][0].length) {
			selectedNotes.push({
				note: editor.renderer.measures[notePos.measureIndex].notes[item.voiceName][0][notePos.noteIndex],
				voiceName: item.voiceName,
				index: item.index,
				measureIndex: notePos.measureIndex,
				noteIndex: notePos.noteIndex
			});
		}
	});
	this.selectedNotes = selectedNotes;
	this.selectedNotes.forEach(function(item, index){
		item.note.oldStyle = item.note.note_heads[0].style.fillStyle;
		item.note.setStyle({fillStyle: "blue"});
	});
	editor.renderer.renderAndDraw();
}

Editor.prototype.addAnnotationLetter = function(letter) {
	if(this.selectedNotes.length!=1)
		return;
	var instrument = this.renderer.composition.instrumentsScore[this.selectedNotes[0].index].instrument;
	console.log(instrument);
	if(instrument!="4_voices" && instrument!="singer_voice")
		return;
	console.log(instrument);
	var editor = this;
	var notes = this.renderer.measures[this.selectedNotes[0].measureIndex].notes[this.selectedNotes[0].voiceName][this.voiceIndex];
	var note = notes[this.selectedNotes[0].noteIndex];
	var text = "";
	note.modifiers.forEach(function(item, index){
		if(typeof item.text !== "undefined") {
			text += item.text;
			note.modifiers.splice(index, 1);
		}
	});
	if(letter=="Backspace")
		text = text.substring(0, text.length-1);
	else if(letter==" " || letter=="Enter") {
		var selectedNotes = this.selectedNotes;
		selectedNotes[0].note.setStyle({fillStyle: (typeof selectedNotes[0].note.oldStyle!=="undefined"?selectedNotes[0].note.oldStyle:"black")});
		this.ajaxRequest(this.setNoteAnnotationTextURL, {
			"measureIndex": editor.selectedNotes[0].measureIndex,
			"staveIndex": editor.selectedNotes[0].index,
			"noteIndex": editor.selectedNotes[0].noteIndex,
			"text": text
		}, function (data){
			console.log(data);
		});
		if(letter==" ") {
			var closeNote = this.getCloseNote(selectedNotes[0], 1);
			if(closeNote.measureIndex>=0 && closeNote.measureIndex<editor.renderer.measures.length && 
					closeNote.noteIndex>=0 && closeNote.noteIndex<=editor.renderer.measures[closeNote.measureIndex].notes[closeNote.voiceName][this.voiceIndex].length) {
				editor.selectedNotes = [closeNote];
				closeNote.note.oldStyle = closeNote.note.note_heads[0].style.fillStyle;
				closeNote.note.setStyle({fillStyle: "blue"});
			}
		}
	}
	else text += letter;
	note.addModifier(0, new Vex.Flow.Annotation(text).setVerticalJustification(Vex.Flow.Annotation.VerticalJustify.TOP));
	this.renderer.renderAndDraw();
}

Editor.prototype.getCloseNote = function(note, steps) {
	var note1, measureIndex, noteIndex, j, n = steps;
	for(var i=note.measureIndex; 
			(steps>0 && n>0 && i<this.renderer.measures.length) || (steps<0 && n<0 && i>=0);
			(steps>0?i++:i--)) {
		if(i==note.measureIndex)
			j = note.noteIndex+steps;
		else (steps>0 ? j=0 : j=this.renderer.measures[i].notes[note.voiceName][this.voiceIndex].length-1);
		for(; (steps>0 && n>0 && j<this.renderer.measures[i].notes[note.voiceName][this.voiceIndex].length) 
				|| (steps<0 && n<0 && j>=0); (steps>0?j++:j--)) {
			note1 = this.renderer.measures[i].notes[note.voiceName][this.voiceIndex][j];
			if(note1.noteType=="n") {
				(steps>0 ? n-- : n++);
				if(n==0){
					measureIndex = i;
					noteIndex = j;
				}
			}
		}
	}
	return {
		note: note1,
		measureIndex: measureIndex,
		index: note.index,
		noteIndex: noteIndex,
		voiceName: note.voiceName
	};
}

Editor.prototype.changeNoteDuration = function(duration) {
	var toChange = [];
	for(var i=0; i<this.selectedNotes.length; i++) 
		toChange.push({
			voiceName: this.selectedNotes[i].voiceName,
			staveIndex: this.selectedNotes[i].index,
			measureIndex: this.selectedNotes[i].measureIndex,
			noteIndex: this.selectedNotes[i].noteIndex
		});
	this.ajaxRequest(this.changeNoteDurationURL, {"toChange":toChange, "duration":duration});
}

Editor.prototype.changeVoice = function(value) {
	this.voiceIndex = voice;
	this.ajaxRequest(this.changeVoiceURL, {"voice":voice});
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

Editor.prototype.ajaxRequest = function(url, data, func=null) {
	var editor = this;
	this.selectedNotes = [];
	$.ajax({
		type: 'post',
		url: url,
		data: data,
		dataType: 'JSON',
		success: (func != null ? func : function(data) {
			if(typeof data.error!=="undefined")
				OW.error(data.error);
			editor.lastUpdate=Date.now();
			try {
				editor.renderer.updateComposition(data);
			} catch(err) {
				location.reload();
			}
		})
	});
}

