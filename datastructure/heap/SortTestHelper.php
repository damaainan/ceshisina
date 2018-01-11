<?php
/**
 * 排序算法帮助函数
 * @author junqi1 <2018-1-7>
 */
/**
 * [generateRandomArray 生成有n个元素的随机数组,每个元素的随机范围为[rangeL, rangeR]]
 * @param  [type] $n      [description]
 * @param  [type] $rangeL [description]
 * @param  [type] $rangeR [description]
 * @return [type]         [description]
 */
function generateRandomArray($n, $rangeL, $rangeR) {
    assert($rangeL < $rangeR);
    $arr = array();
    for ($i = 0; $i < $n; $i++){
        $arr[$i] = rand($rangeL, $rangeR);
    }
    return $arr;
}
/**
 * [generateNearlyOrderedArray 产生n个几乎有序的数组， ]
 * @param  [type] $n         [description]
 * @param  [type] $swapTimes [顺序数组中调换的个数]
 * @return [type]            [description]
 */
function generateNearlyOrderedArray($n, $swapTimes){
    $arr = array();
    for ($i=0; $i < $n; $i++) { 
        $arr[$i] = $i;
    }
    //调换数组
    for ($i=0; $i < $swapTimes; $i++) {
        $swap1 = rand(0, $n-1);
        $swap2 = rand(0, $n-1);
        swap($arr, $swap1, $swap2);
    }
    return $arr;
}
//数组元素交换
function swap(&$arr, $i, $j){
    $tmp = $arr[$i];
    $arr[$i] = $arr[$j];
    $arr[$j] = $tmp;
}
function isSort($arr, $n){
    for ($i=0; $i < $n-1; $i++) {
        if ($arr[$i] > $arr[$i+1]) {
            return false;
        }
    }
    return true;
}
//测试排序算法的性能
function testSort($sortName, $sorFunction, $arr, $n){
    $t1 = microtime(true);
    $sorFunction($arr, $n);
    $t2 = microtime(true);
    assert(isSort($arr, $n), "排序算法错误！\n");
    echo "{$sortName}运行的时间为：". (($t2-$t1)).'s'."\n";
}