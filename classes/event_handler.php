<?php
class SOMUSIC_CLASS_EventHandler {
	private static $classInstance;
	public static function getInstance() {
		if (self::$classInstance === null) {
			self::$classInstance = new self ();
		}
		
		return self::$classInstance;
	}
	
	// Handle event and route
	public function init() {
		// event that allows returning a component to replace the standard status update form
		OW::getEventManager ()->bind ( 'feed.get_status_update_cmp', array (
				$this,
				'onStatusUpdateCreate' 
		) );
		OW::getEventManager ()->bind ( OW_EventManager::ON_APPLICATION_INIT, array (
				$this,
				'onApplicationInit' 
		) );
		OW::getEventManager ()->bind ( 'feed.on_item_render', array (
				$this,
				'onItemRender' 
		) );
		OW::getEventManager ()->bind ( 'feed.before_action_delete', array (
				$this,
				'onBeforePostDelete' 
		) );
	}
	
	// Replace the newsfeed form
	public function onStatusUpdateCreate(OW_Event $event) {
		$params = $event->getParams ();
		
		if (OW::getApplication ()->isMobile ()) {
			// TODO MOBILE PAGE REQUEST
		} else {
			$ret = new SOMUSIC_CMP_UpdateStatus ( $params ['feedAutoId'], $params ['entityType'], $params ['entityId'], $params ['visibility'] );
		}
		
		$event->setData ( $ret );
		return $ret;
	}
	public function onApplicationInit(OW_Event $event) {
		OW::getDocument ()->addStyleSheet ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticCssUrl () . 'animate.css' );
		OW::getDocument ()->addStyleSheet ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticCssUrl () . 'sweetalert.css' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'sweetalert.min.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'vexflow-debug.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'editorData.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'visual-melody.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'measure.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'editor.js', 'text/javascript' );
		// if request is Ajax, we don't need to re-execute the same code again!
		if (! OW::getRequest ()->isAjax ()) {
			// Add ODE.JS script to all the Oxwall pages and set THEME_IMAGES_URL variable with theme image url
			OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'visualmelody.js', 'text/javascript' );
		}
		$js = UTIL_JsGenerator::composeJsString ( '
                VISUALMELODY.ajax_add_comment = {$ajax_add_comment}
            ', array (
				'ajax_add_comment' => OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_Ajax', 'addComment' ) 
		) );
		
		OW::getDocument ()->addOnloadScript ( $js );
		
		$js1 = UTIL_JsGenerator::composeJsString ( '
                VISUALMELODY.ajax_update_status = {$ajax_update_status}
            ', array (
		            		'ajax_update_status' => OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_Ajax', 'statusUpdate' )
		            ) );
		
		OW::getDocument ()->addOnloadScript ( $js1 );
	}
	public function onItemRender(OW_Event $event) {
		$params = $event->getParams ();
		$data = $event->getData ();
		$scoreData = SOMUSIC_BOL_Service::getInstance ()->getScoreByPostId ( $params ['action'] ['entityId'] );
		if (! empty ( $scoreData )) {
			$data ['content'] ['vars'] ['status'] .= '<span class = "zoomIn animated"><div class="score_placeholder" id="score_placeholder_' . $scoreData ['id_post'] . '" style = "overflow-x: auto; overflow-y: hidden;' . '"></div></span>';
			OW::getDocument ()->addOnloadScript ( 'VISUALMELODY.loadScore(' . $scoreData ['data'] . ',"score_placeholder_' . $scoreData ['id_post'] . '","' . $scoreData ['title'] . '");' );
		}
		$event->setData ( $data );
	}
	public function onBeforePostDelete(OW_Event $event) {
		var_dump ( $event );
		$params = $event->getParams ();
		SOMUSIC_BOL_Service::getInstance ()->deleteScoreById ( $params ['entityId'] );
	}
}
