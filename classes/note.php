<?php

class SOMUSIC_CLASS_Note implements Serializable, JsonSerializable {
	public $duration;
	public $step;
	public $octave;
	public $accidental;
	public $isTieStart;
	public $isTieEnd;
	public $dots;
	public $text;
	public $color;
	
	public function __construct($duration, $step, $octave, $accidental, $isTieStart=-1, $isTieEnd=-1, $dots=0, $text=null, $color=null) {
		$this->duration = $duration;
		$this->step = $step;
		$this->octave = $octave;
		$this->accidental = $accidental;
		$this->isTieStart = $isTieStart;
		$this->isTieEnd = $isTieEnd;
		$this->dots = $dots;
		$this->text = $text;
		$this->color = $color;
	}
	
	public function serialize() {
		return serialize([$this->duration, $this->step, $this->octave, $this->accidental,
				$this->isTieStart, $this->isTieEnd, $this->dots, $this->text, $this->color]);
	}
	
	public function unserialize($data) {
		list($this->duration, $this->step, $this->octave, $this->accidental, 
				$this->isTieStart, $this->isTieEnd, $this->dots, $this->text, $this->color) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}
