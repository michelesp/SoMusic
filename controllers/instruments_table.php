<?php

class SOMUSIC_CTRL_InstrumentsTable extends OW_ActionController {
	private $instruments;
	
	public function __construct() {
		//parent::_construct();
		$this->instruments = array();
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
			$this->instruments[strtolower(str_replace(" ", "_", $mi->name))] = array("scoresClef"=>$scoresChelf, "braces"=>$braces);
		}
	}
	
	public function addInstrument() {
		if(empty($_REQUEST["addInstrument"]) || empty($_REQUEST["instruments"]))
			exit(json_encode("error"));
		$preview = unserialize(OW::getSession()->get("preview"));
		$preview->instrumentsTable = $_REQUEST["instruments"];
		$userId = OW::getUser()->getId();
		$firstInstrument = SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups()[0]["instruments"][0];
		array_push($preview->instrumentsTable, array("name"=>$firstInstrument["name"], "type"=>$firstInstrument["optionValue"], "user"=>$userId));
		$preview->instrumentsTable = $this->changeName($preview->instrumentsTable, $this->getDuplicated($preview->instrumentsTable));
		OW::getSession()->set("preview", serialize($preview));
		$users = $this->getUsers($preview->groupId);
		$instrumentsTable = new SOMUSIC_CMP_InstrumentsTable($users, $preview->instrumentsTable);
		$instrumentsUsed = $this->getInstrumentsUsed($preview->instrumentsTable);
		exit(json_encode(array("html"=>$instrumentsTable->render(), "instrumentsUsed"=>$instrumentsUsed, "totNScores"=>$this->getTotNScores($instrumentsUsed))));
	}
	
	public function deleteInstrument() {
		if(!ctype_digit($_REQUEST["deleteInstrument"]) || empty($_REQUEST["instruments"]))
			exit(json_encode("error"));
		$preview = unserialize(OW::getSession()->get("preview"));
		$preview->instrumentsTable = $_REQUEST["instruments"];
		if(count($preview->instrumentsTable)>1)
			array_splice($preview->instrumentsTable, intval($_REQUEST["deleteInstrument"]), 1);
		$preview->instrumentsTable = $this->changeName($preview->instrumentsTable, $this->getDuplicated($preview->instrumentsTable));
		OW::getSession()->set("preview", serialize($preview));
		$users = $this->getUsers($preview->groupId);
		$instrumentsTable = new SOMUSIC_CMP_InstrumentsTable($users, $preview->instrumentsTable);
		$instrumentsUsed = $this->getInstrumentsUsed($preview->instrumentsTable);
		exit(json_encode(array("html"=>$instrumentsTable->render(), "instrumentsUsed"=>$instrumentsUsed, "totNScores"=>$this->getTotNScores($instrumentsUsed))));
	}
	
	public function getTable() {
		$preview = unserialize(OW::getSession()->get("preview"));
		$users = $this->getUsers($preview->groupId);
		$instrumentsTable = new SOMUSIC_CMP_InstrumentsTable($users, $preview->instrumentsTable);
		$instrumentsUsed = $this->getInstrumentsUsed($preview->instrumentsTable);
		exit(json_encode(array("html"=>$instrumentsTable->render(), "instrumentsUsed"=>$instrumentsUsed, "totNScores"=>$this->getTotNScores($instrumentsUsed))));
	}
	
	public function commitChange() {
		if(empty($_REQUEST["instruments"]))
			exit(json_encode("error"));
		$preview = unserialize(OW::getSession()->get("preview"));
		$preview->instrumentsTable = $_REQUEST["instruments"];
		$users = $this->getUsers($preview->groupId);
		$instrumentsTable = new SOMUSIC_CMP_InstrumentsTable($users, $preview->instrumentsTable);
		$instrumentsUsed = $this->getInstrumentsUsed($preview->instrumentsTable);
		exit(json_encode(array("html"=>$instrumentsTable->render(), "instrumentsUsed"=>$instrumentsUsed, "totNScores"=>$this->getTotNScores($instrumentsUsed))));
		
	}
	
	public function changeType() {
		if(!ctype_digit($_REQUEST["index"]) || empty($_REQUEST["value"]))
			exit(json_encode("error"));
		$preview = unserialize(OW::getSession()->get("preview"));
		$preview->instrumentsTable[intval($_REQUEST["index"])]["name"] = ucwords(implode(" ", explode("_", $_REQUEST["value"])));
		$preview->instrumentsTable[intval($_REQUEST["index"])]["type"] = $_REQUEST["value"];
		$preview->instrumentsTable = $this->changeName($preview->instrumentsTable, $this->getDuplicated($preview->instrumentsTable));
		OW::getSession()->set("preview", serialize($preview));
		$users = $this->getUsers($preview->groupId);
		$instrumentsTable = new SOMUSIC_CMP_InstrumentsTable($users, $preview->instrumentsTable);
		$instrumentsUsed = $this->getInstrumentsUsed($preview->instrumentsTable);
		exit(json_encode(array("html"=>$instrumentsTable->render(), "instrumentsUsed"=>$instrumentsUsed, "totNScores"=>$this->getTotNScores($instrumentsUsed))));
	}
	
	private function getUsers($groupId) {
		$userId = OW::getUser()->getId();
		$username = OW::getUser()->getUserObject()->username;
		$users = array($userId=>$username);
		if($groupId>=0) {
			$userIdList = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId);
			foreach ($userIdList as $uid)
				$users[$uid] = BOL_UserService::getInstance()->findByIdWithoutCache($uid)->username;
		}
		return $users;
	}
	
	private function getDuplicated($instrumentsTable) {
		$readed = array();
		$duplicated = array();
		$instrumentsTableName = array();
		foreach ($instrumentsTable as $i=>$instrument) {
			$name = explode(" ", $instrument["name"]);
			if(is_numeric($name[count($name)-1]))
				array_pop($name);
			array_push($instrumentsTableName, implode(" ", $name));
		}
		foreach ($instrumentsTable as $i=>$instrument) {
			if(in_array($instrumentsTableName[$i], $readed)) {
				for($j=0; $j<$i; $j++)
					if($instrumentsTableName[$i]==$instrumentsTableName[$j])
						array_push($duplicated, $instrumentsTable[$j]["name"]);
				array_push($duplicated, $instrument["name"]);
			}
			else array_push($readed, $instrumentsTableName[$i]);
		}
		return $duplicated;
	}
	
	private function changeName($instrumentsTable, $duplicated) {
		$instruments = $this->getInstruments(SOMUSIC_BOL_Service::getInstance()->getMusicInstruments());
		$instrumentsIndexes = array();
		foreach ($instruments as $key=>$value)
			$instrumentsIndexes[$key] = 1;
		foreach ($instrumentsTable as $i=>$instrument) {
			if(in_array($instrument["name"], $duplicated))
				$instrumentsTable[$i]["name"] = $instruments[$instrument["type"]]." ".($instrumentsIndexes[$instrument["type"]]++);
		}
		return $instrumentsTable;
	}
	
	private function getInstruments($instruments) {
		$instruments1 = array();
		foreach ($instruments as $inst) 
			$instruments1[str_replace(" ", "_", strtolower($inst->name))] = $inst->name;
		return $instruments1;
	}
	
	private function getInstrumentsUsed($instrumentsTable) {
		$instrumentsUsed = array();
		foreach ($instrumentsTable as $instrumentRow) {
			$name = $instrumentRow["type"];
			$instrument = $this->instruments[$name];
			array_push($instrumentsUsed, array("labelName"=>$instrumentRow["name"],
					"name"=>$name,
					"scoresClef"=>$instrument["scoresClef"],
					"braces"=>$instrument["braces"]));
		}
		return $instrumentsUsed;
	}
	
	private function getTotNScores($instrumentsUsed) {
		$totNScores = 0;
		foreach ($instrumentsUsed as $instrument) 
			$totNScores += count($instrument["scoresClef"]);
		return $totNScores;
	}
	
}
