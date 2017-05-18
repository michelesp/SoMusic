<?php

class SOMUSIC_CMP_MusicInstrument extends OW_Component {

	public function __construct($instrumentId=null) {
		$service = SOMUSIC_BOL_Service::getInstance();
		$instrumentGroups = $service->getInstrumentGroups();

		$form = new Form("music_instrument");
		$name = new TextField("name");
		$name->setLabel("Name: ");
		$form->addElement($name);
		$groupField = new Selectbox('groupId');
		foreach($instrumentGroups as $ig)
			$groupField->addOption(intval($ig["id"]), $ig["name"]);
		$groupField->addOption(-1, "Other");
		$groupField->setLabel("Instrument group: ");
		$groupField->setHasInvitation(false);
		$groupField->addAttribute("onchange", "SoMusic.admin.checkInstrumentGroup(document.getElementById('newInst'), value);");
		$form->addElement($groupField);
		$newInstGroup = new TextField("newInstGroup");
		$newInstGroup->setLabel("New instrument group name: ");
		$form->addElement($newInstGroup);
		
		$idField = new HiddenField("instrumentId");
		if(isset($instrumentId)) {
			$musicInstrument = SOMUSIC_BOL_Service::getInstance()->getMusicInstrumentById($instrumentId);
			$this->assign("operation", "editInstrument");
			$this->assign("id", $instrumentId);
			$name->setValue($musicInstrument->name);
			$groupField->setValue($musicInstrument->id_group);
			$scoresClef = json_decode($musicInstrument->scoresClef);
			$braces = json_decode($musicInstrument->braces);
			$idField->setValue($instrumentId);
		}
		else{
			$this->assign("operation", "addInstrument");
			$scoresClef = array("treble");
			$braces = array();
			$idField->setValue(-1);
		}
		$form->addElement($idField);
		
		$save = new Submit("save");
		$save->setValue("save");
		$form->addElement($save);
		
		$clefs = array();
		for($i=0; $i<count($scoresClef); $i++) {
			$clefs[] = (object)array("name"=>$scoresClef[$i], "inBraces"=>false);
			/*$clefField = new Selectbox('clefs[]');
			$clefField->addOption(0, "treble");
			$clefField->addOption(1, "bass");
			$clefField->addOption(2, "alto");
			if($scoresClef[$i]=="alto")
				$clefField->setValue(2);
			else if($scoresClef[$i]=="bass")
				$clefField->setValue(1);
			else $clefField->setValue(0);
			$clefField->setHasInvitation(false);
			$form->addElement($clefField);*/
		}
		foreach ($braces as $b) {
			$clefs[$b[0]]->inBraces = true;
			$clefs[$b[1]]->inBraces = true;
		}
		
		$this->assign("clefs", $clefs);
		
		$this->addForm($form);
		$this->assign("form", $form);
	}
	
}