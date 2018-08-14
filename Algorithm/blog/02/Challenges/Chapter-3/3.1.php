<?php

# three in one: use a single array to implement three stacks
# 2
# 12
# 38
# 58

// c) store the links to the begining oa stacks in first elements on the array, and store atacks inline. can make the array circular, if need to extend the last stack go to the beginnig to store part of it's values
// * d) split the array in 3 chunks, and n/x ll be the index of the beginning of the stack
// should be aware of space limit, that if we're trying to add element to the stack that is n/3 size, we can't do that or we'll override the other stack

class Stack {
  
  protected $array;
  protected $stack_size;
  
  //int
  public function __construct($array_size){
    $this->array = new SplFixedArray($array_size);
    $this->stack_size = floor($this->array->count() / 3);
  }
  
  //int
  protected function getStackStartIndex($stack){
    return $stack * $this->stack_size;
  }
  
  //int
  protected function getStackSize($stack){
    $start = $this->getStackStartIndex($stack);
    $size = 0;
    for($i = $start; $i < $this->stack_size + $start; $i ++){
      if($this->array[$i] == null){
        break;
      }
      $size ++;
    }
    return $size;
  }
  
  //mixed
  //int
  public function push($value, $stack){
    if($this->getStackSize($stack) == $this->stack_size){
      return -1;
    }
    
    $start = $this->getStackStartIndex($stack);
    for($i = $start + $this->stack_size - 1; $i > $start ; $i --){
      $this->array[$i] = $this->array[$i-1];
    }
    
    $this->array[$start] = $value;
  }
  
  //int
  public function pop($stack){
    $start = $this->getStackStartIndex($stack);
    $last = $this->stack_size + $start - 1;
    $first_value = $this->array[$start];
    for($i = $start; $i < $last ; $i ++){
      $this->array[$i] = $this->array[$i+1];
    }
    $this->array[$last] = null;
    return $first_value;
  }
  
}

$stackStruct = new Stack(10);
$stackStruct->push('a', 2);
$stackStruct->push('b', 2);
$stackStruct->push('c', 2);

$stackStruct->push('d', 1);
$stackStruct->push('e', 1);
$stackStruct->push('f', 1);

$stackStruct->push('g', 0);
$stackStruct->push('h', 0);
$stackStruct->push('i', 0);

echo $stackStruct->pop(2)."\r\n";
echo $stackStruct->pop(2)."\r\n";
echo $stackStruct->pop(2)."\r\n";

echo $stackStruct->pop(1)."\r\n";
echo $stackStruct->pop(1)."\r\n";
echo $stackStruct->pop(1)."\r\n";

echo $stackStruct->pop(0)."\r\n";
echo $stackStruct->pop(0)."\r\n";
echo $stackStruct->pop(0)."\r\n";

print_r($stackStruct);

//Brute force:
//Final: push O(n) pop O(n)
//BCR:
