<?php

class SOMUSIC_CTRL_NewAssignment extends OW_ActionController {

	public function newAssignment() {
		if(!isset($_REQUEST["groupId"]) || !isset($_REQUEST["name"]) || !isset($_REQUEST["isMultiUser"]))
			exit(json_encode(false));
		$assignment = new SOMUSIC_CLASS_Assignment($_REQUEST["groupId"], $_REQUEST["name"], $_REQUEST["isMultiUser"]);
		OW::getSession()->set("newAssignment", json_encode($assignment));
		exit(json_encode(true));
	}
	
	public function save() {
		if(!isset($_REQUEST["composition"]))
			exit(json_encode(false));
		$assignment = (object)json_decode(OW::getSession()->get("newAssignment"));
		$userId = OW::getUser ()->getId ();
		SOMUSIC_BOL_Service::getInstance ()->addAssignment($assignment->name, $assignment->group_id, $userId, $assignment->is_multi_user, json_encode($_REQUEST["composition"]));
		OW::getSession()->delete("newAssignment");
		exit(json_encode(true));
	}
	
}