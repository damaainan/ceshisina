<?php

// PHP时间正则操作
/*
 *今天 17:27 ^今天.*
 *昨天 19:35 ^昨天.*
 *25日 21:30 ^[\d]*日
 *02月28日 ^[\d]*月[\d]*日
 *13年06月25日 ^[\d]*年[\d]*月[\d]*日
 */

//昨天，今天和明天的日期转换
//($startstr 今天开始时间戳)
//返回(昨天，今天和明天)的0点和23点59分59秒
function alldaytostr($startstr)
{
    $oneday_count = 3600 * 24; //一天有多少秒
    //明天
    $tomorrow_s = $startstr + $oneday_count; //明天开始
    $tomorrow_e = $tomorrow_s + $oneday_count - 1; //明天结束
    //昨天
    $yesterday_s = $startstr - $oneday_count; //昨天开始
    $yesterday_e = $startstr - 1; //昨天结束
    //今天结束
    $today_e = $tomorrow_s - 1;

//昨天、今天和明天 0点和当天23点59分59秒合并成数组
    $allday_array = array('yesterday' => array($yesterday_s, $yesterday_e),
        'today'                           => array($startstr, $today_e),
        'tomorrow'                        => array($tomorrow_s, $tomorrow_e));

    return $allday_array;
}

date_default_timezone_set("Asia/Shanghai");
//当天开始时间
$btime = date('Y-m-d' . ' 00:00:00');

//转换成“开始”的时间戳
$btimestr = strtotime($btime);

$daylist = alldaytostr($btimestr);

$time = "13年06月25日";

//今天
if (preg_match('/^今天.*/', $time)) {
    preg_match('/[\d]*:[\d]*/', $time, $day);
    $created_at = strtotime(date('Y-m-d' . ' ' . $day[0] . ':00'));
} else if (preg_match('/^昨天.*/', $time)) {
    //昨天
    preg_match('/[\d]*:[\d]*/', $time, $day);
    $created_at = strtotime(date('Y-m-d' . ' ' . $day[0] . ':00', $daylist['yesterday'][0]));
} else if (preg_match('/^[\d]*日/', $time)) {
    //本月
    preg_match('/^([\d]*)日\s([\d:]*)/', $time, $day);
    $created_at = strtotime(date('Y-m-' . $day[1] . ' ' . $day[2] . ':00'));
} else if (preg_match('/^[\d]*月[\d]*日/', $time)) {
    //本年
    preg_match('/^([\d]*)月([\d]*)日/', $time, $day);
    $created_at = strtotime(date('Y-' . $day[1] . '-' . $day[2] . ' 00:00:00'));
} else if (preg_match('/^[\d]*年[\d]*月[\d]*日/', $time)) {
    //历年
    preg_match('/^([\d]*)年([\d]*)月([\d]*)日/', $time, $day);
    $created_at = strtotime(date($day[1] . '-' . $day[2] . '-' . $day[3] . ' 00:00:00'));
}

echo $created_at;
