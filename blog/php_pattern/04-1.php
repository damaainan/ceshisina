<?php 

header("Content-type:text/html; Charset=utf-8");

/**
 *
 *
 *深拷贝的实现
 *
 * 
 */

//具体原型类
class Map extends Prototype{
    public $width;
    public $height;
    public $sea;
    public function setAttribute(array $attributes){
        foreach($attributes as $key => $val){
            $this->$key = $val;
        }
    }
     //实现克隆方法，用来实现深拷贝
    public function __clone(){
        $this->sea = clone $this->sea;
    }
}