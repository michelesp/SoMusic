<?php

class SOMUSIC_CLASS_Composition implements Serializable, JsonSerializable {
	private $id;
	private $user_c;
	private $timestamp_c;
	private $stepValue;
	private $accidentalValue;
	public $name;
	public $user_m;
	public $timestamp_m;
	public $instrumentsScore;
	public $instrumentsUsed;
	
	
	public function __construct($id, $name, $user_c, $timestamp_c, $user_m, $timestamp_m, $instrumentsScore, $instrumentsUsed) {
		$this->id = $id;
		$this->name = $name;
		$this->user_c = $user_c;
		$this->timestamp_c = $timestamp_c;
		$this->user_m = $user_m;
		$this->timestamp_m = $timestamp_m;
		$this->instrumentsScore = $instrumentsScore;
		$this->instrumentsUsed = $instrumentsUsed;
		$this->stepValue = array("A"=>5, "B"=>6, "C"=>0, "D"=>1, "E"=>2, "F"=>3, "G"=>4);
		$this->accidentalValue = array("b"=>-1, "clear"=>0, "#"=>1);
	}
	
	public static function getCompositionObject($compositionDB) {
		$composition = new SOMUSIC_CLASS_Composition($compositionDB->id, $compositionDB->name, $compositionDB->user_c, $compositionDB->timestamp_c, $compositionDB->user_m, $compositionDB->timestamp_m, array(), json_decode($compositionDB->instrumentsUsed));
		$instrumentsScoreArray = json_decode($compositionDB->instrumentsScore);
		foreach ($instrumentsScoreArray as $instrumentScoreArray) {
			if(!is_object($instrumentScoreArray)) {
				$instrumentScore = new SOMUSIC_CLASS_InstrumentScore($instrumentScoreArray["default_clef"], $instrumentScoreArray["name"], array(), array(), $instrumentScoreArray["instrument"], $instrumentScoreArray["user"]);
				foreach ($instrumentScoreArray["measures"] as $measureArray) {
					$voices = array();
					foreach ($measureArray["voices"] as $voiceArray) {
						$voice = array();
						foreach ($voiceArray as $noteArray)
							array_push($voice, new SOMUSIC_CLASS_Note($noteArray["step"], $noteArray["octave"], $noteArray["accidental"], $noteArray["duration"], $noteArray["isRest"], $noteArray["isTieStart"], $noteArray["isTieEnd"], $noteArray["dots"]));
							array_push($voices, $voice);
					}
					$measure = new SOMUSIC_CLASS_Measure($measureArray["clef"], $measureArray["keySignature"], $measureArray["timeSignature"], $voices);
					array_push($instrumentScore->measures, $measure);
				}
				foreach ($instrumentScoreArray["ties"] as $tieArray)
					array_push($instrumentScore->ties, new SOMUSIC_CLASS_Tie($tieArray["firstMeasure"], $tieArray["firstNote"], $tieArray["lastMeasure"], $tieArray["lastNote"]));
				array_push($composition->instrumentsScore, $instrumentScore);
			}
			else array_push($composition->instrumentsScore, $instrumentScoreArray);
		}
		return $composition;
	}
	
	/*public static function getCompositionObject($compositionArray) {
		$composition = new SOMUSIC_CLASS_Composition($compositionArray["id"], $compositionArray["name"], $compositionArray["user_c"], $compositionArray["timestamp_c"], $compositionArray["user_m"], $compositionArray["timestamp_m"], array(), $compositionArray["instrumentsUsed"]);
		foreach ($compositionArray["instrumentsScore"] as $instrumentScoreArray) {
			$instrumentScore = new SOMUSIC_CLASS_InstrumentScore($instrumentScoreArray["default_clef"], $instrumentScoreArray["name"], array(), array(), $instrumentScoreArray["instrument"], $instrumentScoreArray["user"]);
			foreach ($instrumentScoreArray["measures"] as $measureArray) {
				$voices = array();
				foreach ($measureArray["voices"] as $voiceArray) {
					$voice = array();
					foreach ($voiceArray as $noteArray)
						array_push($voice, new SOMUSIC_CLASS_Note($noteArray["id"], $noteArray["step"], $noteArray["octave"], $noteArray["accidental"], $noteArray["duration"], $noteArray["isRest"], $noteArray["isTieStart"], $noteArray["isTieEnd"]));
					array_push($voices, $voice);
				}
				$measure = new SOMUSIC_CLASS_Measure($measureArray["id"], $measureArray["clef"], $measureArray["keySignature"], $measureArray["timeSignature"], $voices);
				array_push($instrumentScore->measures, $measure);
			}
			foreach ($instrumentScoreArray["ties"] as $tieArray)
				array_push($instrumentScore->ties, new SOMUSIC_CLASS_Tie($tieArray["firstMeasure"], $tieArray["firstNote"], $tieArray["lastMeasure"], $tieArray["lastNote"]));
				array_push($composition->instrumentsScore, $instrumentScore);
		}
		return $composition;
	}*/

			
	public function getId() {
		return $this->id;
	}
	
	public function getUserC() {
		return $this->user_c;
	}
	
	public function getTimestampC() {
		return $this->timestamp_c;
	}
	
	public function serialize() {
		return serialize([$this->id, $this->user_c, $this->timestamp_c, $this->name,
				$this->user_m, $this->timestamp_m, $this->instrumentsScore, $this->instrumentsUsed]);
	}
	
	public function unserialize($data) {
		list($this->id, $this->user_c, $this->timestamp_c, $this->name,$this->user_m,
			$this->timestamp_m, $this->instrumentsScore, $this->instrumentsUsed) = unserialize($data);
	}
	
	public function jsonSerialize () {
		$vars = get_object_vars($this);
		unset($vars["stepValue"]);
		unset($vars["accidentalValue"]);
		return $vars;
	}
	
	public function getMelodicRepresentation($scoreIndex) {
		$modalNote = $this->findModalNote($scoreIndex);
		$melodicRepresentation = "";
		$measures = $this->instrumentsScore[$scoreIndex]->measures;
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
		return $melodicRepresentation;
	}
	
	public function getMelodicRepresentation2($scoreIndex) {
		$melodicRepresentation = "";
		$measures = $this->instrumentsScore[$scoreIndex]->measures;
		$lastNote = null;
		for($i=0; $i<count($measures); $i++) {
			$voice = $measures[$i]->voices[0];			//TODO: extends to all voices
			for($j=0; $j<count($voice); $j++) {
				$note = $voice[$j];
				if(!$note->isRest) {
					$noteArr = array("octave"=>$note->octave[0], "step"=>$note->step[0], "accidental"=>(isset($note->accidental[0])?$note->accidental[0]:"clear"));
					if(isset($lastNote))
						$noteRepresentation = $this->getNoteDistance($noteArr, $lastNote);
					else $noteRepresentation = 0;
					$melodicRepresentation.=($noteRepresentation>=0?'+'.$noteRepresentation:$noteRepresentation);
					$lastNote = $noteArr;
				}
				else if(strlen($melodicRepresentation)==0 || $melodicRepresentation[strlen($melodicRepresentation)-1]!='p')
					$melodicRepresentation.='p';
			}
		}
		return $melodicRepresentation;
	}
	
	private function getNoteDistance($note, $modal) {
		$distance = 0;
		$note["octave"] = intval($note["octave"]);
		$note["step"] = strtoupper($note["step"]);
		$modal["octave"] = intval($modal["octave"]);
		$modal["step"] = strtoupper($modal["step"]);
		$minMax = $this->getMinMaxNotes($note, $modal);
		for($i=0; $i<count($minMax); $i++) {
			$minMax[$i]["octave"] = intval($minMax[$i]["octave"]);
			$minMax[$i]["step"] = strtoupper($minMax[$i]["step"]);
		}
		$note0 = $minMax[0];
		//var_dump($minMax);
		while($note0["octave"]!=$minMax[1]["octave"] || $note0["step"]!=$minMax[1]["step"]) {
			//var_dump($note0);
			//sleep(1);
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
		$measures = $this->instrumentsScore[$scoreIndex]->measures;
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
