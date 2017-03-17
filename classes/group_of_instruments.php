<?php
class SOMUSIC_CLASS_GroupOfInstruments {
	public $name;
	public $instruments;
	
	public function __construct($name) {
		$this->name = $name;
		$this->instruments = array();
	}
	
	public function addInstrument($instrument) {
		array_push($this->instruments, $instrument);
	}
	
}