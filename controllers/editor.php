<?php

class SOMUSIC_CTRL_Editor extends OW_ActionController {
	
	public function initEditor() {
		if(empty($_REQUEST["instrumentsUsed"]) || empty($_REQUEST["timeSign"]) || empty($_REQUEST["keySign"]))
			exit (json_encode (array('error' => "error initializzation editor")));
		$userId = OW::getUser()->getId();
		$cache = new Memcached();
		$cache->addServer("localhost", 11211);
		$composition = new SOMUSIC_CLASS_Composition(-1, "", $userId, -1, $userId, -1, array());
		foreach ($_REQUEST["instrumentsUsed"] as $instrument) {
			for ($i=0; $i<count($instrument["scoresClef"]); $i++) {
				$is = new SOMUSIC_CLASS_InstrumentScore(-1, $instrument["scoresClef"][$i], $instrument["labelName"]."#score".$i, array(), array(), $instrument["name"]);
				array_push($is->measures, $this->newMeasure($instrument["scoresClef"][$i], explode("/", $_REQUEST["timeSign"]), $_REQUEST["keySign"]));
				array_push($composition->instrumentsScore, $is);
			}
		}
		$cache->set($userId, $composition);
		exit (json_encode($composition));
	}
	
	public function addNote() {
		/*if(empty($_REQUEST["staveIndex"]) || empty($_REQUEST["measureIndex"]) || empty($_REQUEST["noteIndex"]) 
				|| empty($_REQUEST["newNote"]) || empty($_REQUEST["duration"]))
			exit (json_encode (array('error' => "error initializzation editor")));*/
		$staveIndex = intval($_REQUEST["staveIndex"]);
		$measureIndex = intval($_REQUEST["measureIndex"]);
		$noteIndex = intval($_REQUEST["noteIndex"]);
		$newNote = explode("/", $_REQUEST["newNote"]);
		$duration = 64/intval($_REQUEST["duration"]);
		$userId = OW::getUser()->getId();
		$cache = new Memcached();
		$cache->addServer("localhost", 11211);
		$composition = $cache->get($userId);
		$instrumentScore = $composition->instrumentsScore[$staveIndex];
		$measure = $instrumentScore->measures[$measureIndex];
		$note = $measure->voices[0][$noteIndex];
		if($duration>$note->duration) 
			exit (json_encode (array('error' => "error insertion note")));
		$durationDif = $note->duration-$duration;
		if($note->isRest) {
			$toAdd = array();
			while($durationDif>0) {
				$max = $this->getMax2Pow($durationDif);
				array_unshift($toAdd, new SOMUSIC_CLASS_Note(-1, array(), array(), null, $max, true, array(), array()));
				$durationDif-=$max;
			}
			if($_REQUEST["isPause"]=="true")
				array_unshift($toAdd, new SOMUSIC_CLASS_Note(-1, array(), array(), null, $duration, true, array(), array()));
			else array_unshift($toAdd, new SOMUSIC_CLASS_Note(-1, array($newNote[0]), array($newNote[1]), null, $duration, false, array(), array()));
			for($i=$noteIndex+1; $i<count($measure->voices[0]); $i++) {
				$n = $measure->voices[0][$i];
				for($j=0; $j<count($n->isTieStart); $j++)
					$instrumentScore->ties[$n->isTieStart[$j]]->firstNote+=count($toAdd)-1;
				for($j=0; $j<count($n->isTieEnd); $j++)
					$instrumentScore->ties[$n->isTieEnd[$j]]->lastNote+=count($toAdd)-1;
			}
			array_splice($measure->voices[0], $noteIndex, 1, $toAdd);
		}
		else if($duration==$note->duration && $_REQUEST["isPause"]=="false") {
			array_push($note->step, $newNote[0]);
			array_push($note->octave, $newNote[1]);
		}
		if($measureIndex==count($instrumentScore->measures)-1){
			for($i=0; $i<count($composition->instrumentsScore); $i++) {
				$lastMeasure = $composition->instrumentsScore[$i]->measures[$measureIndex];
				$clef = $lastMeasure->clef;
				$timeSign = $lastMeasure->timeSignature;
				$keySign = $lastMeasure->keySignature;
				array_push($composition->instrumentsScore[$i]->measures, $this->newMeasure($clef, explode("/", $timeSign), $keySign));
			}
		}
		$cache->set($userId, $composition);
		exit (json_encode($composition));
	}
	
	public function deleteNotes() {
		$toRemove = $_REQUEST["toRemove"];
		$userId = OW::getUser()->getId();
		$cache = new Memcached();
		$cache->addServer("localhost", 11211);
		$composition = $cache->get($userId);
		foreach ($toRemove as $obj) {
			$is = $composition->instrumentsScore[$obj["staveIndex"]];
			$m = $is->measures[$obj["measureIndex"]];
			$note = $m->voices[0][$obj["noteIndex"]];
			$note->step = array();
			$note->octave = array();
			$note->accidental = null;
			$note->isRest = true;
			foreach ($note->isTieStart as $i=>$tieIndex) {
				$tie = $is->ties[$tieIndex];
				$found = false;
				$alreadyTied = false;
				for($i=$tie->firstMeasure; $i<=$tie->lastMeasure && !$found; $i++) {
					$firstMeasure = $is->measures[$i];
					for($j=$tie->firstNote+1; $j<count($firstMeasure->voices[0]) && !$found; $j++) {
						$n = $firstMeasure->voices[0][$j];
						if(!$n->isRest && !($tie->lastMeasure==$i && $tie->lastNote==$j)) {
							$found = true;
							if(!$this->areTied($is, $i, $j, $tie->lastMeasure, $tie->lastNote)) {
								$tie->firstMeasure = $i;
								$tie->firstNote = $j;
								array_push($is->measures[$i]->voices[0][$j]->isTieStart, $tieIndex);
							}
							else $alreadyTied = true;
						}
					}
				}
				if(!$found || $alreadyTied) 
					$this->removeTie($is, $tieIndex);
			}
			foreach ($note->isTieEnd as $i=>$tieIndex) {
				$tie = $is->ties[$tieIndex];
				$found = false;
				$alreadyTied = false;
				for($i=$tie->lastMeasure; $i>=$tie->lastMeasure && !$found; $i--) {
					$lastMeasure = $is->measures[$i];
					for($j=$tie->lastNote-1; $j>=0 && !$found; $j--) {
						$n = $lastMeasure->voices[0][$j];
						if(!$n->isRest && !($tie->firstMeasure==$i && $tie->firstNote==$j)) {
							$found = true;
							if(!$this->areTied($is, $tie->firstMeasure, $tie->firstNote, $i, $j)) {
								$tie->lastMeasure = $i;
								$tie->lastNote = $j;
								array_push($is->measures[$i]->voices[0][$j]->isTieEnd, $tieIndex);
							}
							else $alreadyTied = true;
						}
					}
				}
				if(!$found || $alreadyTied) 
					$this->removeTie($is, $tieIndex);
			}
			$note->isTieStart = [];
			$note->isTieEnd = [];
		}
		$cache->set($userId, $composition);
		exit (json_encode ($composition));
	}
	
	public function addTie() {
		$toTie = $_REQUEST["toTie"];
		$score = $toTie[0]["voiceName"];
		$userId = OW::getUser()->getId();
		$cache = new Memcached();
		$cache->addServer("localhost", 11211);
		$composition = $cache->get($userId);
		for($i=1; $i<count($toTie); $i++){
			if($toTie[$i]["voiceName"] != $score) {
				//error message
				echo(json_encode($composition));
			}
		}
		$firstMeasure = INF;
		$lastMeasure = -INF;
		for($i=0; $i<count($toTie); $i++) {
			if($toTie[$i]["measureIndex"]<$firstMeasure) {
				$firstMeasure = $toTie[$i]["measureIndex"];
				$firstNote = $toTie[$i]["noteIndex"];
			}
			else if($toTie[$i]["measureIndex"]==$firstMeasure) {
				$pos = $toTie[$i]["noteIndex"];
				if($pos<$firstNote) 
					$firstNote = $pos;
			}
			if($toTie[$i]["measureIndex"]>$lastMeasure) {
				$lastMeasure = $toTie[$i]["measureIndex"];
				$lastNote = $toTie[$i]["noteIndex"];
			}
			else if($toTie[$i]["measureIndex"]==$lastMeasure) {
				$pos = $toTie[$i]["noteIndex"];
				if($pos>$lastNote) 
					$lastNote = $pos;
			}
		}
		$instrumentScore = $composition->instrumentsScore[$toTie[0]["staveIndex"]];
		$tieIndex=$this->areTied($instrumentScore, $firstMeasure, $firstNote, $lastMeasure, $lastNote);
		$startNote = $instrumentScore->measures[$firstMeasure]->voices[0][$firstNote];
		$endNote = $instrumentScore->measures[$lastMeasure]->voices[0][$lastNote];
		if($tieIndex<0) {
			$pos = array_push($instrumentScore->ties, new SOMUSIC_CLASS_Tie($firstMeasure, $firstNote, $lastMeasure, $lastNote));
			array_push($startNote->isTieStart, $pos-1);
			array_push($endNote->isTieEnd, $pos-1);
		}
		else $this->removeTie($instrumentScore, $tieIndex);
		$cache->set($userId, $composition);
		exit (json_encode ($composition));
	}
	
	/*public function save() {
		$userId = OW::getUser()->getId();
		$cache = new Memcached();
		$cache->addServer("localhost", 11211);
		$composition = $cache->get($userId);
		
		$cache->delete($userId);
		exit(json_encode(true));
	}*/
	
	public function getComposition() {
		$id = $_REQUEST["id"];
		$userId = OW::getUser()->getId();
		$cache = new Memcached();
		$cache->addServer("localhost", 11211);
		if(ctype_digit($id)) {
			$composition = json_decode(SOMUSIC_BOL_Service::getInstance ()->getScoreByPostId ( $id )["data"]);
			$cache->set($userId, $composition);
		}
		else 
			$composition = $cache->get($userId);
		exit(json_encode($composition));
	}
	
	private function removeTie($instrumentScore, $tieIndex) {
		$tie = $instrumentScore->ties[$tieIndex];
		$firstNote = $instrumentScore->measures[$tie->firstMeasure]->voices[0][$tie->firstNote];
		$lastNote = $instrumentScore->measures[$tie->lastMeasure]->voices[0][$tie->lastNote];
		array_splice($instrumentScore->ties, $tieIndex, 1);
		if(($key = array_search($tieIndex, $firstNote->isTieStart)) !== false)
			array_splice($firstNote->isTieStart, $key, 1);
		if(($key = array_search($tieIndex, $lastNote->isTieEnd)) !== false)
			array_splice($lastNote->isTieEnd, $key, 1);
		for($i=0; $i<count($instrumentScore->measures); $i++) {
			$m = $instrumentScore->measures[$i];
			for($j=0; $j<count($m->voices[0]); $j++) {
				$note = $m->voices[0][$j];
				for($k=0; $k<count($note->isTieStart); $k++) {
					if($note->isTieStart[$k]>$tieIndex)
						$note->isTieStart[$k]--;
				}
				for($k=0; $k<count($note->isTieEnd); $k++) {
					if($note->isTieEnd[$k]>$tieIndex)
						$note->isTieEnd[$k]--;
				}
			}
		}
	}
	
	private function areTied($instrumentScore, $firstMeasure, $firstNote, $lastMeasure, $lastNote) {
		foreach ($instrumentScore->ties as $i=>$tie) {
			if($tie->firstMeasure==$firstMeasure && $tie->firstNote==$firstNote 
					&& $tie->lastMeasure==$lastMeasure && $tie->lastNote==$lastNote)
				return $i;
		}
		return -1;
	}
	
	private function newMeasure($clef, $timeSign, $keySign) {
		$measure = new SOMUSIC_CLASS_Measure(-1, $clef, $keySign, implode("/", $timeSign), array());
		$voice = array();
		for($j=0; $j<intval($timeSign[0]); $j++) {
			$pause = new SOMUSIC_CLASS_Note(-1, array(), array(), NULL, 64/intval($timeSign[1]), true, array(), array());
			array_push($voice, $pause);
		}
		array_push($measure->voices, $voice);
		return $measure;
	}
	
	private function getMax2Pow($num) {
		$max = 1;
		for($i=0, $pow=1; $pow<=$num; $i++, $pow=pow(2, $i))
			$max = $pow;
		return $max;
	}
	
}
