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
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'somusic.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'renderer.js', 'text/javascript' );
		OW::getDocument ()->addStyleSheet ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticCssUrl () . 'animate.css' );
		OW::getDocument ()->addStyleSheet ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticCssUrl () . 'sweetalert.css' );
		//OW::getDocument ()->addStyleSheet ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticCssUrl () . "preview.css" );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'sweetalert.min.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'vexflow-debug.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'editorData.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'visual-melody.js', 'text/javascript' );
		//OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'preview.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'preview.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'measure.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'editor.js', 'text/javascript' );
		OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'instruments_table.js', 'text/javascript' );
		// if request is Ajax, we don't need to re-execute the same code again!
		/*if (! OW::getRequest ()->isAjax ()) {
			// Add ODE.JS script to all the Oxwall pages and set THEME_IMAGES_URL variable with theme image url
			OW::getDocument ()->addScript ( OW::getPluginManager ()->getPlugin ( 'SoMusic' )->getStaticJsUrl () . 'SoMusic.js', 'text/javascript' );
		}*/
		$js = UTIL_JsGenerator::composeJsString ( '
                SoMusic.ajax_add_comment = {$ajax_add_comment}
            ', array (
				'ajax_add_comment' => OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_Ajax', 'addComment' ) 
		) );
		
		OW::getDocument ()->addOnloadScript ( $js );
		
		$js1 = UTIL_JsGenerator::composeJsString ( '
                SoMusic.ajax_update_status = {$ajax_update_status}
            ', array (
				'ajax_update_status' => OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_Ajax', 'statusUpdate' ) 
		) );
		
		OW::getDocument ()->addOnloadScript ( $js1 );
		
		$js2 = UTIL_JsGenerator::composeJsString ( '
                SoMusic.ajax_update_score = {$ajax_update_score}
            ', array (
		            		'ajax_update_score' => OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_Ajax', 'updateScore' )
		            ) );
		
		OW::getDocument ()->addOnloadScript ( $js2 );
		
		$js3 = UTIL_JsGenerator::composeJsString ( '
                SoMusic.saveAssignmentURL = {$saveAssignmentURL}
            ', array (
		            		'saveAssignmentURL' => OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_AssignmentManager', 'saveNewAssignment' )
		            ) );
		
		OW::getDocument ()->addOnloadScript ( $js3 );
		$js4 = UTIL_JsGenerator::composeJsString ( '
                SoMusic.commitExecutionURL = {$commitExecutionURL}
            ', array (
		            		'commitExecutionURL' => OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_AssignmentManager', 'commitExecution' )
		            ) );
		
		OW::getDocument ()->addOnloadScript ( $js4 );
		$js5 = UTIL_JsGenerator::composeJsString ( '
                SoMusic.editExecutionURL = {$editExecutionURL}
            ', array (
		            		'editExecutionURL' => OW::getRouter ()->urlFor ( 'SOMUSIC_CTRL_AssignmentManager', 'editExecution' )
		            ) );
		
		OW::getDocument ()->addOnloadScript ( $js5 );
	}
	public function onItemRender(OW_Event $event) {
		$params = $event->getParams ();
		$data = $event->getData ();
	    $postId = $params['action']['entityId'];
	    $composition = SOMUSIC_BOL_Service::getInstance()->getScoreByPostId($postId);
	    if (isset($composition)) {
	    	$data['content']['vars']['status'] .= '<span class = "zoomIn animated"><div class="score_placeholder" id="score_placeholder_' . $postId . '" style = "overflow-x: auto; overflow-y: auto;' . '"></div></span>';
	    	$composition = SOMUSIC_CLASS_Composition::getCompositionObject($composition);
	    	OW::getDocument ()->addOnloadScript ( 'SoMusic.loadScore(' . json_encode($composition) . ',"score_placeholder_' . $postId . '","' . $composition->name . '");
				document.getElementById("score_placeholder_' . $postId . '").addEventListener("click", function(e) {
					if(typeof previewFloatBox != "undefined" || document.getElementsByName("floatbox_canvas").length > 0) {
	                    $(".floatbox_canvas").each(function(i, obj) {
	                        obj.style.display = "block";
	                    });
	                    if(document.getElementById("floatbox_overlay") != null)
	                        document.getElementById("floatbox_overlay").style.display = "block";
	                    var fb = SoMusic.floatBox.pop();
						fb.close();
	                    //delete previewFloatBox;
	                }
					SoMusic.idPost = '.$postId.';
	    			console.log('.$composition->getId().');
					SoMusic.floatBox.push({"name":"Editor", "floatBox":OW.ajaxFloatBox("SOMUSIC_CMP_Editor",
						{"compositionId": '.$composition->getId().'},
						{top:"calc(5vh)", width:"calc(80vw)", height:"calc(85vh)", iconClass: "ow_ic_add", title: ""})});
					document.getElementById("vm_placeholder").style.display = "none";
				});' );
	    }
		$event->setData ( $data );
	}
	public function onBeforePostDelete(OW_Event $event) {
		var_dump ( $event );
		$params = $event->getParams ();
		SOMUSIC_BOL_Service::getInstance ()->deleteScoreById ( $params ['entityId'] );
	}
	
}
