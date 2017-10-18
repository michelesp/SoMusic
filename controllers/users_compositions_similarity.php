<?php

class SOMUSIC_CTRL_UsersCompositionsSimilarity extends OW_ActionController {
	
	
	public function index() {
		$this->setPageTitle("Users Compositions Similarity");
		
		$ucs = new SOMUSIC_CLASS_UsersCompositionsSimilarity();
		$graph = $ucs->getGraph();
		
		$bfs = new SOMUSIC_CLASS_Bfs($graph->getVertex(OW::getUser()->getId()));
		$vertices = $bfs->getCloseVertices(5);
		
		$graph = $this->graphToJSON($graph, $vertices);
		
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('SoMusic')->getStaticJsUrl () . 'd3.v4.min.js', 'text/javascript');
		OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('SoMusic')->getStaticJsUrl () . 'graph_viewer.js', 'text/javascript');
		OW::getDocument()->addOnloadScript('load('.json_encode($graph).');');
	}
	
	
	private function graphToJSON($graph, $vertices) {
		$nodes = array();
		$links = array();
		$userId = OW::getUser()->getId();
		$baseUrl = OW::getRouter()->getBaseUrl()."user/";
		foreach ($vertices as $v) {
			$username = $v->getAttribute("username");
			
			$avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($v->getId()));
			$avatarDto = BOL_AvatarService::getInstance()->findByUserId($v->getId());
			$avatarData[$v->getId()]['src'] = BOL_AvatarService::getInstance()->getAvatarUrl($v->getId(), 1, null, true, false);
			$default_avatar['src'] = BOL_AvatarService::getInstance()->getDefaultAvatarUrl(1);
			
			$node = array("id"=>$username, "url"=>$baseUrl.$username, "img"=>!empty($avatarData[$v->getId()]['src']) ? $avatarData[$v->getId()]['src'] : $default_avatar["src"]);
			if($v->getId()==$userId)
				$node["root"] = true;
			array_push($nodes, (object)$node);
			$edges = $v->getEdgesOut();
			foreach ($edges as $e) {
				$username2 = $e->getVertexFromTo($v)->getAttribute("username");
				array_push($links, (object)array("source"=>$username, "target"=>$username2, "value"=>$e->getWeight()));
			}
		}
		return (object)array("nodes"=>$nodes, "links"=>$links);
	}
	
}