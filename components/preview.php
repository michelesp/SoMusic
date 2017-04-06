<?php

class SOMUSIC_CMP_Preview extends OW_Component {
	
	public function __construct($multiUserMod = false, $groupId = -1) {
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'jquery-3.1.1.min.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'bootstrap.min.js', 'text/javascript' );
		OW::getDocument ()->addStyleSheet ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticCssUrl () . 'font-awesome.min.css' );
		OW::getDocument ()->addStyleSheet ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticCssUrl () . 'bootstrap.css' );
		OW::getDocument ()->addStyleSheet ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticCssUrl () . 'preview.css' );
		
		$timeSignatures = array("2/2", "2/4", "3/4", "4/4", "3/8", "6/8");
		$keysignatures = array("C", "Am", "F", "Dm", "Bb", "Gm", "Eb", "Cm", "Ab", "Fm",
				"Db", "Bbm", "Gb", "Ebm", "Cb", "Abm", "G", "Em", "D", "Bm",
				"A", "F#m", "E", "C#m", "B", "G#m", "F#", "D#m", "C#", "A#m");
		
		$form = new Form('preview_form');
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
		$this->addForm($form);
		$this->assign("form", $form);
		
		$userId = OW::getUser()->getId();
		$username = OW::getUser()->getUserObject()->username;
		$users = array($userId=>$username);
		if($multiUserMod && $groupId>=0) {
			$userIdList = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId);
			foreach ($userIdList as $uid)
				$users[$uid] = BOL_UserService::getInstance()->findByIdWithoutCache($uid)->username;			
		}
		if(!$multiUserMod && $groupId==-1)
			OW::getSession()->delete("newAssignment");
		
		$instGroups = SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups();
		$firstInstrument = $instGroups[0]["instruments"][0];
		$instTable = array(array("name"=>$firstInstrument["name"], "type"=>$firstInstrument["optionValue"], "user"=>$userId));
		$instrumentsTable = new SOMUSIC_CMP_InstrumentsTableContainer($users, $instTable);
		
		$preview = new SOMUSIC_CLASS_Preview($timeSignatures[0], $keysignatures[0], $instTable, $multiUserMod, $groupId);
		OW::getSession()->set("preview", serialize($preview));
		
		$this->assign("instrumentsTable", $instrumentsTable->render());
	}
	
}