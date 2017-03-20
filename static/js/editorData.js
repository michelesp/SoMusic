function EditorData(keySign, timeSign, instrumentsUsed) {
    this.keySign = keySign;
    this.timeSign = timeSign;
    this.measures = [];
    this.ties = [];
    this.instrumentsUsed = instrumentsUsed;
}

function NoteData(duration, isRest, keys, accidental) {
    this.duration = duration;
    this.isRest = isRest;
    this.keys = keys;
    this.accidental = accidental;
}

function TieData(voiceName, firstIndex, firstNote, lastIndex, lastNote) {
    this.voiceName = voiceName;
	this.firstIndex = firstIndex;
	this.firstNote = firstNote;
    this.lastIndex = lastIndex;
    this.lastNote = lastNote;
}

function MeasureData(index, voicesName) {
    this.index = index;
    this.notesArr = {};
    for(var i=0; i<voicesName.length; i++)
    	this.notesArr[voicesName[i]] = [];
}