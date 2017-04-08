<?php

class SOMUSIC_BOL_AssignmentExecutionDao extends OW_BaseDao {
	/**
	 * Constructor.
	 */
	protected function __construct() {
		parent::__construct ();
	}
	/**
	 * Singleton instance.
	 *
	 * @var SOMUSIC_BOL_AssignmentExecutionDao
	 */
	private static $classInstance;

	/**
	 * Returns an instance of class (singleton pattern implementation).
	 *
	 * @return SOMUSIC_BOL_AssignmentExecutionDao
	 */
	public static function getInstance() {
		if (self::$classInstance === null) {
			self::$classInstance = new self ();
		}
		return self::$classInstance;
	}

	/**
	 *
	 * @see OW_BaseDao::getDtoClassName()
	 *
	 */
	public function getDtoClassName() {
		return 'SOMUSIC_BOL_AssignmentExecution';
	}

	/**
	 *
	 * @see OW_BaseDao::getTableName()
	 *
	 */
	public function getTableName() {
		return OW_DB_PREFIX . 'assignment_execution';
	}
}