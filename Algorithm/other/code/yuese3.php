<?php 

/**
 * “约瑟夫环”是一个数学的应用问题：一群猴子排成一圈，按1,2,…,n依次编号。然后从第1只开始数，数到第m只,把它踢出圈，从它后面再开始数， 再数到第m只，在把它踢出去…，如此不停的进行下去， 直到最后只剩下一只猴子为止，那只猴子就叫做大王。要求编程模拟此过程，输入m、n, 输出最后那个大王的编号。
 *
 * 方法二：递归算法
 */



function killMonkey($monkeys , $m , $current = 0){
    $number = count($monkeys);
    $num = 1;
    if(count($monkeys) == 1){
        echo $monkeys[0]."成为猴王了\r\n";
        return;
    }
    else{
        while($num++ < $m){
            $current++ ;
            $current = $current%$number;
        }
        echo $monkeys[$current]."的猴子被踢掉了\r\n";
        array_splice($monkeys , $current , 1);
        killMonkey($monkeys , $m , $current);
    }
}
$monkeys = array(1 , 2 , 3 , 4 , 5 , 6 , 7, 8 , 9 , 10); //monkeys的编号
$m = 3; //数到第几只猴子被踢出
killMonkey($monkeys , $m);