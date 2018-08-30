# PHP程序员需要掌握的查找算法

  [Wenzhi Xu][0]   [04/03/201604/10/2017][1]  [DSA][2], [PHP][3]  [No Comments][4]


查找算法同排序算法是最基本的算法，也就是同样也需要我们PHP程序员需要去掌握的，本篇文章将会对常用的查找算法进行简要的介绍以及以循序渐进的方式对各种查找算法进行排版，以达到由浅入深、深入浅出的目的。

  
**提前需要知道的概念**  
查找目标(target):需要查找的值  
待查找序列(list):查找载体

**线性查找(Linear Search)**  
前提:无  
时间复杂度:O(n)  
简介:线性查找是最简单也是最容易理解的查找算法了，基本的工作原理:  
![][5]

对于具体的代码，这里就不再赘述了。

**二分查找(Binary Search)**  
前提:待查找序列为已排序序列  
时间复杂度:O(logn)  
简介:二分查找是一种非常高效的查找方法，但前提是待查找序列一定要是已经排好序的序列。虽然二分查找法的原理比较简单也很容易让人理解，但它比较重要之处在于百度等公司的面试中，二分查找算法基本属于必考题目，所以懂得该查找算法是所有程序员都应该懂得的。基本的工作原理:  
比如如下序列，目标是在如下从小打到的序列中查找31这个值的位置  
![][6]

首先对该序列进行折半，通过`mid = (low+high)/2`公式即4 = (0+9)/2，得到该序列的中间位置，同时也得到中间位置的值为27。  
![][7]

此时拿27与我们需要找的31进行比较，发现27<31，则31一定在27的右侧。此时，此序列的左边部分就不用关心了。  
![][8]

此时，待查找序列就只剩下一半了，也就只剩下右侧的一半，此时将`mid`更新为`low=mid+1(low = 4+1)`，再次计算mid = (5 + 9)/2，也就是mid = 7。此时的定位的值为 35，发现31<35，则31一定在35的左侧，此时序列中35右侧的部分就不用在关心了  
![][9]

…  
以此类推，如此往复下去，最终我们会得到31的位置。

```php
    function binarySearch($arr, $target){
        if(!$arr || empty($arr)) return -1;
        $low = 0;
        $high = count($arr)-1;
        while($low < $high){
            $middle = intval(($low+$high)/2);
            if($target < $arr[$middle]){
                $high = $middle-1;
            }else if($target > $arr[$middle]){
                $low = $middle+1;
            }else{
                return $middle;
            }
        }
        return -1;
    }
```

额外提供一个递归实现的二分查找法

```php
    function recursionBinarySearch($arr, $target, $low, $high) {
        if ($low > $high) { return -1; }
        $middle = intval(($low + $high) / 2);
        $crt_value = $arr[$middle];
        if ($crt_value > $target) {
            return recursionBinarySearch($arr, $target, $low, $middle-1);
        }else if ($crt_value < $target) {
            return recursionBinarySearch($arr, $target, $middle+1, $high);
        }else{
            return $middle;
        }
    }
```

- - -

**插补法查找(Interpolation Search)**  
前提:待查找序列为已排序序列  
时间复杂度:Ο(log (log n))  
简介:插补法查找是升级版的二分查找法，所以原理类似，唯一的区别是对`mid`计算公式做了更改，那么区别究竟在哪里，由于在二分查找中，`mid`的计算是假定`low`和`high`的中间，而插补法查找是通过对当前的`low`、`high`、`arr[low]`、`arr[high]`的进行计算得到一个高效的`mid`，从而缩短查找时间。更多信息请参考[[演算法] 插補搜尋法(Interpolation Search)][10]

```php
    function interpolationSearch($arr, $target)
    {
        $low = 0;
        $high = count($arr)-1;
        while($low <= $high){
            $gap = $arr[$high] - $arr[$low];
            if($gap){
                $mid = intval(($high - $low)*($target - $arr[$low]) / $gap) + $low;
            }else{
                $mid = $low;
            }
            if( $mid < $low || $mid > $high)
                break;
            if($target < $arr[$mid]){
                $high = $mid - 1;
            }else if($target > $arr[$mid]){
                $low = $mid + 1;
            }else{
                return $mid;
            }
        }
        return -1;
    }
```

> 插补法查找更加适合密集型的数据查找，即序列的数据间隔紧密型。

- - -

**参考网址**  
[Data Structure & Algorithms Tutorial][11]  
[[演算法] 插補搜尋法(Interpolation Search)][10]

[0]: http://xuwenzhi.com/author/xuwenzhi/
[1]: http://xuwenzhi.com/2016/04/03/php%e7%a8%8b%e5%ba%8f%e5%91%98%e9%9c%80%e8%a6%81%e6%8e%8c%e6%8f%a1%e7%9a%84%e6%9f%a5%e6%89%be%e7%ae%97%e6%b3%95/
[2]: http://xuwenzhi.com/category/dsa/
[3]: http://xuwenzhi.com/category/php/
[4]: http://xuwenzhi.com/2016/04/03/php%e7%a8%8b%e5%ba%8f%e5%91%98%e9%9c%80%e8%a6%81%e6%8e%8c%e6%8f%a1%e7%9a%84%e6%9f%a5%e6%89%be%e7%ae%97%e6%b3%95/#respond
[5]: ./img/linear_search.gif
[6]: ./img/binary_search_0.jpg
[7]: ./img/binary_search_1.jpg
[8]: ./img/binary_search_2.jpg
[9]: ./img/binary_search_3.jpg
[10]: http://notepad.yehyeh.net/Content/Algorithm/Search/InterpolationSearch/InterpolationSearch.php
[11]: http://www.tutorialspoint.com/data_structures_algorithms/index.htm