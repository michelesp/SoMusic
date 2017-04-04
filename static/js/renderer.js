
function Renderer(canvas, instrumentsUsed) {
	var renderer = this;
	this.canvas = canvas;
	this.renderer = new Vex.Flow.Renderer(this.canvas, Vex.Flow.Renderer.Backends.CANVAS);
	this.selectedNotes = [];
	this.measures = [];
	this.ties = [];
	renderer.totNScores = 0;
	instrumentsUsed.forEach(function(element, index){
		renderer.totNScores += element["scoresClef"].length;
	});
}

Renderer.prototype.renderAndDraw = function() {
	var ctx = this.renderer.getContext();
	ctx.clear();
	console.log("rendereAndDraw");
	this.renderMeasures();
	for (var i = 0; i < this.measures.length; i++) 
		this.measures[i].drawNotes(this.renderer.getContext());
	this.ties.forEach(function (t) {
		t[0].setContext(ctx).draw()
	});
}

Renderer.prototype.updateComposition = function(data) {
	console.log(data);
	var instrumentsScore = data.instrumentsScore;
	this.composition = data;
	this.measures = [];
	this.ties = [];
	this.selectedNotes = [];
	for(var i=0; i<instrumentsScore[0].measures.length; i++) {
		var timeSignature = instrumentsScore[0].measures[i].timeSignature.split("/");
		var m = new Measure(i, timeSignature[0], timeSignature[1], instrumentsScore[0].measures[0].keySignature, data.instrumentsUsed);
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
	var ctx = this.renderer.getContext();
	ctx.clear();
	this.renderMeasures();
	for (var i = 0; i < this.measures.length; i++) 
		this.measures[i].drawNotes(this.renderer.getContext());
	this.ties.forEach(function (t) {
		t[0].setContext(ctx).draw()
	});
}

Renderer.prototype.renderMeasures = function () {
	var size = 0;
	for (var i = 0; i < this.measures.length; i++) {
		this.measures[i].computeScale();
		size += this.measures[i].width;
	}
	var ctx = this.renderer.getContext();
	this.renderer.resize(size + 1500, (this.totNScores*80>250?this.totNScores*83:250));
	ctx.clear();
	this.measures[0].render(ctx, 100);
	for (var i = 1; i < this.measures.length; i++) 
		this.measures[i].render(ctx, this.measures[i - 1].getEndX());
	this.measures[this.measures.length-1].renderEndLine(ctx);
}
