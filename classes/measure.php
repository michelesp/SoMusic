<?php

class SOMUSIC_CLASS_Measure implements Serializable, JsonSerializable {
	private $id;
	public $clef;
	public $keySignature;
	public $timeSignature;
	public $voices;
	
	public function __construct($id, $clef, $keySignature, $timeSignature, $voices) {
		$this->id = $id;
		$this->clef = $clef;
		$this->keySignature = $keySignature;
		$this->timeSignature = $timeSignature;
		$this->voices = $voices;
	}

	
	public function getId() {
		return $this->id;
	}
	
	public function serialize() {
		return serialize([$this->id, $this->clef, $this->keySignature, $this->timeSignature, $this->voices]);
	}
	
	public function unserialize($data) {
		list($this->id, $this->clef, $this->keySignature, $this->timeSignature, $this->voices) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}