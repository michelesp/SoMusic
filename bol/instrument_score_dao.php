<?php
class SOMUSIC_BOL_InstrumentScoreDao extends OW_BaseDao {

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
		return 'SOMUSIC_BOL_InstrumentScore';
	}

	public function getTableName() {
		return OW_DB_PREFIX.'somusic_instrument_score';
	}
	
	public function getInstrumentScores($instrumentId) {
		$example = new OW_Example();
		$example->andFieldEqual("id_instrument", $instrumentId);
		return $this->findListByExample($example);
	}
	
}
