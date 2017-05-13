<?php
class SOMUSIC_CTRL_Preview extends OW_ActionController {

	public function __construct() {
		
	}
	
	public function importMusicXML() {
		if(!isset($_REQUEST["file"]))
			exit(json_encode(false));
		$instrumentsName = array();
		$musicInstruments = SOMUSIC_BOL_Service::getInstance()->getMusicInstruments();
		foreach ($musicInstruments as $mi)
			array_push($instrumentsName, $mi->name);
		$parser = new SOMUSIC_CLASS_MusicXmlParser($instrumentsName);
		$composition = $parser->parseMusicXML($_REQUEST["file"]);
		
		$editor = new SOMUSIC_CTRL_Editor(false);
		$editor->setComposition($composition);
		
		/*$preview = unserialize(OW::getSession()->get("preview"));
		$preview->importedComposition = $composition;
		$preview->instrumentsTable = array();
		foreach ($composition->instrumentsUsed as $instrumentUsed)
			array_push($preview->instrumentsTable, array("name"=>$instrumentUsed->labelName, "type"=>$instrumentUsed->name, "user"=>-1));
		OW::getSession()->set("preview", serialize($preview));*/

		exit(json_encode(true));
	}
	
	public function commitPreview() {
		if(!isset($_REQUEST["timeSignature"]) || !isset($_REQUEST["keySignature"]) || !isset($_REQUEST["instrumentsUsed"]))
			exit(json_encode(false));
		$timeSignature = $_REQUEST["timeSignature"];
		$keySignature = $_REQUEST["keySignature"];
		$instrumentsUsed = $_REQUEST["instrumentsUsed"];
		
		$editor = new SOMUSIC_CTRL_Editor(false);
		$editor->initEditor($instrumentsUsed, $timeSignature, $keySignature);
		
		exit(json_encode(true));
	}
	
}