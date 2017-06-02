
function Renderer(canvas, instrumentsUsed) {
	var renderer = this;
	this.canvas = canvas;
	this.renderer = new Vex.Flow.Renderer(this.canvas, Vex.Flow.Renderer.Backends.CANVAS);
	this.measures = [];
	this.ties = [];
	this.totNScores = 0;
	instrumentsUsed.forEach(function(element, index){
		renderer.totNScores += element["scoresClef"].length;
	});
}

Renderer.prototype.updateComposition = function(data) {
	console.log(data);
	var instrumentsScore = data.instrumentsScore;
	this.composition = data;
	this.measures = [];
	this.ties = [];
	for(var i=0; i<instrumentsScore[0].measures.length; i++) {
		var timeSignature = instrumentsScore[0].measures[i].timeSignature.split("/");
		var m = new Measure(i, timeSignature[0], timeSignature[1], instrumentsScore[0].measures[0].keySignature, data.instrumentsUsed);
		for(var j=0; j<instrumentsScore.length; j++) {
			var m1 = instrumentsScore[j].measures[i];
			//console.log(i, j, m1.voices);
			if(m1.voices.length==0)
				continue;
			for(var voiceIndex = 0; voiceIndex<m1.voices.length; voiceIndex++) {
				for(var k=0; k<m1.voices[voiceIndex].length; k++) {
					var note = m1.voices[voiceIndex][k];
					var keys = [];
					var duration;
					for(var l=0; l<note.step.length; l++)
						keys[l] = note.step[l]+"/"+note.octave[l];
					if(note.step.length==0) {
						if(m1.clef=="treble") {
							if(m1.voices.length>1 && voiceIndex==0)
								keys[voiceIndex] = "f/5";
							else if(m1.voices.length>1 && voiceIndex==1)
								keys[voiceIndex] = "e/4";
							else keys[voiceIndex] = "b/4";
						}
						else if(m1.clef=="bass") {
							if(m1.voices.length>1 && voiceIndex==0)
								keys[voiceIndex] = "a/3";
							else if(m1.voices.length>1 && voiceIndex==1)
								keys[voiceIndex] = "g/2";
							else keys[voiceIndex] = "d/3";
						}
						else if(m1.clef=="alto") {
							if(m1.voices.length>1 && voiceIndex==0)
								keys[voiceIndex] = "g/4";
							else if(m1.voices.length>1 && voiceIndex==1)
								keys[voiceIndex] = "f/3";
							else keys[voiceIndex] = "c/4";
						}
					}
					if(note.dots>0)
						duration = 64/(note.duration*(2*note.dots)/(Math.pow(2, note.dots+1)-1));
					else duration = 64/note.duration;
					var note1 = new Vex.Flow.StaveNote({clef: m1.clef, keys: keys, duration: duration+(note.step.length==0?"r":"")});
					if(note.accidental!=null)
						for(var l=0; l<note.accidental.length; l++)
							if(note.accidental[l]!="clear")
								note1.addAccidental(l, new Vex.Flow.Accidental(note.accidental[l]));
					if(note.text!=null)
						note1.addModifier(0, new Vex.Flow.Annotation(note.text).setVerticalJustification(Vex.Flow.Annotation.VerticalJustify.TOP));
					for(var d=0; d<note.dots; d++)
						note1.addDotToAll();
					m.addNote(note1, instrumentsScore[j].name, k, voiceIndex);
				}
			}
		}
		this.measures.push(m);
	}
	for(var i=0; i<instrumentsScore.length; i++) {
		var instrumentScore = instrumentsScore[i];
		for(var j=0; j<instrumentScore.ties.length; j++) {
			var tie = instrumentScore.ties[j];
			this.ties.push([new Vex.Flow.StaveTie({
				first_note: this.measures[tie.firstMeasure].notes[instrumentScore.name][tie.voiceIndex][tie.firstNote],
				last_note: this.measures[tie.lastMeasure].notes[instrumentScore.name][tie.voiceIndex][tie.lastNote]
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
	this.renderer.resize(size+150, 20+this.totNScores*80);
	ctx.clear();
	this.measures[0].render(ctx, 100);
	for (var i = 1; i < this.measures.length; i++) 
		this.measures[i].render(ctx, this.measures[i - 1].getEndX());
	this.measures[this.measures.length-1].renderEndLine(ctx);
}

