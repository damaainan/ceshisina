<?php
/**
 *钓鱼比赛
ss请cc来家里钓鱼，鱼塘可划分为n＊m的格子，每个格子有不同的概率钓上鱼，cc一直在坐标(x,y)的格子钓鱼，而ss每分钟随机钓一个格子。问t分钟后他们谁至少钓到一条鱼的概率大？为多少？

输入描述:
第一行五个整数n,m,x,y,t(1≤n,m,t≤1000,1≤x≤n,1≤y≤m);
接下来为一个n＊m的矩阵，每行m个一位小数，共n行，第i行第j个数代表坐标为(i,j)的格子钓到鱼的概率为p(0≤p≤1)


输出描述:
输出两行。第一行为概率大的人的名字(cc/ss/equal),第二行为这个概率(保留2位小数)

输入例子:
2 2 1 1 1
0.2 0.1
0.1 0.4

输出例子:
equal
0.20
 */
/**
 * 每分钟随机钓一个格子  概率平均
 */
function deal($n, $m, $x, $y, $t, $arr) {
    $cp = $arr[$x - 1][$y - 1];
    $count = 0;
    foreach ($arr as $ke => $va) {
        $count += array_sum($va);
    }
    $sp = $count / ($n * $m);
    $cpp = 1 - pow($cp, $t);
    $spp = 1 - pow($sp, $t); //一条都钓不到的概率
    // var_dump($spp);
    // var_dump($cpp);
    $p = $cpp == $spp ? "equal" : ($cpp > $spp ? "ss" : "cc");

    echo "name=", $p, "<br/>";
    if ($p == "cc") {
        echo 1 - $cpp;
    } else {
        echo 1 - $spp;
    }

}

$n = 2;
$m = 2;
$x = $y = 1;
$t = 1;
$arr = [
    [0.2, 0.2],
    [0.1, 0.4],
];
deal($n, $m, $x, $y, $t, $arr);