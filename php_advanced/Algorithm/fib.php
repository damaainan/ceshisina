<?php  
//1 1 2 3 5 8 13 21 34 55  
//迭代  
function fib($n){  
    if($n<1) return -1;  
    $a[1]=$a[2]=1;  
    for($i=3;$i<=$n;$i++){  
        $a[$i]=$a[$i-1]+$a[$i-2];  
    }  
    return end($a);  
}  
//递归  
function fib2($n){  
    if($n<1) return -1;  
    if($n==1 || $n==2) return 1;  
    return fib2($n-1)+fib2($n-2);  
}  
echo fib(10).'<br/>';  
echo fib2(10).'<br/>';  