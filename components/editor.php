<?php

class SOMUSIC_CMP_Editor extends OW_Component {
	
	public function __construct($timeSignature, $keySignature, $instrumentsUsed, $composition = null) {
		$imgUrl =  OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticUrl()."img/";
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
		OW::getSession()->delete("preview");
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
		$editorCTRL = new SOMUSIC_CTRL_Editor();
		if($composition==null)
			$composition = $editorCTRL->initEditor($instrumentsUsed, $timeSignature, $keySignature);
		else $editorCTRL->setComposition($composition);
		//$this->assign("instrumentsUsed", json_encode($instrumentsUsed));
		//$this->assign("timeSignature", json_encode($timeSignature));
		//$this->assign("keySignature", json_encode($keySignature));
		$this->assign("composition", json_encode($composition));
		
		$this->addForm($form);
		$this->assign("form", $form);
		
		//$this->assign("initEditorURL", OW::getRouter ()->urlFor ('SOMUSIC_CTRL_Editor', 'initEditor'));
	}
	
	public static function addScripts() {
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'editor.js', 'text/javascript' );
	}
	
}