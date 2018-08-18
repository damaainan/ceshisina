<?php

# Hash table is a structure that stores kay-value pairs
# It's implemented as a simple array with integer indexes, where indexes could be calculated with a hash of a key
# Multiple keys could have the same hash, and we store some other structure in each cell (bucket) like a linked list
# It's fast to find a value because we jump to the cell and then loop throug the snall list
# Associative arrays or dictionaries are hash tables, sometimes extended with some additional traits
# 
# Time complexity:
# insert: O(1) AVG - O(n) WORST
# find: O(1) AVG - O(n) WORST
#
# Animation:
# https://www.cs.usfca.edu/~galles/visualization/OpenHash.html

class HashTable {
    
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
    
    public function add($key, $value){
        $bucketValues = $this->getBucketValues($key, true);
        $valueHolder = new StdClass();
        $valueHolder->key = $key;
        $valueHolder->value = $value;
        $bucketValues->push($valueHolder);
    }
    
    public function get($key){
        $bucketValues = $this->getBucketValues($key);
        foreach($bucketValues as $bucketValue){
            if($bucketValue->key == $key){
                return $bucketValue->value;
            }
        }
        return null;
    }
}

$myHT = new HashTable();
$myHT->add("name", "John");
$myHT->add("age", 30);

echo "{$myHT->get("name")} is {$myHT->get('age')} years old";

