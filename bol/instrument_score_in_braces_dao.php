<?php
class SOMUSIC_BOL_InstrumentScoreInBracesDao extends OW_BaseDao {
	
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
		return 'SOMUSIC_BOL_InstrumentScoreInBraces';
	}

	public function getTableName() {
		return OW_DB_PREFIX.'somusic_instrument_score_in_braces';
	}
	
	public function getInstrumentScoreInBraces($instrumentId, $scoreId1, $scoreId2) {
		$example = new OW_Example();
		$example->andFieldEqual("id_instrument", $instrumentId);
		$example->andFieldEqual("id_score_1", $scoreId1);
		$example->andFieldEqual("id_score_2", $scoreId2);
		return $this->findListByExample($example);
	}
}
