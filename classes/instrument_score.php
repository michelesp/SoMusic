<?php

class SOMUSIC_CLASS_InstrumentScore implements Serializable, JsonSerializable {
	private $id;
	private $default_clef;
	public $name;
	public $measures;
	public $ties;
	public $instrument;
	
	public function __construct($id, $default_clef, $name, $measures, $ties, $instrument) {
		$this->id = $id;
		$this->default_clef = $default_clef;
		$this->name = $name;
		$this->measures = $measures;
		$this->ties = $ties;
		$this->instrument = $instrument;
	}


	public function getId() {
		return $this->id;
	}
	
	public function getDefaultClef() {
		return $this->default_clef;
	}
	
	public function serialize() {
		return serialize([$this->id, $this->default_clef, $this->name, $this->measures, $this->ties, $this->instrument]);
	}
	
	public function unserialize($data) {
		list($this->id, $this->default_clef, $this->name, $this->measures, $this->ties, $this->instrument) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}
