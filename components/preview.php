<?php

class SOMUSIC_CMP_Preview extends OW_Component {
	public function __construct($component = "data-sevc-controllet") {
		$this->assign ( "component", $component );
		OW::getDocument ()->addScript ( "https://code.jquery.com/jquery-3.1.1.min.js", 'text/javascript' );
		OW::getDocument ()->addScript ( "http://netdna.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js", 'text/javascript' );
		OW::getDocument ()->addStyleSheet ( "http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css", 'text/css' );
		OW::getDocument ()->addStyleSheet ( "http://pingendo.github.io/pingendo-bootstrap/themes/default/bootstrap.css", 'text/css' );
		OW::getDocument ()->addStyleSheet ( "ow_static/plugins/somusic/css/preview.css", 'text/css' );
		OW::getDocument ()->addScript("ow_static/plugins/somusic/js/preview.js", "text/javascript");
		
		$this->assign ( "instrumentGroups", $this->getInstrumentGroups() );
		$this->assign ( "instruments", json_encode($this->getInstruments()) );
	}
	
	private function getInstrumentGroups() {
		$instruments = array();
		$groups = SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups();
		foreach ($groups as $group){
			//$groupOfInstruments = new SOMUSIC_CLASS_GroupOfInstruments($group->name);
			$groupOfInstruments = array("name"=>$group->name, "instruments"=>array());
			$musicIntruments = SOMUSIC_BOL_Service::getInstance()->getMusicInstrumentsByGroup($group->id);
			foreach ($musicIntruments as $mi) {
				//$instrumentScores = SOMUSIC_BOL_Service::getInstance()->getInstrumentScores($mi["id"]);
				//$groupOfInstruments->addInstrument(new SOMUSIC_CLASS_Instrument($mi["id"], $mi["name"], $scoresChelf, $braces));
				array_push($groupOfInstruments["instruments"], array("name"=>$mi["name"], "optionValue"=>strtolower(str_replace(" ", "_", $mi["name"]))));
			}
			array_push($instruments, $groupOfInstruments);
		}
		return $instruments;
	}
	
	private function getInstruments() {
		$instruments = array();
		$musicIntruments = SOMUSIC_BOL_Service::getInstance()->getMusicInstruments();
		foreach ($musicIntruments as $mi) {
			$instrumentScores = SOMUSIC_BOL_Service::getInstance()->getInstrumentScores($mi->id);
			$scoresChelf = array();
			$scoresChelfIndex = array();
			$braces = array();
			foreach ($instrumentScores as $i=>$is){
				array_push($scoresChelf, $is["clef"]);
				array_push($scoresChelfIndex, $is["id"]);
			}
			foreach ($scoresChelfIndex as $i) {
				foreach ($scoresChelfIndex as $j) {
					if($i!=$j) {
						$instrumentScoreInBraces = SOMUSIC_BOL_Service::getInstance()->getInstrumentScoreInBraces($mi->id, $i, $j);
						foreach ($instrumentScoreInBraces as $isib)
							array_push($braces, array($isib["id_score_1"]-1, $isib["id_score_2"]-1));
					}
				}
			}
			$instruments[strtolower(str_replace(" ", "_", $mi->name))] = array("scoresClef"=>$scoresChelf, "braces"=>$braces);
			//array_push($instruments, array("name"=>$mi->name, "scoresChelf"=>$scoresChelf, "braces"=>$braces));
		}
		return $instruments;
	}
	
}