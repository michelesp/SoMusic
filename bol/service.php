<?php
class SOMUSIC_BOL_Service {
	private static $classInstance;
	
	private $assignmentDao;
	private $assignmentExecutionDao;
	private $instrumentGroupDao;
	private $musicInstrumentDao;
	private $compositionDao;
	private $somusicPostDao;
	private $usersCompositionsSimilarityDao;
	
	public static function getInstance() {
		if (self::$classInstance === null) {
			self::$classInstance = new self ();
		}
		
		return self::$classInstance;
	}
	
	protected function __construct() {
		$this->assignmentDao = SOMUSIC_BOL_AssignmentDao::getInstance();
		$this->assignmentExecutionDao = SOMUSIC_BOL_AssignmentExecutionDao::getInstance();
		$this->instrumentGroupDao = SOMUSIC_BOL_InstrumentGroupDao::getInstance();
		$this->musicInstrumentDao = SOMUSIC_BOL_MusicInstrumentDao::getInstance();
		$this->compositionDao = SOMUSIC_BOL_CompositionDao::getInstance();
		$this->somusicPostDao = SOMUSIC_BOL_SomusicPostDao::getInstance();
		$this->usersCompositionsSimilarityDao = SOMUSIC_BOL_UsersCompositionsSimilarityDao::getInstance();
	}
	
	public function getAssignmentsByGroupId($groupId) {
		return $this->assignmentDao->getAssignmentsByGroupId($groupId);
	}
	
	public function addAssignment($name, $groupId, $mode, $instrumentsScore, $instrumentsUsed) {
		$userId = OW::getUser()->getId();
		$composition = new SOMUSIC_BOL_Composition();
		$composition->user_c = $userId;
		$composition->user_m = $userId;
		$composition->name = "";
		$composition->instrumentsScore = $instrumentsScore;
		$composition->instrumentsUsed = $instrumentsUsed;
		$this->compositionDao->save($composition);
		$assignment = new SOMUSIC_BOL_Assignment();
		$assignment->composition_id = $composition->id;
		$assignment->group_id = $groupId;
		$assignment->last_user_m = $userId;
		$assignment->mode = $mode;
		$assignment->name = $name;
		$assignment->close = 0;
		$this->assignmentDao->save($assignment);
		return $assignment;
	}
	
	public function getAssignment($id) {
		return $this->assignmentDao->findById($id);
	}
	
	public function removeAssignment($id) {
		$executions = $this->getExecutionsByAssignmentId($id);
		foreach ($executions as $ex) {
			$this->compositionDao->deleteById($ex->composition_id);
			$this->assignmentExecutionDao->deleteById($ex->id);
		}
		$this->assignmentDao->deleteById($id);
	}
	
	public function closeAssignment($id) {
		$this->assignmentDao->closeAssignment($id);
	}
	
	public function addAssignmentExecution($assignmentId, $instrumentsScore, $instrumentsUsed) {
		$userId = OW::getUser()->getId();
		$composition = new SOMUSIC_BOL_Composition();
		$composition->instrumentsScore = $instrumentsScore;
		$composition->instrumentsUsed = $instrumentsUsed;
		$composition->user_c = $userId;
		$composition->user_m = $userId;
		$composition->name = "";
		$this->compositionDao->save($composition);
		$execution = new SOMUSIC_BOL_AssignmentExecution();
		$execution->assignment_id = $assignmentId;
		$execution->user_id = $userId;
		$execution->composition_id = $composition->id;
		$execution->comment = '';
		$this->assignmentExecutionDao->save($execution);
	}
	
	public function getExecution($id) {
		return $this->assignmentExecutionDao->findById($id);
	}
	
	public function getExecutionByAssignmentAndUser($assignmentId, $userId) {
		return $this->assignmentExecutionDao->getExecutionByAssignmentAndUser($assignmentId, $userId);
	}
	
	//TODO: verificare
	public function getAdminGroupIdByExecution($id) {
		$execution = $this->assignmentExecutionDao->findById($id);
		$assignment = $this->assignmentDao->findById($execution->assignment_id);
		$group = GROUPS_BOL_GroupDao::getInstance()->findById($assignment->group_id);
		return $group->userId;
	}
	
	public function getExecutionsByAssignmentId($id) {
		return $this->assignmentExecutionDao->getExecutionsByAssignmentId($id);
	}
	
	//TODO: verificare
	public function getUserAssignmentExecutions($userId) {
		/*$dbo = OW::getDbo();
		$query = "SELECT *
                  FROM ow_assignment_execution
				  WHERE user_id = ".$userId.";";
		return $dbo->queryForList($query);*/
		return $this->assignmentExecutionDao->getUserAssignmentExecutions($userId);
	}
	
	public function setExecutionComment($idExecution, $comment) {
		$this->assignmentExecutionDao->setExecutionComment($idExecution, $comment);
	}
	
	public function getUsersCompositionsSimilarity($userId1, $userId2) {
		return $this->usersCompositionsSimilarityDao->getUsersCompositionsSimilarity($userId1, $userId2);
	}
	
	public function addUsersCompositionsSimilarity($userId1, $userId2, $value, $melodicLength) {
		$ucs = new SOMUSIC_BOL_UsersCompositionsSimilarity();
		$ucs->userId1 = $userId1;
		$ucs->userId2 = $userId2;
		$ucs->value = $value;
		$ucs->melodic_length = $melodicLength;
		$this->usersCompositionsSimilarityDao->save($ucs);
	}
	
	public function updateUsersCompositionsSimilarity($userId1, $userId2, $value, $melodicLength) {
		/*$ucs = $this->getUsersCompositionsSimilarity($userId1, $userId2);
		$ucs->value = $value;
		$ucs->melodic_length = $melodicLength;
		$ucs->last_update = date("F j, Y \a\t g:ia");
		$this->usersCompositionsSimilarityDao->save($ucs);*/
		$this->usersCompositionsSimilarityDao->updateUsersCompositionsSimilarity($userId1, $userId2, $value, $melodicLength);
	}
	
	public function getAllUsersCompositionsSimilarity() {
		return $this->usersCompositionsSimilarityDao->findAll();
	}
	
	public function getMaxMelodicLengthUsersCompositionSimilarity() {
		return $this->usersCompositionsSimilarityDao->getMaxMelodicLengthUsersCompositionSimilarity();
	}
	
	public function getInstrumentGroups() {
		$instruments = array();
		$groups = SOMUSIC_BOL_InstrumentGroupDao::getInstance()->findAll();
		foreach ($groups as $group){
			$groupOfInstruments = array("name"=>$group->name, "instruments"=>array(), "id"=>$group->id);
			$musicIntruments = $this->getMusicInstrumentsByGroup($group->id);
			foreach ($musicIntruments as $mi)
				array_push($groupOfInstruments["instruments"], array("name"=>$mi->name, "optionValue"=>strtolower(str_replace(" ", "_", $mi->name))));
				array_push($instruments, $groupOfInstruments);
		}
		return $instruments;
	}
	
	public function getMusicInstruments() {
		return $this->musicInstrumentDao->findAll();
	}
	
	public function getMusicInstrumentById($id) {
		return $this->musicInstrumentDao->findById($id);
	}
	
	public function addMusicInstrument($name, $idGroup, $scoresClef, $braces) {
		$musicInstrument = new SOMUSIC_BOL_MusicInstrument();
		$musicInstrument->name = $name;
		$musicInstrument->id_group = $idGroup;
		$musicInstrument->scoresClef = $scoresClef;
		$musicInstrument->braces = $braces;
		$this->musicInstrumentDao->save($musicInstrument);
	}
	
	public function editMusicInstrument($id, $name, $idGroup, $scoresClef, $braces) {
		$musicInstrument = $this->musicInstrumentDao->findById($id);
		$musicInstrument->name = $name;
		$musicInstrument->id_group = $idGroup;
		$musicInstrument->scoresClef = $scoresClef;
		$musicInstrument->braces = $braces;
		$this->musicInstrumentDao->save($musicInstrument);
	}
	
	public function removeMusicInsturment($id) {
		exit(json_encode($this->musicInstrumentDao->deleteById($id)>=0?true:false));
	}
	
	public function getMusicInstrumentsByGroup($id_group) {
		return $this->musicInstrumentDao->getMusicInstrumentsByGroup($id_group);
	}
	
	public function getComposition($id) {
		return $this->compositionDao->findById($id);
	}
	
	public function updateComposition($id, $instrumentsScores, $instrumentsUsed) {
		$this->compositionDao->updateComposition($id, $instrumentsScores, $instrumentsUsed);
	}
	
	//TODO: provare
	public function getAllCompositions($userId) {
		/*$dbo = OW::getDbo();
		$query = "SELECT id, data
                  FROM ow_somusic
				  WHERE id_owner = ".$userId.";";
		$compositions = $dbo->queryForList($query);
		$executions = $this->getUserAssignmentExecutions($userId);
		$len = count($compositions);
		foreach ($executions as $ex) {
			$deleted = false;
			for($i=0; $i<count($compositions) && !$deleted; $i++) {
				if($ex["composition_id"] == $compositions[$i]["id"]) {
					array_splice($compositions, $i, 1);
					$deleted = true;
				}
			}
		}
		$toReturn = array();
		foreach ($compositions as $i=>$composition)
			array_push($toReturn, SOMUSIC_CLASS_Composition::getCompositionObject(json_decode($composition["data"], true)));
			return $toReturn;*/
		$compositions = $this->compositionDao->getUserCompositions(intval($userId));
		$executions = $this->getUserAssignmentExecutions($userId);
		foreach ($executions as $ex) {
			$deleted = false;
			for($i=0; $i<count($compositions) && !$deleted; $i++) {
				if($ex->composition_id == $compositions[$i]->id) {
					array_splice($compositions, $i, 1);
					$deleted = true;
				}
			}
		}
		$toReturn = array();
		foreach ($compositions as $i=>$composition)
			array_push($toReturn, SOMUSIC_CLASS_Composition::getCompositionObject($composition));
		return $toReturn;
	}
	
	public function addMelodyOnPost($name, $id_post, $instrumentsScore, $instrumentsUsed) {
		$userId = OW::getUser()->getId();
		$composition = new SOMUSIC_BOL_Composition();
		$composition->name = $name;
		$composition->user_c = $userId;
		$composition->user_m = $userId;
		$composition->instrumentsScore = $instrumentsScore;
		$composition->instrumentsUsed = $instrumentsUsed;
		$this->compositionDao->save($composition);
		$post = new SOMUSIC_BOL_SomusicPost();
		$post->id_melody = $composition->id;
		$post->id_post = $id_post;
		$this->somusicPostDao->save($post);
		return $composition;
	}
	
	public function getScoreByPostId($postId) {
		$somusicPost = $this->somusicPostDao->findByPostId($postId);
		if(!isset($somusicPost))
			return null;
		return $this->compositionDao->findById($somusicPost->id_melody);		
	}
	
	public function deleteScoreById($postId) {
		$somusicPost = $this->somusicPostDao->findByPostId($postId);
		if(isset($somusicPost)) {
			$this->compositionDao->deleteById($somusicPost->id_melody);
			$this->somusicPostDao->deleteById($somusicPost->id);
		}
	}
	
	public function updateScorePost($postId, $instrumentsScore, $instrumentsUsed) {
		$somusicPost = $this->somusicPostDao->findByPostId($postId);
		if(isset($somusicPost))
			$this->compositionDao->updateComposition($somusicPost->id_melody, $instrumentsScore, $instrumentsUsed);
	}
	
}