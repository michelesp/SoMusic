<?php

class SOMUSIC_CMP_AssignmentDetails extends OW_Component {
	
	public function __construct($assignmentId) {
		$service = SOMUSIC_BOL_Service::getInstance();
		$assignment = $service->getAssignment($assignmentId);
		$executions = $service->getExecutionsByAssignmentId($assignmentId);
		$usersId = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($assignment->group_id);
		$users = array();
		$userId = OW::getUser()->getId();
		foreach ($usersId as $id) 
			if($userId!=$id)
				array_push($users, BOL_UserService::getInstance()->findByIdWithoutCache($id));
		usort($users, array($this, "cmpUser"));
		$compositions = array();
		foreach ($executions as $ex) {
			$composition = $service->getComposition($ex->composition_id);
			$composition = SOMUSIC_CLASS_Composition::getCompositionObject($composition);
			$compositions[$ex->user_id] = array("executionId"=>$ex->id,
					"timestamp_c"=>str_replace(" ", "&nbsp;&nbsp;&nbsp;",date("H:i d/m/Y",strtotime($composition->getTimestampC()))),
					"timestamp_m"=>str_replace(" ", "&nbsp;&nbsp;&nbsp;",date("H:i d/m/Y",strtotime($composition->timestamp_m))),
					"compositionId"=>$composition->getId(),
					"comment"=>json_encode($ex->comment)
			);
		}
		$this->assign("id", $assignmentId);
		$this->assign("name", $assignment->name);
		$this->assign("users", $users);
		$this->assign("compositions", $compositions);
	}
	
	private function cmpUser($a, $b) {
		return strcmp($a->username, $b->username);
	}
	
}