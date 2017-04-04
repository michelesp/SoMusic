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
		
		$this->assign('assignments', $assignments);
		$this->assign("isAdmin", $isAdmin);
		$this->assign("groupId", $groupId);
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