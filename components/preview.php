<?php

class SOMUSIC_CMP_Preview extends OW_Component {
	public function __construct($component = "data-sevc-controllet") {
		$this->assign ( "component", $component );
		//OW::getDocument ()->addScript ( "https://code.jquery.com/jquery-3.1.1.min.js", 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'jquery-3.1.1.min.js', 'text/javascript' );
		//OW::getDocument ()->addScript ( "http://netdna.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js", 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'bootstrap.min.js', 'text/javascript' );
		//OW::getDocument ()->addStyleSheet ( "http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css", 'text/css' );
		OW::getDocument ()->addStyleSheet ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticCssUrl () . 'font-awesome.min.css' );
		//OW::getDocument ()->addStyleSheet ( "http://pingendo.github.io/pingendo-bootstrap/themes/default/bootstrap.css", 'text/css' );
		OW::getDocument ()->addStyleSheet ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticCssUrl () . 'bootstrap.css' );
		
		$instruments = $this->getInstruments();
		$this->assign ( "instrumentGroups", $this->getInstrumentGroups() );
		$this->assign ( "instruments", json_encode($instruments) );
		
		/*$cache = new Memcached();
		$cache->addServer("localhost", 11211);
		$userId = OW::getUser()->getId();
		$composition = new SOMUSIC_CLASS_Composition(-1, "", $userId, -1, $userId, -1, array());
		if(count($instruments)>0) {
			$elm = reset($instruments);
			$name = key($instruments);
			for($i=0; $i<count($elm["scoresClef"]); $i++) {
				$instrumentScore = new SOMUSIC_CLASS_InstrumentScore(-1, $elm["scoresClef"][$i], ucwords($name)." ".($i+1), array(), array());
				array_push($composition->instrumentsScore, $instrumentScore);
			}
		}
		$cache->set($userId, $composition);*/
	}
	
	private function getInstrumentGroups() {
		$instruments = array();
		$groups = SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups();
		foreach ($groups as $group){
			$groupOfInstruments = array("name"=>$group->name, "instruments"=>array());
			$musicIntruments = SOMUSIC_BOL_Service::getInstance()->getMusicInstrumentsByGroup($group->id);
			foreach ($musicIntruments as $mi) 
				array_push($groupOfInstruments["instruments"], array("name"=>$mi["name"], "optionValue"=>strtolower(str_replace(" ", "_", $mi["name"]))));
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
		}
		return $instruments;
	}
	
}