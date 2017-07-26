<?php 
header("Content-type:text/html; Charset=utf-8");
/**
 * 猴子吃桃
 */


function Main()
 {
     $sum = SumPeach(1);

     echo "第一天摘得桃子有:". $sum."\n";

 }

 //递归
 function  SumPeach($day)
 {
     if ($day == 10)
         return 1;

     return 2 * SumPeach($day + 1) + 2;
 }

 Main();



function Main2()
{
    $sum = SumPeachTail(1, 1);

    printf("第一天摘得桃子有:%d", $sum);

}

//尾递归
function SumPeachTail($day, $total)
{
    if ($day == 10)
        return $total;

    //将当前的值计算出传递给下一层
    return SumPeachTail($day + 1, 2 * $total + 2);
}
Main2();