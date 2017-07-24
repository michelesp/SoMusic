<?php

class SOMUSIC_CTRL_MySpace extends OW_ActionController {
	
	public function index() {
		$this->setPageTitle("My space");
		$this->setPageHeading("My space");
		
		$userId = OW::getUser()->getId();
		$compositions = SOMUSIC_BOL_Service::getInstance()->getAllCompositions($userId);
		$comps = array();
		
		foreach ($compositions as $comp)
			$comps[$comp->getId()] = ($comp->name==""?"unnamed ".$undCounter++:$comp->name);
		
		$this->assign("img", OW::getPluginManager()->getPlugin('somusic')->getStaticUrl()."img/composition_icon.png");
		$this->assign("compositions", $comps);
			
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'jquery.gridly.js', 'text/javascript');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'jquery.gridly.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap.min.css');
		
		
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'icon.css');
		//OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'collapzion.min.css');
		//OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'collapzion.min.js', 'text/javascript');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'materialize.min.css');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'materialize.min.js', 'text/javascript');
	}
	
	public function addScore() {
		$editor = new SOMUSIC_CTRL_Editor();
		$composition = $editor->reset();
		SOMUSIC_BOL_Service::getInstance()->addComposition($composition->name, json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));
		exit(json_encode(array("status"=>true)));
	}
	
	public function removeScore() {
		if(!isset($_REQUEST["id"]))
			exit(json_encode(false));
		SOMUSIC_BOL_Service::getInstance()->removeComposition(intval($_REQUEST["id"]));
		exit(json_encode(true));
	}
	
	public function shareScore() {
		if(!isset($_REQUEST["id"]))
			exit(json_encode(false));
		$composition = SOMUSIC_BOL_Service::getInstance()->getComposition(intval($_REQUEST["id"]));
		$out = NEWSFEED_BOL_Service::getInstance ()->addStatus ( OW::getUser ()->getId (), "user", 1, 15, $composition->name, array (
				"content" => array(),
				"attachmentId" => null
		));
		$userId = OW::getUser()->getId();
		SOMUSIC_BOL_Service::getInstance ()->addMelodyOnPost1(intval($_REQUEST["id"]), $out['entityId']);
		exit(json_encode(true));
	}
	
}