<?php

# stack of plates

interface SetOfStacksInterface {
  //composed of several stacks

  public function push($value);
  public function pop();
  public function popAt($stackIndex);
}

class SetOfStacks implements SetOfStacksInterface {
  
  protected $limit;
  protected $sizes;
  protected $current_stack = 0;
  protected $stacks = [];
  
  //int
  public function __construct($limit){
    $this->limit = $limit;
    $this->stacks = [0 => new SplStack()];
    $this->sizes = [0 => 0];
  }
  
  public function push($value){  // O(1)
    $stack = $this->getStackToPush();
    
    $this->sizes[$this->current_stack] ++;
    $stack->push($value);
  }
  
  protected function getStackToPush(){
    if($this->sizes[$this->current_stack] >= $this->limit){ // move to a new stack
      $this->current_stack ++;
      $this->stacks[$this->current_stack] = new SplStack();
      $this->sizes[$this->current_stack] = 0;
    }
    
    return $this->stacks[$this->current_stack];
  }
  
  protected function getStackToPop(){
    while($this->sizes[$this->current_stack] < 1){
      $this->current_stack --;
    }
    
    return $this->stacks[$this->current_stack];
  }
  
  public function pop(){ //O(1)
    $stack = $this->getStackToPop();
    $this->sizes[$this->current_stack] --;
    return $stack->pop();
  }
  
  public function popAt($stackIndex){ //O(1)
    if($this->sizes[$stackIndex] > 0 ){
      $stack = $this->stacks[$stackIndex];
      $this->sizes[$stackIndex] --;
      return $stack->pop();
    }
    else{
      return -1;
    }
  }
}

$stack = new SetOfStacks(2);
$stack->push(1);
$stack->push(2);
$stack->push(3);
$stack->push(4);
$stack->push(5);
$stack->push(6);
$stack->push(7);
$stack->push(8);
$stack->push(9);

print_r($stack);

echo $stack->pop()."\r\n";
echo $stack->pop()."\r\n";
echo $stack->pop()."\r\n";
echo $stack->pop()."\r\n";
echo $stack->pop()."\r\n";
echo $stack->pop()."\r\n";
echo $stack->pop()."\r\n";
echo $stack->pop()."\r\n";
echo $stack->pop()."\r\n";

print_r($stack);

// brute force:
// final:
// BCR: 
