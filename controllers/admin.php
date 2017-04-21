<?php

class SOMUSIC_CTRL_Admin extends ADMIN_CTRL_Abstract {
	
	public function getMenu() {
		$item = array();
		$item[0] = new BASE_MenuItem(array());
	
		$item[0]->setLabel("Instruments settings");
		//$item[0]->setIconClass('ow_ic_dashboard');
		$item[0]->setKey('1');
	
		$item[0]->setUrl(OW::getRouter()->urlForRoute('somusic.admin'));
	
		$item[0]->setOrder(1);
	
		/*$item[1] = new BASE_MenuItem(array());
	
		$item[1]->setLabel(OW::getLanguage()->text('groups', 'additional_features'));
		$item[1]->setIconClass('ow_ic_files');
		$item[1]->setKey('2');
		$item[1]->setUrl(
				OW::getRouter()->urlForRoute('groups-admin-additional-features')
				);
	
		$item[1]->setOrder(2);*/
	
		return new BASE_CMP_ContentMenu($item);
	}
	
	public function instruments()
	{
		$this->addComponent('menu', $this->getMenu());
		$instGroup = SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups();
		$instrumentsGroups = array();
		foreach ($instGroup as $group) {
			$instrumentsGroups[$group["name"]] = array();
			$instuents = SOMUSIC_BOL_Service::getInstance()->getMusicInstrumentsByGroup($group["id"]);
			foreach ($instuents as $inst) {
				$staves = SOMUSIC_BOL_Service::getInstance()->getInstrumentScores($inst["id"]);
				$clefs = array();
				foreach ($staves as $s)
					array_push($clefs, $s["clef"]);
				array_push($instrumentsGroups[$group["name"]], array("name"=>$inst["name"], "clefs"=>$clefs, "braces"=>array()));
			}
		}
		$this->assign("instrumentsGroups", $instrumentsGroups);
	}
	
}