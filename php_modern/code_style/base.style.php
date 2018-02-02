<?php
declare(strict_types=1); // 采用完全约束  必须放在第一行
header("Content-type:text/html; Charset=utf-8");
/**
 * Created by Sublime.
 * User: damaainan<damaainan@gmail.com>
 * Date: 2017-12-19 17:03:26
 * 基本代码格式
 */


// 标量

/**
 * int $name 则是形参类型声明
 * : int 是返回类型声明
 */
class Demo {
    public function age(int $age): int {
        return $age;
    }
}
$demo = new Demo();
echo $demo->age(10); 


function foobar(): int {
    return 1; // 1.0 报错 
}

var_dump(foobar()); // int(1)

// 字符串
    // 单引号  双引号 heredoc nowdoc

$num = 10;

// 转义 $num
$str = <<<EOD
Example of string $num
spanning multiple lines
using heredoc syntax.
EOD;
echo $str;
echo "\r\n*****\r\n";

// 不转义 $num
$str = <<<'EOD'
Example of string $num
spanning multiple lines
using nowdoc syntax.
EOD;
echo $str;
echo "\r\n*****\r\n";


// 数组

// for 循环

// foreach 循环

// while

// switch case

// if else

//

//

//

//

//
