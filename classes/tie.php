<?php

class SOMUSIC_CLASS_Tie implements Serializable, JsonSerializable {
	private $id;
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
	
	public function getId() {
		return $this->id;
	}
	
	public function serialize() {
		return serialize([$this->id, $this->firstMeasure, $this->firstNote, $this->lastMeasure, $this->lastNote]);
	}
	
	public function unserialize($data) {
		list($this->id, $this->firstMeasure, $this->firstNote, $this->lastMeasure, $this->lastNote) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}