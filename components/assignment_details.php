<?php

class SOMUSIC_CMP_AssignmentDetails extends OW_Component {
	
	public function __construct($assignmentId) {
		$assignment = SOMUSIC_BOL_Service::getInstance()->getAssignment($assignmentId);
		$executions = SOMUSIC_BOL_Service::getInstance()->getAssignmentExecutionsByAssignmentId($assignmentId);
		$usersId = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($assignment->group_id);
		$isMultiUser = $assignment->mode;
		$users = array();
		$userId = OW::getUser()->getId ();
		foreach ($usersId as $id) 
			if($userId!=$id)
				array_push($users, BOL_UserService::getInstance()->findByIdWithoutCache($id));
		usort($users, array($this, "cmpUser"));
		$compositions = array();
		foreach ($executions as $ex) {
			$composition = SOMUSIC_BOL_Service::getInstance()->getComposition($ex["composition_id"]);
			$compositionObj = (object)json_decode($composition->data);
			$timeSignature = $compositionObj->instrumentsScore[0]->measures[0]->timeSignature;
			$keySignature = $compositionObj->instrumentsScore[0]->measures[0]->keySignature;
			$instrumentsUsed = $compositionObj->instrumentsUsed;
			$compositions["#".$ex["user_id"]] = array("executionId"=>$ex["id"],
					"timestamp_c"=>str_replace(" ", "&nbsp;&nbsp;&nbsp;",date("H:i d/m/Y",strtotime($composition->timestamp_c))),
					"timestamp_m"=>str_replace(" ", "&nbsp;&nbsp;&nbsp;",date("H:i d/m/Y",strtotime($composition->timestamp_m))),
					"timeSignature"=>$timeSignature,
					"keySignature"=>$keySignature,
					"instrumentsUsed"=>json_encode($instrumentsUsed),
					"composition"=>json_encode($compositionObj)
			);
		}
		$this->assign("name", $assignment->name);
		$this->assign("isMultiUser", $isMultiUser);
		$this->assign("users", $users);
		$this->assign("compositions", $compositions);
	}
	
	private function cmpUser($a, $b) {
		return strcmp($a->username, $b->username);
	}
	
}