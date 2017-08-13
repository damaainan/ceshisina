<?php
class Node{
    public $data ;
    public $left ;
    public $right;
	public function __construct($data,$left,$right){
        $this->data = $data;
        $this->left = $left;
        $this->right = $right;
    }


	function show() {
	    return $this->data;
	} 
}