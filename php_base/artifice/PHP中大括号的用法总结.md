# PHP中"{}"大括号的用法总结

 时间 2017-12-11 12:00:00  

原文[https://www.52bz.la/3518.html][1]


1、 `{}` 表示程序块的开始和结束

    if ($x==$y)  {  　do_nothing();  }

2、 `{}`用来表示字符串下标

(引用longnetpro兄弟的话)

`$s{1}`表示字符串

$s的第2个字节（不是第一个）

基本等同于`$s[1]`，只不过后者是老的写法

PHP手册推荐第一种写法 

3、分离变量

    $s = "Di, ";  echo ("${s}omething");  //Output: Di, omething

而如果用echo ("$something");

那么就会输出 $something 这个变量。 

更多示例

示例一

    $var='sky'; echo "{$var}boy"; //输出：skyboy

示例二

    $my_str="1234"; $my_str{2}='5'; echo $my_str;

[1]: https://www.52bz.la/3518.html
