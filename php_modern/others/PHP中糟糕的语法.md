## PHP中糟糕的语法

来源：[http://www.hongweipeng.com/index.php/archives/1688/](http://www.hongweipeng.com/index.php/archives/1688/)

时间 2018-12-13 15:22:00

 
大多使用截图是因为可能未来某个版本就修复了，留个图，有图有真相
 
## 起步
 
今天只想讲 php 里面糟糕的东西。后续有新的话再过来补充。
 
## 混乱的函数命名
 
### 要不要下划线

```
# get
gettype, get_class

# 字符串操作
str_ireplace, str_pad, str_repeat, str_replace, str_shuffle, str_split, str_word_count, strcasecmp, strchr, strcmp, strcoll, strcspn

# PHP 相关信息
php_uname, php_sapi_name, php_logo_guid, phpinfo, phpcredits, phpversion

# encode 相关
base64_encode, quoted_printable_encode, session_encode, rawurlencode, urlencode, gzencode
htmlentities, html_entity_decode
```
 
### to 还是 2

```
# to
stream_copy_to_stream, strtolower, strtotime, strtoupper, unixtojd

# 2
bin2hex, deg2rad, hex2bin, ip2long, long2ip, nl2br, rad2deg
```
 
## 混乱的参数顺序

```php
# 回调函数放最后
array array_filter      (array $input  [, callback $callback  ] )
array array_uintersect  (array $array1  , array $array2  [, array $ ...  ], callback $data_compare_func  )
bool usort              ( array &$array, callback $cmp_function  )

# 回调函数放最前
array array_map         (callback $callback , array $arr1  [, array $...  ] )
mixed call_user_func    (callback $function [, mixed $parameter [, mixed $... ]] )
```
 
数组或字符串搜索时，数组 (`$needle`) 放置的位置混乱：

```php
int strpos          (string $haystack, mixed $needle  [, int $offset= 0  ] )
bool in_array       (mixed $needle, array $haystack  [, bool $strict  ] )

string stristr      (string $haystack, mixed $needle [, bool $before_needle = false ] )
mixed array_search  (mixed $needle, array $haystack  [, bool $strict  ] )
```
 
所以，记不住 PHP 的函数名和参数顺序真不是 PHPer 的记性不好，语言本身没能提供一致性，使得开发人员在使用过程中不断推倒之前的经验和使用习惯，导致的结果就是更加依赖文档，很难达到函数的使用。
 
一致的语言能让开发人员创建在整个语言中工作的习惯和期望，更快地学习语言，更容易找到错误，并且可以减少一次跟踪的事情。
 
## 左结合型的三目运算符
 
![][0]
 
为什么第三第四个打印的是 b ? 再看看 C 语言中的：
 
![][1]
 
这其实是因为 PHP 中的三目运算符是左结合式的。`1 ? "a" : 0 ? "b" : "c"`当成`(1 ? "a" : 0) ? "b" : "c"`。
 
其他语言的三目运算都是采用右结合的方式`1 ? "a" : (0 ? "b" : "c")`，只有 php 这么鹤立独行。
 
##`__toString()`中不允许抛出异常 

```php
class A {
    public function __toString()
    {
        if(!isset($this->a)) {
            throw new Exception('i am an exception');
        }
        return 'this is a';
    }
}

$a = new A();

try {
    echo $a;
} catch (Exception $e) {

}
```
 
这段代码将无法工作，因为不能在`__toString()`方法中抛出异常, 否则引起致命错误。
 
WarningYou cannot throw an exception from within a[__toString()][6] method. Doing so will result in a fatal error.
 
##`func_get_args()`不是获取参数列表 
 
文档中的描述(截图于2018-12-13):
 
![][2]
 
文档中描述的是返回一个包含函数参数列表的数组，而事实上：
 
![][3]
 
参数列表不应该是`[1, null, 3]`吗? 它获取的明明是传递的参数列表，其实我更愿相信这是文档上的疏忽，你看`func_num_args`的描述就没有问题：
 
![][4]
 
那我有别的办法获得函数的参数列表吗？？？
 
## 成员变量定义时不能调用函数

```php
<?php
class A {
   public $var = [
        'dir' => dirname(__FILE__)
    ];
}

// Fatal error: Constant expression contains invalid operations in test.php on line 3
```
 
只能在构造函数中进行初始化了。
 
## 数组类并不总是能在`array_*`运行 

```php
<?php

class MyArray implements ArrayAccess {
    function offsetGet($key){}
    function offsetSet($key,$value){}
    function offsetExists($key){return true;}
    function offsetUnset($key){}
}

$x = new MyArray();
var_dump(array_key_exists("a", $x));     # bool(false)
var_dump(isset($x["a"]));                # bool(true)
```
 
即使实现所有正确接口以表现为数组的类上，也不起作用。PHP 没有完善的行为特征的定义制度。这一方面可以参考下 Python 的鸭子类型协议。
 
## 默认的 htmlspecialchars 并不能过滤 XSS
 
![][5]
 `htmlspecialchars`默认不会对单引号做过滤，要达到过滤 XSS 效果需要是：

```php
htmlspecialchars($str, ENT_QUOTES);
```
 
## parse_str 函数
 `parse_str(string $url)`用来从URL查询字符串返回键/值对，不过这函数名起的真是过分。含糊不清的名字极具有误导性。


[6]: http://php.net/manual/zh/language.oop5.magic.php#object.tostring
[0]: https://img1.tuicool.com/2yyYRbu.png 
[1]: https://img1.tuicool.com/JBBJj27.png 
[2]: https://img1.tuicool.com/QrIVfyR.png 
[3]: https://img1.tuicool.com/aiQ77rq.png 
[4]: https://img1.tuicool.com/eiqENfj.png 
[5]: https://img0.tuicool.com/iuAzu2z.png 