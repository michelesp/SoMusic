<?php

class SOMUSIC_CLASS_Tie implements Serializable, JsonSerializable {
	public $firstMeasure;
	public $firstNote;
	public $lastMeasure;
	public $lastNote;
	public $voiceIndex;
	
	public function __construct($firstMeasure, $firstNote, $lastMeasure, $lastNote, $voiceIndex = 0) {
		$this->firstMeasure = $firstMeasure;
		$this->firstNote = $firstNote;
		$this->lastMeasure = $lastMeasure;
		$this->lastNote = $lastNote;
		$this->voiceIndex = $voiceIndex;
	}
	
	
	public function serialize() {
		return serialize([$this->firstMeasure, $this->firstNote, 
				$this->lastMeasure, $this->lastNote, $this->voiceIndex]);
	}
	
	public function unserialize($data) {
		list($this->firstMeasure, $this->firstNote, $this->lastMeasure,
				$this->lastNote, $this->voiceIndex) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}