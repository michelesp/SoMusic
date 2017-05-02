<?php

class SOMUSIC_CLASS_CompositionUtil {
	private $composition;
	private $modalNotes;
	private $melodicRepresentation;
	private $stepValue;
	private $accidentalValue;
	
	public function __construct($composition) {
		$this->composition = $composition;
		$this->modalNotes = array();
		$this->melodicRepresentation = array();
		$this->stepValue = array("A"=>5, "B"=>6, "C"=>0, "D"=>1, "E"=>2, "F"=>3, "G"=>4);
		$this->accidentalValue = array("b"=>-1, "clear"=>0, "#"=>1);
		for($i=0; $i<count($composition->instrumentsScore); $i++)
			array_push($this->modalNotes, $this->findModalNote($i));
	}
	
	
	public function getMelodicRepresentation($scoreIndex) {
		if(isset($this->melodicRepresentation[$scoreIndex]))
			return $this->melodicRepresentation[$scoreIndex];
		$modalNote = $this->modalNotes[$scoreIndex];
		$melodicRepresentation = "";
		$measures = $this->composition->instrumentsScore[$scoreIndex]->measures;
		for($i=0; $i<count($measures); $i++) {
			$voice = $measures[$i]->voices[0];			//TODO: extends to all voices
			for($j=0; $j<count($voice); $j++) {
				$note = $voice[$j];
				if(!$note->isRest) {
					$noteArr = array("octave"=>$note->octave[0], "step"=>$note->step[0], "accidental"=>(isset($note->accidental[0])?$note->accidental[0]:"clear"));
					$noteRepresentation = $this->getNoteDistance($noteArr, $modalNote);
					$melodicRepresentation.=($noteRepresentation>=0?'+'.$noteRepresentation:$noteRepresentation);
				}
				else if(strlen($melodicRepresentation)==0 || $melodicRepresentation[strlen($melodicRepresentation)-1]!='p')
					$melodicRepresentation.='p';
			}
		}
		$this->melodicRepresentation[$scoreIndex] = $melodicRepresentation;
		return $melodicRepresentation;
	}
	
	private function getNoteDistance($note, $modal) {
		$distance = 0;
		$minMax = $this->getMinMaxNotes($note, $modal);
		$note0 = $minMax[0];
		//var_dump(array($note, $minMax[1]));
		while($note0["octave"]!=$minMax[1]["octave"] || $note0["step"]!=$minMax[1]["step"]) {
			if($note0["step"]=="B" || $note0["step"]=="E")
				$distance++;
			else $distance+=2;
			$stepValue = $this->stepValue[$note0["step"]];
			if($stepValue==6) {
				$note0["step"] = "C";
				$note0["octave"]++;
			}
			else $note0["step"] = array_keys($this->stepValue, $stepValue+1)[0];
		}
		$distance-=$this->accidentalValue[$minMax[0]["accidental"]];
		$distance+=$this->accidentalValue[$minMax[1]["accidental"]];
		return ($minMax[0]==$note?-1*$distance:$distance);
	}
	
	private function getMinMaxNotes($note1, $note2) {
		if($note1["octave"]<$note2["octave"])
			$minNote = $note1;
		else if($note1["octave"]>$note2["octave"])
			$minNote = $note2;
		else {
			if($this->stepValue[$note1["step"]]<$this->stepValue[$note2["step"]])
				$minNote = $note1;
			else if($this->stepValue[$note1["step"]]>$this->stepValue[$note2["step"]])
				$minNote = $note2;
			else {
				if($this->accidentalValue[$note1["accidental"]]<$this->accidentalValue[$note2["accidental"]])
					$minNote = $note1;
				else $minNote = $note2;
			}
		}
		return array($minNote, ($minNote==$note1?$note2:$note1));
	}
	
	private function findModalNote($scoreIndex) {
		$measures = $this->composition->instrumentsScore[$scoreIndex]->measures;
		$occurrences = array();
		for($i=0; $i<count($measures); $i++) {
			$voice = $measures[$i]->voices[0];			//TODO: extends to all voices
			for($j=0; $j<count($voice); $j++) {
				$note = $voice[$j];
				if(!$note->isRest) {
					$noteArr = array("octave"=>$note->octave[0], "step"=>$note->step[0], "accidental"=>(isset($note->accidental[0])?$note->accidental[0]:"clear"));
					$noteName = json_encode($noteArr);
					if(isset($occurrences[$noteName]))
						$occurrences[$noteName]++;
					else $occurrences[$noteName] = 0;
				}
			}
		}
		if(count($occurrences)==0) {
			$this->melodicRepresentation[$scoreIndex] = "p";
			return null;
		}
		return $this->getLowerNoteString(array_keys($occurrences, max($occurrences)));
	}
	
	private function getLowerNoteString($notesString) {
		$notes = array();
		$minOctave = INF;
		foreach ($notesString as $noteString) {
			$note = json_decode($noteString);
			array_push($notes, $note);
			if($note->octave<$minOctave)
				$minOctave = $note->octave;
		}
		$minStep = "B";
		foreach ($notes as $note) {
			if($minOctave==$note->octave && $this->stepValue[$note->step]<$this->stepValue[$minStep])
				$minStep = $note->step;
		}
		$minAccidental = "#";
		foreach ($notes as $note) {
			if($minOctave==$note->octave && $minStep==$note->step && $this->accidentalValue[$note->accidental]<$this->accidentalValue[$minAccidental])
				$minAccidental = $note->accidental;
		}
		return array("octave"=>$minOctave, "step"=>$minStep, "accidental"=>$minAccidental);
	}
	
}