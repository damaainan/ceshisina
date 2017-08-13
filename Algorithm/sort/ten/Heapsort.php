<?php 
header("Content-type:text/html; Charset=utf-8");


$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 50000);
}



// 5 堆排序

/**
 * 交换两个数的位置
 * @param $a
 * @param $b
 */
function swap(&$a,&$b){
    $temp = $b;
    $b = $a;
    $a = $temp;
}

/**
 * 左子树
 * @param $i
 * @return mixed
 */
function lchild($i){ return $i*2+1;}

/**
 * 右子树
 * @param $i
 * @return mixed
 */
function rchild($i){ return $i*2+2;}

/**
 * 整理节点
 * @param $array 待调整的堆数组
 * @param $i 待调整的数组元素的位置
 * @param $heapsize  数组的长度
 */
function build_heap(&$array,$i,$heapsize){

    $left = lchild($i);
    $right = rchild($i);
    $max = $i;
    //如果比左子树小并且在左右子树的右面,边界调整到左侧
    if($i < $heapsize && $left < $heapsize  && $array[$left] > $array[$i] ){
        $max = $left;
    }

    //如果比右子树小并且都小于要构建的数组长度,边界调整到右侧
    if($i < $heapsize && $right < $heapsize && $array[$right] > $array[$max]){
        $max = $right;
    }

    //如果经过两次调整后,要调整的数组不是最大值
    if($i != $max && $i < $heapsize && $max < $heapsize){

        //就交换对应的位置,并再次进行整理节点
        swap($array[$i],$array[$max]);
        build_heap($array,$max,$heapsize);

    }
}

/**
 * 对堆进行排序
 * @param $array 要排序的数组
 * @param $heapsize 数组的长度
 */
function sortHeap(&$array,$heapsize){
    while($heapsize){ //长度逐步递减0

        //首先交换第一个元素和最后一个元素的位置
        swap($array[0],$array[$heapsize-1]);
        $heapsize = $heapsize -1;
        build_heap($array,0,$heapsize); //整理数组的第一个的元素的位置,长度为逐步递减的数组长度
    }
}

/**
 * 创建堆
 * @param $array
 * @param $heapsize
 */
function createHeap(&$array,$heapsize){
    $i = ceil($heapsize/2)-1; //找到中间的位置
    for( ; $i>=0 ;$i-- ){  //从中间往前面整理堆
        build_heap($array,$i,$heapsize);
    }
}

/**
 * 堆排序主函数
 */
function Heapsort($array){
    $heapsize = count($array);
    createHeap($array,$heapsize);
    sortHeap($array,$heapsize);

    return $array;

}



$heapsort_start_time = microtime(true);

$heapsort_sort = Heapsort($arr);

$heapsort_end_time = microtime(true);

$heapsort_need_time = $heapsort_end_time - $heapsort_start_time;

print_r("堆排序耗时:" . $heapsort_need_time . "<br />");