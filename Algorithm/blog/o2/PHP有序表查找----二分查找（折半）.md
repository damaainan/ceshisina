# [PHP有序表查找----二分查找（折半）][0]

 标签： [php][1][二分查找][2][数据结构与算法][3]

 2016-11-06 22:09  314人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [简介][9]
1. [基本思想][10]
1. [代码][11]
1. [总结][12]

## 简介：

二分查找技术，又称为折半查找。它的前提是线性表中的记录必须是关键码有序（通常从小到达有序），线性表必须采用顺序存储。

## 基本思想：

在有序表中，取中间记录作为比较对象，若给定值与中间记录的关键字相等，则查找成功；若给定值小于中间记录的关键字，则在中间记录的左半区继续查找；若给定值大于中间记录的关键字，则在中间记录的右半区继续查找。不断重复上述过程，直到查找成功，或所有查找区域无记录，查找失败为止。

## 代码：
```php
    <?php
    
    //二分搜索(折半查找)算法(前提是数组必须是有序数组) 时间复杂度是 O(logn)
    
    
    $i = 0;    //存储对比的次数
    
    //@param 待查找数组
    //@param 待搜索的数字
    function binsearch($arr,$num){
        $count = count($arr);
        $lower = 0;
        $high = $count - 1;
        global $i;
    
        while($lower <= $high){
    
            $i ++; //计数器
    
            if($arr[$lower] == $num){
                return $lower;
            }
            if($arr[$high] == $num){
                return $high;
            }
    
            $middle = intval(($lower + $high) / 2);
            if($num < $arr[$middle]){
                $high = $middle - 1;
            }else if($num > $arr[$middle]){
                $lower = $middle + 1;
            }else{
                return $middle;
            }
        }
    
        //返回-1表示查找失败
        return -1;
    }
    
    $arr = array(0,1,16,24,35,47,59,62,73,88,99);
    $pos = binsearch($arr,62);
    print($pos);
    echo "<br>";
    echo $i;
```

## 总结：

二叉查找的时间复杂度是 O(logn)。不过由于二叉查找的前提条件是需要有序表顺序存储（数组），如果该有序表需要频繁的执行插入或删除操作，维护有序的排序会带来不小的工作量。

[0]: http://www.csdn.net/baidu_30000217/article/details/53056977
[1]: http://www.csdn.net/tag/php
[2]: http://www.csdn.net/tag/%e4%ba%8c%e5%88%86%e6%9f%a5%e6%89%be
[3]: http://www.csdn.net/tag/%e6%95%b0%e6%8d%ae%e7%bb%93%e6%9e%84%e4%b8%8e%e7%ae%97%e6%b3%95
[8]: #
[9]: #t0
[10]: #t1
[11]: #t2
[12]: #t3