<?php 
class Node{
    public $element;
    public $next = null;
    public $previous = null;//当属性私有的时候不能被访问？
    public function __construct($element)
    {
        $this->element = $element;
    }
}