<?php

class SOMUSIC_CMP_AssignmentsWidget extends BASE_CLASS_Widget {

	public function __construct( BASE_CLASS_WidgetParameter $params ) {
		parent::__construct();
		
		OW::getDocument ()->addStyleSheet ( "https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'assignments.js', 'text/javascript' );
		OW::getDocument ()->addScript ( "https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js", 'text/javascript' );
		SOMUSIC_CMP_InstrumentsTable::addScripts();
		SOMUSIC_CMP_Editor::addScripts();
		
		$groupId = $params->additionalParamList["entityId"];
		$assignments = SOMUSIC_BOL_Service::getInstance()->getAssignmentsByGroupId($groupId);
		
		$group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
		$isAdmin = $group->userId == OW::getUser()->getId();
		
		OW::getDocument ()->addOnloadScript ("");
		
		$userId = OW::getUser()->getId();
		
		$assignment1 = array();
		foreach ($assignments as $a) {
			$execution = SOMUSIC_BOL_Service::getInstance()->getExecutionByAssignmentAndUser($a["id"], $userId);
			if(isset($execution["id"])) {
				$composition = json_decode(SOMUSIC_BOL_Service::getInstance()->getComposition($execution["composition_id"])->data);
			}
			else $composition = json_decode(SOMUSIC_BOL_Service::getInstance()->getComposition($a["composition_id"])->data);
			//if(!is_object($composition))
			//	$composition = $this->getCompositionObject($composition);
			$timeSignature = $composition->instrumentsScore[0]->measures[0]->timeSignature;
			$keySignature = $composition->instrumentsScore[0]->measures[0]->keySignature;
			$instrumentsUsed = $composition->instrumentsUsed;
			array_push($assignment1, array("id"=>$a["id"],
					"isMultiUser"=>$a["mode"],
					"name"=>$a["name"],
					"timeSignature"=>$timeSignature,
					"keySignature"=>$keySignature,
					"instrumentsUsed"=>json_encode($instrumentsUsed),
					"composition"=>json_encode($composition),
					"close"=>$a["close"],
					"executionId"=>(isset($execution["id"])?$execution["id"]:-1)
			));
		}
		
		$this->assign('assignments', $assignment1);
		$this->assign("isAdmin", $isAdmin);
		$this->assign("groupId", $groupId);
		
		$this->assign("saveComment", OW::getRouter()->urlFor('SOMUSIC_CTRL_AssignmentManager', 'saveComment'));
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