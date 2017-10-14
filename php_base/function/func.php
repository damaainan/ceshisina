<?php 

/**
 * 可变参数列表传递：由于PHP实参可以比形参多，那么我们可以传递N个实参，并通过PHP内置函数取到对应参数。

func_get_args()//取所有参数列表（数组）

func_num_args()//返回参数的总个数，相当于count($arr);

func_get_arg(0)//根据下标，取每个参数；相当于$arr0;
 */

// function func($a){
//     $a+=10;
//     return $a;
// }
// echo func(10);

//引用类型的参数传递
$a=10;
function func1(&$a){
    $a+=10;
}
func1($a);//通过取址符号，可以直接将传入的$a的值改掉。
echo $a;

//默认参数
function func2($b,$a=10){
    return $a+$b;
}  
echo func2(20);

//可变参数列表
function func(){
    $arr=func_get_args();
    var_dump(func_get_args());//取所有参数列表（数组）
    var_dump(func_num_args());//返回参数的总个数
    //var_dump(count($arr));//同上
    var_dump(func_get_arg(0));//根据下标，取每个参数
    //var_dump($arr[0]);//同上
    $sum=0;
    $count=func_num_args();
    for($i=0;$i<$count;$i++){
        //$sum+=func_get_arg($i);
        $sum+=$arr[$i];
    }
    return $sum;
}
echo func(1,2,3,4,5);