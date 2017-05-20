<?php

class SOMUSIC_CLASS_Note implements Serializable, JsonSerializable {
	public $step;
	public $octave;
	public $accidental;
	public $duration;
	public $isTieStart;
	public $isTieEnd;
	public $dots;
	public $text;
	
	public function __construct($duration, $step, $octave, $accidental, $isTieStart=-1, $isTieEnd=-1, $dots=0, $text=null) {
		$this->duration = $duration;
		$this->step = $step;
		$this->octave = $octave;
		$this->accidental = $accidental;
		$this->isTieStart = $isTieStart;
		$this->isTieEnd = $isTieEnd;
		$this->dots = $dots;
		$this->text = $text;
	}
	
	public function serialize() {
		return serialize([$this->duration,$this->step, $this->octave, $this->accidental,
				$this->isTieStart, $this->isTieEnd, $this->dots, $this->text]);
	}
	
	public function unserialize($data) {
		list($this->duration, $this->step, $this->octave, $this->accidental, 
				$this->isTieStart, $this->isTieEnd, $this->dots, $this->text) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}
