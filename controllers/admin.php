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
	
	public function instruments() {
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'font-awesome.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-grid.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-reboot.min.css');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'bootstrap.min.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'admin.js', 'text/javascript');
		
		$this->addComponent('menu', $this->getMenu());
		$instGroup = SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups();
		$instrumentsGroups = array();
		foreach ($instGroup as $group) {
			$instrumentsGroups[$group["name"]] = array();
			$instuents = SOMUSIC_BOL_Service::getInstance()->getMusicInstrumentsByGroup($group["id"]);
			foreach ($instuents as $inst)
				array_push($instrumentsGroups[$group["name"]], array("id"=>$inst->id, "name"=>$inst->name, "clefs"=>json_decode($inst->scoresClef), "braces"=>array()));
		}
		$this->assign("instrumentsGroups", $instrumentsGroups);
		$this->assign("addInstrumentURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Admin', 'addInstrument'));
		$this->assign("removeInstrumentURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Admin', 'removeInstrument'));
		$this->assign("editInstrumentURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Admin', 'editInstrument'));
		
		if ( OW::getRequest()->isPost() ) {
			if(isset($_POST["form_name"])) {
				$id = $_POST["instrumentId"];
				$name = $_POST["name"];
				$groupId = $_POST["groupId"];
				$newInstGroup = $_POST["newInstGroup"];
				$scores = $_POST["scores"];
				$braces = array();
				$fBraces = null;
				for($i=0; $i<count($_POST["braces"]); $i++) {
					if(!isset($fBraces))
						$fBraces = $_POST["braces"][$i];
					else {
						$braces[] = array($fBraces, $_POST["braces"][$i]);
						$fBraces = null;
					}
				}
				if($id==-1) {
					SOMUSIC_BOL_Service::getInstance()->addMusicInstrument($name, $groupId, 
							json_encode($scores), json_encode($braces));
				}
				else SOMUSIC_BOL_Service::getInstance()->editMusicInstrument($id, $name, $groupId, 
						json_encode($scores), json_encode($braces));
				$this->redirectToAction('instruments');
			}
			$this->assign("data", json_encode($_POST));
		}
		else $this->assign("data", "");
	}
	
	public function removeInstrument() {
		if(!isset($_REQUEST["id"]))
			exit(json_encode(false));
		SOMUSIC_BOL_Service::getInstance()->removeMusicInsturment($_REQUEST["id"]);
		exit(json_encode(true));
	}

}