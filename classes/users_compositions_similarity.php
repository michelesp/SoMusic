<?php

class SOMUSIC_CLASS_UsersCompositionsSimilarity {
	private $idList;
	private $service;
	private $updateInterval;
	private $memory;
	private $threshold;
	
	public function __construct() {
		$this->service = SOMUSIC_BOL_Service::getInstance();
		$userService = BOL_UserService::getInstance();
		$users = $userService->findList(0, $userService->count(true), true);
		$this->idList = array();
		foreach ($users as $user)
			array_push($this->idList, $user->id);
		//$this->updateInterval = 60*60*24;
		$this->updateInterval = 60*60;
		//$this->updateInterval = 0;
		$this->threshold = 0.05;
		$this->memory = new Memcached ();
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
				$value = $this->calculatesSimilarity($userId, $uid);
				if($toUpdate)
					$this->service->updateUsersCompositionsSimilarity($userId, $uid, $value);
				else $this->service->addUsersCompositionsSimilarity($userId, $uid, $value);
			}
		}
	}
	
	private function calculatesSimilarity($userId1, $userId2) {
		$compositions1 = $this->service->getAllCompositions($userId1);
		$compositions2 = $this->service->getAllCompositions($userId2);
		if(count($compositions1)==0 || count($compositions2)==0)
			return 0;
		$melodicRepresentation1 = $this->getMelodicRepresentation($compositions1);
		$melodicRepresentation2 = $this->getMelodicRepresentation($compositions2);
		$spaceSaved1 = $this->getSpaceSaved($melodicRepresentation1, $melodicRepresentation2);
		$spaceSaved2 = $this->getSpaceSaved($melodicRepresentation2, $melodicRepresentation1);
		return ($spaceSaved1+$spaceSaved2)/2;
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
	
	private function getSpaceSaved($m1, $m2) {
		$lzw = new SOMUSIC_CLASS_Lzw();
		$data = $lzw->compress($m2);
		$indComp = strlen($data);
		$lzw = new SOMUSIC_CLASS_LZW();
		$lzw->compress($m1);
		$data = $lzw->compress($m2);
		$comp = strlen($data);
		return 1-$comp/$indComp;
	}
	
	private function updateGraph() {
		$graph = new \Fhaculty\Graph\Graph();
		$userService = BOL_UserService::getInstance();
		foreach ($this->idList as $userId) {
			$user = $userService->findByIdWithoutCache($userId);
			$v = $graph->createVertex($userId);
			$v->setAttribute("username", $user->username);
		}
		$list = $this->service->getAllUsersCompositionsSimilarity();
		foreach ($list as $ucs) {
			if($ucs->value>$this->threshold) {
				$v1 = $graph->getVertex($ucs->userId1);
				$v2 = $graph->getVertex($ucs->userId2);
				$e = $v1->createEdge($v2);
				$e->setWeight($ucs->value*10);
			}
		}
		$this->memory->set("graphSimilarity", $graph);
		return $graph;
	}
	
}