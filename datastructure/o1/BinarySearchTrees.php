<?php

# Binary search tree is a binary tree (each node has max of 2 children)
# children are named Left and Right, where the key of the Left child is always smaller 
# than the key of it's parent, and the key of the Right child is bigger
# When performing search, we traverse through nodes and compare the keys
# Structure of a tree depends completely on the order of filling.
# Unbalanced tree - when one branch has much more childs than others, 
# and search of values located in that branch will take significantly more time.
#
# Time complexity:
# insert: O(n log n) AVG - O(n) WORST
# find: O(n log n) AVG - O(n) WORST
#
# Animation:
# https://www.cs.usfca.edu/~galles/visualization/BST.html

class TreeNode{
    
    private $left = null;
    private $right = null;
    public $key = null;
    public $value = null;

    public function __construct($key, $value){
        $this->key = $key;
        $this->value = $value;
    }
    
    public function getValue(){
        return $this->value;
    }
    
    public function getKey(){
        return $this->key;
    }
    
    public function append(TreeNode $item){
        if($item->key > $this->key){
            $this->setRight($item);
        }
        elseif($item->key < $this->key){
            $this->setLeft($item);
        }
        else{
            throw new \Exception("Cannot add an item with the same key twice");
        }
    }
    
    private function setLeft(TreeNode $item){
        if($this->left === null){
            $this->left = $item;
        }else{
            $this->left->append($item);
        }
    }
    
    private function setRight(TreeNode $item){
        if($this->right === null){
            $this->right = $item;
        }else{
            $this->right->append($item);
        }
    }
    
    public function find($key){
        if($key === $this->key){
            return $this->value;
        }
        elseif($key > $this->key){
        	if($this->right !== null){
	            return $this->right->find($key);
        	}
        	else{
        		return false;
        	}
        }
        elseif($key < $this->key){
        	if($this->left !== null){
            	return $this->left->find($key);
        	}
        	else{
        		return false;
        	}
        }
    }
}


class BinarySearchTree {

    private $_head = null;
    
    public function add($key, $value){
        $item = new TreeNode($key, $value);
    
        if($this->_head === null){
            $this->_head = $item;
        }
        else{
            $this->_head->append($item);
        }
    }
    
    public function get($key){
        if($this->_head === null){
            return false;
        }
        else{
            return $this->_head->find($key);
        }
    }
    
}

$myBT = new BinarySearchTree();

$myBT->add(50, "first");
$myBT->add(25, "second");
$myBT->add(100, "third");
$myBT->add(10, "fourth");
$myBT->add(30, "fifth");
$myBT->add(1000, "sixth");
$myBT->add(75, "seventh");
$myBT->add(2000, "eighth");
$myBT->add(3000, "nineth");

var_dump( $myBT->get(3000) );
var_dump( $myBT->get(10) );
var_dump( $myBT->get(1230) );
