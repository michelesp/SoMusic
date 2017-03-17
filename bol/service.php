<?php
class SOMUSIC_BOL_Service {
	/**
	 * Singleton instance.
	 *
	 * @var SOMUSIC_BOL_Service
	 */
	private static $classInstance;
	
	/**
	 * Returns an instance of class (singleton pattern implementation).
	 *
	 * @return ODE_BOL_Service
	 */
	public static function getInstance() {
		if (self::$classInstance === null) {
			self::$classInstance = new self ();
		}
		
		return self::$classInstance;
	}
	public function addMelodyOnPost($data, $description, $id_owner, $title, $id_post) {
		$dt = new SOMUSIC_BOL_Somusic ();
		$dt->data = $data;
		$dt->description = $description;
		$dt->id_owner = $id_owner;
		$dt->title = $title;
		SOMUSIC_BOL_SomusicDao::getInstance ()->save ( $dt );
		$dt2 = new SOMUSIC_BOL_SomusicPost ();
		$dt2->id_melody = $dt->id;
		$dt2->id_post = $id_post;
		SOMUSIC_BOL_SomusicPostDao::getInstance ()->save ( $dt2 );
		return $dt;
	}
	public function getScoreByPostId($id) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_somusic_post 
                  JOIN ow_somusic 
                  ON ow_somusic_post.id_melody = ow_somusic.id
                  WHERE ow_somusic_post.id_post = " . $id . ";";
		return $dbo->queryForRow ( $query );
	}
	public function deleteScoreById($id) {
		$dbo = OW::getDbo ();
		$query = "DELETE FROM ow_somusic, ow_somusic_post 
                  USING ow_somusic_post 
                  INNER JOIN ow_somusic 
                  ON ow_somusic_post.id_melody = ow_somusic.id  
                  WHERE ow_somusic_post.id_post = " . $id . ';';
		$dbo->query ( $query );
	}
	
	public function getMusicInstruments() {
		return SOMUSIC_BOL_MusicInstrumentDao::getInstance()->findAll();
	}
	
	public function getMusicInstrumentsByGroup($id_group) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_music_instrument
				  WHERE ow_music_instrument.id_group = ".$id_group.";";
		return $dbo->queryForList ( $query );
	}
	
	public function getInstrumentScores($id_instrument) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_instrument_score
				  WHERE ow_instrument_score.id_instrument = " . $id_instrument . ";";
		return $dbo->queryForList ( $query );
	}
	
	public function getInstrumentScoreInBraces($id_instrument, $id_score_1, $id_score_2) {
		$dbo = OW::getDbo ();
		$query = "SELECT *
                  FROM ow_instrument_score_in_braces
				  WHERE ow_instrument_score_in_braces.id_instrument = " . $id_instrument . "
				  AND ow_instrument_score_in_braces.id_score_1 = " . $id_score_1 . "
				  AND ow_instrument_score_in_braces.id_score_2 = " . $id_score_2 . ";";
		return $dbo->queryForList ( $query );
	}
	
	public function getInstrumentGroups() {
		return SOMUSIC_BOL_InstrumentGroupDao::getInstance()->findAll();
	}
}