<?php

class SOMUSIC_CTRL_AssignmentManager extends OW_ActionController {
	private $service;
	
	public function __construct() {
		$this->service = SOMUSIC_BOL_Service::getInstance();
	}
	
	public function newAssignment() {
		if(!isset($_REQUEST["groupId"]) || !isset($_REQUEST["name"]) || !isset($_REQUEST["isMultiUser"]))
			exit(json_encode(false));
		$assignment = new SOMUSIC_CLASS_Assignment($_REQUEST["groupId"], $_REQUEST["name"], $_REQUEST["isMultiUser"]=="true");
		OW::getSession()->set("newAssignment", json_encode($assignment));
		exit(json_encode(true));
	}
	
	public function saveNewAssignment() {
		if(!isset($_REQUEST["composition"]))
			exit(json_encode(false));
		$assignment = (object)json_decode(OW::getSession()->get("newAssignment"));
		$composition = $this->getCompositionObject($_REQUEST["composition"]);				//TODO: usare quello della classe Composition
		$this->service->addAssignment($assignment->name, $assignment->group_id, $assignment->is_multi_user, json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));
		OW::getSession()->delete("newAssignment");
		exit(json_encode(true));
	}
	
	public function commitExecution() {
		if(!isset($_REQUEST["composition"]) || !isset($_REQUEST["assignmentId"]))
			exit(json_encode(false));
		$assignmentId = intval($_REQUEST["assignmentId"]);
		$assignment = $this->service->getAssignment($assignmentId);
		$group = GROUPS_BOL_Service::getInstance()->findGroupById($assignment->group_id);
		$userId = OW::getUser()->getId();
		if($assignment->mode==1 && $userId!=$group->userId)
			exit(json_encode(true));		//multi-user mode
		else if($assignment->mode==1 && $userId==$group->userId)
			$this->service->closeAssignment($assignmentId);		//multi-user mode
		$composition = $this->getCompositionObject($_REQUEST["composition"]);				//TODO: usare quello in Composition
		$oldExecution = $this->service->getExecutionByAssignmentAndUser($assignmentId, $userId);
		if(!isset($oldExecution))
			$this->service->addAssignmentExecution($assignmentId, json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));
		else $this->service->updateComposition($oldExecution->composition_id, json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));
		exit(json_encode(true));
	}
	
	public function editExecution() {
		if(!isset($_REQUEST["executionId"]) || !isset($_REQUEST["composition"]))
			exit(json_encode(false));
		$execution = $this->service->getExecution(intval($_REQUEST["executionId"]));
		$composition = $this->service->getComposition($execution->composition_id);
		$newComposition = $this->getCompositionObject($_REQUEST["composition"]);		//TODO: usare quello in Composition
		$this->service->updateComposition($composition->id, json_encode($newComposition->instrumentsScore), json_encode($newComposition->instrumentsUsed));
		exit(json_encode(true));
	}
	
	public function removeAssignment() {
		if(!isset($_REQUEST["id"]))
			exit(json_encode(false));
		$id = intval($_REQUEST["id"]);
		$this->service->removeAssignment($id);
		exit(json_encode(true));
	}
	
	public function closeAssignment() {
		if(!isset($_REQUEST["id"]))
			exit(json_encode(false));
		$id = intval($_REQUEST["id"]);
		$this->service->closeAssignment($id);
		exit(json_encode(true));
	}
	
	public function saveComment() {
		if(!isset($_REQUEST["id"]) || !isset($_REQUEST["comment"]))
			exit(json_encode(false));
		$id = intval($_REQUEST["id"]);
		$this->service->setExecutionComment($id, $_REQUEST["comment"]);
		exit(json_encode(true));
	}
	
	//TODO: spostare in Composition
	private function getCompositionObject($compositionArray) {
		$composition = new SOMUSIC_CLASS_Composition(-1, $compositionArray["name"], $compositionArray["user_c"], $compositionArray["timestamp_c"], $compositionArray["user_m"], $compositionArray["timestamp_m"], array(), $compositionArray["instrumentsUsed"]);
		foreach ($compositionArray["instrumentsScore"] as $instrumentScoreArray) {
			$instrumentScore = new SOMUSIC_CLASS_InstrumentScore($instrumentScoreArray["default_clef"], $instrumentScoreArray["name"], array(), array(), $instrumentScoreArray["instrument"], $instrumentScoreArray["user"]);
			foreach ($instrumentScoreArray["measures"] as $measureArray) {
				$voices = array();
				foreach ($measureArray["voices"] as $voiceArray) {
					$voice = array();
					foreach ($voiceArray as $noteArray)
						array_push($voice, new SOMUSIC_CLASS_Note($noteArray["id"], (isset($noteArray["step"])?$noteArray["step"]:array()), (isset($noteArray["octave"])?$noteArray["octave"]:array()), $noteArray["accidental"], $noteArray["duration"], $noteArray["isRest"]=="true", (isset($noteArray["isTieStart"])?$noteArray["isTieStart"]:array()), (isset($noteArray["isTieEnd"])?$noteArray["isTieEnd"]:array())));
					array_push($voices, $voice);
				}
				$measure = new SOMUSIC_CLASS_Measure($measureArray["id"], $measureArray["clef"], $measureArray["keySignature"], $measureArray["timeSignature"], $voices);
				array_push($instrumentScore->measures, $measure);
			}
			if(isset($instrumentScoreArray["ties"]))
				foreach ($instrumentScoreArray["ties"] as $tieArray)
					array_push($instrumentScore->ties, new SOMUSIC_CLASS_Tie($tieArray["firstMeasure"], $tieArray["firstNote"], $tieArray["lastMeasure"], $tieArray["lastNote"]));
			array_push($composition->instrumentsScore, $instrumentScore);
		}
		return $composition;
	}
	
}