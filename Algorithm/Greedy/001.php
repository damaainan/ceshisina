<?php

//0-1背包贪心算法问题
class tanxin
{
    public $weight;
    public $price;
    public function __construct($weight = 0, $price = 0)
    {
        $this->weight = $weight;
        $this->price  = $price;
    }
}
//生成数据
$n = 10;
for ($i = 1; $i <= $n; $i++) {
    $weight = rand(1, 20);
    $price  = rand(1, 10);
    $x[$i]  = new tanxin($weight, $price);
}
//输出结果
function display($x)
{
    $len = count($x);
    foreach ($x as $val) {
        echo $val->weight, ' ', $val->price;
        echo "\r\n";
    }
}
//按照价格和重量比排序
function tsort(&$x)
{
    $len = count($x);
    for ($i = 1; $i <= $len; $i++) {
        for ($j = 1; $j <= $len - $i; $j++) {
            $temp   = $x[$j];
            $res    = $x[$j + 1]->price / $x[$j + 1]->weight;
            $temres = $temp->price / $temp->weight;
            if ($res > $temres) {
                $x[$j]     = $x[$j + 1];
                $x[$j + 1] = $temp;
            }
        }
    }
}
//贪心算法
function tanxin($x, $totalweight = 50)
{
    $len      = count($x);
    $allprice = 0;
    for ($i = 1; $i <= $len; $i++) {
        if ($x[$i]->weight > $totalweight) {
            break;
        } else {
            $allprice += $x[$i]->price;
            $totalweight = $totalweight - $x[$i]->weight;
        }
    }
    if ($i < $len) {
        $allprice += intval($x[$i]->price * ($totalweight / $x[$i]->weight));
    }

    return $allprice;
}
tsort($x); //按非递增次序排序
display($x); //显示
echo '0-1背包最优解为:';
echo tanxin($x);
