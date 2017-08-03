<?php

# a tree where each node could have as much children as there are 
# possible values in the datatype (a-z for letters, 0-9 for numbers etc)

# very useful for searching in the dictionaries, to find if the sequence is a word or a prefix of a word

# add O(1)
# search "isPrefix" O(1)
# search "contains" O(1)
# Because the number of steps required to find a word is <= y
# y - max possible length of a word the dictionary (ex 16)
#
# Space O(n+M), M is the sum of the length of the strings in the dictionary
# O(n)
# in worst case, if not a single word shares a node, there will be n*y records = O(n)


interface Vocabulary {
    
    public function add($word);
    public function isPrefix($prefix);
    public function contains($word);
    
}

class TrieNode {
    
    protected $is_a_word = false;
    protected $children = [];
    
    public function setIsAWord($is_a_word){
        $this->is_a_word = $is_a_word;
    }
    
    public function add($word){
        $letter = substr($word, 0, 1);
        if(!isset($this->children[$letter])){
            $this->children[$letter] = new TrieNode();
        }
        $child_node = $this->children[$letter];
        if(strlen($word) > 1){
            $remaining_word = substr($word, 1);
            $child_node->add($remaining_word);
        }
        else{
            $child_node->setIsAWord(true);
        }
    }
    
    public function find($word){
        $letter = substr($word, 0, 1);
        if(strlen($word) > 1){
            if(isset($this->children[$letter])){ // continue recursive search down the tree
                $remaining_word = substr($word, 1);
                return $this->children[$letter]->find($remaining_word);
            }
        }
        else{ //this is the last letter of the word
            if(isset($this->children[$letter])){
                return $this->children[$letter]; // we return that node because it contains all necessary data
            }
        }
        return false;
    }
    
    public function isAWord(){
        return $this->is_a_word;
    }
    
    public function numberOfChildren(){
        return count($this->children);
    }
    
}

class Trie implements Vocabulary {
    
    protected $head = null;
    
    public function __construct(){
        $this->head = new TrieNode();
    }
    
    public function add($word){
        $this->head->add($word);
    }
    
    public function isPrefix($prefix){
        $node = $this->head->find($prefix);
        if(!empty($node)){
            if($node->numberOfChildren() > 0){
                return true;
            }
        }
        return false;
    }
    
    public function contains($word){
        $node = $this->head->find($word);
        if(!empty($node)){
            if($node->isAWord()){
                return true;
            }
        }
        return false;
    }
}


$trie = new Trie();
$trie->add('tree');
$trie->add('trie');
$trie->add('algo');
$trie->add('assoc');
$trie->add('ass');
$trie->add('all');
$trie->add('also');

var_dump($trie->contains('also'));
var_dump($trie->contains('ass'));
var_dump($trie->contains('abs'));
var_dump($trie->contains('allocation'));
var_dump($trie->contains('trie'));

echo "---- \r\n";

var_dump($trie->isPrefix('also'));
var_dump($trie->isPrefix('ass'));
var_dump($trie->isPrefix('abs'));
var_dump($trie->isPrefix('allocation'));
var_dump($trie->isPrefix('tr'));
