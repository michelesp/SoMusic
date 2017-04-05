<?php

class SOMUSIC_CMP_NewAssignment extends OW_Component {
	
	public function __construct($groupId) {
		$form = new Form("new_assignment_form");
		$name = new TextField("name");
		$name->setLabel("Name: ");
		$form->addElement($name);
		$this->addForm($form);
		$this->assign("form", $form);
		$this->assign("groupId", $groupId);
		$this->assign("newAssignmentURL", OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_NewAssignment', 'newAssignment'));
	}
	
}