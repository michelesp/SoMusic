<?php

class SOMUSIC_CMP_CompositionSettings extends OW_Component {

	public function __construct() {
		$editor = new SOMUSIC_CTRL_Editor();
		$instrumentsUsed = $editor->getInstrumentsUsed();
		$instrumentsUsed1 = array();
		foreach ($instrumentsUsed as $inst) {
			array_push($instrumentsUsed1, array("name"=>$inst->labelName,
					"instrument"=>$inst->name,
					"username"=>BOL_UserService::getInstance()->findByIdWithoutCache($inst->user)->username));
		}
		$this->assign("instrumentsUsed", $instrumentsUsed1);
	}
	
}