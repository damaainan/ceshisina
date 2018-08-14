<?php

# Heap is a binary tree, where all childs' keys should be less (or bigger) that their parents'.
# Heaps are filled in order, top to bottom and left to right
# When we add a new value, we compare it's key to it's parent's, and if it doesn't fit in the structure, 
# we swap it with the parent, and then with it's parent
# Min heap: lowest key at the top, Max heap: biggest key at the top
# Very useful for implementing Priority queues
# 
# Time complexity:
# insert: O(log n)
# find (top) peek: O(1)
# extract: O(log n)
#
# Animation:
# https://www.cs.usfca.edu/~galles/visualization/Heap.html

// Modified version of http://www.sitepoint.com/data-structures-3/

class Heap {
	
	protected $heap;
	
	protected $isMinHeap = false; // max heap by default
	
	public function __construct($isMinHeap = false){
		$this->heap = array();
		$this->isMinHeap = (bool)$isMinHeap;
	}
	
	public function add($key, $value){
		$this->heap[] = [$key, $value];
		$this->adjustBottomToTop($this->lastIndex());
		$this->adjustTopToBottom(0);
	}
	
	public function extract(){
		if($this->isEmpty()){
			throw new RunTimeException('The heap is empty!');
		}
		
		$rootNode = array_shift($this->heap);
		
		if(!$this->isEmpty()){
			// move last item into the root so the heap is no longer disjointed
			$lastNode = array_pop($this->heap);
			array_unshift($lastNode, $lastNode);
			
			$this->adjustTopToBottom(0);
		}
		return $rootNode[1];
	}
	
	public function count(){
		return count($this->heap);
	}
	
	public function lastIndex(){
	    if($this->count() == 0){
	        return 0;
	    }
	    return $this->count() - 1;
	}
	
	protected function indexOfParent($index){
	    $z = ($index % 2 > 0) ? 1 : 2;
	    $x = ($index - $z) / 2;
	    return ($x >= 0) ? $x : 0;
	}
	
	protected function compare($item1, $item2){
		if($item1 == $item2){
			return 0;
		}
		
		$compareValue =  ($item1[0] > $item2[0] ? 1 : -1);
		if($this->isMinHeap){
			$compareValue *= -1;
		}
		
		return $compareValue;
	}
	
	protected function adjustBottomToTop($nodeIndex){
	    if($nodeIndex != 0){
	        $parentIndex = $this->indexOfParent($nodeIndex);
	        
	        $h = $this->heap;
	        
	        if( $this->compare($h[$parentIndex], $h[$nodeIndex]) < 0 ){
	            list($this->heap[$parentIndex], $this->heap[$nodeIndex]) = [$this->heap[$nodeIndex], $this->heap[$parentIndex]];
	            
	            $this->adjustBottomToTop($parentIndex);
	        }
	    }
	}
	
	protected function adjustTopToBottom($rootIndex){
		// we've gone as far as we can down the tree if root is a leaf
		if(!$this->isLeaf($rootIndex)){
			$leftIndex = (2 * $rootIndex) + 1;
			$rightIndex = (2 * $rootIndex) + 2;
			
			//if root is less/bigger than either of it's children
			$h = $this->heap;
			if(
				(isset($h[$leftIndex]) && $this->compare($h[$rootIndex], $h[$leftIndex]) < 0) ||
				(isset($h[$rightIndex]) && $this->compare($h[$rootIndex], $h[$rightIndex]) < 0)
				){
				//find the larger/smaller child
				if(isset($h[$leftIndex]) && isset($h[$rightIndex])){
					$j = ($this->compare($h[$leftIndex], $h[$rightIndex]) >= 0) ? $leftIndex : $rightIndex;
				}
				elseif(isset($h[$leftIndex])){
					$j = $leftIndex; // left child only
				}
				else{
					$j = $rightIndex; // rigth child only
				}
				
				//swap places with root
				list($this->heap[$rootIndex], $this->heap[$j]) = [$this->heap[$j], $this->heap[$rootIndex]];
				
				//recursively adjust semiheap rooted at new node j
				$this->adjustTopToBottom($j);
			}
			// if not, we stop
		}
		//or we stop because the heap is optimized
	}
	
	protected function isLeaf($index){
		// there will always be 2n + 1 nodes in the sub-heap
		return ((2 * $index) + 1) > $this->count();
	}
	
	protected function isEmpty(){
		return empty($this->heap);
	}
	
}

$maxHeap = new Heap();
$maxHeap->add(1, 'first');
$maxHeap->add(3, 'second');
$maxHeap->add(2, 'third');
$maxHeap->add(4, 'fourth');
$maxHeap->add(5, 'fifth');
$maxHeap->add(8, 'sixth');
$maxHeap->add(6, 'seventh');
$maxHeap->add(7, 'eighth');

//print_r($maxHeap);die();

echo $maxHeap->extract()."\r\n"; //sixth
echo $maxHeap->extract()."\r\n"; //eighth
$maxHeap->add(10, 'nineth');
echo $maxHeap->extract()."\r\n"; //nineth

echo "\r\n";

$minHeap = new Heap(true);
$minHeap->add(10, 'first');
$minHeap->add(3, 'second');
$minHeap->add(7, 'third');
$minHeap->add(4, 'fourth');
$minHeap->add(5, 'fifth');
$minHeap->add(9, 'sixth');
$minHeap->add(1, 'seventh');
$minHeap->add(6, 'eighth');

// print_r($minHeap);die();

echo $minHeap->extract()."\r\n"; //seventh
echo $minHeap->extract()."\r\n"; //second
$minHeap->add(2, 'nineth');
echo $minHeap->extract()."\r\n"; //nineth
