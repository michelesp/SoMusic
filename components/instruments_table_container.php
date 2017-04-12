<?php

class SOMUSIC_CMP_InstrumentsTableContainer extends OW_Component {

	public function __construct($users, $instrumentsTable) {
		$instrumentsTable = new SOMUSIC_CMP_InstrumentsTable($users, $instrumentsTable);
		$this->assign("addURL", OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_InstrumentsTable', 'addInstrument' ));
		$this->assign("deleteURL", OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_InstrumentsTable', 'deleteInstrument' ));
		$this->assign("getURL", OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_InstrumentsTable', 'getTable' ));
		$this->assign("commitChangeURL", OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_InstrumentsTable', 'commitChange' ));
		$this->assign("changeTypeURL", OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_InstrumentsTable', 'changeType' ));
		$this->assign("changeUserURL", OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_InstrumentsTable', 'changeUser' ));
		$this->assign("instrumentsTable", $instrumentsTable->render());
	}
	
}