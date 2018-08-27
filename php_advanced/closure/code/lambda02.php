<?php
$f = function () {
    return 100;
};

function B(Closure $callback)
{
    return $callback();
}

$a = B($f);
print_r($a)



function printStr() {
    $func = function( $str ) {
        echo $str;
    };
    $func( ' hello my girlfriend ! ' );
}
printStr();//输出 hello my girlfriend !

//例二
//在函数中把匿名函数返回，并且调用它
function getPrintStrFunc() {
    $func = function( $str ) {
        echo $str;
    };
    return $func;
}
$printStrFunc = getPrintStrFunc();
$printStrFunc( ' do you  love me ? ' );//输出 do you  love me ?

//例三
//把匿名函数当做参数传递，并且调用它
function callFunc( $func ) {
    $func( ' no!i hate you ' );
}

$printStrFunc = function( $str ) {
    echo $str.'<br>';
};
callFunc( $printStrFunc );

//也可以直接将匿名函数进行传递。如果你了解js，这种写法可能会很熟悉
callFunc( function( $str ) {
    echo $str; //输出no!i hate you
} );