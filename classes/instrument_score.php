<?php

class SOMUSIC_CLASS_InstrumentScore implements Serializable, JsonSerializable {
	private $default_clef;
	public $name;
	public $measures;
	public $ties;
	public $instrument;
	public $user;
	
	public function __construct($default_clef, $name, $measures, $ties, $instrument, $user) {
		$this->default_clef = $default_clef;
		$this->name = $name;
		$this->measures = $measures;
		$this->ties = $ties;
		$this->instrument = $instrument;
		$this->user = $user;
	}
	
	
	public function getDefaultClef() {
		return $this->default_clef;
	}
	
	public function serialize() {
		return serialize([$this->default_clef, $this->name, $this->measures, $this->ties, $this->instrument, $this->user]);
	}
	
	public function unserialize($data) {
		list($this->default_clef, $this->name, $this->measures, $this->ties, $this->instrument, $this->user) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}
