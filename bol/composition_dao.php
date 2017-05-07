<?php
class SOMUSIC_BOL_CompositionDao extends OW_BaseDao {
	
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
		return 'SOMUSIC_BOL_Composition';
	}
	
	public function getTableName() {
		return OW_DB_PREFIX.'somusic_composition';
	}
	
	public function updateComposition($id, $instrumentsScores, $instrumentsUsed) {
		$query = 'UPDATE '.$this->getTableName().
				' SET instrumentsScore=:scores, instrumentsUsed = :used, timestamp_m = CURRENT_TIMESTAMP'.
				' WHERE id=:id';
		$this->dbo->query($query, array(
				"id" => $id,
				"scores" => $instrumentsScores,
				"used" => $instrumentsUsed
		));
	}
	
	public function getUserCompositions($userId) {
		$example = new OW_Example();
		$example->andFieldEqual("user_c ", $userId);
		return $this->findListByExample($example);
	}
	
}
