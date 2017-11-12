<?php
header("content-type:text/html;charset=utf-8");

// 矩阵转置求素数冒泡排序选择排序 

/**
 *
 * PHP版数据结构基本算法
 * 1.矩阵转置
 * 2.求素数
 * 3.冒泡排序
 * 4.选择排序
 *//**
 * 矩阵转置
 *
 * @param array $matrix 待转置的矩阵
 * @param array return 转置后的矩阵
 * */
function transposition($matrix)
{
    $i = 0;
    $j = 0;
    foreach ($matrix as $line) {
        foreach ($line as $element) {
            $tm[$j++][$i] = $element;
        }
        $j = 0;
        $i++;
    }
    return $tm;
}
$matrix = array(
    array(1, 2, 3, 'a'),
    array(4, 5, 6, 'b'),
    array(7, 8, 9, 'c'),
);
echo "<br/-->转置前的矩阵：";
foreach ($matrix as $line) {
    echo "<br>";
    foreach ($line as $value) {
        echo $value . "  ";
    }
}
$tm = transposition($matrix);
echo "<br>转置后的矩阵：";
foreach ($tm as $line) {
    echo "<br>";
    foreach ($line as $element) {
        echo $element . "  ";
    }
}/**
 * 求素数
 *@param int  $n 求2~$n内的所有素数
 *@return array 返回2~$n所有的素数集合
 **/
function primenumber($n)
{
    $i     = 3;
    $prime = array(2);
    $tag   = true;
    while ($i <= $n) {
        foreach ($prime as $value) {
            if ($i % $value == 0) {
                $tag = false;
                break;
            }
            $tag = true;
        }
        if ($tag) {
            $prime[] = $i;
        }
        $i++;
    }
    return $prime;
}
$n     = 200;
$prime = primenumber($n);
echo "<br>2~{$n}内的素数有：<br>";
foreach ($prime as $value) {
    echo $value . "  ";
}/**
 * 冒泡排序
 *
 *@param array $data 待排序的数组
 *@param int $tag 0表示由小到大排序,1表示由大到小排序
 *@param array 排序后的结果
 **/
function bubblingsort($data, $tag = 0)
{
    $arrlen = count($data);
    for ($i = $arrlen - 1; $i >= 0; $i--) {
        for ($j = 0; $j < $i; $j++) {
            if ($data[$i] > $data[$j]) {
                if ($tag == 1) {
                    $m        = $data[$j];
                    $data[$j] = $data[$i];
                    $data[$i] = $m;
                }
            } else {
                if ($tag == 0) {
                    $m        = $data[$i];
                    $data[$i] = $data[$j];
                    $data[$j] = $m;
                }
            }
        }
    }
    return $data;
}
$data = array(34, 22, 2, 56, 90);
echo "<br>冒泡排序前：<br>";
foreach ($data as $value) {
    echo $value . "  ";
}
$data = bubblingsort($data);
echo "<br>由小到大排序后：<br>";
foreach ($data as $value) {
    echo $value . "  ";
}
$data = bubblingsort($data, 1);
echo "<br>由大到小排序后：<br>";
foreach ($data as $value) {
    echo $value . "  ";
}
/**
 * 选择排序
 *
 *@param array $data 待排序的数组
 *@param int $tag 0表示由小到大排序,1表示由大到小排序
 *@param array 排序后的结果
 **/
function selectsort($data, $tag = 0)
{
    $arrlen = count($data);
    for ($i = 0; $i < $arrlen - 1; $i++) {
        for ($j = $i + 1; $j < $arrlen; $j++) {
            if ($data[$i] > $data[$j]) {
                if ($tag == 0) {
                    $m        = $data[$i];
                    $data[$i] = $data[$j];
                    $data[$j] = $m;
                }
            } else {
                if ($tag == 1) {
                    $m        = $data[$i];
                    $data[$i] = $data[$j];
                    $data[$j] = $m;
                }
            }
        }
    }
    return $data;
}
$data = array(34, 22, 2, 56, 90);
echo "<br>选择排序前：<br>";
foreach ($data as $value) {
    echo $value . "  ";
}
$data = selectsort($data);
echo "<br>由小到大排序后：<br>";
foreach ($data as $value) {
    echo $value . "  ";
}
$data = selectsort($data, 1);
echo "<br>由大到小排序后：<br>";
foreach ($data as $value) {
    echo $value . "  ";
}
