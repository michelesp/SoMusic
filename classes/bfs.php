<?php

class SOMUSIC_CLASS_Bfs extends \Graphp\Algorithms\Search\BreadthFirst {
	
	public function getCloseVertices($nLevels) {
		$queue = array($this->vertex);
		$mark = array($this->vertex->getId() => true);
		$visited = array();
		$i = 0;
		do {
			$i++;
			$t = array_shift($queue);
			$visited[$t->getId()]= $t;
			foreach ($this->getVerticesAdjacent($t)->getMap() as $id => $vertex) {
				if (!isset($mark[$id])) {
					$queue[] = $vertex;
					$mark[$id] = true;
				}
			}
		} while ($queue && $i<$nLevels);
		return new \Fhaculty\Graph\Set\Vertices($visited);
	}
	
}