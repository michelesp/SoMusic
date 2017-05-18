<?php

class SOMUSIC_CLASS_EventHandler {
	private static $classInstance;
	
	public static function getInstance() {
		if (self::$classInstance === null) {
			self::$classInstance = new self();
		}
		return self::$classInstance;
	}
	
	// Handle event and route
	public function init() {
		// event that allows returning a component to replace the standard status update form
		OW::getEventManager()->bind('feed.get_status_update_cmp', array(
				$this,
				'onStatusUpdateCreate' 
		));
		OW::getEventManager()->bind(OW_EventManager::ON_APPLICATION_INIT, array(
				$this,
				'onApplicationInit' 
		));
		OW::getEventManager()->bind('feed.on_item_render', array(
				$this,
				'onItemRender' 
		));
		OW::getEventManager()->bind('feed.before_action_delete', array(
				$this,
				'onBeforePostDelete' 
		));
	}
	
	// Replace the newsfeed form
	public function onStatusUpdateCreate(OW_Event $event) {
		$params = $event->getParams();
		
		if (OW::getApplication()->isMobile()) {
			// TODO MOBILE PAGE REQUEST
		} else {
			$ret = new SOMUSIC_CMP_UpdateStatus($params['feedAutoId'], $params['entityType'], $params['entityId'], $params['visibility']);
		}
		
		$event->setData($ret);
		return $ret;
	}
	
	public function onApplicationInit(OW_Event $event) {
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'animate.css');
		OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('somusic')->getStaticCssUrl().'sweetalert.css');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'somusic.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'renderer.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'vexflow-debug.js', 'text/javascript');
		//OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'visual-melody.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'preview.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'measure.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'editor.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'instruments_table.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('somusic')->getStaticJsUrl().'sweetalert.min.js', 'text/javascript');

		$jsGenerator = UTIL_JsGenerator::newInstance();
		$jsGenerator->setVariable("SoMusic.ajax_add_comment", OW::getRouter()->urlFor('SOMUSIC_CTRL_Ajax', 'addComment'));
		$jsGenerator->setVariable(" SoMusic.ajax_update_status", OW::getRouter()->urlFor('SOMUSIC_CTRL_Ajax', 'statusUpdate'));
		$jsGenerator->setVariable("SoMusic.ajax_update_score", OW::getRouter()->urlFor('SOMUSIC_CTRL_Ajax', 'updateScore'));
		$jsGenerator->setVariable("SoMusic.saveAssignmentURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_AssignmentManager', 'saveNewAssignment'));
		$jsGenerator->setVariable("SoMusic.commitExecutionURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_AssignmentManager', 'commitExecution'));
		$jsGenerator->setVariable("SoMusic.editExecutionURL", OW::getRouter()->urlFor('SOMUSIC_CTRL_AssignmentManager', 'editExecution'));
		OW::getDocument()->addOnloadScript($jsGenerator->generateJs());
	}
	
	public function onItemRender(OW_Event $event) {
		$params = $event->getParams();
		$data = $event->getData();
	    $postId = $params['action']['entityId'];
	    $composition = SOMUSIC_BOL_Service::getInstance()->getScoreByPostId($postId);
	    if (isset($composition)) {
	    	$data['content']['vars']['status'] .= '<span class = "zoomIn animated"><div class="score_placeholder" id="score_placeholder_'.$postId.'" style = "overflow-x: auto; overflow-y: auto;' . '"></div></span>';
	    	$composition = SOMUSIC_CLASS_Composition::getCompositionObject($composition);
	    	OW::getDocument()->addOnloadScript('SoMusic.loadScore('.json_encode($composition).',"score_placeholder_'.$postId.'", '.$postId.');');
	    }
		$event->setData($data);
	}
	
	public function onBeforePostDelete(OW_Event $event) {
		$params = $event->getParams();
		SOMUSIC_BOL_Service::getInstance()->deleteScoreById($params['entityId']);
	}
	
}
