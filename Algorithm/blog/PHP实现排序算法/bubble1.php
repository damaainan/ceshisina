<?php

//这里使用了类型提示（type hint） array，不熟悉或者不习惯的同学大可去掉，不影响运算结果
function MySort(array &$arr)
{
    $length = count($arr);
    for ($i = 0; $i < $length - 1; $i++) {
        for ($j = $i + 1; $j < $length; $j++) {
            //将小的关键字放前面
            if ($arr[$i] > $arr[$j]) {
                $temp    = $arr[$i];
                $arr[$i] = $arr[$j];
                $arr[$j] = $temp;
            }
        }
    }
}

$arr = array(9, 1, 5, 8, 3, 7, 4, 6, 2);
MySort($arr);
print_r($arr);
