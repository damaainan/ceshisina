<?php

# Unordered collection of objects without index, sequence or key
# No duplicates (cannot add the same value twice)
# Designed for fast lookup to understand if the specific object is already in the collection (no get etc, just check)
# Usually uses the hash table. Hash is taken from the value
#
# Time Complexity: (same as hash table)
# insert: O(1) AVG O(n) WORST
# find: O(1) AVG O(n) WORST
#
# Methods: contains()

class Set {
    
    private $numberOfBuckets = 1000;
    
    private $data = [];
    
    private function hashKey($key){
        $hash = md5($key);
        $substring = substr($hash, 0,16);
        $finalInt = hexdec($substring);
        return $finalInt % $this->numberOfBuckets;
    }
    
    private function getBucketValues($key, $create=false){
        $hash = $this->hashKey($key);
        if(isset($this->data[$hash])){
            $bucketValues = $this->data[$hash];
        }
        else{
            $bucketValues = new SplDoublyLinkedList();
            if($create){
                $this->data[$hash] = $bucketValues;
            }
        }
        return $bucketValues;
    }
    
    public function add($value){
        $bucketValues = $this->getBucketValues($value, true);
        $valueHolder = $value;
        $bucketValues->push($valueHolder);
    }
    
    public function contains($value){
        $bucketValues = $this->getBucketValues($value);
        foreach($bucketValues as $bucketValue){
            if($bucketValue == $value){
                return true;
            }
        }
        return false;
    }
}
$mySet = new Set();
$mySet->add("John");
$mySet->add("Jon");
$mySet->add("Josh");
$mySet->add("Joe");

$names = ["John", "David", "Joe", "Joel"];
foreach($names as $name){
    if($mySet->contains($name)){
        echo "$name is already in the list \r\n";
    }
    else{
        echo "$name is not in the list \r\n";
    }
}
