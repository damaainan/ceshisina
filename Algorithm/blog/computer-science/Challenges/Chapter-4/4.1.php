<?php

# route between nodes

//just a Depth First search

class Graph {
  public $nodes = [];
}

class GraphNode {
  public $adjacent = [];
  public $visited = false;
  public $name;
  public function __construct($name){
      $this->name = $name;
  }
}

function findRoute(GraphNode $nodeA, GraphNode $nodeB){
  
  $queue = new SplQueue();
  
  foreach($nodeA->adjacent as $node){
    $queue->enqueue($node);
  }
  
  while(!$queue->isEmpty()){
    $node = $queue->dequeue();
    if(!$node->visited){
      if($node == $nodeB){
        return true;
      }
      $node->visited = true;
      foreach($node->adjacent as $adjacent){
        $queue->enqueue($adjacent);
      }
    }
  }
  
  return false;
  
}

$nodeA = new GraphNode('A');
$nodeB = new GraphNode('B');
$nodeC = new GraphNode('C');
$nodeD = new GraphNode('D');
$nodeE = new GraphNode('E');
$nodeF = new GraphNode('F');
$nodeG = new GraphNode('G');
$nodeH = new GraphNode('H');

$nodeA->adjacent = [$nodeB, $nodeC];
$nodeB->adjacent = [$nodeC, $nodeD];
$nodeC->adjacent = [$nodeD];
$nodeD->adjacent = [$nodeE];
//$nodeF->adjacent = [];
$nodeG->adjacent = [$nodeF, $nodeH];
$nodeH->adjacent = [$nodeG];

//var_dump(findRoute($nodeA, $nodeH));
var_dump(findRoute($nodeA, $nodeE));


// O(n + k), k - edges
