<?php

class SOMUSIC_CLASS_Composition implements Serializable, JsonSerializable {
	private $id;
	private $user_c;
	private $timestamp_c;
	public $name;
	public $user_m;
	public $timestamp_m;
	public $instrumentsScore;
	
	
	public function __construct($id, $name, $user_c, $timestamp_c, $user_m, $timestamp_m, $instrumentsScore) {
		$this->id = $id;
		$this->name = $name;
		$this->user_c = $user_c;
		$this->timestamp_c = $timestamp_c;
		$this->user_m = $user_m;
		$this->timestamp_m = $timestamp_m;
		$this->instrumentsScore = $instrumentsScore;
	}
	
	
	public function getId() {
		return $this->id;
	}
	
	public function getUserC() {
		return $this->user_c;
	}
	
	public function getTimestampC() {
		return $this->timestamp_c;
	}
	
	public function serialize() {
		return serialize([$this->id, $this->user_c, $this->timestamp_c, $this->name,
				$this->user_m, $this->timestamp_m, $this->instrumentsScore]);
	}
	
	public function unserialize($data) {
		list($this->id, $this->user_c, $this->timestamp_c, $this->name,$this->user_m,
			$this->timestamp_m, $this->instrumentsScore) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}
