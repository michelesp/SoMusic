<?php

class SOMUSIC_CTRL_AssignmentManager extends OW_ActionController {

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
		$userId = OW::getUser ()->getId ();
		$composition = $this->getCompositionObject($_REQUEST["composition"]);
		SOMUSIC_BOL_Service::getInstance()->addAssignment($assignment->name, $assignment->group_id, $userId, $assignment->is_multi_user, json_encode($composition));
		OW::getSession()->delete("newAssignment");
		exit(json_encode(true));
	}
	
	public function commitExecution() {
		if(!isset($_REQUEST["composition"]) || !isset($_REQUEST["assignmentId"]))
			exit(json_encode(false));
		$assignmentId = intval($_REQUEST["assignmentId"]);
		$assignment = SOMUSIC_BOL_Service::getInstance()->getAssignment($assignmentId);
		$group = GROUPS_BOL_Service::getInstance()->findGroupById($assignment->group_id);
		$userId = OW::getUser()->getId();
		if($assignment->mode==1 && $userId!=$group->userId)
			exit(json_encode(true));
		else if($assignment->mode==1 && $userId==$group->userId)
			SOMUSIC_BOL_Service::getInstance()->closeAssignment($assignmentId);
		$composition = $this->getCompositionObject($_REQUEST["composition"]);
		$oldExecution = SOMUSIC_BOL_Service::getInstance()->getExecutionByAssignmentAndUser($assignmentId, $userId);
		if(!array_key_exists("id", $oldExecution))
			SOMUSIC_BOL_Service::getInstance()->addAssignmentExecution($assignmentId, json_encode($composition));
		else SOMUSIC_BOL_Service::getInstance()->updateComposition($oldExecution["composition_id"], json_encode($composition));
		exit(json_encode(true));
	}
	
	public function editExecution() {
		if(!isset($_REQUEST["executionId"]) || !isset($_REQUEST["composition"]))
			exit(json_encode(false));
		$execution = SOMUSIC_BOL_Service::getInstance()->getExecution(intval($_REQUEST["executionId"]));
		$composition = SOMUSIC_BOL_Service::getInstance()->getComposition($execution->composition_id);
		SOMUSIC_BOL_Service::getInstance()->updateComposition($composition->id, json_encode($this->getCompositionObject($_REQUEST["composition"])));
		exit(json_encode(true));
	}
	
	public function removeAssignment() {
		if(!isset($_REQUEST["id"]))
			exit(json_encode(false));
		$id = intval($_REQUEST["id"]);
		SOMUSIC_BOL_Service::getInstance()->removeAssignment($id);
		exit(json_encode(true));
	}
	
	public function closeAssignment() {
		if(!isset($_REQUEST["id"]))
			exit(json_encode(false));
		$id = intval($_REQUEST["id"]);
		SOMUSIC_BOL_Service::getInstance()->closeAssignment($id);
		exit(json_encode(true));
	}
	
	public function saveComment() {
		if(!isset($_REQUEST["id"]) || !isset($_REQUEST["comment"]))
			exit(json_encode(false));
		$id = intval($_REQUEST["id"]);
		SOMUSIC_BOL_Service::getInstance()->setExecutionComment($id, $_REQUEST["comment"]);
		exit(json_encode(true));
	}
	
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