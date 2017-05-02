<?php

class SOMUSIC_CLASS_MusicXMLParser {
	private $xmlString;
	private $composition;
	private $instrumentsName;
	private $major;
	private $minor;
	
	
	public function __construct($instrumentsName) {
		$this->composition = null;
		$this->major = array("-7"=>"Cb", "-6"=>"Gb", "-5"=>"Db", "-4"=>"Ab", "-3"=>"Eb", "-2"=>"Bb", "-1"=>"F",
				"0"=>"C", "1"=>"G", "2"=>"D", "3"=>"A", "4"=>"E", "5"=>"B", "6"=>"F#", "7"=>"C#");
		$this->minor = array("-7"=>"Abm", "-6"=>"Ebm", "-5"=>"Bbm", "-4"=>"Fm", "-3"=>"Cm", "-2"=>"Gm", "-1"=>"Dm",
				"0"=>"Am", "1"=>"Em", "2"=>"Bm", "3"=>"F#m", "4"=>"C#m", "5"=>"G#m", "6"=>"D#m", "7"=>"A#m");
		$this->instrumentsName = array();
		for($i=0; $i<count($instrumentsName); $i++)
			array_push($this->instrumentsName, strtolower($instrumentsName[$i]));
	}
	
	
	public function parseMusicXML($xmlString) {
		if(isset($this->composition) && isset($this->xmlString) && $this->xmlString==$xmlString)
			return $this->composition;
		$this->xmlString = $xmlString;
		$doc = simplexml_load_string($xmlString);
		
		$parts = array();
		$scoreParts = $doc->xpath("//score-part");
		foreach ($scoreParts as $i=>$scoreP) 
			array_push($parts, array("id"=>$scoreP["id"]->__toString(), "name"=>$scoreP->xpath("./part-name")[0]->__toString()));
		$instrumentsUsed = $this->getInstrumentsUsed($doc, $parts);

		$instrumentsScore = array();
		for($i=0; $i<count($parts); $i++) {
			$part = $doc->xpath("//part[@id='".$parts[$i]["id"]."']")[0];
			$is = $this->getInstrumentScore($part, $parts[$i]["name"], $instrumentsUsed[$i]->name);
			for($j=0; $j<count($is); $j++)
				array_push($instrumentsScore, $is[$j]);
		}
		$this->composition = new SOMUSIC_CLASS_Composition(-1, "", -1, time(), -1, time(), $instrumentsScore, $instrumentsUsed);
		return $this->composition;
	}
	
	public function parseComposition($composition) {
		$xml = new SimpleXMLElement("<score-partwise></score-partwise>");
		$xml->addAttribute("version", "3.0");
		$identification = $xml->addChild("identification");
		$encoding = $identification->addChild("encoding");
		$encoding->addChild("software", "SoMusic");
		$encoding->addChild("encoding-date", date('Y-m-d'));
		
		$partList = $xml->addChild("part-list");
		$scoreCount = 0;
		for($i=0; $i<count($composition->instrumentsUsed); $i++) {
			$instrumentUsed = $composition->instrumentsUsed[$i];
			$scorePart = $partList->addChild("score-part");
			$scorePart->addAttribute("id", "P".($i+1));
			$scorePart->addChild("part-name", $instrumentUsed->labelName);
			$scorePart->addChild("part-abbreviation", $instrumentUsed->name);
			$scoreInstrument = $scorePart->addChild("score-instrument");
			$scoreInstrument->addAttribute("id", "P".($i+1)."-I".($i+1));
			$scoreInstrument->addChild("instrument-name", $instrumentUsed->name);
			
			$part = $xml->addChild("part");
			$part->addAttribute("id", "P".($i+1));
			$scores = array();
			for($j=0; $j<count($instrumentUsed->scoresClef); $j++) {
				array_push($scores, $composition->instrumentsScore[$scoreCount]);
				$scoreCount++;
			}
			$nMeasures = count($scores[0]->measures);
			for($j=0; $j<$nMeasures; $j++) {
				$measure = $part->addChild("measure");
				$measure->addAttribute("number", $j+1);
				$attributes = $measure->addChild("attributes");
				$attributes->addChild("divisions", 16);
				$key = $attributes->addChild("key");
				if(!is_bool(strrpos($scores[0]->measures[$j]->keySignature, "m")))
					$key->addChild("fifths", array_search($scores[0]->measures[$j]->keySignature, $this->minor));
				else $key->addChild("fifths", array_search($scores[0]->measures[$j]->keySignature, $this->major));
				$time = $attributes->addChild("time");
				$timeMeasure = explode("/", $scores[0]->measures[$j]->timeSignature);
				$time->addChild("beats", $timeMeasure[0]);
				$time->addChild("beat-type", $timeMeasure[1]);
				$attributes->addChild("staves", count($scores));
				$nVoices = 0;
				for($k=0; $k<count($scores); $k++) {
					$clef = $attributes->addChild("clef");
					$clef->addAttribute("number", $k+1);
					if($scores[$k]->measures[$j]->clef=="alto") {
						$clef->addChild("sign", "C");
						$clef->addChild("line", 3);
					}
					else if($scores[$k]->measures[$j]->clef=="bass") {
						$clef->addChild("sign", "F");
						$clef->addChild("line", 4);
					}
					else {
						$clef->addChild("sign", "G");
						$clef->addChild("line", 2);
					}
					$nVoices += count($scores[$k]->measures[$j]->voices);
				}
				$staffDetails = $attributes->addChild("staff-details");
				$staffDetails->addChild("staff-lines", 5);
				
				/*for($k=0; $k<$nVoices; $k++) {
					$scoreIndex = $k%count($scores);
					$voiceIndex = (int)($k/count($scores));
					$voice = $scores[$scoreIndex]->measures[$j]->voices[$voiceIndex];
					for($l=0; $l<count($voice); $l++) {
						for($m=0; $m<count($voice[$l]->step); $m++) {
							$note = $measure->addChild("note");
							if($m>1)
								$note->addChild("chord");
							if(!$voice[$l]->isRest) {
								$pitch = $note->addChild("pitch");
								$pitch->addChild("step", strtoupper($voice[$l]->step[$m]));
								$pitch->addChild("octave", $voice[$l]->octave[$m]);
							}
							else $note->addChild("rest");
							$note->addChild("duration", $voice[$l]->duration);
							$note->addChild("voice", $k+1);
							$note->addChild("staff", $scoreIndex+1);
							
							if(count($voice[$l]->isTieStart)>0) {
								$tie = $note->addChild("tie");
								$tie->addAttribute("type", "start");
							}
							if(count($voice[$l]->isTieEnd)>0) {
								$tie = $note->addChild("tie");
								$tie->addAttribute("type", "stop");
							}
						}
					}
					if($k!=$nVoices-1) {
						$backup = $measure->addChild("backup");
						$backup->addChild("duration", 64);
					}
				}*/
				$n = 0;
				for($k=0; $k<count($scores); $k++) {
					for($h=0; $h<count($scores[$k]->measures[$j]->voices); $h++) {
						$n++;
						$voice = $scores[$k]->measures[$j]->voices[$h];
						for($l=0; $l<count($voice); $l++) {
							for($m=0; $m<count($voice[$l]->step); $m++) {
								$note = $measure->addChild("note");
								if($m>1)
									$note->addChild("chord");
									if(!$voice[$l]->isRest) {
										$pitch = $note->addChild("pitch");
										$pitch->addChild("step", strtoupper($voice[$l]->step[$m]));
										$pitch->addChild("octave", $voice[$l]->octave[$m]);
									}
									else $note->addChild("rest");
									$note->addChild("duration", $voice[$l]->duration);
									$note->addChild("voice", $n);
									$note->addChild("staff", $k+1);
										
									if(count($voice[$l]->isTieStart)>0) {
										$tie = $note->addChild("tie");
										$tie->addAttribute("type", "start");
									}
									if(count($voice[$l]->isTieEnd)>0) {
										$tie = $note->addChild("tie");
										$tie->addAttribute("type", "stop");
									}
							}
						}
						if($n<$nVoices) {
							$backup = $measure->addChild("backup");
							$backup->addChild("duration", 64);
						}
					}
				}
			}	
				
		}

		return $xml->asXML();
	}
	
	private function getInstrumentsUsed($doc, $parts) {
		$instrumentsUsed = array();
		for($i=0; $i<count($parts); $i++) {
			$iu = array();
			$part = $doc->xpath("//part[@id='".$parts[$i]["id"]."']")[0];
			$iu["labelName"] = $parts[$i]["name"];
			$iu["scoresClef"] = $this->getClefs($part->measure[0]->attributes);
			$iu["user"] = -1;
			$iu["braces"] = array();
			$name = explode(" ", strtolower($iu["labelName"]));
			for ($j=0; $j<count($this->instrumentsName) && !isset($iu["name"]); $j++) {
				$instrumentName = $this->instrumentsName[$j];
				for ($k=0; $k<count($name) && !isset($iu["name"]); $k++)
					if(!is_bool(strpos($instrumentName, $name[$k])))
						$iu["name"] = implode("_", explode(" ", $instrumentName));
			}
			//TODO: modificare
			if(count($iu["scoresClef"])==2) {
				array_push($iu["braces"], array(0, 1));
				if(!isset($iu["name"]))
					$iu["name"] = "piano";
			}
			else if(count($iu["scoresClef"])==3){
				array_push($iu["braces"], array(0, 1));
				if(!isset($iu["name"]))
					$iu["name"] = "organ";
			}
			else if(!isset($iu["name"]))
				$iu["name"] = "accordion";
			array_push($instrumentsUsed, (object)$iu);
		}
		return $instrumentsUsed;
	}
	
	private function getInstrumentScore($part, $name, $instrument) {
		$clefs = $this->getClefs($part->measure[0]->attributes);
		$instrumentScore = array();
		for($i=0; $i<count($clefs); $i++) {
			$is = new SOMUSIC_CLASS_InstrumentScore($clefs[$i], $name."#score".$i, array(), array(), $instrument, -1);
			array_push($instrumentScore, $is);
		
			$tieStart = array();
			
			$lastClef = null;
			$lastTimeSignature = null;
			$lastKeySignature = null;
			$lastDivisions = null;
			for($j=0; $j<count($part->measure); $j++) {
				$measure = $this->getMeasure($part->measure[$j], $lastClef, $lastTimeSignature, $lastKeySignature, $lastDivisions, $i, count($clefs));
				$lastClef = $measure->clef;
				$lastTimeSignature = $measure->timeSignature;
				$lastKeySignature = $measure->keySignature;
				$lastDivisions = $this->getDivisions($part->measure[$j], $lastDivisions);
				array_push($is->measures, $measure);
				$ts = $this->getTieTyped($part->measure[$j], "start", $i);
				for($k=0; $k<count($ts); $k++)
					array_push($tieStart, new SOMUSIC_CLASS_Tie($j, $ts[$k], null, null));
				$tieEnd = $this->getTieTyped($part->measure[$j], "end", $i);
				while (count($tieEnd)>0) {
					$tie = array_pop($tieStart);
					$tie->lastMeasure = $j;
					$tie->lastNote = array_pop($tieEnd);
					$nTie = count($is->ties);
					array_push($is->measures[$tie->firstMeasure]->voices[0][$tieStart]->isTieStart, $nTie);
					array_push($is->measures[$tie->lastMeasure]->voices[0][$tieStart]->isTieEnd, $nTie);
					array_push($is->ties, $tie);
				}
			}
		}
		return $instrumentScore;
	}
	
	private function getDivisions($measureXML, $divisions = null) {
		if(isset($measureXML->attributes))
			$divisions = intval($measureXML->attributes->divisions->__toString());
		return $divisions;
	}
	
	private function getClefs($measureAttr) {
		$toReturn = array();
		for($i=0; $i<count($measureAttr->clef); $i++)
			array_push($toReturn, $this->getClef($measureAttr, $i));
		return $toReturn;
	}
	
	private function getClef($measureAttr, $scoreIndex) {
		$clef = $measureAttr->clef[$scoreIndex]->sign->__toString();
		if($clef=="F")
			return "bass";		//line 4
		if($clef=="C")
			return "alto";		//line 3
		return "treble";		//line 2
	}
	
	private function getKeySignature($measureAttr) {
		$fifths = $measureAttr->key->fifths->__toString();
		if(isset($measureAttr->key->mode) && $measureAttr->key->mode->__toString()=="minor")
			return $this->minor[$fifths];
		return $this->major[$fifths];
	}
	
	private function getMeasure($measureXML, $clef=null, $timeSignature=null, $keySignature=null, $divisions=null, $scoreIndex, $nScorePart) {
		if(isset($measureXML->attributes)) {
			$clef = $this->getClef($measureXML->attributes, $scoreIndex);
			$timeSignature = $measureXML->attributes->time->beats."/".$measureXML->xpath(".//beat-type")[0];
			$keySignature = $this->getKeySignature($measureXML->attributes);
			$divisions = intval($measureXML->attributes->divisions->__toString());
		}
		$measure = new SOMUSIC_CLASS_Measure("-1", $clef, $keySignature, $timeSignature, array());
		$indexs = $this->getMeasureStaveIndexs($measureXML, $scoreIndex, $nScorePart, $divisions);
		for($i=0; $i<count($indexs); $i++) {
			$voice = array();
			$start = $this->getMeasureNoteStart($measureXML, $indexs[$i], $divisions);
			$end = $this->getMeasureNoteEnd($measureXML, $start, $divisions);
			for($j=$start; $j<$end; $j++) {
				$note = $this->getNote($measureXML->note[$j], $divisions);
				if(isset($measureXML->note[$j]->chord)) {
					array_push($voice[count($voice)-1]->octave, $note->octave[0]);
					array_push($voice[count($voice)-1]->step, $note->step[0]);
					array_push($voice[count($voice)-1]->accidental, $note->accidental[0]);
				}
				else array_push($voice, $note);
			}
			array_push($measure->voices, $voice);
		}
		return $measure;
	}
	
	private function getMeasureStaveIndexs($measureXML, $scoreIndex, $nScorePart, $divisions) {
		if((isset($measureXML->backup) && count($measureXML->backup)==$nScorePart-1)
				|| (!isset($measureXML->backup) && $scoreIndex==$nScorePart-1))
			return array($scoreIndex);
		$toReturn = array();
		for($i=0; $i<count($measureXML->backup)+1; $i++) {
			$start = $this->getMeasureNoteStart($measureXML, $i, $divisions);
			if(isset($measureXML->note[$start]->staff) && intval($measureXML->note[$start]->staff->__toString())-1==$scoreIndex)
				array_push($toReturn, $i);
		}
		return $toReturn;
	}
	
	private function getMeasureNoteStart($measureXML, $scoreIndex, $divisions) {
		$beats = $measureXML->attributes->time->beats;
		$si = 0;
		$start = 0;
		$end = count($measureXML->note);
		while($si<$scoreIndex) {
			$totDuration = 0;
			$find = false;
			for($i=$start; $i<$end && !$find; $i++) {
				$totDuration += $measureXML->note[$i]->duration;
				if($totDuration/$divisions==$beats) {
					$start = $i+1;
					$find = true;
					$si++;
				}
			}
			if(!$find)
				$si++;
		}
		return $start;
	}
	
	private function getMeasureNoteEnd($measureXML, $start, $divisions) {
		$beats = $measureXML->attributes->time->beats;
		$end = count($measureXML->note);
		$totDuration = 0;
		for($i=$start; $i<$end; $i++) {
			$totDuration += $measureXML->note[$i]->duration;
			if($totDuration/$divisions==$beats) 
				return $i+1;
		}
		return $end;
	}
	
	private function getNote($noteXML, $divisions) {
		$isRest = isset($noteXML->rest);
		if($isRest) {
			$step = array();
			$octave = array();
			$accidental = array();
		}
		else {
			$step = array($noteXML->pitch->step->__toString());
			$octave = array($noteXML->pitch->octave->__toString());
			$accidental = array($this->getAccidental($noteXML));
		}
		$duration = 16*intval($noteXML->duration)/$divisions;
		$isTieStart = array();
		$isTieEnd = array();
		return new SOMUSIC_CLASS_Note(-1, $step, $octave, $accidental, $duration, $isRest, $isTieStart, $isTieEnd);
	}
	
	private function getAccidental($noteXML) {
		if(!isset($noteXML->pitch->alter))
			return "clear";
		$alter = intval($noteXML->pitch->alter);
		if($alter==0)
			return "clear";
		$accidental = "";
		if($alter>0)
			for($i=0; $i<$alter; $i++)
				$accidental.="#";
		else
			for($i=0; $i>$alter; $i--)
				$accidental.="b";
		return $accidental;
	}
	
	private function getTieTyped($measureXML, $type) {
		$toReturn = array();
		$index = 0;
		for($i=0; $i<count($measureXML->note); $i++) {
			$noteXML = $measureXML->note[$i];
			if(isset($noteXML->tie) && $noteXML->tie["type"]==$type) 
				array_push($toReturn, $index);
			if(!isset($noteXML->chord))
				$index++;
		}
		return $toReturn;
	}
	
}