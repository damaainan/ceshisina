## php json转换相关知识

来源：[http://fbd.intelleeegooo.cc/php-array-object-to-json/](http://fbd.intelleeegooo.cc/php-array-object-to-json/)

时间 2018-12-20 15:35:12


最近在查找一个bug的时候，发现前端传过来的json空对象`{}`，被php转换成了空数组`[]`存到了数据库里面, 读取并返回给前端的时候，没有做特殊处理，返回了`[]`给前端，导致一些问题。

所以决定梳理一下php的json转换相关的内容。


## 索引数组转json

看如下示例代码：

```php
$a = ['aa', 'bb', 'cc'];
$ret = json_encode($a);
var_dump($ret);
```

打印结果是：

```
string(16) "["aa","bb","cc"]"
```

可以看到，索引数组被转换成了json数组


## 关联数组转json

看如下示例代码：

```php
$a = [ 'a' => 'aa', 'b' => 'bb', 'c' => 'cc'];
$ret = json_encode($a);
var_dump($ret);
```

打印结果是：

```
string(28) "{"a":"aa","b":"bb","c":"cc"}"
```

可以看到，关联数组被转换成了json对象

其实索引数组也可以写成关联数组，看如下代码：

```php
$a = [ 0 => 'aa', 1 => 'bb', 2 => 'cc'];
$ret = json_encode($a);
var_dump($ret);

$a = [ 2 => 'aa', 3 => 'bb', 4 => 'cc'];
$ret = json_encode($a);
var_dump($ret);
```

打印结果是：

```
string(16) "["aa","bb","cc"]"
string(28) "{"2":"aa","3":"bb","4":"cc"}"
```

看第一个，索引数组变成关联数组，使用json_encode的时候，还是转换成了json数组；第二个关联数组，被转化成了json对象


## 强制把php索引数组转成json对象

上面看到json_encode把`索引数组`转成了`json数组`，如果要强制转成`json对象`怎么办？

可以用下面这两种方法。第一种方法，是在json_encode的时候设置第二个参数为JSON_FORCE_OBJECT。第二种方法是先强制将php数组变成了php对象，再将php对象转换成json对象

```php
$a = ['aa', 'bb', 'cc'];
$ret = json_encode($a, JSON_FORCE_OBJECT);
var_dump($ret); 

$ret = (object)$a; // 强制将php数组变成了php对象
var_dump($ret); 
$ret = json_encode($ret); // 将php对象转换成json对象
var_dump($ret);
```

打印结果是：

```
string(28) "{"0":"aa","1":"bb","2":"cc"}"
object(stdClass)#1 (3) {
  [0]=>
  string(2) "aa"
  [1]=>
  string(2) "bb"
  [2]=>
  string(2) "cc"
}
string(28) "{"0":"aa","1":"bb","2":"cc"}"
```


## json转成数组

```php
$str = '{"name":"zhangsan", "age": 18}';
$ret2 = json_decode($str, true);
var_dump($ret2);

$str = '["q", "w", "e"]';
$ret = json_decode($str, true);
var_dump($ret);
```

```
array(2) {
  ["name"]=>
  string(8) "zhangsan"
  ["age"]=>
  int(18)
}
array(3) {
  [0]=>
  string(1) "q"
  [1]=>
  string(1) "w"
  [2]=>
  string(1) "e"
}
```

可以看到，json_decode方法如果第二个参数是true的话，会把json对象/json数组转成php数组


## json转成对象

```php
$str = '{"name":"zhangsan", "age": 18}';
$ret = json_decode($str);
var_dump($ret);

var_dump($ret->name);
```

```
object(stdClass)#1 (2) {
  ["name"]=>
  string(8) "zhangsan"
  ["age"]=>
  int(18)
}
string(8) "zhangsan"
```

如果json_decode方法不加第二个参数的话，默认就是false，会把json对象/json数组转成php里面的对象。php里面的对象，可以使用`->`访问其变量


## 对象转成json

看如下示例代码：

```php
class TestJson {
        const CONST_VALUE_A = 'aaa';
        public $b = 'bbb';
        protected $c = 'ccc';
        private $d = 'ddd';
        public function hello() {

                print_r("hello\n");
        }
        public static $stValue = 'st';

}

$test = new TestJson();
var_dump($ret);
$ret = json_encode($test);
var_dump($ret);
```

打印结果是：

```
string(11) "{"b":"bbb"}"
```

可与看到，只有public变量，其他在转换成json的时候都被丢掉了

下面说一下我在本文开头提到的，“发现前端传过来的json空对象`{}`，被php转换成了空数组`[]`存到了数据库里面”

原来我的代码是这样实现的：

存数据相关示例代码：

```php
// 存数据
$raw = file_get_contents('php://input');
$param = json_decode($raw, true);
var_dump($param);
$s = json_encode($param);
var_dump($s); 

……
// 将$s存到了数据库表对应字段里面
……
……
```

打印结果是：

```
array(0) {
}
string(2) "[]"
```

取数据相关示例代码：

```php
// 取数据
$column = json_deocde($c, true);
$resp = [
	'status' => 0,
	'column' => $column,
];
echo json_encode($resp);
```

打印结果是：

```
{"status": 0, "column": []}
```

现在要避免上面这个问题，取出的代码应该这样写，才能实现前端传过来json空对象`{}`，保存在数据库里也是`{}`。从数据库里取出，返回给前端的时候也是一个json空对象`{}`存数据相关代码保持不变，取数据相关示例代码：

```php
// 取数据
$column = json_deocde($c, true);
if (empty($column))  {
	$column = (object)$column; //  强制将php空数组变成了php对象
}
$resp = [
	'status' => 0,
	'column' => $column,
];
echo json_encode($resp);
```

