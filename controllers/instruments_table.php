<?php

class SOMUSIC_CTRL_InstrumentsTable extends OW_ActionController {
	private $instruments;
	private $preview;
	
	public function __construct() {
		$this->instruments = array();
		$musicIntruments = SOMUSIC_BOL_Service::getInstance()->getMusicInstruments();
		foreach ($musicIntruments as $mi)
			$this->instruments[strtolower(str_replace(" ", "_", $mi->name))] = array("scoresClef"=>json_decode($mi->scoresClef), "braces"=>json_decode($mi->braces));
		$this->preview = unserialize(OW::getSession()->get("preview"));
	}
	
	public function __destruct() {
		OW::getSession()->set("preview", serialize($this->preview));
	}
	
	public function addInstrument() {
		$userId = OW::getUser()->getId();
		$firstInstrument = SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups()[0]["instruments"][0];
		array_push($this->preview->instrumentsTable, array("name"=>$firstInstrument["name"], "type"=>$firstInstrument["optionValue"], "user"=>($this->preview->multiUserMod==1?$userId:-1)));
		$this->changeNamesInstruments();
		$this->getTable();
	}
	
	public function deleteInstrument() {
		if(!ctype_digit($_REQUEST["index"]))
			exit(json_encode("error"));
		if(count($this->preview->instrumentsTable)>1)
			array_splice($this->preview->instrumentsTable, intval($_REQUEST["index"]), 1);
		$this->changeNamesInstruments();
		$this->getTable();
	}
	
	public function getTable() {
		$users = $this->getUsers();
		$instrumentsTable = new SOMUSIC_CMP_InstrumentsTable($users, $this->preview->instrumentsTable);
		$instrumentsUsed = $this->getInstrumentsUsed();
		exit(json_encode(array("html"=>$instrumentsTable->render(), "instrumentsUsed"=>$instrumentsUsed, "totNScores"=>$this->getTotNScores($instrumentsUsed))));
	}
	
	public function changeType() {
		if(!ctype_digit($_REQUEST["index"]) || empty($_REQUEST["value"]))
			exit(json_encode("error"));
		$this->preview->instrumentsTable[intval($_REQUEST["index"])]["name"] = ucwords(implode(" ", explode("_", $_REQUEST["value"])));
		$this->preview->instrumentsTable[intval($_REQUEST["index"])]["type"] = $_REQUEST["value"];
		$this->changeNamesInstruments();
		$this->getTable();
	}
	
	public function changeUser() {
		if(!ctype_digit($_REQUEST["index"]) || empty($_REQUEST["id"]))
			exit(json_encode("error"));
		$this->preview->instrumentsTable[intval($_REQUEST["index"])]["user"] = $_REQUEST["id"];
		$this->getTable();
	}
	
	public function changeName() {
		if(!ctype_digit($_REQUEST["index"]) || empty($_REQUEST["value"]))
			exit(json_encode("error"));
		$this->preview->instrumentsTable[intval($_REQUEST["index"])]["name"] = $_REQUEST["value"];
		$this->getTable();
	}
	
	private function getUsers() {
		$userId = OW::getUser()->getId();
		$username = OW::getUser()->getUserObject()->username;
		if($this->preview->multiUserMod==1 && $this->preview->groupId>=0) {
			$users = array($userId=>$username);
			$userIdList = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($this->preview->groupId);
			foreach ($userIdList as $uid)
				$users[$uid] = BOL_UserService::getInstance()->findByIdWithoutCache($uid)->username;
		}
		else $users = array("-1"=>$username);
		return $users;
	}
	
	private function getDuplicated() {
		$readed = array();
		$duplicated = array();
		$instrumentsTableName = array();
		foreach ($this->preview->instrumentsTable as $i=>$instrument) {
			$name = explode(" ", $instrument["name"]);
			if(is_numeric($name[count($name)-1]))
				array_pop($name);
			array_push($instrumentsTableName, implode(" ", $name));
		}
		foreach ($this->preview->instrumentsTable as $i=>$instrument) {
			if(in_array($instrumentsTableName[$i], $readed)) {
				for($j=0; $j<$i; $j++)
					if($instrumentsTableName[$i]==$instrumentsTableName[$j])
						array_push($duplicated, $this->preview->instrumentsTable[$j]["name"]);
				array_push($duplicated, $instrument["name"]);
			}
			else array_push($readed, $instrumentsTableName[$i]);
		}
		return $duplicated;
	}
	
	private function changeNamesInstruments() {
		$duplicated = $this->getDuplicated();
		$instruments = $this->getInstruments(SOMUSIC_BOL_Service::getInstance()->getMusicInstruments());
		$instrumentsIndexes = array();
		foreach ($instruments as $key=>$value)
			$instrumentsIndexes[$key] = 1;
		foreach ($this->preview->instrumentsTable as $i=>$instrument) {
			if(in_array($instrument["name"], $duplicated))
				$this->preview->instrumentsTable[$i]["name"] = $instruments[$instrument["type"]]." ".($instrumentsIndexes[$instrument["type"]]++);
		}
		return $this->preview->instrumentsTable;
	}
	
	private function getInstruments($instruments) {
		$instruments1 = array();
		foreach ($instruments as $inst) 
			$instruments1[str_replace(" ", "_", strtolower($inst->name))] = $inst->name;
		return $instruments1;
	}
	
	private function getInstrumentsUsed() {
		$instrumentsUsed = array();
		foreach ($this->preview->instrumentsTable as $instrumentRow) {
			$name = $instrumentRow["type"];
			$instrument = $this->instruments[$name];
			array_push($instrumentsUsed, array("labelName"=>$instrumentRow["name"],
					"name"=>$name,
					"scoresClef"=>$instrument["scoresClef"],
					"braces"=>$instrument["braces"],
					"user"=>$instrumentRow["user"]));
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
