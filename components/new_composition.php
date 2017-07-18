<?php

class SOMUSIC_CMP_NewComposition extends OW_Component {

	public function __construct() {
		$form = new Form("new_composition_form");
		$name = new TextField("name");
		$name->setLabel("Name: ");
		$form->addElement($name);
		$this->addForm($form);
		$this->assign("form", $form);
	}

}