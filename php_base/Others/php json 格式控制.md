# php json 格式控制 

* [json][0]
* [php][1]

[后台技术][2]

关于 json 这个问题，陆陆续续有新手朋友找我问，比如为什么我输出的是 {"1":"item1","2":"item2","3":"item3"} 而不是 ["item1","item2","item3"]。

## php数组 与 js数组

> 我这里用 php 5.4 以上语法表示。

php 里有关联数组和索引数组，例如：

```php
<?php  
// 索引数组  
$arr = ['item1', 'item2', 'item3'];  
  
// 关联数组  
$arr = [  
  'name' => '张三',  
  'age' => '22',  
];
```
而 js 里只有一种数组，那就是索引数组，也许你会说可以用 K/V 键值对形式模拟关联数组啊。  
K/V 键值对看起来像，但他没有任何数组特性，这里就不详细说明了。

而上面的 php 数组 json_encode 后得到的 json 格式分别是 ["item1","item2","item3"] 和 {"name":"\u5f20\u4e09","age":"22"}。这里的中文被转为 Unicode 了，如果你非要显示中文，php 5.4 之后支持 JSON_UNESCAPED_UNICODE 参数，json_encode($arr, JSON_UNESCAPED_UNICODE) 即可得到 {"name":"张三","age":"22"}，不过非常不推荐这样写。

这里分别得到的是 js 下的 **数组** 和 **对象** 格式的 json 字符串，那为什么会生成这两种类型，或者说，什么情况会生成对象格式，什么情况会生成数组格式呢。

## php 数组 输出格式控制

大致几种情况我都列出来了，直接看代码。

```php
<?php  
  
$arr = [ // 不是 0 开始，会输出对象  
  1 => 'item1',  
  2 => 'item2',  
  3 => 'item3',  
];  
echo "输出对象: ", json_encode($arr), "\n";  
// 输出对象: {"1":"item1","2":"item2","3":"item3"}  
  
$arr = [ // 连续索引，输出数组  
  0 => 'item1',  
  1 => 'item2',  
  2 => 'item3',  
];  
echo "输出数组: ", json_encode($arr), "\n";  
// 输出数组: ["item1","item2","item3"]  
  
$arr = [ // 连续索引，输出数组  
  'item1',  
  'item2',  
  'item3',  
];  
echo "输出数组: ", json_encode($arr), "\n";  
// 输出数组: ["item1","item2","item3"]  
  
$arr = [ // 索引不连续，输出对象  
  0 => 'item1',  
  1 => 'item2',  
  2 => 'item3',  
  5 => 'item5',  
];  
echo "输出对象: ", json_encode($arr), "\n";  
// 输出对象: {"0":"item1","1":"item2","2":"item3","5":"item5"}  
  
$arr = [ // 包含关联索引，一定输出对象  
  0 => 'item1',  
  1 => 'item2',  
  2 => 'item3',  
  'other' => '其他字段'  
];  
echo "输出对象: ", json_encode($arr), "\n";  
// 输出对象: {"0":"item1","1":"item2","2":"item3","other":"\u5176\u4ed6\u5b57\u6bb5"}  
  
// 关联数组 + 索引数组 实例  
$arr = [ // 关联数组  
  'other' => '其他字段',  
  'count' => 3, // 数组个数  
  'list' => [ // 索引数组  
    'item1',  
    'item2',  
    'item3',  
  ],  
];  
echo "对象+数组: ", json_encode($arr), "\n";  
// 对象+数组: {"other":"\u5176\u4ed6\u5b57\u6bb5","count":3,"list":["item1","item2","item3"]}
```
其实第一种就是很多新手朋友经常遇到的问题。  
因为数据库读出来后他们喜欢把 id 当索引用，而数据库的 id 不是从 0 开始的，看下这个例子。

```php
$arr = $User->where($where)->find(); // 读取数据  
  
$list = [];  
foreach($arr as $key => $val) { // 遍历数组  
  $list[$key] = [  
    'name' => $val['name'],  
    'age' => $val['age'],  
  ];  
}  
  
$list['count'] = count($arr); // 其他属性  
  
echo json_encode($list); // 输出 json  
// {"1":{"name":"zhangsan","age":22},"2":{"name":"lisi","age":25},"count":2}
```
而最后一种是比较常用的写法，自定义字段和数组一起用，来修改下刚才例子。

```php
$arr = $User->where($where)->find(); // 读取数据  
  
$list = [];  
foreach($arr as $key => $val) { // 遍历数组  
  $list['list'][] = [ // 修改这里  
    'name' => $val['name'],  
    'age' => $val['age'],  
  ];  
}  
  
$list['count'] = count($arr); // 其他属性  
  
echo json_encode($list); // 输出 json  
// {"list":[{"name":"zhangsan","age":22},{"name":"lisi","age":25}],"count":2}
```
OK，希望对刚入门的 phper 有所帮助。

[0]: http://www.52cik.com/tags/json/
[1]: http://www.52cik.com/tags/php/
[2]: http://www.52cik.com/categories/后台技术/