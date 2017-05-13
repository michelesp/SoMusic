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
		
		$compositionId = SOMUSIC_CLASS_Composition::getCompositionObject($composition)->getId();

		$this->assign("assignmentId", $assignmentId);
		
		$originalComposition = SOMUSIC_CLASS_Composition::getCompositionObject(SOMUSIC_BOL_Service::getInstance()->getComposition($assignment->composition_id));
		$this->assign("executionId", $executionId);

		$userId = OW::getUser()->getId();
		$group = GROUPS_BOL_Service::getInstance()->findGroupById($assignment->group_id);
		$this->assign("isAdmin", ($userId==$group->userId));
	}
	
}