<?php

# usually need this to fetch/traverse all nodes, bad suite to find anything

# start with retreiving all child nodes of the first node and all it's child nodes till the end

# Depth First Traversal (or Search) for a graph is similar to Depth First Traversal of a tree. The only catch here is, 
# unlike trees, graphs may contain cycles, so we may come to the same node again. To avoid processing a node more than once, 
# we use a boolean visited array. 

# The depth first search is well geared towards problems where we want to find any solution to the problem 
# (not necessarily the shortest path), or to visit all of the nodes in the graph
# It is a key property of the Depth First search that we not visit the same node more than once
# The basic concept is to visit a node, then push all of the nodes to be visited onto the stack. 
# To find the next node to visit we simply pop a node of the stack

# Time complexity: O(N + m) // nodes and edges

# Animation:
# https://www.cs.usfca.edu/~galles/visualization/DFS.html

$graph = array(
  'A' => array('B', 'F'),
  'B' => array('A', 'D', 'E'),
  'C' => array('F'),
  'D' => array('B', 'E'),
  'E' => array('B', 'D', 'F'),
  'F' => array('A', 'E', 'C'),
);

class DepthFirstSearch {

    protected $praph;
    protected $visited = [];
    
    // receives graph in an adjacency list format
    public function __construct(Array $graph){
        $this->graph = $graph;
    }
    
    function getAllAccessibleNodes($origin){
        $this->visited = [];
        $this->traverseAdjacentNodes($origin);
        
        return array_keys($this->visited);
    }
    
    private function traverseAdjacentNodes($origin){
        $this->visited[$origin] = true;
        
        $children = $this->graph[$origin];
        foreach($children as $child){
            if(!isset($this->visited[$child])){
                $this->traverseAdjacentNodes($child);
            }
        }
    }
}


$g = new DepthFirstSearch($graph);
print_r($g->getAllAccessibleNodes("A"));
