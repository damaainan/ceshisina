## PHP foreach遍历数组之如何判断当前值已经是数组的最后一个

来源：[http://www.cnblogs.com/HardCarroll/p/10122881.html](http://www.cnblogs.com/HardCarroll/p/10122881.html)

时间 2018-12-15 11:48:00


先给出foreach的两种语法格式

```
1，foreach (array_expression as $value)
    statement
2，foreach (array_expression as $key => $value)
    statement
```

第一种格式遍历给定的 array_expression 数组。每次循环中，当前单元的值被赋给  $value 并且数组内部的指针向前移一步（因此下一次循环中将会得到下一个单元）。

第二种格式做同样的事，只除了当前单元的键名也会在每次循环中被赋给变量 $key 。

foreach在数组遍历的时候格外好用，简单的一句话就能遍历整个数组而且不需要担心数组溢出的问题！

但有些时候我需要知道它是否已经循环到最后一个值了，比如我有一个数组，需要将它转换成json格式，虽然json_encode()可以直接转，这里的重点是如何判断数组已经遍历到最后一项 。

众所周知，json数据格式是键值对之间有逗号，最后一项没有逗号。

所以，这时候如果用foreach遍历的话就需要知道是否已经遍历到最后一项了，因为最后一项不要逗号呀！

end($dataArray) 的作用拿到数组的最后一项的值，只需要判断$value是否等于end($dataArray) 就可以啦！

完整代码如下：

```php


$dataArray = array("name"=>"klaus", "sex"=>"male", "age"=>"18", "country"=>"China");
$ret = '{"';
foreach($dataArray as $key=>$value) {
  $ret .= $key;
  $ret .= '":"';
  $ret .= $value;
  if($value !== end($dataArray)) {
    $ret .= '","';
  }
}
$ret .= '"}';


```

