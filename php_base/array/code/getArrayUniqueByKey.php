<?php

//开发实例：优惠券去重（以 优惠金额-订单金额字段不能重复去除重复项）

// 要求：优惠金额和订单金额都一样的优惠券要求只展示一张给用户选择，并且展示最快到期的那张：

$arrCoupon = array(
    '0' => array(
        'couponCode'      => '3033323852301056',
        'usableStartTime' => "1439740800",
        'usableEndTime'   => "1440798100",
        'couponAmount'    => 100,
        'orderAmount'     => 800,
    ),
    '1' => array(
        'couponCode'      => '3033323852301057',
        'usableStartTime' => "1439740800",
        'usableEndTime'   => "1440768100",
        'couponAmount'    => 100,
        'orderAmount'     => 800,
    ),
    '2' => array(
        'couponCode'      => '3033323852301058',
        'usableStartTime' => "1439740800",
        'usableEndTime'   => "1440788100",
        'couponAmount'    => 100,
        'orderAmount'     => 800,
    ),
    '3' => array(
        'couponCode'      => '3033323852301059',
        'usableStartTime' => "1439740800",
        'usableEndTime'   => "1440779100",
        'couponAmount'    => 200,
        'orderAmount'     => 800,
    ),
    '4' => array(
        'couponCode'      => '3033323852301060',
        'usableStartTime' => "1439740800",
        'usableEndTime'   => "1440758100",
        'couponAmount'    => 200,
        'orderAmount'     => 800,
    ),
    '5' => array(
        'couponCode'      => '3033323852301061',
        'usableStartTime' => "1439740800",
        'usableEndTime'   => "1440798100",
        'couponAmount'    => 200,
        'orderAmount'     => 800,
    ),
);
//print_r($arrCoupon);
function getArrayUniqueByKey($arr)
{
    $arrWish = array();
    $today   = time();
    foreach ($arr as $k => $v) {
        if (($v['usableStartTime'] <= $today) && ($today <= $v['usableEndTime'])) {
            //先确定优惠券的可用日期
            $keyOut = $v['couponAmount'] . "-" . $v['orderAmount'];
            //提取内部一维数组的key(couponAmount orderAmount)作为外部数组的键
            if (array_key_exists($keyOut, $arrWish)) {
                //展现最先到期的优惠券
                if (intval($arrWish[$keyOut]['usableEndTime']) > intval($v['usableEndTime'])) {
                    $arrWish[$keyOut] = $v; //如果原来数组中结束时间大的话，就交换值
                }
                continue;
            }
            $arrWish[$keyOut] = $v; //实现二维数组唯一性
        }
        continue;
    }
    return $arrWish;
}
$arrWant = getArrayUniqueByKey($arrCoupon);
print_r($arrWant);

//输出结果：
/*
Array( [100-800] =>Array ( [couponCode] => 3033323852301057 [usableStartTime] => 1439740800[usableEndTime] => 1440768100 [couponAmount] => 100 [orderAmount] =>800 ) [200-800]=> Array ( [couponCode] => 3033323852301060 [usableStartTime] =>1439740800 [usableEndTime] => 1440758100 [couponAmount] => 200 [orderAmount]=> 800 ) ) */
