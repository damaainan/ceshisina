## 剑指offer试题（PHP篇三）

来源：[http://www.cnblogs.com/zlnevsto/p/8452412.html](http://www.cnblogs.com/zlnevsto/p/8452412.html)

2018-02-18 01:20

**`11.二进制中1的个数`** 
## 题目描述


输入一个整数，输出该数二进制表示中1的个数。其中负数用补码表示。

时间限制：1秒   空间限制：32768K



```php
<?php

function NumberOf1($n)
{
    // write code here
    $count = 0;
    if($n<0){
        $n=$n & 0X7FFFFFFF;
        $count++;
    }
    while($n){
        $n=($n-1)&$n;
        $count++;
    }
    return $count;
}
```






运行时间：11ms   占用内存：2316k

感悟：

　　说实话，这类题是我的弱项，想了大半天，无从下手，后来还是在上网搜了些资料后，才勉强解出。`首先判断n是不是负数，当n为负数的时候，直接用后面的``while``循环会导致死循环，因为负数`


`向左移位的话最高位补``1``！ 因此需要一点点特殊操作，可以将最高位的符号位``1``变成``0``，也就``是n & ``0x7FFFFFFF``，这样就把负数转化成正数了，唯一差别就是最高位由``1``变成``0``，因为少了``一个``1``，所以count加``1``。之后再按照``while``循环里处理正数的方法来操作就可以完美解决。`

 

 **`12.数值的整数次方`** 

## 题目描述


给定一个double类型的浮点数base和int类型的整数exponent。求base的exponent次方。

时间限制：1秒   空间限制：32768K



```php
<?php

function Power($base, $exponent)
{
    // write code here
    return pow($base,$exponent);
}
```






运行时间：15ms   占用内存：2432k

感悟：

　　这题用php做和作弊一样。。。呃，当然，身为一个正直的人，还是要把用算法思路写的解贴出来。


```php
<?php
 
function Power($base, $exponent)
{
         
    if($exponent >= 0){
        $res = 1;
    while($exponent >= 1){
        $res = $res * $base;
        $exponent--;
    }
          return $res;
    }
    if($exponent < 0){
        $exponent2 = abs($exponent);
        $res = 1;
        while($exponent2 >=1){
            $res = $res *$base;
            $exponent2--;
        }
        return 1/$res;
         
    }
   
}


```



其实也不是很难啦= =。

 

**`13.调整数组顺序使奇数位于偶数前面`** 
## 题目描述


输入一个整数数组，实现一个函数来调整该数组中数字的顺序，使得所有的奇数位于数组的前半部分，所有的偶数位于位于数组的后半部分，并保证奇数和奇数，偶数和偶数之间的相对位置不变。

时间限制：1秒   空间限制：32768K



```php
<?php

function reOrderArray($array)
{
    // write code here
    $tmp1 = [];
    $tmp2 = [];
    for($i=0;$i<count($array);$i++){
        if($array[$i]%2){
            $tmp1[] = $array[$i];
        }else{
            $tmp2[] = $array[$i];
        }
    }
    for($j=0;$j<count($tmp2);$j++){
        $tmp1[]=$tmp2[$j];
    }
    return $tmp1;
}
```






运行时间：10ms   占用内存：2432k

感悟：

　　这道题不算难，算法思路：创建两个临时数组，通过对原数组的每个值进行判断，奇偶分别存入两个临时数组中，再将偶数组放入奇数组之后即可。

 

**`14.链表中倒数第k个结点`** 
## 题目描述


输入一个链表，输出该链表中倒数第k个结点。

时间限制：1秒   空间限制：32768K



```php
<?php
/*class ListNode{
    var $val;
    var $next = NULL;
    function __construct($x){
        $this->val = $x;
    }
}*/
function FindKthToTail($head, $k)
{
    // write code here
    $tmp=$head;
    $len=0;
    while($head!=null){
        $len++;
        $head=$head->next;
    }
    if($k>$len){
        return null;
    }
    for($i=0;$i<$len-$k;$i++){
        $tmp=$tmp->next;
    }
    return $tmp;
}
```






运行时间：25ms   占用内存：2316k

感悟：

　　这道题的思路非常清晰，即首先求出链表长度，判断k结点是否超出链表长度，若符合要求，则通过循环找到第k个结点并返回。

 

**`15.反转链表`** 
## 题目描述


输入一个链表，反转链表后，输出链表的所有元素。

时间限制：1秒   空间限制：32768K



```php
<?php
/*class ListNode{
    var $val;
    var $next = NULL;
    function __construct($x){
        $this->val = $x;
    }
}*/
function ReverseList($pHead)
{
    // write code here
    if($pHead==null){
        return null;
    }
    $pre=null;
    while($pHead != null){
        $tmp = $pHead->next;
        $pHead->next = $pre;
        $pre = $pHead;
        $pHead = $tmp;
    }
    return $pre;
}
```






运行时间：9ms   占用内存：3724k

感悟：

　　这道链表题自我感觉还是有点点绕的（个人大脑不是很发达。。。），思路，简单来说就是不断的取出原链表的头，然后存入pre的尾部，这样做的结果就是将整个链表反转。

 

 注：以上均为个人理解，如有错误，请提出，必改正。






















