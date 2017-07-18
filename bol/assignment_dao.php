<?php

class SOMUSIC_BOL_AssignmentDao extends OW_BaseDao {
	
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
		return 'SOMUSIC_BOL_Assignment';
	}

	public function getTableName() {
		return OW_DB_PREFIX.'somusic_assignment';
	}
	
	public function getAssignmentsByGroupId($groupId) {
		$example = new OW_Example();
		$example->andFieldEqual("group_id", $groupId);
		return $this->findListByExample($example);
	}
	
	public function closeAssignment($id) {
		$query = 'UPDATE '.$this->getTableName().' SET close=1 WHERE id=:id';
		$this->dbo->query($query, array("id"=>$id));
	}
	
	public function getAssignmentByNameAndGroup($id, $name) {
		$example = new OW_Example();
		$example->andFieldEqual("group_id", $id);
		$example->andFieldEqual("name", $name);
		return $this->findListByExample($example);
	}
	
}
