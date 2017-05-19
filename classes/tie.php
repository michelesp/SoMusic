<?php

class SOMUSIC_CLASS_Tie implements Serializable, JsonSerializable {
	public $firstMeasure;
	public $firstNote;
	public $lastMeasure;
	public $lastNote;
	
	public function __construct($firstMeasure, $firstNote, $lastMeasure, $lastNote) {
		$this->firstMeasure = $firstMeasure;
		$this->firstNote = $firstNote;
		$this->lastMeasure = $lastMeasure;
		$this->lastNote = $lastNote;
	}
	
	
	public function serialize() {
		return serialize([$this->firstMeasure, $this->firstNote, $this->lastMeasure, $this->lastNote]);
	}
	
	public function unserialize($data) {
		list($this->firstMeasure, $this->firstNote, $this->lastMeasure, $this->lastNote) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}