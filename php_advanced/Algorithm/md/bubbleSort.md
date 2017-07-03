# 排序-冒泡排序及快速排序

作者  [林湾村龙猫][0] 已关注 2016.01.21 01:09*  字数 624  阅读 205 评论 0 喜欢 1

## **概述**

冒泡排序法的基本思想：（以升序为例）含有n个元素的数组原则上要进行n-1次排序。对于每一躺的排序，从第一个数开始，依次比较前一个数与后一个数的大小。如果前一个数比后一个数大，则进行交换。这样一轮过后，最大的数将会出现称为最末位的数组元素。第二轮则去掉最后一个数，对前n-1个数再按照上面的步骤找出最大数，该数将称为倒数第二的数组元素......n-1轮过后，就完成了排序。  
快速排序是冒泡排序的一种改进，快速排序由于排序效率在同为O(N*logN)的几种排序方法中效率较高，因此经常被采用，再加上快速排序思想----分治法也确实实用，因此很多软件公司的笔试面试，包括像腾讯，微软等知名IT公司都喜欢考这个，还有大大小的程序方面的考试如软考，考研中也常常出现快速排序的身影。

1. _冒泡排序有点类似与水中的气泡，越来越大；_
1. _快速排序以一个基准值，将无序列分成两部分（左边小于基准值，右边大于基准值），然后递归。_

## **理论**

[http://www.cnblogs.com/hb_cattle/articles/1552419.html][1]  
[http://blog.csdn.net/morewindows/article/details/6684558][2]

## **动画**

#### **1.冒泡排序**

![][3]



冒泡排序动画1

![][4]



冒泡排序动画2

#### **2.快速排序**

![][5]



快速排序动画1

![][6]



快速排序动画2

## **代码（PHP）**

#### **1.冒泡排序**

    //冒泡排序(O(n2))
    function bubbleSort($arr){
        $length = count($arr);
        if($length < 2){
            return $arr;
        }
        for($i=0;$i<$length;$i++){
            $temp = $arr[0];
            for($j=0;$j< $length-$i-1;$j++){
                if($arr[$j] > $arr[$j+1]){
                    list($arr[$j],$arr[$j+1])= array($arr[$j+1],$arr[$j]);
                }
            }
        }
        return $arr;
    }

#### **2.快速排序**

    //快速排序
    function quickSort(&$arr,$height,$low=0){
        if($height <= $low){
            return $arr;
        }
        $i=$low+1;
        $j= $height;
        $temp = $arr[$low];
        while($i<$j){
            while($i<$j && $arr[$j] > $temp){$j--;}
            while($i<$j && $arr[$i] <= $temp){$i++;}
            list($arr[$i],$arr[$j]) = array($arr[$j],$arr[$i]);
        }
        if($arr[$i] <= $temp){
            list($arr[$low],$arr[$i])=array($arr[$i],$arr[$low]);
        }
    
        quickSort($arr,$i-1,$low);
        quickSort($arr,$height,$j+1);
        return $arr;
    }

#### **3.测试**

    $item =array('2','1','4','3','8','6','5','-1','10','3','7','6','6');
    var_dump(implode(',',$item));
    var_dump(implode(',',bubbleSort($item)));
    var_dump(implode(',',quickSort($item)));

## **结果**

![][7]



冒泡，快速排序

[0]: /u/5a327aab786a
[1]: http://www.cnblogs.com/hb_cattle/articles/1552419.html
[2]: http://blog.csdn.net/morewindows/article/details/6684558
[3]: ../img/301894-ebc68bb6b8cb4a7d.gif
[4]: ../img/301894-bd146c59ea6259d6.gif
[5]: ../img/301894-f1e6c9bc59281e32.gif
[6]: ../img/301894-5613104ea85ee186.gif
[7]: ../img/301894-3c2dee860246b7cc.png