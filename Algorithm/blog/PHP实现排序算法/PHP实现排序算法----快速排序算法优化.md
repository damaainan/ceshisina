# [PHP实现排序算法----快速排序算法优化][0]

 标签： [php][1][排序算法][2][快速排序][3][快速排序算法优化][4]

 2016-11-23 22:56  574人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [优化一优化选取枢轴][10]
1. [优化二优化不必要的交换][11]
1. [优化三优化小数组的排序方案][12]
1. [优化四优化递归操作][13]

本篇博客主要是谈谈对前面 [《PHP实现排序算法—-快速排序（Quick Sort）、快排》][14]的优化问题，如果大家之前没有看过该篇博客，那么必须回去看看，因为这篇博客就是以前一篇博客为基础的。

## 优化一：优化选取枢轴：

在前面的复杂度分析的过程中，我们看到最坏的情况无非就是当我们选中的枢轴是整个序列的边缘值。比如这么一个序列：

    9   1   5   8   3   7   4   6   2

按照习惯我们选择数组的第一个元素作为枢轴，则 $ pivot = 9，在一次循环下来后划分为{1，5，8，3，7，4，6，2} 和{ }（空序列），也就是每一次划分只得到少一个记录的子序列，而另一个子序列为空。最终时间复杂度为 O(n^2)。最优的情况是当我们选中的枢轴是整个序列的中间值。但是我们不能每次都去遍历数组拿到最优值吧？那么就有了一下解决方法：

**1、随机选取：**随机选取 $ low 到 $ high 之间的数值，但是这样的做法有些撞大运的感觉了，万一没撞成功呢，那上面的问题还是没有解决。

**2、三数取中法：**取三个关键字先进行排序，取出中间数作为枢轴。这三个数一般取最左端、最右端和中间三个数，也可以随机取三个数。这样的取法得到的枢轴为中间数的可能性就大大提高了。由于整个序列是无序的，随机选择三个数和从左中右端取出三个数其实就是同一回事。而且随机数生成器本身还会带来时间的开销，因此随机生成不予考虑。

出于这个想法，我们修改 Partition() 函数：
```php
    function Partition(array &$arr,$low,$high){
        $mid = floor($low + ($high - $low) / 2);    //计算数组中间的元素的下标
        if($arr[$low] > $arr[$high]){
            swap($arr,$low,$high);
        }
        if($arr[$mid] > $arr[$high]){
            swap($arr,$mid,$high);
        }
        if($arr[$low] < $arr[$mid]){
            swap($arr,$low,$mid);
        }
    
        //经过上面三步之后，$arr[$low]已经成为整个序列左中右端三个关键字的中间值
        $pivot = $arr[$low];
    
        while($low < $high){   //从数组的两端交替向中间扫描（当 $low 和 $high 碰头时结束循环）
            while($low < $high && $arr[$high] >= $pivot){
                $high --;
            }
            swap($arr,$low,$high);  //终于遇到一个比$pivot小的数，将其放到数组低端
    
            while($low < $high && $arr[$low] <= $pivot){
                $low ++;
            }
            swap($arr,$low,$high);  //终于遇到一个比$pivot大的数，将其放到数组高端
        }
        return $low;   //返回high也行，毕竟最后low和high都是停留在pivot下标处
    }
```

三数取中法对于小数组有很大可能能沟得出比较理想的 $ pivot，但是对于大数组就未必了，因此还有个办法是九数取中法。。。。。。

## 优化二：优化不必要的交换：

现在假如有个待排序的序列如下：

    5   1   9   3   7   4   8   6   2

根据三数取中法我们取 5 7 2 中的 5 作为枢轴。

当你按照快速排序[算法][15]走一个循环，你会发现 5 的下标变换顺序是这样的：0 -> 8 -> 2 -> 5 -> 4，但是它的最终目标就是 4 的位置，当中的交换其实是不需要的。

根据这个思想，我们改进我们的 Partition() 函数:
```php
    function Partition(array &$arr,$low,$high){
        $mid = floor($low + ($high - $low) / 2);    //计算数组中间的元素的下标
        if($arr[$low] > $arr[$high]){
            swap($arr,$low,$high);
        }
        if($arr[$mid] > $arr[$high]){
            swap($arr,$mid,$high);
        }
        if($arr[$low] < $arr[$mid]){
            swap($arr,$low,$mid);
        }
    
        //经过上面三步之后，$arr[$low]已经成为整个序列左中右端三个关键字的中间值
        $pivot = $arr[$low];
    
        $temp = $pivot;
    
        while($low < $high){   //从数组的两端交替向中间扫描（当 $low 和 $high 碰头时结束循环）
            while($low < $high && $arr[$high] >= $pivot){
                $high --;
            }
            //swap($arr,$low,$high);    //终于遇到一个比$pivot小的数，将其放到数组低端
            $arr[$low] = $arr[$high];   //使用替换而不是交换的方式进行操作
    
            while($low < $high && $arr[$low] <= $pivot){
                $low ++;
            }
            //swap($arr,$low,$high);    //终于遇到一个比$pivot大的数，将其放到数组高端
            $arr[$high] = $arr[$low];
        }
    
        $arr[$low] = $temp;    //将枢轴数值替换回 $arr[$low];
    
        return $low;   //返回high也行，毕竟最后low和high都是停留在pivot下标处
    }
```

在上面的改进中，我们使用替换而不是交进行操作，由于在这当中少了多次的数据交换，因此在性能上也是有所提高的。

## 优化三：优化小数组的排序方案：

对于一个数学科学家、博士生导师，他可以攻克世界性的难题，可以培育最优秀的数学博士，当让他去教小学生“1 + 1 = 2”的算术课程，那还真未必比常年在小学里耕耘的数学老师教的好。换句话说，大材小用有时会变得反而不好用。

也就是说，快速排序对于比较大数组来说是一个很好的排序方案，但是假如数组非常小，那么快速排序算法反而不如直接插入排序来得更好（直接插入排序是简单排序中性能最好的）。其原因在于快速排序用到了递归操作，在大量数据排序的时候，这点性能影响相对于它的整体算法优势而言是可以忽略的，但如果数组只有几个记录需要排序时，这就成了大炮打蚊子的大问题。

因此我们需要修改一下我们的 QSort() 函数：
```php
    //规定数组长度阀值
    #define MAX_LENGTH_INSERT_SORT 7
    
    function QSort(array &$arr,$low,$high){
        //当 $low >= $high 时表示不能再进行分组，已经能够得出正确结果了
        if(($high - $low) > MAX_LENGTH_INSERT_SORT){
            $pivot = Partition($arr,$low,$high);  //将$arr[$low...$high]一分为二，算出枢轴值
            QSort($arr,$low,$pivot - 1);    //对低子表（$pivot左边的记录）进行递归排序
            QSort($arr,$pivot + 1,$high);   //对高子表（$pivot右边的记录）进行递归排序
        }else{
            //直接插入排序
            InsertSort($arr);
        }
    }
```


PS：上面的直接插入排序算法大家可以参考：[《PHP实现排序算法—-直接插入排序（Straight Insertion Sort）》][16]

在这里我们增加一个判断，当 $ high - $ low 不大于一个常数时（有资料认为 7 比较合适，也有认为 50 比较合适，实际情况可以是适当调整），就用直接插入排序，这样就能保证最大化的利用这两种排序的优势来完成排序工作。

## 优化四：优化递归操作：

大家知道，递归对性能时有一定影响的，QSort（）函数在其尾部有两次递归的操作，如果待排序的序列划分极端不平衡（就是我们在选择枢轴的时候不是中间值），那么递归的深度将趋近于 n，而不是平衡时的 log₂n，这就不仅仅是速度快慢的问题了。

我们也知道，递归是通过栈来实现的，栈的大小是很有限的，每次递归调用都会耗费一定的栈空间，函数的参数越多，每次递归耗费的空间也越多，因此如果能减少队规，将会大大提高性能。

听说，递归都可以改造成循环实现。我们在这里就是使用循环去优化递归。（关于递归与循环大家可以参考 [《所有递归都可以改写成循环吗？》][17]）

我们对QSort() 函数尾部递归进行优化：
```php
    //规定数组长度阀值
    #define MAX_LENGTH_INSERT_SORT 7
    
    function QSort(array &$arr,$low,$high){
        //当 $low >= $high 时表示不能再进行分组，已经能够得出正确结果了
        if(($high - $low) > MAX_LENGTH_INSERT_SORT){
            while($low < $high){
                $pivot = Partition($arr,$low,$high);  //将$arr[$low...$high]一分为二，算出枢轴值
                QSort($arr,$low,$pivot - 1);    //对低子表（$pivot左边的记录）进行递归排序
                $low = $pivot + 1;
            }
        }else{
            //直接插入排序
            InsertSort($arr);
        }
    }
```

在上面，我们使用循环替换递归，减少了之前一般的递归量。结果是一样的，但是采用循环而不是递归的方法可以缩减堆栈的深度，从而提高了整体性能。

[0]: http://www.csdn.net/baidu_30000217/article/details/53312990
[1]: http://www.csdn.net/tag/php
[2]: http://www.csdn.net/tag/%e6%8e%92%e5%ba%8f%e7%ae%97%e6%b3%95
[3]: http://www.csdn.net/tag/%e5%bf%ab%e9%80%9f%e6%8e%92%e5%ba%8f
[4]: http://www.csdn.net/tag/%e5%bf%ab%e9%80%9f%e6%8e%92%e5%ba%8f%e7%ae%97%e6%b3%95%e4%bc%98%e5%8c%96
[9]: #
[10]: #t0
[11]: #t1
[12]: #t2
[13]: #t3
[14]: http://blog.csdn.net/baidu_30000217/article/details/53311840
[15]: http://lib.csdn.net/base/datastructure
[16]: http://blog.csdn.net/baidu_30000217/article/details/53072746
[17]: https://www.zhihu.com/question/20418254