## PHP中array_merge函数与array+array的区别

来源：[https://segmentfault.com/a/1190000009114383](https://segmentfault.com/a/1190000009114383)


### 在PHP中可以使用array_merge函数和两个数组相加array+array的方式进行数组合并，但两者效果并不相同，下面为大家介绍两者具体的使用区别.
#### 区别如下：

```
1. 当下标为数值时，array_merge()不会覆盖掉原来的值，但array＋array合并数组则会把最先出现的值作为最终结果返回，而把后面的数组拥有相同键名的那些值“抛弃”掉（不是覆盖）. 
2. 当下标为字符时，array＋array仍然把最先出现的值作为最终结果返回，而把后面的数组拥有相同键名的那些值“抛弃”掉，但array_merge()此时会覆盖掉前面相同键名的值. 
```
#### 例子1:

代码：

```php
$arr1 = ['PHP', 'apache'];
$arr2 = ['PHP', 'MySQl', 'HTML', 'CSS'];
$mergeArr = array_merge($arr1, $arr2);
$plusArr = $arr1 + $arr2;
var_dump($mergeArr);
var_dump($plusArr);
```

结果：

$mergeArr：

```
array (size=6)
  0 => string 'PHP' (length=3)
  1 => string 'apache' (length=5)
  2 => string 'PHP' (length=3)
  3 => string 'MySQl' (length=5)
  4 => string 'HTML' (length=4)
  5 => string 'CSS' (length=3)
```

$plusArr：

```
array (size=4)
0 => string 'PHP' (length=3)
1 => string 'apache' (length=5)
2 => string 'HTML' (length=4)
3 => string 'CSS' (length=3)
```
#### 例子2:

代码：

```php
$arr1 = ['PHP', 'a'=>'MySQl'];
$arr2 = ['PHP', 'MySQl', 'a'=>'HTML', 'CSS'];
$mergeArr = array_merge($arr1, $arr2);
$plusArr = $arr1 + $arr2;
var_dump($mergeArr);
var_dump($plusArr);
```

结果

$mergeArr:

```
array (size=5)
  0 => string 'PHP' (length=3)
  'a' => string 'HTML' (length=4)
  1 => string 'PHP' (length=3)
  2 => string 'MySQl' (length=5)
  3 => string 'CSS' (length=3)
```

$plusArr：

```
array (size=4)
0 => string 'PHP' (length=3)
'a' => string 'MySQl' (length=5)
1 => string 'MySQl' (length=5)
2 => string 'CSS' (length=3)
```
#### 例子3:

代码:

```php
$arr1 = ['PHP', 'a'=>'MySQl','6'=>'CSS'];
$arr2 = ['PHP', 'MySQl', 'a'=>'HTML', 'CSS'];
$mergeArr = array_merge($arr1, $arr2);
$plusArr = $arr1 + $arr2;
var_dump($mergeArr);
var_dump($plusArr);
```

结果:

$mergeArr：

```
array (size=6)
  0 => string 'PHP' (length=3)
  'a' => string 'HTML' (length=4)
  1 => string 'CSS' (length=3)
  2 => string 'PHP' (length=3)
  3 => string 'MySQl' (length=5)
  4 => string 'CSS' (length=3)
```

$plusArr：

```
array (size=5)
0 => string 'PHP' (length=3)
'a' => string 'MySQl' (length=5)
6 => string 'CSS' (length=3)
1 => string 'MySQl' (length=5)
2 => string 'CSS' (length=3)
```

相信通过上边三个例子大家已经非常清楚array_merge()函数和array+array数组相加的区别了吧。
转载请注明来源：[https://segmentfault.com/a/11...][0]

[0]: https://segmentfault.com/a/1190000009114383?_ea=1827708
