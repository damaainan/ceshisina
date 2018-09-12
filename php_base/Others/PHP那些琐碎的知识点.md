# PHP那些琐碎的知识点

 时间 2017-05-18 20:33:37 

原文[https://i6448038.github.io/2017/03/25/PHP那些奇怪的语法/][1]


PHP不会检查单引号 '' 字符串中变量内插或（几乎）任何转义序列，所以采用单引号这种方式来定义字符串相当简单快捷。但是，双引号 "" 则不然，php会检查字符串中的变量或者转义序列，并输出变量和转义序列的值。 

    $a = "123";
    print '$a\t';
    print '$a';

输出：

    $a\t$a

如果是双引号 "" : 

    $a = "123";
    print "$a\t";
    print "$a";

输出：

## 注意： 

单引号 '' 可以解释 '\ 和 `\\` 这俩转义字符，就这俩！ 

能使单引号字符尽量使用单引号，单引号的效率比双引号要高（因为双引号要先遍历一遍，判断里面有没有变量，然后再进行操作，而单引号则不需要判断）。

单引号 '' 和双引号 "" 都可以用来 

## echo 和 print的区别： 

这两都是语句，不是函数；这俩语句的作用都是输出字符串。但是：

echo 可以传入多个参数。而 print 只有一个： 

    echo "123", "123";//输出123123
    print "123", "123";//报错，只可以写一个参数 print "123";

echo 无返回值，而 print 返回值恒为1； 

## 注意： 

PHP的八中数据类型，除了数组 array 和没有实现 `__toString` 魔法函数的对象都可以用echo或者print来输出，并且 boolean 类型的用echo或者print来输出，只会显示1或者不显示。 

    echo true; //输出1
    echo false; //什么都不输出

## 数字和字符串相加： 

PHP会自动完成字符串和数字的转换，这样有时候会带来好处，有时候却很让人苦恼。

    echo 1 + "2";//输出3
    echo 1 + "a";//输出1

$a = 1 + "A"; 试问变量 $a 的数据类型？ 

    if(is_numeric($a)){
        echo "是整型";
    }
    else{
        echo "是其他类型";
    }
    
    //最后输出：是整型

## 随机数生成函数 rand() 和 mt_rand() 的区别: 

rand() 和 mt_rand() 用法完全一致，它俩分别有两种用法: 

    //第一种用法：
    rand();//产生的随机数为0到getrandmax()之间
    mt_rand();//产生的随机数为0到mt_getrandmax()之间
    
    //第二种用法：
    rand($min, $max);//产生从$min到$max之间的随机数
    mt_rand($min, $max);//产生从$min到$max之间的随机数

区别： mt_rand() 是更好地随机数生成器，因为它跟 rand() 相比播下了一个更好地随机数种子；而且性能上比 rand() 快4倍， mt_getrandmax() 所表示的数值范围也更大 

## BCMath库和GMP库的区别: 

BCMath库很容易使用。将数字作为字符串传入函数，它会将数字的和（或差，积等）作为字符串返回。不过，使用BCMath时，对数字所能完成的操作仅限于基本算术运算。

    $sum = bcadd("12345678", "87654321");//$sum = "99999999"

GMP函数可以接受整数或者字符串作为参数，不过它们更乐意将数字作为资源来传递，这实际上是指向数字内部表示的指针。所以与BCMath函数不同，BCMath函数返回字符串，而GMP只返回资源。可以将这个资源作为数字传递到任何GMP函数。

    $four = gmp_add(2, 2);//可以传入整数
    $eight = gmp_add('4', '4');//或字符串
    $twelve = gmp_add($four, $eight);//或GMP资源

GMP唯一的缺点是，想要用非GMP函数查看或使用资源时，需要使用 gmp_strval() 或 gmp_intval() 显示地进行转换。 

## 注意 

BCMath与PHP捆绑发行，若GMP不与PHP捆绑，需要另外下载和安装。完成高精度数学运算的另一种选择是使用PECL的 big_int 库。 

## include和require的区别： 

include() 、 require() 语句包含并运行指定文件。这两结构在包含文件上完全一样，唯一的区别是对于错误的处理： 

* require() 语句在遇到包含文件不存在，或是出错的时候，就停止即行，并报错。
* include() 在遇到包含文件不存在的时候，只生成警告，并且脚本会继续。

换句话说，如果你想在丢失文件时停止处理页面，那就别犹豫了，用 require() 吧。 include() 就不是这样，脚本会继续运行。 

## include_once和require_once 

* include_once() 和 require_once() 一样，应该用于在脚本执行期间同一个文件有可能被包含超过一次的情况下，想确保它只被包含一次以避免函数重定义，变量重新赋值等问题。这就是 include_once() 和 require_once() 与 include() 和 require() 的主要区别。
* require_once() 、 include_once() 运行效率要比 require() 和 include() 低，因为前两者需要判断寻找引入的文件是否已经存在。`

## PHP合并数组 + 和 array_merge() 的区别 

同为数组合并，但是还是有差别的:

* 键名为数字时，array_merge()不会覆盖掉原来的值，但＋合并数组则会把最先出现的值作为最终结果返回，而把后面的数组拥有相同键名的那些值“抛弃”掉（不是覆盖）
```php
$a = array('a','b'); 
$b = array('c', 'd'); 
$c = $a + $b; 
var_dump($c);
//输出：
// array (size=2)
//  0 => string 'a' (length=1)
//  1 => string 'b' (length=1) 
var_dump(array_merge($a, $b));
//输出：
//array (size=4)
// 0 => string 'a' (length=1)
// 1 => string 'b' (length=1)
// 2 => string 'c' (length=1)
// 3 => string 'd' (length=1)
```
* 键名为字符时，＋仍然把最先出现的键名的值作为最终结果返回，而把后面的数组拥有相同键名的那些值“抛弃”掉，但array_merge()此时会覆盖掉前面相同键名的值
```php
$a = array('a' => 'a' ,'b' => 'b');
$b = array('a' => 'A', 'b' => 'B');
$c = $a + $b;
var_dump($c);
//输出：
//array (size=2)
//'a' => string 'a' (length=1)
//'b' => string 'b' (length=1)
var_dump(array_merge($a, $b));
//输出：
//array (size=2)
//'a' => string 'A' (length=1)
//'b' => string 'B' (length=1)
```
## 字符串常用函数 

PHP提供了很多方便的`字符串函数`，常用的有：

* strstr ( string $haystack , mixed $needle [, bool $before_needle = false ] ) 。返回 haystack 字符串从 needle 第一次出现的位置开始到 haystack 结尾的字符串。若为before_needle为 TRUE，strstr() 将返回 needle 在 haystack 中的位置之前的部分。
* substr( string $string , int $start [, int $length ] ) 。返回字符串 string 由 start 和 length 参数指定的子字符串。
* substr_replace ( mixed $string , mixed $replacement , mixed $start [, mixed $length ] ) 。substr_replace() 在字符串 string 的副本中将由 start 和可选的 length 参数限定的子字符串使用 replacement 进行替换。
* strrev ( string $string ) 。返回 string 反转后的字符串。
* str_replace ( mixed $search , mixed $replace , mixed $subject [, int &$count ] ) 。该函数返回一个字符串或者数组。该字符串或数组是将 subject 中全部的 search 都被 replace 替换之后的结果。subject为执行替换的数组或者字符串。也就是 haystack。如果 subject 是一个数组，替换操作将遍历整个 subject，返回值也将是一个数组。如果count被指定，它的值将被设置为替换发生的次数。
* strpos ( string $haystack , mixed $needle [, int $offset = 0 ] ) 。返回 needle 在 haystack 中首次出现的数字位置；如果提供了offset参数，搜索会从字符串该字符数的起始位置开始统计。 如果是负数，搜索会从字符串结尾指定字符数开始。
* ltrim() 、 rtrim() 、 trim() 。这仨都是删除字符串中的空白符。 ltrim() 删除字符串开头的空白字符; rtrim() 删除字符串末端的空白字符； trim() 去除字符串首尾处的空白字符。


[1]: https://i6448038.github.io/2017/03/25/PHP那些奇怪的语法/
