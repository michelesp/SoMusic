<?php

class SOMUSIC_CMP_InstrumentsTable extends OW_Component {
	
	public function __construct($users, $instrumentsTable) {
		$tableHeader = array("Name", "Type");
		if(count($users)>1)
			array_push($tableHeader, "User");
		$toRender = array();
		foreach ($instrumentsTable as $row)
			array_push($toRender, array("name"=>$row["name"], "type"=>$row["type"]));
		$this->assign("tableHeader", $tableHeader);
		$this->assign("instrumentGroups", SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups());
		$this->assign("tableRows", $toRender);
		$this->assign("users", $users);
		$this->assign("usersCount", count($users));
	}
	
	public static function addScripts() {
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'instruments_table.js', 'text/javascript' );
	}
}