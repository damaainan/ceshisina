<?php 

/*
问题：给你1000万个不重复的整数，如何排序呢？ 对于一般的内排序算法，需要将数据读入内存，而对于海量数据排序，这显然是不合适的。所以，有必要用某种方式实现数据的压缩，或者转换。  
    这里先看下bitmap的结构： 
   a[0]=0     00000000000000000000000000000000
   a[1]=0       00000000000000000000000000000000
    。。。。。。。。。。。。
   对于程序里的实现，则是一个一维的整形数组，由于每个整数是32位，所以，如果我们能够用一种方法将整数映射到整数的某个位上，
  那么，一个整数就可以保存32个数字了，这样一来，内存空间就压缩了32倍。 办法是有的，就是位运算。
   */


  // 代码：
// bitmap 用于大数据量的无重复数据排序和查找重复 

// 将int型变量a的第k位清0，即 a=a&~(1<<k) 
// 将int型变量a的第k位置1， 即 a=a|(1<<k) 
//取int型变量a的第k位 a>>k&1 
/*a % 2 等价于 a & 1  ( a  & log2(2))       
       a % 4 等价于 a & 2  ( a  & log2(4))   

      .....        
       a % 32 等价于 a & 5 
*/        
define('SHIFT',5); 
define('MASK',0x1F); 
$ary = array(1,2,3,31, 33,56,199,30,50); 
$bitmap = array(); //位图 
function bitset($v) { 
    global $bitmap; 
    $bitmap[$v >> SHIFT] |= 1 << ($v & MASK); 
} 

// 将数组(bitmap)某位置0
function cls($v) { 
    global $bitmap; 
    $bitmap[$v >> SHIFT] &= ~(1 << ($v & MASK)); 
} 

// 检查数组(bitmap)某位是否是1
function test($v) { 
    global $bitmap; 
    return $bitmap[$v >> SHIFT] & (1 << ($v & MASK)); 
} 

foreach($ary as $v) { // 将待排序列各元素依次映射到bitmap中
    bitset($v); 
} 


// 还原---- 
foreach($bitmap as $k =>$v) { 
    //echo str_pad(decbin($v),32,'0'); 
    //if(test($v)) { 
    $bitstr = strval(decbin($v)); 
    $bitstr = strrev($bitstr); 
    echo $k.' == >'.$bitstr.'<br>'; 
    for($bit = 0;$bit < strlen($bitstr);$bit++) { 
        if($bitstr[$bit] == 1) { 
            echo $k*32 + $bit.'<br>'; 
        } 
    } 
     
         
    //} 
} 
print_r($bitmap); 