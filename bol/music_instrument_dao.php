<?php
class SOMUSIC_BOL_MusicInstrumentDao extends OW_BaseDao {

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
		return 'SOMUSIC_BOL_MusicInstrument';
	}

	public function getTableName() {
		return OW_DB_PREFIX.'somusic_music_instrument';
	}
	
	public function getMusicInstrumentsByGroup($groupId) {
		$example = new OW_Example();
		$example->andFieldEqual("id_group", $groupId);
		return $this->findListByExample($example);
	}
	
}
