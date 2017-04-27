# 最大子序列和（4种方式）

作者  林湾村龙猫关注 2016.01.21 01:32  

## 一、问题描述

输入一组整数，求出这组数字子序列和中最大值。也就是只要求出最大子序列的和，不必求出最大的那个序列。例如：

1. 序列：-2 11 -4 13 -5 -2，则最大子序列和为20。
1. 序列：-6 2 4 -7 5 3 2 -1 6 -9 10 -2，则最大子序列和为16。

## 二、PHP代码实现

实现可参考：[http://www.cnblogs.com/CCBB/archive/2009/04/25/1443455.html][1]

    <?php
    /**
     * 找出最大子序列和
     */
    
    // 方法一、 最暴力的方法 O(N^3)
    function findMax1($data){
        $max_value = 0;
        $max_start = 0;
        $max_end = 0;
        $data_num = count($data);
        for($i=0;$i<$data_num;$i++){
            for($j=$i;$j<$data_num;$j++){
                $sum = 0;
                $temp = array();
                for($k=$i;$k<=$j;$k++){
                    $temp[] = $data[$k];
                    $sum += $data[$k];
                }
                if($max_value < $sum){
                    $max_value = $sum;
                    $max_start = $i;
                    $max_end = $j;
                }
            }
        }
    
        return array($max_value,$max_start,$max_end);
    }
    
    
    //方法二、 记录上一次的结果  O(N^2)
    function findMax2($data){
        $max_value = 0;
        $max_start = 0;
        $max_end = 0;
        $data_num = count($data);
        for($i=0;$i<$data_num;$i++){
            $last_sum = 0;
            for($j=$i;$j<$data_num;$j++){
                $sum = $last_sum+$data[$j];
                if($max_value < $sum){
                    $max_value = $sum;
                    $max_start = $i;
                    $max_end = $j;
                }
                $last_sum = $sum;
            }
        }
    
        return array($max_value,$max_start,$max_end);
    }
    
    //方法三、分而自治算法 O(NlogN)
    function findMax3($data,$start=0,$end=0){
        if($start >= $end){
            $max_value =  isset($data[$start])?$data[$start]:0;
        }else{
            $mid = floor(($start+$end)/2);
    
            list($max_left_value,$max_left_start,$max_left_end) = findMax3($data,$start,$mid);
            list($max_right_value,$max_right_start,$max_right_end) = findMax3($data,$mid+1,$end);
    
            //计算左边的最大值
            $max_this_value = $data[$mid];
            $max_this_start = $mid;
            $max_this_end = $mid;
            $temp = 0;
            for($i=$max_this_start;$i>=$start;$i--){
                $temp += $data[$i];
                if($temp > $max_this_value){
                    $max_this_value = $temp;
                    $max_this_start = $i;
                }
            }
    
            //计算右边的最大值
            $temp = $max_this_value;
            for($i=$max_this_end+1;$i<=$end;$i++){
                $temp += $data[$i];
                if($temp > $max_this_value){
                    $max_this_value = $temp;
                    $max_this_end = $i;
                }
            }
    
            $max_value = $max_this_value;
            $start = $max_this_start;
            $end = $max_this_end;
            if($max_value < $max_left_value){
                $max_value = $max_left_value;
                $start = $max_left_start;
                $end = $max_left_end;
            }
            if($max_value < $max_right_value){
                $max_value = $max_right_value;
                $start = $max_right_start;
                $end = $max_right_end;
            }
        }
        return array($max_value,$start,$end);
    }
    
    
    //方法四、找规律算法  O(N)
    function findMax4($data){
        $max_value = 0;
        $thisSum = 0;
        $data_len = count($data);
        $start = 0;
        $end = 0;
        for($i=0;$i<$data_len;$i++){
            $thisSum += $data[$i];
            if($thisSum > $max_value){
                $max_value = $thisSum;
                $end =$i;
            }else if($thisSum < 0){
                $thisSum = 0;
                $start= $i+1;
            }
        }
        return array($max_value,$start,$end);
    }

## 三、测试

    <?php
    // 获取一个随机数列
    function getArrayData($len=0){
        $arr_data = array('4','-6','8','2','-4','7','2','-5','1');
        if($len != 0){
            $arr_data = array();
            for($i=0;$i<$len;$i++){
                $arr_data[] = rand(-9,9);
            }
        }
        return $arr_data;
    }
    
    $arr_data = getArrayData(1000);
    echo implode(',',$arr_data);
    echo "\n";
    
    echo "*************findMax1:最暴力的方法**********\n";
    $start_time = time();
    list($max_value1,$start1,$end1)=findMax1($arr_data);
    echo "max_value:{$max_value1}\n";
    echo "index:{$start1}-{$end1}\n";
    echo 'take time:'.(time()-$start_time);echo "\n";
    
    echo "*************findMax2:记录上一次的结果**********\n";
    $start_time = time();
    list($max_value2,$start2,$end2)=findMax2($arr_data);
    echo "max_value:{$max_value2}\n";
    echo "index:{$start2}-{$end2}\n";
    echo 'take time:'.(time()-$start_time);echo "\n";
    
    echo "*************findMax3:分而治之算法**********\n";
    $start_time = time();
    list($max_value3,$start3,$end3)=findMax3($arr_data,0,count($arr_data)-1);
    echo "max_value:{$max_value3}\n";
    echo "index:{$start3}-{$end3}";echo "\n";
    echo 'take time:'.(time()-$start_time);echo "\n";
    
    echo "*************findMax4:找规律算法**********\n";
    $start_time = time();
    list($max_value4,$start4,$end4)=findMax4($arr_data);
    echo "max_value:{$max_value4}\n";
    echo "{$start4}-{$end4}";echo "\n";
    echo 'take time:'.(time()-$start_time);echo "\n";

## 四、测试结果

1. n=1000

![][2]



测试结果:n=1000
1. n=5000

还在测试中，第一种方案太慢了


[1]: http://www.cnblogs.com/CCBB/archive/2009/04/25/1443455.html
[2]: http://upload-images.jianshu.io/upload_images/301894-968712ed404144d9.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240