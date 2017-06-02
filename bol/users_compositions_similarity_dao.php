<?php

class SOMUSIC_BOL_UsersCompositionsSimilarityDao extends OW_BaseDao {

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
		return 'SOMUSIC_BOL_UsersCompositionsSimilarity';
	}

	public function getTableName() {
		return OW_DB_PREFIX.'somusic_users_compositions_similarity';
	}
	
	public function getUsersCompositionsSimilarity($userId1, $userId2) {
		$example = new OW_Example();
		$example->andFieldEqual("userId1", $userId1);
		$example->andFieldEqual("userId2", $userId2);
		$ucs = $this->findObjectByExample($example);
		if(isset($ucs))
			return $ucs;
		$example = new OW_Example();
		$example->andFieldEqual("userId1", $userId2);
		$example->andFieldEqual("userId2", $userId1);
		return $this->findObjectByExample($example);
	}
	
	public function updateUsersCompositionsSimilarity($userId1, $userId2, $value, $melodicLength) {
		$query = 'UPDATE '.$this->getTableName().
				' SET value = :value, last_update = CURRENT_TIMESTAMP, melodic_length = :melodicLength '.
				' WHERE (userId1 = :userId1 AND userId2 = :userId2) OR '.
						'(userId2 = :userId1 AND userId1 = :userId2)';
		$this->dbo->query($query, array(
				"value" => $value,
				"userId1" => $userId1,
				"userId2" => $userId2,
				"melodicLength" => $melodicLength
		));
	}
	
	public function getMaxMelodicLengthUsersCompositionSimilarity() {
		$query = 'SELECT MAX(melodic_length) as max FROM ow_somusic_users_compositions_similarity';
		return intval($this->dbo->queryForRow($query)["max"]);
	}
	
}
