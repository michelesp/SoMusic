<?php
class SOMUSIC_CTRL_Editor extends OW_ActionController {
	private $id;
	private $userId;
	private $cache;
	private $composition;

	public function __construct() {
		$this->userId = OW::getUser ()->getId ();
		$this->cache = new Memcached ();
		$this->cache->addServer("localhost", 11211);
		$assignment = OW::getSession()->get("newAssignment");
		if($assignment!=null){
			$assignmentObj = json_decode($assignment);
			if($assignmentObj->group_id==null && $assignmentObj->name==null && $assignmentObj->is_multi_user==null)
				$this->id = $this->userId;
			else $this->id = json_encode((object)array("groupId"=>$assignmentObj->groupId, "name"=>$assignmentObj->name));
		}
		else $this->id = $this->userId;
		$this->composition = $this->cache->get($this->id);
	}

	public function __destruct() {
		$this->cache->set ($this->id, $this->composition);
	}
	
	/*public function __construct() {
	 $this->userId = OW::getUser ()->getId ();
	 $this->cache = new Memcached ();
	 $this->cache->addServer("localhost", 11211);
	 $assignment = OW::getSession()->get("newAssignment");
	 if($assignment!=null){
	 $assignmentObj = (object)json_decode($assignment);
	 if($assignmentObj->group_id==null || $assignmentObj->name==null)
	 	$this->id = $this->userId;
	 	else $this->id = $assignment;
	 	}
	 	else $this->id = $this->userId;
	 	//$this->composition = $this->cache->get($this->id);
	 	$composition = OW::getSession()->get($this->id);
	 	if($composition!=null)
	 		$this->composition = unserialize($composition);
	 		else $this->composition = null;
	
	 		}
	
	 		public function __destruct() {
	 		OW::getSession()->set($this->id, serialize($this->composition));
	 		//$this->cache->set ($this->id, $this->composition);
	 		}*/

	public function initEditor($instrumentsUsed, $timeSignature, $keySignature) {
		// TODO: bloccare chiamata rest
		$this->composition = new SOMUSIC_CLASS_Composition (-1, "", $this->userId, - 1, $this->userId, -1, array (), $instrumentsUsed);
		foreach ( $instrumentsUsed as $instrument ) {
			for($i = 0; $i < count ( $instrument ["scoresClef"] ); $i ++) {
				$is = new SOMUSIC_CLASS_InstrumentScore ($instrument ["scoresClef"] [$i], $instrument ["labelName"] . "#score" . $i, array (), array (), $instrument ["name"], $instrument["user"] );
				array_push ( $is->measures, $this->newMeasure ( $instrument ["scoresClef"] [$i], explode ( "/", $timeSignature ), $keySignature ) );
				array_push ( $this->composition->instrumentsScore, $is );
			}
		}
		return $this->composition;
	}

	public function setComposition($composition) {
		// TODO: bloccare chiamata rest
		$this->composition = $composition;
	}

	public function addNote() {
		if(!isset($_REQUEST["staveIndex"]) || !isset($_REQUEST["measureIndex"]) || !isset($_REQUEST["noteIndex"])
				|| !isset($_REQUEST["newNote"]) || !isset($_REQUEST["duration"]))
			$this->error("error insertion note");
		$staveIndex = intval ( $_REQUEST ["staveIndex"] );
		$measureIndex = intval ( $_REQUEST ["measureIndex"] );
		$noteIndex = intval ( $_REQUEST ["noteIndex"] );
		$newNote = explode ( "/", $_REQUEST ["newNote"] );
		$duration = 64 / intval ( $_REQUEST ["duration"] );
		$userId = OW::getUser ()->getId ();
		if(!is_object($this->composition))
			$this->composition = $this->getCompositionObject($this->composition);
		$instrumentScore = $this->composition->instrumentsScore [$staveIndex];
		if($instrumentScore->user!=$userId)
			$this->error("permission denied");
		$measure = $instrumentScore->measures [$measureIndex];
		$note = $measure->voices [0] [$noteIndex];
		if ($duration > $note->duration)
			$this->error("error note duration");
		$durationDif = $note->duration - $duration;
		if ($note->isRest) {
			$toAdd = array ();
			while ( $durationDif > 0 ) {
				$max = $this->getMax2Pow ( $durationDif );
				array_unshift ( $toAdd, new SOMUSIC_CLASS_Note ( - 1, array (), array (), null, $max, true, array (), array () ) );
				$durationDif -= $max;
			}
			if ($_REQUEST ["isPause"] == "true")
				array_unshift ( $toAdd, new SOMUSIC_CLASS_Note ( - 1, array (), array (), null, $duration, true, array (), array () ) );
			else array_unshift ( $toAdd, new SOMUSIC_CLASS_Note ( - 1, array($newNote [0]), array($newNote [1]), null, $duration, false, array (), array () ) );
			for($i = $noteIndex + 1; $i < count ( $measure->voices [0] ); $i ++) {
				$n = $measure->voices [0] [$i];
				for($j = 0; $j < count ( $n->isTieStart ); $j ++)
					$instrumentScore->ties [$n->isTieStart [$j]]->firstNote += count ( $toAdd ) - 1;
				for($j = 0; $j < count ( $n->isTieEnd ); $j ++)
					$instrumentScore->ties [$n->isTieEnd [$j]]->lastNote += count ( $toAdd ) - 1;
			}
			array_splice ( $measure->voices [0], $noteIndex, 1, $toAdd );
		}
		else if ($duration == $note->duration && $_REQUEST ["isPause"] == "false") {
			array_push ( $note->step, $newNote [0] );
			array_push ( $note->octave, $newNote [1] );
		}
		if ($measureIndex == count ( $instrumentScore->measures ) - 1) {
			for($i = 0; $i < count ( $this->composition->instrumentsScore ); $i ++) {
				$lastMeasure = $this->composition->instrumentsScore [$i]->measures [$measureIndex];
				$clef = $lastMeasure->clef;
				$timeSign = $lastMeasure->timeSignature;
				$keySign = $lastMeasure->keySignature;
				array_push ( $this->composition->instrumentsScore [$i]->measures, $this->newMeasure ( $clef, explode ( "/", $timeSign ), $keySign ) );
			}
		}
		exit(json_encode($this->composition));
	}
	
	public function deleteNotes() {
		if(!isset($_REQUEST["toRemove"]))
			$this->error("error notes deletion");
		if(!is_object($this->composition))
			$this->composition = $this->getCompositionObject($this->composition);
		foreach ($_REQUEST ["toRemove"] as $obj) {
			$is = $this->composition->instrumentsScore [$obj ["staveIndex"]];
			if($is->user!=$this->userId)
				continue;
			$m = $is->measures [$obj ["measureIndex"]];
			$note = $m->voices [0] [$obj ["noteIndex"]];
			$note->step = array ();
			$note->octave = array ();
			$note->accidental = null;
			$note->isRest = true;
			foreach ( $note->isTieStart as $i => $tieIndex ) {
				$tie = $is->ties [$tieIndex];
				$found = false;
				$alreadyTied = false;
				for($i = $tie->firstMeasure; $i <= $tie->lastMeasure && ! $found; $i ++) {
					$firstMeasure = $is->measures [$i];
					for($j = $tie->firstNote + 1; $j < count ( $firstMeasure->voices [0] ) && ! $found; $j ++) {
						$n = $firstMeasure->voices [0] [$j];
						if (! $n->isRest && ! ($tie->lastMeasure == $i && $tie->lastNote == $j)) {
							$found = true;
							if (! $this->areTied ( $is, $i, $j, $tie->lastMeasure, $tie->lastNote )) {
								$tie->firstMeasure = $i;
								$tie->firstNote = $j;
								array_push ( $is->measures [$i]->voices [0] [$j]->isTieStart, $tieIndex );
							} else $alreadyTied = true;
						}
					}
				}
				if (! $found || $alreadyTied)
					$this->removeTie ( $is, $tieIndex );
				}
				foreach ( $note->isTieEnd as $i => $tieIndex ) {
					$tie = $is->ties [$tieIndex];
					$found = false;
					$alreadyTied = false;
					for($i = $tie->lastMeasure; $i >= $tie->lastMeasure && ! $found; $i --) {
						$lastMeasure = $is->measures [$i];
						for($j = $tie->lastNote - 1; $j >= 0 && ! $found; $j --) {
							$n = $lastMeasure->voices [0] [$j];
							if (! $n->isRest && ! ($tie->firstMeasure == $i && $tie->firstNote == $j)) {
								$found = true;
							if (! $this->areTied ( $is, $tie->firstMeasure, $tie->firstNote, $i, $j )) {
								$tie->lastMeasure = $i;
								$tie->lastNote = $j;
								array_push ( $is->measures [$i]->voices [0] [$j]->isTieEnd, $tieIndex );
							} else $alreadyTied = true;
						}
					}
				}
				if (! $found || $alreadyTied)
					$this->removeTie ( $is, $tieIndex );
			}
			$note->isTieStart = [ ];
			$note->isTieEnd = [ ];
		}
		exit(json_encode($this->composition));
	}
	
	public function addTie() {
		if(!isset($_REQUEST ["toTie"]))
			$this->error("error insertion tie");
		$toTie = $_REQUEST ["toTie"];
		$score = $toTie [0] ["voiceName"];
		$userId = OW::getUser ()->getId ();
		if(!is_object($this->composition))
			$this->composition = $this->getCompositionObject($this->composition);
		for($i = 1; $i < count ( $toTie ); $i ++) {
			if ($toTie [$i] ["voiceName"] != $score) {
				// error message
				echo (json_encode ( $this->composition ));
			}
		}
		$firstMeasure = INF;
		$lastMeasure = - INF;
		for($i = 0; $i < count ( $toTie ); $i ++) {
			if ($toTie [$i] ["measureIndex"] < $firstMeasure) {
				$firstMeasure = $toTie [$i] ["measureIndex"];
				$firstNote = $toTie [$i] ["noteIndex"];
			}
			else if ($toTie [$i] ["measureIndex"] == $firstMeasure) {
				$pos = $toTie [$i] ["noteIndex"];
				if ($pos < $firstNote)
					$firstNote = $pos;
			}
			if ($toTie [$i] ["measureIndex"] > $lastMeasure) {
				$lastMeasure = $toTie [$i] ["measureIndex"];
				$lastNote = $toTie [$i] ["noteIndex"];
			}
			else if ($toTie [$i] ["measureIndex"] == $lastMeasure) {
				$pos = $toTie [$i] ["noteIndex"];
				if ($pos > $lastNote)
					$lastNote = $pos;
			}
		}
		$instrumentScore = $this->composition->instrumentsScore [$toTie [0] ["staveIndex"]];
		if($instrumentScore->user!=$userId)
			exit(json_encode($this->composition));
		$tieIndex = $this->areTied ( $instrumentScore, $firstMeasure, $firstNote, $lastMeasure, $lastNote );
		$startNote = $instrumentScore->measures [$firstMeasure]->voices [0] [$firstNote];
		$endNote = $instrumentScore->measures [$lastMeasure]->voices [0] [$lastNote];
		if ($tieIndex < 0) {
			$pos = array_push ( $instrumentScore->ties, new SOMUSIC_CLASS_Tie ( $firstMeasure, $firstNote, $lastMeasure, $lastNote ) );
			array_push ( $startNote->isTieStart, $pos - 1 );
			array_push ( $endNote->isTieEnd, $pos - 1 );
		} else $this->removeTie ( $instrumentScore, $tieIndex );
		exit ( json_encode ( $this->composition ) );
	}

	public function getComposition() {
		$id = $_REQUEST ["id"];
		if (ctype_digit ( $id ))
			$this->composition = json_decode ( SOMUSIC_BOL_Service::getInstance ()->getScoreByPostId ( $id ) ["data"] );
		exit (json_encode($this->composition));
	}

	private function getCompositionObject($compositionArray) {
		$this->composition = new SOMUSIC_CLASS_Composition($compositionArray["id"], $compositionArray["name"], $compositionArray["user_c"], $compositionArray["timestamp_c"], $compositionArray["user_m"], $compositionArray["timestamp_m"], array(), $compositionArray["instrumentsUsed"]);
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
				array_push($this->composition->instrumentsScore, $instrumentScore);
		}
		return $this->composition;
	}

	private function removeTie($instrumentScore, $tieIndex) {
		$tie = $instrumentScore->ties [$tieIndex];
		$firstNote = $instrumentScore->measures [$tie->firstMeasure]->voices [0] [$tie->firstNote];
		$lastNote = $instrumentScore->measures [$tie->lastMeasure]->voices [0] [$tie->lastNote];
		array_splice ( $instrumentScore->ties, $tieIndex, 1 );
		if (($key = array_search ( $tieIndex, $firstNote->isTieStart )) !== false)
			array_splice ( $firstNote->isTieStart, $key, 1 );
		if (($key = array_search ( $tieIndex, $lastNote->isTieEnd )) !== false)
			array_splice ( $lastNote->isTieEnd, $key, 1 );
		for($i = 0; $i < count ( $instrumentScore->measures ); $i ++) {
			$m = $instrumentScore->measures [$i];
			for($j = 0; $j < count ( $m->voices [0] ); $j ++) {
				$note = $m->voices [0] [$j];
				for($k = 0; $k < count ( $note->isTieStart ); $k ++) {
					if ($note->isTieStart [$k] > $tieIndex)
						$note->isTieStart [$k] --;
				}
				for($k = 0; $k < count ( $note->isTieEnd ); $k ++) {
					if ($note->isTieEnd [$k] > $tieIndex)
						$note->isTieEnd [$k] --;
				}
			}
		}
	}
	
	private function areTied($instrumentScore, $firstMeasure, $firstNote, $lastMeasure, $lastNote) {
		foreach ( $instrumentScore->ties as $i => $tie ) {
			if ($tie->firstMeasure == $firstMeasure && $tie->firstNote == $firstNote && $tie->lastMeasure == $lastMeasure && $tie->lastNote == $lastNote)
				return $i;
		}
		return - 1;
	}
	
	private function newMeasure($clef, $timeSign, $keySign) {
		$measure = new SOMUSIC_CLASS_Measure ( - 1, $clef, $keySign, implode ( "/", $timeSign ), array () );
		$voice = array ();
		for($j = 0; $j < intval ( $timeSign [0] ); $j ++) {
			$pause = new SOMUSIC_CLASS_Note ( - 1, array (), array (), NULL, 64 / intval ( $timeSign [1] ), true, array (), array () );
			array_push ( $voice, $pause );
		}
		array_push ( $measure->voices, $voice );
		return $measure;
	}
	
	private function getMax2Pow($num) {
		$max = 1;
		for($i = 0, $pow = 1; $pow <= $num; $i ++, $pow = pow ( 2, $i ))
			$max = $pow;
			return $max;
	}
	
	private function error($errorMsg) {
		/*$composition = (array)$this->composition;
		 $composition["error"] = $errorMsg;
		 exit(json_encode((object)$composition));*/
	}
	
}
