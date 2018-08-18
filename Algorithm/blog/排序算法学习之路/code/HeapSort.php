<?php
/**
 * 作者：迹忆
 * 个人博客：迹忆博客
 * 博客url：www.onmpw.com
 * ************
 * 堆排序
 * ************
 */
/**
 * 交换函数
 */
function swap(&$arr,$a,$b){
    $t = $arr[$a];
    $arr[$a] = $arr[$b];
    $arr[$b] = $t;
}
/**
 * 调整一个节点和其左右子节点满足大顶堆的性质
 */
function MakeHeapChild(&$arr,$pos,$end){
    $p = false;
    if($pos*2+1 <= $end){
        //左右子节点相比较，找出最小值
        if($arr[$pos*2-1]>=$arr[$pos*2]){
            $p = $pos*2;
        }else{
            $p = $pos*2+1;
        }
    }elseif($pos*2<=$end){
        $p = $pos*2;
    }
    if(!$p) return $p;
    //比较当前节点和其最小的子节点
    if($arr[$pos-1]<=$arr[$p-1]){
        swap($arr,$pos-1,$p-1);
        return $p;
    }
    return false;

}
function HeapSort(&$arr){
    $start = floor(count($arr)/2); //找到最后一个非叶子节点
    $end = count($arr);
    /*
     * 构造大顶堆
    */
    while($start>0){
        $p = $start;
        while($p){
            $p = MakeHeapChild($arr,$p,$end);
        }
        $start-- ;
    }
    //构造大顶堆结束
    /*
     * 交换顶部节点和最后一个叶子节点 依次调整大顶堆
     */
    while($end>1){
        swap($arr,0,$end-1);
        $end--;
        $p = 1;
        while($p){
            $p = MakeHeapChild($arr,$p,$end);
        }
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
HeapSort($arr);
print_r($arr);