# [PHP实现排序算法----快速排序（Quick Sort）、快排][0]

 标签： [php][1][快速排序][2][排序算法][3]

 2016-11-23 21:56  1572人阅读  [评论][4](0)  [收藏][5]  [举报][6]

![][7]

 分类：

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录[(?)][8] [[+]][8]

1. [基本思想][9]
1. [基本算法步骤][10]
1. [算法实现][11]
1. [复杂度分析][12]

## 基本思想：

快速排序（Quicksort）是对冒泡排序的一种改进。他的基本思想是：通过一趟排序将待排记录分割成独立的两部分，其中一部分的关键字均比另一部分记录的关键字小，则可分别对这两部分记录继续进行快速排序，整个排序过程可以递归进行，以达到整个序列有序的目的。

## 基本算法步骤：

举个栗子：   
![这里写图片描述][13]

假如现在待排序记录是：

    6   2   7   3   8   9

第一步、创建变量 $ low 指向记录中的第一个记录， $ high 指向最后一个记录，$pivot 作为枢轴赋值为待排序记录的第一个元素（不一定是第一个），这里：

    $low = 0;
    $high = 5;
    $pivot = 6;


第二步、我们要把所有比 $ pivot 小的数移动到 $ pivot 的左面，所以我们可以开始寻找比6小的数，从 $ high 开始，从右往左找，不断递减变量 $ high 的值，我们找到第一个下标 3 的数据比 6 小，于是把数据 3 移到下标 0 的位置（ $ low 指向的位置），把下标 0 的数据 6 移到下标 3，完成第一次比较：

    3   2   7   6   8   9
    
    //这时候,$high 减小为 3
    $low = 0;
    $high = 3;
    $pivot = 6;


第三步、我们开始第二次比较，这次要变成找比 $ pivot 大的了，而且要从前往后找了。递加变量 $ low，发现下标 2 的数据是第一个比 $ pivot 大的，于是用下标 2 （ $ low 指向的位置）的数据 7 和 指向的下标 3 （ $ high 指向的位置）的数据的 6 做交换，数据状态变成下表：

    3   2   6   7   8   9
    
    //这时候,$high 减小为 3
    $low = 2;
    $high = 3;
    $pivot = 6;


完成第二步和第三步我们称为完成一个循环。

第四步（也就是开启下一个循环）、模仿第二步的过程执行。   
第五步、模仿第三步的过程执行。

执行完第二个循环之后，数据状态如下：

    3   2   6   7   8   9
    
    //这时候,$high 减小为 3
    $low = 2;
    $high = 2;
    $pivot = 6;


到了这一步，我们发现 $ low 和 $ high“碰头”了：他们都指向了下标 2。于是，第一遍比较结束。得到结果如下，凡是 $ pivot(=6) 左边的数都比它小，凡是 $ pivot 右边的数都比它大。

然后，对 、$pivot 两边的数据 {3，2} 和 {7，8，9}，再分组分别进行上述的过程，直到不能再分组为止。

**注意**：第一遍快速排序不会直接得到最终结果，只会把比k大和比k小的数分到k的两边。为了得到最后结果，需要再次对下标2两边的数组分别执行此步骤，然后再分解数组，直到数组不能再分解为止（只有一个数据），才能得到正确结果。

## 算法实现：
```php
    //交换函数
    function swap(array &$arr,$a,$b){
        $temp = $arr[$a];
        $arr[$a] = $arr[$b];
        $arr[$b] = $temp;
    }
    
    //主函数：
    function QuickSort(array &$arr){
        $low = 0;
        $high = count($arr) - 1;
        QSort($arr,$low,$high);
    }
```

主函数中，由于第一遍快速排序是对整个数组排序的，因此开始是 $ low=0, $ high=count( $ arr)-1。   
然后 QSort() 函数是个递归调用过程，因此对它封装了一下：
```php
    function QSort(array &$arr,$low,$high){
        //当 $low >= $high 时表示不能再进行分组，已经能够得出正确结果了
        if($low < $high){
            $pivot = Partition($arr,$low,$high);  //将$arr[$low...$high]一分为二，算出枢轴值
            QSort($arr,$low,$pivot - 1);    //对低子表（$pivot左边的记录）进行递归排序
            QSort($arr,$pivot + 1,$high);   //对高子表（$pivot右边的记录）进行递归排序
        }
    }
```
从上面的 QSort（）函数中我们看出，Partition（）函数才是整段代码的核心，因为该函数的功能是：选取当中的一个关键字，比如选择第一个关键字。然后想尽办法将它放到某个位置，使得它左边的值都比它小，右边的值都比它大，我们将这样的关键字成为枢轴（pivot）。

直接上代码：
```php
    //选取数组当中的一个关键字，使得它处于数组某个位置时，左边的值比它小，右边的值比它大，该关键字叫做枢轴
    //使枢轴记录到位，并返回其所在位置
    function Partition(array &$arr,$low,$high){
        $pivot = $arr[$low];   //选取子数组第一个元素作为枢轴
        while($low < $high){  //从数组的两端交替向中间扫描（当 $low 和 $high 碰头时结束循环）
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

组合起来的整个代码如下：
```php
    function swap(array &$arr,$a,$b){
        $temp = $arr[$a];
        $arr[$a] = $arr[$b];
        $arr[$b] = $temp;
    }
    
    function Partition(array &$arr,$low,$high){
        $pivot = $arr[$low];   //选取子数组第一个元素作为枢轴
        while($low < $high){  //从数组的两端交替向中间扫描
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
    
    function QSort(array &$arr,$low,$high){
        if($low < $high){
            $pivot = Partition($arr,$low,$high);  //将$arr[$low...$high]一分为二，算出枢轴值
            QSort($arr,$low,$pivot - 1);   //对低子表进行递归排序
            QSort($arr,$pivot + 1,$high);  //对高子表进行递归排序
        }
    }
    
    function QuickSort(array &$arr){
        $low = 0;
        $high = count($arr) - 1;
        QSort($arr,$low,$high);
    }
```

我们调用[算法][14]：

    $arr = array(9,1,5,8,3,7,4,6,2);
    QuickSort($arr);
    var_dump($arr);


## 复杂度分析：

在最优的情况下，也就是选择数轴处于整个数组的中间值的话，则每一次就会不断将数组平分为两半。因此最优情况下的时间复杂度是 O(nlogn) （跟堆排序、归并排序一样）。

最坏的情况下，待排序的序列是正序或逆序的，那么在选择枢轴的时候只能选到边缘数据，每次划分得到的比上一次划分少一个记录，另一个划分为空，这样的情况的最终时间复杂度为 O(n^2).

综合最优与最差情况，平均的时间复杂度是 O(nlogn).

快速排序是一种不稳定排序方法。

由于快速排序是个比较高级的排序，而且被列为20世纪十大算法之一。

[0]: http://www.csdn.net/baidu_30000217/article/details/53311840
[1]: http://www.csdn.net/tag/php
[2]: http://www.csdn.net/tag/%e5%bf%ab%e9%80%9f%e6%8e%92%e5%ba%8f
[3]: http://www.csdn.net/tag/%e6%8e%92%e5%ba%8f%e7%ae%97%e6%b3%95
[8]: #
[9]: #t0
[10]: #t1
[11]: #t2
[12]: #t3
[13]: ../img/20161123210626173.png
[14]: http://lib.csdn.net/base/datastructure