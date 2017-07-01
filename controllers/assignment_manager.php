<?php

class SOMUSIC_CTRL_AssignmentManager extends OW_ActionController {
	private $service;
	
	public function __construct() {
		$this->service = SOMUSIC_BOL_Service::getInstance();
	}
	
	public function newAssignment() {
		if(!isset($_REQUEST["groupId"]) || !isset($_REQUEST["name"]) || !isset($_REQUEST["isMultiUser"]))
			exit(json_encode(false));
		//$assignment = new SOMUSIC_CLASS_Assignment($_REQUEST["groupId"], $_REQUEST["name"], $_REQUEST["isMultiUser"]=="true");
		$assignment = array("group_id"=>$_REQUEST["groupId"], "name"=>$_REQUEST["name"], "is_multi_user"=>intval($_REQUEST["isMultiUser"]));
		OW::getSession()->set("newAssignment", json_encode($assignment));
		//exit(json_encode($assignment));
		exit(json_encode(true));
	}
	
	public function saveNewAssignment() {
		$assignment = (object)json_decode(OW::getSession()->get("newAssignment"));
		$editor = new SOMUSIC_CTRL_Editor();
		$composition = $editor->getComposition();
		//$composition = SOMUSIC_CLASS_Composition::getCompositionObject($_REQUEST["composition"]);
		$this->service->addAssignment($assignment->name, $assignment->group_id, $assignment->is_multi_user, json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));
		OW::getSession()->delete("newAssignment");
		exit(json_encode(true));
	}
	
	public function commitExecution() {
		$assignmentId = OW::getSession()->get("assignmentId");
		if(!isset($assignmentId))
			exit(json_encode(false));
		$assignment = $this->service->getAssignment($assignmentId);
		$group = GROUPS_BOL_Service::getInstance()->findGroupById($assignment->group_id);
		$userId = OW::getUser()->getId();
		if($assignment->mode==1 && $userId!=$group->userId)
			exit(json_encode(true));		//multi-user mode
		else if($assignment->mode==1 && $userId==$group->userId)
			$this->service->closeAssignment($assignmentId);		//multi-user mode
		$editor = new SOMUSIC_CTRL_Editor();
		$composition = $editor->getComposition();
		$oldExecution = $this->service->getExecutionByAssignmentAndUser($assignmentId, $userId);
		if(!isset($oldExecution))
			$this->service->addAssignmentExecution($assignmentId, json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));
		else $this->service->updateComposition($oldExecution->composition_id, json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));
		OW::getSession()->delete("assignmentId");
		exit(json_encode(true));
	}
	
	public function editExecution() {
		$executionId = OW::getSession()->get("executionId");
		if(!isset($executionId))
			exit(json_encode(false));
		$execution = $this->service->getExecution($executionId);
		$composition = $this->service->getComposition($execution->composition_id);
		$editor = new SOMUSIC_CTRL_Editor();
		$newComposition = $editor->getComposition();
		$this->service->updateComposition($composition->id, json_encode($newComposition->instrumentsScore), json_encode($newComposition->instrumentsUsed));
		OW::getSession()->delete("assignmentId");
		OW::getSession()->delete("executionId");
		exit(json_encode(true));
	}
	
	public function removeAssignment() {
		if(!isset($_REQUEST["id"]))
			exit(json_encode(false));
		$this->service->removeAssignment($_REQUEST["id"]);
		exit(json_encode(true));
	}
	
	public function closeAssignment() {
		if(!isset($_REQUEST["id"]))
			exit(json_encode(false));
		$this->service->closeAssignment($_REQUEST["id"]);
		exit(json_encode(true));
	}
	
	public function saveComment() {
		if(!isset($_REQUEST["id"]) || !isset($_REQUEST["comment"]))
			exit(json_encode(false));
		$this->service->setExecutionComment($_REQUEST["id"], $_REQUEST["comment"]);
		exit(json_encode(true));
	}
	
	public function completeAssignment() {
		if(!isset($_REQUEST["assignmentId"]))
			exit(json_encode(false));
		$assignmnet = $this->service->getAssignment($_REQUEST["assignmentId"]);
		if(isset($_REQUEST["executionId"]) && strlen($_REQUEST["executionId"])>0) {
			$execution = $this->service->getExecution($_REQUEST["executionId"]);
			$compositionId = $execution->composition_id;
			OW::getSession()->set("executionId", $_REQUEST["executionId"]);
		}
		else $compositionId = $assignmnet->composition_id;
		$composition = SOMUSIC_CLASS_Composition::getCompositionObject($this->service->getComposition($compositionId));
		$id = null;
		if($assignmnet->mode==1) {
			$id = "groupId#".$assignmnet->group_id;
			OW::getSession()->set("isClose", $assignmnet->close);
		}
		$editor = new SOMUSIC_CTRL_Editor(false, $id);
		if($assignmnet->mode==1 && $editor->isCompositionInCache($composition))
			$editor->loadDataFromCache($composition);
		else $editor->setComposition($composition);
		OW::getSession()->set("assignmentId", $_REQUEST["assignmentId"]);
		exit(json_encode(true));
	}
	
	public function makeCorrection() {
		if(!isset($_REQUEST["executionId"]))
			exit(json_encode(false));
		$executionId = $_REQUEST["executionId"];
		$execution = $this->service->getExecution($executionId);
		$composition = $this->service->getComposition($execution->composition_id);
		$editor = new SOMUSIC_CTRL_Editor();
		$newComposition = $editor->getComposition();
		$this->service->updateComposition($composition->id, json_encode($newComposition->instrumentsScore), json_encode($newComposition->instrumentsUsed));
		exit(json_encode(true));
	}
	
}