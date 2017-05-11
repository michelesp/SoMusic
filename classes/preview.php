<?php

class SOMUSIC_CLASS_Preview implements Serializable {
	public $timeSignature;
	public $keySignature;
	public $instrumentsTable;
	public $multiUserMod;
	public $groupId;
	public $importedComposition;
	
	public function __construct($timeSignature, $keySignature, $instrumentsTable, $multiUsedMod = false, $groupId = -1, $importedComposition = null) {
		$this->timeSignature = $timeSignature;
		$this->keySignature = $keySignature;
		$this->instrumentsTable = $instrumentsTable;
		$this->multiUserMod = $multiUsedMod;
		$this->groupId = $groupId;
		$this->importedComposition = $importedComposition;
	}
	
	public function serialize() {
		return serialize([$this->timeSignature, $this->keySignature, $this->instrumentsTable,
				$this->multiUserMod, $this->groupId, $this->importedComposition]);
	}
	
	public function unserialize($data) {
		list($this->timeSignature, $this->keySignature, $this->instrumentsTable,
				$this->multiUserMod, $this->groupId, $this->importedComposition) = unserialize($data);
	}
	
}