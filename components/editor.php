<?php

class SOMUSIC_CMP_Editor extends OW_Component {
	
	public function __construct($compositionId = -1) {
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-grid.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-reboot.min.css');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'bootstrap.min.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'editor.js', 'text/javascript');
		
		$composition = null;
		$editor = new SOMUSIC_CTRL_Editor(false);
		if($compositionId>=0) {
			$composition = SOMUSIC_CLASS_Composition::getCompositionObject(SOMUSIC_BOL_Service::getInstance()->getComposition($compositionId));
			$editor->setComposition($composition);
		}
		else{
			$editor->loadDataFromCache();
			$composition = $editor->getComposition();
		}
		$this->assign("composition", json_encode($composition));
		$this->assign("instrumentsUsed", json_encode($composition->instrumentsUsed));
		
		$form = $this->makeForm();
		$this->addForm($form);
		$this->assign("deleteNotesURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Editor', 'deleteNotes'));
		$this->assign("addTieURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Editor', 'addTie'));
		$this->assign("addNoteURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Editor', 'addNote'));
		$this->assign("getCompositionURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Editor', 'getJSONComposition'));
		$this->assign("accidentalUpdateURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Editor', 'accidentalUpdate'));
		$this->assign("isClose", 0);		//TODO: passare qualcosa come parametro
		$this->assign("closeURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Editor', 'close'));
		$this->assign("removeInstrumentURL", OW::getRouter()->urlFor( 'SOMUSIC_CTRL_Editor', 'removeInstrument'));
		$this->assign("exportURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_Editor', 'exportMusicXML'));
	}
	
	private function makeForm() {
		$imgUrl =  OW::getPluginManager()->getPlugin('somusic')->getStaticUrl()."img/";
		$notes = array("1"=>$imgUrl."whole-note.png",
				"2"=>$imgUrl."half-note.png",
				"4"=>$imgUrl."quarter-note.png",
				"8"=>$imgUrl."eighth-note.png",
				"16"=>$imgUrl."sixteenth-note.png");
		$rests = array("1r"=>$imgUrl."whole-rest.png",
				"2r"=>$imgUrl."half-rest.png",
				"4r"=>$imgUrl."quarter-rest.png",
				"8r"=>$imgUrl."eighth-rest.png",
				"16r"=>$imgUrl."sixteenth-rest.png");
		$accidentals = array("clear"=>$imgUrl."clear.png",
				"b"=>$imgUrl."flat.png",
				"#"=>$imgUrl."sharp.png",
				"n"=>$imgUrl."restore.png");
		$form = new Form("editor_form");
		$notesField = new RadioField("notes");
		foreach ($notes as $key=>$value)
			$notesField->addOption($key, "<img src='$value' class='noteImg'/>");
		$form->addElement($notesField);
		$restsField = new RadioField("rests");
		foreach ($rests as $key=>$value)
			$restsField->addOption($key, "<img src='$value' class='noteImg'/>");
		$form->addElement($restsField);
		$accidentalsField = new RadioField("accidentals");
		foreach ($accidentals as $key=>$value)
			$accidentalsField->addOption($key, "<img src='$value' class='noteImg'/>");
		$form->addElement($accidentalsField);
		return $form;
	}
	
	//TODO: adesso inutile
	private function isInclused($composition, $instrumentsUsed) {
		foreach ($instrumentsUsed as $instUsed)
			foreach ($composition->instrumentsUsed as $instUsed1)
				if($instUsed["labelName"] == $instUsed1->labelName)
					return true;
		return false;
	}
	
	//TODO: adesso inutile
	private function addOtherInstruments($composition, $instrumentsUsed) {
		foreach ($instrumentsUsed as $instUsed) {
			$find = false;
			for($i=0; $i<count($composition->instrumentsUsed) && !$find; $i++)
				if($instUsed["labelName"] == $composition->instrumentsUsed[$i]->labelName)
					$find = true;
			if(!$find)
				array_push($composition->insturmentsUsed, (object)$instUsed);
		}
		return $composition;
	}
	
}