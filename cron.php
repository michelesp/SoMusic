<?php

class SOMUSIC_Cron extends OW_Cron
{

	public function __construct() {
		parent::__construct();
		$this->addJob('update', 1);
	}

	public function run() {
		//$this->update();
		//$ucs = new SOMUSIC_CLASS_UsersCompositionsSimilarity();
		//$ucs->update();
	}


	/*public function update() {
		SOMUSIC_BOL_Service::getInstance()->test();
	}*/
	
	public function update() {
		$ucs = new SOMUSIC_CLASS_UsersCompositionsSimilarity();
		$ucs->update();
	}

}