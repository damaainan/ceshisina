## php unset对json_encode的影响

来源：[http://www.cnblogs.com/saysmy/p/9957734.html](http://www.cnblogs.com/saysmy/p/9957734.html)

时间 2018-11-14 14:17:00

 
先运行一段php代码：

```php

$a = Array(0=>'hello world', 1=>'girl', 2=>'boy');

var_dump(json_encode($a));

unset($a[1]);

var_dump(json_encode($a));

```
 
返回结果如下：

```

string(28) "["hello world","girl","boy"]"
string(29) "{"0":"hello world","2":"boy"}"

```
 
#### 发现对一个数组unset前后，变量的类型变化了，unset前是数组，unset后是对象
 
#### 这是为什么呢？
 
#### 看下unset和json_encode究竟是做了什么：
 
unset() 销毁指定的变量。可以删除数组的指定元素，删除后索引不重排。
 
json_encode() 用于对变量进行 JSON 编码，该函数如果执行成功返回 JSON 数据，否则返回 FALSE
 
而json_encode转换的对象如果是数组，那么就需要注意下了，看下面的示例：
 
举例：

```php

$a = Array(0=>'hello world', 1=>'girl', 2=>'boy');
var_dump(json_encode($a));

$b = Array('name'=>'hello world', 'age'=>'18', 'gender'=>'man');
var_dump(json_encode($b));

```
 
运算结果：

```

string(28) "["hello world","girl","boy"]"
string(48) "{"name":"hello world","age":"18","gender":"man"}"

```
 
发现上面的结果一个是数组，一个是对象。
 
#### 这是因为$a是索引数组（连续数组）,$b则是关联数组（非连续数组）
 
#### 再看一个官方的例子：
 
![][0]
 
#### 以上输出的结果是：
 
![][1]
 
#### 注意：上面的第二个数组之所以转化后变成对象，是因为键值不是从0开始，这也是非连续数组
 
### 所以对一个连续数组执行unset后，会变成非连续数组，对非连续数组执行json_encode会变成对象。 
 
## 总结： 
 
php中：
 
索引数组：是指以数字为键的数组。并且这个键值 是自增的
 
关联数组：指的是一个键值对应一个值，并且这个键值是不规律的，通常都是我们自己指定的。
 
索引数组转为json后是数组。而关联数组转为json后是对象
 
## 拓展一:
 
那如果想对连续数组执行json_encode后变成对象可以这样做：

```php

$arr = array(
    '0'=>'a','1'=>'b','2'=>'c','3'=>'d'
);
echo json_encode((object)$arr);

```
 
输出结果为：

```

{"0":"a","1":"b","2":"c","3":"d"}

```
 
## 拓展二：
 
如何消除unset对json_encode的影响？达到转换结果依然为数组
 
使用unset时：

```php

foreach ($array as $k => $v) {
    if (某条件) {
        unset($array[$k]);
    }
}

```
 
优化后：

```php

$tmp = array();
foreach ($array as $k => $v) {
    if (某条件)) {
        continue;
    }
    $tmp[] = $v;
}
$array = $tmp;

```
 
对优化后的$array进行json_encode就可以完美的转换为数组而不是对象了


[0]: ../img/vMby2eV.png
[1]: ../img/fQbyEzB.png