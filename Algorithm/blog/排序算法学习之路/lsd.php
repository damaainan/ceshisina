<?php
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
function LSD_RadixSort(&$arr){
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
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
LSD_RadixSort($arr,0,count($arr)-1);
print_r($arr);  