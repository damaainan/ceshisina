# [PHP有序表查找----斐波那契查找][0]

 标签： [php][1][斐波那契查找][2][数据结构与算法][3]

 2016-11-06 22:44  103人阅读 

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [前言][9]
1. [代码][10]

## 前言：

在前面我们介绍了[二分查找][11]、[插值查找][12]。其中的插值查找是对二分查找的改进。同样，本篇博客的主角—-斐波那契查找，也是对二分查找的改进（利用黄金分割原理）。

由于这个过程分析较之前的复杂，大家可以百度。

## 代码：

```php
<?php

//斐波那契查找 利用黄金分割原理

//算法核心：
//1、当$num==$arr[$mid],查找成功
//2、当$num < $arr[$mid],新范围是第$low个到$mid-1个，此时范围个数为Fbi($k-1)-1个
//2、当$num > $arr[$mid],新范围是第$mid+1个到$high个，此时范围个数为Fbi($k-2)-1个

$i = 0;    //存储对比的次数

//为了实现该算法，我们首先要准备一个斐波那契数列
//@func 产生斐波那契数列
//@param 数列长度
function Fbi($i){
    if($i < 2){
        return ($i == 0 ? 0 : 1);
    }
    return Fbi($i - 1) + Fbi($i - 2);
}

//@param 待查找数组
//@param 待搜索的数字
function fbisearch(array $arr,$num){
    $count = count($arr);
    $lower = 0;
    $high = $count - 1;
    $k = 0;
    global $i;
    //计算$count位于斐波那契数列的位置
    while($count > (Fbi($k) - 1)){
        $k ++;
    }
    //将不满的数值补全，补的数值为数组的最后一位
    for($j = $count;$j < Fbi($k) - 1;$j ++){
        $arr[$j] = $arr[$count - 1];
    }
    //查找开始
    while($lower <= $high){
        $i ++;
        //计算当前分隔的下标
        $mid = $lower + Fbi($k - 1) - 1;
        if($num < $arr[$mid]){
            $high = $mid - 1;
            $k = $k - 1;    //斐波那契数列数列下标减一位 
        }else if($num > $arr[$mid]){
            $lower = $mid + 1;
            $k = $k - 2;    //斐波那契数列数列下标减两位
        }else{
            if($mid <= $count - 1){
                return $mid;
            }else{
                return $count - 1;  //这里$mid大于$count-1说明是补全数值，返回$count-1
            }
        }
    }
    return -1;
}

$arr = array(0,1,16,24,35,47,59,62,73,88,99);
$pos = fbisearch($arr,62);
echo $pos."<br>";
echo $i;
```

[0]: http://www.csdn.net/baidu_30000217/article/details/53057281
[1]: http://www.csdn.net/tag/php
[2]: http://www.csdn.net/tag/%e6%96%90%e6%b3%a2%e9%82%a3%e5%a5%91%e6%9f%a5%e6%89%be
[3]: http://www.csdn.net/tag/%e6%95%b0%e6%8d%ae%e7%bb%93%e6%9e%84%e4%b8%8e%e7%ae%97%e6%b3%95
[8]: #
[9]: #t0
[10]: #t1
[11]: http://blog.csdn.net/baidu_30000217/article/details/53056977
[12]: http://blog.csdn.net/baidu_30000217/article/details/53057202