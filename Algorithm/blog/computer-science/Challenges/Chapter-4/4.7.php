<?php

# topological sort

define('STATUS_BLANK', 1);
define('STATUS_PROCESSING', 2);
define('STATUS_VISITED', 3);

class GraphNode{
  public $value = null;
  public $status = STATUS_BLANK;
  public $adjacent = [];
  
  public function __construct($name){
      $this->name = $name;
  }
}

class Graph{
  public $nodes = [];
}

function setStatusesToBlank(Graph $graph){ // O(n)
  foreach($graph->nodes as $node){
    if($node->status !== STATUS_VISITED){
      $node->status = STATUS_BLANK;
    }
  }
}

function runDFS(GraphNode $node, SplStack $order){ // O(m)
    if($node->status == STATUS_PROCESSING){
      return false;
    }
    if($node->status == STATUS_BLANK){
        $node->status = STATUS_PROCESSING;
        foreach($node->adjacent as $adjacent){
          if(!runDFS($adjacent, $order)){
              return false;
          }
        }
        $order->push($node->name);
        $node->status = STATUS_VISITED;
    }
    return true;
}

function findProjectsOrder(Graph $graph){
    $order = new SplStack;
    $nodes = $graph->nodes;
    foreach($graph->nodes as $node){ //O(n)
      setStatusesToBlank($graph);
      if($node->status == STATUS_BLANK){
          if(!runDFS($node, $order)){
              return false;
          }
      }
    }
    return $order;
}




$nodeA = new GraphNode('A');
$nodeB = new GraphNode('B');
$nodeC = new GraphNode('C');
$nodeD = new GraphNode('D');
$nodeE = new GraphNode('E');
$nodeF = new GraphNode('F');
$nodeG = new GraphNode('G');
$nodeH = new GraphNode('H');
$nodeA->adjacent = [$nodeD];
$nodeB->adjacent = [$nodeE];
$nodeC->adjacent = [];
$nodeD->adjacent = [$nodeF];
$nodeE->adjacent = [$nodeG];
$nodeF->adjacent = [$nodeH];
$nodeG->adjacent = [];
$nodeH->adjacent = [];//[$nodeA];

$graph = new Graph();
$graph->nodes = [$nodeA, $nodeB, $nodeC, $nodeD, $nodeE, $nodeF, $nodeG, $nodeH];

$order = findProjectsOrder($graph);
if(!empty($order)){
    $order->rewind();
    while($order->valid()){
        echo "{$order->current()} , ";
        $order->next();
    }
}
else{
    echo "none found";
}


// O(n + m) , m - number of edges
// because we visit each node not more than once
// the same runtime as in DFS because we use it slightly modified
// DFS because it's more easy than BFS and allows to use recursion
