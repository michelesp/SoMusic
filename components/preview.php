<?php

class SOMUSIC_CMP_Preview extends OW_Component {
	
	public function __construct($multiUserMod = false, $groupId = -1) {
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-grid.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-reboot.min.css');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'bootstrap.min.js', 'text/javascript');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin ('somusic')->getStaticCssUrl().'preview.css');
		
		$timeSignatures = array("2/2", "2/4", "3/4", "4/4", "3/8", "6/8");
		$keysignatures = array("C", "Am", "F", "Dm", "Bb", "Gm", "Eb", "Cm", "Ab", "Fm",
				"Db", "Bbm", "Gb", "Ebm", "Cb", "Abm", "G", "Em", "D", "Bm",
				"A", "F#m", "E", "C#m", "B", "G#m", "F#", "D#m", "C#", "A#m");
		
		$form = $this->makeForm($timeSignatures, $keysignatures);
		$this->addForm($form);
		
		$userId = OW::getUser()->getId();
		$users = $this->getUsers($multiUserMod, $groupId);
		
		//if(!$multiUserMod && $groupId==-1 && OW::getSession()->get("newAssignment")!=null)
		//	OW::getSession()->delete("newAssignment");
		
		$instGroups = SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups();
		$firstInstrument = $instGroups[0]["instruments"][0];
		$instTable = array(array("name"=>$firstInstrument["name"], "type"=>$firstInstrument["optionValue"], "user"=>(count($users)>1?$userId:-1)));
		$instrumentsTable = new SOMUSIC_CMP_InstrumentsTableContainer($users, $instTable);
		
		$preview = new SOMUSIC_CLASS_Preview($timeSignatures[0], $keysignatures[0], $instTable, $multiUserMod, $groupId);
		OW::getSession()->set("preview", serialize($preview));
		
		$this->assign("instrumentsTable", $instrumentsTable->render());
		$this->assign("importURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Preview', 'importMusicXML'));
		$this->assign("commitURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Preview', 'commitPreview'));
	}
	
	
	private function makeForm($timeSignatures, $keysignatures) {
		$form = new Form('preview_form');
		$form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
		$tsField = new Selectbox('timeSignature');
		foreach($timeSignatures as $ts)
			$tsField->addOption($ts, $ts);
		$tsField->setLabel("Time signature:");
		$tsField->setHasInvitation(false);
		$form->addElement($tsField);
		$ksField = new Selectbox('keySignature');
		foreach($keysignatures as $ks)
			$ksField->addOption($ks, $ks);
		$ksField->setLabel("Key signature:");
		$ksField->setHasInvitation(false);
		$form->addElement($ksField);
		return $form;
	}
	
	private function getUsers($multiUserMod, $groupId) {
		$userId = OW::getUser()->getId();
		$username = OW::getUser()->getUserObject()->username;
		if($multiUserMod && $groupId>=0) {
			$users = array($userId=>$username);
			$userIdList = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId);
			foreach ($userIdList as $uid)
				$users[$uid] = BOL_UserService::getInstance()->findByIdWithoutCache($uid)->username;
		}
		else $users = array("-1"=>$username);
		return $users;
	}
	
}