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
		
		$assignment1 = array();
		foreach ($assignments as $a) {
			//TODO: se esiste un execution usare quello, oppure gestire questa cosa da un altra parte
			$composition = json_decode(SOMUSIC_BOL_Service::getInstance()->getComposition($a["composition_id"])->data);
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
					"close"=>$a["close"]
			));
		}
		
		$this->assign('assignments', $assignment1);
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
	
	/*private function getCompositionObject($compositionArray) {
		$this->composition = new SOMUSIC_CLASS_Composition($compositionArray["id"], $compositionArray["name"], $compositionArray["user_c"], $compositionArray["timestamp_c"], $compositionArray["user_m"], $compositionArray["timestamp_m"], array(), $compositionArray["instrumentsUsed"]);
		foreach ($compositionArray["instrumentsScore"] as $instrumentScoreArray) {
			$instrumentScore = new SOMUSIC_CLASS_InstrumentScore($instrumentScoreArray["default_clef"], $instrumentScoreArray["name"], array(), array(), $instrumentScoreArray["instrument"], $instrumentScoreArray["user"]);
			foreach ($instrumentScoreArray["measures"] as $measureArray) {
				$voices = array();
				foreach ($measureArray["voices"] as $voiceArray) {
					$voice = array();
					foreach ($voiceArray as $noteArray)
						array_push($voice, new SOMUSIC_CLASS_Note($noteArray["id"], $noteArray["step"], $noteArray["octave"], $noteArray["accidental"], $noteArray["duration"], $noteArray["isRest"], $noteArray["isTieStart"], $noteArray["isTieEnd"]));
						array_push($voices, $voice);
				}
				$measure = new SOMUSIC_CLASS_Measure($measureArray["id"], $measureArray["clef"], $measureArray["keySignature"], $measureArray["timeSignature"], $voices);
				array_push($instrumentScore->measures, $measure);
			}
			foreach ($instrumentScoreArray["ties"] as $tieArray)
				array_push($instrumentScore->ties, new SOMUSIC_CLASS_Tie($tieArray["firstMeasure"], $tieArray["firstNote"], $tieArray["lastMeasure"], $tieArray["lastNote"]));
				array_push($this->composition->instrumentsScore, $instrumentScore);
		}
		return $this->composition;
	}*/
	
}