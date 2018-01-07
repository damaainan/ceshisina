<?php 
//数据结构之最小树生成(用php描述) 

try {
    $graph = new \DataStructure\Graph();
    $vertexArr = [
        [1, 4, 1],
        [1, 2, 2],
        [4, 2, 3],
        [1, 3, 4],
        [3, 4, 2],
        [3, 6, 5],
        [4, 6, 8],
        [6, 7, 1],
        [4, 7, 4],
        [7, 5, 6],
        [2, 5, 10],
    ];
    foreach ($vertexArr as $arr) {
        $edge = new \DataStructure\Edge(new \DataStructure\Vertex($arr[0]), new \DataStructure\Vertex($arr[1]), $arr[2]);
        $graph->addEdge($edge);
    }
    $tree = new \DataStructure\Tree($graph);
    $minimalSpanningTree = $tree->getMinimalSpanningTree();
    /**@var \DataStructure\Edge $edge */
    foreach ($minimalSpanningTree as $edge) {
        echo implode(',', [$edge->getSource()->getNumber(), $edge->getTarget()->getNumber(), $edge->getWeight()]);
        echo '<br/>';
    }
} catch (\Exception $e) {
    var_dump($e->getMessage());
}