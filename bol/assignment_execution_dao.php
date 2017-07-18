<?php

class SOMUSIC_BOL_AssignmentExecutionDao extends OW_BaseDao {

	private static $classInstance;
	
	protected function __construct() {
		parent::__construct ();
	}

	public static function getInstance() {
		if (self::$classInstance === null) {
			self::$classInstance = new self ();
		}
		return self::$classInstance;
	}


	public function getDtoClassName() {
		return 'SOMUSIC_BOL_AssignmentExecution';
	}

	public function getTableName() {
		return OW_DB_PREFIX.'somusic_assignment_execution';
	}
	
	public function getExecutionByAssignmentAndUser($assignmentId, $userId) {
		$example = new OW_Example();
		$example->andFieldEqual("assignment_id", $assignmentId);
		$example->andFieldEqual("user_id", $userId);
		return $this->findObjectByExample($example);
	}
	
	public function getUserAssignmentExecutions($userId) {
		$example = new OW_Example();
		$example->andFieldEqual("user_id", $userId);
		return $this->findListByExample($example);
	}
	
	public function getExecutionsByAssignmentId($id) {
		$example = new OW_Example();
		$example->andFieldEqual("assignment_id", $id);
		return $this->findListByExample($example);
	}
	
	public function setExecutionComment($id, $comment) {
		$query = 'UPDATE '.$this->getTableName().' SET comment=:comment WHERE id=:id';
		$this->dbo->query($query, array(
				"id" => $id,
				"comment" => $comment
		));
	}
}
