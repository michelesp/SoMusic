<?php

class SOMUSIC_CTRL_NewAssignment extends OW_ActionController {

	public function newAssignment() {
		if(!isset($_REQUEST["groupId"]) || !isset($_REQUEST["name"]) || !isset($_REQUEST["isMultiUser"]))
			exit(json_encode(false));
		$assignment = new SOMUSIC_CLASS_Assignment($_REQUEST["groupId"], $_REQUEST["name"], $_REQUEST["isMultiUser"]);
		OW::getSession()->set("newAssignment", json_encode($assignment));
		exit(json_encode(true));
	}
	
}