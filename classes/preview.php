<?php

class SOMUSIC_CLASS_Preview implements Serializable {
	public $timeSignature;
	public $keySignature;
	public $instrumentsTable;
	public $multiUserMod;
	public $groupId;
	
	public function __construct($timeSignature, $keySignature, $instrumentsTable, $multiUsedMod = false, $groupId = -1) {
		$this->timeSignature = $timeSignature;
		$this->keySignature = $keySignature;
		$this->instrumentsTable = $instrumentsTable;
		$this->multiUserMod = $multiUsedMod;
		$this->groupId = $groupId;
	}
	
	public function serialize() {
		return serialize([$this->timeSignature, $this->keySignature, $this->instrumentsTable,
				$this->multiUserMod, $this->groupId]);
	}
	
	public function unserialize($data) {
		list($this->timeSignature, $this->keySignature, $this->instrumentsTable,
				$this->multiUserMod, $this->groupId) = unserialize($data);
	}
	
}