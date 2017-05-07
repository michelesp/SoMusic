<?php
class SOMUSIC_BOL_SomusicPostDao extends OW_BaseDao {

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
		return 'SOMUSIC_BOL_SomusicPost';
	}
	
	public function getTableName() {
		return OW_DB_PREFIX.'somusic_post';
	}
	
	public function findByPostId($postId) {
		$example = new OW_Example();
		$example->andFieldEqual("id_post", $postId);
		return $this->findObjectByExample($example);
	}
	
}