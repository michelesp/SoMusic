<?php

class SOMUSIC_CMP_InstrumentsTable extends OW_Component {
	
	public function __construct($users, $instrumentsTable) {
		//OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'instruments_table.js', 'text/javascript');
		
		$tableHeader = array("Name", "Type");
		if(count($users)>1)
			array_push($tableHeader, "User");
		$toRender = array();
		foreach ($instrumentsTable as $row)
			array_push($toRender, array("name"=>$row["name"], "type"=>$row["type"], "userId"=>$row["user"]));
		$this->assign("tableHeader", $tableHeader);
		$this->assign("instrumentGroups", SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups());
		$this->assign("tableRows", $toRender);
		$this->assign("users", $users);
		$this->assign("usersCount", count($users));
		
		$this->assign("addURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_InstrumentsTable', 'addInstrument'));
		$this->assign("deleteURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_InstrumentsTable', 'deleteInstrument'));
		$this->assign("getURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_InstrumentsTable', 'getTable'));
		$this->assign("commitChangeURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_InstrumentsTable', 'commitChange'));
		$this->assign("changeTypeURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_InstrumentsTable', 'changeType'));
		$this->assign("changeUserURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_InstrumentsTable', 'changeUser'));
	}

}