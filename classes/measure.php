<?php

class SOMUSIC_CLASS_Measure implements Serializable, JsonSerializable {
	public $clef;
	public $keySignature;
	public $timeSignature;
	public $voices;
	
	public function __construct($clef, $keySignature, $timeSignature, $voices) {
		$this->clef = $clef;
		$this->keySignature = $keySignature;
		$this->timeSignature = $timeSignature;
		$this->voices = $voices;
	}

	
	public function serialize() {
		return serialize([$this->clef, $this->keySignature, $this->timeSignature, $this->voices]);
	}
	
	public function unserialize($data) {
		list($this->clef, $this->keySignature, $this->timeSignature, $this->voices) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}