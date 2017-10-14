<?php 
/**
 * 使用call_user_func_array和call_user_func自定义回调函数：

两个函数的第一个参数，均为回调函数，表示执行当前回调；

不同点在于：前者的第二个参数为数组，并将数组的每个值赋给回调函数的参数列表

后者的参数列表直接展开写到第2~多个参数中
 */

//变量函数
function func1($a){
    echo "前端{$a}";
}
$fun="func1";
$fun(10);

//自定义回调函数
function func2($start,$end,$fun){
    //$fun="func";
    //$fun();
    for($i=$start;$i<=$end;$i++){
        if($fun($i)){
            echo "{$i}\r\t";
        }
    }
}
function filter($num){
    if($num%3!=0){
        return true;
    }else{
        return false;
    }
}
func2(1,59,"filter");

//使用call_user_func_array和call_user_func自定义回调函数
function func3(){
    $arr = func_get_args()    ;
    $str = "";
    for($i=0;$i<count($arr);$i++){
        $str = $arr[$i];
    }
    return $str;
}

//相当于apply
echo call_user_func_array("func3", array("杰瑞","教育","HTML5","+","PHP"));
//相当于执行func函数，并且把数组的每一项作为参数传入
echo "\r\t";
//相当于call
echo call_user_func("func3","杰瑞","教育","HTML5","+","PHP");
