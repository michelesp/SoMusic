<?php

class SOMUSIC_CMP_PercentageOfSimilarityWidget extends BASE_CLASS_Widget {
	
	public function __construct( BASE_CLASS_WidgetParameter $params ) {
		parent::__construct();
		$service = SOMUSIC_BOL_Service::getInstance();
		
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-grid.min.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'bootstrap-reboot.min.css');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'bootstrap.min.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'progressbar.min.js', 'text/javascript');
		
		$minValue = $service->getMinValueUsersCompositionSimilarity();
		$maxValue = $service->getMaxValueUsersCompositionSimilarity();
		
		$userId = (int) $params->additionalParamList['entityId'];
		$userId1 = OW::getUser()->getId();
		if($userId!=$userId1){
			$ucs = new SOMUSIC_CLASS_UsersCompositionsSimilarity();
			$graph = $ucs->getGraph();
			$v = $graph->getVertex($userId);
			$v1 = $graph->getVertex($userId1);
			//$edge = $v->hasEdgeFrom($v1);
			$edge = $v1->hasEdgeFrom($v);
			foreach ($graph->getEdges() as $edge) {
				if($edge->isConnection($v, $v1)) {
					//$percentage = $edge->getWeight();
					//$percentage = ($edge->getWeight()*10<=100?$percentage = $edge->getWeight()*10:100);
					$percentage = (($edge->getWeight()/10)-$minValue)*100/($maxValue-$minValue);
					break;
				}
				else $percentage = 0;
			}	
		}
		else $percentage = 100;
		
		$this->assign("percentage", $percentage);
	}
	
	public static function getStandardSettingValueList() {
		return array(
				self::SETTING_TITLE => "Percentage of similarity",
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