<?php
/**
 * 作者：迹忆
 * 个人博客：迹忆博客
 * 博客url：www.onmpw.com
 * ************
 * MSD基数排序
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
 * MSD基数排序函数
 * @param array $arr  待分组的序列
 * @param array $r    表示当前是按照第几位进行分组
 */
function MSD_RadixSort(&$arr,$r){
    $radix = pow(10,$r-1);
    $bucket = array();
    //初始化队列
    for($i=0;$i<10;$i++){
        $bucket[$i]=array('num'=>0,'val'=>array());
    }
    for($j=0;$j<count($arr);$j++){
        $k = floor($arr[$j]%pow(10,$r)/$radix);
        $bucket[$k]['num']++;
        array_push($bucket[$k]['val'],$arr[$j]);
    }
    for($j=0;$j<10;$j++){
        if($bucket[$j]['num']>1){
            MSD_RadixSort($bucket[$j]['val'],$r-1);
        }
    }
    /*
     * 将桶中的数据收集起来，此时序列是有序的
     */
    $arr = array();
    for($j=0;$j<10;$j++){
        for($i=0;$i<count($bucket[$j]['val']);$i++){
            $arr[] = $bucket[$j]['val'][$i];
        }
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
MSD_RadixSort($arr,FindMaxBit($arr));
print_r($arr);