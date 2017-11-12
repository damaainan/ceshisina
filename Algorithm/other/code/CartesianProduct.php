<?php

/**
笛卡尔积

笛卡尔积是指在数学中，两个集合X和Y的笛卡尔积(Cartesian product)，又称直积，表示为X*Y，第一个对象是X的成员而第二个对象是Y的所有可能有序对的其中一个成员。
假设集合A={a,b}，集合B={0,1,2}，则两个集合的笛卡尔积为{(a,0),(a,1),(a,2),(b,0),(b,1),(b,2)}

实现思路

先计算第一个集合和第二个集合的笛卡尔积，把结果保存为一个新集合。 
然后再用新集合与下一个集合计算笛卡尔积，依此循环直到与最后一个集合计算笛卡尔积。
例如有以下几个集合，需要计算笛卡尔积
 */


$sets = array(
    array('白色', '黑色', '红色'),
    array('透气', '防滑'),
    array('37码', '38码', '39码'),
    array('男款', '女款'),
);

/**
 * php 计算多个集合的笛卡尔积
 * Date: 2017-01-10
 * Author: fdipzone
 * Ver: 1.0
 *
 * Func
 * CartesianProduct 计算多个集合的笛卡尔积
 */

/**
 * 计算多个集合的笛卡尔积
 * @param Array $sets 集合数组
 * @return Array
 */
function CartesianProduct($sets)
{

    // 保存结果
    $result = array();

    // 循环遍历集合数据
    for ($i = 0, $count = count($sets); $i < $count - 1; $i++) {

        // 初始化
        if ($i == 0) {
            $result = $sets[$i];
        }

        // 保存临时数据
        $tmp = array();

        // 结果与下一个集合计算笛卡尔积
        foreach ($result as $res) {
            foreach ($sets[$i + 1] as $set) {
                $tmp[] = $res . $set;
            }
        }

        // 将笛卡尔积写入结果
        $result = $tmp;

    }

    return $result;

}

// 定义集合
$sets = array(
    array('白色', '黑色', '红色'),
    array('透气', '防滑'),
    array('37码', '38码', '39码'),
    array('男款', '女款'),
);

$result = CartesianProduct($sets);
print_r($result);
