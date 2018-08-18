<?php
function LSD_RadixSort(&$arr){
    //得到序列中最大位数
    $d = FindMaxBit($arr);
    $bucket = array();
    $temp = array();
    //初始化队列
    for($i=0;$i<10;$i++){
        $bucket[$i] = 0;
    }
    /*
     * 开始进行分配
     */
    $radix = 1;
    for($i=1;$i<=$d;$i++){
        for($j=0;$j<count($arr);$j++){
            $k = floor($arr[$j]/$radix)%10;
            $bucket[$k]++;
        }
        //在桶中调整原序列在临时队列中的位置
        for($j=1;$j<10;$j++){
            $bucket[$j] += $bucket[$j-1];
        }
        for($j=count($arr)-1;$j>=0;$j--){
            $k = floor($arr[$j]/$radix)%10;
            $temp[--$bucket[$k]] = $arr[$j];
        }
        /*
         * 将临时队列赋值给原序列
         */
        for($j=0;$j<count($temp);$j++){
            $arr[$j] = $temp[$j];
        }
        for($j=0;$j<10;$j++){
            $bucket[$j] = 0;
        }
        $radix *= 10;
    }
}