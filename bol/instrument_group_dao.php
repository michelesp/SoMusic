<?php
class SOMUSIC_BOL_InstrumentGroupDao extends OW_BaseDao {
	
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
		return 'SOMUSIC_BOL_InstrumentGroup';
	}
	
	public function getTableName() {
		return OW_DB_PREFIX.'somusic_instrument_group';
	}
	
	
	
}
