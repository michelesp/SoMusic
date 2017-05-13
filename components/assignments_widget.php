<?php

class SOMUSIC_CMP_AssignmentsWidget extends BASE_CLASS_Widget {

	public function __construct( BASE_CLASS_WidgetParameter $params ) {
		parent::__construct();
		$service = SOMUSIC_BOL_Service::getInstance();
		
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-grid.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-reboot.min.css');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'bootstrap.min.js', 'text/javascript');
		//OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'assignments.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'assignment_manager.js', 'text/javascript');
		
		$groupId = $params->additionalParamList["entityId"];
		$assignments = $service->getAssignmentsByGroupId($groupId);
		
		$group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
		$isAdmin = $group->userId == OW::getUser()->getId();
		
		$userId = OW::getUser()->getId();
		
		$assignment1 = array();
		foreach ($assignments as $a) {
			$execution = $service->getExecutionByAssignmentAndUser($a->id, $userId);
			if(isset($execution->id))
				$composition = SOMUSIC_CLASS_Composition::getCompositionObject($service->getComposition($execution->composition_id));
			else $composition = SOMUSIC_CLASS_Composition::getCompositionObject($service->getComposition($a->composition_id));
			//if(!is_object($composition))
			//	$composition = $this->getCompositionObject($composition);
			$timeSignature = $composition->instrumentsScore[0]->measures[0]->timeSignature;
			$keySignature = $composition->instrumentsScore[0]->measures[0]->keySignature;
			$instrumentsUsed = $composition->instrumentsUsed;
			array_push($assignment1, array("id"=>$a->id,
					"isMultiUser"=>$a->mode,
					"name"=>$a->name,
					//"timeSignature"=>$timeSignature,
					//"keySignature"=>$keySignature,
					//"instrumentsUsed"=>json_encode($instrumentsUsed),
					//"composition"=>json_encode($composition),
					"compositionId"=>$composition->getId(),
					"close"=>$a->close,
					"executionId"=>(isset($execution->id)?$execution->id:-1)
			));
		}
		
		$this->assign('assignments', $assignment1);
		$this->assign("isAdmin", $isAdmin);
		$this->assign("groupId", $groupId);
		
		$this->assign("removeURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_AssignmentManager', 'removeAssignment'));
		$this->assign("closeURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_AssignmentManager', 'closeAssignment'));
		$this->assign("saveCommentURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_AssignmentManager', 'saveComment'));
		$this->assign("newAssignmentURL", OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_AssignmentManager', 'newAssignment'));
		$this->assign("completeAssignmentURL", OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_AssignmentManager', 'completeAssignment'));
	}

	public static function getStandardSettingValueList() {
		return array(
				self::SETTING_TITLE => "Assignments",
				//self::SETTING_ICON => self::ICON_CALENDAR,
				self::SETTING_SHOW_TITLE => true,
				self::SETTING_WRAP_IN_BOX => true
		);
	}
	
	public static function getAccess()
	{
		return self::ACCESS_ALL;
	}
	
	
}