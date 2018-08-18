<?php

#useful to find something in children, like the shortest path

# uses queue

# Adjacency List:
$graph = array(
  'A' => array('B', 'F'),
  'B' => array('A', 'D', 'E'),
  'C' => array('F'),
  'D' => array('B', 'E'),
  'E' => array('B', 'D', 'F'),
  'F' => array('A', 'E', 'C'),
);
# Adjacency Matrix:
$graphMatrix1 = [
    [0, 1, 0, 0, 0, 1],
    [1, 0, 0, 1, 1, 0],
    [0, 0, 0, 0, 0, 1],
    [0, 1, 0, 0, 1, 0],
    [0, 1, 0, 1, 0, 1],
    [1, 0, 1, 0, 1, 0],
];
# or: 
$graphMatrix2 = [
    "A" => ["A" => 0, "B" => 1, "C" => 0, "D" => 0, "E" => 0, "F" => 1],
    "B" => ["A" => 1, "B" => 0, "C" => 0, "D" => 1, "E" => 1, "F" => 0],
    "C" => ["A" => 0, "B" => 0, "C" => 0, "D" => 0, "E" => 0, "F" => 1],
    "D" => ["A" => 0, "B" => 1, "C" => 0, "D" => 0, "E" => 1, "F" => 0],
    "E" => ["A" => 0, "B" => 1, "C" => 0, "D" => 1, "E" => 0, "F" => 1],
    "F" => ["A" => 1, "B" => 0, "C" => 1, "D" => 0, "E" => 1, "F" => 0]
];
class Graph{
    protected $praph;
    protected $visited = [];
    
    // receives graph in an adjacency list format
    public function __construct(Array $graph){
        $this->graph = $graph;
    }
    
    //find least number of hops (edges) between 2 nodes (vertices)
    public function getLeastNumberOfHops($origin, $destination){
        //mark all nodes as unvisited
        foreach($this->graph as $vertex => $adj){
            $this->visited[$vertex] = false;
        }
        
        //create an empty queue
        $q = new SplQueue();
        
        //enqueue the origin vertex and mark as visited
        $q->enqueue($origin);
        $this->visited[$origin] = true;
        
        //this is used to track the path back from each node
        $path = [];
        $path[$origin] = new SplDoublyLinkedList();
        //first in first our; don't remove when extract
        $path[$origin]->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO|SplDoublyLinkedList::IT_MODE_KEEP);
        
        $path[$origin]->push($origin);
        
        $found = false;
        //while queue is not empty and destination not found
        while(!$q->isEmpty() && $q->bottom() != $destination){
            $t = $q->dequeue();
            
            if(!empty($this->graph[$t])){ //if this node has neighbors
                //for each adjacent neighbor
                foreach($this->graph[$t] as $vertex){
                    if(!$this->visited[$vertex]){
                        //if not yet visited, enqueue the vertex and mark it as visited
                        $q->enqueue($vertex);
                        $this->visited[$vertex] = true;
                        //add vertex to current path
                        $path[$vertex] = clone $path[$t];
                        $path[$vertex]->push($vertex);
                    }
                }
            }
        }
        
        if(isset($path[$destination])){
            echo "$origin to $destination in", count($path[$destination]) - 1 , " hops \r\n";
            $sep = '';
            foreach($path[$destination] as $vertex){
                echo $sep, $vertex;
                $sep = '->';
            }
            echo "\r\n";
        }
        else{
            echo "No route from $origin to $destination";
        }
    }
}
$g = new Graph($graph);
$g->getLeastNumberOfHops("A", "E");
