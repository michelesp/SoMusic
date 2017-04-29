<?php

class SOMUSIC_CMP_AssignmentExecutionDetails extends OW_Component {

	public function __construct($assignmentId, $executionId) {
		$assignment = SOMUSIC_BOL_Service::getInstance()->getAssignment($assignmentId);
		$execution = SOMUSIC_BOL_Service::getInstance()->getExecution($executionId);
		$composition = SOMUSIC_BOL_Service::getInstance()->getComposition($execution->composition_id);
		$assignmentTimestampC = str_replace(" ", "&nbsp;&nbsp;&nbsp;",date("H:i d/m/Y",strtotime($assignment->timestamp_c)));
		$executionTimestampC = str_replace(" ", "&nbsp;&nbsp;&nbsp;",date("H:i d/m/Y",strtotime($composition->timestamp_c)));
		$executionTimestampM = str_replace(" ", "&nbsp;&nbsp;&nbsp;",date("H:i d/m/Y",strtotime($composition->timestamp_m)));
		$this->assign("name", $assignment->name);
		$this->assign("assignment_timestamp_c", $assignmentTimestampC);
		$this->assign("execution_timestamp_c", $executionTimestampC);
		$this->assign("execution_timestamp_m", $executionTimestampM);
		$this->assign("comment", $execution->comment);
		
		$composition = json_decode($composition->data);
		$timeSignature = $composition->instrumentsScore[0]->measures[0]->timeSignature;
		$keySignature = $composition->instrumentsScore[0]->measures[0]->keySignature;
		$instrumentsUsed = $composition->instrumentsUsed;
		
		$this->assign("assignmentId", $assignmentId);
		$this->assign("timeSignature", $timeSignature);
		$this->assign("keySignature", $keySignature);
		$this->assign("instrumentsUsed", json_encode($instrumentsUsed));
		$this->assign("composition", json_encode($composition));
		
		$originalComposition = SOMUSIC_BOL_Service::getInstance()->getComposition($assignment->composition_id);
		$this->assign("originalComposition", $originalComposition->data);
	
		$userId = OW::getUser()->getId();
		$group = GROUPS_BOL_Service::getInstance()->findGroupById($assignment->group_id);
		$this->assign("isAdmin", ($userId==$group->userId));
		$this->assign("removeAssignmentURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_AssignmentManager', 'removeAssignment'));
	}
	
}