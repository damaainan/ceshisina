# 查找-折半查找

 作者  林湾村龙猫 关注 2016.01.21 01:01  

## **概述**

二分查找法主要是解决在“一堆数中找出指定的数”这类问题。而想要应用二分查找法，这“一堆数”必须有一下特征：

* 存储在数组中
* 有序排列

所以如果是用链表存储的，就无法在其上应用二分查找法了。（曽在面试被问二分查找法可以什么数据结构上使用：数组？链表？）至于是顺序递增排列还是递减排列，数组中是否存在相同的元素都不要紧。不过一般情况，我们还是希望并假设数组是递增排列，数组中的元素互不相同。

## **理论**

参见：  
[http://www.cnblogs.com/ider/archive/2012/04/01/binary_search.html][1]  
[http://taop.marchtea.com/04.01.html][2]

## **递归实现折半查找(PHP)**

```php
<?php
    function re_binary_search($arr,$target,$height,$low=0){
        if($height < $low || $arr[$low] > $target || $arr[$height] < $target){
            return -1;
        }
    
        $mid = intval(($low+$height)/2);
        if($arr[$mid] > $target){//前半段
            return re_binary_search($arr,$target,$mid-1,$low);
        }
        if($arr[$mid] < $target){//后半段
            return re_binary_search($arr,$target,$height,$mid+1);
        }
        return $mid;
    }
```

## **非递归实现折半查找（PHP）**

```php
<?php
    function binary_search($arr,$target){
        $length = count($arr);
        if($length <=0 || $arr[0] > $target || $arr[$length-1] < $target){
            return -1;
        }
        $low = 0;
        $height = $length-1;
        while($low <= $height){
            $mid = (int)(($low+$height)/2);
            if($arr[$mid] > $target){
                $height = $mid-1;
            }else if($arr[$mid] < $target){
                $low = $mid+1;
            }else{
                return $mid;
            }
        }
        return -1;
    }
```

## **调用**

```php
<?php
    var_dump(re_binary_search($item,'8',count($item)-1));
    var_dump($item[re_binary_search($item,'8',count($item)-1)]);
    
    var_dump(binary_search($item,'8'));
    var_dump($item[binary_search($item,'8')]);
```

## **结果**

![][3]



折半查找


[1]: http://www.cnblogs.com/ider/archive/2012/04/01/binary_search.html
[2]: http://taop.marchtea.com/04.01.html
[3]: ../img/301894-b0b139ddc006b977.png