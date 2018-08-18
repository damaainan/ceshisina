<?php
/**
 * 作者：迹忆
 * 个人博客：迹忆博客
 * 博客url：www.onmpw.com
 * ************
 * LSD基数排序
 * ************
 */
/**
 * 找到序列中最大位数
 */
function FindMaxBit($arr){
    /*
     * 首先查找序列中最大的数
     */
    $p = $arr[0];
    for($i=1;$i<count($arr);$i++){
        if($arr[$i]>=$p){
            $p = $arr[$i];
        }
    }
    //得到最大的数以后，计算出该数据有多少位
    $d = 1;
    while(floor($p/10)>0){
        $d++;
        $p = floor($p/10);
    }
    return $d;
}
/**
 * 直接将数据存入桶中，不再申请额外的队列
 */
function LSD1_RadixSort(&$arr){
    //得到序列中最大位数
    $d = FindMaxBit($arr);
    $bucket = array();
    //初始化队列
    for($i=0;$i<10;$i++){
        $bucket[$i]=array('num'=>0,'val'=>array());
    }
    /*
     * 开始进行分配
     */
    $radix = 1;
    for($i=1;$i<=$d;$i++){
        for($j=0;$j<count($arr);$j++){
            $k = floor($arr[$j]/$radix)%10;
            $bucket[$k]['num']++;
            array_push($bucket[$k]['val'],$arr[$j]);
        }
        $arr = array();
        foreach ($bucket as $key => $val) {
            for ($j = 0; $j < $val['num']; $j ++) {
                $arr[] = $val['val'][$j];
            }
        }
        for($l=0;$l<10;$l++){
            $bucket[$l]=array('num'=>0,'val'=>array());
        }
        $radix *= 10;
    }
}
/**
 * 申请一个临时队列，桶中只存原队列中的元素在临时队列中的位置
 */
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
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
LSD_RadixSort($arr);
print_r($arr);