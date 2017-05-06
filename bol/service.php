<?php
class SOMUSIC_BOL_Service {
	/**
	 * Singleton instance.
	 *
	 * @var SOMUSIC_BOL_Service
	 */
	private static $classInstance;
	
	/**
	 * Returns an instance of class (singleton pattern implementation).
	 *
	 * @return ODE_BOL_Service
	 */
	public static function getInstance() {
		if (self::$classInstance === null) {
			self::$classInstance = new self ();
		}
		
		return self::$classInstance;
	}
	public function addMelodyOnPost($data, $description, $id_owner, $title, $id_post) {
		$dt = new SOMUSIC_BOL_Somusic ();
		$dt->data = $data;
		$dt->description = $description;
		$dt->id_owner = $id_owner;
		$dt->title = $title;
		SOMUSIC_BOL_SomusicDao::getInstance ()->save ( $dt );
		$dt2 = new SOMUSIC_BOL_SomusicPost ();
		$dt2->id_melody = $dt->id;
		$dt2->id_post = $id_post;
		SOMUSIC_BOL_SomusicPostDao::getInstance ()->save ( $dt2 );
		return $dt;
	}
	public function getScoreByPostId($id) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_somusic_post 
                  JOIN ow_somusic 
                  ON ow_somusic_post.id_melody = ow_somusic.id
                  WHERE ow_somusic_post.id_post = " . $id . ";";
		return $dbo->queryForRow ( $query );
	}
	public function deleteScoreById($id) {
		$dbo = OW::getDbo ();
		$query = "DELETE FROM ow_somusic, ow_somusic_post 
                  USING ow_somusic_post 
                  INNER JOIN ow_somusic 
                  ON ow_somusic_post.id_melody = ow_somusic.id  
                  WHERE ow_somusic_post.id_post = " . $id . ';';
		$dbo->query ( $query );
	}
	
	public function updateScorePost($idPost, $scores) {
		$dbo = OW::getDbo ();
		$query = "SELECT id_melody FROM ow_somusic_post WHERE id_post=".$idPost;
		$row = $dbo->queryForRow ( $query );
		if(count($row)==0)
			return false;
		$query = "UPDATE ow_somusic
                  SET data = '".$scores."', timestamp_m = CURRENT_TIMESTAMP
                  WHERE ow_somusic.id = " . $row["id_melody"] . ';';
		$dbo->query ( $query );
		return true;
	}
	
	public function getMusicInstruments() {
		return SOMUSIC_BOL_MusicInstrumentDao::getInstance()->findAll();
	}
	
	public function getMusicInstrumentsByGroup($id_group) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_music_instrument
				  WHERE ow_music_instrument.id_group = ".$id_group.";";
		return $dbo->queryForList ( $query );
	}
	
	public function getInstrumentScores($id_instrument) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_instrument_score
				  WHERE ow_instrument_score.id_instrument = " . $id_instrument . ";";
		return $dbo->queryForList ( $query );
	}
	
	public function getInstrumentScoreInBraces($id_instrument, $id_score_1, $id_score_2) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_instrument_score_in_braces
				  WHERE ow_instrument_score_in_braces.id_instrument = " . $id_instrument . "
				  AND ow_instrument_score_in_braces.id_score_1 = " . $id_score_1 . "
				  AND ow_instrument_score_in_braces.id_score_2 = " . $id_score_2 . ";";
		return $dbo->queryForList ( $query );
	}
	
	public function getAssignmentsByGroupId($groupId) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_assignment
				  WHERE ow_assignment.group_id = " . $groupId . ";";
		return $dbo->queryForList ( $query );
	}
	
	public function getInstrumentGroups() {
		$instruments = array();
		$groups = SOMUSIC_BOL_InstrumentGroupDao::getInstance()->findAll();
		foreach ($groups as $group){
			$groupOfInstruments = array("name"=>$group->name, "instruments"=>array(), "id"=>$group->id);
			$musicIntruments = SOMUSIC_BOL_Service::getInstance()->getMusicInstrumentsByGroup($group->id);
			foreach ($musicIntruments as $mi)
				array_push($groupOfInstruments["instruments"], array("name"=>$mi["name"], "optionValue"=>strtolower(str_replace(" ", "_", $mi["name"]))));
			array_push($instruments, $groupOfInstruments);
		}
		return $instruments;
	}
	
	public function addAssignment($name, $groupId, $idOwner, $mode, $composition) {
		$dt = new SOMUSIC_BOL_Somusic ();
		$dt->data = $composition;
		$dt->description = "";
		$dt->id_owner = $idOwner;
		$dt->title = "";
		SOMUSIC_BOL_SomusicDao::getInstance ()->save ( $dt );
		$dt2 = new SOMUSIC_BOL_Assignment();
		$dt2->composition_id = $dt->id;
		$dt2->group_id = $groupId;
		$dt2->last_user_m = $idOwner;
		$dt2->mode = $mode;
		$dt2->name = $name;
		//$dt2->timestamp_c = time();
		//$dt2->timestamp_m = $dt2->timestamp_c;
		SOMUSIC_BOL_AssignmentDao::getInstance ()->save ( $dt2 );
		return $dt2;
	}
	
	public function getAssignment($id) {
		return SOMUSIC_BOL_AssignmentDao::getInstance()->findById($id);
	}
	
	public function getAssignmentExecutionsByAssignmentId($id) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_assignment_execution
				  WHERE ow_assignment_execution.assignment_id = " . $id . ";";
		return $dbo->queryForList ( $query );
	}
	
	public function addAssignmentExecution($assignmentId, $composition) {
		$userId = OW::getUser()->getId();
		$dt = new SOMUSIC_BOL_Somusic ();
		$dt->data = $composition;
		$dt->description = "";
		$dt->id_owner = $userId;
		$dt->title = "";
		SOMUSIC_BOL_SomusicDao::getInstance ()->save ( $dt );
		$execution = new SOMUSIC_BOL_AssignmentExecution();
		$execution->assignment_id = $assignmentId;
		$execution->user_id = $userId;
		$execution->composition_id = $dt->id;
		$execution->comment = '';
		SOMUSIC_BOL_AssignmentExecutionDao::getInstance()->save($execution);
	}
	
	public function getComposition($id) {
		return SOMUSIC_BOL_SomusicDao::getInstance()->findById($id);
	}
	
	public function updateComposition($id, $scores) {
		$dbo = OW::getDbo ();
		$query = "UPDATE ow_somusic ".
                  "SET data = '".$scores."', timestamp_m = CURRENT_TIMESTAMP ".
                  "WHERE ow_somusic.id = " . $id . ";";
		$dbo->query ( $query );
		return true;
	}
	
	public function getExecution($id) {
		return SOMUSIC_BOL_AssignmentExecutionDao::getInstance()->findById($id);
	}
	
	public function removeAssignment($id) {
		$executions = $this->getAssignmentExecutionsByAssignmentId($id);
		foreach ($executions as $ex) {
			SOMUSIC_BOL_SomusicDao::getInstance()->deleteById($ex["composition_id"]);
			SOMUSIC_BOL_AssignmentExecutionDao::getInstance()->deleteById($ex["id"]);
		}
		SOMUSIC_BOL_AssignmentDao::getInstance()->deleteById($id);
	}
	
	public function closeAssignment($id) {
		$dbo = OW::getDbo();
		$query = "UPDATE ow_assignment ".
				"SET ow_assignment.close = 1 ".
				"WHERE ow_assignment.id = ".$id.";";
		$dbo->query($query);
		return true;
	}
	
	public function getExecutionByAssignmentAndUser($assignmentId, $userId) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_assignment_execution
				  WHERE ow_assignment_execution.assignment_id = ".$assignmentId." AND 
				  		ow_assignment_execution.user_id = ".$userId.";";
		return $dbo->queryForRow($query);
	}
	
	public function setExecutionComment($idExecution, $comment) {
		$dbo = OW::getDbo();
		$query = "UPDATE ow_assignment_execution ".
				"SET comment = '".$comment."' ".
				"WHERE id = ".$idExecution.";";
		$dbo->query($query);
	}
	
	public function getAdminGroupIdByExecution($id) {
		$execution = SOMUSIC_BOL_AssignmentExecutionDao::getInstance()->findById($id);
		$assignment = SOMUSIC_BOL_Assignment::getInstance()->findById($execution->assignment_id);
		$group = GROUPS_BOL_GroupDao::getInstance()->findById($assignment->group_id);
		return $group->userId;
	}
	
	public function test() {
		$dbo = OW::getDbo();
		$query = "UPDATE ow_somusic_test SET test = CURRENT_TIMESTAMP WHERE id = 0;";
		$dbo->query($query);
	}
	
	public function getUserAssignmentExecutions($userId) {
		$dbo = OW::getDbo();
		$query = "SELECT *
                  FROM ow_assignment_execution
				  WHERE user_id = ".$userId.";";
		return $dbo->queryForList($query);
	}
	
	public function getAllCompositions($userId) {
		$dbo = OW::getDbo();
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
		return $toReturn;
	}
	
	public function getUsersCompositionsSimilarity($userId1, $userId2) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_somusic_users_compositions_similarity
				  WHERE (userId1 = ".$userId1." AND userId2 = ".$userId2.") OR
				  		(userId2 = ".$userId1." AND userId1 = ".$userId2.");";
		return $dbo->queryForRow($query);
	}
	
	public function addUsersCompositionsSimilarity($userId1, $userId2, $value) {
		$ucs = new SOMUSIC_BOL_UsersCompositionsSimilarity();
		$ucs->userId1 = $userId1;
		$ucs->userId2 = $userId2;
		$ucs->value = $value;
		SOMUSIC_BOL_UsersCompositionsSimilarityDao::getInstance()->save($ucs);
	}
	
	public function updateUsersCompositionsSimilarity($userId1, $userId2, $value) {
		$dbo = OW::getDbo();
		$query = "UPDATE ow_somusic_users_compositions_similarity ".
				"SET value = '".$value."', last_update = CURRENT_TIMESTAMP ".
				"WHERE (userId1 = ".$userId1." AND userId2 = ".$userId2.") OR ".
				  		"(userId2 = ".$userId1." AND userId1 = ".$userId2.");";
		$dbo->query($query);
	}
	
	public function getAllUsersCompositionsSimilarity() {
		return SOMUSIC_BOL_UsersCompositionsSimilarityDao::getInstance()->findAll();
	}
	
}