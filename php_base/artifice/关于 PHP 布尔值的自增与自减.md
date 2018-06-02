## 关于 PHP 布尔值的自增与自减

来源：[http://www.phpyc.com/about-php-increment/](http://www.phpyc.com/about-php-increment/)

时间 2018-05-02 15:35:20

 
在上午和业务需求战斗结束之后，准备打开群看看各位老司机有没有看车，突然看到 @E舞九天 II 发的一些消息
 
![][0]
 
他发现，PHP 的布尔值自增，无论自增多少遍，最后输出的都是 1
 
这就比较有趣了~
 
自增和自减，++$a 和 $a++ 的区别大家都知道
 
```php
++a 表示取 a 的地址，增加内存中 a 的值，然后把值放在寄存器中
a++ 表示取 a 的地址，把 a 的值装入寄存器中，然后增加内存中 a 的值
```
 
我打开一个编辑器，去确认一下这个结果
 
```php
$a = true;

var_dump(--$a);

echo PHP_EOL;

echo $a;

$b = false;
echo PHP_EOL;
var_dump(++$b);
```
 
运行最后输入结果如下
 
```
bool(true)
1
bool(false)
```
 
发现结果和之前设想的不太对，PHP 对于布尔值的自增运算没有做任何处理，而自增后为 1 是因为我们使用了`echo`去输出，导致`bool`被强转
 
去查询 PHP 的官方文档，没想到有一行很明显的提示
 `Note: 递增／递减运算符不影响布尔值。递减 NULL 值也没有效果，但是递增 NULL 的结果是 1。`![][1]
 
:cry: 哈哈，以后还是要多看文档~~~
 


[0]: ../img/jaqiiyF.png 
[1]: ../img/uIjMniE.png 