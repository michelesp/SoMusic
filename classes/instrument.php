<?php
class SOMUSIC_CLASS_Instrument {
	public $id;
	public $name;
	public $scoresChelf;
	public $braces;
	
	public function __construct($id, $name, $scoresChelf, $braces) {
		$this->id = $id;
		$this->name = $name;
		$this->scoresChelf = $scoresChelf;
		$this->braces = $braces;
	}
	
}