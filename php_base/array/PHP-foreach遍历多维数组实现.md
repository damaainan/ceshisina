# PHP-foreach遍历多维数组实现

 作者  [简单方式][0] 关注 2016.08.01 14:57  字数 890  

## 介绍

正常我们的foreach可以按顺序把一维数组里面每个 key => value 打印出来，但是如果是多维数组则需要循环在嵌套循环，或则递归实现，但是这些方式都不够灵活，因为在不确定该数组是几维的情况下，不可能永无止境的嵌套循环，如果采用递归到可以解决，但是如果只想使用foreach全部循环出来该如何实现？

#### 实现方式 一

> 采用PHP本身自带的迭代器类 RecursiveIteratorIterator

```php
  <?php
     $test_arr  = array(1,2,3,array(4,'aa'=>5,6,array(7,'bb'=>8),9,10),11,12); 
     $arrayiter = new RecursiveArrayIterator($test_arr);
     $iteriter  = new RecursiveIteratorIterator($arrayiter);
     //直接打印即可按照横向顺序打印出来
     foreach ($iteriter as $key => $val){ 
          echo $key.'=>'.$val; 
     } 
     //结果
     /*  
         0=>1
         1=>2 
         2=>3
         0=>4
         aa=>5
         2=>6
         0=>7
         bb=>8
         4=>9
         5=>10
         4=>11
         5=>12
   */
```

#### 实现方式 二

> 自己实现一个类似于 RecursiveIteratorIterator 的迭代器类，实现多维数组横向打印功能

```php
  <?php
    class foreachPrintfArr implements Iterator {
           //当前数组作用域
           private $_items; 
           private $_old_items;
           //保存每次执行数组环境栈
           private $_stack = array(); 
    
           public function __construct($data=array()){
               $this->_items = $data;
           }
    
           private function _isset(){
               $val = current($this->_items);
    
               if (empty($this->_stack) && !$val) {
                    return false;
               } else {
                    return true;
               }   
           }
    
           public function current() {
                $this->_old_items = null;
                $val = current($this->_items);
    
                //如果是数组则保存当前执行环境，然后切换到新的数组执行环境
                if (is_array($val)){
                    array_push($this->_stack,$this->_items);
                    $this->_items = $val;
                    return $this->current();
                }
    
                //判断当前执行完成后是否需要切回上次执行环境
                //(1) 如果存在跳出继续执行
                //(2) 如果不存在且环境栈为空，则表示当前执行到最后一个元素
                //(3) 如果当前数组环境下一个元素不存在,则保存一下当前执行数组环境 $this->_old_items = $this->_items;
                //然后切换上次执行环境 $this->_items = array_pop($this->_stack) 继续循环, 直到当前数组环境下一个
                //元素不为空为止
                while (1) {
                    if (next($this->_items)) {    
                        prev($this->_items); break;
                    } elseif (empty($this->_stack)) {
                        end($this->_items); break;
                    } else {
                        end($this->_items);
                        if (!$this->_old_items) 
                            $this->_old_items = $this->_items;
                        $this->_items = array_pop($this->_stack);
                    }
                }
    
                return $val;
           }
    
           public function next() {
                next($this->_items);   
           }
    
           public function key() {
                // 由于 key() 函数执行在 current() 函数之后
                // 所以在 current() 函数切换执行环境 , 会导致切换之前的执行环境最后一个 key
                // 变成切换之后的key , 所以 $this->_old_items 保存一下切换之前的执行环境
                // 防止key打印出错
                return $this->_old_items ? key($this->_old_items) : key($this->_items);
           }
    
           public function rewind() {
                reset($this->_items);
           }
    
           public function valid() {                                                                              
                return $this->_isset();
           }
       }
```

#### 内部执行方式

1. foreach 循环我们自定义的foreachPrintfArr类，会自动调用内部这5个方法 valid()、rewind()、key()、next()、current() 我们只需要实现这几个方法即可.
1. 调用顺序：  
第1次 => rewind -> valid -> current -> key  
第2次~n次 => next -> valid -> current -> key

```php
<?php
 $test_arr = array(1,2,3,array(4,'aa'=>5,6,array(7,'bb'=>8),9,10),11,12);
 $iteriter = new foreachPrintfArr($test_arr);
 foreach ($iteriter as $key => $val){
     echo $key.'=>'.$val;
 } 
 //结果：
 /* 
  0=>1
  1=>2
  2=>3
  0=>4
  aa=>5
  2=>6
  0=>7
  bb=>8
  4=>9
  5=>10
  4=>11
  5=>12
  */
```

[0]: /u/9642a0c8db39