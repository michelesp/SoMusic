<?php

class SOMUSIC_CLASS_UsersCompositionsSimilarity {
	private $idList;
	private $service;
	private $updateInterval;
	private $memory;
	
	public function __construct() {
		$this->service = SOMUSIC_BOL_Service::getInstance();
		$userService = BOL_UserService::getInstance();
		$users = $userService->findList(0, $userService->count(true), true);
		$this->idList = array();
		foreach ($users as $user)
			array_push($this->idList, $user->id);
		//$this->updateInterval = 60*60*24;
		//$this->updateInterval = 60*60;
		$this->updateInterval = 0;
		$this->memory = new Memcached();
		$this->memory->addServer("localhost", 11211);
	}
	
	public function getGraph() {
		$graph = $this->memory->get("graphSimilarity");
		if(is_bool($graph))
			$graph = $this->updateGraph();
		return $graph;
	}
	
	public function update() {
		foreach ($this->idList as $userId)
			$this->updateUser($userId);
		$this->updateGraph();
	}
	
	private function updateUser($userId) {
		$userCompositions = $this->service->getAllCompositions($userId);
		$melodicRepresentation = $this->getMelodicRepresentation($userCompositions);
		$melodicLength = strlen($melodicRepresentation);
		foreach ($this->idList as $uid) {
			if($uid==$userId)
				continue;
			$ucs = $this->service->getUsersCompositionsSimilarity($userId, $uid);
			$toUpdate = true;
			if(count($ucs)==0) {
				$ucs = new SOMUSIC_BOL_UsersCompositionsSimilarity();
				$ucs->userId1 = $userId;
				$ucs->userId2 = $uid;
				$ucs->value = 0;
				$ucs->last_update = 0;
				$toUpdate = false;
			}
			else $ucs = (object)$ucs;
			if(time()-$ucs->last_update>=$this->updateInterval) {
				$userCompositions1 = $this->service->getAllCompositions($uid);
				$melodicRepresentation1 = $this->getMelodicRepresentation($userCompositions1);
				$value = $this->calculateMelodicSimilarity($melodicRepresentation, $melodicRepresentation1);
				if($toUpdate)
					$this->service->updateUsersCompositionsSimilarity($userId, $uid, $value, $melodicLength+strlen($melodicRepresentation1));
				else $this->service->addUsersCompositionsSimilarity($userId, $uid, $value, $melodicLength+strlen($melodicRepresentation1));
			}
		}
	}
	
	private function calculateMelodicSimilarity($melodicRepresentation1, $melodicRepresentation2) {
		if(strlen($melodicRepresentation1)<1 || strlen($melodicRepresentation2)<1)
			return 0;
		$distance = $this->calculateDistance($melodicRepresentation1, $melodicRepresentation2);
		if($distance<0)
			return 0;
		return 1/$distance;
	}
	
	private function getMelodicRepresentation($compositions) {
		$melodicRepresentation = "";
		foreach ($compositions as $composition) {
			for($i=0; $i<count($composition->instrumentsScore); $i++)
				$melodicRepresentation .= "p".$composition->getMelodicRepresentation2($i)."p";
			$melodicRepresentation .= "p";
		}
		return $melodicRepresentation;
	}
	
	private function calculateDistance($x, $y) {
		$lzw = new SOMUSIC_CLASS_Lzw();
		$Cxy = strlen($lzw->compress($x.$y));
		$lzw = new SOMUSIC_CLASS_Lzw();
		$Cx = strlen($lzw->compress($x));
		$lzw = new SOMUSIC_CLASS_Lzw();
		$Cy = strlen($lzw->compress($y));
		return ($Cxy-min($Cx,$Cy))/max($Cx,$Cy);
	}
	
	private function updateGraph() {
		$graph = new \Fhaculty\Graph\Graph();
		$userService = BOL_UserService::getInstance();
		foreach ($this->idList as $userId) {
			$user = $userService->findByIdWithoutCache($userId);
			$v = $graph->createVertex($userId);
			$v->setAttribute("username", $user->username);
		}
		$sum = 0;
		$div = 0;
		$maxML = $this->service->getMaxMelodicLengthUsersCompositionSimilarity();
		$list = $this->service->getAllUsersCompositionsSimilarity();
		foreach ($list as $ucs) {
			if($ucs->value!=0) {
				$sum += $ucs->value*($ucs->melodic_length/$maxML);
				$div += $ucs->melodic_length/$maxML;
			}
		}
		if($div!=0) {
			$threshold = $sum/$div;
			//var_dump($threshold);
			foreach ($list as $ucs) {
				if($ucs->value>=$threshold) {
					$v1 = $graph->getVertex($ucs->userId1);
					$v2 = $graph->getVertex($ucs->userId2);
					$e = $v1->createEdge($v2);
					$e->setWeight($ucs->value*10);
				}
			}
		}
		$this->memory->set("graphSimilarity", $graph);
		return $graph;
	}
	
}