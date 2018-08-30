<?php
/* Created by User: soma Worker:陈鸿扬  Date: 17/2/14  Time: 13:35 */
//namespace Note;
class hashTable {
    private $collection;
    private $size = 100;
    //初始化哈希表的大小
    function __construct($size='')
    {
        $bucketsSize = is_int($size)?$size:$this->size;
        $this->collection = new SplFixedArray($bucketsSize);
    }
    //生成散列值，作为存储数据的位置
    private function _hashAlgorithm($key)
    {
        $length = strlen($key);
        $hashValue = 0;
        for($i=0; $i<$length; $i++) {
            $hashValue += ord($key[$i]);
        }
        return ($hashValue%($this->size));
    }
    //在相应的位置存储对应的值
    function set($key, $val)
    {
        $index = $this->_hashAlgorithm($key);
        $this->collection[$index] = $val;
    }
    //根据键生成散列值，进而找到对应的值
    function get($key)
    {
        $index = $this->_hashAlgorithm($key);
        return $this->collection[$index];
    }
    //删除某个值,成功返回1，失败返回0
    function del($key)
    {
        $index = $this->_hashAlgorithm($key);
        if(isset($this->collection[$index])) {
            unset($this->collection[$index]);
            return 1;
        } else {
            return 0;
        }
    }
    //判断某个值是否存在，存在返回1， 不存在返回0
    function exist($key)
    {
        $index = $this->_hashAlgorithm($key);
        if($this->collection[$index]){
            return 1;
        } else {
            return 0;
        }
    }
    //返回key的个数
    function size()
    {
        $size = 0;
        $length = count($this->collection);
        for($i=0; $i<$length; $i++) {
            if($this->collection[$i]) {
                $size++;
            }
        }
        return $size;
    }
    //返回value的序列
    function val()
    {
        $size = 0;
        $length = count($this->collection);
        for($i=0; $i<$length; $i++) {
            if($this->collection[$i]) {
                echo $this->collection[$i]."<br />";
            }
        }
    }
    //排序输出
    function sort($type=1)
    {
        $length = count($this->collection);
        $temp = array();
        for($i=0; $i<$length; $i++) {
            if($this->collection[$i]) {
                $temp[] = $this->collection[$i];
            }
        }
        switch ($type) {
            case 1:
                //正常比较
                sort($temp, SORT_REGULAR);
                break;
            case 2:
                //按照数字比较
                sort($temp, SORT_NUMERIC);
                break;
            //按照字符串进行比较
            case 3:
                sort($temp, SORT_STRING);
                break;
            //根据本地字符编码环境进行比较
            case 4:
                sort($temp, SORT_LOCALE_STRING);
                break;
        }
        echo "<pre>";
        print_r($temp);
    }
    //逆序输出
    function rev($type=1)
    {
        $length = count($this->collection);
        $temp = array();
        for($i=0; $i<$length; $i++) {
            if($this->collection[$i]) {
                $temp[] = $this->collection[$i];
            }
        }
        switch ($type) {
            case 1:
                //正常比较
                rsort($temp, SORT_REGULAR);
                break;
            case 2:
                //按照数字比较
                rsort($temp, SORT_NUMERIC);
                break;
            //按照字符串进行比较
            case 3:
                rsort($temp, SORT_STRING);
                break;
            //根据本地字符编码环境进行比较
            case 4:
                rsort($temp, SORT_LOCALE_STRING);
                break;
        }
        echo "<pre>";
        print_r($temp);
    }
} 


/**
 * PHP模拟哈希表
 */
require_once('Class/hashTable.class.php'); //use Note\hashTable as hashTable;
$list = new hashTable(200);
$list->set("zero", "zero compare");
$list->set("one", "first test");
$list->set("two", "second test");
$list->set("three", "three test");
$list->set("four", "fouth test");
$list->val();
echo "after sorted : <br />";
$list->rev(3);
