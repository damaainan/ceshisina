# [PHP实现排序算法----基数排序（Radix Sort）][0]

 标签： [php][1][排序算法][2][基数排序][3][桶排序][4]

 2016-11-23 20:29  304人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [基本思想][10]
1. [基本解法][11]
1. [算法实现][12]

基数排序在《大话设计模式》中并未讲到，但是为了凑齐八大排序[算法][13]，我自己通过网络学习了这个排序算法，并给大家分享出来。

## 基本思想：

基数排序（radix sort）属于“分配式排序”（distribution sort），又称“桶子法”（bucket sort）或bin sort，顾名思义，它是透过键值的部份资讯，将要排序的元素分配至某些“桶”中，藉以达到排序的作用，基数排序法是属于稳定性的排序，其时间复杂度为O (nlog(r)m)，其中r为所采取的基数，而m为堆数，在某些时候，基数排序法的效率高于其它的稳定性排序法。

其实这个思想我也没法总结出来，下面通过例子来说明吧：

## 基本解法：

PS：在这里我们介绍的基数排序我们采用 LSD（最低位优先），当然还有 MSD（最高位优先），大家自己去百度一下他们之间的异同吧。

假如现在我们有以下这么一些数：

    2 343 342 1 128 43 4249 814 687 654 3

我们使用基数排序将他们从小到大排序。

第一步、首先根据个位数的数值，在走访数值（从前到后走访，后面步骤相同）时将它们分配至编号0到9的桶子中：

    0 : 
    1 : 1
    2 : 2 342 
    3 : 343 43 3
    4 : 814 654
    5 : 
    6 : 
    7 : 687
    8 : 128
    9 : 4249

第二步、接下来将这些桶子中的数值重新串接起来，成为以下的数列：

    1 2 342 343 43 3 814 654 687 128 4249

第三步、根据十位数的数值，在走访数值（从前到后走访，后面步骤相同）时将它们分配至编号0到9的桶子中：

    0 : 1 2 3 
    1 : 814 
    2 : 128
    3 : 
    4 : 342 343 43 4249
    5 : 654
    6 : 
    7 : 
    8 : 687
    9 : 


第四步、接下来将这些桶子中的数值重新串接起来，成为以下的数列：

    1 2 3 814 128 342 343 43 4249 654 687


第五步、根据百位数的数值，在走访数值（从前到后走访，后面步骤相同）时将它们分配至编号0到9的桶子中：

    0 : 1 2 3 43 
    1 : 128
    2 : 4249
    3 : 342 343
    4 : 
    5 : 
    6 : 654 687
    7 : 
    8 : 814 
    9 : 


第六步、接下来将这些桶子中的数值重新串接起来，成为以下的数列：

    1 2 3 43 128 4249 342 343 654 687 814


。。。。。。后面的步骤大家应该都会走了吧。其实到了第六步的时候就剩 4249 没有排好序了。

从上面的步骤来看，很多的步骤都是相同的，因此肯定是个循环了，我们只需要控制个位、十位、百位、、、、就好了。

还是看代码吧。

## 算法实现：

```php
    //交换函数
    function swap(array &$arr,$a,$b){
        $temp = $arr[$a];
        $arr[$a] = $arr[$b];
        $arr[$b] = $temp;
    }
    
    //获取数组中的最大数
    //就像上面的例子一样，我们最终是否停止算法不过就是看数组中的最大值：4249，它的位数就是循环的次数
    function getMax(array $arr){
        $max = 0;
        $length = count($arr);
        for($i = 0;$i < $length;$i ++){
            if($max < $arr[$i]){
                $max = $arr[$i];
            }
        }
        return $max;
    }
    
    //获取最大数的位数,最大值的位数就是我们分配桶的次数
    function getLoopTimes($maxNum){
        $count = 1;
        $temp = floor($maxNum / 10);
        while($temp != 0){
            $count ++;
            $temp = floor($temp / 10);
        }
        return $count;
    }
    
    /**
     * @param array $arr 待排序数组
     * @param $loop 第几次循环标识
     * 该函数只是完成某一位（个位或十位）上的桶排序
     */
    function R_Sort(array &$arr,$loop){
        //桶数组，在强类型语言中，这个数组应该声明为[10][count($arr)]
        //第一维是 0-9 十个数
        //第二维这样定义是因为有可能待排序的数组中的所有数的某一位上的只是一样的，这样就全挤在一个桶里面了
        $tempArr = array();
        $count = count($arr);
    
        //初始化$tempArr数组
        for($i = 0;$i < 10;$i ++){
            $tempArr[$i] = array();
        }
    
        //求桶的index的除数
        //如798个位桶index=(798/1)%10=8
        //十位桶index=(798/10)%10=9
        //百位桶index=(798/100)%10=7
        //$tempNum为上式中的1、10、100
        $tempNum = (int)pow(10, $loop - 1);
    
        for($i = 0;$i < $count;$i ++){
            //求出某位上的数字
            $row_index = ($arr[$i] / $tempNum) % 10;
            for($j = 0;$j < $count;$j ++){
                if(@$tempArr[$row_index][$j] == NULL){
                    $tempArr[$row_index][$j] = $arr[$i];     //入桶
                    break;
                }
            }
        }
    
        //还原回原数组中
        $k = 0;
        for($i = 0;$i < 10;$i ++){
            for($j = 0;$j < $count;$j ++){
                if(@$tempArr[$i][$j] != NULL){
                    $arr[$k ++] = $tempArr[$i][$j];    //出桶
                    $tempArr[$i][$j] = NULL;   //避免下次循环的时候污染数据
                }
            }
        }
    }
    
    
    //最终调用的主函数
    function RadixSort(array &$arr){
        $max = getMax($arr);
        $loop = getLoopTimes($max);
        //对每一位进行桶分配（1 表示个位，$loop 表示最高位）
        for($i = 1;$i <= $loop;$i ++){
            R_Sort($arr,$i);
        }
    }
```

调用算法：

    $arr = array(2, 343, 342, 1, 128, 43, 4249, 814, 687, 654, 3);
    RadixSort($arr);
    var_dump($arr);


其实这些代码我是在挺早之前写的，今天在写博客的时候发现，其实桶就是一个队列，所以上面的 R_Sort（）函数复杂了，我们使用 array_push() 和 array_shift() 来重写该方法（当然，要模拟队列的话，用 SPL 提供的 splqueue 是最为恰当的，在这里为了简便我就不用了）：

```php
    function R_Sort(array &$arr,$loop){
        $tempArr = array();
        $count = count($arr);
        for($i = 0;$i < 10;$i ++){
            $tempArr[$i] = array();
        }
        //求桶的index的除数
        //如798个位桶index=(798/1)%10=8
        //十位桶index=(798/10)%10=9
        //百位桶index=(798/100)%10=7
        //$tempNum为上式中的1、10、100
        $tempNum = (int)pow(10, $loop - 1);
        for($i = 0;$i < $count;$i ++){
            //求出某位上的数字
            $row_index = ($arr[$i] / $tempNum) % 10;
            //入桶
            array_push($tempArr[$row_index],$arr[$i]);
        }
    
        //还原回原数组中
        $k = 0;
        for($i = 0;$i < 10;$i ++){
            //出桶
            while(count($tempArr[$i]) > 0){
                $arr[$k ++] = array_shift($tempArr[$i]);
            }
        }
    }
```

基数排序法是属于稳定性的排序，其时间复杂度为O (nlog(r)m)，其中r为所采取的基数，而m为堆数。

[0]: http://www.csdn.net/baidu_30000217/article/details/53309720
[1]: http://www.csdn.net/tag/php
[2]: http://www.csdn.net/tag/%e6%8e%92%e5%ba%8f%e7%ae%97%e6%b3%95
[3]: http://www.csdn.net/tag/%e5%9f%ba%e6%95%b0%e6%8e%92%e5%ba%8f
[4]: http://www.csdn.net/tag/%e6%a1%b6%e6%8e%92%e5%ba%8f
[9]: #
[10]: #t0
[11]: #t1
[12]: #t2
[13]: http://lib.csdn.net/base/datastructure