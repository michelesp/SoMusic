<?php

class SOMUSIC_CLASS_Assignment implements Serializable, JsonSerializable {
	public $group_id;
	public $name;
	public $is_multi_user;
	
	public function __construct($group_id, $name, $is_multi_user) {
		$this->group_id;
		$this->name;
		$this->is_multi_user;
	}
	
	public function serialize() {
		return serialize([$this->group_id, $this->name, $this->is_multi_user]);
	}
	
	public function unserialize($data) {
		list($this->group_id, $this->name, $this->is_multi_user) = unserialize($data);
	}
	
	public function jsonSerialize () {
		return get_object_vars($this);
	}
	
}