<?php

class SOMUSIC_CMP_CompositionWidget extends BASE_CLASS_Widget {

	public function __construct(BASE_CLASS_WidgetParameter $params) {
		parent::__construct();
		$service = SOMUSIC_BOL_Service::getInstance();

		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-grid.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-reboot.min.css');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'bootstrap.min.js', 'text/javascript');
	
		if($userId==null)
			$userId = OW::getUser()->getId();
		
		$compositions = SOMUSIC_BOL_Service::getInstance()->getAllCompositions($userId);
		$compNames = array();
		$undCounter = 1;
		//TODO: comp->name=="" sempre; la lettura dal database errore
		foreach ($compositions as $comp)
			$compNames[$comp->getId()] = ($comp->name==""?"unnamed ".$undCounter++:$comp->name);
		$this->assign("compNames", $compNames);
	}

	public static function getStandardSettingValueList() {
		return array(
				self::SETTING_TITLE => "Compositions",
				//self::SETTING_ICON => self::ICON_CALENDAR,
				self::SETTING_SHOW_TITLE => true,
				self::SETTING_WRAP_IN_BOX => true
		);
	}

	public static function getAccess()
	{
		return self::ACCESS_ALL;
	}


}