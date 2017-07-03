# 排序-选择排序

作者  [林湾村龙猫][0] 已关注 2016.01.21 01:18  字数 293  

## **概述**

选择排序：比如在一个长度为N的无序数组中，在第一趟遍历N个数据，找出其中最小的数值与第一个元素交换，第二趟遍历剩下的N-1个数据，找出其中最小的数值与第二个元素交换......第N-1趟遍历剩下的2个数据，找出其中最小的数值与第N-1个元素交换，至此选择排序完成。  
**_选择排序是根据找到无序数列中的最大或最小值插入到有序序列尾部来排序_**

## **理论**

[http://blog.csdn.net/feixiaoxing/article/details/6874619][1]  
[http://www.cnblogs.com/luchen927/archive/2012/02/27/2367108.html][2]

## **动画**

![][3]



选择排序动画1

## **代码（PHP）**

#### **1.直接选择排序**

    //直接选择排序
    function selectSort($arr){
        $length = count($arr);
        if($length < 2){
            return $arr;
        }
        for($i=0;$i<$length-1;$i++){
            $minIndex = $i;
            for($j=$i+1;$j<$length;$j++){
                if($arr[$minIndex]>$arr[$j]){
                    $minIndex = $j;
                }
            }
            if($minIndex != $i){
                list($arr[$minIndex],$arr[$i]) = array($arr[$i],$arr[$minIndex]);
            }
        }
        return $arr;
    }

#### **2.测试**

    $item =array('2','1','4','3','8','6','5','-1','10','3','7','6','6');
    var_dump(implode(',',$item));
    var_dump(implode(',',selectSort($item)));

## **结果**

![][4]



选择排序

[0]: /u/5a327aab786a
[1]: http://blog.csdn.net/feixiaoxing/article/details/6874619
[2]: http://www.cnblogs.com/luchen927/archive/2012/02/27/2367108.html
[3]: ../img/301894-33b3451a1dd33cfc.gif
[4]: ../img/301894-d1ac2fa4892f03cf.png