# [PHP有序表查找----插值查找][0]

 标签： [php][1][插值查找][2][数据结构与算法][3]

 2016-11-06 22:33  311人阅读 

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [前言][9]
1. [基本思想][10]
1. [代码][11]
1. [总结][12]

## 前言：

在前面我们介绍了[二分查找][13]，但是我们考虑一下，为什么一定要折半呢？而不是折四分之一或者更多？

打个比方，在英文词典里查找“apple”，你下意识里翻开词典是翻前面的书页还是后面的书页呢？如果再查“zoo”,你又会怎么查？显然你不会从词典中间开始查起，而是有一定目的地往前或往后翻。

同样，比如要在取值范围在 0 ~ 10000 之间的100个元素从小到大均匀分布的数组中查找5，我们自然而然地先考虑数组下标较小的开始查找。

以上的分析其实就是插值查找的思想，它是二分查找的改进。

## 基本思想：

根据要查找的关键字key与查找表中的最大最小记录的关键字比较后的查找方法，其核心就在于插值计算公式，我们先看折半查找的计算公式：   
![这里写图片描述][14]

而插值查找就是要将其中的 1/2进行改进，改成下面的计算方案：   
![这里写图片描述][15]

插值查找[算法][16]的核心就在于插值的计算公式:

$num - $arr[$lower]   
—————————————   
$arr[$high] - $arr[$lower] 

## 代码：
```php
    <?php
    
    //插值查找(前提是数组必须是有序数组) 事件复杂度　O(logn)
    //但对于数组长度比较大，关键字分布又是比较均匀的来说，插值查找的效率比折半查找的效率高
    
    $i = 0;    //存储对比的次数
    
    //@param 待查找数组
    //@param 待搜索的数字
    function insertsearch($arr,$num){
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
    
            // 折半查找 ： $middle = intval(($lower + $high) / 2);
            $middle = intval($lower + ($num - $arr[$lower]) / ($arr[$high] - $arr[$lower]) * ($high - $lower)); 
            if($num < $arr[$middle]){
                $high = $middle - 1;
            }else if($num > $arr[$middle]){
                $lower = $middle + 1;
            }else{
                return $middle;
            }
        }
    
        return -1;
    }
    
    $arr = array(0,1,16,24,35,47,59,62,73,88,99);
    $pos = insertsearch($arr,62);
    print($pos);
    echo "<br>";
    echo $i;
```
## 总结：

从时间复杂度上来看，它也是 O(logn)，但对于有序表比较长，而关键字分布有比较均匀的查找表来说，插值查找算法的平均性能比二分查找好的多。反之，数组中如果分布类似于{0，1，2，2000，2001，。。。999998，999999}这种极端不均匀的数据，用插值查找未必是很合适的选择。

我自己特别做了个例子：
```php
    $arr = array(0,1,2,2000,2001,2002,2003,2004,5555,69666,99999,100000);
    echo "位置：".binsearch($arr,5555);
    echo "<br>";
    echo "比较次数：".$i;
    $i = 0;    //重置比较次数
    echo "<br>";
    echo "位置：".insertsearch($arr,5555);
    echo "<br>";
    echo "比较次数：".$i;
```

结果输出：

    位置：8
    比较次数：2
    位置：8
    比较次数：9

可以得到，对于极端不均匀的数据，插值查找效率比折半查找低。   
PS：上面提到的binsearch()函数大家可以参考我的上一篇博客：[PHP有序表查找—-二分查找（折半）][13]

[0]: http://www.csdn.net/baidu_30000217/article/details/53057202
[1]: http://www.csdn.net/tag/php
[2]: http://www.csdn.net/tag/%e6%8f%92%e5%80%bc%e6%9f%a5%e6%89%be
[3]: http://www.csdn.net/tag/%e6%95%b0%e6%8d%ae%e7%bb%93%e6%9e%84%e4%b8%8e%e7%ae%97%e6%b3%95
[8]: #
[9]: #t0
[10]: #t1
[11]: #t2
[12]: #t3
[13]: http://blog.csdn.net/baidu_30000217/article/details/53056977
[14]: ./20170306192806899.png
[15]: ./20170306193241359.png
[16]: http://lib.csdn.net/base/datastructure