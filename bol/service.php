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
			$groupOfInstruments = array("name"=>$group->name, "instruments"=>array());
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
		$dt2->timestamp_c = time();
		$dt2->timestamp_m = $dt2->timestamp_c;
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
	
}