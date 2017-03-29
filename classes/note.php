<?php

class SOMUSIC_CLASS_Note implements Serializable, JsonSerializable {
	private $id;
	public $step;
	public $octave;
	public $accidental;
	public $duration;
	public $isRest;
	public $isTieStart;
	public $isTieEnd;
	
	public function __construct($id, $step, $octave, $accidental, $duration, $isRest, $isTieStart, $isTieEnd) {
		$this->id = $id;
		$this->step = $step;
		$this->octave = $octave;
		$this->accidental = $accidental;
		$this->duration = $duration;
		$this->isRest = $isRest;
		$this->isTieStart = $isTieStart;
		$this->isTieEnd = $isTieEnd;
	}
	
	public function serialize() {
		return serialize([$this->id, $this->step, $this->octave, $this->accidental,
				$this->duration, $this->isRest, $this->isTieStart, $this->isTieEnd]);
	}
	
	public function unserialize($data) {
		list($this->id, $this->step, $this->octave,$this->accidental, $this->duration,
				$this->isRest, $this->isTieStart, $this->isTieEnd) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}
