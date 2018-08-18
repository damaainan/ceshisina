<?php

# Animation:
# BFS: https://www.cs.usfca.edu/~galles/visualization/BFS.html
# Dijkstra: https://www.cs.usfca.edu/~galles/visualization/Dijkstra.html
# Time complexity: O(N + m) // nodes and edges

# Finding the Shortest-Path in a weightened graph
# this is a variation of the breadth-first search

$graph = array(
  'A' => array('B' => 3, 'D' => 3, 'F' => 6),
  'B' => array('A' => 3, 'D' => 1, 'E' => 3),
  'C' => array('E' => 2, 'F' => 3),
  'D' => array('A' => 3, 'B' => 1, 'E' => 1, 'F' => 2),
  'E' => array('B' => 3, 'C' => 2, 'D' => 1, 'F' => 5),
  'F' => array('A' => 6, 'C' => 3, 'D' => 2, 'E' => 5),
);

# Dijkstra algorithm: examin each edge between all possible pairs of vertices starting from 
# the source node and maintaining an updated set of vertices with the shortest total distance until 
# the target node is reached, or not reached, whichever the case may be.

# implementation using a PriorityQueue to maintain a list of all “unoptimized” vertices:
// todo: this implementation is from http://www.sitepoint.com/data-structures-4/ and it seems wierd

class Dijkstra {
    
    protected $graph;
    
    
    public function __construct($graph){
        $this->graph = $graph;
    }
    
    
    public function shortestPath($source, $target){
        //array of best estimates of shortest path to each vertex
        $best_estimates = [];
        //array of predecessors for each vertex
        $predecessors = [];
        //queue of all unoptimized vertices
        $q = new SplPriorityQueue();
        
        foreach($this->graph as $vertex => $adj){
            $best_estimates[$vertex] = INF; // set initial value to infinity
            $predecessors[$vertex] = null; // no known predecessors yet
            foreach($adj as $adjacent_vertex => $cost){
                //use the edge cost as the priority
                $q->insert($adjacent_vertex, $cost);
            }
        }
        
        //initial distance at source is 0
        $best_estimates[$source] = 0;
        
        while(!$q->isEmpty()){//while there's something in the queue
            //extract a vertex with a minimum cost (always on the top of the queue)
            $min_cost_vertex = $q->extract(); //this is a currently unoptimized vertext with the minimal cost of travel in some 1 direction
            if(!empty($this->graph[$min_cost_vertex])){  // if there're any adjacent vertices
                //optimize the route to each adjacent vertex
                foreach($this->graph[$min_cost_vertex] as $vertex => $cost){
                    //get an alternate route length to adjacent neighbor
                    $alternate_cost = $best_estimates[$min_cost_vertex] + $cost; //it will be infinity unless the route was started from the $source
                    //if alternate route is shorter
                    if($alternate_cost < $best_estimates[$vertex]){
                        $best_estimates[$vertex] = $alternate_cost; // update  - the minimuc cost to reach this vertex is this
                        $predecessors[$vertex] = $min_cost_vertex; // update - the best predecessor to reach this vertex is this
                    }
                }
            }
        }
        
        //we can now find the shortest path using reverse iteration
        $shortest_path = new SplStack();
        $current_vertex = $target;
        $distance = 0;
        //traverce from target to source
        while(!empty($predecessors[$current_vertex])){
            //echo "adding an edge $current_vertex - {$predecessors[$current_vertex]} ".$this->graph[$current_vertex][$predecessors[$current_vertex]]."\r\n";
            $shortest_path->push($current_vertex);
            $distance += $this->graph[$current_vertex][$predecessors[$current_vertex]]; //add the edge's cost to the overall distance
            $current_vertex = $predecessors[$current_vertex];
        }
        
        //stack will be empty if there's no route back from the target
        if($shortest_path->isEmpty()){
            echo "No route from $source to $target\r\n";
        }
        else{
            //add the source node and print the path in reverse
            $shortest_path->push($source);
            echo "$distance:";
            $sep = '';
            foreach($shortest_path as $vertex){
                echo $sep, $vertex;
                $sep = '->';
            }
            echo "\r\n";
        }
        
    }
    
}


$g = new Dijkstra($graph);

$g->shortestPath('A', 'C'); //6:A->D->E->C


