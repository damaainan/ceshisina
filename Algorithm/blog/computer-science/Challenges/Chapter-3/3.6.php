<?php

# animal shelter

abstract class Animal{
  protected $time;
  
  public function recordTime(){
    $this->time = time();
  }
  public function getTime(){
      return $this->time;
  }
};
class Dog extends Animal{
};
class Cat extends Animal{
};

interface AnimalShelterInterface {
  public function enqueue(Animal $animal);
  public function dequeueAny();
  public function dequeueDog();
  public function dequeueCat();
}

class AnimalShelter implements AnimalShelterInterface {

  // create a doubly linked list, store both pointers to the beginning and the end
  // if requested any, just return the last one and remove it // O(1)
  // if requested dog or cat, iterate backwards until found and remove it SplDoublyLinkedList::key(), SplDoublyLinkedList::offsetUnset($key)
  // O(n)
  
  
  // have 2 queues. if adding a dog, also add NULL to cats queue
  
  //* have 2 queues. store a timestamp
  
  protected $dog_queue;
  protected $cat_queue;
  
  public function __construct(){
    $this->dog_queue = new SplQueue();
    $this->cat_queue = new SplQueue();
  }
  
  public function enqueue(Animal $animal){
    $animal->recordTime();
    if($animal instanceof Dog){
      $this->dog_queue->enqueue($animal);
    }
    else{
      $this->cat_queue->enqueue($animal);
    }
  }
  
  public function dequeueAny(){ //O(1)
    $dog = $this->dog_queue->top();
    $cat = $this->cat_queue->top();
    
    if(!$dog && $cat){
      return $cat;
    }
    elseif(!$cat && $dog){
      return $cat;
    }
    elseif(!$cat && !$dog){
      return null;
    }
    
    if($cat->getTime() > $dog->getTime()){
      return $cat;
    }
    else{
      return $dog;
    }
  }
  
  public function dequeueDog(){ //O(1)
    return $this->dog_queue->dequeue();
  }
  
  public function dequeueCat(){ //O(1)
    return $this->cat_queue->dequeue();
  }
}

$shelter = new AnimalShelter();

$shelter->enqueue( new Dog() );
$shelter->enqueue( new Dog() );
$shelter->enqueue( new Dog() );
$shelter->enqueue( new Cat() );

var_dump($shelter->dequeueAny());
var_dump($shelter->dequeueCat());
var_dump($shelter->dequeueDog());
